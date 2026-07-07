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
$nameFilter = isset($_GET['name']) ? mysqli_real_escape_string($con, $_GET['name']) : '';
$mobileFilter = isset($_GET['mobile']) ? mysqli_real_escape_string($con, $_GET['mobile']) : '';
$cnicFilter = isset($_GET['cnic']) ? mysqli_real_escape_string($con, $_GET['cnic']) : '';

// Build WHERE clause
$whereConditions = [];
$params = [];

// Always filter by current user's team_id (unless admin)
if ($team_id != 0) {
    $whereConditions[] = "team_id = ?";
    $params[] = $team_id;
}

if (!empty($nameFilter)) {
    $whereConditions[] = "donor_name LIKE ?";
    $params[] = "%$nameFilter%";
}

if (!empty($mobileFilter)) {
    $whereConditions[] = "mobile LIKE ?";
    $params[] = "%$mobileFilter%";
}

if (!empty($cnicFilter)) {
    $whereConditions[] = "cnic LIKE ?";
    $params[] = "%$cnicFilter%";
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get total records count
$countQuery = "SELECT COUNT(*) as total FROM f_donors $whereClause";
$countStmt = mysqli_prepare($con, $countQuery);
if (!empty($params)) {
    mysqli_stmt_bind_param($countStmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($countStmt);
$totalResult = mysqli_stmt_get_result($countStmt);
$totalRecords = mysqli_fetch_assoc($totalResult)['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get donors data - direct query from f_donors
$query = "SELECT * FROM f_donors $whereClause ORDER BY created_at DESC LIMIT $offset, $recordsPerPage";
$stmt = mysqli_prepare($con, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$donors = [];
while ($row = mysqli_fetch_assoc($result)) {
    $donors[] = $row;
}
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Page Header -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Donor List</h4>
                    </div>
                    <div class="card-body">
                        <!-- Search Filters -->
                        <div class="search-section">
                            <form method="GET" action="">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="search_name" class="form-label">
                                                <i class="fas fa-user me-2"></i>Donor Name
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-search"></i>
                                                </span>
                                                <input type="text" class="form-control" id="search_name" name="name" placeholder="Enter donor name" value="<?php echo htmlspecialchars($nameFilter); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="search_mobile" class="form-label">
                                                <i class="fas fa-phone me-2"></i>Mobile Number
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-mobile-alt"></i>
                                                </span>
                                                <input type="text" class="form-control" id="search_mobile" name="mobile" placeholder="Enter mobile number" value="<?php echo htmlspecialchars($mobileFilter); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="search_cnic" class="form-label">
                                                <i class="fas fa-id-card me-2"></i>CNIC Number
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-address-card"></i>
                                                </span>
                                                <input type="text" class="form-control" id="search_cnic" name="cnic" placeholder="Enter CNIC" value="<?php echo htmlspecialchars($cnicFilter); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="action-buttons">
                                            <button type="submit" class="btn btn-search">
                                                <i class="fas fa-search me-2"></i>Search Donors
                                            </button>
                                            <a href="list" class="btn btn-clear">
                                                <i class="fas fa-eraser me-2"></i>Clear Filters
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Donors Table -->
                        <div class="table-section">
                            <div class="table-responsive">
                                <table class="modern-table" id="donorsTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center">
                                                <i class="fas fa-hashtag me-1"></i>#</th>
                                            <th>
                                                <i class="fas fa-user me-1"></i>Donor Name</th>
                                            <th>
                                                <i class="fas fa-map-marker-alt me-1"></i>Address</th>
                                            <th>
                                                <i class="fas fa-phone me-1"></i>Mobile</th>
                                            <th>
                                                <i class="fas fa-id-card me-1"></i>CNIC</th>
                                            <th>
                                                <i class="fas fa-calendar-plus me-1"></i>Register On</th>
                                            <th class="text-center">
                                                <i class="fas fa-cogs me-1"></i>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (empty($donors)) {
                                            echo '<tr class="no-records"><td colspan="7" class="text-center">
                                                <div class="no-data">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                                    <h5>No donors found</h5>
                                                    <p class="text-muted">Try adjusting your search filters</p>
                                                </div>
                                            </td></tr>';
                                        } else {
                                            foreach ($donors as $index => $donor) {
                                                $rowNumber = ($page - 1) * $recordsPerPage + $index + 1;
                                                echo '
                                                <tr class="donor-row">
                                                    <td class="text-center">
                                                        <span class="row-number">' . $rowNumber . '</span>
                                                    </td>
                                                    <td>
                                                        <div class="donor-info">
                                                            <strong>' . htmlspecialchars($donor['donor_name'] ?? '-') . '</strong>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="address-text">' . htmlspecialchars($donor['donor_address'] ?? '-') . '</span>
                                                    </td>
                                                    <td>
                                                        <span class="mobile-number">' . htmlspecialchars($donor['mobile'] ?? '-') . '</span>
                                                    </td>
                                                    <td>
                                                        <span class="cnic-number">' . htmlspecialchars($donor['cnic'] ?? '-') . '</span>
                                                    </td>
                                                    <td>
                                                        <span class="register-date">' . date('M d, Y', strtotime($donor['created_at'])) . '</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="action-buttons-inline">
                                                            <a class="btn btn-sm btn-edit" href="add?id=' . $donor['f_donor_id'] . '" title="Edit Donor">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a class="btn btn-sm btn-delete" href="list?donor_del=' . $donor['f_donor_id'] . '" onclick="return confirm(\'Are you sure you want to delete this donor?\')">
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
                        <nav aria-label="Donors pagination" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <!-- Previous button -->
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&name=<?php echo urlencode($nameFilter); ?>&mobile=<?php echo urlencode($mobileFilter); ?>&cnic=<?php echo urlencode($cnicFilter); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <!-- Page numbers -->
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&name=<?php echo urlencode($nameFilter); ?>&mobile=<?php echo urlencode($mobileFilter); ?>&cnic=<?php echo urlencode($cnicFilter); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Next button -->
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&name=<?php echo urlencode($nameFilter); ?>&mobile=<?php echo urlencode($mobileFilter); ?>&cnic=<?php echo urlencode($cnicFilter); ?>">
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
        name: '<?php echo $nameFilter; ?>',
        mobile: '<?php echo $mobileFilter; ?>',
        cnic: '<?php echo $cnicFilter; ?>'
    };
    
    // Build export URL
    let params = new URLSearchParams();
    if (filters.name) params.append('name', filters.name);
    if (filters.mobile) params.append('mobile', filters.mobile);
    if (filters.cnic) params.append('cnic', filters.cnic);
    params.append('export', 'csv');
    
    window.open(`donor_list_ajax.php?${params.toString()}`, '_blank');
}
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>