<?php
// Server-side handler pre upravu/mazanie poloziek v kosiku cez POST formular
// Podpora akcii: decrement, increment, set, remove, clear

// Load config (DB connection etc.)
require_once __DIR__ . '/../../../config/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Akceptujeme iba POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $back = $_POST['return_to'] ?? '/shopcart';
    header('Location: ' . $back);
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$action = isset($_POST['action']) ? (string) $_POST['action'] : 'decrement';
$quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : null;
$returnTo = $_POST['return_to'] ?? '/shopcart';


if (!is_string($returnTo) || strpos($returnTo, '/') !== 0) {
    $returnTo = '/shopcart';
} else {
    $u = parse_url($returnTo);
    if ($u === false || isset($u['scheme']) || isset($u['host'])) {
        $returnTo = '/shopcart';
    }
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Volitelne: overit produkt v DB ak je $conn dostupne
function product_exists_and_stock($conn, $id) {
    if (!($conn instanceof PDO)) {
        return null;
    }
    $stmt = $conn->prepare('SELECT id_product AS id, stock 
                            FROM product 
                            WHERE id_product = :id 
                            LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!is_array($row)) return null;
    return (int) ($row['stock'] ?? 0);
}

// Spracovanie akcii
switch ($action) {
    case 'decrement':
        if ($id > 0 && isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] = max(0, (int) ($_SESSION['cart'][$id]['quantity'] ?? 0) - 1);
            if ($_SESSION['cart'][$id]['quantity'] <= 0) {
                unset($_SESSION['cart'][$id]);
            }
        }
        break;

    case 'increment':
        if ($id > 0) {
            $stock = isset($conn) && ($conn instanceof PDO) ? product_exists_and_stock($conn, $id) : null;
            $current = isset($_SESSION['cart'][$id]) ? (int) ($_SESSION['cart'][$id]['quantity'] ?? 0) : 0;
            $requested = $current + 1;
            if ($stock !== null && $requested > $stock) {
                // prekrocenie skladu -> ignorovat alebo nastavit na max
                if ($stock > 0) {
                    $_SESSION['cart'][$id]['quantity'] = $stock;
                }
            } else {
                if (!isset($_SESSION['cart'][$id])) {
                    $_SESSION['cart'][$id] = ['id' => $id, 'name' => '', 'price' => 0, 'quantity' => 1];
                } else {
                    $_SESSION['cart'][$id]['quantity'] = $requested;
                }
            }
        }
        break;

    case 'set':
        if ($id > 0) {
            $q = $quantity === null ? 0 : max(0, $quantity);
            if ($q <= 0) {
                unset($_SESSION['cart'][$id]);
            } else {
                $stock = isset($conn) && ($conn instanceof PDO) ? product_exists_and_stock($conn, $id) : null;
                if ($stock !== null && $q > $stock) {
                    $q = $stock;
                }
                if (!isset($_SESSION['cart'][$id])) {
                    $_SESSION['cart'][$id] = ['id' => $id, 'name' => '', 'price' => 0, 'quantity' => $q];
                } else {
                    $_SESSION['cart'][$id]['quantity'] = $q;
                }
            }
        }
        break;

    case 'remove':
        if ($id > 0 && isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }
        break;

    case 'clear':
        $_SESSION['cart'] = [];
        break;

    default:
        // neznama akcia -> nic
        break;
}

// Volitelne: pridat flash spravu
// $_SESSION['flash'] = 'Košík bol aktualizovaný.';

header('Location: ' . $returnTo);
exit;
