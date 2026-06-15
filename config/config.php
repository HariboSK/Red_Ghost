<?php

class Database
{
    private const HOST = 'localhost';
    private const DBNAME = 'red_ghost';
    private const USERNAME = 'root';
    private const PASSWORD = '';

    private PDO $connection;

    public function __construct()
    {
        $this->connect();
    }

    private function connect(): void
    {
        $dsn = "mysql:host=" . self::HOST . ";dbname=" . self::DBNAME . ";charset=utf8mb4";

        $this->connection = new PDO($dsn, self::USERNAME, self::PASSWORD, [
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
    $GLOBALS['conn'] = $conn;
} catch (PDOException $e) {
    if (function_exists('app_render_friendly_error')) {
        app_render_friendly_error("Databáza nie je dostupná. Pravdepodobne nie je zapnutá.\nChybová správa: " . $e->getMessage());
    }

    die('Chyba: Databáza nie je dostupná. Pravdepodobne nie je zapnutá. Chybová správa: ' . $e->getMessage());
}

date_default_timezone_set('Europe/Bratislava');