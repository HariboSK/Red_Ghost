<?php
// E-shop stránka - products.php
// include 'config.php'; // Na pripojenie k databáze keď ju nastavíte

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
        // TODO: Produkty sa budú načítavať z databázy
        // Príklad MySQL query:
        // $query = "SELECT * FROM products WHERE featured = 1";
        // $result = $conn->query($query);
        // while($row = $result->fetch_assoc()) { ... }
        
        // DOČASNE - statické produkty (neskôr z DB)
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

  <script>
    // Vyhľadávanie produktov
    document.getElementById('searchInput').addEventListener('keyup', function() {
      const searchValue = this.value.toLowerCase();
      const products = document.querySelectorAll('.product-card');
      
      products.forEach(product => {
        const productName = product.dataset.name.toLowerCase();
        if(productName.includes(searchValue)) {
          product.style.display = 'block';
        } else {
          product.style.display = 'none';
        }
      });
    });

    // Triedenie produktov
    function sortProducts() {
      const sortBy = document.getElementById('sortBy').value;
      const container = document.getElementById('productsContainer');
      const products = Array.from(container.querySelectorAll('.product-card'));
      
      if(sortBy === 'popular') {
        products.sort((a, b) => b.dataset.price - a.dataset.price);
      } else if(sortBy === '') {
        products.sort((a, b) => a.dataset.price - b.dataset.price);
      }
      
      container.innerHTML = '';
      products.forEach(product => container.appendChild(product));
    }

    // Pridanie do košíka
    function addToCart(id, name, price) {
      let cart = JSON.parse(localStorage.getItem('cart')) || [];
      const existingItem = cart.find(item => item.id === id);
      
      if(existingItem) {
        existingItem.quantity++;
      } else {
        cart.push({id, name, price, quantity: 1});
      }
      
      localStorage.setItem('cart', JSON.stringify(cart));
      alert(name + ' bolo pridané do košíka!');
    }
  </script>

<?php
include __DIR__ . '/footer-shop.php';
?>
