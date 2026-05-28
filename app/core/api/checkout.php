<?php

require_once dirname(__DIR__, 3) . '/config/config.php';
require_once dirname(__DIR__) . '/middleware/function.php';
require_once dirname(__DIR__) . '/session_helper.php';

SessionHelper::bootstrap();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . route('/payment'));
    exit;
}

$sessionUser = SessionHelper::user();
$userId = (int) ($sessionUser['id'] ?? 0);
$userEmail = (string) ($sessionUser['email'] ?? '');

if ($userId <= 0 || $userEmail === '') {
    $_SESSION['checkout_error'] = 'Na dokončenie objednávky sa musíte prihlásiť.';
    header('Location: ' . route('/login'));
    exit;
}

$cart = $_SESSION['cart'] ?? [];
if (!is_array($cart) || $cart === []) {
    $_SESSION['checkout_error'] = 'Košík je prázdny.';
    header('Location: ' . route('/shopcart'));
    exit;
}

$customerName = trim((string) ($_POST['customer_name'] ?? ''));
$customerEmail = trim((string) ($_POST['customer_email'] ?? $userEmail));
$customerPhone = trim((string) ($_POST['customer_phone'] ?? ''));
$city = trim((string) ($_POST['city'] ?? ''));
$street = trim((string) ($_POST['street'] ?? ''));
$zip = trim((string) ($_POST['zip'] ?? ''));
$paymentMethod = trim((string) ($_POST['payment_method'] ?? 'card'));
$cashDelivery = trim((string) ($_POST['cash_delivery'] ?? 'standard'));

if ($customerName === '' || $customerEmail === '' || $customerPhone === '' || $city === '' || $street === '' || $zip === '') {
    $_SESSION['checkout_error'] = 'Vyplň všetky dodacie údaje.';
    header('Location: ' . route('/payment'));
    exit;
}

if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['checkout_error'] = 'Zadaj platnú emailovú adresu.';
    header('Location: ' . route('/payment'));
    exit;
}

$allowedPaymentMethods = ['card', 'cash', 'transfer'];
if (!in_array($paymentMethod, $allowedPaymentMethods, true)) {
    $paymentMethod = 'card';
}

$allowedCashDelivery = ['standard', 'fast', 'pickup'];
if (!in_array($cashDelivery, $allowedCashDelivery, true)) {
    $cashDelivery = 'standard';
}

$orderTotal = 0.0;
$orderItems = [];

try {
    $conn->beginTransaction();

    $productStmt = $conn->prepare('SELECT id_product, name, price, stock FROM product WHERE id_product = :id_product LIMIT 1 FOR UPDATE');
    $updateStockStmt = $conn->prepare('UPDATE product SET stock = stock - :quantity WHERE id_product = :id_product');
    $orderStmt = $conn->prepare(
        'INSERT INTO `order` (user_id, customer_name, customer_email, customer_phone, total_price, status, created_at)
         VALUES (:user_id, :customer_name, :customer_email, :customer_phone, :total_price, :status, NOW())'
    );
    $orderItemStmt = $conn->prepare(
        'INSERT INTO order_item (id_order, id_product, quantity, price)
         VALUES (:id_order, :id_product, :quantity, :price)'
    );
    $addressStmt = $conn->prepare(
        'INSERT INTO order_address (type, street, city, zip, country, id_order)
         VALUES (:type, :street, :city, :zip, :country, :id_order)'
    );
    $paymentStmt = $conn->prepare(
        'INSERT INTO payment (id_order, payment_method, amount, status, paid_at)
         VALUES (:id_order, :payment_method, :amount, :status, NULL)'
    );
    $pointsStmt = $conn->prepare(
        'UPDATE `user`
         SET loyalty_points = COALESCE(loyalty_points, 0) + :points
         WHERE id = :user_id'
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
            throw new RuntimeException('Produkt sa nenašiel.');
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

    $orderStmt->execute([
        ':user_id' => $userId,
        ':customer_name' => $customerName,
        ':customer_email' => $customerEmail,
        ':customer_phone' => $customerPhone,
        ':total_price' => $orderTotal,
        ':status' => 'pending',
    ]);
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

    $pointsAwarded = 50;
    $pointsStmt->execute([
        ':points' => $pointsAwarded,
        ':user_id' => $userId,
    ]);

    $conn->commit();

    SessionHelper::refreshSessionPoints($conn, $userEmail);
    $_SESSION['cart'] = [];
    $_SESSION['checkout_order_id'] = $orderId;
    $_SESSION['checkout_points_awarded'] = $pointsAwarded;
    $_SESSION['checkout_success'] = 'Objednávka bola prijatá. Ďakujeme za nákup.';

    header('Location: ' . route('/thank-you'));
    exit;
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log('[checkout] ' . $e->getMessage());
    $_SESSION['checkout_error'] = 'Objednávku sa nepodarilo dokončiť. Skús to znova.';
    header('Location: ' . route('/payment'));
    exit;
}