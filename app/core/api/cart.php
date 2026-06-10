<?php
declare(strict_types=1);

require_once dirname(__DIR__, 1) . '/App.php'; 
App::init();

// Nastavenie hlavičky pre čistú JSON odpoveď
header('Content-Type: application/json; charset=UTF-8');

// Bezpečnostná poistka pre databázové pripojenie
if (!isset($conn) || !($conn instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Databazove spojenie nie je dostupne']);
    exit;
}

// Inicializácia košíka v session, ak neexistuje
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// --- POMOCNÉ FUNKCIE ---

function NormalizeImagePath(string $image): string
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

function GetProductById(PDO $conn, int $productId): ?array
{   
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

function SyncCartPrices(PDO $conn, array &$cart): void
{
    foreach ($cart as $productId => $item) {
        $dbProduct = GetProductById($conn, (int) $productId);
        if ($dbProduct === null) {
            unset($cart[$productId]);
            continue;
        }

        $cart[$productId]['name'] = $dbProduct['name'];
        $cart[$productId]['price'] = $dbProduct['price'];
        $cart[$productId]['image'] = NormalizeImagePath((string) ($dbProduct['image'] ?? ''));
    }
}

function CartSummary(array $cart): array
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

// --- LOGIKA AKCIÍ ---

$action = $_GET['action'] ?? 'summary';

// Synchronizácia košíka s aktuálnymi cenami v DB pri každom dopyte
SyncCartPrices($conn, $_SESSION['cart']);

// Akcia 1: Rýchly sumár pre počítadlo v hlavičke webu
if ($action === 'summary') {
    echo json_encode([
        'success' => true,
        'summary' => CartSummary($_SESSION['cart']),
    ]);
    exit;
}

// Akcia 2: Kompletný zoznam položiek pre vyskakovacie okno (Hover) košíka
if ($action === 'list') {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Metoda neni povolena']);
        exit;
    }

    $cartList = [];
    foreach ($_SESSION['cart'] as $productId => $item) {
        $product = GetProductById($conn, (int) $productId);
        if ($product === null) {
            unset($_SESSION['cart'][$productId]);
            continue;
        }

        $cartList[] = [
            'id' => (int) $productId,
            'name' => (string) ($item['name'] ?? $product['name']),
            'price' => (float) ($item['price'] ?? $product['price']),
            'quantity' => (int) ($item['quantity'] ?? 0),
            'image' => NormalizeImagePath((string) ($product['image'] ?? '')),
        ];
    }

    echo json_encode([
        'success' => true,
        'items' => $cartList,
    ]);
    exit;
}

// Akcia 3: Vyčistenie košíka (napr. po objednávke)
if ($action === 'clear') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Metoda neni povolena']);
        exit;
    }

    $_SESSION['cart'] = [];
    echo json_encode([
        'success' => true,
        'summary' => CartSummary($_SESSION['cart']),
    ]);
    exit;
}

// Ak sa požiadavka netrafila do žiadnej povolenej akcie
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Neznamy alebo nepovoleny action']);