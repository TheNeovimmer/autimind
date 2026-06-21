<?php
namespace App\Models;
use App\Core\Database;

class QuizAttempt
{
    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM quiz_attempts WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public static function create(int $childId): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO quiz_attempts (child_id) VALUES (?)');
        $stmt->execute([$childId]);
        return (int) $db->lastInsertId();
    }
    public static function complete(int $id, int $totalScore, string $riskLevel): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE quiz_attempts SET total_score = ?, risk_level = ?, status = "completed", completed_at = NOW() WHERE id = ?');
        $stmt->execute([$totalScore, $riskLevel, $id]);
    }
    public static function getByChild(int $childId): array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM quiz_attempts WHERE child_id = ? AND status = "completed" ORDER BY completed_at DESC');
        $stmt->execute([$childId]);
        return $stmt->fetchAll();
    }
    public static function getLatestByChild(int $childId): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM quiz_attempts WHERE child_id = ? AND status = "completed" ORDER BY completed_at DESC LIMIT 1');
        $stmt->execute([$childId]);
        return $stmt->fetch();
    }
    public static function getIncomplete(int $childId): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM quiz_attempts WHERE child_id = ? AND status = "in_progress" ORDER BY started_at DESC LIMIT 1');
        $stmt->execute([$childId]);
        return $stmt->fetch();
    }
    public static function countByChild(int $childId): int
    {
        $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM quiz_attempts WHERE child_id = ? AND status = "completed"');
        $stmt->execute([$childId]);
        return (int) $stmt->fetchColumn();
    }
    public static function getAll(): array
    {
        $stmt = Database::getInstance()->query('
            SELECT qa.*, c.name AS child_name, u.name AS parent_name
            FROM quiz_attempts qa
            JOIN children c ON qa.child_id = c.id
            JOIN users u ON c.parent_id = u.id
            ORDER BY qa.started_at DESC
        ');
        return $stmt ? $stmt->fetchAll() : [];
    }
    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM quiz_attempts WHERE id = ?');
        $stmt->execute([$id]);
    }
}
