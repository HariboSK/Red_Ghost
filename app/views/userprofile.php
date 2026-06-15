<?php
declare(strict_types=1);

SessionHelper::bootstrap();

$sessionUser = SessionHelper::user();
if (!($sessionUser['is_logged_in'] ?? false)) {
    (new Redirect(route('/login')))->redirect();
}

$pdo = $GLOBALS['conn'] ?? null;
if (!$pdo instanceof PDO) {
    die("Chyba: Databázové pripojenie nie je k dispozícii.");
}

$profileModule = new UserProfileModule($pdo);
$userId = (int) ($sessionUser['id'] ?? 0);

$auth = new LoginRegister($pdo);
$uniqueResetCode = $auth->getOrGenerateResetCode($userId);

// Príprava cesty k avataru (rovnako ako v admine)
$avatarFile = (string) ($sessionUser['image'] ?? '');
$avatarFsPath = $avatarFile !== ''
    ? dirname(__DIR__, 1) . '/public/uploads/avatars/' . $avatarFile
    : '';
$avatarUrl = ($avatarFile !== '' && is_file($avatarFsPath))
    ? '/uploads/avatars/' . rawurlencode($avatarFile)
    : '';
$avatarInitial = strtoupper(substr(trim((string) ($sessionUser['name'] ?? 'A')), 0, 1));

// Spracovanie POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'reply_to_admin') {
    $replyText = trim((string) ($_POST['reply_text'] ?? ''));
    if ($replyText !== '' && $profileModule->sendReply($userId, $sessionUser['name'], (string)$sessionUser['email'], $replyText)) {
        $_SESSION['profileNotice'] = 'Správa odoslaná.';
    }
    (new Redirect(route('/userprofile')))->redirect();
}

$viewModel = $profileModule->getViewModel($userId, $sessionUser);
extract($viewModel); 

$uploadError = (string) ($_SESSION['upload_error'] ?? '');
unset($_SESSION['upload_error']);

unset($_SESSION['profileNotice']);

include __DIR__ . '/partials/userprofile-header.php';
?>

<main>
    </main>
