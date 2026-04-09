SPRAVIT
- [ ] Admin panel na správu produktov
- [ ] Nákupný košík s uložením do databázy
- [ ] Checkout a platby
- [ ] Užívateľské konta
- [ ] Potvrdenie objednávky emailom
- [ ] pridanie prepinanie light a dark mode 
- [ ] pridanie toggle button na zmenu jazyka
- [ ] uprava css login aj php 


cart.php 
shop.js


Pozriem sa na váš add-to-cart endpoint a navrhnem presnú server-side kontrolu skladu priamo pre váš projekt, aby to sedelo na existujúci kód a nie len všeobecný príklad.

V tvojom prípade to znamená: pred pridaním do košíka si na serveri načítaš aj stock z databázy a porovnáš ho s množstvom v košíku. Teraz to v cart.php:21 vracia len id, name, price, takže stock sa zatiaľ nekontroluje.

Postup je tento:

V SELECTe pridaj stock
V funkcii get_product_by_id uprav dotaz tak, aby čítal aj stock z products.

Vráť stock z funkcie
Do poľa, ktoré funkcia vracia, pridaj aj stock.

Pred pridaním položky over dostupnosť
V časti action = add skontroluj, či už nemáš v košíku viac kusov, než je na sklade.

Logika pre add:

načítaj produkt z DB
zober currentQty z session košíka
ak currentQty + 1 > stock, vráť chybu
inak zvýš quantity
Princíp:

ak stock = 3 a v košíku sú 2 kusy, ďalší add ešte prejde
ak stock = 3 a v košíku sú 3 kusy, add sa zastaví
To isté sprav aj pri update
V časti update nesmieš dovoliť quantity vyššie než stock.
Príklad logiky, ktorú máš doplniť do cart.php:97:

pri add:
ak stock <= existujúce množstvo v košíku, vráť 409 a hlášku typu „Na sklade nie je dosť kusov“

pri update:
ak quantity > stock, tiež vráť 409

Ak chceš, toto je presne ten kus logiky v slovách:

SELECT id, name, price, stock FROM products WHERE id = ?
return aj stock
v add:
currentQty = $_SESSION['cart'][$productId]['quantity'] ?? 0
if currentQty + 1 > product['stock'] => chyba
v update:
if quantity > product['stock'] => chyba
Ak chceš, v ďalšej správe ti napíšem priamo hotový blok pre add a update do tvojho súboru tak, aby si to len vložil.


CTRL CSS PEEK




Dobre — tu je krátky, praktický návod a konkrétne kroky, ako product.php zjednodušiť a spraviť prehľadnejším.

Hlavné zásady

Oddeliť logiku (načítanie dát) od šablóny (HTML).
Premiestniť DB/obchodnú logiku do modelu/service (funkcie mimo view).
Použiť malé helpery: e() pre escaping, asset() pre cesty, route() pre URL.
Minimalizovať inline PHP v šablóne — view iba vypisuje premenné a volá malé helpers.
Použiť komponenty/partials (header, footer, product-card, review-item).

Krátky plán refaktoru






<?php
// app/core/product_controller.php
require_once dirname(__DIR__) . '/models/product.model.php';
require_once dirname(__DIR__) . '/core/view_helpers.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$assetBase = '/assets';
$product = product_find_by_id($id); // vráti asociatívne pole alebo null

if (!$product) {
  http_response_code(404);
  require __DIR__ . '/../views/404.php';
  exit;
}

// príprava dát pre view
$pageTitle = $product['name'] . ' | Red Ghost';
$extraStyles = ['/assets/css/productview.css'];
require __DIR__ . '/../views/product.php';







Vytvoriť kontroler app/core/product_controller.php — načíta produkt, pripraví premenné a includne view.
Presunúť DB volanie do app/models/product.model.php alebo shop_functions.php ak už tam sú.
Upraviť product.php tak, aby bola čistá šablóna — žiadne DB volania, len zobrazovanie hodnôt.
Pridať malé helpery (app/core/view_helpers.php) pre e(), asset(), route().
Ukážka — jednoduchý controller (nový súbor, behie pred includom view)

Ukážka — zjednodušená view (iba zobrazovanie)






<?php
// app/views/product.php (len relevantné časti)
<?php require __DIR__ . '/partials/header-shop.php'; ?>

<main>
  <h1><?= e($product['name']) ?></h1>
  <p class="price"><?= e(format_price($product['price'])) ?> EUR</p>
  <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
  <p><?= nl2br(e($product['description'])) ?></p>

  <button <?= ((int)$product['stock'] <= 0) ? 'disabled' : '' ?>
    onclick="addToCart(<?= (int)$product['id'] ?>)">Pridať do košíka</button>
</main>

<?php require __DIR__ . '/partials/footer-shop.php'; ?>







Konkrétne refaktor návrhy pre tvoj súbor

Presuň require_once dirname(__DIR__, 2) . '/config/config.php'; do controlleru/indexu (alebo nechaj, ak používaš require_once všade — nie je chyba).
Namiesto inline funkcie call_user_func použi v controlleri shop_product_spicy_label() a pošli hotové ['text','class'] do view.
route('/e-shop') a assetBase používaj cez helpery, aby v šablóne bol len href="<?= route('e-shop') ?>" a src="<?= asset('images/chilli.png') ?>".
Extrahuj repeatované HTML fragmenty (review-item, key-fact) do partials alebo malé funkcie.

Malé helpery, ktoré odporúčam pridať


<?php
// app/core/view_helpers.php
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function asset($path){ return '/' . ltrim($path, '/'); }
function format_price($n){ return number_format((float)$n, 2, '.', ''); }







Ďalšie zlepšenia (voliteľné)

Použiť jednoduchý templating (Twig/Plates) pre čistejšie šablóny.
Validovať/typovať $product polia v modeli.
Cache častí (recenzií) ak sú drahé.

vytvoriť product_controller.php + view_helpers.php a upraviť product.php podľa príkladu, alebo
iba pripraviť patch s rozdelením kódu (controller + čistá view). Ktorú možnosť preferuješ?
