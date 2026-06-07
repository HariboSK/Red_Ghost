<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once dirname(__DIR__) . '/core/SessionHelper.php';
require_once dirname(__DIR__) . '/core/middleware/Function.php';
require_once dirname(__DIR__) . '/core/AssetHelper.php';

$sessionUser = SessionHelper::user();
$forgotPasswordNotice = '';

if ((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));

    if ($email !== '') {
        $_SESSION['forgot_password_notice'] = 'Ak účet existuje, pošleme ti inštrukcie na e-mail.';
        $forgotPasswordNotice = (string) $_SESSION['forgot_password_notice'];
    } else {
        $_SESSION['forgot_password_error'] = 'Zadaj svoj e-mail.';
    }
}

$forgotPasswordNotice = (string) ($_SESSION['forgot_password_notice'] ?? $forgotPasswordNotice);
$forgotPasswordError = (string) ($_SESSION['forgot_password_error'] ?? '');
unset($_SESSION['forgot_password_notice'], $_SESSION['forgot_password_error']);
?>

<!DOCTYPE html>
<html lang="sk">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Ghost - Zabudnuté heslo</title>
    <?php foreach (AssetHelper::current_page_assets() as $css): ?>
        <link rel="stylesheet" href="<?php echo asset('css/' . trim($css, '/')); ?>">
    <?php endforeach; ?>
    <link rel="shortcut icon" type="image/x-icon" href="/assets/images/favicon.webp">
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
        </div>

        <a href="<?php echo route('/'); ?>" class="home-link">
            <img src="/assets/icons/alt-house-svgrepo-com.svg" alt="Späť na domov" class="home-logo">
        </a>

        <div class="login-page-container">
            <section class="auth-shell auth-shell--single">
                <div class="auth-panel login-panel active">
                    <h2>Zabudnuté heslo</h2>
                    <p class="auth-helper">Zadaj e-mail a pošleme ti ďalšie kroky.</p>

                    <?php if ($forgotPasswordNotice !== ''): ?>
                        <p class="forgot-password-message forgot-password-message--success"><?php echo htmlspecialchars($forgotPasswordNotice, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>

                    <?php if ($forgotPasswordError !== ''): ?>
                        <p class="forgot-password-message forgot-password-message--error"><?php echo htmlspecialchars($forgotPasswordError, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>

                    <form action="<?php echo route('/forgot-password'); ?>" method="POST" class="auth-form">
                        <label for="forgot-email">E-mail:</label>
                        <div class="input-with-icon email-icon">
                            <span class="input-icon" aria-hidden="true"></span>
                            <input type="email" id="forgot-email" name="email" placeholder="jano@mrkvicka.com" required>
                        </div>

                        <button type="submit" class="auth-submit">Poslať inštrukcie</button>
                    </form>

                    <p class="auth-footer-link">
                        <a href="<?php echo route('/login'); ?>">Späť na prihlásenie</a>
                    </p>
                </div>
            </section>
        </div>

        <?php require __DIR__ . '/partials/footer-shop.php'; ?>
    </main>
</body>

</html>