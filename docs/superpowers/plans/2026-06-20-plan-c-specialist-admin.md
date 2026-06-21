# Plan C: Specialist & Admin Dashboards Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the complete specialist dashboard (patients, appointments, messages, schedule) and admin dashboard (users CRUD, quiz CRUD, activities CRUD, appointments, subscriptions, contacts, FAQ CRUD, settings) with full views and controllers.

**Architecture:** Flat MVC with Service Layer. `SpecialistController` and `AdminController` follow same conventions as `ParentController`. Models already created in Plan B are shared.

**Tech Stack:** PHP 8.x, MySQL via PDO, Session-based auth, existing CSS/JS preserved

**Key Conventions:**
- All models in `app/Models/`, services in `app/Services/`
- Controller methods return `void` and call `View::render()` or redirect
- Views use `<?= htmlspecialchars($var) ?>` for user content
- CSRF token on all POST forms via `Session::csrf_token()`
- Flash messages via `Session::setFlash()` / `getFlash()`

---

### Task 1: Update SpecialistController with Full Implementation

**Files:**
- Modify: `app/Controllers/SpecialistController.php`

- [ ] **Step 1: Replace the full SpecialistController**

```php
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

        View::render('specialist/dashboard', [
            'title' => 'Specialist Dashboard',
            'totalAppointments' => $totalAppointments,
            'pendingAppointments' => $pendingAppointments,
            'upcoming' => $upcoming,
            'unreadMessages' => $unreadMessages,
            'totalPatients' => $totalPatients,
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

        $parent = User::findById($child['parent_id']);

        View::render('specialist/patients-detail', [
            'title' => 'Patient: ' . $child['name'],
            'child' => $child,
            'parent' => $parent,
            'appointments' => $appointments,
            'quizHistory' => $quizHistory,
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

        View::render('specialist/messages', [
            'title' => 'Messages',
            'inbox' => $inbox,
            'sent' => $sent,
            'partners' => $partners,
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

        View::render('specialist/schedule', [
            'title' => 'Schedule',
            'details' => $details,
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

        Session::setFlash('success', 'Schedule updated.');
        header('Location: /specialist/schedule');
        exit;
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

        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?');
        $stmt->execute([$_POST['name'], $_POST['phone'] ?? null, $this->getSpecialistId()]);
        Session::set('user_name', $_POST['name']);

        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 8) {
                Session::setFlash('error', 'Password must be at least 8 characters.');
                header('Location: /specialist/settings');
                exit;
            }
            $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([password_hash($_POST['password'], PASSWORD_BCRYPT), $this->getSpecialistId()]);
        }

        Session::setFlash('success', 'Settings updated.');
        header('Location: /specialist/settings');
        exit;
    }
}
```

---

### Task 2: Create Specialist Dashboard Views

**Files:**
- Create: `app/Views/specialist/dashboard.php` (overwrite)
- Create: `app/Views/specialist/patients.php`
- Create: `app/Views/specialist/patients-detail.php`
- Create: `app/Views/specialist/appointments.php`
- Create: `app/Views/specialist/messages.php`
- Create: `app/Views/specialist/messages-thread.php`
- Create: `app/Views/specialist/schedule.php`
- Create: `app/Views/specialist/settings.php`

- [ ] **Step 1: Create `app/Views/specialist/dashboard.php`**

```php
<div class="dash-header">
  <div>
    <h1>Specialist Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars(\App\Core\Session::get('user_name')) ?></p>
  </div>
</div>

<div class="dash-grid dash-grid-2">
  <div class="dash-card">
    <div class="dash-card-header">
      <h3><i class="fas fa-users"></i> Total Patients</h3>
      <span class="dash-badge"><?= (int)$totalPatients ?></span>
    </div>
    <a href="/specialist/patients" class="dash-link">View Patients →</a>
  </div>

  <div class="dash-card">
    <div class="dash-card-header">
      <h3><i class="fas fa-calendar-check"></i> Appointments</h3>
      <span class="dash-badge"><?= (int)$totalAppointments ?> total</span>
    </div>
    <?php if ($pendingAppointments > 0): ?>
      <p><span class="risk-moderate" style="padding:0.2rem 0.5rem;border-radius:4px;"><?= (int)$pendingAppointments ?> pending confirmation</span></p>
    <?php endif; ?>
    <a href="/specialist/appointments" class="dash-link">Manage Appointments →</a>
  </div>

  <div class="dash-card">
    <div class="dash-card-header">
      <h3><i class="fas fa-arrow-right"></i> Upcoming</h3>
    </div>
    <?php if (!empty($upcoming)): ?>
      <ul class="appointment-list-compact">
        <?php foreach (array_slice($upcoming, 0, 5) as $apt): ?>
          <li>
            <strong><?= htmlspecialchars($apt['date']) ?></strong> at <?= htmlspecialchars(substr($apt['time'], 0, 5)) ?>
            <br><small>with <?= htmlspecialchars($apt['child_name']) ?></small>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="dash-empty">No upcoming appointments.</p>
    <?php endif; ?>
  </div>

  <div class="dash-card">
    <div class="dash-card-header">
      <h3><i class="fas fa-envelope"></i> Messages</h3>
      <?php if ($unreadMessages > 0): ?>
        <span class="dash-badge dash-badge-warning"><?= (int)$unreadMessages ?> unread</span>
      <?php endif; ?>
    </div>
    <p><?= $unreadMessages > 0 ? 'You have ' . $unreadMessages . ' unread messages.' : 'No unread messages.' ?></p>
    <a href="/specialist/messages" class="dash-link">Go to Messages →</a>
  </div>
</div>
```

- [ ] **Step 2: Create `app/Views/specialist/patients.php`**

```php
<div class="dash-header">
  <div>
    <h1>Patients</h1>
    <p>Children assigned to you</p>
  </div>
</div>

<?php if (!empty($patients)): ?>
<div class="table-responsive">
  <table class="dash-table">
    <thead>
      <tr><th>Child</th><th>Parent</th><th>Age</th><th>Diagnosis</th><th>Appointments</th><th>Last Screening</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($patients as $p): ?>
        <tr>
          <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
          <td><?= htmlspecialchars($p['parent_name']) ?></td>
          <td><?= $p['age'] ? (int)$p['age'] . ' yrs' : '-' ?></td>
          <td><?= htmlspecialchars($p['diagnosis_status'] ?? '-') ?></td>
          <td><?= (int)$p['appointment_count'] ?></td>
          <td><?= htmlspecialchars($p['last_screening'] ?? '-') ?></td>
          <td><a href="/specialist/patients/<?= (int)$p['id'] ?>" class="btn-sm btn-outline">View</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="dash-empty-state">
  <h3>No patients yet</h3>
  <p>Patients will appear here once parents book appointments with you.</p>
</div>
<?php endif; ?>
```

