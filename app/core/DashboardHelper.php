<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/ContactMessageModel.php';
require_once __DIR__ . '/../models/DiscountCodeModel.php';
require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../models/ShopReviewModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/ProductModel.php';

$productModel = new ProductModel($pdo ?? $conn ?? null);

class DashboardHelper
{
    private static ?array $baseDashboardState = null;


    public static function h(mixed $value): string
    {
        if (is_array($value)) {
            throw new Error('Error: Cannot be an array');
        }
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function getCouponCount(array $discountCodes): int
    {
        return count($discountCodes);
    }

    public static function buildDashboardState(?PDO $pdo, array $sessionUser, array $server, array $post): array
    {
        $state = self::initializeDashboardState($sessionUser);

        if (!($sessionUser['is_logged_in'] ?? false)) {
            $state['redirectTo'] = '/login.php';
            return $state;
        }

        if ((string) ($server['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            self::handleDashboardPost($pdo, $post, $state);
        }

        if ($pdo instanceof PDO) {
            self::loadDashboardData($pdo, $state);
        } elseif ($state['adminMailerError'] === '') {
            $state['adminMailerError'] = 'Databázové pripojenie nie je dostupné.';
            $state['adminUsersError'] = 'Databázové pripojenie nie je dostupné.';
            $state['registeredUsersError'] = 'Databázové pripojenie nie je dostupné.';
            $state['productError'] = 'Databázové pripojenie nie je dostupné.';
            $state['discountCodesError'] = 'Databázové pripojenie nie je dostupné.';
        }

        if ($state['adminMailerNotice'] !== '') {
            $state['activityFeed'][] = [
                'time' => date('Y-m-d H:i:s'),
                'text' => $state['adminMailerNotice'],
            ];
        }

        foreach (array_slice($state['unreadMessages'], 0, 5) as $message) {
            $state['activityFeed'][] = [
                'time' => (string) ($message['created_at'] ?? date('Y-m-d H:i:s')),
                'text' => 'Nová správa od ' . (string) ($message['sender_name'] ?? 'neznámy používateľ'),
            ];
        }

        if (empty($state['activityFeed'])) {
            $state['activityFeed'][] = [
                'time' => date('Y-m-d H:i:s'),
                'text' => 'Zatiaľ nebola zaznamenaná žiadna nová admin aktivita.',
            ];
        }

        return $state;
    }

    private static function initializeDashboardState(array $sessionUser): array
    {
        if (self::$baseDashboardState === null) {
            self::$baseDashboardState = [
                'redirectTo' => null,
                'adminMailerNotice' => '',
                'adminMailerError' => '',
                'unreadMessages' => [],
                'contactMessages' => [],
                'unreadQuestionsCount' => 0,
                'adminUsers' => [],
                'adminUsersError' => '',
                'registeredUsers' => [],
                'registeredUsersError' => '',
                'discountCodes' => [],
                'discountCodesError' => '',
                'activityFeed' => [],
                'totalAdmins' => 0,
                'activeSessions' => 0,
                'todayOrderCount' => 0,
                'recentOrders' => [],
                'todayRevenue' => 0.0,
                'products' => [],
                'productNotice' => '',
                'productError' => '',
                'discountCodeNotice' => '',
                'discountCodeError' => '',
                'reviewNotice' => '',
                'reviewError' => '',
                'shopReviews' => [],
                'adminDisplayName' => '',
                'adminDisplayEmail' => '',
                'adminDisplayRole' => '',
            ];
        }

        $state = self::$baseDashboardState;
        $state['activeSessions'] = session_status() === PHP_SESSION_ACTIVE ? 1 : 0;
        $state['adminDisplayName'] = (string) ($sessionUser['name'] ?? 'Admin');
        $state['adminDisplayEmail'] = (string) ($sessionUser['email'] ?? '');
        $state['adminDisplayRole'] = (string) ($sessionUser['role'] ?? 'administrator');
        $state['productNotice'] = (string) ($_SESSION['productNotice'] ?? '');
        $state['productError'] = (string) ($_SESSION['productError'] ?? '');
        $state['adminMailerNotice'] = (string) ($_SESSION['adminMailerNotice'] ?? $state['adminMailerNotice']);
        $state['adminMailerError'] = (string) ($_SESSION['adminMailerError'] ?? $state['adminMailerError']);
        $state['discountCodeNotice'] = (string) ($_SESSION['discountCodeNotice'] ?? $state['discountCodeNotice']);
        $state['discountCodeError'] = (string) ($_SESSION['discountCodeError'] ?? $state['discountCodeError']);
        $state['reviewNotice'] = (string) ($_SESSION['reviewNotice'] ?? $state['reviewNotice']);
        $state['reviewError'] = (string) ($_SESSION['reviewError'] ?? $state['reviewError']);

        unset(
            $_SESSION['productNotice'],
            $_SESSION['productError'],
            $_SESSION['adminMailerNotice'],
            $_SESSION['adminMailerError'],
            $_SESSION['discountCodeNotice'],
            $_SESSION['discountCodeError'],
            $_SESSION['reviewNotice'],
            $_SESSION['reviewError']
        );

        return $state;
    }

    private static function handleDashboardPost(?PDO $pdo, array $post, array &$state): void
    {
        if (!($pdo instanceof PDO)) {
            return;
        }

        $productModel = class_exists('ProductModel') ? new ProductModel($pdo) : null;
        $discountCodeModel = new DiscountCodeModel($pdo);
        $contactMessageModel = new ContactMessageModel($pdo);
        $shopReviewModel = new ShopReviewModel($pdo);
        $formType = (string) ($post['form_type'] ?? '');

        if ($formType === 'mark_message_read') {
            $messageId = filter_var($post['message_id'] ?? null, FILTER_VALIDATE_INT);

            if (!$messageId || $messageId < 1) {
                $state['adminMailerError'] = 'Neplatné ID správy.';
            } else {
                try {
                    $contactMessageModel->markAsRead((int) $messageId);
                    $state['adminMailerNotice'] = 'Správa bola označená ako vybavená.';
                } catch (PDOException $exception) {
                    $state['adminMailerError'] = 'Status správy sa nepodarilo aktualizovať.';
                }
            }
        }

        if ($formType === 'reply_message') {
            $messageId = filter_var($post['message_id'] ?? null, FILTER_VALIDATE_INT);
            $replyText = trim((string) ($post['reply_text'] ?? ''));

            if (!$messageId || $messageId < 1) {
                $state['adminMailerError'] = 'Neplatné ID správy.';
            } elseif ($replyText === '') {
                $state['adminMailerError'] = 'Reakcia nemôže byť prázdna.';
            } else {
                try {
                    $contactMessageModel->reply((int) $messageId, $replyText);
                    $state['adminMailerNotice'] = 'Reakcia bola uložená a odoslaná do profilu zákazníka.';
                } catch (PDOException $exception) {
                    $state['adminMailerError'] = 'Reakciu sa nepodarilo uložiť.';
                }
            }
        }

        if ($formType === 'delete_message') {
            $messageId = filter_var($post['message_id'] ?? null, FILTER_VALIDATE_INT);

            if (!$messageId || $messageId < 1) {
                $state['adminMailerError'] = 'Neplatné ID správy.';
            } else {
                try {
                    $contactMessageModel->delete((int) $messageId);
                    $state['adminMailerNotice'] = 'Správa bola odstránená.';
                } catch (PDOException $exception) {
                    $state['adminMailerError'] = 'Správu sa nepodarilo odstrániť.';
                }
            }
        }

        if ($formType === 'delete_all_messages') {
            try {
                $contactMessageModel->deleteAll();
                $state['adminMailerNotice'] = 'Všetky správy boli odstránené.';
            } catch (PDOException $exception) {
                $state['adminMailerError'] = 'Správy sa nepodarilo hromadne odstrániť.';
            }
        }

        if ($formType === 'create_discount_code') {
            $code = strtoupper(trim((string) ($post['code'] ?? '')));
            $description = trim((string) ($post['description'] ?? ''));
            $discountType = (string) ($post['discount_type'] ?? 'percent');
            $discountValue = filter_var($post['value'] ?? null, FILTER_VALIDATE_FLOAT);
            $minOrderValue = filter_var($post['min_order_value'] ?? 0, FILTER_VALIDATE_FLOAT);
            $isActive = isset($post['is_active']) ? 1 : 0;
            $validFrom = trim((string) ($post['valid_from'] ?? ''));
            $validTo = trim((string) ($post['valid_to'] ?? ''));

            if ($code === '' || strlen($code) < 4) {
                $state['discountCodeError'] = 'Kód musí mať aspoň 4 znaky.';
            } elseif ($discountType !== 'percent' && $discountType !== 'fixed') {
                $state['discountCodeError'] = 'Neplatný typ zľavy.';
            } elseif ($discountValue === false || $discountValue === null || $discountValue <= 0) {
                $state['discountCodeError'] = 'Zadaj platnú zľavu.';
            } else {
                $couponPayload = [
                    'code' => $code,
                    'description' => $description,
                    'discount_type' => $discountType,
                    'value' => $discountValue,
                    'min_order_value' => $minOrderValue === false || $minOrderValue === null ? 0 : max(0, (float) $minOrderValue),
                    'is_active' => $isActive,
                    'valid_from' => $validFrom !== '' ? $validFrom : null,
                    'valid_to' => $validTo !== '' ? $validTo : null,
                ];

                try {
                    $discountCodeModel->create($couponPayload);
                    $state['discountCodeNotice'] = 'Zľavový kód bol vytvorený.';
                } catch (PDOException $exception) {
                    $state['discountCodeError'] = 'Zľavový kód sa nepodarilo uložiť.';
                }
            }
        }

        if ($formType === 'create_product' || $formType === 'update_product') {
            if (!class_exists('ProductModel')) {
                $state['productError'] = 'Funkcionalita produktov nie je momentálne dostupná.';
            } else {
                $productValidation = ProductModel::validateAndBuildPayload($post);

                if (!$productValidation['ok']) {
                    $state['productError'] = (string) $productValidation['error'];
                } else {
                    $productId = filter_var($post['product_id'] ?? null, FILTER_VALIDATE_INT);
                    try {
                        if ($formType === 'create_product') {
                            // zápis do DB
                            $productModel->create($productValidation['payload']);
                            $state['productNotice'] = 'Produkt bol úspešne pridaný.';
                        } elseif ($productId && $productId > 0) {
                            // volanie modelu pre úpravu v DB
                            $productModel->update((int) $productId, $productValidation['payload']);
                            $state['productNotice'] = 'Produkt bol úspešne upravený.';
                        } else {
                            $state['productError'] = 'Neplatné ID produktu.';
                        }
                    } catch (PDOException $exception) {
                        // Ak by zlyhala databáza (napr. chýbajúca tabuľka), vypíše sa toto:
                        $state['productError'] = 'Produkt sa nepodarilo uložiť do databázy: ' . $exception->getMessage();
                    }
                }
            }
        }

        if ($formType === 'delete_product') {
            if (!class_exists('ProductModel')) {
                $state['productError'] = 'Funkcionalita produktov nie je momentálne dostupná.';
            } else {
                $productId = filter_var($post['product_id'] ?? null, FILTER_VALIDATE_INT);

                if (!$productId || $productId < 1) {
                    $state['productError'] = 'Neplatné ID produktu.';
                } else {
                    if ($productModel->delete((int) $productId)) {
                        $state['productNotice'] = 'Produkt bol odstránený.';
                    } else {
                        $state['productError'] = 'Produkt sa nepodarilo odstrániť.';
                    }
                }
            }
        }

        if ($formType === 'delete_discount_code') {
            $discountCodeId = filter_var($post['discount_code_id'] ?? null, FILTER_VALIDATE_INT);

            if (!$discountCodeId || $discountCodeId < 1) {
                $state['discountCodeError'] = 'Neplatné ID zľavového kódu.';
            } else {
                if ($discountCodeModel->delete((int) $discountCodeId)) {
                    $state['discountCodeNotice'] = 'Zľavový kód bol odstránený.';
                } else {
                    $state['discountCodeError'] = 'Zľavový kód sa nepodarilo odstrániť.';
                }
            }
        }

        if ($formType === 'approve_shop_review') {
            $reviewId = filter_var($post['review_id'] ?? null, FILTER_VALIDATE_INT);

            if (!$reviewId || $reviewId < 1) {
                $state['reviewError'] = 'Neplatné ID recenzie.';
            } else {
                if ($shopReviewModel->approve((int) $reviewId)) {
                    $state['reviewNotice'] = 'Recenzia bola schválená a používateľ získal 100 vernostných bodov.';
                } else {
                    $state['reviewError'] = 'Recenzia už bola schválená alebo neexistuje.';
                }
            }
        }

        if ($formType === 'delete_shop_review') {
            $reviewId = filter_var($post['review_id'] ?? null, FILTER_VALIDATE_INT);

            if (!$reviewId || $reviewId < 1) {
                $state['reviewError'] = 'Neplatné ID recenzie.';
            } else {
                if ($shopReviewModel->deleteReview((int) $reviewId)) {
                    $state['reviewNotice'] = 'Recenzia bola odstránená.';
                } else {
                    $state['reviewError'] = 'Recenziu sa nepodarilo odstrániť.';
                }
            }
        }

        if ($formType === 'update_order_status') {
            $orderId = filter_var($post['order_id'] ?? null, FILTER_VALIDATE_INT);
            $orderStatus = (string) ($post['order_status'] ?? 'pending');

            if (!$orderId || $orderId < 1) {
                $state['adminMailerError'] = 'Neplatné ID objednávky.';
            } elseif (!in_array($orderStatus, OrderModel::statusOptions(), true)) {
                $state['adminMailerError'] = 'Neplatný stav objednávky.';
            } elseif ((new OrderModel($pdo))->updateStatus((int) $orderId, $orderStatus)) {
                $state['adminMailerNotice'] = 'Stav objednávky bol aktualizovaný.';
            } else {
                $state['adminMailerError'] = 'Stav objednávky sa nepodarilo zmeniť.';
            }
        }

        if ($formType !== '') {
            $_SESSION['adminMailerNotice'] = (string) ($state['adminMailerNotice'] ?? '');
            $_SESSION['adminMailerError'] = (string) ($state['adminMailerError'] ?? '');
            $_SESSION['productNotice'] = (string) ($state['productNotice'] ?? '');
            $_SESSION['productError'] = (string) ($state['productError'] ?? '');
            $_SESSION['discountCodeNotice'] = (string) ($state['discountCodeNotice'] ?? '');
            $_SESSION['discountCodeError'] = (string) ($state['discountCodeError'] ?? '');
            $_SESSION['reviewNotice'] = (string) ($state['reviewNotice'] ?? '');
            $_SESSION['reviewError'] = (string) ($state['reviewError'] ?? '');

            $redirectAnchors = [
                'mark_message_read' => '#mailer',
                'reply_message' => '#mailer',
                'delete_message' => '#mailer',
                'delete_all_messages' => '#mailer',
                'create_product' => '#products',
                'update_product' => '#products',
                'delete_product' => '#products',
                'create_discount_code' => '#coupons',
                'delete_discount_code' => '#coupons',
                'approve_shop_review' => '#reviews',
                'delete_shop_review' => '#reviews',
                'update_order_status' => '#orders',
            ];

            $state['redirectTo'] = '/dashboard' . ($redirectAnchors[$formType] ?? '');
        }
    }

    private static function loadDashboardData(PDO $pdo, array &$state): void
    {
        $productModel = class_exists('ProductModel') ? new ProductModel($pdo) : null;
        $contactMessageModel = new ContactMessageModel($pdo);
        $userModel = new UserModel($pdo);
        $discountCodeModel = new DiscountCodeModel($pdo);
        $orderModel = new OrderModel($pdo);
        $shopReviewModel = new ShopReviewModel($pdo);

        try {
            $state['unreadMessages'] = $contactMessageModel->getUnread();
            $state['unreadQuestionsCount'] = count($state['unreadMessages']);
            $state['contactMessages'] = $contactMessageModel->getAll();
        } catch (PDOException $exception) {
            $state['adminMailerError'] = 'Nepodarilo sa načítať neprečítané správy.';
        }

        try {
            $state['adminUsers'] = $userModel->getAdmins();
            $state['totalAdmins'] = count($state['adminUsers']);
        } catch (PDOException $exception) {
            $state['adminUsersError'] = 'Nepodarilo sa načítať admin používateľov.';
        }

        try {
            $state['registeredUsers'] = $userModel->getRegistered();
        } catch (PDOException $exception) {
            $state['registeredUsersError'] = 'Nepodarilo sa načítať používateľov.';
        }

        try {
            if ($productModel) {
                $state['products'] = $productModel->findAll();
            } else {
                $state['products'] = [];
                $state['productError'] = 'Funkcionalita produktov nie je momentálne dostupná.';
            }
        } catch (PDOException $exception) {
            $state['productError'] = 'Nepodarilo sa načítať produkty: ' . $exception->getMessage();
        }

        try {
            $state['discountCodes'] = $discountCodeModel->getAll();
        } catch (PDOException $exception) {
            $state['discountCodesError'] = 'Nepodarilo sa načítať zľavové kódy.';
        }

        try {
            $state['shopReviews'] = $shopReviewModel->getAll();
        } catch (PDOException $exception) {
            $state['reviewError'] = 'Nepodarilo sa načítať recenzie.';
        }

        try {
            $todaySummary = $orderModel->getTodaySummary();
            $state['todayOrderCount'] = (int) ($todaySummary['order_count'] ?? 0);
            $state['todayRevenue'] = (float) ($todaySummary['revenue'] ?? 0);
        } catch (PDOException $exception) {
            $state['todayOrderCount'] = 0;
            $state['todayRevenue'] = 0.0;
        }

        try {
            $state['recentOrders'] = $orderModel->getRecentOrders(50);
        } catch (PDOException $exception) {
            $state['recentOrders'] = [];
        }
    }
}

// Procedurálne funkcie na spodu súboru
function dash_h($value): string
{
    return DashboardHelper::h($value);
}

function dashboard_mark_message_as_read(PDO $pdo, int $messageId): bool
{
    return (new ContactMessageModel($pdo))->markAsRead($messageId);
}

function dashboard_get_unread_messages(PDO $pdo): array
{
    return (new ContactMessageModel($pdo))->getUnread();
}

function dashboard_get_contact_messages(PDO $pdo): array
{
    return (new ContactMessageModel($pdo))->getAll();
}

function dashboard_reply_to_message(PDO $pdo, int $messageId, string $replyText): bool
{
    return (new ContactMessageModel($pdo))->reply($messageId, $replyText);
}

function dashboard_delete_message(PDO $pdo, int $messageId): bool
{
    return (new ContactMessageModel($pdo))->delete($messageId);
}

function dashboard_delete_all_messages(PDO $pdo): bool
{
    return (new ContactMessageModel($pdo))->deleteAll();
}

function dashboard_get_messages_by_email(PDO $pdo, string $email): array
{
    return (new ContactMessageModel($pdo))->getByEmail($email);
}

function dashboard_get_admin_users(PDO $pdo): array
{
    return (new UserModel($pdo))->getAdmins();
}

function dashboard_get_registered_users(PDO $pdo): array
{
    return (new UserModel($pdo))->getRegistered();
}

function dashboard_get_products(PDO $pdo): array
{
    if (!class_exists('ProductModel')) {
        return [];
    }
    return (new ProductModel($pdo))->findAll();
}

function dashboard_get_discount_codes(PDO $pdo): array
{
    return (new DiscountCodeModel($pdo))->getAll();
}

function dashboard_get_today_order_summary(PDO $pdo): array
{
    return (new OrderModel($pdo))->getTodaySummary();
}

function dashboard_get_coupon_count(array $discountCodes): int
{
    return DashboardHelper::getCouponCount($discountCodes);
}

function dashboard_create_product(PDO $pdo, array $data): void
{
    if (!class_exists('ProductModel')) {
        throw new RuntimeException('Product functionality not available');
    }
    (new ProductModel($pdo))->create($data);
}

function dashboard_update_product(PDO $pdo, int $productId, array $data): void
{
    if (!class_exists('ProductModel')) {
        throw new RuntimeException('Product functionality not available');
    }
    (new ProductModel($pdo))->update($productId, $data);
}

function dashboard_create_discount_code(PDO $pdo, array $data): void
{
    (new DiscountCodeModel($pdo))->create($data);
}

function dashboard_delete_discount_code(PDO $pdo, int $discountCodeId): void
{
    (new DiscountCodeModel($pdo))->delete($discountCodeId);
}

function dashboard_delete_product(PDO $pdo, int $productId): void
{
    if (!class_exists('ProductModel')) {
        throw new RuntimeException('Product functionality not available');
    }
    (new ProductModel($pdo))->delete($productId);
}