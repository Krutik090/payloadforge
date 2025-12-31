<?php
// actions/category_crud.php
require '../config/db.php';
require '../includes/functions.php';

require_login();
require_role(['admin']);

// CSRF Check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF Validation Failed");
}

$action = $_POST['action'] ?? '';

// --- ADD CATEGORY ---
if ($action === 'add') {
    $name = trim($_POST['name']);
    
    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            set_flash("Category '$name' added.", "success");
        } catch (PDOException $e) {
            set_flash("Error: Category likely exists already.", "error");
        }
    } else {
        set_flash("Category name cannot be empty.", "error");
    }
}

// --- UPDATE CATEGORY ---
if ($action === 'update') {
    $id = $_POST['id'];
    $name = trim($_POST['name']);

    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
            set_flash("Category updated.", "success");
        } catch (PDOException $e) {
            set_flash("Error updating category.", "error");
        }
    }
}

// --- DELETE CATEGORY ---
if ($action === 'delete') {
    $id = $_POST['id'];

    // 1. Check if payloads exist in this category
    $check = $pdo->prepare("SELECT COUNT(*) FROM payloads WHERE category_id = ?");
    $check->execute([$id]);
    $count = $check->fetchColumn();

    if ($count > 0) {
        set_flash("Cannot delete category! It contains $count payloads. Move them first.", "error");
    } else {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        set_flash("Category deleted successfully.", "success");
    }
}

header("Location: ../admin.php");
exit;