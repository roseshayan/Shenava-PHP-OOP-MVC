<?php
/**
 * Shenava - Main Entry Point
 */

// Define constants
define('APP_PATH', dirname(__DIR__) . '/app');
define('ROOT_PATH', dirname(__DIR__));

// Load ALL core files manually first to avoid autoloader issues
require_once APP_PATH . '/core/Autoloader.php';
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/Database.php';
require_once APP_PATH . '/core/Model.php';
require_once APP_PATH . '/core/Router.php';
require_once APP_PATH . '/core/Auth.php';
require_once APP_PATH . '/core/ResponseFormatter.php';

// Register autoloader
$autoloader = new Autoloader();
$autoloader->register();

// Load configuration
$config = require_once APP_PATH . '/config/config.php';

// Set error reporting
if ($config['app']['debug']) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Set headers for CORS and JSON
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize router and dispatch
try {
    $router = new Router();

    // Load API routes
    require_once APP_PATH . '/routes/api.php';

    $router->dispatch();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal Server Error',
        'error' => $config['app']['debug'] ? $e->getMessage() : 'Something went wrong',
        'trace' => $config['app']['debug'] ? $e->getTraceAsString() : null
    ]);
}