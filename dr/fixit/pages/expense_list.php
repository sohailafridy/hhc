<?php include '../../config.php'; ?>
<?php include BASE_PATH.'/fixit/inc/header.php';?> 
<?php
if(isset($_GET['delete_expense_id'])) {
    $id = $_GET['delete_expense_id'];
    $sql = "DELETE FROM f_expenses WHERE expense_id = $id";
    mysqli_query($con, $sql);
    // header("Location: list");
}
?>
<style>
/* Beautiful Donor List Styles */
.search-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    font-weight: 600;
    color: white;
    margin-bottom: 8px;
    display: block;
    font-size: 14px;
}

.input-group {
    position: relative;
}

.input-group .form-control {
    padding-left: 45px;
    border: none;
    border-radius: 10px;
    height: 45px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.input-group .form-control:focus {
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    transform: translateY(-2px);
}

.input-group-text {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    z-index: 2;
    font-size: 16px;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-search {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    padding: 12px 30px;
    border-radius: 25px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
}

.btn-search:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(40, 167, 69, 0.4);
}

.btn-clear {
    background: #6c757d;
    border: none;
    padding: 12px 25px;
    border-radius: 25px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-clear:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.btn-export {
    background: linear-gradient(135deg, #FF6B35 0%, #E85D2C 100%);
    border: none;
    padding: 12px 25px;
    border-radius: 25px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
}

.btn-export:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 107, 53, 0.4);
}

.table-section {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.modern-table {
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.modern-table thead {
    background: linear-gradient(135deg, #FF6B35 0%, #E85D2C 100%);
    color: white;
}

.modern-table th {
    padding: 18px 15px;
    font-weight: 600;
    font-size: 14px;
    text-align: left;
    border: none;
    position: relative;
}

.modern-table th.text-center {
    text-align: center;
}

.donor-row {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f8f9fa;
}

.donor-row:hover {
    background: #f8f9fa;
    transform: scale(1.01);
}

.modern-table td {
    padding: 15px;
    vertical-align: middle;
    border: none;
    font-size: 14px;
}

.row-number {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 8px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 12px;
    display: inline-block;
}

.donor-info strong {
    color: #2c3e50;
    font-weight: 600;
}

.address-text {
    color: #6c757d;
    font-size: 13px;
    max-width: 200px;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.mobile-number {
    color: #28a745;
    font-weight: 500;
    font-family: monospace;
}

.cnic-number {
    color: #6f42c1;
    font-weight: 500;
    font-family: monospace;
    font-size: 13px;
}

.register-date {
    color: #6c757d;
    font-size: 13px;
}

.action-buttons-inline {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.btn-edit {
    background: #007bff;
    border: none;
    padding: 8px 12px;
    border-radius: 8px;
    color: white;
    font-size: 12px;
    transition: all 0.3s ease;
}

.btn-edit:hover {
    background: #0056b3;
    transform: scale(1.1);
}

.btn-delete {
    background: #dc3545;
    border: none;
    padding: 8px 12px;
    border-radius: 8px;
    color: white;
    font-size: 12px;
    transition: all 0.3s ease;
}

.btn-delete:hover {
    background: #c82333;
    transform: scale(1.1);
}

.no-records {
    background: #f8f9fa;
}

.no-data {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.no-data i {
    color: #dee2e6;
    margin-bottom: 15px;
}

.no-data h5 {
    color: #495057;
    font-weight: 600;
    margin-bottom: 10px;
}

.pagination .page-link {
    border: none;
    margin: 0 2px;
    border-radius: 8px;
    color: #FF6B35;
    font-weight: 500;
    transition: all 0.3s ease;
}

.pagination .page-link:hover {
    background: #FF6B35;
    color: white;
    transform: translateY(-1px);
}

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #FF6B35 0%, #E85D2C 100%);
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .search-section {
        padding: 20px;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-search, .btn-clear, .btn-export {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .modern-table {
        font-size: 12px;
    }
    
    .modern-table th,
    .modern-table td {
        padding: 10px 5px;
    }
    
    .address-text {
        max-width: 120px;
    }
}
</style> 
<?php include BASE_PATH.'/fixit/inc/top.php';?>
<?php include BASE_PATH.'/fixit/inc/nav.php';?>

<?php
// Get current user's team_id from session
$team_id = isset($_SESSION['team_id']) ? (int)$_SESSION['team_id'] : 0;

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// Get filter parameters
$categoryFilter = isset($_GET['category']) ? mysqli_real_escape_string($con, $_GET['category']) : '';
$monthFilter = isset($_GET['month']) ? (int)$_GET['month'] : 0;
$yearFilter = isset($_GET['year']) ? (int)$_GET['year'] : 0;

// Build WHERE clause
$whereConditions = [];
$params = [];

// Always filter by current user's team_id (unless admin)
if ($team_id != 0) {
    $whereConditions[] = "e.team_id = ?";
    $params[] = $team_id;
}

if (!empty($categoryFilter)) {
    $whereConditions[] = "e.expense_type = ?";
    $params[] = $categoryFilter;
}

if (!empty($monthFilter)) {
    $whereConditions[] = "e.Month = ?";
    $params[] = $monthFilter;
}

if (!empty($yearFilter)) {
    $whereConditions[] = "e.Year = ?";
    $params[] = $yearFilter;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get total records count
$countQuery = "SELECT COUNT(*) as total FROM f_expenses e $whereClause";
$countStmt = mysqli_prepare($con, $countQuery);
if (!empty($params)) {
    mysqli_stmt_bind_param($countStmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($countStmt);
$totalResult = mysqli_stmt_get_result($countStmt);
$totalRecords = mysqli_fetch_assoc($totalResult)['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get expenses data with category names
$query = "SELECT e.*, c.cat as category_name FROM f_expenses e LEFT JOIN f_exp_cat c ON e.expense_type = c.f_exp_cat_id $whereClause ORDER BY e.created_at DESC LIMIT $offset, $recordsPerPage";
$stmt = mysqli_prepare($con, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$expenses = [];
while ($row = mysqli_fetch_assoc($result)) {
    $expenses[] = $row;
}

// Fetch expense categories for dropdown
$categories = [];
$catQuery = "SELECT f_exp_cat_id, cat FROM f_exp_cat ORDER BY cat ASC";
$catResult = mysqli_query($con, $catQuery);
while ($row = mysqli_fetch_assoc($catResult)) {
    $categories[] = $row;
}
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Page Header -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Expense List</h4>
                    </div>
                    <div class="card-body">
                        <!-- Search Filters -->
                        <div class="search-section">
                            <form method="GET" action="">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="search_category" class="form-label">
                                                <i class="fas fa-tags me-2"></i>Expense Category
                                            </label>
                                            <select class="form-control" id="search_category" name="category">
                                                <option value="">All Categories</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category['f_exp_cat_id']; ?>" 
                                                            <?php echo ($categoryFilter == $category['f_exp_cat_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category['cat']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="search_month" class="form-label">
                                                <i class="fas fa-calendar me-2"></i>Month
                                            </label>
                                            <select class="form-control" id="search_month" name="month">
                                                <option value="">All Months</option>
                                                <?php 
                                                $months = [
                                                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                                ];
                                                foreach ($months as $monthNum => $monthName):
                                                ?>
                                                    <option value="<?php echo $monthNum; ?>" 
                                                            <?php echo ($monthFilter == $monthNum) ? 'selected' : ''; ?>>
                                                        <?php echo $monthName; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="search_year" class="form-label">
                                                <i class="fas fa-calendar-alt me-2"></i>Year
                                            </label>
                                            <select class="form-control" id="search_year" name="year">
                                                <option value="">All Years</option>
                                                <?php 
                                                $currentYear = date('Y');
                                                for ($year = $currentYear; $year >= $currentYear - 5; $year--):
                                                ?>
                                                    <option value="<?php echo $year; ?>" 
                                                            <?php echo ($yearFilter == $year) ? 'selected' : ''; ?>>
                                                        <?php echo $year; ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="action-buttons">
                                            <button type="submit" class="btn btn-search">
                                                <i class="fas fa-search me-2"></i>Search Expenses
                                            </button>
                                            <a href="list" class="btn btn-clear">
                                                <i class="fas fa-eraser me-2"></i>Clear Filters
                                            </a>
                                            <a href="add" class="btn btn-export">
                                                <i class="fas fa-plus me-2"></i>Add Expense
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Expenses Table -->
                        <div class="table-section">
                            <div class="table-responsive">
                                <table class="modern-table" id="expensesTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center">
                                                <i class="fas fa-hashtag me-1"></i>#</th>
                                            <th>
                                                <i class="fas fa-tags me-1"></i>Category</th>
                                            <th>
                                                <i class="fas fa-money-bill-wave me-1"></i>Amount</th>
                                            <th>
                                                <i class="fas fa-calendar me-1"></i>Period</th>
                                            <th>
                                                <i class="fas fa-info-circle me-1"></i>Details</th>
                                            <th>
                                                <i class="fas fa-clock me-1"></i>Created On</th>
                                            <th class="text-center">
                                                <i class="fas fa-cogs me-1"></i>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (empty($expenses)) {
                                            echo '<tr class="no-records"><td colspan="7" class="text-center">
                                                <div class="no-data">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                                    <h5>No expenses found</h5>
                                                    <p class="text-muted">Try adjusting your search filters or add new expenses</p>
                                                </div>
                                            </td></tr>';
                                        } else {
                                            foreach ($expenses as $index => $expense) {
                                                $rowNumber = ($page - 1) * $recordsPerPage + $index + 1;
                                                $monthName = isset($months[$expense['Month']]) ? $months[$expense['Month']] : 'Unknown';
                                                echo '
                                                <tr class="expense-row">
                                                    <td class="text-center">
                                                        <span class="row-number">' . $rowNumber . '</span>
                                                    </td>
                                                    <td>
                                                        <div class="expense-category">' . htmlspecialchars($expense['category_name'] ?? 'Unknown') . '</div>
                                                    </td>
                                                    <td>
                                                        <span class="expense-amount">Rs. ' . number_format($expense['amount'], 2) . '</span>
                                                    </td>
                                                    <td>
                                                        <span class="expense-period">' . $monthName . ' ' . $expense['Year'] . '</span>
                                                    </td>
                                                    <td>
                                                        <span class="expense-detail" title="' . htmlspecialchars($expense['detail'] ?? '') . '">' . htmlspecialchars($expense['detail'] ?? '-') . '</span>
                                                    </td>
                                                    <td>
                                                        <span class="expense-date">' . date('M d, Y', strtotime($expense['created_at'])) . '</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="action-buttons-inline">
                                                            <a class="btn btn-sm btn-edit" href="add?id=' . $expense['expense_id'] . '" title="Edit Expense">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a class="btn btn-sm btn-delete" href="list?delete_expense_id=' . $expense['expense_id'] . '" title="Delete Expense" onclick="return confirm(\'Are you sure you want to delete this expense?\');">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>';
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                        <nav aria-label="Expenses pagination" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <!-- Previous button -->
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&category=<?php echo urlencode($categoryFilter); ?>&month=<?php echo $monthFilter; ?>&year=<?php echo $yearFilter; ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <!-- Page numbers -->
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo urlencode($categoryFilter); ?>&month=<?php echo $monthFilter; ?>&year=<?php echo $yearFilter; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Next button -->
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&category=<?php echo urlencode($categoryFilter); ?>&month=<?php echo $monthFilter; ?>&year=<?php echo $yearFilter; ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>



function exportData() {
    const filters = {
        category: '<?php echo $categoryFilter; ?>',
        month: '<?php echo $monthFilter; ?>',
        year: '<?php echo $yearFilter; ?>'
    };
    
    // Build export URL
    let params = new URLSearchParams();
    if (filters.category) params.append('category', filters.category);
    if (filters.month) params.append('month', filters.month);
    if (filters.year) params.append('year', filters.year);
    params.append('export', 'csv');
    
    window.open(`expense_list_ajax.php?${params.toString()}`, '_blank');
}
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>