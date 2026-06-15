<?php
declare(strict_types=1);

require_once dirname(__DIR__, 1) . '/App.php'; 
App::init();

$returnTo = trim((string) ($_POST['return_to'] ?? ($_SERVER['HTTP_REFERER'] ?? '/shopcart')));

//KONTROLA CSRF TOKENU
$csrfToken = $_POST['csrf_token'] ?? null;
if (!SessionHelper::verifyCsrfToken($csrfToken)) {
    $_SESSION['discount_flash'] = ['type' => 'error', 'message' => 'Neplatná požiadavka (CSRF útok).', 'amount' => 0];
    header('Location: ' . $returnTo);
    exit;
}

$code = trim((string) ($_POST['code'] ?? ''));
if (strlen($code) > 64) {
    $code = substr($code, 0, 64);
}

if ($code === '') {
    $_SESSION['discount_flash'] = ['type' => 'error', 'message' => 'Zadaj prosím platný zľavový kód.', 'amount' => 0];
    header('Location: ' . $returnTo);
    exit;
}

//NOVÁ KONTROLA: Či už nie je tento kód aplikovaný v košíku práve teraz
$alreadyApplied = $_SESSION['applied_discount_code'] ?? '';
if ($alreadyApplied !== '' && strcasecmp($alreadyApplied, $code) === 0) {
    $_SESSION['discount_flash'] = ['type' => 'error', 'message' => 'Tento zľavový kód už máte v košíku uplatnený.', 'amount' => 0];
    header('Location: ' . $returnTo);
    exit;
}

//databázové pripojenie
if (!isset($conn) || !($conn instanceof PDO)) {
    $_SESSION['discount_flash'] = ['type' => 'error', 'message' => 'Databázové pripojenie nie je dostupné.', 'amount' => 0];
    header('Location: ' . $returnTo);
    exit;
}

$sessionUser = class_exists('SessionHelper') ? SessionHelper::user() : ($_SESSION['user'] ?? []);
$userId = (isset($sessionUser['id']) && (int)$sessionUser['id'] > 0) ? (int)$sessionUser['id'] : null;

if ($userId === null) {
    $_SESSION['discount_flash'] = ['type' => 'error', 'message' => 'Pre použitie zľavového kódu sa musíte najskôr prihlásiť.', 'amount' => 0];
    header('Location: ' . $returnTo);
    exit;
}

$discountModel = new DiscountCodeModel($conn);
$found = $discountModel->findByCodeAndUser($code, $userId); 

if (!$found) {
    $_SESSION['discount_flash'] = ['type' => 'error', 'message' => 'Kód nebol nájdený.', 'amount' => 0];
    header('Location: ' . $returnTo);
    exit;
}

//Či kód nebol použitý v minulosti
if ((int)($found['already_redeemed'] ?? 0) > 0) {
    $_SESSION['discount_flash'] = ['type' => 'error', 'message' => 'Tento zľavový kód ste už v minulosti využili.', 'amount' => 0];
    header('Location: ' . $returnTo);
    exit;
}

$idDiscountCode = (int) ($found['id_discount_code'] ?? 0);

//aktívny + minimálna hodnota objednávky
$isActive = (bool) ($found['is_active'] ?? false);
$minOrder = (float) ($found['min_order_value'] ?? 0);

// Výpočet medzisúčtu košíka zo Session
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

$discountValue = min($discountValue, $cartSubtotal);

$_SESSION['applied_discount_code'] = (string) ($found['code'] ?? '');
$_SESSION['applied_discount_code_id'] = $idDiscountCode;
$_SESSION['applied_discount_amount'] = $discountValue;
$_SESSION['discount_flash'] = ['type' => 'success', 'message' => 'Zľavový kód bol aplikovaný.', 'amount' => $discountValue];

header('Location: ' . $returnTo);
exit;