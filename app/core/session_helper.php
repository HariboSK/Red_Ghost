<?php

class SessionHelper
{
    public static function bootstrap(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getString(array $keys): string
    {
        foreach ($keys as $key) {
            if (isset($_SESSION[$key]) && is_string($_SESSION[$key]) && trim($_SESSION[$key]) !== '') {
                return trim((string) $_SESSION[$key]);
            }
        }

        return '';
    }

    public static function getInt(array $keys): int
    {
        foreach ($keys as $key) {
            if (isset($_SESSION[$key]) && is_numeric($_SESSION[$key])) {
                return (int) $_SESSION[$key];
            }
        }

        return 0;
    }

    public static function user(): array
    {
        self::bootstrap();

        $email = self::getString(['user_email', 'email', 'userEmail', 'mail']);
        $name = self::getString(['user_name', 'name', 'username', 'full_name']);
        $image = self::getString(['image']);
        $role = self::getString(['user_role', 'role']);
        $id = self::getInt(['user_id', 'id']);
        $points = self::getInt(['loyalty_points']);
        $flag = (!empty($_SESSION['is_logged_in']) && $_SESSION['is_logged_in']) ||
            (!empty($_SESSION['logged_in']) && $_SESSION['logged_in']);

        $isLoggedIn = $flag || $email !== '' || $id > 0;

        return [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'image' => $image,
            'points' => $points,
            'is_logged_in' => $isLoggedIn,
        ];
    }

    public static function storeUser(array $user, int $points = 0): void
    {
        self::bootstrap();

        $id = (int) ($user['id'] ?? 0);
        $name = (string) ($user['name'] ?? '');
        $email = (string) ($user['email'] ?? '');
        $image = (string) ($user['image'] ?? '');
        $role = (string) ($user['role'] ?? 'user');

        $_SESSION['user_id'] = $id;
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['image'] = $image;
        $_SESSION['loayalty_points'] = $points;
        $_SESSION['logged_in'] = true;
    }

    public static function calculateLoyaltyPoints($conn, string $email): int
    {
        if (!($conn instanceof PDO) || $email === '') {
            return 0;
        }

        $stmt = $conn->prepare('SELECT loyalty_points FROM `user` WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return (int) ($row['loyalty_points'] ?? 0);
    }

    public static function refreshSessionPoints($conn, string $email): int
    {
        $points = self::calculateLoyaltyPoints($conn, $email);

        $_SESSION['loayalty_points'] = $points;

        return $points;
    }
}

// Backward-compatible wrappers for existing function-based calls.
if (!function_exists('rg_session_bootstrap')) {
    function rg_session_bootstrap(): void
    {
        SessionHelper::bootstrap();
    }
}

if (!function_exists('rg_session_get_string')) {
    function rg_session_get_string(array $keys): string
    {
        return SessionHelper::getString($keys);
    }
}

if (!function_exists('rg_session_get_int')) {
    function rg_session_get_int(array $keys): int
    {
        return SessionHelper::getInt($keys);
    }
}

if (!function_exists('rg_session_user')) {
    function rg_session_user(): array
    {
        return SessionHelper::user();
    }
}

if (!function_exists('rg_session_store_user')) {
    function rg_session_store_user(array $user, int $points = 0): void
    {
        SessionHelper::storeUser($user, $points);
    }
}

if (!function_exists('rg_calculate_loyalty_points')) {
    function rg_calculate_loyalty_points($conn, string $email): int
    {
        return SessionHelper::calculateLoyaltyPoints($conn, $email);
    }
}

if (!function_exists('rg_refresh_session_points')) {
    function rg_refresh_session_points($conn, string $email): int
    {
        return SessionHelper::refreshSessionPoints($conn, $email);
    }
}
