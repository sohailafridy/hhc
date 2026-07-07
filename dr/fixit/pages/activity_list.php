<?php include '../../config.php'; ?>
<?php include BASE_PATH.'/fixit/inc/header.php';?> 
<?php
if(isset($_GET['donor_del'])) {
    $id = $_GET['donor_del'];
    $sql = "DELETE FROM f_donors WHERE f_donor_id = $id";
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

.activity-name {
    color: #2c3e50;
    font-weight: 600;
}

.activity-location {
    color: #6c757d;
    font-size: 13px;
    max-width: 200px;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.activity-status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #ffc107;
    color: #856404;
}

.status-in_progress {
    background: #17a2b8;
    color: white;
}

.status-completed {
    background: #28a745;
    color: white;
}

.status-cancelled {
    background: #dc3545;
    color: white;
}

.activity-date {
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
    
    .activity-location {
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
$nameFilter = isset($_GET['name']) ? mysqli_real_escape_string($con, $_GET['name']) : '';
$statusFilter = isset($_GET['status']) ? mysqli_real_escape_string($con, $_GET['status']) : '';

// Build WHERE clause
$whereConditions = [];
$params = [];

// Always filter by current user's team_id (unless admin)
if ($team_id != 0) {
    $whereConditions[] = "a.team_id = ?";
    $params[] = $team_id;
}

if (!empty($categoryFilter)) {
    $whereConditions[] = "a.category_id = ?";
    $params[] = $categoryFilter;
}

if (!empty($nameFilter)) {
    $whereConditions[] = "a.name LIKE ?";
    $params[] = "%$nameFilter%";
}

if (!empty($statusFilter)) {
    $whereConditions[] = "a.status = ?";
    $params[] = $statusFilter;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get total records count
$countQuery = "SELECT COUNT(*) as total FROM f_activities a $whereClause";
$countStmt = mysqli_prepare($con, $countQuery);
if (!empty($params)) {
    mysqli_stmt_bind_param($countStmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($countStmt);
$totalResult = mysqli_stmt_get_result($countStmt);
$totalRecords = mysqli_fetch_assoc($totalResult)['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get activities data with category names, donation and expense amounts
$query = "SELECT a.*, c.cat_name as category_name, 
                 COALESCE(SUM(CASE WHEN d.outside = 1 THEN d.amount ELSE 0 END), 0) as donation_amount,
                 COALESCE(SUM(e.amount), 0) as expense_amount
          FROM f_activities a 
          LEFT JOIN f_activities_cat c ON a.category_id = c.f_activity_cat_id 
          LEFT JOIN f_donation d ON a.activity_id = d.activity_id AND d.outside = 1
          LEFT JOIN f_activities_expense e ON a.activity_id = e.activity_id
          $whereClause 
          GROUP BY a.activity_id 
          ORDER BY a.created_at DESC LIMIT $offset, $recordsPerPage";
$stmt = mysqli_prepare($con, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$activities = [];
while ($row = mysqli_fetch_assoc($result)) {
    $activities[] = $row;
}

// Fetch activity categories for dropdown
$categories = [];
$catQuery = "SELECT f_activity_cat_id, cat_name FROM f_activities_cat ORDER BY cat_name ASC";
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
                        <h4 class="card-title">Activity List</h4>
                    </div>
                    <div class="card-body">
                        <!-- Search Filters -->
                        <div class="search-section">
                            <form method="GET" action="">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="search_category" class="form-label">
                                                <i class="fas fa-tags me-2"></i>Activity Category
                                            </label>
                                            <select class="form-control" id="search_category" name="category">
                                                <option value="">All Categories</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category['f_activity_cat_id']; ?>" 
                                                            <?php echo ($categoryFilter == $category['f_activity_cat_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category['cat_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="search_name" class="form-label">
                                                <i class="fas fa-user me-2"></i>Activity Name
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-search"></i>
                                                </span>
                                                <input type="text" class="form-control" id="search_name" name="name" placeholder="Enter activity name" value="<?php echo htmlspecialchars($nameFilter); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="search_status" class="form-label">
                                                <i class="fas fa-info-circle me-2"></i>Status
                                            </label>
                                            <select class="form-control" id="search_status" name="status">
                                                <option value="">All Status</option>
                                                <option value="pending" <?php echo ($statusFilter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="in_progress" <?php echo ($statusFilter == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="completed" <?php echo ($statusFilter == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo ($statusFilter == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="action-buttons">
                                            <button type="submit" class="btn btn-search">
                                                <i class="fas fa-search me-2"></i>Search Activities
                                            </button>
                                            <a href="list" class="btn btn-clear">
                                                <i class="fas fa-eraser me-2"></i>Clear Filters
                                            </a>
                                            <a href="add" class="btn btn-export">
                                                <i class="fas fa-plus me-2"></i>Add Activity
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Activities Table -->
                        <div class="table-section">
                            <div class="table-responsive">
                                <table class="modern-table" id="activitiesTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center">
                                                <i class="fas fa-hashtag me-1"></i>#</th>
                                            <th>
                                                <i class="fas fa-user me-1"></i>Activity Name</th>
                                            <th>
                                                <i class="fas fa-tags me-1"></i>Category</th>
                                            <th>
                                                <i class="fas fa-map-marker-alt me-1"></i>Location</th>
                                            <th>
                                                <i class="fas fa-info-circle me-1"></i>Status</th>
                                            <th>
                                                <i class="fas fa-donate me-1"></i>On the spot Donation</th>
                                            <th>
                                                <i class="fas fa-receipt me-1"></i>Activity Expense</th>
                                            <th class="text-center">
                                                <i class="fas fa-cogs me-1"></i>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (empty($activities)) {
                                            echo '<tr class="no-records"><td colspan="8" class="text-center">
                                                <div class="no-data">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                                    <h5>No activities found</h5>
                                                    <p class="text-muted">Try adjusting your search filters or add new activities</p>
                                                </div>
                                            </td></tr>';
                                        } else {
                                            foreach ($activities as $index => $activity) {
                                                $rowNumber = ($page - 1) * $recordsPerPage + $index + 1;
                                                $statusClass = 'status-' . $activity['status'];
                                                echo '
                                                <tr class="activity-row">
                                                    <td class="text-center">
                                                        <span class="row-number">' . $rowNumber . '</span>
                                                    </td>
                                                    <td>
                                                        <div class="activity-name">' . htmlspecialchars($activity['name'] ?? '-') . '</div>
                                                    </td>
                                                    <td>
                                                        <span>' . htmlspecialchars($activity['category_name'] ?? '-') . '</span>
                                                    </td>
                                                    <td>
                                                        <span class="activity-location" title="' . htmlspecialchars($activity['location'] ?? '') . '">' . htmlspecialchars($activity['location'] ?? '-') . '</span>
                                                    </td>
                                                    <td>
                                                        <span class="activity-status ' . $statusClass . '">' . htmlspecialchars(ucwords(str_replace('_', ' ', $activity['status']))) . '</span>
                                                    </td>
                                                    <td>
                                                        <span class="donation-amount">' . ($activity['donation_amount'] > 0 ? 'PKR ' . number_format($activity['donation_amount'], 2) : '-') . '</span>
                                                    </td>
                                                    <td>
                                                        <span class="expense-amount">' . ($activity['expense_amount'] > 0 ? 'PKR ' . number_format($activity['expense_amount'], 2) : '-') . '</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="action-buttons-inline">
                                                            <a class="btn btn-sm btn-edit" href="add?id=' . $activity['activity_id'] . '" title="Edit Activity">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a class="btn btn-sm btn-delete" href="list?delete_activity_id=' . $activity['activity_id'] . '" title="Delete Activity" onclick="return confirm(\'Are you sure you want to delete this activity?\')">
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
                        <nav aria-label="Activities pagination" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <!-- Previous button -->
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&category=<?php echo urlencode($categoryFilter); ?>&name=<?php echo urlencode($nameFilter); ?>&status=<?php echo urlencode($statusFilter); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <!-- Page numbers -->
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo urlencode($categoryFilter); ?>&name=<?php echo urlencode($nameFilter); ?>&status=<?php echo urlencode($statusFilter); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Next button -->
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&category=<?php echo urlencode($categoryFilter); ?>&name=<?php echo urlencode($nameFilter); ?>&status=<?php echo urlencode($statusFilter); ?>">
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
        name: '<?php echo $nameFilter; ?>',
        status: '<?php echo $statusFilter; ?>'
    };
    
    // Build export URL
    let params = new URLSearchParams();
    if (filters.category) params.append('category', filters.category);
    if (filters.name) params.append('name', filters.name);
    if (filters.status) params.append('status', filters.status);
    params.append('export', 'csv');
    
    window.open(`activity_list_ajax.php?${params.toString()}`, '_blank');
}
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>