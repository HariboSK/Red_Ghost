<?php

require_once __DIR__ . '/base.model.php';

class OrderModel extends BaseModel
{
    public function getTodaySummary(): array
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) AS order_count, COALESCE(SUM(total_price), 0) AS revenue 
                                    FROM `order` 
                                    WHERE DATE(created_at) = CURDATE()');

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

    public function getRecentOrders(int $limit = 50): array
    {
        $limit = max(1, min($limit, 200));

        $sql = "
            SELECT
                o.id_order,
                o.customer_name,
                o.customer_email,
                o.customer_phone,
                o.total_price,
                o.status,
                o.created_at,
                p.payment_method,
                p.status AS payment_status
            FROM `order` o
            LEFT JOIN payment p ON p.id_order = o.id_order
            ORDER BY o.created_at DESC
            LIMIT {$limit}
        ";

        $stmt = $this->pdo->query($sql);
        if (!($stmt instanceof PDOStatement)) {
            return [];
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    }
}
