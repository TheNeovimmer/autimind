<?php
namespace App\Core;

class Env
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    public static function update(string $key, string $value): bool
    {
        $path = __DIR__ . '/../../.env';
        if (!file_exists($path)) {
            return false;
        }

        $content = file_get_contents($path);
        $escapedValue = str_replace(['\\', '"', "'"], ['\\\\', '\"', "\'"], $value);

        if (preg_match('/^' . preg_quote($key, '/') . '=/m', $content)) {
            $content = preg_replace(
                '/^' . preg_quote($key, '/') . '=.*$/m',
                $key . '=' . $escapedValue,
                $content
            );
        } else {
            $content .= "\n" . $key . '=' . $escapedValue;
        }

        file_put_contents($path, $content);
        $_ENV[$key] = $value;
        putenv("$key=$value");
        return true;
    }
}
