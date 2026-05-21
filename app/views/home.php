<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$pdo = $conn ?? ($GLOBALS['conn'] ?? null);


$contactFormErrors = [];
$contactFormSuccess = '';
$contactFormData = [
  'name' => '',
  'email' => '',
  'subject' => '',
  'message' => '',
];

if (isset($_SESSION['contact_form_success'])) {
  $contactFormSuccess = (string) $_SESSION['contact_form_success'];
  unset($_SESSION['contact_form_success']);
}

if (isset($_SESSION['contact_form_errors']) && is_array($_SESSION['contact_form_errors'])) {
  $contactFormErrors = $_SESSION['contact_form_errors'];
  unset($_SESSION['contact_form_errors']);
}

if (isset($_SESSION['contact_form_data']) && is_array($_SESSION['contact_form_data'])) {
  $contactFormData = array_merge($contactFormData, $_SESSION['contact_form_data']);
  unset($_SESSION['contact_form_data']);
}

$shopReviews = [];
if ($pdo instanceof PDO) {
  try {
    $stmt = $pdo->prepare(
      'SELECT reviewer_name, rating, review_text, created_at
       FROM shop_review
       WHERE status = :status
       ORDER BY created_at DESC, id_shop_review DESC
       LIMIT 8'
    );
    $stmt->execute([':status' => 'approved']);
    $shopReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $exception) {
    $shopReviews = [];
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['form_type'] ?? '') === 'contact_message') {
  $contactFormData['name'] = trim((string) ($_POST['name'] ?? ''));
  $contactFormData['email'] = trim((string) ($_POST['email'] ?? ''));
  $contactFormData['subject'] = trim((string) ($_POST['subject'] ?? ''));
  $contactFormData['message'] = trim((string) ($_POST['message'] ?? ''));

  $nameLength = function_exists('mb_strlen') ? mb_strlen($contactFormData['name']) : strlen($contactFormData['name']);
  $subjectLength = function_exists('mb_strlen') ? mb_strlen($contactFormData['subject']) : strlen($contactFormData['subject']);
  $messageLength = function_exists('mb_strlen') ? mb_strlen($contactFormData['message']) : strlen($contactFormData['message']);

  if ($contactFormData['name'] === '' || $nameLength < 2 || $nameLength > 100) {
    $contactFormErrors[] = 'Meno musí mať aspoň 2 znaky a najviac 100 znakov.';
  }

  if (!filter_var($contactFormData['email'], FILTER_VALIDATE_EMAIL)) {
    $contactFormErrors[] = 'Zadajte platnú emailovú adresu.';
  }

  if ($contactFormData['subject'] === '' || $subjectLength < 3 || $subjectLength > 150) {
    $contactFormErrors[] = 'Predmet musí mať aspoň 3 znaky a najviac 150 znakov.';
  }

  if ($contactFormData['message'] === '' || $messageLength < 10 || $messageLength > 5000) {
    $contactFormErrors[] = 'Správa musí mať aspoň 10 znakov a najviac 5000 znakov.';
  }

  if (!$pdo instanceof PDO) {
    $contactFormErrors[] = 'Databázové pripojenie nie je dostupné.';
  }

  if (empty($contactFormErrors) && $pdo instanceof PDO) {
    try {
      $pdo->beginTransaction();

      $stmt = $pdo->prepare(
        'INSERT INTO contact_messages (sender_name, sender_email, subject, status, id_user)
         VALUES (:sender_name, :sender_email, :subject, :status, :id_user)'
      );
      $stmt->execute([
        ':sender_name' => $contactFormData['name'],
        ':sender_email' => $contactFormData['email'],
        ':subject' => $contactFormData['subject'],
        ':status' => 'new',
        ':id_user' => null,
      ]);

      $messageId = (int) $pdo->lastInsertId();
      $stmt = $pdo->prepare(
        'INSERT INTO contact_replies (sender_type, message_text, id_message)
         VALUES (:sender_type, :message_text, :id_message)'
      );
      $stmt->execute([
        ':sender_type' => 'user',
        ':message_text' => $contactFormData['message'],
        ':id_message' => $messageId,
      ]);

      $pdo->commit();

      $_SESSION['contact_form_success'] = 'Správa bola úspešne odoslaná.';
      $_SESSION['contact_form_data'] = [
        'name' => '',
        'email' => '',
        'subject' => '',
        'message' => '',
      ];
      header('Location: ' . route('/home#contact'));
      exit;
    } catch (PDOException $exception) {
      if ($pdo->inTransaction()) {
        $pdo->rollBack();
      }
      $contactFormErrors[] = 'Správu sa nepodarilo uložiť. Skúste to prosím neskôr.';
    }
  }

  if (!empty($contactFormErrors)) {
    $_SESSION['contact_form_errors'] = $contactFormErrors;
    $_SESSION['contact_form_data'] = $contactFormData;
    header('Location: ' . route('/home#contact'));
    exit;
  }
}

include __DIR__ . '/partials/header.php';
?>

