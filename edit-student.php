<?php
/**
 * Edit Student Profile Module
 * Student Record Management System
 */
require_once(__DIR__ . '/includes/header.php');

$error = '';
$success = '';
$sid = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($sid <= 0) {
    set_message('danger', 'Invalid student ID specified.');
    header("Location: view-students.php");
    exit;
}

// Fetch student profile details
try {
    $stmt = $dbh->prepare("SELECT * FROM registration WHERE id = :sid LIMIT 1");
    $stmt->execute(array(':sid' => $sid));
    $student = $stmt->fetch();
    
    if (!$student) {
        set_message('danger', 'Student profile not found.');
        header("Location: view-students.php");
        exit;
    }
    
    // Fetch active academic sessions
    $stmt = $dbh->prepare("SELECT id, session_name, status FROM session ORDER BY status DESC, session_name DESC");
    $stmt->execute();
    $sessions_list = $stmt->fetchAll();
    
    // Fetch courses
    $stmt = $dbh->prepare("SELECT id, course_code, course_name FROM tbl_course ORDER BY course_code ASC");
    $stmt->execute();
    $courses_list = $stmt->fetchAll();

    // Fetch countries
    $stmt = $dbh->prepare("SELECT id, name FROM countries ORDER BY name ASC");
    $stmt->execute();
    $countries_list = $stmt->fetchAll();
    
    // Fetch states of student's current country for pre-filling dropdown
    $stmt = $dbh->prepare("SELECT id, name FROM states WHERE country_id = :cid ORDER BY name ASC");
    $stmt->execute(array(':cid' => $student['country_id']));
    $states_list = $stmt->fetchAll();
    
    // Fetch cities of student's current state for pre-filling dropdown
    $stmt = $dbh->prepare("SELECT id, name FROM cities WHERE state_id = :sid ORDER BY name ASC");
    $stmt->execute(array(':sid' => $student['state_id']));
    $cities_list = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Configuration load error: ' . $e->getMessage();
}

