<?php
/**
 * Shenava - Authors Management
 */

session_start();
require_once '../../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';
require_once BACKEND_PATH . '/app/core/Model.php';
require_once BACKEND_PATH . '/app/models/AuthorModel.php';

$db = new Database();

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);

    if ($action == 'delete') {
        try {
            // Check if author has books
            $db->query("SELECT COUNT(*) as book_count FROM books WHERE author_id = :id");
            $db->bind(':id', $id);
            $result = $db->single();
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        if ($result->book_count > 0) {
            $_SESSION['error'] = 'این نویسنده دارای کتاب است و نمی‌توان حذف کرد';
        } else {
            try {
                $db->query("DELETE FROM authors WHERE id = :id");
                $db->bind(':id', $id);
                $db->execute();
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            $_SESSION['success'] = 'نویسنده با موفقیت حذف شد';
        }
    }

    header('Location: list.php');
    exit;
}

try {
    // Get authors with book counts
    $db->query("SELECT a.*, COUNT(b.id) as book_count 
           FROM authors a 
           LEFT JOIN books b ON a.id = b.author_id 
           GROUP BY a.id 
           ORDER BY a.created_at DESC");
    $authors = $db->resultSet();
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت نویسندگان - شنوا</title>

    <!-- Bootstrap 5 CSS -->
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../node_modules/bootstrap-icons/font/bootstrap-icons.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../../node_modules/@fortawesome/fontawesome-free/css/all.min.css">

    <!-- Vazir Font -->
    <link href="../../../node_modules/vazirmatn/misc/Farsi-Digits/Vazirmatn-FD-font-face.min.css" rel="stylesheet">
</head>
<body>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">مدیریت نویسندگان</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        افزودن نویسنده جدید
                    </a>
                </div>
            </div>

            <!-- Notifications -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Authors Grid -->
            <div class="row g-4">
                <?php foreach ($authors as $author): ?>
                    <div class="col-xl-4 col-lg-6 col-md-6">
                        <div class="card author-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <?php if ($author->avatar_url): ?>
                                        <img src="<?php echo $author->avatar_url; ?>"
                                             alt="<?php echo $author->name; ?>"
                                             class="rounded-circle me-3"
                                             width="80" height="80" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center me-3"
                                             style="width: 80px; height: 80px;">
                                            <i class="fas fa-user-edit text-muted fa-2x"></i>
                                        </div>
                                    <?php endif; ?>

                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-1"><?php echo $author->name; ?></h5>

                                        <?php if ($author->bio): ?>
                                            <p class="card-text text-muted small mb-2">
                                                <?php echo strlen($author->bio) > 100 ? substr($author->bio, 0, 100) . '...' : $author->bio; ?>
                                            </p>
                                        <?php endif; ?>

                                        <div class="d-flex justify-content-between align-items-center text-muted small">
                                            <span>
                                                <i class="fas fa-book me-1"></i>
                                                <?php echo $author->book_count; ?> کتاب
                                            </span>
                                            <span>
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('Y/m/d', strtotime($author->created_at)); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-<?php echo $author->is_active ? 'success' : 'secondary'; ?>">
                                        <?php echo $author->is_active ? 'فعال' : 'غیرفعال'; ?>
                                    </span>

                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?php echo $author->id; ?>"
                                           class="btn btn-outline-primary"
                                           title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $author->id; ?>"
                                           class="btn btn-outline-danger btn-delete"
                                           title="حذف"
                                           data-confirm="آیا از حذف این نویسنده مطمئن هستید؟">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Add New Author Card -->
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="card author-card h-100 border-dashed">
                        <a href="add.php" class="card-body d-flex align-items-center justify-content-center text-decoration-none text-muted">
                            <div class="text-center">
                                <i class="fas fa-plus-circle fa-3x mb-3"></i>
                                <h5>افزودن نویسنده جدید</h5>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <?php if (empty($authors)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-user-edit fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">هیچ نویسنده‌ای یافت نشد</h4>
                    <p class="text-muted">اولین نویسنده را ایجاد کنید</p>
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        افزودن نویسنده
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Scripts -->
<script src="../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../../node_modules/jquery/dist/jquery.min.js"></script>
<script src="../../js/app.js"></script>

<style>
    .author-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: none;
        border-radius: 12px;
    }

    .author-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .border-dashed {
        border: 2px dashed #dee2e6 !important;
    }

    .border-dashed:hover {
        border-color: var(--primary-color) !important;
        color: var(--primary-color) !important;
    }
</style>
</body>
</html>