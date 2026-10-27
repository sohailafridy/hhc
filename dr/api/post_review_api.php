<?php
// post_review.php
require_once __DIR__ . '/../config.php';

// POST data receive karna (App JSON bhej rahi hai)
$input = json_decode(file_get_contents('php://input'), true);

$doctor_id = isset($input['doctor_id']) ? (int)$input['doctor_id'] : 0;
$user_id   = isset($input['user_id']) ? (int)$input['user_id'] : 0; // Agar app login hai
$user_name = isset($input['user_name']) ? trim($input['user_name']) : '';
$user_email= isset($input['user_email']) ? trim($input['user_email']) : '';
$rating    = isset($input['rating']) ? (int)$input['rating'] : 5;
$comment   = isset($input['comment']) ? trim($input['comment']) : '';

// Validation
if ($doctor_id == 0 || $rating < 1 || $rating > 5) {
    echo json_encode(["status" => false, "message" => "Doctor ID and valid Rating (1-5) required"]);
    exit;
}

try {
    // Agar user_id nahi hai (Guest user), toh pehle users table mein check karein ya insert karein
    if ($user_id == 0 && !empty($user_email)) {
        $check_user = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_user->execute([$user_email]);
        $existing_user = $check_user->fetch();
        
        if ($existing_user) {
            $user_id = $existing_user['user_id'];
        } else {
            // Naya guest user create karein (Optional: aap isko skip bhi kar sakte hain)
            $insert_user = $pdo->prepare("INSERT INTO users (username, email, user_type_id, created_at) VALUES (?, ?, 3, NOW())");
            $insert_user->execute([$user_name, $user_email]);
            $user_id = $pdo->lastInsertId();
        }
    }

    // Review Insert karein
    $sql = "INSERT INTO `feedback` (`entity_id`, `user_id`, `comment`, `stars`, `status`, `created_at`) 
            VALUES (?, ?, ?, ?, 1, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$doctor_id, $user_id, $comment, $rating]);

    echo json_encode([
        "status" => true, 
        "message" => "Review submitted successfully!",
        "review_id" => $pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => false, "message" => "Error: " . $e->getMessage()]);
}
?>