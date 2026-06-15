<?php
declare(strict_types=1);

class ShopService
{
    public function __construct(private PDO $conn)
    {
    }

    public function collectProducts(): array
    {
        $featuredProducts = [];
        $otherProducts = [];
        $allProducts = [];

        $stmt = $this->conn->query(
                            'SELECT p.id_product AS id,
                                    p.name,
                                    p.description,
                                    p.price,
                                    p.rating,
                                    p.featured,
                                    p.stock,
                                    p.image,
                                    p.discount,
                                    MIN(c.name) AS category
                            FROM product p
                            LEFT JOIN product_category pc ON pc.id_product = p.id_product
                            LEFT JOIN category c ON c.id_category = pc.id_category
                            GROUP BY p.id_product,
                                    p.name,
                                    p.description,
                                    p.price,
                                    p.rating,
                                    p.featured,
                                    p.stock,
                                    p.image,
                                    p.discount'
        );

        $rows = ($stmt instanceof PDOStatement) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        foreach ($rows as $row) {
            $product = $this->mapProductRow((array) $row);

            if ($product['featured']) {
                $featuredProducts[] = $product;
            } else {
                $otherProducts[] = $product;
            }

            $allProducts[] = $product;
        }

        if (count($featuredProducts) === 0 && count($otherProducts) > 0) {
            $take = min(3, count($otherProducts));
            $featuredProducts = array_slice($otherProducts, 0, $take);
            $otherProducts = array_slice($otherProducts, $take);
        }

        $newProducts = $allProducts;
        usort($newProducts, fn(array $a, array $b): int => (int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0));
        $newProducts = array_slice($newProducts, 0, 4);

        if (count($newProducts) === 0) {
            $newProducts = $this->defaultNewProducts();
        }

        return [
            'featuredProducts' => $featuredProducts,
            'otherProducts' => $otherProducts,
            'newProducts' => $newProducts,
        ];
    }

    private function mapProductRow(array $row): array
    {
        $image = (string) ($row['image'] ?? '');
        if ($image === '') {
            $image = asset('images/omacka3.webp');
        } else {
            $image = preg_replace('~\.(jpe?g)$~i', '.webp', $image);
        }

        $price = (float) ($row['price'] ?? 0);
        $discount = (float) ($row['discount'] ?? 0); 

        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'image' => $image,
            'price' => $price,
            'basePrice' => $price,
            'discount' => $discount,
            'rating' => (int) ($row['rating'] ?? 4),
            'featured' => (int) ($row['featured'] ?? 0) === 1,
            'stock' => (int) ($row['stock'] ?? 0),
            'category' => (string) ($row['category'] ?? 'Chilli produkt'),
        ];
    }
    private function defaultNewProducts(): array
    {
        return [
            [
                'name' => 'Nove chilli produkty',
                'description' => 'Specialne chilli omacky pripravene s laskou od nasich pestovatelov.',
                'image' => asset('images/chilli-sol.webp'),
                'price' => 0,
                'stock' => 10,
                'id' => 1,
            ],
        ];
    }

    public function getProductById(int $productId): ?array
    {
        if ($productId <= 0) {
            return null;
        }

        $stmt = $this->conn->prepare(
                    'SELECT p.id_product AS id,
                            p.name,
                            p.description,
                            p.image,
                            p.price,
                            p.discount,
                            p.rating,
                            p.stock,
                            MIN(c.name) AS category
                    FROM product p
                    LEFT JOIN product_category pc ON pc.id_product = p.id_product
                    LEFT JOIN category c ON c.id_category = pc.id_category
                    WHERE p.id_product = :id
                    GROUP BY p.id_product, p.name, p.description, p.image, p.price, p.discount, p.rating, p.stock
                    LIMIT 1'
        );

        if (!($stmt instanceof PDOStatement)) {
            return null;
        }

        $stmt->execute(['id' => $productId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($row)) {
            return null;
        }

        return $this->mapProductRow($row);
    }

    public function productSpicyLabel(int $rating): array
    {
        if ($rating <= 1) return ['Jemna', 'mild'];
        if ($rating <= 3) return ['Stredna', 'medium'];
        return ['Extremna', 'hot'];
    }

    public function productReviews(array $product): array
    {
        $rating = max(1, min(5, (int) ($product['rating'] ?? 3)));
        return [];
    }
}