<?php
/**
 * Shenava - Authentication Check
 * Protects admin pages from unauthorized access
 */

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../../login.php');
    exit;
}

// Session timeout (8 hours)
$sessionTimeout = 8 * 60 * 60; // 8 hours in seconds
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $sessionTimeout)) {
    session_unset();
    session_destroy();
    header('Location: ../../login.php?timeout=1');
    exit;
}

// Update session time on each request
$_SESSION['login_time'] = time();
?>