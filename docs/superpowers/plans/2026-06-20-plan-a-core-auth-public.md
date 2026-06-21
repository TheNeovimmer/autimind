# Plan A: Core + Auth + Public Pages Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the PHP MVC core framework, authentication system (login/signup/password-reset), database migrations, and convert all public marketing pages from static HTML to dynamic PHP views.

**Architecture:** Flat MVC with Service Layer — custom Router, PDO Database singleton, Session wrapper, Layout-based View rendering, Middleware for auth/role gates. Public pages use `layouts/main.php`, authenticated pages use `layouts/dashboard.php`.

**Tech Stack:** PHP 8.x native, MySQL via PDO, session-based auth, existing CSS/JS preserved

---

### Task 1: Directory structure, autoloader, and environment config

**Files:**
- Create: `composer.json`
- Create: `.env`
- Create: `.env.example`
- Create: `.htaccess` (root → public/ rewrite)
- Create: `public/.htaccess`
- Create: `public/index.php`

- [ ] **Step 1: Create root .htaccess to rewrite to public/**

```apache
RewriteEngine On
RewriteRule ^(.*)$ public/$1 [L]
```

File: `.htaccess` at project root.

- [ ] **Step 2: Create public/.htaccess for front controller routing**

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

File: `public/.htaccess`

- [ ] **Step 3: Move existing assets into public/**

Run:
```bash
mv assets public/assets
```

- [ ] **Step 4: Create .env and .env.example**

File: `.env`
```ini
APP_NAME=AutiMind
APP_ENV=development
APP_URL=http://localhost
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=autimind
DB_USER=root
DB_PASS=
```

File: `.env.example` (same but with placeholder values).

- [ ] **Step 5: Create composer.json with PSR-4 autoloading**

```json
{
    "name": "autimind/app",
    "description": "AutiMind PHP MVC Platform",
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    },
    "require": {
        "php": ">=8.0",
        "vlucas/phpdotenv": "^5.6"
    }
}
```

- [ ] **Step 6: Create directory structure**

Run:
```bash
mkdir -p app/Core app/Controllers app/Models app/Services app/Views/layouts app/Views/auth app/Views/public app/Views/partials app/Views/errors app/Config migrations
```

- [ ] **Step 7: Install dependencies and generate autoloader**

```bash
composer install
```

- [ ] **Step 8: Create .env loader and config files**

File: `app/Config/app.php`
```php
<?php
return [
    'name' => $_ENV['APP_NAME'] ?? 'AutiMind',
    'env'  => $_ENV['APP_ENV'] ?? 'production',
    'url'  => $_ENV['APP_URL'] ?? 'http://localhost',
];
```

File: `app/Config/database.php`
```php
<?php
return [
    'driver' => 'mysql',
    'host'   => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port'   => $_ENV['DB_PORT'] ?? '3306',
    'name'   => $_ENV['DB_NAME'] ?? 'autimind',
    'user'   => $_ENV['DB_USER'] ?? 'root',
    'pass'   => $_ENV['DB_PASS'] ?? '',
];
```

- [ ] **Step 9: Create the front controller (index.php)**

File: `public/index.php`
```php
<?php
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

require __DIR__ . '/../app/Config/app.php';
require __DIR__ . '/../app/Config/database.php';

use App\Core\Router;
use App\Core\Session;

Session::start();

$router = new Router();
require __DIR__ . '/../routes/web.php';
$router->dispatch();
```

- [ ] **Step 10: Commit**

```bash
git add -A && git commit -m "feat: scaffold MVC directory structure, autoloader, env config"
```

---

### Task 2: Core framework classes — Router, Database, Session, View, Middleware

**Files:**
- Create: `app/Core/Router.php`
- Create: `app/Core/Database.php`
- Create: `app/Core/Session.php`
- Create: `app/Core/View.php`
- Create: `app/Core/Middleware.php`
- Create: `routes/web.php`

- [ ] **Step 1: Create Router class**

File: `app/Core/Router.php`
```php
<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $uri, string $action, array $middleware = []): void
    {
        $this->routes[] = ['GET', $uri, $action, $middleware];
    }

    public function post(string $uri, string $action, array $middleware = []): void
    {
        $this->routes[] = ['POST', $uri, $action, $middleware];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as [$routeMethod, $routeUri, $action, $middleware]) {
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $routeUri);
            $pattern = '#^' . $pattern . '$#';

            if ($method !== $routeMethod || !preg_match($pattern, $uri, $matches)) {
                continue;
            }

            // Run middleware
            foreach ($middleware as $m) {
                Middleware::handle($m);
            }

            [$controller, $method] = explode('@', $action);
            $controller = 'App\\Controllers\\' . $controller;
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            $instance = new $controller();
            $instance->$method(...$params);
            return;
        }

        http_response_code(404);
        View::render('errors/404', [], 'main');
    }
}
```

- [ ] **Step 2: Create Database class (PDO singleton)**

File: `app/Core/Database.php`
```php
<?php
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../Config/database.php';
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4";
            try {
                self::$instance = new PDO($dsn, $config['user'], $config['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                die('Database connection failed: ' . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
```

- [ ] **Step 3: Create Session class**

File: `app/Core/Session.php`
```php
<?php
namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        session_destroy();
        $_SESSION = [];
    }

    public static function setFlash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }
}
```

- [ ] **Step 4: Create View class**

File: `app/Core/View.php`
```php
<?php
namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $layoutPath = __DIR__ . '/../Views/layouts/' . $layout . '.php';
        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    public static function renderPartial(string $partial, array $data = []): void
    {
        extract($data);
        $path = __DIR__ . '/../Views/partials/' . $partial . '.php';
        if (file_exists($path)) {
            require $path;
        }
    }
}
```

- [ ] **Step 5: Create Middleware class**

File: `app/Core/Middleware.php`
```php
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
```

- [ ] **Step 6: Create routes file**

File: `routes/web.php`
```php
<?php

use App\Core\Router;

/** @var Router $router */

// Auth routes
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/signup', 'AuthController@signupForm');
$router->post('/signup', 'AuthController@signup');
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@forgotPasswordForm');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password/{token}', 'AuthController@resetPasswordForm');
$router->post('/reset-password/{token}', 'AuthController@resetPassword');

// Public pages
$router->get('/', 'HomeController@index');
$router->get('/about', 'HomeController@about');
$router->get('/pricing', 'HomeController@pricing');
$router->get('/contact', 'HomeController@contact');
$router->post('/contact', 'HomeController@submitContact');
$router->get('/faq', 'HomeController@faq');
$router->get('/program', 'HomeController@program');
$router->get('/espaceenfant', 'HomeController@espaceEnfant');
$router->get('/espaceparent', 'HomeController@espaceParent');
$router->get('/specialists', 'HomeController@specialists');
$router->get('/chatbot', 'HomeController@chatbot');
$router->get('/chatbotstart', 'HomeController@chatbotStart');

