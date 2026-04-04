<?php

if (!function_exists('shop_collect_products')) {
	function shop_collect_products($conn, string $assetBase): array
	{
		$featuredProducts = [];
		$otherProducts = [];
		$allProducts = [];

		if (isset($conn) && $conn instanceof mysqli) {
			$sql = 'SELECT id, name, description, price, rating, featured, stock, image FROM products ORDER BY featured DESC, id ASC';
			$result = $conn->query($sql);

			if ($result instanceof mysqli_result) {
				while ($row = $result->fetch_assoc()) {
					$product = [
						'id' => (int) ($row['id'] ?? 0),
						'name' => (string) ($row['name'] ?? ''),
						'description' => (string) ($row['description'] ?? ''),
						'image' => (string) ($row['image'] ?? ''),
						'price' => (float) ($row['price'] ?? 0),
						'rating' => (int) ($row['rating'] ?? 4),
						'featured' => (int) ($row['featured'] ?? 0) === 1,
						'stock' => (int) ($row['stock'] ?? 0),
					];

					if ($product['image'] === '') {
						$product['image'] = $assetBase . '/images/omacka3.jpg';
					}

					if ($product['featured']) {
						$featuredProducts[] = $product;
					} else {
						$otherProducts[] = $product;
					}

					$allProducts[] = $product;
				}

				$result->free();
			}
		}

		if (count($featuredProducts) === 0 && count($otherProducts) > 0) {
			$featuredProducts = array_slice($otherProducts, 0, min(3, count($otherProducts)));
			$otherProducts = array_slice($otherProducts, min(3, count($otherProducts)));
		}

		$newProducts = $allProducts;
		usort($newProducts, function (array $a, array $b): int {
			return (int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0);
		});
		$newProducts = array_slice($newProducts, 0, 4);

		if (count($newProducts) === 0) {
			$newProducts = [
				[
					'name' => 'Nové chilli produkty',
					'description' => 'Špeciálne chilli omáčky pripravené s láskou od našich pestovateľov.',
					'image' => $assetBase . '/images/chilli-sol.jpg',
					'price' => 0,
					'stock' => 10,
					'id' => 1,
				],
				[
					'name' => 'Domáce omáčky',
					'description' => 'Autentické receptúry tradičnej slovenskej kuchyne s chilli.',
					'image' => $assetBase . '/images/omacky2.jpg',
					'price' => 0,
					'stock' => 10,
					'id' => 2,
				],
				[
					'name' => 'Sušené chilli',
					'description' => 'Prírodne sušené chilli papriky bez chemických прідатків.',
					'image' => $assetBase . '/images/susene-chilli-Picsart-AiImageEnhancer.jpg',
					'price' => 0,
					'stock' => 10,
					'id' => 3,
				],
			];
		}

		return [
			'featuredProducts' => $featuredProducts,
			'otherProducts' => $otherProducts,
			'newProducts' => $newProducts,
		];
	}
}

if (!function_exists('shop_render_product_card')) {
	function shop_render_product_card(array $product): void
	{
		$productId = (int) ($product['id'] ?? 0);
		$name = (string) ($product['name'] ?? 'Produkt');
		$image = (string) ($product['image'] ?? '');
		$price = (float) ($product['price'] ?? 0);
		$rating = max(0, min(5, (int) ($product['rating'] ?? 4)));
		$stock = (int) ($product['stock'] ?? 0);
		$productUrl = function_exists('route') ? route('/product?id=' . $productId) : '/product?id=' . $productId;

		echo '<div class="col-4 product-card" data-id="' . $productId . '" data-price="' . $price . '" data-rating="' . $rating . '" data-stock="' . $stock . '" data-name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">';
		echo '<a class="product-link" href="' . htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') . '">';
		echo '<img src="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">';
		echo '<h4>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</h4>';
		echo '</a>';
		echo '<div class="rating">';
		for ($i = 0; $i < $rating; $i++) {
			echo '<i class="fa fa-star"></i>';
		}
		if ($rating < 5) {
			echo '<i class="far fa-star"></i>';
		}
		echo '</div>';
		echo '<p class="price">' . number_format($price, 2, '.', '') . '€</p>';
		echo '<div class="stock ' . ($stock > 0 ? 'in-stock' : 'out-of-stock') . '">' . ($stock > 0 ? 'Na sklade: ' . $stock . ' ks' : 'Vypredané') . '</div>';
		echo '<button class="add-to-cart" onclick="addToCart(' . $productId . ')" ' . ($stock <= 0 ? 'disabled' : '') . '>Pridať do košíka</button>';
		echo '</div>';
	}
}

if (!function_exists('shop_get_product_by_id')) {
	function shop_get_product_by_id($conn, int $productId, string $assetBase): ?array
	{
		if (!($conn instanceof mysqli) || $productId <= 0) {
			return null;
		}

		$stmt = $conn->prepare('SELECT id, name, description, image, price, rating, stock, category FROM products WHERE id = ? LIMIT 1');
		if (!($stmt instanceof mysqli_stmt)) {
			return null;
		}

		$stmt->bind_param('i', $productId);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = ($result instanceof mysqli_result) ? $result->fetch_assoc() : null;
		$stmt->close();

		if (!is_array($row)) {
			return null;
		}

		$image = (string) ($row['image'] ?? '');
		if ($image === '') {
			$image = $assetBase . '/images/omacka3.jpg';
		}

		return [
			'id' => (int) ($row['id'] ?? 0),
			'name' => (string) ($row['name'] ?? 'Produkt'),
			'description' => (string) ($row['description'] ?? ''),
			'image' => $image,
			'price' => (float) ($row['price'] ?? 0),
			'rating' => max(0, min(5, (int) ($row['rating'] ?? 0))),
			'stock' => (int) ($row['stock'] ?? 0),
			'category' => (string) ($row['category'] ?? 'Chilli produkt'),
		];
	}
}

