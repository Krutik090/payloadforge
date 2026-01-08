<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
$pageTitle = "View Payloads";
include 'includes/header.php';

$cat_id = $_GET['category_id'] ?? 'all';
$categoryName = "All Payloads";

try {
    if ($cat_id === 'all') {
        $sql = "SELECT p.*, c.name as category_name FROM payloads p 
                LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC";
        $stmt = $pdo->query($sql);
    } else {
        $sql = "SELECT p.*, c.name as category_name FROM payloads p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = ? ORDER BY p.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cat_id]);
        
        // Get Category Name
        $catStmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
        $catStmt->execute([$cat_id]);
        $name = $catStmt->fetchColumn();
        if($name) $categoryName = $name;
    }
    $payloads = $stmt->fetchAll();
} catch (PDOException $e) { die("Error: " . $e->getMessage()); }
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">📂 <?= htmlspecialchars($categoryName) ?></h2>
        <a href="index.php" class="btn btn-outline-light btn-sm">&larr; Dashboard</a>
    </div>

    <?php if (empty($payloads)): ?>
        <div class="alert alert-secondary">No payloads found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="20%">Title</th>
                        <th width="45%">Payload</th>
                        <th width="10%">Context</th>
                        <th width="10%">Severity</th>
                        <th width="15%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payloads as $p): ?>
                        <tr>
                            <td class="fw-bold text-white"><?= htmlspecialchars($p['title']) ?></td>
                            
                            <td>
                                <div class="payload-font">
                                    <?= htmlspecialchars($p['payload_content']) ?>
                                </div>
                            </td>

                            <td><small class="text-muted"><?= htmlspecialchars($p['target_context'] ?? 'N/A') ?></small></td>
                            
                            <td>
                                <?php 
                                    $sevClass = match($p['severity']) {
                                        'Critical' => 'bg-critical',
                                        'High'     => 'bg-high',
                                        'Low'      => 'bg-low',
                                        default    => 'bg-medium'
                                    };
                                ?>
                                <span class="badge <?= $sevClass ?>"><?= htmlspecialchars($p['severity']) ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-copy" 
                                        onclick="copyToClipboard(this, '<?= htmlspecialchars(addslashes($p['payload_content']), ENT_QUOTES) ?>')">
                                    Copy
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
function copyToClipboard(btn, text) {
    // If text contains HTML entities (like &quot;), the browser might pass them literally.
    // We create a temporary textarea to decode/handle the copy correctly.
    if (!navigator.clipboard) {
        var textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand("Copy");
        textArea.remove();
        showCopiedFeedback(btn);
        return;
    }

    navigator.clipboard.writeText(text).then(() => {
        let original = btn.innerText;
        btn.innerText = "Copied!";
        btn.classList.add('btn-success');
        setTimeout(() => {
            btn.innerText = original;
            btn.classList.remove('btn-success');
        }, 2000);
    });
}
</script>

<?php include 'includes/footer.php'; ?>