<?php
$bodyClass = 'shopcart-page';
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/app/core/session_helper.php';

SessionHelper::bootstrap();

$cartItems = [];
$cartSummary = [
    'count' => 0,
    'subtotal' => 0.0,
    'shipping' => 0.0,
    'discount' => 0.0,
    'total' => 0.0,
];

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $quantity = max(0, (int) ($item['quantity'] ?? 0));
        if ($quantity <= 0) {
            continue;
        }

        $price = (float) ($item['price'] ?? 0);
        $cartItems[] = [
            'name' => (string) ($item['name'] ?? 'Produkt'),
            'price' => $price,
            'quantity' => $quantity,
            'image' => (string) ($item['image'] ?? '/assets/images/omacka3.jpg'),
        ];

        $cartSummary['count'] += $quantity;
        $cartSummary['subtotal'] += $price * $quantity;
    }
}

$cartSummary['shipping'] = $cartSummary['count'] > 0 ? 3.9 : 0.0;
$cartSummary['total'] = $cartSummary['subtotal'] + $cartSummary['shipping'];

include __DIR__ . '/partials/header-shop.php';
?>

<main class="cart-page">
    <section class="cart-shell">
        <article class="cart-card">
            <div class="cart-head">
                <h1>Tvoj košík</h1>
                <p class="items-count" id="cartPageCount"><?php echo (int) $cartSummary['count']; ?> položiek</p>
            </div>

            <p class="cart-notice" id="cartPageMessage"><?php echo $cartSummary['count'] > 0 ? 'Košík je pripravený.' : 'Košík je prázdny.'; ?></p>

            <div class="cart-items" id="cartPageItemsList" data-cart-page-items>
                <?php if (!empty($cartItems)): ?>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <img src="<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="item-info">
                                <h3><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p>Množstvo: <?php echo (int) $item['quantity']; ?></p>
                            </div>
                            <div class="item-controls">
                                <div class="qty-box">
                                    <button type="button" aria-label="Znížiť množstvo">-</button>
                                    <span><?php echo (int) $item['quantity']; ?></span>
                                    <button type="button" aria-label="Zvýšiť množstvo">+</button>
                                </div>
                                <p class="item-price"><?php echo number_format($item['price'] * $item['quantity'], 2, ',', ' '); ?> EUR</p>
                                <button type="button" class="item-remove">Odstrániť produkt</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="cart-empty">Košík je prázdny.</div>
                <?php endif; ?>
            </div>
        </article>

        <aside class="summary-card">
            <h2>Zhrnutie objednávky</h2>
            <div class="summary-list">
                <div class="summary-row">
                    <span>Medzisúčet</span>
                    <strong id="cartPageSubtotal"><?php echo number_format($cartSummary['subtotal'], 2, ',', ' '); ?> EUR</strong>
                </div>
                <div class="summary-row">
                    <span>Doprava</span>
                    <strong id="cartPageShipping"><?php echo number_format($cartSummary['shipping'], 2, ',', ' '); ?> EUR</strong>
                </div>
                <div class="summary-row">
                    <span>Zľava</span>
                    <strong id="cartPageDiscount"><?php echo number_format($cartSummary['discount'], 2, ',', ' '); ?> EUR</strong>
                </div>
                <div class="summary-row total">
                    <span>Spolu</span>
                    <strong id="cartPageTotal"><?php echo number_format($cartSummary['total'], 2, ',', ' '); ?> EUR</strong>
                </div>
            </div>

            <button type="button" class="checkout-btn" id="checkoutBtn">Pokračovať na objednávku</button>
            <a class="continue-link" href="<?php echo route('/e-shop'); ?>">Späť do e-shopu</a>
        </aside>
    </section>
</main>

<?php include __DIR__ . '/partials/footer-shop.php'; ?>