// Dashboard routes (parent)
$router->get('/parent/dashboard', 'ParentController@dashboard', ['auth', 'role:parent']);

// Dashboard routes (specialist)
$router->get('/specialist/dashboard', 'SpecialistController@dashboard', ['auth', 'role:specialist']);

// Dashboard routes (admin)
$router->get('/admin/dashboard', 'AdminController@dashboard', ['auth', 'role:admin']);
```

- [ ] **Step 7: Commit**

```bash
git add -A && git commit -m "feat: add core framework classes (Router, Database, Session, View, Middleware)"
```

---

### Task 3: Database migrations — create all tables

**Files:**
- Create: `migrations/001_create_tables.sql`
- Create: `app/Core/Migration.php`

- [ ] **Step 1: Create the SQL migration file**

File: `migrations/001_create_tables.sql`
```sql
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `role` ENUM('parent','specialist','admin') NOT NULL DEFAULT 'parent',
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `avatar` VARCHAR(255) NULL,
    `phone` VARCHAR(50) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `children` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `parent_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `age` INT UNSIGNED NULL,
    `avatar` VARCHAR(255) NULL,
    `birth_date` DATE NULL,
    `diagnosis_status` VARCHAR(100) NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `specialist_details` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `bio` TEXT NULL,
    `specializations` TEXT NULL,
    `years_experience` INT UNSIGNED NULL,
    `is_available` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `quiz_questions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `question_text` TEXT NOT NULL,
    `category` ENUM('social_communication','behavior','sensory','developmental') NOT NULL,
    `order_index` INT UNSIGNED NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `quiz_options` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `question_id` INT UNSIGNED NOT NULL,
    `option_text` VARCHAR(255) NOT NULL,
    `weight` INT NOT NULL DEFAULT 0,
    `order_index` INT UNSIGNED NOT NULL,
    FOREIGN KEY (`question_id`) REFERENCES `quiz_questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `quiz_attempts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `child_id` INT UNSIGNED NOT NULL,
    `started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` DATETIME NULL,
    `total_score` INT NULL,
    `risk_level` ENUM('low','moderate','high') NULL,
    `status` ENUM('in_progress','completed') NOT NULL DEFAULT 'in_progress',
    FOREIGN KEY (`child_id`) REFERENCES `children`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `quiz_answers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `attempt_id` INT UNSIGNED NOT NULL,
    `question_id` INT UNSIGNED NOT NULL,
    `option_id` INT UNSIGNED NOT NULL,
    FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`question_id`) REFERENCES `quiz_questions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`option_id`) REFERENCES `quiz_options`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `appointments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `child_id` INT UNSIGNED NOT NULL,
    `specialist_id` INT UNSIGNED NOT NULL,
    `parent_id` INT UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    `time` TIME NOT NULL,
    `duration` INT UNSIGNED NOT NULL DEFAULT 30,
    `status` ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`child_id`) REFERENCES `children`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`specialist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`parent_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sender_id` INT UNSIGNED NOT NULL,
    `receiver_id` INT UNSIGNED NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `body` TEXT NOT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `activities` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `category` ENUM('games','puzzles','stories','video','coloring') NOT NULL,
    `difficulty` ENUM('easy','medium','hard') NOT NULL DEFAULT 'easy',
    `image_url` VARCHAR(255) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `child_progress` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `child_id` INT UNSIGNED NOT NULL,
    `activity_id` INT UNSIGNED NOT NULL,
    `score` INT UNSIGNED NULL,
    `completed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `time_spent_seconds` INT UNSIGNED NULL,
    FOREIGN KEY (`child_id`) REFERENCES `children`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`activity_id`) REFERENCES `activities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `plan` ENUM('standard','premium','family') NOT NULL,
    `status` ENUM('active','cancelled','expired') NOT NULL DEFAULT 'active',
    `started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ends_at` DATETIME NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `chatbot_responses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `keywords` TEXT NOT NULL,
    `response_text` TEXT NOT NULL,
    `category` VARCHAR(100) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `chat_history` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `message` TEXT NOT NULL,
    `sender` ENUM('user','bot') NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `faq_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `question` TEXT NOT NULL,
    `answer` TEXT NOT NULL,
    `category` ENUM('general','features','pricing','technical') NOT NULL,
    `order_index` INT UNSIGNED NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

- [ ] **Step 2: Create Migration runner**

File: `app/Core/Migration.php`
```php
<?php
namespace App\Core;

class Migration
{
    public static function run(): void
    {
        $db = Database::getInstance();
        $sql = file_get_contents(__DIR__ . '/../../migrations/001_create_tables.sql');
        $statements = explode(';', $sql);
        foreach ($statements as $stmt) {
            if (trim($stmt)) {
                $db->exec($stmt . ';');
            }
        }
        echo "Migrations ran successfully.\n";
    }
}
```

- [ ] **Step 3: Seed default data (admin user + quiz questions + sample chatbot responses)**

