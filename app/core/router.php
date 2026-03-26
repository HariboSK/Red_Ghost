<?php

//ROOTY
class Router {
    private $routes = [];

    public function __construct() {
        $this->routes = [
            '/' => 'app/views/partials/home.php',
            '/home' => 'app/views/partials/home.php',
            '/home.php' => 'app/views/partials/home.php',
            '/e-shop' => 'app/views/partials/e_shop.php',
            '/e_shop' => 'app/views/partials/e_shop.php',
            '/e_shop.php' => 'app/views/partials/e_shop.php',
            '/e-shop.php' => 'app/views/partials/e_shop.php',
        ];
    }

    public function dispatch() {
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
        echo "404 - Stránka nenájdená";
    }
}