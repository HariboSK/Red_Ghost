<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class DiscountCodeModel extends BaseModel
{
    public function getAll(): array
    {
        if (!$this->hasDiscountCodesTable()) {
            return [];
        }

        $stmt = $this->pdo->query('SELECT id_discount_code AS id, code, description, discount_type, value, min_order_value, is_active, valid_from, valid_to, created_at FROM discount_code ORDER BY id_discount_code DESC');
        $rows = $stmt instanceof PDOStatement ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        return is_array($rows) ? $rows : [];
    }

    public function findByCode(string $code): ?array
    {
        if (!$this->hasDiscountCodesTable()) {
            return null;
        }

        $stmt = $this->pdo->prepare('SELECT * 
                                    FROM discount_code 
                                    WHERE LOWER(code) = LOWER(:code) 
                                    LIMIT 1');
        $stmt->execute([':code' => $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function create(array $data): void
    {
        if (!$this->hasDiscountCodesTable()) {
            throw new RuntimeException('Tabulka discount_code neexistuje.');
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO discount_code 
                        (code, 
                        description, 
                        discount_type, 
                        value, 
                        min_order_value, 
                        is_active, 
                        valid_from, 
                        valid_to)
             VALUES (:code, 
                    :description, 
                    :discount_type, 
                    :value, 
                    :min_order_value, 
                    :is_active, 
                    :valid_from, 
                    :valid_to)'
        );

        $stmt->execute([
            ':code' => $data['code'],
            ':description' => $data['description'],
            ':discount_type' => $data['discount_type'],
            ':value' => $data['value'],
            ':min_order_value' => $data['min_order_value'],
            ':is_active' => $data['is_active'],
            ':valid_from' => $data['valid_from'],
            ':valid_to' => $data['valid_to'],
        ]);
    }

    public function delete(int $discountCodeId): bool
    {
        if (!$this->hasDiscountCodesTable()) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare('DELETE FROM discount_code WHERE id_discount_code = :id');
            $stmt->execute([':id' => $discountCodeId]);
            $this->pdo->commit();
            return true;
        } catch (PDOException $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    private function hasDiscountCodesTable(): bool
    {
        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'discount_code'");
            if (!($stmt instanceof PDOStatement)) {
                return false;
            }

            return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            return false;
        }
    }
}
