# Plan B: Parent Dashboard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the complete parent dashboard with children CRUD, clinical quiz engine, progress tracking, specialist booking, messaging, chatbot, settings, and an overview dashboard page.

**Architecture:** Flat MVC with Service Layer. `ParentController` handles all parent routes. Models are thin PDO wrappers. Services contain business logic (quiz scoring, chatbot, insights). Views follow existing dashboard layout patterns.

**Tech Stack:** PHP 8.x, MySQL via PDO, Session-based auth, existing CSS/JS preserved

**Key Conventions:**
- All models in `app/Models/`, services in `app/Services/`
- Controller methods return `void` and call `View::render()` or redirect
- Views use `<?= htmlspecialchars($var) ?>` for user content
- CSRF token on all forms via `Session::csrf_token()`
- Flash messages via `Session::setFlash()` / `getFlash()`
- Follow existing `User.php` model pattern for all models

---

### Task 1: Create Parent Models (Child, Appointment, Message, QuizAttempt)

**Files:**
- Create: `app/Models/Child.php`
- Create: `app/Models/Appointment.php`
- Create: `app/Models/Message.php`
- Create: `app/Models/QuizQuestion.php`
- Create: `app/Models/QuizAttempt.php`
- Create: `app/Models/Activity.php`
- Create: `app/Models/Subscription.php`

- [ ] **Step 1: Create `app/Models/Child.php`**

```php
<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Child
{
    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM children WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getAllByParent(int $parentId): array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM children WHERE parent_id = ? ORDER BY name');
        $stmt->execute([$parentId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO children (parent_id, name, age, birth_date, diagnosis_status, notes) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['parent_id'], $data['name'], $data['age'] ?? null, $data['birth_date'] ?? null, $data['diagnosis_status'] ?? null, $data['notes'] ?? null]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE children SET name = ?, age = ?, birth_date = ?, diagnosis_status = ?, notes = ? WHERE id = ?');
        $stmt->execute([$data['name'], $data['age'] ?? null, $data['birth_date'] ?? null, $data['diagnosis_status'] ?? null, $data['notes'] ?? null, $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM children WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function belongsToParent(int $childId, int $parentId): bool
    {
        $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM children WHERE id = ? AND parent_id = ?');
        $stmt->execute([$childId, $parentId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function countByParent(int $parentId): int
    {
        $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM children WHERE parent_id = ?');
        $stmt->execute([$parentId]);
        return (int) $stmt->fetchColumn();
    }
}
```

- [ ] **Step 2: Create `app/Models/Appointment.php`**

```php
<?php
namespace App\Models;

use App\Core\Database;
use PDO;

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

    public static function countByParent(int $parentId, string $status = null): int
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

    public static function countBySpecialist(int $specialistId, string $status = null): int
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
```

- [ ] **Step 3: Create `app/Models/Message.php`**

```php
<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Message
{
    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM messages WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getInbox(int $userId): array
    {
        $stmt = Database::getInstance()->prepare('
            SELECT m.*, u.name AS sender_name
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.receiver_id = ?
            ORDER BY m.created_at DESC
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function getSent(int $userId): array
    {
        $stmt = Database::getInstance()->prepare('
            SELECT m.*, u.name AS receiver_name
            FROM messages m
            JOIN users u ON m.receiver_id = u.id
            WHERE m.sender_id = ?
            ORDER BY m.created_at DESC
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function getThread(int $userId1, int $userId2): array
    {
        $stmt = Database::getInstance()->prepare('
            SELECT m.*, u1.name AS sender_name, u2.name AS receiver_name
            FROM messages m
            JOIN users u1 ON m.sender_id = u1.id
            JOIN users u2 ON m.receiver_id = u2.id
            WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at ASC
        ');
        $stmt->execute([$userId1, $userId2, $userId2, $userId1]);
        return $stmt->fetchAll();
    }

    public static function send(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO messages (sender_id, receiver_id, subject, body) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['sender_id'], $data['receiver_id'], $data['subject'], $data['body']]);
        return (int) $db->lastInsertId();
    }

    public static function markAsRead(int $id): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE messages SET is_read = 1 WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function countUnread(int $userId): int
    {
        $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public static function getConversationPartners(int $userId): array
    {
        $stmt = Database::getInstance()->prepare('
            SELECT DISTINCT u.id, u.name, u.role
            FROM messages m
            JOIN users u ON (CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END) = u.id
            WHERE m.sender_id = ? OR m.receiver_id = ?
        ');
        $stmt->execute([$userId, $userId, $userId]);
        return $stmt->fetchAll();
    }
}
```

- [ ] **Step 4: Create `app/Models/QuizQuestion.php`**

```php
<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class QuizQuestion
{
    public static function getAllActive(): array
    {
        $stmt = Database::getInstance()->prepare('
            SELECT q.*, GROUP_CONCAT(
                JSON_OBJECT("id", o.id, "option_text", o.option_text, "weight", o.weight, "order_index", o.order_index)
                ORDER BY o.order_index ASC SEPARATOR "|||"
            ) AS options_json
            FROM quiz_questions q
            LEFT JOIN quiz_options o ON q.id = o.question_id
            WHERE q.is_active = 1
            GROUP BY q.id
            ORDER BY q.order_index
        ');
        $stmt->execute();
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $options = [];
            foreach (explode('|||', $row['options_json']) as $json) {
                $opt = json_decode($json, true);
                if ($opt) $options[] = $opt;
            }
            $row['options'] = $options;
            unset($row['options_json']);
        }
        return $rows;
    }

    public static function getAll(): array
    {
        $stmt = Database::getInstance()->query('
            SELECT q.*,
                (SELECT COUNT(*) FROM quiz_options WHERE question_id = q.id) AS option_count
            FROM quiz_questions q ORDER BY q.order_index
        ');
        return $stmt ? $stmt->fetchAll() : [];
    }

    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM quiz_questions WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO quiz_questions (question_text, category, order_index) VALUES (?, ?, ?)');
        $stmt->execute([$data['question_text'], $data['category'], $data['order_index']]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE quiz_questions SET question_text = ?, category = ?, order_index = ?, is_active = ? WHERE id = ?');
        $stmt->execute([$data['question_text'], $data['category'], $data['order_index'], $data['is_active'] ?? 1, $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM quiz_questions WHERE id = ?');
        $stmt->execute([$id]);
    }
}
```

- [ ] **Step 5: Create `app/Models/QuizAttempt.php`**

```php
<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class QuizAttempt
{
    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM quiz_attempts WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create(int $childId): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO quiz_attempts (child_id) VALUES (?)');
        $stmt->execute([$childId]);
        return (int) $db->lastInsertId();
    }

    public static function complete(int $id, int $totalScore, string $riskLevel): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE quiz_attempts SET total_score = ?, risk_level = ?, status = "completed", completed_at = NOW() WHERE id = ?');
        $stmt->execute([$totalScore, $riskLevel, $id]);
    }

    public static function getByChild(int $childId): array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM quiz_attempts WHERE child_id = ? AND status = "completed" ORDER BY completed_at DESC');
        $stmt->execute([$childId]);
        return $stmt->fetchAll();
    }

    public static function getLatestByChild(int $childId): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM quiz_attempts WHERE child_id = ? AND status = "completed" ORDER BY completed_at DESC LIMIT 1');
        $stmt->execute([$childId]);
        return $stmt->fetch();
    }

    public static function getIncomplete(int $childId): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM quiz_attempts WHERE child_id = ? AND status = "in_progress" ORDER BY started_at DESC LIMIT 1');
        $stmt->execute([$childId]);
        return $stmt->fetch();
    }

    public static function countByChild(int $childId): int
    {
        $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM quiz_attempts WHERE child_id = ? AND status = "completed"');
        $stmt->execute([$childId]);
        return (int) $stmt->fetchColumn();
    }

    public static function getAll(): array
    {
        $stmt = Database::getInstance()->query('
            SELECT qa.*, c.name AS child_name, u.name AS parent_name
            FROM quiz_attempts qa
            JOIN children c ON qa.child_id = c.id
            JOIN users u ON c.parent_id = u.id
            ORDER BY qa.started_at DESC
        ');
        return $stmt ? $stmt->fetchAll() : [];
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM quiz_attempts WHERE id = ?');
        $stmt->execute([$id]);
    }
}
```

- [ ] **Step 6: Create `app/Models/Activity.php`**

```php
<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Activity
{
    public static function findById(int $id): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM activities WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getAllActive(): array
    {
        $stmt = Database::getInstance()->query('SELECT * FROM activities WHERE is_active = 1 ORDER BY category, title');
        return $stmt ? $stmt->fetchAll() : [];
    }

    public static function getAll(): array
    {
        $stmt = Database::getInstance()->query('SELECT * FROM activities ORDER BY category, title');
        return $stmt ? $stmt->fetchAll() : [];
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO activities (title, description, category, difficulty, image_url) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$data['title'], $data['description'] ?? null, $data['category'], $data['difficulty'], $data['image_url'] ?? null]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE activities SET title = ?, description = ?, category = ?, difficulty = ?, image_url = ?, is_active = ? WHERE id = ?');
        $stmt->execute([$data['title'], $data['description'] ?? null, $data['category'], $data['difficulty'], $data['image_url'] ?? null, $data['is_active'] ?? 1, $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM activities WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function count(): int
    {
        $stmt = Database::getInstance()->query('SELECT COUNT(*) FROM activities');
        return (int) $stmt->fetchColumn();
    }
}
```

- [ ] **Step 7: Create `app/Models/Subscription.php`**

```php
<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Subscription
{
    public static function getByUser(int $userId): array|false
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM subscriptions WHERE user_id = ? AND status = "active" ORDER BY started_at DESC LIMIT 1');
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public static function getAll(): array
    {
        $stmt = Database::getInstance()->query('
            SELECT s.*, u.name AS user_name, u.email AS user_email
            FROM subscriptions s
            JOIN users u ON s.user_id = u.id
            ORDER BY s.started_at DESC
        ');
        return $stmt ? $stmt->fetchAll() : [];
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO subscriptions (user_id, plan, status, ends_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['user_id'], $data['plan'], $data['status'] ?? 'active', $data['ends_at'] ?? null]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::getInstance()->prepare('UPDATE subscriptions SET plan = ?, status = ?, ends_at = ? WHERE id = ?');
        $stmt->execute([$data['plan'], $data['status'], $data['ends_at'] ?? null, $id]);
    }

    public static function count(): int
    {
        $stmt = Database::getInstance()->query('SELECT COUNT(*) FROM subscriptions');
        return (int) $stmt->fetchColumn();
    }

    public static function countActive(): int
    {
        $stmt = Database::getInstance()->query('SELECT COUNT(*) FROM subscriptions WHERE status = "active"');
        return (int) $stmt->fetchColumn();
    }
}
```

---

### Task 2: Create Services (QuizScoring, Chatbot, Insights)

**Files:**
- Create: `app/Services/QuizScoringService.php`
- Create: `app/Services/ChatbotService.php`
- Create: `app/Services/InsightService.php`

