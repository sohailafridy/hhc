<?php
require_once '../config.php';
check_auth();

$patient_id = $_GET['patient_id'] ?? null;
if (!$patient_id) {
    header("Location: ".BASE_URL."/admin/patient/list");
    exit;
}

// Get patient info
$stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $doctor_id = $_POST['doctor_id'];
    $chief_complaint = trim($_POST['chief_complaint']);
    $diagnosis = trim($_POST['diagnosis']);
    $notes = trim($_POST['notes']);
    $next_visit_date = $_POST['next_visit_date'] ?? null;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO visits (patient_id, doctor_id, visit_date, chief_complaint, diagnosis, notes, next_visit_date) 
            VALUES (?, ?, NOW(), ?, ?, ?, ?)
        ");
        $stmt->execute([$patient_id, $doctor_id, $chief_complaint, $diagnosis, $notes, $next_visit_date]);

        $visit_id = $pdo->lastInsertId();
        
        echo '<script>alert("Visit created successfully!"); 
              window.location.href = "'.BASE_URL.'/admin/patient/visit/detail?id='.$visit_id.'";</script>';
    } catch(Exception $e) {
        $message = "Error: ".$e->getMessage();
    }
}
include_once(BASE_PATH . '/inc/header.php');
?>

<!-- Form HTML -->
<div class="card">
    <div class="card-header">
        <h5>New Visit - <?= htmlspecialchars($patient['name']) ?> (MR: <?= $patient['mr_number'] ?>)</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <label>Doctor <span class="text-danger">*</span></label>
                    <select name="doctor_id" class="form-select" required>
                        <?php
                        $doctors = $pdo->query("SELECT id, name, specialization FROM doctors WHERE status=1")->fetchAll();
                        foreach($doctors as $d){
                            echo "<option value='{$d['id']}'>{$d['name']} - {$d['specialization']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Next Visit Date</label>
                    <input type="date" name="next_visit_date" class="form-control">
                </div>

                <div class="col-12 mt-3">
                    <label>Chief Complaint / Reason for Visit <span class="text-danger">*</span></label>
                    <textarea name="chief_complaint" class="form-control" rows="3" required></textarea>
                </div>

                <div class="col-12 mt-3">
                    <label>Diagnosis</label>
                    <textarea name="diagnosis" class="form-control" rows="2"></textarea>
                </div>

                <div class="col-12 mt-3">
                    <label>Notes / Observations</label>
                    <textarea name="notes" class="form-control" rows="4"></textarea>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-success">Save Visit</button>
                </div>
            </div>
        </form>
    </div>
</div>