<?php
/**
 * Shenava - Appearance Settings
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
        'theme_primary_color' => $_POST['theme_primary_color'] ?? '#00BFA5',
        'theme_accent_color' => $_POST['theme_accent_color'] ?? '#FF7043',
        'theme_background_color' => $_POST['theme_background_color'] ?? '#E3F2FD',
        'theme_text_primary' => $_POST['theme_text_primary'] ?? '#212121',
        'theme_text_secondary' => $_POST['theme_text_secondary'] ?? '#757575',
        'theme_dark_mode' => isset($_POST['theme_dark_mode']) ? 1 : 0,
        'theme_font_family' => $_POST['theme_font_family'] ?? 'Vazirmatn FD',
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

    $_SESSION['success'] = 'تنظیمات ظاهر با موفقیت ذخیره شد';
    header('Location: appearance.php');
    exit;
}

try {
// Get current settings
    $db->query("SELECT setting_key, setting_value FROM app_settings WHERE setting_key LIKE 'theme_%'");
    $settingsResult = $db->resultSet();
} catch (Exception $e) {
    echo $e->getMessage();
}

$settings = [];
foreach ($settingsResult as $setting) {
    $settings[$setting->setting_key] = $setting->setting_value;
}

$pageTitle = "تنظیمات ظاهر";
?>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">تنظیمات ظاهر و تم</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="submit" form="appearanceForm" class="btn btn-primary">
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
                                <a href="appearance.php" class="list-group-item list-group-item-action active">
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

                    <!-- Theme Preview -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-eye me-2"></i>
                                پیش‌نمایش تم
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="theme-preview">
                                <div class="preview-header mb-3"
                                     style="background: <?php echo $settings['theme_primary_color'] ?? '#00BFA5'; ?>; color: white; padding: 10px; border-radius: 5px;">
                                    <small>هدر</small>
                                </div>
                                <div class="preview-content mb-2 p-2"
                                     style="background: <?php echo $settings['theme_background_color'] ?? '#E3F2FD'; ?>; border-radius: 5px;">
                                    <small style="color: <?php echo $settings['theme_text_primary'] ?? '#212121'; ?>;">متن
                                        اصلی</small>
                                </div>
                                <div class="preview-secondary p-2">
                                    <small style="color: <?php echo $settings['theme_text_secondary'] ?? '#757575'; ?>;">متن
                                        ثانویه</small>
                                </div>
                                <div class="preview-button mt-3">
                                    <button class="btn btn-sm w-100"
                                            style="background: <?php echo $settings['theme_accent_color'] ?? '#FF7043'; ?>; color: white;">
                                        دکمه
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" id="appearanceForm">
                                <!-- Color Scheme -->
                                <div class="mb-5">
                                    <h5 class="card-title mb-3 border-bottom pb-2">
                                        <i class="fas fa-palette me-2 text-primary"></i>
                                        طرح رنگ
                                    </h5>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="theme_primary_color" class="form-label">رنگ اصلی</label>
                                            <div class="input-group">
                                                <input type="color"
                                                       class="form-control form-control-color"
                                                       id="theme_primary_color"
                                                       name="theme_primary_color"
                                                       value="<?php echo $settings['theme_primary_color'] ?? '#00BFA5'; ?>"
                                                       title="Choose primary color">
                                                <input aria-label="theme_primary_color" type="text"
                                                       class="form-control"
                                                       value="<?php echo $settings['theme_primary_color'] ?? '#00BFA5'; ?>"
                                                       readonly>
                                            </div>
                                            <div class="form-text">رنگ اصلی برای هدر و عناصر مهم</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="theme_accent_color" class="form-label">رنگ تأکیدی</label>
                                            <div class="input-group">
                                                <input type="color"
                                                       class="form-control form-control-color"
                                                       id="theme_accent_color"
                                                       name="theme_accent_color"
                                                       value="<?php echo $settings['theme_accent_color'] ?? '#FF7043'; ?>"
                                                       title="Choose accent color">
                                                <input aria-label="theme_accent_color" type="text"
                                                       class="form-control"
                                                       value="<?php echo $settings['theme_accent_color'] ?? '#FF7043'; ?>"
                                                       readonly>
                                            </div>
                                            <div class="form-text">رنگ برای دکمه‌ها و CTA</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="theme_background_color" class="form-label">رنگ پس‌زمینه</label>
                                            <div class="input-group">
                                                <input type="color"
                                                       class="form-control form-control-color"
                                                       id="theme_background_color"
                                                       name="theme_background_color"
                                                       value="<?php echo $settings['theme_background_color'] ?? '#E3F2FD'; ?>"
                                                       title="Choose background color">
                                                <input aria-label="theme_background_color" type="text"
                                                       class="form-control"
                                                       value="<?php echo $settings['theme_background_color'] ?? '#E3F2FD'; ?>"
                                                       readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="theme_text_primary" class="form-label">رنگ متن اصلی</label>
                                            <div class="input-group">
                                                <input type="color"
                                                       class="form-control form-control-color"
                                                       id="theme_text_primary"
                                                       name="theme_text_primary"
                                                       value="<?php echo $settings['theme_text_primary'] ?? '#212121'; ?>"
                                                       title="Choose text color">
                                                <input aria-label="theme_text_primary" type="text"
                                                       class="form-control"
                                                       value="<?php echo $settings['theme_text_primary'] ?? '#212121'; ?>"
                                                       readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="theme_text_secondary" class="form-label">رنگ متن ثانویه</label>
                                            <div class="input-group">
                                                <input type="color"
                                                       class="form-control form-control-color"
                                                       id="theme_text_secondary"
                                                       name="theme_text_secondary"
                                                       value="<?php echo $settings['theme_text_secondary'] ?? '#757575'; ?>"
                                                       title="Choose secondary text color">
                                                <input aria-label="theme_text_secondary" type="text"
                                                       class="form-control"
                                                       value="<?php echo $settings['theme_text_secondary'] ?? '#757575'; ?>"
                                                       readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Theme Options -->
                                <div class="mb-5">
                                    <h5 class="card-title mb-3 border-bottom pb-2">
                                        <i class="fas fa-sliders-h me-2 text-primary"></i>
                                        گزینه‌های تم
                                    </h5>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="theme_font_family" class="form-label">فونت</label>
                                            <select class="form-select" id="theme_font_family" name="theme_font_family">
                                                <option value="Vazirmatn FD" <?php echo ($settings['theme_font_family'] ?? 'Vazirmatn FD') == 'Vazirmatn FD' ? 'selected' : ''; ?>>
                                                    Vazirmatn FD
                                                </option>
                                                <option value="Tahoma" <?php echo ($settings['theme_font_family'] ?? '') == 'Tahoma' ? 'selected' : ''; ?>>
                                                    Tahoma
                                                </option>
                                                <option value="Arial" <?php echo ($settings['theme_font_family'] ?? '') == 'Arial' ? 'selected' : ''; ?>>
                                                    Arial
                                                </option>
                                                <option value="Segoe UI" <?php echo ($settings['theme_font_family'] ?? '') == 'Segoe UI' ? 'selected' : ''; ?>>
                                                    Segoe UI
                                                </option>
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       id="theme_dark_mode"
                                                       name="theme_dark_mode"
                                                    <?php echo ($settings['theme_dark_mode'] ?? 0) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="theme_dark_mode">
                                                    حالت تاریک
                                                </label>
                                                <div class="form-text">
                                                    فعال کردن تم تاریک برای پنل مدیریت
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Preset Themes -->
                                <div class="mb-4">
                                    <h5 class="card-title mb-3 border-bottom pb-2">
                                        <i class="fas fa-magic me-2 text-primary"></i>
                                        تم‌های از پیش تعریف شده
                                    </h5>

                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="theme-preset-card"
                                                 data-theme='{"primary":"#00BFA5","accent":"#FF7043","bg":"#E3F2FD"}'>
                                                <div class="preset-preview">
                                                    <div class="preset-primary"></div>
                                                    <div class="preset-accent"></div>
                                                    <div class="preset-bg"></div>
                                                </div>
                                                <div class="preset-name">شنوا (پیش‌فرض)</div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="theme-preset-card"
                                                 data-theme='{"primary":"#2196F3","accent":"#FF9800","bg":"#F5F5F5"}'>
                                                <div class="preset-preview">
                                                    <div class="preset-primary" style="background: #2196F3"></div>
                                                    <div class="preset-accent" style="background: #FF9800"></div>
                                                    <div class="preset-bg" style="background: #F5F5F5"></div>
                                                </div>
                                                <div class="preset-name">آبی-نارنجی</div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="theme-preset-card"
                                                 data-theme='{"primary":"#4CAF50","accent":"#FF5252","bg":"#F1F8E9"}'>
                                                <div class="preset-preview">
                                                    <div class="preset-primary" style="background: #4CAF50"></div>
                                                    <div class="preset-accent" style="background: #FF5252"></div>
                                                    <div class="preset-bg" style="background: #F1F8E9"></div>
                                                </div>
                                                <div class="preset-name">سبز-قرمز</div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="theme-preset-card"
                                                 data-theme='{"primary":"#9C27B0","accent":"#00BCD4","bg":"#F3E5F5"}'>
                                                <div class="preset-preview">
                                                    <div class="preset-primary" style="background: #9C27B0"></div>
                                                    <div class="preset-accent" style="background: #00BCD4"></div>
                                                    <div class="preset-bg" style="background: #F3E5F5"></div>
                                                </div>
                                                <div class="preset-name">بنفش-فیروزه‌ای</div>
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

<style>
    .theme-preset-card {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .theme-preset-card:hover {
        border-color: #00BFA5;
        transform: translateY(-2px);
    }

    .theme-preset-card.active {
        border-color: #00BFA5;
        background-color: #f8f9fa;
    }

    .preset-preview {
        display: flex;
        height: 40px;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 8px;
    }

    .preset-primary {
        flex: 1;
        background: #00BFA5;
    }

    .preset-accent {
        flex: 1;
        background: #FF7043;
    }

    .preset-bg {
        flex: 1;
        background: #E3F2FD;
    }

    .preset-name {
        text-align: center;
        font-size: 0.8rem;
        font-weight: 500;
    }
</style>

<script>
    $(document).ready(function () {
        // Update color text inputs when color picker changes
        $('.form-control-color').on('change', function () {
            $(this).next('.form-control').val($(this).val());
            updateThemePreview();
        });

        // Update theme preview
        function updateThemePreview() {
            const primaryColor = $('#theme_primary_color').val();
            const accentColor = $('#theme_accent_color').val();
            const bgColor = $('#theme_background_color').val();
            const textPrimary = $('#theme_text_primary').val();
            const textSecondary = $('#theme_text_secondary').val();

            $('.preview-header').css('background', primaryColor);
            $('.preview-content').css('background', bgColor);
            $('.preview-content small').css('color', textPrimary);
            $('.preview-secondary small').css('color', textSecondary);
            $('.preview-button button').css('background', accentColor);
        }

        // Preset theme selection
        $('.theme-preset-card').on('click', function () {
            $('.theme-preset-card').removeClass('active');
            $(this).addClass('active');

            const theme = JSON.parse($(this).data('theme'));
            $('#theme_primary_color').val(theme.primary).trigger('change');
            $('#theme_accent_color').val(theme.accent).trigger('change');
            $('#theme_background_color').val(theme.bg).trigger('change');
        });

        // Initialize preview
        updateThemePreview();
    });
</script>