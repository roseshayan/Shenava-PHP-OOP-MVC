<?php
/**
 * Shenava - General Settings
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
        'app_name' => $_POST['app_name'] ?? 'شنوا',
        'app_description' => $_POST['app_description'] ?? '',
        'contact_email' => $_POST['contact_email'] ?? '',
        'items_per_page' => intval($_POST['items_per_page'] ?? 20),
        'enable_registration' => isset($_POST['enable_registration']) ? 1 : 0,
        'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
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
                // Update existing setting
                $db->query("UPDATE app_settings SET setting_value = :value WHERE setting_key = :key");
            } else {
                // Insert new setting
                $db->query("INSERT INTO app_settings (setting_key, setting_value, setting_type) VALUES (:key, :value, 'string')");
            }

            $db->bind(':key', $key);
            $db->bind(':value', $value);
            $db->execute();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    $_SESSION['success'] = 'تنظیمات با موفقیت ذخیره شد';
    header('Location: general.php');
    exit;
}

try {
    // Get current settings
    $db->query("SELECT setting_key, setting_value FROM app_settings");
    $settingsResult = $db->resultSet();
} catch (Exception $e) {
    echo $e->getMessage();
}

$settings = [];
foreach ($settingsResult as $setting) {
    $settings[$setting->setting_key] = $setting->setting_value;
}

$pageTitle = "تنظیمات سیستم - شنوا";
?>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">تنظیمات سیستم</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="submit" form="settingsForm" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            ذخیره تنظیمات
                        </button>
                    </div>
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

            <!-- Settings Tabs -->
            <div class="row">
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <a href="general.php" class="list-group-item list-group-item-action active">
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
                            <form method="POST" id="settingsForm">
                                <!-- Basic Settings -->
                                <div class="mb-4">
                                    <h5 class="card-title mb-3">
                                        <i class="fas fa-info-circle me-2 text-primary"></i>
                                        اطلاعات پایه
                                    </h5>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="app_name" class="form-label">نام برنامه *</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="app_name"
                                                   name="app_name"
                                                   value="<?php echo htmlspecialchars($settings['app_name'] ?? 'شنوا'); ?>"
                                                   required>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="contact_email" class="form-label">ایمیل تماس</label>
                                            <input type="email"
                                                   class="form-control"
                                                   id="contact_email"
                                                   name="contact_email"
                                                   value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
                                        </div>

                                        <div class="col-12">
                                            <label for="app_description" class="form-label">توضیحات برنامه</label>
                                            <textarea class="form-control"
                                                      id="app_description"
                                                      name="app_description"
                                                      rows="3"
                                                      placeholder="توضیحات مختصر درباره برنامه"><?php echo htmlspecialchars($settings['app_description'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <!-- System Settings -->
                                <div class="mb-4">
                                    <h5 class="card-title mb-3">
                                        <i class="fas fa-sliders-h me-2 text-primary"></i>
                                        تنظیمات سیستم
                                    </h5>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="items_per_page" class="form-label">تعداد آیتم در هر صفحه</label>
                                            <input type="number"
                                                   class="form-control"
                                                   id="items_per_page"
                                                   name="items_per_page"
                                                   value="<?php echo $settings['items_per_page'] ?? 20; ?>"
                                                   min="5"
                                                   max="100">
                                            <div class="form-text">تعداد آیتم‌های نمایش داده شده در لیست‌ها</div>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <!-- Feature Toggles -->
                                <div class="mb-4">
                                    <h5 class="card-title mb-3">
                                        <i class="fas fa-toggle-on me-2 text-primary"></i>
                                        فعال/غیرفعال کردن قابلیت‌ها
                                    </h5>

                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       id="enable_registration"
                                                       name="enable_registration"
                                                    <?php echo ($settings['enable_registration'] ?? 1) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="enable_registration">
                                                    فعال کردن ثبت‌نام کاربران جدید
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       id="maintenance_mode"
                                                       name="maintenance_mode"
                                                    <?php echo ($settings['maintenance_mode'] ?? 0) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="maintenance_mode">
                                                    حالت تعمیرات
                                                </label>
                                                <div class="form-text text-warning">
                                                    در این حالت فقط مدیران می‌توانند به سیستم دسترسی داشته باشند
                                                </div>
                                            </div>
                                        </div>
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

<script>
    $(document).ready(function() {
        // Form validation
        $('#settingsForm').on('submit', function() {
            const appName = $('#app_name').val().trim();

            if (!appName) {
                ShenavaAdmin.showToast('لطفا نام برنامه را وارد کنید', 'error');
                return false;
            }

            ShenavaAdmin.setButtonLoading($(this).find('button[type="submit"]'), true);
            return true;
        });
    });
</script>