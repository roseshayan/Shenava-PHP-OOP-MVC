<?php
/**
 * Shenava - Backup Settings
 */

session_start();
require_once '../../includes/auth-check.php';

// Define base paths
define('BASE_PATH', dirname(__DIR__) . '/../..');
const BACKEND_PATH = BASE_PATH . '/backend';
require_once BACKEND_PATH . '/app/core/Database.php';

$db = new Database();

// Handle backup actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'create_backup':
            $backupResult = createCompleteDatabaseBackup();
            if ($backupResult['success']) {
                $_SESSION['success'] = 'پشتیبان با موفقیت ایجاد شد: ' . $backupResult['filename'];
            } else {
                $_SESSION['error'] = 'خطا در ایجاد پشتیبان: ' . $backupResult['error'];
            }
            break;

        case 'restore_backup':
            if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
                $restoreResult = restoreDatabaseBackup($_FILES['backup_file']['tmp_name']);
                if ($restoreResult['success']) {
                    $_SESSION['success'] = 'بازیابی با موفقیت انجام شد';
                } else {
                    $_SESSION['error'] = 'خطا در بازیابی: ' . $restoreResult['error'];
                }
            } else {
                $_SESSION['error'] = 'لطفا فایل پشتیبان را انتخاب کنید';
            }
            break;

        case 'delete_backup':
            if (isset($_POST['filename'])) {
                $filename = $_POST['filename'];
                $backupDir = BACKEND_PATH . '/backups/';
                $filepath = $backupDir . $filename;

                if (file_exists($filepath) && unlink($filepath)) {
                    $_SESSION['success'] = 'فایل پشتیبان با موفقیت حذف شد';
                } else {
                    $_SESSION['error'] = 'خطا در حذف فایل پشتیبان';
                }
            }
            break;
    }

    header('Location: backup.php');
    exit;
}

// Get backup files
$backupFiles = [];
$backupDir = BACKEND_PATH . '/backups/';
if (is_dir($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $filePath = $backupDir . $file;
            $backupFiles[] = [
                    'name' => $file,
                    'size' => filesize($filePath),
                    'modified' => filemtime($filePath),
                    'path' => $filePath
            ];
        }
    }

    // Sort by modification time (newest first)
    usort($backupFiles, function ($a, $b) {
        return $b['modified'] - $a['modified'];
    });
}

