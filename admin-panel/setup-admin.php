<?php
/**
 * Shenava - Admin Setup
 * Run this once to create admin user
 */

session_save_path('/tmp');
session_start();

// Define base paths
define('BASE_PATH', dirname(__DIR__));
const BACKEND_PATH = BASE_PATH . '/backend';
const APP_PATH = BACKEND_PATH . '/app';

// Load database configuration directly
$dbConfig = [
    'host' => 'localhost',
    'database' => 'shenava_db', // Change to your database name
    'username' => 'root',       // Change to your database username
    'password' => '',           // Change to your database password
    'charset' => 'utf8mb4'
];

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $options = [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ];

    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);

    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    $adminExists = $stmt->fetch();

    if ($adminExists) {
        echo "✅ Admin user already exists!\n";
        echo "You can login with:\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
        exit;
    }

    // Create admin user
    $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    $passwordHash = password_hash('admin123', PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO users (uuid, username, email, password_hash, display_name, is_active, is_premium) 
                           VALUES (:uuid, 'admin', 'admin@shenava.com', :password_hash, 'System Administrator', 1, 1)");

    $stmt->bindValue(':uuid', $uuid);
    $stmt->bindValue(':password_hash', $passwordHash);

    if ($stmt->execute()) {
        echo "✅ Admin user created successfully!\n";
        echo "Login credentials:\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
        echo "Email: admin@shenava.com\n\n";
        echo "⚠️  IMPORTANT: Change the password after first login!\n";
        echo "📝 Access admin panel at: http://localhost/shenava/admin-panel/\n";
    } else {
        echo "❌ Failed to create admin user\n";
    }

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    echo "💡 Make sure:\n";
    echo "   - Database 'shenava_db' exists\n";
    echo "   - MySQL is running\n";
    echo "   - Database credentials are correct in setup-admin.php\n";
}