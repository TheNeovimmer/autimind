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

        $userId = $this->getParentId();
        $avatarPath = $this->handleAvatarUpload($userId);
        $db = Database::getInstance();

        if ($avatarPath) {
            $stmt = $db->prepare('UPDATE users SET name = ?, phone = ?, avatar = ? WHERE id = ?');
            $stmt->execute([$_POST['name'], $_POST['phone'] ?? null, $avatarPath, $userId]);
        } else {
            $stmt = $db->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?');
            $stmt->execute([$_POST['name'], $_POST['phone'] ?? null, $userId]);
        }

        Session::set('user_name', $_POST['name']);

        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 8) {
                Session::setFlash('error', 'Password must be at least 8 characters.');
                header('Location: /parent/settings');
                exit;
            }
            $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([password_hash($_POST['password'], PASSWORD_BCRYPT), $userId]);
        }

        Session::setFlash('success', 'Settings updated successfully.');
        header('Location: /parent/settings');
        exit;
    }

    private function handleAvatarUpload(int $userId): ?string
    {
        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES['avatar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext, $allowed)) {
            return null;
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $user = User::findById($userId);
        if ($user && !empty($user['avatar'])) {
            $oldFile = $uploadDir . basename($user['avatar']);
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $filename = 'user_' . $userId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return null;
        }

        return '/uploads/avatars/' . $filename;
    }

    public function childrenAvatarUpload(int $childId): void
    {
        if (!Child::belongsToParent($childId, $this->getParentId())) {
            http_response_code(403);
            View::render('errors/403', [], 'dashboard');
            return;
        }

        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'No file uploaded or upload error.');
            header('Location: /parent/children');
            exit;
        }

        $file = $_FILES['avatar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext, $allowed)) {
            Session::setFlash('error', 'Invalid file type. Allowed: JPG, PNG, WebP, GIF.');
            header('Location: /parent/children');
            exit;
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            Session::setFlash('error', 'File too large. Maximum 2MB.');
            header('Location: /parent/children');
            exit;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $child = Child::findById($childId);
        if ($child && !empty($child['avatar'])) {
            $oldFile = $uploadDir . basename($child['avatar']);
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $filename = 'child_' . $childId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            Session::setFlash('error', 'Failed to save uploaded file.');
            header('Location: /parent/children');
            exit;
        }

        $avatarPath = '/uploads/avatars/' . $filename;
        Child::updateAvatar($childId, $avatarPath);

        Session::setFlash('success', 'Avatar updated successfully.');
        header('Location: /parent/children');
        exit;
    }

    public function childDetail(int $id): void
    {
        if (!Child::belongsToParent($id, $this->getParentId())) {
            http_response_code(403);
            View::render('errors/403', [], 'dashboard');
            return;
        }

        $child = Child::findById($id);
        $quizHistory = QuizAttempt::getByChild($id);
        $progress = ChildProgress::getByChild($id);
        $averageScore = ChildProgress::averageScoreByChild($id);

        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT a.*, c.name AS child_name, u.name AS specialist_name
            FROM appointments a
            JOIN children c ON a.child_id = c.id
            JOIN users u ON a.specialist_id = u.id
            WHERE a.child_id = ?
            ORDER BY a.date DESC, a.time DESC
        ');
        $stmt->execute([$id]);
        $appointments = $stmt->fetchAll();

        View::render('parent/children-detail', [
            'title' => $child['name'] . ' - Details',
            'child' => $child,
            'quizHistory' => $quizHistory,
            'progress' => $progress,
            'averageScore' => $averageScore,
            'appointments' => $appointments,
        ], 'dashboard');
    }

    public function rescheduleAppointmentForm(int $id): void
    {
        $appointment = Appointment::findById($id);
        if (!$appointment || $appointment['parent_id'] !== $this->getParentId()) {
            http_response_code(403);
            View::render('errors/403', [], 'dashboard');
            return;
        }

        View::render('parent/appointments-reschedule', [
            'title' => 'Reschedule Appointment',
            'appointment' => $appointment,
            'csrf_token' => Session::csrf_token(),
        ], 'dashboard');
    }

    public function rescheduleAppointment(int $id): void
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

        $validator = new Validator();
        if (!$validator->validate($_POST, [
            'date' => 'required',
            'time' => 'required',
            'notes' => '',
        ])) {
            Session::setFlash('error', 'Please fill in all required fields.');
            header('Location: /parent/appointments/' . $id . '/reschedule');
            exit;
        }

        Appointment::update($id, [
            'date' => $_POST['date'],
            'time' => $_POST['time'],
            'notes' => $_POST['notes'] ?? null,
        ]);

        Session::setFlash('success', 'Appointment rescheduled successfully.');
        header('Location: /parent/appointments');
        exit;
    }

    public function subscription(): void
    {
        $currentSubscription = Subscription::getByUser($this->getParentId());

        $plans = [
            'standard' => ['name' => 'Standard', 'price' => '$9.99/mo', 'features' => ['1 child profile', 'Basic screening quiz', 'Progress tracking', 'Community access']],
            'premium' => ['name' => 'Premium', 'price' => '$19.99/mo', 'features' => ['Up to 3 child profiles', 'Advanced screening quiz', 'Progress tracking & insights', 'Direct messaging with specialists', 'Priority support']],
            'family' => ['name' => 'Family', 'price' => '$29.99/mo', 'features' => ['Unlimited child profiles', 'All quiz & assessment tools', 'Full progress analytics', 'Priority specialist access', 'AI chat assistant', 'Family support group access']],
        ];

        View::render('parent/subscription', [
            'title' => 'Subscription',
            'currentSubscription' => $currentSubscription,
            'plans' => $plans,
            'csrf_token' => Session::csrf_token(),
        ], 'dashboard');
    }

    public function upgradeSubscription(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $plan = $_POST['plan'] ?? '';
        $allowed = ['standard', 'premium', 'family'];
        if (!in_array($plan, $allowed)) {
            Session::setFlash('error', 'Invalid plan selected.');
            header('Location: /parent/subscription');
            exit;
        }

        $current = Subscription::getByUser($this->getParentId());
        if ($current) {
            Subscription::update($current['id'], [
                'plan' => $plan,
                'status' => 'active',
                'ends_at' => date('Y-m-d', strtotime('+1 month')),
            ]);
        } else {
            Subscription::create([
                'user_id' => $this->getParentId(),
                'plan' => $plan,
                'status' => 'active',
                'ends_at' => date('Y-m-d', strtotime('+1 month')),
            ]);
        }

        Session::setFlash('success', 'Subscription updated to ' . ucfirst($plan) . ' plan.');
        header('Location: /parent/subscription');
        exit;
    }

    public function childActivities(int $childId): void
    {
        if (!Child::belongsToParent($childId, $this->getParentId())) {
            http_response_code(403);
            View::render('errors/403', [], 'dashboard');
            return;
        }

        $child = Child::findById($childId);
        $activities = ChildProgress::getByChild($childId);
        $averageScore = ChildProgress::averageScoreByChild($childId);

        View::render('parent/activities', [
            'title' => $child['name'] . ' - Activities',
            'child' => $child,
            'activities' => $activities,
            'averageScore' => $averageScore,
        ], 'dashboard');
    }
}
