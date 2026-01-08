<?php
// actions/category_crud.php
require '../config/db.php';
require '../includes/functions.php';

require_login();
require_role(['admin']); // Strict Admin Only

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF");

    $action = $_POST['action'];
    $name = trim($_POST['name'] ?? '');
    $id = $_POST['id'] ?? null;

    try {
        if ($action === 'add' && $name) {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            set_flash("Category '$name' created.");
        } 
        elseif ($action === 'update' && $id && $name) {
            $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
            set_flash("Category updated.");
        } 
        elseif ($action === 'delete' && $id) {
            // Optional: Check if payloads exist in this category first?
            // For now, simple delete (might fail if foreign keys restrict it, which is good)
            try {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                set_flash("Category deleted.", "warning");
            } catch (Exception $e) {
                set_flash("Cannot delete category. It may contain payloads.", "danger");
            }
        }
    } catch (PDOException $e) {
        set_flash("Error: " . $e->getMessage(), "danger");
    }

    header("Location: ../admin.php");
    exit;
}
?>