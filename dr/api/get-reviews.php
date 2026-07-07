<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include '../config.php';

try {
    $conn = new PDO("mysql:host={$db_host};dbname={$db_name}", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $fixit_id = isset($_GET['fixit_id']) ? (int)$_GET['fixit_id'] : 1;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 5;
    $offset = ($page - 1) * $limit;
    
    // Get total count
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM feedback WHERE fixit_id = ? AND status = 'approved'");
    $countStmt->execute([$fixit_id]);
    $total = $countStmt->fetchColumn();
    
    // Get reviews
    $stmt = $conn->prepare("
        SELECT f.commenter_name, f.commenter_gmail, f.comment, f.stars, f.created_at 
        FROM feedback f 
        WHERE f.fixit_id = ? AND f.status = 'approved' 
        ORDER BY f.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$fixit_id, $limit, $offset]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasMore = $total > ($page * $limit);
    
    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'hasMore' => $hasMore,
        'total' => (int)$total,
        'page' => $page
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
