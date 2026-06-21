<?php
namespace App\Models;
use App\Core\Database;

class QuizQuestion
{
    public static function getAllActive(): array
    {
        $stmt = Database::getInstance()->prepare('
            SELECT q.*, GROUP_CONCAT(
                JSON_OBJECT("id", o.id, "option_text", o.option_text, "weight", o.weight, "order_index", o.order_index)
                ORDER BY o.order_index ASC SEPARATOR "|||"
            ) AS options_json
            FROM quiz_questions q
            LEFT JOIN quiz_options o ON q.id = o.question_id
            WHERE q.is_active = 1
            GROUP BY q.id
            ORDER BY q.order_index
        ');
        $stmt->execute();
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $options = [];
            foreach (explode('|||', $row['options_json']) as $json) {
                $opt = json_decode($json, true);
                if ($opt) $options[] = $opt;
            }
            $row['options'] = $options;
            unset($row['options_json']);
        }
        return $rows;
    }
    public static function getAll(): array
    {
        $stmt = Database::getInstance()->query('
            SELECT q.*,
                (SELECT COUNT(*) FROM quiz_options WHERE question_id = q.id) AS option_count
            FROM quiz_questions q ORDER BY q.order_index
        ');
        return $stmt ? $stmt->fetchAll() : [];
    }
    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM quiz_questions WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO quiz_questions (question_text, category, order_index) VALUES (?, ?, ?)');
        $stmt->execute([$data['question_text'], $data['category'], $data['order_index']]);
        return (int) $db->lastInsertId();
    }
    public static function update(int $id, array $data): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE quiz_questions SET question_text = ?, category = ?, order_index = ?, is_active = ? WHERE id = ?');
        $stmt->execute([$data['question_text'], $data['category'], $data['order_index'], $data['is_active'] ?? 1, $id]);
    }
    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM quiz_questions WHERE id = ?');
        $stmt->execute([$id]);
    }
}
