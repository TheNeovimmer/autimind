<?php
namespace App\Models;
use App\Core\Database;

class SpecialistDetail
{
    public static function findByUserId(int $userId): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM specialist_details WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT sd.*, u.name, u.email, u.is_active, u.avatar FROM specialist_details sd JOIN users u ON sd.user_id = u.id WHERE sd.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create(int $userId, array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO specialist_details (user_id, title, bio, specializations, years_experience, is_available) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $data['title'] ?? '', $data['bio'] ?? null, $data['specializations'] ?? null, $data['years_experience'] ?? null, $data['is_available'] ?? 1]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $userId, array $data): void
    {
        $fields = [];
        $params = [];
        foreach (['title', 'bio', 'specializations', 'years_experience', 'is_available'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "$col = ?";
                $params[] = $data[$col];
            }
        }
        if (empty($fields)) return;
        $params[] = $userId;
        $stmt = Database::getInstance()->prepare('UPDATE specialist_details SET ' . implode(', ', $fields) . ' WHERE user_id = ?');
        $stmt->execute($params);
    }

    public static function getAllActive(): array
    {
        $stmt = Database::getInstance()->query('SELECT sd.*, u.name, u.email, u.avatar FROM specialist_details sd JOIN users u ON sd.user_id = u.id WHERE u.is_active = 1 AND u.role = "specialist" ORDER BY u.name');
        return $stmt->fetchAll();
    }

    public static function getAll(): array
    {
        $stmt = Database::getInstance()->query('SELECT sd.*, u.name, u.email, u.is_active, u.avatar FROM specialist_details sd JOIN users u ON sd.user_id = u.id WHERE u.role = "specialist" ORDER BY u.name');
        return $stmt->fetchAll();
    }
}
