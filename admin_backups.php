<?php
require 'config/db.php';
require 'includes/header.php';
require_login();
require_role(['admin']);

// Robust Path Finding (Points to 'backups' folder in root)
$backupDir = __DIR__ . '/backups';

// Clear PHP's file cache to ensure deleted files disappear immediately
clearstatcache(); 

$files = [];
if (is_dir($backupDir)) {
    $files = scandir($backupDir);
    // Remove . and .. and .htaccess
    $files = array_diff($files, array('.', '..', '.htaccess'));
    // Sort by newest first
    rsort($files);
}
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">💾 Database Backups</h2>
        
        <form action="actions/backup_manager.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <button type="submit" name="create_now" class="btn btn-primary shadow-sm">
                <i class="bi bi-database-add"></i> Create Backup Now
            </button>
        </form>
    </div>

    <div class="alert alert-info border-info bg-dark text-info shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle-fill me-2 fs-4"></i>
            <div>
                <strong>Auto-Backup System:</strong><br>
                The system automatically creates a monthly backup the first time an Admin logs in each month.
                Files are stored securely in <code>/backups/</code>.
            </div>
        </div>
    </div>

    <div class="card shadow border-secondary">
        <div class="card-header bg-secondary text-white fw-bold">
            <i class="bi bi-archive"></i> Available Backups
        </div>
        <div class="card-body p-0">
            <?php if (empty($files)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-hdd-stack display-4 mb-3 d-block opacity-50"></i>
                    No backups found yet. Click "Create Backup Now" to make one.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-dark mb-0 align-middle">
                        <thead class="bg-dark border-bottom border-secondary">
                            <tr>
                                <th class="ps-4">Filename</th>
                                <th>Date Created</th>
                                <th>Size</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($files as $file): ?>
                                <?php 
                                    $path = $backupDir . '/' . $file;
                                    // Check if file exists to avoid errors
                                    if(!file_exists($path)) continue; 

                                    $sizeBytes = filesize($path);
                                    $size = ($sizeBytes > 1024 * 1024) 
                                            ? round($sizeBytes / 1048576, 2) . ' MB' 
                                            : round($sizeBytes / 1024, 2) . ' KB';
                                    
                                    $date = date("M d, Y H:i", filemtime($path));
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-success font-monospace">
                                        <i class="bi bi-filetype-sql me-2"></i><?= $file ?>
                                    </td>
                                    <td class="text-muted"><?= $date ?></td>
                                    <td><span class="badge bg-secondary"><?= $size ?></span></td>
                                    <td class="text-end pe-4">
                                        <a href="actions/backup_manager.php?download=<?= $file ?>" class="btn btn-sm btn-outline-light me-2">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                        
                                        <form action="actions/backup_manager.php" method="POST" class="d-inline" onsubmit="return confirm('⚠️ Are you sure you want to permanently DELETE this backup?');">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <input type="hidden" name="delete_file" value="1">
                                            <input type="hidden" name="file" value="<?= $file ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-dark border-top border-secondary text-muted small">
            Server Path: <code><?= $backupDir ?></code>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>