<?php
namespace App\Models;
use App\Core\Database;

class Activity
{
    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM activities WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public static function getAllActive(): array
    {
        $stmt = Database::getInstance()->query('SELECT * FROM activities WHERE is_active = 1 ORDER BY category, title');
        return $stmt ? $stmt->fetchAll() : [];
    }
    public static function getAll(): array
    {
        $stmt = Database::getInstance()->query('SELECT * FROM activities ORDER BY category, title');
        return $stmt ? $stmt->fetchAll() : [];
    }
    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO activities (title, description, category, difficulty, image_url) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$data['title'], $data['description'] ?? null, $data['category'], $data['difficulty'], $data['image_url'] ?? null]);
        return (int) $db->lastInsertId();
    }
    public static function update(int $id, array $data): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE activities SET title = ?, description = ?, category = ?, difficulty = ?, image_url = ?, is_active = ? WHERE id = ?');
        $stmt->execute([$data['title'], $data['description'] ?? null, $data['category'], $data['difficulty'], $data['image_url'] ?? null, $data['is_active'] ?? 1, $id]);
    }
    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM activities WHERE id = ?');
        $stmt->execute([$id]);
    }
    public static function count(): int
    {
        $stmt = Database::getInstance()->query('SELECT COUNT(*) FROM activities');
        return (int) $stmt->fetchColumn();
    }
}
