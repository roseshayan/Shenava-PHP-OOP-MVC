<?php
/**
 * Shenava - Admin Login
 */

session_start();

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Simple hardcoded admin credentials (Change in production!)
    $admin_username = 'admin';
    $admin_password = 'admin123'; // Change this!

    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = 'مدیر سیستم';
        $_SESSION['login_time'] = time();

        header('Location: index.php');
        exit;
    } else {
        $error = 'نام کاربری یا رمز عبور اشتباه است';
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به پنل مدیریت - شنوا</title>

    <!-- Bootstrap 5 CSS -->
    <link href="../node_modules/bootstrap/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../node_modules/@fortawesome/fontawesome-free/css/all.min.css">

    <!-- Vazir Font -->
    <link href="../node_modules/vazirmatn/misc/Farsi-Digits/Vazirmatn-FD-font-face.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #00BFA5;
            --accent-color: #FF7043;
        }

        body {
            font-family: Vazirmatn FD, sans-serif;
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
                    <p class="mb-0">لطفا وارد شوید</p>
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
                            <label for="username" class="form-label">نام کاربری</label>
                            <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-user text-muted"></i>
                                    </span>
                                <input type="text"
                                       class="form-control border-start-0"
                                       id="username"
                                       name="username"
                                       required
                                       placeholder="نام کاربری خود را وارد کنید">
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
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            اطلاعات ورود پیش‌فرض: admin / admin123
                        </small>
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