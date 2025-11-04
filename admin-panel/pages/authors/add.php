<?php
/**
 * Shenava - Add New Author
 */

session_start();
require_once '../../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';

$db = new Database();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $bio = trim($_POST['bio']);
    $website = trim($_POST['website']);

    // Validate
    $errors = [];

    if (empty($name)) {
        $errors[] = 'نام نویسنده الزامی است';
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

        // Handle avatar upload
        $avatarUrl = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../../assets/images/authors/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExtension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $fileName = 'author_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $filePath)) {
                $avatarUrl = '/assets/images/authors/' . $fileName;
            }
        }

        try {
            // Insert author
            $db->query("INSERT INTO authors (uuid, name, bio, website_url, avatar_url) 
                   VALUES (:uuid, :name, :bio, :website, :avatar)");

            $db->bind(':uuid', $uuid);
            $db->bind(':name', $name);
            $db->bind(':bio', $bio);
            $db->bind(':website', $website);
            $db->bind(':avatar', $avatarUrl);

            if ($db->execute()) {
                $_SESSION['success'] = 'نویسنده با موفقیت ایجاد شد';
                header('Location: list.php');
                exit;
            } else {
                $errors[] = 'خطا در ایجاد نویسنده';
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

$pageTitle = "افزودن نویسنده جدید";
?>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">افزودن نویسنده جدید</h1>
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

            <!-- Add Author Form -->
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
                                                   value="<?php echo $_POST['name'] ?? ''; ?>"
                                                   required
                                                   placeholder="نام کامل نویسنده">
                                        </div>

                                        <div class="mb-3">
                                            <label for="bio" class="form-label">زندگی‌نامه</label>
                                            <textarea class="form-control"
                                                      id="bio"
                                                      name="bio"
                                                      rows="6"
                                                      placeholder="زندگی‌نامه و اطلاعات درباره نویسنده"><?php echo $_POST['bio'] ?? ''; ?></textarea>
                                            <div class="form-text">می‌توانید از HTML برای فرمت‌دهی استفاده کنید</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="website" class="form-label">وبسایت</label>
                                            <input type="url"
                                                   class="form-control"
                                                   id="website"
                                                   name="website"
                                                   value="<?php echo $_POST['website'] ?? ''; ?>"
                                                   placeholder="https://example.com">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar -->
                            <div class="col-md-4">
                                <!-- Avatar -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-image me-2"></i>
                                            تصویر پروفایل
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="avatar" class="form-label">آپلود تصویر</label>
                                            <input type="file"
                                                   class="form-control"
                                                   id="avatar"
                                                   name="avatar"
                                                   accept="image/*">
                                            <div class="form-text">فرمت‌های مجاز: JPG, PNG - حداکثر سایز: 2MB</div>
                                        </div>

                                        <div id="avatarPreview" class="mt-3 text-center" style="display: none;">
                                            <img id="preview" class="img-thumbnail rounded-circle" style="max-height: 150px;" alt="avatarPreview">
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Tips -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-lightbulb me-2"></i>
                                            نکات سریع
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <small class="text-muted">
                                            <strong>برای اطلاعات بهتر:</strong><br>
                                            • زندگی‌نامه کامل بنویسید<br>
                                            • از تصویر با کیفیت استفاده کنید<br>
                                            • لینک وبسایت معتبر وارد کنید<br>
                                            • اطلاعات تماس را در صورت امکان اضافه کنید
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="list.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>
                                        انصراف
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        ایجاد نویسنده
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
    $(document).ready(function() {
        // Avatar preview
        $('#avatar').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview').attr('src', e.target.result);
                    $('#avatarPreview').show();
                }
                reader.readAsDataURL(file);
            }
        });

        // Form validation
        $('#authorForm').on('submit', function() {
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