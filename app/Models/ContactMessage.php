<?php
namespace App\Models;
use App\Core\Database;

class ContactMessage
{
    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM contact_messages WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getAll(): array
    {
        $stmt = Database::getInstance()->query('SELECT * FROM contact_messages ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['name'], $data['email'], $data['subject'], $data['message']]);
        return (int) $db->lastInsertId();
    }

    public static function markAsRead(int $id): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM contact_messages WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function countUnread(): int
    {
        $stmt = Database::getInstance()->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0');
        return (int) $stmt->fetchColumn();
    }
}
