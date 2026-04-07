<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'red_ghost');

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $conn = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
catch(PDOException $e) {
    if(function_exists('app_render_friendly_error')) {
        app_render_friendly_error("Databáza nie je dostupná. Pravdepodobne nie je zapnutá.\nChybová správa: " . $e->getMessage());
    }
    die('Chyba: Databáza nie je dostupná. Pravdepodobne neni zapnuta. Chybová správa: ' . $e->getMessage());
}

date_default_timezone_set('Europe/Bratislava');

?>