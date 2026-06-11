<?php
$pdo = $GLOBALS['conn']; 
$sessionUser = SessionHelper::user();

$resetService = new ResetPassword($pdo);

// token z URL
$tokenFromUrl = $_GET['token'] ?? '';
$isTokenValid = false;
$userEmail = null;

if ($tokenFromUrl !== '') {
    $userEmail = $resetService->verifyToken($tokenFromUrl);
    $isTokenValid = ($userEmail !== null);
}

// 2. NAČÍTANIE A VYČISTENIE NOTIFIKÁCIÍ
$forgotPasswordNotice = $_SESSION['forgot_password_notice'] ?? '';
$forgotPasswordError = $_SESSION['forgot_password_error'] ?? '';
unset($_SESSION['forgot_password_notice'], $_SESSION['forgot_password_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // token a email overenie
    if (isset($_POST['email'])) {
        $email = trim((string)$_POST['email']);

        if ($email !== '') {
            // validovanie či email exsituje v DB
            $checkUser = $pdo->prepare("SELECT id FROM user WHERE email = ?");
            $checkUser->execute([$email]);
            
            if ($checkUser->fetch()) {
                //vygenerujeme token
                $token = bin2hex(random_bytes(32));
                $hashedToken = hash('sha256', $token);
                $expiresAt = date("Y-m-d H:i:s", time() + 3600);

                // Staré tokeny pre tento email zmažem
                $resetService->deleteToken($email);

                // zapis tokenu
                $stmt = $pdo->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)');
                $stmt->execute([
                    ':email' => $email,
                    ':token' => $hashedToken,
                    ':expires_at' => $expiresAt
                ]);

                header("Location: " . route('/forgot-password') . "?token=" . $token);
                exit;
            } else {
                $_SESSION['forgot_password_error'] = 'Používateľ s týmto e-mailom neexistuje.';
                header("Location: " . route('/forgot-password'));
                exit;
            }
        } else {
            $_SESSION['forgot_password_error'] = 'Zadajte platný e-mail.';
            header("Location: " . route('/forgot-password'));
            exit;
        }
    }

    // Používateľ zadal nové heslo s tokenom 
    if (isset($_POST['password'], $_POST['token'])) {
        $postToken = $_POST['token'];
        $newPassword = $_POST['password'];

        $emailForReset = $resetService->verifyToken($postToken);

        if ($emailForReset) {
            // Zmena heslo
            $resetService->resetPassword($emailForReset, $newPassword);
            // Vymažeme token, aby sa nedal použiť znova
            $resetService->deleteToken($emailForReset);

            $_SESSION['forgot_password_notice'] = 'Heslo bolo úspešne zmenené. Môžeš sa prihlásiť.';
            //stránka bez tokenu
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
            <span class="bg-pulse-circle bg-pulse-1"></span><span class="bg-pulse-circle bg-pulse-2"></span>
        </div>

        <div class="login-page-container">
            <section class="auth-shell auth-shell--single">
                <div class="auth-panel login-panel active">

                    <?php if ($forgotPasswordNotice !== ''): ?>
                        <p class="forgot-password-message forgot-password-message--success"><?php echo htmlspecialchars($forgotPasswordNotice, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>

                    <?php if ($forgotPasswordError !== ''): ?>
                        <p class="forgot-password-message forgot-password-message--error"><?php echo htmlspecialchars($forgotPasswordError, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>

                    <?php if ($tokenFromUrl !== '' && $isTokenValid): ?>
    
    
                        <h2>Nové heslo</h2>
                        <p class="auth-helper">Zadaj svoje nové heslo pre účet <strong><?php echo htmlspecialchars($userEmail); ?></strong></p>

                        <form action="<?php echo route('/forgot-password'); ?>" method="POST" class="auth-form">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($tokenFromUrl); ?>">
                            
                            <label for="login-password">Nové heslo:</label>
                            <div class="input-with-icon password-icon">
                                <span class="input-icon" aria-hidden="true"></span> 
                                
                                <input type="password" id="login-password" name="password" placeholder="Heslo123" required>
                                
                                <button type="button" class="password-toggle" data-toggle-password="login-password" aria-label="Zobraziť heslo" aria-pressed="false">
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
                            <label for="forgot-email">E-mail:</label>
                            <div class="input-with-icon email-icon">
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
</body>
<!-- Custom JS -->
<script src="/assets/js/login.js"></script>
</html>