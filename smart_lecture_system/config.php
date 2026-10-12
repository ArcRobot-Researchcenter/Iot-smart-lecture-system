<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'smart_lecture_system');
define('BASE_URL', 'http://localhost/smart_lecture_system/');
define('ADMIN_URL', BASE_URL . 'admin/');

// eBulkSMS API Configuration
define('EBULKSMS_USERNAME', 'your_ebulksms_username');
define('EBULKSMS_PASSWORD', 'your_ebulksms_password');
define('EBULKSMS_API_URL', 'https://api.ebulksms.com:4433/smsapi');

// Database Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Security Functions
function sanitize($conn, $data) {
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($data))), ENT_QUOTES, 'UTF-8');
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function csrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && isset($_SESSION['last_activity']);
}

function checkSession() {
    if (isLoggedIn()) {
        if (time() - $_SESSION['last_activity'] > 3600) {
            session_destroy();
            header("Location: " . BASE_URL . "index.php?timeout=1");
            exit();
        }
        $_SESSION['last_activity'] = time();
    }
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "index.php");
        exit();
    }
}

function hasRole($role) {
    return isLoggedIn() && $_SESSION['role'] === $role;
}

function redirectIfNotAdmin() {
    if (!hasRole('Super Admin') && !hasRole('Department Officer')) {
        header("Location: " . ADMIN_URL . "dashboard.php?error=unauthorized");
        exit();
    }
}

function showAlert($message, $type = 'success') {
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">' . 
           $message . 
           '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

function formatPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) == 11 && $phone[0] == '0') {
        return '234' . substr($phone, 1);
    }
    if (strlen($phone) == 13 && substr($phone, 0, 3) == '234') {
        return $phone;
    }
    if (strlen($phone) == 10) {
        return '234' . $phone;
    }
    return $phone;
}

checkSession();
?>
