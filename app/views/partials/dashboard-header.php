<!DOCTYPE html>
<html lang="sk">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Jakub Chrkavý">
    <meta name="description" content="Red Ghost Admin Dashboard">
    <title>Red Ghost - Dashboard</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/animation.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/userprofile.css">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon"
        href="/assets/images/favicon.webp">
</head>

<body>
    <!-- Header / Navbar -->
    <header>
        <nav class="navbar section-content">
            <a href="<?php echo route('/dashboard'); ?>" class="nav-logo">
                <img src="/assets/images/logo-text.webp" class="logo" alt="Red Ghost logo">
                <h2 class="logo-text">Red Ghost</h2>
            </a>

            <button id="menu-open-button" class="fas fa-bars"></button>
        </nav>
    </header>