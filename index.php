<?php
require 'config/db.php';
require 'includes/header.php';
require_login();

// Filters
$q = $_GET['q'] ?? '';
$cat = $_GET['cat'] ?? '';

// Build Query
$sql = "SELECT p.*, c.name as cat_name FROM payloads p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE (title LIKE ? OR tags LIKE ? OR payload_content LIKE ?)";
$params = ["%$q%", "%$q%", "%$q%"];

if (!empty($cat)) {
    $sql .= " AND category_id = ?";
    $params[] = $cat;
}

$sql .= " ORDER BY created_at DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payloads = $stmt->fetchAll();

// Get Categories for dropdown
$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<div class="container">
    <div class="flex-row" style="justify-content:space-between; margin-bottom: 20px;">
        <h2>Payload Repository</h2>
        <a href="payload_add.php"><button class="btn-sm"> + New Payload</button></a>
    </div>

    <form method="GET" class="flex-row" style="margin-bottom: 20px;">
        <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search title, tags, or content...">
        <select name="cat" style="width: 200px;">
            <option value="">All Categories</option>
            <?php foreach($cats as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $cat == $c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-sm">Filter</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Context</th>
                <th>Severity</th>
                <th width="150">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($payloads as $p): ?>
            <tr>
                <td>
                    <a href="payload_view.php?id=<?= $p['id'] ?>"><strong><?= h($p['title']) ?></strong></a><br>
                    <small style="color:#888"><?= h($p['tags']) ?></small>
                </td>
                <td><?= h($p['cat_name']) ?></td>
                <td><?= h($p['target_context']) ?></td>
                <td><span class="badge severity-<?= h($p['severity']) ?>"><?= h($p['severity']) ?></span></td>
                <td>
                    <textarea id="raw_<?= $p['id'] ?>" style="display:none;"><?= h($p['payload_content']) ?></textarea>
                    <button class="btn-sm" onclick="copyToClipboard('raw_<?= $p['id'] ?>')">Copy</button>
                    <a href="payload_view.php?id=<?= $p['id'] ?>" class="btn-sm" style="background:#444; color:#fff; padding:6px 10px; border-radius:2px;">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require 'includes/footer.php'; ?>