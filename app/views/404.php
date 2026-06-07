<?php
require_once dirname(__DIR__) . '/core/middleware/Function.php';
require_once dirname(__DIR__) . '/core/AssetHelper.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chyba 404</title>

    <!-- Custom CSS -->
    <?php foreach (AssetHelper::current_page_assets() as $css): ?>
        <link rel="stylesheet" href="<?php echo asset('css/' . ltrim($css, '/')); ?>">
    <?php endforeach; ?>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon"
    href="/assets/images/favicon.webp">

</head>

<body>
    <div class="error-container">
        <h1>404 - Stránka sa nenašla</h1>
        <p>Ups, stránka ktorú hľadáš neexistuje alebo bola presunutá.</p>
        <p>Prosím skuste to neskôr.</p>

        <a href="<?php echo route('/home'); ?>" class="home-link">Späť na domovskú stránku</a>
        <a href="<?php echo route('/e_shop'); ?>" class="shop-link">Späť do E-shopu</a>

    </div>


    <!-- Footer section -->
    <?php require __DIR__ . '/partials/footer.php'; ?>