<?php

require_once dirname(__DIR__, 3) . '/config/config.php';
require_once dirname(__DIR__) . '/session_helper.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');

if (!isset($conn) || !($conn instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Databazove spojenie nie je dostupne']);
    exit;
}

SessionHelper::bootstrap();
$sessionUser = SessionHelper::user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metoda neni povolena.']);
    exit;
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody ?: '{}', true);
if (!is_array($payload)) {
    $payload = [];
}

if (!(bool) ($sessionUser['is_logged_in'] ?? false)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Na objednávku sa musíš prihlásiť.']);
    exit;
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || $_SESSION['cart'] === []) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Košík je prázdny.']);
    exit;
}

function checkout_cart_summary(array $cart): array
{
    $count = 0;
    $total = 0.0;

    foreach ($cart as $item) {
        $quantity = (int) ($item['quantity'] ?? 0);
        $price = (float) ($item['price'] ?? 0);
        $count += $quantity;
        $total += $quantity * $price;
    }

    return [
        'count' => $count,
        'total' => round($total, 2),
    ];
}

function load_product_for_checkout(PDO $conn, int $productId): ?array
{
    $stmt = $conn->prepare('SELECT id_product AS id, name, price, stock FROM product WHERE id_product = :id LIMIT 1 FOR UPDATE');
    $stmt->execute(['id' => $productId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!is_array($row)) {
        return null;
    }

    return [
        'id' => (int) ($row['id'] ?? 0),
        'name' => (string) ($row['name'] ?? ''),
        'price' => (float) ($row['price'] ?? 0),
        'stock' => (int) ($row['stock'] ?? 0),
    ];
}

$cart = $_SESSION['cart'];
$customerName = trim((string) ($sessionUser['name'] ?? ''));
$customerEmail = trim((string) ($sessionUser['email'] ?? ''));
$customerPhone = trim((string) ($payload['customer_phone'] ?? $_POST['customer_phone'] ?? ''));
$userId = (int) ($sessionUser['id'] ?? 0);

if ($customerName === '' || $customerEmail === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'V profile chýba meno alebo email.']);
    exit;
}

try {
    $conn->beginTransaction();

    $items = [];
    $total = 0.0;

    foreach ($cart as $productId => $item) {
        $productId = (int) $productId;
        $quantity = (int) ($item['quantity'] ?? 0);

        if ($productId <= 0 || $quantity <= 0) {
            continue;
        }

        $product = load_product_for_checkout($conn, $productId);
        if ($product === null) {
            throw new RuntimeException('Jeden z produktov už nie je dostupný.');
        }

        if ((int) ($product['stock'] ?? 0) < $quantity) {
            throw new RuntimeException('Na sklade nie je dosť kusov pre: ' . $product['name']);
        }

        $stockUpdateStmt = $conn->prepare(
            'UPDATE product SET stock = stock - :quantity WHERE id_product = :id AND stock >= :quantity'
        );
        $stockUpdateStmt->execute([
            ':quantity' => $quantity,
            ':id' => $productId,
        ]);

        if ($stockUpdateStmt->rowCount() !== 1) {
            throw new RuntimeException('Na sklade nie je dosť kusov pre: ' . $product['name']);
        }

        $lineTotal = round($product['price'] * $quantity, 2);
        $total += $lineTotal;

        $items[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
        ];
    }

    if ($items === []) {
        throw new RuntimeException('Košík je prázdny.');
    }

    $orderStmt = $conn->prepare(
        'INSERT INTO `order` (customer_name, customer_email, customer_phone, total_price, status, user_id) VALUES (:customer_name, :customer_email, :customer_phone, :total_price, :status, :user_id)'
    );
    $orderStmt->execute([
        ':customer_name' => $customerName,
        ':customer_email' => $customerEmail,
        ':customer_phone' => $customerPhone !== '' ? $customerPhone : null,
        ':total_price' => $total,
        ':status' => 'pending',
        ':user_id' => $userId > 0 ? $userId : null,
    ]);

    $orderId = (int) $conn->lastInsertId();
    if ($orderId <= 0) {
        throw new RuntimeException('Objednávku sa nepodarilo uložiť.');
    }

    $orderItemStmt = $conn->prepare(
        'INSERT INTO order_item (quantity, price, id_order, id_product) VALUES (:quantity, :price, :id_order, :id_product)'
    );
    foreach ($items as $item) {
        $orderItemStmt->execute([
            ':quantity' => $item['quantity'],
            ':price' => $item['price'],
            ':id_order' => $orderId,
            ':id_product' => $item['id'],
        ]);
    }

    $conn->commit();

    $_SESSION['cart'] = [];

    echo json_encode([
        'success' => true,
        'message' => 'Objednávka bola vytvorená.',
        'order_id' => $orderId,
        'summary' => checkout_cart_summary($items),
    ]);
    exit;
} catch (Throwable $exception) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    http_response_code(409);
    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage() !== '' ? $exception->getMessage() : 'Objednávku sa nepodarilo vytvoriť.',
    ]);
    exit;
}