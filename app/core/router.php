<?php

//ROOTY
class Router
{
    private $routes = [];
    private static $page_assets = [
        '/' => ['style.css', 'animation.css'],
        '/home' => ['style.css', 'animation.css'],
        '/home.php' => ['style.css', 'animation.css'],
        '/e-shop' => ['style2.css', ],
        '/e_shop' => ['style2.css'],
        '/e_shop.php' => ['style2.css'],
        '/e-shop.php' => ['style2.css'],
        '/product' => ['style2.css', 'productview.css'],
        '/product.php' => ['style2.css', 'productview.css'],
        '/shopcart' => ['style2.css', 'shopcart.css'],
        '/shopcart.php' => ['style2.css', 'shopcart.css'],
        '/dashboard' => ['style.css','dashboard.css'],
        '/dashboard.php' => ['style.css','dashboard.css'],
        '/userprofile' => ['style.css','userprofile.css'],
        '/userprofile.php' => ['style.css','userprofile.css'],
        '/login.php' => ['style2.css', 'login.css'],
        '/login' => ['style2.css', 'login.css'],
        '/404' => ['errors.css', 'style.css'],
        '/404.php' => ['errors.css', 'style.css'],
        '/error500' => ['errors.css', 'style.css'],
        '/error500.php' => ['errors.css', 'style.css'],
    ];

    public function __construct()
    {
        $this->routes = [
            '/' => 'app/views/home.php',
            '/home' => 'app/views/home.php',
            '/home.php' => 'app/views/home.php',
            '/e-shop' => 'app/views/e_shop.php',
            '/e_shop' => 'app/views/e_shop.php',
            '/e_shop.php' => 'app/views/e_shop.php',
            '/e-shop.php' => 'app/views/e_shop.php',
            '/product' => 'app/views/product.php',
            '/product.php' => 'app/views/product.php',
            '/shopcart' => 'app/views/shopcart.php',
            '/shopcart.php' => 'app/views/shopcart.php',
            '/dashboard' => 'app/views/dashboard.php',
            '/dashboard.php' => 'app/views/dashboard.php',
            '/userprofile' => 'app/views/userprofile.php',
            '/userprofile.php' => 'app/views/userprofile.php',
            '/login.php' => 'app/views/login.php',
            '/login' => 'app/views/login.php',
            '/login-register' => 'app/core/login_register.php',
            '/login-register.php' => 'app/core/login_register.php',
            '/login_register' => 'app/core/login_register.php',
            '/login_register.php' => 'app/core/login_register.php',
            '/send-message' => 'app/core/mail.php',
            '/send-message.php' => 'app/core/mail.php',
            '/send_message' => 'app/core/mail.php',
            '/send_message.php' => 'app/core/mail.php',
            '/api/cart' => 'app/core/api/cart.php',
            '/api/cart.php' => 'app/core/api/cart.php',
            '/logout' => 'app/views/logout.php'
        ];

    }

    public static function assetsFor(string $path): array
    {
        $basePath = function_exists('app_base_path') ? app_base_path() : '';
        $normalizedPath = parse_url((string) $path, PHP_URL_PATH);
        $normalizedPath = is_string($normalizedPath) ? $normalizedPath : '/';

        if ($basePath !== '' && strpos($normalizedPath, $basePath) === 0) {
            $normalizedPath = substr($normalizedPath, strlen($basePath));
        }

        $normalizedPath = '/' . ltrim($normalizedPath, '/');
        $normalizedPath = rtrim($normalizedPath, '/');
        $normalizedPath = ($normalizedPath === '') ? '/' : $normalizedPath;

        if (isset(self::$page_assets[$normalizedPath])) {
            return self::$page_assets[$normalizedPath];
        }

        $statusCode = http_response_code();
        if ($statusCode === 404 && isset(self::$page_assets['/404'])) {
            return self::$page_assets['/404'];
        }

        if ($statusCode === 500 && isset(self::$page_assets['/error500'])) {
            return self::$page_assets['/error500'];
        }

        return [];
    }

    public function dispatch()
    {
        $urlPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $urlPath = is_string($urlPath) ? $urlPath : '/';

        $basePath = function_exists('app_base_path') ? app_base_path() : '';
        if ($basePath !== '' && strpos($urlPath, $basePath) === 0) {
            $urlPath = substr($urlPath, strlen($basePath));
        }

        $url = '/' . ltrim($urlPath, '/');
        $url = rtrim($url, '/');
        $url = ($url === '') ? '/' : $url;

        if (isset($this->routes[$url])) {
            $projectRoot = dirname(__DIR__, 2);
            $viewPath = $projectRoot . '/' . $this->routes[$url];
            if (file_exists($viewPath)) {
                include $viewPath;
                return;
            }
        }

        http_response_code(404);
        $projectRoot = dirname(__DIR__, 2);
        $notFoundViewPath = $projectRoot . '/app/views/404.php';

        if (file_exists($notFoundViewPath)) {
            include $notFoundViewPath;
            return;
        }

        echo '404 - Stranka nenajdena';
        exit;
    }

    // helper to generate normalized application URLs
    public static function url($path)
    {
        $basePath = function_exists('app_base_path') ? app_base_path() : '';
        $normalizedPath = '/' . ltrim((string) $path, '/');

        return htmlspecialchars($basePath . $normalizedPath, ENT_QUOTES, 'UTF-8');
    }
}