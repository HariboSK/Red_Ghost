<?php
require_once dirname(__DIR__, 3) . '/config/config.php';
require_once dirname(__DIR__) . '/SessionHelper.php';
require_once dirname(__DIR__, 3) . '/app/models/DiscountCodeModel.php';

SessionHelper::bootstrap();

$pdo = $conn ?? ($GLOBALS['conn'] ?? null);

// Prefer explicit return_to from POST, then Referer, then default
$returnTo = trim((string) ($_POST['return_to'] ?? ($_SERVER['HTTP_REFERER'] ?? '/shopcart')));

$code = trim((string) ($_POST['code'] ?? ''));
if (strlen($code) > 64) {
    $code = substr($code, 0, 64);
}

if ($code === '') {
    $_SESSION['discount_flash'] = ['type' => 'error', 'message' => 'Zadaj prosím platný zľavový kód.', 'amount' => 0];
    header('Location: ' . $returnTo);
    exit;
}

if (!($pdo instanceof PDO)) {
    $_SESSION['discount_flash'] = ['type' => 'error', 'message' => 'Databázové pripojenie nie je dostupné.', 'amount' => 0];
    header('Location: ' . $returnTo);
    exit;
}

// Find the discount code with a case-insensitive match (DB-side)
$stmt = $pdo->prepare('SELECT * FROM discount_code WHERE LOWER(code) = LOWER(:code) LIMIT 1');
$stmt->execute([':code' => $code]);
$found = $stmt->fetch(PDO::FETCH_ASSOC);

if (!is_array($found)) {
    $_SESSION['discount_flash'] = ['type' => 'error', 'message' => 'Kód nebol nájdený.', 'amount' => 0];
    header('Location: ' . $returnTo);
    exit;
}

// basic validation: active + min order
$isActive = (bool) ($found['is_active'] ?? false);
$minOrder = (float) ($found['min_order_value'] ?? 0);

$cartSubtotal = 0.0;
foreach ($_SESSION['cart'] ?? [] as $id => $item) {
    $qty = max(0, (int) ($item['quantity'] ?? 0));
    $price = (float) ($item['price'] ?? 0);
    $cartSubtotal += $qty * $price;
}

if (!$isActive) {
    $_SESSION['discount_flash'] = ['type' => 'error', 'message' => 'Tento kupón nie je aktívny.', 'amount' => 0];
    header('Location: ' . $returnTo);
    exit;
}

if ($cartSubtotal < $minOrder) {
    $_SESSION['discount_flash'] = ['type' => 'error', 'message' => 'Objednávka nespĺňa minimálnu hodnotu pre tento kupón.', 'amount' => 0];
    header('Location: ' . $returnTo);
    exit;
}

$discountValue = 0.0;
if (($found['discount_type'] ?? '') === 'percent') {
    $percent = (float) ($found['value'] ?? 0);
    $discountValue = round($cartSubtotal * ($percent / 100), 2);
} else {
    $discountValue = (float) ($found['value'] ?? 0);
}

// ensure discount never exceeds subtotal
$discountValue = min($discountValue, $cartSubtotal);

$_SESSION['applied_discount_code'] = (string) ($found['code'] ?? '');
$_SESSION['applied_discount_amount'] = $discountValue;
$_SESSION['discount_flash'] = ['type' => 'success', 'message' => 'Zľavový kód bol aplikovaný.', 'amount' => $discountValue];

header('Location: ' . $returnTo);
exit;
