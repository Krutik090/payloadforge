<?php
require 'config/db.php';
require 'includes/header.php';
require_login();
require_role(['editor', 'admin']);

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM payloads WHERE id = ?");
$stmt->execute([$id]);
$payload = $stmt->fetch();

if (!$payload) die("Not found");
$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<div class="container">
    <h2>Edit Payload</h2>
    <form action="actions/payload_crud.php" method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" value="<?= $payload['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <label>Title</label>
        <input type="text" name="title" value="<?= h($payload['title']) ?>" required>

        <div class="flex-row">
            <div style="width:50%">
                <label>Category</label>
                <select name="category_id" required>
                    <?php foreach($cats as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $payload['category_id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="width:50%">
                <label>Sub Category</label>
                <input type="text" name="sub_category" value="<?= h($payload['sub_category']) ?>">
            </div>
        </div>

        <label>Payload Content</label>
        <textarea name="payload_content" style="height: 150px; font-family:monospace; color:#00e676;" required><?= h($payload['payload_content']) ?></textarea>

        <div class="flex-row">
            <div style="width:33%">
                <label>Target Context</label>
                <select name="target_context">
                    <?php 
                    $ctxs = ['URL Parameter (GET)', 'Body Parameter (POST)', 'JSON Body', 'HTTP Header', 'Cookie'];
                    foreach($ctxs as $ctx) {
                        $sel = ($ctx == $payload['target_context']) ? 'selected' : '';
                        echo "<option $sel>$ctx</option>";
                    }
                    ?>
                </select>
            </div>
            <div style="width:33%">
                <label>Severity</label>
                <select name="severity">
                    <?php 
                    $sevs = ['Low', 'Medium', 'High', 'Critical'];
                    foreach($sevs as $sev) {
                        $sel = ($sev == $payload['severity']) ? 'selected' : '';
                        echo "<option $sel>$sev</option>";
                    }
                    ?>
                </select>
            </div>
            <div style="width:33%">
                <label>Tags</label>
                <input type="text" name="tags" value="<?= h($payload['tags']) ?>">
            </div>
        </div>

        <button type="submit">Update Payload</button>
        <a href="payload_view.php?id=<?= $payload['id'] ?>" style="margin-left:10px;">Cancel</a>
    </form>
</div>
<?php require 'includes/footer.php'; ?>