File: `migrations/002_seed_data.sql`
```sql
-- Default admin account (password: admin123)
INSERT INTO `users` (`role`, `name`, `email`, `password`, `is_active`) VALUES
('admin', 'Admin', 'admin@autimind.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('specialist', 'Dr. Sarah Chen', 'sarah@autimind.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('specialist', 'David Okonkwo', 'david@autimind.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

INSERT INTO `specialist_details` (`user_id`, `title`, `bio`, `specializations`, `years_experience`) VALUES
(2, 'Speech Therapist', 'Expert in pediatric speech and language development.', '["speech therapy","language disorders","social communication"]', 12),
(3, 'Behavior Analyst', 'Board-certified behavior analyst specializing in early intervention.', '["ABA therapy","behavior management","early intervention"]', 8);

-- Quiz questions (10 questions, 4 categories)
INSERT INTO `quiz_questions` (`id`, `question_text`, `category`, `order_index`) VALUES
(1, 'Does your child make eye contact when interacting with you?', 'social_communication', 1),
(2, 'Does your child respond to their name when called?', 'social_communication', 2),
(3, 'Does your child engage in pretend play (e.g., feeding a doll)?', 'social_communication', 3),
(4, 'Does your child have repetitive movements (e.g., hand-flapping, rocking)?', 'behavior', 4),
(5, 'Does your child become distressed by minor changes in routine?', 'behavior', 5),
(6, 'Does your child show intense, focused interests in specific objects or topics?', 'behavior', 6),
(7, 'Does your child react strongly to certain sounds, textures, or lights?', 'sensory', 7),
(8, 'Does your child seek out or avoid specific sensory experiences?', 'sensory', 8),
(9, 'Does your child use gestures (pointing, waving) to communicate?', 'developmental', 9),
(10, 'Does your child imitate actions or sounds you make?', 'developmental', 10);

-- Options (6 per question, weight 0-5)
INSERT INTO `quiz_options` (`question_id`, `option_text`, `weight`, `order_index`) VALUES
(1, 'Always', 5, 1), (1, 'Usually', 4, 2), (1, 'Often', 3, 3), (1, 'Sometimes', 2, 4), (1, 'Rarely', 1, 5), (1, 'Never', 0, 6),
(2, 'Always', 5, 1), (2, 'Usually', 4, 2), (2, 'Often', 3, 3), (2, 'Sometimes', 2, 4), (2, 'Rarely', 1, 5), (2, 'Never', 0, 6),
(3, 'Always', 5, 1), (3, 'Usually', 4, 2), (3, 'Often', 3, 3), (3, 'Sometimes', 2, 4), (3, 'Rarely', 1, 5), (3, 'Never', 0, 6),
(4, 'Always', 5, 1), (4, 'Usually', 4, 2), (4, 'Often', 3, 3), (4, 'Sometimes', 2, 4), (4, 'Rarely', 1, 5), (4, 'Never', 0, 6),
(5, 'Always', 5, 1), (5, 'Usually', 4, 2), (5, 'Often', 3, 3), (5, 'Sometimes', 2, 4), (5, 'Rarely', 1, 5), (5, 'Never', 0, 6),
(6, 'Always', 5, 1), (6, 'Usually', 4, 2), (6, 'Often', 3, 3), (6, 'Sometimes', 2, 4), (6, 'Rarely', 1, 5), (6, 'Never', 0, 6),
(7, 'Always', 5, 1), (7, 'Usually', 4, 2), (7, 'Often', 3, 3), (7, 'Sometimes', 2, 4), (7, 'Rarely', 1, 5), (7, 'Never', 0, 6),
(8, 'Always', 5, 1), (8, 'Usually', 4, 2), (8, 'Often', 3, 3), (8, 'Sometimes', 2, 4), (8, 'Rarely', 1, 5), (8, 'Never', 0, 6),
(9, 'Always', 5, 1), (9, 'Usually', 4, 2), (9, 'Often', 3, 3), (9, 'Sometimes', 2, 4), (9, 'Rarely', 1, 5), (9, 'Never', 0, 6),
(10, 'Always', 5, 1), (10, 'Usually', 4, 2), (10, 'Often', 3, 3), (10, 'Sometimes', 2, 4), (10, 'Rarely', 1, 5), (10, 'Never', 0, 6);

-- FAQ items
INSERT INTO `faq_items` (`question`, `answer`, `category`, `order_index`) VALUES
('What is AutiMind?', 'AutiMind is a comprehensive platform designed to support children with autism and their families through early screening, personalized activities, professional guidance, and a supportive community.', 'general', 1),
('Who can use AutiMind?', 'AutiMind is designed for parents, caregivers, educators, and healthcare professionals working with children on the autism spectrum.', 'general', 2),
('How does the screening quiz work?', 'Our screening quiz consists of 10 clinically-informed questions across key developmental areas. Based on your answers, we provide a risk assessment along with personalized recommendations.', 'features', 3),
('Can I track my child''s progress?', 'Yes! AutiMind includes a detailed progress tracking system that monitors your child''s development across activities, quiz results, and behavioral milestones over time.', 'features', 4),
('Is AutiMind free to use?', 'We offer a free Standard plan with basic features. Our Premium and Family plans unlock advanced tools, detailed analytics, and direct specialist messaging.', 'pricing', 5),
('Can I upgrade or downgrade my plan?', 'Yes, you can change your plan at any time. Upgrades take effect immediately, while downgrades apply at the end of your billing cycle.', 'pricing', 6),
('Is my data secure?', 'Absolutely. We use industry-standard encryption, secure servers, and strict data protection protocols. Your family''s privacy is our top priority.', 'technical', 7),
('Which devices are supported?', 'AutiMind works on all modern browsers on desktop, tablet, and mobile devices. Our responsive design ensures a seamless experience across screen sizes.', 'technical', 8),
('How do I contact support?', 'You can reach our support team through the Contact form on our website, via email at autimind@autism.com, or by phone at +91 6232-1151-22.', 'technical', 9);

-- Chatbot responses
INSERT INTO `chatbot_responses` (`keywords`, `response_text`, `category`) VALUES
('["hello","hi","hey","greetings"]', 'Hello! I''m AutiMind assistant. How can I help you today?', 'general'),
('["autism","what is autism","spectrum"]', 'Autism Spectrum Disorder (ASD) is a developmental condition that affects communication, behavior, and social interaction. Every child with autism is unique, and early intervention can make a significant difference.', 'general'),
('["screening","quiz","test","assessment"]', 'Our screening quiz helps identify potential signs of autism. It takes about 10 minutes and covers key developmental areas. You can start it from your Parent Dashboard.', 'features'),
('["progress","tracking","monitor"]', 'Progress tracking allows you to monitor your child''s development across activities and quiz results over time. You can view detailed reports in your Parent Dashboard.', 'features'),
('["appointment","booking","schedule","specialist"]', 'You can browse our specialist directory and book appointments directly from your Parent Dashboard. Simply select a specialist, choose an available time slot, and confirm.', 'features'),
('["message","contact","specialist"]', 'You can send messages to your child''s specialist through the messaging system in your Parent Dashboard.', 'features'),
('["password","forgot","reset","login"]', 'If you forgot your password, click "Forgot Password" on the login page and follow the instructions sent to your email.', 'technical'),
('["pricing","plan","cost","subscription"]', 'We offer three plans: Standard (free), Premium ($19/month), and Family ($29/month). Each plan includes different features. Visit our Pricing page for details.', 'pricing');
```

- [ ] **Step 4: Run migrations**

Run:
```bash
php -r "require 'vendor/autoload.php'; App\Core\Migration::run();"
```

Then seed:
```bash
mysql -u root autimind < migrations/002_seed_data.sql
```

- [ ] **Step 5: Commit**

```bash
git add -A && git commit -m "feat: add database migrations and seed data"
```

---

### Task 4: Auth system — login, signup, logout, password reset

**Files:**
- Create: `app/Controllers/AuthController.php`
- Create: `app/Models/User.php`
- Create: `app/Core/Validator.php`
- Create: `app/Views/auth/login.php`
- Create: `app/Views/auth/signup.php`
- Create: `app/Views/auth/forgot-password.php`
- Create: `app/Views/auth/reset-password.php`

- [ ] **Step 1: Create Validator class**

