<?php
require 'config/db.php';
require 'includes/header.php';
require_login();
require_role(['editor', 'admin']);

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM payloads WHERE id = ?");
$stmt->execute([$id]);
$payload = $stmt->fetch();

if (!$payload) die("<div class='container mt-5 alert alert-danger'>Payload not found.</div>");
$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">✏️ Edit Payload</h2>
        <a href="payload_view.php?category_id=<?= $payload['category_id'] ?>" class="btn btn-outline-secondary btn-sm">Cancel</a>
    </div>

    <div class="card shadow-sm border-warning">
        <div class="card-body">
            <form action="actions/payload_crud.php" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $payload['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" value="<?= h($payload['title']) ?>" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach($cats as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $c['id'] == $payload['category_id'] ? 'selected' : '' ?>>
                                    <?= h($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sub Category</label>
                        <input type="text" name="sub_category" class="form-control" value="<?= h($payload['sub_category']) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Payload Content</label>
                    <textarea name="payload_content" class="form-control font-monospace" rows="6" style="background: #000; color: #00e676; border-color: #444;" required><?= h($payload['payload_content']) ?></textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Target Context</label>
                        <select name="target_context" class="form-select">
                            <?php 
                            $ctxs = ['URL Parameter (GET)', 'Body Parameter (POST)', 'JSON Body', 'HTTP Header', 'Cookie'];
                            foreach($ctxs as $ctx) {
                                $sel = ($ctx == $payload['target_context']) ? 'selected' : '';
                                echo "<option $sel>$ctx</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Severity</label>
                        <select name="severity" class="form-select">
                            <?php 
                            $sevs = ['Low', 'Medium', 'High', 'Critical'];
                            foreach($sevs as $sev) {
                                $sel = ($sev == $payload['severity']) ? 'selected' : '';
                                echo "<option $sel>$sev</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tags</label>
                        <input type="text" name="tags" class="form-control" value="<?= h($payload['tags']) ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-warning px-4">Update Payload</button>
            </form>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>