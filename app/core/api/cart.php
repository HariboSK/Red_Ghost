<?php

require_once dirname(__DIR__, 3) . '/config/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');

if (!isset($conn) || !($conn instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Databazove spojenie nie je dostupne']);
    exit;
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function product_stock(PDO $conn, int $productId): int
{
    $stmt = $conn->prepare('SELECT stock FROM product WHERE id_product = :id LIMIT 1');
    $stmt->execute(['id' => $productId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!is_array($row)) {
        return -1;
    }

    return (int) ($row['stock'] ?? 0);
}

function product_has_stock(PDO $conn, int $productId, int $requestedQuantity): bool
{
    if ($requestedQuantity <= 0) {
        return false;
    }

    return product_stock($conn, $productId) >= $requestedQuantity;
}

function cart_quantity_for_product(array $cart, int $productId): int
{
    return (int) ($cart[$productId]['quantity'] ?? 0);
}

function normalize_image_path(string $image): string
{
    $image = trim($image);

    if ($image === '') {
        return '/assets/images/omacka3.webp';
    }

    if (preg_match('~^(https?:)?//~i', $image) === 1 || strpos($image, '/') === 0) {
        return preg_replace('~\.(jpe?g)$~i', '.webp', $image);
    }

    return preg_replace('~\.(jpe?g)$~i', '.webp', '/assets/images/' . ltrim($image, '/'));
}

// Nacita jeden produkt z databazy, aby kosik pouzival aktualne data.
function get_product_by_id(PDO $conn, int $productId): ?array
{   
    //stmt skratka pre statement, teda pripravený SQL dopyt
    $stmt = $conn->prepare('SELECT id_product AS id, name, price, image, stock FROM product WHERE id_product = :id LIMIT 1');
    $stmt->execute(['id' => $productId]);
    $product = $stmt->fetch();

    if (!is_array($product)) {
        return null;
    }

    return [
        'id' => (int) $product['id'],
        'name' => (string) $product['name'],
        'price' => (float) $product['price'],
        'stock' => (float) $product['stock'],
        'image' => (string) $product['image'],
    ];
}

// Pred odpovedou obnovi nazvy a ceny poloziek v kosiku z databazy.
function sync_cart_prices(PDO $conn, array &$cart): void
{
    foreach ($cart as $productId => $item) {
        $dbProduct = get_product_by_id($conn, (int) $productId);
        if ($dbProduct === null) {
            unset($cart[$productId]);
            continue;
        }

        $cart[$productId]['name'] = $dbProduct['name'];
        $cart[$productId]['price'] = $dbProduct['price'];
        $cart[$productId]['image'] = normalize_image_path((string) ($dbProduct['image'] ?? ''));

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

// kontrola GET a POST metod pre rozdielne akcie, summary a list su GET, add, update a clear su POST
$allowedActions = ['summary', 'list', 'add', 'update', 'clear', 'remove'];

if (!in_array($action, $allowedActions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Neznama akcia']);
    exit;
}

$getActions = ['summary', 'list'];

// Načítaj JSON payload pre POST requesty
$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody ?: '{}', true);
if (!is_array($payload)) {
    $payload = [];
}

if (in_array($action, $getActions)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Metoda neni povolena']);
        exit;
    }
} else {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Metoda neni povolena']);
        exit;
    }
}

if ($action === 'list') {
    $cartList = [];
    foreach ($_SESSION['cart'] as $productId => $item) {
        $product = get_product_by_id($conn, (int) $productId);
        if ($product === null) {
            unset($_SESSION['cart'][$productId]);
            continue;
        }

        $cartList[] = [
            'id' => (int) $productId,
            'name' => (string) ($item['name'] ?? $product['name']),
            'price' => (float) ($item['price'] ?? $product['price']),
            'quantity' => (int) ($item['quantity'] ?? 0),
            'image' => normalize_image_path((string) ($product['image'] ?? '')),
        ];
    }

    echo json_encode([
        'success' => true,
        'items' => $cartList,
    ]);
    exit;
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

    $currentQuantity = cart_quantity_for_product($_SESSION['cart'], $productId);
    $requestedQuantity = $currentQuantity + 1;

    if (!product_has_stock($conn, $productId, $requestedQuantity)) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Na sklade nie je dosť kusov.']);
        exit;
    }

    if (!isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = [
            'id' => $productId,
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => 0,
            'image' => normalize_image_path((string) ($product['image'] ?? '')),
        ];
    }

    $_SESSION['cart'][$productId]['quantity'] += 1;
    $_SESSION['cart'][$productId]['name'] = $product['name'];
    $_SESSION['cart'][$productId]['price'] = $product['price'];
    $_SESSION['cart'][$productId]['image'] = normalize_image_path((string) ($product['image'] ?? ''));

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

    $currentQuantity = (int) ($_SESSION['cart'][$productId]['quantity'] ?? 0);
    $product = get_product_by_id($conn, $productId);
    if ($product === null) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Produkt uz nie je dostupny']);
        exit;
    }

    if ($quantity > $currentQuantity && !product_has_stock($conn, $productId, $quantity)) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Požadované množstvo presahuje sklad.']);
        exit;
    }

    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId]['name'] = $product['name'];
        $_SESSION['cart'][$productId]['price'] = $product['price'];
        $_SESSION['cart'][$productId]['quantity'] = $quantity;
        $_SESSION['cart'][$productId]['image'] = normalize_image_path((string) ($product['image'] ?? ''));
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

if ($action === 'remove') {
    $productId = isset($payload['id']) ? (int) $payload['id'] : 0;

    if ($productId <= 0 || !isset($_SESSION['cart'][$productId])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Produkt v košiku neexistuje']);
        exit;
    }

    $currentQuantity = (int) ($_SESSION['cart'][$productId]['quantity'] ?? 0);
    if ($currentQuantity <= 0) {
        unset($_SESSION['cart'][$productId]);
        echo json_encode([
            'success' => true,
            'message' => 'Produkt bol aktualizovaný',
            'summary' => cart_summary($_SESSION['cart']),
        ]);
        exit;
    }

    $_SESSION['cart'][$productId]['quantity'] -= 1;

    if ($_SESSION['cart'][$productId]['quantity'] <= 0) {
        unset($_SESSION['cart'][$productId]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Produkt bol aktualizovaný',
        'summary' => cart_summary($_SESSION['cart']),
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Neznamy action']);
