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

// Ak spojenie stále neexistuje, vyhodiť bezpečné stopnutie
if (!$conn) {
    $_SESSION['checkout_error'] = 'Chyba aplikácie: Databázové spojenie ($conn) nie je k dispozícii.';
    header('Location: /payment');
    exit;
}
// --------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /payment');
    exit;
}

$sessionUser = SessionHelper::user();
// Ak je používateľ prihlásený, vezme ID. Ak nie je, priradí striktne null, čo DB akceptuje
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

$street = ''; 
$city = ''; 
$zip = '';

if ($deliveryMethod === 'alzabox') {
    $boxName = trim((string) ($_POST['alzabox_name'] ?? ''));
    $boxCode = trim((string) ($_POST['alzabox_code'] ?? ''));
    if ($boxName === '' || $boxCode === '') {
        $_SESSION['checkout_error'] = 'Vyberte prosím konkrétny AlzaBox.'; header('Location: /payment'); exit;
    }
    $street = "AlzaBox: $boxName ($boxCode)";
    $city = "Odberné miesto";
    $zip = "00000";

} elseif ($deliveryMethod === 'post') {

    $pName = trim((string) ($_POST['post_name'] ?? ''));
    $pStreet = trim((string) ($_POST['post_street'] ?? ''));
    $pCity = trim((string) ($_POST['post_city'] ?? ''));
    $pZip = trim((string) ($_POST['post_zip'] ?? ''));

    if ($pName === '' || $pStreet === '' || $pCity === '' || $pZip === '') {
        $_SESSION['checkout_error'] = 'Vyplňte všetky údaje pre poštu.';
        header('Location: /payment');
        exit;
    }

    $street = "Pošta: {$pName}, {$pStreet}";
    $city = $pCity;
    $zip = $pZip;

} elseif ($deliveryMethod === 'courier') {

    $street = trim((string) ($_POST['courier_street'] ?? ''));
    $city = trim((string) ($_POST['courier_city'] ?? ''));
    $zip = trim((string) ($_POST['courier_zip'] ?? ''));

    if ($street === '' || $city === '' || $zip === '') {
        $_SESSION['checkout_error'] = 'Vyplňte adresu kuriéra.';
        header('Location: /payment');
        exit;
    }

} else {

    $street = trim((string) ($_POST['street'] ?? ''));
    $city = trim((string) ($_POST['city'] ?? ''));
    $zip = trim((string) ($_POST['zip'] ?? ''));

    if ($street === '' || $city === '' || $zip === '') {
        $_SESSION['checkout_error'] = 'Vyplňte adresu doručenia.';
        header('Location: /payment');
        exit;
    }
}

if ($customerName === '' || $customerEmail === '' || $customerPhone === '' || $city === '' || $street === '' || $zip === '') {
    $_SESSION['checkout_error'] = 'Vyplň všetky dodacie údaje. JavaScript ti ich mohol skryť, ale PHP ich vyžaduje!';
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

    $productStmt = $conn->prepare('SELECT id_product, name, price, stock FROM product WHERE id_product = :id_product LIMIT 1 FOR UPDATE');
    $updateStockStmt = $conn->prepare('UPDATE product SET stock = stock - :quantity WHERE id_product = :id_product');
    
    $orderStmt = $conn->prepare(
                                'INSERT INTO `order`
                                (
                                    user_id,
                                    customer_name,
                                    customer_email,
                                    customer_phone,
                                    total_price,
                                    status,
                                    delivery_method,
                                    created_at
                                )
                                VALUES
                                (
                                    :user_id,
                                    :customer_name,
                                    :customer_email,
                                    :customer_phone,
                                    :total_price,
                                    :status,
                                    :delivery_method,
                                    NOW()
                                )'
    );
    
    $orderItemStmt = $conn->prepare(
        'INSERT INTO `order_item` (id_order, id_product, quantity, price)
         VALUES (:id_order, :id_product, :quantity, :price)'
    );
    
    $addressStmt = $conn->prepare(
        'INSERT INTO `order_address` (type, street, city, zip, country, id_order)
         VALUES (:type, :street, :city, :zip, :country, :id_order)'
    );
    
    $paymentStmt = $conn->prepare(
        'INSERT INTO `payment` (id_order, payment_method, amount, status, paid_at)
         VALUES (:id_order, :payment_method, :amount, :status, NULL)'
    );

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

        $unitPrice = (float) ($product['price'] ?? 0);
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

    if ($orderTotal <= 0) {
        throw new RuntimeException('Celková suma objednávky je neplatná.');
    }

    // --- BEZPEČNÉ NAVIAZANIE HODNÔT (Rieši prechod NULL do foreign key) ---
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

    // Zápis do order_address 
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

    // Loyalty body dáme len ak je užívateľ reálne registrovaný a prihlásený
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
    $_SESSION['checkout_error'] = 'Chyba databázy/kódu: ' . $e->getMessage() . ' v súbore ' . $e->getFile() . ' na riadku ' . $e->getLine();
    header('Location: /payment');
    exit;
}