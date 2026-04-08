<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 1) . '/core/shop_functions.php';
include __DIR__ . '/partials/header-shop.php';

$assetBase = asset_base();
$shopData = shop_collect_products($conn ?? null);
shop_render_main($shopData);

include __DIR__ . '/partials/footer-shop.php';
?>