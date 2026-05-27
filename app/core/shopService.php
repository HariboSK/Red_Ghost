<?php

class ShopService
{
    private static function normalizeImagePath(string $imagePath): string
    {
        $imagePath = trim($imagePath);

        if ($imagePath === '') {
            return asset('images/omacka3.webp');
        }

        if (preg_match('/\.(jpe?g)$/i', $imagePath) === 1) {
            $webpPath = preg_replace('/\.(jpe?g)$/i', '.webp', $imagePath);

            if (is_string($webpPath)) {
                $publicPath = dirname(__DIR__, 2) . '/public' . $webpPath;
                if (is_file($publicPath)) {
                    return $webpPath;
                }
            }
        }

        return $imagePath;
    }

    private static function defaultCatalog(): array
    {
        return [
            1 => [
                'id' => 1,
                'name' => 'Red Ghost Chilli Omacka',
                'description' => 'Vynikajúca chilli omáčka s bohatou chuťou a paprikovými nádychmi.',
                'image' => asset('images/omacka3.webp'),
                'price' => 12.99,
                'basePrice' => 12.99,
                'discountPercent' => 0,
                'rating' => 5,
                'featured' => true,
                'stock' => 15,
                'category' => 'Omáčky',
            ],
            2 => [
                'id' => 2,
                'name' => 'Domaca Chilli Pasta',
                'description' => 'Tradičná slovenská recepta s domácimi paprikami.',
                'image' => asset('images/omacky2.webp'),
                'price' => 8.99,
                'basePrice' => 8.99,
                'discountPercent' => 0,
                'rating' => 4,
                'featured' => true,
                'stock' => 20,
                'category' => 'Omáčky',
            ],
            3 => [
                'id' => 3,
                'name' => 'Susene Chilli Papriky',
                'description' => 'Prírodne sušené chilli papriky bez chemických prídavkov.',
                'image' => asset('images/susene-chilli-Picsart-AiImageEnhancer.webp'),
                'price' => 14.99,
                'basePrice' => 14.99,
                'discountPercent' => 0,
                'rating' => 4,
                'featured' => false,
                'stock' => 8,
                'category' => 'Chilli',
            ],
        ];
    }

    public static function collectProducts($conn): array
    {
        $featuredProducts = [];
        $otherProducts = [];
        $allProducts = [];

        if ($conn instanceof PDO) {
            $stmt = $conn->query(
                'SELECT p.id_product AS id,
                        p.name,
                        p.description,
                        p.price,
                        p.rating,
                        p.featured,
                        p.stock,
                        p.image,
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
                          p.image
                 ORDER BY p.featured DESC, p.id_product ASC'
            );

            $rows = ($stmt instanceof PDOStatement) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

            foreach ($rows as $row) {
                $product = self::mapProductRow((array) $row);

                if ($product['featured']) {
                    $featuredProducts[] = $product;
                } else {
                    $otherProducts[] = $product;
                }

                $allProducts[] = $product;
            }
        }

        if (count($featuredProducts) === 0 && count($otherProducts) > 0) {
            $take = min(3, count($otherProducts));
            $featuredProducts = array_slice($otherProducts, 0, $take);
            $otherProducts = array_slice($otherProducts, $take);
        }

        $newProducts = $allProducts;
        usort($newProducts, function (array $a, array $b): int {
            return (int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0);
        });
        $newProducts = array_slice($newProducts, 0, 4);

        if (count($newProducts) === 0) {
            $newProducts = self::defaultNewProducts();
        }

        return [
            'featuredProducts' => $featuredProducts,
            'otherProducts' => $otherProducts,
            'newProducts' => $newProducts,
        ];
    }

