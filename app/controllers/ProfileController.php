<?php
declare(strict_types=1);

class ProfileController
{
    public function __construct(private UserModel $users, private PostModel $posts) {}

    public function show(): void
    {
        require_auth();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : (int) current_user_id();
        $user = $this->users->findById($id);
        if (!$user) { http_response_code(404); exit('User not found.'); }
        $userPosts = $this->posts->userPosts($id);
        require __DIR__ . '/../views/profile/profile.php';
    }

    public function edit(): void
    {
        require_auth();
        $user = $this->users->findById((int) current_user_id());
        if ($_SERVER['REQUEST_METHOD'] === 'GET') { require __DIR__ . '/../views/profile/edit.php'; return; }
        verify_csrf();
        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $errors = [];
        if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) $errors[] = 'Invalid username format.';
        if ($fullName === '' || mb_strlen($fullName) > 100) $errors[] = 'Full name is required and must be 100 characters or fewer.';
        if (mb_strlen($bio) > 500) $errors[] = 'Bio must be 500 characters or fewer.';
        if ($this->users->usernameTaken($username, (int) $user['id'])) $errors[] = 'Username is already taken.';
        $image = null;
        try { $image = upload_image($_FILES['profile_image'] ?? [], 'profiles'); } catch (Throwable $e) { $errors[] = $e->getMessage(); }
        if (!$errors) {
            $this->users->update((int) $user['id'], $username, $fullName, $bio, $image);
            $_SESSION['user'] = $this->users->findById((int) $user['id']);
            flash('success', 'Profile updated.');
            redirect('index.php?page=profile');
        }
        require __DIR__ . '/../views/profile/edit.php';
    }
}
