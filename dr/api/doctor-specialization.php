<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "../config.php";

$sql = "SELECT dr_cat_type_id, type FROM dr_cat_types";
$result = mysqli_query($con, $sql);

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        "id" => $row['dr_cat_type_id'],
        "type" => $row['type']
    ];
}

echo json_encode([
    "status" => true,
    "total" => count($data),
    "data" => $data
]);
