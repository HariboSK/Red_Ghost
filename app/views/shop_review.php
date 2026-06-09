<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$pdo = $conn ?? ($GLOBALS['conn'] ?? null);
$reviewFormErrors = [];
$reviewFormSuccess = '';
$reviewFormData = [
  'name' => '',
  'rating' => '5',
  'review_text' => '',
];

if (isset($_SESSION['shop_review_success'])) {
  $reviewFormSuccess = (string) $_SESSION['shop_review_success'];
  unset($_SESSION['shop_review_success']);
}

if (isset($_SESSION['shop_review_errors']) && is_array($_SESSION['shop_review_errors'])) {
  $reviewFormErrors = $_SESSION['shop_review_errors'];
  unset($_SESSION['shop_review_errors']);
}

if (isset($_SESSION['shop_review_data']) && is_array($_SESSION['shop_review_data'])) {
  $reviewFormData = array_merge($reviewFormData, $_SESSION['shop_review_data']);
  unset($_SESSION['shop_review_data']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['form_type'] ?? '') === 'shop_review') {
  $reviewFormData['name'] = trim((string) ($_POST['name'] ?? ''));
  $reviewFormData['rating'] = trim((string) ($_POST['rating'] ?? '5'));
  $reviewFormData['review_text'] = trim((string) ($_POST['review_text'] ?? ''));

  $reviewNameLength = function_exists('mb_strlen') ? mb_strlen($reviewFormData['name']) : strlen($reviewFormData['name']);
  $reviewTextLength = function_exists('mb_strlen') ? mb_strlen($reviewFormData['review_text']) : strlen($reviewFormData['review_text']);
  $reviewRating = filter_var($reviewFormData['rating'], FILTER_VALIDATE_INT);

  if ($reviewFormData['name'] === '' || $reviewNameLength < 2 || $reviewNameLength > 100) {
    $reviewFormErrors[] = 'Meno musi mat aspon 2 znaky a najviac 100 znakov.';
  }

  if ($reviewRating === false || $reviewRating === null || $reviewRating < 1 || $reviewRating > 5) {
    $reviewFormErrors[] = 'Hodnotenie musi byt v rozsahu 1 az 5.';
  }

  if ($reviewFormData['review_text'] === '' || $reviewTextLength < 10 || $reviewTextLength > 1000) {
    $reviewFormErrors[] = 'Recenzia musi mat aspon 10 znakov a najviac 1000 znakov.';
  }

  if (!$pdo instanceof PDO) {
    $reviewFormErrors[] = 'Databazove pripojenie nie je dostupne.';
  }

  if (empty($reviewFormErrors) && $pdo instanceof PDO) {
    try {

    $userId = null;
    if (isset($_SESSION['user_id'])) {
        $tmpUserId = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
        if ($tmpUserId && $tmpUserId > 0) {
            $userId = (int) $tmpUserId;
        }
    }

      $stmt = $pdo->prepare(
        'INSERT INTO shop_review (reviewer_name, rating, review_text, status, id_user)
         VALUES (:reviewer_name, :rating, :review_text, :status, :id_user)'
      );

      $stmt->execute([
        ':reviewer_name' => $reviewFormData['name'],
        ':rating' => (int) $reviewRating,
        ':review_text' => $reviewFormData['review_text'],
        ':status' => 'pending',
        ':id_user' => $userId,
      ]);

      $_SESSION['shop_review_success'] = 'Recenzia bola odoslana na schvalenie.';
      $_SESSION['shop_review_data'] = [
        'name' => '',
        'rating' => '5',
        'review_text' => '',
      ];
      header('Location: ' . route('/shop-review'));
      exit;
    } catch (PDOException $exception) {
      $reviewFormErrors[] = 'Recenziu sa nepodarilo ulozit. Skuste to prosim neskor.';
    }
  }

  if (!empty($reviewFormErrors)) {
    $_SESSION['shop_review_errors'] = $reviewFormErrors;
    $_SESSION['shop_review_data'] = $reviewFormData;
    header('Location: ' . route('/shop-review'));
    exit;
  }
}

$bodyClass = 'shop-review-body';
include __DIR__ . '/partials/header.php';
?>

<main class="shop-review-page">
  <section class="contact-section review-section" id="shop-review-form">
    <h2 class="section-title autoshow">Pridať recenziu</h2>
    <p class="section-description">Zdieľaj svoj názor o našom obchode s ostatnými zákazníkmi.</p>
    <h3 class="section-subtitle">Ak tvoja recenzia bude schvalovaná, získavaš 100 bodov.</h3>
    <div class="section-content review-section-content">
      <form action="<?php echo route('/shop-review'); ?>" class="contact-form review-form-card" method="POST">
        <input type="hidden" name="form_type" value="shop_review">

        <input type="text" name="name" placeholder="Tvoje meno" class="form-input" required
          value="<?php echo htmlspecialchars($reviewFormData['name'], ENT_QUOTES, 'UTF-8'); ?>">

        <input type="number" name="rating" min="1" max="5" placeholder="Hodnotenie 1-5" class="form-input" required
          value="<?php echo htmlspecialchars($reviewFormData['rating'], ENT_QUOTES, 'UTF-8'); ?>">

        <textarea name="review_text" placeholder="Tvoja recenzia na obchod" class="form-input" required><?php echo htmlspecialchars($reviewFormData['review_text'], ENT_QUOTES, 'UTF-8'); ?></textarea>

        <button type="submit" class="submit-button">Odoslať recenziu</button>
        <a href="<?php echo route('/home#testimonials'); ?>" class="submit-button review-back-link">Späť na recenzie</a>
      </form>

      <?php if (!empty($reviewFormSuccess)): ?>
        <p class="form-feedback form-feedback--success">
          <?php echo htmlspecialchars($reviewFormSuccess, ENT_QUOTES, 'UTF-8'); ?>
        </p>
      <?php endif; ?>

      <?php if (!empty($reviewFormErrors)): ?>
        <div class="form-feedback form-feedback--error" role="alert">
          <?php foreach ($reviewFormErrors as $reviewFormError): ?>
            <p><?php echo htmlspecialchars((string) $reviewFormError, ENT_QUOTES, 'UTF-8'); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
