<?php

//ROOTY
class Router
{
    private $routes = [];

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
}