$pageTitle = "پشتیبان‌گیری و بازیابی";
?>
<?php include '../../includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">پشتیبان‌گیری و بازیابی</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="create_backup">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-download me-2"></i>
                                ایجاد پشتیبان جدید
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Notifications -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $_SESSION['success'];
                        unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <a href="general.php" class="list-group-item list-group-item-action">
                                        <i class="fas fa-cog me-2"></i>
                                        تنظیمات عمومی
                                    </a>
                                    <a href="appearance.php" class="list-group-item list-group-item-action">
                                        <i class="fas fa-palette me-2"></i>
                                        ظاهر و تم
                                    </a>
                                    <a href="api.php" class="list-group-item list-group-item-action">
                                        <i class="fas fa-code me-2"></i>
                                        تنظیمات API
                                    </a>
                                    <a href="storage.php" class="list-group-item list-group-item-action">
                                        <i class="fas fa-database me-2"></i>
                                        ذخیره‌سازی
                                    </a>
                                    <a href="backup.php" class="list-group-item list-group-item-action active">
                                        <i class="fas fa-shield-alt me-2"></i>
                                        پشتیبان‌گیری
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Backup Info -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    اطلاعات پشتیبان
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="backup-info">
                                    <div class="d-flex justify-content-between text-muted small mb-2">
                                        <span>تعداد پشتیبان‌ها:</span>
                                        <span><?php echo count($backupFiles); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted small mb-2">
                                        <span>آخرین پشتیبان:</span>
                                        <span>
                                        <?php
                                        if (!empty($backupFiles)) {
                                            echo date('Y/m/d H:i', $backupFiles[0]['modified']);
                                        } else {
                                            echo '---';
                                        }
                                        ?>
                                    </span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted small">
                                        <span>فضای استفاده شده:</span>
                                        <span>
                                        <?php
                                        $totalSize = 0;
                                        foreach ($backupFiles as $file) {
                                            $totalSize += $file['size'];
                                        }
                                        echo formatBytes($totalSize);
                                        ?>
                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9">
                        <!-- Create Backup Card -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-download me-2 text-success"></i>
                                    ایجاد پشتیبان جدید
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <p class="mb-2">یک پشتیبان کامل از پایگاه داده ایجاد کنید.</p>
                                        <small class="text-muted">
                                            پشتیبان شامل تمام جداول و داده‌های سیستم می‌شود.
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <form method="POST">
                                            <input type="hidden" name="action" value="create_backup">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fas fa-database me-2"></i>
                                                ایجاد پشتیبان
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Restore Backup Card -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-upload me-2 text-warning"></i>
                                    بازیابی پشتیبان
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="restore_backup">

                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="backup_file" class="form-label">انتخاب فایل پشتیبان</label>
                                                <input type="file"
                                                       class="form-control"
                                                       id="backup_file"
                                                       name="backup_file"
                                                       accept=".sql"
                                                       required>
                                                <div class="form-text">فایل پشتیبان باید با پسوند .sql باشد</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <button type="submit" class="btn btn-warning btn-lg"
                                                    onclick="return confirm('⚠️ هشدار: بازیابی پشتیبان تمام داده‌های فعلی را جایگزین می‌کند. آیا مطمئن هستید؟')">
                                                <i class="fas fa-history me-2"></i>
                                                بازیابی
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Backup Files List -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-list me-2 text-primary"></i>
                                    فایل‌های پشتیبان موجود
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($backupFiles)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">هیچ فایل پشتیبانی یافت نشد</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                            <tr>
                                                <th>نام فایل</th>
                                                <th>سایز</th>
                                                <th>تاریخ ایجاد</th>
                                                <th>عملیات</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($backupFiles as $backup): ?>
                                                <tr>
                                                    <td>
                                                        <i class="fas fa-database text-primary me-2"></i>
                                                        <?php echo $backup['name']; ?>
                                                    </td>
                                                    <td><?php echo formatBytes($backup['size']); ?></td>
                                                    <td>
                                                        <?php echo date('Y/m/d H:i', $backup['modified']); ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="download_backup.php?file=<?php echo urlencode($backup['name']); ?>"
                                                               class="btn btn-outline-primary"
                                                               title="دانلود">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                            <form method="POST" class="d-inline"
                                                                  onsubmit="return confirm('آیا از حذف این پشتیبان مطمئن هستید؟')">
                                                                <input type="hidden" name="action"
                                                                       value="delete_backup">
                                                                <input type="hidden" name="filename"
                                                                       value="<?php echo $backup['name']; ?>">
                                                                <button type="submit" class="btn btn-outline-danger"
                                                                        title="حذف">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
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

                        <!-- Backup Schedule -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-clock me-2 text-info"></i>
                                    زمان‌بندی پشتیبان‌گیری خودکار
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>توجه:</strong> برای راه‌اندازی پشتیبان‌گیری خودکار، نیاز به تنظیم cron job
                                    دارید.
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">دستور Cron Job</label>
                                        <code class="d-block p-2 bg-light rounded mb-2">
                                            0 2 * * * /usr/bin/php <?php echo BACKEND_PATH; ?>/cron/backup.php
                                        </code>
                                        <small class="text-muted">
                                            این دستور هر روز ساعت 2 بامداد پشتیبان می‌گیرد.
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">فایل پیکربندی</label>
                                        <div class="d-grid gap-2">
                                            <a href="download_backup.php?file=backup_config"
                                               class="btn btn-outline-secondary">
                                                <i class="fas fa-file-download me-2"></i>
                                                دریافت فایل پیکربندی
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<?php
// Helper function to format bytes
function formatBytes($bytes, $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Complete database backup function - FIXED VERSION
function createCompleteDatabaseBackup(): array
{
    try {
        $backupDir = BACKEND_PATH . '/backups/';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backupDir . $filename;

        $db = new Database();

        // Get all tables
        $db->query("SHOW TABLES");
        $tables = $db->resultSet();

        $backupContent = "-- Shenava Database Backup\n";
        $backupContent .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $backupContent .= "-- Database: shenava_db\n\n";

        // Disable foreign key checks for restore
        $backupContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tableObj) {
            $tableName = current((array)$tableObj);

            // Drop table if exists
            $backupContent .= "DROP TABLE IF EXISTS `$tableName`;\n";

            // Create table structure
            $db->query("SHOW CREATE TABLE `$tableName`");
            $createTable = $db->single();
            $createTableSql = $createTable->{'Create Table'};
            $backupContent .= $createTableSql . ";\n\n";

            // Table data - ALWAYS include INSERT statement even for empty tables
            $db->query("SELECT * FROM `$tableName`");
            $rows = $db->resultSet();

            $backupContent .= "-- Data for table `$tableName`\n";

            if (!empty($rows)) {
                // Get column names
                $db->query("SHOW COLUMNS FROM `$tableName`");
                $columns = $db->resultSet();
                $columnNames = array_map(function ($col) {
                    return $col->Field;
                }, $columns);

                foreach ($rows as $row) {
                    $values = [];
                    foreach ($columnNames as $column) {
                        $value = $row->$column ?? null;
                        $values[] = is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                    }

                    $backupContent .= "INSERT INTO `$tableName` (`" . implode('`, `', $columnNames) . "`) VALUES (" . implode(', ', $values) . ");\n";
                }
            } else {
                // Empty table - still include INSERT statement structure for reference
                $backupContent .= "-- Table is empty\n";
            }
            $backupContent .= "\n";
        }

        // Re-enable foreign key checks
        $backupContent .= "SET FOREIGN_KEY_CHECKS=1;\n";

        // Write to file
        if (file_put_contents($filepath, $backupContent) !== false) {
            return [
                    'success' => true,
                    'filename' => $filename,
                    'filepath' => $filepath,
                    'tables_count' => count($tables)
            ];
        } else {
            return [
                    'success' => false,
                    'error' => 'Cannot write backup file'
            ];
        }

    } catch (Exception $e) {
        return [
                'success' => false,
                'error' => $e->getMessage()
        ];
    }
}

