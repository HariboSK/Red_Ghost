<?php
declare(strict_types=1);

App::init();

$pdo = $GLOBALS['conn']; 

$reviewModel = new ShopReviewModel($pdo);
$reviewFormErrors = [];
$reviewFormSuccess = '';

//load data zo Session
$reviewFormData = $_SESSION['shop_review_data'] ?? [
    'name' => '', 
    'rating' => 5, 
    'review_text' => ''
];
unset($_SESSION['shop_review_data']);

//Načítanie stavových správ zo session
if (isset($_SESSION['shop_review_success'])) {
    $reviewFormSuccess = $_SESSION['shop_review_success'];
    unset($_SESSION['shop_review_success']);
}

if (isset($_SESSION['shop_review_errors'])) {
    $reviewFormErrors = $_SESSION['shop_review_errors'];
    unset($_SESSION['shop_review_errors']);
}

//Spracovanie POST požiadavky
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'shop_review') {
    
    // CSRF overenie
    if (!SessionHelper::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['shop_review_errors'] = ['Bezpečnostná chyba: Neplatný CSRF token.'];
        header('Location: ' . route('/shop-review'));
        exit;
    }

    // Validácia vstupov
    $name = trim((string) ($_POST['name'] ?? ''));
    $rating = filter_var($_POST['rating'] ?? 5, FILTER_VALIDATE_INT);
    $text = trim((string) ($_POST['review_text'] ?? ''));
    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    $errors = [];
    if (empty($name) || mb_strlen($name) < 2) $errors[] = 'Meno musí mať aspoň 2 znaky.';
    if ($rating === false || $rating < 1 || $rating > 5) $errors[] = 'Hodnotenie musí byť 1-5.';
    if (empty($text) || mb_strlen($text) < 10) $errors[] = 'Recenzia musí mať aspoň 10 znakov.';

    if (empty($errors)) {
        try {
            $reviewModel->create($name, $rating, $text, $userId);
            $_SESSION['shop_review_success'] = 'Recenzia bola odoslaná na schválenie.';
            header('Location: ' . route('/shop-review'));
            exit;
        } catch (Exception $e) {
            $errors[] = 'Nastala chyba pri ukladaní do databázy.';
        }
    }
    
    $_SESSION['shop_review_errors'] = $errors;
    $_SESSION['shop_review_data'] = ['name' => $name, 'rating' => $rating, 'review_text' => $text];
    header('Location: ' . route('/shop-review'));
    exit;
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
        <?php echo SessionHelper::csrfField(); ?>
        <input type="hidden" name="form_type" value="shop_review">

        <input type="text" name="name" placeholder="Tvoje meno" class="form-input" required
          value="<?php echo htmlspecialchars($reviewFormData['name'], ENT_QUOTES, 'UTF-8'); ?>">

        <input type="number" name="rating" min="1" max="5" placeholder="Hodnotenie 1-5" class="form-input" required
          value="<?php echo htmlspecialchars((string)$reviewFormData['rating'], ENT_QUOTES, 'UTF-8'); ?>">

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
