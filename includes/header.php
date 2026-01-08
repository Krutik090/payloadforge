<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/functions.php'; 

// Helper to highlight active links
function isActive($page) {
    return basename($_SERVER['PHP_SELF']) == $page ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayloadForge - Red Team KB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    
    <?php if(isset($_SESSION['user_id'])): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-secondary mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold text-danger" href="index.php">
                <i class="bi bi-rocket-takeoff"></i> PayloadForge
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    
                    <li class="nav-item">
                        <a class="nav-link <?= isActive('index.php') ?>" href="index.php">Repository</a>
                    </li>

                    <?php if(has_role(['contributor', 'editor', 'admin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= isActive('payload_add.php') ?>" href="payload_add.php">Add Payload</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= isActive('payload_import.php') ?>" href="payload_import.php">Import</a>
                        </li>
                    <?php endif; ?>

                    <?php if(has_role(['admin'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-warning" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Admin
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark border-secondary">
                                <li><a class="dropdown-item" href="admin.php">Control Panel</a></li>
                                <li><a class="dropdown-item" href="payload_maintenance.php">Maintenance</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                        <a href="actions/logout.php" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-box-arrow-right"></i> Logout (<?= h($_SESSION['username'] ?? 'User') ?>)
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <div class="container-fluid px-4" style="max-width: 1400px;">
        <?php display_flash(); ?>