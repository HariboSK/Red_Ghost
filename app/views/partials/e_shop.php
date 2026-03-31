<?php
include __DIR__ . '/header-shop.php';
require_once dirname(__DIR__, 3) . '/config/config.php';
$assetBase = '/assets';

$featuredProducts = [];
$otherProducts = [];

if (isset($conn) && $conn instanceof mysqli) {
  $sql = 'SELECT id, name, price, rating, featured, image FROM products ORDER BY featured DESC, id ASC';
  $result = $conn->query($sql);

  if ($result instanceof mysqli_result) {
    while ($row = $result->fetch_assoc()) {
      $product = [
        'id' => (int) ($row['id'] ?? 0),
        'name' => (string) ($row['name'] ?? ''),
        'image' => (string) ($row['image'] ?? ''),
        'price' => (float) ($row['price'] ?? 0),
        'rating' => (int) ($row['rating'] ?? 4),
        'featured' => (int) ($row['featured'] ?? 0) === 1,
      ];

      if ($product['image'] === '') {
        $product['image'] = $assetBase . '/images/omacka3.jpg';
      }

      if ($product['featured']) {
        $featuredProducts[] = $product;
      } else {
        $otherProducts[] = $product;
      }
    }

    $result->free();
  }
}

if (count($featuredProducts) === 0 && count($otherProducts) > 0) {
  $featuredProducts = array_slice($otherProducts, 0, min(3, count($otherProducts)));
  $otherProducts = array_slice($otherProducts, min(3, count($otherProducts)));
}

function renderProductCard(array $product): void
{
  $productId = (int) ($product['id'] ?? 0);
  $name = (string) ($product['name'] ?? 'Produkt');
  $image = (string) ($product['image'] ?? '');
  $price = (float) ($product['price'] ?? 0);
  $rating = max(0, min(5, (int) ($product['rating'] ?? 4)));

  echo '<div class="col-4 product-card" data-id="' . $productId . '" data-price="' . $price . '" data-name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">';
  echo '<img src="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">';
  echo '<h4>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</h4>';
  echo '<div class="rating">';
  for ($i = 0; $i < $rating; $i++) {
    echo '<i class="fa fa-star"></i>';
  }
  if ($rating < 5) {
    echo '<i class="far fa-star"></i>';
  }
  echo '</div>';
  echo '<p class="price">' . number_format($price, 2, '.', '') . '€</p>';
  echo '<button class="add-to-cart" onclick="addToCart(' . $productId . ')">Pridať do košíka</button>';
  echo '</div>';
}
?>

<main>
  <!-- Sort section -->
  <div class="container-baseline">
    <div class="row">
      <h2>Všetky produkty</h2>
      <select id="sortBy" onchange="sortProducts()">
        <option value="">Podľa ceny</option>
        <option value="popular">Poďla popularity</option>
        <option value="rating">Podľa hodnotenia</option>
        <option value="discount">V zľave !</option>
      </select>
    </div>
  </div>

  <!-- Produkty -->
  <div class="small-container">
    <h2 class="title">Odporúčané produkty</h2>
    <div class="row" id="productsContainer">
      <?php
      foreach ($featuredProducts as $product) {
        renderProductCard($product);
      }
      ?>
    </div>

    <h2 class="title">Ostatné produkty</h2>
    <div class="row">
      <?php
      foreach ($otherProducts as $product) {
        renderProductCard($product);
      }
      ?>
    </div>
  </div>

</main>

<script src="<?php echo htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8'); ?>/js/shop.js"></script>

<?php
include __DIR__ . '/footer-shop.php';
?>