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
    <link rel="stylesheet" href="assets/style.css">
</head>
<body style="display:flex; justify-content:center; align-items:center; height:100vh;">
    <div class="container" style="width: 350px;">
        <h2 style="text-align:center; color:#ff5252;">PayloadForge</h2>
        <?php display_flash(); ?>
        <form action="actions/login_action.php" method="POST">
            <label>Username</label>
            <input type="text" name="username" required autofocus>
            
            <label>Password</label>
            <input type="password" name="password" required>
            
            <button type="submit">Access System</button>
        </form>
    </div>
</body>
</html>