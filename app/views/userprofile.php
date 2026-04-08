<?php
require_once dirname(__DIR__, 2) . '/app/core/session_helper.php';
require_once dirname(__DIR__, 2) . '/app/core/Redirect.php';

rg_session_bootstrap();

$sessionUser = rg_session_user();
$profileEmail = (string) ($sessionUser['email'] ?? '');
$profileName = (string) ($sessionUser['name'] ?? '');
$isLoggedIn = (bool) ($sessionUser['is_logged_in'] ?? false);


if (!$isLoggedIn) {
    (new Redirect('/login.php'))->redirect();
}
include __DIR__ . '/partials/userprofile-header.php';

?>

<main>
    <section class="profile-page">
        <div class="profile-shell">
            <aside class="profile-card">
                <div class="avatar-wrap">
                    <img src="/assets/icons/user-svgrepo-com.svg" alt="Avatar uzivatela" class="profile-avatar">
                </div>
                <h1>VITAJ, <span><?= htmlspecialchars($profileName !== '' ? $profileName : 'Zakaznik', ENT_QUOTES, 'UTF-8'); ?></span></h1>
                <p class="profile-email"><?= htmlspecialchars($profileEmail !== '' ? $profileEmail : 'Neznamy e-mail', ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="profile-tag">Zakaznik od 2025</p>

                <div class="profile-stats">
                    <div class="stat-item">
                        <span>Objednavky</span>
                        <strong>14</strong>
                    </div>
                    <div class="stat-item">
                        <span>Usetrene</span>
                        <strong>38 EUR</strong>
                    </div>
                </div>
            </aside>

            <div class="profile-main">
                <div class="profile-top-actions">
                    <a href="<?php echo route('/e-shop'); ?>" class="profile-logout-icon" title="Spat do e-shopu"
                        aria-label="Spat do e-shopu">
                        <i class="fa-solid fa-store" aria-hidden="true"></i>
                        <span>Spat do e-shopu</span>
                    </a>
                    <a href="<?php echo route('/logout'); ?>" class="profile-logout-icon" title="Odhlasit sa"
                        aria-label="Odhlasit sa">
                        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                        <span>Odhlasit sa</span>
                    </a>
                </div>

                <section class="account-card">
                    <div class="section-head">
                        <h2>Svoje udaje uctu</h2>
                        <a href="#" class="edit-link">Upravit profil</a>
                    </div>

                    <div class="account-grid">
                        <article>
                            <h3>Meno a priezvisko</h3>
                            <p>Jano Mrkvicka</p>
                        </article>
                        <article>
                            <h3>Email</h3>
                            <p>jano@mrkvicka.com</p>
                        </article>
                        <article>
                            <h3>Telefon</h3>
                            <p>+421 901 234 567</p>
                        </article>
                        <article>
                            <h3>Adresa</h3>
                            <p>Hlavna 24, Bratislava</p>
                        </article>
                    </div>
                </section>

                <section class="orders-card">
                    <div class="section-head">
                        <h2>Historia objednavok</h2>
                        <a href="#" class="edit-link">Zobrazit vsetky</a>
                    </div>

                    <div class="orders-list">
                        <article class="order-row">
                            <div>
                                <h3>#1052</h3>
                                <p>25.03.2026</p>
                            </div>
                            <span class="order-state done">Dorucena</span>
                            <strong>18.00 EUR</strong>
                        </article>

                        <article class="order-row">
                            <div>
                                <h3>#1047</h3>
                                <p>11.03.2026</p>
                            </div>
                            <span class="order-state progress">Na ceste</span>
                            <strong>26.90 EUR</strong>
                        </article>

                        <article class="order-row">
                            <div>
                                <h3>#1043</h3>
                                <p>02.03.2026</p>
                            </div>
                            <span class="order-state done">Dorucena</span>
                            <strong>9.00 EUR</strong>
                        </article>
                    </div>
                </section>
            </div>
        </div>
    </section>

    <?php
    include __DIR__ . '/partials/footer.php';
    ?>