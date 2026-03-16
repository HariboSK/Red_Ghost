<?php
// Footer - Globálny footer pre všetky stránky

if (!isset($assetBase)) {
  $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
  $baseUrl = rtrim(dirname($scriptName), '/');
  if ($baseUrl === '/' || $baseUrl === '.') {
    $baseUrl = '';
  }
  $assetBase = rtrim($baseUrl, '/') . '/public/assets';
}

$assetBaseEscaped = htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8');
?>
    <!-- Footer section -->
    <footer class="footer-section">
      <div class="section-content">
        <p class="copyright-text">© 2025 Red_Ghost_Slovakia</p>

        <div class="social-link-list">
            <a href="https://www.facebook.com/profile.php?id=100063660427736" target="_blank" rel="noopener noreferrer" class="social-link1"><i class="fa-brands fa-facebook"></i></a>
            <a href="https://www.instagram.com/red_ghost_slovakia/?fbclid=IwY2xjawK1m1NleHRuA2FlbQIxMAABHhN7UFe1Z0tFRk3C9Py8Ji1yELLKaEQYjPExpfQD0vmtt5V7jiwRdZisOOXZ_aem_kmrVnxHYgfBFf8ysUkMJnQ" target="_blank" rel="noopener noreferrer" class="social-link2"><i class="fa-brands fa-instagram"></i></a>
        </div>

        <p class="policy-text">
          <a href="#" class="policy-link">Privacy policy</a>
          <span class="separator">•</span>
          <a href="#" class="policy-link">Refund policy</a>
        </p>
      </div>
    </footer>
  </main>

  <!-- Scripts -->
  <script src="<?php echo $assetBaseEscaped; ?>/js/animaciaScript.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="<?php echo $assetBaseEscaped; ?>/js/script.js"></script>
</body>
</html>
