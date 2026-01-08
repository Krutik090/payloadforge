<?php
// actions/payload_crud.php
require '../config/db.php';
require '../includes/functions.php';

require_login();
require_role(['contributor', 'editor', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CSRF Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF Validation Failed");
    }

    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;

    // Common Data
    $title = $_POST['title'] ?? '';
    $content = $_POST['payload_content'] ?? ''; // Fixed: Matches DB column
    $cat_id = $_POST['category_id'] ?? 1;
    $sub_cat = $_POST['sub_category'] ?? '';
    $context = $_POST['target_context'] ?? 'URL Parameter';
    $severity = $_POST['severity'] ?? 'Medium';
    $tags = $_POST['tags'] ?? '';

    try {
        if ($action === 'add') {
            $sql = "INSERT INTO payloads (title, payload_content, category_id, sub_category, target_context, severity, tags, author_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $content, $cat_id, $sub_cat, $context, $severity, $tags, $_SESSION['user_id']]);
            
            set_flash("Payload created successfully!", "success");
        } 
        elseif ($action === 'edit' && $id) {
            // Role check: Only Admins/Editors can edit any payload; Contributors can edit their own? 
            // For simplicity, we allow based on require_role above.
            
            $sql = "UPDATE payloads SET title=?, payload_content=?, category_id=?, sub_category=?, target_context=?, severity=?, tags=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $content, $cat_id, $sub_cat, $context, $severity, $tags, $id]);
            
            set_flash("Payload updated successfully!", "success");
        } 
        elseif ($action === 'delete' && $id) {
            // Delete logic (usually called via GET or separate form, but handling here if needed)
             $stmt = $pdo->prepare("DELETE FROM payloads WHERE id = ?");
             $stmt->execute([$id]);
             set_flash("Payload deleted.", "warning");
        }

    } catch (PDOException $e) {
        set_flash("Database Error: " . $e->getMessage(), "danger");
        // Redirect back to form on error if needed
        if($action == 'edit') { header("Location: ../payload_edit.php?id=$id"); exit; }
        header("Location: ../payload_add.php"); exit;
    }

    // Redirect
    if ($action === 'edit' && $id) {
        header("Location: ../payload_view.php?category_id=$cat_id");
    } else {
        header("Location: ../index.php");
    }
    exit;
}
?>