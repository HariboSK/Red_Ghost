<?php

//ROOTY
class Router
{
    private $routes = [];

    public function __construct()
    {
        $this->routes = [
            '/' => 'app/views/partials/home.php',
            '/home' => 'app/views/partials/home.php',
            '/home.php' => 'app/views/partials/home.php',
            '/e-shop' => 'app/views/partials/e_shop.php',
            '/e_shop' => 'app/views/partials/e_shop.php',
            '/e_shop.php' => 'app/views/partials/e_shop.php',
            '/e-shop.php' => 'app/views/partials/e_shop.php',
            '/shopcart' => 'app/views/partials/shopcart.php',
            '/shopcart.php' => 'app/views/partials/shopcart.php',
            '/dashboard' => 'app/views/partials/dashboard.php',
            '/dashboard.php' => 'app/views/partials/dashboard.php',
            '/userprofile' => 'app/views/partials/userprofile.php',
            '/userprofile.php' => 'app/views/partials/userprofile.php',
            '/login.php' => 'app/views/partials/login.php',
            '/login' => 'app/views/partials/login.php',
            '/logout' => 'app/views/partials/logout.php'
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
        $notFoundViewPath = $projectRoot . '/app/views/partials/404.php';

        if (file_exists($notFoundViewPath)) {
            include $notFoundViewPath;
            return;
        }

        echo '404 - Stranka nenajdena';
        exit;
    }
}