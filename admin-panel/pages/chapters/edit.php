<?php
/**
 * Shenava - Edit Chapter
 */
session_save_path('/tmp');
session_start();
require_once '../../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';

$db = new Database();

// Get chapter ID
$chapterId = intval($_GET['id'] ?? 0);
if (!$chapterId) {
    header('Location: ../books/list.php');
    exit;
}

try {
    // Get chapter data with book info
    $db->query("SELECT c.*, b.title as book_title, b.id as book_id 
           FROM chapters c 
           JOIN books b ON c.book_id = b.id 
           WHERE c.id = :id");
    $db->bind(':id', $chapterId);
    $chapter = $db->single();
} catch (Exception $e) {
    echo $e->getMessage();
}

if (!$chapter) {
    header('Location: ../books/list.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $chapterNumber = intval($_POST['chapter_number']);
    $duration = intval($_POST['duration']);
    $isFree = isset($_POST['is_free']) ? 1 : 0;
    $sortOrder = intval($_POST['sort_order'] ?? 0);

    $errors = [];

    if (empty($title)) {
        $errors[] = 'عنوان فصل الزامی است';
    }

    if ($chapterNumber < 1) {
        $errors[] = 'شماره فصل باید بزرگتر از 0 باشد';
    }

    if ($duration < 1) {
        $errors[] = 'مدت زمان باید بزرگتر از 0 باشد';
    }

    // Handle audio file upload if new file is provided
    $audioUrl = $chapter->audio_url;
    $fileSize = $chapter->file_size;

    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../../assets/audio/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileExtension = pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['mp3', 'wav', 'm4a', 'ogg'];

        if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
            $errors[] = 'فرمت فایل مجاز نیست. فقط MP3, WAV, M4A, OGG مجاز هستند.';
        } elseif ($_FILES['audio_file']['size'] > 50 * 1024 * 1024) {
            $errors[] = 'حجم فایل نباید بیشتر از 50 مگابایت باشد.';
        } else {
            // Delete old file if exists
            if ($audioUrl && file_exists('../../..' . $audioUrl)) {
                unlink('../../..' . $audioUrl);
            }

            $fileName = 'book_' . $chapter->book_id . '_chapter_' . $chapterNumber . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $filePath)) {
                $audioUrl = '/assets/audio/' . $fileName;
                $fileSize = $_FILES['audio_file']['size'];
            } else {
                $errors[] = 'خطا در آپلود فایل';
            }
        }
    }

    if (empty($errors)) {
        try {
            // Update chapter
            $db->query("UPDATE chapters SET 
                    title = :title,
                    chapter_number = :chapter_number,
                    audio_url = :audio_url,
                    duration = :duration,
                    file_size = :file_size,
                    is_free = :is_free,
                    sort_order = :sort_order,
                    updated_at = NOW()
                    WHERE id = :id");

            $db->bind(':title', $title);
            $db->bind(':chapter_number', $chapterNumber);
            $db->bind(':audio_url', $audioUrl);
            $db->bind(':duration', $duration);
            $db->bind(':file_size', $fileSize);
            $db->bind(':is_free', $isFree);
            $db->bind(':sort_order', $sortOrder);
            $db->bind(':id', $chapterId);

            if ($db->execute()) {
                $_SESSION['success'] = 'فصل با موفقیت به‌روزرسانی شد';
                header("Location: ../books/chapters.php?book_id=" . $chapter->book_id);
                exit;
            } else {
                $errors[] = 'خطا در به‌روزرسانی فصل';
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

$pageTitle = "ویرایش فصل - $chapter->title";
?>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">ویرایش فصل: <?php echo $chapter->title; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="../books/chapters.php?book_id=<?php echo $chapter->book_id; ?>"
                       class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>
                        بازگشت به فصل‌ها
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

            <!-- Edit Chapter Form -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="chapterForm">
                        <div class="row g-4">
                            <!-- Chapter Information -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-info-circle me-2"></i>
                                            اطلاعات فصل
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">کتاب</label>
                                            <input aria-label="bookTitle" type="text" class="form-control"
                                                   value="<?php echo $chapter->book_title; ?>" readonly>
                                        </div>

                                        <div class="mb-3">
                                            <label for="title" class="form-label">عنوان فصل *</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="title"
                                                   name="title"
                                                   value="<?php echo $chapter->title; ?>"
                                                   required>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="chapter_number" class="form-label">شماره فصل *</label>
                                                    <input type="number"
                                                           class="form-control"
                                                           id="chapter_number"
                                                           name="chapter_number"
                                                           value="<?php echo $chapter->chapter_number; ?>"
                                                           min="1"
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="duration" class="form-label">مدت زمان (ثانیه) *</label>
                                                    <input type="number"
                                                           class="form-control"
                                                           id="duration"
                                                           name="duration"
                                                           value="<?php echo $chapter->duration; ?>"
                                                           min="1"
                                                           required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="sort_order" class="form-label">ترتیب نمایش</label>
                                            <input type="number"
                                                   class="form-control"
                                                   id="sort_order"
                                                   name="sort_order"
                                                   value="<?php echo $chapter->sort_order; ?>"
                                                   min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar -->
                            <div class="col-md-4">
                                <!-- Current Audio File -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-file-audio me-2"></i>
                                            فایل صوتی فعلی
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($chapter->audio_url): ?>
                                            <div class="text-center mb-3">
                                                <i class="fas fa-music fa-3x text-primary mb-2"></i>
                                                <p class="mb-1">فایل صوتی موجود</p>
                                                <small class="text-muted">
                                                    <?php
                                                    $fileSize = $chapter->file_size;
                                                    if ($fileSize >= 1048576) {
                                                        echo number_format($fileSize / 1048576, 2) . ' MB';
                                                    } else {
                                                        echo number_format($fileSize / 1024, 2) . ' KB';
                                                    }
                                                    ?>
                                                </small>
                                            </div>
                                            <div class="d-grid gap-2">
                                                <a href="<?php echo $chapter->audio_url; ?>"
                                                   class="btn btn-outline-primary btn-sm"
                                                   target="_blank">
                                                    <i class="fas fa-play me-1"></i>
                                                    پخش فایل
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center text-muted">
                                                <i class="fas fa-times-circle fa-3x mb-2"></i>
                                                <p>فایل صوتی موجود نیست</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- New Audio File -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-upload me-2"></i>
                                            فایل صوتی جدید
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="audio_file" class="form-label">آپلود فایل جدید</label>
                                            <input type="file"
                                                   class="form-control"
                                                   id="audio_file"
                                                   name="audio_file"
                                                   accept=".mp3,.wav,.m4a,.ogg">
                                            <div class="form-text">
                                                در صورت انتخاب، فایل قبلی جایگزین می‌شود
                                            </div>
                                        </div>

                                        <div id="audioInfo" class="mt-3" style="display: none;">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <span id="fileInfo"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Settings -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-cog me-2"></i>
                                            تنظیمات
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="is_free"
                                                   name="is_free"
                                                <?php echo $chapter->is_free ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_free">
                                                فصل رایگان
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Chapter Stats -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-chart-bar me-2"></i>
                                            آمار فصل
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between text-muted small mb-2">
                                            <span>تعداد پخش:</span>
                                            <span><?php echo $chapter->plays_count; ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between text-muted small">
                                            <span>تاریخ ایجاد:</span>
                                            <span><?php echo date('Y/m/d', strtotime($chapter->created_at)); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="../books/chapters.php?book_id=<?php echo $chapter->book_id; ?>"
                                       class="btn btn-outline-secondary">
                                        انصراف
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        ذخیره تغییرات
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
    $(document).ready(function () {
        // Show file info
        $('#audio_file').on('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                const fileType = file.name.split('.').pop().toUpperCase();
                $('#fileInfo').text(`فایل: ${file.name} (${fileSize} MB) - نوع: ${fileType}`);
                $('#audioInfo').show();

                // Validate file size
                if (file.size > 50 * 1024 * 1024) {
                    ShenavaAdmin.showToast('حجم فایل بیشتر از 50 مگابایت است', 'error');
                    $(this).val('');
                    $('#audioInfo').hide();
                }
            }
        });

        // Duration display
        $('#duration').on('blur', function () {
            const seconds = parseInt($(this).val());
            if (seconds > 0) {
                const hours = Math.floor(seconds / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                const remainingSeconds = seconds % 60;

                let timeString = '';
                if (hours > 0) timeString += `${hours} ساعت و `;
                if (minutes > 0) timeString += `${minutes} دقیقه و `;
                timeString += `${remainingSeconds} ثانیه`;

                $(this).next('.form-text').text(`مدت زمان: ${timeString}`);
            }
        }).trigger('blur');

        // Form validation
        $('#chapterForm').on('submit', function () {
            const title = $('#title').val().trim();
            const chapterNumber = $('#chapter_number').val();
            const duration = $('#duration').val();

            if (!title) {
                ShenavaAdmin.showToast('لطفا عنوان فصل را وارد کنید', 'error');
                return false;
            }

            if (!chapterNumber || chapterNumber < 1) {
                ShenavaAdmin.showToast('شماره فصل باید بزرگتر از 0 باشد', 'error');
                return false;
            }

            if (!duration || duration < 1) {
                ShenavaAdmin.showToast('مدت زمان باید بزرگتر از 0 باشد', 'error');
                return false;
            }

            ShenavaAdmin.setButtonLoading($(this).find('button[type="submit"]'), true);
            return true;
        });
    });
</script>