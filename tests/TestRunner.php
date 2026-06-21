<?php
namespace Tests;

use App\Core\Database;
use PDO;

abstract class TestRunner
{
    protected static PDO $db;
    private static array $fixtures = [];
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    public static function setUpBeforeClass(): void {}

    public static function tearDownAfterClass(): void {}

    protected function setUp(): void {}

    protected function tearDown(): void {}

    protected function assertTrue(bool $condition, string $message = ''): void
    {
        if (!$condition) {
            throw new TestFailureException($message ?: 'Expected true, got false');
        }
    }

    protected function assertFalse(bool $condition, string $message = ''): void
    {
        if ($condition) {
            throw new TestFailureException($message ?: 'Expected false, got true');
        }
    }

    protected function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            $msg = $message ?: "Expected " . $this->export($expected) . " but got " . $this->export($actual);
            throw new TestFailureException($msg);
        }
    }

    protected function assertNotEquals(mixed $expected, mixed $actual, string $message = ''): void
    {
        if ($expected === $actual) {
            $msg = $message ?: "Expected values to differ but both are " . $this->export($expected);
            throw new TestFailureException($msg);
        }
    }

    protected function assertNull(mixed $value, string $message = ''): void
    {
        if ($value !== null) {
            throw new TestFailureException($message ?: "Expected null, got " . $this->export($value));
        }
    }

    protected function assertNotNull(mixed $value, string $message = ''): void
    {
        if ($value === null) {
            throw new TestFailureException($message ?: "Expected non-null value");
        }
    }

    protected function assertEmpty(mixed $value, string $message = ''): void
    {
        if (!empty($value)) {
            throw new TestFailureException($message ?: "Expected empty value");
        }
    }

    protected function assertNotEmpty(mixed $value, string $message = ''): void
    {
        if (empty($value)) {
            throw new TestFailureException($message ?: "Expected non-empty value");
        }
    }

    protected function assertContains(mixed $needle, array $haystack, string $message = ''): void
    {
        if (!in_array($needle, $haystack, true)) {
            throw new TestFailureException($message ?: "Expected " . $this->export($needle) . " to be in array");
        }
    }

    protected function assertArrayHasKey(string|int $key, array $array, string $message = ''): void
    {
        if (!array_key_exists($key, $array)) {
            throw new TestFailureException($message ?: "Expected key " . $this->export($key) . " not found in array");
        }
    }

    protected function assertMatchesRegex(string $pattern, string $string, string $message = ''): void
    {
        if (!preg_match($pattern, $string)) {
            throw new TestFailureException($message ?: "String does not match pattern " . $pattern);
        }
    }

    protected function assertGreaterThan(int|float $expected, int|float $actual, string $message = ''): void
    {
        if (!($actual > $expected)) {
            throw new TestFailureException($message ?: "Expected $actual > $expected");
        }
    }

    protected function assertGreaterThanOrEqual(int|float $expected, int|float $actual, string $message = ''): void
    {
        if (!($actual >= $expected)) {
            throw new TestFailureException($message ?: "Expected $actual >= $expected");
        }
    }

    protected function assertIsArray(mixed $value, string $message = ''): void
    {
        if (!is_array($value)) {
            throw new TestFailureException($message ?: "Expected array, got " . gettype($value));
        }
    }

    protected function assertCount(int $expected, array $haystack, string $message = ''): void
    {
        if (count($haystack) !== $expected) {
            throw new TestFailureException($message ?: "Expected count $expected, got " . count($haystack));
        }
    }

    protected function assertLessThanOrEqual(int|float $expected, int|float $actual, string $message = ''): void
    {
        if (!($actual <= $expected)) {
            throw new TestFailureException($message ?: "Expected $actual <= $expected");
        }
    }

    private function export(mixed $value): string
    {
        if (is_null($value)) return 'null';
        if (is_bool($value)) return $value ? 'true' : 'false';
        if (is_string($value)) return '"' . $value . '"';
        if (is_array($value)) return json_encode($value);
        if (is_object($value)) return get_class($value);
        return (string)$value;
    }

    public static function getDb(): PDO
    {
        return Database::getInstance();
    }

    protected static function insertFixture(string $table, array $data): int
    {
        $db = self::getDb();
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $db->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
        $stmt->execute(array_values($data));
        $id = (int)$db->lastInsertId();
        self::$fixtures[] = ['table' => $table, 'id' => $id];
        return $id;
    }

    protected static function cleanupFixtures(): void
    {
        $db = self::getDb();
        foreach (array_reverse(self::$fixtures) as $fixture) {
            $db->prepare("DELETE FROM {$fixture['table']} WHERE id = ?")->execute([$fixture['id']]);
        }
        self::$fixtures = [];
    }

    public function run(): array
    {
        $this->passed = 0;
        $this->failed = 0;
        $this->failures = [];
        $ref = new \ReflectionClass($this);
        $methods = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);

        $this->setUpBeforeClass();

        foreach ($methods as $method) {
            if (str_starts_with($method->name, 'test')) {
                try {
                    $this->setUp();
                    $method->invoke($this);
                    $this->passed++;
                    echo "  ✓ " . $method->name . "\n";
                } catch (TestFailureException $e) {
                    $this->failed++;
                    $this->failures[] = ['test' => $method->name, 'error' => $e->getMessage()];
                    echo "  ✗ " . $method->name . ": " . $e->getMessage() . "\n";
                } catch (\Throwable $e) {
                    $this->failed++;
                    $this->failures[] = ['test' => $method->name, 'error' => get_class($e) . ': ' . $e->getMessage()];
                    echo "  ✗ " . $method->name . ": " . get_class($e) . ' - ' . $e->getMessage() . "\n";
                } finally {
                    $this->tearDown();
                }
            }
        }

        $this->tearDownAfterClass();

        return [
            'passed' => $this->passed,
            'failed' => $this->failed,
            'total' => $this->passed + $this->failed,
            'failures' => $this->failures,
        ];
    }

    public function getResults(): array
    {
        return [
            'passed' => $this->passed,
            'failed' => $this->failed,
            'total' => $this->passed + $this->failed,
            'failures' => $this->failures,
        ];
    }
}

class TestFailureException extends \RuntimeException {}
