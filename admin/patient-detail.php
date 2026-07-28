<?php
require_once '../config.php';
check_auth();

$patient_id = $_GET['id'] ?? null;
if (!$patient_id) {
    header("Location: " . BASE_URL . "/admin/patient/list");
    exit;
}

// Fetch Patient Basic Info
$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.email as user_email 
    FROM patients p 
    LEFT JOIN users u ON p.user_id = u.id 
    WHERE p.id = ?
");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

if (!$patient) {
    echo '<script>alert("Patient not found!"); window.location.href="' . BASE_URL . '/admin/patient/list";</script>';
    exit;
}

// Fetch Visits History
$stmt_visits = $pdo->prepare("
    SELECT v.*, d.name as doctor_name, d.specialization 
    FROM visits v 
    LEFT JOIN doctors d ON v.doctor_id = d.id 
    WHERE v.patient_id = ? 
    ORDER BY v.visit_date DESC
");
$stmt_visits->execute([$patient_id]);
$visits = $stmt_visits->fetchAll();

// Fetch All Lab Tests (across all visits)
$stmt_tests = $pdo->prepare("
    SELECT vt.*, v.visit_date 
    FROM visit_tests vt 
    JOIN visits v ON vt.visit_id = v.id 
    WHERE v.patient_id = ? 
    ORDER BY vt.test_date DESC
");
$stmt_tests->execute([$patient_id]);
$all_tests = $stmt_tests->fetchAll();

// Fetch All Prescriptions
$stmt_meds = $pdo->prepare("
    SELECT vp.*, v.visit_date 
    FROM visit_prescriptions vp 
    JOIN visits v ON vp.visit_id = v.id 
    WHERE v.patient_id = ? 
    ORDER BY v.visit_date DESC
");
$stmt_meds->execute([$patient_id]);
$all_meds = $stmt_meds->fetchAll();

// Fetch Chronic Diseases
$stmt_diseases = $pdo->prepare("SELECT * FROM patient_diseases WHERE patient_id = ? ORDER BY diagnosed_date DESC");
$stmt_diseases->execute([$patient_id]);
$diseases = $stmt_diseases->fetchAll();
?>

<?php include_once(BASE_PATH . '/inc/header.php'); ?>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-user-circle me-2 text-primary"></i>
                    Patient Profile - <?= htmlspecialchars($patient['name']) ?>
                </h5>
                <div>
                    <a href="<?=BASE_URL?>/admin/patient/edit?id=<?= $patient_id ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="<?=BASE_URL?>/admin/patient/visit/add?patient_id=<?= $patient_id ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> New Visit
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Patient Basic Info -->
                <div class="row mb-5">
                    <div class="col-md-3 text-center">
                        <?php if (!empty($patient['img'])): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($patient['img']) ?>" class="img-fluid rounded-circle mb-3" style="width: 160px; height: 160px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 160px; height: 160px; font-size: 4rem;">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <h5 class="text-success"><?= htmlspecialchars($patient['mr_number']) ?></h5>
                    </div>

                    <div class="col-md-9">
                        <div class="row g-3">
                            <div class="col-md-6"><strong>Name:</strong> <?= htmlspecialchars($patient['name']) ?></div>
                            <div class="col-md-6"><strong>Father Name:</strong> <?= htmlspecialchars($patient['father_name'] ?? '-') ?></div>
                            <div class="col-md-4"><strong>Age:</strong> <?= $patient['age'] ?> years</div>
                            <div class="col-md-4"><strong>Gender:</strong> <?= $patient['gender'] ?></div>
                            <div class="col-md-4"><strong>Blood Group:</strong> <?= htmlspecialchars($patient['blood_group'] ?? '-') ?></div>
                            <div class="col-md-6"><strong>Phone:</strong> <?= htmlspecialchars($patient['phone']) ?></div>
                            <div class="col-md-6"><strong>CNIC:</strong> <?= htmlspecialchars($patient['cnic'] ?? '-') ?></div>
                            <div class="col-12"><strong>Address:</strong> <?= htmlspecialchars($patient['address'] ?? '-') ?></div>
                            <div class="col-md-6"><strong>Allergies:</strong> <?= nl2br(htmlspecialchars($patient['allergies'] ?? 'None')) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" id="patientTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" id="visits-tab" data-bs-toggle="tab" href="#visits">Visits (<?= count($visits) ?>)</a></li>
                    <li class="nav-item"><a class="nav-link" id="tests-tab" data-bs-toggle="tab" href="#tests">Lab Tests (<?= count($all_tests) ?>)</a></li>
                    <li class="nav-item"><a class="nav-link" id="meds-tab" data-bs-toggle="tab" href="#meds">Prescriptions (<?= count($all_meds) ?>)</a></li>
                    <li class="nav-item"><a class="nav-link" id="diseases-tab" data-bs-toggle="tab" href="#diseases">Chronic Diseases (<?= count($diseases) ?>)</a></li>
                </ul>

                <div class="tab-content">
                    <!-- Visits Tab -->
                    <div class="tab-pane fade show active" id="visits">
                        <?php if (count($visits) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Doctor</th>
                                            <th>Chief Complaint</th>
                                            <th>Diagnosis</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($visits as $v): ?>
                                        <tr>
                                            <td><?= date('d-m-Y', strtotime($v['visit_date'])) ?></td>
                                            <td><?= htmlspecialchars($v['doctor_name']) ?></td>
                                            <td><?= htmlspecialchars(substr($v['chief_complaint'], 0, 80)) ?>...</td>
                                            <td><?= htmlspecialchars($v['diagnosis'] ?? '-') ?></td>
                                            <td>
                                                <a href="<?=BASE_URL?>/admin/patient/visit/detail?id=<?= $v['id'] ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No visits recorded yet.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Lab Tests Tab -->
                    <div class="tab-pane fade" id="tests">
                        <?php if (count($all_tests) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Test Name</th>
                                            <th>Result</th>
                                            <th>Normal Range</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_tests as $t): ?>
                                        <tr>
                                            <td><?= date('d-m-Y', strtotime($t['test_date'])) ?></td>
                                            <td><?= htmlspecialchars($t['test_name']) ?></td>
                                            <td><strong><?= htmlspecialchars($t['result']) ?></strong></td>
                                            <td><?= htmlspecialchars($t['normal_range'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($t['notes'] ?? '-') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No lab tests recorded.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Prescriptions Tab -->
                    <div class="tab-pane fade" id="meds">
                        <?php if (count($all_meds) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Medicine</th>
                                            <th>Dosage</th>
                                            <th>Frequency</th>
                                            <th>Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_meds as $m): ?>
                                        <tr>
                                            <td><?= date('d-m-Y', strtotime($m['visit_date'])) ?></td>
                                            <td><?= htmlspecialchars($m['medicine_name']) ?></td>
                                            <td><?= htmlspecialchars($m['dosage'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($m['frequency'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($m['duration'] ?? '-') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No prescriptions found.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Diseases Tab -->
                    <div class="tab-pane fade" id="diseases">
                        <?php if (count($diseases) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Disease</th>
                                            <th>Status</th>
                                            <th>Diagnosed Date</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($diseases as $d): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($d['disease_name']) ?></td>
                                            <td><span class="badge bg-<?= $d['status']=='Chronic' ? 'warning' : 'info' ?>"><?= $d['status'] ?></span></td>
                                            <td><?= $d['diagnosed_date'] ? date('d-m-Y', strtotime($d['diagnosed_date'])) : '-' ?></td>
                                            <td><?= htmlspecialchars($d['notes'] ?? '-') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No chronic diseases recorded.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include_once(BASE_PATH . '/inc/footer.php'); ?>