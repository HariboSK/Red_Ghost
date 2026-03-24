<?php
include __DIR__ . '/header-shop.php';
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

      <!-- Searchbar -->
      <div class="nav-bar-searchbar">
        <div class="search-bar-container">
          <input type="text" id="searchInput" placeholder="Hľadaj produkty..." class="search-bar-input">
          <a href="#" class="search_icon"><i class="fas fa-search"></i></a>
        </div>
      </div>
    </div>

    <!-- Produkty -->
    <div class="small-container">
      <h2 class="title">Odporúčané produkty</h2>
      <div class="row" id="productsContainer">
        <?php
        $products = array(
          array(
            'id' => 1,
            'name' => 'Pálivá omáčka',
            'image' => $assetBase . '/images/omacka3.jpg',
            'price' => 10,
            'rating' => 4,
            'featured' => true
          ),
          array(
            'id' => 2,
            'name' => 'Kľúčenka na cestu',
            'image' => $assetBase . '/images/klucenka.png',
            'price' => 3,
            'rating' => 4,
            'featured' => true
          ),
          array(
            'id' => 3,
            'name' => 'Chilli soľ',
            'image' => $assetBase . '/images/chilli-sol.jpg',
            'price' => 5,
            'rating' => 4,
            'featured' => true
          ),
          array(
            'id' => 4,
            'name' => 'Sadenice',
            'image' => $assetBase . '/images/sadenice-Picsart-AiImageEnhancer.png',
            'price' => 10,
            'rating' => 4,
            'featured' => false
          ),
          array(
            'id' => 5,
            'name' => 'Extrakt z chilli',
            'image' => $assetBase . '/images/exktrakt.png',
            'price' => 10,
            'rating' => 4,
            'featured' => false
          )
        );

        foreach($products as $product) {
          echo '<div class="col-4 product-card" data-id="' . $product['id'] . '" data-price="' . $product['price'] . '" data-name="' . $product['name'] . '">';
          echo '<img src="' . $product['image'] . '" alt="' . $product['name'] . '">';
          echo '<h4>' . $product['name'] . '</h4>';
          echo '<div class="rating">';
          for($i = 0; $i < $product['rating']; $i++) {
            echo '<i class="fa fa-star"></i>';
          }
          if($product['rating'] < 5) {
            echo '<i class="far fa-star"></i>';
          }
          echo '</div>';
          echo '<p class="price">' . $product['price'] . '€</p>';
          echo '<button class="add-to-cart" onclick="addToCart(' . $product['id'] . ', \'' . addslashes($product['name']) . '\', ' . $product['price'] . ')">Pridať do košíka</button>';
          echo '</div>';
        }
        ?>
      </div>

      <h2 class="title">Ostatné produkty</h2>
      <div class="row">
        <?php
        $otherProducts = array(
          array(
            'id' => 6,
            'name' => 'Mleté chilli',
            'image' => $assetBase . '/images/mlete-chilli-Picsart-AiImageEnhancer.jpg',
            'price' => 7,
            'rating' => 4
          ),
          array(
            'id' => 7,
            'name' => 'Sušené papričky',
            'image' => $assetBase . '/images/susene-chilli-Picsart-AiImageEnhancer.jpg',
            'price' => 8,
            'rating' => 4
          ),
          array(
            'id' => 8,
            'name' => 'Chutney',
            'image' => $assetBase . '/images/chutney.jpg',
            'price' => 6,
            'rating' => 4
          )
        );

        foreach($otherProducts as $product) {
          echo '<div class="col-4 product-card" data-id="' . $product['id'] . '" data-price="' . $product['price'] . '" data-name="' . $product['name'] . '">';
          echo '<img src="' . $product['image'] . '" alt="' . $product['name'] . '">';
          echo '<h4>' . $product['name'] . '</h4>';
          echo '<div class="rating">';
          for($i = 0; $i < $product['rating']; $i++) {
            echo '<i class="fa fa-star"></i>';
          }
          echo '</div>';
          echo '<p class="price">' . $product['price'] . '€</p>';
          echo '<button class="add-to-cart" onclick="addToCart(' . $product['id'] . ', \'' . addslashes($product['name']) . '\', ' . $product['price'] . ')">Pridať do košíka</button>';
          echo '</div>';
        }
        ?>
      </div>
    </div>

  </main>

<script src="<?php echo htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8'); ?>/js/shop.js"></script>

<?php
include __DIR__ . '/footer-shop.php';
?>
