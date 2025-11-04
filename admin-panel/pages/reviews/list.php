<?php
/**
 * Shenava - Reviews Management
 */

session_start();
require_once '../../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';

$db = new Database();

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);

    switch ($action) {
        case 'approve':
            try {
                $db->query("UPDATE reviews SET is_approved = 1 WHERE id = :id");
                $db->bind(':id', $id);
                $db->execute();
                $_SESSION['success'] = 'نظر با موفقیت تایید شد';
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            break;

        case 'reject':
            try {
                $db->query("UPDATE reviews SET is_approved = 0 WHERE id = :id");
                $db->bind(':id', $id);
                $db->execute();
                $_SESSION['success'] = 'نظر با موفقیت رد شد';
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            break;

        case 'delete':
            try {
                $db->query("DELETE FROM reviews WHERE id = :id");
                $db->bind(':id', $id);
                $db->execute();
                $_SESSION['success'] = 'نظر با موفقیت حذف شد';
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            break;
    }

    header('Location: list.php');
    exit;
}

// Get filters
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where = "WHERE 1=1";
$params = [];

if ($status === 'approved') {
    $where .= " AND r.is_approved = 1";
} elseif ($status === 'pending') {
    $where .= " AND r.is_approved = 0";
}

if ($search) {
    $where .= " AND (u.username LIKE :search OR b.title LIKE :search OR r.comment LIKE :search)";
    $params[':search'] = "%$search%";
}

try {
    // Get reviews
    $sql = "SELECT r.*, u.username, u.display_name, b.title as book_title, b.uuid as book_uuid
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        JOIN books b ON r.book_id = b.id
        $where
        ORDER BY r.created_at DESC
        LIMIT 50";

    $db->query($sql);
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }
    $reviews = $db->resultSet();
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت نظرات - شنوا</title>

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
                <h1 class="h2">مدیریت نظرات</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="?status=pending" class="btn btn-sm btn-outline-warning">
                            نظرات در انتظار تایید
                        </a>
                    </div>
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

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <select aria-label="status" name="status" class="form-select">
                                <option value="">همه وضعیت‌ها</option>
                                <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>تایید
                                    شده
                                </option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>در انتظار
                                    تایید
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input aria-label="search" type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="جستجو در کاربر، کتاب یا متن نظر..."
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>
                                فیلتر
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="list.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-2"></i>
                                پاک کردن
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reviews List -->
            <div class="row g-4">
                <?php foreach ($reviews as $review): ?>
                    <div class="col-12">
                        <div class="card review-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                                 style="width: 50px; height: 50px;">
                                                <i class="fas fa-user text-muted"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?php echo $review->display_name ?: $review->username; ?></h6>
                                            <small class="text-muted"><?php echo $review->username; ?></small>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <div class="rating mb-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $review->rating ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('Y/m/d H:i', strtotime($review->created_at)); ?>
                                        </small>
                                    </div>
                                </div>

                                <h6 class="text-primary mb-2">
                                    <i class="fas fa-book me-2"></i>
                                    <?php echo $review->book_title; ?>
                                </h6>

                                <?php if ($review->title): ?>
                                    <h6 class="mb-2"><?php echo $review->title; ?></h6>
                                <?php endif; ?>

                                <p class="card-text"><?php echo nl2br(htmlspecialchars($review->comment)); ?></p>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="badge bg-<?php echo $review->is_approved ? 'success' : 'warning'; ?>">
                                        <?php echo $review->is_approved ? 'تایید شده' : 'در انتظار تایید'; ?>
                                    </span>

                                    <div class="btn-group btn-group-sm">
                                        <?php if (!$review->is_approved): ?>
                                            <a href="?action=approve&id=<?php echo $review->id; ?>"
                                               class="btn btn-outline-success"
                                               title="تایید نظر">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="?action=reject&id=<?php echo $review->id; ?>"
                                               class="btn btn-outline-warning"
                                               title="رد نظر">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php endif; ?>

                                        <a href="?action=delete&id=<?php echo $review->id; ?>"
                                           class="btn btn-outline-danger btn-delete"
                                           title="حذف نظر"
                                           data-confirm="آیا از حذف این نظر مطمئن هستید؟">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Empty State -->
            <?php if (empty($reviews)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">هیچ نظری یافت نشد</h4>
                    <p class="text-muted">هنوز هیچ نظری ثبت نشده است</p>
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
    .review-card {
        transition: box-shadow 0.2s ease;
        border: none;
        border-radius: 12px;
    }

    .review-card:hover {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .rating {
        font-size: 0.9rem;
    }
</style>
</body>
</html>