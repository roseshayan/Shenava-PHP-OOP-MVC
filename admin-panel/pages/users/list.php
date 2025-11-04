<?php
/**
 * Shenava - Users Management
 */

session_start();
require_once '../../includes/auth-check.php';
require_once '../../../backend/app/models/UserModel.php';

$userModel = new UserModel();

// Get pagination and filters
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// Build query
$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (username LIKE :search OR email LIKE :search OR display_name LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($status === 'active') {
    $where .= " AND is_active = 1";
} elseif ($status === 'inactive') {
    $where .= " AND is_active = 0";
} elseif ($status === 'premium') {
    $where .= " AND is_premium = 1";
}

try {
    // Get users
    $sql = "SELECT * FROM users $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $db = new Database();
    $db->query($sql);
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }
    $users = $db->resultSet();
} catch (Exception $e) {
    echo $e->getMessage();
}

try {
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM users $where";
    $db->query($countSql);
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }
    $totalUsers = $db->single()->total;
} catch (Exception $e) {
    echo $e->getMessage();
}
$totalPages = ceil($totalUsers / $limit);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت کاربران - شنوا</title>

    <!-- Bootstrap 5 CSS -->
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../node_modules/bootstrap-icons/font/bootstrap-icons.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../../node_modules/@fortawesome/fontawesome-free/css/all.min.css">

    <!-- Vazir Font -->
    <link href="../../../node_modules/vazirmatn/misc/Farsi-Digits/Vazirmatn-FD-font-face.min.css" rel="stylesheet">

    <!-- DataTables -->
    <link rel="stylesheet" href="../../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
</head>
<body>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">مدیریت کاربران</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="exportUsers">
                            <i class="fas fa-download me-2"></i>
                            خروجی
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card border-0 bg-primary text-white">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0"><?php echo number_format($totalUsers); ?></h4>
                                    <small>کل کاربران</small>
                                </div>
                                <i class="fas fa-users fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                try {
                    // Get active users count
                    $db->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
                    $activeUsers = $db->single()->count;
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                ?>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card border-0 bg-success text-white">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0"><?php echo number_format($activeUsers); ?></h4>
                                    <small>کاربران فعال</small>
                                </div>
                                <i class="fas fa-user-check fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                try {
                    // Get premium users count
                    $db->query("SELECT COUNT(*) as count FROM users WHERE is_premium = 1");
                    $premiumUsers = $db->single()->count;
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                ?>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card border-0 bg-warning text-dark">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0"><?php echo number_format($premiumUsers); ?></h4>
                                    <small>کاربران ویژه</small>
                                </div>
                                <i class="fas fa-crown fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                try {
                    // Get today registered users
                    $db->query("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()");
                    $todayUsers = $db->single()->count;
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                ?>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card border-0 bg-info text-white">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0"><?php echo number_format($todayUsers); ?></h4>
                                    <small>ثبت‌نام امروز</small>
                                </div>
                                <i class="fas fa-user-plus fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input aria-label="search" type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="جستجو در نام کاربری، ایمیل یا نام نمایشی..."
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select aria-label="status" name="status" class="form-select">
                                <option value="">همه وضعیت‌ها</option>
                                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>فعال
                                </option>
                                <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>
                                    غیرفعال
                                </option>
                                <option value="premium" <?php echo $status === 'premium' ? 'selected' : ''; ?>>کاربران
                                    ویژه
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>
                                فیلتر
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="list.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-2"></i>
                                پاک کردن
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead>
                            <tr>
                                <th>کاربر</th>
                                <th>اطلاعات</th>
                                <th>وضعیت</th>
                                <th>تنظیمات</th>
                                <th>تاریخ ثبت‌نام</th>
                                <th>عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($user->avatar_url): ?>
                                                <img src="<?php echo $user->avatar_url; ?>"
                                                     alt="<?php echo $user->username; ?>"
                                                     class="rounded-circle me-3"
                                                     width="40" height="40" style="object-fit: cover;">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center me-3"
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                            <?php endif; ?>

                                            <div>
                                                <strong><?php echo $user->username; ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo $user->email; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <small>
                                                <strong>نام نمایشی:</strong>
                                                <?php echo $user->display_name ?: '---'; ?>
                                            </small>
                                            <br>
                                            <small>
                                                <strong>UUID:</strong>
                                                <code class="text-muted"><?php echo substr($user->uuid, 0, 8) . '...'; ?></code>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                                <span class="badge bg-<?php echo $user->is_active ? 'success' : 'secondary'; ?>">
                                                    <?php echo $user->is_active ? 'فعال' : 'غیرفعال'; ?>
                                                </span>
                                            <?php if ($user->is_premium): ?>
                                                <span class="badge bg-warning">کاربر ویژه</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php if ($user->dark_mode): ?>
                                                <span class="badge bg-dark" title="حالت تاریک">
                                                    <i class="fas fa-moon"></i>
                                                </span>
                                            <?php endif; ?>

                                            <?php if ($user->sleep_timer_enabled): ?>
                                                <span class="badge bg-info" title="تایمر خواب">
                                                    <i class="fas fa-bed"></i>
                                                </span>
                                            <?php endif; ?>

                                            <?php if ($user->driving_mode): ?>
                                                <span class="badge bg-primary" title="حالت رانندگی">
                                                    <i class="fas fa-car"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('Y/m/d', strtotime($user->created_at)); ?>
                                            <br>
                                            <span class="text-muted"><?php echo date('H:i', strtotime($user->created_at)); ?></span>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary"
                                                    title="مشاهده پروفایل"
                                                    onclick="viewUser(<?php echo $user->id; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-warning"
                                                    title="ویرایش کاربر"
                                                    onclick="editUser(<?php echo $user->id; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="../../actions/toggle-status.php?type=user&id=<?php echo $user->id; ?>"
                                               class="btn btn-outline-<?php echo $user->is_active ? 'secondary' : 'success'; ?>"
                                               title="<?php echo $user->is_active ? 'غیرفعال کردن' : 'فعال کردن'; ?>">
                                                <i class="fas fa-<?php echo $user->is_active ? 'ban' : 'check'; ?>"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                           href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                                            قبلی
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link"
                                           href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                           href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                                            بعدی
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Scripts -->
<script src="../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../../node_modules/jquery/dist/jquery.min.js"></script>
<script src="../../../node_modules/datatables.net/js/dataTables.min.js"></script>
<script src="../../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<script src="../../js/app.js"></script>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#usersTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fa.json'
            },
            ordering: false,
            info: false,
            lengthChange: false,
            paging: false
        });

        // Export users
        $('#exportUsers').on('click', function () {
            ShenavaAdmin.showToast('در حال آماده‌سازی خروجی...', 'info');
            // Implement export functionality here
        });
    });

    function viewUser(userId) {
        ShenavaAdmin.showToast('در حال بارگذاری اطلاعات کاربر...', 'info');
        // Implement view user modal
    }

    function editUser(userId) {
        window.location.href = 'edit.php?id=' + userId;
    }
</script>
</body>
</html>