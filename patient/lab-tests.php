<?php
require_once '../config.php';
check_auth();

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

// Fetch visits with their lab tests
$sql = "
    SELECT v.id as visit_id, v.visit_date, v.diagnosis, d.name as doctor_name,
           vt.id as test_id, vt.test_name, vt.result, vt.normal_range, vt.notes
    FROM visits v
    LEFT JOIN doctors d ON v.doctor_id = d.id
    LEFT JOIN visit_tests vt ON vt.visit_id = v.id
    WHERE v.patient_id = ?
";

$params = [$patient_id];

if (!empty($search)) {
    $sql .= " AND (vt.test_name LIKE ? OR vt.result LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql .= " ORDER BY v.visit_date DESC, vt.test_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$all_data = $stmt->fetchAll();

// Group by visit
$visits_grouped = [];
foreach ($all_data as $row) {
    $vid = $row['visit_id'];
    if (!isset($visits_grouped[$vid])) {
        $visits_grouped[$vid] = [
            'visit_date' => $row['visit_date'],
            'doctor_name' => $row['doctor_name'],
            'diagnosis' => $row['diagnosis'],
            'tests' => []
        ];
    }
    if ($row['test_id']) {
        $visits_grouped[$vid]['tests'][] = [
            'test_name' => $row['test_name'],
            'result' => $row['result'],
            'normal_range' => $row['normal_range'],
            'notes' => $row['notes'],
            'visit_id' => $row['visit_id']
        ];
    }
}
?>

<?php include_once(BASE_PATH . '/inc/header.php'); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-vial me-2 text-info"></i>
                        My Lab Tests (Visit Wise)
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
                                       placeholder="Search test name or result...">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </div>
                    </form>

                    <?php if (!empty($visits_grouped)): ?>
                        <?php foreach ($visits_grouped as $visit): ?>
                            <div class="mb-5">
                                <!-- Visit Header -->
                                <div class="bg-light p-3 rounded-top border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="fs-5">Visit Date: <?= date('d-m-Y', strtotime($visit['visit_date'])) ?></strong><br>
                                            <small>Dr. <?= htmlspecialchars($visit['doctor_name'] ?? 'N/A') ?></small>
                                        </div>
                                        <?php if ($visit['diagnosis']): ?>
                                        <div class="text-end">
                                            <small class="text-muted">Diagnosis:</small><br>
                                            <strong><?= htmlspecialchars($visit['diagnosis']) ?></strong>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Lab Tests for this visit -->
                                <?php if (count($visit['tests']) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                	<th>Visit-ID</th>
                                                    <th>Test Name</th>
                                                    <th>Result</th>
                                                    <th>Normal Range</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($visit['tests'] as $test): ?>
                                                <tr>
                                                	<td><a href="<?= BASE_URL ?>/patient/visit/detail?id=<?=$test['visit_id']?>&page=lab">#ID-<?=$test['visit_id']?></a>	</td>
                                                    <td><strong><?= htmlspecialchars($test['test_name']) ?></strong></td>
                                                    <td><?= htmlspecialchars($test['result']) ?></td>
                                                    <td><?= htmlspecialchars($test['normal_range'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($test['notes'] ?? '-') ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="p-4 text-center text-muted border">
                                        No lab tests were performed in this visit.
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-vial fa-4x text-muted mb-3"></i>
                            <h5>No Lab Tests Found</h5>
                            <p class="text-muted">Your lab test records will appear here visit-wise.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once(BASE_PATH . '/inc/footer.php'); ?>