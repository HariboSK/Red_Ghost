<?php
declare(strict_types=1);

// Load application config (project root `config/config.php`).
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../Flash.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Minimal DB connection from config
if (!isset($conn) || !($conn instanceof PDO)) {
    // Can't proceed; redirect back with error param if provided
    $back = $_POST['return_to'] ?? '/shop';
    set_flash('error', 'Košík sa nepodarilo aktualizovať.');
    header('Location: ' . $back);
    exit;
}

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

$productId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$returnTo = $_POST['return_to'] ?? '/shop';

if ($productId <= 0) {
    set_flash('error', 'Neplatný produkt.');
    header('Location: ' . $returnTo);
    exit;
}

// check product exists and stock
$stmt = $conn->prepare('SELECT id_product AS id, name, price, stock, image FROM product WHERE id_product = :id LIMIT 1');
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!is_array($product)) {
    set_flash('error', 'Produkt sa nenašiel.');
    header('Location: ' . $returnTo);
    exit;
}

$stock = (int) ($product['stock'] ?? 0);

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$currentQty = isset($_SESSION['cart'][$productId]['quantity']) ? (int) $_SESSION['cart'][$productId]['quantity'] : 0;
$requested = $currentQty + 1;

if ($requested > $stock) {
    // not enough stock; redirect back
    set_flash('error', 'Na sklade nie je dostatok kusov.');
    header('Location: ' . $returnTo);
    exit;
}

if (!isset($_SESSION['cart'][$productId])) {
    $_SESSION['cart'][$productId] = [
        'id' => $productId,
        'name' => $product['name'],
        'price' => (float) $product['price'],
        'quantity' => 0,
        'image' => NormalizeImagePath((string) ($product['image'] ?? '')),
    ];
}

$_SESSION['cart'][$productId]['quantity'] += 1;
// ensure latest name/price
$_SESSION['cart'][$productId]['name'] = $product['name'];
$_SESSION['cart'][$productId]['price'] = (float) $product['price'];
$_SESSION['cart'][$productId]['image'] = NormalizeImagePath((string) ($product['image'] ?? ''));

set_flash('success', 'Produkt bol pridaný do košíka.');

header('Location: ' . $returnTo);
exit;
