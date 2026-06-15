<?php
declare(strict_types=1);

class Helper
{
    public static function getPageTitle(): string
    {
        $uri = strtolower($_SERVER['REQUEST_URI'] ?? '');

        if (strpos($uri, 'shopcart') !== false) return 'Red Ghost - Košík';
        if (strpos($uri, 'e_shop') !== false) return isset($_GET['product']) ? 'E-shop - Produkt' : 'E-shop';
        if (strpos($uri, 'dashboard') !== false) return 'Red Ghost - Admin Dashboard';
        if (strpos($uri, 'userprofile') !== false) return 'Red Ghost - Profil';
        if (strpos($uri, 'login') !== false) return 'Red Ghost - Prihlásenie';
        
        return 'Red Ghost';
    }
}