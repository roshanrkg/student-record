<?php
/**
 * Admin Profile Management Module
 * Student Record Management System
 */
require_once(__DIR__ . '/includes/header.php');

$error = '';
$success = '';
$admin_id = $_SESSION['aid'];

// Fetch current details
try {
    $stmt = $dbh->prepare("SELECT * FROM tbl_login WHERE id = :aid LIMIT 1");
    $stmt->execute(array(':aid' => $admin_id));
    $admin = $stmt->fetch();
    
    if (!$admin) {
        set_message('danger', 'Admin record not found.');
        header("Location: dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    $error = 'Error fetching profile: ' . $e->getMessage();
}

// Process update action
if (isset($_POST['update_profile_btn'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_message('danger', 'Security check failed. Please refresh and try again.');
    } else {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $username = sanitize($_POST['username']);
        
        if (empty($name) || empty($email) || empty($username)) {
            set_message('danger', 'All fields are required.');
        } else {
            try {
                // Verify email uniqueness excluding current admin
                $stmt = $dbh->prepare("SELECT COUNT(*) FROM tbl_login WHERE email = :email AND id != :aid");
                $stmt->execute(array(':email' => $email, ':aid' => $admin_id));
                if ($stmt->fetchColumn() > 0) {
                    set_message('danger', 'Email address ' . $email . ' is already in use by another admin account.');
                } else {
                    // Verify username uniqueness excluding current admin
                    $stmt = $dbh->prepare("SELECT COUNT(*) FROM tbl_login WHERE username = :uname AND id != :aid");
                    $stmt->execute(array(':uname' => $username, ':aid' => $admin_id));
                    if ($stmt->fetchColumn() > 0) {
                        set_message('danger', 'Username ' . $username . ' is already in use.');
                    } else {
                        // Update details
                        $stmt = $dbh->prepare("UPDATE tbl_login SET name = :name, email = :email, username = :uname WHERE id = :aid");
                        $stmt->execute(array(
                            ':name' => $name,
                            ':email' => $email,
                            ':uname' => $username,
                            ':aid' => $admin_id
                        ));
                        
                        // Update session variables
                        $_SESSION['aname'] = $name;
                        $_SESSION['alogin'] = $email;
                        
                        set_message('success', 'Profile updated successfully.');
                        header("Location: admin-profile.php");
                        exit;
                    }
                }
            } catch (PDOException $e) {
                set_message('danger', 'System error: ' . $e->getMessage());
            }
        }
    }
    header("Location: admin-profile.php");
    exit;
}

$csrf_token = generate_csrf_token();
?>

<!-- Header Section -->
<div class="page-header">
    <div>
        <h1 class="page-title">Admin Profile</h1>
        <div class="page-breadcrumbs">Home / Administrator / Profile</div>
    </div>
</div>

<div class="grid-3">
    
    <!-- Profile Card widget -->
    <div class="glass-panel" style="grid-column: span 1; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px;">
        <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 800; color: white; margin-bottom: 20px; box-shadow: var(--accent-glow);">
            <?php echo strtoupper(substr($admin['name'], 0, 1)); ?>
        </div>
        <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 5px;"><?php echo htmlspecialchars($admin['name']); ?></h2>
        <span style="color: var(--primary); font-size: 13px; font-weight: 600; background: rgba(99, 102, 241, 0.15); padding: 4px 12px; border-radius: 20px; margin-bottom: 20px;">
            System Admin
        </span>
        
        <div style="width: 100%; border-top: 1px solid var(--border-card); padding-top: 20px; text-align: left; font-size: 13px;">
            <p style="color: var(--text-secondary); margin-bottom: 8px;"><i class="fas fa-circle-user" style="width: 20px; color: var(--text-muted);"></i> Username: <strong><?php echo htmlspecialchars($admin['username']); ?></strong></p>
            <p style="color: var(--text-secondary); margin-bottom: 8px;"><i class="fas fa-envelope" style="width: 20px; color: var(--text-muted);"></i> Email: <?php echo htmlspecialchars($admin['email']); ?></p>
            <p style="color: var(--text-secondary);"><i class="fas fa-clock-rotate-left" style="width: 20px; color: var(--text-muted);"></i> Created: <?php echo date('d M, Y', strtotime($admin['creation_date'])); ?></p>
        </div>
    </div>
    
    <!-- Edit Form widget -->
    <div class="glass-panel" style="grid-column: span 2;">
        <h3 class="glass-panel-title">Update Profile Credentials</h3>
        
        <form action="admin-profile.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="grid-2">
                <!-- Name -->
                <div class="form-group">
                    <label for="name">Display Name</label>
                    <div class="input-group">
                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter full name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                        <i class="fas fa-user-tag"></i>
                    </div>
                </div>
                
                <!-- Username -->
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-group">
                        <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </div>
            
            <!-- Email -->
            <div class="form-group" style="margin-top: 10px;">
                <label for="email">Email Address</label>
                <div class="input-group">
                    <input type="email" id="email" name="email" class="form-control" placeholder="admin@mail.com" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
            
            <!-- Action buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 15px; border-top: 1px solid var(--border-card); padding-top: 25px; margin-top: 30px;">
                <button type="submit" name="update_profile_btn" class="btn btn-primary" style="padding: 12px 30px;">
                    <i class="fas fa-floppy-disk"></i>
                    <span>Save Profile Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once(__DIR__ . '/includes/footer.php');
?>
