<?php
declare(strict_types=1);

require_once dirname(__DIR__, 1) . '/App.php';
App::init();

// --- ZÁKLADNÉ BEZPEČNOSTNÉ KONTROLY ---
$returnTo = $_POST['return_to'] ?? '/shopcart';

// Kontrola CSRF tokenu
$csrfToken = $_POST['csrf_token'] ?? null;
if (!class_exists('SessionHelper') || !SessionHelper::verifyCsrfToken($csrfToken)) {
    set_flash('error', 'Neplatná požiadavka (CSRF útok).');
    header('Location: ' . $returnTo);
    exit;
}

// Kontrola metódy
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $returnTo);
    exit;
}

// Validácia DB pripojenia
$conn = $GLOBALS['conn'] ?? null;
$dbAvailable = ($conn instanceof PDO);

// --- VSTUPY ---
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$action = isset($_POST['action']) ? (string)$_POST['action'] : 'decrement';
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

// Ošetrenie otvoreného presmerovania (Open Redirect)
$path = parse_url($returnTo, PHP_URL_PATH);
$returnTo = ($path && strpos($path, '/') === 0) ? $returnTo : '/shopcart';

// Inicializácia session
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Pomocná funkcia pre kontrolu skladu
function ProductExistsAndStock(PDO $conn, int $id): int {
    $stmt = $conn->prepare('SELECT stock FROM product WHERE id_product = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? (int)($row['stock'] ?? 0) : 0;
}

// --- LOGIKA ---
$successMessage = 'Košík bol aktualizovaný.';

switch ($action) {
    case 'decrement':
        if ($id > 0 && isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] = max(0, (int)$_SESSION['cart'][$id]['quantity'] - 1);
            if ($_SESSION['cart'][$id]['quantity'] <= 0) {
                unset($_SESSION['cart'][$id]);
            }
            $successMessage = 'Počet kusov bol znížený.';
        }
        break;

    case 'increment':
        if ($id > 0) {
            $stock = $dbAvailable ? ProductExistsAndStock($conn, $id) : 9999; // Fallback ak nie je DB
            $current = isset($_SESSION['cart'][$id]) ? (int)$_SESSION['cart'][$id]['quantity'] : 0;
            
            if ($current < $stock) {
                if (!isset($_SESSION['cart'][$id])) {
                    $_SESSION['cart'][$id] = ['id' => $id, 'quantity' => 1];
                } else {
                    $_SESSION['cart'][$id]['quantity'] = $current + 1;
                }
                $successMessage = 'Počet kusov bol zvýšený.';
            } else {
                set_flash('error', 'Produkt už nie je skladom v takom množstve.');
                header('Location: ' . $returnTo);
                exit;
            }
        }
        break;

    case 'set':
        if ($id > 0) {
            $q = max(0, $quantity);
            $stock = $dbAvailable ? ProductExistsAndStock($conn, $id) : 9999;
            $finalQty = ($q > $stock) ? $stock : $q;

            if ($finalQty <= 0) {
                unset($_SESSION['cart'][$id]);
                $successMessage = 'Produkt bol odstránený.';
            } else {
                $_SESSION['cart'][$id] = ['id' => $id, 'quantity' => $finalQty];
                $successMessage = 'Množstvo bolo nastavené.';
            }
        }
        break;

    case 'remove':
        if ($id > 0 && isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
            $successMessage = 'Produkt bol odstránený z košíka.';
        }
        break;

    case 'clear':
        $_SESSION['cart'] = [];
        $successMessage = 'Košík bol vyprázdnený.';
        break;

    default:
        set_flash('error', 'Neplatná akcia.');
        header('Location: ' . $returnTo);
        exit;
}

// --- DOKONČENIE ---
set_flash('success', $successMessage);
header('Location: ' . $returnTo);
exit;