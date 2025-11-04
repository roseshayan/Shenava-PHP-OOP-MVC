<?php
/**
 * Shenava - Advanced Reports Dashboard
 */
session_save_path('/tmp');
session_start();
require_once '../../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';

$db = new Database();

// Get date range
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of the current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today

try {
    // Total stats
    $db->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $db->single()->total;
} catch (Exception $e) {
    echo $e->getMessage();
}

try {
    $db->query("SELECT COUNT(*) as total FROM books WHERE is_active = 1");
    $totalBooks = $db->single()->total;
} catch (Exception $e) {
    echo $e->getMessage();
}

try {
    $db->query("SELECT COUNT(*) as total FROM chapters");
    $totalChapters = $db->single()->total;
} catch (Exception $e) {
    echo $e->getMessage();
}

try {
    $db->query("SELECT SUM(plays_count) as total FROM chapters");
    $totalPlays = $db->single()->total;
} catch (Exception $e) {
    echo $e->getMessage();
}

try {
    // Recent stats (last 30 days)
    $db->query("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $recentUsers = $db->single()->count;
} catch (Exception $e) {
    echo $e->getMessage();
}

try {
    $db->query("SELECT COUNT(*) as count FROM books WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $recentBooks = $db->single()->count;
} catch (Exception $e) {
    echo $e->getMessage();
}

try {
    $db->query("SELECT COUNT(*) as count FROM listening_history WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $recentPlays = $db->single()->count;
} catch (Exception $e) {
    echo $e->getMessage();
}

try {
    // Top books
    $db->query("SELECT b.title, b.total_views, a.name as author_name, COUNT(lh.id) as play_count
           FROM books b
           LEFT JOIN authors a ON b.author_id = a.id
           LEFT JOIN listening_history lh ON b.id = lh.book_id
           WHERE b.is_active = 1
           GROUP BY b.id
           ORDER BY play_count DESC
           LIMIT 10");
    $topBooks = $db->resultSet();
} catch (Exception $e) {
    echo $e->getMessage();
}

try {
    // User growth data (last 7 days)
    $db->query("SELECT DATE(created_at) as date, COUNT(*) as count 
           FROM users 
           WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
           GROUP BY DATE(created_at)
           ORDER BY date");
    $userGrowth = $db->resultSet();
} catch (Exception $e) {
    echo $e->getMessage();
}

$pageTitle = "گزارشات و آمار - شنوا";
?>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">گزارش‌ها و آمار پیشرفته</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>
                            چاپ گزارش
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" id="exportReport">
                            <i class="fas fa-file-export me-2"></i>
                            خروجی Excel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Date Range Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">از تاریخ</label>
                            <input type="date"
                                   class="form-control"
                                   id="start_date"
                                   name="start_date"
                                   value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">تا تاریخ</label>
                            <input type="date"
                                   class="form-control"
                                   id="end_date"
                                   name="end_date"
                                   value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i>
                                اعمال فیلتر
                            </button>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <a href="dashboard.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-2"></i>
                                حذف فیلتر
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="row g-4 mb-5">
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0"><?php echo isset($totalUsers) ? number_format($totalUsers) : 0; ?></h3>
                                    <small>کل کاربران</small>
                                </div>
                                <i class="fas fa-users fa-2x opacity-50"></i>
                            </div>
                            <div class="mt-2">
                                <small>
                                    <i class="fas fa-arrow-up me-1"></i>
                                    <?php echo number_format($recentUsers); ?> در ۳۰ روز گذشته
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0"><?php echo number_format($totalBooks); ?></h3>
                                    <small>کل کتاب‌ها</small>
                                </div>
                                <i class="fas fa-book fa-2x opacity-50"></i>
                            </div>
                            <div class="mt-2">
                                <small>
                                    <i class="fas fa-arrow-up me-1"></i>
                                    <?php echo number_format($recentBooks); ?> در ۳۰ روز گذشته
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card stat-card bg-warning text-dark">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0"><?php echo isset($totalPlays) ? number_format($totalPlays) : 0; ?></h3>
                                    <small>کل پخش‌ها</small>
                                </div>
                                <i class="fas fa-play-circle fa-2x opacity-50"></i>
                            </div>
                            <div class="mt-2">
                                <small>
                                    <i class="fas fa-arrow-up me-1"></i>
                                    <?php echo number_format($recentPlays); ?> در ۳۰ روز گذشته
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0"><?php echo number_format($totalChapters); ?></h3>
                                    <small>کل فصل‌ها</small>
                                </div>
                                <i class="fas fa-list fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card stat-card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0">
                                        <?php
                                        try {
                                            $avgRating = 0;
                                            $db->query("SELECT AVG(rating) as avg_rating FROM reviews WHERE is_approved = 1");
                                            $result = $db->single();
                                            if ($result && $result->avg_rating) {
                                                $avgRating = number_format($result->avg_rating, 1);
                                            }
                                            echo $avgRating;
                                        } catch (Exception $e) {
                                            echo $e->getMessage();
                                        }
                                        ?>
                                    </h3>
                                    <small>میانگین امتیاز</small>
                                </div>
                                <i class="fas fa-star fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-4 mb-5">
                <!-- User Growth Chart -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-line me-2 text-primary"></i>
                                رشد کاربران (۷ روز گذشته)
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="userGrowthChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Books -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-trophy me-2 text-warning"></i>
                                پربازدیدترین کتاب‌ها
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <?php foreach ($topBooks as $index => $book): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-primary me-2"><?php echo $index + 1; ?></span>
                                            <div>
                                                <h6 class="mb-0"><?php echo $book->title; ?></h6>
                                                <small class="text-muted"><?php echo $book->author_name; ?></small>
                                            </div>
                                        </div>
                                        <span class="badge bg-success"><?php echo $book->play_count; ?> پخش</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Reports -->
            <div class="row g-4">
                <!-- Recent Activity -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-history me-2 text-info"></i>
                                فعالیت‌های اخیر
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php
                            try {
                                $db->query("SELECT lh.*, u.username, b.title as book_title, c.title as chapter_title
                                           FROM listening_history lh
                                           JOIN users u ON lh.user_id = u.id
                                           JOIN books b ON lh.book_id = b.id
                                           JOIN chapters c ON lh.chapter_id = c.id
                                           ORDER BY lh.last_listened_at DESC
                                           LIMIT 10");
                                $recentActivity = $db->resultSet();
                            } catch (Exception $e) {
                                echo $e->getMessage();
                            }
                            ?>

                            <div class="list-group list-group-flush">
                                <?php foreach ($recentActivity as $activity): ?>
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo $activity->username; ?></h6>
                                                <p class="mb-1 text-muted">
                                                    <?php echo $activity->book_title; ?>
                                                    - <?php echo $activity->chapter_title; ?>
                                                </p>
                                                <small class="text-muted">
                                                    <?php
                                                    $progress = $activity->progress_seconds;
                                                    $hours = floor($progress / 3600);
                                                    $minutes = floor(($progress % 3600) / 60);
                                                    $seconds = $progress % 60;
                                                    echo sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                                                    ?>
                                                </small>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo date('H:i', strtotime($activity->last_listened_at)); ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Health -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-heartbeat me-2 text-danger"></i>
                                سلامت سیستم
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                    <span>وضعیت سرور</span>
                                    <span class="badge bg-success">آنلاین</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                    <span>پایگاه داده</span>
                                    <span class="badge bg-success">متصل</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                    <span>فضای ذخیره‌سازی</span>
                                    <span class="badge bg-warning">75% پر</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                    <span>حافظه سرور</span>
                                    <span class="badge bg-success">45% استفاده</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                    <span>آخرین پشتیبان</span>
                                    <span class="badge bg-info">2 ساعت پیش</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
    $(document).ready(function () {
        // بررسی وجود المنت نمودار قبل از ایجاد
        const userGrowthCanvas = document.getElementById('userGrowthChart');
        if (userGrowthCanvas) {
            const userGrowthCtx = userGrowthCanvas.getContext('2d');
            const userGrowthChart = new Chart(userGrowthCtx, {
                type: 'bar',
                data: {
                    labels: [<?php
                        $labels = [];
                        foreach ($userGrowth as $growth) {
                            $labels[] = "'" . date('m/d', strtotime($growth->date)) . "'";
                        }
                        echo implode(', ', $labels);
                        ?>],
                    datasets: [{
                        label: 'کاربران جدید',
                        data: [<?php
                            $data = [];
                            foreach ($userGrowth as $growth) {
                                $data[] = $growth->count;
                            }
                            echo implode(', ', $data);
                            ?>],
                        backgroundColor: '#00BFA5',
                        borderColor: '#00897B',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Export report
        $('#exportReport').on('click', function () {
            ShenavaAdmin.showToast('در حال تولید گزارش Excel...', 'info');
            // Implement export functionality
        });
    });
</script>

<style>
    .stat-card {
        border: none;
        border-radius: 12px;
        transition: transform 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }
</style>