- [ ] **Step 1: Create `app/Services/QuizScoringService.php`**

```php
<?php
namespace App\Services;

use App\Core\Database;
use App\Models\QuizAttempt;
use PDO;

class QuizScoringService
{
    private array $categoryMap = [
        'social_communication' => ['min' => 0, 'max' => 15],
        'behavior' => ['min' => 0, 'max' => 15],
        'sensory' => ['min' => 0, 'max' => 10],
        'developmental' => ['min' => 0, 'max' => 10],
    ];

    public function calculateScore(array $answers): array
    {
        $totalScore = 0;
        $categoryScores = [
            'social_communication' => 0,
            'behavior' => 0,
            'sensory' => 0,
            'developmental' => 0,
        ];
        $categoryMax = [
            'social_communication' => 15,
            'behavior' => 15,
            'sensory' => 10,
            'developmental' => 10,
        ];

        $db = Database::getInstance();

        foreach ($answers as $questionId => $optionId) {
            $stmt = $db->prepare('
                SELECT qo.weight, qq.category
                FROM quiz_options qo
                JOIN quiz_questions qq ON qo.question_id = qq.id
                WHERE qo.id = ?
            ');
            $stmt->execute([$optionId]);
            $result = $stmt->fetch();

            if ($result) {
                $weight = (int) $result['weight'];
                $category = $result['category'];
                $totalScore += $weight;
                $categoryScores[$category] += $weight;
            }
        }

        $categoryPercentages = [];
        foreach ($categoryScores as $cat => $score) {
            $categoryPercentages[$cat] = $categoryMax[$cat] > 0
                ? round(($score / $categoryMax[$cat]) * 100, 1)
                : 0;
        }

        $riskLevel = $this->mapRiskLevel($totalScore);

        return [
            'total_score' => $totalScore,
            'max_score' => 50,
            'risk_level' => $riskLevel,
            'category_scores' => $categoryScores,
            'category_percentages' => $categoryPercentages,
            'category_max' => $categoryMax,
        ];
    }

    public function mapRiskLevel(int $score): string
    {
        if ($score <= 15) return 'low';
        if ($score <= 30) return 'moderate';
        return 'high';
    }

    public function saveAttempt(int $attemptId, array $answers): array
    {
        $result = $this->calculateScore($answers);

        $db = Database::getInstance();

        foreach ($answers as $questionId => $optionId) {
            $stmt = $db->prepare('INSERT INTO quiz_answers (attempt_id, question_id, option_id) VALUES (?, ?, ?)');
            $stmt->execute([$attemptId, (int)$questionId, (int)$optionId]);
        }

        QuizAttempt::complete($attemptId, $result['total_score'], $result['risk_level']);

        return $result;
    }
}
```

- [ ] **Step 2: Create `app/Services/ChatbotService.php`**

```php
<?php
namespace App\Services;

use App\Core\Database;
use PDO;

class ChatbotService
{
    public function getResponse(string $message, int $userId): string
    {
        $this->saveMessage($userId, $message, 'user');

        $response = $this->findMatch($message);

        if (!$response) {
            $response = "I'm not sure I understand. Could you rephrase that? You can ask me about autism, screening, progress tracking, appointments, messaging, or our pricing plans.";
        }

        $this->saveMessage($userId, $response, 'bot');

        return $response;
    }

    private function findMatch(string $message): ?string
    {
        $db = Database::getInstance();
        $stmt = $db->query('SELECT * FROM chatbot_responses WHERE is_active = 1');
        $responses = $stmt ? $stmt->fetchAll() : [];

        $messageLower = mb_strtolower($message);

        $bestMatch = null;
        $bestScore = 0;

        foreach ($responses as $response) {
            $keywords = json_decode($response['keywords'], true);
            if (!is_array($keywords)) continue;

            $score = 0;
            foreach ($keywords as $keyword) {
                $keyword = mb_strtolower(trim($keyword));
                if (str_contains($messageLower, $keyword)) {
                    $score++;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $response['response_text'];
            }
        }

        return $bestMatch;
    }

    private function saveMessage(int $userId, string $message, string $sender): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO chat_history (user_id, message, sender) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $message, $sender]);
    }

    public function getHistory(int $userId, int $limit = 50): array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM chat_history WHERE user_id = ? ORDER BY created_at DESC LIMIT ?');
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
}
```

- [ ] **Step 3: Create `app/Services/InsightService.php`**

```php
<?php
namespace App\Services;

use App\Core\Database;
use PDO;

class InsightService
{
    public function getInsights(int $childId): array
    {
        $db = Database::getInstance();

        $stmt = $db->prepare('
            SELECT cp.score, a.category, a.title
            FROM child_progress cp
            JOIN activities a ON cp.activity_id = a.id
            WHERE cp.child_id = ?
            ORDER BY cp.score DESC
            LIMIT 5
        ');
        $stmt->execute([$childId]);
        $progressData = $stmt->fetchAll();

        $strength = $this->generateStrengthInsight($progressData);

        $stmt = $db->prepare('
            SELECT total_score, risk_level
            FROM quiz_attempts
            WHERE child_id = ? AND status = "completed"
            ORDER BY completed_at DESC
            LIMIT 1
        ');
        $stmt->execute([$childId]);
        $latestQuiz = $stmt->fetch();

        $recommendation = $this->generateRecommendationInsight($latestQuiz);

        return array_filter([$strength, $recommendation]);
    }

    private function generateStrengthInsight(array $progressData): ?array
    {
        if (empty($progressData)) {
            return [
                'type' => 'strength',
                'title' => 'Getting Started',
                'description' => 'Complete some activities with your child to unlock personalized insights about their strengths and progress.',
                'icon' => 'fa-star',
            ];
        }

        $bestCategory = $progressData[0]['category'] ?? 'activities';
        $categoryLabels = [
            'games' => 'interactive games',
            'puzzles' => 'puzzle solving',
            'stories' => 'story comprehension',
            'video' => 'visual learning',
            'coloring' => 'creative activities',
        ];

        return [
            'type' => 'strength',
            'title' => 'Strength: ' . ($categoryLabels[$bestCategory] ?? $bestCategory),
            'description' => 'Your child shows strong engagement in ' . ($categoryLabels[$bestCategory] ?? $bestCategory) . '. Continue to encourage this area of interest.',
            'icon' => 'fa-trophy',
        ];
    }

    private function generateRecommendationInsight(array|false $latestQuiz): ?array
    {
        if (!$latestQuiz) {
            return [
                'type' => 'recommendation',
                'title' => 'Complete a Screening',
                'description' => 'Take our developmental screening quiz to get personalized recommendations for your child\'s needs.',
                'icon' => 'fa-lightbulb',
            ];
        }

        $riskLabels = [
            'low' => 'maintain current supportive strategies and continue monitoring development',
            'moderate' => 'consult with a specialist for a comprehensive evaluation',
            'high' => 'seek professional guidance as soon as possible for early intervention',
        ];

        return [
            'type' => 'recommendation',
            'title' => 'Recommendation',
            'description' => 'Based on the latest screening, we recommend you ' . ($riskLabels[$latestQuiz['risk_level']] ?? 'continue monitoring your child\'s development'),
            'icon' => 'fa-lightbulb',
        ];
    }
}
```

---

### Task 3: Add Parent Dashboard Routes

**Files:**
- Modify: `routes/web.php` (add all parent routes)

- [ ] **Step 1: Add all parent routes after existing parent route**

Add these lines before the specialist dashboard route:

```php
// Parent dashboard routes
$router->get('/parent/dashboard', 'ParentController@dashboard', ['auth', 'role:parent']);
$router->get('/parent/children', 'ParentController@children', ['auth', 'role:parent']);
$router->get('/parent/children/add', 'ParentController@addChildForm', ['auth', 'role:parent']);
$router->post('/parent/children/add', 'ParentController@addChild', ['auth', 'role:parent']);
$router->get('/parent/children/{id}/edit', 'ParentController@editChildForm', ['auth', 'role:parent']);
$router->post('/parent/children/{id}/edit', 'ParentController@editChild', ['auth', 'role:parent']);
$router->post('/parent/children/{id}/delete', 'ParentController@deleteChild', ['auth', 'role:parent']);
$router->get('/parent/quiz', 'ParentController@quizList', ['auth', 'role:parent']);
$router->get('/parent/quiz/start/{childId}', 'ParentController@quizStart', ['auth', 'role:parent']);
$router->post('/parent/quiz/submit', 'ParentController@quizSubmit', ['auth', 'role:parent']);
$router->get('/parent/quiz/results/{attemptId}', 'ParentController@quizResults', ['auth', 'role:parent']);
$router->get('/parent/progress', 'ParentController@progress', ['auth', 'role:parent']);
$router->get('/parent/specialists', 'ParentController@specialists', ['auth', 'role:parent']);
$router->get('/parent/appointments', 'ParentController@appointments', ['auth', 'role:parent']);
$router->get('/parent/appointments/book', 'ParentController@bookAppointmentForm', ['auth', 'role:parent']);
$router->post('/parent/appointments/book', 'ParentController@bookAppointment', ['auth', 'role:parent']);
$router->post('/parent/appointments/{id}/cancel', 'ParentController@cancelAppointment', ['auth', 'role:parent']);
$router->get('/parent/messages', 'ParentController@messages', ['auth', 'role:parent']);
$router->get('/parent/messages/send/{receiverId}', 'ParentController@sendMessageForm', ['auth', 'role:parent']);
$router->post('/parent/messages/send', 'ParentController@sendMessage', ['auth', 'role:parent']);
$router->get('/parent/messages/thread/{partnerId}', 'ParentController@messageThread', ['auth', 'role:parent']);
$router->post('/parent/messages/reply', 'ParentController@replyMessage', ['auth', 'role:parent']);
$router->get('/parent/chatbot', 'ParentController@chatbot', ['auth', 'role:parent']);
$router->post('/parent/chatbot/message', 'ParentController@chatbotMessage', ['auth', 'role:parent']);
$router->get('/parent/settings', 'ParentController@settings', ['auth', 'role:parent']);
$router->post('/parent/settings', 'ParentController@updateSettings', ['auth', 'role:parent']);

// Specialist dashboard routes (already present)
```

---

### Task 4: Build ParentController (all functionality)

**Files:**
- Modify: `app/Controllers/ParentController.php` (replace stub with full implementation)

- [ ] **Step 1: Replace ParentController with full implementation**

