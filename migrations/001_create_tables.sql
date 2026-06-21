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
    `token` VARCHAR(255) NOT NULL UNIQUE,
    `expires_at` DATETIME NOT NULL,
    `used` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_password_resets_email` (`email`),
    INDEX `idx_password_resets_token` (`token`)
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
