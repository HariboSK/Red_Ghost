<?php

class UserProfileModule {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getProfileData(int $userId, string $email): array {
        $stmt = $this->pdo->prepare("
            SELECT u.unique_reset_passwd, u.telephone, u.loyalty_points, u.created_at, a.street 
            FROM `user` u
            LEFT JOIN address a ON u.id = a.id_user
            WHERE u.id = :id
        ");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        //Objednávky
        $orderModel = new OrderModel($this->pdo);
        $orders = $orderModel->getOrdersByUserId($userId, 12);

        //Správy
        $contactModel = new ContactMessageModel($this->pdo);
        $messages = $contactModel->getByEmail($email);

        return [
            'user' => $user,
            'orders' => $orders,
            'messages' => $messages
        ];
    }

    public function getAvatarUrl(?string $filename): string {
        $path = dirname(__DIR__, 2) . '/public/uploads/avatars/' . $filename;
        if ($filename && is_file($path)) {
            return '/uploads/avatars/' . rawurlencode($filename);
        }
        return '';
    }

    public function sendReply(int $userId, string $name, string $email, string $text): bool {
        $stmt = $this->pdo->prepare("INSERT INTO `contact_messages` (sender_name, sender_email, subject, id_user) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$name, $email, $text, $userId]);
    }

    public function getViewModel(int $userId, array $sessionUser): array {
    // 1. ziskanie dat
    $data = $this->getProfileData($userId, (string)($sessionUser['email'] ?? ''));
    
    // 2. Priprava všetky premenné
    $profileName = (string) ($sessionUser['name'] ?? 'Zákazník');
    $profileEmail = (string) ($sessionUser['email'] ?? '');
    $uniqueResetCode = (string) ($data['user']['unique_reset_passwd'] ?? '');
    
    return [
        'dbUser'              => $data['user'],
        'profileOrders'       => $data['orders'],
        'profileMessages'     => $data['messages'],
        'profileName'         => $profileName,
        'profileEmail'        => $profileEmail,
        'profileAddress'    => trim((string) ($data['user']['street'] ?? '')), 
        'profilePhone'      => trim((string) ($data['user']['telephone'] ?? '')),
        'avatarInitial'       => mb_substr($profileName, 0, 1),
        'avatarUrl'           => $this->getAvatarUrl($sessionUser['image'] ?? null),
        'customerSinceYear'   => date('Y', strtotime($data['user']['created_at'] ?? 'now')),
        'totalOrdersCount'    => count($data['orders']),
        'totalSavedMoney'     => count($data['orders']) * 2.50,
        'loyaltyPoints'       => (int)($data['user']['loyalty_points'] ?? 0),
        'profilePhone'        => trim((string)($data['user']['telephone'] ?? '')),
        // Inicializácia error 
        'profileNotice'       => (string) ($_SESSION['profileNotice'] ?? ''),
        'uniqueResetCode'     => $uniqueResetCode,
        'profileError'        => '',
        'profileOrdersError'  => '',
        'profileMessagesError'=> '',
        'sessionUser'         => $sessionUser 
    ];
}
}