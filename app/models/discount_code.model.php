<?php

require_once __DIR__ . '/base.model.php';

class DiscountCodeModel extends BaseModel
{
    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT id, code, title, discount_type, discount_value, min_order_total, usage_limit, used_count, is_active, starts_at, ends_at, created_at FROM discount_codes ORDER BY id DESC');
        $rows = $stmt instanceof PDOStatement ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        return is_array($rows) ? $rows : [];
    }

    public function create(array $data): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO discount_codes (code, title, discount_type, discount_value, min_order_total, usage_limit, used_count, is_active, starts_at, ends_at) VALUES (:code, :title, :discount_type, :discount_value, :min_order_total, :usage_limit, :used_count, :is_active, :starts_at, :ends_at)'
        );

        $stmt->execute([
            ':code' => $data['code'],
            ':title' => $data['title'],
            ':discount_type' => $data['discount_type'],
            ':discount_value' => $data['discount_value'],
            ':min_order_total' => $data['min_order_total'],
            ':usage_limit' => $data['usage_limit'],
            ':used_count' => 0,
            ':is_active' => $data['is_active'],
            ':starts_at' => $data['starts_at'],
            ':ends_at' => $data['ends_at'],
        ]);
    }

    public function delete(int $discountCodeId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM discount_codes WHERE id = :id');
        $stmt->execute([':id' => $discountCodeId]);
    }
}
