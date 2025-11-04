<?php
/**
 * Shenava - Author Model
 * Handles all author-related database operations
 */

class AuthorModel extends Model
{

    protected $table = 'authors';
    protected string $primaryKey = 'id';

    /**
     * Get active authors with book count
     * @return array
     * @throws Exception
     */
    public function getActiveAuthors(): array
    {
        $sql = "SELECT a.*, COUNT(b.id) as book_count
                FROM authors a
                LEFT JOIN books b ON a.id = b.author_id
                WHERE a.is_active = 1
                GROUP BY a.id
                ORDER BY a.name";

        $this->db->query($sql);
        return $this->db->resultSet();
    }

    /**
     * Get author by ID with book count
     * @param int $authorId
     * @return object|null
     * @throws Exception
     */
    public function getAuthorWithStats(int $authorId): ?object
    {
        $sql = "SELECT a.*, 
                       COUNT(b.id) as book_count,
                       COUNT(DISTINCT CASE WHEN b.is_featured = 1 THEN b.id END) as featured_book_count
                FROM authors a
                LEFT JOIN books b ON a.id = b.author_id
                WHERE a.id = :id
                GROUP BY a.id";

        $this->db->query($sql);
        $this->db->bind(':id', $authorId);
        return $this->db->single();
    }

    /**
     * Get authors for dropdown
     * @return array
     * @throws Exception
     */
    public function getAuthorsForDropdown(): array
    {
        $this->db->query("SELECT id, name FROM authors WHERE is_active = 1 ORDER BY name");
        return $this->db->resultSet();
    }

    /**
     * Search authors by name
     * @param string $searchTerm
     * @return array
     * @throws Exception
     */
    public function searchAuthors(string $searchTerm): array
    {
        $sql = "SELECT a.*, COUNT(b.id) as book_count
                FROM authors a
                LEFT JOIN books b ON a.id = b.author_id
                WHERE a.name LIKE :search OR a.bio LIKE :search
                GROUP BY a.id
                ORDER BY a.name";

        $this->db->query($sql);
        $this->db->bind(':search', "%$searchTerm%");
        return $this->db->resultSet();
    }

    /**
     * Get authors with pagination
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function getAuthorsPaginated(array $options = []): array
    {
        $limit = $options['limit'] ?? 20;
        $offset = $options['offset'] ?? 0;
        $search = $options['search'] ?? '';
        $activeOnly = $options['active_only'] ?? true;

        $where = "WHERE 1=1";
        $params = [];

        if ($activeOnly) {
            $where .= " AND a.is_active = 1";
        }

        if ($search) {
            $where .= " AND (a.name LIKE :search OR a.bio LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql = "SELECT a.*, COUNT(b.id) as book_count
                FROM authors a
                LEFT JOIN books b ON a.id = b.author_id
                $where
                GROUP BY a.id
                ORDER BY a.created_at DESC
                LIMIT :limit OFFSET :offset";

        $this->db->query($sql);

        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }

        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Count total authors
     * @param bool $activeOnly
     * @return int
     * @throws Exception
     */
    public function countAuthors(bool $activeOnly = true): int
    {
        $where = $activeOnly ? "WHERE is_active = 1" : "";
        $this->db->query("SELECT COUNT(*) as total FROM authors $where");
        $result = $this->db->single();
        return $result->total;
    }

    /**
     * Update author status
     * @param int $authorId
     * @param bool $isActive
     * @return bool
     * @throws Exception
     */
    public function updateStatus(int $authorId, bool $isActive): bool
    {
        return $this->update($authorId, [
            'is_active' => $isActive ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get authors with most books
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public function getTopAuthors(int $limit = 10): array
    {
        $sql = "SELECT a.*, COUNT(b.id) as book_count
                FROM authors a
                LEFT JOIN books b ON a.id = b.author_id
                WHERE a.is_active = 1
                GROUP BY a.id
                ORDER BY book_count DESC, a.name
                LIMIT :limit";

        $this->db->query($sql);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
}