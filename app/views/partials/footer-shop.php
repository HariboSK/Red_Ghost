<footer class="footer footer-shop">
  <div class="container footer-shell">
    <div class="footer-brand">
      <img src="/assets/images/155555.webp" alt="Red Ghost logo" class="footer-logo">
      <p>Red Ghost prináša poctivo pestované chilli a výrazné chute pre ľudí, ktorí chcú produkty s charakterom.</p>
      <div class="footer-cta-row">
        <a href="<?php echo route('/e-shop'); ?>" class="footer-cta">E-shop</a>
        <i class="footer-divider">|</i>
        <a href="<?php echo route('/shopcart'); ?>" class="footer-ghost-link">Košík</a>
      </div>
    </div>

    <div class="footer-links">
      <h3>Rýchle odkazy</h3>
      <ul>
        <li><a href="<?php echo route('/e-shop'); ?>">Produkty</a></li>
        <li><a href="<?php echo route('/shopcart'); ?>">Košík</a></li>
        <li><a href="#">Kupóny</a></li>
        <li><a href="#">Doprava a platba</a></li>
      </ul>
    </div>

    <div class="footer-links">
      <h3>Podpora</h3>
      <ul>
        <li><a href="#">Kontakt</a></li>
        <li><a href="#">Reklamácie</a></li>
        <li><a href="#">Obchodné podmienky</a></li>
        <li><a href="#">Privacy Policy</a></li>
      </ul>
    </div>

    <div class="footer-socials">
      <h3>Social</h3>
      <div class="footer-social-list">
        <a href="https://www.facebook.com/profile.php?id=100063660427736" target="_blank" rel="noopener noreferrer" class="social-link1">Facebook</a>
        <a href="https://www.instagram.com/red_ghost_slovakia/?fbclid=IwY2xjawK1m1NleHRuA2FlbQIxMAABHhN7UFe1Z0tFRk3C9Py8Ji1yELLKaEQYjPExpfQD0vmtt5V7jiwRdZisOOXZ_aem_kmrVnxHYgfBFf8ysUkMJnQ" target="_blank" rel="noopener noreferrer" class="social-link2">Instagram</a>
        <a href="mailto:info@redghost.sk" class="footer-mail-link">info@redghost.sk</a>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="container footer-bottom-inner">
      <p class="copyright">&copy; 2025 Red Ghost. Všetky práva vyhradené.</p>
      <p class="footer-note">Chilli, ktoré má chuť aj postoj.</p>
    </div>
  </div>
</footer>

<div class="faq-floating-widget" id="faqWidget">
  <button type="button" class="faq-floating-toggle" id="faqWidgetToggle" aria-expanded="false" aria-controls="faqWidgetPanel" aria-label="Otvoriť FAQ">
    <span class="faq-floating-icon" aria-hidden="true"><i class="fa-solid fa-circle-question"></i></span>
    <span class="faq-floating-label">FAQ</span>
  </button>

  <div class="faq-floating-backdrop" id="faqWidgetBackdrop" hidden></div>

  <section class="faq-floating-panel" id="faqWidgetPanel" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="faqWidgetTitle">
    <div class="faq-floating-header">
      <div>
        <p class="faq-floating-kicker">Pomoc</p>
        <h2 id="faqWidgetTitle">Často kladené otázky</h2>
      </div>
      <button type="button" class="faq-floating-close" id="faqWidgetClose" aria-label="Zatvoriť FAQ">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </div>

    <div class="faq-floating-body">
      <details class="faq-item" open>
        <summary>Ako pridám produkt do košíka?</summary>
        <p>Na produkte klikni na <strong>Pridať do košíka</strong>. Košík sa okamžite aktualizuje v hornej lište.</p>
      </details>

      <details class="faq-item">
        <summary>Prečo nevidím suggestions pri vyhľadávaní?</summary>
        <p>Návrhy sa zobrazia po napísaní aspoň dvoch znakov. Skús názov produktu alebo kategóriu.</p>
      </details>

      <details class="faq-item">
        <summary>Kam sa dostanem po kliknutí na FAQ?</summary>
        <p>Nikam. Otvorí sa len toto okienko priamo na stránke, bez presmerovania.</p>
      </details>

      <details class="faq-item">
        <summary>Čo ak je produkt vypredaný?</summary>
        <p>Vypredané produkty sú označené priamo na karte alebo v banneri a nedajú sa pridať do košíka.</p>
      </details>
    </div>
  </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="<?php echo asset('js/shop.js'); ?>"></script>
</body>

</html>