- [ ] **Step 3: Create `app/Views/specialist/patients-detail.php`**

```php
<div class="dash-header">
  <div>
    <h1><?= htmlspecialchars($child['name']) ?></h1>
    <p>Patient details</p>
  </div>
  <a href="/specialist/messages/thread/<?= (int)$parent['id'] ?>" class="btn btn-outline"><i class="fas fa-envelope"></i> Message Parent</a>
</div>

<div class="dash-grid dash-grid-2 mb-2">
  <div class="dash-card">
    <h3>Child Information</h3>
    <p><strong>Age:</strong> <?= $child['age'] ? (int)$child['age'] . ' yrs' : '-' ?></p>
    <p><strong>Birth Date:</strong> <?= htmlspecialchars($child['birth_date'] ?? '-') ?></p>
    <p><strong>Diagnosis:</strong> <?= htmlspecialchars($child['diagnosis_status'] ?? 'Not specified') ?></p>
    <p><strong>Notes:</strong> <?= nl2br(htmlspecialchars($child['notes'] ?? 'None')) ?></p>
  </div>

  <div class="dash-card">
    <h3>Parent Information</h3>
    <p><strong>Name:</strong> <?= htmlspecialchars($parent['name']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($parent['email']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($parent['phone'] ?? '-') ?></p>
  </div>
</div>

<div class="dash-card mb-2">
  <h3>Appointment History</h3>
  <?php if (!empty($appointments)): ?>
    <table class="dash-table">
      <thead><tr><th>Date</th><th>Time</th><th>Status</th><th>Notes</th></tr></thead>
      <tbody>
        <?php foreach ($appointments as $apt): ?>
          <tr>
            <td><?= htmlspecialchars($apt['date']) ?></td>
            <td><?= htmlspecialchars(substr($apt['time'], 0, 5)) ?></td>
            <td><span class="status-<?= htmlspecialchars($apt['status']) ?>"><?= ucfirst(htmlspecialchars($apt['status'])) ?></span></td>
            <td><?= htmlspecialchars($apt['notes'] ?? '-') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="dash-empty">No appointments yet.</p>
  <?php endif; ?>
</div>

<?php if (!empty($quizHistory)): ?>
<div class="dash-card">
  <h3>Screening History</h3>
  <table class="dash-table">
    <thead><tr><th>Date</th><th>Score</th><th>Risk Level</th></tr></thead>
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
</div>
<?php endif; ?>
```

- [ ] **Step 4: Create `app/Views/specialist/appointments.php`**

```php
<div class="dash-header">
  <div>
    <h1>Appointments</h1>
    <p>Manage your appointments</p>
  </div>
</div>

<?php if (!empty($appointments)): ?>
<div class="table-responsive">
  <table class="dash-table">
    <thead>
      <tr><th>Child</th><th>Parent</th><th>Date</th><th>Time</th><th>Status</th><th>Notes</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($appointments as $apt): ?>
        <tr>
          <td><?= htmlspecialchars($apt['child_name']) ?></td>
          <td><?= htmlspecialchars($apt['parent_name']) ?></td>
          <td><?= htmlspecialchars($apt['date']) ?></td>
          <td><?= htmlspecialchars(substr($apt['time'], 0, 5)) ?></td>
          <td><span class="status-<?= htmlspecialchars($apt['status']) ?>"><?= ucfirst(htmlspecialchars($apt['status'])) ?></span></td>
          <td><?= htmlspecialchars(substr($apt['notes'] ?? '', 0, 50)) ?></td>
          <td>
            <?php if ($apt['status'] === 'pending'): ?>
              <form method="POST" action="/specialist/appointments/<?= (int)$apt['id'] ?>/status" style="display:flex;gap:0.25rem;">
                <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
                <input type="hidden" name="status" value="confirmed">
                <button type="submit" class="btn-sm" style="background:#dcfce7;color:#16a34a;border:none;cursor:pointer;">Confirm</button>
              </form>
              <form method="POST" action="/specialist/appointments/<?= (int)$apt['id'] ?>/status" style="display:flex;gap:0.25rem;">
                <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" class="btn-sm" style="background:#fee2e2;color:#dc2626;border:none;cursor:pointer;">Decline</button>
              </form>
            <?php elseif ($apt['status'] === 'confirmed'): ?>
              <form method="POST" action="/specialist/appointments/<?= (int)$apt['id'] ?>/status" style="display:inline;">
                <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
                <input type="hidden" name="status" value="completed">
                <button type="submit" class="btn-sm btn-outline">Complete</button>
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
  <p>Appointments will appear here when parents book sessions with you.</p>
</div>
<?php endif; ?>
```

- [ ] **Step 5: Create `app/Views/specialist/messages.php`**

```php
<div class="dash-header">
  <div>
    <h1>Messages</h1>
    <p>Communicate with parents</p>
  </div>
</div>

<div class="dash-grid dash-grid-2">
  <div class="dash-card">
    <h3><i class="fas fa-inbox"></i> Inbox (<?= count($inbox) ?>)</h3>
    <?php if (!empty($inbox)): ?>
      <div class="message-list">
        <?php foreach ($inbox as $msg): ?>
          <a href="/specialist/messages/thread/<?= (int)$msg['sender_id'] ?>" class="message-item <?= !$msg['is_read'] ? 'unread' : '' ?>">
            <div class="msg-sender"><?= htmlspecialchars($msg['sender_name']) ?></div>
            <div class="msg-subject"><?= htmlspecialchars($msg['subject']) ?></div>
            <div class="msg-date"><?= htmlspecialchars($msg['created_at']) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="dash-empty">No messages.</p>
    <?php endif; ?>
  </div>

  <div class="dash-card">
    <h3><i class="fas fa-users"></i> Conversations</h3>
    <?php if (!empty($partners)): ?>
      <div class="message-list">
        <?php foreach ($partners as $partner): ?>
          <a href="/specialist/messages/thread/<?= (int)$partner['id'] ?>" class="message-item">
            <div class="msg-sender"><?= htmlspecialchars($partner['name']) ?></div>
            <div class="msg-subject"><?= ucfirst(htmlspecialchars($partner['role'])) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="dash-empty">No conversations yet.</p>
    <?php endif; ?>
  </div>
</div>
```

- [ ] **Step 6: Create `app/Views/specialist/messages-thread.php`**

```php
<div class="dash-header">
  <div>
    <h1>Messages with <?= htmlspecialchars($partner['name']) ?></h1>
  </div>
  <a href="/specialist/messages" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
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
    <p class="dash-empty">No messages yet.</p>
  <?php endif; ?>
</div>

<form method="POST" action="/specialist/messages/send" class="dash-form mt-1">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <input type="hidden" name="receiver_id" value="<?= (int)$partner['id'] ?>">
  <input type="hidden" name="subject" value="Re: <?= htmlspecialchars($thread[0]['subject'] ?? 'Message') ?>">

  <div class="form-group">
    <textarea name="body" rows="3" required placeholder="Type your reply..."></textarea>
  </div>
  <button type="submit" class="btn btn-primary">Send Reply</button>
</form>
```

