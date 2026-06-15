<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class ProductReviewModel extends BaseModel
{
    public function getApprovedByProduct(int $productId): array
    {
        if ($productId <= 0) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT pr.id_review AS id,
                    pr.rating,
                    pr.title,
                    pr.content,
                    pr.created_at,
                    COALESCE(u.name, "Zákazník") AS reviewer_name
             FROM product_review pr
             LEFT JOIN `user` u ON u.id = pr.id_user
             WHERE pr.id_product = :id_product
               AND pr.status = "approved"
             ORDER BY pr.created_at DESC, pr.id_review DESC'
        );

        $stmt->execute([':id_product' => $productId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function getSummary(int $productId): array
    {
        if ($productId <= 0) {
            return ['count' => 0, 'average' => 0.0];
        }

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) AS review_count,
                    COALESCE(ROUND(AVG(rating), 1), 0) AS average_rating
             FROM product_review
             WHERE id_product = :id_product
               AND status = "approved"'
        );

        $stmt->execute([':id_product' => $productId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'count' => (int) ($row['review_count'] ?? 0),
            'average' => (float) ($row['average_rating'] ?? 0),
        ];
    }

    public function getUserReview(int $productId, int $userId): ?array
    {
        if ($productId <= 0 || $userId <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT id_review AS id,
                    rating,
                    title,
                    content,
                    status
             FROM product_review
             WHERE id_product = :id_product
               AND id_user = :id_user
             LIMIT 1'
        );

        $stmt->execute([
            ':id_product' => $productId,
            ':id_user' => $userId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    // PRIDANÝ PARAMETER $autoApprove NA KONIEC FUNKCIE
    public function saveReview(int $productId, int $userId, int $rating, string $title, string $content, bool $verifiedPurchase = false, ?int $orderItemId = null, bool $autoApprove = false): bool
    {
        if ($productId <= 0 || $userId <= 0) {
            return false;
        }

        // Dynamická kontrola statusu podľa zaslaného parametra
        $statusValue = $autoApprove ? 'approved' : 'pending';

        $existing = $this->getUserReview($productId, $userId);

        if (is_array($existing)) {
            $stmt = $this->pdo->prepare(
                'UPDATE product_review
                 SET rating = :rating,
                     title = :title,
                     content = :content,
                     status = :status,
                     is_verified_purchase = :verified,
                     id_order_item = :order_item,
                     updated_at = NOW()
                 WHERE id_review = :id_review'
            );

            return (bool) $stmt->execute([
                ':rating' => $rating,
                ':title' => $title,
                ':content' => $content,
                ':status' => $statusValue,
                ':verified' => $verifiedPurchase ? 1 : 0,
                ':order_item' => $orderItemId,
                ':id_review' => (int) ($existing['id'] ?? 0),
            ]);
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO product_review (
                rating,
                title,
                content,
                status,
                is_verified_purchase,
                created_at,
                updated_at,
                id_product,
                id_user,
                id_order_item
             ) VALUES (
                :rating,
                :title,
                :content,
                :status,
                :verified,
                NOW(),
                NULL,
                :id_product,
                :id_user,
                :order_item
             )'
        );

        return (bool) $stmt->execute([
            ':rating' => $rating,
            ':title' => $title,
            ':content' => $content,
            ':status' => $statusValue,
            ':verified' => $verifiedPurchase ? 1 : 0,
            ':id_product' => $productId,
            ':id_user' => $userId,
            ':order_item' => $orderItemId,
        ]);
    }
}