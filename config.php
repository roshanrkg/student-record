<?php
/**
 * Application Database Connection and Security Settings
 * Student Record Management System
 */

// Start session securely
if (session_status() == PHP_SESSION_NONE) {
    // Set secure cookie flags if supported
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    
    session_start();
}

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student_record_db');

try {
    // Establish PDO connection
    $dbh = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, array(
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ));
} catch (PDOException $e) {
    // In production, log error. For local setup, print clean explanation
    die("<div style='font-family: sans-serif; padding: 2rem; background: #fff5f5; color: #c53030; border-radius: 8px; margin: 2rem auto; max-width: 600px; border: 1px solid #feb2b2;'>
            <h3 style='margin-top:0;'>Database Connection Failed!</h3>
            <p>Could not connect to the database <strong>" . DB_NAME . "</strong> on <strong>" . DB_HOST . "</strong>.</p>
            <p><strong>Steps to resolve:</strong></p>
            <ol>
                <li>Make sure your XAMPP/WAMP/MAMP server is running with MySQL enabled.</li>
                <li>Import the <a href='database.sql' style='color:#c53030; font-weight:bold;'>database.sql</a> file using phpMyAdmin or terminal.</li>
                <li>Verify your database settings in <code>config.php</code> file.</li>
            </ol>
            <p style='font-size: 0.85em; color: #742a2a; border-top: 1px dashed #fecaca; padding-top: 10px;'>Error Detail: " . htmlspecialchars($e->getMessage()) . "</p>
         </div>");
}

/**
 * Security Helpers
 */

// Generate CSRF Token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF Token
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Sanitize inputs
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Check Admin Authentication
function check_login() {
    if (!isset($_SESSION['alogin']) || strlen($_SESSION['alogin']) == 0) {
        header("Location: login.php");
        exit;
    }
}

// Set message notifications
function set_message($type, $msg) {
    $_SESSION['msg_type'] = $type; // 'success' or 'danger' or 'info'
    $_SESSION['msg_text'] = $msg;
}

// Display message notifications
function display_message() {
    if (isset($_SESSION['msg_text'])) {
        $type = $_SESSION['msg_type'] ?? 'info';
        $text = $_SESSION['msg_text'];
        unset($_SESSION['msg_type']);
        unset($_SESSION['msg_text']);
        
        $icon = 'info-circle';
        if ($type === 'success') $icon = 'check-circle';
        if ($type === 'danger') $icon = 'exclamation-circle';
        
        return "
        <div class='alert-toast alert-toast-{$type}' id='alertToast'>
            <i class='fas fa-{$icon}'></i>
            <span>" . htmlspecialchars($text) . "</span>
            <button type='button' class='toast-close' onclick='document.getElementById(\"alertToast\").remove()'>&times;</button>
        </div>";
    }
    return '';
}

// Fetch Active Session name
function get_active_session_name($dbh) {
    try {
        $stmt = $dbh->prepare("SELECT session_name FROM session WHERE status = 1 LIMIT 1");
        $stmt->execute();
        $res = $stmt->fetch();
        return $res ? $res['session_name'] : 'No Active Session';
    } catch (PDOException $e) {
        return 'Unknown';
    }
}
?>
