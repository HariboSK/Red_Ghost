<?php
declare(strict_types=1);

class AvatarManager {
    private $pdo;
    private $uploadDir = "uploads/avatars/";
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    private $maxSize = 2097152; // 2MB v bajtoch

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }


    public function uploadAvatar(int $userId, array $file): bool {
        $this->validateFile($file);

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = "avatar_" . $userId . "_" . time() . "." . $extension;
        $fullPath = $this->uploadDir . $newFileName;

        // Vytvorenie priečinka, ak neexistuje
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            return $this->updateUserImage($userId, $newFileName);
        }

        return false;
    }

    private function validateFile(array $file) {
        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new Exception("Nepodporovaný formát súboru.");
        }

        if ($file['size'] > $this->maxSize) {
            throw new Exception("Súbor je príliš veľký.");
        }
    }

    private function updateUserImage(int $userId, string $fileName): bool {
        $stmt = $this->pdo->prepare("UPDATE user SET image = ? WHERE id = ?");
        $success = $stmt->execute([$fileName, $userId]);

        if ($success) {
            $_SESSION['user']['image'] = $fileName;
        }

        return $success;
    }
}