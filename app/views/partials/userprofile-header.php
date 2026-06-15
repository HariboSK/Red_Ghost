<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/core/App.php';

App::init();

$resolvedPageTitle = (isset($pageTitle) && $pageTitle !== '') ? $pageTitle : Helper::getPageTitle();
?>

<!DOCTYPE html>
<html lang="sk">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Jakub Chrkavý">
    <meta name="description" content="Red Ghost User Profile">
    <title><?php echo $resolvedPageTitle; ?></title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- Custom CSS -->
    <?php foreach (AssetHelper::current_page_assets() as $css): ?>
        <link rel="stylesheet" href="<?php echo asset('css/' . ltrim($css, '/')); ?>">
    <?php endforeach; ?>
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon"
        href="/assets/images/favicon.webp">
</head>

<body>
    <!-- Header / Navbar -->
    <header>
        <nav class="navbar section-content">
            <a href="<?php echo route('/userprofile'); ?>" class="nav-logo">
                <img src="/assets/images/logo-text.webp" class="logo" alt="Red Ghost logo">
                <h2 class="logo-text">Red Ghost</h2>
            </a>

            <button id="menu-open-button" class="fas fa-bars"></button>
        </nav>
    </header>