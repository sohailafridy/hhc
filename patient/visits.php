<?php
require_once '../config.php';
check_auth();

// Get logged in patient's user_id
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

// Fetch Patient ID
$stmt_patient = $pdo->prepare("SELECT id FROM patients WHERE user_id = ?");
$stmt_patient->execute([$user_id]);
$patient = $stmt_patient->fetch();

if (!$patient) {
    echo "<script>alert('Patient record not found!'); window.location.href='" . BASE_URL . "/patient/dashboard.php';</script>";
    exit;
}

$patient_id = $patient['id'];

// Search functionality
$search = trim($_GET['search'] ?? '');

// Pagination
$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Count total visits
$sql_count = "SELECT COUNT(*) as total FROM visits WHERE patient_id = ?";
$params_count = [$patient_id];

if (!empty($search)) {
    $sql_count .= " AND (chief_complaint LIKE ? OR diagnosis LIKE ?)";
    $searchTerm = "%$search%";
    $params_count = [$patient_id, $searchTerm, $searchTerm];
}

$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params_count);
$total_records = $stmt_count->fetch()['total'];
$total_pages = ceil($total_records / $per_page);

// Fetch all visits
$sql = "
    SELECT v.*, d.name as doctor_name, d.specialization 
    FROM visits v 
    LEFT JOIN doctors d ON v.doctor_id = d.id 
    WHERE v.patient_id = ?
";

$params = [$patient_id];

if (!empty($search)) {
    $sql .= " AND (v.chief_complaint LIKE ? OR v.diagnosis LIKE ?)";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql .= " ORDER BY v.visit_date DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$visits = $stmt->fetchAll();
?>

<?php include_once(BASE_PATH . '/inc/header.php'); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2 text-primary"></i>
                        My All Visits
                    </h5>
                    <a href="<?= BASE_URL ?>/patient/dashboard" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <div class="card-body">
                    <!-- Search -->
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-10">
                                <input type="text" name="search" class="form-control" 
                                       value="<?= htmlspecialchars($search) ?>" 
                                       placeholder="Search by complaint or diagnosis...">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </div>
                    </form>

                    <?php if (count($visits) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Visit Date</th>
                                    <th>Doctor</th>
                                    <th>Chief Complaint</th>
                                    <th>Diagnosis</th>
                                    <th>Next Visit</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($visits as $visit): ?>
                                <tr>
                                    <td>
                                        <strong><?= date('d-m-Y', strtotime($visit['visit_date'])) ?></strong><br>
                                        <small><?= date('h:i A', strtotime($visit['visit_date'])) ?></small>
                                    </td>
                                    <td>
                                        Dr. <?= htmlspecialchars($visit['doctor_name']) ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($visit['specialization'] ?? '') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars(substr($visit['chief_complaint'], 0, 70)) ?>...</td>
                                    <td><?= htmlspecialchars($visit['diagnosis'] ?? '-') ?></td>
                                    <td>
                                        <?= $visit['next_visit_date'] ? date('d-m-Y', strtotime($visit['next_visit_date'])) : '<span class="text-muted">Not Set</span>' ?>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/patient/visit/detail?id=<?= $visit['id'] ?>&page=visits" 
                                           class="btn btn-sm btn-primary">
                                           <i class="fas fa-eye"></i> View Detail
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>

                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-alt fa-4x text-muted mb-3"></i>
                            <h5>No visits found</h5>
                            <p class="text-muted">You haven't had any visits yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once(BASE_PATH . '/inc/footer.php'); ?>