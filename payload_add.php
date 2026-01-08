<?php
require 'config/db.php';
require 'includes/header.php';
require_login();
require_role(['contributor', 'editor', 'admin']);

$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">➕ Add New Payload</h2>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">Cancel</a>
    </div>

    <div class="card shadow-sm border-secondary">
        <div class="card-body">
            <form action="actions/payload_crud.php" method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="mb-3">
                    <label class="form-label">Payload Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g., Basic SQLi Union Select" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach($cats as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= h($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sub Category <small class="text-muted">(Optional)</small></label>
                        <input type="text" name="sub_category" class="form-control" placeholder="e.g., Time-based Blind">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Payload Content (Raw)</label>
                    <textarea name="payload_content" class="form-control font-monospace" rows="6" style="background: #000; color: #00e676; border-color: #444;" required></textarea>
                    <div class="form-text text-muted">Paste the exact payload string above.</div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Target Context</label>
                        <select name="target_context" class="form-select">
                            <option>URL Parameter (GET)</option>
                            <option>Body Parameter (POST)</option>
                            <option>JSON Body</option>
                            <option>HTTP Header</option>
                            <option>Cookie</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Severity</label>
                        <select name="severity" class="form-select">
                            <option value="Low">Low (Green)</option>
                            <option value="Medium" selected>Medium (Yellow)</option>
                            <option value="High">High (Orange)</option>
                            <option value="Critical">Critical (Red)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tags</label>
                        <input type="text" name="tags" class="form-control" placeholder="mysql, auth, bypass">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">Save Payload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>