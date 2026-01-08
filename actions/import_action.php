<?php
// actions/import_action.php
require '../config/db.php';
require '../includes/functions.php';

require_login();
require_role(['contributor', 'editor', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
    
    // Security Check
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF Error");

    $file = $_FILES['import_file']['tmp_name'];
    $ext = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);
    
    $payloads = [];

    // 1. Parse JSON
    if (strtolower($ext) === 'json') {
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        if (is_array($data)) $payloads = $data;
    } 
    // 2. Parse CSV
    elseif (strtolower($ext) === 'csv') {
        if (($handle = fopen($file, "r")) !== FALSE) {
            $headers = fgetcsv($handle, 1000, ","); // Skip header row
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Map CSV columns: Title, Category, Payload, Type, Severity
                $payloads[] = [
                    'title' => $data[0] ?? 'Untitled',
                    'category' => $data[1] ?? 'General',
                    'payload' => $data[2] ?? '',
                    'type' => $data[3] ?? 'URL Parameter',
                    'severity' => $data[4] ?? 'Medium',
                    'tags' => $data[5] ?? ''
                ];
            }
            fclose($handle);
        }
    } else {
        set_flash("Invalid file format. Only JSON or CSV allowed.", "danger");
        header("Location: ../payload_import.php");
        exit;
    }

    // 3. Insert into Database
    $count = 0;
    foreach ($payloads as $p) {
        // Resolve Category Name to ID
        $cat_name = trim($p['category']);
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name LIKE ?");
        $stmt->execute([$cat_name]);
        $cat_id = $stmt->fetchColumn();

        // If category doesn't exist, default to 1 (Make sure you have a category with ID 1!)
        if (!$cat_id) $cat_id = 1;

        // MAPPING FIX: 'payload' from file -> 'payload_content' in DB
        $sql = "INSERT INTO payloads (title, category_id, payload_content, target_context, severity, tags, author_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $p['title'], 
                $cat_id, 
                $p['payload'], // The content from the file
                $p['type'] ?? 'Body Parameter',
                $p['severity'] ?? 'Medium',
                $p['tags'] ?? '',
                $_SESSION['user_id']
            ]);
            $count++;
        } catch (Exception $e) {
            // Silently skip errors or log them
            continue;
        }
    }

    set_flash("Successfully imported $count payloads!", "success");
    header("Location: ../index.php");
    exit;
}
?>