```php
<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Core\Validator;
use App\Core\Database;
use App\Models\User;
use App\Models\Child;
use App\Models\Appointment;
use App\Models\Message;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use App\Services\QuizScoringService;
use App\Services\ChatbotService;
use App\Services\InsightService;
use PDO;

class ParentController
{
    private function getParentId(): int
    {
        return (int) Session::get('user_id');
    }

    private function getChildren(): array
    {
        return Child::getAllByParent($this->getParentId());
    }

    // Dashboard
    public function dashboard(): void
    {
        $parentId = $this->getParentId();
        $children = $this->getChildren();
        $upcomingAppointments = Appointment::getUpcomingByParent($parentId);
        $unreadMessages = Message::countUnread($parentId);
        $childCount = Child::countByParent($parentId);

        $insights = [];
        $latestQuiz = null;
        if (!empty($children)) {
            $insightService = new InsightService();
            $insights = $insightService->getInsights($children[0]['id']);
            $latestQuiz = QuizAttempt::getLatestByChild($children[0]['id']);
        }

        View::render('parent/dashboard', [
            'title' => 'Parent Dashboard',
            'children' => $children,
            'upcomingAppointments' => $upcomingAppointments,
            'unreadMessages' => $unreadMessages,
            'childCount' => $childCount,
            'insights' => $insights,
            'latestQuiz' => $latestQuiz,
        ], 'dashboard');
    }

    // Children CRUD
    public function children(): void
    {
        $children = $this->getChildren();
        View::render('parent/children', ['title' => 'My Children', 'children' => $children], 'dashboard');
    }

    public function addChildForm(): void
    {
        View::render('parent/children-add', ['title' => 'Add Child', 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function addChild(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, [
            'name' => 'required|max:255',
            'age' => 'numeric',
            'birth_date' => '',
            'diagnosis_status' => 'max:100',
            'notes' => '',
        ])) {
            View::render('parent/children-add', ['errors' => $validator->errors(), 'old' => $_POST, 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        Child::create([
            'parent_id' => $this->getParentId(),
            'name' => $_POST['name'],
            'age' => $_POST['age'] ?? null,
            'birth_date' => $_POST['birth_date'] ?? null,
            'diagnosis_status' => $_POST['diagnosis_status'] ?? null,
            'notes' => $_POST['notes'] ?? null,
        ]);

        Session::setFlash('success', 'Child added successfully.');
        header('Location: /parent/children');
        exit;
    }

    public function editChildForm(int $id): void
    {
        if (!Child::belongsToParent($id, $this->getParentId())) {
            http_response_code(403);
            View::render('errors/403', [], 'dashboard');
            return;
        }
        $child = Child::findById($id);
        View::render('parent/children-edit', ['title' => 'Edit Child', 'child' => $child, 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function editChild(int $id): void
    {
        if (!Child::belongsToParent($id, $this->getParentId())) {
            http_response_code(403);
            View::render('errors/403', [], 'dashboard');
            return;
        }

        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, [
            'name' => 'required|max:255',
            'age' => 'numeric',
            'birth_date' => '',
            'diagnosis_status' => 'max:100',
            'notes' => '',
        ])) {
            $child = Child::findById($id);
            View::render('parent/children-edit', ['errors' => $validator->errors(), 'child' => $child, 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        Child::update($id, [
            'name' => $_POST['name'],
            'age' => $_POST['age'] ?? null,
            'birth_date' => $_POST['birth_date'] ?? null,
            'diagnosis_status' => $_POST['diagnosis_status'] ?? null,
            'notes' => $_POST['notes'] ?? null,
        ]);

        Session::setFlash('success', 'Child updated successfully.');
        header('Location: /parent/children');
        exit;
    }

    public function deleteChild(int $id): void
    {
        if (!Child::belongsToParent($id, $this->getParentId())) {
            http_response_code(403);
            View::render('errors/403', [], 'dashboard');
            return;
        }

        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        Child::delete($id);
        Session::setFlash('success', 'Child removed successfully.');
        header('Location: /parent/children');
        exit;
    }

    // Quiz
    public function quizList(): void
    {
        $children = $this->getChildren();
        $quizData = [];
        foreach ($children as $child) {
            $attempts = QuizAttempt::getByChild($child['id']);
            $latest = QuizAttempt::getLatestByChild($child['id']);
            $quizData[] = [
                'child' => $child,
                'attempts' => $attempts,
                'latest' => $latest,
                'attemptCount' => count($attempts),
            ];
        }
        View::render('parent/quiz-list', ['title' => 'Screening Quiz', 'quizData' => $quizData, 'children' => $children], 'dashboard');
    }

    public function quizStart(int $childId): void
    {
        if (!Child::belongsToParent($childId, $this->getParentId())) {
            http_response_code(403);
            View::render('errors/403', [], 'dashboard');
            return;
        }

        $existing = QuizAttempt::getIncomplete($childId);
        if ($existing) {
            header('Location: /parent/quiz/results/' . $existing['id']);
            exit;
        }

        $attemptId = QuizAttempt::create($childId);
        $questions = QuizQuestion::getAllActive();
        $child = Child::findById($childId);

        View::render('parent/quiz-take', [
            'title' => 'Take Quiz',
            'attemptId' => $attemptId,
            'questions' => $questions,
            'child' => $child,
            'csrf_token' => Session::csrf_token(),
        ], 'dashboard');
    }

    public function quizSubmit(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $attemptId = (int) ($_POST['attempt_id'] ?? 0);
        $attempt = QuizAttempt::findById($attemptId);

        if (!$attempt || $attempt['status'] === 'completed') {
            Session::setFlash('error', 'Invalid or already completed attempt.');
            header('Location: /parent/quiz');
            exit;
        }

        $answers = $_POST['answers'] ?? [];
        if (empty($answers)) {
            Session::setFlash('error', 'Please answer all questions.');
            header('Location: /parent/quiz/start/' . $attempt['child_id']);
            exit;
        }

        $scoringService = new QuizScoringService();
        $result = $scoringService->saveAttempt($attemptId, $answers);

        header('Location: /parent/quiz/results/' . $attemptId);
        exit;
    }

    public function quizResults(int $attemptId): void
    {
        $attempt = QuizAttempt::findById($attemptId);
        if (!$attempt) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }

        $child = Child::findById($attempt['child_id']);
        if (!$child || $child['parent_id'] !== $this->getParentId()) {
            http_response_code(403);
            View::render('errors/403', [], 'dashboard');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT qa.*, qo.weight, qq.question_text, qq.category, qo.option_text
            FROM quiz_answers qa
            JOIN quiz_options qo ON qa.option_id = qo.id
            JOIN quiz_questions qq ON qa.question_id = qq.id
            WHERE qa.attempt_id = ?
            ORDER BY qq.order_index
        ');
        $stmt->execute([$attemptId]);
        $answers = $stmt->fetchAll();

        $scoringService = new QuizScoringService();
        $result = $scoringService->calculateScore(
            array_combine(
                array_column($answers, 'question_id'),
                array_column($answers, 'option_id')
            )
        );

        $previousAttempts = QuizAttempt::getByChild($child['id']);

        View::render('parent/quiz-results', [
            'title' => 'Quiz Results',
            'attempt' => $attempt,
            'child' => $child,
            'answers' => $answers,
            'result' => $result,
            'previousAttempts' => $previousAttempts,
        ], 'dashboard');
    }

    // Progress
    public function progress(): void
    {
        $children = $this->getChildren();

        $childId = (int) ($_GET['child_id'] ?? ($children[0]['id'] ?? 0));
        if ($childId && !Child::belongsToParent($childId, $this->getParentId())) {
            $childId = $children[0]['id'] ?? 0;
        }

        $progressData = [];
        $quizHistory = [];
        if ($childId) {
            $db = Database::getInstance();
            $stmt = $db->prepare('
                SELECT cp.*, a.title, a.category, a.difficulty
                FROM child_progress cp
                JOIN activities a ON cp.activity_id = a.id
                WHERE cp.child_id = ?
                ORDER BY cp.completed_at DESC
                LIMIT 20
            ');
            $stmt->execute([$childId]);
            $progressData = $stmt->fetchAll();

            $quizHistory = QuizAttempt::getByChild($childId);
        }

        View::render('parent/progress', [
            'title' => 'Progress Tracking',
            'children' => $children,
            'selectedChildId' => $childId,
            'progressData' => $progressData,
            'quizHistory' => $quizHistory,
        ], 'dashboard');
    }

    // Specialists (browse)
    public function specialists(): void
    {
        $db = Database::getInstance();
        $stmt = $db->query('
            SELECT u.id, u.name, u.avatar, u.email, sd.title, sd.bio, sd.specializations, sd.years_experience
            FROM users u
            JOIN specialist_details sd ON u.id = sd.user_id
            WHERE u.role = "specialist" AND u.is_active = 1
        ');
        $specialists = $stmt ? $stmt->fetchAll() : [];

        View::render('parent/specialists', ['title' => 'Specialists', 'specialists' => $specialists], 'dashboard');
    }

    // Appointments
    public function appointments(): void
    {
        $appointments = Appointment::getAllByParent($this->getParentId());
        View::render('parent/appointments', ['title' => 'Appointments', 'appointments' => $appointments], 'dashboard');
    }

    public function bookAppointmentForm(): void
    {
        $children = $this->getChildren();
        $db = Database::getInstance();
        $stmt = $db->query('
            SELECT u.id, u.name, sd.title
            FROM users u
            JOIN specialist_details sd ON u.id = sd.user_id
            WHERE u.role = "specialist" AND u.is_active = 1
        ');
        $specialists = $stmt ? $stmt->fetchAll() : [];

        View::render('parent/appointments-book', [
            'title' => 'Book Appointment',
            'children' => $children,
            'specialists' => $specialists,
            'csrf_token' => Session::csrf_token(),
        ], 'dashboard');
    }

    public function bookAppointment(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, [
            'child_id' => 'required',
            'specialist_id' => 'required',
            'date' => 'required',
            'time' => 'required',
            'notes' => '',
        ])) {
            Session::setFlash('error', 'Please fill in all required fields.');
            header('Location: /parent/appointments/book');
            exit;
        }

        if (!Child::belongsToParent((int)$_POST['child_id'], $this->getParentId())) {
            http_response_code(403);
            View::render('errors/403', [], 'dashboard');
            return;
        }

        Appointment::create([
            'child_id' => (int)$_POST['child_id'],
            'specialist_id' => (int)$_POST['specialist_id'],
            'parent_id' => $this->getParentId(),
            'date' => $_POST['date'],
            'time' => $_POST['time'],
            'duration' => (int)($_POST['duration'] ?? 30),
            'notes' => $_POST['notes'] ?? null,
        ]);

        Session::setFlash('success', 'Appointment booked successfully.');
        header('Location: /parent/appointments');
        exit;
    }

    public function cancelAppointment(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $appointment = Appointment::findById($id);
        if (!$appointment || $appointment['parent_id'] !== $this->getParentId()) {
            http_response_code(403);
            View::render('errors/403', [], 'dashboard');
            return;
        }

        Appointment::updateStatus($id, 'cancelled');
        Session::setFlash('success', 'Appointment cancelled.');
        header('Location: /parent/appointments');
        exit;
    }

    // Messages
    public function messages(): void
    {
        $inbox = Message::getInbox($this->getParentId());
        $sent = Message::getSent($this->getParentId());
        View::render('parent/messages', ['title' => 'Messages', 'inbox' => $inbox, 'sent' => $sent], 'dashboard');
    }

    public function sendMessageForm(int $receiverId): void
    {
        $receiver = User::findById($receiverId);
        if (!$receiver || !$receiver['is_active']) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }
        View::render('parent/messages-send', [
            'title' => 'Send Message',
            'receiver' => $receiver,
            'csrf_token' => Session::csrf_token(),
        ], 'dashboard');
    }

    public function sendMessage(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, [
            'receiver_id' => 'required',
            'subject' => 'required|max:255',
            'body' => 'required',
        ])) {
            Session::setFlash('error', 'Please fill in all required fields.');
            header('Location: /parent/messages');
            exit;
        }

        Message::send([
            'sender_id' => $this->getParentId(),
            'receiver_id' => (int)$_POST['receiver_id'],
            'subject' => $_POST['subject'],
            'body' => $_POST['body'],
        ]);

        Session::setFlash('success', 'Message sent successfully.');
        header('Location: /parent/messages');
        exit;
    }

    public function messageThread(int $partnerId): void
    {
        $partner = User::findById($partnerId);
        if (!$partner) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }

        $thread = Message::getThread($this->getParentId(), $partnerId);

        foreach ($thread as $msg) {
            if ($msg['receiver_id'] === $this->getParentId() && !$msg['is_read']) {
                Message::markAsRead($msg['id']);
            }
        }

        View::render('parent/messages-thread', [
            'title' => 'Messages',
            'partner' => $partner,
            'thread' => $thread,
            'csrf_token' => Session::csrf_token(),
        ], 'dashboard');
    }

    public function replyMessage(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $receiverId = (int)($_POST['receiver_id'] ?? 0);
        $body = $_POST['body'] ?? '';

        if (empty($body) || !$receiverId) {
            Session::setFlash('error', 'Message cannot be empty.');
            header('Location: /parent/messages');
            exit;
        }

        Message::send([
            'sender_id' => $this->getParentId(),
            'receiver_id' => $receiverId,
            'subject' => $_POST['subject'] ?? 'Re: Message',
            'body' => $body,
        ]);

        Session::setFlash('success', 'Message sent.');
        header('Location: /parent/messages/thread/' . $receiverId);
        exit;
    }

    // Chatbot
    public function chatbot(): void
    {
        $chatbotService = new ChatbotService();
        $history = $chatbotService->getHistory($this->getParentId());

        View::render('parent/chatbot', [
            'title' => 'AI Chat',
            'history' => $history,
            'csrf_token' => Session::csrf_token(),
        ], 'dashboard');
    }

    public function chatbotMessage(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }

        $message = $_POST['message'] ?? '';
        if (empty($message)) {
            echo json_encode(['response' => 'Please enter a message.']);
            exit;
        }

        $chatbotService = new ChatbotService();
        $response = $chatbotService->getResponse($message, $this->getParentId());

        header('Content-Type: application/json');
        echo json_encode(['response' => $response]);
        exit;
    }

    // Settings
    public function settings(): void
    {
        $user = User::findById($this->getParentId());
        View::render('parent/settings', ['title' => 'Settings', 'user' => $user, 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function updateSettings(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, [
            'name' => 'required|max:255',
            'phone' => 'max:50',
        ])) {
            $user = User::findById($this->getParentId());
            View::render('parent/settings', ['errors' => $validator->errors(), 'user' => $user, 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?');
        $stmt->execute([$_POST['name'], $_POST['phone'] ?? null, $this->getParentId()]);
        Session::set('user_name', $_POST['name']);

        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 8) {
                Session::setFlash('error', 'Password must be at least 8 characters.');
                header('Location: /parent/settings');
                exit;
            }
            $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([password_hash($_POST['password'], PASSWORD_BCRYPT), $this->getParentId()]);
        }

        Session::setFlash('success', 'Settings updated successfully.');
        header('Location: /parent/settings');
        exit;
    }
}
```

