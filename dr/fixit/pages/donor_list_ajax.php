<?php
include '../config.php';

header('Content-Type: application/json');

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// Get filter parameters
$nameFilter = isset($_GET['name']) ? mysqli_real_escape_string($con, $_GET['name']) : '';
$mobileFilter = isset($_GET['mobile']) ? mysqli_real_escape_string($con, $_GET['mobile']) : '';
$cnicFilter = isset($_GET['cnic']) ? mysqli_real_escape_string($con, $_GET['cnic']) : '';
$teamFilter = isset($_GET['team_id']) ? (int)$_GET['team_id'] : '';

// Get current user's team_id from session
$team_id = isset($_SESSION['team_id']) ? (int)$_SESSION['team_id'] : 0;

// Build WHERE clause
$whereConditions = [];
$params = [];

// Always filter by current user's team_id (unless admin)
if ($team_id != 0) {
    $whereConditions[] = "team_id = ?";
    $params[] = $team_id;
}

if (!empty($nameFilter)) {
    $whereConditions[] = "donor_name LIKE ?";
    $params[] = "%$nameFilter%";
}

if (!empty($mobileFilter)) {
    $whereConditions[] = "mobile LIKE ?";
    $params[] = "%$mobileFilter%";
}

if (!empty($cnicFilter)) {
    $whereConditions[] = "cnic LIKE ?";
    $params[] = "%$cnicFilter%";
}

if (!empty($teamFilter) && $team_id == 0) {
    // Only allow team filter if user is admin
    $whereConditions[] = "team_id = ?";
    $params[] = $teamFilter;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Simple direct query from f_donors table
$countQuery = "SELECT COUNT(*) as total FROM f_donors $whereClause";

$countStmt = mysqli_prepare($con, $countQuery);
if (!empty($params)) {
    mysqli_stmt_bind_param($countStmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($countStmt);
$totalResult = mysqli_stmt_get_result($countStmt);
$totalRecords = mysqli_fetch_assoc($totalResult)['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get donors data - direct query from f_donors
$query = "SELECT * FROM f_donors $whereClause ORDER BY created_at DESC LIMIT $offset, $recordsPerPage";

$stmt = mysqli_prepare($con, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$donors = [];
while ($row = mysqli_fetch_assoc($result)) {
    $donors[] = $row;
}

// Handle export to CSV
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="donors_export.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV header
    fputcsv($output, ['ID', 'Team ID', 'Donor Name', 'Address', 'Mobile', 'CNIC', 'Created Date', 'Updated Date']);
    
    // CSV data
    foreach ($donors as $donor) {
        fputcsv($output, [
            $donor['f_donor_id'],
            $donor['team_id'],
            $donor['donor_name'],
            $donor['donor_address'],
            $donor['mobile'],
            $donor['cnic'],
            $donor['created_at'],
            $donor['updated_at']
        ]);
    }
    
    fclose($output);
    exit;
}

// Prepare pagination data
$pagination = [
    'current_page' => $page,
    'total_pages' => $totalPages,
    'total_records' => $totalRecords,
    'records_per_page' => $recordsPerPage,
    'has_previous' => $page > 1,
    'has_next' => $page < $totalPages
];

// Return JSON response
echo json_encode([
    'success' => true,
    'donors' => $donors,
    'pagination' => $pagination
]);
?>
