<?php

require_once __DIR__ . '/base.model.php';

class OrderModel extends BaseModel
{
    public function getTodaySummary(): array
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) AS order_count, COALESCE(SUM(total_price), 0) AS revenue FROM orders WHERE DATE(created_at) = CURDATE()');

        if (!($stmt instanceof PDOStatement)) {
            return [
                'order_count' => 0,
                'revenue' => 0.0,
            ];
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($row)) {
            return [
                'order_count' => 0,
                'revenue' => 0.0,
            ];
        }

        return [
            'order_count' => (int) ($row['order_count'] ?? 0),
            'revenue' => (float) ($row['revenue'] ?? 0),
        ];
    }
}
