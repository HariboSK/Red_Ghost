<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class ProductModel extends BaseModel
{
    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT p.id_product AS id,
                    p.name,
                    p.description,
                    p.price,
                    p.discount,
                    p.image,
                    MIN(c.name) AS category,
                    p.stock,
                    p.featured,
                    p.rating,
                    p.created_at,
                    p.updated_at
             FROM product p
               LEFT JOIN product_category pc ON pc.id_product = p.id_product
               LEFT JOIN category c ON c.id_category = pc.id_category
               GROUP BY p.id_product,
                      p.name,
                      p.description,
                      p.price,
                      p.image,
                      p.stock,
                      p.featured,
                      p.rating,
                      p.created_at,
                      p.updated_at
             ORDER BY p.id_product DESC'
        );
        $rows = $stmt instanceof PDOStatement ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.id_product AS id,
                    p.name,
                    p.description,
                    p.price,
                    p.discount,
                    p.image,
                    MIN(c.name) AS category,
                    p.stock,
                    p.featured,
                    p.rating,
                    p.created_at,
                    p.updated_at
             FROM product p
               LEFT JOIN product_category pc ON pc.id_product = p.id_product
               LEFT JOIN category c ON c.id_category = pc.id_category
             WHERE p.id_product = :id
               GROUP BY p.id_product,
                      p.name,
                      p.description,
                      p.price,
                      p.image,
                      p.stock,
                      p.featured,
                      p.rating,
                      p.created_at,
                      p.updated_at
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function create(array $data): void
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                'INSERT INTO product (name, description, price, image, stock, featured, rating, discount)
                 VALUES (:name, :description, :price, :image, :stock, :featured, :rating, :discount)'
            );

            $stmt->execute([
                ':name' => $data['name'],
                ':description' => $data['description'],
                ':price' => $data['price'],
                ':image' => $data['image'],
                ':stock' => $data['stock'],
                ':featured' => $data['featured'],
                ':rating' => $data['rating'],
                ':discount' => $data['discount'] ?? 0,
            ]);

            $productId = (int) $this->pdo->lastInsertId();
            $this->syncProductCategory($productId, (string) ($data['category'] ?? ''));

            $this->pdo->commit();
        } catch (PDOException $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function update(int $productId, array $data): void
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                'UPDATE product
                 SET name = :name,
                     description = :description,
                     price = :price,
                     image = :image,
                     stock = :stock,
                     featured = :featured,
                     rating = :rating,
                     discount = :discount
                 WHERE id_product = :id'
            );

            $stmt->execute([
                ':id' => $productId,
                ':name' => $data['name'],
                ':description' => $data['description'],
                ':price' => $data['price'],
                ':image' => $data['image'],
                ':stock' => $data['stock'],
                ':featured' => $data['featured'],
                ':rating' => $data['rating'],
                ':discount' => $data['discount'] ?? 0,
            ]);

            $this->syncProductCategory($productId, (string) ($data['category'] ?? ''));

            $this->pdo->commit();
        } catch (PDOException $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function delete(int $productId): bool
    {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare('DELETE FROM product_category WHERE id_product = :id');
            $stmt->execute([':id' => $productId]);

            $stmt = $this->pdo->prepare('DELETE FROM product WHERE id_product = :id');
            $stmt->execute([':id' => $productId]);
            $this->pdo->commit();
            return true;
        } catch (PDOException $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    public static function validateAndBuildPayload(array $post): array
    {
        $name = trim((string) ($post['name'] ?? ''));
        $description = trim((string) ($post['description'] ?? ''));
        $price = filter_var($post['price'] ?? null, FILTER_VALIDATE_FLOAT);
        $stock = filter_var($post['stock'] ?? null, FILTER_VALIDATE_INT);
        $category = trim((string) ($post['category'] ?? ''));
        $image = trim((string) ($post['image'] ?? ''));
        $featured = isset($post['featured']) ? 1 : 0;
        $rating = filter_var($post['rating'] ?? 4, FILTER_VALIDATE_INT);
        $discount = filter_var($post['discount'] ?? 0, FILTER_VALIDATE_FLOAT);

        if ($name === '' || strlen($name) < 2) {
            return ['ok' => false, 'error' => 'Názov produktu je povinný.', 'payload' => []];
        }

        if ($price === false || $price === null || $price < 0) {
            return ['ok' => false, 'error' => 'Zadaj platnú cenu produktu.', 'payload' => []];
        }

        if ($stock === false || $stock === null || $stock < 0) {
            return ['ok' => false, 'error' => 'Zadaj platný počet skladom.', 'payload' => []];
        }

        if ($category === '') {
            return ['ok' => false, 'error' => 'Kategória je povinná.', 'payload' => []];
        }

        if ($discount === false || $discount === null || $discount < 0 || $discount > 100) {
            return ['ok' => false, 'error' => 'Zadaj platnú zľavu produktu (0-100%).', 'payload' => []];
        }

        return [
            'ok' => true,
            'error' => '',
            'payload' => [
                'name' => $name,
                'description' => $description,
                'price' => (float) $price,
                'image' => $image,
                'category' => $category,
                'stock' => (int) $stock,
                'featured' => $featured,
                'rating' => max(1, min(5, (int) ($rating ?: 4))),
                'discount' => filter_var($post['discount'] ?? 0, FILTER_VALIDATE_FLOAT) ?: 0,
            ],
        ];
    }

    private function syncProductCategory(int $productId, string $categoryName): void
    {
        $categoryName = trim($categoryName);
        if ($categoryName === '') {
            return;
        }

        $categoryId = $this->getOrCreateCategoryId($categoryName);
        if ($categoryId === null) {
            return;
        }

        $stmt = $this->pdo->prepare('DELETE FROM product_category WHERE id_product = :product_id');
        $stmt->execute([':product_id' => $productId]);

        $stmt = $this->pdo->prepare(
            'INSERT INTO product_category (id_product, id_category) VALUES (:product_id, :category_id)'
        );
        $stmt->execute([
            ':product_id' => $productId,
            ':category_id' => $categoryId,
        ]);
    }

    private function getOrCreateCategoryId(string $categoryName): ?int
    {
        $stmt = $this->pdo->prepare('SELECT id_category FROM category WHERE name = :name LIMIT 1');
        $stmt->execute([':name' => $categoryName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (is_array($row) && isset($row['id_category'])) {
            return (int) $row['id_category'];
        }

        $stmt = $this->pdo->prepare('INSERT INTO category (name) VALUES (:name)');
        $stmt->execute([':name' => $categoryName]);

        $id = (int) $this->pdo->lastInsertId();
        return $id > 0 ? $id : null;
    }
}