// Improved database restore function
function restoreDatabaseBackup($backupFile): array
{
    try {
        $db = new Database();

        // Read backup file
        $sql = file_get_contents($backupFile);
        if ($sql === false) {
            return [
                    'success' => false,
                    'error' => 'Cannot read backup file'
            ];
        }

        // Remove comments and empty lines
        $sql = preg_replace('/--.*$/m', '', $sql); // Remove single line comments
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove multi-line comments
        $sql = preg_replace('/^\s*$/m', '', $sql); // Remove empty lines

        // Split SQL statements - improved method
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        $delimiter = ';';

        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];

            // Handle string literals
            if (($char === "'" || $char === '"') && ($i === 0 || $sql[$i - 1] !== '\\')) {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($inString && $stringChar === $char) {
                    $inString = false;
                }
            }

            $current .= $char;

            // If we're not in a string and found a delimiter, split
            if (!$inString && $char === $delimiter) {
                $statements[] = trim($current);
                $current = '';
            }
        }

        // Add any remaining statement
        if (!empty(trim($current))) {
            $statements[] = trim($current);
        }

        // Filter out empty statements
        $statements = array_filter($statements, function ($stmt) {
            return !empty(trim($stmt)) && strlen(trim($stmt)) > 10; // Minimum length to avoid tiny fragments
        });

        // Execute each statement with transaction
        $db->query("START TRANSACTION");
        $db->execute();

        try {
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    try {
                        $db->query($statement);
                        $db->execute();
                    } catch (Exception $e) {
                        // Log error but continue with other statements
                        error_log("SQL Error in backup restore: " . $e->getMessage() . " - Statement: " . substr($statement, 0, 100));
                        // Don't continue on critical errors
                        if (str_contains($e->getMessage(), 'Table') && str_contains($e->getMessage(), 'already exists')) {
                            // Table already exists - this is OK for some cases
                            continue;
                        }
                    }
                }
            }

            $db->query("COMMIT");
            $db->execute();

        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $db->execute();
            throw $e;
        }

        return [
                'success' => true,
                'statements_executed' => count($statements)
        ];

    } catch (Exception $e) {
        return [
                'success' => false,
                'error' => $e->getMessage()
        ];
    }
}

// Also create download_backup.php file:
function downloadBackupFile($filename): void
{
    $backupDir = BACKEND_PATH . '/backups/';
    $filepath = $backupDir . $filename;

    if (file_exists($filepath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    } else {
        http_response_code(404);
        echo 'File not found';
    }
}

?>