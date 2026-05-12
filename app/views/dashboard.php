<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once dirname(__DIR__) . '/core/session_helper.php';
require_once dirname(__DIR__) . '/core/Redirect.php';
require_once dirname(__DIR__) . '/core/dashboard_helper.php';
require_once dirname(__DIR__, 2) . '/config/config.php';

$sessionUser = SessionHelper::user();
$pdo = $conn ?? ($GLOBALS['conn'] ?? null);

$dashboardState = DashboardHelper::buildDashboardState($pdo, $sessionUser, $_SERVER, $_POST);
if (($dashboardState['redirectTo'] ?? '') !== '') {
    (new Redirect((string) $dashboardState['redirectTo']))->redirect();
}

extract($dashboardState, EXTR_SKIP);

$avatarFile = (string) ($sessionUser['image'] ?? '');
$avatarFsPath = $avatarFile !== ''
    ? dirname(__DIR__, 2) . '/public/uploads/avatars/' . $avatarFile
    : '';
$avatarUrl = ($avatarFile !== '' && is_file($avatarFsPath))
    ? '/uploads/avatars/' . rawurlencode($avatarFile)
    : '';
$avatarInitial = strtoupper(substr(trim((string) ($sessionUser['name'] ?? 'A')), 0, 1));

$uploadError = (string) ($_SESSION['upload_error'] ?? '');
unset($_SESSION['upload_error']);

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
                    <a href="<?php echo route('/userprofile'); ?>">Uživateľsky profil</a>
                    <a href="<?php echo route('/logout'); ?>">Odhlásiť</a>
                    <a href="<?php echo route('/home'); ?>">Domov</a>
                </div>
            </details>
        </div>
    </aside>

    <section class="admin-main dashboard-tabs-ready">
        <section class="dashboard-panel is-active" id="overview" data-dashboard-panel="overview">
            <div class="overview-intro-layout">
                <section class="admin-metrics admin-metrics-overview">
                <article class="metric-card">
                    <p>Počet adminov</p>
                    <h2><?php echo dash_h($totalAdmins); ?></h2>
                    <small>Zaregistrovaní administrátori</small>
                </article>

                <article class="metric-card">
                    <p>Dnešné objednávky</p>
                    <h2><?php echo dash_h($todayOrderCount); ?></h2>
                    <small>Objednávky vytvorené dnes</small>
                </article>

                <article class="metric-card">
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

                <aside class="admin-card user-avatar-side-card" aria-label="Sekcia používateľa">
                    <form action="UploadAvatar.php" method="POST" enctype="multipart/form-data">
                        <div class="user-avatar-frame">
                            <?php if ($avatarUrl !== ''): ?>
                                <img src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar">
                            <?php else: ?>
                                <span aria-hidden="true"><?php echo dash_h($avatarInitial !== '' ? $avatarInitial : 'A'); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($uploadError !== ''): ?>
                            <p class="user-avatar-note user-avatar-note--error"><?php echo dash_h($uploadError); ?></p>
                        <?php endif; ?>
    
                        <label for="user-avatar-file" class="management-submit user-avatar-edit-btn">
                            Edit avatara
                        </label>
                        <input type="file" name="avatar" id="user-avatar-file" class="user-avatar-input" accept="image/*" onchange="this.form.submit()">
                    </form>

                    <p class="user-avatar-name">Vitaj späť, <strong><?php echo dash_h($sessionUser['name'] ?? 'Admin'); ?></strong></p>

                    <div class="user-avatar-meta">
                        <p>Informácie o administrátorovi</p>
                        <h3><?php echo dash_h($adminDisplayName ?: 'Admin'); ?></h3>
                        <small><?php echo dash_h($adminDisplayEmail !== '' ? $adminDisplayEmail : $adminDisplayRole); ?></small>
                    </div>
                </aside>
            </div>

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
                    <div>
                        <h2>Správy z kontaktného formulára</h2>
                        <span>Načítané priamo z tabuľky contact_messages</span>
                    </div>
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
                            <?php if (empty($contactMessages)): ?>
                                <tr>
                                    <td colspan="6" class="empty-cell">Žiadne správy nie sú k dispozícii.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($contactMessages as $message): ?>
                                    <tr>
                                        <td><?php echo dash_h($message['sender_name'] ?? ''); ?></td>
                                        <td><?php echo dash_h($message['sender_email'] ?? ''); ?></td>
                                        <td><?php echo dash_h($message['subject'] ?? ''); ?></td>
                                        <td><?php echo dash_h($message['created_at'] ?? ''); ?></td>
                                        <td>
                                            <details class="message-detail">
                                                <summary>Reagovať</summary>
                                                <p><?php echo nl2br(dash_h($message['message_text'] ?? '')); ?></p>

                                                <form method="POST" action="<?php echo route('/dashboard#mailer'); ?>" class="message-reply-form">
                                                    <input type="hidden" name="form_type" value="reply_message">
                                                    <input type="hidden" name="message_id" value="<?php echo dash_h($message['id'] ?? ''); ?>">
                                                    <textarea name="reply_text" rows="4" placeholder="Napíš reakciu pre zákazníka" required></textarea>
                                                    <button type="submit" class="management-submit message-reply-btn">Uložiť reakciu</button>
                                                </form>

                                                <form method="POST" action="<?php echo route('/dashboard#mailer'); ?>" class="message-reply-form">
                                                    <input type="hidden" name="form_type" value="delete_message">
                                                    <input type="hidden" name="message_id" value="<?php echo dash_h($message['id'] ?? ''); ?>">
                                                    <button type="submit" class="delete-btn">Vymazať</button>
                                                </form>

                                            </details>
                                        </td>
                                        <td>
                                            <div class="message-status-cell">
                                                
                                                <span class="message-status-badge message-status-<?php echo dash_h((string) ($message['status'] ?? 'new')); ?>">
                                                    <?php echo dash_h((string) ($message['status'] ?? 'new')); ?>
                                                </span>

                                                <?php if (($message['status'] ?? 'new') === 'new'): ?>
                                                    <form method="POST" action="<?php echo route('/dashboard#mailer'); ?>" class="message-status-form">
                                                        <input type="hidden" name="form_type" value="mark_message_read">
                                                        <input type="hidden" name="message_id" value="<?php echo dash_h($message['id'] ?? ''); ?>">
                                                        <button type="submit" class="management-submit message-done-btn">Označiť ako vybavené</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($contactMessages)): ?>
                    <form method="POST" action="<?php echo route('/dashboard#mailer'); ?>" class="mailer-bulk-delete-form mailer-bulk-delete-form-bottom" onsubmit="return confirm('Naozaj chceš vymazať všetky správy?');">
                        <input type="hidden" name="form_type" value="delete_all_messages">
                        <button type="submit" class="delete-btn mailer-bulk-delete-btn" title="Vymazať všetky správy" aria-label="Vymazať všetky správy">
                            <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                            Vymazať všetky správy
                        </button>
                    </form>
                <?php endif; ?>
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
                        <p class="card-subtitle">Klikni na NOVÝ PRODUKT a pridaj nový produkt do databázy.</p>
                        <button type="button" class="management-submit" onclick="document.getElementById('create-product-form').style.display = document.getElementById('create-product-form').style.display === 'none' ? 'block' : 'none'; this.textContent = this.textContent === 'Nový produkt' ? 'Zrušiť' : 'Nový produkt';">Nový produkt</button>
                        
                        <form id="create-product-form" method="POST" action="#products" class="management-form create-product-form">
                            <input type="hidden" name="form_type" value="create_product">
                            
                            <label for="quick-product-name">Názov produktu *</label>
                            <input id="quick-product-name" name="name" type="text" required>

                            <label for="quick-product-description">Popis produktu</label>
                            <textarea id="quick-product-description" name="description" rows="3" placeholder="Podrobný popis produktu"></textarea>

                            <label for="quick-product-price">Cena (EUR) *</label>
                            <input id="quick-product-price" name="price" type="number" step="0.01" min="0" required>

                            <label for="quick-product-discount">Zľava (%)</label>
                            <input id="quick-product-discount" name="discount_percent" type="number" min="0" max="100" step="0.01" value="0">

                            <label for="quick-product-stock">Skladom (ks) *</label>
                            <input id="quick-product-stock" name="stock" type="number" min="0" required>

                            <label for="quick-product-category">Kategória *</label>
                            <input id="quick-product-category" name="category" type="text" value="uncategorized" required>

                            <label for="quick-product-image">Obrázok (URL)</label>
                            <input id="quick-product-image" name="image" type="text" placeholder="https://example.com/image.jpg">

                            <label for="quick-product-rating">Hodnotenie (1-5)</label>
                            <input id="quick-product-rating" name="rating" type="number" min="1" max="5" value="4">

                            <label><input type="checkbox" name="featured"> Odporúčaný produkt</label>

                            <button type="submit" class="management-submit">Uložiť produkt</button>
                        </form>
                    </article>

                    <article class="management-card product-overview-card">
                        <h3>Prehľad produktov</h3>
                        <p class="card-subtitle">Klikni EDIT na zmenu údajov priamo v tabuľke. VYMAZAŤ zmaže produkt.</p>

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
                                            $productId = (int) ($product['id'] ?? 0);
                                            $basePrice = (float) ($product['price'] ?? 0);
                                            $discountPercent = (float) ($product['discount_percent'] ?? 0);
                                            $finalPrice = $discountPercent > 0 ? max(0, $basePrice * (1 - ($discountPercent / 100))) : $basePrice;
                                            ?>
                                            <tr class="product-row" data-product-id="<?php echo dash_h($productId); ?>">
                                                <td class="product-view-mode product-view-cell" colspan="5">
                                                    <div class="product-row-layout">
                                                        <div class="product-col-id">
                                                            <strong><?php echo dash_h($productId); ?></strong>
                                                        </div>
                                                        <div class="product-col-main">
                                                            <strong><?php echo dash_h($product['name'] ?? ''); ?></strong>
                                                            <div class="product-meta product-category-pill"><?php echo dash_h($product['category'] ?? 'uncategorized'); ?></div>
                                                        </div>
                                                        <div class="product-col-price">
                                                            <?php if ($discountPercent > 0): ?>
                                                                <span class="product-price-old"><?php echo dash_h(number_format($basePrice, 2, '.', '')); ?></span><br>
                                                                <span class="product-price-chip"><?php echo dash_h(number_format($finalPrice, 2, '.', '')); ?> €</span>
                                                            <?php else: ?>
                                                                <span class="product-price-chip"><?php echo dash_h(number_format($basePrice, 2, '.', '')); ?> €</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="product-col-stock">
                                                            <span class="product-stock-chip <?php echo ((int) ($product['stock'] ?? 0) > 0) ? 'in-stock' : 'out-of-stock'; ?>"><?php echo dash_h($product['stock'] ?? 0); ?> ks</span>
                                                        </div>
                                                        <div class="product-col-actions">
                                                            <button type="button" class="product-action-link edit-btn" onclick="toggleProductEditMode(<?php echo dash_h($productId); ?>)">EDIT</button>
                                                            <form method="POST" action="#products" class="product-delete-form">
                                                                <input type="hidden" name="form_type" value="delete_product">
                                                                <input type="hidden" name="product_id" value="<?php echo dash_h($productId); ?>">
                                                                <button type="submit" class="delete-btn" onclick="return confirm('Naozaj chceš zmazať produkt &quot;<?php echo dash_h($product['name'] ?? ''); ?>&quot;?');">VYMAZAŤ</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="product-edit-mode" id="edit-row-<?php echo dash_h($productId); ?>">
                                                <td colspan="5" class="product-edit-cell">
                                                    <form method="POST" action="#products" class="product-inline-edit-form">
                                                        <input type="hidden" name="form_type" value="update_product">
                                                        <input type="hidden" name="product_id" value="<?php echo dash_h($productId); ?>">

                                                        <div class="product-edit-grid product-edit-grid--four">
                                                            <div>
                                                                <label class="product-edit-label">Názov</label>
                                                                <input type="text" name="name" value="<?php echo dash_h($product['name'] ?? ''); ?>" required class="product-edit-input">
                                                            </div>
                                                            <div>
                                                                <label class="product-edit-label">Cena (EUR)</label>
                                                                <input type="number" name="price" step="0.01" min="0" value="<?php echo dash_h(number_format($basePrice, 2, '.', '')); ?>" required class="product-edit-input">
                                                            </div>
                                                            <div>
                                                                <label class="product-edit-label">Zľava (%)</label>
                                                                <input type="number" name="discount_percent" min="0" max="100" step="0.01" value="<?php echo dash_h(number_format($discountPercent, 2, '.', '')); ?>" class="product-edit-input">
                                                            </div>
                                                            <div>
                                                                <label class="product-edit-label">Skladom (ks)</label>
                                                                <input type="number" name="stock" min="0" value="<?php echo dash_h($product['stock'] ?? 0); ?>" required class="product-edit-input">
                                                            </div>
                                                        </div>

                                                        <div class="product-edit-grid product-edit-grid--two">
                                                            <div>
                                                                <label class="product-edit-label">Kategória</label>
                                                                <input type="text" name="category" value="<?php echo dash_h($product['category'] ?? 'uncategorized'); ?>" class="product-edit-input">
                                                            </div>
                                                            <div>
                                                                <label class="product-edit-label">Popis</label>
                                                                <textarea name="description" rows="2" class="product-edit-textarea"><?php echo dash_h($product['description'] ?? ''); ?></textarea>
                                                            </div>
                                                        </div>

                                                        <div class="product-edit-grid product-edit-grid--three">
                                                            <div>
                                                                <label class="product-edit-label">Obrázok (URL)</label>
                                                                <input type="text" name="image" value="<?php echo dash_h($product['image'] ?? ''); ?>" class="product-edit-input">
                                                            </div>
                                                            <div>
                                                                <label class="product-edit-label">Hodnotenie (1-5)</label>
                                                                <input type="number" name="rating" min="1" max="5" value="<?php echo dash_h($product['rating'] ?? '4'); ?>" class="product-edit-input">
                                                            </div>
                                                            <div>
                                                                <label class="product-edit-label product-edit-label--checkbox"><input type="checkbox" name="featured" <?php echo ((int) ($product['featured'] ?? 0) === 1) ? 'checked' : ''; ?>> Odporúčaný</label>
                                                            </div>
                                                        </div>

                                                        <div class="product-edit-actions">
                                                            <button type="submit" class="management-submit product-edit-action">ULOŽIŤ ZMENY</button>
                                                            <button type="button" class="management-submit management-submit--muted product-edit-action" onclick="toggleProductEditMode(<?php echo dash_h($productId); ?>)">ZRUŠIŤ</button>
                                                        </div>
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

                            <label for="coupon-description">Popis</label>
                            <input id="coupon-description" name="description" type="text" placeholder="Leto 2026">

                            <label for="coupon-type">Typ zľavy</label>
                            <select id="coupon-type" name="discount_type">
                                <option value="percent">Percento</option>
                                <option value="fixed">Fixná suma</option>
                            </select>

                            <label for="coupon-value">Hodnota zľavy</label>
                            <input id="coupon-value" name="value" type="number" step="0.01" min="0" placeholder="20" required>

                            <label for="coupon-min-total">Minimálna hodnota objednávky</label>
                            <input id="coupon-min-total" name="min_order_value" type="number" step="0.01" min="0" value="0">

                            <label for="coupon-starts">Platí od</label>
                            <input id="coupon-starts" name="valid_from" type="datetime-local">

                            <label for="coupon-ends">Platí do</label>
                            <input id="coupon-ends" name="valid_to" type="datetime-local">

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
                                                    <div class="product-meta"><?php echo dash_h($discountCode['description'] ?? ''); ?></div>
                                                </td>
                                                <td>
                                                    <?php if ((string) ($discountCode['discount_type'] ?? 'percent') === 'fixed'): ?>
                                                        <span class="product-price-chip"><?php echo dash_h(number_format((float) ($discountCode['value'] ?? 0), 2, '.', '')); ?> €</span>
                                                    <?php else: ?>
                                                        <span class="product-price-chip"><?php echo dash_h(number_format((float) ($discountCode['value'] ?? 0), 2, '.', '')); ?> %</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="product-stock-chip">
                                                        min. <?php echo dash_h(number_format((float) ($discountCode['min_order_value'] ?? 0), 2, '.', '')); ?> €
                                                    </span>
                                                </td>
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

<script>
function toggleProductEditMode(productId) {
    const viewRow = document.querySelector(`tr[data-product-id="${productId}"] .product-view-mode`).closest('tr');
    const editRow = document.getElementById(`edit-row-${productId}`);
    
    if (editRow.style.display === 'none') {
        editRow.style.display = 'table-row';
        viewRow.style.display = 'none';
    } else {
        editRow.style.display = 'none';
        viewRow.style.display = 'table-row';
    }
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>