<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once __DIR__ . '/../models/AvatarManager.php';
require_once dirname(__DIR__) . '/core/session_helper.php';

$sessionUser = SessionHelper::user();
$pdo = $conn ?? ($GLOBALS['conn'] ?? null);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])) {
    $avatarManager = new AvatarManager($pdo);
    $userId = $_SESSION['user_id'];

    if ($avatarManager->uploadAvatar($userId, $_FILES['avatar'])) {
        header("Location: profile.php?success=1");
        exit;
    } else {
        header("Location: profile.php?error=upload_failed");
        exit;
    }
}