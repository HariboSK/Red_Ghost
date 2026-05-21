<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once dirname(__DIR__, 2) . '/core/middleware/function.php';
require_once dirname(__DIR__, 2) . '/core/helper.php';
require_once dirname(__DIR__, 2) . '/core/assetHelper.php';

$resolvedPageTitle = isset($pageTitle) && is_string($pageTitle) && $pageTitle !== ''
  ? $pageTitle
  : Helper::getPageTitle() . ' - E-shop';

require_once dirname(__DIR__, 2) . '/core/session_helper.php';
SessionHelper::bootstrap();

$sessionUser = SessionHelper::user();

$profileEmail = (string) ($sessionUser['email'] ?? '');
$profileName = (string) ($sessionUser['name'] ?? '');
$isLoggedIn = (bool) ($sessionUser['is_logged_in'] ?? false);
$profilePoints = (int) ($sessionUser['points'] ?? 0);
$userRole = (string) ($sessionUser['role'] ?? 'guest');

if ($isLoggedIn && $profileEmail !== '' && isset($conn) && $conn instanceof PDO) {
  $profilePoints = SessionHelper::refreshSessionPoints($conn, $profileEmail);
}

if ($isLoggedIn && $userRole === 'admin') {
  $profileHref = route('/dashboard');
} elseif ($isLoggedIn) {
  $profileHref = route('/userprofile');
} else {
  $profileHref = route('/login');
}

?>


<!DOCTYPE html>
<html lang="sk">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="author" content="Jakub Chrkavý">
  <meta name="description"
    content="Objav našu ponuku chilli papričiek – klikni a ochutnaj pálivú vášeň zo slovenských záhrad! Vyber si svoju pálivosť – od jemného Jalapena po extrémne Carolina Reaper!">
  <title><?php echo htmlspecialchars(Helper::getPageTitle(), ENT_QUOTES, 'UTF-8'); ?></title>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
  <!-- Custom CSS -->
  <?php foreach (AssetHelper::current_page_assets() as $css): ?>
     <link rel="stylesheet" href="<?php echo asset('css/' . ltrim($css, '/')); ?>">
  <?php endforeach; ?>
  <!-- Favicon -->
  <link rel="shortcut icon" type="image/x-icon"
    href="/assets/images/favicon.webp">
</head>

<body<?php echo isset($bodyClass) && is_string($bodyClass) && $bodyClass !== '' ? ' class="' . htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
  <!-- Header / Navbar -->
  <header>
    <nav class="navbar section-content">
      <a href="<?php echo route('/home'); ?>" class="nav-logo">
        <img src="/assets/images/logo-text.webp" class="logo" alt="Red Ghost logo">
        <h2 class="logo-text">Red Ghost</h2>
      </a>
      <ul class="nav-menu">
        <button id="menu-close-button" class="fas fa-times"></button>
        <li class="nav-item">
          <a href="<?php echo route('/home'); ?>" class="nav-link">Domov</a>
        </li>
        <li class="nav-item">
          <a href="<?php echo route('/home#about'); ?>" class="nav-link">O nás</a>
        </li>
        <li class="nav-item">
          <a href="<?php echo route('/home#menu'); ?>" class="nav-link">Produkty</a>
        </li>
        <li class="nav-item">
          <a href="<?php echo route('/home#testimonials'); ?>" class="nav-link">Recenzie</a>
        </li>
        <li class="nav-item">
          <a href="<?php echo route('/home#contact'); ?>" class="nav-link">Kontakt</a>
        </li>
        <li class="nav-item">
          <a href="<?php echo route('/e-shop'); ?>" class="nav-link">E-shop</a>
        </li>
        <?php if ($isLoggedIn): ?>
          <li class="nav-item nav-login-mobile">
            <a href="<?php echo htmlspecialchars($userRole === 'admin' ? htmlspecialchars(route('/dashboard')) : htmlspecialchars(route('/userprofile')), ENT_QUOTES, 'UTF-8'); ?>" class="nav-link nav-login-mobile-link">
              <img src="/assets/icons/user-svgrepo-com.svg" alt="Log-in icon" class="nav-login-icon">
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item nav-login-mobile">
            <a href="<?php echo htmlspecialchars(route('/login'), ENT_QUOTES, 'UTF-8'); ?>" class="nav-link nav-login-mobile-link">
              <img src="/assets/icons/user-svgrepo-com.svg" alt="Log-in icon" class="nav-login-icon">
            </a>
          </li>
        <?php endif; ?>
      </ul>

      <?php if ($isLoggedIn): ?>
        <a href="<?php echo htmlspecialchars($userRole === 'admin' ? htmlspecialchars(route('/dashboard')) : htmlspecialchars(route('/userprofile')), ENT_QUOTES, 'UTF-8'); ?>" class="nav-login">
          <img src="/assets/icons/user-svgrepo-com.svg" alt="Log-in icon" class="nav-login-icon">
        </a>
      <?php else: ?>
        <a href="<?php echo htmlspecialchars(route('/login'), ENT_QUOTES, 'UTF-8'); ?>" class="nav-login">
          <img src="/assets/icons/user-svgrepo-com.svg" alt="Log-in icon" class="nav-login-icon">
        </a>
      <?php endif; ?>
      
      <button id="menu-open-button" class="fas fa-bars"></button>
    </nav>
  </header>