File: `app/Core/Validator.php`
```php
<?php
namespace App\Core;

class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? '';
            foreach (explode('|', $ruleSet) as $rule) {
                if ($rule === 'required' && empty($value)) {
                    $this->errors[$field][] = "$field is required";
                }
                if (str_starts_with($rule, 'min:') && strlen($value) < (int) explode(':', $rule)[1]) {
                    $this->errors[$field][] = "$field must be at least " . explode(':', $rule)[1] . " characters";
                }
                if (str_starts_with($rule, 'max:') && strlen($value) > (int) explode(':', $rule)[1]) {
                    $this->errors[$field][] = "$field must not exceed " . explode(':', $rule)[1] . " characters";
                }
                if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "$field must be a valid email";
                }
                if ($rule === 'confirmed' && $value !== ($data[$field . '_confirmation'] ?? '')) {
                    $this->errors[$field][] = "$field confirmation does not match";
                }
                if (str_starts_with($rule, 'in:') && !in_array($value, explode(',', substr($rule, 3)))) {
                    $this->errors[$field][] = "$field must be one of: " . substr($rule, 3);
                }
            }
        }
        return empty($this->errors);
    }

    public function errors(): array { return $this->errors; }
}
```

- [ ] **Step 2: Create User model**

File: `app/Models/User.php`
```php
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
        $stmt = $db->prepare('INSERT INTO users (role, name, email, password) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['role'], $data['name'], $data['email'], $data['password']]);
        return (int) $db->lastInsertId();
    }

    public static function updatePassword(int $id, string $password): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([$password, $id]);
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
}
```

- [ ] **Step 3: Create AuthController**

File: `app/Controllers/AuthController.php`
```php
<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Core\Validator;
use App\Core\Database;
use App\Models\User;
use PDO;

class AuthController
{
    public function loginForm(): void
    {
        if (Session::has('user_id')) {
            $this->redirectToDashboard();
            return;
        }
        View::render('auth/login', ['title' => 'Login'], 'main');
    }

    public function login(): void
    {
        $validator = new Validator();
        if (!$validator->validate($_POST, [
            'email' => 'required|email',
            'password' => 'required'
        ])) {
            View::render('auth/login', ['errors' => $validator->errors(), 'old' => $_POST], 'main');
            return;
        }

        $user = User::findByEmail($_POST['email']);
        if (!$user || !password_verify($_POST['password'], $user['password'])) {
            View::render('auth/login', [
                'errors' => ['email' => ['Invalid email or password']],
                'old' => $_POST
            ], 'main');
            return;
        }

        if (!$user['is_active']) {
            View::render('auth/login', [
                'errors' => ['email' => ['Your account has been deactivated']],
                'old' => $_POST
            ], 'main');
            return;
        }

        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('role', $user['role']);
        Session::regenerate_id();

        $this->redirectToDashboard();
    }

    public function signupForm(): void
    {
        if (Session::has('user_id')) {
            $this->redirectToDashboard();
            return;
        }
        View::render('auth/signup', ['title' => 'Sign Up'], 'main');
    }

    public function signup(): void
    {
        $validator = new Validator();
        if (!$validator->validate($_POST, [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'role' => 'required|in:parent,specialist,educator'
        ])) {
            View::render('auth/signup', ['errors' => $validator->errors(), 'old' => $_POST], 'main');
            return;
        }

        if (User::findByEmail($_POST['email'])) {
            View::render('auth/signup', [
                'errors' => ['email' => ['An account with this email already exists']],
                'old' => $_POST
            ], 'main');
            return;
        }

        $role = $_POST['role'] === 'educator' ? 'specialist' : $_POST['role'];
        $userId = User::create([
            'role' => $role,
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
        ]);

        Session::set('user_id', $userId);
        Session::set('user_name', $_POST['name']);
        Session::set('user_email', $_POST['email']);
        Session::set('role', $role);

        $this->redirectToDashboard();
    }

    public function logout(): void
    {
        Session::destroy();
        header('Location: /');
        exit;
    }

    public function forgotPasswordForm(): void
    {
        View::render('auth/forgot-password', ['title' => 'Forgot Password'], 'main');
    }

    public function forgotPassword(): void
    {
        $validator = new Validator();
        if (!$validator->validate($_POST, ['email' => 'required|email'])) {
            View::render('auth/forgot-password', ['errors' => $validator->errors()], 'main');
            return;
        }

        $user = User::findByEmail($_POST['email']);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $db = Database::getInstance();
            $stmt = $db->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))');
            $stmt->execute([$_POST['email'], $token]);
            // In production, send email. Here we just show success.
        }

        Session::setFlash('success', 'If that email exists, we have sent a password reset link.');
        header('Location: /login');
        exit;
    }

    public function resetPasswordForm(string $token): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()');
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            Session::setFlash('error', 'Invalid or expired reset token.');
            header('Location: /login');
            exit;
        }

        View::render('auth/reset-password', ['token' => $token, 'title' => 'Reset Password'], 'main');
    }

    public function resetPassword(string $token): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()');
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            Session::setFlash('error', 'Invalid or expired reset token.');
            header('Location: /login');
            exit;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, ['password' => 'required|min:8'])) {
            View::render('auth/reset-password', ['errors' => $validator->errors(), 'token' => $token], 'main');
            return;
        }

        User::updatePasswordByEmail($reset['email'], password_hash($_POST['password'], PASSWORD_BCRYPT));

        $stmt = $db->prepare('UPDATE password_resets SET used = 1 WHERE token = ?');
        $stmt->execute([$token]);

        Session::setFlash('success', 'Password reset successfully. Please log in.');
        header('Location: /login');
        exit;
    }

    private function redirectToDashboard(): void
    {
        $role = Session::get('role');
        $redirects = [
            'parent' => '/parent/dashboard',
            'specialist' => '/specialist/dashboard',
            'admin' => '/admin/dashboard',
        ];
        header('Location: ' . ($redirects[$role] ?? '/'));
        exit;
    }
}
```

- [ ] **Step 4: Update User model — add updatePasswordByEmail method**

Add to `app/Models/User.php`:
```php
public static function updatePasswordByEmail(string $email, string $password): void
{
    $stmt = Database::getInstance()->prepare('UPDATE users SET password = ? WHERE email = ?');
    $stmt->execute([$password, $email]);
}

public static function regenerateId(): void
{
    session_regenerate_id(true);
}
```

Add to Session class — add `regenerate_id`:
```php
public static function regenerate_id(): void
{
    session_regenerate_id(true);
}
```

- [ ] **Step 5: Create login view**

File: `app/Views/auth/login.php`
```php
<div class="auth-page">
  <div class="auth-container">
    <div class="auth-card">
      <h1 class="auth-title">Welcome Back</h1>
      <p class="auth-subtitle">Sign in to your AutiMind account</p>

      <?php if (Session::hasFlash('success')): ?>
        <div class="alert alert-success"><?= Session::getFlash('success') ?></div>
      <?php endif; ?>
      <?php if (Session::hasFlash('error')): ?>
        <div class="alert alert-error"><?= Session::getFlash('error') ?></div>
      <?php endif; ?>

      <form method="POST" action="/login" class="auth-form">
        <div class="form-group">
          <div class="input-icon-wrapper">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="Email" required
                   value="<?= htmlspecialchars($old['email'] ?? '') ?>">
          </div>
          <?php if (isset($errors['email'])): ?>
            <span class="field-error"><?= htmlspecialchars($errors['email'][0]) ?></span>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <div class="input-icon-wrapper">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Password" required>
            <button type="button" class="password-toggle" onclick="togglePassword(this)">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          <?php if (isset($errors['password'])): ?>
            <span class="field-error"><?= htmlspecialchars($errors['password'][0]) ?></span>
          <?php endif; ?>
        </div>
        <div class="form-options">
          <label><input type="checkbox" name="remember"> Remember me</label>
          <a href="/forgot-password">Forgot password?</a>
        </div>
        <button type="submit" class="btn-submit">Sign In</button>
      </form>
      <p class="auth-switch">Don't have an account? <a href="/signup">Sign Up</a></p>
    </div>
  </div>
</div>
```