- [ ] **Step 7: Create `app/Views/specialist/schedule.php`**

```php
<div class="dash-header">
  <div>
    <h1>Schedule</h1>
    <p>Manage your availability</p>
  </div>
</div>

<div class="dash-card">
  <h3>Availability Settings</h3>
  <form method="POST" action="/specialist/schedule" class="dash-form">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="form-group">
      <label>
        <input type="checkbox" name="is_available" value="1" <?= ($details['is_available'] ?? 1) ? 'checked' : '' ?>>
        Available for appointments
      </label>
    </div>

    <div class="form-group">
      <label for="title">Professional Title</label>
      <input type="text" id="title" name="title" value="<?= htmlspecialchars($details['title'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label for="bio">Bio / Description</label>
      <textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($details['bio'] ?? '') ?></textarea>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
  </form>
</div>
```

- [ ] **Step 8: Create `app/Views/specialist/settings.php`**

```php
<div class="dash-header">
  <div>
    <h1>Settings</h1>
    <p>Manage your account</p>
  </div>
</div>

<form method="POST" action="/specialist/settings" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

  <div class="dash-card mb-2">
    <h3>Profile</h3>
    <div class="form-group">
      <label for="name">Full Name *</label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? $user['name']) ?>" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
    </div>
    <div class="form-group">
      <label for="phone">Phone</label>
      <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($old['phone'] ?? $user['phone'] ?? '') ?>">
    </div>
  </div>

  <div class="dash-card mb-2">
    <h3>Change Password</h3>
    <p class="text-muted">Leave blank to keep current.</p>
    <div class="form-group">
      <label for="password">New Password</label>
      <input type="password" id="password" name="password" minlength="8">
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">Save Settings</button>
  </div>
</form>
```

---

### Task 3: Update AdminController with Full CRUD Implementation

**Files:**
- Modify: `app/Controllers/AdminController.php`

- [ ] **Step 1: Replace with full AdminController**

