<?php
/**
 * Shared Page Header
 * Student Record Management System
 */
require_once('config.php');
check_login();

// Fetch Admin details
$admin_name = $_SESSION['aname'] ?? 'Administrator';
$admin_email = $_SESSION['alogin'] ?? 'admin@mail.com';
$active_session = get_active_session_name($dbh);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRMS | Student Record Management System</title>
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- FontAwesome Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Glowing spheres backgrounds -->
    <div class="glowing-bg">
        <div class="circle circle-1"></div>
        <div class="circle circle-2"></div>
    </div>

    <!-- Master Layout Wrapper -->
    <div class="wrapper">
        
        <!-- Sidebar Navigation Included -->
        <?php include('sidebar.php'); ?>

        <!-- Right Side Master Layout -->
        <div class="main-content">
            
            <!-- Top Navigation bar -->
            <nav class="top-navbar">
                <div class="navbar-left">
                    <button class="menu-toggle-btn" id="menuToggle" title="Toggle Navigation Menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <span class="navbar-session" title="Active Academic Session">
                        <i class="far fa-calendar-check"></i>
                        Session: <?php echo htmlspecialchars($active_session); ?>
                    </span>
                </div>
                
                <div class="navbar-right">
                    <!-- User Profile Dropdown Widget -->
                    <div class="user-profile" onclick="window.location.href='admin-profile.php'" title="Manage Profile">
                        <div class="user-profile-img">
                            <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
                        </div>
                        <div class="user-profile-name">
                            <?php echo htmlspecialchars($admin_name); ?>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Begin Page Content Core -->
            <div class="content-body">
                
                <!-- Print Session Messages (Toasts) if any -->
                <?php echo display_message(); ?>
