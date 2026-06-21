<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Session;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

Session::start();

$router = new Router();
require __DIR__ . '/../routes/web.php';
$router->dispatch();
