<?php
/**
 * Shenava - Admin Sidebar
 * Navigation menu for admin panel
 */

// Get the current page for active state
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!-- Sidebar -->
<div class="col-md-3 col-lg-2 sidebar p-0">
    <div class="p-4">
        <div class="text-center mb-4">
            <i class="fas fa-headphones fa-2x mb-2"></i>
            <h6 class="mb-0">شنوا</h6>
            <small class="text-black-50">پنل مدیریت</small>
        </div>

        <nav class="nav flex-column">
            <a class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>" href="../../index.php">
                <i class="fas fa-tachometer-alt me-2"></i>
                داشبورد
            </a>

            <a class="nav-link <?php echo ($currentDir == 'books') ? 'active' : ''; ?>" href="../../pages/books/list.php">
                <i class="fas fa-book me-2"></i>
                مدیریت کتاب‌ها
            </a>

            <a class="nav-link <?php echo ($currentDir == 'categories') ? 'active' : ''; ?>"
               href="../../pages/categories/list.php">
                <i class="fas fa-folder me-2"></i>
                دسته‌بندی‌ها
            </a>

            <a class="nav-link <?php echo ($currentDir == 'users') ? 'active' : ''; ?>" href="../../pages/users/list.php">
                <i class="fas fa-users me-2"></i>
                کاربران
            </a>

            <a class="nav-link <?php echo ($currentDir == 'authors') ? 'active' : ''; ?>"
               href="../../pages/authors/list.php">
                <i class="fas fa-user-edit me-2"></i>
                نویسندگان
            </a>

            <a class="nav-link <?php echo ($currentDir == 'chapters') ? 'active' : ''; ?>"
               href="../../pages/chapters/list.php">
                <i class="fas fa-list me-2"></i>
                فصل‌ها
            </a>

            <a class="nav-link <?php echo ($currentDir == 'reviews') ? 'active' : ''; ?>"
               href="../../pages/reviews/list.php">
                <i class="fas fa-star me-2"></i>
                نظرات
            </a>

            <div class="sidebar-divider my-3"></div>

            <a class="nav-link <?php echo ($currentDir == 'settings') ? 'active' : ''; ?>"
               href="../../pages/settings/general.php">
                <i class="fas fa-cog me-2"></i>
                تنظیمات
            </a>

            <a class="nav-link <?php echo ($currentDir == 'reports') ? 'active' : ''; ?>"
               href="../../pages/reports/dashboard.php">
                <i class="fas fa-chart-bar me-2"></i>
                گزارشات
            </a>
        </nav>
    </div>
</div>

<style>
    .sidebar-divider {
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        margin: 1rem 0;
    }
</style>