- [ ] **Step 6: Create signup view**

File: `app/Views/auth/signup.php`
```php
<div class="auth-page">
  <div class="auth-container">
    <div class="auth-card">
      <h1 class="auth-title">Create Account</h1>
      <p class="auth-subtitle">Join AutiMind today</p>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <?php foreach ($errors as $fieldErrors): ?>
            <?php foreach ($fieldErrors as $error): ?>
              <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="/signup" class="auth-form">
        <div class="form-group">
          <input type="text" name="name" placeholder="Full Name" required
                 value="<?= htmlspecialchars($old['name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <input type="email" name="email" placeholder="Email" required
                 value="<?= htmlspecialchars($old['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <select name="role" required>
            <option value="">Select Role</option>
            <option value="parent" <?= ($old['role'] ?? '') === 'parent' ? 'selected' : '' ?>>Parent</option>
            <option value="specialist" <?= ($old['role'] ?? '') === 'specialist' ? 'selected' : '' ?>>Specialist</option>
            <option value="educator" <?= ($old['role'] ?? '') === 'educator' ? 'selected' : '' ?>>Educator</option>
          </select>
        </div>
        <div class="form-group">
          <div class="input-icon-wrapper">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Password" required>
            <button type="button" class="password-toggle" onclick="togglePassword(this)">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>
        <div class="form-group">
          <div class="input-icon-wrapper">
            <i class="fas fa-lock"></i>
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
          </div>
        </div>
        <div class="form-options">
          <label><input type="checkbox" name="terms" required> I agree to the Terms & Conditions</label>
        </div>
        <button type="submit" class="btn-submit">Create Account</button>
      </form>
      <p class="auth-switch">Already have an account? <a href="/login">Sign In</a></p>
    </div>
  </div>
</div>
```

- [ ] **Step 7: Create forgot-password and reset-password views**

File: `app/Views/auth/forgot-password.php`
```php
<div class="auth-page">
  <div class="auth-container">
    <div class="auth-card">
      <h1 class="auth-title">Forgot Password</h1>
      <p class="auth-subtitle">Enter your email and we'll send you a reset link</p>
      <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errors['email'][0] ?? '') ?></div>
      <?php endif; ?>
      <form method="POST" action="/forgot-password" class="auth-form">
        <div class="form-group">
          <input type="email" name="email" placeholder="Email" required>
        </div>
        <button type="submit" class="btn-submit">Send Reset Link</button>
      </form>
      <p class="auth-switch"><a href="/login">Back to Login</a></p>
    </div>
  </div>
</div>
```

File: `app/Views/auth/reset-password.php`
```php
<div class="auth-page">
  <div class="auth-container">
    <div class="auth-card">
      <h1 class="auth-title">Reset Password</h1>
      <p class="auth-subtitle">Enter your new password</p>
      <form method="POST" action="/reset-password/<?= htmlspecialchars($token) ?>" class="auth-form">
        <div class="form-group">
          <input type="password" name="password" placeholder="New Password" required>
        </div>
        <div class="form-group">
          <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
        </div>
        <button type="submit" class="btn-submit">Reset Password</button>
      </form>
    </div>
  </div>
</div>
```

- [ ] **Step 8: Commit**

```bash
git add -A && git commit -m "feat: add authentication system with login, signup, password reset"
```

---

### Task 5: HomeController and public page views

**Files:**
- Create: `app/Controllers/HomeController.php`
- Create: `app/Views/layouts/main.php`
- Create: `app/Views/layouts/dashboard.php`
- Create: `app/Views/partials/nav.php`
- Create: `app/Views/partials/footer.php`
- Create: `app/Views/public/index.php`
- Create: `app/Views/public/about.php`
- Create: `app/Views/public/pricing.php`
- Create: `app/Views/public/contact.php`
- Create: `app/Views/public/faq.php`
- Create: `app/Views/public/program.php`
- Create: `app/Views/public/espaceenfant.php`
- Create: `app/Views/public/espaceparent.php`
- Create: `app/Views/public/specialists.php`
- Create: `app/Views/public/chatbot.php`
- Create: `app/Views/public/chatbotstart.php`

- [ ] **Step 1: Create HomeController**

File: `app/Controllers/HomeController.php`
```php
<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Core\Validator;
use App\Core\Database;
use App\Models\User;
use PDO;

class HomeController
{
    public function index(): void
    {
        View::render('public/index', ['title' => 'Home'], 'main');
    }

    public function about(): void
    {
        View::render('public/about', ['title' => 'About'], 'main');
    }

    public function pricing(): void
    {
        $user = null;
        if (Session::has('user_id')) {
            $user = User::findById(Session::get('user_id'));
        }
        View::render('public/pricing', ['title' => 'Pricing', 'user' => $user], 'main');
    }

    public function contact(): void
    {
        View::render('public/contact', ['title' => 'Contact'], 'main');
    }

    public function submitContact(): void
    {
        $validator = new Validator();
        if (!$validator->validate($_POST, [
            'name' => 'required|max:255',
            'email' => 'required|email',
            'subject' => 'required|max:255',
            'message' => 'required'
        ])) {
            View::render('public/contact', ['errors' => $validator->errors(), 'old' => $_POST], 'main');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
        $stmt->execute([$_POST['name'], $_POST['email'], $_POST['subject'], $_POST['message']]);

        Session::setFlash('success', 'Thank you for your message. We will get back to you soon.');
        header('Location: /contact');
        exit;
    }

    public function faq(): void
    {
        $db = Database::getInstance();
        $stmt = $db->query('SELECT * FROM faq_items WHERE is_active = 1 ORDER BY order_index');
        $faqItems = $stmt->fetchAll();
        View::render('public/faq', ['title' => 'FAQ', 'faqItems' => $faqItems], 'main');
    }

    public function program(): void
    {
        View::render('public/program', ['title' => 'Program'], 'main');
    }

    public function espaceEnfant(): void
    {
        View::render('public/espaceenfant', ['title' => 'Children Space'], 'main');
    }

    public function espaceParent(): void
    {
        View::render('public/espaceparent', ['title' => 'Parents Space'], 'main');
    }

    public function specialists(): void
    {
        $db = Database::getInstance();
        $stmt = $db->query('
            SELECT u.id, u.name, u.avatar, sd.title, sd.bio, sd.specializations, sd.years_experience
            FROM users u
            JOIN specialist_details sd ON u.id = sd.user_id
            WHERE u.role = "specialist" AND u.is_active = 1
        ');
        $specialists = $stmt->fetchAll();
        View::render('public/specialists', ['title' => 'Specialists', 'specialists' => $specialists], 'main');
    }

    public function chatbot(): void
    {
        View::render('public/chatbot', ['title' => 'Chatbot'], 'main');
    }

    public function chatbotStart(): void
    {
        View::render('public/chatbotstart', ['title' => 'Chatbot'], 'main');
    }
}
```

