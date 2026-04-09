<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 1) . '/core/shopService.php';
include __DIR__ . '/partials/header-shop.php';

$shopData = ShopService::collectProducts($conn ?? null);
ShopService::renderMain($shopData);

include __DIR__ . '/partials/footer-shop.php';
?>