<main>
    <section class="profile-page">
        <div class="profile-shell">
            <aside class="profile-card">
                <form action="UploadAvatar.php" method="POST" enctype="multipart/form-data" class="avatar-form">
                    <input type="hidden" name="redirect_to" value="/userprofile">
                    <?php echo SessionHelper::csrfField(); ?>
                    
                    <div class="avatar-wrap">
                        <?php if ($avatarUrl !== ''): ?>
                            <img src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar" class="profile-avatar">
                        <?php else: ?>
                            <span aria-hidden="true" class="profile-avatar-initial"><?php echo DashboardHelper::h($avatarInitial !== '' ? $avatarInitial : 'A'); ?></span>
                        <?php endif; ?>
                    </div>

                    <input type="file" name="avatar" id="user-avatar-file" class="user-avatar-input" accept="image/*" onchange="this.form.submit()" style="display: none;">
                    <label for="user-avatar-file" class="management-submit user-avatar-edit-btn">
                        Edit avatara
                    </label>

                    <?php if (!empty($uploadError)): ?>
                        <p class="user-avatar-note user-avatar-note--error"><?php echo DashboardHelper::h($uploadError); ?></p>
                    <?php endif; ?>
                </form>
                
                <h1>VITAJ, <span><?= htmlspecialchars($profileName !== '' ? $profileName : 'Zákazník', ENT_QUOTES, 'UTF-8'); ?></span></h1>
                <p class="profile-email"><?= htmlspecialchars($profileEmail !== '' ? $profileEmail : 'Neznámy e-mail', ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="profile-tag">Zákazník od <?= htmlspecialchars($customerSinceYear, ENT_QUOTES, 'UTF-8'); ?></p>

                <div class="profile-stats">
                    <div class="stat-item">
                        <span>VERNOSTNÉ BODY</span>
                        <strong><?= $loyaltyPoints; ?></strong>
                    </div>
                    <div class="stat-item">
                        <span>Objednávky</span>
                        <strong><?= (int) $totalOrdersCount; ?></strong>
                    </div>
                    <div class="stat-item">
                        <span>Ušetrené</span>
                        <strong><?= number_format($totalSavedMoney, 2, ',', ' '); ?> EUR</strong>
                    </div>
                    <div class="stat-item">
                        <span class="unique-code">Unikátny kód na reset hesla</span>
                        
                        <div class="input-with-icon password-icon">
                            <input type="password" id="reset-code-input" 
                                    value="<?php echo htmlspecialchars($uniqueResetCode, ENT_QUOTES, 'UTF-8'); ?>" 
                                    readonly>
                            
                            <button type="button" class="password-toggle" data-toggle-password="reset-code-input">
                                <i class="fa-solid fa-eye password-toggle-icon"></i>
                            </button>
                        </div>

                        <p class="unique-code-info">Pozor tento kód je dôležitý pre obnovu hesla. Uchovajte ho v bezpečí. A nikomu ho nedávajte.</p>
                    </div>
                </div>
            </aside>

            <div class="profile-main">
                <div class="profile-top-actions">
                    <a href="<?php echo route('/e-shop'); ?>" class="profile-logout-icon" title="Späť do e-shopu" aria-label="Späť do e-shopu">
                        <i class="fa-solid fa-store" aria-hidden="true"></i>
                        <span>Späť do e-shopu</span>
                    </a>
                    <a href="<?php echo route('/logout'); ?>" class="profile-logout-icon" title="Odhásiť sa" aria-label="Odhásiť sa">
                        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                        <span>Odhásiť sa</span>
                    </a>
                </div>

                <section class="account-card">
                    <div class="section-head">
                        <h2>Svoje údaje účtu</h2>
                        <a href="<?php echo route('/profile-edit'); ?>" class="edit-link">Upraviť profil</a>
                    </div>

                    <?php if ($profileNotice !== ''): ?>
                        <p class="panel-success"><?= htmlspecialchars($profileNotice, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <div class="account-grid">
                        <article>
                            <h3>Meno a priezvisko</h3>
                            <p><?= htmlspecialchars($profileName !== '' ? $profileName : 'Nepasované', ENT_QUOTES, 'UTF-8'); ?></p>
                        </article>
                        <article>
                            <h3>Email</h3>
                            <p><?= htmlspecialchars($profileEmail !== '' ? $profileEmail : 'Nepasované', ENT_QUOTES, 'UTF-8'); ?></p>
                        </article>
                        <article>
                            <h3>Telefón</h3>
                            <p>
                                <?php if ($profilePhone !== ''): ?>
                                    <?= htmlspecialchars($profilePhone, ENT_QUOTES, 'UTF-8'); ?>
                                <?php else: ?>
                                    <span style="color: rgba(255,255,255,0.3); font-weight: normal; font-style: italic;">
                                        Číslo nie je zadané
                                    </span>
                                <?php endif; ?>
                            </p>
                        </article>
                        <article>
                            <h3>Adresa</h3>
                                <p>
                                    <?php if ($profileAddress !== ''): ?>
                                        <?= htmlspecialchars($profileAddress, ENT_QUOTES, 'UTF-8'); ?>
                                    <?php else: ?>
                                        <span style="color: rgba(255,255,255,0.3); font-style: italic;">Adresa nie je zadaná</span>
                                    <?php endif; ?>
                                </p>
                        </article>
                    </div>
                </section>

                <section class="orders-card">
                    <div class="section-head">
                        <h2>História objednávok</h2>
                    </div>

                    <div class="orders-list">
                        <?php if ($profileOrdersError !== ''): ?>
                            <p class="message-empty"><?= htmlspecialchars($profileOrdersError, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php elseif (!empty($profileOrders)): ?>
                            <?php foreach ($profileOrders as $order): 
                                $statusRaw = strtolower((string)($order['status'] ?? 'pending'));
                                $statusClass = 'progress'; 
                                $statusLabel = 'Prijatá';

                                switch ($statusRaw) {
                                    case 'delivered':
                                        $statusClass = 'done';
                                        $statusLabel = 'Doručená';
                                        break;
                                    case 'shipped':
                                        $statusClass = 'progress';
                                        $statusLabel = 'Na ceste';
                                        break;
                                    case 'processing':
                                        $statusClass = 'progress';
                                        $statusLabel = 'Spracováva sa';
                                        break;
                                    case 'paid':
                                        $statusClass = 'done';
                                        $statusLabel = 'Zaplatená';
                                        break;
                                    case 'cancelled':
                                    case 'refunded':
                                        $statusClass = 'progress'; 
                                        $statusLabel = $statusRaw === 'cancelled' ? 'Zrušená' : 'Vrátená';
                                        break;
                                    default:
                                        $statusClass = 'new';
                                        $statusLabel = 'Čaká';
                                        break;
                                }
                            ?>
                                <article class="order-row">
                                    <div>
                                        <h3>#<?= htmlspecialchars((string)($order['id_order'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></h3>
                                        <p><?= htmlspecialchars(date('d.m.Y', strtotime($order['created_at'] ?? 'now')), ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                    <span class="order-state <?= $statusClass; ?>"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <strong><?= number_format((float)($order['total_price'] ?? 0.0), 2, ',', ' '); ?> EUR</strong>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="message-empty" style="opacity: 0.6; font-style: italic; padding: 10px 0;">Zatiaľ u nás nemáš žiadne objednávky.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="messages-card">
                    <div class="section-head">
                        <h2>Správy a reakcie</h2>
                        <span class="edit-link">Načítané podľa tvojho emailu</span>
                    </div>

                    <?php if ($profileNotice !== ''): ?>
                        <p class="panel-success"><?= htmlspecialchars($profileNotice, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>

                    <?php if ($profileError !== ''): ?>
                        <p class="panel-error"><?= htmlspecialchars($profileError, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>

                    <?php if ($profileMessagesError !== ''): ?>
                        <p class="message-empty"><?= htmlspecialchars($profileMessagesError, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php elseif (empty($profileMessages)): ?>
                        <p class="message-empty">Zatiaľ nemáš žiadnu správu z kontaktného formulára.</p>
                    <?php else: ?>
                        <div class="messages-list">
                            <?php foreach ($profileMessages as $profileMessage): ?>
                                <article class="message-entry" style="border: 1px solid rgba(255,255,255,0.06); padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                    <div class="message-entry-head" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <div>
                                            <h3 style="margin: 0; font-size: 1.1rem;"><?= htmlspecialchars((string) ($profileMessage['subject'] ?? 'Bez predmetu'), ENT_QUOTES, 'UTF-8'); ?></h3>
                                            <p style="margin: 0; font-size: 0.85rem; opacity: 0.6;"><?= htmlspecialchars((string) ($profileMessage['created_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                        <span class="message-badge <?= htmlspecialchars((string) ($profileMessage['status'] ?? 'new'), ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars((string) ($profileMessage['status'] ?? 'new'), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>

                                    <div class="message-body" style="margin-bottom: 15px;">
                                        <strong>Tvoja správa:</strong>
                                        <p style="margin-top: 5px;"><?= nl2br(htmlspecialchars((string) ($profileMessage['message_text'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></p>
                                    </div>

                                    <div class="message-reply" style="background: rgba(255,255,255,0.02); padding: 10px; border-radius: 6px; margin-bottom: 15px;">
                                        <strong>Reakcia od Red Ghost:</strong>
                                        <?php if (!empty($profileMessage['reply_text'])): ?>
                                            <p style="margin-top: 5px;"><?= nl2br(htmlspecialchars((string) $profileMessage['reply_text'], ENT_QUOTES, 'UTF-8')); ?></p>
                                            <?php if (!empty($profileMessage['reply_at'])): ?>
                                                <small style="opacity: 0.5;">Odpoveď: <?= htmlspecialchars((string) $profileMessage['reply_at'], ENT_QUOTES, 'UTF-8'); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p class="message-reply-empty" style="opacity: 0.5; font-style: italic; margin-top: 5px;">Na túto správu ešte neprišla reakcia.</p>
                                        <?php endif; ?>
                                    </div>

                                    <form method="POST" action="<?php echo route('/userprofile#contact'); ?>" class="message-reply-form">
                                        <?php echo SessionHelper::csrfField(); ?>
                                        <input type="hidden" name="form_type" value="reply_to_admin">
                                        <input type="hidden" name="message_id" value="<?= htmlspecialchars((string) ($profileMessage['id'] ?? $profileMessage['id_message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                        
                                        <label class="message-napis-znova" for="reply-<?php echo htmlspecialchars((string) ($profileMessage['id'] ?? $profileMessage['id_message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">Napíš odpoveď</label>
                                        <textarea class="message-napis-textarea" id="reply-<?php echo htmlspecialchars((string) ($profileMessage['id'] ?? $profileMessage['id_message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" name="reply_text" rows="3" placeholder="Doplň otázku alebo reakciu" required ></textarea>
                                        <button type="submit" class="management-submit">Odoslať odpoveď</button>
                                    </form>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </section>
</main>

<script src="<?php echo route('/assets/js/login.js'); ?>" defer></script>

<?php include __DIR__ . '/partials/footer.php'; ?>