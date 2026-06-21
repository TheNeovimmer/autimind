<?php
namespace App\Core;

use PDOException;

class Migration
{
    public static function run(): void
    {
        try {
            $db = Database::getInstance();
            $sql = file_get_contents(__DIR__ . '/../../migrations/001_create_tables.sql');
            $statements = preg_split('/;\s*\n/', $sql);
            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if ($stmt) {
                    $db->exec($stmt);
                }
            }
            echo "Schema migration ran successfully.\n";
        } catch (PDOException $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    public static function runSeed(): void
    {
        try {
            $db = Database::getInstance();
            $sql = file_get_contents(__DIR__ . '/../../migrations/002_seed_data.sql');
            $statements = preg_split('/;\s*\n/', $sql);
            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if ($stmt) {
                    $db->exec($stmt);
                }
            }
            echo "Seed data inserted successfully.\n";
        } catch (PDOException $e) {
            echo "Seed failed: " . $e->getMessage() . "\n";
            exit(1);
        }
    }


}
