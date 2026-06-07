<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once dirname(__DIR__, 2) . '/core/middleware/Function.php';
require_once dirname(__DIR__, 2) . '/core/Helper.php';
require_once dirname(__DIR__, 2) . '/core/AssetHelper.php';
require_once dirname(__DIR__, 2) . '/core/Flash.php';

$resolvedPageTitle = isset($pageTitle) && is_string($pageTitle) && $pageTitle !== ''
  ? $pageTitle
  : Helper::getPageTitle() . ' - E-shop';

require_once dirname(__DIR__, 2) . '/core/SessionHelper.php';
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

$profileHref = $isLoggedIn ? route('/userprofile') : route('/login');

if (!function_exists('normalize_cart_image_path')) {
  function normalize_cart_image_path(string $image): string
  {
    $image = trim($image);

    if ($image === '') {
      return '/assets/images/omacka3.webp';
    }

    if (preg_match('~^(https?:)?//~i', $image) === 1 || strpos($image, '/') === 0) {
      return preg_replace('~\.(jpe?g)$~i', '.webp', $image);
    }

    return preg_replace('~\.(jpe?g)$~i', '.webp', '/assets/images/' . ltrim($image, '/'));
  }
}

$headerCartItems = [];
$headerCartCountValue = 0;
$headerCartTotalValue = 0.0;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $item) {
    $quantity = max(0, (int) ($item['quantity'] ?? 0));
    if ($quantity <= 0) {
      continue;
    }

    $price = (float) ($item['price'] ?? 0);
    $headerCartItems[] = [
      'name' => (string) ($item['name'] ?? 'Produkt'),
      'price' => $price,
      'quantity' => $quantity,
      'image' => normalize_cart_image_path((string) ($item['image'] ?? '')),
    ];

    $headerCartCountValue += $quantity;
    $headerCartTotalValue += $price * $quantity;
  }
}

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

  <!-- Custom CSS -->
  <?php foreach (AssetHelper::current_page_assets() as $css): ?>
    <link rel="stylesheet" href="<?php echo asset('css/' . ltrim($css, '/')); ?>">
  <?php endforeach; ?>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

  <!-- Favicon -->
  <link rel="shortcut icon" type="image/x-icon"
    href="/assets/images/favicon.webp">
</head>

<body class="<?php echo isset($bodyClass) ? htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') : ''; ?>"
  data-cart-api="<?php echo route('/api/cart.php'); ?>"
  data-checkout-api="<?php echo route('/api/checkout.php'); ?>">
  <?php if ($f = get_flash()): ?>
    <div class="flash flash--<?php echo htmlspecialchars((string) ($f['type'] ?? 'info'), ENT_QUOTES, 'UTF-8'); ?>" role="status" aria-live="polite">
      <?php echo htmlspecialchars((string) ($f['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php endif; ?>
  
  <!-- Header pre e-shop -->
  <header class="posun">
    <div class="container">
      <div class="navbar">
        <a href="<?php echo route('/home'); ?>" class="nav-logo">
          <img src="/assets/images/logo-text.webp" class="logo" alt="Red Ghost logo">
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
                <a href="<?php echo htmlspecialchars($userRole === 'admin' ? htmlspecialchars(route('/dashboard')) : htmlspecialchars(route('/userprofile')), ENT_QUOTES, 'UTF-8'); ?>" class="profile-popup-link">Prejsť na profil</a>
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
              <span id="headerCartCount" class="header-cart-count"><?php echo (int) $headerCartCountValue; ?></span>
              <span id="headerCartTotal" class="sr-only"><?php echo number_format($headerCartTotalValue, 2, '.', ''); ?> EUR</span>
            </button>
  
            <div id="cartPopup" class="cart-popup" aria-hidden="true">
              <div class="cart-items-list" id="cartItemsList">
                <?php if (!empty($headerCartItems)): ?>
                  <?php foreach ($headerCartItems as $item): ?>
                    <div class="cart-item">
                      <img src="<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>">
                      <div class="cart-item-info">
                        <p class="cart-item-name"><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="cart-item-price"><?php echo number_format($item['price'], 2, '.', ''); ?> EUR x <?php echo (int) $item['quantity']; ?></p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <p style="padding: 10px; text-align: center;">Košík je prázdny</p>
                <?php endif; ?>
              </div>
              <a href="<?php echo route('/shopcart'); ?>" class="cart-view-btn">Zobraziť košík</a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </header>