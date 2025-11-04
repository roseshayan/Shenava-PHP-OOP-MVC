<?php
/**
 * Shenava - Chapters Management
 */

session_start();
require_once '../../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';

$db = new Database();

// Get filters
$book_id = $_GET['book_id'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where = "WHERE 1=1";
$params = [];

if ($book_id) {
    $where .= " AND c.book_id = :book_id";
    $params[':book_id'] = $book_id;
}

if ($search) {
    $where .= " AND (c.title LIKE :search OR b.title LIKE :search)";
    $params[':search'] = "%$search%";
}

try {
    // Get chapters
    $sql = "SELECT c.*, b.title as book_title, b.uuid as book_uuid
        FROM chapters c
        JOIN books b ON c.book_id = b.id
        $where
        ORDER BY c.book_id, c.sort_order, c.chapter_number
        LIMIT 50";

    $db->query($sql);
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }
    $chapters = $db->resultSet();
} catch (Exception $e) {
    echo $e->getMessage();
}

try {
    // Get books for filter
    $db->query("SELECT id, title FROM books WHERE is_active = 1 ORDER BY title");
    $books = $db->resultSet();
} catch (Exception $e) {
    echo $e->getMessage();
}

$pageTitle = "مدیریت فصل‌ها - شنوا";
?>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">مدیریت فصل‌ها و فایل‌های صوتی</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        افزودن فصل جدید
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <select aria-label="book_id" name="book_id" class="form-select">
                                <option value="">همه کتاب‌ها</option>
                                <?php foreach ($books as $book): ?>
                                    <option value="<?php echo $book->id; ?>"
                                        <?php echo $book_id == $book->id ? 'selected' : ''; ?>>
                                        <?php echo $book->title; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input aria-label="search" type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="جستجو در عنوان فصل یا کتاب..."
                                   value="<?php echo htmlspecialchars($search); ?>">
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

            <!-- Chapters Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="chaptersTable">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>عنوان فصل</th>
                                <th>کتاب</th>
                                <th>مدت زمان</th>
                                <th>سایز فایل</th>
                                <th>تعداد پخش</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($chapters as $chapter): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $chapter->chapter_number; ?></strong>
                                    </td>
                                    <td>
                                        <strong><?php echo $chapter->title; ?></strong>
                                        <?php if (!$chapter->is_free): ?>
                                            <span class="badge bg-warning me-2">غیر رایگان</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="../books/edit.php?id=<?php echo $chapter->book_id; ?>"
                                           class="text-decoration-none">
                                            <?php echo $chapter->book_title; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php
                                        $duration = $chapter->duration;
                                        $hours = floor($duration / 3600);
                                        $minutes = floor(($duration % 3600) / 60);
                                        $seconds = $duration % 60;

                                        if ($hours > 0) {
                                            echo sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                                        } else {
                                            echo sprintf("%02d:%02d", $minutes, $seconds);
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($chapter->file_size > 0) {
                                            $size = $chapter->file_size;
                                            if ($size >= 1073741824) {
                                                echo number_format($size / 1073741824, 2) . ' GB';
                                            } elseif ($size >= 1048576) {
                                                echo number_format($size / 1048576, 2) . ' MB';
                                            } elseif ($size >= 1024) {
                                                echo number_format($size / 1024, 2) . ' KB';
                                            } else {
                                                echo $size . ' B';
                                            }
                                        } else {
                                            echo '---';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $chapter->plays_count; ?></span>
                                    </td>
                                    <td>
                                            <span class="badge bg-<?php echo $chapter->is_free ? 'success' : 'warning'; ?>">
                                                <?php echo $chapter->is_free ? 'رایگان' : 'غیر رایگان'; ?>
                                            </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit.php?id=<?php echo $chapter->id; ?>"
                                               class="btn btn-outline-primary"
                                               title="ویرایش">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="../../actions/delete.php?type=chapter&id=<?php echo $chapter->id; ?>"
                                               class="btn btn-outline-danger btn-delete"
                                               title="حذف"
                                               data-confirm="آیا از حذف این فصل مطمئن هستید؟">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php if ($chapter->audio_url): ?>
                                                <a href="<?php echo $chapter->audio_url; ?>"
                                                   class="btn btn-outline-success"
                                                   title="پخش"
                                                   target="_blank">
                                                    <i class="fas fa-play"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
    $(document).ready(function () {
        $('#chaptersTable').DataTable({
            language: {
                url: '../../js/Persian.json'
            },
            order: [[0, 'asc']],
            pageLength: 25
        });
    });
</script>