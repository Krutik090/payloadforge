<?php
// actions/import_action.php
require '../config/db.php';
require '../includes/functions.php';

require_login();
require_role(['contributor', 'editor', 'admin']);

// 1. Security Checks
if ($_SERVER['REQUEST_METHOD'] !== 'POST') die("Invalid Method");
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF Fail");

if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
    set_flash("File upload failed.", "error");
    header("Location: ../payload_import.php");
    exit;
}

$file = $_FILES['import_file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$tmpName = $file['tmp_name'];

// 2. Parse Data based on Extension
$data = [];

if ($ext === 'json') {
    $jsonContent = file_get_contents($tmpName);
    $data = json_decode($jsonContent, true);
    if (!$data) {
        set_flash("Invalid JSON format.", "error");
        header("Location: ../payload_import.php");
        exit;
    }
} 
elseif ($ext === 'csv') {
    if (($handle = fopen($tmpName, "r")) !== FALSE) {
        // Get headers
        $headers = fgetcsv($handle, 1000, ",");
        
        // Loop rows
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Combine headers with row data safely
            if (count($headers) == count($row)) {
                $data[] = array_combine($headers, $row);
            }
        }
        fclose($handle);
    }
} 
else {
    set_flash("Unsupported file format. Use CSV or JSON.", "error");
    header("Location: ../payload_import.php");
    exit;
}

// 3. Import Logic
$count = 0;
$errors = 0;

// Fetch all categories for mapping (Name -> ID)
$cats = $pdo->query("SELECT id, LOWER(name) as name FROM categories")->fetchAll(PDO::FETCH_KEY_PAIR);
// Invert array to search by name: ['sql injection' => 1, 'xss' => 2]
$catMap = array_flip($cats);

$stmt = $pdo->prepare("INSERT INTO payloads (title, payload_content, category_id, sub_category, target_context, severity, tags, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($data as $row) {
    // Normalize keys (lowercase) if needed, or assume template adherence
    $title = $row['title'] ?? 'Imported Payload';
    $payload = $row['payload'] ?? '';
    $catName = strtolower(trim($row['category'] ?? ''));
    $type = $row['type'] ?? 'URL Parameter'; // Context
    $severity = $row['severity'] ?? 'Medium';
    $tags = $row['tags'] ?? 'import';

    // Find Category ID (Default to 1 if not found)
    $catId = $catMap[$catName] ?? 1;

    if (!empty($payload)) {
        try {
            $stmt->execute([
                $title,
                $payload,
                $catId,
                'Imported', // Sub-category default
                $type,
                $severity,
                $tags,
                $_SESSION['user_id']
            ]);
            $count++;
        } catch (Exception $e) {
            $errors++;
        }
    }
}

// 4. Finish
set_flash("Import complete! Added: $count, Failed: $errors", "success");
header("Location: ../index.php");
?>