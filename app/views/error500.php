<?php
require_once dirname(__DIR__) . '/core/middleware/function.php';
require_once dirname(__DIR__) . '/core/assetHelper.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chyba Aplikacie</title>

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
        <h1>500 - Chyba Aplikacie</h1>
        <p><?php echo isset($errorMessage) ? nl2br(htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8')) : 'Ospravedlňujeme sa, ale došlo k neočakávanej chybe. Naša technická podpora bola informovana a pracuje na jej odstránení.'; ?></p>
        <p>Prosím skuste to neskôr.</p>

        <a href="<?php echo route('/home'); ?>" class="home-link">Späť na domovskú stránku</a>
        <a href="<?php echo route('/e_shop'); ?>" class="shop-link">Späť do E-shopu</a>

    </div>

    <!-- Footer section -->
    <?php require __DIR__ . '/partials/footer.php'; ?>