<?php
declare(strict_types=1);

class PostController
{
    public function __construct(private PostModel $posts, private CommentModel $comments, private LikeModel $likes) {}

    public function feed(): void
    {
        require_auth();
        $search = trim($_GET['q'] ?? '');
        $posts = $this->posts->all($search);
        foreach ($posts as &$post) {
            $post['comments'] = $this->comments->byPost((int) $post['id']);
            $post['liked'] = $this->likes->likedByUser((int) $post['id'], (int) current_user_id());
        }
        unset($post);
        require __DIR__ . '/../views/posts/feed.php';
    }

    public function create(): void
    {
        require_auth(); verify_csrf();
        $content = trim($_POST['content'] ?? '');
        if ($content === '' || mb_strlen($content) > 2000) { flash('error', 'Post content is required and must be 2,000 characters or less.'); redirect('index.php?page=feed'); }
        try { $image = upload_image($_FILES['image'] ?? [], 'posts'); $this->posts->create((int) current_user_id(), $content, $image); flash('success', 'Post published.'); }
        catch (Throwable $e) { flash('error', $e->getMessage()); }
        redirect('index.php?page=feed');
    }

    public function edit(): void
    {
        require_auth();
        $id = (int) ($_GET['id'] ?? 0);
        $post = $this->posts->find($id);
        if (!$post || (int) $post['user_id'] !== (int) current_user_id()) { http_response_code(403); exit('You cannot edit this post.'); }
        if ($_SERVER['REQUEST_METHOD'] === 'GET') { require __DIR__ . '/../views/posts/edit.php'; return; }
        verify_csrf();
        $content = trim($_POST['content'] ?? '');
        if ($content === '' || mb_strlen($content) > 2000) { flash('error', 'Post content is required and must be 2,000 characters or less.'); redirect('index.php?page=post_edit&id=' . $id); }
        $this->posts->update($id, (int) current_user_id(), $content);
        flash('success', 'Post updated.'); redirect('index.php?page=feed');
    }

    public function delete(): void
    {
        require_auth(); verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $this->posts->delete($id, (int) current_user_id());
        flash('success', 'Post deleted.'); redirect('index.php?page=feed');
    }

    public function like(): void
    {
        require_auth(); verify_csrf();
        $this->likes->toggle((int) ($_POST['post_id'] ?? 0), (int) current_user_id());
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php?page=feed');
    }
}
