<?php
/**
 * Shenava - User Model
 * Handles all user-related database operations
 */

class UserModel extends Model
{

    protected $table = 'users';
    protected $primaryKey = 'id';

    /**
     * Find user by email
     * @param string $email
     * @return object|null
     */
    public function findByEmail($email)
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE email = :email");
        $this->db->bind(':email', $email);
        return $this->db->single();
    }

    /**
     * Find user by username
     * @param string $username
     * @return object|null
     */
    public function findByUsername($username)
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE username = :username");
        $this->db->bind(':username', $username);
        return $this->db->single();
    }

    /**
     * Update user preferences
     * @param int $userId
     * @param array $preferences
     * @return bool
     */
    public function updatePreferences($userId, $preferences)
    {
        $allowedFields = [
            'dark_mode',
            'sleep_timer_enabled',
            'sleep_timer_duration',
            'driving_mode'
        ];

        $data = [];
        foreach ($preferences as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $data[$key] = $value;
            }
        }

        return $this->update($userId, $data);
    }

    /**
     * Get user favorites
     * @param int $userId
     * @param array $options
     * @return array
     */
    public function getFavorites($userId, $options = [])
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
     */
    public function addFavorite($userId, $bookId)
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
     */
    public function removeFavorite($userId, $bookId)
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
     */
    public function isFavorite($userId, $bookId)
    {
        $this->db->query("SELECT id FROM user_favorites WHERE user_id = :user_id AND book_id = :book_id");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':book_id', $bookId);
        return $this->db->single() !== false;
    }
}