<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 1) . '/core/ShopService.php';

$conn = (isset($conn) && $conn instanceof PDO)
	? $conn
	: ((isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof PDO) ? $GLOBALS['conn'] : null);


include __DIR__ . '/partials/header-shop.php';

// Ensure we pass a valid PDO instance to ShopService.
// `config.php` may have been required earlier (inside App::init()), so $conn
// may not be defined in this file scope. Use $GLOBALS['conn'] as fallback.

$shopData = ShopService::collectProducts($conn);
ShopService::renderMain($shopData);

include __DIR__ . '/partials/footer-shop.php';
?>