<?php
declare(strict_types=1);

class SessionManager
{
    public static function start(array $options = []): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        $defaults = [
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ];

        $opts = array_merge($defaults, $options);

        ini_set('session.use_strict_mode', '1');

        ini_set('session.cookie_httponly', $opts['httponly'] ? '1' : '0');
        ini_set('session.cookie_secure', $opts['secure'] ? '1' : '0');
        if (PHP_VERSION_ID >= 70300) {
            ini_set('session.cookie_samesite', $opts['samesite']);
        }

        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params([
                'lifetime' => $opts['lifetime'],
                'path' => $opts['path'],
                'domain' => $opts['domain'],
                'secure' => $opts['secure'],
                'httponly' => $opts['httponly'],
                'samesite' => $opts['samesite']
            ]);
        } else {
            session_set_cookie_params(
                $opts['lifetime'],
                $opts['path'] . ($opts['samesite'] ? ('; samesite=' . $opts['samesite']) : ''),
                $opts['domain'],
                $opts['secure'],
                $opts['httponly']
            );
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function regenerate(bool $deleteOld = true): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id($deleteOld);
        }
    }
}