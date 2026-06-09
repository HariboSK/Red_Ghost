<?php
declare(strict_types=1);

class Redirect
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function redirect(): void
    {
        header('Location: ' . $this->url);
        exit;
    }
}