<?php
$conn = (isset($conn) && $conn instanceof PDO)
    ? $conn
    : ((isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof PDO) ? $GLOBALS['conn'] : null);

include __DIR__ . '/partials/header-shop.php';

$shopData = ShopService::collectProducts($conn);
ShopService::renderMain($shopData);

include __DIR__ . '/partials/footer-shop.php';
?>