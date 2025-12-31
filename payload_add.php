<?php
require 'config/db.php';
require 'includes/header.php';
require_login();
require_role(['contributor', 'editor', 'admin']);

$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<div class="container">
    <h2>Add New Payload</h2>
    <form action="actions/payload_crud.php" method="POST">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <label>Title</label>
        <input type="text" name="title" required placeholder="e.g., Basic SQLi Union Select">

        <div class="flex-row">
            <div style="width:50%">
                <label>Category</label>
                <select name="category_id" required>
                    <?php foreach($cats as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= h($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="width:50%">
                <label>Sub Category</label>
                <input type="text" name="sub_category" placeholder="e.g., Time-based Blind">
            </div>
        </div>

        <label>Payload Content (Raw)</label>
        <textarea name="payload_content" style="height: 150px; font-family:monospace; color:#00e676;" required></textarea>

        <div class="flex-row">
            <div style="width:33%">
                <label>Target Context</label>
                <select name="target_context">
                    <option>URL Parameter (GET)</option>
                    <option>Body Parameter (POST)</option>
                    <option>JSON Body</option>
                    <option>HTTP Header</option>
                    <option>Cookie</option>
                </select>
            </div>
            <div style="width:33%">
                <label>Severity</label>
                <select name="severity">
                    <option>Low</option>
                    <option selected>Medium</option>
                    <option>High</option>
                    <option>Critical</option>
                </select>
            </div>
            <div style="width:33%">
                <label>Tags (Comma separated)</label>
                <input type="text" name="tags" placeholder="mysql, auth-bypass, waf">
            </div>
        </div>

        <button type="submit">Save Payload</button>
    </form>
</div>
<?php require 'includes/footer.php'; ?>