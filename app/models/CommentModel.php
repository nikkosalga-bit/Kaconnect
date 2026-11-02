<?php
declare(strict_types=1);

class CommentModel
{
    public function __construct(private PDO $db) {}

    public function byPost(int $postId): array
    {
        $stmt = $this->db->prepare('SELECT c.*, u.username, u.full_name FROM comments c JOIN users u ON u.id=c.user_id WHERE c.post_id=? ORDER BY c.created_at ASC');
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    public function create(int $postId, int $userId, string $content): int
    {
        $stmt = $this->db->prepare('INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)');
        $stmt->execute([$postId, $userId, $content]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $userId, string $content): bool
    {
        $stmt = $this->db->prepare('UPDATE comments SET content=? WHERE id=? AND user_id=?');
        return $stmt->execute([$content, $id, $userId]);
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM comments WHERE id=? AND user_id=?');
        return $stmt->execute([$id, $userId]);
    }
}