// Pomocné funkcie pre zobrazenie detailu produktu

if (!function_exists('shop_product_spicy_label')) {
	function shop_product_spicy_label(int $rating): array
	{
		if ($rating <= 1) {
			return ['Jemna', 'mild'];
		}
		if ($rating <= 3) {
			return ['Stredna', 'medium'];
		}
		return ['Extremna', 'hot'];
	}
}

if (!function_exists('shop_product_reviews')) {
	function shop_product_reviews(array $product): array
	{
		$rating = max(1, min(5, (int) ($product['rating'] ?? 3)));
		return [
			[
				'name' => 'Marek K.',
				'title' => 'Vyborna chut aj palivost',
				'text' => 'Vyrazna chut paprik, dobra konzistencia a prijemny aftertaste. Urcite objednam znova.',
				'rating' => $rating,
				'date' => '12.03.2026',
			],
			[
				'name' => 'Petra S.',
				'title' => 'Kvalitny produkt',
				'text' => 'Pouzivam do varenia aj ku grilovanemu masu. Chut je stabilna a produkt prisiel bez problemov.',
				'rating' => max(1, $rating - 1),
				'date' => '03.03.2026',
			],
			[
				'name' => 'Roman T.',
				'title' => 'Presne to, co som hladal',
				'text' => 'Dobry pomer cena/kvalita. Ak mas rad chilli, tento produkt nesklame.',
				'rating' => $rating,
				'date' => '25.02.2026',
			],
		];
	}
}

if (!function_exists('shop_render_main')) {
	function shop_render_main(array $data): void
	{
		$featuredProducts = $data['featuredProducts'] ?? [];
		$otherProducts = $data['otherProducts'] ?? [];
		$newProducts = $data['newProducts'] ?? [];
		?>
<main class="shop-main">
	<section class="shop-banner">
		<div class="swiper shopBannerSwiper">
			<div class="swiper-wrapper">
				<?php foreach ($newProducts as $bannerProduct): ?>
					<div class="swiper-slide product-showcase">
						<!-- Diagonálny pás nalavo -->
						<div class="diagonal-stripe diagonal-left"></div>
						
						<!-- Produktová fotka -->
						<div class="showcase-image">
							<img src="<?php echo htmlspecialchars((string) ($bannerProduct['image'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) ($bannerProduct['name'] ?? 'Produkt'), ENT_QUOTES, 'UTF-8'); ?>">
						</div>
						
						<!-- Produktové info -->
						<div class="showcase-info">
							<span class="badge">Nové produkty</span>
							<h2><?php echo htmlspecialchars((string) ($bannerProduct['name'] ?? 'Produkt'), ENT_QUOTES, 'UTF-8'); ?></h2>
							<?php if ((float) ($bannerProduct['price'] ?? 0) > 0): ?>
								<p class="price"><?php echo number_format((float) $bannerProduct['price'], 2, '.', ''); ?> €</p>
							<?php endif; ?>
							<?php if (!empty($bannerProduct['description'])): ?>
								<p class="description"><?php echo htmlspecialchars((string) $bannerProduct['description'], ENT_QUOTES, 'UTF-8'); ?></p>
							<?php endif; ?>
							<button class="add-to-cart-btn" onclick="addToCart(<?php echo (int) ($bannerProduct['id'] ?? 0); ?>)"><?php echo ((int) ($bannerProduct['stock'] ?? 0) > 0) ? 'Pridať do košíka' : 'Vypredané'; ?></button>
						</div>
						
						<!-- Diagonálny pás napravo -->
						<div class="diagonal-stripe diagonal-right"></div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="swiper-pagination"></div>
			<div class="swiper-slide-button swiper-button-prev"></div>
			<div class="swiper-slide-button swiper-button-next"></div>
		</div>
	</section>

	<section class="small-container shop-controls">
		<h2>Všetky produkty</h2>
		<div class="controls-row">
			<label class="shop-search" for="searchInput">
				<i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
				<input type="text" id="searchInput" placeholder="Hľadaj podľa názvu produktu...">
			</label>

			<select id="sortBy" onchange="sortProducts()" aria-label="Triedenie produktov">
				<option value="price-asc">Cena: od najnižšej</option>
				<option value="price-desc">Cena: od najvyššej</option>
				<option value="rating">Podľa hodnotenia</option>
				<option value="stock">Najviac skladom</option>
			</select>
		</div>
	</section>

	<div class="small-container shop-sections">
		<h2 class="title">Odporúčané produkty</h2>
		<div class="row product-grid" id="featuredProductsContainer">
			<?php
			foreach ($featuredProducts as $product) {
				shop_render_product_card($product);
			}
			?>
		</div>

		<h2 class="title">Ostatné produkty</h2>
		<div class="row product-grid" id="productsContainer">
			<?php
			foreach ($otherProducts as $product) {
				shop_render_product_card($product);
			}
			?>
		</div>
	</div>
</main>
<?php
	}
}

