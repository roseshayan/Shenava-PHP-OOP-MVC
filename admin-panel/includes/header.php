<?php
/**
 * Shenava - Admin Header
 * Includes all CSS and JS dependencies
 */
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'پنل مدیریت شنوا'; ?></title>

    <!-- Meta Description -->
    <meta name="description" content="شنوا، کتابخانه‌ای آنلاین از کتاب‌های صوتی رایگان است. در شنوا می‌توانید انواع داستان‌های شنیدنی، رمان‌ها، ادبیات کلاسیک و آثار فانتزی را به‌صورت رایگان گوش دهید. هرجا و هرزمان با شنوا همراه باشید.">

    <!-- Meta Keywords -->
    <meta name="keywords" content="شنوا, کتاب صوتی, رایگان, اپلیکیشن کتاب صوتی, داستان صوتی, رمان صوتی, شنیدن کتاب, Shenava, Free Audiobooks">

    <!-- Open Graph (برای اشتراک‌گذاری در شبکه‌های اجتماعی) -->
    <meta property="og:title" content="شنوا | کتاب‌های صوتی رایگان و داستانی">
    <meta property="og:description" content="با شنوا، کتاب‌ها را بشنوید. مجموعه‌ای از داستان‌ها و رمان‌های شنیدنی، همه رایگان و همیشه در دسترس.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://shennava.ir">
    <meta property="og:image" content="https://shennava.ir/admin-panel/img/logo.png">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="شنوا | کتاب‌های صوتی رایگان و داستانی">
    <meta name="twitter:description" content="کتاب‌ها رو بشنو، دنیایی تازه بساز.">
    <meta name="twitter:image" content="https://shennava.ir/admin-panel/img/logo.png">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../img/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="../../img/favicon/favicon.svg" />
    <link rel="shortcut icon" href="../../img/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="../../img/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Shennava" />
    <link rel="manifest" href="../../img/favicon/site.webmanifest" />

    <!-- Bootstrap 5 CSS -->
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../node_modules/bootstrap-icons/font/bootstrap-icons.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../../node_modules/@fortawesome/fontawesome-free/css/all.min.css">

    <!-- Vazir Font -->
    <link href="../../../node_modules/vazirmatn/misc/Farsi-Digits/Vazirmatn-FD-font-face.min.css" rel="stylesheet">

    <!-- DataTables -->
    <link rel="stylesheet" href="../../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">

    <!-- Chart.Js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- @simonwep/pickr -->
    <link rel="stylesheet" href="../../../node_modules/@simonwep/pickr/dist/themes/classic.min.css">

    <link rel="stylesheet" href="../../css/style.css">

    <style>
        :root {
            --primary-color: #00BFA5;
            --accent-color: #FF7043;
            --bg-light: #E3F2FD;
            --text-primary: #212121;
            --text-secondary: #757575;
        }

        body {
            font-family: Vazirmatn FD, sans-serif;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-headphones"></i>
            شنوا - پنل مدیریت
        </a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3"><?php echo $_SESSION['admin_name'] ?? 'مدیر'; ?></span>
            <a href="/admin-panel/logout.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt"></i>
                خروج
            </a>
        </div>
    </div>
</nav>