```php
<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Core\Validator;
use App\Core\Database;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Message;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use App\Models\Activity;
use App\Models\Subscription;
use PDO;

class AdminController
{
    public function dashboard(): void
    {
        $totalUsers = User::countByRole('parent') + User::countByRole('specialist');
        $totalParents = User::countByRole('parent');
        $totalSpecialists = User::countByRole('specialist');
        $totalAppointments = count(Appointment::getAll());
        $totalSubscriptions = Subscription::count();
        $activeSubscriptions = Subscription::countActive();
        $totalQuizAttempts = count(QuizAttempt::getAll());
        $totalActivities = Activity::count();

        $db = Database::getInstance();
        $unreadContacts = $db->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();
        $unreadMessages = $db->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();

        View::render('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'totalUsers' => $totalUsers,
            'totalParents' => $totalParents,
            'totalSpecialists' => $totalSpecialists,
            'totalAppointments' => $totalAppointments,
            'totalSubscriptions' => $totalSubscriptions,
            'activeSubscriptions' => $activeSubscriptions,
            'totalQuizAttempts' => $totalQuizAttempts,
            'totalActivities' => $totalActivities,
            'unreadContacts' => (int)$unreadContacts,
            'unreadMessages' => (int)$unreadMessages,
        ], 'dashboard');
    }

    // Users CRUD
    public function users(): void
    {
        $db = Database::getInstance();
        $users = $db->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
        View::render('admin/users', ['title' => 'Manage Users', 'users' => $users], 'dashboard');
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
            View::render('admin/users-add', ['errors' => $validator->errors(), 'old' => $_POST, 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        if (User::findByEmail($_POST['email'])) {
            View::render('admin/users-add', ['errors' => ['email' => ['Email already exists']], 'old' => $_POST, 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        User::create([
            'role' => $_POST['role'],
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
        ]);

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

        $validator = new Validator();
        if (!$validator->validate($_POST, [
            'name' => 'required|max:255',
            'role' => 'required|in:parent,specialist,admin',
            'is_active' => '',
        ])) {
            $user = User::findById($id);
            View::render('admin/users-edit', ['errors' => $validator->errors(), 'user' => $user, 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET name = ?, role = ?, is_active = ? WHERE id = ?');
        $stmt->execute([$_POST['name'], $_POST['role'], $_POST['is_active'] ?? 1, $id]);

        if (!empty($_POST['password'])) {
            $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([password_hash($_POST['password'], PASSWORD_BCRYPT), $id]);
        }

        Session::setFlash('success', 'User updated.');
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

        if ($id === (int)Session::get('user_id')) {
            Session::setFlash('error', 'Cannot delete yourself.');
            header('Location: /admin/users');
            exit;
        }

        $stmt = Database::getInstance()->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        Session::setFlash('success', 'User deleted.');
        header('Location: /admin/users');
        exit;
    }

    // Specialists management
    public function manageSpecialists(): void
    {
        $db = Database::getInstance();
        $specialists = $db->query('
            SELECT u.*, sd.title, sd.specializations, sd.years_experience, sd.is_available
            FROM users u
            LEFT JOIN specialist_details sd ON u.id = sd.user_id
            WHERE u.role = "specialist"
            ORDER BY u.created_at DESC
        ')->fetchAll();
        View::render('admin/specialists', ['title' => 'Specialists', 'specialists' => $specialists], 'dashboard');
    }

    public function approveSpecialist(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $isActive = $_POST['is_active'] ?? 1;
        $stmt = Database::getInstance()->prepare('UPDATE users SET is_active = ? WHERE id = ? AND role = "specialist"');
        $stmt->execute([$isActive, $id]);

        Session::setFlash('success', 'Specialist status updated.');
        header('Location: /admin/specialists');
        exit;
    }

    // Quiz CRUD
    public function quiz(): void
    {
        $questions = QuizQuestion::getAll();
        View::render('admin/quiz', ['title' => 'Manage Quiz', 'questions' => $questions], 'dashboard');
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
            View::render('admin/quiz-add', ['errors' => $validator->errors(), 'old' => $_POST, 'csrf_token' => Session::csrf_token()], 'dashboard');
            return;
        }

        $questionId = QuizQuestion::create([
            'question_text' => $_POST['question_text'],
            'category' => $_POST['category'],
            'order_index' => (int)$_POST['order_index'],
        ]);

        // Add options if provided
        if (isset($_POST['options']) && is_array($_POST['options'])) {
            $db = Database::getInstance();
            foreach ($_POST['options'] as $i => $opt) {
                if (!empty($opt['text'])) {
                    $stmt = $db->prepare('INSERT INTO quiz_options (question_id, option_text, weight, order_index) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$questionId, $opt['text'], (int)($opt['weight'] ?? 0), $i + 1]);
                }
            }
        }

        Session::setFlash('success', 'Question added.');
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

        QuizQuestion::update($id, [
            'question_text' => $_POST['question_text'],
            'category' => $_POST['category'],
            'order_index' => (int)$_POST['order_index'],
            'is_active' => $_POST['is_active'] ?? 1,
        ]);

        Session::setFlash('success', 'Question updated.');
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

    // Activities CRUD
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
            View::render('admin/activities-add', ['errors' => $validator->errors(), 'old' => $_POST, 'csrf_token' => Session::csrf_token()], 'dashboard');
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

    // Appointments (read-only)
    public function appointments(): void
    {
        $appointments = Appointment::getAll();
        View::render('admin/appointments', ['title' => 'Appointments', 'appointments' => $appointments], 'dashboard');
    }

    // Messages (read-only)
    public function messages(): void
    {
        $db = Database::getInstance();
        $messages = $db->query('
            SELECT m.*, s.name AS sender_name, r.name AS receiver_name
            FROM messages m
            JOIN users s ON m.sender_id = s.id
            JOIN users r ON m.receiver_id = r.id
            ORDER BY m.created_at DESC
            LIMIT 100
        ')->fetchAll();
        View::render('admin/messages', ['title' => 'Messages', 'messages' => $messages], 'dashboard');
    }

    // Subscriptions CRUD
    public function subscriptions(): void
    {
        $subscriptions = Subscription::getAll();
        View::render('admin/subscriptions', ['title' => 'Subscriptions', 'subscriptions' => $subscriptions], 'dashboard');
    }

    public function addSubscriptionForm(): void
    {
        $db = Database::getInstance();
        $users = $db->query('SELECT id, name, email FROM users ORDER BY name')->fetchAll();
        View::render('admin/subscriptions-add', ['title' => 'Add Subscription', 'users' => $users, 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function addSubscription(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        Subscription::create([
            'user_id' => (int)$_POST['user_id'],
            'plan' => $_POST['plan'],
            'status' => 'active',
            'ends_at' => $_POST['ends_at'] ?? null,
        ]);

        Session::setFlash('success', 'Subscription created.');
        header('Location: /admin/subscriptions');
        exit;
    }

    public function editSubscriptionForm(int $id): void
    {
        $subscription = Database::getInstance()->prepare('SELECT * FROM subscriptions WHERE id = ?');
        $subscription->execute([$id]);
        $sub = $subscription->fetch();

        if (!$sub) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }
        View::render('admin/subscriptions-edit', ['title' => 'Edit Subscription', 'subscription' => $sub, 'csrf_token' => Session::csrf_token()], 'dashboard');
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

    // Contacts
    public function contacts(): void
    {
        $db = Database::getInstance();
        $contacts = $db->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();
        View::render('admin/contacts', ['title' => 'Contact Messages', 'contacts' => $contacts], 'dashboard');
    }

    public function markContactRead(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }
        $stmt = Database::getInstance()->prepare('UPDATE contact_messages SET is_read = NOT is_read WHERE id = ?');
        $stmt->execute([$id]);
        Session::setFlash('success', 'Contact status toggled.');
        header('Location: /admin/contacts');
        exit;
    }

    // FAQ CRUD
    public function faq(): void
    {
        $db = Database::getInstance();
        $faqItems = $db->query('SELECT * FROM faq_items ORDER BY order_index')->fetchAll();
        View::render('admin/faq', ['title' => 'FAQ Management', 'faqItems' => $faqItems], 'dashboard');
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
        $item = $stmt->fetch();

        if (!$item) {
            http_response_code(404);
            View::render('errors/404', [], 'dashboard');
            return;
        }
        View::render('admin/faq-edit', ['title' => 'Edit FAQ', 'item' => $item, 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function editFaq(int $id): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
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

        $stmt = Database::getInstance()->prepare('DELETE FROM faq_items WHERE id = ?');
        $stmt->execute([$id]);
        Session::setFlash('success', 'FAQ deleted.');
        header('Location: /admin/faq');
        exit;
    }

    // Settings
    public function settings(): void
    {
        $user = User::findById((int)Session::get('user_id'));
        View::render('admin/settings', ['title' => 'Settings', 'user' => $user, 'csrf_token' => Session::csrf_token()], 'dashboard');
    }

    public function updateSettings(): void
    {
        $adminId = (int)Session::get('user_id');
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'dashboard');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET name = ? WHERE id = ?');
        $stmt->execute([$_POST['name'], $adminId]);
        Session::set('user_name', $_POST['name']);

        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 8) {
                Session::setFlash('error', 'Password must be at least 8 characters.');
                header('Location: /admin/settings');
                exit;
            }
            $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([password_hash($_POST['password'], PASSWORD_BCRYPT), $adminId]);
        }

        Session::setFlash('success', 'Settings updated.');
        header('Location: /admin/settings');
        exit;
    }
}
```

---

### Task 4: Create Admin Dashboard Views

**Files:**
- Create: `app/Views/admin/dashboard.php` (overwrite)
- Create: `app/Views/admin/users.php`
- Create: `app/Views/admin/users-add.php`
- Create: `app/Views/admin/users-edit.php`
- Create: `app/Views/admin/specialists.php`
- Create: `app/Views/admin/quiz.php`
- Create: `app/Views/admin/quiz-add.php`
- Create: `app/Views/admin/quiz-edit.php`
- Create: `app/Views/admin/activities.php`
- Create: `app/Views/admin/activities-add.php`
- Create: `app/Views/admin/activities-edit.php`
- Create: `app/Views/admin/appointments.php`
- Create: `app/Views/admin/messages.php`
- Create: `app/Views/admin/subscriptions.php`
- Create: `app/Views/admin/subscriptions-add.php`
- Create: `app/Views/admin/subscriptions-edit.php`
- Create: `app/Views/admin/contacts.php`
- Create: `app/Views/admin/faq.php`
- Create: `app/Views/admin/faq-add.php`
- Create: `app/Views/admin/faq-edit.php`
- Create: `app/Views/admin/settings.php`

- [ ] **Step 1: Create `app/Views/admin/dashboard.php`**

