<?php
declare(strict_types=1);

require_once dirname(__DIR__, 1) . '/App.php'; 
App::init();

header('Content-Type: application/json; charset=UTF-8');

// Vytiahnutie PDO pripojenia z GLOBALS
$pdo = $GLOBALS['conn'] ?? null;

if (!($pdo instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Databazove spojenie nie je dostupne']);
    exit;
}


$cartService = new CartService($pdo);

$action = $_GET['action'] ?? 'summary';

// Akcia 1: Rýchly sumár pre počítadlo
if ($action === 'summary') {
    echo json_encode([
        'success' => true,
        'summary' => $cartService->getSummary(),
    ]);
    exit;
}

// Akcia 2: Kompletný zoznam položiek
if ($action === 'list') {
    echo json_encode([
        'success' => true,
        'items' => $cartService->getItemsList(),
    ]);
    exit;
}

// Akcia 3: Vyčistenie košíka
if ($action === 'clear') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Metoda neni povolena']);
        exit;
    }

    $cartService->clear();
    echo json_encode([
        'success' => true,
        'summary' => $cartService->getSummary(),
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Neznamy alebo nepovoleny action']);