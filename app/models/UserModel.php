<?php
declare(strict_types=1);

class UserModel
{
    public function __construct(private PDO $db) {}

    public function create(string $username, string $passwordHash, string $fullName, string $bio, ?string $profileImage): int
    {
        $stmt = $this->db->prepare('INSERT INTO users (username, password, full_name, bio, profile_image) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$username, $passwordHash, $fullName, $bio, $profileImage]);
        return (int) $this->db->lastInsertId();
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, username, full_name, bio, profile_image, created_at FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function update(int $id, string $username, string $fullName, string $bio, ?string $profileImage = null): bool
    {
        if ($profileImage !== null) {
            $stmt = $this->db->prepare('UPDATE users SET username = ?, full_name = ?, bio = ?, profile_image = ? WHERE id = ?');
            return $stmt->execute([$username, $fullName, $bio, $profileImage, $id]);
        }
        $stmt = $this->db->prepare('UPDATE users SET username = ?, full_name = ?, bio = ? WHERE id = ?');
        return $stmt->execute([$username, $fullName, $bio, $id]);
    }

    public function usernameTaken(string $username, int $exceptId = 0): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE username = ? AND id <> ?');
        $stmt->execute([$username, $exceptId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function search(string $term): array
    {
        $like = '%' . $term . '%';
        $stmt = $this->db->prepare('SELECT id, username, full_name, bio, profile_image FROM users WHERE username LIKE ? OR full_name LIKE ? ORDER BY full_name LIMIT 20');
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }
}
