<?php
/**
 * Shenava - Categories Management
 */
session_save_path('/tmp');
session_start();
require_once '../../includes/auth-check.php';
// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';
require_once BACKEND_PATH . '/app/core/Model.php';
require_once BACKEND_PATH . '/app/models/CategoryModel.php';

$categoryModel = new CategoryModel();
$categories = $categoryModel->getCategories();

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);

    switch ($action) {
        case 'toggle':
            try {
                // Toggle category status
                $db = new Database();
                $db->query("UPDATE categories SET is_active = NOT is_active WHERE id = :id");
                $db->bind(':id', $id);
                $db->execute();
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            break;

        case 'delete':
            try {
                // Check if category has books
                $db = new Database();
                $db->query("SELECT COUNT(*) as book_count FROM books WHERE category_id = :id");
                $db->bind(':id', $id);
                $result = $db->single();
            } catch (Exception $e) {
                echo $e->getMessage();
            }

            if ($result->book_count > 0) {
                $_SESSION['error'] = 'این دسته‌بندی دارای کتاب است و نمی‌توان حذف کرد';
            } else {
                try {
                    $db->query("DELETE FROM categories WHERE id = :id");
                    $db->bind(':id', $id);
                    $db->execute();
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                $_SESSION['success'] = 'دسته‌بندی با موفقیت حذف شد';
            }
            break;
    }

    header('Location: list.php');
    exit;
}

$pageTitle = "مدیریت دسته‌بندی‌ها - شنوا";
?>
<?php include '../../includes/header.php'; ?>
<style>
    .category-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: none;
        border-radius: 12px;
    }

    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .category-color {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: inline-block;
        margin-left: 8px;
    }

    .actions-dropdown {
        position: absolute;
        left: 15px;
        top: 15px;
    }

    .category-description {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
        max-height: 4.2em;
    }

</style>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">مدیریت دسته‌بندی‌ها</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        افزودن دسته‌بندی جدید
                    </a>
                </div>
            </div>

            <!-- Notifications -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Categories Grid -->
            <div class="row g-4">
                <?php foreach ($categories as $category): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card category-card h-100">
                            <div class="card-body position-relative">
                                <!-- Actions Dropdown -->
                                <div class="actions-dropdown">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                type="button"
                                                data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item"
                                                   href="edit.php?id=<?php echo $category->id; ?>">
                                                    <i class="fas fa-edit me-2"></i>
                                                    ویرایش
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item"
                                                   href="?action=toggle&id=<?php echo $category->id; ?>">
                                                    <i class="fas fa-eye-slash me-2"></i>
                                                    غیرفعال کردن
                                                </a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger btn-delete"
                                                   href="?action=delete&id=<?php echo $category->id; ?>"
                                                   data-confirm="آیا از حذف این دسته‌بندی مطمئن هستید؟">
                                                    <i class="fas fa-trash me-2"></i>
                                                    حذف
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Category Content -->
                                <div class="text-center">
                                    <?php if ($category->cover_image): ?>
                                        <img src="<?php echo $category->cover_image; ?>"
                                             alt="<?php echo $category->name; ?>"
                                             class="rounded-circle mb-3"
                                             width="80" height="80" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3"
                                             style="width: 80px; height: 80px;">
                                            <i class="fas fa-folder text-muted fa-2x"></i>
                                        </div>
                                    <?php endif; ?>

                                    <h5 class="card-title"><?php echo $category->name; ?></h5>

                                    <p class="card-text text-muted small category-description">
                                        <?php echo $category->description ?: 'بدون توضیحات'; ?>
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center text-muted small">
                                        <span>
                                            <i class="fas fa-book me-1"></i>
                                            <?php echo $category->book_count; ?> کتاب
                                        </span>
                                        <span>
                                            <?php if ($category->parent_name): ?>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-level-up-alt me-1"></i>
                                                    <?php echo $category->parent_name; ?>
                                                </span>
                                            <?php endif; ?>
                                            <span class="category-color"
                                                  style="background-color: <?php echo $category->color; ?>"></span>
                                            رنگ
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-transparent">
                                <small class="text-muted">
                                    <i class="fas fa-sort me-1"></i>
                                    ترتیب: <?php echo $category->sort_order; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Add New Category Card -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card category-card h-100 border-dashed">
                        <a href="add.php"
                           class="card-body d-flex align-items-center justify-content-center text-decoration-none text-muted">
                            <div class="text-center">
                                <i class="fas fa-plus-circle fa-3x mb-3"></i>
                                <h5>افزودن دسته‌بندی جدید</h5>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <?php if (empty($categories)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">هیچ دسته‌بندی‌ای یافت نشد</h4>
                    <p class="text-muted">اولین دسته‌بندی را ایجاد کنید</p>
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        افزودن دسته‌بندی
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<style>
    .border-dashed {
        border: 2px dashed #dee2e6 !important;
    }

    .border-dashed:hover {
        border-color: var(--primary-color) !important;
        color: var(--primary-color) !important;
    }
</style>