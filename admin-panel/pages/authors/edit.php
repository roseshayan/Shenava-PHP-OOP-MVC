<?php
/**
 * Shenava - Edit Author
 */

session_start();
require_once '../../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
define('BACKEND_PATH', BASE_PATH . '/backend');
require_once BACKEND_PATH . '/app/core/Database.php';

$db = new Database();

// Get author ID
$authorId = intval($_GET['id'] ?? 0);
if (!$authorId) {
    header('Location: list.php');
    exit;
}

try {
    // Get author data
    $db->query("SELECT * FROM authors WHERE id = :id");
    $db->bind(':id', $authorId);
    $author = $db->single();
} catch (Exception $e) {
    die($e->getMessage());
}

if (!$author) {
    header('Location: list.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $bio = trim($_POST['bio']);
    $website = trim($_POST['website']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    // Validate
    $errors = [];

    if (empty($name)) {
        $errors[] = 'نام نویسنده الزامی است';
    }

    if (empty($errors)) {
        // Handle avatar upload
        $avatarUrl = $author->avatar_url;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../../assets/images/authors/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Delete old avatar if exists
            if ($avatarUrl && file_exists('../../..' . $avatarUrl)) {
                unlink('../../..' . $avatarUrl);
            }

            $fileExtension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $fileName = 'author_' . $authorId . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $filePath)) {
                $avatarUrl = '/assets/images/authors/' . $fileName;
            }
        }

        // Remove avatar if requested
        if (isset($_POST['remove_avatar']) && $avatarUrl) {
            if (file_exists('../../..' . $avatarUrl)) {
                unlink('../../..' . $avatarUrl);
            }
            $avatarUrl = null;
        }

        try {
            // Update author
            $db->query("UPDATE authors SET 
                name = :name,
                bio = :bio,
                website_url = :website,
                avatar_url = :avatar,
                is_active = :is_active
                WHERE id = :id");

            $db->bind(':name', $name);
            $db->bind(':bio', $bio);
            $db->bind(':website', $website);
            $db->bind(':avatar', $avatarUrl);
            $db->bind(':is_active', $isActive);
            $db->bind(':id', $authorId);

            if ($db->execute()) {
                $_SESSION['success'] = 'نویسنده با موفقیت به‌روزرسانی شد';
                header('Location: list.php');
                exit;
            } else {
                $errors[] = 'خطا در به‌روزرسانی نویسنده';
            }
        } catch (Exception $e) {
            $errors[] = 'خطای پایگاه داده: ' . $e->getMessage();
        }
    }
}

try {
    // Get author's book count for stats
    $db->query("SELECT COUNT(*) as book_count FROM books WHERE author_id = :author_id");
    $db->bind(':author_id', $authorId);
    $bookCountResult = $db->single();
    $bookCount = $bookCountResult->book_count ?? 0;
} catch (Exception $e) {
    die($e->getMessage());
}

$pageTitle = "ویرایش نویسنده - {$author->name}";
?>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">ویرایش نویسنده: <?php echo htmlspecialchars($author->name); ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="list.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>
                        بازگشت به لیست
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

            <!-- Edit Author Form -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="authorForm">
                        <div class="row g-4">
                            <!-- Author Information -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-info-circle me-2"></i>
                                            اطلاعات نویسنده
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">نام نویسنده *</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="name"
                                                   name="name"
                                                   value="<?php echo htmlspecialchars($author->name); ?>"
                                                   required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="bio" class="form-label">زندگی‌نامه</label>
                                            <textarea class="form-control"
                                                      id="bio"
                                                      name="bio"
                                                      rows="6"
                                                      placeholder="زندگی‌نامه و اطلاعات درباره نویسنده"><?php echo htmlspecialchars($author->bio ?? ''); ?></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="website" class="form-label">وبسایت</label>
                                            <input type="url"
                                                   class="form-control"
                                                   id="website"
                                                   name="website"
                                                   value="<?php echo htmlspecialchars($author->website_url ?? ''); ?>"
                                                   placeholder="https://example.com">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar -->
                            <div class="col-md-4">
                                <!-- Current Avatar -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-image me-2"></i>
                                            تصویر فعلی
                                        </h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <?php if ($author->avatar_url): ?>
                                            <img src="<?php echo $author->avatar_url; ?>"
                                                 alt="<?php echo htmlspecialchars($author->name); ?>"
                                                 class="rounded-circle mb-3"
                                                 width="120" height="120" style="object-fit: cover;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="remove_avatar"
                                                       name="remove_avatar" value="1">
                                                <label class="form-check-label" for="remove_avatar">
                                                    حذف تصویر
                                                </label>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted mb-3">
                                                <i class="fas fa-user-circle fa-4x"></i>
                                                <p class="mt-2">تصویری تنظیم نشده</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- New Avatar -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-upload me-2"></i>
                                            تصویر جدید
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="avatar" class="form-label">آپلود تصویر جدید</label>
                                            <input type="file"
                                                   class="form-control"
                                                   id="avatar"
                                                   name="avatar"
                                                   accept="image/*">
                                            <div class="form-text">فرمت‌های مجاز: JPG, PNG, GIF - حداکثر 2MB</div>
                                        </div>

                                        <div id="avatarPreview" class="mt-3 text-center" style="display: none;">
                                            <img id="preview" class="rounded-circle" width="100" height="100"
                                                 style="object-fit: cover;" alt="avatarPreview">
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
                                                   id="is_active"
                                                   name="is_active"
                                                   value="1"
                                                    <?php echo $author->is_active ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_active">
                                                فعال
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Author Stats -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-chart-bar me-2"></i>
                                            آمار نویسنده
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between text-muted small mb-2">
                                            <span>تعداد کتاب‌ها:</span>
                                            <span><?php echo $bookCount; ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between text-muted small">
                                            <span>تاریخ ایجاد:</span>
                                            <span><?php echo date('Y/m/d', strtotime($author->created_at)); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="list.php" class="btn btn-outline-secondary">
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
        // Avatar preview
        $('#avatar').on('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    ShenavaAdmin.showToast('حجم فایل باید کمتر از 2MB باشد', 'error');
                    $(this).val('');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#preview').attr('src', e.target.result);
                    $('#avatarPreview').show();
                }
                reader.readAsDataURL(file);
            }
        });

        // Form validation
        $('#authorForm').on('submit', function () {
            const name = $('#name').val().trim();

            if (!name) {
                ShenavaAdmin.showToast('لطفا نام نویسنده را وارد کنید', 'error');
                return false;
            }

            ShenavaAdmin.setButtonLoading($(this).find('button[type="submit"]'), true);
            return true;
        });
    });
</script>