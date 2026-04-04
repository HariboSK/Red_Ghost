<?php

if (!function_exists('shop_collect_products')) {
	function shop_collect_products($conn, string $assetBase): array
	{
		$featuredProducts = [];
		$otherProducts = [];
		$allProducts = [];

		if (isset($conn) && $conn instanceof mysqli) {
			$sql = 'SELECT id, name, price, rating, featured, stock, image FROM products ORDER BY featured DESC, id ASC';
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
					'image' => $assetBase . '/images/chilli-sol.jpg',
					'price' => 0,
				],
				[
					'name' => 'Domáce omáčky',
					'image' => $assetBase . '/images/omacky2.jpg',
					'price' => 0,
				],
				[
					'name' => 'Sušené chilli',
					'image' => $assetBase . '/images/susene-chilli-Picsart-AiImageEnhancer.jpg',
					'price' => 0,
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

		echo '<div class="col-4 product-card" data-id="' . $productId . '" data-price="' . $price . '" data-rating="' . $rating . '" data-stock="' . $stock . '" data-name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">';
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
		echo '<div class="stock ' . ($stock > 0 ? 'in-stock' : 'out-of-stock') . '">' . ($stock > 0 ? 'Na sklade: ' . $stock . ' ks' : 'Vypredané') . '</div>';
		echo '<button class="add-to-cart" onclick="addToCart(' . $productId . ')" ' . ($stock <= 0 ? 'disabled' : '') . '>Pridať do košíka</button>';
		echo '</div>';
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
					<div class="swiper-slide">
						<img src="<?php echo htmlspecialchars((string) ($bannerProduct['image'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) ($bannerProduct['name'] ?? 'Produkt'), ENT_QUOTES, 'UTF-8'); ?>">
						<div class="banner-overlay">
							<span class="banner-chip">Nové produkty</span>
							<h2><?php echo htmlspecialchars((string) ($bannerProduct['name'] ?? 'Produkt'), ENT_QUOTES, 'UTF-8'); ?></h2>
							<?php if ((float) ($bannerProduct['price'] ?? 0) > 0): ?>
								<p>Už od <?php echo number_format((float) $bannerProduct['price'], 2, '.', ''); ?> €</p>
							<?php endif; ?>
						</div>
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

