<?php
require_once dirname(__DIR__, 2) . '/app/core/session_helper.php';
require_once dirname(__DIR__, 2) . '/app/core/Redirect.php';

SessionHelper::bootstrap();

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__) . '/models/contact_message.model.php';
$sessionUser = SessionHelper::user();
$profileEmail = (string) ($sessionUser['email'] ?? '');
$profileName = (string) ($sessionUser['name'] ?? '');
$isLoggedIn = (bool) ($sessionUser['is_logged_in'] ?? false);
$pdo = $conn ?? ($GLOBALS['conn'] ?? null);
$profileMessages = [];
$profileMessagesError = '';
$profileNotice = (string) ($_SESSION['profileNotice'] ?? '');
$profileError = (string) ($_SESSION['profileError'] ?? '');

unset($_SESSION['profileNotice'], $_SESSION['profileError']);


if (!$isLoggedIn) {
    (new Redirect('/login.php'))->redirect();
}

if ((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && (string) ($_POST['form_type'] ?? '') === 'reply_to_admin') {
    $messageId = filter_var($_POST['message_id'] ?? null, FILTER_VALIDATE_INT);
    $replyText = trim((string) ($_POST['reply_text'] ?? ''));

    if (!($pdo instanceof PDO)) {
        $_SESSION['profileError'] = 'Databázové pripojenie nie je dostupné.';
    } elseif ($profileEmail === '') {
        $_SESSION['profileError'] = 'E-mail používateľa nie je dostupný.';
    } elseif (!$messageId || $messageId < 1) {
        $_SESSION['profileError'] = 'Neplatné ID správy.';
    } elseif ($replyText === '') {
        $_SESSION['profileError'] = 'Odpoveď nemôže byť prázdna.';
    } else {
        $contactMessageModel = new ContactMessageModel($pdo);
        if ($contactMessageModel->addUserReply((int) $messageId, $profileEmail, $replyText)) {
            $_SESSION['profileNotice'] = 'Odpoveď bola odoslaná administrátorovi.';
        } else {
            $_SESSION['profileError'] = 'Odpoveď sa nepodarilo uložiť.';
        }
    }

    (new Redirect('/userprofile.php'))->redirect();
}

include __DIR__ . '/partials/userprofile-header.php';


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
                                <article class="message-entry">
                                    <div class="message-entry-head">
                                        <div>
                                            <h3><?= htmlspecialchars((string) ($profileMessage['subject'] ?? 'Bez predmetu'), ENT_QUOTES, 'UTF-8'); ?></h3>
                                            <p><?= htmlspecialchars((string) ($profileMessage['created_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                        <span class="message-badge <?= htmlspecialchars((string) ($profileMessage['status'] ?? 'new'), ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars((string) ($profileMessage['status'] ?? 'new'), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>

                                    <div class="message-body">
                                        <strong>Tvoja správa</strong>
                                        <p><?= nl2br(htmlspecialchars((string) ($profileMessage['message_text'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></p>
                                    </div>

                                    <div class="message-reply">
                                        <strong>Reakcia od Red Ghost</strong>
                                        <?php if (!empty($profileMessage['reply_text'])): ?>
                                            <p><?= nl2br(htmlspecialchars((string) $profileMessage['reply_text'], ENT_QUOTES, 'UTF-8')); ?></p>
                                            <?php if (!empty($profileMessage['reply_at'])): ?>
                                                <small>Odpoveď: <?= htmlspecialchars((string) $profileMessage['reply_at'], ENT_QUOTES, 'UTF-8'); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p class="message-reply-empty">Na túto správu ešte neprišla reakcia.</p>
                                        <?php endif; ?>
                                    </div>

                                    <form method="POST" action="<?php echo route('/userprofile.php'); ?>" class="message-reply-form">
                                        <input type="hidden" name="form_type" value="reply_to_admin">
                                        <input type="hidden" name="message_id" value="<?= htmlspecialchars((string) ($profileMessage['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                        <label class="message-reply-label" for="reply-<?php echo htmlspecialchars((string) ($profileMessage['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">Napíš odpoveď</label>
                                        <textarea id="reply-<?php echo htmlspecialchars((string) ($profileMessage['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" name="reply_text" rows="3" placeholder="Doplň otázku alebo reakciu" required></textarea>
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

    <?php
    include __DIR__ . '/partials/footer.php';
    ?>