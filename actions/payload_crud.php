<?php
require '../config/db.php';
require '../includes/functions.php';
require_login();

// Validate CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF Validation Failed");
}

$action = $_POST['action'] ?? '';

// --- ADD PAYLOAD ---
if ($action === 'add') {
    require_role(['contributor', 'editor', 'admin']);
    
    $sql = "INSERT INTO payloads (title, payload_content, category_id, sub_category, target_context, severity, tags, author_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([
            $_POST['title'],
            $_POST['payload_content'],
            $_POST['category_id'],
            $_POST['sub_category'],
            $_POST['target_context'],
            $_POST['severity'],
            $_POST['tags'],
            $_SESSION['user_id']
        ]);
        set_flash("Payload added successfully!");
    } catch (Exception $e) {
        set_flash("Error adding payload: " . $e->getMessage(), "error");
    }
    header("Location: ../index.php");
}

// --- EDIT PAYLOAD ---
if ($action === 'edit') {
    require_role(['editor', 'admin']);
    
    $id = $_POST['id'];
    $new_content = $_POST['payload_content'];

    // 1. Get current data for versioning
    $stmt = $pdo->prepare("SELECT payload_content FROM payloads WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetch();

    if (!$current) die("Payload not found");

    // 2. Archive if content changed
    if ($current['payload_content'] !== $new_content) {
        $histSql = "INSERT INTO payload_versions (payload_id, old_content, modified_by) VALUES (?, ?, ?)";
        $pdo->prepare($histSql)->execute([$id, $current['payload_content'], $_SESSION['user_id']]);
    }

    // 3. Update record
    $sql = "UPDATE payloads SET title=?, payload_content=?, category_id=?, sub_category=?, target_context=?, severity=?, tags=? WHERE id=?";
    $pdo->prepare($sql)->execute([
        $_POST['title'],
        $new_content,
        $_POST['category_id'],
        $_POST['sub_category'],
        $_POST['target_context'],
        $_POST['severity'],
        $_POST['tags'],
        $id
    ]);

    set_flash("Payload updated successfully!");
    header("Location: ../payload_view.php?id=$id");
}

// --- DELETE PAYLOAD ---
if ($action === 'delete') {
    require_role(['admin']);
    $id = $_POST['id'];
    $pdo->prepare("DELETE FROM payloads WHERE id = ?")->execute([$id]);
    set_flash("Payload deleted.", "success");
    header("Location: ../index.php");
}
?>