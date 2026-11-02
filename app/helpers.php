<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!hash_equals($_SESSION['_csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token. Please go back and try again.');
    }
}

function auth_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_auth(): void
{
    if (!auth_user()) {
        redirect('index.php?page=login');
    }
}

function current_user_id(): ?int
{
    return isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
}

function upload_image(array $file, string $folder, array $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Image must be 5 MB or smaller.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!in_array($mime, $allowed, true)) {
        throw new RuntimeException('Invalid image type.');
    }

    $extensionMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    $filename = bin2hex(random_bytes(12)) . '.' . $extensionMap[$mime];
    $targetDir = __DIR__ . '/../public/uploads/' . $folder;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }
    $target = $targetDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Unable to save image.');
    }
    return 'uploads/' . $folder . '/' . $filename;
}


function avatar_markup(?array $user, string $class = 'avatar'): string
{
    $name = trim((string) ($user['full_name'] ?? 'K'));
    $image = $user['profile_image'] ?? null;
    if ($image) {
        return '<img class="' . e($class) . ' avatar-photo" src="' . e($image) . '" alt="' . e($name) . ' profile picture">';
    }
    return '<span class="' . e($class) . '" aria-label="' . e($name) . '">' . e(strtoupper(mb_substr($name, 0, 1))) . '</span>';
}

function friendly_date(?string $date): string
{
    if (!$date) return '';
    try { return date('M j, Y · g:i A', strtotime($date)); } catch (Throwable $e) { return $date; }
}
