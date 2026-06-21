<?php
namespace App\Models;
use App\Core\Database;

class Appointment
{
    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM appointments WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public static function getAllByParent(int $parentId): array
    {
        $stmt = Database::getInstance()->prepare('
            SELECT a.*, c.name AS child_name, u.name AS specialist_name
            FROM appointments a
            JOIN children c ON a.child_id = c.id
            JOIN users u ON a.specialist_id = u.id
            WHERE a.parent_id = ?
            ORDER BY a.date DESC, a.time DESC
        ');
        $stmt->execute([$parentId]);
        return $stmt->fetchAll();
    }
    public static function getAllBySpecialist(int $specialistId): array
    {
        $stmt = Database::getInstance()->prepare('
            SELECT a.*, c.name AS child_name, u.name AS parent_name
            FROM appointments a
            JOIN children c ON a.child_id = c.id
            JOIN users u ON a.parent_id = u.id
            WHERE a.specialist_id = ?
            ORDER BY a.date DESC, a.time DESC
        ');
        $stmt->execute([$specialistId]);
        return $stmt->fetchAll();
    }
    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO appointments (child_id, specialist_id, parent_id, date, time, duration, notes) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['child_id'], $data['specialist_id'], $data['parent_id'], $data['date'], $data['time'], $data['duration'] ?? 30, $data['notes'] ?? null]);
        return (int) $db->lastInsertId();
    }
    public static function updateStatus(int $id, string $status): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE appointments SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public static function update(int $id, array $data): void
    {
        $fields = [];
        $params = [];
        foreach (['date', 'time', 'duration', 'notes', 'status'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "$col = ?";
                $params[] = $data[$col];
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        $stmt = Database::getInstance()->prepare('UPDATE appointments SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM appointments WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function countByParent(int $parentId, ?string $status = null): int
    {
        $sql = 'SELECT COUNT(*) FROM appointments WHERE parent_id = ?';
        $params = [$parentId];
        if ($status) {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
    public static function countBySpecialist(int $specialistId, ?string $status = null): int
    {
        $sql = 'SELECT COUNT(*) FROM appointments WHERE specialist_id = ?';
        $params = [$specialistId];
        if ($status) {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
    public static function getUpcomingByParent(int $parentId, int $limit = 5): array
    {
        $stmt = Database::getInstance()->prepare('
            SELECT a.*, c.name AS child_name, u.name AS specialist_name
            FROM appointments a
            JOIN children c ON a.child_id = c.id
            JOIN users u ON a.specialist_id = u.id
            WHERE a.parent_id = ? AND a.date >= CURDATE() AND a.status IN ("pending","confirmed")
            ORDER BY a.date ASC, a.time ASC
            LIMIT ?
        ');
        $stmt->execute([$parentId, $limit]);
        return $stmt->fetchAll();
    }
    public static function getUpcomingBySpecialist(int $specialistId, int $limit = 10): array
    {
        $stmt = Database::getInstance()->prepare('
            SELECT a.*, c.name AS child_name, u.name AS parent_name
            FROM appointments a
            JOIN children c ON a.child_id = c.id
            JOIN users u ON a.parent_id = u.id
            WHERE a.specialist_id = ? AND a.date >= CURDATE() AND a.status = "confirmed"
            ORDER BY a.date ASC, a.time ASC
            LIMIT ?
        ');
        $stmt->execute([$specialistId, $limit]);
        return $stmt->fetchAll();
    }
    public static function getAll(): array
    {
        $stmt = Database::getInstance()->query('
            SELECT a.*, c.name AS child_name, u.name AS parent_name, s.name AS specialist_name
            FROM appointments a
            JOIN children c ON a.child_id = c.id
            JOIN users u ON a.parent_id = u.id
            JOIN users s ON a.specialist_id = s.id
            ORDER BY a.date DESC, a.time DESC
        ');
        return $stmt ? $stmt->fetchAll() : [];
    }
}
