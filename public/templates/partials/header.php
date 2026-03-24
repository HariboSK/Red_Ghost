<?php
// Header - Globálny header pre všetky stránky

if (!isset($baseUrl)) {
  $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
  $baseUrl = str_replace('\\', '/', dirname($scriptName));
  $baseUrl = rtrim($baseUrl, '/');
  
  if (substr($baseUrl, -19) === '/templates/partials') {
    $baseUrl = substr($baseUrl, 0, -19);
  }
  if ($baseUrl === '/' || $baseUrl === '\\' || $baseUrl === '.') {
        $baseUrl = '';
    }
}

$scriptFilename = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
$isPublicEntrypoint = preg_match('~/public/[^/]+\\.php$~', $scriptFilename) === 1;
$assetBase = rtrim($baseUrl, '/') . ($isPublicEntrypoint ? '/assets' : '/public/assets');
$baseUrlEscaped = htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8');
$assetBaseEscaped = htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Jakub Chrkavý">
    <meta name="description" content="Objav našu ponuku chilli papričiek – klikni a ochutnaj pálivú vášeň zo slovenských záhrad! Vyber si svoju pálivosť – od jemného Jalapena po extrémne Carolina Reaper!">
    <title>Red Ghost - O NÁS</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars($assetBaseEscaped); ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($assetBaseEscaped); ?>/css/animation.css">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo htmlspecialchars($assetBaseEscaped); ?>/images/305036798_501410268657650_7493754093765322046_n-modified.png">
</head>
<body>
  <!-- Header / Navbar -->
  <header>
    <nav class="navbar section-content">
      <a href="<?php echo $baseUrlEscaped; ?>/home.php" class="nav-logo">
        <img src="<?php echo htmlspecialchars($assetBaseEscaped); ?>/images/logo-text.png" class="logo" alt="Red Ghost logo"><h2 class="logo-text">Red Ghost</h2>
      </a>                                                        
      <ul class="nav-menu"> 
        <button id="menu-close-button" class="fas fa-times"></button>
        <li class="nav-item">
          <a href="<?php echo $baseUrlEscaped; ?>/home.php" class="nav-link">Domov</a>
        </li>
        <li class="nav-item">
          <a href="<?php echo $baseUrlEscaped; ?>/home.php#about" class="nav-link">O nás</a>
        </li>
        <li class="nav-item">
          <a href="<?php echo $baseUrlEscaped; ?>/home.php#menu" class="nav-link">Produkty</a>
        </li>
        <li class="nav-item">
          <a href="<?php echo $baseUrlEscaped; ?>/home.php#testimonials" class="nav-link">Recenzie</a>
        </li>
        <li class="nav-item">
          <a href="<?php echo $baseUrlEscaped; ?>/home.php#contact" class="nav-link">Kontakt</a>
        </li>
        <li class="nav-item">
          <a href="<?php echo $baseUrlEscaped; ?>/e_shop.php" class="nav-link">E-shop</a>
        </li>
      </ul>

      <button id="menu-open-button" class="fas fa-bars"></button>
    </nav>  
  </header>
