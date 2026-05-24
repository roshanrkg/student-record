<?php
/**
 * Manage Academic Sessions Module
 * Student Record Management System
 */
require_once('header.php');

$error = '';
$success = '';

// Add Session processing
if (isset($_POST['add_session'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_message('danger', 'Security check failed. Please refresh and try again.');
    } else {
        $session_name = sanitize($_POST['session_name']);
        
        if (empty($session_name)) {
            set_message('danger', 'Academic session name cannot be empty.');
        } else {
            try {
                // Check if session name already exists
                $stmt = $dbh->prepare("SELECT COUNT(*) FROM session WHERE session_name = :sname");
                $stmt->execute(array(':sname' => $session_name));
                if ($stmt->fetchColumn() > 0) {
                    set_message('danger', 'Session ' . $session_name . ' already exists.');
                } else {
                    $stmt = $dbh->prepare("INSERT INTO session (session_name, status) VALUES (:sname, 0)");
                    $stmt->execute(array(':sname' => $session_name));
                    set_message('success', 'Academic Session ' . $session_name . ' added successfully.');
                }
            } catch (PDOException $e) {
                set_message('danger', 'System error: ' . $e->getMessage());
            }
        }
    }
    header("Location: manage-session.php");
    exit;
}

// Toggle Session Status processing (Set Active)
if (isset($_GET['action']) && $_GET['action'] == 'activate' && isset($_GET['id'])) {
    $sid = intval($_GET['id']);
    try {
        // Set all sessions to inactive (0)
        $dbh->query("UPDATE session SET status = 0");
        
        // Set target session to active (1)
        $stmt = $dbh->prepare("UPDATE session SET status = 1 WHERE id = :sid");
        $stmt->execute(array(':sid' => $sid));
        
        set_message('success', 'Academic Session updated and activated.');
    } catch (PDOException $e) {
        set_message('danger', 'System error: ' . $e->getMessage());
    }
    header("Location: manage-session.php");
    exit;
}

// Delete Session processing
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $sid = intval($_GET['id']);
    try {
        // Check if session is currently active
        $stmt = $dbh->prepare("SELECT status FROM session WHERE id = :sid");
        $stmt->execute(array(':sid' => $sid));
        $status = $stmt->fetchColumn();
        
        if ($status == 1) {
            set_message('danger', 'Cannot delete the active academic session. Activate another session first.');
        } else {
            $stmt = $dbh->prepare("DELETE FROM session WHERE id = :sid");
            $stmt->execute(array(':sid' => $sid));
            set_message('success', 'Academic Session deleted.');
        }
    } catch (PDOException $e) {
        set_message('danger', 'System error: ' . $e->getMessage());
    }
    header("Location: manage-session.php");
    exit;
}

// Fetch all sessions
try {
    $stmt = $dbh->prepare("SELECT * FROM session ORDER BY id DESC");
    $stmt->execute();
    $sessions = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error listing sessions: ' . $e->getMessage();
}

$csrf_token = generate_csrf_token();
?>

<!-- Header Section -->
<div class="page-header">
    <div>
        <h1 class="page-title">Manage Academic Sessions</h1>
        <div class="page-breadcrumbs">Home / Settings / Sessions</div>
    </div>
</div>

<div class="grid-3">
    
    <!-- Add Session Panel -->
    <div class="glass-panel" style="grid-column: span 1;">
        <h3 class="glass-panel-title">Add Academic Session</h3>
        
        <form action="manage-session.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label for="session_name">Academic Session Year</label>
                <div class="input-group">
                    <input type="text" id="session_name" name="session_name" class="form-control" placeholder="e.g. 2025-2026" required>
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <small style="display: block; margin-top: 8px; color: var(--text-muted); font-size: 11px;">
                    Use standard session year labels matching your school terms.
                </small>
            </div>
            
            <button type="submit" name="add_session" class="btn btn-primary btn-block" style="margin-top: 20px;">
                <i class="fas fa-plus"></i>
                <span>Add Session</span>
            </button>
        </form>
    </div>
    
    <!-- Sessions Table List -->
    <div class="glass-panel" style="grid-column: span 2;">
        <h3 class="glass-panel-title">All Academic Sessions</h3>
        
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Session name</th>
                        <th>Status</th>
                        <th>Creation Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($sessions) > 0): ?>
                        <?php $cnt = 1; foreach ($sessions as $row): ?>
                            <tr>
                                <td><?php echo $cnt++; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['session_name']); ?></strong></td>
                                <td>
                                    <?php if ($row['status'] == 1): ?>
                                        <span style="background: rgba(16, 185, 129, 0.15); color: var(--success); padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                            <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--success);"></span>
                                            Active Session
                                        </span>
                                    <?php else: ?>
                                        <span style="background: rgba(255, 255, 255, 0.05); color: var(--text-secondary); padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500;">
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d M Y', strtotime($row['creation_date'])); ?></td>
                                <td style="text-align: right;">
                                    <div class="action-btns" style="justify-content: flex-end;">
                                        <?php if ($row['status'] == 0): ?>
                                            <!-- Activate Action -->
                                            <a href="manage-session.php?action=activate&id=<?php echo $row['id']; ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;" title="Set as Active Session">
                                                <i class="fas fa-check" style="color: var(--success);"></i>
                                                Activate
                                            </a>
                                            <!-- Delete Action -->
                                            <a href="manage-session.php?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this session? This will remove related student registrations.')" class="btn-icon btn-icon-delete" title="Delete Session">
                                                <i class="fas fa-trash-can"></i>
                                            </a>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-size: 12px; font-style: italic; padding-right: 10px;">No Actions</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                                No academic sessions found. Create one first!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once('footer.php');
?>
