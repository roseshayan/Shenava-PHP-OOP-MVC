<?php
/**
 * Shenava - Get User Profile (AJAX)
 */
session_save_path('/tmp');
session_start();
require_once '../../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';

header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="alert alert-danger">شناسه کاربر نامعتبر است</div>';
    exit;
}

$userId = intval($_GET['id']);
$db = new Database();

try {
    // Get user data with additional stats
    $db->query("
        SELECT u.*, 
               COUNT(DISTINCT uf.id) as favorites_count,
               COUNT(DISTINCT lh.id) as listening_history_count,
               COUNT(DISTINCT r.id) as reviews_count
        FROM users u
        LEFT JOIN user_favorites uf ON u.id = uf.user_id
        LEFT JOIN listening_history lh ON u.id = lh.user_id
        LEFT JOIN reviews r ON u.id = r.user_id
        WHERE u.id = :id
        GROUP BY u.id
    ");
    $db->bind(':id', $userId);
    $user = $db->single();

    if (!$user) {
        echo '<div class="alert alert-danger">کاربر یافت نشد</div>';
        exit;
    }

    ?>
    <div class="row">
        <div class="col-md-4 text-center">
            <?php if ($user->avatar_url): ?>
                <img src="<?php echo $user->avatar_url; ?>"
                     alt="<?php echo htmlspecialchars($user->username); ?>"
                     class="rounded-circle mb-3"
                     width="120" height="120" style="object-fit: cover;">
            <?php else: ?>
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3"
                     style="width: 120px; height: 120px;">
                    <i class="fas fa-user text-muted fa-3x"></i>
                </div>
            <?php endif; ?>

            <h4><?php echo htmlspecialchars($user->display_name ?: $user->username); ?></h4>
            <p class="text-muted">@<?php echo htmlspecialchars($user->username); ?></p>

            <div class="d-flex justify-content-center gap-2 mb-3">
                <span class="badge bg-<?php echo $user->is_active ? 'success' : 'secondary'; ?>">
                    <?php echo $user->is_active ? 'فعال' : 'غیرفعال'; ?>
                </span>
                <span class="badge bg-<?php echo $user->is_premium ? 'warning' : 'info'; ?>">
                    <?php echo $user->is_premium ? 'ویژه' : 'عادی'; ?>
                </span>
            </div>
        </div>

        <div class="col-md-8">
            <div class="row g-3 mb-4">
                <div class="col-4">
                    <div class="text-center p-3 border rounded">
                        <h5 class="text-primary mb-1"><?php echo $user->favorites_count; ?></h5>
                        <small class="text-muted">علاقه‌مندی‌ها</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="text-center p-3 border rounded">
                        <h5 class="text-success mb-1"><?php echo $user->listening_history_count; ?></h5>
                        <small class="text-muted">تاریخچه پخش</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="text-center p-3 border rounded">
                        <h5 class="text-warning mb-1"><?php echo $user->reviews_count; ?></h5>
                        <small class="text-muted">نظرات</small>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-6">
                    <strong>ایمیل:</strong><br>
                    <span class="text-muted"><?php echo htmlspecialchars($user->email); ?></span>
                </div>
                <div class="col-6">
                    <strong>نام نمایشی:</strong><br>
                    <span class="text-muted"><?php echo htmlspecialchars($user->display_name ?: '---'); ?></span>
                </div>
                <div class="col-6">
                    <strong>تاریخ ثبت‌نام:</strong><br>
                    <span class="text-muted"><?php echo date('Y/m/d H:i', strtotime($user->created_at)); ?></span>
                </div>
                <div class="col-6">
                    <strong>آخرین به‌روزرسانی:</strong><br>
                    <span class="text-muted"><?php echo date('Y/m/d H:i', strtotime($user->updated_at)); ?></span>
                </div>
                <div class="col-12">
                    <strong>UUID:</strong><br>
                    <code class="text-muted"><?php echo htmlspecialchars($user->uuid); ?></code>
                </div>
            </div>

            <?php if ($user->dark_mode || $user->sleep_timer_enabled || $user->driving_mode): ?>
                <div class="mt-3">
                    <strong>تنظیمات:</strong>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <?php if ($user->dark_mode): ?>
                            <span class="badge bg-dark">
                            <i class="fas fa-moon me-1"></i>حالت تاریک
                        </span>
                        <?php endif; ?>
                        <?php if ($user->sleep_timer_enabled): ?>
                            <span class="badge bg-info">
                            <i class="fas fa-bed me-1"></i>تایمر خواب
                        </span>
                        <?php endif; ?>
                        <?php if ($user->driving_mode): ?>
                            <span class="badge bg-primary">
                            <i class="fas fa-car me-1"></i>حالت رانندگی
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php

} catch (Exception $e) {
    echo '<div class="alert alert-danger">خطا در بارگذاری اطلاعات: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>