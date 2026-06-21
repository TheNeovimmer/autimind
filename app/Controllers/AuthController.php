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
    private function filterOld(array $data): array
    {
        unset($data['password'], $data['password_confirmation']);
        return $data;
    }
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
            View::render('auth/login', ['errors' => $validator->errors(), 'old' => $this->filterOld($_POST)], 'main');
            return;
        }

        $user = User::findByEmail($_POST['email']);
        if (!$user || !password_verify($_POST['password'], $user['password'])) {
            View::render('auth/login', [
                'errors' => ['email' => ['Invalid email or password']],
                'old' => $this->filterOld($_POST)
            ], 'main');
            return;
        }

        if (!$user['is_active']) {
            View::render('auth/login', [
                'errors' => ['email' => ['Your account has been deactivated']],
                'old' => $this->filterOld($_POST)
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
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:parent,specialist,educator'
        ])) {
            View::render('auth/signup', ['errors' => $validator->errors(), 'old' => $this->filterOld($_POST)], 'main');
            return;
        }

        if (User::findByEmail($_POST['email'])) {
            View::render('auth/signup', [
                'errors' => ['email' => ['An account with this email already exists']],
                'old' => $this->filterOld($_POST)
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

        if ($role === 'specialist') {
            $db = Database::getInstance();
            $stmt = $db->prepare('INSERT INTO specialist_details (user_id, title, bio) VALUES (?, ?, ?)');
            $stmt->execute([$userId, 'New Specialist', '']);
        }

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
            $resetLink = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'autimind.ddev.site') . '/reset-password/' . $token;
            Session::setFlash('success', 'Reset link: <a href="' . $resetLink . '">' . $resetLink . '</a>');
        } else {
            Session::setFlash('success', 'If that email exists, we have sent a password reset link.');
        }
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
        if (!$validator->validate($_POST, ['password' => 'required|min:8|confirmed'])) {
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
