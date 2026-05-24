<?php
/**
 * Shared Sidebar Navigation
 * Student Record Management System
 */

$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="dashboard.php" class="sidebar-logo">
            <i class="fas fa-graduation-cap"></i>
            <span>SRMS Portal</span>
        </a>
    </div>
    
    <ul class="sidebar-menu">
        <!-- Dashboard Core Link -->
        <li class="sidebar-menu-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <a href="dashboard.php" class="sidebar-menu-link">
                <i class="fas fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <!-- Courses Link -->
        <li class="sidebar-menu-item <?php echo ($current_page == 'manage-courses.php') ? 'active' : ''; ?>">
            <a href="manage-courses.php" class="sidebar-menu-link">
                <i class="fas fa-book-open"></i>
                <span>Courses</span>
            </a>
        </li>
        
        <!-- Subjects Link -->
        <li class="sidebar-menu-item <?php echo ($current_page == 'manage-subjects.php') ? 'active' : ''; ?>">
            <a href="manage-subjects.php" class="sidebar-menu-link">
                <i class="fas fa-tags"></i>
                <span>Subjects</span>
            </a>
        </li>
        
        <!-- Academic Sessions Link -->
        <li class="sidebar-menu-item <?php echo ($current_page == 'manage-session.php') ? 'active' : ''; ?>">
            <a href="manage-session.php" class="sidebar-menu-link">
                <i class="fas fa-calendar-alt"></i>
                <span>Academic Session</span>
            </a>
        </li>
        
        <!-- Student Registration Link -->
        <li class="sidebar-menu-item <?php echo ($current_page == 'register-student.php') ? 'active' : ''; ?>">
            <a href="register-student.php" class="sidebar-menu-link">
                <i class="fas fa-user-plus"></i>
                <span>Register Student</span>
            </a>
        </li>
        
        <!-- View Students Link -->
        <li class="sidebar-menu-item <?php echo ($current_page == 'view-students.php') ? 'active' : ''; ?>">
            <a href="view-students.php" class="sidebar-menu-link">
                <i class="fas fa-users-viewfinder"></i>
                <span>View Students</span>
            </a>
        </li>
        
        <!-- Divider Style menu label -->
        <li style="padding: 15px 16px 5px 16px; font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700; font-family: var(--font-heading);">
            Administrative
        </li>

        <!-- Admin Profile Link -->
        <li class="sidebar-menu-item <?php echo ($current_page == 'admin-profile.php') ? 'active' : ''; ?>">
            <a href="admin-profile.php" class="sidebar-menu-link">
                <i class="fas fa-user-gear"></i>
                <span>Admin Profile</span>
            </a>
        </li>
        
        <!-- Change Password Link -->
        <li class="sidebar-menu-item <?php echo ($current_page == 'change-password.php') ? 'active' : ''; ?>">
            <a href="change-password.php" class="sidebar-menu-link">
                <i class="fas fa-shield-halved"></i>
                <span>Change Password</span>
            </a>
        </li>
        
        <!-- Logout Link -->
        <li class="sidebar-menu-item" style="margin-top: 15px;">
            <a href="logout.php" class="sidebar-menu-link" style="color: var(--danger); border-color: rgba(239, 68, 68, 0.2);">
                <i class="fas fa-arrow-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</aside>