<main>
  <!-- Hero section -->
    <section class="hero-section">
    <div class="section-content">
      <div class="hero-details">
        <div class="typewriter-box">
          <h2 class="title" id="typewriter">Red Ghost - kde každé sústo rozpráva príbeh ohňa!</h2>
        </div>
          <h3 class="subtitle">Ohnivé chute, ktoré ťa dostanú! Predaj chilli papričiek a chilli omáčiek rôznych príchutí</h3>
        <p class="description"> Ohnivá explózia chutí! Zaži skutočný pikantný zážitok s našimi prémiovými chilli omáčkami a čerstvými papričkami! Každá kvapka našich omáčok je dokonale vyvážená - od jemnej pikantnosti až po extrémne ohnivé kúsky, ktoré rozohrejú tvoje chuťové bunky! </p>
        
        <div class="buttons">
          <a href="<?php echo route('/e_shop'); ?>" class="button order-now">Objednaj si tu</a>
          <a href="#contact" class="button contact-us">Kontakt na nás</a>
        </div>
      </div>
      <div class="hero-image-wrapper">
          <img src="/assets/images/hero.webp" alt="Hero" 
          class="hero-image">
      </div>
    </div>
  </section>

  <div class="gradient-transition"></div>

  <!-- About section-->
    <section class="about-section" id="about">
    <br>
    <div class="section-content">
      <div class="about-image-wrapper imageReveal">
        <img src="/assets/images/1111.webp" alt="O nás" class="about-image">
      </div>
      <div class="about-details autoshow">
        <h2 class="section-title">O NÁS</h2>
        <p class="text autoshow">S vášňou pre pikantné chute prinášame to najlepšie zo sveta chilli.
          Naša ponuka zahŕňa starostlivo vypestované chilli sadeníc rôznych druhov chilli. Ponúkame rôzne druhy sadeníc, ktoré su aj hybridované
          čo znamená že máme spojené druhy papričiek na dosiahnutie nových chutí, ktoré nikde nenájdete alebo len zriedkavo. Kvalitné chilli omáčky, ktoré 
          sú výsledkom poctivej práce a lásky k ostrým chutiam taktiež rôznych príchutí, určite si každy príde na tu svoju chuť. 
          Veríme, že každé jedlo môže byť zážitkom, a práve naše produkty pomáhajú objaviť nové dimenzie chuti.
        </p>
        <div class="social-link-list logoshow">
          <a href="https://www.facebook.com/profile.php?id=100063660427736" target="_blank" rel="noopener noreferrer" class="social-link1F"><i class="fa-brands fa-facebook"></i></a>
          <a href="https://www.instagram.com/red_ghost_slovakia/?fbclid=IwY2xjawK1m1NleHRuA2FlbQIxMAABHhN7UFe1Z0tFRk3C9Py8Ji1yELLKaEQYjPExpfQD0vmtt5V7jiwRdZisOOXZ_aem_kmrVnxHYgfBFf8ysUkMJnQ" target="_blank" rel="noopener noreferrer" class="social-link1I"><i class="fa-brands fa-instagram"></i></a>
        </div>
      </div>
    </div>
    </section>

  <div class="gradient-transition2"></div>

  <!-- Menu section -->
    <section class="menu-section" id="menu">
    <br>
    <h2 class="section-title autoshow">NAŠE PONUKY</h2>
    <div class="section-content">
      <ul class="menu-list">
        <li class="menu-item fadeUp">
          <img src="/assets/images/klucenka.webp" alt=" CHILLI KĽÚČENKY" class="menu-image">
          <h3 class="name"> CHILLI KĽÚČENKY</h3>
          <p class="text">Kľučenky podľa vlasntého výberu farby + vlasný výber náplne do nej 1,50€ do 2,00€</p>
        </li>
        <li class="menu-item fadeUp">
          <img src="/assets/images/omacka3.webp" alt="CHILLI OMÁČKY" class="menu-image">
          <h3 class="name">CHILLI OMÁČKY</h3>
          <p class="text">Rôzne príchute a druhy chilli omáčiek podľa preferencie intenzity pálivosti a chute</p>
        </li>
        <li class="menu-item fadeUp">
          <img src="/assets/images/chutney.webp" alt="CHUTNEY" class="menu-image">
          <h3 class="name">CHUTNEY</h3>
        </li>
          <li class="menu-item fadeUp">
          <img src="/assets/images/susene-chilli-Picsart-AiImageEnhancer.webp" alt="SUŠENÉ PAPRIČKY" class="menu-image">
          <h3 class="name">SUŠENÉ PAPRIČKY</h3>
        </li>
        <li class="menu-item fadeUp">
          <img src="/assets/images/sadenice-Picsart-AiImageEnhancer.webp" alt=" CHILLI SADENICE" class="menu-image">
          <h3 class="name"> CHILLI SADENICE</h3>
        </li>
          <li class="menu-item fadeUp">
          <img src="/assets/images/mlete-chilli-Picsart-AiImageEnhancer.webp" alt=" MLETE CHILLI" class="menu-image">
          <h3 class="name"> MLETE CHILLI</h3>
        </li>
        <li class="menu-item fadeUp">
          <img src="/assets/images/chilli-sol.webp" alt=" CHILLI SOĽ" class="menu-image">
          <h3 class="name"> CHILLI SOĽ</h3>
        </li>
        <li class="menu-item fadeUp">
          <img src="/assets/images/exktrakt.webp" alt="CHILLI EXTRAKT" class="menu-image">
          <h3 class="name">CHILLI EXTRAKT</h3>
        </li>
      </ul>
    </div>
  </section>

  <div class="gradient-transition3"></div>

  <!-- Testimonials section -->
  <section class="testimonials-section" id="testimonials">
  <br>
  <h2 class="section-title autoshow">Recenzie</h2>
  <div class="section-content fadeUp2">
    <div class="slider-container swiper">
      <div class="slider-wrapper">
        <ul class="testimonials-list swiper-wrapper">
          <?php if (empty($shopReviews)): ?>
            <li class="testimonial swiper-slide">
              <h3 class="name">Zatiaľ bez recenzií</h3>
              <i class="feedback">"Buď prvý, kto pridá recenziu na obchod."</i>
            </li>
          <?php else: ?>
            <?php foreach ($shopReviews as $shopReview): ?>
              <li class="testimonial swiper-slide">
                <h3 class="name"><?php echo htmlspecialchars((string) ($shopReview['reviewer_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h3>
                <div class="review-rating" aria-label="Hodnotenie <?php echo (int) ($shopReview['rating'] ?? 0); ?> z 5">
                  <?php for ($i = 0; $i < (int) ($shopReview['rating'] ?? 0); $i++): ?>
                    <i class="fa-solid fa-star"></i>
                  <?php endfor; ?>
                </div>
                <i class="feedback">
                  "<?php echo nl2br(htmlspecialchars((string) ($shopReview['review_text'] ?? ''), ENT_QUOTES, 'UTF-8')); ?>"
                </i>
              </li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>

        <!-- If we need pagination -->
        <div class="swiper-pagination"></div>
        <!-- If we need navigation buttons -->
        <div class="swiper-slide-button swiper-button-prev"></div>
        <div class="swiper-slide-button swiper-button-next"></div>
      </div>
    </div>
  </div>

  <div class="section-content fadeUp2 testimonials-cta-wrap">
    <a href="<?php echo route('/shop-review'); ?>" class="testimonials-cta-button">Pridať recenziu</a>
  </div>
  </section>

  <div class="gradient-transition4"></div>

  <!-- Contact section -->
    <section class="contact-section fadeUp3" id="contact">
    <br>
    <h2 class="section-title autoshow">Kontaktujte nás</h2>
    <div class="section-content">
      <ul class="contact-info-list">
        <li class="contact-info">
          <i class="fa-solid fa-location-crosshairs"></i>
            <p>SLOVENSKO</p>
        </li>
        <li class="contact-info">
          <i class="fa-regular fa-envelope"> </i>
            <p>info@gmail.com</p>
        </li>
        <li class="contact-info">
          <i class="fa-solid fa-phone"></i>
            <p>421 000 000</p>
        </li>
        <li class="contact-info">
          <i class="fa-regular fa-clock"></i>
            <p>Pondelok - Piatok</p>
            <p>9:00 - 19:00</p>
        </li>
      </ul>

      <form action="<?php echo route('/home#contact'); ?>" class="contact-form" method="POST">
        <input type="hidden" name="form_type" value="contact_message">
        
        <input type="text" name="name" placeholder="Tvoje meno" class="form-input" required 
        value="<?php echo htmlspecialchars($contactFormData['name'], ENT_QUOTES, 'UTF-8'); ?>">

        <input type="email" name="email" placeholder="Tvoj email" class="form-input" required 
        value="<?php echo htmlspecialchars($contactFormData['email'], ENT_QUOTES, 'UTF-8'); ?>">

        <input type="text" name="subject" placeholder="Predmet" class="form-input" required 
        value="<?php echo htmlspecialchars($contactFormData['subject'], ENT_QUOTES, 'UTF-8'); ?>">

        <textarea name="message" placeholder="Tvoja správa" class="form-input" required 
        value="<?php echo htmlspecialchars($contactFormData['message'], ENT_QUOTES, 'UTF-8'); ?>" ></textarea>

        <button type="submit" class="submit-button">Poslať</button>
      </form>

      <?php if (!empty($contactFormSuccess)): ?>
        <p class="form-feedback form-feedback--success">
          <?php echo htmlspecialchars($contactFormSuccess, ENT_QUOTES, 'UTF-8'); ?>
        </p>
      <?php endif; ?>

      <?php if (!empty($contactFormErrors)): ?>
        <div class="form-feedback form-feedback--error" role="alert">
          <?php foreach ($contactFormErrors as $contactFormError): ?>
            <p><?php echo htmlspecialchars((string) $contactFormError, ENT_QUOTES, 'UTF-8'); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    
    </div>
    </section>

<?php
include __DIR__ . '/partials/footer.php';
?>
