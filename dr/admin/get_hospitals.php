<?php include '../config.php';

header('Content-Type: application/json');

if (!isset($_GET['city_id']) || !is_numeric($_GET['city_id'])) {
    echo json_encode([]);
    exit;
}

$city_id = $_GET['city_id'];

$query = "SELECT hospital_id, hospital_name 
          FROM hospitals 
          WHERE city_id = $city_id AND status = 1 
          ORDER BY hospital_name";

$result = mysqli_query($con, $query);

$hospitals = [];
while ($hospital = mysqli_fetch_assoc($result)) {
    $hospitals[] = [
        'hospital_id' => $hospital['hospital_id'],
        'hospital_name' => $hospital['hospital_name']
    ];
}

echo json_encode($hospitals);
?>
