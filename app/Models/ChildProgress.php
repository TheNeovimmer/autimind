<?php
namespace App\Models;
use App\Core\Database;

class ChildProgress
{
    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM child_progress WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getByChild(int $childId): array
    {
        $stmt = Database::getInstance()->prepare('SELECT cp.*, a.title, a.category, a.difficulty FROM child_progress cp JOIN activities a ON cp.activity_id = a.id WHERE cp.child_id = ? ORDER BY cp.completed_at DESC');
        $stmt->execute([$childId]);
        return $stmt->fetchAll();
    }

    public static function getRecentByChild(int $childId, int $limit = 10): array
    {
        $stmt = Database::getInstance()->prepare('SELECT cp.*, a.title, a.category, a.difficulty FROM child_progress cp JOIN activities a ON cp.activity_id = a.id WHERE cp.child_id = ? ORDER BY cp.completed_at DESC LIMIT ?');
        $stmt->execute([$childId, $limit]);
        return $stmt->fetchAll();
    }

    public static function countByChild(int $childId): int
    {
        $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM child_progress WHERE child_id = ?');
        $stmt->execute([$childId]);
        return (int) $stmt->fetchColumn();
    }

    public static function averageScoreByChild(int $childId): ?float
    {
        $stmt = Database::getInstance()->prepare('SELECT AVG(score) FROM child_progress WHERE child_id = ? AND score IS NOT NULL');
        $stmt->execute([$childId]);
        $val = $stmt->fetchColumn();
        return $val !== false && $val !== null ? (float) $val : null;
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO child_progress (child_id, activity_id, score, time_spent_seconds) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['child_id'], $data['activity_id'], $data['score'] ?? null, $data['time_spent_seconds'] ?? null]);
        return (int) $db->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM child_progress WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function getAllByActivity(int $activityId): array
    {
        $stmt = Database::getInstance()->prepare('SELECT cp.*, c.name AS child_name, u.name AS parent_name FROM child_progress cp JOIN children c ON cp.child_id = c.id JOIN users u ON c.parent_id = u.id WHERE cp.activity_id = ? ORDER BY cp.completed_at DESC');
        $stmt->execute([$activityId]);
        return $stmt->fetchAll();
    }
}
