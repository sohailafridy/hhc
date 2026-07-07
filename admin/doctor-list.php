<?php
require_once '../config.php';
check_auth();

$title = "Doctor List";

//Delete/hide doctor
if (isset($_GET['del_id'])) {
    $del_id = $_GET['del_id'];
    $sql = "UPDATE users SET status = 0,updated_at = NOW() WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$del_id]);
    if ($stmt->rowCount() > 0) {
        echo '<script>alert("Doctor hidden successfully.");window.location.href="doctor-list.php";</script>';
    } else {
        echo '<script>alert("Failed to hide doctor.");window.location.href="doctor-list.php";</script>';
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
              FROM doctors d 
              LEFT JOIN users u ON d.user_id = u.id 
              WHERE 1=1";
$params_count = [];
if (!empty($search)) {
    $sql_count .= " AND (d.name LIKE ? OR d.specialization LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $searchTerm = "%$search%";
    $params_count = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
}
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params_count);
$total_records = $stmt_count->fetch()['total'];
$total_pages = ceil($total_records / $per_page);

// Fetch doctors with optional search and pagination
$sql = "SELECT d.*, u.username, u.email 
        FROM doctors d 
        LEFT JOIN users u ON d.user_id = u.id 
        WHERE 1=1 AND u.status = 1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (d.name LIKE ? OR d.specialization LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

$sql .= " ORDER BY d.created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$doctors = $stmt->fetchAll();

include_once(BASE_PATH . '/inc/header.php');
?>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user-md me-2 text-primary"></i>Doctor List</h5>
                <a href="doctor-add.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Add New Doctor
                </a>
            </div>
            <div class="card-body">
                <!-- Search Form -->
                <form method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-10">
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name, specialization, username or email...">
                            <input type="hidden" name="page" value="1">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Doctor Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Specialization</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($doctors) > 0): ?>
                                <?php foreach ($doctors as $doctor): ?>
                                    <tr>
                                        <td><?php echo $doctor['id']; ?></td>
                                        <td>
                                            <?php if (!empty($doctor['img'])): ?>
                                                <img src="<?php echo BASE_URL . '/' . htmlspecialchars($doctor['img']); ?>" alt="<?php echo htmlspecialchars($doctor['name']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                            <?php else: ?>
                                                <div class="bg-primary text-white rounded-circle d-inline-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                                                    <i class="fas fa-user-md"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($doctor['name']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['username'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['email'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $doctor['status'] == 1 ? 'success' : 'danger'; ?>">
                                                <?php echo $doctor['status'] == 1 ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d-m-Y', strtotime($doctor['created_at'])); ?></td>
                                        <td>
                                            <a href="doctor-edit.php?id=<?php echo $doctor['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                            <a href="doctor-list.php?del_id=<?php echo $doctor['user_id']; ?>" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No doctors found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-4">
                            <!-- Previous Button -->
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a>
                            </li>

                            <!-- Page Numbers -->
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Button -->
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