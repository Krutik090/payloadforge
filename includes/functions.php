<?php
// includes/functions.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Authentication Check
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

// Role Check
function has_role($allowed_roles = []) {
    if (!isset($_SESSION['role'])) return false;
    return in_array($_SESSION['role'], $allowed_roles);
}

function require_role($allowed_roles = []) {
    if (!has_role($allowed_roles)) {
        die("<h3>Access Denied: You do not have permission to view this page.</h3>");
    }
}

// HTML Escaping (Security)
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Flash Messages
function set_flash($message, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $message, 'type' => $type];
}

function display_flash() {
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $msg = $_SESSION['flash']['msg'];
        echo "<div class='alert $type'>$msg</div>";
        unset($_SESSION['flash']);
    }
}
?>