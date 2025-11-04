<?php
/**
 * Shenava - Database Class
 * PDO wrapper for database operations
 */

class Database
{
    private PDO $pdo;
    private $stmt;
    private $error;

    /**
     * Constructor - connect to database
     * @throws Exception
     */
    public function __construct()
    {
        try {
            // Load config directly to avoid path issues
            $config = [
                'host' => 'localhost',
                'database' => 'shenava_db',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8'
            ];

            $dsn = "mysql:host={$config['host']};dbname={$config['database']}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_PERSISTENT => false // Non-persistent for better error handling
            ];

            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);

            // Set charset separately
            $this->pdo->exec("SET NAMES '{$config['charset']}'");

        } catch (PDOException $e) {
            // More detailed error message
            $errorMsg = "Database connection failed: " . $e->getMessage();
            $errorMsg .= "\nPlease check:";
            $errorMsg .= "\n- MySQL server is running";
            $errorMsg .= "\n- Database 'shenava_db' exists";
            $errorMsg .= "\n- Username/password are correct";
            throw new Exception($errorMsg);
        }
    }

    /**
     * Prepare statement
     */
    public function query($sql): void
    {
        $this->stmt = $this->pdo->prepare($sql);
    }

    /**
     * Bind parameters
     */
    public function bind($param, $value, $type = null): void
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
     * @throws Exception
     */
    public function execute()
    {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }

    /**
     * Get result set as array
     * @throws Exception
     */
    public function resultSet()
    {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    /**
     * Get a single record
     * @throws Exception
     */
    public function single()
    {
        $this->execute();
        return $this->stmt->fetch();
    }

    /**
     * Get row count
     */
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId(): bool|string
    {
        return $this->pdo->lastInsertId();
    }
}