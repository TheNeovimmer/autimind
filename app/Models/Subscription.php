<?php
namespace App\Models;
use App\Core\Database;

class Subscription
{
    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT s.*, u.name AS user_name, u.email AS user_email FROM subscriptions s JOIN users u ON s.user_id = u.id WHERE s.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getByUser(int $userId): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM subscriptions WHERE user_id = ? AND status = "active" ORDER BY started_at DESC LIMIT 1');
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    public static function getAll(): array
    {
        $stmt = Database::getInstance()->query('
            SELECT s.*, u.name AS user_name, u.email AS user_email
            FROM subscriptions s
            JOIN users u ON s.user_id = u.id
            ORDER BY s.started_at DESC
        ');
        return $stmt ? $stmt->fetchAll() : [];
    }
    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO subscriptions (user_id, plan, status, ends_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['user_id'], $data['plan'], $data['status'] ?? 'active', $data['ends_at'] ?? null]);
        return (int) $db->lastInsertId();
    }
    public static function update(int $id, array $data): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE subscriptions SET plan = ?, status = ?, ends_at = ? WHERE id = ?');
        $stmt->execute([$data['plan'], $data['status'], $data['ends_at'] ?? null, $id]);
    }
    public static function count(): int
    {
        $stmt = Database::getInstance()->query('SELECT COUNT(*) FROM subscriptions');
        return (int) $stmt->fetchColumn();
    }
    public static function countActive(): int
    {
        $stmt = Database::getInstance()->query('SELECT COUNT(*) FROM subscriptions WHERE status = "active"');
        return (int) $stmt->fetchColumn();
    }
    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM subscriptions WHERE id = ?');
        $stmt->execute([$id]);
    }
}
