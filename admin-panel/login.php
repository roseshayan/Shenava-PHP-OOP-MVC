<?php
/**
 * Shenava - Admin Login
 */

session_save_path('/tmp');
session_start();

// Define base paths
define('BASE_PATH', dirname(__DIR__));
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $error = '';

    try {
        $db = new Database();

        // Find user by username or email
        $db->query("SELECT * FROM users WHERE (username = :username OR email = :username) AND is_active = 1");
        $db->bind(':username', $username);
        $user = $db->single();

        if ($user) {
            // Check if user is premium
            if (!$user->is_premium) {
                $error = 'فقط کاربران ویژه می‌توانند به پنل مدیریت دسترسی داشته باشند';
            } // Verify password
            elseif (password_verify($password, $user->password_hash)) {
                // Login successful
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user_id'] = $user->id;
                $_SESSION['admin_username'] = $user->username;
                $_SESSION['admin_name'] = $user->display_name ?: $user->username;
                $_SESSION['admin_email'] = $user->email;
                $_SESSION['login_time'] = time();

                // Update last login
                $db->query("UPDATE users SET last_login_at = NOW(), login_count = COALESCE(login_count, 0) + 1 WHERE id = :id");
                $db->bind(':id', $user->id);
                $db->execute();

                header('Location: index.php');
                exit;
            } else {
                $error = 'رمز عبور اشتباه است';
            }
        } else {
            $error = 'کاربری با این مشخصات یافت نشد';
        }
    } catch (Exception $e) {
        $error = 'خطا در ارتباط با سرور. لطفا مجددا تلاش کنید.';
        error_log("Login error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به پنل مدیریت - شنوا</title>

    <!-- Meta Description -->
    <meta name="description"
          content="شنوا، کتابخانه‌ای آنلاین از کتاب‌های صوتی رایگان است. در شنوا می‌توانید انواع داستان‌های شنیدنی، رمان‌ها، ادبیات کلاسیک و آثار فانتزی را به‌صورت رایگان گوش دهید. هرجا و هرزمان با شنوا همراه باشید.">

    <!-- Meta Keywords -->
    <meta name="keywords"
          content="شنوا, کتاب صوتی, رایگان, اپلیکیشن کتاب صوتی, داستان صوتی, رمان صوتی, شنیدن کتاب, Shenava, Free Audiobooks">

    <!-- Open Graph (برای اشتراک‌گذاری در شبکه‌های اجتماعی) -->
    <meta property="og:title" content="شنوا | کتاب‌های صوتی رایگان و داستانی">
    <meta property="og:description"
          content="با شنوا، کتاب‌ها را بشنوید. مجموعه‌ای از داستان‌ها و رمان‌های شنیدنی، همه رایگان و همیشه در دسترس.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://shennava.ir">
    <meta property="og:image" content="https://shennava.ir/admin-panel/img/logo.png">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="شنوا | کتاب‌های صوتی رایگان و داستانی">
    <meta name="twitter:description" content="کتاب‌ها رو بشنو، دنیایی تازه بساز.">
    <meta name="twitter:image" content="https://shennava.ir/admin-panel/img/logo.png">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/favicon/favicon-96x96.png" sizes="96x96"/>
    <link rel="icon" type="image/svg+xml" href="img/favicon/favicon.svg"/>
    <link rel="shortcut icon" href="img/favicon/favicon.ico"/>
    <link rel="apple-touch-icon" sizes="180x180" href="img/favicon/apple-touch-icon.png"/>
    <meta name="apple-mobile-web-app-title" content="Shennava"/>
    <link rel="manifest" href="img/favicon/site.webmanifest"/>

    <!-- Bootstrap 5 CSS -->
    <link href="../node_modules/bootstrap/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../node_modules/@fortawesome/fontawesome-free/css/all.min.css">

    <!-- Vazir Font -->
    <link href="../node_modules/vazirmatn/misc/Farsi-Digits/Vazirmatn-FD-font-face.min.css" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">

    <style>
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, #00897B 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .login-header {
            background: var(--primary-color);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .login-body {
            padding: 2rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 191, 165, 0.25);
        }

        .btn-login {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: #00897B;
            transform: translateY(-2px);
        }

        .premium-badge {
            background: linear-gradient(45deg, #FFD700, #FFA500);
            color: #000;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-right: 8px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="login-card">
                <div class="login-header">
                    <i class="fas fa-headphones fa-3x mb-3"></i>
                    <h3>پنل مدیریت شنوا</h3>
                    <p class="mb-0">
                        <span class="premium-badge">
                            <i class="fas fa-crown me-1"></i>
                            ویژه
                        </span>
                        دسترسی محدود
                    </p>
                </div>

                <div class="login-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">نام کاربری یا ایمیل</label>
                            <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-user text-muted"></i>
                                    </span>
                                <input type="text"
                                       class="form-control border-start-0"
                                       id="username"
                                       name="username"
                                       required
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                       placeholder="نام کاربری یا ایمیل خود را وارد کنید">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">رمز عبور</label>
                            <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-lock text-muted"></i>
                                    </span>
                                <input type="password"
                                       class="form-control border-start-0"
                                       id="password"
                                       name="password"
                                       required
                                       placeholder="رمز عبور خود را وارد کنید">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-login w-100 btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            ورود به پنل
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>توجه:</strong> فقط کاربران ویژه می‌توانند وارد پنل مدیریت شوند.
                        </div>

                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            دسترسی امن به مدیریت سیستم
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Focus on username field on page load
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('username').focus();
    });
</script>
</body>
</html>