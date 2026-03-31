<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: /login.php');
    exit();
}

include __DIR__ . '/dashboard-header.php';

?>

<main class="dashboard-page">
    <section class="dashboard-topbar">
        <div>
            <p class="dash-eyebrow">Admin panel</p>
            <h1>Dashboard</h1>
            <p class="dash-title">Vitaj,<span><?= $_SESSION['name']; ?></span> v sprave tvojho e-shopu</p>
            <p class="dash-subtitle">Prehlad objednavok, predaja a stavu produktov na jednom mieste.</p>
        </div>
        <div class="dash-actions">
            <a href="<?php echo route('/e-shop'); ?>" class="dash-btn primary">E-shop</a>
            <a href="<?php echo route('/shopcart'); ?>" class="dash-btn ghost">Kosik</a>
            <a href="<?php echo route('/logout'); ?>" class="dash-logout-icon" title="Odhlasit sa"
                aria-label="Odhlasit sa">
                <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                <span>Odhlasit sa</span>
            </a>
        </div>
    </section>

    <section class="dash-kpi-grid">
        <article class="dash-kpi-card">
            <h3>Objednavky dnes</h3>
            <p class="value">24</p>
            <p class="meta">+12% oproti vcera</p>
        </article>
        <article class="dash-kpi-card">
            <h3>Trzba dnes</h3>
            <p class="value">568 EUR</p>
            <p class="meta">Priemer objednavky 23.7 EUR</p>
        </article>
        <article class="dash-kpi-card">
            <h3>Nove registracie</h3>
            <p class="value">7</p>
            <p class="meta">2 caka na potvrdenie</p>
        </article>
        <article class="dash-kpi-card">
            <h3>Nizky sklad</h3>
            <p class="value">5</p>
            <p class="meta">Potrebne doplnenie tovaru</p>
        </article>
    </section>

    <section class="dash-panels">
        <article class="dash-panel">
            <div class="dash-panel-head">
                <h2>Posledne objednavky</h2>
                <a href="#" class="panel-link">Zobrazit vsetko</a>
            </div>
            <ul class="dash-list">
                <li><span>#1052</span><strong>Chilli omacka</strong><em>18 EUR</em></li>
                <li><span>#1051</span><strong>Chilli sol</strong><em>6 EUR</em></li>
                <li><span>#1050</span><strong>Chutney</strong><em>9 EUR</em></li>
                <li><span>#1049</span><strong>Klucenka</strong><em>3 EUR</em></li>
            </ul>
        </article>

        <article class="dash-panel">
            <div class="dash-panel-head">
                <h2>Rychle ulohy</h2>
                <a href="#" class="panel-link">Upravit</a>
            </div>
            <ul class="dash-checklist">
                <li><input type="checkbox"> Potvrdit nove objednavky</li>
                <li><input type="checkbox"> Doplnit sklad Carolina Reaper</li>
                <li><input type="checkbox"> Pridat vikendovu akciu</li>
                <li><input type="checkbox"> Odpovedat na spravy</li>
            </ul>
        </article>
    </section>

    <section class="dash-management">
        <div class="dash-management-head">
            <h2>Sprava produktov a zliav</h2>
            <p>Formulare su pripravene ako template. Napojenie na backend a DB doplnis podla vlastnej logiky.</p>
        </div>

        <div class="dash-form-grid">
            <article class="dash-form-card">
                <h3>Pridanie produktu</h3>
                <form action="#" method="POST" class="dash-form">
                    <label for="product-name">Nazov produktu</label>
                    <input id="product-name" name="product_name" type="text" placeholder="Napriklad Chilli omacka"
                        required>

                    <label for="product-price">Cena (EUR)</label>
                    <input id="product-price" name="price" type="number" step="0.01" min="0" placeholder="12.99"
                        required>

                    <label for="product-stock">Skladom (ks)</label>
                    <input id="product-stock" name="stock" type="number" min="0" placeholder="25" required>

                    <label for="product-category">Kategoria</label>
                    <input id="product-category" name="category" type="text" placeholder="Omacky" required>

                    <button type="submit" class="dash-submit">Pridat produkt</button>
                </form>
            </article>

            <article class="dash-form-card">
                <h3>Uprava ceny produktu</h3>
                <form action="#" method="POST" class="dash-form">
                    <label for="edit-product-id">ID produktu</label>
                    <input id="edit-product-id" name="product_id" type="text" placeholder="Napriklad 1052" required>

                    <label for="edit-old-price">Povodna cena (EUR)</label>
                    <input id="edit-old-price" name="old_price" type="number" step="0.01" min="0" placeholder="18.00">

                    <label for="edit-new-price">Nova cena (EUR)</label>
                    <input id="edit-new-price" name="new_price" type="number" step="0.01" min="0" placeholder="15.99"
                        required>

                    <button type="submit" class="dash-submit">Ulozit novu cenu</button>
                </form>
            </article>

            <article class="dash-form-card">
                <h3>Pridanie zlavy na produkt</h3>
                <form action="#" method="POST" class="dash-form">
                    <label for="discount-product-id">ID produktu</label>
                    <input id="discount-product-id" name="product_id_discount" type="text" placeholder="Napriklad 1052"
                        required>

                    <label for="discount-type">Typ zlavy</label>
                    <select id="discount-type" name="discount_type" required>
                        <option value="percent">Percentualna (%)</option>
                        <option value="fixed">Fixna (EUR)</option>
                    </select>

                    <label for="discount-value">Hodnota zlavy</label>
                    <input id="discount-value" name="discount_value" type="number" step="0.01" min="0" placeholder="10"
                        required>

                    <label for="discount-until">Platnost do</label>
                    <input id="discount-until" name="discount_until" type="date">

                    <button type="submit" class="dash-submit">Pridat zlavu</button>
                </form>
            </article>

            <article class="dash-form-card">
                <h3>Zlavovy kod</h3>
                <form action="#" method="POST" class="dash-form">
                    <label for="coupon-code">Kod</label>
                    <input id="coupon-code" name="coupon_code" type="text" placeholder="CHILLI10" required>

                    <label for="coupon-discount">Zlava (%)</label>
                    <input id="coupon-discount" name="coupon_discount" type="number" min="1" max="100" placeholder="10"
                        required>

                    <label for="coupon-min-order">Minimalna objednavka (EUR)</label>
                    <input id="coupon-min-order" name="coupon_min_order" type="number" step="0.01" min="0"
                        placeholder="20.00">

                    <label for="coupon-expire">Platnost do</label>
                    <input id="coupon-expire" name="coupon_expire" type="date">

                    <button type="submit" class="dash-submit">Vytvorit zlavovy kod</button>
                </form>
            </article>
        </div>
    </section>
</main>

<?php
include __DIR__ . '/footer.php';
?>