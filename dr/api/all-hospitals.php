<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "../config.php";

$sql = "SELECT `hospital_id`,`hospital_name` FROM `hospitals` WHERE `status`=1";
$result = mysqli_query($con, $sql);

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        "id" => $row['hospital_id'],
        "hospital_name" => $row['hospital_name']
    ];
}

echo json_encode([
    "status" => true,
    "total" => count($data),
    "data" => $data
]);
