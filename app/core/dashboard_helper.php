<?php

require_once __DIR__ . '/../models/contact_message.model.php';
require_once __DIR__ . '/../models/discount_code.model.php';
require_once __DIR__ . '/../models/order.model.php';
require_once __DIR__ . '/../models/product.model.php';
require_once __DIR__ . '/../models/user.model.php';

class DashboardHelper
{
    private static ?array $baseDashboardState = null;

    public static function h($value): string
    {
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
                'todayRevenue' => 0.0,
                'products' => [],
                'productNotice' => '',
                'productError' => '',
                'discountCodeNotice' => '',
                'discountCodeError' => '',
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

        unset($_SESSION['productNotice'], $_SESSION['productError']);

        return $state;
    }

    private static function handleDashboardPost(?PDO $pdo, array $post, array &$state): void
    {
        if (!($pdo instanceof PDO)) {
            return;
        }

        $productModel = new ProductModel($pdo);
        $discountCodeModel = new DiscountCodeModel($pdo);
        $contactMessageModel = new ContactMessageModel($pdo);
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

        if ($formType === 'create_discount_code') {
            $code = strtoupper(trim((string) ($post['code'] ?? '')));
            $title = trim((string) ($post['title'] ?? ''));
            $discountType = (string) ($post['discount_type'] ?? 'percent');
            $discountValue = filter_var($post['discount_value'] ?? null, FILTER_VALIDATE_FLOAT);
            $minOrderTotal = filter_var($post['min_order_total'] ?? 0, FILTER_VALIDATE_FLOAT);
            $usageLimit = filter_var($post['usage_limit'] ?? null, FILTER_VALIDATE_INT);
            $isActive = isset($post['is_active']) ? 1 : 0;
            $startsAt = trim((string) ($post['starts_at'] ?? ''));
            $endsAt = trim((string) ($post['ends_at'] ?? ''));

            if ($code === '' || strlen($code) < 4) {
                $state['discountCodeError'] = 'Kód musí mať aspoň 4 znaky.';
            } elseif ($discountType !== 'percent' && $discountType !== 'fixed') {
                $state['discountCodeError'] = 'Neplatný typ zľavy.';
            } elseif ($discountValue === false || $discountValue === null || $discountValue <= 0) {
                $state['discountCodeError'] = 'Zadaj platnú zľavu.';
            } else {
                $couponPayload = [
                    'code' => $code,
                    'title' => $title,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'min_order_total' => $minOrderTotal === false || $minOrderTotal === null ? 0 : max(0, (float) $minOrderTotal),
                    'usage_limit' => $usageLimit === false ? null : $usageLimit,
                    'is_active' => $isActive,
                    'starts_at' => $startsAt !== '' ? $startsAt : null,
                    'ends_at' => $endsAt !== '' ? $endsAt : null,
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
            $productValidation = ProductModel::validateAndBuildPayload($post);

            if (!$productValidation['ok']) {
                $state['productError'] = (string) $productValidation['error'];
            } else {
                $productId = filter_var($post['product_id'] ?? null, FILTER_VALIDATE_INT);
                try {
                    if ($formType === 'create_product') {
                        $productModel->create($productValidation['payload']);
                        $state['productNotice'] = 'Produkt bol úspešne pridaný.';
                    } elseif ($productId && $productId > 0) {
                        $productModel->update((int) $productId, $productValidation['payload']);
                        $state['productNotice'] = 'Produkt bol úspešne upravený.';
                    } else {
                        $state['productError'] = 'Neplatné ID produktu.';
                    }
                } catch (PDOException $exception) {
                    $state['productError'] = 'Produkt sa nepodarilo uložiť.';
                }
            }
        }

        if ($formType === 'delete_product') {
            $productId = filter_var($post['product_id'] ?? null, FILTER_VALIDATE_INT);

            if (!$productId || $productId < 1) {
                $state['productError'] = 'Neplatné ID produktu.';
            } else {
                try {
                    $productModel->delete((int) $productId);
                    $state['productNotice'] = 'Produkt bol odstránený.';
                } catch (PDOException $exception) {
                    $state['productError'] = 'Produkt sa nepodarilo odstrániť.';
                }
            }
        }

        if ($formType === 'delete_discount_code') {
            $discountCodeId = filter_var($post['discount_code_id'] ?? null, FILTER_VALIDATE_INT);

            if (!$discountCodeId || $discountCodeId < 1) {
                $state['discountCodeError'] = 'Neplatné ID zľavového kódu.';
            } else {
                try {
                    $discountCodeModel->delete((int) $discountCodeId);
                    $state['discountCodeNotice'] = 'Zľavový kód bol odstránený.';
                } catch (PDOException $exception) {
                    $state['discountCodeError'] = 'Zľavový kód sa nepodarilo odstrániť.';
                }
            }
        }
    }

    private static function loadDashboardData(PDO $pdo, array &$state): void
    {
        $productModel = new ProductModel($pdo);
        $contactMessageModel = new ContactMessageModel($pdo);
        $userModel = new UserModel($pdo);
        $discountCodeModel = new DiscountCodeModel($pdo);
        $orderModel = new OrderModel($pdo);

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
            $state['adminUsersError'] = 'Nepodarilo sa nacitat admin pouzivatelov.';
        }

        try {
            $state['registeredUsers'] = $userModel->getRegistered();
        } catch (PDOException $exception) {
            $state['registeredUsersError'] = 'Nepodarilo sa načítať používateľov.';
        }

        try {
            $state['products'] = $productModel->findAll();
        } catch (PDOException $exception) {
            $state['productError'] = 'Nepodarilo sa načítať produkty: ' . $exception->getMessage();
        }

        try {
            $state['discountCodes'] = $discountCodeModel->getAll();
        } catch (PDOException $exception) {
            $state['discountCodesError'] = 'Nepodarilo sa načítať zľavové kódy.';
        }

        try {
            $todaySummary = $orderModel->getTodaySummary();
            $state['todayOrderCount'] = (int) ($todaySummary['order_count'] ?? 0);
            $state['todayRevenue'] = (float) ($todaySummary['revenue'] ?? 0);
        } catch (PDOException $exception) {
            $state['todayOrderCount'] = 0;
            $state['todayRevenue'] = 0.0;
        }
    }
}

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
    (new ProductModel($pdo))->create($data);
}

function dashboard_update_product(PDO $pdo, int $productId, array $data): void
{
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
    (new ProductModel($pdo))->delete($productId);
}
