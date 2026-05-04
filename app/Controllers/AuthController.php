<?php
namespace App\Controllers;

use Core\Controller;

class AuthController extends Controller {

    public function login() {
        require_once __DIR__ . '/../../includes/auth.php';
        if (isset($_SESSION['user_id'])) {
            $this->redirect('dashboard');
        }

        $data = [
            'registered' => isset($_GET['registered']) && $_GET['registered'] === '1',
            'logout' => isset($_GET['logout']) && $_GET['logout'] === '1',
            'timeout' => isset($_GET['timeout']) && $_GET['timeout'] === '1',
            'error' => $_GET['error'] ?? null
        ];

        $this->renderWithoutLayout('auth/login', $data);
    }

    public function postLogin() {
        require_once __DIR__ . '/../../includes/db.php';
        require_once __DIR__ . '/../../includes/auth.php';
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $this->redirect('login?error=' . urlencode('Email and password are required.'));
        }

        // Look up user via Firebase — catch config/network errors so they show
        // a friendly message instead of a fatal 500.
        try {
            $user = firebase_users_find_by_email($email);
        } catch (Throwable $e) {
            error_log('[auth] Firebase error during login: ' . $e->getMessage());
            $this->redirect('login?error=' . urlencode('Service temporarily unavailable. Please try again later.'));
            return;
        }
        
        if (!$user || empty($user['password_hash'])) {
            $this->redirect('login?error=' . urlencode('Invalid credentials.'));
        }

        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'] ?? 'User';
            $_SESSION['user_role'] = $user['role'] ?? 'User';
            $_SESSION['last_activity'] = time();

            $this->redirect('dashboard');
        } else {
            $this->redirect('login?error=' . urlencode('Invalid credentials.'));
        }
    }

    public function logout() {
        require_once __DIR__ . '/../../includes/auth.php';
        auth_logout_and_redirect();
    }
    
    public function register() {
        // Simple stub for now
        $this->redirect('login');
    }
}
