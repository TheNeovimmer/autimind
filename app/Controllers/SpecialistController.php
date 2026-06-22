<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Core\Validator;
use App\Core\Database;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Message;
use App\Models\Child;
use PDO;

class SpecialistController
{
    private function getSpecialistId(): int
    {
        return (int) Session::get('user_id');
    }

    public function dashboard(): void
    {
        $specialistId = $this->getSpecialistId();
        $totalAppointments = Appointment::countBySpecialist($specialistId);
        $pendingAppointments = Appointment::countBySpecialist($specialistId, 'pending');
        $upcoming = Appointment::getUpcomingBySpecialist($specialistId);
        $unreadMessages = Message::countUnread($specialistId);

        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT COUNT(DISTINCT child_id) FROM appointments WHERE specialist_id = ?');
        $stmt->execute([$specialistId]);
        $totalPatients = (int) $stmt->fetchColumn();

        // Weekly appointment trend (last 8 weeks)
        $stmt = $db->prepare("
            SELECT
                YEARWEEK(date, 1) as yw,
                DATE_FORMAT(DATE_SUB(date, INTERVAL WEEKDAY(date) DAY), '%Y-%m-%d') as week_start,
                COUNT(*) as count
            FROM appointments
            WHERE specialist_id = ?
                AND date >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)
            GROUP BY YEARWEEK(date, 1), week_start
            ORDER BY yw ASC
        ");
        $stmt->execute([$specialistId]);
        $weeklyAppts = $stmt->fetchAll();

        // Patient age distribution
        $stmt = $db->prepare("
            SELECT
                CASE
                    WHEN c.age BETWEEN 0 AND 3 THEN '0-3'
                    WHEN c.age BETWEEN 4 AND 6 THEN '4-6'
                    WHEN c.age BETWEEN 7 AND 9 THEN '7-9'
                    WHEN c.age BETWEEN 10 AND 12 THEN '10-12'
                    ELSE '13+'
                END as age_range,
                COUNT(DISTINCT c.id) as count
            FROM children c
            JOIN appointments a ON a.child_id = c.id
            WHERE a.specialist_id = ?
                AND c.age IS NOT NULL
            GROUP BY age_range
            ORDER BY age_range
        ");
        $stmt->execute([$specialistId]);
        $ageDist = $stmt->fetchAll();

        // Quiz risk level distribution
        $stmt = $db->prepare("
            SELECT qa.risk_level, COUNT(*) as count
            FROM quiz_attempts qa
            JOIN appointments a ON a.child_id = qa.child_id
            WHERE a.specialist_id = ?
                AND qa.risk_level IS NOT NULL
            GROUP BY qa.risk_level
        ");
        $stmt->execute([$specialistId]);
        $riskDist = $stmt->fetchAll();

        // Appointment status counts
        $stmt = $db->prepare("
            SELECT status, COUNT(*) as count
            FROM appointments
            WHERE specialist_id = ?
            GROUP BY status
        ");
        $stmt->execute([$specialistId]);
        $statusCounts = $stmt->fetchAll();

        View::render('specialist/dashboard', [
            'title' => 'Specialist Dashboard',
            'totalAppointments' => $totalAppointments,
            'pendingAppointments' => $pendingAppointments,
            'upcoming' => $upcoming,
            'unreadMessages' => $unreadMessages,
            'totalPatients' => $totalPatients,
            'weeklyAppts' => $weeklyAppts,
            'ageDist' => $ageDist,
            'riskDist' => $riskDist,
            'statusCounts' => $statusCounts,
        ], 'dashboard');
    }

    public function patients(): void
    {
        $specialistId = $this->getSpecialistId();
        $db = Database::getInstance();

        $stmt = $db->prepare('
            SELECT DISTINCT c.*, u.name AS parent_name, u.email AS parent_email,
                (SELECT COUNT(*) FROM appointments WHERE child_id = c.id AND specialist_id = ?) AS appointment_count,
                (SELECT MAX(completed_at) FROM quiz_attempts qa WHERE qa.child_id = c.id AND qa.status = "completed") AS last_screening
            FROM children c
            JOIN appointments a ON c.id = a.child_id
            JOIN users u ON c.parent_id = u.id
            WHERE a.specialist_id = ?
            ORDER BY c.name
        ');
        $stmt->execute([$specialistId, $specialistId]);
        $patients = $stmt->fetchAll();

        View::render('specialist/patients', ['title' => 'Patients', 'patients' => $patients], 'dashboard');
    }

    public function patientDetail(int $id): void
    {
        $child = Child::findById($id);
        if (!$child) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM appointments WHERE child_id = ? AND specialist_id = ? ORDER BY date DESC');
        $stmt->execute([$id, $this->getSpecialistId()]);
        $appointments = $stmt->fetchAll();

        $stmt = $db->prepare('SELECT * FROM quiz_attempts WHERE child_id = ? AND status = "completed" ORDER BY completed_at DESC');
        $stmt->execute([$id]);
        $quizHistory = $stmt->fetchAll();

        $quizBreakdowns = [];
        foreach ($quizHistory as $qh) {
            $quizBreakdowns[$qh['id']] = $this->getQuizCategoryBreakdown($qh['id']);
        }

        $parent = User::findById($child['parent_id']);

        View::render('specialist/patients-detail', [
            'title' => 'Patient: ' . $child['name'],
            'child' => $child,
            'parent' => $parent,
            'appointments' => $appointments,
            'quizHistory' => $quizHistory,
            'quizBreakdowns' => $quizBreakdowns,
            'csrf_token' => Session::csrf_token(),
        ], 'dashboard');
    }

    public function appointments(): void
    {
        $appointments = Appointment::getAllBySpecialist($this->getSpecialistId());
        View::render('specialist/appointments', ['title' => 'Appointments', 'appointments' => $appointments], 'dashboard');
    }

    public function updateAppointmentStatus(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $appointment = Appointment::findById($id);
        if (!$appointment || $appointment['specialist_id'] !== $this->getSpecialistId()) {
            http_response_code(403);
            View::render('errors/403', [], 'dashboard');
            return;
        }

        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['confirmed', 'cancelled', 'completed'])) {
            Session::setFlash('error', 'Invalid status.');
            header('Location: /specialist/appointments');
            exit;
        }

        Appointment::updateStatus($id, $status);
        Session::setFlash('success', 'Appointment status updated.');
        header('Location: /specialist/appointments');
        exit;
    }

    public function messages(): void
    {
        $specialistId = $this->getSpecialistId();
        $inbox = Message::getInbox($specialistId);
        $sent = Message::getSent($specialistId);
        $partners = Message::getConversationPartners($specialistId);
        $parents = User::getAllByRole('parent');

        View::render('specialist/messages', [
            'title' => 'Messages',
            'inbox' => $inbox,
            'sent' => $sent,
            'partners' => $partners,
            'parents' => $parents,
        ], 'dashboard');
    }

    public function messageThread(int $partnerId): void
    {
        $partner = User::findById($partnerId);
        if (!$partner) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }

        $thread = Message::getThread($this->getSpecialistId(), $partnerId);

        foreach ($thread as $msg) {
            if ($msg['receiver_id'] === $this->getSpecialistId() && !$msg['is_read']) {
                Message::markAsRead($msg['id']);
            }
        }

        View::render('specialist/messages-thread', [
            'title' => 'Messages',
            'partner' => $partner,
            'thread' => $thread,
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
            'body' => 'required',
        ])) {
            Session::setFlash('error', 'Message cannot be empty.');
            $receiverId = (int)($_POST['receiver_id'] ?? 0);
            header('Location: /specialist/messages/thread/' . $receiverId);
            exit;
        }

        Message::send([
            'sender_id' => $this->getSpecialistId(),
            'receiver_id' => (int)$_POST['receiver_id'],
            'subject' => $_POST['subject'] ?? 'Re: Message',
            'body' => $_POST['body'],
        ]);

        Session::setFlash('success', 'Message sent.');
        header('Location: /specialist/messages/thread/' . (int)$_POST['receiver_id']);
        exit;
    }

