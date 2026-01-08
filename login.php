<?php 
require 'includes/functions.php'; 
// If already logged in
if (isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - PayloadForge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-dark">
    
    <div class="card shadow-lg p-4 border-secondary" style="width: 100%; max-width: 400px; background: #1e1e1e;">
        <div class="card-body">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-danger">🚀 PayloadForge</h2>
                <p class="text-muted">Red Team Knowledge Base</p>
            </div>

            <?php display_flash(); ?>

            <form action="actions/login_action.php" method="POST">
                <div class="mb-3">
                    <label class="form-label text-white">Username</label>
                    <input type="text" name="username" class="form-control bg-dark text-white border-secondary" required autofocus>
                </div>
                
                <div class="mb-4">
                    <label class="form-label text-white">Password</label>
                    <input type="password" name="password" class="form-control bg-dark text-white border-secondary" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Access System</button>
            </form>
        </div>
        <div class="card-footer text-center border-0 pt-3" style="background: transparent;">
            <small class="text-muted">Authorized Personnel Only</small>
        </div>
    </div>

</body>
</html>