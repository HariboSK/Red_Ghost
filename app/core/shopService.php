<?php
declare(strict_types=1);

class ShopService
{
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
        } else {
            $image = preg_replace('~\.(jpe?g)$~i', '.webp', $image);
        }

        $price = (float) ($row['price'] ?? 0);
        $discountPercent = 0.0;
        $finalPrice = $price;

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
            'category' => (string) ($row['category'] ?? 'Chilli produkt'),
        ];
    }

    private static function defaultNewProducts(): array
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
            [
                'name' => 'Domace omacky',
                'description' => 'Autenticke receptury tradicnej slovenskej kuchyne s chilli.',
                'image' => asset('images/omacky2.webp'),
                'price' => 0,
                'stock' => 10,
                'id' => 2,
            ],
            [
                'name' => 'Susene chilli',
                'description' => 'Prirodne susene chilli papriky bez chemickych pridatkov.',
                'image' => asset('images/susene-chilli-Picsart-AiImageEnhancer.webp'),
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
        $category = (string) ($product['category'] ?? 'Chilli produkt');
        $description = trim((string) ($product['description'] ?? ''));
        $productUrl = function_exists('route') ? route('/product?id=' . $productId) : '/product?id=' . $productId;
        $searchTerms = trim($name . ' ' . $category . ' ' . $description);

        echo '<div class="col-4 product-card" data-id="' . $productId . '" data-price="' . $price . '" data-base-price="' . $basePrice . '" data-rating="' . $rating . '" data-stock="' . $stock . '" data-category="' . htmlspecialchars($category, ENT_QUOTES, 'UTF-8') . '" data-name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" data-search="' . htmlspecialchars($searchTerms, ENT_QUOTES, 'UTF-8') . '">';
        echo '<a class="product-link" href="' . htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') . '">';
        echo '<img src="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">';
        echo '<h4>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</h4>';
        echo '</a>';
        echo '<p class="product-category">' . htmlspecialchars($category, ENT_QUOTES, 'UTF-8') . '</p>';
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
        
        // server-side add_to_cart form (refreshes page)
        $returnTo = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8');
        echo '<form method="POST" action="' . htmlspecialchars(route('/api/AddToCart.php'), ENT_QUOTES, 'UTF-8') . '" class="add_to_cart_form">';
        echo '<input type="hidden" name="id" value="' . $productId . '">';
        echo '<input type="hidden" name="return_to" value="' . $returnTo . '">';
        echo '<button type="submit" class="add_to_cart-btn" ' . ($stock <= 0 ? 'disabled' : '') . '>' . ($stock > 0 ? 'Pridať do košíka' : 'Vypredané') . '</button>';
        echo '</form>';
        echo '</div>';
    }

    public static function getProductById($conn, int $productId): ?array
    {
        if (!($conn instanceof PDO) || $productId <= 0) {
            return null;
        }

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
            $image = asset('images/omacka3.webp');
        } else {
            $image = preg_replace('~\.(jpe?g)$~i', '.webp', $image);
        }

        $price = (float) ($row['price'] ?? 0);
        $discountPercent = 0.0;
        $finalPrice = $price;

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

    public static function renderMain(array $data): void
    {
        $featuredProducts = $data['featuredProducts'] ?? [];
        $otherProducts = $data['otherProducts'] ?? [];
        $newProducts = $data['newProducts'] ?? [];
        $bannerProducts = [];
        $bannerAccents = ['#fa6e17', '#ff7a45', '#ff4d4d', '#ffb347'];
        $returnTo = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8');
        foreach (array_merge($featuredProducts, $newProducts) as $candidateProduct) {
            $candidateId = (int) ($candidateProduct['id'] ?? 0);
            if ($candidateId > 0 && isset($bannerProducts[$candidateId])) {
                continue;
            }

            if ($candidateId > 0) {
                $bannerProducts[$candidateId] = $candidateProduct;
                continue;
            }

            $bannerProducts[] = $candidateProduct;
        }

        if (empty($bannerProducts)) {
            $bannerProducts = $newProducts;
        } else {
            $bannerProducts = array_values($bannerProducts);
        }
        ?>
<main class="shop-main">
    <section class="shop-banner">
        <div class="swiper shopBannerSwiper">
            <div class="swiper-wrapper">
                <?php foreach ($bannerProducts as $index => $bannerProduct): ?>
                    <?php
                        $bannerId = (int) ($bannerProduct['id'] ?? 0);
                        $bannerName = (string) ($bannerProduct['name'] ?? 'Produkt');
                        $bannerImage = (string) ($bannerProduct['image'] ?? '');
                        if ($bannerImage === '') {
                            $bannerImage = asset('images/omacka3.webp');
                        }
                        $bannerPrice = (float) ($bannerProduct['price'] ?? 0);
                        $bannerDescription = trim((string) ($bannerProduct['description'] ?? ''));
                        $bannerStock = (int) ($bannerProduct['stock'] ?? 0);
                        $bannerCategory = (string) ($bannerProduct['category'] ?? 'Novinka');
                        $bannerUrl = $bannerId > 0 ? (function_exists('route') ? route('/product?id=' . $bannerId) : '/product?id=' . $bannerId) : '#';
                        $bannerAccent = $bannerAccents[$index % count($bannerAccents)];
                    ?>
                    <div class="swiper-slide shop-banner-slide" style="--banner-accent: <?php echo htmlspecialchars($bannerAccent, ENT_QUOTES, 'UTF-8'); ?>;">
                        <div class="shop-banner-copy">
                            <span class="shop-banner-kicker"><?php echo htmlspecialchars($bannerCategory, ENT_QUOTES, 'UTF-8'); ?></span>
                            <h2 class="shop-banner-title"><?php echo htmlspecialchars($bannerName, ENT_QUOTES, 'UTF-8'); ?></h2>
                            <?php if ($bannerDescription !== ''): ?>
                                <p class="shop-banner-description"><?php echo htmlspecialchars($bannerDescription, ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>
                            <div class="shop-banner-meta">
                                <?php if ($bannerPrice > 0): ?>
                                    <span class="shop-banner-price"><?php echo number_format($bannerPrice, 2, '.', ''); ?> EUR</span>
                                <?php endif; ?>
                                <span class="shop-banner-stock <?php echo $bannerStock > 0 ? 'is-available' : 'is-empty'; ?>">
                                    <?php echo $bannerStock > 0 ? 'Skladom ' . $bannerStock . ' ks' : 'Vypredané'; ?>
                                </span>
                            </div>
                            <div class="shop-banner-actions">
                                <a class="shop-banner-btn" href="<?php echo htmlspecialchars($bannerUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                    Zobraziť produkt
                                </a>
                                <form method="POST" action="<?php echo htmlspecialchars(route('/api/AddToCart.php'), ENT_QUOTES, 'UTF-8'); ?>" class="shop-banner-cart-form">
                                    <input type="hidden" name="id" value="<?php echo $bannerId; ?>">
                                    <input type="hidden" name="return_to" value="<?php echo $returnTo; ?>">
                                    <button type="submit" class="shop-banner-btn shop-banner-btn--ghost" <?php echo ($bannerStock <= 0 || $bannerId <= 0) ? 'disabled' : ''; ?>>
                                        <?php echo $bannerStock > 0 ? 'Pridať do košíka' : 'Vypredané'; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="shop-banner-visual" aria-hidden="true">
                            <div class="shop-banner-orb"></div>
                            <img src="<?php echo htmlspecialchars($bannerImage, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($bannerName, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
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

            <div class="filter-actions">
                <div class="shop-search-summary shop-search-summary--panel" id="shopSearchSummary" aria-live="polite">Napíš názov produktu a výsledky sa ukážu hneď.</div>
                <button type="button" class="shop-reset-filters" id="clearShopFilters">Zrušiť filtre</button>
            </div>
        </aside>

        <div class="shop-catalog-content">
            <div class="shop-empty-state" id="shopNoResults" hidden>
                <h3>Nenašli sa žiadne produkty</h3>
                <p>Skús iné slovo, nižšiu cenu alebo iné zoradenie.</p>
                <button type="button" class="shop-reset-filters shop-reset-filters--ghost" id="clearShopFiltersEmpty">Zrušiť filtre</button>
            </div>

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
