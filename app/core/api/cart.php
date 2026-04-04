<?php

require_once dirname(__DIR__, 3) . '/config/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');

if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Databazove spojenie nie je dostupne']);
    exit;
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Nacita jeden produkt z databazy, aby kosik pouzival aktualne data.
function get_product_by_id(mysqli $conn, int $productId): ?array
{
    $stmt = $conn->prepare('SELECT id, name, price FROM products WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!is_array($product)) {
        return null;
    }

    return [
        'id' => (int) $product['id'],
        'name' => (string) $product['name'],
        'price' => (float) $product['price'],
    ];
}

// Pred odpovedou obnovi nazvy a ceny poloziek v kosiku z databazy.
function sync_cart_prices(mysqli $conn, array &$cart): void
{
    foreach ($cart as $productId => $item) {
        $dbProduct = get_product_by_id($conn, (int) $productId);
        if ($dbProduct === null) {
            unset($cart[$productId]);
            continue;
        }

        $cart[$productId]['name'] = $dbProduct['name'];
        $cart[$productId]['price'] = $dbProduct['price'];
    }
}

// Vypocita pocet kusov a celkovu cenu aktualneho kosika.
function cart_summary(array $cart): array
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

$action = $_GET['action'] ?? 'summary';

sync_cart_prices($conn, $_SESSION['cart']);

if ($action === 'summary') {
    echo json_encode([
        'success' => true,
        'summary' => cart_summary($_SESSION['cart']),
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody ?: '{}', true);
if (!is_array($payload)) {
    $payload = [];
}

if ($action === 'add') {
    $productId = isset($payload['id']) ? (int) $payload['id'] : 0;
    if ($productId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Neplatny produkt']);
        exit;
    }

    $product = get_product_by_id($conn, $productId);
    if ($product === null) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Produkt neexistuje']);
        exit;
    }

    if (!isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = [
            'id' => $productId,
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => 0,
        ];
    }

    $_SESSION['cart'][$productId]['quantity'] += 1;

    echo json_encode([
        'success' => true,
        'message' => $product['name'] . ' bolo pridane do kosika',
        'summary' => cart_summary($_SESSION['cart']),
    ]);
    exit;
}

if ($action === 'update') {
    $productId = isset($payload['id']) ? (int) $payload['id'] : 0;
    $quantity = isset($payload['quantity']) ? (int) $payload['quantity'] : -1;

    if ($productId <= 0 || !isset($_SESSION['cart'][$productId])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Produkt v kosiku neexistuje']);
        exit;
    }

    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $product = get_product_by_id($conn, $productId);
        if ($product === null) {
            unset($_SESSION['cart'][$productId]);
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Produkt uz nie je dostupny']);
            exit;
        }

        $_SESSION['cart'][$productId]['name'] = $product['name'];
        $_SESSION['cart'][$productId]['price'] = $product['price'];
        $_SESSION['cart'][$productId]['quantity'] = $quantity;
    }

    echo json_encode([
        'success' => true,
        'summary' => cart_summary($_SESSION['cart']),
    ]);
    exit;
}

if ($action === 'clear') {
    $_SESSION['cart'] = [];
    echo json_encode([
        'success' => true,
        'summary' => cart_summary($_SESSION['cart']),
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Neznamy action']);
