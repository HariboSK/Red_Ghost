<?php

require_once __DIR__ . '/base.model.php';

class UserModel extends BaseModel
{
    public function getAdmins(): array
    {
        $stmt = $this->pdo->prepare('SELECT id, name AS username, password, created_at FROM `user` WHERE role = :role ORDER BY id DESC LIMIT 20');
        $stmt->execute([':role' => 'admin']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function getRegistered(): array
    {
        $stmt = $this->pdo->query('SELECT id, name, email, loyalty_points AS loyalty_points, role FROM `user` ORDER BY id DESC LIMIT 50');
        $rows = $stmt instanceof PDOStatement ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        return is_array($rows) ? $rows : [];
    }
}
