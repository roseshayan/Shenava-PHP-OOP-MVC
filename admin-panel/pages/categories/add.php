<?php
/**
 * Shenava - Add New Category
 */

session_start();
require_once '../../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';
require_once BACKEND_PATH . '/app/core/Model.php';
require_once BACKEND_PATH . '/app/models/CategoryModel.php';

$db = new Database();
$categoryModel = new CategoryModel();

// Get parent categories for dropdown
try {
    $parentCategories = $categoryModel->getParentCategories();
} catch (Exception $e) {
    die($e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description']);
    $color = $_POST['color'] ?? '#00BFA5';
    $sort_order = intval($_POST['sort_order'] ?? 0);
    $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null; // اضافه شدن parent_id

    // Validate
    $errors = [];

    if (empty($name)) {
        $errors[] = 'نام دسته‌بندی الزامی است';
    }

    if (empty($slug)) {
        $errors[] = 'slug الزامی است';
    }

    try {
        // Check if slug exists
        $db->query("SELECT id FROM categories WHERE slug = :slug");
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

        // Handle file upload
        $cover_image = null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../../assets/images/categories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExtension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $fileName = $slug . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $filePath)) {
                $cover_image = '/assets/images/categories/' . $fileName;
            }
        }

        try {
            // Insert category - parent_id اضافه شد
            $db->query("INSERT INTO categories (uuid, name, slug, description, cover_image, color, sort_order, parent_id) 
                   VALUES (:uuid, :name, :slug, :description, :cover_image, :color, :sort_order, :parent_id)");

            $db->bind(':uuid', $uuid);
            $db->bind(':name', $name);
            $db->bind(':slug', $slug);
            $db->bind(':description', $description);
            $db->bind(':cover_image', $cover_image);
            $db->bind(':color', $color);
            $db->bind(':sort_order', $sort_order);
            $db->bind(':parent_id', $parent_id);

            if ($db->execute()) {
                $_SESSION['success'] = 'دسته‌بندی با موفقیت ایجاد شد';
                header('Location: list.php');
                exit;
            } else {
                $errors[] = 'خطا در ایجاد دسته‌بندی';
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
$pageTitle = "افزودن دسته‌بندی - شنوا";
?>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">افزودن دسته‌بندی جدید</h1>
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

            <!-- Add Category Form -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="categoryForm">
                        <div class="row g-4">
                            <!-- Basic Information -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-info-circle me-2"></i>
                                            اطلاعات اصلی
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">نام دسته‌بندی *</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="name"
                                                   name="name"
                                                   value="<?php echo $_POST['name'] ?? ''; ?>"
                                                   required
                                                   placeholder="مثلا: رمان فارسی">
                                        </div>

                                        <div class="mb-3">
                                            <label for="slug" class="form-label">Slug *</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="slug"
                                                   name="slug"
                                                   value="<?php echo $_POST['slug'] ?? ''; ?>"
                                                   required
                                                   placeholder="مثلا: persian-novel">
                                            <div class="form-text">شناسه یکتا برای URL - فقط حروف انگلیسی، اعداد و خط
                                                تیره
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="parent_id" class="form-label">دسته‌بندی والد</label>
                                            <select class="form-select" id="parent_id" name="parent_id">
                                                <option value="">بدون والد (دسته‌بندی اصلی)</option>
                                                <?php foreach ($parentCategories as $parentCat): ?>
                                                    <option value="<?php echo $parentCat->id; ?>"
                                                            <?php echo (isset($_POST['parent_id']) && $_POST['parent_id'] == $parentCat->id) ? 'selected' : ''; ?>>
                                                        <?php echo $parentCat->name; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">در صورت انتخاب، این دسته‌بندی به عنوان زیرمجموعه
                                                نمایش داده می‌شود
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">توضیحات</label>
                                            <textarea class="form-control"
                                                      id="description"
                                                      name="description"
                                                      rows="4"
                                                      placeholder="توضیحات مختصر درباره این دسته‌بندی"><?php echo $_POST['description'] ?? ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar Settings -->
                            <div class="col-md-4">
                                <!-- Color Picker -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-palette me-2"></i>
                                            رنگ دسته‌بندی
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">انتخاب رنگ</label>
                                            <div id="colorPicker" class="mb-2"></div>
                                            <input type="hidden"
                                                   id="color"
                                                   name="color"
                                                   value="<?php echo $_POST['color'] ?? '#00BFA5'; ?>">
                                        </div>
                                    </div>
                                </div>

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
                                            <div class="form-text">فرمت‌های مجاز: JPG, PNG, GIF - حداکثر سایز: 2MB</div>
                                        </div>

                                        <div id="imagePreview" class="mt-3 text-center" style="display: none;">
                                            <img id="preview" class="img-thumbnail" style="max-height: 150px;"
                                                 alt="imagePreview">
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
                                        <div class="mb-3">
                                            <label for="sort_order" class="form-label">ترتیب نمایش</label>
                                            <input type="number"
                                                   class="form-control"
                                                   id="sort_order"
                                                   name="sort_order"
                                                   value="<?php echo $_POST['sort_order'] ?? 0; ?>"
                                                   min="0"
                                                   max="100">
                                            <div class="form-text">اعداد کمتر اولویت بالاتری دارند</div>
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
                                        ایجاد دسته‌بندی
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
        // Initialize color picker
        const pickr = Pickr.create({
            el: '#colorPicker',
            theme: 'classic',
            default: '#00BFA5',
            swatches: [
                '#00BFA5', '#FF7043', '#2196F3', '#4CAF50', '#FFC107',
                '#9C27B0', '#F44336', '#607D8B', '#795548', '#00BCD4'
            ],
            components: {
                preview: true,
                opacity: false,
                hue: true,
                interaction: {
                    hex: true,
                    rgba: false,
                    hsla: false,
                    hsva: false,
                    cmyk: false,
                    input: true,
                    clear: false,
                    save: true
                }
            }
        });

        pickr.on('save', (color) => {
            $('#color').val(color.toHEXA().toString());
            pickr.hide();
        });

        // Display the currently selected color
        pickr.setColor($('#color').val());

        // Generate slug from name
        $('#name').on('blur', function () {
            if (!$('#slug').val()) {
                const name = $(this).val();
                const slug = name.toLowerCase()
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
        $('#categoryForm').on('submit', function () {
            const name = $('#name').val().trim();
            const slug = $('#slug').val().trim();

            if (!name) {
                ShenavaAdmin.showToast('لطفا نام دسته‌بندی را وارد کنید', 'error');
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

            ShenavaAdmin.setButtonLoading($(this).find('button[type="submit"]'), true);
            return true;
        });
    });
</script>