---

### Task 5: Create Parent Dashboard Views

**Files to create:**
- `app/Views/parent/dashboard.php` (overwrite existing stub)
- `app/Views/parent/children.php`
- `app/Views/parent/children-add.php`
- `app/Views/parent/children-edit.php`
- `app/Views/parent/quiz-list.php`
- `app/Views/parent/quiz-take.php`
- `app/Views/parent/quiz-results.php`
- `app/Views/parent/progress.php`
- `app/Views/parent/specialists.php`
- `app/Views/parent/appointments.php`
- `app/Views/parent/appointments-book.php`
- `app/Views/parent/messages.php`
- `app/Views/parent/messages-send.php`
- `app/Views/parent/messages-thread.php`
- `app/Views/parent/chatbot.php`
- `app/Views/parent/settings.php`

- [ ] **Step 1: Create `app/Views/parent/dashboard.php`**

```php
<div class="dash-header">
  <div>
    <h1>Parent Dashboard</h1>
    <p>Welcome back, <?= htmlspecialchars(\App\Core\Session::get('user_name')) ?>!</p>
  </div>
</div>

<?php if (!empty($children)): ?>
<div class="dash-grid">
  <div class="dash-card">
    <div class="dash-card-header">
      <h3><i class="fas fa-child"></i> Children</h3>
      <span class="dash-badge"><?= count($children) ?></span>
    </div>
    <ul class="child-list-compact">
      <?php foreach ($children as $child): ?>
        <li>
          <span class="child-avatar-sm"><?= strtoupper(substr(htmlspecialchars($child['name']), 0, 1)) ?></span>
          <span><?= htmlspecialchars($child['name']) ?></span>
          <?php if ($child['age']): ?><small>(<?= (int)$child['age'] ?> yrs)</small><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
    <a href="/parent/children" class="dash-link">Manage Children →</a>
  </div>

  <div class="dash-card">
    <div class="dash-card-header">
      <h3><i class="fas fa-calendar-check"></i> Upcoming Appointments</h3>
      <span class="dash-badge"><?= count($upcomingAppointments) ?></span>
    </div>
    <?php if (!empty($upcomingAppointments)): ?>
      <ul class="appointment-list-compact">
        <?php foreach ($upcomingAppointments as $apt): ?>
          <li>
            <strong><?= htmlspecialchars($apt['date']) ?></strong> at <?= htmlspecialchars(substr($apt['time'], 0, 5)) ?>
            <br><small><?= htmlspecialchars($apt['child_name']) ?> with <?= htmlspecialchars($apt['specialist_name']) ?></small>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="dash-empty">No upcoming appointments.</p>
    <?php endif; ?>
    <a href="/parent/appointments" class="dash-link">View All →</a>
  </div>

  <div class="dash-card">
    <div class="dash-card-header">
      <h3><i class="fas fa-envelope"></i> Messages</h3>
      <?php if ($unreadMessages > 0): ?>
        <span class="dash-badge dash-badge-warning"><?= $unreadMessages ?> unread</span>
      <?php endif; ?>
    </div>
    <p><?= $unreadMessages > 0 ? 'You have ' . $unreadMessages . ' unread message(s).' : 'No unread messages.' ?></p>
    <a href="/parent/messages" class="dash-link">Go to Messages →</a>
  </div>

  <div class="dash-card">
    <div class="dash-card-header">
      <h3><i class="fas fa-clipboard-list"></i> Latest Screening</h3>
    </div>
    <?php if ($latestQuiz): ?>
      <p>Score: <strong><?= (int)$latestQuiz['total_score'] ?>/50</strong> · Risk: <span class="risk-<?= htmlspecialchars($latestQuiz['risk_level']) ?>"><?= ucfirst(htmlspecialchars($latestQuiz['risk_level'])) ?></span></p>
      <a href="/parent/quiz" class="dash-link">View Details →</a>
    <?php else: ?>
      <p class="dash-empty">No screening completed yet.</p>
      <a href="/parent/quiz" class="dash-link">Start Screening →</a>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($insights)): ?>
<div class="dash-section">
  <h2><i class="fas fa-lightbulb"></i> AI Insights</h2>
  <div class="dash-grid dash-grid-2">
    <?php foreach ($insights as $insight): ?>
      <div class="dash-card insight-card insight-<?= htmlspecialchars($insight['type']) ?>">
        <div class="insight-icon"><i class="fas <?= htmlspecialchars($insight['icon']) ?>"></i></div>
        <div class="insight-content">
          <h4><?= htmlspecialchars($insight['title']) ?></h4>
          <p><?= htmlspecialchars($insight['description']) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php else: ?>
<div class="dash-empty-state">
  <i class="fas fa-child" style="font-size: 3rem; color: var(--primary);"></i>
  <h2>Welcome to AutiMind!</h2>
  <p>Get started by adding your first child to begin tracking their progress.</p>
  <a href="/parent/children/add" class="btn btn-primary">Add Your First Child</a>
</div>
<?php endif; ?>
```

- [ ] **Step 2: Create `app/Views/parent/children.php`**

```php
<div class="dash-header">
  <div>
    <h1>My Children</h1>
    <p>Manage your children's profiles</p>
  </div>
  <a href="/parent/children/add" class="btn btn-primary"><i class="fas fa-plus"></i> Add Child</a>
</div>

<?php if (!empty($children)): ?>
<div class="table-responsive">
  <table class="dash-table">
    <thead>
      <tr>
        <th>Name</th>
        <th>Age</th>
        <th>Birth Date</th>
        <th>Diagnosis Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($children as $child): ?>
        <tr>
          <td><strong><?= htmlspecialchars($child['name']) ?></strong></td>
          <td><?= $child['age'] ? (int)$child['age'] . ' yrs' : '-' ?></td>
          <td><?= htmlspecialchars($child['birth_date'] ?? '-') ?></td>
          <td><?= htmlspecialchars($child['diagnosis_status'] ?? '-') ?></td>
          <td class="actions">
            <a href="/parent/children/<?= (int)$child['id'] ?>/edit" class="btn-sm btn-outline"><i class="fas fa-edit"></i></a>
            <form method="POST" action="/parent/children/<?= (int)$child['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Remove this child?')">
              <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
              <button type="submit" class="btn-sm btn-danger"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="dash-empty-state">
  <i class="fas fa-child" style="font-size: 3rem; color: var(--primary);"></i>
  <h3>No children added yet</h3>
  <p>Add your first child to start using the screening quiz and progress tracking.</p>
  <a href="/parent/children/add" class="btn btn-primary">Add Child</a>
</div>
<?php endif; ?>
```

- [ ] **Step 3: Create `app/Views/parent/children-add.php`**

```php
<div class="dash-header">
  <div>
    <h1>Add Child</h1>
    <p>Add a new child profile</p>
  </div>
</div>

<form method="POST" action="/parent/children/add" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  
  <div class="form-group">
    <label for="name">Child's Name *</label>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
    <?php if (!empty($errors['name'])): ?><span class="form-error"><?= htmlspecialchars($errors['name'][0]) ?></span><?php endif; ?>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label for="age">Age</label>
      <input type="number" id="age" name="age" min="0" max="18" value="<?= htmlspecialchars($old['age'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label for="birth_date">Birth Date</label>
      <input type="date" id="birth_date" name="birth_date" value="<?= htmlspecialchars($old['birth_date'] ?? '') ?>">
    </div>
  </div>

  <div class="form-group">
    <label for="diagnosis_status">Diagnosis Status</label>
    <input type="text" id="diagnosis_status" name="diagnosis_status" placeholder="e.g. ASD Level 1, Under evaluation..." value="<?= htmlspecialchars($old['diagnosis_status'] ?? '') ?>">
  </div>

  <div class="form-group">
    <label for="notes">Notes</label>
    <textarea id="notes" name="notes" rows="3"><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
  </div>

  <div class="form-actions">
    <a href="/parent/children" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Add Child</button>
  </div>
</form>
```

