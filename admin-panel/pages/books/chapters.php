<?php
/**
 * Shenava - Book Chapters Management
 */

session_start();
require_once '../../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';

$db = new Database();

// Get book ID
$bookId = intval($_GET['book_id'] ?? 0);
if (!$bookId) {
    header('Location: list.php');
    exit;
}

try {
    // Get book info
    $db->query("SELECT id, title FROM books WHERE id = :id");
    $db->bind(':id', $bookId);
    $book = $db->single();
} catch (Exception $e) {
    echo $e->getMessage();
}

if (!$book) {
    header('Location: list.php');
    exit;
}

try {
    // Get chapters
    $db->query("SELECT * FROM chapters WHERE book_id = :book_id ORDER BY sort_order, chapter_number");
    $db->bind(':book_id', $bookId);
    $chapters = $db->resultSet();
} catch (Exception $e) {
    echo $e->getMessage();
}

// Handle chapter actions
if (isset($_GET['action']) && isset($_GET['chapter_id'])) {
    $action = $_GET['action'];
    $chapterId = intval($_GET['chapter_id']);

    if ($action == 'delete') {
        try {
            $db->query("DELETE FROM chapters WHERE id = :id");
            $db->bind(':id', $chapterId);
            $db->execute();
            $_SESSION['success'] = 'فصل با موفقیت حذف شد';
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        header("Location: chapters.php?book_id=$bookId");
        exit;
    }
}

$pageTitle = "مدیریت فصل‌ها - شنوا";
?>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">مدیریت فصل‌های: <?php echo $book->title; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="add-chapter.php?book_id=<?php echo $bookId; ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        افزودن فصل جدید
                    </a>
                    <a href="list.php" class="btn btn-outline-secondary ms-2">
                        <i class="fas fa-arrow-right me-2"></i>
                        بازگشت به لیست کتاب‌ها
                    </a>
                </div>
            </div>

            <!-- Notifications -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Chapters List -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($chapters)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-headphones fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">هنوز فصلی اضافه نشده است</h4>
                            <p class="text-muted">اولین فصل این کتاب را ایجاد کنید</p>
                            <a href="add-chapter.php?book_id=<?php echo $bookId; ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                افزودن فصل
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>عنوان فصل</th>
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
                                                <a href="../chapters/edit.php?id=<?php echo $chapter->id; ?>"
                                                   class="btn btn-outline-primary"
                                                   title="ویرایش">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="chapters.php?book_id=<?php echo $bookId; ?>&action=delete&chapter_id=<?php echo $chapter->id; ?>"
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
                    <?php endif; ?>
                </div>
            </div>

            <!-- Book Summary -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">خلاصه کتاب</h6>
                            <div class="d-flex justify-content-between text-muted mb-2">
                                <span>تعداد فصل‌ها:</span>
                                <span><?php echo count($chapters); ?></span>
                            </div>
                            <div class="d-flex justify-content-between text-muted mb-2">
                                <span>کل مدت زمان:</span>
                                <span>
                                        <?php
                                        $totalDuration = 0;
                                        foreach ($chapters as $chapter) {
                                            $totalDuration += $chapter->duration;
                                        }
                                        $hours = floor($totalDuration / 3600);
                                        $minutes = floor(($totalDuration % 3600) / 60);
                                        echo $hours > 0 ? "$hours ساعت و $minutes دقیقه" : "$minutes دقیقه";
                                        ?>
                                    </span>
                            </div>
                            <div class="d-flex justify-content-between text-muted">
                                <span>فصل‌های رایگان:</span>
                                <span>
                                        <?php
                                        $freeChapters = 0;
                                        foreach ($chapters as $chapter) {
                                            if ($chapter->is_free) $freeChapters++;
                                        }
                                        echo $freeChapters;
                                        ?>
                                    </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>