```php
<div class="dash-header">
  <div>
    <h1>Admin Dashboard</h1>
    <p>System overview and management</p>
  </div>
</div>

<div class="dash-grid dash-grid-2">
  <div class="dash-card">
    <div class="dash-card-header"><h3><i class="fas fa-users"></i> Users</h3><span class="dash-badge"><?= (int)$totalUsers ?></span></div>
    <p><?= (int)$totalParents ?> parents · <?= (int)$totalSpecialists ?> specialists</p>
    <a href="/admin/users" class="dash-link">Manage Users →</a>
  </div>
  <div class="dash-card">
    <div class="dash-card-header"><h3><i class="fas fa-calendar"></i> Appointments</h3><span class="dash-badge"><?= (int)$totalAppointments ?></span></div>
    <a href="/admin/appointments" class="dash-link">View All →</a>
  </div>
  <div class="dash-card">
    <div class="dash-card-header"><h3><i class="fas fa-credit-card"></i> Subscriptions</h3><span class="dash-badge"><?= (int)$totalSubscriptions ?></span></div>
    <p><?= (int)$activeSubscriptions ?> active</p>
    <a href="/admin/subscriptions" class="dash-link">Manage →</a>
  </div>
  <div class="dash-card">
    <div class="dash-card-header"><h3><i class="fas fa-clipboard-list"></i> Quiz</h3><span class="dash-badge"><?= (int)$totalQuizAttempts ?> attempts</span></div>
    <a href="/admin/quiz" class="dash-link">Manage Quiz →</a>
  </div>
  <div class="dash-card">
    <div class="dash-card-header"><h3><i class="fas fa-gamepad"></i> Activities</h3><span class="dash-badge"><?= (int)$totalActivities ?></span></div>
    <a href="/admin/activities" class="dash-link">Manage →</a>
  </div>
  <div class="dash-card">
    <div class="dash-card-header"><h3><i class="fas fa-envelope"></i> Messages</h3><span class="dash-badge dash-badge-warning"><?= (int)$unreadMessages ?> unread</span></div>
    <a href="/admin/messages" class="dash-link">View →</a>
  </div>
  <div class="dash-card">
    <div class="dash-card-header"><h3><i class="fas fa-id-card"></i> Contacts</h3><span class="dash-badge dash-badge-warning"><?= (int)$unreadContacts ?> new</span></div>
    <a href="/admin/contacts" class="dash-link">View →</a>
  </div>
  <div class="dash-card">
    <div class="dash-card-header"><h3><i class="fas fa-question-circle"></i> FAQ</h3></div>
    <a href="/admin/faq" class="dash-link">Manage →</a>
  </div>
</div>
```

- [ ] **Step 2: Create `app/Views/admin/users.php`**

```php
<div class="dash-header">
  <div><h1>Users</h1><p>Manage all platform users</p></div>
  <a href="/admin/users/add" class="btn btn-primary"><i class="fas fa-plus"></i> Add User</a>
</div>

<div class="table-responsive">
  <table class="dash-table">
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th>Created</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="dash-badge"><?= ucfirst(htmlspecialchars($u['role'])) ?></span></td>
          <td><?= $u['is_active'] ? '<span style="color:#16a34a">Yes</span>' : '<span style="color:#dc2626">No</span>' ?></td>
          <td><?= htmlspecialchars($u['created_at']) ?></td>
          <td class="actions">
            <a href="/admin/users/<?= (int)$u['id'] ?>/edit" class="btn-sm btn-outline"><i class="fas fa-edit"></i></a>
            <form method="POST" action="/admin/users/<?= (int)$u['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this user?')">
              <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
              <button type="submit" class="btn-sm btn-danger"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
```

- [ ] **Step 3: Create `app/Views/admin/users-add.php`**

```php
<div class="dash-header">
  <div><h1>Add User</h1><p>Create a new user account</p></div>
</div>
<form method="POST" action="/admin/users/add" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <div class="form-group">
    <label for="name">Name *</label>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
    <?php if (!empty($errors['name'])): ?><span class="form-error"><?= htmlspecialchars($errors['name'][0]) ?></span><?php endif; ?>
  </div>
  <div class="form-group">
    <label for="email">Email *</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
    <?php if (!empty($errors['email'])): ?><span class="form-error"><?= htmlspecialchars($errors['email'][0]) ?></span><?php endif; ?>
  </div>
  <div class="form-group">
    <label for="password">Password *</label>
    <input type="password" id="password" name="password" required minlength="8">
  </div>
  <div class="form-group">
    <label for="role">Role *</label>
    <select id="role" name="role" required>
      <option value="parent" <?= ($old['role'] ?? '') === 'parent' ? 'selected' : '' ?>>Parent</option>
      <option value="specialist" <?= ($old['role'] ?? '') === 'specialist' ? 'selected' : '' ?>>Specialist</option>
      <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
    </select>
  </div>
  <div class="form-actions">
    <a href="/admin/users" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Add User</button>
  </div>
</form>
```

- [ ] **Step 4: Create `app/Views/admin/users-edit.php`**

```php
<div class="dash-header">
  <div><h1>Edit User</h1><p><?= htmlspecialchars($user['email']) ?></p></div>
</div>
<form method="POST" action="/admin/users/<?= (int)$user['id'] ?>/edit" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <div class="form-group">
    <label for="name">Name *</label>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? $user['name']) ?>" required>
  </div>
  <div class="form-group">
    <label>Email</label>
    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
  </div>
  <div class="form-group">
    <label for="role">Role</label>
    <select id="role" name="role">
      <option value="parent" <?= $user['role'] === 'parent' ? 'selected' : '' ?>>Parent</option>
      <option value="specialist" <?= $user['role'] === 'specialist' ? 'selected' : '' ?>>Specialist</option>
      <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
    </select>
  </div>
  <div class="form-group">
    <label><input type="checkbox" name="is_active" value="1" <?= $user['is_active'] ? 'checked' : '' ?>> Active</label>
  </div>
  <div class="form-group">
    <label for="password">New Password (leave blank to keep)</label>
    <input type="password" id="password" name="password" minlength="8">
  </div>
  <div class="form-actions">
    <a href="/admin/users" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Save</button>
  </div>
</form>
```

- [ ] **Step 5: Create `app/Views/admin/specialists.php`**

```php
<div class="dash-header">
  <div><h1>Specialists</h1><p>Manage specialist accounts</p></div>
</div>
<div class="table-responsive">
  <table class="dash-table">
    <thead><tr><th>Name</th><th>Title</th><th>Active</th><th>Available</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($specialists as $s): ?>
        <tr>
          <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
          <td><?= htmlspecialchars($s['title'] ?? '-') ?></td>
          <td><?= $s['is_active'] ? '<span style="color:#16a34a">Yes</span>' : '<span style="color:#dc2626">No</span>' ?></td>
          <td><?= $s['is_available'] ? '<span style="color:#16a34a">Yes</span>' : '<span style="color:#dc2626">No</span>' ?></td>
          <td>
            <form method="POST" action="/admin/specialists/<?= (int)$s['id'] ?>/approve" style="display:inline">
              <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
              <input type="hidden" name="is_active" value="<?= $s['is_active'] ? 0 : 1 ?>">
              <button type="submit" class="btn-sm btn-outline"><?= $s['is_active'] ? 'Deactivate' : 'Activate' ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
```

