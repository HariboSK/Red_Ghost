<?php
$resolvedPageTitle = isset($pageTitle) && is_string($pageTitle) && $pageTitle !== ''
  ? $pageTitle
  : 'Red Ghost - E-shop';
$resolvedExtraStyles = (isset($extraStyles) && is_array($extraStyles)) ? $extraStyles : [];

require_once dirname(__DIR__, 2) . '/core/session_helper.php';
rg_session_bootstrap();

$sessionUser = rg_session_user();

$profileEmail = (string) ($sessionUser['email'] ?? '');
$profileName = (string) ($sessionUser['name'] ?? '');
$isLoggedIn = (bool) ($sessionUser['is_logged_in'] ?? false);
$profilePoints = (int) ($sessionUser['points'] ?? 0);

if ($isLoggedIn && $profileEmail !== '' && isset($conn) && $conn instanceof mysqli) {
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
  <link rel="stylesheet" href="./assets/css/shopcart.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="/assets/css/style2.css">


  <?php foreach ($resolvedExtraStyles as $stylePath): ?>
    <?php if (is_string($stylePath) && $stylePath !== ''): ?>
      <link rel="stylesheet" href="<?php echo htmlspecialchars($stylePath, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
  <?php endforeach; ?>


  <!-- Favicon -->
  <link rel="shortcut icon" type="image/x-icon"
    href="/assets/images/305036798_501410268657650_7493754093765322046_n-modified.png">
</head>

<body class="<?php echo isset($bodyClass) ? htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') : ''; ?>"
  data-cart-api="<?php echo route('/api/cart.php'); ?>">
  <!-- Header pre e-shop -->
  <header class="posun">
    <div class="container">
      <div class="navbar">
        <a href="<?php echo route('/home'); ?>" class="nav-logo">
          <img src="/assets/images/logo-text.png" class="logo" alt="Red Ghost logo">
          <h2 class="logo-text">Red Ghost</h2>
        </a>

        <div class="shop-header-tools">
          <div class="profile-menu" id="profileMenu">
            <a href="<?php echo htmlspecialchars($profileHref, ENT_QUOTES, 'UTF-8'); ?>" class="profile-icon" aria-label="Profil pouzivatela" id="profileIcon">
              <i class="fa-solid fa-user"></i>
            </a>

            <div class="profile-popup" id="profilePopup" role="menu" aria-hidden="true">

              <?php if ($isLoggedIn): ?>
                <p class="profile-popup-email"><?php echo htmlspecialchars($profileEmail !== '' ? $profileEmail : ($profileName !== '' ? $profileName : 'Prihlaseny pouzivatel'), ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="profile-popup-points">Body z nakupov: <strong><?php echo $profilePoints; ?></strong></p>
                <a href="<?php echo htmlspecialchars(route('/userprofile'), ENT_QUOTES, 'UTF-8'); ?>" class="profile-popup-link">Prejst na profil</a>
              <?php else: ?>
                <p class="profile-popup-email">Nie si prihlaseny.</p>
                <a href="<?php echo htmlspecialchars(route('/login'), ENT_QUOTES, 'UTF-8'); ?>" class="profile-popup-link">Prihlas sa</a>
              <?php endif; ?>
              
            </div>
          </div>

          <a href="<?php echo route('/shopcart'); ?>" class="header-cart-summary" aria-label="Otvorit kosik">
            <i class="fa-solid fa-cart-shopping"></i>
            <span id="headerCartTotal">0.00 EUR</span>
          </a>
        </div>

      </div>
    </div>
  </header>