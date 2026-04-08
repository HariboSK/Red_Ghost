<?php

class Helper
{
    public static function getPageTitle(): string
    {
        $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
        $page = ucfirst(basename($script, '.php'));
        if (strtolower($page) === 'index') {
            $page = 'Home';
        }
        return 'Red Ghost - ' . $page;
    }
}