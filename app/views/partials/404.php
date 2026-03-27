<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chyba 404</title>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/errors.css">
    <link rel="stylesheet" href="/assets/css/style.css">
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
    <?php require __DIR__ . '/footer.php'; ?>