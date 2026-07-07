<?php include '../../config.php'; ?>
<?php include BASE_PATH.'/fixit/inc/header.php';?> 

<style>
/* Beautiful Donor Add Form Styles */
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
    padding: 15px 20px;
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
</style>

<?php include BASE_PATH.'/fixit/inc/top.php';?>
<?php include BASE_PATH.'/fixit/inc/nav.php';?>

<?php
// Get current user's team_id from session
$team_id = isset($_SESSION['team_id']) ? (int)$_SESSION['team_id'] : 0;

// Check if it's edit mode
$isEdit = isset($_GET['id']) && !empty($_GET['id']);
$donorId = $isEdit ? (int)$_GET['id'] : 0;

$donor = [
    'donor_name' => '',
    'donor_address' => '',
    'mobile' => '',
    'cnic' => ''
];

// If edit mode, fetch existing donor data
if ($isEdit) {
    $query = "SELECT * FROM f_donors WHERE f_donor_id = ? AND team_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $donorId, $team_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $donor = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donor_name = mysqli_real_escape_string($con, $_POST['donor_name']);
    $donor_address = mysqli_real_escape_string($con, $_POST['donor_address']);
    $mobile = mysqli_real_escape_string($con, $_POST['mobile']);
    $cnic = mysqli_real_escape_string($con, $_POST['cnic']);
    
    // Validate required fields
    $errors = [];
    if (empty($donor_name)) $errors[] = "Donor name is required";
    if (empty($mobile)) $errors[] = "Mobile number is required";
    if (empty($cnic)) $errors[] = "CNIC is required";
    
    if (empty($errors)) {
        if ($isEdit) {
            // Update existing donor
            $updateQuery = "UPDATE f_donors SET donor_name = ?, donor_address = ?, mobile = ?, cnic = ?, updated_at = NOW() WHERE f_donor_id = ? AND team_id = ?";
            $updateStmt = mysqli_prepare($con, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, 'sssisi', $donor_name, $donor_address, $mobile, $cnic, $donorId, $team_id);
            
            if (mysqli_stmt_execute($updateStmt)) {
                $successMessage = "Donor updated successfully!";
            } else {
                $errorMessage = "Error updating donor. Please try again.";
            }
        } else {
            // Add new donor
            $insertQuery = "INSERT INTO f_donors (team_id, donor_name, donor_address, mobile, cnic, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
            $insertStmt = mysqli_prepare($con, $insertQuery);
            mysqli_stmt_bind_param($insertStmt, 'issss', $team_id, $donor_name, $donor_address, $mobile, $cnic);
            
            if (mysqli_stmt_execute($insertStmt)) {
                $successMessage = "Donor added successfully!";
            } else {
                $errorMessage = "Error adding donor. Please try again.";
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
                                <?php echo $isEdit ? 'Edit Donor' : 'Add New Donor'; ?>
                            </h3>
                            <div class="subtitle">
                                <?php echo $isEdit ? 'Update donor information below' : 'Fill in the donor details below'; ?>
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
                                            <label for="donor_name" class="form-label">
                                                Donor Name <span class="required">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="donor_name" name="donor_name" 
                                                   value="<?php echo htmlspecialchars($donor['donor_name']); ?>" 
                                                   placeholder="Enter donor name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mobile" class="form-label">
                                                Mobile Number <span class="required">*</span>
                                            </label>
                                            <input type="tel" class="form-control" id="mobile" name="mobile" 
                                                   value="<?php echo htmlspecialchars($donor['mobile']); ?>" 
                                                   placeholder="Enter mobile number" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="donor_address" class="form-label">
                                                Address
                                            </label>
                                            <textarea class="form-control" id="donor_address" name="donor_address" 
                                                      placeholder="Enter complete address"><?php echo htmlspecialchars($donor['donor_address']); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="cnic" class="form-label">
                                                CNIC Number <span class="required">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="cnic" name="cnic" 
                                                   value="<?php echo htmlspecialchars($donor['cnic']); ?>" 
                                                   placeholder="Enter CNIC number (e.g., 12345-1234567-1)" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn-submit">
                                        <i class="fas fa-save me-2"></i>
                                        <?php echo $isEdit ? 'Update Donor' : 'Add Donor'; ?>
                                    </button>
                                    <a href="donor_list.php" class="btn-cancel">
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