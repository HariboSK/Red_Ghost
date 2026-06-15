<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/config/config.php';
require_once dirname(__DIR__) . '/middleware/Function.php';
require_once dirname(__DIR__) . '/SessionHelper.php';

SessionHelper::bootstrap();

// --- INICIALIZÁCIA DATABÁZOVÉHO SPOJENIA ---
$conn = $GLOBALS['conn'] ?? null;

if (!$conn && class_exists('Database')) {
    try {
        $database = new Database();
        $conn = $database->getConnection();
    } catch (Throwable $e) {
        $_SESSION['checkout_error'] = 'Nepodarilo sa inicializovať databázu: ' . $e->getMessage();
        header('Location: /payment');
        exit;
    }
}

if (!$conn) {
    $_SESSION['checkout_error'] = 'Chyba aplikácie: Databázové spojenie ($conn) nie je k dispozícii.';
    header('Location: /payment');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /payment');
    exit;
}

$sessionUser = SessionHelper::user();
$userId = (isset($sessionUser['id']) && (int)$sessionUser['id'] > 0) ? (int)$sessionUser['id'] : null;
$userEmail = (string) ($sessionUser['email'] ?? '');

$cart = $_SESSION['cart'] ?? [];
if (!is_array($cart) || $cart === []) {
    $_SESSION['checkout_error'] = 'Košík je prázdny.';
    header('Location: /shopcart');
    exit;
}

// Priprava udajov z Postu
$customerName = trim((string) ($_POST['customer_name'] ?? ''));
$customerEmail = trim((string) ($_POST['customer_email'] ?? $userEmail));
$customerPhone = trim((string) ($_POST['customer_phone'] ?? ''));
$paymentMethod = trim((string) ($_POST['payment_method'] ?? 'card'));
$deliveryMethod = trim((string) ($_POST['delivery_method'] ?? 'courier'));

//pokús načítať základnú adresu z prvého kroku
$street = trim((string) ($_POST['street'] ?? ''));
$city   = trim((string) ($_POST['city'] ?? ''));
$zip    = trim((string) ($_POST['zip'] ?? ''));

//Ak je zvolený iný spôsob doručenia ako základná adresa, prepíš tieto hodnoty
if ($paymentMethod === 'cash') {
    if ($deliveryMethod === 'alzabox') {
        $boxName = trim((string) ($_POST['alzabox_name'] ?? ''));
        $boxCode = trim((string) ($_POST['alzabox_code'] ?? ''));
        
        if ($boxName !== '' && $boxCode !== '') {
            $street = "AlzaBox: $boxName ($boxCode)";
            $city = "Odberné miesto";
            $zip = "00000";
        }
    } elseif ($deliveryMethod === 'post') {
        $pName   = trim((string) ($_POST['post_name'] ?? ''));
        $pStreet = trim((string) ($_POST['post_street'] ?? ''));
        $pCity   = trim((string) ($_POST['post_city'] ?? ''));
        $pZip    = trim((string) ($_POST['post_zip'] ?? ''));

        if ($pName !== '' && $pStreet !== '' && $pCity !== '' && $pZip !== '') {
            $street = "Pošta: $pName, $pStreet";
            $city = $pCity;
            $zip = $pZip;
        }
    } elseif ($deliveryMethod === 'courier') {
        $cStreet = trim((string) ($_POST['courier_street'] ?? ''));
        $cCity   = trim((string) ($_POST['courier_city'] ?? ''));
        $cZip    = trim((string) ($_POST['courier_zip'] ?? ''));

        if ($cStreet !== '' && $cCity !== '' && $cZip !== '') {
            $street = $cStreet;
            $city = $cCity;
            $zip = $cZip;
        }
    }
}

if ($customerName === '' || $customerEmail === '' || $customerPhone === '' || $city === '' || $street === '' || $zip === '') {
    $_SESSION['checkout_error'] = 'Vyplň všetky povinné dodacie údaje.';
    header('Location: /payment');
    exit;
}

if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['checkout_error'] = 'Zadaj platnú emailovú adresu.';
    header('Location: /payment');
    exit;
}

$allowedPaymentMethods = ['card', 'cash', 'transfer'];
if (!in_array($paymentMethod, $allowedPaymentMethods, true)) {
    $paymentMethod = 'card';
}

$orderTotal = 0.0;
$orderItems = [];

