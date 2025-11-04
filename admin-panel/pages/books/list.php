<?php
/**
 * Shenava - Books Management
 */

session_start();
require_once '../../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';
require_once BACKEND_PATH . '/app/core/Model.php';
require_once BACKEND_PATH . '/app/models/BookModel.php';
require_once BACKEND_PATH . '/app/models/CategoryModel.php';

$bookModel = new BookModel();
$categoryModel = new CategoryModel();

// Get filters
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

// Build query
$where = "WHERE b.is_active = 1";
$params = [];

if ($search) {
    $where .= " AND (b.title LIKE :search OR a.name LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($category) {
    $where .= " AND c.slug = :category";
    $params[':category'] = $category;
}

try {
    // Get books
    $sql = "SELECT b.*, a.name as author_name, c.name as category_name, c.slug as category_slug
        FROM books b
        LEFT JOIN authors a ON b.author_id = a.id
        LEFT JOIN categories c ON b.category_id = c.id
        $where
        ORDER BY b.created_at DESC
        LIMIT $limit OFFSET $offset";

    $db = new Database();
    $db->query($sql);
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }
    $books = $db->resultSet();
} catch (Exception $e) {
    die($e->getMessage());
}

try {
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM books b
             LEFT JOIN authors a ON b.author_id = a.id
             LEFT JOIN categories c ON b.category_id = c.id
             $where";
    $db->query($countSql);
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }
    $totalBooks = $db->single()->total;
    $totalPages = ceil($totalBooks / $limit);
} catch (Exception $e) {
    die($e->getMessage());
}

// Get categories for filter
$categories = $categoryModel->getCategories();

$pageTitle = "مدیریت کتاب‌ها - شنوا";
?>
<?php include '../../includes/header.php'; ?>
<style>
    .table-actions {
        white-space: nowrap;
    }

    .book-cover {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">مدیریت کتاب‌ها</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        افزودن کتاب جدید
                    </a>
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
                                   placeholder="جستجو در عنوان یا نویسنده..."
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select aria-label="category" name="category" class="form-select">
                                <option value="">همه دسته‌بندی‌ها</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat->slug; ?>"
                                            <?php echo $category === $cat->slug ? 'selected' : ''; ?>>
                                        <?php echo $cat->name; ?>
                                    </option>
                                <?php endforeach; ?>
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

            <!-- Books Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="booksTable">
                            <thead>
                            <tr>
                                <th>کاور</th>
                                <th>عنوان</th>
                                <th>نویسنده</th>
                                <th>دسته‌بندی</th>
                                <th>وضعیت</th>
                                <th>تاریخ</th>
                                <th>عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($books as $book): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo $book->cover_image ?: '../../../assets/images/default-book.jpg'; ?>"
                                             alt="<?php echo $book->title; ?>"
                                             class="book-cover">
                                    </td>
                                    <td>
                                        <strong><?php echo $book->title; ?></strong>
                                        <?php if ($book->is_featured): ?>
                                            <span class="badge bg-warning me-2">ویژه</span>
                                        <?php endif; ?>
                                        <?php if ($book->is_free): ?>
                                            <span class="badge bg-success">رایگان</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $book->author_name ?: '---'; ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $book->category_name; ?></span>
                                    </td>
                                    <td>
                                            <span class="badge bg-<?php echo $book->is_active ? 'success' : 'secondary'; ?>">
                                                <?php echo $book->is_active ? 'فعال' : 'غیرفعال'; ?>
                                            </span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo date('Y/m/d', strtotime($book->created_at)); ?></small>
                                    </td>
                                    <td class="table-actions">
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit.php?id=<?php echo $book->id; ?>"
                                               class="btn btn-outline-primary"
                                               title="ویرایش">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="chapters.php?book_id=<?php echo $book->id; ?>"
                                               class="btn btn-outline-info"
                                               title="مدیریت فصل‌ها">
                                                <i class="fas fa-list"></i>
                                            </a>
                                            <a href="../../actions/toggle-status.php?type=book&id=<?php echo $book->id; ?>"
                                               class="btn btn-outline-<?php echo $book->is_active ? 'warning' : 'success'; ?>"
                                               title="<?php echo $book->is_active ? 'غیرفعال کردن' : 'فعال کردن'; ?>">
                                                <i class="fas fa-<?php echo $book->is_active ? 'eye-slash' : 'eye'; ?>"></i>
                                            </a>
                                            <a href="../../actions/delete.php?type=book&id=<?php echo $book->id; ?>"
                                               class="btn btn-outline-danger btn-delete"
                                               title="حذف"
                                               data-confirm="آیا از حذف این کتاب مطمئن هستید؟">
                                                <i class="fas fa-trash"></i>
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
                                           href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>">
                                            قبلی
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link"
                                           href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                           href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>">
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

<?php include '../../includes/footer.php'; ?>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#booksTable').DataTable({
            language: {
                url: '../../js/Persian.json'
            },
            ordering: false, // Disable default sorting
            info: false, // Remove "Showing X of Y entries"
            lengthChange: false, // Remove entries per page
            paging: false // Remove DataTable pagination (using custom)
        });
    });
</script>