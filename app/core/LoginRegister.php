<?php
declare(strict_types=1);

require_once __DIR__ . '/SessionHelper.php';
require_once __DIR__ . '/Redirect.php';


$conn = (isset($conn) && $conn instanceof PDO)
    ? $conn
    : ((isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof PDO) ? $GLOBALS['conn'] : null);

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

    public function getOrGenerateResetCode(int $userId): string
    {
        $stmt = $this->conn->prepare('SELECT unique_reset_passwd FROM `user` WHERE id = ?');
        $stmt->execute([$userId]);
        $code = $stmt->fetchColumn();

        if (!$code) {
            $code = bin2hex(random_bytes(6));

            $update = $this->conn->prepare('UPDATE `user` SET unique_reset_passwd = ? WHERE id = ?');
            $update->execute([$code, $userId]);
        }

        return (string) $code;
    }

    private function handleRegister(): void
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $passwordRaw = (string) ($_POST['password'] ?? '');
        $repeatPassword = (string) ($_POST['repeat-password'] ?? '');
        $role = 'customer';

        $resetCode = bin2hex(random_bytes(6));

        $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

        $insertUser = $this->conn->prepare('
            INSERT INTO `user` (name, email, password, role, unique_reset_passwd) 
            VALUES (:name, :email, :password, :role, :unique_reset_passwd)
        ');
        
        $insertUser->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $password,
            ':role' => $role,
            ':unique_reset_passwd' => $resetCode,
        ]);

        (new Redirect('/login.php'))->redirect();
    }

    private function handleLogin(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $result = $this->conn->prepare('SELECT * FROM `user` WHERE email = :email LIMIT 1');
        $result->execute([':email' => $email]);
        $user = $result->fetch();

        if ($user && password_verify($password, (string) ($user['password'] ?? ''))) {
            
            // OPRAVA: Použitie natívnej, bezpečnej PHP funkcie namiesto chýbajúcej triedy
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }

            $points = isset($user['loyalty_points']) ? (int) $user['loyalty_points'] : 0;
            SessionHelper::storeUser($user, $points);

            if (($user['role'] ?? 'customer') === 'admin') {
                (new Redirect('/dashboard.php'))->redirect();
            }

            (new Redirect('/userprofile.php'))->redirect();
        }

        $_SESSION['login_error'] = 'Neplatny e-mail alebo heslo';
        $_SESSION['active_form'] = 'login';
        (new Redirect('/login.php'))->redirect();
    }
}

// Spustenie spracovania formulárov až po overení pripojenia a deklarácii triedy
if (!($conn instanceof PDO)) {
    if (function_exists('app_render_friendly_error')) {
        app_render_friendly_error('Databázové pripojenie nie je dostupné. Skúste neskôr.');
    }

    http_response_code(500);
    echo "Chyba aplikacie\nPrepacte, nieco sa pokazilo. Skuste to prosim znova o chvilu.";
    exit(1);
}

$loginRegister = new LoginRegister($conn);
$loginRegister->handleRequest();