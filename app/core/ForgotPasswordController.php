<?php
declare(strict_types=1);

class ForgotPasswordController
{
    private ResetPassword $resetService;
    private ?string $verifiedEmail = null;
    private string $notice = '';
    private string $error = '';

    public function __construct(PDO $db)
    {
        $this->resetService = new ResetPassword($db);
    }

    /**
     * Hlavná metóda, ktorá riadi životný cyklus stránky (POST aj GET).
     */
    public function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Načítanie správ zo session a ich okamžité zmazanie
        $this->notice = $_SESSION['forgot_password_notice'] ?? '';
        $this->error = $_SESSION['forgot_password_error'] ?? '';
        unset($_SESSION['forgot_password_notice'], $_SESSION['forgot_password_error']);

        // 2. Kontrola, či už užívateľ úspešne prešiel overením kódu
        $this->verifiedEmail = $_SESSION['allow_password_reset_for'] ?? null;

        // 3. Ak ide o POST, spracujeme formuláre
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processPost();
        }
    }

    /**
     * Spracovanie odoslaných formulárov (POST)
     */
    private function processPost(): void
    {
        // KROK 1: Overenie E-mailu a 12-miestneho kódu
        if (isset($_POST['email'], $_POST['recovery_code'])) {
            $email = trim((string)$_POST['email']);
            $code = trim((string)$_POST['recovery_code']);

            if ($email === '' || $code === '') {
                $this->redirectWithError('Vyplňte e-mail aj bezpečnostný kód.');
            }

            if ($this->resetService->verifyRecoveryCode($email, $code)) {
                $_SESSION['allow_password_reset_for'] = $email;
                $this->redirect();
            } else {
                $this->redirectWithError('Zadaný e-mail alebo bezpečnostný kód nie je správny.');
            }
        }

        // KROK 2: Spracovanie nového hesla
        if (isset($_POST['password']) && $this->verifiedEmail !== null) {
            $newPassword = (string)$_POST['password'];

            if (strlen($newPassword) < 6) {
                $this->redirectWithError('Heslo musí mať aspoň 6 znakov.');
            }

            $this->resetService->resetPassword($this->verifiedEmail, $newPassword);
            unset($_SESSION['allow_password_reset_for']);

            $_SESSION['login_success'] = 'Heslo bolo úspešne zmenené. Teraz sa môžeš prihlásiť.';
            header("Location: " . route('/login'));
            exit;
        }
    }

    private function redirectWithError(string $message): void
    {
        $_SESSION['forgot_password_error'] = $message;
        $this->redirect();
    }

    private function redirect(): void
    {
        header("Location: " . route('/forgot-password'));
        exit;
    }

    // Gettery pre zobrazenie (View)
    public function getNotice(): string { return $this->notice; }
    public function getError(): string { return $this->error; }
    public function getVerifiedEmail(): ?string { return $this->verifiedEmail; }
}