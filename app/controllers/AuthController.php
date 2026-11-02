<?php
declare(strict_types=1);

class AuthController
{
    public function __construct(private UserModel $users) {}

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require __DIR__ . '/../views/auth/register.php';
            return;
        }
        verify_csrf();
        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];
        if ($fullName === '' || mb_strlen($fullName) < 2) $errors[] = 'Full name is required.';
        if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) $errors[] = 'Username must be 3–30 characters using letters, numbers, or underscore.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($this->users->findByUsername($username)) $errors[] = 'Username is already taken.';
        if ($errors) { require __DIR__ . '/../views/auth/register.php'; return; }
        $id = $this->users->create($username, password_hash($password, PASSWORD_DEFAULT), $fullName, '', null);
        session_regenerate_id(true);
        $_SESSION['user'] = $this->users->findById($id);
        flash('success', 'Account created successfully.');
        redirect('index.php?page=feed');
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') { require __DIR__ . '/../views/auth/login.php'; return; }
        verify_csrf();
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $user = $this->users->findByUsername($username);
        $errors = [];
        if (!$user || !password_verify($password, $user['password'])) $errors[] = 'Invalid username or password.';
        if ($errors) { require __DIR__ . '/../views/auth/login.php'; return; }
        session_regenerate_id(true);
        unset($user['password']);
        $_SESSION['user'] = $user;
        redirect('index.php?page=feed');
    }

    public function logout(): void
    {
        require_auth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed.'); }
        verify_csrf();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        redirect('index.php?page=login');
    }
}
