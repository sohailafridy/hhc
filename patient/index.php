<?php
require_once '../config.php';
check_auth(); // Assume this sets $_SESSION['user_id'] and role

// Get logged in patient's user_id
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

// Fetch Patient Info using user_id
$stmt = $pdo->prepare("
    SELECT p.* FROM patients p 
    WHERE p.user_id = ? 
    LIMIT 1
");
$stmt->execute([$user_id]);
$patient = $stmt->fetch();

if (!$patient) {
    echo "<script>alert('Patient record not found!'); window.location.href = '" . BASE_URL . "/logout.php';</script>";
    exit;
}

$patient_id = $patient['id'];

// Fetch Recent Visits (Last 5)
$stmt_visits = $pdo->prepare("
    SELECT v.*, d.name as doctor_name 
    FROM visits v 
    LEFT JOIN doctors d ON v.doctor_id = d.id 
    WHERE v.patient_id = ? 
    ORDER BY v.visit_date DESC LIMIT 5
");
$stmt_visits->execute([$patient_id]);
$recent_visits = $stmt_visits->fetchAll();

// Fetch Recent Lab Tests
$stmt_tests = $pdo->prepare("
    SELECT vt.*, v.visit_date 
    FROM visit_tests vt 
    JOIN visits v ON vt.visit_id = v.id 
    WHERE v.patient_id = ? 
    ORDER BY vt.test_date DESC LIMIT 6
");
$stmt_tests->execute([$patient_id]);
$recent_tests = $stmt_tests->fetchAll();

// Fetch Recent Prescriptions
$stmt_meds = $pdo->prepare("
    SELECT vp.*, v.visit_date 
    FROM visit_prescriptions vp 
    JOIN visits v ON vp.visit_id = v.id 
    WHERE v.patient_id = ? 
    ORDER BY v.visit_date DESC LIMIT 5
");
$stmt_meds->execute([$patient_id]);
$recent_meds = $stmt_meds->fetchAll();
?>

<?php include_once(BASE_PATH . '/inc/header.php'); ?>

<div class="container-fluid">
    <div class="row">
        <!-- Welcome Header -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">Welcome, <?= htmlspecialchars($patient['name']) ?>!</h3>
                            <p class="mb-0">MR Number: <strong><?= htmlspecialchars($patient['mr_number']) ?></strong></p>
                        </div>
                        <?php if (!empty($patient['img'])): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($patient['img']) ?>" class="rounded-circle" width="80" height="80" style="object-fit: cover;">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="row g-4">

                <!-- Quick Stats -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
                            <h4><?= count($recent_visits) ?></h4>
                            <p class="text-muted mb-0">Recent Visits</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-vial fa-3x text-info mb-3"></i>
                            <h4><?= count($recent_tests) ?></h4>
                            <p class="text-muted mb-0">Lab Tests</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-pills fa-3x text-warning mb-3"></i>
                            <h4><?= count($recent_meds) ?></h4>
                            <p class="text-muted mb-0">Medicines</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Visits -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between">
                            <h5><i class="fas fa-history"></i> Recent Visits</h5>
                            <a href="<?= BASE_URL ?>/patient/visits" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (count($recent_visits) > 0): ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($recent_visits as $visit): ?>
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?= date('d M, Y', strtotime($visit['visit_date'])) ?></strong><br>
                                                <small class="text-muted">Dr. <?= htmlspecialchars($visit['doctor_name']) ?></small>
                                            </div>
                                            <a href="<?= BASE_URL ?>/patient/visit/detail?id=<?= $visit['id'] ?>" class="btn btn-sm btn-primary">View</a>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No visits yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Lab Tests -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between">
                            <h5><i class="fas fa-vial"></i> Recent Lab Tests</h5>
                            <a href="<?= BASE_URL ?>/patient/lab/tests" class="btn btn-sm btn-outline-info">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (count($recent_tests) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Test</th>
                                                <th>Result</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_tests as $test): ?>
                                            <tr>
                                                <td><?= date('d-m-Y', strtotime($test['visit_date'])) ?></td>
                                                <td><?= htmlspecialchars($test['test_name']) ?></td>
                                                <td><strong><?= htmlspecialchars($test['result']) ?></strong></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No lab tests recorded yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Prescriptions -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between">
                            <h5><i class="fas fa-pills"></i> Recent Prescriptions</h5>
                            <a href="<?= BASE_URL ?>/patient/medicine/detail" class="btn btn-sm btn-outline-info">View All</a>
                        </div>



                        <div class="card-body">
                            <?php if (count($recent_meds) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Medicine</th>
                                                <th>Dosage</th>
                                                <th>Frequency</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_meds as $med): ?>
                                            <tr>
                                                <td><?= date('d-m-Y', strtotime($med['visit_date'])) ?></td>
                                                <td><?= htmlspecialchars($med['medicine_name']) ?></td>
                                                <td><?= htmlspecialchars($med['dosage'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($med['frequency'] ?? '-') ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No prescriptions found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once(BASE_PATH . '/inc/footer.php'); ?>