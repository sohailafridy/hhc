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
        $where_clause .= " AND l.lab_name LIKE '%$search%' ";
    }
    if (!empty($city)) {
        $where_clause .= " AND l.city_id = '$city' ";
    }


// Base query
$sql = "SELECT l.*, c.city_name 
             FROM laboratories l 
             LEFT JOIN cities c ON l.city_id = c.city_id 
             WHERE l.status = 1 $where_clause
             ORDER BY l.lab_name ASC";


// Execute query
$result = mysqli_query($con, $sql);

$labs = [];
while ($row = mysqli_fetch_assoc($result)) {
    $labs[] = $row;
}

// Response
echo json_encode([
    "status" => true,
    "total" => count($labs),
    "data" => $labs
]);
