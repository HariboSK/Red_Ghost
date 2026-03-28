<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Ghost Log-in</title>
    <!-- Pridanie odkazu na CSS súbor -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/login.css">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="./assets/images/305036798_501410268657650_7493754093765322046_n-modified.png">

</head>

<body class="login-page">

<main>

    <div class="login-page-container">
        <section class="auth-shell" id="authShell">
            <div class="auth-tabs" role="tablist" aria-label="Prepnutie formulara">
                <button type="button" class="auth-tab is-active" data-target="login" aria-selected="true">Prihlásenie</button>
                <button type="button" class="auth-tab" data-target="register" aria-selected="false">Registrovať sa</button>
                <span class="auth-tab-indicator" aria-hidden="true"></span>
            </div>

            <div class="auth-track" id="authTrack">
                <section class="auth-panel login-panel" aria-labelledby="loginTitle">
                    <h2 id="loginTitle">Prihlásenie</h2>
                    <p class="auth-helper">Nemáte účet ?<br><button type="button" class="auth-inline-switch" data-target="register">Registrovať sa</button></p>
                   
                    <form action="/login" method="POST" class="auth-form">
                        <label for="login-email">E-mail:</label>
                        <div class="input-with-icon email-icon">
                            <span class="input-icon" aria-hidden="true"></span>
                            <input type="email" id="login-email" name="email" placeholder="jano@mrkvicka.com" required>
                        </div>

                        <label for="login-password">Heslo:</label>
                        <div class="input-with-icon password-icon">
                            <span class="input-icon" aria-hidden="true"></span>
                            <input type="password" id="login-password" name="password" placeholder="Heslo123" required>
                            <button type="button" class="password-toggle" data-toggle-password="login-password" aria-label="Zobraziť heslo" aria-pressed="false">
                                <span class="password-toggle-icon" aria-hidden="true"></span>
                            </button>
                        </div>

                        <label for="login-password-repe">Zopakuj heslo:</label>
                        <div class="input-with-icon password-icon">
                            <span class="input-icon" aria-hidden="true"></span>
                            <input type="password" id="login-password-repe" name="repaet-password" placeholder="Heslo123" required>
                            <button type="button" class="password-toggle" data-toggle-password="login-password-repe" aria-label="Zobraziť heslo" aria-pressed="false">
                                <span class="password-toggle-icon" aria-hidden="true"></span>
                            </button>
                        </div>

                        <!--Zabudnute heslo link-->
                        <a class="forgot-password" href="<?php echo route('/forgot-password'); ?>">Zabudli ste heslo?</a>

                        <button type="submit" name="login" class="auth-submit">Prihlásiť sa</button>
                    </form>
                
                </section>

                <section class="auth-panel register-panel" aria-labelledby="registerTitle">
                    <h2 id="registerTitle">Registrácia</h2>
                    <p class="auth-helper">Už máte účet ?<br><button type="button" class="auth-inline-switch" data-target="login">Prihlásiť sa</button></p>
                    
                    <form action="/register" method="POST" class="auth-form">
                        <label for="register-meno">Meno:</label>
                        <div class="input-with-icon user-icon">
                            <span class="input-icon" aria-hidden="true"></span>
                            <input type="text" id="register-meno" name="meno" placeholder="Jano" required>
                        </div>

                        <label for="register-priezvisko">Priezvisko:</label>
                        <div class="input-with-icon user-icon">
                            <span class="input-icon" aria-hidden="true"></span>
                            <input type="text" id="register-priezvisko" name="priezvisko" placeholder="Mrkvicka" required>
                        </div>

                        <label for="register-email">E-mail:</label>
                        <div class="input-with-icon email-icon">
                            <span class="input-icon" aria-hidden="true"></span>
                            <input type="email" id="register-email" name="email" placeholder="jano@mrkvicka.com" required>
                        </div>

                        <label for="register-password">Heslo:</label>
                        <div class="input-with-icon password-icon">
                            <span class="input-icon" aria-hidden="true"></span>
                            <input type="password" id="register-password" name="password" placeholder="Heslo123" required>
                            <button type="button" class="password-toggle" data-toggle-password="register-password" aria-label="Zobraziť heslo" aria-pressed="false">
                                <span class="password-toggle-icon" aria-hidden="true"></span>
                            </button>
                        </div>

                        <button type="submit" name="register" class="auth-submit">Vytvoriť účet</button>
                    </form>
                
                </section>
            </div>
        </section>
    </div>


<!-- Custom JS -->
<script src="/assets/js/login.js"></script>

<!-- Footer section -->
<?php require __DIR__ . '/footer.php'; ?>