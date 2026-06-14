<?php
declare(strict_types=1);

class ResetPassword
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    //Vygeneruje token pre existujúci email. Ak email neexistuje, vráti null.
    public function generateTokenForEmail(string $email): ?string
    {
        // 1. Overenie, či používateľ existuje
        $stmt = $this->db->prepare("SELECT id FROM user WHERE email = ?");
        $stmt->execute([$email]);
        if (!$stmt->fetch()) {
            return null; // Používateľ neexistuje
        }

        // 2. Vygenerovanie unikátneho tokenu
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $expiresAt = date("Y-m-d H:i:s", time() + 3600); // Platnosť 1 hodina

        // 3. Zmazanie starých tokenov
        $this->deleteToken($email);

        // 4. Zápis nového tokenu
        $stmt = $this->db->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)');
        $stmt->execute([
            ':email'      => $email,
            ':token'      => $hashedToken,
            ':expires_at' => $expiresAt
        ]);

        return $token;
    }

    public function verifyToken(string $token): ?string
    {
        $hashedToken = hash('sha256', $token);
        $stmt = $this->db->prepare('SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()');
        $stmt->execute([$hashedToken]);
        $result = $stmt->fetch();

        return $result ? $result['email'] : null;
    }

    public function resetPassword(string $email, string $password): bool
    {
        // Tu použi svoje hashovanie hesiel (napr. PASSWORD_BCRYPT)
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $this->db->prepare('UPDATE user SET password = ? WHERE email = ?');
        return $stmt->execute([$hashedPassword, $email]);
    }

    public function deleteToken(string $email): void
    {
        $stmt = $this->db->prepare('DELETE FROM password_resets WHERE email = ?');
        $stmt->execute([$email]);
    }
}