    public function schedule(): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM specialist_details WHERE user_id = ?');
        $stmt->execute([$this->getSpecialistId()]);
        $details = $stmt->fetch();

        $availability = [];
        if (!empty($details['specializations'])) {
            $decoded = json_decode($details['specializations'], true);
            if (is_array($decoded) && isset($decoded['availability'])) {
                $availability = $decoded['availability'];
            }
        }

        View::render('specialist/schedule', [
            'title' => 'Schedule',
            'details' => $details,
            'availability' => $availability,
            'csrf_token' => Session::csrf_token(),
        ], 'dashboard');
    }

    public function updateSchedule(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $db = Database::getInstance();

        $stmt = $db->prepare('UPDATE specialist_details SET is_available = ?, title = ?, bio = ? WHERE user_id = ?');
        $stmt->execute([
            $_POST['is_available'] ?? 1,
            $_POST['title'] ?? '',
            $_POST['bio'] ?? '',
            $this->getSpecialistId(),
        ]);

        $this->updateTimeSlots();

        Session::setFlash('success', 'Schedule updated.');
        header('Location: /specialist/schedule');
        exit;
    }

    private function updateTimeSlots(): void
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $availability = [];
        foreach ($days as $day) {
            $start = $_POST[$day . '_start'] ?? '';
            $end = $_POST[$day . '_end'] ?? '';
            if (!empty($start) && !empty($end)) {
                $availability[$day] = ['start' => $start, 'end' => $end];
            }
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT specializations FROM specialist_details WHERE user_id = ?');
        $stmt->execute([$this->getSpecialistId()]);
        $row = $stmt->fetch();
        $existing = $row['specializations'] ?? '';

        $data = json_decode($existing, true);
        if (!is_array($data)) {
            $data = ['specializations' => $existing];
        }
        $data['availability'] = $availability;

        $stmt = $db->prepare('UPDATE specialist_details SET specializations = ? WHERE user_id = ?');
        $stmt->execute([json_encode($data), $this->getSpecialistId()]);
    }

    public function settings(): void
    {
        $user = User::findById($this->getSpecialistId());
        View::render('specialist/settings', ['title' => 'Settings', 'user' => $user, 'csrf_token' => Session::csrf_token()], 'dashboard');
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
            $user = User::findById($this->getSpecialistId());
            View::render('specialist/settings', ['errors' => $validator->errors(), 'user' => $user, 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        $userId = $this->getSpecialistId();
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
                header('Location: /specialist/settings');
                exit;
            }
            $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([password_hash($_POST['password'], PASSWORD_BCRYPT), $userId]);
        }

        Session::setFlash('success', 'Settings updated.');
        header('Location: /specialist/settings');
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

    public function addPatientNote(int $childId): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $child = Child::findById($childId);
        if (!$child) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT COUNT(*) FROM appointments WHERE child_id = ? AND specialist_id = ?');
        $stmt->execute([$childId, $this->getSpecialistId()]);
        if ((int)$stmt->fetchColumn() === 0) {
            http_response_code(403);
            View::render('errors/403', [], 'dashboard');
            return;
        }

        $notes = $_POST['notes'] ?? '';
        Child::update($childId, [
            'name' => $child['name'],
            'age' => $child['age'],
            'birth_date' => $child['birth_date'],
            'diagnosis_status' => $child['diagnosis_status'],
            'notes' => $notes,
        ]);

        Session::setFlash('success', 'Observation notes updated.');
        header('Location: /specialist/patients/' . $childId);
        exit;
    }

    public function sendMessageForm(int $receiverId): void
    {
        $receiver = User::findById($receiverId);
        if (!$receiver) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }

        View::render('specialist/messages-send', [
            'title' => 'New Message',
            'receiver' => $receiver,
            'csrf_token' => Session::csrf_token(),
        ], 'dashboard');
    }

    public function calendar(): void
    {
        $year = (int)($_GET['year'] ?? date('Y'));
        $month = (int)($_GET['month'] ?? date('n'));
        if ($month < 1) { $month = 1; $year--; }
        if ($month > 12) { $month = 12; $year++; }

        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth = (int)date('t', $firstDay);
        $startDow = (int)date('w', $firstDay);
        $monthName = date('F Y', $firstDay);

        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT DAY(a.date) AS day, a.date, a.time, a.status, c.name AS child_name
            FROM appointments a
            JOIN children c ON a.child_id = c.id
            WHERE a.specialist_id = ?
              AND YEAR(a.date) = ?
              AND MONTH(a.date) = ?
            ORDER BY a.date, a.time
        ");
        $stmt->execute([$this->getSpecialistId(), $year, $month]);
        $appointments = $stmt->fetchAll();

        $dayAppts = [];
        foreach ($appointments as $apt) {
            $d = (int)$apt['day'];
            if (!isset($dayAppts[$d])) {
                $dayAppts[$d] = ['count' => 0, 'children' => []];
            }
            $dayAppts[$d]['count']++;
            if (!in_array($apt['child_name'], $dayAppts[$d]['children'])) {
                $dayAppts[$d]['children'][] = $apt['child_name'];
            }
        }

        View::render('specialist/calendar', [
            'title' => 'Calendar - ' . $monthName,
            'year' => $year,
            'month' => $month,
            'monthName' => $monthName,
            'daysInMonth' => $daysInMonth,
            'startDow' => $startDow,
            'prevMonth' => $prevMonth,
            'prevYear' => $prevYear,
            'nextMonth' => $nextMonth,
            'nextYear' => $nextYear,
            'dayAppts' => $dayAppts,
        ], 'dashboard');
    }

    public function exportPatientsCSV(): void
    {
        $specialistId = $this->getSpecialistId();
        $db = Database::getInstance();

        $stmt = $db->prepare('
            SELECT DISTINCT c.*, u.name AS parent_name, u.email AS parent_email,
                (SELECT COUNT(*) FROM appointments WHERE child_id = c.id AND specialist_id = ?) AS appointment_count,
                (SELECT MAX(completed_at) FROM quiz_attempts qa WHERE qa.child_id = c.id AND qa.status = "completed") AS last_screening
            FROM children c
            JOIN appointments a ON c.id = a.child_id
            JOIN users u ON c.parent_id = u.id
            WHERE a.specialist_id = ?
            ORDER BY c.name
        ');
        $stmt->execute([$specialistId, $specialistId]);
        $patients = $stmt->fetchAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="patients_export_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['child_name', 'parent_name', 'parent_email', 'age', 'diagnosis_status', 'appointment_count', 'last_screening_date']);

        foreach ($patients as $p) {
            fputcsv($output, [
                $p['name'],
                $p['parent_name'],
                $p['parent_email'],
                $p['age'] ?? '',
                $p['diagnosis_status'] ?? '',
                (int)$p['appointment_count'],
                $p['last_screening'] ?? '',
            ]);
        }

        fclose($output);
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
        if (!$appointment || $appointment['specialist_id'] !== $this->getSpecialistId()) {
            http_response_code(403);
            View::render('errors/403', [], 'dashboard');
            return;
        }

        Appointment::updateStatus($id, 'cancelled');
        Session::setFlash('success', 'Appointment cancelled.');
        header('Location: /specialist/appointments');
        exit;
    }

    public function completeAppointment(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $appointment = Appointment::findById($id);
        if (!$appointment || $appointment['specialist_id'] !== $this->getSpecialistId()) {
            http_response_code(403);
            View::render('errors/403', [], 'dashboard');
            return;
        }

        Appointment::updateStatus($id, 'completed');
        Session::setFlash('success', 'Appointment completed.');
        header('Location: /specialist/appointments');
        exit;
    }

    private function getQuizCategoryBreakdown(int $attemptId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT q.category, SUM(o.weight) AS score
            FROM quiz_answers a
            JOIN quiz_questions q ON a.question_id = q.id
            JOIN quiz_options o ON a.option_id = o.id
            WHERE a.attempt_id = ?
            GROUP BY q.category
        ');
        $stmt->execute([$attemptId]);
        return $stmt->fetchAll();
    }
}
