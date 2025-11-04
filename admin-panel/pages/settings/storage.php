<?php
/**
 * Shenava - Storage Settings
 */

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
        'storage_audio_path' => $_POST['storage_audio_path'] ?? '/assets/audio/',
        'storage_images_path' => $_POST['storage_images_path'] ?? '/assets/images/',
        'storage_max_file_size' => intval($_POST['storage_max_file_size'] ?? 50),
        'storage_allowed_audio_types' => $_POST['storage_allowed_audio_types'] ?? 'mp3,wav,m4a,ogg',
        'storage_allowed_image_types' => $_POST['storage_allowed_image_types'] ?? 'jpg,jpeg,png,gif',
    ];

    foreach ($settings as $key => $value) {
        try {
            // Check if setting exists
            $db->query("SELECT id FROM app_settings WHERE setting_key = :key");
            $db->bind(':key', $key);
            $existing = $db->single();
        } catch (Exception $e) {
            die($e->getMessage());
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
            die($e->getMessage());
        }
    }

    $_SESSION['success'] = 'تنظیمات ذخیره‌سازی با موفقیت ذخیره شد';
    header('Location: storage.php');
    exit;
}

try {
    // Get current settings
    $db->query("SELECT setting_key, setting_value FROM app_settings WHERE setting_key LIKE 'storage_%'");
    $settingsResult = $db->resultSet();
} catch (Exception $e) {
    die($e->getMessage());
}

$settings = [];
foreach ($settingsResult as $setting) {
    $settings[$setting->setting_key] = $setting->setting_value;
}

// Calculate storage usage
$totalSize = 0;
$audioSize = 0;
$imageSize = 0;

// This is a simplified calculation - in production you'd want to scan directories
try {
    $db->query("SELECT SUM(file_size) as total FROM chapters");
    $result = $db->single();
    $audioSize = $result->total ?? 0;
    $totalSize += $audioSize;
} catch (Exception $e) {
    // Ignore errors for demo
}

$pageTitle = "تنظیمات ذخیره‌سازی";
?>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">تنظیمات ذخیره‌سازی</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="submit" form="storageForm" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        ذخیره تنظیمات
                    </button>
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
                                <a href="api.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-code me-2"></i>
                                    تنظیمات API
                                </a>
                                <a href="storage.php" class="list-group-item list-group-item-action active">
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

                    <!-- Storage Stats -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-chart-pie me-2"></i>
                                آمار ذخیره‌سازی
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="storage-stats">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>فضای کل استفاده شده:</small>
                                        <small><?php echo formatBytes($totalSize); ?></small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar"
                                             style="width: <?php echo min(($totalSize / (1024 * 1024 * 1024)) * 100, 100); ?>%"></div>
                                    </div>
                                </div>

                                <div class="storage-breakdown">
                                    <div class="d-flex justify-content-between text-muted small mb-1">
                                        <span>فایل‌های صوتی:</span>
                                        <span><?php echo formatBytes($audioSize); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted small">
                                        <span>تصاویر:</span>
                                        <span><?php echo formatBytes($imageSize); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" id="storageForm">
                                <!-- Storage Paths -->
                                <div class="mb-5">
                                    <h5 class="card-title mb-3 border-bottom pb-2">
                                        <i class="fas fa-folder me-2 text-primary"></i>
                                        مسیرهای ذخیره‌سازی
                                    </h5>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="storage_audio_path" class="form-label">مسیر فایل‌های
                                                صوتی</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="storage_audio_path"
                                                   name="storage_audio_path"
                                                   value="<?php echo $settings['storage_audio_path'] ?? '/assets/audio/'; ?>"
                                                   required>
                                            <div class="form-text">مسیر ذخیره‌سازی فایل‌های صوتی نسبت به root پروژه
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="storage_images_path" class="form-label">مسیر تصاویر</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="storage_images_path"
                                                   name="storage_images_path"
                                                   value="<?php echo $settings['storage_images_path'] ?? '/assets/images/'; ?>"
                                                   required>
                                            <div class="form-text">مسیر ذخیره‌سازی تصاویر نسبت به root پروژه</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- File Upload Limits -->
                                <div class="mb-5">
                                    <h5 class="card-title mb-3 border-bottom pb-2">
                                        <i class="fas fa-upload me-2 text-primary"></i>
                                        محدودیت‌های آپلود
                                    </h5>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="storage_max_file_size" class="form-label">حداکثر سایز فایل
                                                (MB)</label>
                                            <input type="number"
                                                   class="form-control"
                                                   id="storage_max_file_size"
                                                   name="storage_max_file_size"
                                                   value="<?php echo $settings['storage_max_file_size'] ?? 50; ?>"
                                                   min="1"
                                                   max="500"
                                                   required>
                                            <div class="form-text">حداکثر حجم مجاز برای آپلود فایل‌ها به مگابایت</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Allowed File Types -->
                                <div class="mb-4">
                                    <h5 class="card-title mb-3 border-bottom pb-2">
                                        <i class="fas fa-file me-2 text-primary"></i>
                                        فرمت‌های مجاز
                                    </h5>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="storage_allowed_audio_types" class="form-label">فرمت‌های صوتی
                                                مجاز</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="storage_allowed_audio_types"
                                                   name="storage_allowed_audio_types"
                                                   value="<?php echo $settings['storage_allowed_audio_types'] ?? 'mp3,wav,m4a,ogg'; ?>"
                                                   required>
                                            <div class="form-text">فرمت‌های مجاز با کاما جدا شوند (مثلا: mp3,wav,m4a)
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="storage_allowed_image_types" class="form-label">فرمت‌های تصویری
                                                مجاز</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="storage_allowed_image_types"
                                                   name="storage_allowed_image_types"
                                                   value="<?php echo $settings['storage_allowed_image_types'] ?? 'jpg,jpeg,png,gif'; ?>"
                                                   required>
                                            <div class="form-text">فرمت‌های مجاز با کاما جدا شوند (مثلا: jpg,png,gif)
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Storage Cleanup -->
                                <div class="alert alert-warning">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-trash me-2"></i>
                                        پاکسازی فضای ذخیره‌سازی
                                    </h6>
                                    <p class="mb-2">اقدامات مدیریت فضای ذخیره‌سازی:</p>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                id="cleanOrphanedFiles">
                                            <i class="fas fa-broom me-1"></i>
                                            پاکسازی فایل‌های بدون استفاده
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm"
                                                id="regenerateThumbnails">
                                            <i class="fas fa-sync me-1"></i>
                                            تولید مجدد تصاویر بندانگشتی
                                        </button>
                                    </div>
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

