<?php
/**
 * View Registered Students Module
 * Student Record Management System
 */
require_once('header.php');

$error = '';
$success = '';

// Delete Student Action processing
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $sid = intval($_GET['id']);
    try {
        $stmt = $dbh->prepare("DELETE FROM registration WHERE id = :sid");
        $stmt->execute(array(':sid' => $sid));
        set_message('success', 'Student record deleted successfully.');
    } catch (PDOException $e) {
        set_message('danger', 'System error: ' . $e->getMessage());
    }
    header("Location: view-students.php");
    exit;
}

// Fetch all registered students using clean relational JOINs
try {
    $stmt = $dbh->prepare("SELECT r.*, c.course_code, c.course_name, s.session_name, 
                                  co.name as country_name, st.name as state_name, ci.name as city_name 
                           FROM registration r
                           LEFT JOIN tbl_course c ON r.course_id = c.id
                           LEFT JOIN session s ON r.session_id = s.id
                           LEFT JOIN countries co ON r.country_id = co.id
                           LEFT JOIN states st ON r.state_id = st.id
                           LEFT JOIN cities ci ON r.city_id = ci.id
                           ORDER BY r.id DESC");
    $stmt->execute();
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error listing student profiles: ' . $e->getMessage();
}
?>

<!-- Header Section -->
<div class="page-header">
    <div>
        <h1 class="page-title">Students Registry</h1>
        <div class="page-breadcrumbs">Home / Student / View Students</div>
    </div>
    
    <button class="btn btn-primary" onclick="window.location.href='register-student.php'">
        <i class="fas fa-user-plus"></i>
        <span>Enroll New Student</span>
    </button>
</div>

<!-- Registry Panel -->
<div class="glass-panel">
    <div class="glass-panel-title">
        <span>Registered Profiles (<?php echo count($students); ?>)</span>
        
        <!-- Live Instant Search -->
        <div class="search-box">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" id="studentSearch" class="form-control search-control" placeholder="Quick search details...">
        </div>
    </div>
    
    <!-- Table content -->
    <div class="table-responsive">
        <table class="table-custom" id="studentsTable">
            <thead>
                <tr>
                    <th>Reg No</th>
                    <th>Full Name</th>
                    <th>Course Code</th>
                    <th>Session</th>
                    <th>Email / Contact</th>
                    <th>Location Info</th>
                    <th>Enrollment Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($students) > 0): ?>
                    <?php foreach ($students as $row): ?>
                        <tr>
                            <!-- Reg Number highlight -->
                            <td>
                                <strong style="color: var(--primary); font-family: var(--font-heading);"><?php echo htmlspecialchars($row['student_reg_no']); ?></strong>
                            </td>
                            
                            <!-- Name and gender visual icon -->
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <?php if ($row['gender'] == 'Male'): ?>
                                        <i class="fas fa-circle-user" style="color: var(--info);" title="Male"></i>
                                    <?php elseif ($row['gender'] == 'Female'): ?>
                                        <i class="fas fa-circle-user" style="color: var(--secondary);" title="Female"></i>
                                    <?php else: ?>
                                        <i class="fas fa-circle-user" style="color: var(--text-muted);" title="Other"></i>
                                    <?php endif; ?>
                                    <span style="font-weight: 600;"><?php echo htmlspecialchars($row['student_name']); ?></span>
                                </div>
                            </td>
                            
                            <!-- Course Badge -->
                            <td>
                                <span style="background: rgba(168, 85, 247, 0.15); color: var(--secondary); padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; border: 1px solid rgba(168, 85, 247, 0.2);" title="<?php echo htmlspecialchars($row['course_name']); ?>">
                                    <?php echo htmlspecialchars($row['course_code']); ?>
                                </span>
                            </td>
                            
                            <!-- Session name -->
                            <td>
                                <span style="font-size: 13px; font-weight: 500; color: var(--text-secondary);"><?php echo htmlspecialchars($row['session_name']); ?></span>
                            </td>
                            
                            <!-- Contact Details email/mobile -->
                            <td>
                                <div style="font-size: 13px; display: flex; flex-direction: column;">
                                    <span><i class="far fa-envelope" style="width: 16px; color: var(--text-muted);"></i> <?php echo htmlspecialchars($row['email']); ?></span>
                                    <span style="color: var(--text-secondary);"><i class="fas fa-phone-flip" style="width: 16px; color: var(--text-muted);"></i> <?php echo htmlspecialchars($row['mobile']); ?></span>
                                </div>
                            </td>
                            
                            <!-- Complete geographic details -->
                            <td>
                                <div style="font-size: 12px; color: var(--text-secondary); display: flex; flex-direction: column;">
                                    <span><strong><?php echo htmlspecialchars($row['city_name']); ?></strong>, <?php echo htmlspecialchars($row['state_name']); ?></span>
                                    <span style="color: var(--text-muted);"><?php echo htmlspecialchars($row['country_name']); ?></span>
                                </div>
                            </td>
                            
                            <!-- Enrollment Date -->
                            <td>
                                <span style="font-size: 13px; color: var(--text-secondary);"><?php echo date('d M Y', strtotime($row['reg_date'])); ?></span>
                            </td>
                            
                            <!-- Dynamic Actions menu -->
                            <td style="text-align: right;">
                                <div class="action-btns" style="justify-content: flex-end;">
                                    <!-- Edit details -->
                                    <a href="edit-student.php?id=<?php echo $row['id']; ?>" class="btn-icon btn-icon-edit" title="Edit Student Profile">
                                        <i class="fas fa-user-pen"></i>
                                    </a>
                                    <!-- Delete record -->
                                    <a href="view-students.php?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete the student profile of <?php echo addslashes($row['student_name']); ?>? This operation is permanent!')" class="btn-icon btn-icon-delete" title="Delete Student Profile">
                                        <i class="fas fa-trash-can"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 50px;">
                            <i class="fas fa-users-slash" style="font-size: 3rem; color: var(--border-card); display: block; margin-bottom: 15px;"></i>
                            No students registered in the system database. Enroll a new student profile to get started!
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Bind search engine
    document.addEventListener('DOMContentLoaded', function() {
        initSearchFilter('studentSearch', 'studentsTable');
    });
</script>

<?php
require_once('footer.php');
?>
