<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Session;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Secure session cookie config
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);

Session::start();

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(self), microphone=(), camera=()');

// Session timeout tracking
if (Session::has('user_id')) {
    $lifetime = 3600;
    if (!isset($_SESSION['_last_activity'])) {
        $_SESSION['_last_activity'] = time();
    }
    if ((time() - $_SESSION['_last_activity']) > $lifetime) {
        Session::destroy();
        header('Location: /login?expired=1');
        exit;
    }
    $_SESSION['_last_activity'] = time();
}

$router = new Router();
require __DIR__ . '/../routes/web.php';
$router->dispatch();
