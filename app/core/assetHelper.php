<?php
declare(strict_types=1);

class AssetHelper
{
    public static function current_page_assets(): array
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $requestPath = parse_url((string) $requestUri, PHP_URL_PATH);
        $requestPath = is_string($requestPath) ? $requestPath : '/';

        if (function_exists('current_path')) {
            $requestPath = current_path();
        }

        return class_exists('Router') ? Router::assetsFor($requestPath) : [];
    }
}