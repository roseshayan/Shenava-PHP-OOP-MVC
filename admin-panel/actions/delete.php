<?php
/**
 * Shenava - Delete Action
 * Handles deletion of various items
 */

session_start();
require_once '../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/..');
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';

if (!isset($_GET['type']) || !isset($_GET['id'])) {
    $_SESSION['error'] = 'پارامترهای لازم ارسال نشده است';
    header('Location: ../index.php');
    exit;
}

$type = $_GET['type'];
$id = intval($_GET['id']);
$db = new Database();

try {
    switch ($type) {
        case 'user':
            // Check if user has any data before deletion
            $db->query("SELECT COUNT(*) as count FROM listening_history WHERE user_id = :id");
            $db->bind(':id', $id);
            $userData = $db->single();

            if ($userData->count > 0) {
                $_SESSION['error'] = 'این کاربر دارای تاریخچه گوش دادن است و نمی‌توان حذف کرد';
            } else {
                $db->query("DELETE FROM users WHERE id = :id");
                $db->bind(':id', $id);
                $db->execute();
                $_SESSION['success'] = 'کاربر با موفقیت حذف شد';
            }
            header('Location: ../pages/users/list.php');
            break;

        case 'book':
            // Check if book has chapters
            $db->query("SELECT COUNT(*) as count FROM chapters WHERE book_id = :id");
            $db->bind(':id', $id);
            $chapterCount = $db->single();

            if ($chapterCount->count > 0) {
                $_SESSION['error'] = 'این کتاب دارای فصل است و نمی‌توان حذف کرد. ابتدا فصل‌ها را حذف کنید.';
            } else {
                $db->query("DELETE FROM books WHERE id = :id");
                $db->bind(':id', $id);
                $db->execute();
                $_SESSION['success'] = 'کتاب با موفقیت حذف شد';
            }
            header('Location: ../pages/books/list.php');
            break;

        case 'category':
            // Check if category has books
            $db->query("SELECT COUNT(*) as count FROM books WHERE category_id = :id");
            $db->bind(':id', $id);
            $bookCount = $db->single();

            if ($bookCount->count > 0) {
                $_SESSION['error'] = 'این دسته‌بندی دارای کتاب است و نمی‌توان حذف کرد';
            } else {
                $db->query("DELETE FROM categories WHERE id = :id");
                $db->bind(':id', $id);
                $db->execute();
                $_SESSION['success'] = 'دسته‌بندی با موفقیت حذف شد';
            }
            header('Location: ../pages/categories/list.php');
            break;

        case 'author':
            // Check if author has books
            $db->query("SELECT COUNT(*) as count FROM books WHERE author_id = :id OR narrator_id = :id");
            $db->bind(':id', $id);
            $authorBooks = $db->single();

            if ($authorBooks->count > 0) {
                $_SESSION['error'] = 'این نویسنده دارای کتاب است و نمی‌توان حذف کرد';
            } else {
                $db->query("DELETE FROM authors WHERE id = :id");
                $db->bind(':id', $id);
                $db->execute();
                $_SESSION['success'] = 'نویسنده با موفقیت حذف شد';
            }
            header('Location: ../pages/authors/list.php');
            break;

        case 'chapter':
            // Get chapter info for redirection
            $db->query("SELECT book_id FROM chapters WHERE id = :id");
            $db->bind(':id', $id);
            $chapter = $db->single();

            if ($chapter) {
                // Delete chapter
                $db->query("DELETE FROM chapters WHERE id = :id");
                $db->bind(':id', $id);
                $db->execute();
                $_SESSION['success'] = 'فصل با موفقیت حذف شد';
                header('Location: ../pages/books/chapters.php?book_id=' . $chapter->book_id);
            } else {
                $_SESSION['error'] = 'فصل یافت نشد';
                header('Location: ../pages/chapters/list.php');
            }
            break;

        case 'review':
            $db->query("DELETE FROM reviews WHERE id = :id");
            $db->bind(':id', $id);
            $db->execute();
            $_SESSION['success'] = 'نظر با موفقیت حذف شد';
            header('Location: ../pages/reviews/list.php');
            break;

        default:
            $_SESSION['error'] = 'نوع نامعتبر';
            header('Location: ../index.php');
            break;
    }

} catch (Exception $e) {
    $_SESSION['error'] = 'خطا در حذف: ' . $e->getMessage();

    // Redirect back based on type
    switch ($type) {
        case 'user':
            header('Location: ../pages/users/list.php');
            break;
        case 'book':
            header('Location: ../pages/books/list.php');
            break;
        case 'category':
            header('Location: ../pages/categories/list.php');
            break;
        case 'author':
            header('Location: ../pages/authors/list.php');
            break;
        case 'chapter':
            header('Location: ../pages/chapters/list.php');
            break;
        case 'review':
            header('Location: ../pages/reviews/list.php');
            break;
        default:
            header('Location: ../index.php');
            break;
    }
}
exit;
?>