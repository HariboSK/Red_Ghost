<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/app/core/shop_functions.php';
// asset helpers available via middleware/function.php
include __DIR__ . '/partials/header-shop.php';

$assetBase = asset_base();
$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = shop_get_product_by_id($conn ?? null, $productId);

if (!$product) {
    http_response_code(404);
}

$pageTitle = $product
  ? ((string) $product['name']) . ' | Red Ghost'
  : 'Produkt nenajdeny | Red Ghost';
$extraStyles = ['/assets/css/productview.css'];

?>
  <main class="product-page product-page-content">
    <header class="product-header">
      <a href="<?php echo htmlspecialchars(route('/e-shop'), ENT_QUOTES, 'UTF-8'); ?>" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Spat do e-shopu
      </a>
    </header>
    <?php if (!$product): ?>
      <section class="not-found">
        <h1>Produkt nenajdeny</h1>
        <p>Skontroluj odkaz alebo sa vrat spat do e-shopu.</p>
      </section>
    <?php else: ?>
      <?php
      $spicyResult = function_exists('shop_product_spicy_label')
        ? call_user_func('shop_product_spicy_label', (int) $product['rating'])
        : ['Stredna', 'medium'];
      [$spicyText, $spicyClass] = $spicyResult;

      $reviews = function_exists('shop_product_reviews')
        ? call_user_func('shop_product_reviews', $product)
        : [];
      ?>
      <section class="product-detail">
        <div class="image-panel">
          <button type="button" class="zoom-trigger" id="zoomTrigger" aria-label="Priblizit obrazok produktu">
            <img src="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
          </button>
        </div>

        <div class="info-panel">
          <div class="top-meta">
            <span class="category-chip"><?php echo htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8'); ?></span>
            <p class="stock <?php echo ((int) $product['stock'] > 0) ? 'ok' : 'empty'; ?>">
              <?php echo ((int) $product['stock'] > 0) ? 'Na sklade: ' . (int) $product['stock'] . ' ks' : 'Vypredane'; ?>
            </p>
          </div>

          <h1><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
          <p class="price"><?php echo number_format((float) $product['price'], 2, '.', ''); ?> EUR</p>

          <div class="spicy-meter <?php echo htmlspecialchars($spicyClass, ENT_QUOTES, 'UTF-8'); ?>">
            <strong>Palivost:</strong>
            <span><?php echo htmlspecialchars($spicyText, ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="chilies" aria-hidden="true">
              <?php for ($i = 0; $i < (int) $product['rating']; $i++): ?>
                <i class="fa-solid fa-pepper-hot"></i>
              <?php endfor; ?>
            </span>
          </div>

          <p class="description short"><?php echo htmlspecialchars((string) $product['description'], ENT_QUOTES, 'UTF-8'); ?></p>

          <div class="key-facts">
            <div class="fact-item">
              <span class="label">Kategoria</span>
              <span class="value"><?php echo htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="fact-item">
              <span class="label">Hodnotenie</span>
              <span class="value"><?php echo (int) $product['rating']; ?>/5</span>
            </div>
            <div class="fact-item">
              <span class="label">Dostupnost</span>
              <span class="value"><?php echo ((int) $product['stock'] > 0) ? 'Skladom' : 'Vypredane'; ?></span>
            </div>
          </div>

          <div class="actions">
            <button class="add-to-cart-btn" onclick="addToCart(<?php echo (int) $product['id']; ?>)" <?php echo ((int) $product['stock'] <= 0) ? 'disabled' : ''; ?>>
              Pridat do kosika
            </button>
          </div>
        </div>
      </section>

      <section class="product-lower">
        <article class="detail-card">
          <h2>Popis produktu</h2>
          <p class="description long"><?php echo nl2br(htmlspecialchars((string) $product['description'], ENT_QUOTES, 'UTF-8')); ?></p>
          <ul class="details-list">
            <li>Vyrobeny z kvalitnych surovin bez zbytocnych aditiv.</li>
            <li>Idealny na varenie, marinady aj dochutenie hotovych jedal.</li>
            <li>Skladuj v suchu a po otvoreni v chladnicke.</li>
          </ul>
        </article>

        <article class="detail-card reviews-card">
          <h2>Recenzie zakaznikov</h2>
          <?php foreach ($reviews as $review): ?>
            <div class="review-item">
              <div class="review-head">
                <strong><?php echo htmlspecialchars($review['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                <span class="review-date"><?php echo htmlspecialchars($review['date'], ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
              <div class="review-rating" aria-label="Hodnotenie <?php echo (int) $review['rating']; ?> z 5">
                <?php for ($i = 0; $i < (int) $review['rating']; $i++): ?>
                  <i class="fa-solid fa-star"></i>
                <?php endfor; ?>
              </div>
              <p><?php echo htmlspecialchars($review['text'], ENT_QUOTES, 'UTF-8'); ?></p>
              <span class="review-author"><?php echo htmlspecialchars($review['name'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
          <?php endforeach; ?>
        </article>
      </section>
    <?php endif; ?>
  </main>

  <div class="zoom-modal" id="zoomModal" aria-hidden="true">
    <button class="close-modal" id="closeModal" aria-label="Zavriet priblizenie">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <img id="zoomedImage" src="" alt="Priblizeny obrazok produktu">
  </div>

  
  <?php include __DIR__ . '/partials/footer-shop.php'; ?>
