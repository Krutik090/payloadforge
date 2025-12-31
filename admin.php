<?php
// admin.php
require 'config/db.php';
require 'includes/header.php';
require_login();
require_role(['admin']);

// --- LOGIC: CREATE USER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF");
    
    $passHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$_POST['username'], $passHash, $_POST['role']]);
        set_flash("User created!");
    } catch(Exception $e) {
        set_flash("Error: Username might exist.", "error");
    }
    header("Location: admin.php"); 
    exit;
}

// --- DATA FETCHING ---
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// --- EDIT MODE LOGIC ---
$edit_cat = null;
if (isset($_GET['edit_cat_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$_GET['edit_cat_id']]);
    $edit_cat = $stmt->fetch();
}
?>

<div class="container">
    <h2>Admin Panel</h2>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
        <div>
            <div class="code-block" style="background: #252526; border: 1px solid #444; padding:15px; margin-bottom: 20px;">
                <h3>Create New User</h3>
                <form method="POST">
                    <input type="hidden" name="create_user" value="1">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <label>Username</label>
                    <input type="text" name="username" required>
                    
                    <label>Password</label>
                    <input type="password" name="password" required>
                    
                    <label>Role</label>
                    <select name="role">
                        <option value="viewer">Viewer</option>
                        <option value="contributor">Contributor</option>
                        <option value="editor">Editor</option>
                        <option value="admin">Admin</option>
                    </select>
                    
                    <button type="submit" style="width:100%; margin-top:10px;">Create User</button>
                </form>
            </div>

            <h3>Existing Users</h3>
            <table>
                <thead><tr><th>User</th><th>Role</th></tr></thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td><?= h($u['username']) ?></td>
                        <td><span class="badge"><?= h($u['role']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div>
            <div class="code-block" style="background: #252526; border: 1px solid #444; padding:15px; margin-bottom: 20px;">
                <h3>
                    <?= $edit_cat ? "Edit Category" : "Add Category" ?>
                    <?php if($edit_cat): ?> 
                        <a href="admin.php" style="font-size:0.8rem; float:right;">(Cancel)</a>
                    <?php endif; ?>
                </h3>

                <form action="actions/category_crud.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <?php if ($edit_cat): ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $edit_cat['id'] ?>">
                        <input type="text" name="name" value="<?= h($edit_cat['name']) ?>" required>
                        <button type="submit" style="background: #ff9800;">Update Category</button>
                    <?php else: ?>
                        <input type="hidden" name="action" value="add">
                        <input type="text" name="name" placeholder="New Category Name..." required>
                        <button type="submit" style="background: #4caf50;">Add Category</button>
                    <?php endif; ?>
                </form>
            </div>

            <h3>Existing Categories</h3>
            <table>
                <thead><tr><th>Name</th><th width="120">Actions</th></tr></thead>
                <tbody>
                    <?php foreach($categories as $c): ?>
                    <tr>
                        <td><?= h($c['name']) ?></td>
                        <td>
                            <a href="admin.php?edit_cat_id=<?= $c['id'] ?>" class="badge" style="background:#2979ff; color:white;">Edit</a>
                            
                            <form action="actions/category_crud.php" method="POST" style="display:inline; float:right;" onsubmit="return confirm('Delete category?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <button type="submit" style="background:none; border:none; padding:0; cursor:pointer; color:#f44336; font-weight:bold;">X</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    
    </div> </div>
<?php require 'includes/footer.php'; ?>