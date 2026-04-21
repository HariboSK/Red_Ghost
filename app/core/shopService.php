<?php

class ShopService
{
    public static function collectProducts($conn): array
    {
        $featuredProducts = [];
        $otherProducts = [];
        $allProducts = [];

        if ($conn instanceof PDO) {
            $hasDiscountPercent = self::hasDiscountPercentColumn($conn);
            $sql = $hasDiscountPercent
                ? 'SELECT id, name, description, price, discount_percent, rating, featured, stock, image FROM products ORDER BY featured DESC, id ASC'
                : 'SELECT id, name, description, price, rating, featured, stock, image FROM products ORDER BY featured DESC, id ASC';

            $stmt = $conn->query($sql);

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
            $image = asset('images/omacka3.jpg');
        }

        $price = (float) ($row['price'] ?? 0);
        $discountPercent = max(0, min(100, (float) ($row['discount_percent'] ?? 0)));
        $finalPrice = $discountPercent > 0 ? max(0, $price * (1 - ($discountPercent / 100))) : $price;

        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'image' => $image,
            'price' => $finalPrice,
            'basePrice' => $price,
            'discountPercent' => $discountPercent,
            'rating' => (int) ($row['rating'] ?? 4),
            'featured' => (int) ($row['featured'] ?? 0) === 1,
            'stock' => (int) ($row['stock'] ?? 0),
        ];
    }

    private static function defaultNewProducts(): array
    {
        return [
            [
                'name' => 'Nove chilli produkty',
                'description' => 'Specialne chilli omacky pripravene s laskou od nasich pestovatelov.',
                'image' => asset('images/chilli-sol.jpg'),
                'price' => 0,
                'stock' => 10,
                'id' => 1,
            ],
            [
                'name' => 'Domace omacky',
                'description' => 'Autenticke receptury tradicnej slovenskej kuchyne s chilli.',
                'image' => asset('images/omacky2.jpg'),
                'price' => 0,
                'stock' => 10,
                'id' => 2,
            ],
            [
                'name' => 'Susene chilli',
                'description' => 'Prirodne susene chilli papriky bez chemickych pridatkov.',
                'image' => asset('images/susene-chilli-Picsart-AiImageEnhancer.jpg'),
                'price' => 0,
                'stock' => 10,
                'id' => 3,
            ],
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
        if (!($conn instanceof PDO) || $productId <= 0) {
            return null;
        }

        $hasDiscountPercent = self::hasDiscountPercentColumn($conn);
        $sql = $hasDiscountPercent
            ? 'SELECT id, name, description, image, price, discount_percent, rating, stock, category FROM products WHERE id = :id LIMIT 1'
            : 'SELECT id, name, description, image, price, rating, stock, category FROM products WHERE id = :id LIMIT 1';

        $stmt = $conn->prepare($sql);
        if (!($stmt instanceof PDOStatement)) {
            return null;
        }

        $stmt->execute(['id' => $productId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            return null;
        }

        $image = (string) ($row['image'] ?? '');
        if ($image === '') {
            $image = asset('images/omacka3.jpg');
        }

        $price = (float) ($row['price'] ?? 0);
        $discountPercent = max(0, min(100, (float) ($row['discount_percent'] ?? 0)));
        $finalPrice = $discountPercent > 0 ? max(0, $price * (1 - ($discountPercent / 100))) : $price;

        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? 'Produkt'),
            'description' => (string) ($row['description'] ?? ''),
            'image' => $image,
            'price' => $finalPrice,
            'basePrice' => $price,
            'discountPercent' => $discountPercent,
            'rating' => max(0, min(5, (int) ($row['rating'] ?? 0))),
            'stock' => (int) ($row['stock'] ?? 0),
            'category' => (string) ($row['category'] ?? 'Chilli produkt'),
        ];
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

    private static function hasDiscountPercentColumn(PDO $conn): bool
    {
        try {
            $stmt = $conn->query("SHOW COLUMNS FROM products LIKE 'discount_percent'");
            if (!($stmt instanceof PDOStatement)) {
                return false;
            }

            return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            return false;
        }
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