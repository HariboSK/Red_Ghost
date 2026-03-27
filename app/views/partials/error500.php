<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chyba Aplikacie</title>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/errors.css">
    <link rel="stylesheet" href="/assets/css/style.css">

     <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="./assets/images/305036798_501410268657650_7493754093765322046_n-modified.png">

</head>
<body>
    <div class="error-container">
        <h1>500 - Chyba Aplikacie</h1>
        <p>Ospravedlňujeme sa, ale došlo k neočakávanej chybe. Naša technická podpora bola informována a pracuje na jej odstránení.</p>
        <p>Prosím skuste to neskôr.</p>

        <a href="<?php echo route('/home'); ?>" class="home-link">Späť na domovskú stránku</a>
        <a href="<?php echo route('/e_shop'); ?>" class="shop-link">Späť do E-shopu</a>

    </div>

    <!-- Footer section -->
    <?php require __DIR__ . '/footer.php'; ?>