<?php
require 'config/db.php';
require 'includes/header.php';
require_login();

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT p.*, c.name as cat_name, u.username as author 
                       FROM payloads p 
                       JOIN categories c ON p.category_id = c.id 
                       JOIN users u ON p.author_id = u.id 
                       WHERE p.id = ?");
$stmt->execute([$id]);
$payload = $stmt->fetch();

if (!$payload) die("<div class='container'>Payload not found.</div>");

// Get History
$histStmt = $pdo->prepare("SELECT v.*, u.username FROM payload_versions v JOIN users u ON v.modified_by = u.id WHERE payload_id = ? ORDER BY modified_at DESC");
$histStmt->execute([$id]);
$history = $histStmt->fetchAll();
?>

<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2><?= h($payload['title']) ?></h2>
        <div>
            <?php if(has_role(['editor', 'admin'])): ?>
                <a href="payload_edit.php?id=<?= $payload['id'] ?>"><button>Edit</button></a>
            <?php endif; ?>
            <?php if(has_role(['admin'])): ?>
                <form action="actions/payload_crud.php" method="POST" style="display:inline;" onsubmit="return confirm('Delete this payload?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $payload['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <button type="submit" class="btn-danger">Delete</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex-row" style="margin-bottom:15px; background:#252526; padding:10px;">
        <span class="badge severity-<?= $payload['severity'] ?>"><?= $payload['severity'] ?></span>
        <span><strong>Category:</strong> <?= h($payload['cat_name']) ?> / <?= h($payload['sub_category']) ?></span>
        <span><strong>Context:</strong> <?= h($payload['target_context']) ?></span>
        <span><strong>Author:</strong> <?= h($payload['author']) ?></span>
    </div>

    <label>Payload Content:</label>
    <div class="code-block"><?= h($payload['payload_content']) ?></div>
    
    <textarea id="raw_view_<?= $payload['id'] ?>" style="display:none;"><?= h($payload['payload_content']) ?></textarea>
    <button onclick="copyToClipboard('raw_view_<?= $payload['id'] ?>')">Copy to Clipboard</button>

    <hr style="border-color:#333; margin: 30px 0;">

    <h3>Version History</h3>
    <?php if(count($history) > 0): ?>
    <table>
        <thead><tr><th>Date</th><th>Modified By</th><th>Previous Content (Snippet)</th></tr></thead>
        <tbody>
            <?php foreach($history as $h): ?>
            <tr>
                <td><?= $h['modified_at'] ?></td>
                <td><?= h($h['username']) ?></td>
                <td style="font-family:monospace; color:#888;">
                    <?= h(substr($h['old_content'], 0, 80)) ?>...
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No previous versions.</p>
    <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>