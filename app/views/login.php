<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once dirname(__DIR__) . '/core/AuthView.php';
require_once dirname(__DIR__) . '/core/middleware/Function.php';
require_once dirname(__DIR__) . '/core/AssetHelper.php';
$auth = new AuthView();
$errors = $auth->getErrors();
$activeForm = $auth->getActiveForm();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Ghost Log-in</title>
    <!-- Pridanie odkazu na CSS súbor -->
    <?php foreach (AssetHelper::current_page_assets() as $css): ?>
        <link rel="stylesheet" href="<?php echo asset('css/' . trim($css, '/')); ?>">
    <?php endforeach; ?>
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon"
        href="./assets/images/favicon.webp">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

</head>

<body class="login-page">

    <main>

        <div class="bg-pulse-layer" aria-hidden="true">
            <span class="bg-pulse-circle bg-pulse-1"></span>
            <span class="bg-pulse-circle bg-pulse-2"></span>
            <span class="bg-pulse-circle bg-pulse-3"></span>
            <span class="bg-pulse-circle bg-pulse-4"></span>
        </div>

        <div class="floating-chilli-layer" aria-hidden="true">
            <img src="/assets/images/chilli2.webp" alt="chilli" class="floating-chilli chilli-1">
            <img src="/assets/images/chilli2.webp" alt="chilli" class="floating-chilli chilli-2">
            <img src="/assets/images/chilli2.webp" alt="chilli" class="floating-chilli chilli-3">
            <img src="/assets/images/chilli2.webp" alt="chilli" class="floating-chilli chilli-4">
            <img src="/assets/images/chilli.webp" alt="chilli" class="floating-chilli chilli-5">
            <img src="/assets/images/chilli.webp" alt="chilli" class="floating-chilli chilli-6">
            <img src="/assets/images/chilli.webp" alt="chilli" class="floating-chilli chilli-7">
        </div>

        <!--Icona domov-->
        <a href="<?php echo route('/'); ?>" class="home-link">
            <img src="./assets/icons/alt-house-svgrepo-com.svg" alt="Icona na vratenie na Home" class="home-logo">
        </a>


        <div class="login-page-container">
            <section class="auth-shell" id="authShell">
                <div class="auth-tabs" role="tablist" aria-label="Prepnutie formulara">
                    <button type="button" class="auth-tab is-active" data-target="login"
                        aria-selected="true">Prihlásenie</button>
                    <button type="button" class="auth-tab" data-target="register" aria-selected="false">Registrovať
                        sa</button>
                    <span class="auth-tab-indicator" aria-hidden="true"></span>
                </div>

                <div class="auth-track" id="authTrack">
                    <section class="auth-panel login-panel <?= $auth->isActiveForm('login'); ?>"
                        aria-labelledby="loginTitle">
                        <h2 id="loginTitle">Prihlásenie</h2>
                        <p class="auth-helper">Nemáte účet?</p>
                        <button type="button" class="auth-inline-switch" data-target="register">Registrovať sa</button>

                        <!-- FORM na LOGIN -->
                        <form action="<?php echo route('/login_register'); ?>" method="POST" class="auth-form">
                            <label for="login-email">E-mail:</label>
                            <div class="input-with-icon email-icon">
                                <span class="input-icon" aria-hidden="true"></span>
                                <input type="email" id="login-email" name="email" placeholder="jano@mrkvicka.com"
                                    required>
                            </div>

                            <label for="login-password">Heslo:</label>
                            <div class="input-with-icon password-icon">
                                <span class="input-icon" aria-hidden="true"></span>
                                <input type="password" id="login-password" name="password" placeholder="Heslo123"
                                    required>
                                <button type="button" class="password-toggle" data-toggle-password="login-password"
                                    aria-label="Zobraziť heslo" aria-pressed="false">
                                    <span class="password-toggle-icon" aria-hidden="true"></span>
                                </button>
                            </div>

                            <!--Zabudnute heslo link-->
                            <a class="forgot-password" href="<?php echo route('/forgot-password'); ?>">Zabudli ste heslo?</a>

                            <button type="submit" name="login" class="auth-submit">Prihlásiť sa</button>
                            <?= $auth->showError($errors['login']); ?>
                        </form>

                    </section>

                    <section class="auth-panel register-panel <?= $auth->isActiveForm('register'); ?>"
                        aria-labelledby="registerTitle">
                        <h2 id="registerTitle">Registrácia</h2>

                        <p class="auth-helper">Už máte účet?</p>
                        <button type="button" class="auth-inline-switch" data-target="login">Prihlásiť sa</button>


                        <!--FORM na REGISTER -->
                        <form action="<?php echo route('/login-register'); ?>" method="POST" class="auth-form">
                            <div class="name-row">
                                <div class="name-col">
                                    <label for="register-name">Meno:</label>
                                    <div class="input-with-icon user-icon">
                                        <span class="input-icon" aria-hidden="true"></span>
                                        <input type="text" id="register-name" name="name" placeholder="Jano" required>
                                    </div>
                                </div>
                                <div class="name-col">
                                    <label for="register-priezvisko">Priezvisko:</label>
                                    <div class="input-with-icon user-icon">
                                        <span class="input-icon" aria-hidden="true"></span>
                                        <input type="text" id="register-priezvisko" name="priezvisko"
                                            placeholder="Mrkvicka" required>
                                    </div>
                                </div>
                            </div>

                            <label for="register-email">E-mail:</label>
                            <div class="input-with-icon email-icon">
                                <span class="input-icon" aria-hidden="true"></span>
                                <input type="email" id="register-email" name="email" placeholder="jano@mrkvicka.com"
                                    required>
                            </div>

                            <label for="register-password">Heslo:</label>
                            <div class="input-with-icon password-icon">
                                <span class="input-icon" aria-hidden="true"></span>
                                <input type="password" id="register-password" name="password" placeholder="Heslo123"
                                    required>
                                <button type="button" class="password-toggle" data-toggle-password="register-password"
                                    aria-label="Zobraziť heslo" aria-pressed="false">
                                    <span class="password-toggle-icon" aria-hidden="true"></span>
                                </button>
                            </div>

                            <label for="login-password-repe">Zopakuj heslo:</label>
                            <div class="input-with-icon password-icon">
                                <span class="input-icon" aria-hidden="true"></span>
                                <input type="password" id="login-password-repe" name="repeat-password"
                                    placeholder="Heslo123" required>
                                <button type="button" class="password-toggle" data-toggle-password="login-password-repe"
                                    aria-label="Zobraziť heslo" aria-pressed="false">
                                    <span class="password-toggle-icon" aria-hidden="true"></span>
                                </button>
                            </div>

                            <button type="submit" name="register" class="auth-submit">Vytvoriť účet</button>
                            <?= $auth->showError($errors['register']); ?>
                        </form>

                    </section>
                </div>
            </section>
        </div>


        <!-- Custom JS -->
        <script src="/assets/js/login.js"></script>

        <!-- Footer section -->
        <?php require __DIR__ . '/partials/footer-shop.php'; ?>