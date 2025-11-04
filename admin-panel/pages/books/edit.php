<?php
/**
 * Shenava - Edit Book
 */

session_start();
require_once '../../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';

$db = new Database();

// Get book ID
$bookId = intval($_GET['id'] ?? 0);
if (!$bookId) {
    header('Location: list.php');
    exit;
}

try {
    // Get book data
    $db->query("SELECT * FROM books WHERE id = :id");
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
    // Get authors and categories
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
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $price = floatval($_POST['price'] ?? 0);

    try {
        // Update book
        $db->query("UPDATE books SET 
                title = :title,
                slug = :slug,
                description = :description,
                author_id = :author_id,
                narrator_id = :narrator_id,
                category_id = :category_id,
                price = :price,
                is_free = :is_free,
                is_featured = :is_featured,
                is_active = :is_active,
                updated_at = NOW()
                WHERE id = :id");

        $db->bind(':title', $title);
        $db->bind(':slug', $slug);
        $db->bind(':description', $description);
        $db->bind(':author_id', $authorId);
        $db->bind(':narrator_id', $narratorId);
        $db->bind(':category_id', $categoryId);
        $db->bind(':price', $price);
        $db->bind(':is_free', $isFree);
        $db->bind(':is_featured', $isFeatured);
        $db->bind(':is_active', $isActive);
        $db->bind(':id', $bookId);

        if ($db->execute()) {
            $_SESSION['success'] = 'کتاب با موفقیت به‌روزرسانی شد';
            header('Location: list.php');
            exit;
        } else {
            $error = 'خطا در به‌روزرسانی کتاب';
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
$pageTitle = "ویرایش کتاب - شنوا";
?>
<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">ویرایش کتاب: <?php echo $book->title; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="list.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>
                        بازگشت به لیست
                    </a>
                </div>
            </div>

            <!-- Notifications -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Edit Book Form -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" id="bookForm">
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
                                                   value="<?php echo $book->title; ?>"
                                                   required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="slug" class="form-label">Slug *</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="slug"
                                                   name="slug"
                                                   value="<?php echo $book->slug; ?>"
                                                   required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">توضیحات</label>
                                            <textarea class="form-control"
                                                      id="description"
                                                      name="description"
                                                      rows="6"><?php echo $book->description; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar -->
                            <div class="col-md-4">
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
                                                        <?php echo $book->author_id == $author->id ? 'selected' : ''; ?>>
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
                                                        <?php echo $book->narrator_id == $author->id ? 'selected' : ''; ?>>
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
                                                        <?php echo $book->category_id == $category->id ? 'selected' : ''; ?>>
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
                                                   value="<?php echo $book->price; ?>"
                                                   min="0"
                                                   step="1000">
                                        </div>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="is_free"
                                                   name="is_free"
                                                <?php echo $book->is_free ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_free">
                                                کتاب رایگان
                                            </label>
                                        </div>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="is_featured"
                                                   name="is_featured"
                                                <?php echo $book->is_featured ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_featured">
                                                کتاب ویژه
                                            </label>
                                        </div>

                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="is_active"
                                                   name="is_active"
                                                <?php echo $book->is_active ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_active">
                                                فعال
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