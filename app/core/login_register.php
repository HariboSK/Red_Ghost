<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once __DIR__ . '/session_helper.php';
require_once __DIR__ . '/Redirect.php';

class LoginRegister
{
    private PDO $conn;

    public function __construct($dbConnection)
    {
        if (!($dbConnection instanceof PDO)) {
            throw new RuntimeException('Neplatne DB pripojenie v LoginRegister.');
        }

        $this->conn = $dbConnection;
    }

    public function handleRequest(): void
    {
        if (isset($_POST['register'])) {
            $this->handleRegister();
            return;
        }

        if (isset($_POST['login'])) {
            $this->handleLogin();
            return;
        }
    }

    private function handleRegister(): void
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $passwordRaw = (string) ($_POST['password'] ?? '');
        $repeatPassword = (string) ($_POST['repeat-password'] ?? '');
        $role = 'user';

        if ($passwordRaw !== $repeatPassword) {
            $_SESSION['register_error'] = 'Hesla sa nezhoduju';
            $_SESSION['active_form'] = 'register';
            (new Redirect('/login.php'))->redirect();
        }

        $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

        $checkEmail = $this->conn->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
        $checkEmail->execute([':email' => $email]);

        if ($checkEmail->fetchColumn()) {
            $_SESSION['register_error'] = 'E-mail uz existuje';
            $_SESSION['active_form'] = 'register';
            (new Redirect('/login.php'))->redirect();
        }

        $insertUser = $this->conn->prepare('INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)');
        $insertUser->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $password,
            ':role' => $role,
        ]);

        (new Redirect('/login.php'))->redirect();
    }

    private function handleLogin(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $result = $this->conn->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $result->execute([':email' => $email]);
        $user = $result->fetch();

        if ($user && password_verify($password, (string) ($user['password'] ?? ''))) {
            SessionManager::regenerate(true);

            $points = isset($user['loayalty_points']) ? (int) $user['loayalty_points'] : 0;
            SessionHelper::storeUser($user, $points);

            if (($user['role'] ?? 'user') === 'admin') {
                (new Redirect('/dashboard.php'))->redirect();
            }

            (new Redirect('/userprofile.php'))->redirect();
        }

        $_SESSION['login_error'] = 'Neplatny e-mail alebo heslo';
        $_SESSION['active_form'] = 'login';
        (new Redirect('/login.php'))->redirect();
    }
}

$loginRegister = new LoginRegister($conn);
$loginRegister->handleRequest();

?>