<?php
SessionHelper::bootstrap();

$sessionUser = SessionHelper::user();
$avatarFile = (string) ($sessionUser['image'] ?? '');
$avatarFsPath = $avatarFile !== ''
    ? dirname(__DIR__, 2) . '/public/uploads/avatars/' . $avatarFile
    : '';
$avatarUrl = ($avatarFile !== '' && is_file($avatarFsPath))
    ? '/uploads/avatars/' . rawurlencode($avatarFile)
    : '';
$avatarInitial = strtoupper(substr(trim((string) ($sessionUser['name'] ?? 'A')), 0, 1));
$profileEmail = (string) ($sessionUser['email'] ?? '');
$profilePhone = (string) ($sessionUser['phone'] ?? '');
$profileName = (string) ($sessionUser['name'] ?? '');
$isLoggedIn = (bool) ($sessionUser['is_logged_in'] ?? false);
$pdo = $conn ?? ($GLOBALS['conn'] ?? null);
$profileMessages = [];
$profileMessagesError = '';
$profileNotice = (string) ($_SESSION['profileNotice'] ?? '');
$profileError = (string) ($_SESSION['profileError'] ?? '');

unset($_SESSION['profileNotice'], $_SESSION['profileError']);

// Ak používateľ nie je prihlásený, presmerujeme ho
if (!$isLoggedIn) {
    (new Redirect(route('/login')))->redirect();
}

// ==============================
// SPRAVA OD POUŽÍVATEĽA -> ADMIN
// ==============================
if ((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && (string) ($_POST['form_type'] ?? '') === 'reply_to_admin') {
    $messageId = filter_var($_POST['message_id'] ?? null, FILTER_VALIDATE_INT);
    $replyText = trim((string) ($_POST['reply_text'] ?? ''));    
    $userId = filter_var($sessionUser['id'] ?? null, FILTER_VALIDATE_INT);

    if (!($pdo instanceof PDO)) {
        $_SESSION['profileError'] = 'Databázové pripojenie nie je dostupné.';
    } elseif ($replyText === '') {
        $_SESSION['profileError'] = 'Správa nemôže byť prázdna.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO `contact_messages` 
                                (sender_name, 
                                 sender_email, 
                                 subject, 
                                 id_user) 
                                VALUES 
                                (:sender_name, 
                                 :sender_email, 
                                 :subject, 
                                 :id_user)");
            
            $execution = $stmt->execute([
                ':sender_name'  => $profileName !== '' ? $profileName : 'Zákazník',
                ':sender_email' => $profileEmail,
                ':subject'      => $replyText,
                ':id_user'      => $userId > 0 ? $userId : null
            ]);

            if ($execution) {
                $_SESSION['profileNotice'] = 'Správa bola úspešne odoslaná administrátorovi.';
            } else {
                $_SESSION['profileError'] = 'Správu sa nepodarilo odoslať.';
            }
        } catch (Exception $e) {
            $_SESSION['profileError'] = 'Chyba databázy: ' . $e->getMessage();
        }
    }

    (new Redirect(route('/userprofile')))->redirect();
}


// Načítanie správ z databázy podľa emailu prihláseného používateľa
if ($pdo instanceof PDO && $profileEmail !== '') {
    try {
        $contactMessageModel = new ContactMessageModel($pdo);
        $profileMessages = $contactMessageModel->getByEmail($profileEmail);
    } catch (PDOException $exception) {
        $profileMessagesError = 'Správy sa nepodarilo načítať.';
    }
} elseif ($profileEmail !== '') {
    $profileMessagesError = 'Databázové pripojenie nie je dostupné.';
}

// --- DYNAMICKÉ NAČÍTANIE ---
$profileOrders = [];
$totalOrdersCount = 0;
$totalSavedMoney = 0.0;
$loyaltyPoints = 0;
$profileOrdersError = '';
$customerSinceYear = '—';

if ($pdo instanceof PDO) {
    $userId = filter_var($sessionUser['id'] ?? null, FILTER_VALIDATE_INT);

    if ($userId && $userId > 0) {
        try {
            // SPOLOČNÝ FIX: Vytiahneme najaktuálnejšie dáta priamo z DB (vyrieši tel. číslo, vernostné body aj správny rok)
            $userStmt = $pdo->prepare("SELECT telephone, loyalty_points, created_at FROM `user` WHERE id = :id");
            $userStmt->execute([':id' => $userId]);
            $dbUser = $userStmt->fetch(PDO::FETCH_ASSOC);

            if ($dbUser) {
                // Fix pre telefónne číslo z DB stĺpca telephone
                if (isset($dbUser['telephone']) && trim((string)$dbUser['telephone']) !== '') {
                    $profilePhone = trim((string)$dbUser['telephone']);
                }
                
                // Fix pre presné body priamo z databázy
                $loyaltyPoints = (int) ($dbUser['loyalty_points'] ?? 0);
                
                // Fix pre dynamický rok registrácie
                if (!empty($dbUser['created_at'])) {
                    $customerSinceYear = date('Y', strtotime($dbUser['created_at']));
                }
            }

            // 1. Načítanie objednávok z OrderModelu pomocou ID používateľa
            $orderModel = new OrderModel($pdo);
            $profileOrders = $orderModel->getOrdersByUserId($userId, 12);
            $totalOrdersCount = count($profileOrders);

            // Výpočet "Ušetrené"
            $totalSavedMoney = $totalOrdersCount * 2.50;

        } catch (Exception $e) {
            $profileOrdersError = 'Nepodarilo sa načítať históriu objednávok a profilové údaje.';
        }
    }
}

include __DIR__ . '/partials/userprofile-header.php';
?>

<main>
    <section class="profile-page">
        <div class="profile-shell">
            <aside class="profile-card">
                <div class="avatar-wrap">
                    <?php if ($avatarUrl !== ''): ?>
                        <img src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar používateľa" class="profile-avatar">
                    <?php else: ?>
                        <span aria-hidden="true" class="profile-avatar-initial"><?php echo htmlspecialchars($avatarInitial !== '' ? $avatarInitial : 'A', ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
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
                            <p><?= htmlspecialchars((string)($sessionUser['address'] ?? 'Hlavná 24, Bratislava'), ENT_QUOTES, 'UTF-8'); ?></p>
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

<?php include __DIR__ . '/partials/footer.php'; ?>