<?php
declare(strict_types=1);

class CommentController
{
    public function __construct(private CommentModel $comments) {}

    public function create(): void
    {
        require_auth(); verify_csrf();
        $postId = (int) ($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        if ($postId > 0 && $content !== '' && mb_strlen($content) <= 1000) $this->comments->create($postId, (int) current_user_id(), $content);
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php?page=feed');
    }

    public function edit(): void
    {
        require_auth(); verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        if ($content !== '' && mb_strlen($content) <= 1000) $this->comments->update($id, (int) current_user_id(), $content);
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php?page=feed');
    }

    public function delete(): void
    {
        require_auth(); verify_csrf();
        $this->comments->delete((int) ($_POST['id'] ?? 0), (int) current_user_id());
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php?page=feed');
    }
}
