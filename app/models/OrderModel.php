<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class OrderModel extends BaseModel
{
    public static function statusOptions(): array
    {
        return ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
    }

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

    // Posledn pridane objednavky
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
                    o.delivery_method,
                    oa.street AS street,
                    p.payment_method,
                    p.status AS payment_status
                FROM `order` o
                LEFT JOIN payment p ON p.id_order = o.id_order
                LEFT JOIN order_address oa ON oa.id_order = o.id_order -- TU ZMEŇ INNER NA LEFT
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

    // Objednávky konkretneho uživatela
    public function getOrdersByUserId(int $userId, int $limit = 12): array
    {
        if ($userId <= 0) {
            return [];
        }

        $limit = max(1, min($limit, 50));
        $sql = "
            SELECT
                o.id_order,
                o.customer_name,
                o.customer_email,
                oa.street,
                o.total_price,
                o.status,
                o.created_at,
                p.payment_method,
                p.status AS payment_status
            FROM `order` o
            LEFT JOIN payment p ON p.id_order = o.id_order
            INNER JOIN order_address oa ON oa.id_order = o.id_order
            WHERE o.user_id = :user_id
            ORDER BY o.created_at DESC
            LIMIT {$limit}
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    }

    public function updateStatus(int $orderId, string $status): bool
    {
        if ($orderId <= 0 || !in_array($status, self::statusOptions(), true)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare('UPDATE `order` SET status = :status, updated_at = NOW() WHERE id_order = :id_order');
            $stmt->execute([
                ':status' => $status,
                ':id_order' => $orderId,
            ]);

            if ($stmt->rowCount() > 0) {
                $historyStmt = $this->pdo->prepare('INSERT INTO order_status_history (status, id_order) VALUES (:status, :id_order)');
                $historyStmt->execute([
                    ':status' => $status,
                    ':id_order' => $orderId,
                ]);
            }

            $this->pdo->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return false;
        }
    }
}
