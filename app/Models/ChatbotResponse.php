<?php
namespace App\Models;
use App\Core\Database;

class ChatbotResponse
{
    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM chatbot_responses WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getAll(): array
    {
        $stmt = Database::getInstance()->query('SELECT * FROM chatbot_responses ORDER BY category, id');
        return $stmt->fetchAll();
    }

    public static function getAllActive(): array
    {
        $stmt = Database::getInstance()->query('SELECT * FROM chatbot_responses WHERE is_active = 1 ORDER BY category, id');
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO chatbot_responses (keywords, response_text, category) VALUES (?, ?, ?)');
        $stmt->execute([$data['keywords'], $data['response_text'], $data['category'] ?? null]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $fields = [];
        $params = [];
        foreach (['keywords', 'response_text', 'category', 'is_active'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "$col = ?";
                $params[] = $data[$col];
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        $stmt = Database::getInstance()->prepare('UPDATE chatbot_responses SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM chatbot_responses WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function findByKeyword(string $keyword): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM chatbot_responses WHERE keywords LIKE ? AND is_active = 1 LIMIT 1');
        $stmt->execute(['%' . $keyword . '%']);
        return $stmt->fetch();
    }
}
