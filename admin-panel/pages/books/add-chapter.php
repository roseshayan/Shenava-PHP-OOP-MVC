<?php
/**
 * Shenava - Add Chapter to Book
 * This is the file referenced in chapters.php
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
    // Get the next chapter number
    $db->query("SELECT MAX(chapter_number) as max_number FROM chapters WHERE book_id = :book_id");
    $db->bind(':book_id', $bookId);
    $result = $db->single();
} catch (Exception $e) {
    echo $e->getMessage();
}
$nextChapterNumber = ($result->max_number ?? 0) + 1;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $chapterNumber = intval($_POST['chapter_number']);
    $duration = intval($_POST['duration']);
    $isFree = isset($_POST['is_free']) ? 1 : 0;
    $sortOrder = intval($_POST['sort_order'] ?? 0);

    // Handle audio file upload
    $audioUrl = null;
    $fileSize = 0;

    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../../assets/audio/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileExtension = pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['mp3', 'wav', 'm4a', 'ogg'];

        if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
            $errors[] = 'فرمت فایل مجاز نیست. فقط MP3, WAV, M4A, OGG مجاز هستند.';
        } elseif ($_FILES['audio_file']['size'] > 50 * 1024 * 1024) { // 50MB
            $errors[] = 'حجم فایل نباید بیشتر از 50 مگابایت باشد.';
        } else {
            $fileName = 'book_' . $bookId . '_chapter_' . $chapterNumber . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $filePath)) {
                $audioUrl = '/assets/audio/' . $fileName;
                $fileSize = $_FILES['audio_file']['size'];
            } else {
                $errors[] = 'خطا در آپلود فایل';
            }
        }
    } else {
        $errors[] = 'لطفا فایل صوتی را انتخاب کنید';
    }

    // Validate other fields
    if (empty($title)) {
        $errors[] = 'عنوان فصل الزامی است';
    }

    if ($chapterNumber < 1) {
        $errors[] = 'شماره فصل باید بزرگتر از 0 باشد';
    }

    if ($duration < 1) {
        $errors[] = 'مدت زمان باید بزرگتر از 0 باشد';
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

        try {
            // Insert chapter
            $db->query("INSERT INTO chapters (uuid, book_id, title, chapter_number, audio_url, duration, file_size, is_free, sort_order) 
                   VALUES (:uuid, :book_id, :title, :chapter_number, :audio_url, :duration, :file_size, :is_free, :sort_order)");

            $db->bind(':uuid', $uuid);
            $db->bind(':book_id', $bookId);
            $db->bind(':title', $title);
            $db->bind(':chapter_number', $chapterNumber);
            $db->bind(':audio_url', $audioUrl);
            $db->bind(':duration', $duration);
            $db->bind(':file_size', $fileSize);
            $db->bind(':is_free', $isFree);
            $db->bind(':sort_order', $sortOrder);

            if ($db->execute()) {
                $_SESSION['success'] = 'فصل با موفقیت ایجاد شد';
                header("Location: chapters.php?book_id=$bookId");
                exit;
            } else {
                $errors[] = 'خطا در ایجاد فصل';
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

$pageTitle = "افزودن فصل جدید - {$book->title}";
?>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">افزودن فصل جدید به: <?php echo $book->title; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="chapters.php?book_id=<?php echo $bookId; ?>" class="btn btn-outline-secondary">
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

            <!-- Add Chapter Form -->
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
                                            <label for="title" class="form-label">عنوان فصل *</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="title"
                                                   name="title"
                                                   value="<?php echo $_POST['title'] ?? ''; ?>"
                                                   required
                                                   placeholder="عنوان فصل را وارد کنید">
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="chapter_number" class="form-label">شماره فصل *</label>
                                                    <input type="number"
                                                           class="form-control"
                                                           id="chapter_number"
                                                           name="chapter_number"
                                                           value="<?php echo $_POST['chapter_number'] ?? $nextChapterNumber; ?>"
                                                           min="1"
                                                           required>
                                                    <div class="form-text">شماره فصل در ترتیب نمایش</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="duration" class="form-label">مدت زمان (ثانیه) *</label>
                                                    <input type="number"
                                                           class="form-control"
                                                           id="duration"
                                                           name="duration"
                                                           value="<?php echo $_POST['duration'] ?? ''; ?>"
                                                           min="1"
                                                           required
                                                           placeholder="3600 برای 1 ساعت">
                                                    <div class="form-text">مدت زمان فصل به ثانیه</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="sort_order" class="form-label">ترتیب نمایش</label>
                                            <input type="number"
                                                   class="form-control"
                                                   id="sort_order"
                                                   name="sort_order"
                                                   value="<?php echo $_POST['sort_order'] ?? $nextChapterNumber; ?>"
                                                   min="0">
                                            <div class="form-text">برای تغییر ترتیب نمایش فصل‌ها استفاده می‌شود</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar -->
                            <div class="col-md-4">
                                <!-- Audio File -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-file-audio me-2"></i>
                                            فایل صوتی
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="audio_file" class="form-label">آپلود فایل صوتی *</label>
                                            <input type="file"
                                                   class="form-control"
                                                   id="audio_file"
                                                   name="audio_file"
                                                   accept=".mp3,.wav,.m4a,.ogg"
                                                   required>
                                            <div class="form-text">
                                                فرمت‌های مجاز: MP3, WAV, M4A, OGG<br>
                                                حداکثر سایز: 50MB
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
                                                <?php echo isset($_POST['is_free']) ? 'checked' : 'checked'; ?>>
                                            <label class="form-check-label" for="is_free">
                                                فصل رایگان
                                            </label>
                                        </div>
                                        <div class="form-text">
                                            اگر غیرفعال باشد، کاربران برای گوش دادن نیاز به خرید دارند
                                        </div>
                                    </div>
                                </div>

                                <!-- Duration Helper -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-clock me-2"></i>
                                            راهنمای مدت زمان
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <small class="text-muted">
                                            <strong>مثال‌ها:</strong><br>
                                            60 = 1 دقیقه<br>
                                            300 = 5 دقیقه<br>
                                            600 = 10 دقیقه<br>
                                            1800 = 30 دقیقه<br>
                                            3600 = 1 ساعت<br>
                                            7200 = 2 ساعت
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="chapters.php?book_id=<?php echo $bookId; ?>"
                                       class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>
                                        انصراف
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        ایجاد فصل
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

        // Duration calculator
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
        });

        // Form validation
        $('#chapterForm').on('submit', function () {
            const title = $('#title').val().trim();
            const chapterNumber = $('#chapter_number').val();
            const duration = $('#duration').val();
            const audioFile = $('#audio_file').val();

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

            if (!audioFile) {
                ShenavaAdmin.showToast('لطفا فایل صوتی را انتخاب کنید', 'error');
                return false;
            }

            ShenavaAdmin.setButtonLoading($(this).find('button[type="submit"]'), true);
            return true;
        });
    });
</script>