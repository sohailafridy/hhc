<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "../config.php";

// GET parameters
$search         = $_GET['search'] ?? '';
$city           = $_GET['city'] ?? '';
$hospital       = $_GET['hospital'] ?? '';
$specialization = $_GET['specialization'] ?? '';
$doctor_type    = $_GET['doctor_type'] ?? '';
$experience     = $_GET['experience'] ?? '';
$lady_doctor    = $_GET['lady_doctor'] ?? '';

// Base query
$sql = "SELECT 
            d.doctor_id,
            d.doctor_name,
            d.gender,
            d.experience_years,
            c.city_name,
            h.hospital_name,
            dct.type
        FROM doctors d
        LEFT JOIN cities c ON c.city_id = d.city_id
        LEFT JOIN hospitals h ON h.hospital_id = d.hospital_id
        LEFT JOIN dr_cat_types dct ON dct.dr_cat_type_id = d.cat_type_id
        WHERE 1=1";

// Dynamic filters
if ($search != '') {
    $sql .= " AND d.doctor_name LIKE '%$search%'";
}

if ($city != '') {
    $sql .= " AND d.city_id = '$city'";
}

if ($hospital != '') {
    $sql .= " AND d.hospital_id = '$hospital'";
}

if ($specialization != '' && $specialization != 0) {
    $sql .= " AND d.cat_type_id = '$specialization'";
}



if ($experience != '') {
    $sql .= " AND d.experience_years >= '$experience'";
}

if ($lady_doctor == 1) {
    $sql .= " AND d.gender = 'female'";
}

$sql .= " ORDER BY d.doctor_name ASC";




// Execute query
$result = mysqli_query($con, $sql);

$doctors = [];
while ($row = mysqli_fetch_assoc($result)) {
    $doctors[] = $row;
}

// Response
echo json_encode([
    "status" => true,
    "total" => count($doctors),
    "data" => $doctors
]);
