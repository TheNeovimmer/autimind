<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    public static function findByEmail(string $email): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $cols = ['role', 'name', 'email', 'password'];
        $vals = [$data['role'], $data['name'], $data['email'], $data['password']];
        foreach (['avatar', 'phone', 'is_active'] as $extra) {
            if (array_key_exists($extra, $data)) {
                $cols[] = $extra;
                $vals[] = $data[$extra];
            }
        }
        $placeholders = implode(', ', array_fill(0, count($cols), '?'));
        $stmt = $db->prepare('INSERT INTO users (' . implode(', ', $cols) . ') VALUES (' . $placeholders . ')');
        $stmt->execute($vals);
        return (int) $db->lastInsertId();
    }

    public static function updatePassword(int $id, string $password): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([$password, $id]);
    }

    public static function updatePasswordByEmail(string $email, string $password): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE users SET password = ? WHERE email = ?');
        $stmt->execute([$password, $email]);
    }

    public static function getAllByRole(string $role): array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM users WHERE role = ? AND is_active = 1 ORDER BY name');
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    public static function countByRole(string $role): int
    {
        $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM users WHERE role = ?');
        $stmt->execute([$role]);
        return (int) $stmt->fetchColumn();
    }

    public static function getAll(): array
    {
        $stmt = Database::getInstance()->query('SELECT * FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public static function update(int $id, array $data): void
    {
        $allowed = ['name', 'email', 'role', 'is_active', 'avatar', 'phone'];
        $fields = [];
        $params = [];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "$col = ?";
                $params[] = $data[$col];
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        $stmt = Database::getInstance()->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
    }
}