- [ ] **Step 6: Create `app/Views/admin/quiz.php`**

```php
<div class="dash-header">
  <div><h1>Quiz Questions</h1><p>Manage screening questions</p></div>
  <a href="/admin/quiz/add" class="btn btn-primary"><i class="fas fa-plus"></i> Add Question</a>
</div>
<div class="table-responsive">
  <table class="dash-table">
    <thead><tr><th>#</th><th>Question</th><th>Category</th><th>Options</th><th>Active</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($questions as $q): ?>
        <tr>
          <td><?= (int)$q['order_index'] ?></td>
          <td><?= htmlspecialchars(substr($q['question_text'], 0, 60)) ?>...</td>
          <td><span class="dash-badge"><?= ucwords(str_replace('_', ' ', htmlspecialchars($q['category']))) ?></span></td>
          <td><?= (int)$q['option_count'] ?></td>
          <td><?= $q['is_active'] ? 'Yes' : 'No' ?></td>
          <td class="actions">
            <a href="/admin/quiz/<?= (int)$q['id'] ?>/edit" class="btn-sm btn-outline"><i class="fas fa-edit"></i></a>
            <form method="POST" action="/admin/quiz/<?= (int)$q['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this question?')">
              <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
              <button type="submit" class="btn-sm btn-danger"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
```

- [ ] **Step 7: Create `app/Views/admin/quiz-add.php`**

```php
<div class="dash-header">
  <div><h1>Add Question</h1></div>
</div>
<form method="POST" action="/admin/quiz/add" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <div class="form-group">
    <label for="question_text">Question Text *</label>
    <textarea id="question_text" name="question_text" rows="3" required><?= htmlspecialchars($old['question_text'] ?? '') ?></textarea>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label for="category">Category *</label>
      <select id="category" name="category" required>
        <option value="social_communication">Social Communication</option>
        <option value="behavior">Behavior</option>
        <option value="sensory">Sensory</option>
        <option value="developmental">Developmental</option>
      </select>
    </div>
    <div class="form-group">
      <label for="order_index">Order *</label>
      <input type="number" id="order_index" name="order_index" value="<?= (int)($old['order_index'] ?? 1) ?>" required>
    </div>
  </div>
  <div class="dash-card mb-2">
    <h4>Options (6 recommended)</h4>
    <div id="optionsContainer">
      <?php for ($i = 0; $i < 6; $i++): ?>
        <div class="form-row mb-1" style="align-items:end;">
          <div class="form-group" style="flex:3;">
            <label>Option <?= $i + 1 ?></label>
            <input type="text" name="options[<?= $i ?>][text]" placeholder="e.g. Always" value="<?= htmlspecialchars($old['options'][$i]['text'] ?? '') ?>">
          </div>
          <div class="form-group" style="flex:1;">
            <label>Weight</label>
            <input type="number" name="options[<?= $i ?>][weight]" min="0" max="5" value="<?= (int)($old['options'][$i]['weight'] ?? 0) ?>">
          </div>
        </div>
      <?php endfor; ?>
    </div>
  </div>
  <div class="form-actions">
    <a href="/admin/quiz" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Add Question</button>
  </div>
</form>
```

- [ ] **Step 8: Create `app/Views/admin/quiz-edit.php`**

```php
<div class="dash-header">
  <div><h1>Edit Question</h1></div>
</div>
<form method="POST" action="/admin/quiz/<?= (int)$question['id'] ?>/edit" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <div class="form-group">
    <label for="question_text">Question Text *</label>
    <textarea id="question_text" name="question_text" rows="3" required><?= htmlspecialchars($question['question_text']) ?></textarea>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label for="category">Category</label>
      <select id="category" name="category">
        <option value="social_communication" <?= $question['category'] === 'social_communication' ? 'selected' : '' ?>>Social Communication</option>
        <option value="behavior" <?= $question['category'] === 'behavior' ? 'selected' : '' ?>>Behavior</option>
        <option value="sensory" <?= $question['category'] === 'sensory' ? 'selected' : '' ?>>Sensory</option>
        <option value="developmental" <?= $question['category'] === 'developmental' ? 'selected' : '' ?>>Developmental</option>
      </select>
    </div>
    <div class="form-group">
      <label for="order_index">Order</label>
      <input type="number" id="order_index" name="order_index" value="<?= (int)$question['order_index'] ?>">
    </div>
  </div>
  <div class="form-group">
    <label><input type="checkbox" name="is_active" value="1" <?= $question['is_active'] ? 'checked' : '' ?>> Active</label>
  </div>
  <div class="form-actions">
    <a href="/admin/quiz" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Save</button>
  </div>
</form>
```

- [ ] **Step 9: Create `app/Views/admin/activities.php`**

```php
<div class="dash-header">
  <div><h1>Activities</h1><p>Manage learning activities</p></div>
  <a href="/admin/activities/add" class="btn btn-primary"><i class="fas fa-plus"></i> Add Activity</a>
</div>
<div class="table-responsive">
  <table class="dash-table">
    <thead><tr><th>Title</th><th>Category</th><th>Difficulty</th><th>Active</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($activities as $a): ?>
        <tr>
          <td><strong><?= htmlspecialchars($a['title']) ?></strong></td>
          <td><span class="dash-badge"><?= ucfirst(htmlspecialchars($a['category'])) ?></span></td>
          <td><?= ucfirst(htmlspecialchars($a['difficulty'])) ?></td>
          <td><?= $a['is_active'] ? 'Yes' : 'No' ?></td>
          <td class="actions">
            <a href="/admin/activities/<?= (int)$a['id'] ?>/edit" class="btn-sm btn-outline"><i class="fas fa-edit"></i></a>
            <form method="POST" action="/admin/activities/<?= (int)$a['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete?')">
              <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
              <button type="submit" class="btn-sm btn-danger"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
```

- [ ] **Step 10: Create `app/Views/admin/activities-add.php`**

```php
<div class="dash-header">
  <div><h1>Add Activity</h1></div>
</div>
<form method="POST" action="/admin/activities/add" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <div class="form-group">
    <label for="title">Title *</label>
    <input type="text" id="title" name="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>" required>
  </div>
  <div class="form-group">
    <label for="description">Description</label>
    <textarea id="description" name="description" rows="3"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label for="category">Category *</label>
      <select id="category" name="category" required>
        <option value="games">Games</option>
        <option value="puzzles">Puzzles</option>
        <option value="stories">Stories</option>
        <option value="video">Video</option>
        <option value="coloring">Coloring</option>
      </select>
    </div>
    <div class="form-group">
      <label for="difficulty">Difficulty *</label>
      <select id="difficulty" name="difficulty" required>
        <option value="easy">Easy</option>
        <option value="medium">Medium</option>
        <option value="hard">Hard</option>
      </select>
    </div>
  </div>
  <div class="form-group">
    <label for="image_url">Image URL</label>
    <input type="text" id="image_url" name="image_url" value="<?= htmlspecialchars($old['image_url'] ?? '') ?>">
  </div>
  <div class="form-actions">
    <a href="/admin/activities" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Add</button>
  </div>
</form>
```

