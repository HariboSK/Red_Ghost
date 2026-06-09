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
}
