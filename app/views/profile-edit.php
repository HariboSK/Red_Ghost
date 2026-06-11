<?php
SessionHelper::bootstrap();

$sessionUser = SessionHelper::user();
$isLoggedIn = (bool) ($sessionUser['is_logged_in'] ?? false);
$pdo = $conn ?? ($GLOBALS['conn'] ?? null);

// Ochrana: Ak používateľ nie je prihlásený, smeruje na login
if (!$isLoggedIn) {
    (new Redirect(route('/login')))->redirect();
}

$userId = filter_var($sessionUser['id'] ?? null, FILTER_VALIDATE_INT);
$profileName = (string) ($sessionUser['name'] ?? '');
$profileEmail = (string) ($sessionUser['email'] ?? '');
$profilePhone = (string) ($sessionUser['phone'] ?? $sessionUser['telephone'] ?? '');
$profileAddress = (string) ($sessionUser['address'] ?? '');

$errorMsg = '';
$successMsg = '';

// Spracovanie odoslaného formulára (POST)
if ((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $newName = trim((string) ($_POST['name'] ?? ''));
    $newPhone = trim((string) ($_POST['phone'] ?? ''));
    $newAddress = trim((string) ($_POST['address'] ?? ''));

    if (!($pdo instanceof PDO)) {
        $errorMsg = 'Databázové pripojenie nie je dostupné.';
    } elseif ($userId <= 0) {
        $errorMsg = 'Neplatná relácia používateľa.';
    } elseif ($newName === '') {
        $errorMsg = 'Meno a priezvisko nemôže byť prázdne.';
    } else {
        try {
            // Aktualizácia dát v databáze (tabuľka `user`)
            // TIP: Ak si v databáze použil názov stĺpca `telephone`, prepíš nižšie `phone = :phone` na `telephone = :phone`
            $stmt = $pdo->prepare('
                UPDATE `user` 
                SET name = :name, phone = :phone, address = :address 
                WHERE id = :id
            ');
            
            $success = $stmt->execute([
                ':name'    => $newName,
                ':phone'   => $newPhone !== '' ? $newPhone : null,
                ':address' => $newAddress !== '' ? $newAddress : null,
                ':id'      => $userId
            ]);

            if ($success) {
                // KĽÚČOVÝ KROK: Aktualizujeme dáta aj v Session, aby sa zmeny ihneď prejavili
                $_SESSION['user']['name'] = $newName;
                $_SESSION['user']['phone'] = $newPhone;
                $_SESSION['user']['address'] = $newAddress;

                // Nastavíme oznam a presmerujeme používateľa späť na profil
                $_SESSION['profileNotice'] = 'Vaše osobné údaje boli úspešne upravené.';
                (new Redirect(route('/userprofile')))->redirect();
                exit;
            } else {
                $errorMsg = 'Údaje sa nepodarilo aktualizovať.';
            }
        } catch (PDOException $e) {
            $errorMsg = 'Chyba databázy pri ukladaní údajov: ' . $e->getMessage();
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
                    <?php 
                    $avatarFile = (string) ($sessionUser['image'] ?? '');
                    $avatarFsPath = $avatarFile !== '' ? dirname(__DIR__, 2) . '/public/uploads/avatars/' . $avatarFile : '';
                    $avatarUrl = ($avatarFile !== '' && is_file($avatarFsPath)) ? '/uploads/avatars/' . rawurlencode($avatarFile) : '';
                    $avatarInitial = strtoupper(substr(trim($profileName), 0, 1));
                    
                    if ($avatarUrl !== ''): ?>
                        <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar" class="profile-avatar">
                    <?php else: ?>
                        <span aria-hidden="true" class="profile-avatar-initial"><?= htmlspecialchars($avatarInitial !== '' ? $avatarInitial : 'A', ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
                <h1>ÚPRAVA <span>PROFILU</span></h1>
                <p class="profile-email"><?= htmlspecialchars($profileEmail, ENT_QUOTES, 'UTF-8'); ?></p>
                <a href="<?= route('/userprofile'); ?>" style="display: inline-block; margin-top: 15px; color: #ff5e00; text-decoration: none; font-size: 0.9rem;">
                    <i class="fa-solid fa-arrow-left"></i> Späť na profil
                </a>
            </aside>

            <div class="profile-main">
                <section class="account-card">
                    <div class="section-head">
                        <h2>Upraviť osobné údaje</h2>
                    </div>

                    <?php if ($errorMsg !== ''): ?>
                        <p class="panel-error" style="color: #ff4a4a; background: rgba(255,74,74,0.1); padding: 10px; border-radius: 6px; margin-bottom: 20px;">
                            <?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    <?php endif; ?>

                    <form method="POST" action="<?= route('/profile-edit'); ?>" style="display: flex; flex-direction: column; gap: 20px; margin-top: 15px;">
                        
                        <div style="display: flex; flex-direction: column; gap: 5px;">
                            <label style="font-size: 0.85rem; uppercase; opacity: 0.7;" for="edit-name">Meno a priezvisko</label>
                            <input type="text" id="edit-name" name="name" 
                                   value="<?= htmlspecialchars($profileName, ENT_QUOTES, 'UTF-8'); ?>" 
                                   style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 6px; color: #fff; font-size: 1rem;" 
                                   required>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 5px;">
                            <label style="font-size: 0.85rem; uppercase; opacity: 0.7;" for="edit-email">Email (Nemožno zmeniť)</label>
                            <input type="email" id="edit-email" 
                                   value="<?= htmlspecialchars($profileEmail, ENT_QUOTES, 'UTF-8'); ?>" 
                                   style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 12px; border-radius: 6px; color: #888; font-size: 1rem;" 
                                   disabled>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 5px;">
                            <label style="font-size: 0.85rem; uppercase; opacity: 0.7;" for="edit-phone">Telefónne číslo</label>
                            <input type="text" id="edit-phone" name="phone" 
                                   value="<?= htmlspecialchars($profilePhone, ENT_QUOTES, 'UTF-8'); ?>" 
                                   placeholder="+421 900 000 000"
                                   style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 6px; color: #fff; font-size: 1rem;">
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 5px;">
                            <label style="font-size: 0.85rem; uppercase; opacity: 0.7;" for="edit-address">Fakturačná / Doručovacia adresa</label>
                            <input type="text" id="edit-address" name="address" 
                                   value="<?= htmlspecialchars($profileAddress, ENT_QUOTES, 'UTF-8'); ?>" 
                                   placeholder="Ulica, Číslo, Mesto, PSČ"
                                   style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 6px; color: #fff; font-size: 1rem;">
                        </div>

                        <div style="display: flex; gap: 15px; margin-top: 10px;">
                            <button type="submit" class="management-submit" style="background: #ff5e00; color: #fff; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                                Uložiť zmeny
                            </button>
                            <a href="<?= route('/userprofile'); ?>" style="background: rgba(255,255,255,0.05); color: #fff; padding: 12px 24px; border-radius: 6px; text-decoration: none; text-align: center; font-size: 0.95rem;">
                                Zrušiť
                            </a>
                        </div>
                    </form>
                </section>
            </div>

        </div>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>