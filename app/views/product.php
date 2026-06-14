<?php
SessionHelper::bootstrap();
$pdo = $conn ?? ($GLOBALS['conn'] ?? null);

// Inicializácia OOP controllera
$controller = new ProductController($pdo, SessionHelper::user());

$productId = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'product_review') {
    $controller->handleReviewSubmission($productId, $_POST);
}

// Získanie dát cez Controller
$data = $controller->getProductPageData($productId);
$product = $data['product'];
$reviews = $data['reviews'];
$reviewSummary = $data['summary'];
$userReview = $data['userReview'];

// Feedback správy zo session
$reviewSuccess = $_SESSION['product_review_success'] ?? '';
unset($_SESSION['product_review_success']);

$reviewErrors = $_SESSION['product_review_errors'] ?? [];
unset($_SESSION['product_review_errors']);

$reviewData = $_SESSION['product_review_data'] ?? ['rating' => '5', 'title' => '', 'content' => ''];
unset($_SESSION['product_review_data']);

include __DIR__ . '/partials/header-product.php';
?>

<main class="product-page product-page-content">
  <header class="product-header">
    <a href="<?php echo htmlspecialchars(route('/e-shop'), ENT_QUOTES, 'UTF-8'); ?>" class="back-link">
      <i class="fa-solid fa-arrow-left"></i> Späť do e-shopu
    </a>
  </header>

  <?php if (!is_array($product)): ?>
    <section class="product-detail not-found">
        <h1>Produkt sa nenašiel</h1>
    </section>
  <?php else: ?>
    <section class="product-detail">
      <div class="image-panel">
        <button type="button" class="zoom-trigger" data-zoom-image="<?php echo htmlspecialchars($product['image'] ?? ''); ?>">
          <img src="<?php echo htmlspecialchars($product['image'] ?? ''); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </button>
      </div>

      <div class="info-panel">
        <div class="top-meta">
          <span class="category-chip"><?php echo htmlspecialchars($product['category'] ?? 'Chilli produkt'); ?></span>
          <div class="review-badge">
            <?php echo number_format((float) ($reviewSummary['average'] ?? 0), 1); ?>/5 · <?php echo (int) ($reviewSummary['count'] ?? 0); ?> recenzií
          </div>
        </div>

        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
        <p class="price">
            <?php if ($product['has_discount'] ?? false): ?>
                <span class="original-price" style="text-decoration: line-through; color: #888; font-size: 0.9em; margin-right: 10px;">
                    <?php echo number_format((float)$product['price'], 2); ?> €
                </span>
                <span class="discounted-price" style="color: #d9534f; font-weight: bold;">
                    <?php echo number_format((float)$product['discounted_price'], 2); ?> €
                </span>
            <?php else: ?>
                <?php echo number_format((float)$product['price'], 2); ?> €
            <?php endif; ?>
        </p>
        <p class="description short"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        
        <div class="actions">
            <?php if ((int)($product['stock'] ?? 0) > 0): ?>
                <form method="POST" action="<?php echo route('/api/AddToCart.php'); ?>">
                    <?php echo SessionHelper::csrfField(); ?>
                    <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">
                    <button type="submit" class="add-to-cart-btn">Pridať do košíka</button>
                </form>
            <?php else: ?>
                <button class="add-to-cart-btn-vypredane" disabled>Vypredané</button>
            <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="product-lower">
      <article class="detail-card product-review-section" id="reviews">
        <div class="section-head">
          <h2>Recenzie produktu</h2>
        </div>

        <?php if ($reviewSuccess): ?>
            <p class="review-feedback review-feedback--success"><?php echo htmlspecialchars($reviewSuccess); ?></p>
        <?php endif; ?>

        <?php if (!empty($reviewErrors)): ?>
            <div class="review-feedback review-feedback--error">
                <?php foreach ($reviewErrors as $error): ?><p><?php echo htmlspecialchars($error); ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="review-list">
          <?php if (empty($reviews)): ?>
            <p class="review-empty">Zatiaľ tu nie sú žiadne recenzie.</p>
          <?php else: ?>
            <?php foreach ($reviews as $review): ?>
              <article class="review-item">
                <div class="review-head">
                  <h3 class="review-title"><?php echo htmlspecialchars((string) ($review['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h3>
                  <span class="review-date">
                    <?php echo htmlspecialchars(date('d.m.Y', strtotime((string) ($review['created_at'] ?? 'now'))), ENT_QUOTES, 'UTF-8'); ?>
                  </span>
                </div>

                <div class="review-rating" aria-label="Hodnotenie <?php echo (int) ($review['rating'] ?? 0); ?> z 5">
                  <?php for ($i = 0; $i < (int) ($review['rating'] ?? 0); $i++): ?>
                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                  <?php endfor; ?>
                </div>
                
                <p><?php echo nl2br(htmlspecialchars((string) ($review['content'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></p>
                <p class="review-author">Napísal uživateľ:</p>
                  <span class="review-author-name">
                    <?php echo htmlspecialchars((string) ($review['reviewer_name'] ?? 'Zákazník'), ENT_QUOTES, 'UTF-8'); ?>
                  </span>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <div class="review-form-wrap">
          <h3><?php echo is_array($userReview) ? 'Upraviť recenziu' : 'Pridať recenziu'; ?></h3>
          
          <?php if (!SessionHelper::user()['is_logged_in']): ?>
            <p class="review-empty">Na pridanie recenzie sa <a href="<?php echo htmlspecialchars(route('/login'), ENT_QUOTES, 'UTF-8'); ?>">prihlás</a>.</p>
          <?php else: ?>
            <form method="POST" action="<?php echo htmlspecialchars(route('/product?id=' . (int)$productId . '#reviews'), ENT_QUOTES, 'UTF-8'); ?>" class="review-form-card">
              <?php echo SessionHelper::csrfField(); ?>
              <input type="hidden" name="form_type" value="product_review">

              <label>
                Hodnotenie
                <select name="rating" class="review-input" required>
                  <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?php echo $i; ?>" <?php echo ((int)$reviewData['rating'] === $i) ? 'selected' : ''; ?>>
                      <?php echo $i; ?>/5
                    </option>
                  <?php endfor; ?>
                </select>
              </label>

              <label>
                Nadpis
                <input type="text" name="title" class="review-input" maxlength="150" required 
                      value="<?php echo htmlspecialchars((string)$reviewData['title'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>

              <label>
                Recenzia
                <textarea name="content" class="review-input review-textarea" rows="5" maxlength="2000" required><?php echo htmlspecialchars((string)$reviewData['content'], ENT_QUOTES, 'UTF-8'); ?></textarea>
              </label>

              <button type="submit" class="cart-view-btn" id="btn-reviews">
                <?php echo is_array($userReview) ? 'Upraviť recenziu' : 'Odoslať recenziu'; ?>
              </button>
            </form>
          <?php endif; ?>
        </div>
      </article>
    </section>
  <?php endif; ?>
</main>

<div class="zoom-modal" id="zoomModal" aria-hidden="true">
  <button type="button" class="close-modal" id="closeZoomModal">×</button>
  <img src="" alt="">
</div>


<script src="/assets/js/product.js"></script>
<?php include __DIR__ . '/partials/footer-shop.php'; ?>