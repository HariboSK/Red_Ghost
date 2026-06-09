<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once __DIR__ . '/Redirect.php';
require_once __DIR__ . '/../models/AvatarManager.php';
require_once dirname(__DIR__) . '/core/SessionHelper.php';

$sessionUser = SessionHelper::user();
$pdo = $conn ?? ($GLOBALS['conn'] ?? null);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])) {
    $avatarManager = new AvatarManager($pdo);
    $userId = $_SESSION['user_id'];

    try {
        if ($avatarManager->uploadAvatar($userId, $_FILES['avatar'])) {
            $_SESSION['image'] = $_SESSION['user']['image'] ?? ($_SESSION['image'] ?? '');
            $_SESSION['user']['image'] = $_SESSION['image'];
            unset($_SESSION['upload_error']);
            (new Redirect('/dashboard'))->redirect();
        }

        $_SESSION['upload_error'] = 'Avatar sa nepodarilo nahrať.';
        (new Redirect('/dashboard'))->redirect();
    } catch (Throwable $exception) {
        $_SESSION['upload_error'] = $exception->getMessage();
        (new Redirect('/dashboard'))->redirect();
    }
}