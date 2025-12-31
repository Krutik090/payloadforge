<?php
// payload_import.php
require 'config/db.php';
require 'includes/header.php';
require_login();
require_role(['contributor', 'editor', 'admin']);
?>

<div class="container">
    <h2>Bulk Payload Import</h2>
    
    <div class="alert" style="background: #2c2c2c; border: 1px solid #444;">
        <strong>Instructions:</strong>
        <ul>
            <li>Supported formats: <strong>.json</strong> or <strong>.csv</strong></li>
            <li><strong>Category Mapping:</strong> The system will try to match the "Category" name text to existing categories. If not found, it defaults to ID 1.</li>
            <li><strong>CSV Headers:</strong> <code>title, category, payload, type, severity</code></li>
            <li><strong>JSON Structure:</strong> Array of objects with keys: <code>title, category, payload, type, severity</code></li>
        </ul>
        <a href="assets/template.csv" download style="color:#2979ff;">Download CSV Template</a> | 
        <a href="assets/template.json" download style="color:#2979ff;">Download JSON Template</a>
    </div>

    <form action="actions/import_action.php" method="POST" enctype="multipart/form-data" style="margin-top:20px;">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <label>Select File</label>
        <input type="file" name="import_file" accept=".csv, .json" required style="padding: 10px; background: #333;">

        <button type="submit" style="margin-top:15px; width: 200px;">Upload & Import</button>
    </form>
</div>

<?php require 'includes/footer.php'; ?>