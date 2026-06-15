<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once __DIR__ . '/Redirect.php';
require_once __DIR__ . '/../models/AvatarManager.php';
require_once dirname(__DIR__) . '/core/SessionHelper.php';

$sessionUser = SessionHelper::user();
$pdo = $conn ?? ($GLOBALS['conn'] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    
    $allowedAvatarRedirects = [
            '/dashboard'        => true,
            '/dashboard.php'    => true,
            '/userprofile'      => true,
            '/userprofile.php'  => true,
        ];

    // 2. Načítanie cieľa z POST dát a jeho validácia
    $redirectTo = $_POST['redirect_to'] ?? '/dashboard';
    if (!isset($allowedAvatarRedirects[$redirectTo])) {
        $redirectTo = '/dashboard';
    }

    // Obrana pre prípad, že by session vypršala počas nahrávania
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId === null) {
        $_SESSION['upload_error'] = 'Relácia vypršala. Prihláste sa znova.';
        (new Redirect('/login.php'))->redirect();
        exit;
    }

    $avatarManager = new AvatarManager($pdo);

    try {
        if ($avatarManager->uploadAvatar($userId, $_FILES['avatar'])) {
            $_SESSION['image'] = $_SESSION['user']['image'] ?? ($_SESSION['image'] ?? '');
            $_SESSION['user']['image'] = $_SESSION['image'];
            unset($_SESSION['upload_error']);
            
            // Bezpečné presmerovanie na overenú stránku
            (new Redirect($redirectTo))->redirect();
            exit;
        }

        $_SESSION['upload_error'] = 'Avatar sa nepodarilo nahrať.';
        (new Redirect($redirectTo))->redirect();
        exit;
        
    } catch (Throwable $exception) {
        $_SESSION['upload_error'] = $exception->getMessage();
        (new Redirect($redirectTo))->redirect();
        exit;
    }
}