<?php
namespace App\Models;
use App\Core\Database;

class Message
{
    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM messages WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public static function getInbox(int $userId): array
    {
        $stmt = Database::getInstance()->prepare('
            SELECT m.*, u.name AS sender_name
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.receiver_id = ?
            ORDER BY m.created_at DESC
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    public static function getSent(int $userId): array
    {
        $stmt = Database::getInstance()->prepare('
            SELECT m.*, u.name AS receiver_name
            FROM messages m
            JOIN users u ON m.receiver_id = u.id
            WHERE m.sender_id = ?
            ORDER BY m.created_at DESC
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    public static function getThread(int $userId1, int $userId2): array
    {
        $stmt = Database::getInstance()->prepare('
            SELECT m.*, u1.name AS sender_name, u2.name AS receiver_name
            FROM messages m
            JOIN users u1 ON m.sender_id = u1.id
            JOIN users u2 ON m.receiver_id = u2.id
            WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at ASC
        ');
        $stmt->execute([$userId1, $userId2, $userId2, $userId1]);
        return $stmt->fetchAll();
    }
    public static function send(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO messages (sender_id, receiver_id, subject, body) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['sender_id'], $data['receiver_id'], $data['subject'], $data['body']]);
        return (int) $db->lastInsertId();
    }
    public static function markAsRead(int $id): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE messages SET is_read = 1 WHERE id = ?');
        $stmt->execute([$id]);
    }
    public static function countUnread(int $userId): int
    {
        $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }
    public static function getConversationPartners(int $userId): array
    {
        $stmt = Database::getInstance()->prepare('
            SELECT DISTINCT u.id, u.name, u.role
            FROM messages m
            JOIN users u ON (CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END) = u.id
            WHERE m.sender_id = ? OR m.receiver_id = ?
        ');
        $stmt->execute([$userId, $userId, $userId]);
        return $stmt->fetchAll();
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM messages WHERE id = ?');
        $stmt->execute([$id]);
    }
}
