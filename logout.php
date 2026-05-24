<?php
/**
 * Admin Logout Controller
 * Student Record Management System
 */
require_once(__DIR__ . '/config.php');

// Unset all session variables
$_SESSION = array();

// Destroy session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy active session
session_destroy();

// Start a fresh secure session for the next user and set a logout notification
session_start();
set_message('info', 'You have been successfully signed out of the dashboard.');

// Redirect to login portal
header("Location: login.php");
exit;
?>