try {
    $conn->beginTransaction();

    $productStmt = $conn->prepare('SELECT id_product, 
                                            name, 
                                            price, 
                                            stock, 
                                            discount 
                                            FROM product 
                                            WHERE id_product = :id_product 
                                            LIMIT 1 FOR UPDATE');

    $updateStockStmt = $conn->prepare('UPDATE product 
                                            SET stock = stock - :quantity 
                                            WHERE id_product = :id_product');
    
    $orderStmt = $conn->prepare('INSERT INTO `order` 
                                            (user_id, customer_name, customer_email, customer_phone, total_price, status, delivery_method, created_at) 
                                            VALUES (:user_id, :customer_name, :customer_email, :customer_phone, :total_price, :status, :delivery_method, NOW())');
    
    $orderItemStmt = $conn->prepare('INSERT INTO `order_item` 
                                                (id_order, id_product, quantity, price) 
                                                VALUES (:id_order, :id_product, :quantity, :price)');
    
    $addressStmt = $conn->prepare('INSERT INTO `order_address` 
                                                (type, street, city, zip, country, id_order) 
                                                VALUES (:type, :street, :city, :zip, :country, :id_order)');
    
    $paymentStmt = $conn->prepare('INSERT INTO `payment` 
                                                (id_order, payment_method, amount, status, paid_at) 
                                                VALUES (:id_order, :payment_method, :amount, :status, NULL)');

    foreach ($cart as $item) {
        $productId = (int) ($item['id_product'] ?? ($item['id'] ?? 0));
        $quantity = max(1, (int) ($item['quantity'] ?? 0));

        if ($productId <= 0 || $quantity <= 0) {
            throw new RuntimeException('Neplatná položka v košíku.');
        }

        $productStmt->execute([':id_product' => $productId]);
        $product = $productStmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new RuntimeException('Produkt s ID ' . $productId . ' sa nenašiel v DB.');
        }

        $stock = (int) ($product['stock'] ?? 0);
        if ($stock < $quantity) {
            throw new RuntimeException('Na sklade nie je dosť kusov pre produkt: ' . (string) ($product['name'] ?? 'Produkt'));
        }

        // VÝPOČET CENY SO ZĽAVOU PRODUKTU
        $basePrice = (float) ($product['price'] ?? 0);
        $discount = (float) ($product['discount'] ?? 0);
        $unitPrice = $basePrice;

        if ($discount > 0) {
            $unitPrice = $basePrice * (1 - ($discount / 100));
        }

        $lineTotal = $unitPrice * $quantity;

        $orderItems[] = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'price' => $unitPrice,
        ];

        $orderTotal += $lineTotal;
        $updateStockStmt->execute([
            ':quantity' => $quantity,
            ':id_product' => $productId,
        ]);
    }

    // APLIKÁCIA ZĽAVOVÉHO KÓDU NA CELKOVÚ SUMU OBJEDNÁVKY
    $appliedDiscountAmount = (float) ($_SESSION['applied_discount_amount'] ?? 0.0);
    if ($appliedDiscountAmount > 0) {
        $orderTotal = max(0.0, $orderTotal - $appliedDiscountAmount);
    }

    if ($orderTotal <= 0) {
        throw new RuntimeException('Celková suma objednávky je neplatná.');
    }

    $orderStmt->bindValue(':user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $orderStmt->bindValue(':customer_name', $customerName, PDO::PARAM_STR);
    $orderStmt->bindValue(':customer_email', $customerEmail, PDO::PARAM_STR);
    $orderStmt->bindValue(':customer_phone', $customerPhone, PDO::PARAM_STR);
    $orderStmt->bindValue(':total_price', $orderTotal);
    $orderStmt->bindValue(':status', 'pending', PDO::PARAM_STR);
    $orderStmt->bindValue(':delivery_method', $deliveryMethod, PDO::PARAM_STR);
    $orderStmt->execute();
    $orderId = (int) $conn->lastInsertId();

    foreach ($orderItems as $orderItem) {
        $orderItemStmt->execute([
            ':id_order' => $orderId,
            ':id_product' => $orderItem['product_id'],
            ':quantity' => $orderItem['quantity'],
            ':price' => $orderItem['price'],
        ]);
    }

    $addressStmt->execute([
        ':id_order' => $orderId,
        ':type' => 'shipping',
        ':city' => $city,
        ':street' => $street,
        ':zip' => $zip,
        ':country' => 'Slovensko',
    ]);

    $paymentStmt->execute([
        ':id_order' => $orderId,
        ':payment_method' => $paymentMethod,
        ':amount' => $orderTotal,
        ':status' => 'pending',
    ]);

    // ZÁPIS UPLATNENIA ZĽAVOVÉHO KÓDU (iba ak bol nejaký úspešne aplikovaný)
    $appliedDiscountCodeId = $_SESSION['applied_discount_code_id'] ?? null;
    if ($appliedDiscountCodeId !== null && $userId !== null) {
        // Poistka vo vnútri transakcie pred samotným zápisom
        $checkRedeem = $conn->prepare('SELECT id_redemption FROM discount_code_redemption WHERE id_user = :id_user AND id_discount_code = :id_discount_code LIMIT 1');
        $checkRedeem->execute([':id_user' => $userId, ':id_discount_code' => $appliedDiscountCodeId]);
        
        if ($checkRedeem->fetch()) {
            throw new RuntimeException('Tento zľavový kód ste už uplatnili v minulosti.');
        }

        $redemptionStmt = $conn->prepare('
            INSERT INTO discount_code_redemption (id_discount_code, id_user, id_order, used_at) 
            VALUES (:id_discount_code, :id_user, :id_order, NOW())
        ');
        $redemptionStmt->execute([
            ':id_discount_code' => $appliedDiscountCodeId,
            ':id_user' => $userId,
            ':id_order' => $orderId
        ]);

        // Vymazanie zliav zo session, aby kód nezostal aktívny pre ďalší nákup
        unset($_SESSION['applied_discount_code']);
        unset($_SESSION['applied_discount_code_id']);
        unset($_SESSION['applied_discount_amount']);
    }

    if ($userId !== null && $userId > 0) {
        $pointsStmt = $conn->prepare('UPDATE `user` SET loyalty_points = COALESCE(loyalty_points, 0) + :points WHERE id = :user_id');
        $pointsStmt->execute([':points' => 50, ':user_id' => $userId]);
        SessionHelper::refreshSessionPoints($conn, $customerEmail);
    }

    $conn->commit();

    $_SESSION['cart'] = [];
    $_SESSION['checkout_order_id'] = $orderId;
    $_SESSION['checkout_success'] = 'Objednávka bola prijatá. Ďakujeme za nákup.';

    header('Location: /thank_you');
    exit;

} catch (Throwable $e) {
    if (isset($conn) && $conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log('[checkout] ' . $e->getMessage());
    $_SESSION['checkout_error'] = 'Chyba databázy/kódu: ' . $e->getMessage();
    header('Location: /payment');
    exit;
}