<?php

if (!function_exists('rg_session_bootstrap')) {
    function rg_session_bootstrap(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}

if (!function_exists('rg_session_get_string')) {
    function rg_session_get_string(array $keys): string
    {
        foreach ($keys as $key) {
            if (isset($_SESSION[$key]) && is_string($_SESSION[$key]) && trim($_SESSION[$key]) !== '') {
                return trim((string) $_SESSION[$key]);
            }
        }
        return '';
    }
}

if (!function_exists('rg_session_get_int')) {
    function rg_session_get_int(array $keys): int
    {
        foreach ($keys as $key) {
            if (isset($_SESSION[$key]) && is_numeric($_SESSION[$key])) {
                return (int) $_SESSION[$key];
            }
        }
        return 0;
    }
}

if (!function_exists('rg_session_user')) {
    function rg_session_user(): array
    {
        rg_session_bootstrap();

        $email = rg_session_get_string(['user_email', 'email', 'userEmail', 'mail']);
        $name = rg_session_get_string(['user_name', 'name', 'username', 'full_name']);
        $role = rg_session_get_string(['user_role', 'role']);
        $id = rg_session_get_int(['user_id', 'id']);
        $points = rg_session_get_int(['user_points', 'loayalty_points', 'loyalty_points', 'points', 'loyaltyPoints']);
        $flag = (!empty($_SESSION['is_logged_in']) && $_SESSION['is_logged_in']) ||
            (!empty($_SESSION['logged_in']) && $_SESSION['logged_in']);

        $isLoggedIn = $flag || $email !== '' || $id > 0;

        return [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'points' => $points,
            'is_logged_in' => $isLoggedIn,
        ];
    }
}

if (!function_exists('rg_session_store_user')) {
    function rg_session_store_user(array $user, int $points = 0): void
    {
        rg_session_bootstrap();

        $id = (int) ($user['id'] ?? 0);
        $name = (string) ($user['name'] ?? '');
        $email = (string) ($user['email'] ?? '');
        $role = (string) ($user['role'] ?? 'user');

        // Canonical keys
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = $role;
        $_SESSION['user_points'] = $points;
        $_SESSION['is_logged_in'] = true;

        // Legacy compatibility keys used in existing views.
        $_SESSION['id'] = $id;
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['loayalty_points'] = $points;
        $_SESSION['loyalty_points'] = $points;
        $_SESSION['logged_in'] = true;
    }
}

if (!function_exists('rg_calculate_loyalty_points')) {
    function rg_calculate_loyalty_points($conn, string $email): int
    {
        if (!($conn instanceof PDO) || $email === '') {
            return 0;
        }

        $stmt = $conn->prepare('SELECT loayalty_points FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return (int) ($row['loayalty_points'] ?? 0);
    }
}

if (!function_exists('rg_refresh_session_points')) {
    function rg_refresh_session_points($conn, string $email): int
    {
        $points = rg_calculate_loyalty_points($conn, $email);

        $_SESSION['user_points'] = $points;
        $_SESSION['loayalty_points'] = $points;
        $_SESSION['loyalty_points'] = $points;
        $_SESSION['points'] = $points;

        return $points;
    }
}
