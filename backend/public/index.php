<?php

/**
 * Shenava - Audiobook & Podcast App
 * Main Entry Point
 */

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Define base path
define('BASE_PATH', dirname(__DIR__));
const APP_PATH = BASE_PATH . '/app';

// Require autoloader
require_once APP_PATH . '/core/Autoloader.php';

// Initialize autoloader
$autoloader = new Autoloader();
$autoloader->register();

// Load configuration
$config = require_once APP_PATH . '/config/config.php';

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize router and handle request
try {
    $router = new Router();
    $router->dispatch();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error',
        'error' => $config['debug'] ? $e->getMessage() : null
    ]);
}