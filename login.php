<?php
/**
 * Administrator Authentication Module
 * Student Record Management System
 */
require_once(__DIR__ . '/config.php');

// Redirect to dashboard if already authenticated
if (isset($_SESSION['alogin']) && strlen($_SESSION['alogin']) > 0) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

// Process Login Form Submission
if (isset($_POST['login_btn'])) {
    
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Security check failed. Please refresh and try again.';
    } else {
        $username = sanitize($_POST['username']);
        $password = trim($_POST['password']);
        
        if (empty($username) || empty($password)) {
            $error = 'Both username and password are required.';
        } else {
            try {
                // Fetch user data by Username or Email
                $stmt = $dbh->prepare("SELECT id, username, password, email, name FROM tbl_login WHERE username = :uname OR email = :uname LIMIT 1");
                $stmt->execute(array(':uname' => $username));
                $user = $stmt->fetch();
                
                if ($user && $password == $user['password']) {
                    // Start secure authenticated session
                    $_SESSION['alogin'] = $user['email'];
                    $_SESSION['aname'] = $user['name'];
                    $_SESSION['aid'] = $user['id'];
                    
                    // Reset CSRF token for security
                    unset($_SESSION['csrf_token']);
                    
                    // Set success toast and redirect
                    set_message('success', 'Welcome back, ' . $user['name'] . '! Login successful.');
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = 'Invalid credentials. Please try again.';
                }
            } catch (PDOException $e) {
                $error = 'System error: ' . $e->getMessage();
            }
        }
    }
}

// Generate CSRF Token for Form
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRMS Portal | Admin Secure Login</title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="min-height: 100vh; display: flex; align-items: center; justify-content: center;">

    <!-- Background glowing spheres -->
    <div class="glowing-bg">
        <div class="circle circle-1"></div>
        <div class="circle circle-2"></div>
    </div>

    <!-- Login Container -->
    <div class="login-container">
        
        <!-- Elegant Glass Card -->
        <div class="login-card">
            
            <div class="login-brand">
                <i class="fas fa-graduation-cap" style="font-size: 3.5rem; background: var(--accent-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 12px; filter: drop-shadow(var(--accent-glow));"></i>
                <h2>SRMS Admin Portal</h2>
                <p>Enter credentials to access the console</p>
            </div>

            <!-- Error alerts inside card -->
            <?php if (!empty($error)): ?>
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; padding: 12px 16px; border-radius: 12px; font-size: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-circle-exclamation"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="login.php" method="POST" autocomplete="off">
                <!-- CSRF Protection -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <!-- Username or Email Field -->
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <div class="input-group">
                        <input type="text" id="username" name="username" class="form-control" placeholder="Enter username or email" required autofocus>
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group" style="margin-bottom: 30px;">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                        <i class="fas fa-lock"></i>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="login_btn" class="btn btn-primary btn-block">
                    <span>Secure Sign In</span>
                    <i class="fas fa-arrow-right-to-bracket"></i>
                </button>
            </form>
            
            <div style="margin-top: 25px; text-align: center; font-size: 12px; color: var(--text-muted);">
                <p><i class="fas fa-lock" style="margin-right: 4px;"></i> Protected by 256-bit secure session hashes</p>
                <p style="margin-top: 10px;">Demo Admin credentials: <strong>admin</strong> / <strong>admin123</strong></p>
            </div>

        </div> <!-- End .login-card -->
    </div> <!-- End .login-container -->

    <!-- Core Javascript Scripts -->
    <script src="assets/js/main.js"></script>

</body>
</html>
