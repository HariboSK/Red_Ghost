<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__) . '/core/SessionHelper.php';
require_once dirname(__DIR__) . '/core/ShopService.php';
require_once dirname(__DIR__) . '/models/ProductReviewModel.php';

SessionHelper::bootstrap();

$sessionUser = SessionHelper::user();
$pdo = $conn ?? ($GLOBALS['conn'] ?? null);

$productId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$productId = ($productId && $productId > 0) ? (int) $productId : 0;

$pageTitle = 'Produkt | Red Ghost';
$bodyClass = 'product-page-body';

$reviewErrors = [];
$reviewSuccess = '';
$reviewData = [
    'rating' => '5',
    'title' => '',
    'content' => '',
];

if (isset($_SESSION['product_review_success'])) {
    $reviewSuccess = (string) $_SESSION['product_review_success'];
    unset($_SESSION['product_review_success']);
}

if (isset($_SESSION['product_review_errors']) && is_array($_SESSION['product_review_errors'])) {
    $reviewErrors = $_SESSION['product_review_errors'];
    unset($_SESSION['product_review_errors']);
}

if (isset($_SESSION['product_review_data']) && is_array($_SESSION['product_review_data'])) {
    $reviewData = array_merge($reviewData, $_SESSION['product_review_data']);
    unset($_SESSION['product_review_data']);
}

$product = null;
$reviews = [];
$reviewSummary = ['count' => 0, 'average' => 0.0];
$userReview = null;
$reviewModel = $pdo instanceof PDO ? new ProductReviewModel($pdo) : null;

