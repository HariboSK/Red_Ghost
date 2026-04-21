<?php

class DashboardHelper
{
    public static function h($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function markMessageAsRead(PDO $pdo, int $messageId): bool
    {
        $stmt = $pdo->prepare('UPDATE contact_messages SET status = :new_status WHERE id = :id AND status = :old_status');

        return $stmt->execute([
            ':new_status' => 'read',
            ':id' => $messageId,
            ':old_status' => 'unread',
        ]);
    }

    public static function getUnreadMessages(PDO $pdo): array
    {
        $stmt = $pdo->prepare('SELECT id, sender_name, sender_email, subject, message_text, created_at FROM contact_messages WHERE status = :status ORDER BY created_at DESC');
        $stmt->execute([':status' => 'unread']);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public static function getAdminUsers(PDO $pdo): array
    {
        $stmt = $pdo->prepare('SELECT id, name AS username, password, NULL AS created_at FROM users WHERE role = :role ORDER BY id DESC LIMIT 20');
        $stmt->execute([':role' => 'admin']);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public static function getRegisteredUsers(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT id, name, email, loayalty_points AS loyalty_points, role FROM users ORDER BY id DESC LIMIT 50');
        $rows = $stmt instanceof PDOStatement ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        return is_array($rows) ? $rows : [];
    }

    public static function getProducts(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT id, name, description, price, discount_percent, image, category, stock, featured, rating, created_at, updated_at FROM products ORDER BY id DESC');
        $rows = $stmt instanceof PDOStatement ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        return is_array($rows) ? $rows : [];
    }

    public static function getDiscountCodes(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT id, code, title, discount_type, discount_value, min_order_total, usage_limit, used_count, is_active, starts_at, ends_at, created_at FROM discount_codes ORDER BY id DESC');
        $rows = $stmt instanceof PDOStatement ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        return is_array($rows) ? $rows : [];
    }

    public static function getTodayOrderSummary(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT COUNT(*) AS order_count, COALESCE(SUM(total_price), 0) AS revenue FROM orders WHERE DATE(created_at) = CURDATE()');

        if (!($stmt instanceof PDOStatement)) {
            return [
                'order_count' => 0,
                'revenue' => 0.0,
            ];
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($row)) {
            return [
                'order_count' => 0,
                'revenue' => 0.0,
            ];
        }

        return [
            'order_count' => (int) ($row['order_count'] ?? 0),
            'revenue' => (float) ($row['revenue'] ?? 0),
        ];
    }

    public static function getCouponCount(array $discountCodes): int
    {
        return count($discountCodes);
    }

    public static function createProduct(PDO $pdo, array $data): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO products (name, description, price, discount_percent, image, category, stock, featured, rating) VALUES (:name, :description, :price, :discount_percent, :image, :category, :stock, :featured, :rating)'
        );

        $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':discount_percent' => $data['discount_percent'],
            ':image' => $data['image'],
            ':category' => $data['category'],
            ':stock' => $data['stock'],
            ':featured' => $data['featured'],
            ':rating' => $data['rating'],
        ]);
    }

    public static function updateProduct(PDO $pdo, int $productId, array $data): void
    {
        $stmt = $pdo->prepare(
            'UPDATE products SET name = :name, description = :description, price = :price, discount_percent = :discount_percent, image = :image, category = :category, stock = :stock, featured = :featured, rating = :rating WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $productId,
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':discount_percent' => $data['discount_percent'],
            ':image' => $data['image'],
            ':category' => $data['category'],
            ':stock' => $data['stock'],
            ':featured' => $data['featured'],
            ':rating' => $data['rating'],
        ]);
    }

    public static function createDiscountCode(PDO $pdo, array $data): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO discount_codes (code, title, discount_type, discount_value, min_order_total, usage_limit, used_count, is_active, starts_at, ends_at) VALUES (:code, :title, :discount_type, :discount_value, :min_order_total, :usage_limit, :used_count, :is_active, :starts_at, :ends_at)'
        );

        $stmt->execute([
            ':code' => $data['code'],
            ':title' => $data['title'],
            ':discount_type' => $data['discount_type'],
            ':discount_value' => $data['discount_value'],
            ':min_order_total' => $data['min_order_total'],
            ':usage_limit' => $data['usage_limit'],
            ':used_count' => 0,
            ':is_active' => $data['is_active'],
            ':starts_at' => $data['starts_at'],
            ':ends_at' => $data['ends_at'],
        ]);
    }

    public static function deleteDiscountCode(PDO $pdo, int $discountCodeId): void
    {
        $stmt = $pdo->prepare('DELETE FROM discount_codes WHERE id = :id');
        $stmt->execute([':id' => $discountCodeId]);
    }

    public static function deleteProduct(PDO $pdo, int $productId): void
    {
        $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
        $stmt->execute([':id' => $productId]);
    }

    public static function buildDashboardState(?PDO $pdo, array $sessionUser, array $server, array $post): array
    {
        $state = self::defaultDashboardState($sessionUser);

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

    private static function defaultDashboardState(array $sessionUser): array
    {
        return [
            'redirectTo' => null,
            'adminMailerNotice' => '',
            'adminMailerError' => '',
            'unreadMessages' => [],
            'unreadQuestionsCount' => 0,
            'adminUsers' => [],
            'adminUsersError' => '',
            'registeredUsers' => [],
            'registeredUsersError' => '',
            'discountCodes' => [],
            'discountCodesError' => '',
            'activityFeed' => [],
            'totalAdmins' => 0,
            'activeSessions' => session_status() === PHP_SESSION_ACTIVE ? 1 : 0,
            'todayOrderCount' => 0,
            'todayRevenue' => 0.0,
            'products' => [],
            'productNotice' => '',
            'productError' => '',
            'discountCodeNotice' => '',
            'discountCodeError' => '',
            'adminDisplayName' => (string) ($sessionUser['name'] ?? 'Admin'),
            'adminDisplayEmail' => (string) ($sessionUser['email'] ?? ''),
            'adminDisplayRole' => (string) ($sessionUser['role'] ?? 'administrator'),
        ];
    }

    private static function handleDashboardPost(?PDO $pdo, array $post, array &$state): void
    {
        $formType = (string) ($post['form_type'] ?? '');

        if ($formType === 'mark_message_read') {
            $messageId = filter_var($post['message_id'] ?? null, FILTER_VALIDATE_INT);

            if (!$pdo instanceof PDO) {
                $state['adminMailerError'] = 'Databázové pripojenie nie je dostupné.';
            } elseif (!$messageId || $messageId < 1) {
                $state['adminMailerError'] = 'Neplatné ID správy.';
            } else {
                try {
                    self::markMessageAsRead($pdo, (int) $messageId);
                    $state['adminMailerNotice'] = 'Správa bola označená ako vybavená.';
                } catch (PDOException $exception) {
                    $state['adminMailerError'] = 'Status správy sa nepodarilo aktualizovať.';
                }
            }
        }

        if ($formType === 'create_discount_code') {
            if (!$pdo instanceof PDO) {
                $state['discountCodeError'] = 'Databázové pripojenie nie je dostupné.';
            } else {
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
                        self::createDiscountCode($pdo, $couponPayload);
                        $state['discountCodeNotice'] = 'Zľavový kód bol vytvorený.';
                    } catch (PDOException $exception) {
                        $state['discountCodeError'] = 'Zľavový kód sa nepodarilo uložiť.';
                    }
                }
            }
        }

        if ($formType === 'create_product' || $formType === 'update_product') {
            if (!$pdo instanceof PDO) {
                $state['productError'] = 'Databázové pripojenie nie je dostupné.';
            } else {
                $name = trim((string) ($post['name'] ?? ''));
                $description = trim((string) ($post['description'] ?? ''));
                $price = filter_var($post['price'] ?? null, FILTER_VALIDATE_FLOAT);
                $stock = filter_var($post['stock'] ?? null, FILTER_VALIDATE_INT);
                $category = trim((string) ($post['category'] ?? ''));
                $image = trim((string) ($post['image'] ?? ''));
                $featured = isset($post['featured']) ? 1 : 0;
                $rating = filter_var($post['rating'] ?? 4, FILTER_VALIDATE_INT);
                $productId = filter_var($post['product_id'] ?? null, FILTER_VALIDATE_INT);
                $discountPercent = filter_var($post['discount_percent'] ?? 0, FILTER_VALIDATE_FLOAT);

                if ($name === '' || strlen($name) < 2) {
                    $state['productError'] = 'Názov produktu je povinný.';
                } elseif ($price === false || $price === null || $price < 0) {
                    $state['productError'] = 'Zadaj platnú cenu produktu.';
                } elseif ($stock === false || $stock === null || $stock < 0) {
                    $state['productError'] = 'Zadaj platný počet skladom.';
                } elseif ($discountPercent === false || $discountPercent === null || $discountPercent < 0 || $discountPercent > 100) {
                    $state['productError'] = 'Zľava produktu musí byť medzi 0 a 100.';
                } elseif ($category === '') {
                    $state['productError'] = 'Kategória je povinná.';
                } else {
                    $payload = [
                        'name' => $name,
                        'description' => $description,
                        'price' => $price,
                        'discount_percent' => max(0, min(100, (float) $discountPercent)),
                        'image' => $image,
                        'category' => $category,
                        'stock' => $stock,
                        'featured' => $featured,
                        'rating' => max(1, min(5, (int) ($rating ?: 4))),
                    ];

                    try {
                        if ($formType === 'create_product') {
                            self::createProduct($pdo, $payload);
                            $state['productNotice'] = 'Produkt bol úspešne pridaný.';
                        } elseif ($productId && $productId > 0) {
                            self::updateProduct($pdo, (int) $productId, $payload);
                            $state['productNotice'] = 'Produkt bol úspešne upravený.';
                        } else {
                            $state['productError'] = 'Neplatné ID produktu.';
                        }
                    } catch (PDOException $exception) {
                        $state['productError'] = 'Produkt sa nepodarilo uložiť.';
                    }
                }
            }
        }

        if ($formType === 'delete_product') {
            $productId = filter_var($post['product_id'] ?? null, FILTER_VALIDATE_INT);

            if (!$pdo instanceof PDO) {
                $state['productError'] = 'Databázové pripojenie nie je dostupné.';
            } elseif (!$productId || $productId < 1) {
                $state['productError'] = 'Neplatné ID produktu.';
            } else {
                try {
                    self::deleteProduct($pdo, (int) $productId);
                    $state['productNotice'] = 'Produkt bol odstránený.';
                } catch (PDOException $exception) {
                    $state['productError'] = 'Produkt sa nepodarilo odstrániť.';
                }
            }
        }

        if ($formType === 'delete_discount_code') {
            $discountCodeId = filter_var($post['discount_code_id'] ?? null, FILTER_VALIDATE_INT);

            if (!$pdo instanceof PDO) {
                $state['discountCodeError'] = 'Databázové pripojenie nie je dostupné.';
            } elseif (!$discountCodeId || $discountCodeId < 1) {
                $state['discountCodeError'] = 'Neplatné ID zľavového kódu.';
            } else {
                try {
                    self::deleteDiscountCode($pdo, (int) $discountCodeId);
                    $state['discountCodeNotice'] = 'Zľavový kód bol odstránený.';
                } catch (PDOException $exception) {
                    $state['discountCodeError'] = 'Zľavový kód sa nepodarilo odstrániť.';
                }
            }
        }
    }

    private static function loadDashboardData(PDO $pdo, array &$state): void
    {
        try {
            $state['unreadMessages'] = self::getUnreadMessages($pdo);
            $state['unreadQuestionsCount'] = count($state['unreadMessages']);
        } catch (PDOException $exception) {
            $state['adminMailerError'] = 'Nepodarilo sa načítať neprečítané správy.';
        }

        try {
            $state['adminUsers'] = self::getAdminUsers($pdo);
            $state['totalAdmins'] = count($state['adminUsers']);
        } catch (PDOException $exception) {
            $state['adminUsersError'] = 'Nepodarilo sa nacitat admin pouzivatelov.';
        }

        try {
            $state['registeredUsers'] = self::getRegisteredUsers($pdo);
        } catch (PDOException $exception) {
            $state['registeredUsersError'] = 'Nepodarilo sa načítať používateľov.';
        }

        try {
            $state['products'] = self::getProducts($pdo);
        } catch (PDOException $exception) {
            $state['productError'] = 'Nepodarilo sa načítať produkty.';
        }

        try {
            $state['discountCodes'] = self::getDiscountCodes($pdo);
        } catch (PDOException $exception) {
            $state['discountCodesError'] = 'Nepodarilo sa načítať zľavové kódy.';
        }

        try {
            $todaySummary = self::getTodayOrderSummary($pdo);
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
    return DashboardHelper::markMessageAsRead($pdo, $messageId);
}

function dashboard_get_unread_messages(PDO $pdo): array
{
    return DashboardHelper::getUnreadMessages($pdo);
}

function dashboard_get_admin_users(PDO $pdo): array
{
    return DashboardHelper::getAdminUsers($pdo);
}

function dashboard_get_registered_users(PDO $pdo): array
{
    return DashboardHelper::getRegisteredUsers($pdo);
}

function dashboard_get_products(PDO $pdo): array
{
    return DashboardHelper::getProducts($pdo);
}

function dashboard_get_discount_codes(PDO $pdo): array
{
    return DashboardHelper::getDiscountCodes($pdo);
}

function dashboard_get_today_order_summary(PDO $pdo): array
{
    return DashboardHelper::getTodayOrderSummary($pdo);
}

function dashboard_get_coupon_count(array $discountCodes): int
{
    return DashboardHelper::getCouponCount($discountCodes);
}

function dashboard_create_product(PDO $pdo, array $data): void
{
    DashboardHelper::createProduct($pdo, $data);
}

function dashboard_update_product(PDO $pdo, int $productId, array $data): void
{
    DashboardHelper::updateProduct($pdo, $productId, $data);
}

function dashboard_create_discount_code(PDO $pdo, array $data): void
{
    DashboardHelper::createDiscountCode($pdo, $data);
}

function dashboard_delete_discount_code(PDO $pdo, int $discountCodeId): void
{
    DashboardHelper::deleteDiscountCode($pdo, $discountCodeId);
}

function dashboard_delete_product(PDO $pdo, int $productId): void
{
    DashboardHelper::deleteProduct($pdo, $productId);
}
