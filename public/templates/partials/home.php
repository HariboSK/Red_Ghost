<?php
// Hlavná stránka - index.php
include __DIR__ . '/header.php';

$sendStatus = $_GET['success'] ?? null;
$contactNotice = '';
if ($sendStatus === '1') {
  $contactNotice = 'Sprava bola uspesne odoslana.';
} elseif ($sendStatus === '0') {
  $contactNotice = 'Spravu sa nepodarilo odoslat. Skus to znova neskor alebo nas kontaktuj cez socialne siete.';
}
?>

<?php if ($contactNotice !== ''): ?>
  <div id="sendStatusOverlay" class="<?php echo $sendStatus === '1' ? 'success' : 'error'; ?>" role="status" aria-live="polite">
    <div class="overlay-card"><?php echo htmlspecialchars($contactNotice, ENT_QUOTES, 'UTF-8'); ?></div>
  </div>
<?php endif; ?>

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
            <a href="<?php echo htmlspecialchars($baseUrlEscaped); ?>/e_shop.php" class="button order-now">Objednaj si tu</a>
            <a href="#contact" class="button contact-us">Kontakt na nás</a>
          </div>
        </div>
        <div class="hero-image-wrapper">
            <img src="<?php echo htmlspecialchars($assetBaseEscaped); ?>/images/Copilot_upravene.png" alt="Hero" 
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
          <img src="<?php echo htmlspecialchars($assetBaseEscaped); ?>/images/1111.jpg" alt="O nás" class="about-image">
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
            <img src="<?php echo htmlspecialchars($assetBaseEscaped); ?>/images/klucenka.png" alt=" CHILLI KĽÚČENKY" class="menu-image">
            <h3 class="name"> CHILLI KĽÚČENKY</h3>
            <p class="text">Kľučenky podľa vlasntého výberu farby + vlasný výber náplne do nej 1,50€ do 2,00€</p>
          </li>
          <li class="menu-item fadeUp">
            <img src="<?php echo htmlspecialchars($assetBaseEscaped); ?>/images/omacka3.jpg" alt="CHILLI OMÁČKY" class="menu-image">
            <h3 class="name">CHILLI OMÁČKY</h3>
            <p class="text">Rôzne príchute a druhy chilli omáčiek podľa preferencie intenzity pálivosti a chute</p>
          </li>
          <li class="menu-item fadeUp">
            <img src="<?php echo htmlspecialchars($assetBaseEscaped); ?>/images/chutney.jpg" alt="CHUTNEY" class="menu-image">
            <h3 class="name">CHUTNEY</h3>
          </li>
           <li class="menu-item fadeUp">
            <img src="<?php echo htmlspecialchars($assetBaseEscaped); ?>/images/susene-chilli-Picsart-AiImageEnhancer.jpg" alt="SUŠENÉ PAPRIČKY" class="menu-image">
            <h3 class="name">SUŠENÉ PAPRIČKY</h3>
          </li>
          <li class="menu-item fadeUp">
            <img src="<?php echo htmlspecialchars($assetBaseEscaped); ?>/images/sadenice-Picsart-AiImageEnhancer.png" alt=" CHILLI SADENICE" class="menu-image">
            <h3 class="name"> CHILLI SADENICE</h3>
          </li>
           <li class="menu-item fadeUp">
            <img src="<?php echo htmlspecialchars($assetBaseEscaped); ?>/images/mlete-chilli-Picsart-AiImageEnhancer.jpg" alt=" MLETE CHILLI" class="menu-image">
            <h3 class="name"> MLETE CHILLI</h3>
          </li>
          <li class="menu-item fadeUp">
            <img src="<?php echo htmlspecialchars($assetBaseEscaped); ?>/images/chilli-sol.jpg" alt=" CHILLI SOĽ" class="menu-image">
            <h3 class="name"> CHILLI SOĽ</h3>
          </li>
          <li class="menu-item fadeUp">
            <img src="<?php echo htmlspecialchars($assetBaseEscaped); ?>/images/exktrakt.png" alt="CHILLI EXTRAKT" class="menu-image">
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
            <li class="testimonial swiper-slide">
              <h3 class="name">Dominika Janošíková</h3>
              <i class="feedback">"❤️Ochota-TOP <br>❤️Tovar-TOP "</i>
            </li>
              <li class="testimonial swiper-slide">
              <h3 class="name">Kristína Mravcová</h3>
              <i class="feedback">"Skvelá komunikácia, chutné omáčky. za 1 hodinu zmizla polovica"</i>
            </li>
              <li class="testimonial swiper-slide">
              <h3 class="name">Adam Kováč</h3>
              <i class="feedback">"Mal som tu mozost okostovat Marhulove chutney s chilli.. 
                Chut bola krasne vyvazena.. sladka s tonom stiplavosti. 
                Okostoval som to s masom a krasne to doplnilo chute masa a dodalo mu to uplne iny smrnc.
                Zvysok som zjedol ako detsku vyzivu lyzickou nakolko mi to chutilo 😃 mozem len odporucit. 
                Nevydrzalo mi to ani cestu domov 😃"</i>
            </li>
              <li class="testimonial swiper-slide">
              <h3 class="name">Silvia Tuatitanko</h3>
              <i class="feedback">"Top produkty, komunikacia, rychlost dodania. 
                Nakupovala som a budem nakupovat 🙂👏"</i>
            </li>
              <li class="testimonial swiper-slide">
              <h3 class="name">Juraj Simansky</h3>
              <i class="feedback">"Super produkty zatiaľ som síce skúšal len extrakty ale určite neskôe
                 vyskúšam aj niečo iné."</i>
            </li>
          </ul>

          <!-- If we need pagination -->
          <div class="swiper-pagination"></div>
          <!-- If we need navigation buttons -->
          <div class="swiper-slide-button swiper-button-prev"></div>
          <div class="swiper-slide-button swiper-button-next"></div>
        </div>
      </div>
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
              <p>Pondelok - Nedeľa 9:00 - 19:00</p>           
        </ul>

        <form action="<?php echo htmlspecialchars(rtrim($baseUrl, '/'), ENT_QUOTES, 'UTF-8'); ?>/send-message.php" class="contact-form" method="POST">
          <input type="text" name="name" placeholder="Tvoje meno" class="form-input" required>
          <input type="email" name="email" placeholder="Tvoj email" class="form-input" required>
          <textarea name="message" placeholder="Tvoja správa" class="form-input" required></textarea>

          <!-- Honeypot pole (skryté pre ľudí) -->
          <input type="text" name="robot" style="display:none">

          <!-- Google reCAPTCHA v2 -->
          <div class="g-recaptcha" data-sitekey="6LcM2_0rAAAAAPCxV2J4TstOJOKV15GVPDnuJ3u8 "></div>
          <script src="https://www.google.com/recaptcha/api.js" async defer></script>

          <button type="submit" class="submit-button">Poslať</button>
        </form>

        <div id="confirmBox">✅ Správa bola odoslaná</div>
      
      </div>
     </section>

<?php
include __DIR__ . '/footer.php';
?>
