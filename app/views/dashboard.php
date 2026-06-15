<?php
declare(strict_types=1);

if (!isset($_SESSION['user_id'])) {
    set_flash('error', 'Najskôr sa musíš prihlásiť.');
    header('Location: /login.php');
    exit;
}

$userRole = $_SESSION['role'] ?? ($_SESSION['user']['role'] ?? 'user');

if ($userRole !== 'admin') {
    set_flash('error', 'Nemáš práva na túto stránku. Prístup majú len administrátori.');
    header('Location: /404.php'); 
    exit;
}

$adminMailerNotice = '';
$adminMailerError = '';

$flash = get_flash(); 

if ($flash !== null) {
    if ($flash['type'] === 'notice') {
        $adminMailerNotice = $flash['message'];
    } elseif ($flash['type'] === 'error') {
        $adminMailerError = $flash['message'];
    }
}

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
        <div class="sidebar-top-content">
            <details class="admin-profile-menu sidebar-profile-menu">
                <summary>
                    <?php if ($avatarUrl !== ''): ?>
                        <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Avatar" class="admin-avatar">
                    <?php else: ?>
                        <span class="admin-avatar">
                            <?php 
                                $initial = ($avatarInitial !== '') ? $avatarInitial : ($sessionUser['name'] ?? 'U');
                                echo DashboardHelper::h(strtoupper(substr((string)$initial, 0, 1))); 
                            ?>
                        </span>
                    <?php endif; ?>
                </summary>
                
                <div class="admin-profile-dropdown sidebar-profile-dropdown">
                    <div class="profile-meta">
                        <strong><?php echo DashboardHelper::h($sessionUser['name'] ?? 'Admin'); ?></strong>
                        <span>Administrator</span>
                    </div>
                    <a href="<?php echo route('/home'); ?>">Domov</a>
                    <a href="<?php echo route('/e_shop'); ?>">Obchod</a>
                    <a href="<?php echo route('/userprofile'); ?>">Uživateľsky profil</a>
                    <a href="<?php echo route('/logout'); ?>">Odhlásiť</a>
                </div>
            </details>
        </div>

        <nav class="sidebar-nav">
            <a href="#overview" class="sidebar-link active" title="Prehľad" aria-label="Prehľad"><i class="fa-solid fa-house-chimney"></i>Prehľad</a>
            <a href="#products" class="sidebar-link" title="Produkty" aria-label="Produkty"><i class="fa-solid fa-boxes-stacked"></i>Produkty</a>
            <a href="#coupons" class="sidebar-link" title="Kupóny" aria-label="Kupóny"><i class="fa-solid fa-ticket"></i>Kupóny</a>
            <a href="#users" class="sidebar-link" title="Používatelia" aria-label="Používatelia"><i class="fa-solid fa-users"></i>Používatelia</a>
            <a href="#reviews" class="sidebar-link" title="Recenzie" aria-label="Recenzie"><i class="fa-solid fa-star"></i>Recenzie</a>
            <a href="#mailer" class="sidebar-link" title="Pošta" aria-label="Pošta"><i class="fa-solid fa-envelope"></i>Pošta</a>
            <a href="#orders" class="sidebar-link" title="Objednávky" aria-label="Objednávky"><i class="fa-solid fa-chart-line"></i>Objednávky</a>
            <a href="#settings" class="sidebar-link" title="Nastavenia" aria-label="Nastavenia"><i class="fa-solid fa-gear"></i>Nastavenia</a>
        </nav>
    </aside>

    <section class="admin-main dashboard-tabs-ready">
        <section class="dashboard-panel is-active" id="overview" data-dashboard-panel="overview">
            <div class="overview-intro-layout">
                <section class="admin-metrics admin-metrics-overview">
                <article class="metric-card">
                    <p>Počet adminov</p>
                    <h2><?php echo DashboardHelper::h($totalAdmins); ?></h2>
                    <small>Zaregistrovaní administrátori</small>
                </article>

                <article class="metric-card">
                    <p>Dnešné objednávky</p>
                    <h2><?php echo DashboardHelper::h($todayOrderCount); ?></h2>
                    <small>Objednávky vytvorené dnes</small>
                </article>

                <article class="metric-card">
                    <p>Dnešné tržby</p>
                    <h2><?php echo DashboardHelper::h(number_format($todayRevenue, 2, '.', ',')); ?> €</h2>
                    <small>Súčet dnešných objednávok</small>
                </article>

                <article class="metric-card">
                    <p>Prijaté správy</p>
                    <h2><?php echo DashboardHelper::h($unreadQuestionsCount); ?></h2>
                    <small>Neprečítané správy z kontaktného formulára</small>
                </article>

                <article class="metric-card">
                    <p>Aktívne kupóny</p>
                    <h2><?php echo DashboardHelper::h(count($discountCodes)); ?></h2>
                    <small>Zľavové kódy pripravené na použitie</small>
                </article>

                <article class="metric-card">
                    <p>Aktívne relácie</p>
                    <h2><?php echo DashboardHelper::h($activeSessions); ?></h2>
                    <small>Aktívny monitoring prihlásených používateľov</small>
                </article>
                </section>

                <aside class="admin-card user-avatar-side-card" aria-label="Sekcia používateľa">
                    <form action="UploadAvatar.php" method="POST" enctype="multipart/form-data">
                        <?php echo SessionHelper::csrfField(); ?>
                        <div class="user-avatar-frame">
                            <?php if ($avatarUrl !== ''): ?>
                                <img src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar">
                            <?php else: ?>
                                <span aria-hidden="true"><?php echo DashboardHelper::h($avatarInitial !== '' ? $avatarInitial : 'A'); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($uploadError !== ''): ?>
                            <p class="user-avatar-note user-avatar-note--error"><?php echo DashboardHelper::h($uploadError); ?></p>
                        <?php endif; ?>
    
                        <label for="user-avatar-file" class="management-submit user-avatar-edit-btn">
                            Edit avatara
                        </label>
                        <input type="hidden" name="redirect_to" value="/dashboard">
                        <input type="file" name="avatar" id="user-avatar-file" class="user-avatar-input" accept="image/*" onchange="this.form.submit()">
                    </form>

                    <p class="user-avatar-name">Vitaj späť, <strong><?php echo DashboardHelper::h($sessionUser['name'] ?? 'Admin'); ?></strong></p>

                    <div class="user-avatar-meta">
                        <p>Informácie o administrátorovi</p>
                        <h3><?php echo DashboardHelper::h($adminDisplayName ?: 'Admin'); ?></h3>
                        <small><?php echo DashboardHelper::h($adminDisplayEmail !== '' ? $adminDisplayEmail : $adminDisplayRole); ?></small>
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
                        <span class="overview-pill">Produkty: <?php echo DashboardHelper::h(count($products)); ?></span>
                        <span class="overview-pill">Kupóny: <?php echo DashboardHelper::h(DashboardHelper::getCouponCount($discountCodes)); ?></span>
                        <span class="overview-pill">Správy: <?php echo DashboardHelper::h($unreadQuestionsCount); ?></span>
                        <span class="overview-pill">Admini: <?php echo DashboardHelper::h($totalAdmins); ?></span>
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
                                <time><?php echo DashboardHelper::h($item['time'] ?? ''); ?></time>
                                <p><?php echo DashboardHelper::h($item['text'] ?? ''); ?></p>
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

                <?php if ($adminMailerNotice !== ''): ?>
                    <p class="panel-success"><?php echo DashboardHelper::h($adminMailerNotice); ?></p>
                <?php endif; ?>

                <?php if ($adminMailerError !== ''): ?>
                    <p class="panel-error"><?php echo DashboardHelper::h($adminMailerError); ?></p>
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
                                <th>Zmena hesla</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($registeredUsers)): ?>
                                <tr>
                                    <td colspan="6" class="empty-cell">Žiadni používatelia nie sú k dispozícii.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($registeredUsers as $registeredUser): ?>
                                    <tr>
                                        <td><?php echo DashboardHelper::h($registeredUser['id'] ?? ''); ?></td>
                                        <td><?php echo DashboardHelper::h($registeredUser['name'] ?? ''); ?></td>
                                        <td><?php echo DashboardHelper::h($registeredUser['email'] ?? ''); ?></td>
                                        <td><?php echo DashboardHelper::h($registeredUser['loyalty_points'] ?? 0); ?></td>
                                        <td><?php echo DashboardHelper::h($registeredUser['role'] ?? 'user'); ?></td>
                                        <td>
                                            <form action="" method="POST" style="display: inline-flex; gap: 6px; align-items: center; margin: 0;">
                                                <input type="hidden" name="csrf_token" value="<?php echo SessionHelper::getCsrfToken(); ?>">
                                                <input type="hidden" name="form_type" value="change_user_password">
                                                <input type="hidden" name="user_id" value="<?php echo DashboardHelper::h($registeredUser['id'] ?? ''); ?>">
                                                
                                                <div class="admin-inline-pwd" style="position: relative;">
                                                    <input type="name" name="new_password" placeholder="Nové heslo" required 
                                                        style="padding: 6px 10px; border: 1px solid #555; background: rgba(0,0,0,0.2); color: #fff; border-radius: 4px; font-size: 13px; width: 130px; transition: all 0.3s ease; outline: none;">
                                                </div>
                                                
                                                <button type="submit" title="Uložiť nové heslo" 
                                                        style="background: #ff6600; color: #fff; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 13px; transition: background 0.2s ease;">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
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
                    <p class="panel-success"><?php echo DashboardHelper::h($adminMailerNotice); ?></p>
                <?php endif; ?>

                <?php if ($adminMailerError !== ''): ?>
                    <p class="panel-error"><?php echo DashboardHelper::h($adminMailerError); ?></p>
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
                                        <td><?php echo DashboardHelper::h($message['sender_name'] ?? ''); ?></td>
                                        <td><?php echo DashboardHelper::h($message['sender_email'] ?? ''); ?></td>
                                        <td><?php echo DashboardHelper::h($message['subject'] ?? ''); ?></td>
                                        <td><?php echo DashboardHelper::h($message['created_at'] ?? ''); ?></td>
                                        <td>
                                            <details class="message-detail">
                                                <summary>Reagovať</summary>
                                                <p><?php echo nl2br(DashboardHelper::h($message['message_text'] ?? '')); ?></p>

                                                <form method="POST" action="<?php echo route('/dashboard#mailer'); ?>" class="message-reply-form">
                                                    <?php echo SessionHelper::csrfField(); ?>
                                                    <input type="hidden" name="form_type" value="reply_message">
                                                    <input type="hidden" name="message_id" value="<?php echo DashboardHelper::h($message['id'] ?? ''); ?>">
                                                    <textarea name="reply_text" rows="4" placeholder="Napíš reakciu pre zákazníka" required></textarea>
                                                    <button type="submit" class="management-submit message-reply-btn">Uložiť reakciu</button>
                                                </form>

                                                <form method="POST" action="<?php echo route('/dashboard#mailer'); ?>" class="message-reply-form">
                                                    <?php echo SessionHelper::csrfField(); ?>
                                                    <input type="hidden" name="form_type" value="delete_message">
                                                    <input type="hidden" name="message_id" value="<?php echo DashboardHelper::h($message['id'] ?? ''); ?>">
                                                    <button type="submit" class="delete-btn">Vymazať</button>
                                                </form>

                                            </details>
                                        </td>
                                        <td>
                                            <div class="message-status-cell">
                                                <span class="message-status-badge message-status-<?php echo DashboardHelper::h((string) ($message['status'] ?? 'new')); ?>">
                                                    <?php echo DashboardHelper::h((string) ($message['status'] ?? 'new')); ?>
                                                </span>

                                                <?php if (($message['status'] ?? 'new') === 'new'): ?>
                                                    <form method="POST" action="<?php echo route('/dashboard#mailer'); ?>" class="message-status-form">
                                                        <?php echo SessionHelper::csrfField(); ?>
                                                        <input type="hidden" name="form_type" value="mark_message_read">
                                                        <input type="hidden" name="message_id" value="<?php echo DashboardHelper::h($message['id'] ?? ''); ?>">
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
                        <?php echo SessionHelper::csrfField(); ?>
                        <input type="hidden" name="form_type" value="delete_all_messages">
                        <button type="submit" class="delete-btn mailer-bulk-delete-btn" title="Vymazať všetky správy" aria-label="Vymazať všetky správy">
                            <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                            Vymazať všetky správy
                        </button>
                    </form>
                <?php endif; ?>
            </section>
        </section>

        <section class="dashboard-panel" id="reviews" data-dashboard-panel="reviews">
            <section class="admin-card contact-mailer-section">
                <div class="admin-card-head">
                    <div>
                        <h2>Recenzie na obchod</h2>
                        <span>Správa recenzií od zákazníkov</span>
                    </div>
                </div>

                <?php if ($reviewNotice !== ''): ?>
                    <p class="panel-success"><?php echo DashboardHelper::h($reviewNotice); ?></p>
                <?php endif; ?>

                <?php if ($reviewError !== ''): ?>
                    <p class="panel-error"><?php echo DashboardHelper::h($reviewError); ?></p>
                <?php endif; ?>

                <div class="table-wrap contact-mailer-wrap">
                    <table class="admin-table contact-mailer-table">
                        <thead>
                            <tr>
                                <th>Meno</th>
                                <th>Hodnotenie</th>
                                <th>Obsah recenzie</th>
                                <th>Stav</th>
                                <th>Akcia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($shopReviews)): ?>
                                <tr>
                                    <td colspan="5" class="empty-cell">Žiadne recenzie nie sú k dispozícii.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($shopReviews as $shopReview): ?>
                                    <tr>
                                        <td><?php echo DashboardHelper::h($shopReview['reviewer_name'] ?? ''); ?></td>
                                        <td><?php echo DashboardHelper::h($shopReview['rating'] ?? 0); ?>/5</td>
                                        <td><?php echo nl2br(DashboardHelper::h($shopReview['review_text'] ?? '')); ?></td>
                                        <td>
                                            <span class="message-status-badge message-status-<?php echo DashboardHelper::h((string) ($shopReview['status'] ?? 'pending')); ?>">
                                                <?php echo DashboardHelper::h((string) ($shopReview['status'] ?? 'pending')); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="product-col-actions">
                                                <?php if ((string) ($shopReview['status'] ?? 'pending') !== 'approved'): ?>
                                                    <form method="POST" action="<?php echo route('/dashboard#reviews'); ?>" class="message-status-form">
                                                        <?php echo SessionHelper::csrfField(); ?>
                                                        <input type="hidden" name="form_type" value="approve_shop_review">
                                                        <input type="hidden" name="review_id" value="<?php echo DashboardHelper::h($shopReview['id'] ?? ''); ?>">
                                                        <button type="submit" class="management-submit message-done-btn">Approve</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="overview-pill">Schválené</span>
                                                <?php endif; ?>

                                                <form method="POST" action="<?php echo route('/dashboard#reviews'); ?>" class="delete-form" onsubmit="return confirm('Naozaj chceš vymazať túto recenziu?');">
                                                    <?php echo SessionHelper::csrfField(); ?>
                                                    <input type="hidden" name="form_type" value="delete_shop_review">
                                                    <input type="hidden" name="review_id" value="<?php echo DashboardHelper::h($shopReview['id'] ?? ''); ?>">
                                                    <button type="submit" class="delete-btn" title="Vymazať recenziu" aria-label="Vymazať recenziu">
                                                        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                                    </button>
                                                </form>
                                            </div>
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
                    <p class="panel-success"><?php echo DashboardHelper::h($productNotice); ?></p>
                <?php endif; ?>

                <?php if ($productError !== ''): ?>
                    <p class="panel-error"><?php echo DashboardHelper::h($productError); ?></p>
                <?php endif; ?>

                <div class="management-grid">
                    <article class="management-card new-product-card">
                        <h3>Nový produkt</h3>
                        <p class="card-subtitle">Klikni na NOVÝ PRODUKT a pridaj nový produkt do databázy.</p>
                        <button type="button" class="management-submit" onclick="document.getElementById('create-product-form').style.display = document.getElementById('create-product-form').style.display === 'none' ? 'block' : 'none'; this.textContent = this.textContent === 'Nový produkt' ? 'Zrušiť' : 'Nový produkt';">Nový produkt</button>
                        
                        <form id="create-product-form" method="POST" action="#products" class="management-form create-product-form" style="display: none;">
                            <?php echo SessionHelper::csrfField(); ?>
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

                            <label class="checkbox-field"><input type="checkbox" name="featured"> Odporúčaný produkt</label>

                            <button type="submit" class="management-submit">Uložiť produkt</button>
                        </form>
                    </article>

                    <article class="management-card product-overview-card">
                        <h3>Prehľad produktov</h3>
                        <p class="card-subtitle">Klikni EDIT na zmenu údajov priamo v tabuľke. VYMAZAŤ zmaže produkt.</p>

                        <div class="table-wrap">
                            <div class="products-table">
                                
                                <div class="products-table-header products-grid-layout">
                                    <div>ID</div>
                                    <div>Názov</div>
                                    <div>Cena</div>
                                    <div>Sklad</div>
                                    <div style="text-align: right; padding-right: 12px;">Akcie</div>
                                </div>

                                <div class="products-table-body">
                                    <?php if (empty($products)): ?>
                                        <div class="empty-cell">Žiadne produkty nie sú k dispozícii.</div>
                                    <?php else: ?>
                                        <?php foreach ($products as $product): ?>
                                            <?php
                                            $productId = (int) ($product['id'] ?? 0);
                                            $basePrice = (float) ($product['price'] ?? 0);
                                            $discountPercent = (float) ($product['discount_percent'] ?? 0);
                                            $finalPrice = $discountPercent > 0 ? max(0, $basePrice * (1 - ($discountPercent / 100))) : $basePrice;
                                            ?>
                                            
                                            <div class="product-row" data-product-id="<?php echo DashboardHelper::h($productId); ?>">
                                                
                                                <div id="product-view-<?php echo $productId; ?>" class="products-grid-layout">
                                                    <div class="product-id"><strong><?php echo DashboardHelper::h($productId); ?></strong></div>
                                                    <div class="product-info">
                                                        <span class="product-name"><?php echo DashboardHelper::h($product['name'] ?? ''); ?></span><br>
                                                        <small class="product-category"><?php echo DashboardHelper::h($product['category'] ?? 'uncategorized'); ?></small>
                                                    </div>
                                                    <div class="product-price-box">
                                                        <?php if ($discountPercent > 0): ?>
                                                            <span class="price-old"><?php echo number_format($basePrice, 2, '.', ''); ?> €</span><br>
                                                            <span class="price-new"><?php echo number_format($finalPrice, 2, '.', ''); ?> €</span>
                                                        <?php else: ?>
                                                            <span class="price-regular"><?php echo number_format($basePrice, 2, '.', ''); ?> €</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="product-stock"><?php echo DashboardHelper::h($product['stock'] ?? 0); ?> ks</div>
                                                    <div class="product-actions">
                                                        <button type="button" class="management-submit btn-edit" onclick="toggleProductEditMode(<?php echo $productId; ?>)">EDIT</button>
                                                        
                                                        <form method="POST" action="#products" class="delete-form" onsubmit="return confirm('Naozaj chceš zmazať produkt &quot;<?php echo DashboardHelper::h($product['name'] ?? ''); ?>&quot;?');">
                                                            <?php echo SessionHelper::csrfField(); ?>
                                                            <input type="hidden" name="form_type" value="delete_product">
                                                            <input type="hidden" name="product_id" value="<?php echo DashboardHelper::h($productId); ?>">
                                                            <button type="submit" class="delete-btn"><i class="fa-solid fa-trash-can"></i></button>
                                                        </form>
                                                    </div>
                                                </div>

                                                <div id="product-edit-<?php echo $productId; ?>" class="product-edit-container">
                                                    <form method="POST" action="#products" class="management-form">
                                                        <?php echo SessionHelper::csrfField(); ?>
                                                        <input type="hidden" name="form_type" value="edit_product">
                                                        <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                                        
                                                        <div class="form-grid">
                                                            <div class="form-group">
                                                                <label>Názov</label>
                                                                <input type="text" name="name" value="<?php echo htmlspecialchars((string)($product['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Cena (€)</label>
                                                                <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars((string)($product['price'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Zľava (%)</label>
                                                                <input type="number" step="0.01" name="discount" value="<?php echo htmlspecialchars((string)($product['discount'] ?? '0'), ENT_QUOTES, 'UTF-8'); ?>">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Skladom</label>
                                                                <input type="number" name="stock" value="<?php echo htmlspecialchars((string)($product['stock'] ?? '0'), ENT_QUOTES, 'UTF-8'); ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Kategória</label>
                                                                <input type="text" name="category" value="<?php echo htmlspecialchars((string)($product['category'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                                            </div>
                                                        </div>
                                                        
                                                        <input type="hidden" name="description" value="<?php echo htmlspecialchars((string)($product['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                        <input type="hidden" name="image" value="<?php echo htmlspecialchars((string)($product['image'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                        <input type="hidden" name="rating" value="<?php echo htmlspecialchars((string)($product['rating'] ?? '4'), ENT_QUOTES, 'UTF-8'); ?>">
                                                        <input type="hidden" name="featured" value="<?php echo (int)($product['featured'] ?? 0); ?>">

                                                        <div class="form-actions">
                                                            <button type="button" class="delete-btn btn-cancel" onclick="toggleProductEditMode(<?php echo $productId; ?>)">Zrušiť</button>
                                                            <button type="submit" class="management-submit btn-save">Uložiť zmeny</button>
                                                        </div>
                                                    </form>
                                                </div>

                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                            </div>
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
                    <p class="panel-success"><?php echo DashboardHelper::h($discountCodeNotice); ?></p>
                <?php endif; ?>

                <?php if ($discountCodeError !== ''): ?>
                    <p class="panel-error"><?php echo DashboardHelper::h($discountCodeError); ?></p>
                <?php endif; ?>

                <div class="coupons-grid">
                    <article class="management-card coupon-form-card">
                        <h3>Nový zľavový kód</h3>
                        <form method="POST" action="#coupons" class="management-form">
                            <?php echo SessionHelper::csrfField(); ?>
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
                                                    <strong><?php echo DashboardHelper::h($discountCode['code'] ?? ''); ?></strong>
                                                    <div class="product-meta"><?php echo DashboardHelper::h($discountCode['description'] ?? ''); ?></div>
                                                </td>
                                                <td>
                                                    <?php if ((string) ($discountCode['discount_type'] ?? 'percent') === 'fixed'): ?>
                                                        <span class="product-price-chip"><?php echo DashboardHelper::h(number_format((float) ($discountCode['value'] ?? 0), 2, '.', '')); ?> €</span>
                                                    <?php else: ?>
                                                        <span class="product-price-chip"><?php echo DashboardHelper::h(number_format((float) ($discountCode['value'] ?? 0), 2, '.', '')); ?> %</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="product-stock-chip">
                                                        min. <?php echo DashboardHelper::h(number_format((float) ($discountCode['min_order_value'] ?? 0), 2, '.', '')); ?> €
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="coupon-status <?php echo ((int) ($discountCode['is_active'] ?? 0) === 1) ? 'is-active' : 'is-disabled'; ?>">
                                                        <?php echo ((int) ($discountCode['is_active'] ?? 0) === 1) ? 'Aktívny' : 'Vypnutý'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <form method="POST" action="#coupons" class="delete-form">
                                                        <?php echo SessionHelper::csrfField(); ?>
                                                        <input type="hidden" name="form_type" value="delete_discount_code">
                                                        <input type="hidden" name="discount_code_id" value="<?php echo DashboardHelper::h($discountCode['id'] ?? ''); ?>">
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
        
        <!-- SEKCIA ORDERS -->
        <section class="dashboard-panel" id="orders" data-dashboard-panel="orders">
            <div class="admin-content-grid">
                
                <article class="admin-card activity-panel">
                    <div class="admin-card-head">
                        <h2>Systémová aktivita</h2>
                        <span>Objednávky, tržby a posledná aktivita</span>
                    </div>

                    <?php if (!empty($adminMailerError)): ?>
                        <p class="panel-error"><?php echo DashboardHelper::h($adminMailerError); ?></p>
                    <?php endif; ?>

                    <div class="orders-summary-strip">
                        <div class="orders-summary-item">
                            <span>Dnešné objednávky</span>
                            <strong><?php echo DashboardHelper::h($todayOrderCount ?? 0); ?></strong>
                        </div>
                        <div class="orders-summary-item">
                            <span>Dnešné tržby</span>
                            <strong><?php echo DashboardHelper::h(number_format((float)($todayRevenue ?? 0), 2, '.', ',')); ?> €</strong>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="admin-table setting-table">
                            <thead>
                                <tr>
                                    <th>ID</th><th>Zákazník</th><th>Email</th><th>Suma</th>
                                    <th>Stav</th><th>Doprava</th><th>Platba</th><th>Dátum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentOrders)): ?>
                                    <tr><td colspan="8" class="empty-cell">Žiadne objednávky.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td><?php echo DashboardHelper::h($order['id_order'] ?? ''); ?></td>
                                            <td><?php echo DashboardHelper::h($order['customer_name'] ?? ''); ?></td>
                                            <td><?php echo DashboardHelper::h($order['customer_email'] ?? ''); ?></td>
                                            <td><?php echo DashboardHelper::h(number_format((float)($order['total_price'] ?? 0), 2, '.', ',')); ?> €</td>
                                            <td><?php echo DashboardHelper::h($order['status'] ?? ''); ?></td>
                                            <td><span class="delivery-tag"><?php echo DashboardHelper::h($order['delivery_method'] ?? 'Nezadané'); ?></span></td>
                                            <td><?php echo DashboardHelper::h(($order['payment_method'] ?? '-') . ' / ' . ($order['payment_status'] ?? '-')); ?></td>
                                            <td><?php echo DashboardHelper::h($order['created_at'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="admin-card orders-management-section">
                    <div class="admin-card-head">
                        <h2>Správa objednávok</h2>
                        <span>Rýchla zmena stavu objednávky</span>
                    </div>

                    <?php if (!empty($adminMailerNotice)): ?>
                        <p class="panel-success"><?php echo DashboardHelper::h($adminMailerNotice); ?></p>
                    <?php endif; ?>

                    <div class="table-wrap recent-orders-wrap">
                        <table class="admin-table recent-orders-table">
                            <thead>
                                <tr>
                                    <th>ID</th><th>Zákazník</th><th>Email</th><th>Suma</th>
                                    <th>Stav</th><th>Platba</th><th>Zmena stavu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentOrders)): ?>
                                    <tr><td colspan="7" class="empty-cell">Žiadne objednávky.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td><?php echo DashboardHelper::h($order['id_order'] ?? ''); ?></td>
                                            <td><?php echo DashboardHelper::h($order['customer_name'] ?? ''); ?></td>
                                            <td><?php echo DashboardHelper::h($order['customer_email'] ?? ''); ?></td>
                                            <td><?php echo DashboardHelper::h(number_format((float)($order['total_price'] ?? 0), 2, '.', ',')); ?> €</td>
                                            <td>
                                                <span class="message-status-badge message-status-<?php echo DashboardHelper::h((string)($order['status'] ?? 'pending')); ?>">
                                                    <?php echo DashboardHelper::h((string)($order['status'] ?? 'pending')); ?>
                                                </span>
                                            </td>
                                            <td><?php echo DashboardHelper::h(($order['payment_method'] ?? '-') . ' / ' . ($order['payment_status'] ?? '-')); ?></td>
                                            <td>
                                                <form method="POST" action="#orders" class="order-status-form">
                                                    <input type="hidden" name="form_type" value="update_order_status">
                                                    <input type="hidden" name="order_id" value="<?php echo DashboardHelper::h($order['id_order'] ?? ''); ?>">
                                                    <select name="order_status" class="order-status-select">
                                                        <?php foreach (OrderModel::statusOptions() as $statusOption): ?>
                                                            <option value="<?php echo DashboardHelper::h($statusOption); ?>" <?php echo ((string)($order['status'] ?? 'pending') === $statusOption) ? 'selected' : ''; ?>>
                                                                <?php echo DashboardHelper::h($statusOption); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="management-submit order-status-btn">Uložiť</button>
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
   
        <!-- SEKCIA SETTINGS -->
        <section class="dashboard-panel" id="settings" data-dashboard-panel="settings">
             <article class="admin-card quick-tools-panel">
                    <div class="admin-card-head">
                        <h2>Objednávky a nástroje</h2>
                        <span>Denné tržby, objednávky a rýchle akcie</span>
                    </div>
                    <div class="quick-tools-buttons">
                        <a href="<?php echo route('/forgot-password'); ?>" class="tool-btn">Resetovať heslo</a>
                        <a href="<?php echo route('/e-shop'); ?>" class="tool-btn ghost-link">Otvoriť obchod</a>
                        <a href="<?php echo route('/shopcart'); ?>" class="tool-btn ghost-link">Zobraziť košík</a>
                    </div>
                </article>
        </section>

</main>

<script src="/assets/js/dashboard.js"></script>

<?php include __DIR__ . '/partials/footer.php'; ?>