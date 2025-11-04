<?php
/**
 * Shenava - Admin Logout
 */

session_save_path('/tmp');
session_start();
session_unset();
session_destroy();

header('Location: login.php');
exit;
?>