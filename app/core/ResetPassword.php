<?php
declare(strict_types=1);

class ResetPassword
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    //Skontroluje, či zadaný e-mail a 12-miestny kód sedia na záznam v databáze.
    public function verifyRecoveryCode(string $email, string $code): bool
    {
        if ($email === '' || $code === '') {
            return false;
        }

        // Hľadáme používateľa, ktorý má v stĺpci unique_reset_passwd
        $stmt = $this->db->prepare('SELECT id FROM user WHERE email = :email AND unique_reset_passwd = :code');
        $stmt->execute([
            ':email' => $email,
            ':code'  => $code
        ]);

        // Ak vráti riadok, údaje sú správne
        return $stmt->fetch() !== false;
    }

    //Zmení používateľovi heslo pomocou bezpečného BCryptu.

    public function resetPassword(string $email, string $password): bool
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $this->db->prepare('UPDATE user SET password = ? WHERE email = ?');
        return $stmt->execute([$hashedPassword, $email]);
    }
}