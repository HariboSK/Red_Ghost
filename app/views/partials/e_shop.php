<?php
require_once dirname(__DIR__, 3) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/core/shop_functions.php';
include __DIR__ . '/header-shop.php';

$assetBase = '/assets';
$shopData = shop_collect_products($conn ?? null, $assetBase);
shop_render_main($shopData);

include __DIR__ . '/footer-shop.php';
?>