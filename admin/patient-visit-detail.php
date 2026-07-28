<?php
require_once '../config.php';
check_auth();

$visit_id = $_GET['id'] ?? null;
if (!$visit_id) {
    header("Location: " . BASE_URL . "/admin/patient/list");
    exit;
}

// Fetch Visit Details
$stmt = $pdo->prepare("
    SELECT v.*, 
           p.name as patient_name, p.mr_number, p.phone, p.age, p.gender,
           d.name as doctor_name, d.specialization
    FROM visits v
    LEFT JOIN patients p ON v.patient_id = p.id
    LEFT JOIN doctors d ON v.doctor_id = d.id
    WHERE v.id = ?
");
$stmt->execute([$visit_id]);
$visit = $stmt->fetch();

if (!$visit) {
    echo "<script>alert('Visit not found!'); window.location.href='" . BASE_URL . "/admin/patient/list';</script>";
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

// Handle Add Test
if (isset($_POST['add_test'])) {
    $test_name = trim($_POST['test_name']);
    $result = trim($_POST['result']);
    $normal_range = trim($_POST['normal_range'] ?? '');
    $notes = trim($_POST['test_notes'] ?? '');

    $stmt = $pdo->prepare("
        INSERT INTO visit_tests (visit_id, test_name, result, normal_range, notes, test_date) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$visit_id, $test_name, $result, $normal_range, $notes]);
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $visit_id);
    exit;
}

// Handle Add Prescription
if (isset($_POST['add_prescription'])) {
    $medicine_name = trim($_POST['medicine_name']);
    $dosage = trim($_POST['dosage']);
    $frequency = trim($_POST['frequency']);
    $duration = trim($_POST['duration']);
    $instructions = trim($_POST['instructions'] ?? '');

    $stmt = $pdo->prepare("
        INSERT INTO visit_prescriptions (visit_id, medicine_name, dosage, frequency, duration, instructions) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$visit_id, $medicine_name, $dosage, $frequency, $duration, $instructions]);
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $visit_id);
    exit;
}
?>

<?php include_once(BASE_PATH . '/inc/header.php'); ?>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-check me-2 text-success"></i>
                    Visit Detail - <?= htmlspecialchars($visit['patient_name']) ?> 
                    <small class="text-muted">(MR: <?= $visit['mr_number'] ?>)</small>
                </h5>
                <a href="<?=BASE_URL?>/admin/patient/detail?id=<?= $visit['patient_id'] ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Patient
                </a>
            </div>

            <div class="card-body">
                <!-- Visit Information -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <strong>Visit Date:</strong><br>
                        <?= date('d-m-Y h:i A', strtotime($visit['visit_date'])) ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Doctor:</strong><br>
                        <?= htmlspecialchars($visit['doctor_name']) ?> 
                        <small>(<?= htmlspecialchars($visit['specialization'] ?? '') ?>)</small>
                    </div>
                    <div class="col-md-3">
                        <strong>Next Visit:</strong><br>
                        <?= $visit['next_visit_date'] ? date('d-m-Y', strtotime($visit['next_visit_date'])) : 'Not Set' ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Status:</strong><br>
                        <span class="badge bg-success">Completed</span>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <!-- Left Side: Chief Complaint & Diagnosis -->
                    <div class="col-lg-5">
                        <h6 class="border-bottom pb-2">Chief Complaint</h6>
                        <p class="bg-light p-3 rounded"><?= nl2br(htmlspecialchars($visit['chief_complaint'])) ?></p>

                        <h6 class="border-bottom pb-2 mt-4">Diagnosis</h6>
                        <p class="bg-light p-3 rounded"><?= nl2br(htmlspecialchars($visit['diagnosis'] ?? 'No diagnosis recorded')) ?></p>

                        <h6 class="border-bottom pb-2 mt-4">Notes / Observations</h6>
                        <p class="bg-light p-3 rounded"><?= nl2br(htmlspecialchars($visit['notes'] ?? 'No notes')) ?></p>
                    </div>

                    <!-- Right Side: Add Test & Add Medicine -->
                    <div class="col-lg-7">
                        
                        <!-- Add Test Form -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <strong><i class="fas fa-vial"></i> Add Lab Test</strong>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="add_test" value="1">
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <input type="text" name="test_name" class="form-control" placeholder="Test Name (e.g. CBC, LFT)" required>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" name="result" class="form-control" placeholder="Result" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="normal_range" class="form-control" placeholder="Normal Range">
                                        </div>
                                        <div class="col-12">
                                            <textarea name="test_notes" class="form-control" rows="2" placeholder="Test notes (optional)"></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Add Test
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Add Prescription Form -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <strong><i class="fas fa-pills"></i> Add Medicine</strong>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="add_prescription" value="1">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <input type="text" name="medicine_name" class="form-control" placeholder="Medicine Name" required>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" name="dosage" class="form-control" placeholder="Dosage">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" name="frequency" class="form-control" placeholder="Frequency">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" name="duration" class="form-control" placeholder="Duration">
                                        </div>
                                        <div class="col-12">
                                            <textarea name="instructions" class="form-control" rows="2" placeholder="Special instructions"></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-plus"></i> Add Medicine
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Tests List -->
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
                    <p class="text-muted">No tests recorded for this visit.</p>
                <?php endif; ?>

                <!-- Prescriptions List -->
                <h5 class="mb-3"><i class="fas fa-pills text-warning"></i> Prescriptions</h5>
                <?php if (count($prescriptions) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Medicine</th>
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
                    <p class="text-muted">No medicines prescribed in this visit.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once(BASE_PATH . '/inc/footer.php'); ?>