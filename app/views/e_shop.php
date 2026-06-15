<?php
// Zaistíme databázové spojenie
$conn = (isset($conn) && $conn instanceof PDO)
    ? $conn
    : ((isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof PDO) ? $GLOBALS['conn'] : null);

// Inicializácia OOP Služby a natiahnutie dát
$shopService = new ShopService($conn);
$shopData = $shopService->collectProducts();

$featuredProducts = $shopData['featuredProducts'] ?? [];
$otherProducts = $shopData['otherProducts'] ?? [];
$newProducts = $shopData['newProducts'] ?? [];

// Logika pre výber produktov do bannera (zabránenie duplicitám)
$bannerProductsMap = [];
foreach (array_merge($featuredProducts, $newProducts) as $candidateProduct) {
    $candidateId = (int) ($candidateProduct['id'] ?? 0);
    if ($candidateId > 0 && isset($bannerProductsMap[$candidateId])) {
        continue;
    }
    if ($candidateId > 0) {
        $bannerProductsMap[$candidateId] = $candidateProduct;
        continue;
    }
    $bannerProductsMap[] = $candidateProduct;
}

$bannerProducts = empty($bannerProductsMap) ? $newProducts : array_values($bannerProductsMap);
$bannerAccents = ['#fa6e17', '#ff7a45', '#ff4d4d', '#ffb347'];
$returnTo = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8');

include __DIR__ . '/partials/header-shop.php';


