<?php include '../../config.php'; ?>
<?php include BASE_PATH.'/fixit/inc/header.php';?>

<style>
/* Beautiful Donation Add Form Styles */
.form-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px;
    border-radius: 20px;
    margin-bottom: 30px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

.form-card {
    background: white;
    border-radius: 15px;
    padding: 0;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.form-header {
    background: linear-gradient(135deg, #FF6B35 0%, #E85D2C 100%);
    color: white;
    padding: 25px 30px;
    text-align: center;
}

.form-header h3 {
    margin: 0;
    font-weight: 700;
    font-size: 24px;
}

.form-header .subtitle {
    margin: 5px 0 0 0;
    opacity: 0.9;
    font-size: 14px;
}

.form-body {
    padding: 40px 30px;
}

.form-group {
    margin-bottom: 25px;
}

.form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 10px;
    display: block;
    font-size: 16px;
}

.form-label .required {
    color: #dc3545;
}

.form-control {
    width: 100%;
    /* padding: 15px 20px; */
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 15px;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.form-control:focus {
    border-color: #FF6B35;
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
    background: white;
    outline: none;
}

textarea.form-control {
    min-height: 120px;
    resize: vertical;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
}

.btn-submit {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    padding: 15px 40px;
    border-radius: 25px;
    color: white;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s ease;
    box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(40, 167, 69, 0.4);
}

.btn-cancel {
    background: #6c757d;
    border: none;
    padding: 15px 30px;
    border-radius: 25px;
    color: white;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s ease;
}

.btn-cancel:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.alert-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 15px 25px;
    border-radius: 10px;
    margin-bottom: 20px;
    border: none;
    font-weight: 600;
}

.alert-error {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    padding: 15px 25px;
    border-radius: 10px;
    margin-bottom: 20px;
    border: none;
    font-weight: 600;
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-section {
        padding: 20px;
    }
    
    .form-body {
        padding: 25px 20px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-submit, .btn-cancel {
        width: 100%;
        margin-bottom: 10px;
    }
}

.input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.input-group-text {
    position: absolute;
    left: 15px;
    color: #6c757d;
    font-size: 16px;
    z-index: 1;
    pointer-events: none;
}

.input-group .form-control {
    padding-left: 45px;
}
</style>

<?php include BASE_PATH.'/fixit/inc/top.php';?>
<?php include BASE_PATH.'/fixit/inc/nav.php';?>

<?php
// Get current user's team_id from session
$team_id = isset($_SESSION['team_id']) ? (int)$_SESSION['team_id'] : 0;

// Check if it's edit mode
$isEdit = isset($_GET['id']) && !empty($_GET['id']);
$donationId = $isEdit ? (int)$_GET['id'] : 0;

// Fetch donors for dropdown
$donors = [];
$donorQuery = "SELECT f_donor_id, donor_name FROM f_donors WHERE team_id = ? ORDER BY donor_name ASC";
$donorStmt = mysqli_prepare($con, $donorQuery);
mysqli_stmt_bind_param($donorStmt, 'i', $team_id);
mysqli_stmt_execute($donorStmt);
$donorResult = mysqli_stmt_get_result($donorStmt);
while ($row = mysqli_fetch_assoc($donorResult)) {
    $donors[] = $row;
}

// Fetch activities for dropdown
$activities = [];
$activityQuery = "SELECT activity_id, name FROM f_activities WHERE team_id = ? ORDER BY name ASC";
$activityStmt = mysqli_prepare($con, $activityQuery);
mysqli_stmt_bind_param($activityStmt, 'i', $team_id);
mysqli_stmt_execute($activityStmt);
$activityResult = mysqli_stmt_get_result($activityStmt);
while ($row = mysqli_fetch_assoc($activityResult)) {
    $activities[] = $row;
}

$donation = [
    'donor_id' => '',
    'activity_id' => '',
    'outside' => '',
    'amount' => ''
];

// If edit mode, fetch existing donation data
if ($isEdit) {
    $query = "SELECT * FROM f_donation WHERE donation_id = ? AND team_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $donationId, $team_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $donation = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donor_id = mysqli_real_escape_string($con, $_POST['donor_id']);
    $activity_id = mysqli_real_escape_string($con, $_POST['activity_id']);
    $outside = mysqli_real_escape_string($con, $_POST['outside']);
    $amount = mysqli_real_escape_string($con, $_POST['amount']);
    
    // Validate required fields
    $errors = [];
    if (empty($donor_id)) $errors[] = "Donor is required";
    if (empty($activity_id)) $errors[] = "Activity is required";
    if (empty($amount)) $errors[] = "Amount is required";
    
    if (empty($errors)) {
        if ($isEdit) {
            // Update existing donation
            $updateQuery = "UPDATE f_donation SET donor_id = ?, activity_id = ?, outside = ?, amount = ?, updated_at = NOW() WHERE donation_id = ? AND team_id = ?";
            $updateStmt = mysqli_prepare($con, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, 'iisii', $donor_id, $activity_id, $outside, $amount, $donationId, $team_id);
            
            if (mysqli_stmt_execute($updateStmt)) {
                $successMessage = "Donation updated successfully!";
            } else {
                $errorMessage = "Error updating donation. Please try again.";
            }
        } else {
            // Add new donation
            $insertQuery = "INSERT INTO f_donation (team_id, donor_id, activity_id, outside, amount, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
            $insertStmt = mysqli_prepare($con, $insertQuery);
            mysqli_stmt_bind_param($insertStmt, 'iiisi', $team_id, $donor_id, $activity_id, $outside, $amount);
            
            if (mysqli_stmt_execute($insertStmt)) {
                $successMessage = "Donation added successfully!";
                // Reset form fields after successful submission
                $donation = [
                    'donor_id' => '',
                    'activity_id' => '',
                    'outside' => '',
                    'amount' => ''
                ];
            } else {
                $errorMessage = "Error adding donation. Please try again.";
            }
        }
    } else {
        $errorMessage = implode("<br>", $errors);
    }
}
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Form Section -->
                <div class="form-section">
                    <div class="form-card">
                        <div class="form-header">
                            <h3>
                                <?php echo $isEdit ? 'Edit Donation' : 'Add New Donation'; ?>
                            </h3>
                            <div class="subtitle">
                                <?php echo $isEdit ? 'Update donation information below' : 'Fill in donation details below'; ?>
                            </div>
                        </div>
                        
                        <div class="form-body">
                            <?php if (isset($errorMessage)): ?>
                                <div class="alert-error">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <?php echo $errorMessage; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($successMessage)): ?>
                                <div class="alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo $successMessage; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="donor_id" class="form-label">
                                                Donor <span class="required">*</span>
                                            </label>
                                            <select class="form-control" id="donor_id" name="donor_id" >
                                                <option value="">Select Donor</option>
                                                <?php foreach ($donors as $donor): ?>
                                                    <option value="<?php echo $donor['f_donor_id']; ?>" 
                                                            <?php echo ($donation['donor_id'] == $donor['f_donor_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($donor['donor_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="activity_id" class="form-label">
                                                Activity <span class="required">*</span>
                                            </label>
                                            <select class="form-control" id="activity_id" name="activity_id" >
                                                <option value="">Select Activity</option>
                                                <?php foreach ($activities as $activity): ?>
                                                    <option value="<?php echo $activity['activity_id']; ?>" 
                                                            <?php echo ($donation['activity_id'] == $activity['activity_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($activity['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="outside" class="form-label">
                                                Donation Type <span class="required">*</span>
                                            </label>
                                            <select class="form-control" id="outside" name="outside" required>
                                                <option value="">Select Type</option>
                                                <option value="0" <?php echo ($donation['outside'] == '0') ? 'selected' : ''; ?>>Regular</option>
                                                <option value="1" <?php echo ($donation['outside'] == '1') ? 'selected' : ''; ?>>Outside</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="amount" class="form-label">
                                                Amount <span class="required">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-rupee-sign"></i>
                                                </span>
                                                <input type="number" class="form-control" id="amount" name="amount" 
                                                       value="<?php echo htmlspecialchars($donation['amount']); ?>" 
                                                       placeholder="Enter donation amount" step="0.01" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn-submit">
                                        <i class="fas fa-save me-2"></i>
                                        <?php echo $isEdit ? 'Update Donation' : 'Add Donation'; ?>
                                    </button>
                                    <a href="donation_list.php" class="btn-cancel">
                                        <i class="fas fa-times me-2"></i>
                                        Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH.'/admin/inc/footer.php';?>

<script>
    $(document).ready(function() {
        $('#donor_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search donor...',
            allowClear: true,
            width: '100%'
        });
        
        $('#activity_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search activity...',
            allowClear: true,
            width: '100%'
        });
        
        $('#outside').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select type...',
            allowClear: true,
            width: '100%'
        });
    });
</script>