- [ ] **Step 2: Create main layout**

File: `app/Views/layouts/main.php`
```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'AutiMind') ?> - AutiMind</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800&family=Inter:wght@400;500;600&family=Outfit:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
  <?php partial('nav'); ?>
  <?= $content ?>
  <?php partial('footer'); ?>
  <div class="custom-cursor"></div>
  <div class="cursor-follower"></div>
  <script src="https://unpkg.com/lenis@1.1.18/dist/lenis.min.js"></script>
  <script src="/assets/js/app.js"></script>
</body>
</html>
```

- [ ] **Step 3: Create dashboard layout**

File: `app/Views/layouts/dashboard.php`
```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'Dashboard') ?> - AutiMind</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800&family=Inter:wght@400;500;600&family=Outfit:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/assets/css/styles.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body class="dashboard-body">
  <div class="dashboard-wrapper">
    <?php partial('dashboard-sidebar'); ?>
    <main class="dashboard-main">
      <?php partial('dashboard-topbar'); ?>
      <div class="dashboard-content">
        <?= $content ?>
      </div>
    </main>
  </div>
  <script src="/assets/js/app.js"></script>
</body>
</html>
```

- [ ] **Step 4: Create nav partial**

File: `app/Views/partials/nav.php`
```html
<nav class="navbar">
  <div class="nav-logo">
    <a href="/"><img src="https://static.codia.ai/image/2026-06-19/6vuxJTHMOw.png" alt="AutiMind"></a>
  </div>
  <button class="nav-toggle" aria-label="Toggle navigation">
    <span></span><span></span><span></span>
  </button>
  <ul class="nav-links">
    <li><a href="/" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/') && $_SERVER['REQUEST_URI'] === '/' ? 'active' : '' ?>">Home</a></li>
    <li><a href="/about" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/about') ? 'active' : '' ?>">About</a></li>
    <li><a href="/program" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/program') ? 'active' : '' ?>">Program</a></li>
    <li><a href="/espaceenfant" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/espaceenfant') ? 'active' : '' ?>">Children</a></li>
    <li><a href="/espaceparent" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/espaceparent') ? 'active' : '' ?>">Parents</a></li>
    <li><a href="/specialists" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/specialists') ? 'active' : '' ?>">Specialists</a></li>
    <li><a href="/pricing" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/pricing') ? 'active' : '' ?>">Pricing</a></li>
    <li><a href="/contact" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/contact') ? 'active' : '' ?>">Contact</a></li>
    <?php if (\App\Core\Session::has('user_id')): ?>
      <li class="nav-btn-item"><a href="/logout">Logout</a></li>
    <?php else: ?>
      <li class="nav-btn-item"><a href="/signup">Get Started</a></li>
    <?php endif; ?>
  </ul>
  <?php if (\App\Core\Session::has('user_id')): ?>
    <a href="/logout" class="btn-signup">Logout</a>
  <?php else: ?>
    <a href="/signup" class="btn-signup">Get Started</a>
  <?php endif; ?>
</nav>
```

- [ ] **Step 5: Create footer partial**

File: `app/Views/partials/footer.php`
```html
<footer class="footer">
  <div class="footer-bg" aria-hidden="true"></div>
  <div class="footer-inner">
    <div class="footer-layout">
      <div class="footer-main">
        <h2 class="footer-heading">Keeping Loved Ones<br>Close and Comfortable</h2>
        <div class="footer-links">
          <div class="footer-col">
            <h4>CONTACT INFORMATION</h4>
            <a href="tel:+916232115122"><i class="fas fa-phone footer-link-icon"></i>+91 6232-1151-22</a>
            <a href="mailto:Autimind@autism.com"><i class="fas fa-envelope footer-link-icon"></i>Autimind@autism.com</a>
          </div>
          <div class="footer-col">
            <h4>Company</h4>
            <a href="/about">About Us</a>
            <a href="/program">Program</a>
            <a href="/pricing">Pricing</a>
          </div>
          <div class="footer-col">
            <h4>Help</h4>
            <a href="/contact">Contact</a>
          </div>
          <div class="footer-col">
            <h4>Accounts</h4>
            <a href="/login">Login</a>
            <a href="/signup">Sign Up</a>
          </div>
        </div>
      </div>
      <aside class="footer-sidebar">
        <h3 class="footer-sidebar-title">Get in touch !</h3>
        <form class="subscribe-form" action="/subscribe" method="POST" data-validate>
          <input type="email" name="email" placeholder="Enter your email" required>
          <button type="submit">Subscribe</button>
        </form>
        <div class="footer-social">
          <a href="#" class="footer-social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="footer-social-link" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" class="footer-social-link" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" class="footer-social-link" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </aside>
    </div>
    <div class="footer-divider"></div>
    <div class="footer-bottom">
      <p>AutiMind . All Rights Reserved.</p>
      <p>&copy; EDUDYS. All Rights Reserved.</p>
    </div>
  </div>
</footer>
```

- [ ] **Step 6: Copy existing HTML from current static files into View files**

For each public page view under `app/Views/public/`, copy the `<section>` content from the corresponding existing HTML file (without the `<html>`, `<head>`, nav, footer wrappers — those come from the layout).

Example: `app/Views/public/index.php`
```php
<section class="hero">
  <div class="hero-inner">
    <!-- Copy the full hero section content from index.html here -->
    <!-- Replace static images with dynamic data where needed -->
  </div>
</section>
<!-- Copy all other sections from index.html: stats, how-it-works, testimonials, pricing, etc. -->
```

For `app/Views/public/faq.php`, the FAQ items should be rendered dynamically:
```php
<section class="faq-section">
  <div class="faq-inner">
    <h1>Frequently Asked Questions</h1>
    <div class="faq-filters">
      <button class="faq-filter active" data-category="all">All</button>
      <button class="faq-filter" data-category="general">General</button>
      <button class="faq-filter" data-category="features">Features</button>
      <button class="faq-filter" data-category="pricing">Pricing</button>
      <button class="faq-filter" data-category="technical">Technical</button>
    </div>
    <div class="faq-list">
      <?php foreach ($faqItems as $item): ?>
      <div class="faq-item" data-category="<?= htmlspecialchars($item['category']) ?>">
        <button class="faq-question">
          <?= htmlspecialchars($item['question']) ?>
          <i class="fas fa-plus"></i>
        </button>
        <div class="faq-answer"><?= htmlspecialchars($item['answer']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
```

