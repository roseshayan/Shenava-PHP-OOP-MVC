<?php
/**
 * Shenava - Toggle Status Action
 * Handles activating/deactivating items
 */

session_start();
require_once '../includes/auth-check.php';

if (!isset($_GET['type']) || !isset($_GET['id'])) {
    $_SESSION['error'] = 'پارامترهای لازم ارسال نشده است';
    header('Location: ../index.php');
    exit;
}

$type = $_GET['type'];
$id = intval($_GET['id']);
$db = new Database();

switch ($type) {
    case 'user':
        try {
            $db->query("UPDATE users SET is_active = NOT is_active WHERE id = :id");
            $db->bind(':id', $id);
            $db->execute();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        $_SESSION['success'] = 'وضعیت کاربر با موفقیت تغییر کرد';
        header('Location: ../pages/users/list.php');
        break;

    case 'book':
        try {
            $db->query("UPDATE books SET is_active = NOT is_active WHERE id = :id");
            $db->bind(':id', $id);
            $db->execute();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        $_SESSION['success'] = 'وضعیت کتاب با موفقیت تغییر کرد';
        header('Location: ../pages/books/list.php');
        break;

    case 'category':
        try {
            $db->query("UPDATE categories SET is_active = NOT is_active WHERE id = :id");
            $db->bind(':id', $id);
            $db->execute();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        $_SESSION['success'] = 'وضعیت دسته‌بندی با موفقیت تغییر کرد';
        header('Location: ../pages/categories/list.php');
        break;

    default:
        $_SESSION['error'] = 'نوع نامعتبر';
        header('Location: ../index.php');
        break;
}
exit;
?>