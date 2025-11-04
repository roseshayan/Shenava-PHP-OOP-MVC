<?php
/**
 * Shenava - API Settings
 */
session_save_path('/tmp');
session_start();
require_once '../../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';

$db = new Database();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'api_rate_limit' => intval($_POST['api_rate_limit'] ?? 1000),
        'api_cors_enabled' => isset($_POST['api_cors_enabled']) ? 1 : 0,
        'api_debug_mode' => isset($_POST['api_debug_mode']) ? 1 : 0,
        'jwt_secret' => trim($_POST['jwt_secret'] ?? ''),
        'jwt_expire' => intval($_POST['jwt_expire'] ?? 24),
    ];

    foreach ($settings as $key => $value) {
        try {
            // Check if setting exists
            $db->query("SELECT id FROM app_settings WHERE setting_key = :key");
            $db->bind(':key', $key);
            $existing = $db->single();
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        try {
            if ($existing) {
                $db->query("UPDATE app_settings SET setting_value = :value WHERE setting_key = :key");
            } else {
                $db->query("INSERT INTO app_settings (setting_key, setting_value, setting_type) VALUES (:key, :value, 'string')");
            }

            $db->bind(':key', $key);
            $db->bind(':value', $value);
            $db->execute();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    $_SESSION['success'] = 'تنظیمات API با موفقیت ذخیره شد';
    header('Location: api.php');
    exit;
}

try {
    // Get current settings
    $db->query("SELECT setting_key, setting_value FROM app_settings WHERE setting_key LIKE 'api_%' OR setting_key LIKE 'jwt_%'");
    $settingsResult = $db->resultSet();
} catch (Exception $e) {
    echo $e->getMessage();
}

$settings = [];
foreach ($settingsResult as $setting) {
    $settings[$setting->setting_key] = $setting->setting_value;
}

$pageTitle = "تنظیمات API";
?>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">تنظیمات API</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="submit" form="apiSettingsForm" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        ذخیره تنظیمات
                    </button>
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

            <div class="row">
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <a href="general.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-cog me-2"></i>
                                    تنظیمات عمومی
                                </a>
                                <a href="appearance.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-palette me-2"></i>
                                    ظاهر و تم
                                </a>
                                <a href="api.php" class="list-group-item list-group-item-action active">
                                    <i class="fas fa-code me-2"></i>
                                    تنظیمات API
                                </a>
                                <a href="storage.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-database me-2"></i>
                                    ذخیره‌سازی
                                </a>
                                <a href="backup.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    پشتیبان‌گیری
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" id="apiSettingsForm">
                                <!-- API Security -->
                                <div class="mb-5">
                                    <h5 class="card-title mb-3 border-bottom pb-2">
                                        <i class="fas fa-shield-alt me-2 text-primary"></i>
                                        امنیت API
                                    </h5>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="jwt_secret" class="form-label">کلید مخفی JWT *</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="jwt_secret"
                                                   name="jwt_secret"
                                                   value="<?php echo htmlspecialchars($settings['jwt_secret'] ?? 'your-secret-key-here'); ?>"
                                                   required>
                                            <div class="form-text">
                                                کلید مخفی برای امضای توکن‌های JWT. این کلید باید طولانی و تصادفی باشد.
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="jwt_expire" class="form-label">مدت اعتبار توکن (ساعت)</label>
                                            <input type="number"
                                                   class="form-control"
                                                   id="jwt_expire"
                                                   name="jwt_expire"
                                                   value="<?php echo $settings['jwt_expire'] ?? 24; ?>"
                                                   min="1"
                                                   max="720">
                                            <div class="form-text">مدت زمان اعتبار توکن‌های دسترسی به ساعت</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- API Limits -->
                                <div class="mb-5">
                                    <h5 class="card-title mb-3 border-bottom pb-2">
                                        <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                                        محدودیت‌های API
                                    </h5>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="api_rate_limit" class="form-label">محدودیت نرخ (درخواست در ساعت)</label>
                                            <input type="number"
                                                   class="form-control"
                                                   id="api_rate_limit"
                                                   name="api_rate_limit"
                                                   value="<?php echo $settings['api_rate_limit'] ?? 1000; ?>"
                                                   min="10"
                                                   max="10000">
                                            <div class="form-text">حداکثر تعداد درخواست‌های مجاز برای هر کاربر در ساعت</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- API Features -->
                                <div class="mb-4">
                                    <h5 class="card-title mb-3 border-bottom pb-2">
                                        <i class="fas fa-sliders-h me-2 text-primary"></i>
                                        قابلیت‌های API
                                    </h5>

                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       id="api_cors_enabled"
                                                       name="api_cors_enabled"
                                                    <?php echo ($settings['api_cors_enabled'] ?? 1) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="api_cors_enabled">
                                                    فعال کردن CORS
                                                </label>
                                                <div class="form-text">
                                                    اجازه دسترسی به API از دامنه‌های دیگر (برای برنامه‌های موبایل و وب)
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       id="api_debug_mode"
                                                       name="api_debug_mode"
                                                    <?php echo ($settings['api_debug_mode'] ?? 0) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="api_debug_mode">
                                                    حالت دیباگ
                                                </label>
                                                <div class="form-text text-warning">
                                                    فقط در محیط توسعه فعال شود. در تولید غیرفعال باشد.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- API Documentation -->
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-book me-2"></i>
                                        مستندات API
                                    </h6>
                                    <p class="mb-2">آدرس‌های اصلی API:</p>
                                    <ul class="mb-0">
                                        <li><strong>Base URL:</strong> <code>https://yourdomain.com/backend/public/api/v1</code></li>
                                        <li><strong>Auth:</strong> <code>/auth/register</code>, <code>/auth/login</code></li>
                                        <li><strong>Books:</strong> <code>/books</code>, <code>/books/{id}</code></li>
                                        <li><strong>Audio:</strong> <code>/audio/{chapter_id}</code></li>
                                    </ul>
                                </div>

                                <!-- Form Actions -->
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo me-2"></i>
                                        بازنشانی
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        ذخیره تنظیمات
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
    $(document).ready(function() {
        // Form validation
        $('#apiSettingsForm').on('submit', function() {
            const jwtSecret = $('#jwt_secret').val().trim();

            if (!jwtSecret) {
                ShenavaAdmin.showToast('لطفا کلید مخفی JWT را وارد کنید', 'error');
                return false;
            }

            if (jwtSecret.length < 10) {
                ShenavaAdmin.showToast('کلید مخفی باید حداقل 10 کاراکتر باشد', 'error');
                return false;
            }

            ShenavaAdmin.setButtonLoading($(this).find('button[type="submit"]'), true);
            return true;
        });

        // Generate random JWT secret
        $('#generateJwtSecret').on('click', function() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
            let secret = '';
            for (let i = 0; i < 32; i++) {
                secret += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            $('#jwt_secret').val(secret);
            ShenavaAdmin.showToast('کلید مخفی جدید تولید شد', 'success');
        });
    });
</script>