- [ ] **Step 11: Create `app/Views/admin/activities-edit.php`**

```php
<div class="dash-header">
  <div><h1>Edit Activity</h1><p><?= htmlspecialchars($activity['title']) ?></p></div>
</div>
<form method="POST" action="/admin/activities/<?= (int)$activity['id'] ?>/edit" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <div class="form-group">
    <label for="title">Title *</label>
    <input type="text" id="title" name="title" value="<?= htmlspecialchars($activity['title']) ?>" required>
  </div>
  <div class="form-group">
    <label for="description">Description</label>
    <textarea id="description" name="description" rows="3"><?= htmlspecialchars($activity['description']) ?></textarea>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label for="category">Category</label>
      <select id="category" name="category">
        <option value="games" <?= $activity['category'] === 'games' ? 'selected' : '' ?>>Games</option>
        <option value="puzzles" <?= $activity['category'] === 'puzzles' ? 'selected' : '' ?>>Puzzles</option>
        <option value="stories" <?= $activity['category'] === 'stories' ? 'selected' : '' ?>>Stories</option>
        <option value="video" <?= $activity['category'] === 'video' ? 'selected' : '' ?>>Video</option>
        <option value="coloring" <?= $activity['category'] === 'coloring' ? 'selected' : '' ?>>Coloring</option>
      </select>
    </div>
    <div class="form-group">
      <label for="difficulty">Difficulty</label>
      <select id="difficulty" name="difficulty">
        <option value="easy" <?= $activity['difficulty'] === 'easy' ? 'selected' : '' ?>>Easy</option>
        <option value="medium" <?= $activity['difficulty'] === 'medium' ? 'selected' : '' ?>>Medium</option>
        <option value="hard" <?= $activity['difficulty'] === 'hard' ? 'selected' : '' ?>>Hard</option>
      </select>
    </div>
  </div>
  <div class="form-group">
    <label for="image_url">Image URL</label>
    <input type="text" id="image_url" name="image_url" value="<?= htmlspecialchars($activity['image_url']) ?>">
  </div>
  <div class="form-group">
    <label><input type="checkbox" name="is_active" value="1" <?= $activity['is_active'] ? 'checked' : '' ?>> Active</label>
  </div>
  <div class="form-actions">
    <a href="/admin/activities" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Save</button>
  </div>
</form>
```

- [ ] **Step 12: Create `app/Views/admin/appointments.php`**

```php
<div class="dash-header">
  <div><h1>Appointments</h1><p>All platform appointments</p></div>
</div>
<div class="table-responsive">
  <table class="dash-table">
    <thead><tr><th>Child</th><th>Parent</th><th>Specialist</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($appointments as $apt): ?>
        <tr>
          <td><?= htmlspecialchars($apt['child_name']) ?></td>
          <td><?= htmlspecialchars($apt['parent_name']) ?></td>
          <td><?= htmlspecialchars($apt['specialist_name']) ?></td>
          <td><?= htmlspecialchars($apt['date']) ?></td>
          <td><?= htmlspecialchars(substr($apt['time'], 0, 5)) ?></td>
          <td><span class="status-<?= htmlspecialchars($apt['status']) ?>"><?= ucfirst(htmlspecialchars($apt['status'])) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
```

- [ ] **Step 13: Create `app/Views/admin/messages.php`**

```php
<div class="dash-header">
  <div><h1>Messages</h1><p>All platform messages</p></div>
</div>
<div class="table-responsive">
  <table class="dash-table">
    <thead><tr><th>From</th><th>To</th><th>Subject</th><th>Date</th><th>Read</th></tr></thead>
    <tbody>
      <?php foreach ($messages as $m): ?>
        <tr>
          <td><?= htmlspecialchars($m['sender_name']) ?></td>
          <td><?= htmlspecialchars($m['receiver_name']) ?></td>
          <td><?= htmlspecialchars($m['subject']) ?></td>
          <td><?= htmlspecialchars($m['created_at']) ?></td>
          <td><?= $m['is_read'] ? '<span style="color:#16a34a">Yes</span>' : '<span style="color:#dc2626">No</span>' ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
```

- [ ] **Step 14: Create `app/Views/admin/subscriptions.php`**

```php
<div class="dash-header">
  <div><h1>Subscriptions</h1><p>Manage user subscriptions</p></div>
  <a href="/admin/subscriptions/add" class="btn btn-primary"><i class="fas fa-plus"></i> Add Subscription</a>
</div>
<div class="table-responsive">
  <table class="dash-table">
    <thead><tr><th>User</th><th>Plan</th><th>Status</th><th>Started</th><th>Ends</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($subscriptions as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['user_name']) ?></td>
          <td><span class="dash-badge"><?= ucfirst(htmlspecialchars($s['plan'])) ?></span></td>
          <td><?= ucfirst(htmlspecialchars($s['status'])) ?></td>
          <td><?= htmlspecialchars($s['started_at']) ?></td>
          <td><?= htmlspecialchars($s['ends_at'] ?? '-') ?></td>
          <td><a href="/admin/subscriptions/<?= (int)$s['id'] ?>/edit" class="btn-sm btn-outline"><i class="fas fa-edit"></i></a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
```

- [ ] **Step 15: Create `app/Views/admin/subscriptions-add.php`**

```php
<div class="dash-header">
  <div><h1>Add Subscription</h1></div>
</div>
<form method="POST" action="/admin/subscriptions/add" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <div class="form-group">
    <label for="user_id">User *</label>
    <select id="user_id" name="user_id" required>
      <option value="">Select user</option>
      <?php foreach ($users as $u): ?>
        <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)</option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-group">
    <label for="plan">Plan *</label>
    <select id="plan" name="plan" required>
      <option value="standard">Standard</option>
      <option value="premium">Premium ($19/mo)</option>
      <option value="family">Family ($29/mo)</option>
    </select>
  </div>
  <div class="form-group">
    <label for="ends_at">End Date</label>
    <input type="date" id="ends_at" name="ends_at">
  </div>
  <div class="form-actions">
    <a href="/admin/subscriptions" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Add</button>
  </div>
</form>
```

- [ ] **Step 16: Create `app/Views/admin/subscriptions-edit.php`**

