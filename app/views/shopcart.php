<?php
$bodyClass = 'shopcart-page';
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/app/core/SessionHelper.php';

SessionHelper::bootstrap();

function NormalizeImagePath(string $image): string
{
    $image = trim($image);

    if ($image === '') {
        return '/assets/images/omacka3.webp';
    }

    if (preg_match('~^(https?:)?//~i', $image) === 1 || strpos($image, '/') === 0) {
        return preg_replace('~\.(jpe?g)$~i', '.webp', $image);
    }

    return preg_replace('~\.(jpe?g)$~i', '.webp', '/assets/images/' . ltrim($image, '/'));
}

$cartItems = [];
$cartSummary = [
    'count' => 0,
    'subtotal' => 0.0,
    'shipping' => 0.0,
    'discount' => 0.0,
    'total' => 0.0,
];
$discountFlash = null;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $productId => $item) {
        $quantity = max(0, (int) ($item['quantity'] ?? 0));
        if ($quantity <= 0) {
            continue;
        }

        $productId = (int) ($item['id'] ?? $productId);
        $price = (float) ($item['price'] ?? 0);
        $cartItems[] = [
            'id' => $productId,
            'name' => (string) ($item['name'] ?? 'Produkt'),
            'price' => $price,
            'quantity' => $quantity,
            'image' => NormalizeImagePath((string) ($item['image'] ?? '')),
        ];

        $cartSummary['count'] += $quantity;
        $cartSummary['subtotal'] += $price * $quantity;
    }
}

$cartSummary['shipping'] = $cartSummary['count'] > 0 ? 3.9 : 0.0;
$cartSummary['discount'] = abs((float) ($_SESSION['applied_discount_amount'] ?? 0));
$discountFromFlash = null;

if (isset($_SESSION['discount_flash']) && is_array($_SESSION['discount_flash'])) {
    $discountFlash = $_SESSION['discount_flash'];
    $discountFromFlash = abs((float) ($discountFlash['amount'] ?? 0));
    unset($_SESSION['discount_flash']);
}

if ($cartSummary['discount'] <= 0 && $discountFromFlash !== null) {
    $cartSummary['discount'] = $discountFromFlash;
}

$subtotalAfterDiscount = max(0, $cartSummary['subtotal'] - $cartSummary['discount']);
$cartSummary['total'] = $subtotalAfterDiscount + $cartSummary['shipping'];

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

            <div class="cart-items" id="cartPageItemsList">
                <?php if (!empty($cartItems)): ?>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <img src="<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="item-info">
                                <h3><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p>Množstvo: <?php echo (int) $item['quantity']; ?></p>
                            </div>
                            <div class="item-controls">
                                <p class="item-price"><?php echo number_format($item['price'] * $item['quantity'], 2, ',', ' '); ?> EUR</p>

                                <div class="cart-item-controls">
                                    <form method="POST" action="<?php echo route('/api/RemoveCart.php'); ?>" class="cart-action-form">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars((string) $item['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/shopcart', ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" name="action" value="decrement">-</button>
                                    </form>

                                    <form method="POST" action="<?php echo route('/api/RemoveCart.php'); ?>" class="cart-action-form">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars((string) $item['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/shopcart', ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" name="action" value="increment">+</button>
                                    </form>

                                    <form method="POST" action="<?php echo route('/api/RemoveCart.php'); ?>" class="cart-action-form">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars((string) $item['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/shopcart', ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" name="action" value="remove">Odstrániť</button>
                                    </form>
                                </div>
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
                    <strong id="cartPageDiscount">-<?php echo number_format($cartSummary['discount'], 2, ',', ' '); ?> EUR</strong>
                </div>
                <div class="summary-row total">
                    <span>Spolu</span>
                    <strong id="cartPageTotal"><?php echo number_format($cartSummary['total'], 2, ',', ' '); ?> EUR</strong>
                </div>
            </div>

            <form method="POST" action="<?php echo route('/api/ApplyDiscount.php'); ?>" class="discount-form">
                <label for="discount_code">Zľavový kód</label>
                <div style="display:flex;gap:8px;margin-top:6px;">
                    <input id="discount_code" name="code" type="text" placeholder="Zadaj kód" style="flex:1;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,0.06);" value="<?php echo htmlspecialchars((string) ($_SESSION['applied_discount_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="apply-discount-btn">Použiť</button>
                </div>
                <?php if (is_array($discountFlash)): ?>
                    <div class="discount-message discount-message--<?php echo htmlspecialchars((string) ($discountFlash['type'] ?? 'info'), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars((string) ($discountFlash['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($cartSummary['discount'])): ?>
                    <p class="discount-applied">Aplikovaná zľava: <?php echo number_format($cartSummary['discount'], 2, ',', ' '); ?> EUR</p>
                <?php endif; ?>
            </form>

            <button type="button" class="checkout-btn" id="checkoutBtn" data-payment-url="<?php echo route('/payment'); ?>" <?php echo $cartSummary['count'] <= 0 ? 'disabled' : ''; ?>>Pokračovať na objednávku</button>
            <a class="continue-link" href="<?php echo route('/e-shop'); ?>">Späť do e-shopu</a>
        </aside>
    </section>
</main>

<?php include __DIR__ . '/partials/footer-shop.php'; ?>
