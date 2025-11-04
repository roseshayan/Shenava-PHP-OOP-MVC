<?php
/**
 * Shenava - Admin Panel
 * Main Dashboard
 */

session_start();

// Define base paths
define('BASE_PATH', dirname(__DIR__));
const BACKEND_PATH = BASE_PATH . '/backend';

// Require core files
require_once BACKEND_PATH . '/app/core/Database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Initialize variables
$totalUsers = $totalBooks = $totalCategories = $todayPlays = 0;
$recentBooks = [];

try {
    // Get stats for dashboard
    $db = new Database();

    // Get total counts with error handling for each query
    try {
        $db->query("SELECT COUNT(*) as total FROM users");
        $result = $db->single();
        $totalUsers = $result->total ?? 0;
    } catch (Exception $e) {
        error_log("Users count error: " . $e->getMessage());
        $totalUsers = 0;
    }

    try {
        $db->query("SELECT COUNT(*) as total FROM books WHERE is_active = 1");
        $result = $db->single();
        $totalBooks = $result->total ?? 0;
    } catch (Exception $e) {
        error_log("Books count error: " . $e->getMessage());
        $totalBooks = 0;
    }

    try {
        $db->query("SELECT COUNT(*) as total FROM categories WHERE is_active = 1");
        $result = $db->single();
        $totalCategories = $result->total ?? 0;
    } catch (Exception $e) {
        error_log("Categories count error: " . $e->getMessage());
        $totalCategories = 0;
    }

    try {
        $db->query("SELECT COUNT(*) as total FROM listening_history WHERE DATE(created_at) = CURDATE()");
        $result = $db->single();
        $todayPlays = $result->total ?? 0;
    } catch (Exception $e) {
        error_log("Today plays error: " . $e->getMessage());
        $todayPlays = 0;
    }

    try {
        // Get recent books
        $db->query("SELECT b.*, a.name as author_name, c.name as category_name 
               FROM books b 
               LEFT JOIN authors a ON b.author_id = a.id 
               LEFT JOIN categories c ON b.category_id = c.id 
               ORDER BY b.created_at DESC 
               LIMIT 5");
        $recentBooks = $db->resultSet();
    } catch (Exception $e) {
        error_log("Recent books error: " . $e->getMessage());
        $recentBooks = [];
    }

} catch (Exception $e) {
    // Log the main database connection error
    error_log("Database connection failed: " . $e->getMessage());
    $dbError = "خطا در اتصال به پایگاه داده. لطفا تنظیمات را بررسی کنید.";
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت شنوا</title>

    <!-- Meta Description -->
    <meta name="description" content="شنوا، کتابخانه‌ای آنلاین از کتاب‌های صوتی رایگان است. در شنوا می‌توانید انواع داستان‌های شنیدنی، رمان‌ها، ادبیات کلاسیک و آثار فانتزی را به‌صورت رایگان گوش دهید. هرجا و هرزمان با شنوا همراه باشید.">

    <!-- Meta Keywords -->
    <meta name="keywords" content="شنوا, کتاب صوتی, رایگان, اپلیکیشن کتاب صوتی, داستان صوتی, رمان صوتی, شنیدن کتاب, Shenava, Free Audiobooks">

    <!-- Open Graph (برای اشتراک‌گذاری در شبکه‌های اجتماعی) -->
    <meta property="og:title" content="شنوا | کتاب‌های صوتی رایگان و داستانی">
    <meta property="og:description" content="با شنوا، کتاب‌ها را بشنوید. مجموعه‌ای از داستان‌ها و رمان‌های شنیدنی، همه رایگان و همیشه در دسترس.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://shennava.ir">
    <meta property="og:image" content="https://shennava.ir/admin-panel/img/logo.png">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="شنوا | کتاب‌های صوتی رایگان و داستانی">
    <meta name="twitter:description" content="کتاب‌ها رو بشنو، دنیایی تازه بساز.">
    <meta name="twitter:image" content="https://shennava.ir/admin-panel/img/logo.png">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="img/favicon/favicon.svg" />
    <link rel="shortcut icon" href="img/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="img/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Shennava" />
    <link rel="manifest" href="img/favicon/site.webmanifest" />

    <!-- Bootstrap 5 CSS -->
    <link href="../node_modules/bootstrap/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../node_modules/@fortawesome/fontawesome-free/css/all.min.css">

    <!-- Vazir Font -->
    <link href="../node_modules/vazirmatn/misc/Farsi-Digits/Vazirmatn-FD-font-face.css" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
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
                <div class="text-center mb-4">
                    <i class="fas fa-headphones fa-2x text-white mb-2"></i>
                    <h6 class="text-white mb-0">شنوا</h6>
                    <small class="text-white-50">پنل مدیریت</small>
                </div>

                <nav class="nav flex-column">
                    <a class="nav-link active" href="index.php">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        داشبورد
                    </a>

                    <a class="nav-link" href="pages/books/list.php">
                        <i class="fas fa-book me-2"></i>
                        مدیریت کتاب‌ها
                    </a>

                    <a class="nav-link" href="pages/categories/list.php">
                        <i class="fas fa-folder me-2"></i>
                        دسته‌بندی‌ها
                    </a>

                    <a class="nav-link" href="pages/users/list.php">
                        <i class="fas fa-users me-2"></i>
                        کاربران
                    </a>

                    <a class="nav-link" href="pages/authors/list.php">
                        <i class="fas fa-user-edit me-2"></i>
                        نویسندگان
                    </a>

                    <a class="nav-link" href="pages/chapters/list.php">
                        <i class="fas fa-list me-2"></i>
                        فصل‌ها
                    </a>

                    <a class="nav-link" href="pages/reviews/list.php">
                        <i class="fas fa-star me-2"></i>
                        نظرات
                    </a>

                    <div class="sidebar-divider my-3"></div>

                    <a class="nav-link" href="pages/settings/general.php">
                        <i class="fas fa-cog me-2"></i>
                        تنظیمات
                    </a>

                    <a class="nav-link" href="pages/reports/dashboard.php">
                        <i class="fas fa-chart-bar me-2"></i>
                        گزارشات
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
            <!-- Database Error -->
            <?php if (isset($dbError)): ?>
                <div class="alert alert-danger">
                    <h4 class="alert-heading">خطا در اتصال به پایگاه داده</h4>
                    <p><?php echo $dbError; ?></p>
                    <hr>
                    <p class="mb-0">لطفا از موارد زیر اطمینان حاصل کنید:</p>
                    <ul>
                        <li>سرور MySQL در حال اجرا است</li>
                        <li>پایگاه داده 'shenava_db' وجود دارد</li>
                        <li>نام کاربری و رمز عبور صحیح هستند</li>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">داشبورد</h1>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-5">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h5 class="card-title text-white mb-2">کل کاربران</h5>
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
                                    <h5 class="card-title text-white mb-2">کل کتاب‌ها</h5>
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
                                    <h5 class="card-title text-white mb-2">دسته‌بندی‌ها</h5>
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
                                    <h5 class="card-title text-white mb-2">پخش امروز</h5>
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

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">اقدامات سریع</h5>
                            <div class="d-grid gap-2 d-md-flex">
                                <a href="pages/books/add.php" class="btn btn-primary-custom me-2">
                                    <i class="fas fa-plus me-2"></i>
                                    افزودن کتاب جدید
                                </a>
                                <a href="pages/categories/add.php" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-folder-plus me-2"></i>
                                    افزودن دسته‌بندی
                                </a>
                                <a href="pages/authors/add.php" class="btn btn-outline-success">
                                    <i class="fas fa-user-plus me-2"></i>
                                    افزودن نویسنده
                                </a>
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
</body>
</html>