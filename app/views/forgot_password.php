<?php
declare(strict_types=1);

$pdo = $GLOBALS['conn'] ?? null; 
if (!($pdo instanceof PDO)) {
    die('Systémová chyba: Databázové pripojenie zlyhalo.');
}

$resetService = new ResetPassword($pdo);

// 1. SPRACOVANIE GET (Zobrazenie formulára pre nové heslo)
$tokenFromUrl = $_GET['token'] ?? '';
$isTokenValid = false;
$userEmail = null;

if ($tokenFromUrl !== '') {
    $userEmail = $resetService->verifyToken($tokenFromUrl);
    $isTokenValid = ($userEmail !== null);
}

// Načítanie notifikácií zo session
$forgotPasswordNotice = $_SESSION['forgot_password_notice'] ?? '';
$forgotPasswordError = $_SESSION['forgot_password_error'] ?? '';
$simulatedEmailLink = $_SESSION['simulated_email_link'] ?? ''; // Trik pre localhost obhajobu
unset($_SESSION['forgot_password_notice'], $_SESSION['forgot_password_error'], $_SESSION['simulated_email_link']);

// 2. SPRACOVANIE POST (Odoslanie formulárov)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Krok 1: Používateľ požiadal o reset (zadal email)
    if (isset($_POST['email'])) {
        $email = trim((string)$_POST['email']);

        if ($email === '') {
            $_SESSION['forgot_password_error'] = 'Zadajte platný e-mail.';
            header("Location: " . route('/forgot-password'));
            exit;
        }

        // Voláme čisté OOP riešenie z našej služby
        $token = $resetService->generateTokenForEmail($email);

        // BEZPEČNOSŤ: Vždy povieme, že e-mail bol odoslaný (ochrana pred zisťovaním existujúcich emailov)
        $_SESSION['forgot_password_notice'] = 'Ak zadaný e-mail v systéme existuje, poslali sme naň odkaz na obnovu hesla.';

        // LOCALHOST TRIK: Ak sme na localhoste a token sa vygeneroval, nasimulujeme e-mail do Session
        if ($token !== null && ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1')) {
            $_SESSION['simulated_email_link'] = route('/forgot-password') . "?token=" . $token;
        }

        header("Location: " . route('/forgot-password'));
        exit;
    }

    // Krok 2: Používateľ zadáva nové heslo
    if (isset($_POST['password'], $_POST['token'])) {
        $postToken = (string)$_POST['token'];
        $newPassword = (string)$_POST['password'];

        $emailForReset = $resetService->verifyToken($postToken);

        if ($emailForReset) {
            $resetService->resetPassword($emailForReset, $newPassword);
            $resetService->deleteToken($emailForReset);

            $_SESSION['login_success'] = 'Heslo bolo úspešne zmenené. Môžeš sa prihlásiť.';
            header("Location: " . route('/login'));
            exit;
        } else {
            $_SESSION['forgot_password_error'] = 'Token vypršal alebo je neplatný.';
            header("Location: " . route('/forgot-password'));
            exit;
        }
    }
}
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

            <!--Icona domov-->
            <a href="<?php echo route('/'); ?>" class="home-link">
                <img src="./assets/icons/alt-house-svgrepo-com.svg" alt="Icona na vratenie na Home" class="home-logo">
            </a>

            <section class="auth-shell auth-shell--single">
                <div class="auth-panel login-panel active">

                    <?php if ($forgotPasswordNotice !== ''): ?>
                        <p class="forgot-password-message forgot-password-message--success"><?php echo htmlspecialchars($forgotPasswordNotice, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>

                    <?php if ($forgotPasswordError !== ''): ?>
                        <p class="forgot-password-message forgot-password-message--error"><?php echo htmlspecialchars($forgotPasswordError, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>

                    <?php if ($simulatedEmailLink !== ''): ?>
                        <div style="background: #222; border: 1px solid #ff4a4a; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                            <strong style="color: #ff4a4a;"><i class="fa-solid fa-envelope"></i> [MOCK EMAIL SERVICE]</strong>
                            <p style="font-size: 13px; margin: 5px 0;">Na produkcii by sa tento link odoslal na e-mail. Pre účely obhajoby na localhoste klikni sem:</p>
                            <a href="<?php echo $simulatedEmailLink; ?>" style="color: #00fff0; font-weight: bold; word-break: break-all;"><?php echo $simulatedEmailLink; ?></a>
                        </div>
                    <?php endif; ?>


                    <?php if ($tokenFromUrl !== '' && $isTokenValid): ?>
                        
                        <h2>Nové heslo</h2>
                        <p class="auth-helper">Zadaj svoje nové heslo pre účet <strong><?php echo htmlspecialchars((string)$userEmail); ?></strong></p>

                        <form action="<?php echo route('/forgot-password'); ?>" method="POST" class="auth-form">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($tokenFromUrl); ?>">
                            
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

                            <button type="submit" class="auth-submit">Zmeniť heslo</button>
                        </form>

                    <?php else: ?>
                        
                        <h2>Zabudnuté heslo</h2>
                        
                        <?php if ($tokenFromUrl !== '' && !$isTokenValid): ?>
                            <p class="forgot-password-message forgot-password-message--error">Odkaz na reset hesla je neplatný alebo vypršal.</p>
                        <?php endif; ?>
                        
                        <p class="auth-helper">Zadaj e-mail a prejdeme k zmene hesla.</p>

                        <form action="<?php echo route('/forgot-password'); ?>" method="POST" class="auth-form">
                            <?php echo SessionHelper::csrfField(); ?>
                            <label for="forgot-email">E-mail:</label>
                            <div class="input-with-icon email-icon">
                                <span class="input-icon" aria-hidden="true"></span>
                                <input type="email" id="forgot-email" name="email" placeholder="jano@mrkvicka.com" required>
                            </div>
                            <button type="submit" class="auth-submit">Resetovať heslo</button>
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