<?php

require_once __DIR__ . '/base.model.php';

class ProductModel extends BaseModel
{
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT id, name, description, price, discount_percent, image, category, stock, featured, rating, created_at, updated_at FROM products ORDER BY id DESC');
        $rows = $stmt instanceof PDOStatement ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, description, price, discount_percent, image, category, stock, featured, rating, created_at, updated_at FROM products WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function create(array $data): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO products (name, description, price, discount_percent, image, category, stock, featured, rating) VALUES (:name, :description, :price, :discount_percent, :image, :category, :stock, :featured, :rating)'
        );

        $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':discount_percent' => $data['discount_percent'],
            ':image' => $data['image'],
            ':category' => $data['category'],
            ':stock' => $data['stock'],
            ':featured' => $data['featured'],
            ':rating' => $data['rating'],
        ]);
    }

    public function update(int $productId, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE products SET name = :name, description = :description, price = :price, discount_percent = :discount_percent, image = :image, category = :category, stock = :stock, featured = :featured, rating = :rating WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $productId,
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':discount_percent' => $data['discount_percent'],
            ':image' => $data['image'],
            ':category' => $data['category'],
            ':stock' => $data['stock'],
            ':featured' => $data['featured'],
            ':rating' => $data['rating'],
        ]);
    }

    public function delete(int $productId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM products WHERE id = :id');
        $stmt->execute([':id' => $productId]);
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
        $discountPercent = filter_var($post['discount_percent'] ?? 0, FILTER_VALIDATE_FLOAT);

        if ($name === '' || strlen($name) < 2) {
            return ['ok' => false, 'error' => 'Názov produktu je povinný.', 'payload' => []];
        }

        if ($price === false || $price === null || $price < 0) {
            return ['ok' => false, 'error' => 'Zadaj platnú cenu produktu.', 'payload' => []];
        }

        if ($stock === false || $stock === null || $stock < 0) {
            return ['ok' => false, 'error' => 'Zadaj platný počet skladom.', 'payload' => []];
        }

        if ($discountPercent === false || $discountPercent === null || $discountPercent < 0 || $discountPercent > 100) {
            return ['ok' => false, 'error' => 'Zľava produktu musí byť medzi 0 a 100.', 'payload' => []];
        }

        if ($category === '') {
            return ['ok' => false, 'error' => 'Kategória je povinná.', 'payload' => []];
        }

        return [
            'ok' => true,
            'error' => '',
            'payload' => [
                'name' => $name,
                'description' => $description,
                'price' => (float) $price,
                'discount_percent' => max(0, min(100, (float) $discountPercent)),
                'image' => $image,
                'category' => $category,
                'stock' => (int) $stock,
                'featured' => $featured,
                'rating' => max(1, min(5, (int) ($rating ?: 4))),
            ],
        ];
    }
}
