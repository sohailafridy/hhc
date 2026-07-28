<?php
require_once '../config.php';
check_auth(); // Patient login check
$page = '';
$url = 'patient/dashboard';
$back_to = 'Back to Dashboard';
$visit_id = $_GET['id'] ?? null;
if (!$visit_id) {
    header("Location: " . BASE_URL . "/patient/dashboard.php");
    exit;
}
if (isset($_GET['page']) && $_GET['page'] =='visits') {
    $url = 'patient/visits';
    $back_to = 'Back to Visits';
}
if (isset($_GET['page']) && $_GET['page'] =='lab') {
    $url = 'patient/lab/tests';
    $back_to = 'Back to Lab Tests';
}

// Get logged in patient's user_id
$user_id = $_SESSION['user_id'] ?? null;

// Fetch Visit Details with security check (only patient's own visit)
$stmt = $pdo->prepare("
    SELECT v.*,
           p.name as patient_name, p.mr_number, p.phone, p.age, p.gender,
           d.name as doctor_name, d.specialization
    FROM visits v
    JOIN patients p ON v.patient_id = p.id
    LEFT JOIN doctors d ON v.doctor_id = d.id
    WHERE v.id = ? AND p.user_id = ?
");
$stmt->execute([$visit_id, $user_id]);
$visit = $stmt->fetch();

if (!$visit) {
    echo "<script>alert('Visit not found or access denied!'); window.location.href='" . BASE_URL . "/patient/dashboard.php';</script>";
    exit;
}

// Fetch Tests for this visit
$stmt_tests = $pdo->prepare("SELECT * FROM visit_tests WHERE visit_id = ? ORDER BY test_date DESC");
$stmt_tests->execute([$visit_id]);
$tests = $stmt_tests->fetchAll();

// Fetch Prescriptions for this visit
$stmt_meds = $pdo->prepare("SELECT * FROM visit_prescriptions WHERE visit_id = ? ORDER BY created_at DESC");
$stmt_meds->execute([$visit_id]);
$prescriptions = $stmt_meds->fetchAll();
?>

<?php include_once(BASE_PATH . '/inc/header.php'); ?>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-check me-2 text-success"></i>
                    Visit Detail - <?= date('d-m-Y', strtotime($visit['visit_date'])) ?>
                </h5>
                <a href="<?=BASE_URL?>/<?=$url?>" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left"></i> <?=$back_to?>
                </a>
            </div>

            <div class="card-body">
                <!-- Visit Information -->
                <div class="row mb-4 p-3 bg-light rounded">
                    <div class="col-md-3">
                        <strong>Visit Date:</strong><br>
                        <?= date('d-m-Y h:i A', strtotime($visit['visit_date'])) ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Doctor:</strong><br>
                        Dr. <?= htmlspecialchars($visit['doctor_name']) ?><br>
                        <small><?= htmlspecialchars($visit['specialization'] ?? '') ?></small>
                    </div>
                    <div class="col-md-3">
                        <strong>Next Visit:</strong><br>
                        <?= $visit['next_visit_date'] ? date('d-m-Y', strtotime($visit['next_visit_date'])) : 'Not Scheduled' ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Status:</strong><br>
                        <span class="badge bg-success">Completed</span>
                    </div>
                </div>

                <div class="row">
                    <!-- Left Side: Medical Notes -->
                    <div class="col-lg-12">
                        <h6 class="border-bottom pb-2">Chief Complaint</h6>
                        <p class="bg-light p-3 rounded"><?= nl2br(htmlspecialchars($visit['chief_complaint'])) ?></p>

                        <h6 class="border-bottom pb-2 mt-4">Diagnosis</h6>
                        <p class="bg-light p-3 rounded"><?= nl2br(htmlspecialchars($visit['diagnosis'] ?? 'No diagnosis recorded')) ?></p>

                        <h6 class="border-bottom pb-2 mt-4">Doctor's Notes</h6>
                        <p class="bg-light p-3 rounded"><?= nl2br(htmlspecialchars($visit['notes'] ?? 'No additional notes')) ?></p>
                    </div>
                </div>

                <hr>

                <!-- Lab Tests -->
                <h5 class="mb-3"><i class="fas fa-vial text-info"></i> Lab Tests</h5>
                <?php if (count($tests) > 0): ?>
                <div class="table-responsive mb-5">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Test Name</th>
                                <th>Result</th>
                                <th>Normal Range</th>
                                <th>Notes</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tests as $test): ?>
                            <tr>
                                <td><?= htmlspecialchars($test['test_name']) ?></td>
                                <td><strong><?= htmlspecialchars($test['result']) ?></strong></td>
                                <td><?= htmlspecialchars($test['normal_range'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($test['notes'] ?? '-') ?></td>
                                <td><?= date('d-m-Y', strtotime($test['test_date'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-muted">No lab tests were done in this visit.</p>
                <?php endif; ?>

                <!-- Prescriptions -->
                <h5 class="mb-3"><i class="fas fa-pills text-warning"></i> Prescribed Medicines</h5>
                <?php if (count($prescriptions) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Medicine Name</th>
                                <th>Dosage</th>
                                <th>Frequency</th>
                                <th>Duration</th>
                                <th>Instructions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prescriptions as $med): ?>
                            <tr>
                                <td><?= htmlspecialchars($med['medicine_name']) ?></td>
                                <td><?= htmlspecialchars($med['dosage'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($med['frequency'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($med['duration'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($med['instructions'] ?? '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-muted">No medicines were prescribed in this visit.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once(BASE_PATH . '/inc/footer.php'); ?>