- [ ] **Step 4: Create `app/Views/parent/children-edit.php`**

```php
<div class="dash-header">
  <div>
    <h1>Edit Child</h1>
    <p>Update <?= htmlspecialchars($child['name']) ?>'s profile</p>
  </div>
</div>

<form method="POST" action="/parent/children/<?= (int)$child['id'] ?>/edit" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  
  <div class="form-group">
    <label for="name">Child's Name *</label>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? $child['name']) ?>" required>
    <?php if (!empty($errors['name'])): ?><span class="form-error"><?= htmlspecialchars($errors['name'][0]) ?></span><?php endif; ?>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label for="age">Age</label>
      <input type="number" id="age" name="age" min="0" max="18" value="<?= htmlspecialchars($old['age'] ?? $child['age']) ?>">
    </div>
    <div class="form-group">
      <label for="birth_date">Birth Date</label>
      <input type="date" id="birth_date" name="birth_date" value="<?= htmlspecialchars($old['birth_date'] ?? $child['birth_date']) ?>">
    </div>
  </div>

  <div class="form-group">
    <label for="diagnosis_status">Diagnosis Status</label>
    <input type="text" id="diagnosis_status" name="diagnosis_status" value="<?= htmlspecialchars($old['diagnosis_status'] ?? $child['diagnosis_status']) ?>">
  </div>

  <div class="form-group">
    <label for="notes">Notes</label>
    <textarea id="notes" name="notes" rows="3"><?= htmlspecialchars($old['notes'] ?? $child['notes']) ?></textarea>
  </div>

  <div class="form-actions">
    <a href="/parent/children" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Update Child</button>
  </div>
</form>
```

- [ ] **Step 5: Create `app/Views/parent/quiz-list.php`**

```php
<div class="dash-header">
  <div>
    <h1>Screening Quiz</h1>
    <p>Developmental screening for your children</p>
  </div>
</div>

<div class="dash-info-box">
  <i class="fas fa-info-circle"></i>
  <p>This screening quiz consists of <strong>10 questions</strong> across key developmental areas. It takes about 5-10 minutes to complete. Results are for informational purposes only and not a medical diagnosis.</p>
</div>

<?php if (!empty($quizData)): ?>
  <?php foreach ($quizData as $data): ?>
    <div class="dash-card mb-2">
      <div class="dash-card-header">
        <h3><i class="fas fa-child"></i> <?= htmlspecialchars($data['child']['name']) ?></h3>
        <?php if ($data['attemptCount'] > 0): ?>
          <span class="dash-badge"><?= $data['attemptCount'] ?> attempt(s)</span>
        <?php endif; ?>
      </div>
      
      <?php if ($data['latest']): ?>
        <p>Latest result: Score <strong><?= (int)$data['latest']['total_score'] ?>/50</strong> · 
        Risk: <span class="risk-<?= htmlspecialchars($data['latest']['risk_level']) ?>"><?= ucfirst(htmlspecialchars($data['latest']['risk_level'])) ?></span> · 
        <?= htmlspecialchars($data['latest']['completed_at']) ?></p>
        <div class="mt-1">
          <a href="/parent/quiz/results/<?= (int)$data['latest']['id'] ?>" class="btn btn-outline btn-sm">View Results</a>
          <a href="/parent/quiz/start/<?= (int)$data['child']['id'] ?>" class="btn btn-primary btn-sm">Take New Quiz</a>
        </div>
      <?php else: ?>
        <p class="dash-empty">No screening completed yet.</p>
        <a href="/parent/quiz/start/<?= (int)$data['child']['id'] ?>" class="btn btn-primary btn-sm">Start Screening</a>
      <?php endif; ?>
      
      <?php if (count($data['attempts']) > 1): ?>
        <details class="mt-1">
          <summary>View History (<?= count($data['attempts']) ?> attempts)</summary>
          <table class="dash-table mt-1">
            <thead>
              <tr><th>Date</th><th>Score</th><th>Risk Level</th><th></th></tr>
            </thead>
            <tbody>
              <?php foreach ($data['attempts'] as $attempt): ?>
                <tr>
                  <td><?= htmlspecialchars($attempt['completed_at']) ?></td>
                  <td><?= (int)$attempt['total_score'] ?>/50</td>
                  <td><span class="risk-<?= htmlspecialchars($attempt['risk_level']) ?>"><?= ucfirst(htmlspecialchars($attempt['risk_level'])) ?></span></td>
                  <td><a href="/parent/quiz/results/<?= (int)$attempt['id'] ?>">View</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </details>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php else: ?>
  <div class="dash-empty-state">
    <h3>No children added</h3>
    <p>Add a child first to take the screening quiz.</p>
    <a href="/parent/children/add" class="btn btn-primary">Add Child</a>
  </div>
<?php endif; ?>
```

- [ ] **Step 6: Create `app/Views/parent/quiz-take.php`**

```php
<div class="dash-header">
  <div>
    <h1>Screening Quiz</h1>
    <p>For: <?= htmlspecialchars($child['name']) ?></p>
  </div>
</div>

<div class="dash-info-box">
  <i class="fas fa-clock"></i>
  <p>Answer all 10 questions based on your observation of your child's typical behavior. There are no right or wrong answers.</p>
</div>

<form id="quizForm" method="POST" action="/parent/quiz/submit" class="quiz-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <input type="hidden" name="attempt_id" value="<?= (int)$attemptId ?>">

  <?php foreach ($questions as $index => $question): ?>
    <div class="dash-card quiz-question" data-question="<?= $index + 1 ?>">
      <div class="question-header">
        <span class="question-number">Q<?= $index + 1 ?></span>
        <span class="question-category"><?= ucwords(str_replace('_', ' ', htmlspecialchars($question['category']))) ?></span>
      </div>
      <p class="question-text"><?= htmlspecialchars($question['question_text']) ?></p>
      
      <div class="question-options">
        <?php foreach ($question['options'] as $option): ?>
          <label class="option-label">
            <input type="radio" name="answers[<?= (int)$question['id'] ?>]" value="<?= (int)$option['id'] ?>" required>
            <span class="option-text"><?= htmlspecialchars($option['option_text']) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="form-actions">
    <a href="/parent/quiz" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Submit Quiz</button>
  </div>
</form>

<script>
document.getElementById('quizForm')?.addEventListener('submit', function(e) {
  const total = document.querySelectorAll('.quiz-question').length;
  const answered = document.querySelectorAll('input[type="radio"]:checked').length;
  if (answered < total) {
    if (!confirm('You have answered ' + answered + ' of ' + total + ' questions. Submit anyway?')) {
      e.preventDefault();
    }
  }
});
</script>
```

- [ ] **Step 7: Create `app/Views/parent/quiz-results.php`**

```php
<div class="dash-header">
  <div>
    <h1>Quiz Results</h1>
    <p>For: <?= htmlspecialchars($child['name']) ?> · <?= htmlspecialchars($attempt['completed_at'] ?? 'In progress') ?></p>
  </div>
</div>

<?php
$riskColors = ['low' => '#22c55e', 'moderate' => '#f59e0b', 'high' => '#ef4444'];
$riskColor = $riskColors[$result['risk_level']] ?? '#6b7280';
?>

<div class="dash-grid dash-grid-2 mb-2">
  <div class="dash-card result-summary" style="border-left: 4px solid <?= $riskColor ?>;">
    <h2>Total Score: <?= (int)$result['total_score'] ?>/50</h2>
    <p class="risk-badge risk-<?= htmlspecialchars($result['risk_level']) ?>">Risk Level: <?= ucfirst(htmlspecialchars($result['risk_level'])) ?></p>
    <div class="score-bar">
      <div class="score-bar-fill" style="width: <?= min(100, ((int)$result['total_score'] / 50) * 100) ?>%; background: <?= $riskColor ?>;"></div>
    </div>
  </div>

  <div class="dash-card">
    <h3>Category Breakdown</h3>
    <div class="category-scores">
      <?php foreach ($result['category_scores'] as $cat => $score): ?>
        <div class="category-item">
          <span class="category-label"><?= ucwords(str_replace('_', ' ', htmlspecialchars($cat))) ?></span>
          <span class="category-score"><?= (int)$score ?>/<?= (int)$result['category_max'][$cat] ?> (<?= (float)$result['category_percentages'][$cat] ?>%)</span>
          <div class="score-bar-sm">
            <div class="score-bar-fill" style="width: <?= min(100, (float)$result['category_percentages'][$cat]) ?>%; background: <?= $riskColor ?>;"></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="dash-card mb-2">
  <h3>Answer Details</h3>
  <table class="dash-table">
    <thead><tr><th>#</th><th>Question</th><th>Category</th><th>Your Answer</th><th>Weight</th></tr></thead>
    <tbody>
      <?php foreach ($answers as $ans): ?>
        <tr>
          <td><?= (int)$ans['weight'] >= 3 ? '<i class="fas fa-circle" style="color: var(--warning); font-size: 0.6rem;"></i>' : '' ?></td>
          <td><?= htmlspecialchars($ans['question_text']) ?></td>
          <td><?= ucwords(str_replace('_', ' ', htmlspecialchars($ans['category']))) ?></td>
          <td><?= htmlspecialchars($ans['option_text']) ?></td>
          <td><?= (int)$ans['weight'] ?>/5</td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if (count($previousAttempts) > 1): ?>
<div class="dash-card">
  <h3>Score History</h3>
  <table class="dash-table">
    <thead><tr><th>Date</th><th>Score</th><th>Risk Level</th></tr></thead>
    <tbody>
      <?php foreach ($previousAttempts as $pa): ?>
        <tr>
          <td><?= htmlspecialchars($pa['completed_at']) ?></td>
          <td><?= (int)$pa['total_score'] ?>/50</td>
          <td><span class="risk-<?= htmlspecialchars($pa['risk_level']) ?>"><?= ucfirst(htmlspecialchars($pa['risk_level'])) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<div class="form-actions mt-2">
  <a href="/parent/quiz" class="btn btn-outline">Back to Quiz</a>
  <a href="/parent/quiz/start/<?= (int)$child['id'] ?>" class="btn btn-primary">Take Another Quiz</a>
</div>
```

- [ ] **Step 8: Create `app/Views/parent/progress.php`**

