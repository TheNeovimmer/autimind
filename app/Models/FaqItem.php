<?php
namespace App\Models;
use App\Core\Database;

class FaqItem
{
    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM faq_items WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getAllActive(): array
    {
        $stmt = Database::getInstance()->query('SELECT * FROM faq_items WHERE is_active = 1 ORDER BY category, order_index');
        return $stmt->fetchAll();
    }

    public static function getAll(): array
    {
        $stmt = Database::getInstance()->query('SELECT * FROM faq_items ORDER BY category, order_index');
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO faq_items (question, answer, category, order_index) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['question'], $data['answer'], $data['category'], $data['order_index'] ?? 0]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $fields = [];
        $params = [];
        foreach (['question', 'answer', 'category', 'order_index', 'is_active'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "$col = ?";
                $params[] = $data[$col];
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        $stmt = Database::getInstance()->prepare('UPDATE faq_items SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM faq_items WHERE id = ?');
        $stmt->execute([$id]);
    }
}
