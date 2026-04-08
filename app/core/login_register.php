<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once __DIR__ . '/session_helper.php';
require_once __DIR__ . '/Redirect.php';

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $passwordRaw = $_POST['password'] ?? '';
    $repeatPassword = $_POST['repeat-password'] ?? '';
    $role = 'user';

    if ($passwordRaw !== $repeatPassword) {
        $_SESSION["register_error"] = 'Heslá sa nezhodujú';
        $_SESSION['active_form'] = 'register';
        (new Redirect('/login.php'))->redirect();
    }

    $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

    $checkEmail = $conn->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
    $checkEmail->execute([':email' => $email]);

    if ($checkEmail->fetchColumn()) {
        $_SESSION["register_error"] = 'E-mail už existuje';
        $_SESSION['active_form'] = 'register';
    } else {
        $insertUser = $conn->prepare('INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)');
        $insertUser->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $password,
            ':role' => $role,
        ]);
    }

    (new Redirect('/login.php'))->redirect();
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $result->execute([':email' => $email]);
    $user = $result->fetch();

    if ($user && password_verify($password, $user['password'])) {
        SessionManager::regenerate(true);

        $points = isset($user['loayalty_points']) ? (int) $user['loayalty_points'] : 0;
        rg_session_store_user($user, $points);

        if ($user['role'] === 'admin') {
            (new Redirect('/dashboard.php'))->redirect();
        } else {
            (new Redirect('/userprofile.php'))->redirect();
        }
    }

    $_SESSION["login_error"] = 'Neplatný e-mail alebo heslo';
    $_SESSION['active_form'] = 'login';
    (new Redirect('/login.php'))->redirect();
}

?>