<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "../config.php";

$sql = "SELECT hospitals.*,cities.city_name FROM `hospitals` 
left join cities on cities.city_id=hospitals.city_id
WHERE hospitals.status=1";
$result = mysqli_query($con, $sql);

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        "entity_id" => $row['entity_id'],
        "hospital_id" => $row['hospital_id'],
        "hospital_name" => $row['hospital_name'],
        "hospital_address" => $row['hospital_address'],
        "hospital_phone" => $row['hospital_phone'],
        "status" => $row['status']
    ];
}

echo json_encode([
    "status" => true,
    "total" => count($data),
    "data" => $data
]);
