<?php
$bodyClass = 'shopcart-page';
include __DIR__ . '/header-shop.php';
?>

<main class="cart-page">

    <section class="cart-shell">
        <article class="cart-card">
            <div class="cart-head">
                <h1>Tvoj košík</h1>
                <p class="items-count">3 položky</p>
            </div>

            <div class="cart-items">
                <div class="cart-item">
                    <img src="/assets/images/omacka3.jpg" alt="Pálivá omáčka">
                    <div class="item-info">
                        <h3>Pálivá omáčka</h3>
                        <p>Kategória: Omáčky</p>
                    </div>
                    <div class="item-controls">
                        <div class="qty-box">
                            <button type="button">-</button>
                            <span>1</span>
                            <button type="button">+</button>
                        </div>
                        <p class="item-price">10,00 EUR</p>
                    </div>
                </div>

                <div class="cart-item">
                    <img src="/assets/images/chilli-sol.jpg" alt="Chilli soľ">
                    <div class="item-info">
                        <h3>Chilli soľ</h3>
                        <p>Kategória: Koreniny</p>
                    </div>
                    <div class="item-controls">
                        <div class="qty-box">
                            <button type="button">-</button>
                            <span>2</span>
                            <button type="button">+</button>
                        </div>
                        <p class="item-price">10,00 EUR</p>
                    </div>
                </div>

                <div class="cart-item">
                    <img src="/assets/images/klucenka.png" alt="Kľúčenka na cestu">
                    <div class="item-info">
                        <h3>Kľúčenka na cestu</h3>
                        <p>Kategória: Merch</p>
                    </div>
                    <div class="item-controls">
                        <div class="qty-box">
                            <button type="button">-</button>
                            <span>1</span>
                            <button type="button">+</button>
                        </div>
                        <p class="item-price">3,00 EUR</p>
                    </div>
                </div>
            </div>
        </article>

        <aside class="summary-card">
            <h2>Zhrnutie objednávky</h2>
            <div class="summary-list">
                <div class="summary-row">
                    <span>Medzisúčet</span>
                    <strong>23,00 EUR</strong>
                </div>
                <div class="summary-row">
                    <span>Doprava</span>
                    <strong>3,90 EUR</strong>
                </div>
                <div class="summary-row">
                    <span>Zľava</span>
                    <strong>-0,00 EUR</strong>
                </div>
                <div class="summary-row total">
                    <span>Spolu</span>
                    <strong>26,90 EUR</strong>
                </div>
            </div>

            <button type="button" class="checkout-btn">Pokračovať na objednávku</button>
            <a class="continue-link" href="<?php echo route('/e-shop'); ?>">Späť do e-shopu</a>
        </aside>
    </section>
</main>

<?php include __DIR__ . '/footer-shop.php'; ?>