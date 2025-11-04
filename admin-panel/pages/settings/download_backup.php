<?php
/**
 * Shenava - Download Backup File
 */
session_save_path('/tmp');
session_start();
require_once '../../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
define('BACKEND_PATH', BASE_PATH . '/backend');

// Check if file parameter is provided
if (!isset($_GET['file'])) {
    header('HTTP/1.0 400 Bad Request');
    exit('Filename parameter is required');
}

$filename = basename($_GET['file']);
$backupDir = BACKEND_PATH . '/backups/';
$filepath = $backupDir . $filename;

// Security check: prevent directory traversal
if (str_contains($filename, '..') || !preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
    header('HTTP/1.0 400 Bad Request');
    exit('Invalid filename');
}

// Check if the file exists and is an SQL file
if (!file_exists($filepath) || pathinfo($filename, PATHINFO_EXTENSION) !== 'sql') {
    header('HTTP/1.0 404 Not Found');
    exit('Backup file not found');
}

// Set headers for file download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));

// Clear output buffer
ob_clean();
flush();

// Read the file and output it
readfile($filepath);
exit;