<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once dirname(__DIR__) . '/core/session_helper.php';
require_once dirname(__DIR__) . '/core/Redirect.php';
require_once dirname(__DIR__) . '/core/dashboard_helper.php';
require_once dirname(__DIR__, 2) . '/config/config.php';
$sessionUser = SessionHelper::user();
$pdo = (isset($pdo) && $pdo instanceof PDO) ? $pdo : ((isset($conn) && $conn instanceof PDO) ? $conn : null);

$dashboardState = DashboardHelper::buildDashboardState($pdo, $sessionUser, $_SERVER, $_POST);
if (($dashboardState['redirectTo'] ?? '') !== '') {
    (new Redirect((string) $dashboardState['redirectTo']))->redirect();
}

extract($dashboardState, EXTR_SKIP);

include __DIR__ . '/partials/dashboard-header.php';

?>

<main class="admin-dashboard-ui">
    <aside class="admin-sidebar" aria-label="Primary navigation">
        <a class="sidebar-brand" href="<?php echo route('/dashboard'); ?>" title="Dashboard">
            <span><img src="/assets/images/logo-text.webp" alt="Red Ghost logo"></span>
        </a>
        <nav class="sidebar-nav">
            <a href="#overview" class="sidebar-link active" title="Prehľad" aria-label="Prehľad"><i class="fa-solid fa-house-chimney"></i></a>
            <a href="#products" class="sidebar-link" title="Produkty" aria-label="Produkty"><i class="fa-solid fa-boxes-stacked"></i></a>
            <a href="#coupons" class="sidebar-link" title="Kupóny" aria-label="Kupóny"><i class="fa-solid fa-ticket"></i></a>
            <a href="#users" class="sidebar-link" title="Používatelia" aria-label="Používatelia"><i class="fa-solid fa-users"></i></a>
            <a href="#mailer" class="sidebar-link" title="Pošta" aria-label="Pošta"><i class="fa-solid fa-envelope"></i></a>
            <a href="#orders" class="sidebar-link" title="Objednávky" aria-label="Objednávky"><i class="fa-solid fa-chart-line"></i></a>
        </nav>

        <div class="sidebar-bottom">
            <details class="admin-profile-menu sidebar-profile-menu">
                <summary>
                    <span class="admin-avatar"><?php echo strtoupper(substr((string) ($sessionUser['name'] ?? 'U'), 0, 1)); ?></span>
                </summary>
                <div class="admin-profile-dropdown sidebar-profile-dropdown">
                    <div class="profile-meta">
                        <strong><?php echo dash_h($sessionUser['name'] ?? 'Admin'); ?></strong>
                        <span>Administrator</span>
                    </div>
                    <a href="<?php echo route('/userprofile'); ?>">Profile</a>
                    <a href="<?php echo route('/logout'); ?>">Logout</a>
                </div>
            </details>
        </div>
    </aside>

    <section class="admin-main dashboard-tabs-ready">
        <section class="dashboard-panel is-active" id="overview" data-dashboard-panel="overview">
            <section class="admin-metrics">
            <article class="metric-card metric-card-wide">
                <p>Informácie o administrátorovi</p>
                <h2><?php echo dash_h($adminDisplayName ?: 'Admin'); ?></h2>
                <small><?php echo dash_h($adminDisplayEmail !== '' ? $adminDisplayEmail : $adminDisplayRole); ?></small>
            </article>

            <article class="metric-card">
                <p>Počet adminov</p>
                <h2><?php echo dash_h($totalAdmins); ?></h2>
                <small>Zaregistrovaní administrátori</small>
            </article>

            <article class="metric-card is-accent">
                <p>Dnešné objednávky</p>
                <h2><?php echo dash_h($todayOrderCount); ?></h2>
                <small>Objednávky vytvorené dnes</small>
            </article>

            <article class="metric-card metric-card-wide">
                <p>Dnešné tržby</p>
                <h2><?php echo dash_h(number_format($todayRevenue, 2, '.', ',')); ?> €</h2>
                <small>Súčet dnešných objednávok</small>
            </article>

            <article class="metric-card">
                <p>Prijaté správy</p>
                <h2><?php echo dash_h($unreadQuestionsCount); ?></h2>
                <small>Neprečítané správy z kontaktného formulára</small>
            </article>

            <article class="metric-card">
                <p>Aktívne kupóny</p>
                <h2><?php echo dash_h(count($discountCodes)); ?></h2>
                <small>Zľavové kódy pripravené na použitie</small>
            </article>

            <article class="metric-card">
                <p>Aktívne relácie</p>
                <h2><?php echo dash_h($activeSessions); ?></h2>
                <small>Aktívny monitoring prihlásených používateľov</small>
            </article>
            </section>

            <div class="overview-grid">
                <article class="admin-card overview-info-card">
                    <div class="admin-card-head">
                        <h2>Rýchly prehľad</h2>
                        <span>Najdôležitejšie veci na jednom mieste</span>
                    </div>
                    <div class="overview-pills">
                        <span class="overview-pill">Produkty: <?php echo dash_h(count($products)); ?></span>
                        <span class="overview-pill">Kupóny: <?php echo dash_h(dashboard_get_coupon_count($discountCodes)); ?></span>
                        <span class="overview-pill">Správy: <?php echo dash_h($unreadQuestionsCount); ?></span>
                        <span class="overview-pill">Admini: <?php echo dash_h($totalAdmins); ?></span>
                    </div>
                </article>

                <article class="admin-card overview-feed-card">
                    <div class="admin-card-head">
                        <h2>Posledná aktivita</h2>
                        <span>Posledné systémové udalosti</span>
                    </div>
                    <ul class="activity-feed compact-feed">
                        <?php foreach (array_slice($activityFeed, 0, 4) as $item): ?>
                            <li>
                                <time><?php echo dash_h($item['time'] ?? ''); ?></time>
                                <p><?php echo dash_h($item['text'] ?? ''); ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            </div>
        </section>

        <section class="dashboard-panel" id="users" data-dashboard-panel="users">
            <article class="admin-card user-overview-card">
                <div class="admin-card-head">
                    <h1>Používatelia</h1>
                    <span>Prihlásení a zaregistrovaní používatelia s emailom a bodmi vernosti</span>
                </div>

                <?php if ($registeredUsersError !== ''): ?>
                    <p class="panel-error"><?php echo dash_h($registeredUsersError); ?></p>
                <?php endif; ?>

                <div class="table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Meno</th>
                                <th>Email</th>
                                <th>Body vernosti</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($registeredUsers)): ?>
                                <tr>
                                    <td colspan="5" class="empty-cell">Žiadni používatelia nie sú k dispozícii.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($registeredUsers as $registeredUser): ?>
                                    <tr>
                                        <td><?php echo dash_h($registeredUser['id'] ?? ''); ?></td>
                                        <td><?php echo dash_h($registeredUser['name'] ?? ''); ?></td>
                                        <td><?php echo dash_h($registeredUser['email'] ?? ''); ?></td>
                                        <td><?php echo dash_h($registeredUser['loyalty_points'] ?? 0); ?></td>
                                        <td><?php echo dash_h($registeredUser['role'] ?? 'user'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        <section class="dashboard-panel" id="orders" data-dashboard-panel="orders">
            <div class="admin-content-grid">
                <article class="admin-card quick-tools-panel">
                    <div class="admin-card-head">
                        <h2>Objednávky a nástroje</h2>
                        <span>Denné tržby, objednávky a rýchle akcie</span>
                    </div>
                    <div class="quick-tools-buttons">
                        <button type="button" class="tool-btn">Pridať admina</button>
                        <button type="button" class="tool-btn">Resetovať heslo</button>
                        <a href="<?php echo route('/e-shop'); ?>" class="tool-btn ghost-link">Otvoriť obchod</a>
                        <a href="<?php echo route('/shopcart'); ?>" class="tool-btn ghost-link">Zobraziť košík</a>
                    </div>
                </article>

                <article class="admin-card activity-panel">
                    <div class="admin-card-head">
                        <h2>Systémová aktivita</h2>
                        <span>Objednávky, tržby a posledná aktivita administrátora</span>
                    </div>

                    <?php if ($adminMailerError !== ''): ?>
                        <p class="panel-error"><?php echo dash_h($adminMailerError); ?></p>
                    <?php endif; ?>

                    <div class="orders-summary-strip">
                        <div class="orders-summary-item">
                            <span>Dnešné objednávky</span>
                            <strong><?php echo dash_h($todayOrderCount); ?></strong>
                        </div>
                        <div class="orders-summary-item">
                            <span>Dnešné tržby</span>
                            <strong><?php echo dash_h(number_format($todayRevenue, 2, '.', ',')); ?> €</strong>
                        </div>
                    </div>

                    <ul class="activity-feed">
                        <?php foreach ($activityFeed as $item): ?>
                            <li>
                                <time><?php echo dash_h($item['time'] ?? ''); ?></time>
                                <p><?php echo dash_h($item['text'] ?? ''); ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            </div>
        </section>

        <section class="dashboard-panel" id="mailer" data-dashboard-panel="mailer">
            <section class="admin-card contact-mailer-section">
                <div class="admin-card-head">
                    <h2>Správy z kontaktného formulára</h2>
                    <span>Načítané priamo z tabuľky contact_messages</span>
                </div>

                <?php if ($adminMailerNotice !== ''): ?>
                    <p class="panel-success"><?php echo dash_h($adminMailerNotice); ?></p>
                <?php endif; ?>

                <?php if ($adminMailerError !== ''): ?>
                    <p class="panel-error"><?php echo dash_h($adminMailerError); ?></p>
                <?php endif; ?>

                <div class="table-wrap contact-mailer-wrap">
                    <table class="admin-table contact-mailer-table">
                        <thead>
                            <tr>
                                <th>Meno</th>
                                <th>Email</th>
                                <th>Predmet</th>
                                <th>Dátum</th>
                                <th>Detail</th>
                                <th>Stav</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($unreadMessages)): ?>
                                <tr>
                                    <td colspan="6" class="empty-cell">Žiadne neprečítané správy nie sú k dispozícii.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($unreadMessages as $message): ?>
                                    <tr>
                                        <td><?php echo dash_h($message['sender_name'] ?? ''); ?></td>
                                        <td><?php echo dash_h($message['sender_email'] ?? ''); ?></td>
                                        <td><?php echo dash_h($message['subject'] ?? ''); ?></td>
                                        <td><?php echo dash_h($message['created_at'] ?? ''); ?></td>
                                        <td>
                                            <details class="message-detail">
                                                <summary>Zobraziť správu</summary>
                                                <p><?php echo nl2br(dash_h($message['message_text'] ?? '')); ?></p>
                                            </details>
                                        </td>
                                        <td>
                                            <form method="POST" action="<?php echo route('/dashboard'); ?>" class="message-status-form">
                                                <input type="hidden" name="form_type" value="mark_message_read">
                                                <input type="hidden" name="message_id" value="<?php echo dash_h($message['id'] ?? ''); ?>">
                                                <button type="submit" class="management-submit message-done-btn">Označiť ako vybavené</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>

        <section class="dashboard-panel" id="products" data-dashboard-panel="products">
            <section class="admin-card management-section">
                <div class="admin-card-head">
                    <h2>Správa produktov</h2>
                    <span>Prehľad produktov, zliav, cien, skladov a akcií</span>
                </div>

                <?php if ($productNotice !== ''): ?>
                    <p class="panel-success"><?php echo dash_h($productNotice); ?></p>
                <?php endif; ?>

                <?php if ($productError !== ''): ?>
                    <p class="panel-error"><?php echo dash_h($productError); ?></p>
                <?php endif; ?>

                <div class="management-grid">
                    <article class="management-card new-product-card">
                        <h3>Nový produkt</h3>
                        <form action="#products" method="POST" class="management-form">
                            <input type="hidden" name="form_type" value="create_product">

                            <label for="new-product-name">Názov produktu</label>
                            <input id="new-product-name" name="name" type="text" placeholder="Napríklad chilli omáčka" required>

                            <label for="new-product-description">Popis</label>
                            <textarea id="new-product-description" name="description" rows="3" placeholder="Krátky popis produktu"></textarea>

                            <label for="new-product-price">Cena (EUR)</label>
                            <input id="new-product-price" name="price" type="number" step="0.01" min="0" placeholder="12.99" required>

                            <label for="new-product-discount">Zľava produktu (%)</label>
                            <input id="new-product-discount" name="discount_percent" type="number" min="0" max="100" step="0.01" value="0">

                            <label for="new-product-stock">Skladom (ks)</label>
                            <input id="new-product-stock" name="stock" type="number" min="0" placeholder="25" required>

                            <label for="new-product-category">Kategória</label>
                            <input id="new-product-category" name="category" type="text" placeholder="Omáčky" required>

                            <label for="new-product-image">Obrázok</label>
                            <input id="new-product-image" name="image" type="text" placeholder="/assets/images/omacka3.webp">

                            <label for="new-product-rating">Hodnotenie</label>
                            <input id="new-product-rating" name="rating" type="number" min="1" max="5" value="4">

                            <label class="checkbox-field"><input type="checkbox" name="featured" value="1"> Odporúčaný produkt</label>

                            <button type="submit" class="management-submit">Pridať produkt</button>
                        </form>
                    </article>

                    <article class="management-card product-overview-card">
                        <h3>Prehľad produktov</h3>
                        <p class="card-subtitle">Prehľad produktov v shope. Úprava cien, akcií a skladu priamo tu.</p>

                        <div class="table-wrap product-table-wrap">
                            <table class="admin-table product-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Názov</th>
                                        <th>Cena</th>
                                        <th>Sklad</th>
                                        <th>Akcie</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($products)): ?>
                                        <tr>
                                            <td colspan="5" class="empty-cell">Žiadne produkty nie sú k dispozícii.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($products as $product): ?>
                                            <?php
                                            $basePrice = (float) ($product['price'] ?? 0);
                                            $discountPercent = (float) ($product['discount_percent'] ?? 0);
                                            $finalPrice = $discountPercent > 0 ? max(0, $basePrice * (1 - ($discountPercent / 100))) : $basePrice;
                                            ?>
                                            <tr>
                                                <td><?php echo dash_h($product['id'] ?? ''); ?></td>
                                                <td>
                                                    <strong><?php echo dash_h($product['name'] ?? ''); ?></strong>
                                                    <div class="product-meta product-category-pill"><?php echo dash_h($product['category'] ?? ''); ?></div>
                                                </td>
                                                <td>
                                                    <?php if ($discountPercent > 0): ?>
                                                        <span class="product-price-old"><?php echo dash_h(number_format($basePrice, 2, '.', '')); ?> EUR</span>
                                                        <span class="product-price-chip"><?php echo dash_h(number_format($finalPrice, 2, '.', '')); ?> EUR</span>
                                                    <?php else: ?>
                                                        <span class="product-price-chip"><?php echo dash_h(number_format($basePrice, 2, '.', '')); ?> EUR</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="product-stock-chip <?php echo ((int) ($product['stock'] ?? 0) > 0) ? 'in-stock' : 'out-of-stock'; ?>"><?php echo dash_h($product['stock'] ?? 0); ?> ks</span></td>
                                                <td class="product-actions-cell">
                                                    <span class="product-action-link">Podrobnosti</span>
                                                    <details class="product-actions-detail">
                                                        <summary>Upraviť</summary>
                                                        <form action="#products" method="POST" class="management-form product-inline-form">
                                                            <input type="hidden" name="form_type" value="update_product">
                                                            <input type="hidden" name="product_id" value="<?php echo dash_h($product['id'] ?? ''); ?>">

                                                            <label>Názov</label>
                                                            <input name="name" type="text" value="<?php echo dash_h($product['name'] ?? ''); ?>" required>

                                                            <label>Popis</label>
                                                            <textarea name="description" rows="2"><?php echo dash_h($product['description'] ?? ''); ?></textarea>

                                                            <label>Cena</label>
                                                            <input name="price" type="number" step="0.01" min="0" value="<?php echo dash_h($product['price'] ?? 0); ?>" required>

                                                            <label>Zľava (%)</label>
                                                            <input name="discount_percent" type="number" min="0" max="100" step="0.01" value="<?php echo dash_h($product['discount_percent'] ?? 0); ?>">

                                                            <label>Sklad</label>
                                                            <input name="stock" type="number" min="0" value="<?php echo dash_h($product['stock'] ?? 0); ?>" required>

                                                            <label>Kategória</label>
                                                            <input name="category" type="text" value="<?php echo dash_h($product['category'] ?? ''); ?>" required>

                                                            <label>Obrázok</label>
                                                            <input name="image" type="text" value="<?php echo dash_h($product['image'] ?? ''); ?>">

                                                            <label>Hodnotenie</label>
                                                            <input name="rating" type="number" min="1" max="5" value="<?php echo dash_h($product['rating'] ?? 4); ?>">

                                                            <label class="checkbox-field"><input type="checkbox" name="featured" value="1" <?php echo ((int) ($product['featured'] ?? 0) === 1) ? 'checked' : ''; ?>> Odporúčaný produkt</label>

                                                            <button type="submit" class="management-submit">Uložiť</button>
                                                        </form>
                                                    </details>
                                                    <form action="#products" method="POST" class="delete-form">
                                                        <input type="hidden" name="form_type" value="delete_product">
                                                        <input type="hidden" name="product_id" value="<?php echo dash_h($product['id'] ?? ''); ?>">
                                                        <button type="submit" class="delete-btn" onclick="return confirm('Naozaj chceš zmazať tento produkt?');">Odstrániť</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </article>
                </div>
            </section>
        </section>

        <section class="dashboard-panel" id="coupons" data-dashboard-panel="coupons">
            <section class="admin-card coupons-section">
                <div class="admin-card-head">
                    <h2>Zľavové kódy</h2>
                    <span>Tvorba zľavových kódov a správa aktivácie</span>
                </div>

                <?php if ($discountCodeNotice !== ''): ?>
                    <p class="panel-success"><?php echo dash_h($discountCodeNotice); ?></p>
                <?php endif; ?>

                <?php if ($discountCodeError !== ''): ?>
                    <p class="panel-error"><?php echo dash_h($discountCodeError); ?></p>
                <?php endif; ?>

                <div class="coupons-grid">
                    <article class="management-card coupon-form-card">
                        <h3>Nový zľavový kód</h3>
                        <form method="POST" action="#coupons" class="management-form">
                            <input type="hidden" name="form_type" value="create_discount_code">

                            <label for="coupon-code">Kód</label>
                            <input id="coupon-code" name="code" type="text" placeholder="SUMMER20" required>

                            <label for="coupon-title">Názov</label>
                            <input id="coupon-title" name="title" type="text" placeholder="Leto 2026">

                            <label for="coupon-type">Typ zľavy</label>
                            <select id="coupon-type" name="discount_type">
                                <option value="percent">Percento</option>
                                <option value="fixed">Fixná suma</option>
                            </select>

                            <label for="coupon-value">Hodnota zľavy</label>
                            <input id="coupon-value" name="discount_value" type="number" step="0.01" min="0" placeholder="20" required>

                            <label for="coupon-min-total">Minimálna hodnota objednávky</label>
                            <input id="coupon-min-total" name="min_order_total" type="number" step="0.01" min="0" value="0">

                            <label for="coupon-limit">Limit použití</label>
                            <input id="coupon-limit" name="usage_limit" type="number" min="1" placeholder="100">

                            <label for="coupon-starts">Platí od</label>
                            <input id="coupon-starts" name="starts_at" type="datetime-local">

                            <label for="coupon-ends">Platí do</label>
                            <input id="coupon-ends" name="ends_at" type="datetime-local">

                            <label class="checkbox-field"><input type="checkbox" name="is_active" checked> Aktívny</label>

                            <button type="submit" class="management-submit">Vytvoriť kód</button>
                        </form>
                    </article>

                    <article class="management-card coupon-list-card">
                        <h3>Dostupné kódy</h3>
                        <div class="table-wrap coupon-table-wrap">
                            <table class="admin-table coupon-table">
                                <thead>
                                    <tr>
                                        <th>Kód</th>
                                        <th>Zľava</th>
                                        <th>Obmedzenie</th>
                                        <th>Použité</th>
                                        <th>Stav</th>
                                        <th>Akcia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($discountCodes)): ?>
                                        <tr>
                                            <td colspan="6" class="empty-cell">Žiadne zľavové kódy nie sú k dispozícii.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($discountCodes as $discountCode): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo dash_h($discountCode['code'] ?? ''); ?></strong>
                                                    <div class="product-meta"><?php echo dash_h($discountCode['title'] ?? ''); ?></div>
                                                </td>
                                                <td>
                                                    <?php if ((string) ($discountCode['discount_type'] ?? 'percent') === 'fixed'): ?>
                                                        <span class="product-price-chip"><?php echo dash_h(number_format((float) ($discountCode['discount_value'] ?? 0), 2, '.', '')); ?> €</span>
                                                    <?php else: ?>
                                                        <span class="product-price-chip"><?php echo dash_h(number_format((float) ($discountCode['discount_value'] ?? 0), 2, '.', '')); ?> %</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="product-stock-chip">
                                                        min. <?php echo dash_h(number_format((float) ($discountCode['min_order_total'] ?? 0), 2, '.', '')); ?> €
                                                    </span>
                                                </td>
                                                <td><?php echo dash_h($discountCode['used_count'] ?? 0); ?> / <?php echo dash_h($discountCode['usage_limit'] ?? '∞'); ?></td>
                                                <td>
                                                    <span class="coupon-status <?php echo ((int) ($discountCode['is_active'] ?? 0) === 1) ? 'is-active' : 'is-disabled'; ?>">
                                                        <?php echo ((int) ($discountCode['is_active'] ?? 0) === 1) ? 'Aktívny' : 'Vypnutý'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <form method="POST" action="#coupons" class="delete-form">
                                                        <input type="hidden" name="form_type" value="delete_discount_code">
                                                        <input type="hidden" name="discount_code_id" value="<?php echo dash_h($discountCode['id'] ?? ''); ?>">
                                                        <button type="submit" class="delete-btn" onclick="return confirm('Naozaj chceš zmazať tento zľavový kód?');">Odstrániť</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </article>
                </div>
            </section>
        </section>
    </section>
</main>

<?php
include __DIR__ . '/partials/footer.php';
?>