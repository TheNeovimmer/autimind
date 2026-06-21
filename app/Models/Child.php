<?php
namespace App\Models;
use App\Core\Database;

class Child
{
    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM children WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public static function getAllByParent(int $parentId): array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM children WHERE parent_id = ? ORDER BY name');
        $stmt->execute([$parentId]);
        return $stmt->fetchAll();
    }
    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO children (parent_id, name, age, birth_date, diagnosis_status, notes) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['parent_id'], $data['name'], $data['age'] ?? null, $data['birth_date'] ?? null, $data['diagnosis_status'] ?? null, $data['notes'] ?? null]);
        return (int) $db->lastInsertId();
    }
    public static function update(int $id, array $data): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE children SET name = ?, age = ?, birth_date = ?, diagnosis_status = ?, notes = ? WHERE id = ?');
        $stmt->execute([$data['name'], $data['age'] ?? null, $data['birth_date'] ?? null, $data['diagnosis_status'] ?? null, $data['notes'] ?? null, $id]);
    }
    public static function updateAvatar(int $id, string $avatar): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE children SET avatar = ? WHERE id = ?');
        $stmt->execute([$avatar, $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM children WHERE id = ?');
        $stmt->execute([$id]);
    }
    public static function belongsToParent(int $childId, int $parentId): bool
    {
        $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM children WHERE id = ? AND parent_id = ?');
        $stmt->execute([$childId, $parentId]);
        return (int) $stmt->fetchColumn() > 0;
    }
    public static function countByParent(int $parentId): int
    {
        $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM children WHERE parent_id = ?');
        $stmt->execute([$parentId]);
        return (int) $stmt->fetchColumn();
    }
}
