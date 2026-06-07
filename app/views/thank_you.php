<?php
$bodyClass = 'thank-you-page';
$pageTitle = 'Dakujeme za objednavku';
$fixedPointsAwarded = 50;

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/app/core/SessionHelper.php';

SessionHelper::bootstrap();
include __DIR__ . '/partials/header-shop.php';

$orderId = (int) ($_SESSION['checkout_order_id'] ?? 0);
$pointsAwarded = (int) ($_SESSION['checkout_points_awarded'] ?? $fixedPointsAwarded);
$orderMessage = (string) ($_SESSION['checkout_success'] ?? 'Objednávka bola úspešne uložená.');

unset(
    $_SESSION['checkout_order_id'],
    $_SESSION['checkout_points_awarded'],
    $_SESSION['checkout_success']
);
?>

<main class="thank-you-page-shell">
    <section class="thank-you-card">
        <p class="thank-you-kicker">Objednávka úspešne odoslaná</p>
        <h1>Ďakujeme za objednávku</h1>
        <p class="thank-you-message"><?php echo htmlspecialchars($orderMessage, ENT_QUOTES, 'UTF-8'); ?></p>

        <div class="thank-you-grid">
            <div class="thank-you-box">
                <span>Číslo objednávky</span>
                <strong>#<?php echo $orderId > 0 ? htmlspecialchars((string) $orderId, ENT_QUOTES, 'UTF-8') : '—'; ?></strong>
            </div>
            <div class="thank-you-box">
                <span>Pripísané body</span>
                <strong>+<?php echo htmlspecialchars((string) $pointsAwarded, ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div class="thank-you-box thank-you-box--wide">
                <span>Stav</span>
                <strong>Objednávka bola uložená do databázy a loyalty body boli pripísané k účtu.</strong>
            </div>
        </div>

        <div class="thank-you-actions">
            <a href="<?php echo route('/e-shop'); ?>" class="thank-you-primary">Späť do e-shopu</a>
            <a href="<?php echo route('/shopcart'); ?>" class="thank-you-secondary">Zobraziť košík</a>
        </div>
    </section>
</main>

<?php include __DIR__ . '/partials/footer-shop.php'; ?>