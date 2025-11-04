<?php
/**
 * Shenava - Base Model Class
 * Provides common database operations for all models
 */

abstract class Model
{

    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Find record by ID
     * @param int $id
     * @return object|null
     */
    public function find($id)
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Find record by UUID
     * @param string $uuid
     * @return object|null
     */
    public function findByUuid($uuid)
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE uuid = :uuid");
        $this->db->bind(':uuid', $uuid);
        return $this->db->single();
    }

    /**
     * Get all records
     * @param array $options
     * @return array
     */
    public function all($options = [])
    {
        $limit = $options['limit'] ?? 50;
        $offset = $options['offset'] ?? 0;
        $orderBy = $options['orderBy'] ?? 'created_at DESC';

        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} LIMIT :limit OFFSET :offset";
        $this->db->query($sql);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Create new record
     * @param array $data
     * @return int|bool
     */
    public function create($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($sql);

        foreach ($data as $key => $value) {
            $this->db->bind(':' . $key, $value);
        }

        return $this->db->execute() ? $this->db->lastInsertId() : false;
    }

    /**
     * Update record
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        $set = '';
        foreach ($data as $key => $value) {
            $set .= "{$key} = :{$key}, ";
        }
        $set = rtrim($set, ', ');

        $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = :id";
        $this->db->query($sql);

        foreach ($data as $key => $value) {
            $this->db->bind(':' . $key, $value);
        }
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    /**
     * Delete record
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $this->db->query("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Count total records
     * @return int
     */
    public function count()
    {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        $result = $this->db->single();
        return $result->total;
    }
}