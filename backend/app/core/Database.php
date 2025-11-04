<?php

/**
 * Shenava - Database Class
 * PDO wrapper for database operations
 */

class Database
{

    private PDO $pdo;
    private $stmt;
    private string $error;

    /**
     * Constructor - connect to database
     * @throws Exception
     */
    public function __construct()
    {
        $config = require_once APP_PATH . '/config/database.php';

        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ];

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            throw new Exception("Database connection failed: " . $this->error);
        }
    }

    /**
     * Prepare statement
     * @param string $sql
     */
    public function query(string $sql): void
    {
        $this->stmt = $this->pdo->prepare($sql);
    }

    /**
     * Bind parameters
     * @param mixed $param
     * @param mixed $value
     * @param int|null $type
     */
    public function bind(mixed $param, mixed $value, int $type = null): void
    {
        if (is_null($type)) {
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
        }

        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * Execute prepared statement
     * @return bool
     * @throws Exception
     */
    public function execute(): bool
    {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }

    /**
     * Get result set as array
     * @return array
     * @throws Exception
     */
    public function resultSet(): array
    {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    /**
     * Get a single record
     * @return object
     * @throws Exception
     */
    public function single(): object
    {
        $this->execute();
        return $this->stmt->fetch();
    }

    /**
     * Get row count
     * @return int
     */
    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * Get last insert ID
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->pdo->rollback();
    }
}