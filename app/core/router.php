<?php
declare(strict_types=1);

class Router
{
    private $routes = [];

    private static $page_scripts = [
        '/'          => ['animaciaScript.js'],
        '/home'      => ['animaciaScript.js'],
        '/home.php'  => ['animaciaScript.js'],
    ];

    private static $page_assets = [
        '/' => ['style.css', 'animation.css'],
        '/home' => ['style.css', 'animation.css'],
        '/home.php' => ['style.css', 'animation.css'],
        '/e-shop' => ['style2.css'],
        '/e_shop' => ['style2.css'],
        '/e_shop.php' => ['style2.css'],
        '/e-shop.php' => ['style2.css'],
        '/product' => ['style2.css', 'productview.css'],
        '/product.php' => ['style2.css', 'productview.css'],
        '/shopcart' => ['style2.css', 'shopcart.css'],
        '/shopcart.php' => ['style2.css', 'shopcart.css'],
        '/payment' => ['style2.css', 'payment.css'],
        '/payment.php' => ['style2.css', 'payment.css'],
        '/thank-you' => ['style2.css', 'thank_you.css'],
        '/thank-you.php' => ['style2.css', 'thank_you.css'],
        '/thank_you' => ['style2.css', 'thank_you.css'],
        '/thank_you.php' => ['style2.css', 'thank_you.css'],
        '/dashboard' => ['style.css','dashboard.css'],
        '/dashboard.php' => ['style.css','dashboard.css'],
        '/create' => ['style.css','dashboard.css'],
        '/create.php' => ['style.css','dashboard.css'],
        '/edit' => ['style.css','dashboard.css'],
        '/edit.php' => ['style.css','dashboard.css'],
        '/userprofile' => ['style.css','userprofile.css'],
        '/userprofile.php' => ['style.css','userprofile.css'],
        '/login.php' => ['style2.css', 'login.css'],
        '/login' => ['style2.css', 'login.css'],
        '/forgot-password' => ['style2.css', 'login.css'],
        '/forgot-password.php' => ['style2.css', 'login.css'],
        '/shop-review' => ['style.css', 'animation.css'],
        '/shop-review.php' => ['style.css', 'animation.css'],
        '/404' => ['errors.css', 'style.css'],
        '/404.php' => ['errors.css', 'style.css'],
        '/error500' => ['errors.css', 'style.css'],
        '/error500.php' => ['errors.css', 'style.css'],
        '/profile-edit' => ['style.css','userprofile.css'],
    ];

    public static function scriptsFor(string $path): array
    {
        $basePath = function_exists('app_base_path') ? app_base_path() : '';
        $normalizedPath = parse_url((string) $path, PHP_URL_PATH);
        $normalizedPath = is_string($normalizedPath) ? $normalizedPath : '/';

        if ($basePath !== '' && strpos($normalizedPath, $basePath) === 0) {
            $normalizedPath = substr($normalizedPath, strlen($basePath));
        }

        $normalizedPath = '/' . trim($normalizedPath, '/');
        $normalizedPath = ($normalizedPath === '') ? '/' : $normalizedPath;

        return self::$page_scripts[$normalizedPath] ?? [];
    }

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
            '/payment' => 'app/views/payment.php',
            '/payment.php' => 'app/views/payment.php',
            '/thank-you' => 'app/views/thank_you.php',
            '/thank-you.php' => 'app/views/thank_you.php',
            '/thank_you' => 'app/views/thank_you.php',
            '/thank_you.php' => 'app/views/thank_you.php',
            '/dashboard' => 'app/views/dashboard.php',
            '/dashboard.php' => 'app/views/dashboard.php',
            '/create' => 'app/views/create.php',
            '/create.php' => 'app/views/create.php',
            '/edit' => 'app/views/edit.php',
            '/edit.php' => 'app/views/edit.php',
            '/userprofile' => 'app/views/userprofile.php',
            '/userprofile.php' => 'app/views/userprofile.php',
            '/login.php' => 'app/views/login.php',
            '/login' => 'app/views/login.php',
            '/forgot-password' => 'app/views/forgot_password.php',
            '/forgot-password.php' => 'app/views/forgot_password.php',
            '/reset-password' => 'app/core/ResetPassword.php',
            '/reset-password.php' => 'app/core/ResetPassword.php',
            '/shop-review' => 'app/views/shop_review.php',
            '/shop-review.php' => 'app/views/shop_review.php',
            '/profile-edit' => 'app/views/profile-edit.php',
            '/profile-edit.php' => 'app/views/profile-edit.php',
            '/login-register' => 'app/core/LoginRegister.php',
            '/login-register.php' => 'app/core/LoginRegister.php',
            '/login_register' => 'app/core/LoginRegister.php',
            '/login_register.php' => 'app/core/LoginRegister.php',
            '/api/cart' => 'app/core/api/Cart.php',
            '/api/cart.php' => 'app/core/api/Cart.php',
            '/api/Cart' => 'app/core/api/Cart.php',
            '/api/Cart.php' => 'app/core/api/Cart.php',
            '/api/AddToCart' => 'app/core/api/AddToCart.php',
            '/api/AddToCart.php' => 'app/core/api/AddToCart.php',
            '/api/RemoveCart' => 'app/core/api/RemoveCart.php',
            '/api/RemoveCart.php' => 'app/core/api/RemoveCart.php',
            '/api/ApplyDiscount' => 'app/core/api/ApplyDiscount.php',
            '/api/ApplyDiscount.php' => 'app/core/api/ApplyDiscount.php',
            '/api/checkout' => 'app/core/api/Checkout.php',
            '/api/checkout.php' => 'app/core/api/Checkout.php',
            '/api/Checkout' => 'app/core/api/Checkout.php',
            '/api/Checkout.php' => 'app/core/api/Checkout.php',
            '/logout' => 'app/views/logout.php',
            '/UploadAvatar.php' => 'app/core/UploadAvatar.php',
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

        $normalizedPath = '/' . trim($normalizedPath, '/');
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

        $url = '/' . trim($urlPath, '/');
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

    public static function url($path)
    {
        $basePath = function_exists('app_base_path') ? app_base_path() : '';
        $normalizedPath = '/' . trim((string) $path, '/');

        return htmlspecialchars($basePath . $normalizedPath, ENT_QUOTES, 'UTF-8');
    }
}