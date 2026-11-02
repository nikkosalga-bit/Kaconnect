<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers.php';
foreach (glob(__DIR__ . '/../app/models/*.php') as $file) require_once $file;
foreach (glob(__DIR__ . '/../app/controllers/*.php') as $file) require_once $file;

try {
    $db = (new Database())->connect();
} catch (Throwable $e) {
    http_response_code(500);
    exit('Database connection failed. Check config/database.php and make sure MySQL is running.');
}

$users = new UserModel($db);
$posts = new PostModel($db);
$comments = new CommentModel($db);
$likes = new LikeModel($db);
$auth = new AuthController($users);
$profile = new ProfileController($users, $posts);
$post = new PostController($posts, $comments, $likes);
$comment = new CommentController($comments);

$page = $_GET['page'] ?? (auth_user() ? 'feed' : 'login');
$routes = [
    'register' => fn() => $auth->register(),
    'login' => fn() => $auth->login(),
    'logout' => fn() => $auth->logout(),
    'feed' => fn() => $post->feed(),
    'post_create' => fn() => $post->create(),
    'post_edit' => fn() => $post->edit(),
    'post_delete' => fn() => $post->delete(),
    'like' => fn() => $post->like(),
    'comment_create' => fn() => $comment->create(),
    'comment_edit' => fn() => $comment->edit(),
    'comment_delete' => fn() => $comment->delete(),
    'profile' => fn() => $profile->show(),
    'profile_edit' => fn() => $profile->edit(),
];

if (!isset($routes[$page])) { http_response_code(404); exit('Page not found.'); }
$routes[$page]();
