<?php
/**
 * Shenava - Edit User
 */
session_save_path('/tmp');
session_start();
require_once '../../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';

$db = new Database();

// Get user ID
$userId = intval($_GET['id'] ?? 0);
if (!$userId) {
    header('Location: list.php');
    exit;
}

try {
    // Get user data
    $db->query("SELECT * FROM users WHERE id = :id");
    $db->bind(':id', $userId);
    $user = $db->single();
} catch (Exception $e) {
    echo $e->getMessage();
}

if (!$user) {
    header('Location: list.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $displayName = trim($_POST['display_name']);
    $email = trim($_POST['email']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $isPremium = isset($_POST['is_premium']) ? 1 : 0;
    $darkMode = isset($_POST['dark_mode']) ? 1 : 0;

    // Password change fields
    $changePassword = isset($_POST['change_password']) ? 1 : 0;
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $errors = [];

    // Basic validation
    if (empty($email)) {
        $errors[] = 'ایمیل الزامی است';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'فرمت ایمیل نامعتبر است';
    }

    // Check if email exists (excluding current user)
    try {
        $db->query("SELECT id FROM users WHERE email = :email AND id != :id");
        $db->bind(':email', $email);
        $db->bind(':id', $userId);
        if ($db->single()) {
            $errors[] = 'این ایمیل قبلا توسط کاربر دیگری استفاده شده است';
        }
    } catch (Exception $e) {
        $errors[] = 'خطا در بررسی ایمیل';
    }

    // Password validation if changing password
    if ($changePassword) {
        if (empty($newPassword)) {
            $errors[] = 'رمز عبور جدید الزامی است';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'رمز عبور جدید باید حداقل ۶ کاراکتر باشد';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'رمز عبور جدید و تکرار آن مطابقت ندارند';
        }
    }

    if (empty($errors)) {
        try {
            // Build update query
            $updateFields = [
                    'display_name = :display_name',
                    'email = :email',
                    'is_active = :is_active',
                    'is_premium = :is_premium',
                    'dark_mode = :dark_mode',
                    'updated_at = NOW()'
            ];

            $params = [
                    ':display_name' => $displayName,
                    ':email' => $email,
                    ':is_active' => $isActive,
                    ':is_premium' => $isPremium,
                    ':dark_mode' => $darkMode,
                    ':id' => $userId
            ];

            // Add password update if changing
            if ($changePassword && !empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateFields[] = 'password_hash = :password_hash';
                $params[':password_hash'] = $hashedPassword;
            }

            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :id";

            $db->query($sql);
            foreach ($params as $key => $value) {
                $db->bind($key, $value);
            }

            if ($db->execute()) {
                $_SESSION['success'] = 'اطلاعات کاربر با موفقیت به‌روزرسانی شد' .
                        ($changePassword ? ' و رمز عبور تغییر کرد' : '');
                header('Location: list.php');
                exit;
            } else {
                $errors[] = 'خطا در به‌روزرسانی اطلاعات کاربر';
            }
        } catch (Exception $e) {
            $errors[] = 'خطای پایگاه داده: ' . $e->getMessage();
        }
    }
}

$pageTitle = "ویرایش کاربر - شنوا";
?>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">ویرایش کاربر: <?php echo $user->username; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="list.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>
                        بازگشت به لیست
                    </a>
                </div>
            </div>

            <!-- Notifications -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Edit User Form -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">نام کاربری</label>
                                        <input aria-label="username" type="text" class="form-control"
                                               value="<?php echo $user->username; ?>"
                                               readonly>
                                        <div class="form-text">نام کاربری قابل تغییر نیست</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label">ایمیل *</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                               value="<?php echo $user->email; ?>" required>
                                    </div>

                                    <div class="col-12">
                                        <label for="display_name" class="form-label">نام نمایشی</label>
                                        <input type="text" class="form-control" id="display_name"
                                               name="display_name"
                                               value="<?php echo $user->display_name ?? ''; ?>">
                                    </div>

                                    <!-- Password Change Section -->
                                    <div class="col-12">
                                        <div class="card border-warning">
                                            <div class="card-header bg-warning bg-opacity-10">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-key me-2 text-warning"></i>
                                                    تغییر رمز عبور
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           id="change_password"
                                                           name="change_password"
                                                           value="1"
                                                           onchange="togglePasswordFields()">
                                                    <label class="form-check-label" for="change_password">
                                                        تغییر رمز عبور
                                                    </label>
                                                </div>

                                                <div id="passwordFields" style="display: none;">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label for="new_password" class="form-label">رمز عبور
                                                                جدید *</label>
                                                            <input type="password"
                                                                   class="form-control"
                                                                   id="new_password"
                                                                   name="new_password"
                                                                   placeholder="رمز عبور جدید">
                                                            <div class="form-text">رمز عبور باید حداقل ۶ کاراکتر
                                                                باشد
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label for="confirm_password" class="form-label">تکرار
                                                                رمز عبور *</label>
                                                            <input type="password"
                                                                   class="form-control"
                                                                   id="confirm_password"
                                                                   name="confirm_password"
                                                                   placeholder="تکرار رمز عبور">
                                                        </div>
                                                    </div>

                                                    <div class="alert alert-info mt-3 mb-0">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        پس از تغییر رمز عبور، کاربر باید با رمز عبور جدید وارد سیستم
                                                        شود.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2">تنظیمات حساب</h6>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="is_active"
                                                   name="is_active"
                                                    <?php echo $user->is_active ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_active">
                                                حساب فعال
                                            </label>
                                        </div>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="is_premium"
                                                   name="is_premium"
                                                    <?php echo $user->is_premium ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_premium">
                                                کاربر ویژه
                                            </label>
                                        </div>

                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="dark_mode"
                                                   name="dark_mode"
                                                    <?php echo $user->dark_mode ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="dark_mode">
                                                حالت تاریک
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        ذخیره تغییرات
                                    </button>
                                    <a href="list.php" class="btn btn-outline-secondary">
                                        انصراف
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- User Info Card -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">اطلاعات کاربر</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <?php if ($user->avatar_url): ?>
                                    <img src="<?php echo $user->avatar_url; ?>"
                                         alt="<?php echo $user->username; ?>"
                                         class="rounded-circle mb-3"
                                         width="100" height="100" style="object-fit: cover;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3"
                                         style="width: 100px; height: 100px;">
                                        <i class="fas fa-user text-muted fa-3x"></i>
                                    </div>
                                <?php endif; ?>

                                <h5><?php echo $user->display_name ?: $user->username; ?></h5>
                                <p class="text-muted">@<?php echo $user->username; ?></p>
                            </div>

                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                    <span>وضعیت:</span>
                                    <span class="badge bg-<?php echo $user->is_active ? 'success' : 'secondary'; ?>">
                                            <?php echo $user->is_active ? 'فعال' : 'غیرفعال'; ?>
                                        </span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                    <span>نوع حساب:</span>
                                    <span class="badge bg-<?php echo $user->is_premium ? 'warning' : 'info'; ?>">
                                            <?php echo $user->is_premium ? 'ویژه' : 'عادی'; ?>
                                        </span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                    <span>تاریخ ثبت‌نام:</span>
                                    <span><?php echo date('Y/m/d', strtotime($user->created_at)); ?></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                    <span>آخرین به‌روزرسانی:</span>
                                    <span><?php echo date('Y/m/d', strtotime($user->updated_at)); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Info Card -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-shield-alt me-2"></i>
                                اطلاعات امنیتی
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                    <span>آخرین ورود:</span>
                                    <span class="text-muted small">
                                        <?php
                                        if ($user->last_login_at && $user->last_login_at !== '0000-00-00 00:00:00') {
                                            echo date('Y/m/d H:i', strtotime($user->last_login_at));
                                        } else {
                                            echo '---';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                    <span>تعداد ورود:</span>
                                    <span class="badge bg-info"><?php echo $user->login_count ?? 0; ?></span>
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
    function togglePasswordFields() {
        const changePassword = document.getElementById('change_password').checked;
        const passwordFields = document.getElementById('passwordFields');

        if (changePassword) {
            passwordFields.style.display = 'block';
            // Clear password fields when showing
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
        } else {
            passwordFields.style.display = 'none';
            // Clear password fields when hiding
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
        }
    }

    $(document).ready(function () {
        // Form validation
        $('#userForm').on('submit', function (e) {
            const changePassword = $('#change_password').is(':checked');
            const newPassword = $('#new_password').val();
            const confirmPassword = $('#confirm_password').val();
            const email = $('#email').val().trim();

            // Email validation
            if (!email) {
                ShenavaAdmin.showToast('لطفا ایمیل را وارد کنید', 'error');
                e.preventDefault();
                return false;
            }

            // Password validation if changing password
            if (changePassword) {
                if (!newPassword) {
                    ShenavaAdmin.showToast('لطفا رمز عبور جدید را وارد کنید', 'error');
                    e.preventDefault();
                    return false;
                }

                if (newPassword.length < 6) {
                    ShenavaAdmin.showToast('رمز عبور جدید باید حداقل ۶ کاراکتر باشد', 'error');
                    e.preventDefault();
                    return false;
                }

                if (newPassword !== confirmPassword) {
                    ShenavaAdmin.showToast('رمز عبور جدید و تکرار آن مطابقت ندارند', 'error');
                    e.preventDefault();
                    return false;
                }
            }

            ShenavaAdmin.setButtonLoading($(this).find('button[type="submit"]'), true);
            return true;
        });
    });
</script>

<style>
    #passwordFields {
        transition: all 0.3s ease;
    }

    .card-border-warning {
        border-left: 4px solid #ffc107;
    }
</style>