if ((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && (string) ($_POST['form_type'] ?? '') === 'product_review') {
    $reviewData['rating'] = trim((string) ($_POST['rating'] ?? '5'));
    $reviewData['title'] = trim((string) ($_POST['title'] ?? ''));
    $reviewData['content'] = trim((string) ($_POST['content'] ?? ''));

    $ratingValue = filter_var($reviewData['rating'], FILTER_VALIDATE_INT);
    $titleLength = function_exists('mb_strlen') ? mb_strlen($reviewData['title']) : strlen($reviewData['title']);
    $contentLength = function_exists('mb_strlen') ? mb_strlen($reviewData['content']) : strlen($reviewData['content']);
    $userId = (int) ($sessionUser['id'] ?? 0);

    if ($productId <= 0) {
        $reviewErrors[] = 'Produkt nebol nájdený.';
    }

    if (!($sessionUser['is_logged_in'] ?? false) || $userId <= 0) {
        $reviewErrors[] = 'Na pridanie recenzie sa musíš prihlásiť.';
    }

    if ($ratingValue === false || $ratingValue === null || $ratingValue < 1 || $ratingValue > 5) {
        $reviewErrors[] = 'Hodnotenie musí byť od 1 do 5.';
    }

    if ($reviewData['title'] === '' || $titleLength < 3 || $titleLength > 150) {
        $reviewErrors[] = 'Nadpis recenzie musí mať aspoň 3 a najviac 150 znakov.';
    }

    if ($reviewData['content'] === '' || $contentLength < 10 || $contentLength > 2000) {
        $reviewErrors[] = 'Text recenzie musí mať aspoň 10 a najviac 2000 znakov.';
    }

    if (!($pdo instanceof PDO)) {
        $reviewErrors[] = 'Databázové pripojenie nie je dostupné.';
    }

    if (empty($reviewErrors) && $reviewModel instanceof ProductReviewModel) {
        $product = ShopService::getProductById($pdo, $productId);
        if (!is_array($product)) {
            $reviewErrors[] = 'Produkt sa nepodarilo načítať.';
        } else {
            $saved = $reviewModel->saveReview(
                $productId,
                $userId,
                (int) $ratingValue,
                $reviewData['title'],
                $reviewData['content'],
                false, //
                null,  //
                true   // <--- TOTO POSIELA $autoApprove
            );

            if ($saved) {
                $_SESSION['product_review_success'] = 'Recenzia bola úspešne pridaná.';
                $_SESSION['product_review_data'] = [
                    'rating' => '5',
                    'title' => '',
                    'content' => '',
                ];
                header('Location: ' . route('/product?id=' . $productId . '#btn-reviews'));
                exit;
            }

            $reviewErrors[] = 'Recenziu sa nepodarilo uložiť.';
        }
    }

    if (!empty($reviewErrors)) {
        $_SESSION['product_review_errors'] = $reviewErrors;
        $_SESSION['product_review_data'] = $reviewData;
        header('Location: ' . route('/product?id=' . $productId . '#btn-reviews'));
        exit;
    }
}

if ($productId > 0 && $pdo instanceof PDO) {
    $product = ShopService::getProductById($pdo, $productId);

    if (is_array($product) && $reviewModel instanceof ProductReviewModel) {
        $reviews = $reviewModel->getApprovedByProduct($productId);
        $reviewSummary = $reviewModel->getSummary($productId);

        $userId = (int) ($sessionUser['id'] ?? 0);
        if ($userId > 0) {
            $userReview = $reviewModel->getUserReview($productId, $userId);
            if (is_array($userReview)) {
                $reviewData['rating'] = (string) ($userReview['rating'] ?? '5');
                $reviewData['title'] = (string) ($userReview['title'] ?? '');
                $reviewData['content'] = (string) ($userReview['content'] ?? '');
            }
        }
    }
}

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
      <div class="detail-card">
        <h1>Produkt sa nenašiel</h1>
        <p class="description short">Skontroluj odkaz alebo sa vráť späť do e-shopu.</p>
        <a href="<?php echo htmlspecialchars(route('/e-shop'), ENT_QUOTES, 'UTF-8'); ?>" class="cart-view-btn">Späť do e-shopu</a>
      </div>
    </section>
  <?php else: ?>
    <section class="product-detail">
      <div class="image-panel">
        <button type="button" class="zoom-trigger" aria-label="Zväčšiť obrázok produktu" data-zoom-image="<?php echo htmlspecialchars((string) ($product['image'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
          <img src="<?php echo htmlspecialchars((string) ($product['image'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) ($product['name'] ?? 'Produkt'), ENT_QUOTES, 'UTF-8'); ?>">
        </button>
      </div>

      <div class="info-panel">
        <div class="top-meta">
          <span class="category-chip"><?php echo htmlspecialchars((string) ($product['category'] ?? 'Chilli produkt'), ENT_QUOTES, 'UTF-8'); ?></span>
          <div class="review-badge">
            <?php echo number_format((float) ($reviewSummary['average'] ?? 0), 1, '.', ''); ?>/5 · <?php echo (int) ($reviewSummary['count'] ?? 0); ?> recenzií
          </div>
        </div>

        <h1 class="product-title"><?php echo htmlspecialchars((string) ($product['name'] ?? 'Produkt'), ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="price"><?php echo number_format((float) ($product['price'] ?? 0), 2, '.', ''); ?> EUR</p>
        <p class="description short"><?php echo nl2br(htmlspecialchars((string) ($product['description'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></p>

        <div class="key-facts">
          <div class="fact-item">
            <span class="label">Sklad</span>
            <span class="value"><?php echo ((int) ($product['stock'] ?? 0) > 0) ? (int) $product['stock'] . ' ks' : 'Vypredané'; ?></span>
          </div>
          <div class="fact-item">
            <span class="label">Hodnotenie</span>
            <span class="value"><?php echo (int) ($product['rating'] ?? 0); ?>/5</span>
          </div>
          <div class="fact-item">
            <span class="label">Cena</span>
            <span class="value"><?php echo number_format((float) ($product['price'] ?? 0), 2, '.', ''); ?> EUR</span>
          </div>
        </div> 

        <div class="actions" style="display: block; width: 100%; margin-top: 20px; clear: both;">
          <?php if ((int) ($product['stock'] ?? 0) > 0): ?>
            <form method="POST" action="<?php echo route('/api/AddToCart.php'); ?>" class="add_to_cart_form" style="width: 100%;">
              <input type="hidden" name="id" value="<?php echo (int) ($product['id'] ?? 0); ?>">
              <input type="hidden" name="return_to" value="<?php echo htmlspecialchars(route('/product?id=' . (int) ($product['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>">
              <button type="submit" class="add-to-cart-btn">
                Pridať do košíka
              </button>
            </form>
          <?php else: ?>
            <button class="add-to-cart-btn-vypredane" disabled>
              Vypredané
            </button>
          <?php endif; ?>
        </div> 

      </div> </section>

    <section class="product-lower">
      <article class="detail-card">
        <h2>Popis produktu</h2>
        <p class="description long"><?php echo nl2br(htmlspecialchars((string) ($product['description'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></p>
      </article>

      <article class="detail-card product-review-section" id="reviews">
        <div class="section-head">
          <h2>Recenzie produktu</h2>
          <span><?php echo (int) ($reviewSummary['count'] ?? 0); ?> schválených recenzií</span>
        </div>

        <div class="review-list">
          <?php if (empty($reviews)): ?>
            <p class="review-empty">Zatiaľ tu nie sú žiadne recenzie.</p>
          <?php else: ?>
            <?php foreach ($reviews as $review): ?>
              <article class="review-item">
                <div class="review-head">
                  <h3 class="review-title"><?php echo htmlspecialchars((string) ($review['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h3>
                  <span class="review-date"><?php echo htmlspecialchars(date('d.m.Y', strtotime((string) ($review['created_at'] ?? 'now'))), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="review-rating" aria-label="Hodnotenie <?php echo (int) ($review['rating'] ?? 0); ?> z 5">
                  <?php for ($i = 0; $i < (int) ($review['rating'] ?? 0); $i++): ?>
                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                  <?php endfor; ?>
                </div>
                <p><?php echo nl2br(htmlspecialchars((string) ($review['content'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></p>
                <span class="review-author"><?php echo htmlspecialchars((string) ($review['reviewer_name'] ?? 'Zákazník'), ENT_QUOTES, 'UTF-8'); ?></span>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <div class="review-form-wrap">
          <h3>Pridať recenziu</h3>
          <?php if (!($sessionUser['is_logged_in'] ?? false)): ?>
            <p class="review-empty">Na pridanie recenzie sa <a href="<?php echo htmlspecialchars(route('/login'), ENT_QUOTES, 'UTF-8'); ?>">prihlás</a>.</p>
          <?php else: ?>
            <form method="POST" action="<?php echo htmlspecialchars(route('/product?id=' . (int) ($product['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="review-form-card">
              <input type="hidden" name="form_type" value="product_review">

              <label>
                Hodnotenie
                <select name="rating" class="review-input" required>
                  <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                    <option value="<?php echo $rating; ?>" <?php echo ((int) $reviewData['rating'] === $rating) ? 'selected' : ''; ?>><?php echo $rating; ?>/5</option>
                  <?php endfor; ?>
                </select>
              </label>

              <label>
                Nadpis
                <input type="text" name="title" class="review-input" maxlength="150" required value="<?php echo htmlspecialchars((string) $reviewData['title'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>

              <label>
                Recenzia
                <textarea name="content" class="review-input review-textarea" rows="5" maxlength="2000" required><?php echo htmlspecialchars((string) $reviewData['content'], ENT_QUOTES, 'UTF-8'); ?></textarea>
              </label>

              <button type="submit" class="cart-view-btn" id="btn-reviews"><?php echo is_array($userReview) ? 'Upraviť recenziu' : 'Odoslať recenziu'; ?></button>
            </form>
          <?php endif; ?>
        </div>
      </article>
    </section>
  <?php endif; ?>

        <?php if ($reviewSuccess !== ''): ?>
          <p class="review-feedback review-feedback--success"><?php echo htmlspecialchars($reviewSuccess, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <?php if (!empty($reviewErrors)): ?>
          <div class="review-feedback review-feedback--error" role="alert">
            <?php foreach ($reviewErrors as $reviewError): ?>
              <p><?php echo htmlspecialchars((string) $reviewError, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
</main>

<div class="zoom-modal" id="zoomModal" aria-hidden="true">
  <button type="button" class="close-modal" aria-label="Zavrieť náhľad" id="closeZoomModal">×</button>
  <img src="" alt="Zväčšený produkt">
</div>

<script>
  (function () {
    const zoomTrigger = document.querySelector('.zoom-trigger');
    const zoomModal = document.getElementById('zoomModal');
    const zoomImage = zoomModal ? zoomModal.querySelector('img') : null;
    const closeZoomModal = document.getElementById('closeZoomModal');

    if (!zoomTrigger || !zoomModal || !zoomImage || !closeZoomModal) {
      return;
    }

    zoomTrigger.addEventListener('click', function () {
      const src = zoomTrigger.getAttribute('data-zoom-image') || '';
      if (!src) {
        return;
      }

      zoomImage.src = src;
      zoomModal.classList.add('open');
      zoomModal.setAttribute('aria-hidden', 'false');
    });

    const closeModal = function () {
      zoomModal.classList.remove('open');
      zoomModal.setAttribute('aria-hidden', 'true');
      zoomImage.src = '';
    };

    closeZoomModal.addEventListener('click', closeModal);
    zoomModal.addEventListener('click', function (event) {
      if (event.target === zoomModal) {
        closeModal();
      }
    });
  })();
</script>

<?php include __DIR__ . '/partials/footer-shop.php'; ?>
