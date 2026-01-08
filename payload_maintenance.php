<?php
// 1. Security & Configuration
require_once 'config/db.php';      // Database connection
require_once 'includes/auth.php';  // Ensure user is logged in
require_once 'includes/functions.php';

// 2. Handle the "Remove Duplicates" Action
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deduplicate'])) {
    try {
        // Count duplicates before deleting (for reporting)
        // FIXED: Changed 't1.payload' to 't1.payload_content'
        $countSql = "SELECT COUNT(*) FROM payloads t1 
                     INNER JOIN payloads t2 
                     WHERE t1.id > t2.id AND t1.payload_content = t2.payload_content";
        $stmt = $pdo->query($countSql);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            // Delete duplicates (Keep the one with the lowest ID)
            // FIXED: Changed 't1.payload' to 't1.payload_content'
            $sql = "DELETE t1 FROM payloads t1
                    INNER JOIN payloads t2 
                    WHERE t1.id > t2.id AND t1.payload_content = t2.payload_content";
            $pdo->exec($sql);
            $message = "<div class='alert alert-success'>✅ Success! Deleted $count duplicate payloads.</div>";
        } else {
            $message = "<div class='alert alert-info'>👍 Database is clean. No duplicates found.</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// 3. Load Header
$pageTitle = "System Maintenance";
include 'includes/header.php';
?>

<div class="container mt-4">
    <h2>🛠️ Payload Maintenance</h2>
    <p class="text-muted">Use this tool to clean up your database after bulk imports.</p>
    <hr>

    <?php if ($message): ?>
        <?= $message ?>
    <?php endif; ?>

    <div class="card shadow-sm border-warning">
        <div class="card-header bg-warning text-dark">
            <strong>Duplicate Remover</strong>
        </div>
        <div class="card-body">
            <p>If you have imported the same JSON file multiple times, you might have duplicate payloads. This tool will scan the database and keep only the <strong>first</strong> unique entry for each payload.</p>
            
            <form method="POST">
                <button type="submit" name="deduplicate" class="btn btn-danger" onclick="return confirm('Are you sure? This will permanently delete duplicate records.');">
                    ⚠️ Find & Remove Duplicates
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>