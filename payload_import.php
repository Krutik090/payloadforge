<?php
// payload_import.php
require 'config/db.php';
require 'includes/header.php';
require_login();
require_role(['contributor', 'editor', 'admin']);
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">📥 Bulk Payload Import</h2>
        <a href="index.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
    </div>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card bg-dark border-secondary h-100">
                <div class="card-header bg-secondary text-white fw-bold">Instructions</div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">✅ <strong>Formats:</strong> <code>.json</code> or <code>.csv</code></li>
                        <li class="mb-2">📂 <strong>Auto-Category:</strong> System matches category name or creates new.</li>
                        <li class="mb-2">📝 <strong>CSV Headers:</strong><br> <code>title, category, payload, type, severity</code></li>
                        <li>🤖 <strong>JSON Format:</strong> Array of objects.</li>
                    </ul>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="assets/template.csv" download class="btn btn-sm btn-outline-info">Download CSV Template</a>
                        <a href="assets/template.json" download class="btn btn-sm btn-outline-warning">Download JSON Template</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm border-primary h-100">
                <div class="card-body d-flex flex-column justify-content-center py-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-cloud-upload display-4 text-primary"></i>
                        <h4 class="mt-2">Upload File</h4>
                        <p class="text-muted">Drag and drop or click below</p>
                    </div>

                    <form action="actions/import_action.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="mb-4 px-5">
                            <input type="file" name="import_file" class="form-control form-control-lg bg-dark text-white border-secondary" accept=".csv, .json" required>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                Start Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>