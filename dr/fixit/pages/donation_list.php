<?php include '../../config.php'; ?>

<?php
// Handle delete operation
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $team_id = isset($_SESSION['team_id']) ? (int)$_SESSION['team_id'] : 0;
    
    // Delete donation from database
    $delete_query = "DELETE FROM f_donation WHERE donation_id = ? AND team_id = ?";
    $delete_stmt = mysqli_prepare($con, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, 'ii', $delete_id, $team_id);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        $_SESSION['success_msg'] = "Donation deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error deleting donation. Please try again.";
    }
    
    // Redirect to remove delete_id from URL
    header('Location: list');
    exit();
}
?>

<style>
.page-header {
    background: linear-gradient(135deg, #FF6B35 0%, #E85D2C 100%);
    color: white;
    padding: 20px 0;
    margin-bottom: 30px;
    border-radius: 15px 20px 0 0;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.page-header h4 {
    margin: 0;
    font-weight: 700;
    font-size: 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.page-header .btn-add {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    padding: 12px 25px;
    border-radius: 25px;
    color: white;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s ease;
    box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
    text-decoration: none;
}

.page-header .btn-add:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(40, 167, 69, 0.4);
}

.card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px 30px;
    text-align: center;
}

.card-header h5 {
    margin: 0;
    font-weight: 600;
    font-size: 20px;
}

.search-section {
    background: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 10px;
    display: block;
    font-size: 16px;
}

.form-control {
    width: 100%;
    padding: 12px 20px;
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

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #FF6B35 0%, #E85D2C 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #E85D2C 0%, #D63384 100%);
    transform: translateY(-2px);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.table-responsive {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 10px;
    overflow: hidden;
}

.modern-table th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    text-align: left;
    padding: 15px;
    border: none;
    font-size: 14px;
}

.modern-table td {
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
    font-size: 14px;
}

.modern-table tbody tr:hover {
    background: #f8f9fa;
    transform: scale(1.01);
    transition: all 0.3s ease;
}

.donation-amount {
    font-weight: 600;
    color: #28a745;
    font-size: 16px;
}

.donation-type {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.type-regular {
    background: #17a2b8;
    color: white;
}

.type-outside {
    background: #ffc107;
    color: #856404;
}

.badge-success {
    background: #28a745;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.badge-danger {
    background: #dc3545;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

.btn-info {
    background: #17a2b8;
    color: white;
}

.btn-info:hover {
    background: #138496;
    transform: scale(1.05);
}

.btn-warning {
    background: #ffc107;
    color: #856404;
}

.btn-warning:hover {
    background: #e0a800;
    transform: scale(1.05);
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
    transform: scale(1.05);
}

.no-records {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.no-records i {
    font-size: 48px;
    color: #dee2e6;
    margin-bottom: 20px;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 30px;
}

.pagination .page-link {
    padding: 10px 15px;
    border: none;
    border-radius: 8px;
    color: #FF6B35;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
}

.pagination .page-link:hover {
    background: #FF6B35;
    color: white;
    transform: translateY(-2px);
}

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #FF6B35 0%, #E85D2C 100%);
}

/* Responsive Design */
@media (max-width: 768px) {
    .search-section {
        padding: 20px;
    }
    
    .modern-table {
        font-size: 12px;
    }
    
    .modern-table th,
    .modern-table td {
        padding: 10px 5px;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 5px;
    }
}
</style>

<?php include BASE_PATH.'/admin/inc/header.php';?>
<?php include BASE_PATH.'/admin/inc/top.php';?>
<?php include BASE_PATH.'/fixit/inc/nav.php';?>

<?php
// Get current user's team_id from session
$team_id = isset($_SESSION['team_id']) ? (int)$_SESSION['team_id'] : 0;

// Pagination variables
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search filters
$search_donor = isset($_GET['search_donor']) ? mysqli_real_escape_string($con, $_GET['search_donor']) : '';
$search_activity = isset($_GET['search_activity']) ? mysqli_real_escape_string($con, $_GET['search_activity']) : '';
$filter_type = isset($_GET['filter_type']) ? mysqli_real_escape_string($con, $_GET['filter_type']) : '';

// Build WHERE clause
$where_conditions = [];
$params = [];

if ($team_id != 0) {
    $where_conditions[] = "d.team_id = ?";
    $params[] = $team_id;
}

if (!empty($search_donor)) {
    $where_conditions[] = "donor.donor_name LIKE ?";
    $params[] = "%$search_donor%";
}

if (!empty($search_activity)) {
    $where_conditions[] = "a.name LIKE ?";
    $params[] = "%$search_activity%";
}

if (!empty($filter_type)) {
    if ($filter_type == 'regular') {
        $where_conditions[] = "d.outside = 0";
    } elseif ($filter_type == 'outside') {
        $where_conditions[] = "d.outside = 1";
    }
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total records count
$count_query = "SELECT COUNT(*) as total FROM f_donation d 
               LEFT JOIN f_donors donor ON d.donor_id = donor.f_donor_id 
               LEFT JOIN f_activities a ON d.activity_id = a.activity_id 
               $where_clause";
$count_stmt = mysqli_prepare($con, $count_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($count_stmt);
$total_records = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch donations data
$query = "SELECT d.*, donor.donor_name as donor_name, a.name as activity_name, 
               CASE WHEN d.outside = 0 THEN 'Regular' ELSE 'Outside' END as donation_type,
               d.created_at as donation_date
        FROM f_donation d 
        LEFT JOIN f_donors donor ON d.donor_id = donor.f_donor_id 
        LEFT JOIN f_activities a ON d.activity_id = a.activity_id 
        $where_clause 
        ORDER BY d.created_at DESC 
        LIMIT $offset, $records_per_page";
$stmt = mysqli_prepare($con, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$donations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $donations[] = $row;
}
?>

<div class="content-wrapper">
   <!-- Container-fluid starts -->
   <div class="container-fluid">
      <div class="row">
         <div class="col-12">
            <!-- Page Header -->
            <div class="page-header">
               <h4>Donation List</h4>
               <a href="add" class="btn-add">
                  <i class="fas fa-plus"></i> Add New Donation
               </a>
            </div>
            
            <!-- Session Messages -->
            <?php 
            if (isset($_SESSION['success_msg'])): ?>
               <div class="alert alert-success"><?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_msg'])): ?>
               <div class="alert alert-danger"><?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?></div>
            <?php endif; ?>
            
            <!-- Search Section -->
            <div class="card">
               <div class="card-header">
                  <h5>Search & Filter</h5>
               </div>
               <div class="card-block">
                  <form method="GET" action="">
                     <div class="row">
                        <div class="col-md-4">
                           <div class="form-group">
                              <label for="search_donor" class="form-label">Search by Donor Name</label>
                              <input type="text" class="form-control" id="search_donor" name="search_donor" 
                                     value="<?php echo htmlspecialchars($search_donor); ?>" 
                                     placeholder="Enter donor name">
                           </div>
                        </div>
                        <div class="col-md-4">
                           <div class="form-group">
                              <label for="search_activity" class="form-label">Search by Activity</label>
                              <input type="text" class="form-control" id="search_activity" name="search_activity" 
                                     value="<?php echo htmlspecialchars($search_activity); ?>" 
                                     placeholder="Enter activity name">
                           </div>
                        </div>
                        <div class="col-md-4">
                           <div class="form-group">
                              <label for="filter_type" class="form-label">Filter by Type</label>
                              <select class="form-control" id="filter_type" name="filter_type">
                                 <option value="">All Types</option>
                                 <option value="regular" <?php echo ($filter_type == 'regular') ? 'selected' : ''; ?>>Regular</option>
                                 <option value="outside" <?php echo ($filter_type == 'outside') ? 'selected' : ''; ?>>Outside</option>
                              </select>
                           </div>
                        </div>
                        <div class="col-md-12">
                           <div class="form-group">
                              <label>&nbsp;</label><br>
                              <button type="submit" class="btn btn-primary">Search</button>
                              <a href="list" class="btn btn-secondary">Reset</a>
                           </div>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
            
            <!-- Donations Table -->
            <div class="card">
               <div class="card-header">
                  <h5>Donations (<?php echo $total_records; ?> records)</h5>
               </div>
               <div class="table-responsive">
                  <table class="modern-table">
                     <thead>
                        <tr>
                           <th>#</th>
                           <th>Donor Name</th>
                           <th>Activity</th>
                           <th>Type</th>
                           <th>Amount</th>
                           <th>Date</th>
                           <th>Actions</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php if (empty($donations)): ?>
                           <tr class="no-records">
                              <td colspan="6">
                                 <div>
                                       <i class="fas fa-inbox"></i>
                                       <h5>No donations found</h5>
                                       <p>Try adjusting your search filters</p>
                                 </div>
                              </td>
                           </tr>
                        <?php else: ?>
                           <?php $serial = $offset + 1; ?>
                           <?php foreach ($donations as $donation): ?>
                              <tr>
                                 <td><?php echo $serial++; ?></td>
                                 <td><?php echo htmlspecialchars($donation['donor_name']); ?></td>
                                 <td><?php echo htmlspecialchars($donation['activity_name']); ?></td>
                                 <td>
                                    <span class="donation-type <?php echo ($donation['donation_type'] == 'Regular') ? 'type-regular' : 'type-outside'; ?>">
                                          <?php echo htmlspecialchars($donation['donation_type']); ?>
                                    </span>
                                 </td>
                                 <td>
                                    <span class="donation-amount">PKR <?php echo number_format($donation['amount'], 2); ?></span>
                                 </td>
                                 <td><?php echo date('M d, Y', strtotime($donation['donation_date'])); ?></td>
                                 <td>
                                    <div class="action-buttons">
                                       <a href="add?id=<?php echo $donation['donation_id']; ?>" class="btn btn-sm btn-info" title="Edit Donation">
                                          <i class="fas fa-edit"></i>
                                       </a>
                                       <a href="javascript:void(0)" onclick="deleteDonation(<?php echo $donation['donation_id']; ?>)" class="btn btn-sm btn-danger" title="Delete Donation">
                                          <i class="fas fa-trash"></i>
                                       </a>
                                    </div>
                                 </td>
                              </tr>
                           <?php endforeach; ?>
                        <?php endif; ?>
                        <!-- total amount line -->
                        <?php if (empty($donations)): ?>
                           <tr class="total-row">
                              <td colspan="4" style="text-align: right; font-weight: 600; color: #2c3e50;">
                                 <strong>Total Amount:</strong>
                              </td>
                              <td style="font-weight: 700; color: #28a745; font-size: 16px;">
                                 PKR <?php echo number_format(array_sum(array_column($donations, 'amount')), 2); ?>
                              </td>
                              <td colspan="2"></td>
                           </tr>
                        <?php endif; ?>
                     </tbody>
                  </table>
               </div>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
               <div class="pagination">
                  <?php if ($page > 1): ?>
                     <a class="page-link" href="?page=<?php echo $page - 1; ?>&search_donor=<?php echo urlencode($search_donor); ?>&search_activity=<?php echo urlencode($search_activity); ?>&filter_type=<?php echo urlencode($filter_type); ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                     </a>
                  <?php endif; ?>
                  
                  <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                     <a class="page-link <?php echo ($page == $i) ? 'active' : ''; ?>" href="?page=<?php echo $i; ?>&search_donor=<?php echo urlencode($search_donor); ?>&search_activity=<?php echo urlencode($search_activity); ?>&filter_type=<?php echo urlencode($filter_type); ?>">
                        <?php echo $i; ?>
                     </a>
                  <?php endfor; ?>
                  
                  <?php if ($page < $total_pages): ?>
                     <a class="page-link" href="?page=<?php echo $page + 1; ?>&search_donor=<?php echo urlencode($search_donor); ?>&search_activity=<?php echo urlencode($search_activity); ?>&filter_type=<?php echo urlencode($filter_type); ?>">
                        Next <i class="fas fa-chevron-right"></i>
                     </a>
                  <?php endif; ?>
               </div>
            <?php endif; ?>
         </div>
      </div>
   </div>
   <!-- Container-fluid ends -->
</div>

<script>
function deleteDonation(donationId) {
    if (confirm('Are you sure you want to delete this donation?')) {
        window.location.href = '?delete_id=' + donationId;
    }
}
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>