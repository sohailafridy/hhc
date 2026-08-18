<?php include '../../config.php'; ?>
<?php include BASE_PATH . '/admin/inc/header.php'; ?>
<?php include BASE_PATH . '/admin/inc/top.php'; ?>
<?php include BASE_PATH . '/admin/inc/nav.php'; ?>

<?php
// ============================================
// DELETE USER
// ============================================
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    // First check if user exists
    $check_query = "SELECT user_id FROM users WHERE user_id = $delete_id";
    $check_result = mysqli_query($con, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $delete_query = "DELETE FROM users WHERE user_id = $delete_id";
        if (mysqli_query($con, $delete_query)) {
            $_SESSION['success_msg'] = "User deleted successfully!";
        } else {
            $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
        }
    } else {
        $_SESSION['error_msg'] = "User not found!";
    }
    
    // Redirect back with filters
    $redirect_url = BASE_URL . 'admin/users';
    if (isset($_GET['type'])) {
        $redirect_url .= '?type=' . $_GET['type'];
    }
    header('Location: ' . $redirect_url);
    exit();
}

// ============================================
// TOGGLE USER STATUS
// ============================================
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $user_id = (int)$_GET['toggle_status'];
    $new_status = isset($_GET['status']) ? (int)$_GET['status'] : 0;
    
     $update_query = "UPDATE users SET status = $new_status, updated_at = NOW() WHERE user_id = $user_id";
    if (mysqli_query($con, $update_query)) {
        $_SESSION['success_msg'] = "User status updated successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }
    
    // $redirect_url = BASE_URL . 'admin/users';
    // if (isset($_GET['type'])) {
    //     $redirect_url .= '?type=' . $_GET['type'];
    // }
    // header('Location: ' . $redirect_url);
    // exit();
}

// ============================================
// GET USER TYPES
// ============================================
$user_types_query = "SELECT usertypes_id, type FROM usertypes ORDER BY type ASC";
$user_types_result = mysqli_query($con, $user_types_query);
$user_types = [];
while ($row = mysqli_fetch_assoc($user_types_result)) {
    $user_types[] = $row;
}

// ============================================
// FILTERS & PAGINATION
// ============================================
$filter_type = isset($_GET['type']) ? mysqli_real_escape_string($con, $_GET['type']) : '';
$filter_search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$filter_status = isset($_GET['status']) ? (int)$_GET['status'] : -1;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Build WHERE clause
$where_conditions = [];

if (!empty($filter_type)) {
    $where_conditions[] = "ut.type = '$filter_type'";
}

if (!empty($filter_search)) {
    $where_conditions[] = "(u.username LIKE '%$filter_search%' OR u.email LIKE '%$filter_search%')";
}

if ($filter_status !== -1) {
    $where_conditions[] = "u.status = $filter_status";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Count total records
$count_query = "SELECT COUNT(*) as total 
                FROM users u
                LEFT JOIN usertypes ut ON u.user_type_id = ut.usertypes_id
                $where_clause";
$count_result = mysqli_query($con, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $per_page);

// Fetch users
$query = "SELECT u.*, ut.type as user_type, 
                 (SELECT COUNT(*) FROM entities WHERE user_id = u.user_id) as entity_count
          FROM users u
          LEFT JOIN usertypes ut ON u.user_type_id = ut.usertypes_id
          $where_clause
          ORDER BY u.created_at DESC
          LIMIT $offset, $per_page";

$result = mysqli_query($con, $query);
?>

<style>
:root {
    --primary: #4facfe;
    --primary-dark: #0d6efd;
    --accent: #00f2fe;
    --text: #1e293b;
    --muted: #64748b;
    --border: #e2e8f0;
    --bg: #f8fafc;
    --card: #ffffff;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
}

.content-wrapper {
    background: var(--bg);
    min-height: 100vh;
    padding: 24px 32px 60px;
}

/* ===== PAGE HEADER ===== */
.page-header-modern {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 28px;
}

.page-header-modern h4 {
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-header-modern h4 i {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: white;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.badge-count {
    background: var(--primary);
    color: white;
    padding: 2px 12px;
    border-radius: 50px;
    font-size: 0.8rem;
}

/* ===== FILTER SECTION ===== */
.filter-section {
    background: white;
    border-radius: 16px;
    padding: 20px 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid var(--border);
    margin-bottom: 24px;
}

.filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 180px;
}

.filter-group label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 4px;
    display: block;
}

.filter-group .form-control,
.filter-group .form-select {
    border-radius: 10px;
    border: 2px solid var(--border);
    padding: 8px 14px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.filter-group .form-control:focus,
.filter-group .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79,172,254,0.12);
}

.btn-filter {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    border: none;
    padding: 9px 24px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(13,110,253,0.3);
    color: white;
}

.btn-reset {
    background: var(--bg);
    color: var(--muted);
    border: 2px solid var(--border);
    padding: 9px 20px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-reset:hover {
    background: var(--danger);
    color: white;
    border-color: var(--danger);
}

/* ===== TABLE ===== */
.table-responsive {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid var(--border);
}

.table {
    margin: 0;
}

.table thead {
    background: #f8fafc;
}

.table th {
    font-weight: 700;
    font-size: 0.8rem;
    text-transform: uppercase;
    color: var(--muted);
    padding: 14px 16px;
    border-bottom: 2px solid var(--border);
}

.table td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}

