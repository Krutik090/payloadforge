<?php require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayloadForge - Red Team KB</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php if(isset($_SESSION['user_id'])): ?>
    <nav>
        <div class="nav-brand">PayloadForge</div>
        <div class="nav-links">
            <a href="index.php">Repository</a>

            <?php if(has_role(['contributor', 'editor', 'admin'])): ?>
                <a href="payload_add.php">Add Payload</a>
                <a href="payload_import.php">Bulk Import</a> <?php endif; ?>

            <?php if(has_role(['admin'])): ?>
                <a href="admin.php">Admin Panel</a>
            <?php endif; ?>

            <a href="actions/logout.php" class="logout">Logout (<?= h($_SESSION['username']) ?>)</a>
        </div>
    </nav>
    <?php endif; ?>
    <div class="main-content">
        <?php display_flash(); ?>