<?php

/**
 * Shenava - Category Model
 * Handles all category-related database operations
 */

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected string $primaryKey = 'id';

    /**
     * Get all active categories with book count and parent info
     * @return array
     * @throws Exception
     */
    public function getCategories(): array
    {
        $sql = "SELECT c.*, 
                       p.name as parent_name,
                       COUNT(b.id) as book_count
                FROM categories c
                LEFT JOIN categories p ON c.parent_id = p.id
                LEFT JOIN books b ON c.id = b.category_id
                GROUP BY c.id
                ORDER BY c.sort_order, c.name";

        $this->db->query($sql);
        return $this->db->resultSet();
    }

    /**
     * Get category by slug
     * @param string $slug
     * @return object|null
     * @throws Exception
     */
    public function getBySlug(string $slug): ?object
    {
        $this->db->query("SELECT * FROM $this->table WHERE slug = :slug AND is_active = 1");
        $this->db->bind(':slug', $slug);
        return $this->db->single();
    }

    /**
     * Get all parent categories (categories without parent)
     * @return array
     * @throws Exception
     */
    public function getParentCategories(): array
    {
        $sql = "SELECT * FROM categories 
                WHERE (parent_id IS NULL OR parent_id = 0) 
                AND is_active = 1
                ORDER BY sort_order, name";

        $this->db->query($sql);
        return $this->db->resultSet();
    }

    /**
     * Get subcategories by parent ID
     * @param int $parentId
     * @return array
     * @throws Exception
     */
    public function getSubcategories(int $parentId): array
    {
        $sql = "SELECT c.*, COUNT(b.id) as book_count
                FROM categories c
                LEFT JOIN books b ON c.id = b.category_id AND b.is_active = 1
                WHERE c.parent_id = :parent_id AND c.is_active = 1
                GROUP BY c.id
                ORDER BY c.sort_order, c.name";

        $this->db->query($sql);
        $this->db->bind(':parent_id', $parentId);
        return $this->db->resultSet();
    }
}