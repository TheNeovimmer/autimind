<?php
namespace App\Core;

class Middleware
{
    public static function handle(string $middleware): void
    {
        if ($middleware === 'auth') {
            if (!Session::has('user_id')) {
                header('Location: /login');
                exit;
            }
        }

        if (str_starts_with($middleware, 'role:')) {
            $role = explode(':', $middleware)[1];
            if (Session::get('role') !== $role) {
                http_response_code(403);
                View::render('errors/403', [], 'main');
                exit;
            }
        }
    }
}
