<?php
/**
 * Shenava - Add New Book
 */
session_save_path('/tmp');
session_start();
require_once '../../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';

$db = new Database();

try {
    // Get authors and categories for dropdowns
    $db->query("SELECT id, name FROM authors WHERE is_active = 1 ORDER BY name");
    $authors = $db->resultSet();
} catch (Exception $e) {
    echo $e->getMessage();
}

try {
    $db->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name");
    $categories = $db->resultSet();
} catch (Exception $e) {
    echo $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description']);
    $authorId = intval($_POST['author_id']);
    $narratorId = intval($_POST['narrator_id']);
    $categoryId = intval($_POST['category_id']);
    $isFree = isset($_POST['is_free']) ? 1 : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $price = floatval($_POST['price'] ?? 0);

    // Validate
    $errors = [];

    if (empty($title)) {
        $errors[] = 'عنوان کتاب الزامی است';
    }

    if (empty($slug)) {
        $errors[] = 'slug الزامی است';
    }


    try {
        // Check if slug exists
        $db->query("SELECT id FROM books WHERE slug = :slug");
        $db->bind(':slug', $slug);
        if ($db->single()) {
            $errors[] = 'این slug قبلا استفاده شده است';
        }
    } catch (Exception $e) {
        echo $e->getMessage();
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

        // Handle cover image upload
        $coverImage = null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../../assets/images/books/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExtension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $fileName = $slug . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $filePath)) {
                $coverImage = '/assets/images/books/' . $fileName;
            }
        }

        try {
            // Insert book
            $db->query("INSERT INTO books (uuid, title, slug, description, cover_image, author_id, narrator_id, category_id, price, is_free, is_featured) 
                   VALUES (:uuid, :title, :slug, :description, :cover_image, :author_id, :narrator_id, :category_id, :price, :is_free, :is_featured)");

            $db->bind(':uuid', $uuid);
            $db->bind(':title', $title);
            $db->bind(':slug', $slug);
            $db->bind(':description', $description);
            $db->bind(':cover_image', $coverImage);
            $db->bind(':author_id', $authorId);
            $db->bind(':narrator_id', $narratorId);
            $db->bind(':category_id', $categoryId);
            $db->bind(':price', $price);
            $db->bind(':is_free', $isFree);
            $db->bind(':is_featured', $isFeatured);
            if ($db->execute()) {
                $bookId = $db->lastInsertId();
                $_SESSION['success'] = 'کتاب با موفقیت ایجاد شد';
                header("Location: chapters.php?book_id=$bookId");
                exit;
            } else {
                $errors[] = 'خطا در ایجاد کتاب';
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

$pageTitle = "افزودن کتاب جدید - شنوا";
?>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">افزودن کتاب جدید</h1>
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

            <!-- Add Book Form -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="bookForm">
                        <div class="row g-4">
                            <!-- Basic Information -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-info-circle me-2"></i>
                                            اطلاعات کتاب
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">عنوان کتاب *</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="title"
                                                   name="title"
                                                   value="<?php echo $_POST['title'] ?? ''; ?>"
                                                   required
                                                   placeholder="عنوان کامل کتاب">
                                        </div>

                                        <div class="mb-3">
                                            <label for="slug" class="form-label">Slug *</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="slug"
                                                   name="slug"
                                                   value="<?php echo $_POST['slug'] ?? ''; ?>"
                                                   required
                                                   placeholder="persian-novel">
                                            <div class="form-text">شناسه یکتا برای URL - فقط حروف انگلیسی، اعداد و خط
                                                تیره
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">توضیحات</label>
                                            <textarea class="form-control"
                                                      id="description"
                                                      name="description"
                                                      rows="6"
                                                      placeholder="توضیحات کامل درباره کتاب"><?php echo $_POST['description'] ?? ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar -->
                            <div class="col-md-4">
                                <!-- Cover Image -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-image me-2"></i>
                                            تصویر کاور
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="cover_image" class="form-label">آپلود تصویر</label>
                                            <input type="file"
                                                   class="form-control"
                                                   id="cover_image"
                                                   name="cover_image"
                                                   accept="image/*">
                                            <div class="form-text">فرمت‌های مجاز: JPG, PNG - حداکثر سایز: 2MB</div>
                                        </div>

                                        <div id="imagePreview" class="mt-3 text-center" style="display: none;">
                                            <img id="preview" class="img-thumbnail" style="max-height: 150px;"
                                                 alt="imagePreview">
                                        </div>
                                    </div>
                                </div>

                                <!-- Book Details -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-cog me-2"></i>
                                            جزئیات کتاب
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="author_id" class="form-label">نویسنده *</label>
                                            <select class="form-select" id="author_id" name="author_id" required>
                                                <option value="">انتخاب نویسنده</option>
                                                <?php foreach ($authors as $author): ?>
                                                    <option value="<?php echo $author->id; ?>"
                                                            <?php echo ($_POST['author_id'] ?? '') == $author->id ? 'selected' : ''; ?>>
                                                        <?php echo $author->name; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="narrator_id" class="form-label">گوینده</label>
                                            <select class="form-select" id="narrator_id" name="narrator_id">
                                                <option value="">انتخاب گوینده</option>
                                                <?php foreach ($authors as $author): ?>
                                                    <option value="<?php echo $author->id; ?>"
                                                            <?php echo ($_POST['narrator_id'] ?? '') == $author->id ? 'selected' : ''; ?>>
                                                        <?php echo $author->name; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="category_id" class="form-label">دسته‌بندی *</label>
                                            <select class="form-select" id="category_id" name="category_id" required>
                                                <option value="">انتخاب دسته‌بندی</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category->id; ?>"
                                                            <?php echo ($_POST['category_id'] ?? '') == $category->id ? 'selected' : ''; ?>>
                                                        <?php echo $category->name; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Settings -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-sliders-h me-2"></i>
                                            تنظیمات
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="price" class="form-label">قیمت (تومان)</label>
                                            <input type="number"
                                                   class="form-control"
                                                   id="price"
                                                   name="price"
                                                   value="<?php echo $_POST['price'] ?? 0; ?>"
                                                   min="0"
                                                   step="1000">
                                        </div>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="is_free"
                                                   name="is_free"
                                                    <?php echo isset($_POST['is_free']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_free">
                                                کتاب رایگان
                                            </label>
                                        </div>

                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="is_featured"
                                                   name="is_featured"
                                                    <?php echo isset($_POST['is_featured']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_featured">
                                                کتاب ویژه
                                            </label>
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
                                        <i class="fas fa-times me-2"></i>
                                        انصراف
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        ایجاد کتاب و ادامه به افزودن فصل‌ها
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
        // Generate slug from title
        $('#title').on('blur', function () {
            if (!$('#slug').val()) {
                const title = $(this).val();
                const slug = title.toLowerCase()
                    .replace(/[^a-z0-9\u0600-\u06FF\s-]/g, '')
                    .replace(/[\s-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                $('#slug').val(slug);
            }
        });

        // Image preview
        $('#cover_image').on('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#preview').attr('src', e.target.result);
                    $('#imagePreview').show();
                }
                reader.readAsDataURL(file);
            }
        });

        // Form validation
        $('#bookForm').on('submit', function () {
            const title = $('#title').val().trim();
            const slug = $('#slug').val().trim();
            const authorId = $('#author_id').val();
            const categoryId = $('#category_id').val();

            if (!title) {
                ShenavaAdmin.showToast('لطفا عنوان کتاب را وارد کنید', 'error');
                return false;
            }

            if (!slug) {
                ShenavaAdmin.showToast('لطفا slug را وارد کنید', 'error');
                return false;
            }

            if (!/^[a-z0-9-]+$/.test(slug)) {
                ShenavaAdmin.showToast('slug فقط می‌تواند شامل حروف انگلیسی، اعداد و خط تیره باشد', 'error');
                return false;
            }

            if (!authorId) {
                ShenavaAdmin.showToast('لطفا نویسنده را انتخاب کنید', 'error');
                return false;
            }

            if (!categoryId) {
                ShenavaAdmin.showToast('لطفا دسته‌بندی را انتخاب کنید', 'error');
                return false;
            }

            ShenavaAdmin.setButtonLoading($(this).find('button[type="submit"]'), true);
            return true;
        });
    });
</script>