<?php
// actions/backup_manager.php
require '../config/db.php';
require '../includes/functions.php';

require_login();
require_role(['admin']);

// Absolute path to the backups folder
$backupDir = realpath(__DIR__ . '/../backups');

// Check if directory exists
if (!$backupDir || !is_dir($backupDir)) {
    // Try to create it if missing
    $backupDir = __DIR__ . '/../backups';
    if (!mkdir($backupDir, 0777, true)) {
        die("CRITICAL ERROR: Could not create backups directory at $backupDir");
    }
    chmod($backupDir, 0777); // Force permissions
    $backupDir = realpath($backupDir);
}

// --- FUNCTION: PERFORM BACKUP ---
function create_backup($pdo, $backupDir) {
    try {
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $sqlScript = "-- PayloadForge Backup " . date('Y-m-d H:i:s') . "\n";
        $sqlScript .= "SET FOREIGN_KEY_CHECKS=0;\n";

        foreach ($tables as $table) {
            $row = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
            $sqlScript .= "\n" . $row[1] . ";\n";
            $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $vals = array_map(fn($v) => $v === null ? "NULL" : $pdo->quote($v), array_values($row));
                $sqlScript .= "INSERT INTO `$table` VALUES (" . implode(',', $vals) . ");\n";
            }
        }
        $sqlScript .= "\nSET FOREIGN_KEY_CHECKS=1;";
        
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $fullPath = $backupDir . '/' . $filename;

        if (file_put_contents($fullPath, $sqlScript) === false) {
            throw new Exception("Write failed to $fullPath");
        }
        
        // Force permissions on the new file so it can be deleted later
        chmod($fullPath, 0666); 
        
        return $filename;
    } catch (Exception $e) {
        throw $e;
    }
}

// --- ACTIONS ---

// 1. Create
if (isset($_POST['create_now'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF");
    try {
        $file = create_backup($pdo, $backupDir);
        set_flash("✅ Backup created: $file", "success");
    } catch (Exception $e) {
        set_flash("❌ Error: " . $e->getMessage(), "danger");
    }
    header("Location: ../admin_backups.php");
    exit;
}

// 2. Download
if (isset($_GET['download'])) {
    $file = basename($_GET['download']);
    $filepath = $backupDir . '/' . $file;
    if (file_exists($filepath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$file.'"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
}

// 3. Delete (DEBUG VERSION)
if (isset($_POST['delete_file'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF");
    
    $file = basename($_POST['file']);
    $filepath = $backupDir . '/' . $file;
    
    // Clear cache to ensure we see the real file status
    clearstatcache();

    if (file_exists($filepath)) {
        // Check if writable/deletable
        if (!is_writable($filepath)) {
            // Try to fix permissions before deleting
            @chmod($filepath, 0777);
            @chmod($backupDir, 0777);
        }

        if (unlink($filepath)) {
            set_flash("✅ Deleted: $file", "warning");
        } else {
            // Detailed Error for Troubleshooting
            $owner = fileowner($filepath);
            $perms = substr(sprintf('%o', fileperms($filepath)), -4);
            set_flash("❌ DELETE FAILED. File: $filepath | Owner ID: $owner | Perms: $perms", "danger");
        }
    } else {
        set_flash("❌ File not found: $file", "danger");
    }
    
    header("Location: ../admin_backups.php");
    exit;
}
?>