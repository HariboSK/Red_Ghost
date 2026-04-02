<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'red_ghost');

// Vytvorenie spojenia
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Kontrola spojenia
if ($conn->connect_error) {
    if (function_exists('app_render_friendly_error')) {
        app_render_friendly_error('Databáza nie je dostupná. Pravdepodobne nie je zapnutá. Chybová správa: ' . $conn->connect_error);
    } else {
        die('Chyba: Databáza nie je dostupná. Pravdepodobne neni zapnuta. Chybová správa: ' . $conn->connect_error);
    }
}

// Nastavenie kódovania na UTF-8
$conn->set_charset("utf8mb4");

// Predvolené časové pásmo
date_default_timezone_set('Europe/Bratislava');
?>