    private static function mapProductRow(array $row): array
    {
        $image = (string) ($row['image'] ?? '');
        if ($image === '') {
            $image = asset('images/omacka3.webp');
        }
        $image = self::normalizeImagePath($image);

        $price = (float) ($row['price'] ?? 0);

        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'image' => $image,
            'price' => $price,
            'basePrice' => $price,
            'discountPercent' => 0.0,
            'rating' => (int) ($row['rating'] ?? 4),
            'featured' => (int) ($row['featured'] ?? 0) === 1,
            'stock' => (int) ($row['stock'] ?? 0),
            'category' => (string) ($row['category'] ?? 'Chilli produkt'),
        ];
    }

    private static function defaultNewProducts(): array
    {
        $catalog = self::defaultCatalog();

        return [
            $catalog[1],
            $catalog[2],
            $catalog[3],
        ];
    }

    public static function renderProductCard(array $product): void
    {
        $productId = (int) ($product['id'] ?? 0);
        $name = (string) ($product['name'] ?? 'Produkt');
        $image = (string) ($product['image'] ?? '');
        $price = (float) ($product['price'] ?? 0);
        $basePrice = (float) ($product['basePrice'] ?? $price);
        $discountPercent = max(0, (float) ($product['discountPercent'] ?? 0));
        $rating = max(0, min(5, (int) ($product['rating'] ?? 4)));
        $stock = (int) ($product['stock'] ?? 0);
        $productUrl = function_exists('route') ? route('/product?id=' . $productId) : '/product?id=' . $productId;

        echo '<div class="col-4 product-card" data-id="' . $productId . '" data-price="' . $price . '" data-rating="' . $rating . '" data-stock="' . $stock . '" data-name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">';
        echo '<a class="product-link" href="' . htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') . '">';
        echo '<img src="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">';
        echo '<h4>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</h4>';
        echo '</a>';
        echo '<div class="rating">';
        for ($i = 0; $i < $rating; $i++) {
            echo '<i class="fa fa-star"></i>';
        }
        if ($rating < 5) {
            echo '<i class="far fa-star"></i>';
        }
        echo '</div>';
        if ($discountPercent > 0) {
            echo '<p class="price-example"><span class="price-old">' . number_format($basePrice, 2, '.', '') . '€</span> ' . number_format($price, 2, '.', '') . '€</p>';
            echo '<div class="discount-badge">-' . number_format($discountPercent, 0, '.', '') . '%</div>';
        } else {
            echo '<p class="price-example">' . number_format($price, 2, '.', '') . '€</p>';
        }
        echo '<div class="stock ' . ($stock > 0 ? 'in-stock' : 'out-of-stock') . '">' . ($stock > 0 ? 'Na sklade: ' . $stock . ' ks' : 'Vypredane') . '</div>';
        echo '<button class="add-to-cart" onclick="addToCart(' . $productId . ')" ' . ($stock <= 0 ? 'disabled' : '') . '>Pridat do kosika</button>';
        echo '</div>';
    }

    public static function getProductById($conn, int $productId): ?array
    {
        if ($productId <= 0) {
            return null;
        }

        if ($conn instanceof PDO) {
            $stmt = $conn->prepare(
                'SELECT p.id_product AS id,
                        p.name,
                        p.description,
                        p.image,
                        p.price,
                        p.rating,
                        p.stock,
                        MIN(c.name) AS category
                 FROM product p
                 LEFT JOIN product_category pc ON pc.id_product = p.id_product
                 LEFT JOIN category c ON c.id_category = pc.id_category
                 WHERE p.id_product = :id
                 GROUP BY p.id_product,
                          p.name,
                          p.description,
                          p.image,
                          p.price,
                          p.rating,
                          p.stock
                 LIMIT 1'
            );

            if ($stmt instanceof PDOStatement) {
                $stmt->execute([':id' => $productId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if (is_array($row)) {
                    $image = (string) ($row['image'] ?? '');
                    if ($image === '') {
                        $image = asset('images/omacka3.webp');
                    }
                    $image = self::normalizeImagePath($image);

                    $price = (float) ($row['price'] ?? 0);

                    return [
                        'id' => (int) ($row['id'] ?? 0),
                        'name' => (string) ($row['name'] ?? 'Produkt'),
                        'description' => (string) ($row['description'] ?? ''),
                        'image' => $image,
                        'price' => $price,
                        'basePrice' => $price,
                        'discountPercent' => 0.0,
                        'rating' => max(0, min(5, (int) ($row['rating'] ?? 0))),
                        'stock' => (int) ($row['stock'] ?? 0),
                        'category' => (string) ($row['category'] ?? 'Chilli produkt'),
                    ];
                }
            }
        }

        $catalog = self::defaultCatalog();

        if (isset($catalog[$productId])) {
            return $catalog[$productId];
        }

        return null;
    }

    public static function productSpicyLabel(int $rating): array
    {
        if ($rating <= 1) {
            return ['Jemna', 'mild'];
        }

        if ($rating <= 3) {
            return ['Stredna', 'medium'];
        }

        return ['Extremna', 'hot'];
    }

    public static function productReviews(array $product): array
    {
        $rating = max(1, min(5, (int) ($product['rating'] ?? 3)));

        return [
            [
                'name' => 'Marek K.',
                'title' => 'Vyborna chut aj palivost',
                'text' => 'Vyrazna chut paprik, dobra konzistencia a prijemny aftertaste. Urcite objednam znova.',
                'rating' => $rating,
                'date' => '12.03.2026',
            ],
            [
                'name' => 'Petra S.',
                'title' => 'Kvalitny produkt',
                'text' => 'Pouzivam do varenia aj ku grilovanemu masu. Chut je stabilna a produkt prisiel bez problemov.',
                'rating' => max(1, $rating - 1),
                'date' => '03.03.2026',
            ],
            [
                'name' => 'Roman T.',
                'title' => 'Presne to, co som hladal',
                'text' => 'Dobry pomer cena/kvalita. Ak mas rad chilli, tento produkt nesklame.',
                'rating' => $rating,
                'date' => '25.02.2026',
            ],
        ];
    }

    public static function renderMain(array $data): void
    {
        $featuredProducts = $data['featuredProducts'] ?? [];
        $otherProducts = $data['otherProducts'] ?? [];
        $newProducts = $data['newProducts'] ?? [];
        ?>
<main class="shop-main">
    <section class="shop-banner">
        <div class="swiper shopBannerSwiper">
            <div class="swiper-wrapper">
                <?php foreach ($newProducts as $bannerProduct): ?>
                    <div class="swiper-slide product-showcase">
                        <div class="diagonal-stripe diagonal-left"></div>
                        <div class="showcase-image">
                            <img src="<?php echo htmlspecialchars((string) ($bannerProduct['image'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) ($bannerProduct['name'] ?? 'Produkt'), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="showcase-info">
                            <span class="badge">Nové produkty</span>
                            <h2><?php echo htmlspecialchars((string) ($bannerProduct['name'] ?? 'Produkt'), ENT_QUOTES, 'UTF-8'); ?></h2>
                            <?php if ((float) ($bannerProduct['price'] ?? 0) > 0): ?>
                                <p class="price"><?php echo number_format((float) $bannerProduct['price'], 2, '.', ''); ?> €</p>
                            <?php endif; ?>
                            <?php if (!empty($bannerProduct['description'])): ?>
                                <p class="description"><?php echo htmlspecialchars((string) $bannerProduct['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>
                            <button class="add-to-cart-btn" onclick="addToCart(<?php echo (int) ($bannerProduct['id'] ?? 0); ?>)"><?php echo ((int) ($bannerProduct['stock'] ?? 0) > 0) ? 'Pridať do košíka' : 'Vypredané'; ?></button>
                        </div>
                        <div class="diagonal-stripe diagonal-right"></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-slide-button swiper-button-prev"></div>
            <div class="swiper-slide-button swiper-button-next"></div>
        </div>
    </section>

    <section class="shop-catalog">
        <aside class="shop-filter-panel" aria-label="Filter produktov">
            <h2>Filter produktov</h2>

            <div class="filter-field">
                <label for="priceFrom">Cena od</label>
                <select id="priceFrom" onchange="filterProducts()" aria-label="Cena od">
                    <option value="0">Všetky ceny</option>
                    <option value="5">Od 5 €</option>
                    <option value="10">Od 10 €</option>
                    <option value="15">Od 15 €</option>
                    <option value="20">Od 20 €</option>
                    <option value="30">Od 30 €</option>
                </select>
            </div>

            <div class="filter-field">
                <label for="sortBy">Zoradiť podľa</label>
                <select id="sortBy" onchange="sortProducts()" aria-label="Triedenie produktov">
                    <option value="price-asc">Cena: od najnižšej</option>
                    <option value="price-desc">Cena: od najvyššej</option>
                    <option value="rating">Podľa hodnotenia</option>
                    <option value="stock">Najviac skladom</option>
                </select>
            </div>
        </aside>

        <div class="shop-catalog-content">
            <div class="shop-sections">
                <h2 class="title">Odporúčané produkty</h2>
                <div class="row product-grid" id="featuredProductsContainer">
                    <?php foreach ($featuredProducts as $product) { self::renderProductCard($product); } ?>
                </div>

                <h2 class="title">Ostatné produkty</h2>
                <div class="row product-grid" id="productsContainer">
                    <?php foreach ($otherProducts as $product) { self::renderProductCard($product); } ?>
                </div>
            </div>
        </div>
    </section>
</main>
<?php
    }
}