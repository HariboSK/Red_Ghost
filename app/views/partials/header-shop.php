<?php
require_once dirname(__DIR__, 2) . '/core/middleware/function.php';
require_once dirname(__DIR__, 2) . '/core/helper.php';

$resolvedPageTitle = isset($pageTitle) && is_string($pageTitle) && $pageTitle !== ''
  ? $pageTitle
  : Helper::getPageTitle() . ' - E-shop';
$resolvedExtraStyles = (isset($extraStyles) && is_array($extraStyles)) ? $extraStyles : [];

require_once dirname(__DIR__, 2) . '/core/session_helper.php';
rg_session_bootstrap();

$sessionUser = rg_session_user();

$profileEmail = (string) ($sessionUser['email'] ?? '');
$profileName = (string) ($sessionUser['name'] ?? '');
$isLoggedIn = (bool) ($sessionUser['is_logged_in'] ?? false);
$profilePoints = (int) ($sessionUser['points'] ?? 0);

if ($isLoggedIn && $profileEmail !== '' && isset($conn) && $conn instanceof PDO) {
  $profilePoints = rg_refresh_session_points($conn, $profileEmail);
}

$profileHref = $isLoggedIn ? route('/userprofile') : route('/login');

?>

<!DOCTYPE html>
<html lang="sk">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="author" content="Jakub Chrkavy">
  <meta name="description"
    content="Objav nasu ponuku chilli papriciek - klikni a ochutnaj palivu vasen zo slovenskych zahrad! Vyber si svoju palivost - od jemneho Jalapena po extremne Carolina Reaper!">
  <title><?php echo htmlspecialchars($resolvedPageTitle, ENT_QUOTES, 'UTF-8'); ?></title>

  <!-- CSS shopcart -->
  <link rel="stylesheet" href="<?php echo htmlspecialchars(asset('css/shopcart.css'), ENT_QUOTES, 'UTF-8'); ?>">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?php echo htmlspecialchars(asset('css/style2.css'), ENT_QUOTES, 'UTF-8'); ?>">


  <?php foreach ($resolvedExtraStyles as $stylePath): ?>
    <?php if (is_string($stylePath) && $stylePath !== ''): ?>
      <link rel="stylesheet" href="<?php echo htmlspecialchars($stylePath, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
  <?php endforeach; ?>


  <!-- Favicon -->
  <link rel="shortcut icon" type="image/x-icon"
    href="<?php echo htmlspecialchars(asset('images/favicon.webp'), ENT_QUOTES, 'UTF-8'); ?>">
</head>

<body class="<?php echo isset($bodyClass) ? htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') : ''; ?>"
  data-cart-api="<?php echo route('/api/cart.php'); ?>">
  <!-- Header pre e-shop -->
  <header class="posun">
    <div class="container">
      <div class="navbar">
        <a href="<?php echo route('/home'); ?>" class="nav-logo">
          <img src="<?php echo htmlspecialchars(asset('images/logo-text.webp'), ENT_QUOTES, 'UTF-8'); ?>" class="logo" alt="Red Ghost logo">
          <h2 class="logo-text">RED GHOST</h2>
        </a>

        <!-- Search-bar -->
        <form class="shop-header-search" role="search" onsubmit="return false;">
          <label class="shop-header-search-field" for="searchInput">
            <input type="search" id="searchInput" class="shop-search-input" placeholder="Hľadaj produkty..." autocomplete="off">
          </label>
          <button type="button" class="shop-header-search-btn" aria-label="Hladat produkty">
            <i class="fa-solid fa-magnifying-glass"></i>
          </button>
          <div id="searchSuggestions" class="search-suggestions" role="listbox" aria-label="Návrhy produktov" aria-hidden="true"></div>
        </form>

        <div class="shop-header-tools">

          <!-- Icona v headeru profil -->
          <div class="profile-menu" id="profileMenu">
            <a href="<?php echo htmlspecialchars($profileHref, ENT_QUOTES, 'UTF-8'); ?>" class="profile-icon" aria-label="Profil pouzivatela" id="profileIcon">
              <i class="fa-solid fa-user"></i>
            </a>

            <div class="profile-popup" id="profilePopup" role="menu" aria-hidden="true">

              <?php if ($isLoggedIn): ?>
                <p class="profile-popup-email"><?php echo htmlspecialchars($profileEmail !== '' ? $profileEmail : ($profileName !== '' ? $profileName : 'Prihlaseny pouzivatel'), ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="profile-popup-points">Body z nákupov: <strong><?php echo $profilePoints; ?></strong></p>
                <a href="<?php echo htmlspecialchars(route('/userprofile'), ENT_QUOTES, 'UTF-8'); ?>" class="profile-popup-link">Prejsť na profil</a>
              <?php else: ?>
                <p class="profile-popup-email">Neni si prihlásený.</p>
                <a href="<?php echo htmlspecialchars(route('/login'), ENT_QUOTES, 'UTF-8'); ?>" class="profile-popup-link">Prihlás sa</a>
              <?php endif; ?>
              
            </div>
          </div>

          <!-- Nakupny košik s popup v header -->
          <div id="cartMenu" class="cart-menu">
            <button id="cartIcon" aria-expanded="false" aria-label="Otvorit kosik">
              <i class="fa-solid fa-cart-shopping"></i>
              <span id="headerCartCount" class="header-cart-count">0</span>
              <span id="headerCartTotal" class="sr-only">0.00 EUR</span>
            </button>
  
            <div id="cartPopup" class="cart-popup" aria-hidden="true">
              <div class="cart-items-list" id="cartItemsList">
                <!-- pridane produkty do košika sa pridaju sem -->
              </div>
              <a href="<?php echo route('/shopcart'); ?>" class="cart-view-btn">Zobraziť košík</a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </header>