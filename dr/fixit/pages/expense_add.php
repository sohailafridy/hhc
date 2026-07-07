<?php include '../../config.php'; ?>
<?php include BASE_PATH.'/fixit/inc/header.php';?> 

<style>
/* Beautiful Expense Add Form Styles */
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
$expenseId = $isEdit ? (int)$_GET['id'] : 0;

// Fetch expense categories from f_exp_cat table
$categories = [];
$catQuery = "SELECT f_exp_cat_id, cat FROM f_exp_cat ORDER BY cat ASC";
$catResult = mysqli_query($con, $catQuery);
while ($row = mysqli_fetch_assoc($catResult)) {
    $categories[] = $row;
}

$expense = [
    'expense_type' => '',
    'amount' => '',
    'Month' => '',
    'Year' => '',
    'detail' => ''
];

// If edit mode, fetch existing expense data
if ($isEdit) {
    $query = "SELECT * FROM f_expenses WHERE expense_id = ? AND team_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $expenseId, $team_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $expense = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expense_type = mysqli_real_escape_string($con, $_POST['expense_type']);
    $amount = mysqli_real_escape_string($con, $_POST['amount']);
    $Month = mysqli_real_escape_string($con, $_POST['Month']);
    $Year = mysqli_real_escape_string($con, $_POST['Year']);
    $detail = mysqli_real_escape_string($con, $_POST['detail']);
    
    // Validate required fields
    $errors = [];
    if (empty($expense_type)) $errors[] = "Expense category is required";
    if (empty($amount) || !is_numeric($amount) || $amount <= 0) $errors[] = "Valid amount is required";
    if (empty($Month)) $errors[] = "Month is required";
    if (empty($Year)) $errors[] = "Year is required";
    
    if (empty($errors)) {
        if ($isEdit) {
            // Update existing expense
            $updateQuery = "UPDATE f_expenses SET expense_type = ?, amount = ?, Month = ?, Year = ?, detail = ?, updated_at = NOW() WHERE expense_id = ? AND team_id = ?";
            $updateStmt = mysqli_prepare($con, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, 'sdssisi', $expense_type, $amount, $Month, $Year, $detail, $expenseId, $team_id);
            
            if (mysqli_stmt_execute($updateStmt)) {
                $successMessage = "Expense updated successfully!";
            } else {
                $errorMessage = "Error updating expense. Please try again.";
            }
        } else {
            // Add new expense
            $insertQuery = "INSERT INTO f_expenses (team_id, expense_type, amount, Month, Year, detail, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $insertStmt = mysqli_prepare($con, $insertQuery);
            mysqli_stmt_bind_param($insertStmt, 'idssss', $team_id, $expense_type, $amount, $Month, $Year, $detail);
            
            if (mysqli_stmt_execute($insertStmt)) {
                $successMessage = "Expense added successfully!";
                // Reset form fields after successful submission
                $expense = [
                    'expense_type' => '',
                    'amount' => '',
                    'Month' => '',
                    'Year' => '',
                    'detail' => ''
                ];
            } else {
                $errorMessage = "Error adding expense. Please try again.";
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
                                <?php echo $isEdit ? 'Edit Expense' : 'Add New Expense'; ?>
                            </h3>
                            <div class="subtitle">
                                <?php echo $isEdit ? 'Update expense information below' : 'Fill in the expense details below'; ?>
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
                                            <label for="expense_type" class="form-label">
                                                Expense Category <span class="required">*</span>
                                            </label>
                                            <select class="form-control" id="expense_type" name="expense_type" required>
                                                <option value="">Select Category</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category['f_exp_cat_id']; ?>" 
                                                            <?php echo ($expense['expense_type'] == $category['f_exp_cat_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category['cat']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="amount" class="form-label">
                                                Amount <span class="required">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="amount" name="amount" 
                                                   value="<?php echo htmlspecialchars($expense['amount']); ?>" 
                                                   placeholder="Enter amount" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Month" class="form-label">
                                                Month <span class="required">*</span>
                                            </label>
                                            <select class="form-control" id="Month" name="Month" required>
                                                <option value="">Select Month</option>
                                                <?php 
                                                $months = [
                                                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                                ];
                                                foreach ($months as $monthNum => $monthName):
                                                ?>
                                                    <option value="<?php echo $monthNum; ?>" 
                                                            <?php echo ($expense['Month'] == $monthNum) ? 'selected' : ''; ?>>
                                                        <?php echo $monthName; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Year" class="form-label">
                                                Year <span class="required">*</span>
                                            </label>
                                            <select class="form-control" id="Year" name="Year" required>
                                                <option value="">Select Year</option>
                                                <?php 
                                                $currentYear = date('Y');
                                                for ($year = $currentYear; $year >= $currentYear - 5; $year--):
                                                ?>
                                                    <option value="<?php echo $year; ?>" 
                                                            <?php echo ($expense['Year'] == $year) ? 'selected' : ''; ?>>
                                                        <?php echo $year; ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="detail" class="form-label">
                                                Details/Description
                                            </label>
                                            <textarea class="form-control" id="detail" name="detail" 
                                                      placeholder="Enter expense details or description"><?php echo htmlspecialchars($expense['detail']); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn-submit">
                                        <i class="fas fa-save me-2"></i>
                                        <?php echo $isEdit ? 'Update Expense' : 'Add Expense'; ?>
                                    </button>
                                    <a href="expense_list.php" class="btn-cancel">
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