```php
<div class="dash-header">
  <div>
    <h1>Progress Tracking</h1>
    <p>Monitor your child's development</p>
  </div>
</div>

<?php if (!empty($children)): ?>
<div class="dash-card mb-2">
  <div class="form-group">
    <label for="child-select">Select Child:</label>
    <select id="child-select" onchange="window.location.href='/parent/progress?child_id=' + this.value">
      <?php foreach ($children as $child): ?>
        <option value="<?= (int)$child['id'] ?>" <?= $child['id'] === $selectedChildId ? 'selected' : '' ?>>
          <?= htmlspecialchars($child['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<?php if ($selectedChildId): ?>
  <div class="dash-grid dash-grid-2 mb-2">
    <div class="dash-card">
      <h3><i class="fas fa-clipboard-list"></i> Quiz History</h3>
      <?php if (!empty($quizHistory)): ?>
        <table class="dash-table">
          <thead><tr><th>Date</th><th>Score</th><th>Risk</th></tr></thead>
          <tbody>
            <?php foreach ($quizHistory as $qh): ?>
              <tr>
                <td><?= htmlspecialchars($qh['completed_at']) ?></td>
                <td><?= (int)$qh['total_score'] ?>/50</td>
                <td><span class="risk-<?= htmlspecialchars($qh['risk_level']) ?>"><?= ucfirst(htmlspecialchars($qh['risk_level'])) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="dash-empty">No quiz data yet.</p>
      <?php endif; ?>
    </div>

    <div class="dash-card">
      <h3><i class="fas fa-gamepad"></i> Recent Activity</h3>
      <?php if (!empty($progressData)): ?>
        <table class="dash-table">
          <thead><tr><th>Activity</th><th>Category</th><th>Score</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach ($progressData as $pd): ?>
              <tr>
                <td><?= htmlspecialchars($pd['title']) ?></td>
                <td><?= ucfirst(htmlspecialchars($pd['category'])) ?></td>
                <td><?= $pd['score'] !== null ? (int)$pd['score'] : '-' ?></td>
                <td><?= htmlspecialchars($pd['completed_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="dash-empty">No activity data yet.</p>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<?php else: ?>
<div class="dash-empty-state">
  <h3>No children added</h3>
  <p>Add a child first to view progress tracking.</p>
  <a href="/parent/children/add" class="btn btn-primary">Add Child</a>
</div>
<?php endif; ?>
```

- [ ] **Step 9: Create `app/Views/parent/specialists.php`**

```php
<div class="dash-header">
  <div>
    <h1>Specialists</h1>
    <p>Browse our specialist directory</p>
  </div>
</div>

<div class="specialist-grid">
  <?php foreach ($specialists as $spec): ?>
    <div class="dash-card specialist-card">
      <div class="specialist-avatar">
        <span class="avatar-initials"><?= strtoupper(substr(htmlspecialchars($spec['name']), 0, 1)) ?></span>
      </div>
      <h3><?= htmlspecialchars($spec['name']) ?></h3>
      <p class="specialist-title"><?= htmlspecialchars($spec['title'] ?? 'Specialist') ?></p>
      <p class="specialist-bio"><?= htmlspecialchars(substr($spec['bio'] ?? '', 0, 120)) ?></p>
      <?php if ($spec['specializations']): ?>
        <?php $specs = json_decode($spec['specializations'], true) ?? []; ?>
        <div class="specialist-tags">
          <?php foreach ($specs as $s): ?>
            <span class="tag"><?= htmlspecialchars($s) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <p class="specialist-exp"><?= (int)$spec['years_experience'] ?> years experience</p>
      <div class="specialist-actions">
        <a href="/parent/appointments/book?specialist_id=<?= (int)$spec['id'] ?>" class="btn btn-primary btn-sm">Book Appointment</a>
        <a href="/parent/messages/send/<?= (int)$spec['id'] ?>" class="btn btn-outline btn-sm">Send Message</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php if (empty($specialists)): ?>
<div class="dash-empty-state">
  <h3>No specialists available</h3>
  <p>Please check back later.</p>
</div>
<?php endif; ?>
```

- [ ] **Step 10: Create `app/Views/parent/appointments.php`**

```php
<div class="dash-header">
  <div>
    <h1>Appointments</h1>
    <p>Manage your appointments</p>
  </div>
  <a href="/parent/appointments/book" class="btn btn-primary"><i class="fas fa-plus"></i> Book Appointment</a>
</div>

<?php if (!empty($appointments)): ?>
<div class="table-responsive">
  <table class="dash-table">
    <thead>
      <tr><th>Child</th><th>Specialist</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($appointments as $apt): ?>
        <tr>
          <td><?= htmlspecialchars($apt['child_name']) ?></td>
          <td><?= htmlspecialchars($apt['specialist_name']) ?></td>
          <td><?= htmlspecialchars($apt['date']) ?></td>
          <td><?= htmlspecialchars(substr($apt['time'], 0, 5)) ?></td>
          <td><span class="status-<?= htmlspecialchars($apt['status']) ?>"><?= ucfirst(htmlspecialchars($apt['status'])) ?></span></td>
          <td>
            <?php if ($apt['status'] === 'pending' || $apt['status'] === 'confirmed'): ?>
              <form method="POST" action="/parent/appointments/<?= (int)$apt['id'] ?>/cancel" style="display:inline" onsubmit="return confirm('Cancel this appointment?')">
                <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
                <button type="submit" class="btn-sm btn-danger">Cancel</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="dash-empty-state">
  <h3>No appointments</h3>
  <p>Book your first appointment with a specialist.</p>
  <a href="/parent/appointments/book" class="btn btn-primary">Book Appointment</a>
</div>
<?php endif; ?>
```

- [ ] **Step 11: Create `app/Views/parent/appointments-book.php`**

```php
<div class="dash-header">
  <div>
    <h1>Book Appointment</h1>
    <p>Schedule a session with a specialist</p>
  </div>
</div>

<form method="POST" action="/parent/appointments/book" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  
  <div class="form-group">
    <label for="child_id">Child *</label>
    <select id="child_id" name="child_id" required>
      <option value="">Select a child</option>
      <?php foreach ($children as $child): ?>
        <option value="<?= (int)$child['id'] ?>"><?= htmlspecialchars($child['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="form-group">
    <label for="specialist_id">Specialist *</label>
    <select id="specialist_id" name="specialist_id" required>
      <option value="">Select a specialist</option>
      <?php foreach ($specialists as $spec): ?>
        <option value="<?= (int)$spec['id'] ?>"><?= htmlspecialchars($spec['name']) ?> — <?= htmlspecialchars($spec['title'] ?? '') ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label for="date">Date *</label>
      <input type="date" id="date" name="date" required min="<?= date('Y-m-d') ?>">
    </div>
    <div class="form-group">
      <label for="time">Time *</label>
      <input type="time" id="time" name="time" required>
    </div>
    <div class="form-group">
      <label for="duration">Duration (minutes)</label>
      <select id="duration" name="duration">
        <option value="30">30 min</option>
        <option value="45">45 min</option>
        <option value="60">60 min</option>
      </select>
    </div>
  </div>

  <div class="form-group">
    <label for="notes">Notes</label>
    <textarea id="notes" name="notes" rows="3" placeholder="Any specific concerns or topics you'd like to discuss..."><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
  </div>

  <div class="form-actions">
    <a href="/parent/appointments" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Book Appointment</button>
  </div>
</form>
```

- [ ] **Step 12: Create `app/Views/parent/messages.php`**

```php
<div class="dash-header">
  <div>
    <h1>Messages</h1>
    <p>Communicate with specialists</p>
  </div>
</div>

<div class="dash-grid dash-grid-2">
  <div class="dash-card">
    <h3><i class="fas fa-inbox"></i> Inbox (<?= count($inbox) ?>)</h3>
    <?php if (!empty($inbox)): ?>
      <div class="message-list">
        <?php foreach ($inbox as $msg): ?>
          <a href="/parent/messages/thread/<?= (int)$msg['sender_id'] ?>" class="message-item <?= !$msg['is_read'] ? 'unread' : '' ?>">
            <div class="msg-sender"><?= htmlspecialchars($msg['sender_name']) ?></div>
            <div class="msg-subject"><?= htmlspecialchars($msg['subject']) ?></div>
            <div class="msg-date"><?= htmlspecialchars($msg['created_at']) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="dash-empty">No messages yet.</p>
    <?php endif; ?>
  </div>

  <div class="dash-card">
    <h3><i class="fas fa-paper-plane"></i> Sent (<?= count($sent) ?>)</h3>
    <?php if (!empty($sent)): ?>
      <div class="message-list">
        <?php foreach ($sent as $msg): ?>
          <a href="/parent/messages/thread/<?= (int)$msg['receiver_id'] ?>" class="message-item">
            <div class="msg-sender">To: <?= htmlspecialchars($msg['receiver_name']) ?></div>
            <div class="msg-subject"><?= htmlspecialchars($msg['subject']) ?></div>
            <div class="msg-date"><?= htmlspecialchars($msg['created_at']) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="dash-empty">No sent messages.</p>
    <?php endif; ?>
  </div>
</div>
```

- [ ] **Step 13: Create `app/Views/parent/messages-send.php`**

```php
<div class="dash-header">
  <div>
    <h1>Send Message</h1>
    <p>To: <?= htmlspecialchars($receiver['name']) ?></p>
  </div>
  <a href="/parent/messages" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<form method="POST" action="/parent/messages/send" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <input type="hidden" name="receiver_id" value="<?= (int)$receiver['id'] ?>">

  <div class="form-group">
    <label for="subject">Subject *</label>
    <input type="text" id="subject" name="subject" required placeholder="Enter message subject...">
  </div>

  <div class="form-group">
    <label for="body">Message *</label>
    <textarea id="body" name="body" rows="6" required placeholder="Write your message here..."></textarea>
  </div>

  <div class="form-actions">
    <a href="/parent/messages" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Send Message</button>
  </div>
</form>
```

- [ ] **Step 14: Create `app/Views/parent/messages-thread.php`**

```php
<div class="dash-header">
  <div>
    <h1>Messages with <?= htmlspecialchars($partner['name']) ?></h1>
  </div>
  <a href="/parent/messages" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="dash-card message-thread">
  <?php if (!empty($thread)): ?>
    <?php foreach ($thread as $msg): ?>
      <div class="thread-message <?= $msg['sender_id'] === \App\Core\Session::get('user_id') ? 'own' : 'other' ?>">
        <div class="thread-header">
          <strong><?= htmlspecialchars($msg['sender_name']) ?></strong>
          <small><?= htmlspecialchars($msg['created_at']) ?></small>
        </div>
        <div class="thread-subject"><?= htmlspecialchars($msg['subject']) ?></div>
        <div class="thread-body"><?= nl2br(htmlspecialchars($msg['body'])) ?></div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p class="dash-empty">No messages in this conversation.</p>
  <?php endif; ?>
</div>

<form method="POST" action="/parent/messages/reply" class="dash-form mt-1">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <input type="hidden" name="receiver_id" value="<?= (int)$partner['id'] ?>">
  <input type="hidden" name="subject" value="Re: <?= htmlspecialchars($thread[0]['subject'] ?? 'Message') ?>">

  <div class="form-group">
    <label for="body">Reply</label>
    <textarea id="body" name="body" rows="3" required placeholder="Type your reply..."></textarea>
  </div>

  <button type="submit" class="btn btn-primary">Send Reply</button>
</form>
```

