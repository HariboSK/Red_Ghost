<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class ContactMessageModel extends BaseModel
{
    public function markAsRead(int $messageId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE contact_messages SET status = :new_status WHERE id_contact_msg = :id AND status = :old_status');

        return $stmt->execute([
            ':new_status' => 'read',
            ':id' => $messageId,
            ':old_status' => 'new',
        ]);
    }

    public function reply(int $messageId, string $replyText): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                'INSERT INTO contact_replies (sender_type, message_text, id_message)
                 VALUES (:sender_type, :message_text, :id_message)'
            );
            $stmt->execute([
                ':sender_type' => 'admin',
                ':message_text' => $replyText,
                ':id_message' => $messageId,
            ]);

            $stmt = $this->pdo->prepare(
                'UPDATE contact_messages
                 SET status = :status
                 WHERE id_contact_msg = :id'
            );
            $stmt->execute([
                ':status' => 'replied',
                ':id' => $messageId,
            ]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    public function addUserReply(int $messageId, string $email, string $messageText): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                'SELECT id_contact_msg
                 FROM contact_messages
                 WHERE id_contact_msg = :id AND sender_email = :email
                 LIMIT 1'
            );
            $stmt->execute([
                ':id' => $messageId,
                ':email' => $email,
            ]);

            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->pdo->rollBack();
                return false;
            }

            $stmt = $this->pdo->prepare(
                'INSERT INTO contact_replies (sender_type, message_text, id_message)
                 VALUES (:sender_type, :message_text, :id_message)'
            );
            $stmt->execute([
                ':sender_type' => 'user',
                ':message_text' => $messageText,
                ':id_message' => $messageId,
            ]);

            $stmt = $this->pdo->prepare(
                'UPDATE contact_messages
                 SET status = :status
                 WHERE id_contact_msg = :id'
            );
            $stmt->execute([
                ':status' => 'new',
                ':id' => $messageId,
            ]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    public function delete(int $messageId): bool
    {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare('DELETE FROM contact_replies WHERE id_message = :id');
            $stmt->execute([':id' => $messageId]);

            $stmt = $this->pdo->prepare('DELETE FROM contact_messages WHERE id_contact_msg = :id');
            $stmt->execute([':id' => $messageId]);
            $this->pdo->commit();
            return true;
        }
        
        catch (PDOException $e) {
            if($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
            
        }
    }

    public function deleteAll(): bool
    {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare('DELETE FROM contact_replies');
            $stmt->execute();

            $stmt = $this->pdo->prepare('DELETE FROM contact_messages');
            $stmt->execute();
            $this->pdo->commit();
            return true;
        }

        catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    public function getUnread(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT m.id_contact_msg AS id,
                    m.sender_name,
                    m.sender_email,
                    m.subject,
                    (SELECT r.message_text
                     FROM contact_replies r
                     WHERE r.id_message = m.id_contact_msg AND r.sender_type = \'user\'
                     ORDER BY r.created_at ASC
                     LIMIT 1) AS message_text,
                    (SELECT r.message_text
                     FROM contact_replies r
                     WHERE r.id_message = m.id_contact_msg AND r.sender_type = \'admin\'
                     ORDER BY r.created_at DESC
                     LIMIT 1) AS reply_text,
                    (SELECT r.created_at
                     FROM contact_replies r
                     WHERE r.id_message = m.id_contact_msg AND r.sender_type = \'admin\'
                     ORDER BY r.created_at DESC
                     LIMIT 1) AS reply_at,
                    m.created_at
             FROM contact_messages m
             WHERE m.status = :status
             ORDER BY m.created_at DESC'
        );
        $stmt->execute([':status' => 'new']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT m.id_contact_msg AS id,
                    m.sender_name,
                    m.sender_email,
                    m.subject,
                    (SELECT r.message_text
                     FROM contact_replies r
                     WHERE r.id_message = m.id_contact_msg AND r.sender_type = \'user\'
                     ORDER BY r.created_at ASC
                     LIMIT 1) AS message_text,
                    (SELECT r.message_text
                     FROM contact_replies r
                     WHERE r.id_message = m.id_contact_msg AND r.sender_type = \'admin\'
                     ORDER BY r.created_at DESC
                     LIMIT 1) AS reply_text,
                    (SELECT r.created_at
                     FROM contact_replies r
                     WHERE r.id_message = m.id_contact_msg AND r.sender_type = \'admin\'
                     ORDER BY r.created_at DESC
                     LIMIT 1) AS reply_at,
                    m.status,
                    m.created_at
             FROM contact_messages m
             ORDER BY m.created_at DESC'
        );
        $rows = $stmt instanceof PDOStatement ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        return is_array($rows) ? $rows : [];
    }

    public function getByEmail(string $email): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT m.id_contact_msg AS id,
                    m.sender_name,
                    m.sender_email,
                    m.subject,
                    (SELECT r.message_text
                     FROM contact_replies r
                     WHERE r.id_message = m.id_contact_msg AND r.sender_type = \'user\'
                     ORDER BY r.created_at ASC
                     LIMIT 1) AS message_text,
                    (SELECT r.message_text
                     FROM contact_replies r
                     WHERE r.id_message = m.id_contact_msg AND r.sender_type = \'admin\'
                     ORDER BY r.created_at DESC
                     LIMIT 1) AS reply_text,
                    (SELECT r.created_at
                     FROM contact_replies r
                     WHERE r.id_message = m.id_contact_msg AND r.sender_type = \'admin\'
                     ORDER BY r.created_at DESC
                     LIMIT 1) AS reply_at,
                    m.status,
                    m.created_at
             FROM contact_messages m
             WHERE m.sender_email = :sender_email
             ORDER BY m.created_at DESC
             LIMIT 20'
        );
        $stmt->execute([':sender_email' => $email]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    // Validácia polí kontaktného formulára pred uložením
    public function validate(array $data): array
    {
        $errors = [];
        $name = trim((string) ($data['name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $subject = trim((string) ($data['subject'] ?? ''));
        $message = trim((string) ($data['message'] ?? ''));

        $nameLength = function_exists('mb_strlen') ? mb_strlen($name) : strlen($name);
        $subjectLength = function_exists('mb_strlen') ? mb_strlen($subject) : strlen($subject);
        $messageLength = function_exists('mb_strlen') ? mb_strlen($message) : strlen($message);

        if ($name === '' || $nameLength < 2 || $nameLength > 100) {
            $errors[] = 'Meno musí mať aspoň 2 znaky a najviac 100 znakov.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Zadajte platnú emailovú adresu.';
        }

        if ($subject === '' || $subjectLength < 3 || $subjectLength > 150) {
            $errors[] = 'Predmet musí mať aspoň 3 znaky a najviac 150 znakov.';
        }

        if ($message === '' || $messageLength < 10 || $messageLength > 5000) {
            $errors[] = 'Správa musí mať aspoň 10 znakov a najviac 5000 znakov.';
        }

        return $errors;
    }

    // Založenie nového vlákna správy a vloženie prvého textu od používateľa
    public function createNewMessage(string $name, string $email, string $subject, string $messageText): bool
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Vytvorenie hlavného záznamu správy
            $stmt = $this->pdo->prepare(
                'INSERT INTO contact_messages (sender_name, sender_email, subject, status)
                 VALUES (:sender_name, :sender_email, :subject, :status)'
            );
            $stmt->execute([
                ':sender_name'  => $name,
                ':sender_email' => $email,
                ':subject'      => $subject,
                ':status'       => 'new',
            ]);

            // Získanie ID vygenerovanej správy (používa sa správny stĺpec id_contact_msg)
            $messageId = (int) $this->pdo->lastInsertId();

            // 2. Vloženie textu správy do previazanej tabuľky replies
            $stmt = $this->pdo->prepare(
                'INSERT INTO contact_replies (sender_type, message_text, id_message)
                 VALUES (:sender_type, :message_text, :id_message)'
            );
            $stmt->execute([
                ':sender_type'  => 'user',
                ':message_text' => $messageText,
                ':id_message'   => $messageId,
            ]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }
}
