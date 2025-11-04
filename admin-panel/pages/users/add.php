<?php
/**
 * Shenava - Add New User
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
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $displayName = trim($_POST['display_name']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $isPremium = isset($_POST['is_premium']) ? 1 : 0;

    // Validate
    $errors = [];

    if (empty($username)) {
        $errors[] = 'نام کاربری الزامی است';
    }

    if (empty($email)) {
        $errors[] = 'ایمیل الزامی است';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'فرمت ایمیل نامعتبر است';
    }

    if (empty($password)) {
        $errors[] = 'رمز عبور الزامی است';
    } elseif (strlen($password) < 6) {
        $errors[] = 'رمز عبور باید حداقل ۶ کاراکتر باشد';
    }

    // Check if the username exists
    try {
        $db->query("SELECT id FROM users WHERE username = :username");
        $db->bind(':username', $username);
        if ($db->single()) {
            $errors[] = 'این نام کاربری قبلا استفاده شده است';
        }
    } catch (Exception $e) {
        $errors[] = 'خطا در بررسی نام کاربری';
    }

    // Check if email exists
    try {
        $db->query("SELECT id FROM users WHERE email = :email");
        $db->bind(':email', $email);
        if ($db->single()) {
            $errors[] = 'این ایمیل قبلا استفاده شده است';
        }
    } catch (Exception $e) {
        $errors[] = 'خطا در بررسی ایمیل';
    }

    if (empty($errors)) {
        // Generate UUID
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Insert user
            $db->query("INSERT INTO users (uuid, username, email, password_hash, display_name, is_active, is_premium) 
                   VALUES (:uuid, :username, :email, :password_hash, :display_name, :is_active, :is_premium)");

            $db->bind(':uuid', $uuid);
            $db->bind(':username', $username);
            $db->bind(':email', $email);
            $db->bind(':password_hash', $hashedPassword);
            $db->bind(':display_name', $displayName);
            $db->bind(':is_active', $isActive);
            $db->bind(':is_premium', $isPremium);

            if ($db->execute()) {
                $_SESSION['success'] = 'کاربر با موفقیت ایجاد شد';
                header('Location: list.php');
                exit;
            } else {
                $errors[] = 'خطا در ایجاد کاربر';
            }
        } catch (Exception $e) {
            $errors[] = 'خطای پایگاه داده: ' . $e->getMessage();
        }
    }
}

$pageTitle = "افزودن کاربر جدید - شنوا";
?>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">افزودن کاربر جدید</h1>
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

            <!-- Add User Form -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" id="userForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="username" class="form-label">نام کاربری *</label>
                                        <input type="text"
                                               class="form-control"
                                               id="username"
                                               name="username"
                                               value="<?php echo $_POST['username'] ?? ''; ?>"
                                               required
                                               placeholder="نام کاربری">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label">ایمیل *</label>
                                        <input type="email"
                                               class="form-control"
                                               id="email"
                                               name="email"
                                               value="<?php echo $_POST['email'] ?? ''; ?>"
                                               required
                                               placeholder="example@domain.com">
                                    </div>

                                    <div class="col-12">
                                        <label for="display_name" class="form-label">نام نمایشی</label>
                                        <input type="text"
                                               class="form-control"
                                               id="display_name"
                                               name="display_name"
                                               value="<?php echo $_POST['display_name'] ?? ''; ?>"
                                               placeholder="نام کامل نمایشی">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="password" class="form-label">رمز عبور *</label>
                                        <input type="password"
                                               class="form-control"
                                               id="password"
                                               name="password"
                                               required
                                               placeholder="حداقل ۶ کاراکتر">
                                        <div class="form-text">رمز عبور باید حداقل ۶ کاراکتر باشد</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">تکرار رمز عبور *</label>
                                        <input type="password"
                                               class="form-control"
                                               id="confirm_password"
                                               name="confirm_password"
                                               required
                                               placeholder="تکرار رمز عبور">
                                    </div>

                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2">تنظیمات حساب</h6>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="is_active"
                                                   name="is_active" value="1" checked>
                                            <label class="form-check-label" for="is_active">
                                                حساب فعال
                                            </label>
                                        </div>

                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_premium"
                                                   name="is_premium" value="1">
                                            <label class="form-check-label" for="is_premium">
                                                کاربر ویژه
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        ایجاد کاربر
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
                    <!-- Help Card -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">راهنما</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert-info" style="border-radius: 8px;padding: 1rem 1.5rem;">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>توجه:</strong>
                                <ul class="mt-2 mb-0">
                                    <li>نام کاربری باید یکتا باشد</li>
                                    <li>ایمیل باید معتبر و یکتا باشد</li>
                                    <li>رمز عبور حداقل ۶ کاراکتر باشد</li>
                                    <li>کاربر ویژه به تمام محتوا دسترسی دارد</li>
                                </ul>
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
        // Form validation
        $('#userForm').on('submit', function (e) {
            const password = $('#password').val();
            const confirmPassword = $('#confirm_password').val();
            const username = $('#username').val().trim();
            const email = $('#email').val().trim();

            if (!username) {
                ShenavaAdmin.showToast('لطفا نام کاربری را وارد کنید', 'error');
                e.preventDefault();
                return false;
            }

            if (!email) {
                ShenavaAdmin.showToast('لطفا ایمیل را وارد کنید', 'error');
                e.preventDefault();
                return false;
            }

            if (!password) {
                ShenavaAdmin.showToast('لطفا رمز عبور را وارد کنید', 'error');
                e.preventDefault();
                return false;
            }

            if (password.length < 6) {
                ShenavaAdmin.showToast('رمز عبور باید حداقل ۶ کاراکتر باشد', 'error');
                e.preventDefault();
                return false;
            }

            if (password !== confirmPassword) {
                ShenavaAdmin.showToast('رمز عبور و تکرار آن مطابقت ندارند', 'error');
                e.preventDefault();
                return false;
            }

            ShenavaAdmin.setButtonLoading($(this).find('button[type="submit"]'), true);
            return true;
        });
    });
</script>