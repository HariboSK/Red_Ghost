<?php
if (session_status() === PHP_SESSION_NONE) session_start();

class ResetPassword
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function verifyToken(string $token): ?string
    {
        if (empty($token)) return null;

        $hashedToken = hash('sha256', $token);
        $stmt = $this->pdo->prepare("SELECT email 
                                    FROM password_resets 
                                    WHERE token = :token 
                                    AND expires_at > NOW()");
        $stmt->execute([':token' => $hashedToken]);
        $resetData = $stmt->fetch();

        return $resetData ? $resetData['email'] : null;
    }

    public function resetPassword(string $email, string $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $update = $this->pdo->prepare("UPDATE user 
                                        SET password = :password 
                                        WHERE email = :email");
        return $update->execute([
            ':password' => $hashedPassword,
            ':email' => $email
        ]);
    }

    public function deleteToken(string $email): void
    {
        $this->pdo->prepare("DELETE FROM password_resets 
                            WHERE email = ?")->execute([$email]);
    }   
}