.table tbody tr:hover {
    background: #f8fafc;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    color: white;
}

.user-avatar.admin { background: linear-gradient(135deg, #ef4444, #dc2626); }
.user-avatar.doctor { background: linear-gradient(135deg, #4facfe, #0d6efd); }
.user-avatar.hospital { background: linear-gradient(135deg, #10b981, #059669); }
.user-avatar.lab { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
.user-avatar.blood_bank { background: linear-gradient(135deg, #f59e0b, #d97706); }
.user-avatar.fixit { background: linear-gradient(135deg, #ec4899, #db2777); }
.user-avatar.default { background: linear-gradient(135deg, #6b7280, #4b5563); }

.badge-type {
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
}

.badge-type.admin { background: #fee2e2; color: #991b1b; }
.badge-type.doctor { background: #dbeafe; color: #1e40af; }
.badge-type.hospital { background: #d1fae5; color: #065f46; }
.badge-type.lab { background: #ede9fe; color: #5b21b6; }
.badge-type.blood_bank { background: #fef3c7; color: #92400e; }
.badge-type.fixit { background: #fce7f3; color: #9d174d; }

.badge-status {
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
}

.badge-status.active { background: #d1fae5; color: #065f46; }
.badge-status.inactive { background: #fee2e2; color: #991b1b; }

.btn-action {
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 0.8rem;
    border: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-action:hover {
    transform: translateY(-1px);
}

.btn-action.view { background: #dbeafe; color: #1e40af; }
.btn-action.edit { background: #fef3c7; color: #92400e; }
.btn-action.delete { background: #fee2e2; color: #991b1b; }
.btn-action.status { background: #d1fae5; color: #065f46; }

/* ===== PAGINATION ===== */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    padding: 16px 24px;
    background: white;
    border-radius: 0 0 16px 16px;
    border-top: 1px solid var(--border);
}

.pagination {
    margin: 0;
    gap: 4px;
}

.pagination .page-link {
    border: none;
    border-radius: 8px;
    color: var(--text);
    padding: 8px 14px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.pagination .page-link:hover {
    background: var(--primary);
    color: white;
}

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
}

.pagination .page-item.disabled .page-link {
    opacity: 0.4;
    cursor: not-allowed;
}

.pagination-info {
    color: var(--muted);
    font-size: 0.85rem;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .content-wrapper {
        padding: 16px;
    }
    .filter-row {
        flex-direction: column;
    }
    .filter-group {
        min-width: 100%;
    }
}

@media (max-width: 768px) {
    .table-responsive {
        overflow-x: auto;
    }
    .pagination-container {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<div class="content-wrapper">

    <!-- ===== PAGE HEADER ===== -->
    <div class="page-header-modern">
        <h4>
            <i class="fas fa-users"></i>
            Users Management
            <span class="badge-count"><?php echo $total_records; ?></span>
        </h4>
        <?php if (!empty($filter_type)): ?>
            <span class="badge bg-primary">
                <i class="fas fa-filter me-1"></i>
                <?php echo ucfirst(str_replace('_', ' ', $filter_type)); ?>
            </span>
        <?php endif; ?>
    </div>

    <!-- ===== ALERTS ===== -->
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $_SESSION['success_msg'];
            unset($_SESSION['success_msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo $_SESSION['error_msg'];
            unset($_SESSION['error_msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ===== FILTER SECTION ===== -->
    <div class="filter-section">
        <form method="GET" action="" id="filterForm">
            <div class="filter-row">
                <div class="filter-group">
                    <label><i class="fas fa-search"></i> Search</label>
                    <input type="text" class="form-control" name="search" 
                           value="<?php echo htmlspecialchars($filter_search); ?>" 
                           placeholder="Username or Email...">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-user-tag"></i> User Type</label>
                    <select class="form-select" name="type">
                        <option value="">All Types</option>
                        <?php foreach ($user_types as $type): ?>
                            <option value="<?php echo $type['type']; ?>" 
                                    <?php echo ($filter_type == $type['type']) ? 'selected' : ''; ?>>
                                <?php echo ucfirst(str_replace('_', ' ', $type['type'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-toggle-on"></i> Status</label>
                    <select class="form-select" name="status">
                        <option value="-1">All Status</option>
                        <option value="1" <?php echo ($filter_status == 1) ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo ($filter_status == 0) ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="filter-group" style="flex: 0 0 auto; display: flex; gap: 8px;">
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="<?php echo BASE_URL; ?>admin/users" class="btn-reset">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </div>
            <input type="hidden" name="page" value="1">
        </form>
    </div>

    <!-- ===== TABLE ===== -->
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>User</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $serial = $offset + 1; ?>
                    <?php while ($user = mysqli_fetch_assoc($result)): ?>
                        <?php
                        $avatar_class = !empty($user['user_type']) ? $user['user_type'] : 'default';
                        $avatar_letter = !empty($user['username']) ? strtoupper(substr($user['username'], 0, 1)) : 'U';
                        ?>
                        <tr>
                            <td><?php echo $serial++; ?></td>
                            <td>
                                <div class="user-avatar <?php echo $avatar_class; ?>" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.9rem;color:#fff;">
                                    <?php echo $avatar_letter; ?>
                                </div>
                            </td>
                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge-type <?php echo $user['user_type'] ?? 'default'; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $user['user_type'] ?? 'Unknown')); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge-status <?php echo $user['status'] == 1 ? 'active' : 'inactive'; ?>">
                                    <?php echo $user['status'] == 1 ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <!-- View Button -->
                                    <a href="javascript:void(0)" 
                                       class="btn-action view" 
                                       onclick="viewUser(<?php echo $user['user_id']; ?>)"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <!-- Edit Button -->
                                    <a href="<?php echo BASE_URL; ?>admin/users/edit?id=<?php echo $user['user_id']; ?>" 
                                       class="btn-action edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <!-- Toggle Status -->
                                    <?php if ($user['status'] == 1): ?>
                                        <a href="<?php echo BASE_URL; ?>admin/users?toggle_status=<?php echo $user['user_id']; ?>&status=0<?php echo !empty($filter_type) ? '&type=' . $filter_type : ''; ?>" 
                                           class="btn-action status" title="Deactivate"
                                           onclick="return confirm('Are you sure you want to deactivate this user?')">
                                            <i class="fas fa-pause"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo BASE_URL; ?>admin/users?toggle_status=<?php echo $user['user_id']; ?>&status=1<?php echo !empty($filter_type) ? '&type=' . $filter_type : ''; ?>" 
                                           class="btn-action status" title="Activate"
                                           onclick="return confirm('Are you sure you want to activate this user?')">
                                            <i class="fas fa-play"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <!-- Delete Button -->
                                    <a href="<?php echo BASE_URL; ?>admin/users?delete_id=<?php echo $user['user_id']; ?><?php echo !empty($filter_type) ? '&type=' . $filter_type : ''; ?>" 
                                       class="btn-action delete" title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-users fa-2x text-muted mb-2 d-block"></i>
                            <p class="text-muted">No users found matching your criteria.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ===== PAGINATION ===== -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <nav>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($filter_search); ?>&type=<?php echo urlencode($filter_type); ?>&status=<?php echo $filter_status; ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    if ($start_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?page=1&search=' . urlencode($filter_search) . '&type=' . urlencode($filter_type) . '&status=' . $filter_status . '">1</a></li>';
                        if ($start_page > 2) {
                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                        }
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        if ($i == $page) {
                            echo '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
                        } else {
                            echo '<li class="page-item"><a class="page-link" href="?page=' . $i . '&search=' . urlencode($filter_search) . '&type=' . urlencode($filter_type) . '&status=' . $filter_status . '">' . $i . '</a></li>';
                        }
                    }
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&search=' . urlencode($filter_search) . '&type=' . urlencode($filter_type) . '&status=' . $filter_status . '">' . $total_pages . '</a></li>';
                    }
                    ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($filter_search); ?>&type=<?php echo urlencode($filter_type); ?>&status=<?php echo $filter_status; ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="pagination-info">
                Showing <?php echo ($offset + 1); ?>-<?php echo min($offset + $per_page, $total_records); ?> of <?php echo $total_records; ?> users
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- ===== VIEW USER MODAL ===== -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user me-2"></i>User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================
// VIEW USER DETAILS (AJAX)
// ============================================
function viewUser(userId) {
    const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
    const content = document.getElementById('userDetailsContent');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading user details...</p>
        </div>
    `;
    
    modal.show();
    
    // Fetch user details
    fetch('<?php echo BASE_URL; ?>admin/users/get-user.php?id=' + userId)
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                const user = data.data;
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase fw-bold">Username</label>
                                <p class="fw-bold fs-5">${user.username}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase fw-bold">Email</label>
                                <p>${user.email}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase fw-bold">User Type</label>
                                <p><span class="badge-type ${user.user_type}">${user.user_type ? user.user_type.charAt(0).toUpperCase() + user.user_type.slice(1) : 'Unknown'}</span></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase fw-bold">Status</label>
                                <p><span class="badge-status ${user.status == 1 ? 'active' : 'inactive'}">${user.status == 1 ? 'Active' : 'Inactive'}</span></p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase fw-bold">Created At</label>
                                <p>${new Date(user.created_at).toLocaleString()}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase fw-bold">Last Updated</label>
                                <p>${user.updated_at ? new Date(user.updated_at).toLocaleString() : 'Never'}</p>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <label class="text-muted small text-uppercase fw-bold">Associated Entities</label>
                            <p>${user.entity_count || 0} entities linked to this user</p>
                        </div>
                    </div>
                `;
            } else {
                content.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <p class="text-danger">${data.message || 'User not found'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            content.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                    <p class="text-danger">Error loading user details. Please try again.</p>
                </div>
            `;
        });
}
</script>

<?php include BASE_PATH . '/admin/inc/footer.php'; ?>