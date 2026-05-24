<?php
/**
 * Admin Change Password Module
 * Student Record Management System
 */
require_once(__DIR__ . '/includes/header.php');

$error = '';
$success = '';
$admin_id = $_SESSION['aid'];

// Process password change
if (isset($_POST['change_pwd_btn'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_message('danger', 'Security check failed. Please refresh and try again.');
    } else {
        $curr_pwd = trim($_POST['current_password']);
        $new_pwd = trim($_POST['new_password']);
        $conf_pwd = trim($_POST['confirm_password']);
        
        if (empty($curr_pwd) || empty($new_pwd) || empty($conf_pwd)) {
            set_message('danger', 'All password fields are mandatory.');
        } else if ($new_pwd !== $conf_pwd) {
            set_message('danger', 'New password and confirmation password do not match.');
        } else if (strlen($new_pwd) < 6) {
            set_message('danger', 'New password must be at least 6 characters long.');
        } else {
            try {
                // Fetch current password hash
                $stmt = $dbh->prepare("SELECT password FROM tbl_login WHERE id = :aid LIMIT 1");
                $stmt->execute(array(':aid' => $admin_id));
                $db_hash = $stmt->fetchColumn();
                
                if ($db_hash && password_verify($curr_pwd, $db_hash)) {
                    // Hash new password using secure bcrypt
                    $new_hash = password_hash($new_pwd, PASSWORD_DEFAULT);
                    
                    // Update database
                    $stmt = $dbh->prepare("UPDATE tbl_login SET password = :hash WHERE id = :aid");
                    $stmt->execute(array(':hash' => $new_hash, ':aid' => $admin_id));
                    
                    set_message('success', 'Administrator password updated successfully. Your new settings are active.');
                    header("Location: change-password.php");
                    exit;
                } else {
                    set_message('danger', 'Your current password input is incorrect.');
                }
            } catch (PDOException $e) {
                set_message('danger', 'System error: ' . $e->getMessage());
            }
        }
    }
    header("Location: change-password.php");
    exit;
}

$csrf_token = generate_csrf_token();
?>

<!-- Header Section -->
<div class="page-header">
    <div>
        <h1 class="page-title">Change Password</h1>
        <div class="page-breadcrumbs">Home / Administrator / Security</div>
    </div>
</div>

<div style="max-width: 600px; margin: 0 auto;">
    
    <!-- Change Password Panel -->
    <div class="glass-panel">
        <h3 class="glass-panel-title">
            <span><i class="fas fa-key" style="color: var(--primary); margin-right: 8px;"></i> Change Admin Password</span>
        </h3>
        
        <form action="change-password.php" method="POST" onsubmit="return validatePasswords()">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <!-- Current Password -->
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <div class="input-group">
                    <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Enter current administrator password" required>
                    <i class="fas fa-lock"></i>
                </div>
            </div>
            
            <!-- New Password -->
            <div class="form-group" style="margin-top: 15px;">
                <label for="new_password">New Password</label>
                <div class="input-group">
                    <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Minimum 6 characters" required>
                    <i class="fas fa-key"></i>
                </div>
            </div>
            
            <!-- Confirm Password -->
            <div class="form-group" style="margin-top: 15px;">
                <label for="confirm_password">Confirm New Password</label>
                <div class="input-group">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repeat new password" required>
                    <i class="fas fa-shield-check"></i>
                </div>
                <div id="passwordErrorMsg" style="color: #f87171; font-size: 12px; margin-top: 8px; display: none; align-items: center; gap: 6px;">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>Passwords do not match.</span>
                </div>
            </div>
            
            <!-- Actions -->
            <div style="display: flex; justify-content: flex-end; gap: 15px; border-top: 1px solid var(--border-card); padding-top: 25px; margin-top: 30px;">
                <button type="submit" name="change_pwd_btn" class="btn btn-primary" style="padding: 12px 30px;">
                    <i class="fas fa-shield-halved"></i>
                    <span>Update Security Password</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Password validation logic -->
<script>
function validatePasswords() {
    const newPwd = document.getElementById('new_password').value;
    const confPwd = document.getElementById('confirm_password').value;
    const errorMsg = document.getElementById('passwordErrorMsg');
    
    if (newPwd !== confPwd) {
        errorMsg.style.display = 'flex';
        return false;
    }
    
    errorMsg.style.display = 'none';
    return true;
}
</script>

<?php
require_once(__DIR__ . '/includes/footer.php');
?>
