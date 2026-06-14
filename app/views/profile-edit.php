<?php
SessionHelper::bootstrap();
$sessionUser = SessionHelper::user();
$pdo = $conn ?? ($GLOBALS['conn'] ?? null);

// Inštancia modulu
$userModule = new UserEditModule($pdo);
$profileName = (string) ($sessionUser['name'] ?? '');
$profileEmail = (string) ($sessionUser['email'] ?? '');

$profilePhone = (string) ($sessionUser['phone'] ?? $sessionUser['telephone'] ?? '');
$profileAddress = (string) ($sessionUser['address'] ?? '');

$profilePhone = $profilePhone ?? '';
$profileAddress = $profileAddress ?? '';

$errorMsg = '';
$successMsg = '';
$profilePhone = (string) ($sessionUser['phone'] ?? $sessionUser['telephone'] ?? '');
$profileAddress = (string) ($sessionUser['address'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'    => trim($_POST['name'] ?? ''),
        'phone'   => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? '')
    ];

    if ($userModule->updateProfile($sessionUser['id'], $data)) {
        // Aktualizácia session
        $_SESSION['user']['name'] = $data['name'];
        $_SESSION['profileNotice'] = 'Údaje boli úspešne uložené.';
        (new Redirect(route('/userprofile')))->redirect();
        exit;
    } else {
        $errorMsg = 'Nastala chyba pri ukladaní údajov.';
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
                        <p class="panel-error"><?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>

                    <form method="POST" action="<?= route('/profile-edit'); ?>" class="profile-edit-form">
                        <?php echo SessionHelper::csrfField(); ?>
                        <div class="form-group">
                            <label for="edit-name">Meno a priezvisko</label>
                            <input type="text" id="edit-name" name="name" value="<?= htmlspecialchars($profileName, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="edit-email">Email (Nemožno zmeniť)</label>
                            <input type="email" id="edit-email" value="<?= htmlspecialchars($profileEmail, ENT_QUOTES, 'UTF-8'); ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label for="edit-phone">Telefónne číslo</label>
                            <input type="text" id="edit-phone" name="phone" value="<?= htmlspecialchars($profilePhone, ENT_QUOTES, 'UTF-8'); ?>" placeholder="+421 900 000 000">
                        </div>

                        <div class="form-group">
                            <label for="edit-address">Fakturačná / Doručovacia adresa</label>
                            <input type="text" id="edit-address" name="address" value="<?= htmlspecialchars($profileAddress, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ulica, Číslo, Mesto, PSČ">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="management-submit">Uložiť zmeny</button>
                            <a href="<?= route('/userprofile'); ?>" class="cancel-link">Zrušiť</a>
                        </div>
                    </form>
                </section>
            </div>

        </div>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>