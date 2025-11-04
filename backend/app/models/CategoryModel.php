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
     * Get all active categories with book count
     * @return array
     */
    public function getActiveCategories(): array
    {
        $sql = "SELECT c.*, COUNT(b.id) as book_count
                FROM categories c
                LEFT JOIN books b ON c.id = b.category_id AND b.is_active = 1
                WHERE c.is_active = 1
                GROUP BY c.id
                ORDER BY c.sort_order, c.name";

        $this->db->query($sql);
        return $this->db->resultSet();
    }

    /**
     * Get category by slug
     * @param string $slug
     * @return object|null
     */
    public function getBySlug(string $slug): ?object
    {
        $this->db->query("SELECT * FROM $this->table WHERE slug = :slug AND is_active = 1");
        $this->db->bind(':slug', $slug);
        return $this->db->single();
    }
}