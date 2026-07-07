<?php include '../../config.php'; ?>
<?php include BASE_PATH.'/fixit/inc/header.php';?> 

<style>
/* Beautiful Activity Add Form Styles */
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
    padding: 8px 20px;
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
$activityId = $isEdit ? (int)$_GET['id'] : 0;

// Fetch activity categories from f_activities_cat table
$categories = [];
$catQuery = "SELECT f_activity_cat_id, cat_name FROM f_activities_cat ORDER BY cat_name ASC";
$catResult = mysqli_query($con, $catQuery);
while ($row = mysqli_fetch_assoc($catResult)) {
    $categories[] = $row;
}

$activity = [
    'category_id' => '',
    'name' => '',
    'location' => '',
    'detail' => '',
    'status' => '',
    'remarks' => ''
];

// If edit mode, fetch existing activity data
if ($isEdit) {
    $query = "SELECT * FROM f_activities WHERE activity_id = ? AND team_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $activityId, $team_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $activity = $row;
    }
    
    // Fetch activity expense data
    $expenseQuery = "SELECT * FROM f_activities_expense WHERE activity_id = ? ORDER BY created_at DESC LIMIT 1";
    $expenseStmt = mysqli_prepare($con, $expenseQuery);
    mysqli_stmt_bind_param($expenseStmt, 'i', $activityId);
    mysqli_stmt_execute($expenseStmt);
    $expenseResult = mysqli_stmt_get_result($expenseStmt);
    if ($expenseRow = mysqli_fetch_assoc($expenseResult)) {
        $activity['activity_expense_amount'] = $expenseRow['amount'];
        $activity['expense_detail'] = $expenseRow['detail'];
    }
    
    // Fetch one the spot donation data
    $donationQuery = "SELECT * FROM f_donation WHERE activity_id = ? AND outside = 1 ORDER BY created_at DESC LIMIT 1";
    $donationStmt = mysqli_prepare($con, $donationQuery);
    mysqli_stmt_bind_param($donationStmt, 'i', $activityId);
    mysqli_stmt_execute($donationStmt);
    $donationResult = mysqli_stmt_get_result($donationStmt);
    if ($donationRow = mysqli_fetch_assoc($donationResult)) {
        $activity['one_the_spot_donation'] = $donationRow['amount'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = mysqli_real_escape_string($con, $_POST['category_id']);
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $location = mysqli_real_escape_string($con, $_POST['location']);
    $detail = mysqli_real_escape_string($con, $_POST['detail']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $remarks = mysqli_real_escape_string($con, $_POST['remarks']);
    
    // Validate required fields
    $errors = [];
    if (empty($category_id)) $errors[] = "Activity category is required";
    if (empty($name)) $errors[] = "Activity name is required";
    if (empty($location)) $errors[] = "Location is required";
    if (empty($status)) $errors[] = "Status is required";
    
    if (empty($errors)) {
        if ($isEdit) {
            // echo "<pre>";
            // print_r($_POST);
            // echo "</pre>";
            // exit;
            // Update existing activity
            $updateQuery = "UPDATE f_activities SET category_id = ?, name = ?, location = ?, detail = ?, status = ?, remarks = ?, updated_at = NOW() WHERE activity_id = ? AND team_id = ?";
            $updateStmt = mysqli_prepare($con, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, 'isssssii', $category_id, $name, $location, $detail, $status, $remarks, $activityId, $team_id);
            
            if (mysqli_stmt_execute($updateStmt)) {
                if(isset($_POST['activity_expense_amount']) && !empty($_POST['activity_expense_amount'])) {
                    // Insert activity expense
                    $activity_expense_amount = mysqli_real_escape_string($con, $_POST['activity_expense_amount']);
                    $activity_expense_detail = mysqli_real_escape_string($con, $_POST['expense_detail']);
                    $insertExpenseQuery = "INSERT INTO f_activities_expense (activity_id, amount, detail, created_at) VALUES (?, ?, ?, NOW())";
                    $insertExpenseStmt = mysqli_prepare($con, $insertExpenseQuery);
                    mysqli_stmt_bind_param($insertExpenseStmt, 'ids', $activityId, $activity_expense_amount, $activity_expense_detail);
                    mysqli_stmt_execute($insertExpenseStmt);
                }
                if(isset($_POST['one_the_spot_donation']) && !empty($_POST['one_the_spot_donation'])) {
                    // Insert one the spot donation
                    $one_the_spot_donation_amount = mysqli_real_escape_string($con, $_POST['one_the_spot_donation']);
                    $outside = 1; // Store hardcoded value in variable
                    $insertDonationQuery = "INSERT INTO f_donation (activity_id,team_id,outside, amount, created_at) VALUES (?, ?, ?, ?, NOW())";
                    $insertDonationStmt = mysqli_prepare($con, $insertDonationQuery);
                    mysqli_stmt_bind_param($insertDonationStmt, 'iiid', $activityId, $team_id, $outside, $one_the_spot_donation_amount);
                    mysqli_stmt_execute($insertDonationStmt);
                }
                $successMessage = "Activity updated successfully!";
            } else {
                $errorMessage = "Error updating activity. Please try again.";
            }
        } else {
            // Add new activity
            $insertQuery = "INSERT INTO f_activities (team_id, category_id, name, location, detail, status, remarks, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $insertStmt = mysqli_prepare($con, $insertQuery);
            mysqli_stmt_bind_param($insertStmt, 'iisssss', $team_id, $category_id, $name, $location, $detail, $status, $remarks);
            
            if (mysqli_stmt_execute($insertStmt)) {
                $successMessage = "Activity added successfully!";
                // Reset form fields after successful submission
                $activity = [
                    'category_id' => '',
                    'name' => '',
                    'location' => '',
                    'detail' => '',
                    'status' => '',
                    'remarks' => ''
                ];
            } else {
                $errorMessage = "Error adding activity. Please try again.";
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
                                <?php echo $isEdit ? 'Edit Activity' : 'Add New Activity'; ?>
                            </h3>
                            <div class="subtitle">
                                <?php echo $isEdit ? 'Update activity information below' : 'Fill in activity details below'; ?>
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
                                            <label for="category_id" class="form-label">
                                                Activity Category <span class="required">*</span>
                                            </label>
                                            <select class="form-control" id="category_id" name="category_id" required>
                                                <option value="">Select Category</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category['f_activity_cat_id']; ?>" 
                                                            <?php echo ($activity['category_id'] == $category['f_activity_cat_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category['cat_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name" class="form-label">
                                                Activity Name <span class="required">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?php echo htmlspecialchars($activity['name']); ?>" 
                                                   placeholder="Enter activity name" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="location" class="form-label">
                                                Location <span class="required">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="location" name="location" 
                                                   value="<?php echo htmlspecialchars($activity['location']); ?>" 
                                                   placeholder="Enter location" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status" class="form-label">
                                                Status <span class="required">*</span>
                                            </label>
                                            <select class="form-control" id="status" name="status" required>
                                                <option value="">Select Status</option>
                                                <option value="pending" <?php echo ($activity['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="in_progress" <?php echo ($activity['status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="completed" <?php echo ($activity['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo ($activity['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row hiden-row" id="completedFields" style="display: none;">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="activity_expense_amount" class="form-label">
                                                Activity Expense Amount
                                            </label>
                                            <input type="number" class="form-control" id="activity_expense_amount" name="activity_expense_amount" 
                                                   value="<?php echo htmlspecialchars($activity['activity_expense_amount'] ?? ''); ?>" 
                                                   placeholder="Enter expense amount" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="expense_detail" class="form-label">
                                                Expense Detail
                                            </label>
                                            <input type="text" class="form-control" id="expense_detail" name="expense_detail" 
                                                   value="<?php echo htmlspecialchars($activity['expense_detail'] ?? ''); ?>" 
                                                   placeholder="Enter expense detail">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="one_the_spot_donation" class="form-label">
                                                One The Spot Donation
                                            </label>
                                            <input type="number" class="form-control" id="one_the_spot_donation" name="one_the_spot_donation" 
                                                   value="<?php echo htmlspecialchars($activity['one_the_spot_donation'] ?? ''); ?>" 
                                                   placeholder="Enter donation amount" step="0.01">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="detail" class="form-label">
                                                Details
                                            </label>
                                            <textarea class="form-control" id="detail" name="detail" 
                                                      placeholder="Enter activity details"><?php echo htmlspecialchars($activity['detail']); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="remarks" class="form-label">
                                                Remarks
                                            </label>
                                            <textarea class="form-control" id="remarks" name="remarks" 
                                                      placeholder="Enter remarks or notes"><?php echo htmlspecialchars($activity['remarks']); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn-submit">
                                        <i class="fas fa-save me-2"></i>
                                        <?php echo $isEdit ? 'Update Activity' : 'Add Activity'; ?>
                                    </button>
                                    <a href="activity_list.php" class="btn-cancel">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const completedFields = document.getElementById('completedFields');
    
    function toggleCompletedFields() {
        if (statusSelect.value === 'completed') {
            completedFields.style.display = 'block';
        } else {
            completedFields.style.display = 'none';
        }
    }
    
    // Check on page load (for edit mode)
    toggleCompletedFields();
    
    // Check on status change
    statusSelect.addEventListener('change', toggleCompletedFields);
});
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>