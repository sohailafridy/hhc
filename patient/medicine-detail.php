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

// Search
$search = trim($_GET['search'] ?? '');

// Fetch visits with their medicines (grouped)
$sql = "
    SELECT v.id as visit_id, v.visit_date, v.diagnosis, d.name as doctor_name,
           vp.id as pres_id, vp.medicine_name, vp.dosage, vp.frequency, 
           vp.duration, vp.instructions
    FROM visits v
    LEFT JOIN doctors d ON v.doctor_id = d.id
    LEFT JOIN visit_prescriptions vp ON vp.visit_id = v.id
    WHERE v.patient_id = ?
";

$params = [$patient_id];

if (!empty($search)) {
    $sql .= " AND (vp.medicine_name LIKE ? OR v.diagnosis LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql .= " ORDER BY v.visit_date DESC, vp.created_at ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$all_data = $stmt->fetchAll();

// Group data by visit
$visits_grouped = [];
foreach ($all_data as $row) {
    $vid = $row['visit_id'];
    if (!isset($visits_grouped[$vid])) {
        $visits_grouped[$vid] = [
            'visit_date' => $row['visit_date'],
            'doctor_name' => $row['doctor_name'],
            'diagnosis' => $row['diagnosis'],
            'medicines' => []
        ];
    }
    if ($row['pres_id']) {  // Only add if medicine exists
        $visits_grouped[$vid]['medicines'][] = [
            'medicine_name' => $row['medicine_name'],
            'dosage' => $row['dosage'],
            'frequency' => $row['frequency'],
            'duration' => $row['duration'],
            'instructions' => $row['instructions']
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
                        <i class="fas fa-pills me-2 text-warning"></i>
                        My Prescriptions
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
                                       placeholder="Search medicine or diagnosis...">
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

                                <!-- Medicines for this visit -->
                                <?php if (count($visit['medicines']) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0">
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
                                                <?php foreach ($visit['medicines'] as $med): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($med['medicine_name']) ?></strong></td>
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
                                    <div class="p-4 text-center text-muted border">
                                        No medicines prescribed in this visit.
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-pills fa-4x text-muted mb-3"></i>
                            <h5>No Prescriptions Found</h5>
                            <p class="text-muted">Your medicine records will appear here visit-wise.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once(BASE_PATH . '/inc/footer.php'); ?>