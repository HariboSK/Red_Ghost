<?php

require_once __DIR__ . '/base.model.php';

class ContactMessageModel extends BaseModel
{
    public function markAsRead(int $messageId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE contact_messages SET status = :new_status WHERE id = :id AND status = :old_status');

        return $stmt->execute([
            ':new_status' => 'read',
            ':id' => $messageId,
            ':old_status' => 'unread',
        ]);
    }

    public function reply(int $messageId, string $replyText): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE contact_messages
             SET reply_text = :reply_text, reply_at = NOW(), status = :status
             WHERE id = :id'
        );

        return $stmt->execute([
            ':reply_text' => $replyText,
            ':status' => 'replied',
            ':id' => $messageId,
        ]);
    }

    public function getUnread(): array
    {
        $stmt = $this->pdo->prepare('SELECT id, sender_name, sender_email, subject, message_text, reply_text, reply_at, created_at FROM contact_messages WHERE status = :status ORDER BY created_at DESC');
        $stmt->execute([':status' => 'unread']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT id, sender_name, sender_email, subject, message_text, reply_text, reply_at, status, created_at FROM contact_messages ORDER BY created_at DESC');
        $rows = $stmt instanceof PDOStatement ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        return is_array($rows) ? $rows : [];
    }

    public function getByEmail(string $email): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, sender_name, sender_email, subject, message_text, reply_text, reply_at, status, created_at
             FROM contact_messages
             WHERE sender_email = :sender_email
             ORDER BY created_at DESC
             LIMIT 20'
        );
        $stmt->execute([':sender_email' => $email]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }
}