- [ ] **Step 15: Create `app/Views/parent/chatbot.php`**

```php
<div class="dash-header">
  <div>
    <h1>AI Chat Assistant</h1>
    <p>Ask questions about autism, screening, and features</p>
  </div>
</div>

<div class="dash-card chatbot-container">
  <div class="chatbot-messages" id="chatMessages">
    <?php if (!empty($history)): ?>
      <?php foreach (array_reverse($history) as $entry): ?>
        <div class="chat-message <?= htmlspecialchars($entry['sender']) ?>">
          <div class="chat-bubble">
            <?php if ($entry['sender'] === 'bot'): ?>
              <i class="fas fa-robot chat-icon"></i>
            <?php endif; ?>
            <span><?= nl2br(htmlspecialchars($entry['message'])) ?></span>
            <small class="chat-time"><?= htmlspecialchars($entry['created_at']) ?></small>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="chat-message bot">
        <div class="chat-bubble">
          <i class="fas fa-robot chat-icon"></i>
          <span>Hello! I'm AutiMind assistant. Ask me about autism, screening, progress tracking, appointments, or our features!</span>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div class="chatbot-input">
    <input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrf_token) ?>">
    <input type="text" id="chatInput" placeholder="Type your message..." autofocus>
    <button id="chatSendBtn" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const chatInput = document.getElementById('chatInput');
  const chatSend = document.getElementById('chatSendBtn');
  const chatMessages = document.getElementById('chatMessages');

  function scrollToBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  function addMessage(text, sender) {
    const div = document.createElement('div');
    div.className = 'chat-message ' + sender;
    div.innerHTML = '<div class="chat-bubble">' + (sender === 'bot' ? '<i class="fas fa-robot chat-icon"></i>' : '') + '<span>' + text + '</span></div>';
    chatMessages.appendChild(div);
    scrollToBottom();
  }

  function sendMessage() {
    const message = chatInput.value.trim();
    if (!message) return;

    addMessage(message, 'user');
    chatInput.value = '';
    chatInput.disabled = true;
    chatSend.disabled = true;

    fetch('/parent/chatbot/message', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        _csrf_token: document.getElementById('csrfToken').value,
        message: message
      })
    })
    .then(r => r.json())
    .then(data => {
      addMessage(data.response, 'bot');
    })
    .catch(() => {
      addMessage('Sorry, something went wrong. Please try again.', 'bot');
    })
    .finally(() => {
      chatInput.disabled = false;
      chatSend.disabled = false;
      chatInput.focus();
    });
  }

  chatSend.addEventListener('click', sendMessage);
  chatInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') sendMessage();
  });

  scrollToBottom();
});
</script>
```

- [ ] **Step 16: Create `app/Views/parent/settings.php`**

```php
<div class="dash-header">
  <div>
    <h1>Settings</h1>
    <p>Manage your account settings</p>
  </div>
</div>

<form method="POST" action="/parent/settings" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

  <div class="dash-card mb-2">
    <h3>Profile Information</h3>
    
    <div class="form-group">
      <label for="name">Full Name *</label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? $user['name']) ?>" required>
      <?php if (!empty($errors['name'])): ?><span class="form-error"><?= htmlspecialchars($errors['name'][0]) ?></span><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
      <small>Email cannot be changed.</small>
    </div>

    <div class="form-group">
      <label for="phone">Phone</label>
      <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($old['phone'] ?? $user['phone'] ?? '') ?>">
    </div>
  </div>

  <div class="dash-card mb-2">
    <h3>Change Password</h3>
    <p class="text-muted">Leave blank to keep current password.</p>

    <div class="form-group">
      <label for="password">New Password</label>
      <input type="password" id="password" name="password" minlength="8">
    </div>

    <div class="form-group">
      <label for="password_confirmation">Confirm New Password</label>
      <input type="password" id="password_confirmation" name="password_confirmation">
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">Save Settings</button>
  </div>
</form>
```

---

### Task 6: Add Dashboard CSS Styles

**Files:**
- Modify: `public/assets/css/dashboard.css` (append styles)

- [ ] **Step 1: Append dashboard styles**

Append to `public/assets/css/dashboard.css`:

```css

/* ==========================================
   Parent Dashboard Enhancements
   ========================================== */

.dash-grid-2 {
  grid-template-columns: repeat(2, 1fr);
}

.dash-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.dash-header h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text, #1a1a2e);
  margin: 0 0 0.25rem;
}

.dash-header p {
  color: var(--text-muted, #6b7280);
  margin: 0;
}

.dash-card {
  background: var(--card-bg, #fff);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.dash-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.dash-card-header h3 {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.dash-badge {
  background: var(--primary-light, #e0e7ff);
  color: var(--primary, #6366f1);
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.dash-badge-warning {
  background: #fef3c7;
  color: #d97706;
}

.dash-link {
  display: inline-block;
  margin-top: 0.75rem;
  color: var(--primary, #6366f1);
  font-weight: 500;
  font-size: 0.9rem;
}

.dash-empty {
  color: var(--text-muted, #9ca3af);
  font-style: italic;
  padding: 1rem 0;
}

.dash-empty-state {
  text-align: center;
  padding: 3rem 1rem;
}

.dash-empty-state h2, .dash-empty-state h3 {
  margin: 1rem 0 0.5rem;
}

.dash-empty-state p {
  color: var(--text-muted, #6b7280);
  margin-bottom: 1.5rem;
}

.dash-section {
  margin-top: 2rem;
}

.dash-section h2 {
  font-size: 1.3rem;
  font-weight: 600;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Insights */
.insight-card {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}

.insight-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  flex-shrink: 0;
}

.insight-strength .insight-icon {
  background: #dcfce7;
  color: #16a34a;
}

.insight-recommendation .insight-icon {
  background: #fef3c7;
  color: #d97706;
}

.insight-content h4 {
  margin: 0 0 0.25rem;
  font-size: 1rem;
}

.insight-content p {
  margin: 0;
  font-size: 0.9rem;
  color: var(--text-muted, #6b7280);
  line-height: 1.5;
}

/* Children list compact */
.child-list-compact {
  list-style: none;
  padding: 0;
  margin: 0;
}

.child-list-compact li {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 0;
  border-bottom: 1px solid var(--border, #f3f4f6);
}

.child-list-compact li:last-child {
  border-bottom: none;
}

.child-avatar-sm {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--primary, #6366f1);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.8rem;
}

/* Appointment list compact */
.appointment-list-compact {
  list-style: none;
  padding: 0;
  margin: 0;
}

.appointment-list-compact li {
  padding: 0.5rem 0;
  border-bottom: 1px solid var(--border, #f3f4f6);
  line-height: 1.5;
}

.appointment-list-compact li:last-child {
  border-bottom: none;
}

/* Tables */
.table-responsive {
  overflow-x: auto;
}

.dash-table {
  width: 100%;
  border-collapse: collapse;
}

.dash-table th, .dash-table td {
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid var(--border, #f3f4f6);
}

.dash-table th {
  font-weight: 600;
  font-size: 0.85rem;
  color: var(--text-muted, #6b7280);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.dash-table tbody tr:hover {
  background: var(--bg-hover, #f9fafb);
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.btn-sm {
  padding: 0.35rem 0.75rem;
  font-size: 0.8rem;
  border-radius: 6px;
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  transition: all 0.2s;
}

.btn-outline {
  background: transparent;
  border: 1px solid var(--border, #d1d5db);
  color: var(--text, #374151);
  text-decoration: none;
}

.btn-outline:hover {
  background: var(--bg-hover, #f3f4f6);
}

.btn-danger {
  background: #fee2e2;
  color: #dc2626;
  border: none;
}

.btn-danger:hover {
  background: #fecaca;
}

.btn-primary {
  background: var(--primary, #6366f1);
  color: #fff;
  border: none;
  text-decoration: none;
}

.btn-primary:hover {
  background: var(--primary-dark, #4f46e5);
}

/* Forms */
.dash-form {
  max-width: 700px;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  font-weight: 500;
  margin-bottom: 0.4rem;
  font-size: 0.9rem;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.6rem 0.75rem;
  border: 1px solid var(--border, #d1d5db);
  border-radius: 8px;
  font-size: 0.95rem;
  background: var(--input-bg, #fff);
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--primary, #6366f1);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}

.form-group input[disabled] {
  background: var(--bg-muted, #f9fafb);
  color: var(--text-muted, #9ca3af);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-error {
  color: #dc2626;
  font-size: 0.8rem;
  margin-top: 0.25rem;
  display: block;
}

.form-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.form-actions .btn {
  padding: 0.6rem 1.5rem;
  font-size: 0.95rem;
  border-radius: 8px;
  cursor: pointer;
  border: none;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

/* Quiz */
.quiz-question {
  margin-bottom: 1.5rem;
}

.question-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.question-number {
  background: var(--primary, #6366f1);
  color: #fff;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.8rem;
}

.question-category {
  font-size: 0.8rem;
  color: var(--text-muted, #6b7280);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.question-text {
  font-size: 1rem;
  font-weight: 500;
  margin-bottom: 1rem;
  line-height: 1.5;
}

.question-options {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.option-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.6rem 1rem;
  border: 1px solid var(--border, #e5e7eb);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.option-label:hover {
  border-color: var(--primary, #6366f1);
  background: rgba(99,102,241,0.03);
}

.option-label input[type="radio"]:checked + .option-text {
  color: var(--primary, #6366f1);
  font-weight: 600;
}

.option-label:has(input[type="radio"]:checked) {
  border-color: var(--primary, #6366f1);
  background: rgba(99,102,241,0.06);
}

.option-text {
  font-size: 0.95rem;
}

/* Results */
.result-summary h2 {
  font-size: 1.5rem;
  margin: 0 0 0.5rem;
}

.score-bar {
  height: 10px;
  background: var(--border, #e5e7eb);
  border-radius: 5px;
  overflow: hidden;
  margin-top: 0.75rem;
}

.score-bar-fill {
  height: 100%;
  border-radius: 5px;
  transition: width 0.6s ease;
}

.score-bar-sm {
  height: 6px;
  background: var(--border, #e5e7eb);
  border-radius: 3px;
  overflow: hidden;
  margin-top: 0.3rem;
}

.category-scores {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.category-item {
  display: flex;
  flex-direction: column;
}

.category-label {
  font-weight: 500;
  font-size: 0.9rem;
}

.category-score {
  font-size: 0.85rem;
  color: var(--text-muted, #6b7280);
}

.risk-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.85rem;
}

.risk-low {
  background: #dcfce7;
  color: #16a34a;
}

.risk-moderate {
  background: #fef3c7;
  color: #d97706;
}

.risk-high {
  background: #fee2e2;
  color: #dc2626;
}

/* Status badges */
.status-pending {
  color: #d97706;
  font-weight: 500;
}
.status-confirmed {
  color: #16a34a;
  font-weight: 500;
}
.status-cancelled {
  color: #dc2626;
  font-weight: 500;
}
.status-completed {
  color: #6b7280;
  font-weight: 500;
}

/* Specialist grid */
.specialist-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.specialist-card {
  text-align: center;
}

.specialist-avatar {
  margin-bottom: 0.75rem;
}

.avatar-initials {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: var(--primary, #6366f1);
  color: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.3rem;
}

.specialist-title {
  color: var(--primary, #6366f1);
  font-weight: 500;
  font-size: 0.9rem;
  margin: 0.25rem 0;
}

.specialist-bio {
  font-size: 0.85rem;
  color: var(--text-muted, #6b7280);
  line-height: 1.5;
  margin: 0.5rem 0;
}

.specialist-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
  justify-content: center;
  margin: 0.75rem 0;
}

.tag {
  background: var(--bg-muted, #f3f4f6);
  padding: 0.2rem 0.6rem;
  border-radius: 12px;
  font-size: 0.75rem;
  color: var(--text-muted, #6b7280);
}

.specialist-exp {
  font-size: 0.85rem;
  color: var(--text-muted, #6b7280);
  margin: 0.25rem 0;
}

.specialist-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
  margin-top: 1rem;
}

/* Messages */
.message-list {
  display: flex;
  flex-direction: column;
}

.message-item {
  display: flex;
  flex-direction: column;
  padding: 0.75rem;
  border-bottom: 1px solid var(--border, #f3f4f6);
  text-decoration: none;
  color: inherit;
  transition: background 0.2s;
}

.message-item:hover {
  background: var(--bg-hover, #f9fafb);
}

.message-item.unread {
  background: rgba(99,102,241,0.03);
  border-left: 3px solid var(--primary, #6366f1);
}

.msg-sender {
  font-weight: 600;
  font-size: 0.9rem;
}

.msg-subject {
  font-size: 0.85rem;
  color: var(--text-muted, #6b7280);
}

.msg-date {
  font-size: 0.75rem;
  color: var(--text-muted, #9ca3af);
  margin-top: 0.2rem;
}

/* Message thread */
.message-thread {
  max-height: 500px;
  overflow-y: auto;
}

.thread-message {
  padding: 1rem;
  margin-bottom: 0.75rem;
  border-radius: 8px;
}

.thread-message.own {
  background: rgba(99,102,241,0.06);
  border-left: 3px solid var(--primary, #6366f1);
}

.thread-message.other {
  background: var(--bg-muted, #f9fafb);
  border-left: 3px solid #d1d5db;
}

.thread-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.25rem;
}

.thread-subject {
  font-size: 0.85rem;
  color: var(--text-muted, #6b7280);
  margin-bottom: 0.5rem;
}

.thread-body {
  font-size: 0.95rem;
  line-height: 1.6;
}

/* Chatbot */
.chatbot-container {
  display: flex;
  flex-direction: column;
  height: 600px;
}

.chatbot-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.chat-message {
  display: flex;
}

.chat-message.user {
  justify-content: flex-end;
}

.chat-message.bot {
  justify-content: flex-start;
}

.chat-bubble {
  max-width: 75%;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  font-size: 0.9rem;
  line-height: 1.5;
  display: flex;
  gap: 0.5rem;
  align-items: flex-start;
}

.chat-message.user .chat-bubble {
  background: var(--primary, #6366f1);
  color: #fff;
  border-bottom-right-radius: 4px;
}

.chat-message.bot .chat-bubble {
  background: var(--bg-muted, #f3f4f6);
  color: var(--text, #1a1a2e);
  border-bottom-left-radius: 4px;
}

.chat-icon {
  font-size: 1.1rem;
  margin-top: 0.1rem;
}

.chat-time {
  font-size: 0.7rem;
  opacity: 0.7;
  display: block;
  margin-top: 0.25rem;
}

.chatbot-input {
  display: flex;
  gap: 0.5rem;
  padding: 1rem;
  border-top: 1px solid var(--border, #e5e7eb);
  background: var(--card-bg, #fff);
  border-radius: 0 0 12px 12px;
}

.chatbot-input input {
  flex: 1;
  padding: 0.6rem 1rem;
  border: 1px solid var(--border, #d1d5db);
  border-radius: 8px;
  font-size: 0.95rem;
}

.chatbot-input input:focus {
  outline: none;
  border-color: var(--primary, #6366f1);
}

.chatbot-input button {
  padding: 0.6rem 1.2rem;
}

/* Info box */
.dash-info-box {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  color: #1e40af;
  font-size: 0.9rem;
  line-height: 1.5;
}

.dash-info-box i {
  font-size: 1.1rem;
  margin-top: 0.15rem;
}

.dash-info-box p {
  margin: 0;
}

/* Utilities */
.mb-2 { margin-bottom: 2rem; }
.mb-1 { margin-bottom: 1rem; }
.mt-1 { margin-top: 1rem; }
.mt-2 { margin-top: 2rem; }

/* Responsive */
@media (max-width: 768px) {
  .dash-grid-2 {
    grid-template-columns: 1fr;
  }
  .form-row {
    grid-template-columns: 1fr;
  }
  .specialist-grid {
    grid-template-columns: 1fr;
  }
  .dash-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
```

