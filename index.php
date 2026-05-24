<?php
/**
 * Root Router entry point
 * Student Record Management System
 */
require_once(__DIR__ . '/config.php');

// Redirect to dashboard or login depending on session status
if (isset($_SESSION['alogin']) && strlen($_SESSION['alogin']) > 0) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit;
?>
