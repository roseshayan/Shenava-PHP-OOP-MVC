<?php
/**
 * Shenava - User Model
 * Handles all user-related database operations
 */

class UserModel extends Model
{

    protected $table = 'users';
    protected string $primaryKey = 'id';

    /**
     * Find user by email
     * @param string $email
     * @return object|null
     * @throws Exception
     */
    public function findByEmail(string $email): ?object
    {
        $this->db->query("SELECT * FROM $this->table WHERE email = :email");
        $this->db->bind(':email', $email);
        return $this->db->single();
    }

    /**
     * Find user by username
     * @param string $username
     * @return object|null
     * @throws Exception
     */
    public function findByUsername(string $username): ?object
    {
        $this->db->query("SELECT * FROM $this->table WHERE username = :username");
        $this->db->bind(':username', $username);
        return $this->db->single();
    }

    /**
     * Update user preferences
     * @param int $userId
     * @param array $preferences
     * @return bool
     * @throws Exception
     */
    public function updatePreferences(int $userId, array $preferences): bool
    {
        $allowedFields = [
            'dark_mode',
            'sleep_timer_enabled',
            'sleep_timer_duration',
            'driving_mode'
        ];

        $data = array_filter($preferences, function ($key) use ($allowedFields) {
            return in_array($key, $allowedFields);
        }, ARRAY_FILTER_USE_KEY);

        return $this->update($userId, $data);
    }

    /**
     * Get user favorites
     * @param int $userId
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function getFavorites(int $userId, array $options = []): array
    {
        $limit = $options['limit'] ?? 20;
        $offset = $options['offset'] ?? 0;

        $sql = "SELECT b.*, a.name as author_name, c.name as category_name
                FROM user_favorites uf
                JOIN books b ON uf.book_id = b.id
                JOIN authors a ON b.author_id = a.id
                JOIN categories c ON b.category_id = c.id
                WHERE uf.user_id = :user_id
                ORDER BY uf.created_at DESC
                LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Add book to favorites
     * @param int $userId
     * @param int $bookId
     * @return bool
     * @throws Exception
     */
    public function addFavorite(int $userId, int $bookId): bool
    {
        $this->db->query("INSERT IGNORE INTO user_favorites (user_id, book_id) VALUES (:user_id, :book_id)");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':book_id', $bookId);
        return $this->db->execute();
    }

    /**
     * Remove book from favorites
     * @param int $userId
     * @param int $bookId
     * @return bool
     * @throws Exception
     */
    public function removeFavorite(int $userId, int $bookId): bool
    {
        $this->db->query("DELETE FROM user_favorites WHERE user_id = :user_id AND book_id = :book_id");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':book_id', $bookId);
        return $this->db->execute();
    }

    /**
     * Check if book is in favorites
     * @param int $userId
     * @param int $bookId
     * @return bool
     * @throws Exception
     */
    public function isFavorite(int $userId, int $bookId): bool
    {
        $this->db->query("SELECT id FROM user_favorites WHERE user_id = :user_id AND book_id = :book_id");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':book_id', $bookId);
        return $this->db->single() != false;
    }
}