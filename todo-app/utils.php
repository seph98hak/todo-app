<?php
// utils.php - helper functions

function simulate_email($to, $subject, $message) {
    $log = "=== " . date('Y-m-d H:i:s') . " ===\nTo: $to\nSubject: $subject\nMessage: $message\n\n";
    file_put_contents(__DIR__ . '/email_log.txt', $log, FILE_APPEND | LOCK_EX);
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Simple CSRF token helpers
function csrf_token() {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}
function csrf_check($token) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

