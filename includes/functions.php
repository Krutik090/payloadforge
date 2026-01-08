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
        die("<div class='container mt-5'><div class='alert alert-danger'>⛔ Access Denied: Insufficient permissions.</div></div>");
    }
}

// HTML Escaping
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Flash Messages (Updated for Bootstrap 5)
function set_flash($message, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $message, 'type' => $type];
}

function display_flash() {
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $msg = $_SESSION['flash']['msg'];
        
        // Map custom types to Bootstrap classes
        $alertClass = match($type) {
            'error', 'danger' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info',
            default => 'alert-success'
        };

        echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>
                $msg
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
        unset($_SESSION['flash']);
    }
}
?>