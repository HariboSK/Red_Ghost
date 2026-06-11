<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$productModelPath = dirname(__DIR__) . '/models/product.model.php';
if (is_file($productModelPath)) {
    require_once $productModelPath;
}

$sessionUser = SessionHelper::user();
$pdo = $conn ?? ($GLOBALS['conn'] ?? null);

if (!($sessionUser['is_logged_in'] ?? false) || (string) ($sessionUser['role'] ?? 'user') !== 'admin') {
    http_response_code(403);
    exit('Prístup zamietnutý.');
}

$dashboardError = '';
if ((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['form_type'] ?? '') === 'edit_product') {
    
    $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
    
    if ($productId && class_exists('ProductModel') && ($pdo instanceof PDO)) {
        $productModel = new ProductModel($pdo);
        $validation = ProductModel::validateAndBuildPayload($_POST);
        
        if (!$validation['ok']) {
            $_SESSION['productError'] = (string) $validation['error'];
        } else {
            try {
                $productModel->update((int) $productId, $validation['payload']);
                $_SESSION['productNotice'] = 'Produkt bol úspešne upravený.';
                // Refreshne stránku na kotvu #products, aby si hneď videl zmeny
                (new Redirect('/dashboard#products'))->redirect();
            } catch (PDOException $exception) {
                $_SESSION['productError'] = 'Produkt sa nepodarilo uložiť.';
            }
        }
    }
}

if (class_exists('ProductModel') && ($pdo instanceof PDO)) {
    $productModel = new ProductModel($pdo);
    $products = $productModel->findAll(); // alebo tvoja metóda na vytiahnutie produktov
}

?>