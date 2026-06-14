<?php

class UserEditModule {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function updateProfile(int $userId, array $data): bool {
        try {
            $this->pdo->beginTransaction();

            // 1. Aktualizácia tabuľky `user`
            $stmt = $this->pdo->prepare('UPDATE `user` SET name = :name, telephone = :telephone WHERE id = :id');
            $stmt->execute([
                ':name'      => $data['name'],
                ':telephone' => $data['phone'] ?? null,
                ':id'        => $userId
            ]);

            // 2. Aktualizácia alebo vloženie do `address`
            $this->updateAddress($userId, $data);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Chyba pri aktualizácii profilu: " . $e->getMessage());
            return false;
        }
    }

    private function updateAddress(int $userId, array $data): void {
        // Skontrolujeme, či adresa existuje
        $stmt = $this->pdo->prepare('SELECT id_address FROM address WHERE id_user = :id_user');
        $stmt->execute([':id_user' => $userId]);
        
        if ($stmt->fetch()) {
            $stmt = $this->pdo->prepare('UPDATE address SET street = :street WHERE id_user = :id_user');
        } else {
            $stmt = $this->pdo->prepare('INSERT INTO address (street, id_user) VALUES (:street, :id_user)');
        }
        
        $stmt->execute([
            ':street' => $data['address'] ?? null,
            ':id_user' => $userId
        ]);
    }
}