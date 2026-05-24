<?php
/**
 * Administrator Dashboard Dashboard
 * Student Record Management System
 */
require('header.php');

// Fetch Metrics using PDO
try {
    // Total Courses
    $stmt = $dbh->prepare("SELECT COUNT(*) as count FROM tbl_course");
    $stmt->execute();
    $total_courses = $stmt->fetch()['count'];
    
    // Total Subjects
    $stmt = $dbh->prepare("SELECT COUNT(*) as count FROM subject");
    $stmt->execute();
    $total_subjects = $stmt->fetch()['count'];

    // Total Students
    $stmt = $dbh->prepare("SELECT COUNT(*) as count FROM registration");
    $stmt->execute();
    $total_students = $stmt->fetch()['count'];

    // Total Countries
    $stmt = $dbh->prepare("SELECT COUNT(*) as count FROM countries");
    $stmt->execute();
    $total_countries = $stmt->fetch()['count'];

    // Total States
    $stmt = $dbh->prepare("SELECT COUNT(*) as count FROM states");
    $stmt->execute();
    $total_states = $stmt->fetch()['count'];

    // Total Cities
    $stmt = $dbh->prepare("SELECT COUNT(*) as count FROM cities");
    $stmt->execute();
    $total_cities = $stmt->fetch()['count'];

    // Fetch 5 most recent registrations
    $stmt_recent = $dbh->prepare("SELECT r.student_name, r.student_reg_no, c.course_code, r.reg_date 
                                  FROM registration r 
                                  LEFT JOIN tbl_course c ON r.course_id = c.id 
                                  ORDER BY r.id DESC LIMIT 5");
    $stmt_recent->execute();
    $recent_students = $stmt_recent->fetchAll();

} catch (PDOException $e) {
    echo "<div class='alert-toast alert-toast-danger'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<!-- Header Section -->
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard Overview</h1>
        <div class="page-breadcrumbs">Home / Administrative Dashboard</div>
    </div>
    <div style="font-size: 13px; font-weight: 500; color: var(--text-secondary); background: var(--bg-card); border: 1px solid var(--border-card); padding: 8px 16px; border-radius: 12px; display: flex; align-items: center; gap: 8px;">
        <i class="far fa-clock" style="color: var(--primary);"></i>
        <span>Current Date: <?php echo date('d M, Y'); ?></span>
    </div>
</div>

<!-- Metrics Cards Grid -->
<div class="metrics-grid">
    
    <!-- Total Courses Card -->
    <div class="metric-card">
        <div class="metric-info">
            <h3>Total Courses</h3>
            <div class="metric-number"><?php echo $total_courses; ?></div>
        </div>
        <div class="metric-icon-box">
            <i class="fas fa-book-open"></i>
        </div>
    </div>
    
    <!-- Total Subjects Card -->
    <div class="metric-card">
        <div class="metric-info">
            <h3>Total Subjects</h3>
            <div class="metric-number"><?php echo $total_subjects; ?></div>
        </div>
        <div class="metric-icon-box">
            <i class="fas fa-tags"></i>
        </div>
    </div>
    
    <!-- Total Students Card -->
    <div class="metric-card">
        <div class="metric-info">
            <h3>Registered Students</h3>
            <div class="metric-number"><?php echo $total_students; ?></div>
        </div>
        <div class="metric-icon-box">
            <i class="fas fa-user-graduate"></i>
        </div>
    </div>
    
    <!-- Total Locations Grid details -->
    <div class="metric-card">
        <div class="metric-info">
            <h3>Countries Listed</h3>
            <div class="metric-number"><?php echo $total_countries; ?></div>
        </div>
        <div class="metric-icon-box">
            <i class="fas fa-globe"></i>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-info">
            <h3>States Covered</h3>
            <div class="metric-number"><?php echo $total_states; ?></div>
        </div>
        <div class="metric-icon-box">
            <i class="fas fa-map-location-dot"></i>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-info">
            <h3>Cities covered</h3>
            <div class="metric-number"><?php echo $total_cities; ?></div>
        </div>
        <div class="metric-icon-box">
            <i class="fas fa-city"></i>
        </div>
    </div>
</div>

<!-- Extra Details Panel -->
<div class="glass-panel" style="margin-top: 20px;">
    <div class="glass-panel-title">
        <span><i class="fas fa-user-clock" style="color: var(--primary); margin-right: 8px;"></i> Recent Student Enrollments</span>
        <button class="btn btn-secondary" onclick="window.location.href='view-students.php'" style="padding: 6px 12px; font-size: 13px;">View All Students</button>
    </div>
    
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Registration No</th>
                    <th>Student Name</th>
                    <th>Course Code</th>
                    <th>Enrollment Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($recent_students) > 0): ?>
                    <?php foreach ($recent_students as $row): ?>
                        <tr>
                            <td><strong style="color: var(--primary);"><?php echo htmlspecialchars($row['student_reg_no']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                            <td><span style="background: rgba(168, 85, 247, 0.15); color: var(--secondary); padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;"><?php echo htmlspecialchars($row['course_code']); ?></span></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($row['reg_date'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 40px;">
                            <i class="fas fa-users" style="font-size: 2.5rem; color: var(--border-card); display: block; margin-bottom: 10px;"></i>
                            No students registered yet. Click <a href="register-student.php" style="font-weight:600;">here</a> to register a student.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once('footer.php');
?>
