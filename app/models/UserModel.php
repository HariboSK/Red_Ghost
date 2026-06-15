<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class UserModel extends BaseModel
{
    public function getAdmins(): array
    {

        $stmt = $this->pdo->prepare("SELECT id, 
                                    name, 
                                    email, 
                                    password, 
                                    role, 
                                    image, 
                                    telephone AS phone 
                                    FROM `user` 
                                    WHERE role = :role");
        $stmt->execute([':role' => 'admin']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function getRegistered(): array
    {
        $stmt = $this->pdo->query('SELECT id, 
                                telephone AS phone, 
                                name, email, 
                                loyalty_points AS loyalty_points, 
                                role FROM `user` 
                                ORDER BY id DESC 
                                LIMIT 50');
        $rows = $stmt instanceof PDOStatement ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        return is_array($rows) ? $rows : [];
    }

    public function changePassword(int $userId, string $hashedPassword): bool
    {
        $stmt = $this->pdo->prepare('UPDATE user SET password = ? WHERE id = ?');
        return $stmt->execute([$hashedPassword, $userId]);
    }

    public function getUserNameById(int $userId): ?string
    {
        $stmt = $this->pdo->prepare('SELECT name FROM user WHERE id = ?');
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['name'] ?? null;
    }
}
