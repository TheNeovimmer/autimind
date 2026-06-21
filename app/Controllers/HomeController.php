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
        View::render('public/contact', ['title' => 'Contact', 'csrf_token' => Session::csrf_token()], 'main');
    }

    public function submitContact(): void
    {
        if (!Session::verify_csrf($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            View::render('errors/419', [], 'main');
            return;
        }

        $validator = new Validator();
        if (!$validator->validate($_POST, [
            'name' => 'required|max:255',
            'email' => 'required|email',
            'subject' => 'required|max:255',
            'message' => 'required'
        ])) {
            View::render('public/contact', ['errors' => $validator->errors(), 'old' => $_POST, 'csrf_token' => Session::csrf_token()], 'main');
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
        $faqItems = $stmt ? $stmt->fetchAll() : [];
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
        $specialists = $stmt ? $stmt->fetchAll() : [];
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
}