For `app/Views/public/specialists.php`, render specialist cards from DB data:
```php
<div class="spec-grid">
  <?php foreach ($specialists as $spec): ?>
  <div class="spec-card">
    <div class="spec-card-image" style="background-image: url(<?= htmlspecialchars($spec['avatar'] ?? 'https://via.placeholder.com/299x386') ?>);">
      <div class="spec-card-info">
        <div>
          <div class="spec-card-name"><?= htmlspecialchars($spec['name']) ?></div>
          <div class="spec-card-role"><?= htmlspecialchars($spec['title']) ?></div>
        </div>
        <img src="https://static.codia.ai/image/2026-06-19/Oh6dBOahV5.png" alt="" class="spec-card-arrow">
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
```

For `app/Views/public/pricing.php`, the pricing toggle should work client-side with the existing JS. The static plan data remains as-is from the original HTML.

- [ ] **Step 7: Create error views**

File: `app/Views/errors/404.php`
```html
<div class="error-page">
  <h1>404</h1>
  <p>Page not found</p>
  <a href="/" class="btn-submit">Go Home</a>
</div>
```

File: `app/Views/errors/403.php`
```html
<div class="error-page">
  <h1>403</h1>
  <p>You don't have permission to access this page.</p>
  <a href="/" class="btn-submit">Go Home</a>
</div>
```

File: `app/Views/errors/500.php`
```html
<div class="error-page">
  <h1>500</h1>
  <p>Something went wrong. Please try again later.</p>
  <a href="/" class="btn-submit">Go Home</a>
</div>
```

- [ ] **Step 8: Commit**

```bash
git add -A && git commit -m "feat: add HomeController, layouts, public page views, error pages"
```

---

### Task 6: Dashboard sidebar, topbar, and placeholder controllers

**Files:**
- Create: `app/Views/partials/dashboard-sidebar.php`
- Create: `app/Views/partials/dashboard-topbar.php`
- Create: `app/Controllers/ParentController.php`
- Create: `app/Controllers/SpecialistController.php`
- Create: `app/Controllers/AdminController.php`
- Create: `assets/css/dashboard.css`

- [ ] **Step 1: Create dashboard sidebar partial**

File: `app/Views/partials/dashboard-sidebar.php`
```php
<?php
$role = \App\Core\Session::get('role');
$currentUri = $_SERVER['REQUEST_URI'];
?>
<aside class="dash-sidebar">
  <div class="dash-sidebar-logo">
    <a href="/"><img src="https://static.codia.ai/image/2026-06-19/6vuxJTHMOw.png" alt="AutiMind"></a>
  </div>
  <nav class="dash-sidebar-nav">
    <?php if ($role === 'parent'): ?>
      <a href="/parent/dashboard" class="dash-nav-item <?= $currentUri === '/parent/dashboard' ? 'active' : '' ?>">
        <i class="fas fa-th-large"></i><span>Dashboard</span>
      </a>
      <a href="/parent/children" class="dash-nav-item <?= str_starts_with($currentUri, '/parent/children') ? 'active' : '' ?>">
        <i class="fas fa-child"></i><span>Children</span>
      </a>
      <a href="/parent/quiz" class="dash-nav-item <?= str_starts_with($currentUri, '/parent/quiz') ? 'active' : '' ?>">
        <i class="fas fa-clipboard-list"></i><span>Screening Quiz</span>
      </a>
      <a href="/parent/progress" class="dash-nav-item <?= str_starts_with($currentUri, '/parent/progress') ? 'active' : '' ?>">
        <i class="fas fa-chart-line"></i><span>Progress</span>
      </a>
      <a href="/parent/specialists" class="dash-nav-item <?= str_starts_with($currentUri, '/parent/specialists') && !str_starts_with($currentUri, '/parent/specialists/') ? 'active' : '' ?>">
        <i class="fas fa-user-md"></i><span>Specialists</span>
      </a>
      <a href="/parent/appointments" class="dash-nav-item <?= str_starts_with($currentUri, '/parent/appointments') ? 'active' : '' ?>">
        <i class="fas fa-calendar-check"></i><span>Appointments</span>
      </a>
      <a href="/parent/messages" class="dash-nav-item <?= str_starts_with($currentUri, '/parent/messages') ? 'active' : '' ?>">
        <i class="fas fa-envelope"></i><span>Messages</span>
      </a>
      <a href="/parent/chatbot" class="dash-nav-item <?= str_starts_with($currentUri, '/parent/chatbot') ? 'active' : '' ?>">
        <i class="fas fa-robot"></i><span>AI Chat</span>
      </a>
      <a href="/parent/settings" class="dash-nav-item <?= $currentUri === '/parent/settings' ? 'active' : '' ?>">
        <i class="fas fa-cog"></i><span>Settings</span>
      </a>
    <?php elseif ($role === 'specialist'): ?>
      <a href="/specialist/dashboard" class="dash-nav-item"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
      <a href="/specialist/patients" class="dash-nav-item"><i class="fas fa-users"></i><span>Patients</span></a>
      <a href="/specialist/appointments" class="dash-nav-item"><i class="fas fa-calendar-check"></i><span>Appointments</span></a>
      <a href="/specialist/messages" class="dash-nav-item"><i class="fas fa-envelope"></i><span>Messages</span></a>
      <a href="/specialist/schedule" class="dash-nav-item"><i class="fas fa-clock"></i><span>Schedule</span></a>
      <a href="/specialist/settings" class="dash-nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
    <?php elseif ($role === 'admin'): ?>
      <a href="/admin/dashboard" class="dash-nav-item"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
      <a href="/admin/users" class="dash-nav-item"><i class="fas fa-users-cog"></i><span>Users</span></a>
      <a href="/admin/specialists" class="dash-nav-item"><i class="fas fa-user-md"></i><span>Specialists</span></a>
      <a href="/admin/quiz" class="dash-nav-item"><i class="fas fa-clipboard-list"></i><span>Quiz</span></a>
      <a href="/admin/activities" class="dash-nav-item"><i class="fas fa-gamepad"></i><span>Activities</span></a>
      <a href="/admin/appointments" class="dash-nav-item"><i class="fas fa-calendar"></i><span>Appointments</span></a>
      <a href="/admin/messages" class="dash-nav-item"><i class="fas fa-envelope"></i><span>Messages</span></a>
      <a href="/admin/subscriptions" class="dash-nav-item"><i class="fas fa-credit-card"></i><span>Subscriptions</span></a>
      <a href="/admin/contacts" class="dash-nav-item"><i class="fas fa-id-card"></i><span>Contacts</span></a>
      <a href="/admin/faq" class="dash-nav-item"><i class="fas fa-question-circle"></i><span>FAQ</span></a>
      <a href="/admin/settings" class="dash-nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
    <?php endif; ?>
  </nav>
  <div class="dash-sidebar-footer">
    <a href="/logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
  </div>
</aside>
```

