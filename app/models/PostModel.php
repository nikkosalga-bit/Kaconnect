<?php
declare(strict_types=1);

class PostModel
{
    public function __construct(private PDO $db) {}

    public function create(int $userId, string $content, ?string $image): int
    {
        $stmt = $this->db->prepare('INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $content, $image]);
        return (int) $this->db->lastInsertId();
    }

    public function all(?string $search = null): array
    {
        if ($search !== null && $search !== '') {
            $like = '%' . $search . '%';
            $stmt = $this->db->prepare('SELECT p.*, u.username, u.full_name, u.profile_image, (SELECT COUNT(*) FROM likes l WHERE l.post_id=p.id) AS like_count FROM posts p JOIN users u ON u.id=p.user_id WHERE p.content LIKE ? OR u.full_name LIKE ? OR u.username LIKE ? ORDER BY p.created_at DESC');
            $stmt->execute([$like, $like, $like]);
        } else {
            $stmt = $this->db->query('SELECT p.*, u.username, u.full_name, u.profile_image, (SELECT COUNT(*) FROM likes l WHERE l.post_id=p.id) AS like_count FROM posts p JOIN users u ON u.id=p.user_id ORDER BY p.created_at DESC');
        }
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM posts WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function update(int $id, int $userId, string $content, ?string $image = null): bool
    {
        if ($image !== null) {
            $stmt = $this->db->prepare('UPDATE posts SET content = ?, image = ? WHERE id = ? AND user_id = ?');
            return $stmt->execute([$content, $image, $id, $userId]);
        }
        $stmt = $this->db->prepare('UPDATE posts SET content = ? WHERE id = ? AND user_id = ?');
        return $stmt->execute([$content, $id, $userId]);
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM posts WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $userId]);
    }

    public function userPosts(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT p.*, (SELECT COUNT(*) FROM likes l WHERE l.post_id=p.id) AS like_count FROM posts p WHERE p.user_id = ? ORDER BY p.created_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