```php
<div class="dash-header">
  <div><h1>Edit Subscription</h1></div>
</div>
<form method="POST" action="/admin/subscriptions/<?= (int)$subscription['id'] ?>/edit" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <div class="form-group">
    <label for="plan">Plan</label>
    <select id="plan" name="plan">
      <option value="standard" <?= $subscription['plan'] === 'standard' ? 'selected' : '' ?>>Standard</option>
      <option value="premium" <?= $subscription['plan'] === 'premium' ? 'selected' : '' ?>>Premium</option>
      <option value="family" <?= $subscription['plan'] === 'family' ? 'selected' : '' ?>>Family</option>
    </select>
  </div>
  <div class="form-group">
    <label for="status">Status</label>
    <select id="status" name="status">
      <option value="active" <?= $subscription['status'] === 'active' ? 'selected' : '' ?>>Active</option>
      <option value="cancelled" <?= $subscription['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
      <option value="expired" <?= $subscription['status'] === 'expired' ? 'selected' : '' ?>>Expired</option>
    </select>
  </div>
  <div class="form-group">
    <label for="ends_at">End Date</label>
    <input type="date" id="ends_at" name="ends_at" value="<?= htmlspecialchars($subscription['ends_at'] ?? '') ?>">
  </div>
  <div class="form-actions">
    <a href="/admin/subscriptions" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Save</button>
  </div>
</form>
```

- [ ] **Step 17: Create `app/Views/admin/contacts.php`**

```php
<div class="dash-header">
  <div><h1>Contact Messages</h1><p>User-submitted inquiries</p></div>
</div>
<div class="table-responsive">
  <table class="dash-table">
    <thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Date</th><th>Read</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($contacts as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['name']) ?></td>
          <td><?= htmlspecialchars($c['email']) ?></td>
          <td><?= htmlspecialchars($c['subject']) ?></td>
          <td><?= htmlspecialchars(substr($c['message'], 0, 80)) ?>...</td>
          <td><?= htmlspecialchars($c['created_at']) ?></td>
          <td><?= $c['is_read'] ? '<span style="color:#16a34a">Yes</span>' : '<span style="color:#dc2626">No</span>' ?></td>
          <td>
            <form method="POST" action="/admin/contacts/<?= (int)$c['id'] ?>/read" style="display:inline">
              <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
              <button type="submit" class="btn-sm btn-outline">Toggle Read</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
```

- [ ] **Step 18: Create `app/Views/admin/faq.php`**

```php
<div class="dash-header">
  <div><h1>FAQ Management</h1><p>Manage frequently asked questions</p></div>
  <a href="/admin/faq/add" class="btn btn-primary"><i class="fas fa-plus"></i> Add FAQ</a>
</div>
<div class="table-responsive">
  <table class="dash-table">
    <thead><tr><th>#</th><th>Question</th><th>Category</th><th>Active</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($faqItems as $item): ?>
        <tr>
          <td><?= (int)$item['order_index'] ?></td>
          <td><?= htmlspecialchars(substr($item['question'], 0, 60)) ?>...</td>
          <td><span class="dash-badge"><?= ucfirst(htmlspecialchars($item['category'])) ?></span></td>
          <td><?= $item['is_active'] ? 'Yes' : 'No' ?></td>
          <td class="actions">
            <a href="/admin/faq/<?= (int)$item['id'] ?>/edit" class="btn-sm btn-outline"><i class="fas fa-edit"></i></a>
            <form method="POST" action="/admin/faq/<?= (int)$item['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete?')">
              <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrf_token() ?>">
              <button type="submit" class="btn-sm btn-danger"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
```

- [ ] **Step 19: Create `app/Views/admin/faq-add.php`**

```php
<div class="dash-header">
  <div><h1>Add FAQ</h1></div>
</div>
<form method="POST" action="/admin/faq/add" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <div class="form-group">
    <label for="question">Question *</label>
    <input type="text" id="question" name="question" required>
  </div>
  <div class="form-group">
    <label for="answer">Answer *</label>
    <textarea id="answer" name="answer" rows="4" required></textarea>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label for="category">Category</label>
      <select id="category" name="category">
        <option value="general">General</option>
        <option value="features">Features</option>
        <option value="pricing">Pricing</option>
        <option value="technical">Technical</option>
      </select>
    </div>
    <div class="form-group">
      <label for="order_index">Order</label>
      <input type="number" id="order_index" name="order_index" value="1">
    </div>
  </div>
  <div class="form-actions">
    <a href="/admin/faq" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Add</button>
  </div>
</form>
```

- [ ] **Step 20: Create `app/Views/admin/faq-edit.php`**

```php
<div class="dash-header">
  <div><h1>Edit FAQ</h1></div>
</div>
<form method="POST" action="/admin/faq/<?= (int)$item['id'] ?>/edit" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <div class="form-group">
    <label for="question">Question *</label>
    <input type="text" id="question" name="question" value="<?= htmlspecialchars($item['question']) ?>" required>
  </div>
  <div class="form-group">
    <label for="answer">Answer *</label>
    <textarea id="answer" name="answer" rows="4" required><?= htmlspecialchars($item['answer']) ?></textarea>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label for="category">Category</label>
      <select id="category" name="category">
        <option value="general" <?= $item['category'] === 'general' ? 'selected' : '' ?>>General</option>
        <option value="features" <?= $item['category'] === 'features' ? 'selected' : '' ?>>Features</option>
        <option value="pricing" <?= $item['category'] === 'pricing' ? 'selected' : '' ?>>Pricing</option>
        <option value="technical" <?= $item['category'] === 'technical' ? 'selected' : '' ?>>Technical</option>
      </select>
    </div>
    <div class="form-group">
      <label for="order_index">Order</label>
      <input type="number" id="order_index" name="order_index" value="<?= (int)$item['order_index'] ?>">
    </div>
  </div>
  <div class="form-group">
    <label><input type="checkbox" name="is_active" value="1" <?= $item['is_active'] ? 'checked' : '' ?>> Active</label>
  </div>
  <div class="form-actions">
    <a href="/admin/faq" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Save</button>
  </div>
</form>
```

- [ ] **Step 21: Create `app/Views/admin/settings.php`**

```php
<div class="dash-header">
  <div><h1>Settings</h1><p>Admin account settings</p></div>
</div>
<form method="POST" action="/admin/settings" class="dash-form">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <div class="dash-card mb-2">
    <h3>Profile</h3>
    <div class="form-group">
      <label for="name">Name *</label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
    </div>
  </div>
  <div class="dash-card mb-2">
    <h3>Change Password</h3>
    <div class="form-group">
      <label for="password">New Password</label>
      <input type="password" id="password" name="password" minlength="8">
    </div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary">Save</button>
  </div>
</form>
```
