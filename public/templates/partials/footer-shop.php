<?php
// Footer pre e-shop stranku - oddeleny od hlavnej stranky

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
  <div class="footer">
    <div class="container">
      <div class="row">
        <div class="footer-col-1">
          <img src="<?php echo htmlspecialchars($assetBaseEscaped); ?>/images/155555.jpg" alt="Red Ghost farma">
          <p>Nasim cielom je priniest ludom kvalitne, poctivo pestovane chilli, ktore nielen rozohreje chutove pohariky, ale aj rozprudni vasen pre odvazne chute a autenticke zazitky.</p>
        </div>
        <div class="footer-col-2">
          <h3>Uzitocne linky</h3>
          <ul>
            <li><a href="#" class="social-link">Kupony</a></li>
            <li><a href="#" class="social-link">Privacy Policy</a></li>
          </ul>
        </div>
        <div class="footer-col-3">
          <h3>Sleduj nas na socialnych sietach</h3>
          <ul>
            <li><a href="https://www.facebook.com/profile.php?id=100063660427736" target="_blank" rel="noopener noreferrer" class="social-link1">Facebook</a></li>
            <li><a href="https://www.instagram.com/red_ghost_slovakia/?fbclid=IwY2xjawK1m1NleHRuA2FlbQIxMAABHhN7UFe1Z0tFRk3C9Py8Ji1yELLKaEQYjPExpfQD0vmtt5V7jiwRdZisOOXZ_aem_kmrVnxHYgfBFf8ysUkMJnQ" target="_blank" rel="noopener noreferrer" class="social-link2">Instagram</a></li>
          </ul>
        </div>
      </div>
      <hr>
      <p class="copyright">&copy; 2025 Red Ghost. Vsetky prava vyhradene.</p>
    </div>
  </div>

</main>
</body>
</html>