$renderProductCard = function(array $product) use ($returnTo) {
    $productId = (int) ($product['id'] ?? 0);
    $name = (string) ($product['name'] ?? 'Produkt');
    $image = (string) ($product['image'] ?? '');
    $price = (float) ($product['price'] ?? 0);
    $basePrice = (float) ($product['basePrice'] ?? $price);
    $discount = max(0, (float) ($product['discount'] ?? 0));
    $rating = max(0, min(5, (int) ($product['rating'] ?? 4)));
    $stock = (int) ($product['stock'] ?? 0);
    $category = (string) ($product['category'] ?? 'Chilli produkt');
    $description = trim((string) ($product['description'] ?? ''));
    $productUrl = function_exists('route') ? route('/product?id=' . $productId) : '/product?id=' . $productId;
    $searchTerms = trim($name . ' ' . $category . ' ' . $description);

    
    $finalPrice = $basePrice;
    if ($discount > 0) {
        $finalPrice = $basePrice * (1 - ($discount / 100));
    }
    ?>
    <div class="col-4 product-card" style="position: relative;" data-id="<?= $productId ?>" data-price="<?= $finalPrice ?>" data-category="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>" data-search="<?= htmlspecialchars($searchTerms, ENT_QUOTES, 'UTF-8') ?>">
        
        <?php if ($discount > 0): ?>
            <div class="action-ribbon">
                <span>AKCIA -<?= number_format($discount, 0) ?>%</span>
            </div>
        <?php endif; ?>

        <a class="product-link" href="<?= htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') ?>">
            <img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
            <h4><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h4>
        </a>
        <p class="product-category"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></p>
        
        <div class="rating">
            <?php for ($i = 0; $i < $rating; $i++): ?>
                <i class="fa fa-star"></i>
            <?php endfor; ?>
            <?php for ($i = $rating; $i < 5; $i++): ?>
                <i class="far fa-star"></i>
            <?php endfor; ?>
        </div>

        <?php if ($discount > 0): ?>
            <p class="price-example">
                <span class="price-old" style="text-decoration: line-through; color: #999; font-size: 0.9em;">
                    <?= number_format($basePrice, 2, '.', '') ?>€
                </span> 
                <span style="color: #ff0000; font-weight: bold;">
                    <?= number_format($finalPrice, 2, '.', '') ?>€
                </span>
            </p>
        <?php else: ?>
            <p class="price-example"><?= number_format($basePrice, 2, '.', '') ?>€</p>
        <?php endif; ?>
        
        <div class="stock <?= $stock > 0 ? 'in-stock' : 'out-of-stock' ?>">
            <?= $stock > 0 ? "Na sklade: {$stock} ks" : 'Vypredané' ?>
        </div>
        
        <form method="POST" action="<?= htmlspecialchars(function_exists('route') ? route('/api/AddToCart.php') : '/api/AddToCart.php', ENT_QUOTES, 'UTF-8') ?>" class="add_to_cart_form">
            <?= SessionHelper::csrfField(); ?>
            <input type="hidden" name="id" value="<?= $productId ?>">
            <input type="hidden" name="return_to" value="<?= $returnTo ?>">
            <button type="submit" class="add_to_cart-btn" <?= $stock <= 0 ? 'disabled' : '' ?>>
                <?= $stock > 0 ? 'Pridať do košíka' : 'Vypredané' ?>
            </button>
        </form>
    </div>
<?php
};

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
                            $bannerImage = function_exists('asset') ? asset('images/omacka3.webp') : 'images/omacka3.webp';
                        }
                        
                        // Získanie cien a zľavy
                        $bannerPrice = (float) ($bannerProduct['price'] ?? 0);
                        $bannerDiscount = max(0, (float) ($bannerProduct['discount'] ?? 0));
                        
                        // Výpočet zľavnenej ceny
                        $bannerFinalPrice = $bannerPrice;
                        if ($bannerDiscount > 0) {
                            $bannerFinalPrice = $bannerPrice * (1 - ($bannerDiscount / 100));
                        }

                        $bannerDiscount = (float) ($bannerProduct['discount'] ?? 0);
                        $bannerFinalPrice = $bannerPrice;
                        if ($bannerDiscount > 0) {
                            $bannerFinalPrice = $bannerPrice * (1 - ($bannerDiscount / 100));
                        }

                        $bannerDescription = trim((string) ($bannerProduct['description'] ?? ''));
                        $bannerStock = (int) ($bannerProduct['stock'] ?? 0);
                        $bannerCategory = (string) ($bannerProduct['category'] ?? 'Novinka');
                        $bannerUrl = $bannerId > 0 ? (function_exists('route') ? route('/product?id=' . $bannerId) : '/product?id=' . $bannerId) : '#';
                        $bannerAccent = $bannerAccents[$index % count($bannerAccents)];
                    ?>
                    <div class="swiper-slide shop-banner-slide" style="--banner-accent: <?= htmlspecialchars($bannerAccent, ENT_QUOTES, 'UTF-8') ?>;">
                        
                        <?php if ($bannerDiscount > 0): ?>
                            <div class="shop-banner-ribbon">
                                <span>AKCIA -<?= number_format($bannerDiscount, 0) ?>%</span>
                            </div>
                        <?php endif; ?>

                        <div class="shop-banner-copy">
                            <span class="shop-banner-kicker"><?= htmlspecialchars($bannerCategory, ENT_QUOTES, 'UTF-8') ?></span>
                            <h2 class="shop-banner-title"><?= htmlspecialchars($bannerName, ENT_QUOTES, 'UTF-8') ?></h2>
                            
                            <?php if ($bannerDescription !== ''): ?>
                                <p class="shop-banner-description"><?= htmlspecialchars($bannerDescription, ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                            
                            <div class="shop-banner-meta">
                                <span class="shop-banner-price">
                                    <?php if ($bannerDiscount > 0): ?>
                                        <span style="text-decoration: line-through; color: #999; font-size: 0.9em; margin-right: 8px;">
                                            <?= number_format($bannerPrice, 2, '.', '') ?> EUR
                                        </span>
                                        <span style="color: #ff0000; font-weight: bold;">
                                            <?= number_format($bannerFinalPrice, 2, '.', '') ?> EUR
                                        </span>
                                    <?php else: ?>
                                        <?= number_format($bannerPrice, 2, '.', '') ?> EUR
                                    <?php endif; ?>
                                </span>
                                </span>
                                
                                <span class="shop-banner-stock <?= $bannerStock > 0 ? 'is-available' : 'is-empty' ?>">
                                    <?= $bannerStock > 0 ? 'Skladom ' . $bannerStock . ' ks' : 'Vypredané' ?>
                                </span>
                            </div>
                            
                            <div class="shop-banner-actions">
                                <a class="shop-banner-btn" href="<?= htmlspecialchars($bannerUrl, ENT_QUOTES, 'UTF-8') ?>">
                                    Zobraziť produkt
                                </a>
                                <form method="POST" action="<?= htmlspecialchars(function_exists('route') ? route('/api/AddToCart.php') : '/api/AddToCart.php', ENT_QUOTES, 'UTF-8') ?>" class="shop-banner-cart-form">
                                    <?= SessionHelper::csrfField(); ?>

                                    <input type="hidden" name="id" value="<?= $bannerId ?>">
                                    <input type="hidden" name="return_to" value="<?= $returnTo ?>">
                                    <button type="submit" class="shop-banner-btn shop-banner-btn--ghost" <?= ($bannerStock <= 0 || $bannerId <= 0) ? 'disabled' : '' ?>>
                                        <?= $bannerStock > 0 ? 'Pridať do košíka' : 'Vypredané' ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="shop-banner-visual" aria-hidden="true">
                            <div class="shop-banner-orb"></div>
                            <img src="<?= htmlspecialchars($bannerImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($bannerName, ENT_QUOTES, 'UTF-8') ?>">
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
                    <?php foreach ($featuredProducts as $product) { $renderProductCard($product); } ?>
                </div>

                <h2 class="title">Ostatné produkty</h2>
                <div class="row product-grid" id="productsContainer">
                    <?php foreach ($otherProducts as $product) { $renderProductCard($product); } ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/partials/footer-shop.php'; ?>