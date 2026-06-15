<?php
declare(strict_types=1);

$pdo = $GLOBALS['conn'] ?? null; 
if (!($pdo instanceof PDO)) {
    die('Systémová chyba: Databázové pripojenie zlyhalo.');
}

// Spustenie OOP Controlleru
$controller = new ForgotPasswordController($pdo);
$controller->handle();

// Vytiahnutie dát pre HTML šablónu
$forgotPasswordNotice = $controller->getNotice();
$forgotPasswordError = $controller->getError();
$verifiedEmailInSession = $controller->getVerifiedEmail();
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Ghost - Obnova hesla</title>
    <?php foreach (AssetHelper::current_page_assets() as $css): ?>
        <link rel="stylesheet" href="<?php echo asset('css/' . trim($css, '/')); ?>">
    <?php endforeach; ?>
    <link rel="shortcut icon" type="image/x-icon" href="./assets/images/favicon.webp">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body class="login-page">
    <main>
        <div class="login-page-container">

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

            <a href="<?php echo route('/'); ?>" class="home-link">
                <img src="./assets/icons/alt-house-svgrepo-com.svg" alt="Ikona na vrátenie na Home" class="home-logo">
            </a>

            <section class="auth-shell auth-shell--single">
                <div class="auth-panel login-panel active">

                    <?php if ($forgotPasswordNotice !== ''): ?>
                        <p class="forgot-password-message forgot-password-message--success"><?php echo htmlspecialchars($forgotPasswordNotice, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>

                    <?php if ($forgotPasswordError !== ''): ?>
                        <p class="forgot-password-message forgot-password-message--error"><?php echo htmlspecialchars($forgotPasswordError, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>

                    <?php if ($verifiedEmailInSession !== null): ?>
                        
                        <h2>Nové heslo</h2>
                        <p class="auth-helper">Kód bol úspešne overený. Zadaj nové heslo pre účet: <br><strong><?php echo htmlspecialchars($verifiedEmailInSession, ENT_QUOTES, 'UTF-8'); ?></strong></p>

                        <form action="<?php echo route('/forgot-password'); ?>" method="POST" class="auth-form">
                            <label for="login-password">Nové heslo:</label>
                            <div class="input-with-icon password-icon">
                                <span class="input-icon" aria-hidden="true"></span>
                                <input type="password" id="login-password" name="password" placeholder="MojeNoveHeslo123" required>
                                <button type="button" class="password-toggle" data-toggle-password="login-password" aria-label="Zobraziť heslo" aria-pressed="false">
                                    <span class="password-toggle-icon" aria-hidden="true"></span>
                                </button>
                            </div>
                            <button type="submit" class="auth-submit">Uložiť nové heslo</button>
                        </form>

                    <?php else: ?>
                        
                        <h2>Obnova hesla</h2>
                        <p class="auth-helper">Pre obnovu hesla zadaj svoj registrovaný e-mail a 12-miestny bezpečnostný kód.</p>

                        <form action="<?php echo route('/forgot-password'); ?>" method="POST" class="auth-form">
                            <?php echo SessionHelper::csrfField(); ?>
                            
                            <label for="forgot-email">E-mail:</label>
                            <div class="input-with-icon email-icon">
                                <span class="input-icon" aria-hidden="true"></span>
                                <input type="email" id="forgot-email" name="email" placeholder="jano@mrkvicka.com" required>
                            </div>

                            <label for="recovery-code">12-miestny bezpečnostný kód:</label>
                            <div class="key-icon">
                                <input type="text" id="recovery-code" name="recovery_code" placeholder="888877766655" maxlength="12" required>
                                <span class="input-icon" aria-hidden="true"><i class="fa-solid fa-key"></i></span>
                            </div>

                            <button type="submit" class="auth-submit">Overiť údaje</button>
                        </form>

                    <?php endif; ?>

                    <p class="auth-footer-link">
                        <a href="<?php echo route('/login'); ?>">Späť na prihlásenie</a>
                    </p>
                </div>
            </section>
        </div>
        <?php require __DIR__ . '/partials/footer-shop.php'; ?>
    </main>
    <script src="/assets/js/login.js"></script>
</body>
</html>