---

### Task 7: Add Parent Dashboard Sidebar Active States

**Files:**
- Modify: `app/Views/partials/dashboard-sidebar.php` (already has parent nav items — no change needed)

---

### Task 8: Specialisten + Admin Route Updates

**Files:**
- Modify: `routes/web.php` (replace existing route blocks with full specialist + admin routes)

- [ ] **Step 1: Replace the routes section for specialist and admin**

Update `routes/web.php` so after the parent routes, the specialist and admin sections become:

```php
// Specialist dashboard routes
$router->get('/specialist/dashboard', 'SpecialistController@dashboard', ['auth', 'role:specialist']);
$router->get('/specialist/patients', 'SpecialistController@patients', ['auth', 'role:specialist']);
$router->get('/specialist/patients/{id}', 'SpecialistController@patientDetail', ['auth', 'role:specialist']);
$router->get('/specialist/appointments', 'SpecialistController@appointments', ['auth', 'role:specialist']);
$router->post('/specialist/appointments/{id}/status', 'SpecialistController@updateAppointmentStatus', ['auth', 'role:specialist']);
$router->get('/specialist/messages', 'SpecialistController@messages', ['auth', 'role:specialist']);
$router->get('/specialist/messages/thread/{partnerId}', 'SpecialistController@messageThread', ['auth', 'role:specialist']);
$router->post('/specialist/messages/send', 'SpecialistController@sendMessage', ['auth', 'role:specialist']);
$router->get('/specialist/schedule', 'SpecialistController@schedule', ['auth', 'role:specialist']);
$router->post('/specialist/schedule', 'SpecialistController@updateSchedule', ['auth', 'role:specialist']);
$router->get('/specialist/settings', 'SpecialistController@settings', ['auth', 'role:specialist']);
$router->post('/specialist/settings', 'SpecialistController@updateSettings', ['auth', 'role:specialist']);

// Admin dashboard routes
$router->get('/admin/dashboard', 'AdminController@dashboard', ['auth', 'role:admin']);
$router->get('/admin/users', 'AdminController@users', ['auth', 'role:admin']);
$router->get('/admin/users/add', 'AdminController@addUserForm', ['auth', 'role:admin']);
$router->post('/admin/users/add', 'AdminController@addUser', ['auth', 'role:admin']);
$router->get('/admin/users/{id}/edit', 'AdminController@editUserForm', ['auth', 'role:admin']);
$router->post('/admin/users/{id}/edit', 'AdminController@editUser', ['auth', 'role:admin']);
$router->post('/admin/users/{id}/delete', 'AdminController@deleteUser', ['auth', 'role:admin']);
$router->get('/admin/specialists', 'AdminController@manageSpecialists', ['auth', 'role:admin']);
$router->post('/admin/specialists/{id}/approve', 'AdminController@approveSpecialist', ['auth', 'role:admin']);
$router->get('/admin/quiz', 'AdminController@quiz', ['auth', 'role:admin']);
$router->get('/admin/quiz/add', 'AdminController@addQuizForm', ['auth', 'role:admin']);
$router->post('/admin/quiz/add', 'AdminController@addQuiz', ['auth', 'role:admin']);
$router->get('/admin/quiz/{id}/edit', 'AdminController@editQuizForm', ['auth', 'role:admin']);
$router->post('/admin/quiz/{id}/edit', 'AdminController@editQuiz', ['auth', 'role:admin']);
$router->post('/admin/quiz/{id}/delete', 'AdminController@deleteQuiz', ['auth', 'role:admin']);
$router->get('/admin/activities', 'AdminController@activities', ['auth', 'role:admin']);
$router->get('/admin/activities/add', 'AdminController@addActivityForm', ['auth', 'role:admin']);
$router->post('/admin/activities/add', 'AdminController@addActivity', ['auth', 'role:admin']);
$router->get('/admin/activities/{id}/edit', 'AdminController@editActivityForm', ['auth', 'role:admin']);
$router->post('/admin/activities/{id}/edit', 'AdminController@editActivity', ['auth', 'role:admin']);
$router->post('/admin/activities/{id}/delete', 'AdminController@deleteActivity', ['auth', 'role:admin']);
$router->get('/admin/appointments', 'AdminController@appointments', ['auth', 'role:admin']);
$router->get('/admin/messages', 'AdminController@messages', ['auth', 'role:admin']);
$router->get('/admin/subscriptions', 'AdminController@subscriptions', ['auth', 'role:admin']);
$router->get('/admin/subscriptions/add', 'AdminController@addSubscriptionForm', ['auth', 'role:admin']);
$router->post('/admin/subscriptions/add', 'AdminController@addSubscription', ['auth', 'role:admin']);
$router->get('/admin/subscriptions/{id}/edit', 'AdminController@editSubscriptionForm', ['auth', 'role:admin']);
$router->post('/admin/subscriptions/{id}/edit', 'AdminController@editSubscription', ['auth', 'role:admin']);
$router->get('/admin/contacts', 'AdminController@contacts', ['auth', 'role:admin']);
$router->post('/admin/contacts/{id}/read', 'AdminController@markContactRead', ['auth', 'role:admin']);
$router->get('/admin/faq', 'AdminController@faq', ['auth', 'role:admin']);
$router->get('/admin/faq/add', 'AdminController@addFaqForm', ['auth', 'role:admin']);
$router->post('/admin/faq/add', 'AdminController@addFaq', ['auth', 'role:admin']);
$router->get('/admin/faq/{id}/edit', 'AdminController@editFaqForm', ['auth', 'role:admin']);
$router->post('/admin/faq/{id}/edit', 'AdminController@editFaq', ['auth', 'role:admin']);
$router->post('/admin/faq/{id}/delete', 'AdminController@deleteFaq', ['auth', 'role:admin']);
$router->get('/admin/settings', 'AdminController@settings', ['auth', 'role:admin']);
$router->post('/admin/settings', 'AdminController@updateSettings', ['auth', 'role:admin']);
```
