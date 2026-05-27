<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/core/session_helper.php';
require_once dirname(__DIR__) . '/core/Redirect.php';
require_once dirname(__DIR__, 2) . '/config/config.php';
// product model may be intentionally removed while rebuilding product flow
$productModelPath = dirname(__DIR__) . '/models/product.model.php';
if (is_file($productModelPath)) {
    require_once $productModelPath;
}

$sessionUser = SessionHelper::user();
$pdo = $conn ?? ($GLOBALS['conn'] ?? null);

if (!($sessionUser['is_logged_in'] ?? false)) {
    (new Redirect('/login.php'))->redirect();
}

if ((string) ($sessionUser['role'] ?? 'user') !== 'admin') {
    http_response_code(403);
    exit('Prístup zamietnutý.');
}

$productId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$productId || $productId < 1) {
    $_SESSION['productError'] = 'Neplatné ID produktu.';
    (new Redirect('/dashboard#products'))->redirect();
}

if (!($pdo instanceof PDO)) {
    $_SESSION['productError'] = 'Databázové pripojenie nie je dostupné.';
    (new Redirect('/dashboard#products'))->redirect();
}

if (!class_exists('ProductModel')) {
    $_SESSION['productError'] = 'Funkcionalita produktov nie je momentalne dostupná.';
    (new Redirect('/dashboard#products'))->redirect();
}

$productModel = new ProductModel($pdo);
$product = $productModel->findById((int) $productId);

if (!is_array($product)) {
    $_SESSION['productError'] = 'Produkt neexistuje.';
    (new Redirect('/dashboard#products'))->redirect();
}

$formError = '';
$formValues = [
    'name' => (string) ($product['name'] ?? ''),
    'description' => (string) ($product['description'] ?? ''),
    'price' => (string) ($product['price'] ?? ''),
    'discount_percent' => (string) ($product['discount_percent'] ?? '0'),
    'image' => (string) ($product['image'] ?? ''),
    'category' => (string) ($product['category'] ?? ''),
    'stock' => (string) ($product['stock'] ?? '0'),
    'featured' => (int) ($product['featured'] ?? 0),
    'rating' => (string) ($product['rating'] ?? '4'),
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

    if (!class_exists('ProductModel')) {
        $formError = 'Funkcionalita produktov nie je momentalne dostupná.';
    } else {
        $validation = ProductModel::validateAndBuildPayload($_POST);
        if (!$validation['ok']) {
            $formError = (string) $validation['error'];
        } else {
            try {
                $productModel->update((int) $productId, $validation['payload']);
                $_SESSION['productNotice'] = 'Produkt bol úspešne upravený.';
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
        <section class="admin-card management-section" id="edit-product">
            <div class="admin-card-head">
                <h2>Edit produkt #<?php echo htmlspecialchars((string) $productId, ENT_QUOTES, 'UTF-8'); ?></h2>
                <span>Samostatná edit.php stránka pre CRUD operáciu Update</span>
            </div>

            <?php if ($formError !== ''): ?>
                <p class="panel-error"><?php echo htmlspecialchars($formError, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <form action="<?php echo route('/edit.php?id=' . urlencode((string) $productId)); ?>" method="POST" class="management-form">
                <label for="edit-product-name">Názov produktu</label>
                <input id="edit-product-name" name="name" type="text" required value="<?php echo htmlspecialchars((string) $formValues['name'], ENT_QUOTES, 'UTF-8'); ?>">

                <label for="edit-product-description">Popis</label>
                <textarea id="edit-product-description" name="description" rows="3"><?php echo htmlspecialchars((string) $formValues['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>

                <label for="edit-product-price">Cena (EUR)</label>
                <input id="edit-product-price" name="price" type="number" step="0.01" min="0" required value="<?php echo htmlspecialchars((string) $formValues['price'], ENT_QUOTES, 'UTF-8'); ?>">

                <label for="edit-product-discount">Zľava produktu (%)</label>
                <input id="edit-product-discount" name="discount_percent" type="number" min="0" max="100" step="0.01" value="<?php echo htmlspecialchars((string) $formValues['discount_percent'], ENT_QUOTES, 'UTF-8'); ?>">

                <label for="edit-product-stock">Skladom (ks)</label>
                <input id="edit-product-stock" name="stock" type="number" min="0" required value="<?php echo htmlspecialchars((string) $formValues['stock'], ENT_QUOTES, 'UTF-8'); ?>">

                <label for="edit-product-category">Kategória</label>
                <input id="edit-product-category" name="category" type="text" required value="<?php echo htmlspecialchars((string) $formValues['category'], ENT_QUOTES, 'UTF-8'); ?>">

                <label for="edit-product-image">Obrázok</label>
                <input id="edit-product-image" name="image" type="text" value="<?php echo htmlspecialchars((string) $formValues['image'], ENT_QUOTES, 'UTF-8'); ?>">

                <label for="edit-product-rating">Hodnotenie</label>
                <input id="edit-product-rating" name="rating" type="number" min="1" max="5" value="<?php echo htmlspecialchars((string) $formValues['rating'], ENT_QUOTES, 'UTF-8'); ?>">

                <label class="checkbox-field">
                    <input type="checkbox" name="featured" value="1" <?php echo ((int) $formValues['featured'] === 1) ? 'checked' : ''; ?>>
                    Odporúčaný produkt
                </label>

                <div class="quick-tools-buttons">
                    <button type="submit" class="management-submit">Uložiť zmeny</button>
                    <a href="<?php echo route('/dashboard#products'); ?>" class="tool-btn ghost-link">Späť na dashboard</a>
                </div>
            </form>
        </section>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php';
