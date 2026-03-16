<?php
// Header pre e-shop stranku - oddeleny od hlavnej stranky

if (!isset($baseUrl)) {
  $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
  $publicPos = strpos($scriptName, '/public/');

  if ($publicPos !== false) {
    $baseUrl = substr($scriptName, 0, $publicPos);
  } else {
    $baseUrl = rtrim(dirname($scriptName), '/');
    if ($baseUrl === '/' || $baseUrl === '.') {
      $baseUrl = '';
    }
  }
}

$assetBase = rtrim($baseUrl, '/') . '/public/assets';
$baseUrlEscaped = htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8');
$assetBaseEscaped = htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Jakub Chrkavy">
    <meta name="description" content="Objav nasu ponuku chilli papriciek - klikni a ochutnaj palivu vasen zo slovenskych zahrad! Vyber si svoju palivost - od jemneho Jalapena po extremne Carolina Reaper!">
    <title>Red Ghost - E-shop</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $assetBaseEscaped; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo $assetBaseEscaped; ?>/css/style2.css">
    <link rel="stylesheet" href="<?php echo $assetBaseEscaped; ?>/css/animation.css">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo $assetBaseEscaped; ?>/images/305036798_501410268657650_7493754093765322046_n-modified.png">
</head>
<body>
  <!-- Header pre e-shop -->
  <header class="posun">
    <div class="container">
      <div class="navbar">
        <a href="<?php echo $baseUrlEscaped; ?>/home.php" class="nav-logo">
          <img src="<?php echo $assetBaseEscaped; ?>/images/logo-text.png" class="logo" alt="Red Ghost logo"><h2 class="logo-text">Red Ghost</h2>
        </a>
        <a href="<?php echo $baseUrlEscaped; ?>/E-shop.php" aria-label="Kosik">
          <img src="<?php echo $assetBaseEscaped; ?>/images/shoppingcart.png" width="40" height="40" class="shoppingcart" alt="Kosik">
        </a>
      </div>
    </div>
  </header>
