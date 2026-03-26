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
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/animation.css">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="/assets/images/305036798_501410268657650_7493754093765322046_n-modified.png">
</head>
<body>
  <!-- Header / Navbar -->
  <header>
    <nav class="navbar section-content">
      <a href="<?php echo route('/home'); ?>" class="nav-logo">
        <img src="/assets/images/logo-text.png" class="logo" alt="Red Ghost logo"><h2 class="logo-text">Red Ghost</h2>
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
      </ul>

      <button id="menu-open-button" class="fas fa-bars"></button>
    </nav>  
  </header>
