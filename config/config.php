<?php

class Database
{
    private string $host = 'localhost';
    private string $dbname = 'red_ghost';
    private string $username = 'root';
    private string $password = '';

    private PDO $connection;

    public function __construct()
    {
        $this->connect();
    }

    private function connect(): void
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";

        $this->connection = new PDO($dsn, $this->username, $this->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}

try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (PDOException $e) {
    if (function_exists('app_render_friendly_error')) {
        app_render_friendly_error("Databáza nie je dostupná. Pravdepodobne nie je zapnutá.\nChybová správa: " . $e->getMessage());
    }

    die('Chyba: Databáza nie je dostupná. Pravdepodobne nie je zapnutá. Chybová správa: ' . $e->getMessage());
}

date_default_timezone_set('Europe/Bratislava');