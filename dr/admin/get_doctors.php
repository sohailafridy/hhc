<?php include '../config.php';

header('Content-Type: application/json');

if (!isset($_GET['hospital_id']) || !is_numeric($_GET['hospital_id'])) {
    echo json_encode([]);
    exit;
}

$hospital_id = $_GET['hospital_id'];

$query = "SELECT d.doctor_id, d.doctor_name, 
                COALESCE(dc.cat_name, 'General') as specialization
          FROM doctors d
          LEFT JOIN dr_cat_types dct ON dct.dr_cat_type_id = d.cat_type_id
          LEFT JOIN dr_categories dc ON dc.dr_cat_id = dct.dr_cat_id
          WHERE d.hospital_id = $hospital_id AND d.status = 1 
          ORDER BY d.doctor_name";

$result = mysqli_query($con, $query);

$doctors = [];
while ($doctor = mysqli_fetch_assoc($result)) {
    $doctors[] = [
        'doctor_id' => $doctor['doctor_id'],
        'doctor_name' => $doctor['doctor_name'],
        'specialization' => $doctor['specialization']
    ];
}

echo json_encode($doctors);
?>