- [ ] **Step 2: Create dashboard topbar partial**

File: `app/Views/partials/dashboard-topbar.php`
```php
<div class="dash-topbar">
  <div class="dash-topbar-left">
    <button class="dash-sidebar-toggle"><i class="fas fa-bars"></i></button>
    <h2><?= htmlspecialchars($title ?? 'Dashboard') ?></h2>
  </div>
  <div class="dash-topbar-right">
    <span class="dash-user-name"><?= htmlspecialchars(\App\Core\Session::get('user_name')) ?></span>
    <div class="dash-user-avatar"><?= strtoupper(substr(\App\Core\Session::get('user_name'), 0, 1)) ?></div>
  </div>
</div>
```

- [ ] **Step 3: Create dashboard.css for sidebar/topbar styling**

File: `assets/css/dashboard.css`
```css
.dashboard-body {
  background: radial-gradient(ellipse at top, #5a0177 0%, #140718 70%);
  min-height: 100vh;
  color: #fff;
  font-family: 'Geist', sans-serif;
  margin: 0;
}

.dashboard-wrapper {
  display: flex;
  min-height: 100vh;
}

.dash-sidebar {
  width: 260px;
  background: rgba(108,0,144,0.15);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-right: 1px solid rgba(255,255,255,0.06);
  padding: 24px 16px;
  display: flex;
  flex-direction: column;
  gap: 32px;
  flex-shrink: 0;
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  z-index: 100;
  overflow-y: auto;
}

.dash-sidebar-logo img { height: 40px; }

.dash-sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
}

.dash-nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  border-radius: 12px;
  color: rgba(255,255,255,0.7);
  text-decoration: none;
  font-size: 15px;
  transition: all 0.2s;
}

.dash-nav-item:hover { background: rgba(255,255,255,0.08); color: #fff; }
.dash-nav-item.active { background: rgba(108,0,144,0.3); color: #fff; }
.dash-nav-item i { width: 20px; text-align: center; font-size: 16px; }

.dash-sidebar-footer { border-top: 1px solid rgba(255,255,255,0.08); padding-top: 16px; }
.dash-sidebar-footer a {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  border-radius: 12px;
  color: rgba(255,255,255,0.5);
  text-decoration: none;
  font-size: 15px;
}
.dash-sidebar-footer a:hover { background: rgba(255,50,50,0.1); color: #ff6b6b; }

.dashboard-main {
  margin-left: 260px;
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.dash-topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 32px;
  background: rgba(108,0,144,0.08);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255,255,255,0.06);
}

.dash-topbar-left { display: flex; align-items: center; gap: 16px; }
.dash-topbar-left h2 { font-size: 20px; font-weight: 600; margin: 0; }
.dash-sidebar-toggle { display: none; background: none; border: none; color: #fff; font-size: 20px; cursor: pointer; }

.dash-topbar-right { display: flex; align-items: center; gap: 12px; }
.dash-user-name { font-size: 15px; color: rgba(255,255,255,0.8); }
.dash-user-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  background: var(--primary);
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; font-weight: 600;
}

.dashboard-content { padding: 32px; flex: 1; }

@media (max-width: 768px) {
  .dash-sidebar { transform: translateX(-100%); transition: transform 0.3s; }
  .dash-sidebar.open { transform: translateX(0); }
  .dashboard-main { margin-left: 0; }
  .dash-sidebar-toggle { display: block; }
  .dashboard-content { padding: 20px; }
}
```

- [ ] **Step 4: Create placeholder controllers for dashboard routes**

File: `app/Controllers/ParentController.php`
```php
<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Models\User;

class ParentController
{
    public function dashboard(): void
    {
        View::render('parent/dashboard', ['title' => 'Dashboard'], 'dashboard');
    }
}
```

File: `app/Controllers/SpecialistController.php`
```php
<?php
namespace App\Controllers;

use App\Core\View;

class SpecialistController
{
    public function dashboard(): void
    {
        View::render('specialist/dashboard', ['title' => 'Dashboard'], 'dashboard');
    }
}
```

File: `app/Controllers/AdminController.php`
```php
<?php
namespace App\Controllers;

use App\Core\View;

class AdminController
{
    public function dashboard(): void
    {
        View::render('admin/dashboard', ['title' => 'Dashboard'], 'dashboard');
    }
}
```

- [ ] **Step 5: Create placeholder dashboard views**

File: `app/Views/parent/dashboard.php`
```html
<div class="dash-welcome">
  <h1>Welcome, <?= htmlspecialchars(\App\Core\Session::get('user_name')) ?>!</h1>
  <p>Your parent dashboard is ready.</p>
</div>
```

File: `app/Views/specialist/dashboard.php`
```html
<div class="dash-welcome">
  <h1>Welcome, <?= htmlspecialchars(\App\Core\Session::get('user_name')) ?>!</h1>
  <p>Your specialist dashboard is ready.</p>
</div>
```

File: `app/Views/admin/dashboard.php`
```html
<div class="dash-welcome">
  <h1>Admin Dashboard</h1>
  <p>Welcome, <?= htmlspecialchars(\App\Core\Session::get('user_name')) ?>.</p>
</div>
```

- [ ] **Step 6: Commit**

```bash
git add -A && git commit -m "feat: add dashboard sidebar, topbar, placeholder controllers and views"
```

---

### Task 7: Add newsletter subscription endpoint

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Controllers/HomeController.php`

- [ ] **Step 1: Add subscribe route**

Add to `routes/web.php`:
```php
$router->post('/subscribe', 'HomeController@subscribe');
```

- [ ] **Step 2: Add subscribe method to HomeController**

Add to `app/Controllers/HomeController.php`:
```php
public function subscribe(): void
{
    $email = $_POST['email'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Session::setFlash('error', 'Please enter a valid email.');
        header('Location: /');
        exit;
    }

    $db = Database::getInstance();
    $stmt = $db->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
    $stmt->execute(['Newsletter', $email, 'Newsletter Subscription', 'Subscribed to newsletter']);
    Session::setFlash('success', 'Thank you for subscribing!');
    header('Location: /');
    exit;
}
```

- [ ] **Step 3: Update Session class — add regenerate_id**

Add to `app/Core/Session.php`:
```php
public static function regenerate_id(): void
{
    session_regenerate_id(true);
}
```

- [ ] **Step 4: Commit**

```bash
git add -A && git commit -m "feat: add newsletter subscription and session regenerate"
```

---

**End of Plan A.** After this plan, the project has a working PHP MVC framework, database with all tables seeded, authentication system, all public pages rendering dynamically, and dashboard layouts ready for Plan B (Parent Dashboard) and Plan C (Specialist/Admin).
