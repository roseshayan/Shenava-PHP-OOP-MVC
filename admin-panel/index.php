<?php
/**
 * Shenava - Admin Panel
 * Main Dashboard
 */

session_start();
require_once '../backend/app/core/Database.php';
require_once '../backend/app/models/UserModel.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get stats for dashboard
$db = new Database();
$userModel = new UserModel();
$bookModel = new BookModel();

// Get total counts
$db->query("SELECT COUNT(*) as total FROM users");
try {
    $totalUsers = $db->single()->total;
} catch (Exception $e) {

}

$db->query("SELECT COUNT(*) as total FROM books WHERE is_active = 1");
try {
    $totalBooks = $db->single()->total;
} catch (Exception $e) {

}

$db->query("SELECT COUNT(*) as total FROM categories WHERE is_active = 1");
try {
    $totalCategories = $db->single()->total;
} catch (Exception $e) {

}

$db->query("SELECT COUNT(*) as total FROM listening_history WHERE DATE(created_at) = CURDATE()");
try {
    $todayPlays = $db->single()->total;
} catch (Exception $e) {

}

// Get recent books
$db->query("SELECT b.*, a.name as author_name, c.name as category_name 
           FROM books b 
           LEFT JOIN authors a ON b.author_id = a.id 
           LEFT JOIN categories c ON b.category_id = c.id 
           ORDER BY b.created_at DESC 
           LIMIT 5");
try {
    $recentBooks = $db->resultSet();
} catch (Exception $e) {

}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت شنوا</title>

    <!-- Bootstrap 5 CSS -->
    <link href="../node_modules/bootstrap/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../node_modules/@fortawesome/fontawesome-free/css/all.min.css">

    <!-- Vazir Font -->
    <link href="../node_modules/vazirmatn/misc/Farsi-Digits/Vazirmatn-FD-font-face.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/dashboard.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #00BFA5;
            --accent-color: #FF7043;
            --bg-light: #E3F2FD;
            --text-primary: #212121;
            --text-secondary: #757575;
        }

        body {
            font-family: Vazir, sans-serif;
            background-color: #f8f9fa;
        }

        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #00897B 100%);
            color: white;
            min-height: 100vh;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 4px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link i {
            margin-left: 10px;
        }

        .stat-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .card-body i {
            font-size: 2.5rem;
            opacity: 0.8;
        }

        .bg-primary-custom {
            background-color: var(--primary-color) !important;
        }

        .bg-accent-custom {
            background-color: var(--accent-color) !important;
        }

        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary-custom:hover {
            background-color: #00897B;
            border-color: #00897B;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <i class="fas fa-headphones"></i>
            شنوا - پنل مدیریت
        </a>

        <div class="d-flex align-items-center">
            <span class="text-white me-3"><?php echo $_SESSION['admin_name'] ?? 'مدیر'; ?></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt"></i>
                خروج
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar p-0">
            <div class="p-4">
                <h5 class="text-center mb-4">منوها</h5>

                <nav class="nav flex-column">
                    <a class="nav-link active" href="index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        داشبورد
                    </a>
                    <a class="nav-link" href="pages/books/list.php">
                        <i class="fas fa-book"></i>
                        مدیریت کتاب‌ها
                    </a>
                    <a class="nav-link" href="pages/categories/list.php">
                        <i class="fas fa-folder"></i>
                        دسته‌بندی‌ها
                    </a>
                    <a class="nav-link" href="pages/users/list.php">
                        <i class="fas fa-users"></i>
                        کاربران
                    </a>
                    <a class="nav-link" href="pages/authors/list.php">
                        <i class="fas fa-user-edit"></i>
                        نویسندگان
                    </a>
                    <a class="nav-link" href="pages/chapters/list.php">
                        <i class="fas fa-list"></i>
                        فصل‌ها
                    </a>
                    <a class="nav-link" href="pages/reviews/list.php">
                        <i class="fas fa-star"></i>
                        نظرات
                    </a>
                    <a class="nav-link" href="pages/settings/general.php">
                        <i class="fas fa-cog"></i>
                        تنظیمات
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">داشبورد</h1>
                <div class="btn-group">
                    <button class="btn btn-primary-custom">
                        <i class="fas fa-sync-alt"></i>
                        بروزرسانی
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-5">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h5 class="card-title text-muted mb-2">کل کاربران</h5>
                                    <h3 class="mb-0"><?php echo number_format($totalUsers); ?></h3>
                                </div>
                                <div class="col-4 text-end">
                                    <i class="fas fa-users text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h5 class="card-title text-muted mb-2">کل کتاب‌ها</h5>
                                    <h3 class="mb-0"><?php echo number_format($totalBooks); ?></h3>
                                </div>
                                <div class="col-4 text-end">
                                    <i class="fas fa-book text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h5 class="card-title text-muted mb-2">دسته‌بندی‌ها</h5>
                                    <h3 class="mb-0"><?php echo number_format($totalCategories); ?></h3>
                                </div>
                                <div class="col-4 text-end">
                                    <i class="fas fa-folder text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h5 class="card-title text-muted mb-2">پخش امروز</h5>
                                    <h3 class="mb-0"><?php echo number_format($todayPlays); ?></h3>
                                </div>
                                <div class="col-4 text-end">
                                    <i class="fas fa-play-circle text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Books & Quick Actions -->
            <div class="row g-4">
                <!-- Recent Books -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-clock text-primary me-2"></i>
                                کتاب‌های اخیر
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>عنوان</th>
                                        <th>نویسنده</th>
                                        <th>دسته‌بندی</th>
                                        <th>تاریخ</th>
                                        <th>عملیات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($recentBooks as $book): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo $book->cover_image ?: '../assets/images/default-book.jpg'; ?>"
                                                         alt="<?php echo $book->title; ?>"
                                                         class="rounded me-3"
                                                         width="40" height="40">
                                                    <div>
                                                        <h6 class="mb-0"><?php echo $book->title; ?></h6>
                                                        <small class="text-muted"><?php echo $book->is_featured ? 'ویژه' : 'عادی'; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo $book->author_name ?: '---'; ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $book->category_name; ?></span>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo date('Y/m/d', strtotime($book->created_at)); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" title="مشاهده">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning" title="ویرایش">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bolt text-warning me-2"></i>
                                اقدامات سریع
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="pages/books/add.php" class="btn btn-primary-custom btn-lg">
                                    <i class="fas fa-plus me-2"></i>
                                    افزودن کتاب جدید
                                </a>
                                <a href="pages/categories/add.php" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-folder-plus me-2"></i>
                                    افزودن دسته‌بندی
                                </a>
                                <a href="pages/authors/add.php" class="btn btn-outline-success btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>
                                    افزودن نویسنده
                                </a>
                                <a href="pages/chapters/add.php" class="btn btn-outline-info btn-lg">
                                    <i class="fas fa-file-audio me-2"></i>
                                    افزودن فصل
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- System Status -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-server text-info me-2"></i>
                                وضعیت سیستم
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                    <span>وضعیت سرور</span>
                                    <span class="badge bg-success">فعال</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                    <span>پایگاه داده</span>
                                    <span class="badge bg-success">متصل</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                    <span>فضای ذخیره‌سازی</span>
                                    <span class="badge bg-warning">75%</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                    <span>ورژن سیستم</span>
                                    <span class="badge bg-primary">1.0.0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="../node_modules/jquery/dist/jquery.min.js"></script>
<script src="js/app.js"></script>
<script src="js/dashboard.js"></script>
</body>
</html>