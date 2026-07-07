<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "../config.php";
$kohat_city_id = 7;
// GET parameters
   // Handle search and filters
    $search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
    $city = isset($_GET['city']) ? (int)$_GET['city'] : $kohat_city_id; // Default to Kohat if not set


$where_clause = '';
    if (!empty($search)) {
        $where_clause .= " AND bb.bb_name LIKE '%$search%' ";
    }
    if (!empty($city)) {
        $where_clause .= " AND bb.city_id = '$city' ";
    }


// Base query
$sql = "SELECT bb.*, c.city_name 
             FROM blood_bank bb 
             LEFT JOIN cities c ON bb.city_id = c.city_id 
             WHERE bb.status = 1 $where_clause
             ORDER BY bb.bb_name ASC";


// Execute query
$result = mysqli_query($con, $sql);

$bb = [];
while ($row = mysqli_fetch_assoc($result)) {
    $bb[] = $row;
}

// Response
echo json_encode([
    "status" => true,
    "total" => count($bb),
    "data" => $bb
]);
