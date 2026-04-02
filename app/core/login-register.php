<?php

session_start();
require_once dirname(__DIR__, 2) . '/config/config.php';

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $passwordRaw = $_POST['password'] ?? '';
    $repeatPassword = $_POST['repeat-password'] ?? '';
    $role = 'user';

    if ($passwordRaw !== $repeatPassword) {
        $_SESSION["register_error"] = 'Heslá sa nezhodujú';
        $_SESSION['active_form'] = 'register';
        header("Location: /login.php");
        exit();
    }

    $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

    $checkEmail = $conn->query("SELECT email FROM users WHERE email = '$email'");
    if ($checkEmail->num_rows > 0) {
        $_SESSION["register_error"] = 'E-mail už existuje';
        $_SESSION['active_form'] = 'register';
    } else {
        $conn->query("INSERT INTO users (name, email, password, role) 
        VALUES ('$name', '$email', '$password', '$role')");
    }

    header("Location: /login.php");
    exit();
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];

            if ($user['role'] === 'admin') {
                header("Location: /dashboard.php");
            } else {
                header("Location: /userprofile.php");
            }
            exit();
        }
    }

    $_SESSION["login_error"] = 'Neplatný e-mail alebo heslo';
    $_SESSION['active_form'] = 'login';
    header("Location: /login.php");
    exit();
}

?>