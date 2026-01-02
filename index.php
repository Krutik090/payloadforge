<?php
// index.php
require 'config/db.php';
require 'includes/header.php';
require_login();

// Search Filters
$q = $_GET['q'] ?? '';
$cat_filter = $_GET['cat'] ?? '';

// 1. Fetch Payloads with Category Names
$sql = "SELECT p.*, c.name as cat_name 
        FROM payloads p 
        JOIN categories c ON p.category_id = c.id 
        WHERE (p.title LIKE ? OR p.tags LIKE ? OR p.payload_content LIKE ?)";

$params = ["%$q%", "%$q%", "%$q%"];

// Apply specific category filter if selected
if (!empty($cat_filter)) {
    $sql .= " AND c.id = ?";
    $params[] = $cat_filter;
}

// Order by Category Name first, then Payload Title
$sql .= " ORDER BY c.name ASC, p.title ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$all_payloads = $stmt->fetchAll();

// 2. Group Payloads by Category
$grouped = [];
foreach ($all_payloads as $p) {
    $cat = $p['cat_name'];
    if (!isset($grouped[$cat])) {
        $grouped[$cat] = [];
    }
    $grouped[$cat][] = $p;
}

// Fetch categories for the filter dropdown
$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<div class="container">
    <div class="flex-row" style="justify-content:space-between; align-items:center; margin-bottom: 20px;">
        <h2>Payload Knowledge Base</h2>
        <?php if(has_role(['contributor', 'editor', 'admin'])): ?>
            <div>
                <a href="payload_import.php" class="btn-sm" style="background:#2c2c2c; margin-right:5px;">Bulk Import</a>
                <a href="payload_add.php"><button class="btn-sm"> + Add Payload</button></a>
            </div>
        <?php endif; ?>
    </div>

    <form method="GET" class="flex-row" style="background: #252526; padding: 15px; border-radius: 5px; margin-bottom: 30px;">
        <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search payloads..." style="flex: 2;">
        <select name="cat" style="flex: 1;">
            <option value="">All Categories</option>
            <?php foreach($cats as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $cat_filter == $c['id'] ? 'selected' : '' ?>>
                    <?= h($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-sm">Search</button>
        <?php if($q || $cat_filter): ?>
            <a href="index.php" style="color: #ff5252; margin-left: 10px; font-size: 0.9rem;">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (empty($grouped)): ?>
        <div class="alert error">No payloads found matching your criteria.</div>
    <?php else: ?>
        
        <?php foreach ($grouped as $category_name => $payloads): ?>
            <div class="category-section" style="margin-bottom: 40px;">
                
                <h3 style="border-bottom: 2px solid #444; padding-bottom: 10px; color: #00e676; margin-bottom: 15px;">
                    <?= h($category_name) ?> 
                    <span style="font-size: 0.8rem; color: #888; margin-left: 10px;">(<?= count($payloads) ?> items)</span>
                </h3>

                <table>
                    <thead>
                        <tr>
                            <th width="30%">Title</th>
                            <th width="15%">Context</th>
                            <th width="10%">Severity</th>
                            <th width="25%">Preview</th>
                            <th width="20%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payloads as $p): ?>
                        <tr>
                            <td>
                                <a href="payload_view.php?id=<?= $p['id'] ?>" style="font-weight:bold; font-size: 1.05rem;">
                                    <?= h($p['title']) ?>
                                </a>
                                <br>
                                <small style="color:#888"><?= h($p['tags']) ?></small>
                            </td>
                            <td><?= h($p['target_context']) ?></td>
                            <td>
                                <span class="badge severity-<?= h($p['severity']) ?>"><?= h($p['severity']) ?></span>
                            </td>
                            <td style="font-family: monospace; color: #ccc; font-size: 0.9rem;">
                                <?= h(mb_strimwidth($p['payload_content'], 0, 40, "...")) ?>
                            </td>
                            <td>
                                <textarea id="raw_<?= $p['id'] ?>" style="display:none;"><?= h($p['payload_content']) ?></textarea>
                                <button class="btn-sm" onclick="copyToClipboard('raw_<?= $p['id'] ?>')">Copy</button>
                                <a href="payload_view.php?id=<?= $p['id'] ?>" class="btn-sm" style="background:#444; color:#fff; text-decoration:none; display:inline-block; padding: 6px 10px;">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>