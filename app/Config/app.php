<?php
return [
    'name' => $_ENV['APP_NAME'] ?? 'AutiMind',
    'env'  => $_ENV['APP_ENV'] ?? 'production',
    'url'  => $_ENV['APP_URL'] ?? 'http://localhost',
];
