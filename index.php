<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
$pageTitle = "Dashboard";
include 'includes/header.php';

try {
    // Fetch Categories and Counts
    $sql = "SELECT c.id, c.name, COUNT(p.id) as payload_count 
            FROM categories c 
            LEFT JOIN payloads p ON c.id = p.category_id 
            GROUP BY c.id 
            ORDER BY c.name ASC";
    $stmt = $pdo->query($sql);
    $categories = $stmt->fetchAll();

    // Global Stats
    $totalPayloads = $pdo->query("SELECT COUNT(*) FROM payloads")->fetchColumn();
    $totalCategories = count($categories);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="h2 text-white">🚀 Payload Dashboard</h1>
            <p class="text-muted">Welcome, <strong class="text-white"><?= htmlspecialchars($_SESSION['username']); ?></strong></p>
        </div>
        <div>
            <?php if(has_role(['contributor', 'editor', 'admin'])): ?>
                <a href="payload_add.php" class="btn btn-primary shadow-sm"><i class="bi bi-plus-lg"></i>Add New</a>
                <a href="payload_import.php" class="btn btn-outline-dark shadow-sm ms-2">Bulk Import</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-md-6 col-xl-3">
            <div class="card shadow h-100 py-2 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Payloads</div>
                    <div class="h3 mb-0 font-weight-bold text-white"><?= number_format($totalPayloads) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card shadow h-100 py-2 border-start border-4 border-success">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Categories</div>
                    <div class="h3 mb-0 font-weight-bold text-white"><?= $totalCategories ?></div>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mb-4 text-white border-bottom border-secondary pb-2">Browse by Category</h4>

    <div class="row g-4">
        <div class="col-md-6 col-lg-4 col-xl-3">
            <a href="payload_view.php?category_id=all" class="card h-100 cat-card" style="border-left: 4px solid #00e676 !important;">
                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title fw-bold">All Payloads</h5>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="badge bg-success text-white"><?= number_format($totalPayloads) ?> Items</span>
                        <span class="text-muted">&rarr;</span>
                    </div>
                </div>
            </a>
        </div>

        <?php foreach ($categories as $cat): ?>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <a href="payload_view.php?category_id=<?= $cat['id'] ?>" class="card h-100 cat-card">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <h5 class="card-title fw-bold text-light"><?= htmlspecialchars($cat['name']) ?></h5>
                        <div class="d-flex justify-content-between align-items-center mt-3 border-top border-secondary pt-3">
                            <span class="badge bg-secondary"><?= number_format($cat['payload_count']) ?> Payloads</span>
                            <span class="text-muted small">View</span>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>