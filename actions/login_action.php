<?php
// actions/login_action.php
require '../config/db.php';
require '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        set_flash("Welcome back, " . h($user['username']) . "!");
        header("Location: ../index.php");
        exit;
    } else {
        set_flash("Invalid username or password.", "danger");
        header("Location: ../login.php");
        exit;
    }
}
?>