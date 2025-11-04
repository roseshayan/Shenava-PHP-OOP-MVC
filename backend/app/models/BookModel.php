<?php
/**
 * Shenava - Book Model
 * Handles all book-related database operations
 */

class BookModel extends Model
{

    protected $table = 'books';
    protected $primaryKey = 'id';

    /**
     * Get books with pagination and filters
     * @param array $filters
     * @return array
     */
    public function getBooks($filters = [])
    {
        $limit = $filters['limit'] ?? 20;
        $offset = $filters['offset'] ?? 0;
        $category = $filters['category'] ?? null;
        $search = $filters['search'] ?? null;
        $featured = $filters['featured'] ?? null;

        $sql = "SELECT b.*, 
                       a.name as author_name, 
                       n.name as narrator_name,
                       c.name as category_name,
                       c.slug as category_slug,
                       (SELECT COUNT(*) FROM reviews r WHERE r.book_id = b.id AND r.is_approved = 1) as review_count
                FROM books b
                LEFT JOIN authors a ON b.author_id = a.id
                LEFT JOIN authors n ON b.narrator_id = n.id
                LEFT JOIN categories c ON b.category_id = c.id
                WHERE b.is_active = 1";

        $params = [];

        if ($category) {
            $sql .= " AND c.slug = :category";
            $params[':category'] = $category;
        }

        if ($search) {
            $sql .= " AND (b.title LIKE :search OR b.description LIKE :search OR a.name LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        if ($featured) {
            $sql .= " AND b.is_featured = 1";
        }

        $sql .= " ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);

        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Get book by UUID with full details
     * @param string $uuid
     * @return object|null
     */
    public function getBookByUuid($uuid)
    {
        $sql = "SELECT b.*, 
                       a.name as author_name, 
                       a.bio as author_bio,
                       a.avatar_url as author_avatar,
                       n.name as narrator_name,
                       c.name as category_name,
                       c.slug as category_slug,
                       (SELECT COUNT(*) FROM reviews r WHERE r.book_id = b.id AND r.is_approved = 1) as review_count,
                       (SELECT AVG(rating) FROM reviews r WHERE r.book_id = b.id AND r.is_approved = 1) as average_rating
                FROM books b
                LEFT JOIN authors a ON b.author_id = a.id
                LEFT JOIN authors n ON b.narrator_id = n.id
                LEFT JOIN categories c ON b.category_id = c.id
                WHERE b.uuid = :uuid AND b.is_active = 1";

        $this->db->query($sql);
        $this->db->bind(':uuid', $uuid);
        return $this->db->single();
    }

    /**
     * Get book chapters
     * @param int $bookId
     * @return array
     */
    public function getChapters($bookId)
    {
        $this->db->query("SELECT * FROM chapters WHERE book_id = :book_id ORDER BY sort_order, chapter_number");
        $this->db->bind(':book_id', $bookId);
        return $this->db->resultSet();
    }

    /**
     * Get featured books
     * @param int $limit
     * @return array
     */
    public function getFeaturedBooks($limit = 10)
    {
        $this->db->query("SELECT b.*, a.name as author_name, c.name as category_name
                         FROM books b
                         LEFT JOIN authors a ON b.author_id = a.id
                         LEFT JOIN categories c ON b.category_id = c.id
                         WHERE b.is_featured = 1 AND b.is_active = 1
                         ORDER BY b.created_at DESC
                         LIMIT :limit");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Get books by category
     * @param string $categorySlug
     * @param array $options
     * @return array
     */
    public function getBooksByCategory($categorySlug, $options = [])
    {
        $limit = $options['limit'] ?? 20;
        $offset = $options['offset'] ?? 0;

        $sql = "SELECT b.*, a.name as author_name, c.name as category_name
                FROM books b
                LEFT JOIN authors a ON b.author_id = a.id
                LEFT JOIN categories c ON b.category_id = c.id
                WHERE c.slug = :category_slug AND b.is_active = 1
                ORDER BY b.created_at DESC
                LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        $this->db->bind(':category_slug', $categorySlug);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Increment book views
     * @param int $bookId
     * @return bool
     */
    public function incrementViews($bookId)
    {
        $this->db->query("UPDATE books SET total_views = total_views + 1 WHERE id = :id");
        $this->db->bind(':id', $bookId);
        return $this->db->execute();
    }
}