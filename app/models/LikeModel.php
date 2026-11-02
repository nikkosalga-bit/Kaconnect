<?php
declare(strict_types=1);

class LikeModel
{
    public function __construct(private PDO $db) {}

    public function toggle(int $postId, int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM likes WHERE post_id=? AND user_id=? LIMIT 1');
        $stmt->execute([$postId, $userId]);
        $like = $stmt->fetch();
        if ($like) {
            $delete = $this->db->prepare('DELETE FROM likes WHERE id=?');
            $delete->execute([(int) $like['id']]);
            return false;
        }
        $insert = $this->db->prepare('INSERT INTO likes (post_id, user_id) VALUES (?, ?)');
        $insert->execute([$postId, $userId]);
        return true;
    }

    public function likedByUser(int $postId, int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM likes WHERE post_id=? AND user_id=?');
        $stmt->execute([$postId, $userId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
