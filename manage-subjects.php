<?php
/**
 * Subject Management Module
 * Student Record Management System
 */
require_once('header.php');

$error = '';
$success = '';
$edit_mode = false;
$edit_sub = array('id' => '', 'course_id' => '', 'subject_code' => '', 'subject_name' => '');

// Fetch active courses list for selectors
try {
    $stmt = $dbh->prepare("SELECT id, course_code, course_name FROM tbl_course ORDER BY course_code ASC");
    $stmt->execute();
    $courses_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error listing courses: ' . $e->getMessage();
}

// Check if Edit mode is active
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $sid = intval($_GET['id']);
    try {
        $stmt = $dbh->prepare("SELECT * FROM subject WHERE id = :sid LIMIT 1");
        $stmt->execute(array(':sid' => $sid));
        $res = $stmt->fetch();
        if ($res) {
            $edit_mode = true;
            $edit_sub = $res;
        } else {
            set_message('danger', 'Subject not found.');
        }
    } catch (PDOException $e) {
        set_message('danger', 'System error: ' . $e->getMessage());
    }
}

// Add Subject Action
if (isset($_POST['add_subject'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_message('danger', 'Security check failed. Please refresh and try again.');
    } else {
        $course_id = intval($_POST['course_id']);
        $subject_code = sanitize($_POST['subject_code']);
        $subject_name = sanitize($_POST['subject_name']);
        
        if ($course_id <= 0 || empty($subject_code) || empty($subject_name)) {
            set_message('danger', 'Please provide a course, subject code, and subject name.');
        } else {
            try {
                // Check code uniqueness
                $stmt = $dbh->prepare("SELECT COUNT(*) FROM subject WHERE subject_code = :scode");
                $stmt->execute(array(':scode' => $subject_code));
                if ($stmt->fetchColumn() > 0) {
                    set_message('danger', 'Subject code ' . $subject_code . ' is already in use.');
                } else {
                    $stmt = $dbh->prepare("INSERT INTO subject (course_id, subject_code, subject_name) VALUES (:cid, :scode, :sname)");
                    $stmt->execute(array(':cid' => $course_id, ':scode' => $subject_code, ':sname' => $subject_name));
                    set_message('success', 'Subject ' . $subject_name . ' (' . $subject_code . ') added successfully.');
                }
            } catch (PDOException $e) {
                set_message('danger', 'System error: ' . $e->getMessage());
            }
        }
    }
    header("Location: manage-subjects.php");
    exit;
}

// Update Subject Action
if (isset($_POST['update_subject'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_message('danger', 'Security check failed. Please refresh and try again.');
    } else {
        $sid = intval($_POST['subject_id']);
        $course_id = intval($_POST['course_id']);
        $subject_code = sanitize($_POST['subject_code']);
        $subject_name = sanitize($_POST['subject_name']);
        
        if ($course_id <= 0 || empty($subject_code) || empty($subject_name)) {
            set_message('danger', 'Please provide a course, subject code, and subject name.');
        } else {
            try {
                // Check code uniqueness excluding current subject
                $stmt = $dbh->prepare("SELECT COUNT(*) FROM subject WHERE subject_code = :scode AND id != :sid");
                $stmt->execute(array(':scode' => $subject_code, ':sid' => $sid));
                if ($stmt->fetchColumn() > 0) {
                    set_message('danger', 'Subject code ' . $subject_code . ' is already in use by another subject.');
                } else {
                    $stmt = $dbh->prepare("UPDATE subject SET course_id = :cid, subject_code = :scode, subject_name = :sname WHERE id = :sid");
                    $stmt->execute(array(':cid' => $course_id, ':scode' => $subject_code, ':sname' => $subject_name, ':sid' => $sid));
                    set_message('success', 'Subject details updated successfully.');
                }
            } catch (PDOException $e) {
                set_message('danger', 'System error: ' . $e->getMessage());
            }
        }
    }
    header("Location: manage-subjects.php");
    exit;
}

// Delete Subject Action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $sid = intval($_GET['id']);
    try {
        $stmt = $dbh->prepare("DELETE FROM subject WHERE id = :sid");
        $stmt->execute(array(':sid' => $sid));
        set_message('success', 'Subject deleted successfully.');
    } catch (PDOException $e) {
        set_message('danger', 'System error: ' . $e->getMessage());
    }
    header("Location: manage-subjects.php");
    exit;
}

// Filter subjects by Course if filter is set
$filter_course_id = isset($_GET['filter_course']) ? intval($_GET['filter_course']) : 0;

// Fetch subjects
try {
    $query = "SELECT s.*, c.course_name, c.course_code FROM subject s LEFT JOIN tbl_course c ON s.course_id = c.id";
    if ($filter_course_id > 0) {
        $query .= " WHERE s.course_id = :fcid";
    }
    $query .= " ORDER BY c.course_code ASC, s.subject_code ASC";
    
    $stmt = $dbh->prepare($query);
    if ($filter_course_id > 0) {
        $stmt->execute(array(':fcid' => $filter_course_id));
    } else {
        $stmt->execute();
    }
    $subjects = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error listing subjects: ' . $e->getMessage();
}

$csrf_token = generate_csrf_token();
?>

<!-- Header Section -->
<div class="page-header">
    <div>
        <h1 class="page-title">Manage Subjects</h1>
        <div class="page-breadcrumbs">Home / Curriculum / Subjects</div>
    </div>
</div>

<div class="grid-3">
    
    <!-- Left form card: Add or Edit depending on mode -->
    <div class="glass-panel" style="grid-column: span 1; align-self: flex-start;">
        <?php if ($edit_mode): ?>
            <h3 class="glass-panel-title">Edit Subject Details</h3>
            
            <form action="manage-subjects.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="subject_id" value="<?php echo $edit_sub['id']; ?>">
                
                <!-- Course Selector -->
                <div class="form-group">
                    <label for="course_id">Course Program</label>
                    <div class="input-group">
                        <select id="course_id" name="course_id" class="form-control" required>
                            <option value="">-- Choose Course --</option>
                            <?php foreach ($courses_list as $row): ?>
                                <option value="<?php echo $row['id']; ?>" <?php echo ($edit_sub['course_id'] == $row['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($row['course_code'] . ' - ' . $row['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-book"></i>
                    </div>
                </div>
                
                <!-- Subject Code -->
                <div class="form-group">
                    <label for="subject_code">Subject Code</label>
                    <div class="input-group">
                        <input type="text" id="subject_code" name="subject_code" class="form-control" placeholder="e.g. CS-301" value="<?php echo htmlspecialchars($edit_sub['subject_code']); ?>" required>
                        <i class="fas fa-barcode"></i>
                    </div>
                </div>
                
                <!-- Subject Name -->
                <div class="form-group">
                    <label for="subject_name">Subject Name</label>
                    <div class="input-group">
                        <input type="text" id="subject_name" name="subject_name" class="form-control" placeholder="e.g. Advanced Databases" value="<?php echo htmlspecialchars($edit_sub['subject_name']); ?>" required>
                        <i class="fas fa-tag"></i>
                    </div>
                </div>
                
                <!-- Actions -->
                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="submit" name="update_subject" class="btn btn-primary" style="flex-grow: 1;">
                        <i class="fas fa-check"></i>
                        <span>Update</span>
                    </button>
                    <a href="manage-subjects.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
            
        <?php else: ?>
            <h3 class="glass-panel-title">Add New Subject</h3>
            
            <form action="manage-subjects.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <!-- Course Selector -->
                <div class="form-group">
                    <label for="course_id">Course Program</label>
                    <div class="input-group">
                        <select id="course_id" name="course_id" class="form-control" required>
                            <option value="">-- Choose Course --</option>
                            <?php foreach ($courses_list as $row): ?>
                                <option value="<?php echo $row['id']; ?>">
                                    <?php echo htmlspecialchars($row['course_code'] . ' - ' . $row['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-book"></i>
                    </div>
                </div>
                
                <!-- Subject Code -->
                <div class="form-group">
                    <label for="subject_code">Subject Code</label>
                    <div class="input-group">
                        <input type="text" id="subject_code" name="subject_code" class="form-control" placeholder="e.g. CS-301" required>
                        <i class="fas fa-barcode"></i>
                    </div>
                </div>
                
                <!-- Subject Name -->
                <div class="form-group">
                    <label for="subject_name">Subject Name</label>
                    <div class="input-group">
                        <input type="text" id="subject_name" name="subject_name" class="form-control" placeholder="e.g. Web Programming" required>
                        <i class="fas fa-tag"></i>
                    </div>
                </div>
                
                <button type="submit" name="add_subject" class="btn btn-primary btn-block" style="margin-top: 25px;">
                    <i class="fas fa-plus"></i>
                    <span>Create Subject</span>
                </button>
            </form>
        <?php endif; ?>
    </div>
    
    <!-- Right list card: Subjects Table List -->
    <div class="glass-panel" style="grid-column: span 2;">
        <div class="glass-panel-title" style="flex-wrap: wrap; gap: 15px;">
            <span>Subjects Covered (<?php echo count($subjects); ?>)</span>
            
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <!-- Filter Dropdown -->
                <div class="input-group">
                    <select id="courseFilter" class="form-control form-control-no-icon" style="padding: 6px 12px; font-size: 13px; height: 38px; width: 180px;" onchange="filterByCourse()">
                        <option value="0">All Courses</option>
                        <?php foreach ($courses_list as $row): ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo ($filter_course_id == $row['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['course_code']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Quick Filter Bar -->
                <div class="search-box">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" id="subjectSearch" class="form-control search-control" style="height: 38px;" placeholder="Filter subjects...">
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table-custom" id="subjectsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subject Code</th>
                        <th>Subject Title</th>
                        <th>Course</th>
                        <th>Creation Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($subjects) > 0): ?>
                        <?php $cnt = 1; foreach ($subjects as $row): ?>
                            <tr>
                                <td><?php echo $cnt++; ?></td>
                                <td><span style="background: rgba(168, 85, 247, 0.15); color: var(--secondary); padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; border: 1px solid rgba(168, 85, 247, 0.2);"><?php echo htmlspecialchars($row['subject_code']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($row['subject_name']); ?></strong></td>
                                <td style="color: var(--text-secondary); font-size: 13px; font-weight: 500;">
                                    <?php echo htmlspecialchars($row['course_code'] . ' - ' . $row['course_name']); ?>
                                </td>
                                <td><?php echo date('d M Y', strtotime($row['creation_date'])); ?></td>
                                <td style="text-align: right;">
                                    <div class="action-btns" style="justify-content: flex-end;">
                                        <!-- Edit -->
                                        <a href="manage-subjects.php?action=edit&id=<?php echo $row['id']; ?>&filter_course=<?php echo $filter_course_id; ?>" class="btn-icon btn-icon-edit" title="Edit Subject Details">
                                            <i class="fas fa-pencil"></i>
                                        </a>
                                        <!-- Delete -->
                                        <a href="manage-subjects.php?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this subject?')" class="btn-icon btn-icon-delete" title="Delete Subject">
                                            <i class="fas fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                                No subjects found. Choose another course or create a new subject.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Handle filter redirection
    function filterByCourse() {
        const cid = document.getElementById('courseFilter').value;
        window.location.href = 'manage-subjects.php?filter_course=' + cid;
    }
    
    // Bind search engine
    document.addEventListener('DOMContentLoaded', function() {
        initSearchFilter('subjectSearch', 'subjectsTable');
    });
</script>

<?php
require_once('footer.php');
?>
