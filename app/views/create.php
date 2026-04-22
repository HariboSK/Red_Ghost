<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/core/session_helper.php';
require_once dirname(__DIR__) . '/core/Redirect.php';
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__) . '/models/product.model.php';

$sessionUser = SessionHelper::user();
$pdo = (isset($pdo) && $pdo instanceof PDO) ? $pdo : ((isset($conn) && $conn instanceof PDO) ? $conn : null);

if (!($sessionUser['is_logged_in'] ?? false)) {
    (new Redirect('/login.php'))->redirect();
}

if ((string) ($sessionUser['role'] ?? 'user') !== 'admin') {
    http_response_code(403);
    exit('Prístup zamietnutý.');
}

$formError = '';
$formValues = [
    'name' => '',
    'description' => '',
    'price' => '',
    'discount_percent' => '0',
    'image' => '',
    'category' => '',
    'stock' => '0',
    'featured' => 0,
    'rating' => '4',
];

if ((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $formValues = array_merge($formValues, [
        'name' => (string) ($_POST['name'] ?? ''),
        'description' => (string) ($_POST['description'] ?? ''),
        'price' => (string) ($_POST['price'] ?? ''),
        'discount_percent' => (string) ($_POST['discount_percent'] ?? '0'),
        'image' => (string) ($_POST['image'] ?? ''),
        'category' => (string) ($_POST['category'] ?? ''),
        'stock' => (string) ($_POST['stock'] ?? '0'),
        'featured' => isset($_POST['featured']) ? 1 : 0,
        'rating' => (string) ($_POST['rating'] ?? '4'),
    ]);

    if (!($pdo instanceof PDO)) {
        $formError = 'Databázové pripojenie nie je dostupné.';
    } else {
        $validation = ProductModel::validateAndBuildPayload($_POST);
        if (!$validation['ok']) {
            $formError = (string) $validation['error'];
        } else {
            try {
                $productModel = new ProductModel($pdo);
                $productModel->create($validation['payload']);

                $_SESSION['productNotice'] = 'Produkt bol úspešne pridaný.';
                (new Redirect('/dashboard#products'))->redirect();
            } catch (PDOException $exception) {
                $formError = 'Produkt sa nepodarilo uložiť.';
            }
        }
    }
}

include __DIR__ . '/partials/dashboard-header.php';
?>

<main class="admin-dashboard-ui">
    <section class="admin-main">
        <section class="admin-card management-section" id="create-product">
            <div class="admin-card-head">
                <h2>Create produkt</h2>
                <span>Samostatná create.php stránka pre CRUD operáciu Create</span>
            </div>

            <?php if ($formError !== ''): ?>
                <p class="panel-error"><?php echo htmlspecialchars($formError, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <form action="<?php echo route('/create.php'); ?>" method="POST" class="management-form">
                <label for="create-product-name">Názov produktu</label>
                <input id="create-product-name" name="name" type="text" required value="<?php echo htmlspecialchars((string) $formValues['name'], ENT_QUOTES, 'UTF-8'); ?>">

                <label for="create-product-description">Popis</label>
                <textarea id="create-product-description" name="description" rows="3"><?php echo htmlspecialchars((string) $formValues['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>

                <label for="create-product-price">Cena (EUR)</label>
                <input id="create-product-price" name="price" type="number" step="0.01" min="0" required value="<?php echo htmlspecialchars((string) $formValues['price'], ENT_QUOTES, 'UTF-8'); ?>">

                <label for="create-product-discount">Zľava produktu (%)</label>
                <input id="create-product-discount" name="discount_percent" type="number" min="0" max="100" step="0.01" value="<?php echo htmlspecialchars((string) $formValues['discount_percent'], ENT_QUOTES, 'UTF-8'); ?>">

                <label for="create-product-stock">Skladom (ks)</label>
                <input id="create-product-stock" name="stock" type="number" min="0" required value="<?php echo htmlspecialchars((string) $formValues['stock'], ENT_QUOTES, 'UTF-8'); ?>">

                <label for="create-product-category">Kategória</label>
                <input id="create-product-category" name="category" type="text" required value="<?php echo htmlspecialchars((string) $formValues['category'], ENT_QUOTES, 'UTF-8'); ?>">

                <label for="create-product-image">Obrázok</label>
                <input id="create-product-image" name="image" type="text" value="<?php echo htmlspecialchars((string) $formValues['image'], ENT_QUOTES, 'UTF-8'); ?>">

                <label for="create-product-rating">Hodnotenie</label>
                <input id="create-product-rating" name="rating" type="number" min="1" max="5" value="<?php echo htmlspecialchars((string) $formValues['rating'], ENT_QUOTES, 'UTF-8'); ?>">

                <label class="checkbox-field">
                    <input type="checkbox" name="featured" value="1" <?php echo ((int) $formValues['featured'] === 1) ? 'checked' : ''; ?>>
                    Odporúčaný produkt
                </label>

                <div class="quick-tools-buttons">
                    <button type="submit" class="management-submit">Uložiť nový produkt</button>
                    <a href="<?php echo route('/dashboard#products'); ?>" class="tool-btn ghost-link">Späť na dashboard</a>
                </div>
            </form>
        </section>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php';
