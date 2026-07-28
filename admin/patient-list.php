<?php
require_once '../config.php';
check_auth();

$title = "Patient List";

// Delete/Hide Patient
if (isset($_GET['del_id'])) {
    $del_id = $_GET['del_id'];
    $sql = "UPDATE users SET status = 0, updated_at = NOW() WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$del_id]);
    
    if ($stmt->rowCount() > 0) {
        echo '<script>alert("Patient hidden successfully.");window.location.href="' . BASE_URL . '/admin/patient/list";</script>';
    } else {
        echo '<script>alert("Failed to hide patient.");window.location.href="'. BASE_URL .'/admin/patient/list";</script>';
    }
}

// Get search query
$search = trim($_GET['search'] ?? '');

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Fetch total records for pagination
$sql_count = "SELECT COUNT(*) as total
              FROM patients p
              LEFT JOIN users u ON p.user_id = u.id
              WHERE 1=1";
$params_count = [];

if (!empty($search)) {
    $sql_count .= " AND (p.name LIKE ? OR p.mr_number LIKE ? OR p.cnic LIKE ? OR p.phone LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $searchTerm = "%$search%";
    $params_count = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params_count);
$total_records = $stmt_count->fetch()['total'];
$total_pages = ceil($total_records / $per_page);

// Fetch patients with optional search and pagination
$sql = "SELECT p.*, u.username, u.email, u.status as user_status
        FROM patients p
        LEFT JOIN users u ON p.user_id = u.id
        WHERE 1=1 AND u.status = 1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.mr_number LIKE ? OR p.cnic LIKE ? OR p.phone LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

$sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$patients = $stmt->fetchAll();

include_once(BASE_PATH . '/inc/header.php');
?>
<style>
    .table-container{
    background:#fff;
    border-radius:15px;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
    overflow:hidden;
}

.custom-table{
    overflow-x:auto;
    overflow-y:hidden;
    white-space:nowrap;
    scrollbar-width:thin;
    scrollbar-color:#0d6efd #f1f1f1;
}

/* Chrome Scrollbar */
.custom-table::-webkit-scrollbar{
    height:10px;
}

.custom-table::-webkit-scrollbar-track{
    background:#f1f1f1;
}

.custom-table::-webkit-scrollbar-thumb{
    background:#0d6efd;
    border-radius:20px;
}

.custom-table table{
    min-width:1500px; /* Forces horizontal scrolling */
}

.custom-table thead th{
    position:sticky;
    top:0;
    z-index:5;
    background:#0d6efd;
    color:#fff;
    font-weight:600;
    border:none;
    padding:14px;
}

.custom-table tbody td{
    vertical-align:middle;
    padding:14px;
}

.custom-table tbody tr{
    transition:.25s;
}

.custom-table tbody tr:hover{
    background:#f8fbff;
    transform:scale(1.003);
}

.custom-table .btn{
    margin:0 2px;
    border-radius:8px;
}

.custom-table .badge{
    padding:7px 12px;
    font-size:12px;
    font-weight:600;
}

.table td,
.table th{
    white-space:nowrap;
}
</style>
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2 text-primary"></i>Patient List
                </h5>
                <a href="<?=BASE_URL?>/admin/patient/add" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Add New Patient
                </a>
            </div>
            <div class="card-body">
                <!-- Search Form -->
                <form method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-10">
                            <input type="text" class="form-control" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by MR Number, Name, CNIC, Phone, Username or Email...">
                            <input type="hidden" name="page" value="1">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Patient Table -->
                <div class="table-container">
    <div class="table-responsive custom-table">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>MR Number</th>
                    <th>Name</th>
                    <th>CNIC</th>
                    <th>Phone</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($patients) > 0): ?>
                    <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($patient['mr_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($patient['name']); ?></td>
                            <td><?php echo htmlspecialchars($patient['cnic'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                            <td><?php echo htmlspecialchars($patient['username'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($patient['email'] ?? '-'); ?></td>
                            <td><?php echo $patient['age']; ?></td>
                            <td><?php echo htmlspecialchars($patient['gender']); ?></td>

                            <td>
                                <span class="badge rounded-pill bg-<?php echo $patient['status'] == 1 ? 'success' : 'danger'; ?>">
                                    <?php echo $patient['status'] == 1 ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>

                            <td>
                                <?php echo date('d M, Y', strtotime($patient['registration_date'] ?? $patient['created_at'])); ?>
                            </td>

                            <td class="text-center">
                                <div class="btn-group btn-group-sm">

                                    <a href="<?=BASE_URL?>/admin/patient/visit/add?patient_id=<?php echo $patient['id']; ?>"
                                        class="btn btn-outline-success"
                                        title="New Visit">
                                        <i class="fas fa-notes-medical"></i>
                                    </a>

                                    <a href="<?=BASE_URL?>/admin/patient/detail?id=<?php echo $patient['id']; ?>"
                                        class="btn btn-outline-info"
                                        title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="<?=BASE_URL?>/admin/patient/edit?id=<?php echo $patient['id']; ?>"
                                        class="btn btn-outline-primary"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <a href="<?=BASE_URL?>/admin/patient/list?del_id=<?php echo $patient['user_id']; ?>"
                                        class="btn btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to hide this patient?');"
                                        title="Hide">
                                        <i class="fas fa-trash"></i>
                                    </a>

                                </div>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">
                            No patients found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>

        </table>
    </div>
</div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-4">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once(BASE_PATH . '/inc/footer.php'); ?>