// Process update action
if (isset($_POST['update_btn'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_message('danger', 'Security check failed. Please refresh and try again.');
    } else {
        // Collect fields
        $reg_no = sanitize($_POST['student_reg_no']);
        $course_id = intval($_POST['course_id']);
        $session_id = intval($_POST['session_id']);
        $name = sanitize($_POST['student_name']);
        $gender = sanitize($_POST['gender']);
        $dob = sanitize($_POST['dob']);
        $email = sanitize($_POST['email']);
        $mobile = sanitize($_POST['mobile']);
        $address = sanitize($_POST['address']);
        $country_id = intval($_POST['country_id']);
        $state_id = intval($_POST['state_id']);
        $city_id = intval($_POST['city_id']);
        
        // Basic validations
        if (empty($reg_no) || $course_id <= 0 || $session_id <= 0 || empty($name) || empty($gender) || empty($dob) || empty($email) || empty($mobile) || empty($address) || $country_id <= 0 || $state_id <= 0 || $city_id <= 0) {
            set_message('danger', 'All fields are mandatory. Please fill in every detail.');
        } else {
            try {
                // Check if Reg No already exists for ANOTHER student
                $stmt = $dbh->prepare("SELECT COUNT(*) FROM registration WHERE student_reg_no = :reg AND id != :sid");
                $stmt->execute(array(':reg' => $reg_no, ':sid' => $sid));
                if ($stmt->fetchColumn() > 0) {
                    set_message('danger', 'Registration Number ' . $reg_no . ' is already in use by another student.');
                } else {
                    // Check if email already exists for ANOTHER student
                    $stmt = $dbh->prepare("SELECT COUNT(*) FROM registration WHERE email = :email AND id != :sid");
                    $stmt->execute(array(':email' => $email, ':sid' => $sid));
                    if ($stmt->fetchColumn() > 0) {
                        set_message('danger', 'Email Address ' . $email . ' is already in use by another student.');
                    } else {
                        // Update record
                        $stmt = $dbh->prepare("UPDATE registration 
                                               SET student_reg_no = :reg, course_id = :cid, session_id = :sid, 
                                                   student_name = :sname, gender = :gender, dob = :dob, email = :email, 
                                                   mobile = :mobile, address = :addr, country_id = :country, 
                                                   state_id = :state, city_id = :city 
                                               WHERE id = :sid");
                        $stmt->execute(array(
                            ':reg' => $reg_no,
                            ':cid' => $course_id,
                            ':sid' => $session_id,
                            ':sname' => $name,
                            ':gender' => $gender,
                            ':dob' => $dob,
                            ':email' => $email,
                            ':mobile' => $mobile,
                            ':addr' => $address,
                            ':country' => $country_id,
                            ':state' => $state_id,
                            ':city' => $city_id,
                            ':sid' => $sid
                        ));
                        
                        set_message('success', 'Student record updated successfully.');
                        header("Location: view-students.php");
                        exit;
                    }
                }
            } catch (PDOException $e) {
                set_message('danger', 'System error: ' . $e->getMessage());
            }
        }
    }
    header("Location: edit-student.php?id=" . $sid);
    exit;
}

$csrf_token = generate_csrf_token();
?>

<!-- Header Section -->
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Student Profile</h1>
        <div class="page-breadcrumbs">Home / Student / Edit Profile</div>
    </div>
    
    <button class="btn btn-secondary" onclick="window.location.href='view-students.php'">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Registry</span>
    </button>
</div>

<!-- Main Edit Form Panel -->
<div class="glass-panel" style="max-width: 900px; margin: 0 auto 30px auto;">
    <h3 class="glass-panel-title">
        <span><i class="fas fa-user-pen" style="color: var(--primary); margin-right: 8px;"></i> Edit Details for <?php echo htmlspecialchars($student['student_name']); ?></span>
    </h3>
    
    <form action="edit-student.php?id=<?php echo $sid; ?>" method="POST" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        
        <!-- SECTION 1: ACADEMIC DETAILS -->
        <div class="form-section-title">
            <i class="fas fa-graduation-cap"></i> Academic Details
        </div>
        
        <div class="grid-3">
            <!-- Registration No -->
            <div class="form-group">
                <label for="student_reg_no">Registration Number</label>
                <div class="input-group">
                    <input type="text" id="student_reg_no" name="student_reg_no" class="form-control" value="<?php echo htmlspecialchars($student['student_reg_no']); ?>" placeholder="e.g. REG-2025-001" required>
                    <i class="fas fa-id-card"></i>
                </div>
            </div>
            
            <!-- Session Selector -->
            <div class="form-group">
                <label for="session_id">Academic Session</label>
                <div class="input-group">
                    <select id="session_id" name="session_id" class="form-control" required>
                        <option value="">-- Select Session --</option>
                        <?php foreach ($sessions_list as $row): ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo ($student['session_id'] == $row['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['session_name']); ?> <?php echo ($row['status'] == 1) ? '(Active)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
            
            <!-- Course Selector -->
            <div class="form-group">
                <label for="course_id">Course / Program</label>
                <div class="input-group">
                    <select id="course_id" name="course_id" class="form-control" required>
                        <option value="">-- Choose Course --</option>
                        <?php foreach ($courses_list as $row): ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo ($student['course_id'] == $row['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['course_code'] . ' - ' . $row['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>
        
        <!-- SECTION 2: PERSONAL DETAILS -->
        <div class="form-section-title">
            <i class="fas fa-user"></i> Personal Details
        </div>
        
        <div class="grid-2">
            <!-- Student Name -->
            <div class="form-group">
                <label for="student_name">Full Name</label>
                <div class="input-group">
                    <input type="text" id="student_name" name="student_name" class="form-control" value="<?php echo htmlspecialchars($student['student_name']); ?>" placeholder="Enter student's full name" required>
                    <i class="fas fa-user-tag"></i>
                </div>
            </div>
            
            <!-- Gender Selector -->
            <div class="form-group">
                <label for="gender">Gender</label>
                <div class="input-group">
                    <select id="gender" name="gender" class="form-control" required>
                        <option value="">-- Choose Gender --</option>
                        <option value="Male" <?php echo ($student['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($student['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($student['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                    <i class="fas fa-venus-mars"></i>
                </div>
            </div>
        </div>
        
        <div class="grid-3" style="margin-top: 10px;">
            <!-- DOB -->
            <div class="form-group">
                <label for="dob">Date of Birth</label>
                <div class="input-group">
                    <input type="date" id="dob" name="dob" class="form-control" value="<?php echo $student['dob']; ?>" required>
                    <i class="fas fa-cake-candles"></i>
                </div>
            </div>
            
            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-group">
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" placeholder="student@example.com" required>
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
            
            <!-- Mobile -->
            <div class="form-group">
                <label for="mobile">Mobile Number</label>
                <div class="input-group">
                    <input type="tel" id="mobile" name="mobile" class="form-control" value="<?php echo htmlspecialchars($student['mobile']); ?>" placeholder="e.g. 9876543210" required>
                    <i class="fas fa-phone"></i>
                </div>
            </div>
        </div>
        
        <!-- SECTION 3: ADDRESS & LOCATION DETAILS -->
        <div class="form-section-title">
            <i class="fas fa-map-location-dot"></i> Address & Location Info
        </div>
        
        <div class="form-group">
            <label for="address">Residential Address</label>
            <div class="input-group">
                <textarea id="address" name="address" class="form-control" rows="3" style="padding-left: 16px; height: auto;" placeholder="Street address, Apartment, Suite, etc." required><?php echo htmlspecialchars($student['address']); ?></textarea>
            </div>
        </div>
        
        <div class="grid-3" style="margin-top: 15px;">
            <!-- Country Selector -->
            <div class="form-group">
                <label for="country_id">Country</label>
                <div class="input-group">
                    <select id="country_id" name="country_id" class="form-control" required>
                        <option value="">-- Choose Country --</option>
                        <?php foreach ($countries_list as $row): ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo ($student['country_id'] == $row['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-globe"></i>
                </div>
            </div>
            
            <!-- State Selector (Initially filled based on country) -->
            <div class="form-group">
                <label for="state_id">State / Province</label>
                <div class="input-group">
                    <select id="state_id" name="state_id" class="form-control" required>
                        <option value="">-- Choose State --</option>
                        <?php foreach ($states_list as $row): ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo ($student['state_id'] == $row['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-map"></i>
                </div>
            </div>
            
            <!-- City Selector (Initially filled based on state) -->
            <div class="form-group">
                <label for="city_id">City</label>
                <div class="input-group">
                    <select id="city_id" name="city_id" class="form-control" required>
                        <option value="">-- Choose City --</option>
                        <?php foreach ($cities_list as $row): ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo ($student['city_id'] == $row['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-city"></i>
                </div>
            </div>
        </div>
        
        <!-- Form Submission Footer Actions -->
        <div style="display: flex; justify-content: flex-end; gap: 15px; border-top: 1px solid var(--border-card); padding-top: 25px; margin-top: 30px;">
            <a href="view-students.php" class="btn btn-secondary">
                <i class="fas fa-xmark"></i>
                <span>Cancel</span>
            </a>
            
            <button type="submit" name="update_btn" class="btn btn-primary" style="padding: 12px 36px;">
                <i class="fas fa-circle-check"></i>
                <span>Save Changes</span>
            </button>
        </div>
    </form>
</div>

<!-- AJAX Dynamic Location Selectors loading -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Bind Country Dropdown change
    $('#country_id').on('change', function() {
        const countryId = $(this).val();
        const stateSelect = $('#state_id');
        const citySelect = $('#city_id');
        
        // Reset state and city selectors
        stateSelect.empty().append('<option value="">-- Choose State --</option>').prop('disabled', true);
        citySelect.empty().append('<option value="">-- Select State First --</option>').prop('disabled', true);
        
        if (countryId) {
            // Trigger AJAX request
            $.ajax({
                url: 'api/get-states.php',
                type: 'GET',
                data: { country_id: countryId },
                dataType: 'json',
                success: function(data) {
                    if (data && data.length > 0) {
                        stateSelect.prop('disabled', false);
                        data.forEach(function(state) {
                            stateSelect.append('<option value="' + state.id + '">' + state.name + '</option>');
                        });
                    } else {
                        stateSelect.empty().append('<option value="">No states found</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error fetching states:', error);
                }
            });
        }
    });
    
    // Bind State Dropdown change
    $('#state_id').on('change', function() {
        const stateId = $(this).val();
        const citySelect = $('#city_id');
        
        // Reset city selector
        citySelect.empty().append('<option value="">-- Choose City --</option>').prop('disabled', true);
        
        if (stateId) {
            // Trigger AJAX request
            $.ajax({
                url: 'api/get-cities.php',
                type: 'GET',
                data: { state_id: stateId },
                dataType: 'json',
                success: function(data) {
                    if (data && data.length > 0) {
                        citySelect.prop('disabled', false);
                        data.forEach(function(city) {
                            citySelect.append('<option value="' + city.id + '">' + city.name + '</option>');
                        });
                    } else {
                        citySelect.empty().append('<option value="">No cities found</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error fetching cities:', error);
                }
            });
        }
    });
});
</script>

<?php
require_once(__DIR__ . '/includes/footer.php');
?>
