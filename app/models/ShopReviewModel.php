<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class ShopReviewModel extends BaseModel
{
    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id_shop_review AS id,
                    reviewer_name,
                    rating,
                    review_text,
                    status,
                    created_at
             FROM shop_review
             ORDER BY created_at DESC, id_shop_review DESC'
        );

        $rows = $stmt instanceof PDOStatement ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        return is_array($rows) ? $rows : [];
    }

    public function approve(int $reviewId): bool
    {
        $this->pdo->beginTransaction();

        try {
            $reviewStmt = $this->pdo->prepare(
                'SELECT status, id_user
                 FROM shop_review
                 WHERE id_shop_review = :id
                 FOR UPDATE'
            );
            $reviewStmt->execute([':id' => $reviewId]);
            $review = $reviewStmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($review)) {
                $this->pdo->rollBack();
                return false;
            }

            if ((string) ($review['status'] ?? '') === 'approved') {
                $this->pdo->commit();
                return false;
            }

            $userId = filter_var($review['id_user'] ?? null, FILTER_VALIDATE_INT);
            if ($userId && $userId > 0) {
                $rewardStmt = $this->pdo->prepare(
                    'UPDATE user
                     SET loyalty_points = COALESCE(loyalty_points, 0) + :points
                     WHERE id = :id_user'
                );
                $rewardStmt->execute([
                    ':points' => 100,
                    ':id_user' => (int) $userId,
                ]);
            }

            $approveStmt = $this->pdo->prepare(
                'UPDATE shop_review
                 SET status = :status,
                     updated_at = NOW()
                 WHERE id_shop_review = :id'
            );

            $approveStmt->execute([
                ':status' => 'approved',
                ':id' => $reviewId,
            ]);

            $this->pdo->commit();
            return true;
        } catch (Throwable $throwable) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $throwable;
        }
    }

    // vytvorenie novej recenzie pre shop
    public function create(string $name, int $rating, string $text, ?int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO shop_review (reviewer_name, rating, review_text, status, id_user)
            VALUES (:reviewer_name, :rating, :review_text, :status, :id_user)'
        );

        return $stmt->execute([
            ':reviewer_name' => $name,
            ':rating'        => $rating,
            ':review_text'   => $text,
            ':status'        => 'pending',
            ':id_user'       => $userId,
        ]);
    }

    public function deleteReview(int $reviewId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM shop_review
             WHERE id_shop_review = :id'
        );

        return $stmt->execute([
            ':id' => $reviewId,
        ]);
    }

    // Vytiahne iba schválené recenzie obchodu pre zobrazenie na domovskej stránke
    public function getLatestApproved(int $limit = 8): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT reviewer_name, rating, review_text, created_at
             FROM shop_review
             WHERE status = :status
             ORDER BY created_at DESC, id_shop_review DESC
             LIMIT :limit'
        );

        $stmt->bindValue(':status', 'approved', PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    }

}