<?php
require 'config/db.php';
require 'includes/header.php';
require_login();
require_role(['admin']);

// User Creation Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF Error");
    $passHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['username'], $passHash, $_POST['role']]);
        set_flash("User created successfully!");
    } catch(Exception $e) {
        set_flash("Error: Username likely already exists.", "danger");
    }
    header("Location: admin.php"); exit;
}

// Fetch Data
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Edit Logic
$edit_cat = null;
if (isset($_GET['edit_cat_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$_GET['edit_cat_id']]);
    $edit_cat = $stmt->fetch();
}
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">🛡️ Admin Control Panel</h2>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="bi bi-person-plus"></i> Create New User
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="create_user" value="1">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select">
                                    <option value="viewer">Viewer</option>
                                    <option value="contributor">Contributor</option>
                                    <option value="editor">Editor</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Create User</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white border-bottom border-secondary">
                    Existing Users
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>User</th><th>Role</th></tr></thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <tr>
                                <td><?= h($u['username']) ?></td>
                                <td><span class="badge bg-secondary"><?= h($u['role']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm mb-4 border-warning">
                <div class="card-header bg-warning text-dark fw-bold">
                    <?= $edit_cat ? "✏️ Edit Category" : "📂 Add Category" ?>
                    <?php if($edit_cat): ?> 
                        <a href="admin.php" class="float-end text-dark small text-decoration-none">Cancel</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form action="actions/category_crud.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <?php if ($edit_cat): ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= $edit_cat['id'] ?>">
                            <div class="input-group">
                                <input type="text" name="name" class="form-control" value="<?= h($edit_cat['name']) ?>" required>
                                <button type="submit" class="btn btn-success">Update</button>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="action" value="add">
                            <div class="input-group">
                                <input type="text" name="name" class="form-control" placeholder="New Category Name..." required>
                                <button type="submit" class="btn btn-warning">Add</button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white border-bottom border-secondary">
                    Existing Categories
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Name</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                            <?php foreach($categories as $c): ?>
                            <tr>
                                <td><?= h($c['name']) ?></td>
                                <td class="text-end">
                                    <a href="admin.php?edit_cat_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-info me-1">Edit</a>
                                    <form action="actions/category_crud.php" method="POST" class="d-inline" onsubmit="return confirm('Delete category?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Del</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>