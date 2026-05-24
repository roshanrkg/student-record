<?php
/**
 * Course Management Module
 * Student Record Management System
 */
require_once('header.php');

$error = '';
$success = '';
$edit_mode = false;
$edit_course = array('id' => '', 'course_code' => '', 'course_name' => '');

// Check if Edit mode is active
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $cid = intval($_GET['id']);
    try {
        $stmt = $dbh->prepare("SELECT * FROM tbl_course WHERE id = :cid LIMIT 1");
        $stmt->execute(array(':cid' => $cid));
        $res = $stmt->fetch();
        if ($res) {
            $edit_mode = true;
            $edit_course = $res;
        } else {
            set_message('danger', 'Course not found.');
        }
    } catch (PDOException $e) {
        set_message('danger', 'System error: ' . $e->getMessage());
    }
}

// Add Course Action
if (isset($_POST['add_course'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_message('danger', 'Security check failed. Please refresh and try again.');
    } else {
        $course_code = sanitize($_POST['course_code']);
        $course_name = sanitize($_POST['course_name']);
        
        if (empty($course_code) || empty($course_name)) {
            set_message('danger', 'Both course code and name are required.');
        } else {
            try {
                // Check code uniqueness
                $stmt = $dbh->prepare("SELECT COUNT(*) FROM tbl_course WHERE course_code = :ccode");
                $stmt->execute(array(':ccode' => $course_code));
                if ($stmt->fetchColumn() > 0) {
                    set_message('danger', 'Course code ' . $course_code . ' is already in use.');
                } else {
                    $stmt = $dbh->prepare("INSERT INTO tbl_course (course_code, course_name) VALUES (:ccode, :cname)");
                    $stmt->execute(array(':ccode' => $course_code, ':cname' => $course_name));
                    set_message('success', 'Course ' . $course_name . ' (' . $course_code . ') added successfully.');
                }
            } catch (PDOException $e) {
                set_message('danger', 'System error: ' . $e->getMessage());
            }
        }
    }
   echo "<script> window.Location.href('manage-courses.php')</script>";
    exit;
}

// Update Course Action
if (isset($_POST['update_course'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_message('danger', 'Security check failed. Please refresh and try again.');
    } else {
        $cid = intval($_POST['course_id']);
        $course_code = sanitize($_POST['course_code']);
        $course_name = sanitize($_POST['course_name']);
        
        if (empty($course_code) || empty($course_name)) {
            set_message('danger', 'Both course code and name are required.');
        } else {
            try {
                // Check code uniqueness excluding current course
                $stmt = $dbh->prepare("SELECT COUNT(*) FROM tbl_course WHERE course_code = :ccode AND id != :cid");
                $stmt->execute(array(':ccode' => $course_code, ':cid' => $cid));
                if ($stmt->fetchColumn() > 0) {
                    set_message('danger', 'Course code ' . $course_code . ' is already in use by another program.');
                } else {
                    $stmt = $dbh->prepare("UPDATE tbl_course SET course_code = :ccode, course_name = :cname WHERE id = :cid");
                    $stmt->execute(array(':ccode' => $course_code, ':cname' => $course_name, ':cid' => $cid));
                    set_message('success', 'Course details updated successfully.');
                }
            } catch (PDOException $e) {
                set_message('danger', 'System error: ' . $e->getMessage());
            }
        }
    }
       echo "<script> window.Location.href('manage-courses.php')</script>";
    exit;
}

// Delete Course Action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $cid = intval($_GET['id']);
    try {
        $stmt = $dbh->prepare("DELETE FROM tbl_course WHERE id = :cid");
        $stmt->execute(array(':cid' => $cid));
        set_message('success', 'Course and its related subjects deleted successfully.');
    } catch (PDOException $e) {
        set_message('danger', 'System error: ' . $e->getMessage());
    }
 echo "<script> window.Location.href('manage-courses.php')</script>";
    exit;
}

// Fetch all courses
try {
    $stmt = $dbh->prepare("SELECT * FROM tbl_course ORDER BY id DESC");
    $stmt->execute();
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error listing courses: ' . $e->getMessage();
}

$csrf_token = generate_csrf_token();
?>

<!-- Header Section -->
<div class="page-header">
    <div>
        <h1 class="page-title">Manage Courses</h1>
        <div class="page-breadcrumbs">Home / Curriculum / Courses</div>
    </div>
</div>

<div class="grid-3">
    
    <!-- Left form card: Add or Edit depending on mode -->
    <div class="glass-panel" style="grid-column: span 1; align-self: flex-start;">
        <?php if ($edit_mode): ?>
            <h3 class="glass-panel-title">Edit Course Details</h3>
            
            <form action="manage-courses.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="course_id" value="<?php echo $edit_course['id']; ?>">
                
                <!-- Course Code -->
                <div class="form-group">
                    <label for="course_code">Course Code</label>
                    <div class="input-group">
                        <input type="text" id="course_code" name="course_code" class="form-control" placeholder="e.g. CS101" value="<?php echo htmlspecialchars($edit_course['course_code']); ?>" required>
                        <i class="fas fa-barcode"></i>
                    </div>
                </div>
                
                <!-- Course Name -->
                <div class="form-group">
                    <label for="course_name">Course Name</label>
                    <div class="input-group">
                        <input type="text" id="course_name" name="course_name" class="form-control" placeholder="e.g. B.Tech Computer Science" value="<?php echo htmlspecialchars($edit_course['course_name']); ?>" required>
                        <i class="fas fa-book"></i>
                    </div>
                </div>
                
                <!-- Actions -->
                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="submit" name="update_course" class="btn btn-primary" style="flex-grow: 1;">
                        <i class="fas fa-check"></i>
                        <span>Update</span>
                    </button>
                    <a href="manage-courses.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
            
        <?php else: ?>
            <h3 class="glass-panel-title">Add New Course</h3>
            
            <form action="manage-courses.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <!-- Course Code -->
                <div class="form-group">
                    <label for="course_code">Course Code</label>
                    <div class="input-group">
                        <input type="text" id="course_code" name="course_code" class="form-control" placeholder="e.g. CS101" required>
                        <i class="fas fa-barcode"></i>
                    </div>
                </div>
                
                <!-- Course Name -->
                <div class="form-group">
                    <label for="course_name">Course Name</label>
                    <div class="input-group">
                        <input type="text" id="course_name" name="course_name" class="form-control" placeholder="e.g. Bachelor of Technology" required>
                        <i class="fas fa-book"></i>
                    </div>
                </div>
                
                <button type="submit" name="add_course" class="btn btn-primary btn-block" style="margin-top: 25px;">
                    <i class="fas fa-plus"></i>
                    <span>Create Course</span>
                </button>
            </form>
        <?php endif; ?>
    </div>
    
    <!-- Right list card: Courses Table List -->
    <div class="glass-panel" style="grid-column: span 2;">
        <div class="glass-panel-title">
            <span>All Courses (<?php echo count($courses); ?>)</span>
            
            <!-- Quick Filter Bar -->
            <div class="search-box">
                <i class="fas fa-magnifying-glass"></i>
                <input type="text" id="courseSearch" class="form-control search-control" placeholder="Filter courses...">
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table-custom" id="coursesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Course Name</th>
                        <th>Creation Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($courses) > 0): ?>
                        <?php $cnt = 1; foreach ($courses as $row): ?>
                            <tr>
                                <td><?php echo $cnt++; ?></td>
                                <td><span style="background: rgba(99, 102, 241, 0.15); color: var(--primary); padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; border: 1px solid rgba(99, 102, 241, 0.2);"><?php echo htmlspecialchars($row['course_code']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($row['course_name']); ?></strong></td>
                                <td><?php echo date('d M Y', strtotime($row['creation_date'])); ?></td>
                                <td style="text-align: right;">
                                    <div class="action-btns" style="justify-content: flex-end;">
                                        <!-- Edit -->
                                        <a href="manage-courses.php?action=edit&id=<?php echo $row['id']; ?>" class="btn-icon btn-icon-edit" title="Edit Course Details">
                                            <i class="fas fa-pencil"></i>
                                        </a>
                                        <!-- Delete -->
                                        <a href="manage-courses.php?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this course? This will remove all associated subjects and student enrollments!')" class="btn-icon btn-icon-delete" title="Delete Course">
                                            <i class="fas fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                                No courses found in database. Create one now.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Bind search engine
    document.addEventListener('DOMContentLoaded', function() {
        initSearchFilter('courseSearch', 'coursesTable');
    });
</script>

<?php
require_once('footer.php');
?>