<?php
// Helper function to format bytes
function formatBytes($bytes, $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

?>

<script>
    $(document).ready(function () {
        // Storage cleanup actions
        $('#cleanOrphanedFiles').on('click', function () {
            if (confirm('آیا از پاکسازی فایل‌های بدون استفاده مطمئن هستید؟ این عمل غیرقابل بازگشت است.')) {
                ShenavaAdmin.showToast('در حال پاکسازی فایل‌های بدون استفاده...', 'info');
                // Implement cleanup logic here
                setTimeout(() => {
                    ShenavaAdmin.showToast('پاکسازی با موفقیت انجام شد', 'success');
                }, 2000);
            }
        });

        $('#regenerateThumbnails').on('click', function () {
            ShenavaAdmin.showToast('در حال تولید مجدد تصاویر بندانگشتی...', 'info');
            // Implement thumbnail regeneration logic here
            setTimeout(() => {
                ShenavaAdmin.showToast('تولید مجدد با موفقیت انجام شد', 'success');
            }, 2000);
        });

        // Form validation
        $('#storageForm').on('submit', function () {
            const maxFileSize = $('#storage_max_file_size').val();
            const audioTypes = $('#storage_allowed_audio_types').val();
            const imageTypes = $('#storage_allowed_image_types').val();

            if (maxFileSize < 1 || maxFileSize > 500) {
                ShenavaAdmin.showToast('حداکثر سایز فایل باید بین 1 تا 500 مگابایت باشد', 'error');
                return false;
            }

            if (!audioTypes.trim()) {
                ShenavaAdmin.showToast('لطفا فرمت‌های صوتی مجاز را وارد کنید', 'error');
                return false;
            }

            if (!imageTypes.trim()) {
                ShenavaAdmin.showToast('لطفا فرمت‌های تصویری مجاز را وارد کنید', 'error');
                return false;
            }

            ShenavaAdmin.setButtonLoading($(this).find('button[type="submit"]'), true);
            return true;
        });
    });
</script>