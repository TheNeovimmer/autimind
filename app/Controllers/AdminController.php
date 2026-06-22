<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Core\Validator;
use App\Core\Database;
use App\Core\Env;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Message;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use App\Models\Activity;
use App\Models\Subscription;
use App\Models\ChatbotResponse;
use App\Models\ChildProgress;
use App\Models\SpecialistDetail;
use App\Models\QuizOption;
use App\Models\Child;
use PDO;

class AdminController
{
    private function getAdminId(): int
    {
        return (int) Session::get('user_id');
    }

    public function dashboard(): void
    {
        $db = Database::getInstance();

        $totalUsers = User::countByRole('parent') + User::countByRole('specialist');
        $totalParents = User::countByRole('parent');
        $totalSpecialists = User::countByRole('specialist');
        $totalAppointments = (int) $db->query('SELECT COUNT(*) FROM appointments')->fetchColumn();
        $pendingAppointments = (int) $db->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn();
        $totalChildren = (int) $db->query('SELECT COUNT(*) FROM children')->fetchColumn();
        $totalSubscriptions = Subscription::count();
        $activeSubscriptions = Subscription::countActive();
        $unreadMessages = (int) $db->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
        $unreadContacts = (int) $db->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();

        $stmt = $db->query('SELECT DATE(created_at) as day, COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY day');
        $registrations = $stmt ? $stmt->fetchAll() : [];

        $stmt = $db->query("SELECT status, COUNT(*) as count FROM appointments GROUP BY status");
        $appointmentStatuses = $stmt ? $stmt->fetchAll() : [];

        $stmt = $db->query('SELECT role, COUNT(*) as count FROM users WHERE is_active = 1 GROUP BY role');
        $roleDistribution = $stmt ? $stmt->fetchAll() : [];

        $stmt = $db->query("SELECT plan, COUNT(*) as count FROM subscriptions GROUP BY plan");
        $planDistribution = $stmt ? $stmt->fetchAll() : [];

        $stmt = $db->query('SELECT DATE(completed_at) as day, COUNT(*) as count FROM quiz_attempts WHERE completed_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND completed_at IS NOT NULL GROUP BY DATE(completed_at) ORDER BY day');
        $quizAttempts = $stmt ? $stmt->fetchAll() : [];

        View::render('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'totalUsers' => $totalUsers,
            'totalParents' => $totalParents,
            'totalSpecialists' => $totalSpecialists,
            'totalAppointments' => $totalAppointments,
            'pendingAppointments' => $pendingAppointments,
            'totalChildren' => $totalChildren,
            'totalSubscriptions' => $totalSubscriptions,
            'activeSubscriptions' => $activeSubscriptions,
            'unreadMessages' => $unreadMessages,
            'unreadContacts' => $unreadContacts,
            'registrations' => $registrations,
            'appointmentStatuses' => $appointmentStatuses,
            'roleDistribution' => $roleDistribution,
            'planDistribution' => $planDistribution,
            'quizAttempts' => $quizAttempts,
        ], 'dashboard');
    }

    public function users(): void
    {
        $db = Database::getInstance();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $search = trim($_GET['search'] ?? '');
        $role = trim($_GET['role'] ?? '');

        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(name LIKE ? OR email LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($role !== '' && in_array($role, ['parent', 'specialist', 'admin'])) {
            $where[] = 'role = ?';
            $params[] = $role;
        }

        $whereClause = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $db->prepare("SELECT COUNT(*) FROM users $whereClause");
        $countStmt->execute($params);
        $totalUsers = (int) $countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalUsers / $perPage));

        $params[] = $perPage;
        $params[] = $offset;
        $stmt = $db->prepare("SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        View::render('admin/users', [
            'title' => 'Users',
            'users' => $users,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers,
            'search' => $search,
            'role' => $role,
        ], 'dashboard');
    }

    public function addUserForm(): void
    {
        View::render('admin/users-add', ['title' => 'Add User', 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function addUser(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, [
            'name' => 'required|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'role' => 'required|in:parent,specialist,admin',
        ])) {
            View::render('admin/users-add', ['errors' => $validator->errors(), 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        if (User::findByEmail($_POST['email'])) {
            Session::setFlash('error', 'Email already exists.');
            header('Location: /admin/users/add');
            exit;
        }

        $userId = User::create([
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
            'role' => $_POST['role'],
        ]);

        if ($_POST['role'] === 'specialist') {
            $db = Database::getInstance();
            $stmt = $db->prepare('INSERT INTO specialist_details (user_id, title, bio) VALUES (?, ?, ?)');
            $stmt->execute([$userId, $_POST['title'] ?? '', $_POST['bio'] ?? '']);
        }

        Session::setFlash('success', 'User created successfully.');
        header('Location: /admin/users');
        exit;
    }

    public function editUserForm(int $id): void
    {
        $user = User::findById($id);
        if (!$user) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }
        View::render('admin/users-edit', ['title' => 'Edit User', 'user' => $user, 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function editUser(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $user = User::findById($id);
        if (!$user) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, ['name' => 'required|max:255', 'email' => 'required|email'])) {
            View::render('admin/users-edit', ['user' => $user, 'errors' => $validator->errors(), 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET name = ?, email = ?, role = ?, phone = ?, is_active = ? WHERE id = ?');
        $stmt->execute([$_POST['name'], $_POST['email'], $_POST['role'] ?? $user['role'], $_POST['phone'] ?? null, $_POST['is_active'] ?? 1, $id]);

        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 8) {
                Session::setFlash('error', 'Password must be at least 8 characters.');
                header('Location: /admin/users/' . $id . '/edit');
                exit;
            }
            $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([password_hash($_POST['password'], PASSWORD_BCRYPT), $id]);
        }

        Session::setFlash('success', 'User updated successfully.');
        header('Location: /admin/users');
        exit;
    }

    public function deleteUser(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        if ($id === $this->getAdminId()) {
            Session::setFlash('error', 'You cannot delete your own account.');
            header('Location: /admin/users');
            exit;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);

        Session::setFlash('success', 'User deleted successfully.');
        header('Location: /admin/users');
        exit;
    }

    public function manageSpecialists(): void
    {
        $db = Database::getInstance();
        $stmt = $db->query('
            SELECT u.*, sd.title, sd.specializations, sd.years_experience, sd.is_available, sd.bio
            FROM users u
            LEFT JOIN specialist_details sd ON u.id = sd.user_id
            WHERE u.role = "specialist"
            ORDER BY u.name
        ');
        $specialists = $stmt ? $stmt->fetchAll() : [];
        View::render('admin/specialists', ['title' => 'Specialists', 'specialists' => $specialists, 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function approveSpecialist(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET is_active = ? WHERE id = ? AND role = "specialist"');
        $stmt->execute([$_POST['is_active'] ?? 1, $id]);

        Session::setFlash('success', 'Specialist status updated.');
        header('Location: /admin/specialists');
        exit;
    }

    public function editSpecialistDetailsForm(int $id): void
    {
        $user = User::findById($id);
        if (!$user || $user['role'] !== 'specialist') {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }
        $details = SpecialistDetail::findByUserId($id);
        View::render('admin/specialists-edit', [
            'title' => 'Edit Specialist',
            'user' => $user,
            'details' => $details,
            'csrf_token' => Session::csrf_token(),
        ], 'dashboard');
    }

    public function editSpecialistDetails(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $user = User::findById($id);
        if (!$user || $user['role'] !== 'specialist') {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET is_active = ? WHERE id = ?');
        $stmt->execute([$_POST['is_active'] ?? 1, $id]);

        SpecialistDetail::update($id, [
            'title' => $_POST['title'] ?? '',
            'bio' => $_POST['bio'] ?? null,
            'specializations' => $_POST['specializations'] ?? null,
            'years_experience' => $_POST['years_experience'] !== '' ? (int)$_POST['years_experience'] : null,
            'is_available' => $_POST['is_available'] ?? 1,
        ]);

        Session::setFlash('success', 'Specialist updated successfully.');
        header('Location: /admin/specialists');
        exit;
    }

    public function quiz(): void
    {
        $questions = QuizQuestion::getAll();
        View::render('admin/quiz', ['title' => 'Quiz Questions', 'questions' => $questions], 'dashboard');
    }

    public function addQuizForm(): void
    {
        View::render('admin/quiz-add', ['title' => 'Add Question', 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function addQuiz(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, [
            'question_text' => 'required',
            'category' => 'required|in:social_communication,behavior,sensory,developmental',
            'order_index' => 'required',
        ])) {
            View::render('admin/quiz-add', ['errors' => $validator->errors(), 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        $qId = QuizQuestion::create([
            'question_text' => $_POST['question_text'],
            'category' => $_POST['category'],
            'order_index' => (int)$_POST['order_index'],
        ]);

        if (!empty($_POST['options']) && is_array($_POST['options'])) {
            $db = Database::getInstance();
            foreach ($_POST['options'] as $i => $opt) {
                if (!empty($opt['text'])) {
                    $stmt = $db->prepare('INSERT INTO quiz_options (question_id, option_text, weight, order_index) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$qId, $opt['text'], (int)($opt['weight'] ?? 0), $i]);
                }
            }
        }

        Session::setFlash('success', 'Question added successfully.');
        header('Location: /admin/quiz');
        exit;
    }

    public function editQuizForm(int $id): void
    {
        $question = QuizQuestion::findById($id);
        if (!$question) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM quiz_options WHERE question_id = ? ORDER BY order_index');
        $stmt->execute([$id]);
        $options = $stmt->fetchAll();

        View::render('admin/quiz-edit', ['title' => 'Edit Question', 'question' => $question, 'options' => $options, 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function editQuiz(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $question = QuizQuestion::findById($id);
        if (!$question) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, [
            'question_text' => 'required',
            'category' => 'required|in:social_communication,behavior,sensory,developmental',
            'order_index' => 'required',
        ])) {
            View::render('admin/quiz-edit', ['question' => $question, 'errors' => $validator->errors(), 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        QuizQuestion::update($id, [
            'question_text' => $_POST['question_text'],
            'category' => $_POST['category'],
            'order_index' => (int)$_POST['order_index'],
            'is_active' => $_POST['is_active'] ?? 1,
        ]);

        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM quiz_options WHERE question_id = ?');
        $stmt->execute([$id]);

        if (!empty($_POST['options']) && is_array($_POST['options'])) {
            foreach ($_POST['options'] as $i => $opt) {
                if (!empty($opt['text'])) {
                    $stmt = $db->prepare('INSERT INTO quiz_options (question_id, option_text, weight, order_index) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$id, $opt['text'], (int)($opt['weight'] ?? 0), $i]);
                }
            }
        }

        Session::setFlash('success', 'Question updated successfully.');
        header('Location: /admin/quiz');
        exit;
    }

    public function deleteQuiz(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        QuizQuestion::delete($id);
        Session::setFlash('success', 'Question deleted.');
        header('Location: /admin/quiz');
        exit;
    }

    public function quizOptions(int $questionId): void
    {
        $question = QuizQuestion::findById($questionId);
        if (!$question) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }
        $options = QuizOption::getByQuestion($questionId);
        View::render('admin/quiz-options', [
            'title' => 'Quiz Options',
            'question' => $question,
            'options' => $options,
        ], 'dashboard');
    }

    public function addQuizOptionForm(int $questionId): void
    {
        $question = QuizQuestion::findById($questionId);
        if (!$question) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }
        View::render('admin/quiz-options-add', [
            'title' => 'Add Option',
            'question' => $question,
            'csrf_token' => Session::csrf_token(),
        ], 'dashboard');
    }

    public function addQuizOption(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, [
            'question_id' => 'required',
            'option_text' => 'required',
        ])) {
            Session::setFlash('error', 'Option text is required.');
            header('Location: /admin/quiz/options/add/' . (int)$_POST['question_id']);
            exit;
        }

        QuizOption::create([
            'question_id' => (int)$_POST['question_id'],
            'option_text' => $_POST['option_text'],
            'weight' => (int)($_POST['weight'] ?? 0),
            'order_index' => (int)($_POST['order_index'] ?? 0),
        ]);

        Session::setFlash('success', 'Option added.');
        header('Location: /admin/quiz/' . (int)$_POST['question_id'] . '/options');
        exit;
    }

    public function editQuizOptionForm(int $id): void
    {
        $option = QuizOption::findById($id);
        if (!$option) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }
        $question = QuizQuestion::findById($option['question_id']);
        View::render('admin/quiz-options-edit', [
            'title' => 'Edit Option',
            'option' => $option,
            'question' => $question,
            'csrf_token' => Session::csrf_token(),
        ], 'dashboard');
    }

    public function editQuizOption(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $option = QuizOption::findById($id);
        if (!$option) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, ['option_text' => 'required'])) {
            Session::setFlash('error', 'Option text is required.');
            header('Location: /admin/quiz/options/' . $id . '/edit');
            exit;
        }

        QuizOption::update($id, [
            'option_text' => $_POST['option_text'],
            'weight' => (int)($_POST['weight'] ?? 0),
            'order_index' => (int)($_POST['order_index'] ?? 0),
        ]);

        Session::setFlash('success', 'Option updated.');
        header('Location: /admin/quiz/' . (int)$option['question_id'] . '/options');
        exit;
    }

    public function deleteQuizOption(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $option = QuizOption::findById($id);
        if (!$option) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }

        QuizOption::delete($id);
        Session::setFlash('success', 'Option deleted.');
        header('Location: /admin/quiz/' . (int)$option['question_id'] . '/options');
        exit;
    }

    public function activities(): void
    {
        $activities = Activity::getAll();
        View::render('admin/activities', ['title' => 'Activities', 'activities' => $activities], 'dashboard');
    }

    public function addActivityForm(): void
    {
        View::render('admin/activities-add', ['title' => 'Add Activity', 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function addActivity(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, [
            'title' => 'required|max:255',
            'category' => 'required|in:games,puzzles,stories,video,coloring',
            'difficulty' => 'required|in:easy,medium,hard',
        ])) {
            View::render('admin/activities-add', ['errors' => $validator->errors(), 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        Activity::create([
            'title' => $_POST['title'],
            'description' => $_POST['description'] ?? null,
            'category' => $_POST['category'],
            'difficulty' => $_POST['difficulty'],
            'image_url' => $_POST['image_url'] ?? null,
        ]);

        Session::setFlash('success', 'Activity added.');
        header('Location: /admin/activities');
        exit;
    }

    public function editActivityForm(int $id): void
    {
        $activity = Activity::findById($id);
        if (!$activity) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }
        View::render('admin/activities-edit', ['title' => 'Edit Activity', 'activity' => $activity, 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function editActivity(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $activity = Activity::findById($id);
        if (!$activity) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, ['title' => 'required|max:255', 'category' => 'required|in:games,puzzles,stories,video,coloring', 'difficulty' => 'required|in:easy,medium,hard'])) {
            View::render('admin/activities-edit', ['activity' => $activity, 'errors' => $validator->errors(), 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        Activity::update($id, [
            'title' => $_POST['title'],
            'description' => $_POST['description'] ?? null,
            'category' => $_POST['category'],
            'difficulty' => $_POST['difficulty'],
            'image_url' => $_POST['image_url'] ?? null,
            'is_active' => $_POST['is_active'] ?? 1,
        ]);

        Session::setFlash('success', 'Activity updated.');
        header('Location: /admin/activities');
        exit;
    }

    public function deleteActivity(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        Activity::delete($id);
        Session::setFlash('success', 'Activity deleted.');
        header('Location: /admin/activities');
        exit;
    }

    public function appointments(): void
    {
        $allAppointments = Appointment::getAll();
        View::render('admin/appointments', ['title' => 'Appointments', 'appointments' => $allAppointments, 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function messages(): void
    {
        $db = Database::getInstance();
        $stmt = $db->query('
            SELECT m.*, s.name AS sender_name, r.name AS receiver_name
            FROM messages m
            JOIN users s ON m.sender_id = s.id
            JOIN users r ON m.receiver_id = r.id
            ORDER BY m.created_at DESC
        ');
        $allMessages = $stmt ? $stmt->fetchAll() : [];
        View::render('admin/messages', ['title' => 'Messages', 'messages' => $allMessages], 'dashboard');
    }

    public function subscriptions(): void
    {
        $all = Subscription::getAll();
        View::render('admin/subscriptions', ['title' => 'Subscriptions', 'subscriptions' => $all, 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function deleteSubscription(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        Subscription::delete($id);
        Session::setFlash('success', 'Subscription deleted.');
        header('Location: /admin/subscriptions');
        exit;
    }

    public function addSubscriptionForm(): void
    {
        $parents = User::getAllByRole('parent');
        View::render('admin/subscriptions-add', ['title' => 'Add Subscription', 'parents' => $parents, 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function addSubscription(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, ['user_id' => 'required', 'plan' => 'required|in:standard,premium,family'])) {
            Session::setFlash('error', 'Invalid subscription data.');
            header('Location: /admin/subscriptions/add');
            exit;
        }

        Subscription::create([
            'user_id' => (int)$_POST['user_id'],
            'plan' => $_POST['plan'],
            'status' => $_POST['status'] ?? 'active',
            'ends_at' => $_POST['ends_at'] ?? null,
        ]);

        Session::setFlash('success', 'Subscription created.');
        header('Location: /admin/subscriptions');
        exit;
    }

    public function editSubscriptionForm(int $id): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT s.*, u.name AS user_name FROM subscriptions s JOIN users u ON s.user_id = u.id WHERE s.id = ?');
        $stmt->execute([$id]);
        $subscription = $stmt->fetch();

        if (!$subscription) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }

        View::render('admin/subscriptions-edit', ['title' => 'Edit Subscription', 'subscription' => $subscription, 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function editSubscription(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        Subscription::update($id, [
            'plan' => $_POST['plan'],
            'status' => $_POST['status'],
            'ends_at' => $_POST['ends_at'] ?? null,
        ]);

        Session::setFlash('success', 'Subscription updated.');
        header('Location: /admin/subscriptions');
        exit;
    }

    public function contacts(): void
    {
        $db = Database::getInstance();
        $stmt = $db->query('SELECT * FROM contact_messages ORDER BY created_at DESC');
        $contacts = $stmt ? $stmt->fetchAll() : [];
        View::render('admin/contacts', ['title' => 'Contacts', 'contacts' => $contacts, 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function markContactRead(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?');
        $stmt->execute([$id]);

        header('Location: /admin/contacts');
        exit;
    }

    public function deleteContact(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM contact_messages WHERE id = ?');
        $stmt->execute([$id]);

        Session::setFlash('success', 'Contact message deleted.');
        header('Location: /admin/contacts');
        exit;
    }

    public function faq(): void
    {
        $db = Database::getInstance();
        $stmt = $db->query('SELECT * FROM faq_items ORDER BY category, order_index');
        $faqs = $stmt ? $stmt->fetchAll() : [];
        View::render('admin/faq', ['title' => 'FAQ', 'faqs' => $faqs], 'dashboard');
    }

    public function addFaqForm(): void
    {
        View::render('admin/faq-add', ['title' => 'Add FAQ', 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function addFaq(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, [
            'question' => 'required',
            'answer' => 'required',
            'category' => 'required|in:general,features,pricing,technical',
        ])) {
            View::render('admin/faq-add', ['errors' => $validator->errors(), 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO faq_items (question, answer, category, order_index) VALUES (?, ?, ?, ?)');
        $stmt->execute([$_POST['question'], $_POST['answer'], $_POST['category'], (int)($_POST['order_index'] ?? 0)]);

        Session::setFlash('success', 'FAQ added.');
        header('Location: /admin/faq');
        exit;
    }

    public function editFaqForm(int $id): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM faq_items WHERE id = ?');
        $stmt->execute([$id]);
        $faq = $stmt->fetch();

        if (!$faq) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }

        View::render('admin/faq-edit', ['title' => 'Edit FAQ', 'faq' => $faq, 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function editFaq(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, ['question' => 'required', 'answer' => 'required', 'category' => 'required|in:general,features,pricing,technical'])) {
            Session::setFlash('error', 'Invalid FAQ data.');
            header('Location: /admin/faq/' . $id . '/edit');
            exit;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE faq_items SET question = ?, answer = ?, category = ?, order_index = ?, is_active = ? WHERE id = ?');
        $stmt->execute([$_POST['question'], $_POST['answer'], $_POST['category'], (int)($_POST['order_index'] ?? 0), $_POST['is_active'] ?? 1, $id]);

        Session::setFlash('success', 'FAQ updated.');
        header('Location: /admin/faq');
        exit;
    }

    public function deleteFaq(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM faq_items WHERE id = ?');
        $stmt->execute([$id]);

        Session::setFlash('success', 'FAQ deleted.');
        header('Location: /admin/faq');
        exit;
    }

    public function chatbot(): void
    {
        $responses = ChatbotResponse::getAll();
        $openrouterModel = Env::get('OPENROUTER_MODEL', 'google/gemma-4-31b-it:free');
        $apiKeySet = !empty(Env::get('OPENROUTER_API_KEY', ''));
        View::render('admin/chatbot', [
            'title' => 'Chatbot Responses',
            'responses' => $responses,
            'openrouterModel' => $openrouterModel,
            'apiKeySet' => $apiKeySet,
            'csrf_token' => Session::csrf_token(),
        ], 'dashboard');
    }

    public function addChatbotForm(): void
    {
        View::render('admin/chatbot-add', ['title' => 'Add Chatbot Response', 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function addChatbot(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, ['keywords' => 'required', 'response_text' => 'required'])) {
            View::render('admin/chatbot-add', ['errors' => $validator->errors(), 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        ChatbotResponse::create([
            'keywords' => $_POST['keywords'],
            'response_text' => $_POST['response_text'],
            'category' => $_POST['category'] ?? null,
        ]);

        Session::setFlash('success', 'Chatbot response added.');
        header('Location: /admin/chatbot');
        exit;
    }

    public function editChatbotForm(int $id): void
    {
        $response = ChatbotResponse::findById($id);
        if (!$response) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }
        View::render('admin/chatbot-edit', ['title' => 'Edit Chatbot Response', 'response' => $response, 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function editChatbot(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $response = ChatbotResponse::findById($id);
        if (!$response) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, ['keywords' => 'required', 'response_text' => 'required'])) {
            View::render('admin/chatbot-edit', ['response' => $response, 'errors' => $validator->errors(), 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        ChatbotResponse::update($id, [
            'keywords' => $_POST['keywords'],
            'response_text' => $_POST['response_text'],
            'category' => $_POST['category'] ?? null,
            'is_active' => $_POST['is_active'] ?? 1,
        ]);

        Session::setFlash('success', 'Chatbot response updated.');
        header('Location: /admin/chatbot');
        exit;
    }

    public function deleteChatbot(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        ChatbotResponse::delete($id);
        Session::setFlash('success', 'Chatbot response deleted.');
        header('Location: /admin/chatbot');
        exit;
    }

    public function updateChatbotConfig(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $model = $_POST['openrouter_model'] ?? '';
        if (!empty($model)) {
            Env::update('OPENROUTER_MODEL', $model);
        }

        Session::setFlash('success', 'OpenRouter configuration updated.');
        header('Location: /admin/chatbot');
        exit;
    }

    public function progress(): void
    {
        $db = Database::getInstance();
        $stmt = $db->query('
            SELECT c.*, u.name AS parent_name,
                (SELECT COUNT(*) FROM child_progress cp WHERE cp.child_id = c.id) AS activities_completed,
                ROUND((SELECT AVG(score) FROM child_progress cp WHERE cp.child_id = c.id AND cp.score IS NOT NULL), 1) AS avg_score,
                (SELECT COUNT(*) FROM quiz_attempts qa WHERE qa.child_id = c.id) AS quiz_count
            FROM children c
            JOIN users u ON c.parent_id = u.id
            ORDER BY c.name
        ');
        $children = $stmt ? $stmt->fetchAll() : [];
        View::render('admin/progress', ['title' => 'Child Progress', 'children' => $children], 'dashboard');
    }

    public function childProgress(int $childId): void
    {
        $child = Child::findById($childId);
        if (!$child) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT u.name AS parent_name FROM users u WHERE u.id = ?');
        $stmt->execute([$child['parent_id']]);
        $parent = $stmt->fetch();

        $activities = ChildProgress::getByChild($childId);
        $avgScore = ChildProgress::averageScoreByChild($childId);
        $totalActivities = ChildProgress::countByChild($childId);

        $stmt = $db->prepare('SELECT qa.*, q.question_text FROM quiz_attempts qa LEFT JOIN quiz_questions q ON qa.question_id = q.id WHERE qa.child_id = ? ORDER BY qa.completed_at DESC LIMIT 20');
        $stmt->execute([$childId]);
        $quizAttempts = $stmt->fetchAll();

        View::render('admin/progress-child', [
            'title' => 'Child Progress - ' . htmlspecialchars($child['name']),
            'child' => $child,
            'parent' => $parent,
            'activities' => $activities,
            'avgScore' => $avgScore,
            'totalActivities' => $totalActivities,
            'quizAttempts' => $quizAttempts,
        ], 'dashboard');
    }

    public function settings(): void
    {
        $user = User::findById($this->getAdminId());
        View::render('admin/settings', ['title' => 'Settings', 'user' => $user, 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function updateSettings(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, ['name' => 'required|max:255'])) {
            $user = User::findById($this->getAdminId());
            View::render('admin/settings', ['errors' => $validator->errors(), 'user' => $user, 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        $userId = $this->getAdminId();
        $avatarPath = $this->handleAvatarUpload($userId);
        $db = Database::getInstance();

        if ($avatarPath) {
            $stmt = $db->prepare('UPDATE users SET name = ?, email = ?, phone = ?, avatar = ? WHERE id = ?');
            $stmt->execute([$_POST['name'], $_POST['email'] ?? '', $_POST['phone'] ?? null, $avatarPath, $userId]);
        } else {
            $stmt = $db->prepare('UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?');
            $stmt->execute([$_POST['name'], $_POST['email'] ?? '', $_POST['phone'] ?? null, $userId]);
        }

        Session::set('user_name', $_POST['name']);

        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 8) {
                Session::setFlash('error', 'Password must be at least 8 characters.');
                header('Location: /admin/settings');
                exit;
            }
            $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([password_hash($_POST['password'], PASSWORD_BCRYPT), $userId]);
        }

        Session::setFlash('success', 'Settings updated.');
        header('Location: /admin/settings');
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
}
