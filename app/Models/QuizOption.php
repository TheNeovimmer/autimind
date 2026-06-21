<?php
namespace App\Models;
use App\Core\Database;

class QuizOption
{
    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM quiz_options WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getByQuestion(int $questionId): array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM quiz_options WHERE question_id = ? ORDER BY order_index');
        $stmt->execute([$questionId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO quiz_options (question_id, option_text, weight, order_index) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['question_id'], $data['option_text'], $data['weight'] ?? 0, $data['order_index'] ?? 0]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $fields = [];
        $params = [];
        foreach (['option_text', 'weight', 'order_index'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "$col = ?";
                $params[] = $data[$col];
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        $stmt = Database::getInstance()->prepare('UPDATE quiz_options SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM quiz_options WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function deleteByQuestion(int $questionId): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM quiz_options WHERE question_id = ?');
        $stmt->execute([$questionId]);
    }

    public static function countByQuestion(int $questionId): int
    {
        $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM quiz_options WHERE question_id = ?');
        $stmt->execute([$questionId]);
        return (int) $stmt->fetchColumn();
    }
}
