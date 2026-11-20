<?php
include_once '../config.php';

header('Content-Type: application/json');

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$offset  = isset($_POST['offset']) ? (int)$_POST['offset'] : 5;
$limit   = isset($_POST['limit']) ? (int)$_POST['limit'] : 5;

if ($user_id <= 0) {
    echo json_encode(['success' => false, 'html' => '']);
    exit();
}

$query = "SELECT f.* FROM feedback f 
          WHERE f.user_id = $user_id AND f.status = 1 
          ORDER BY f.created_at DESC 
          LIMIT $limit OFFSET $offset";
$result = mysqli_query($con, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'html' => '']);
    exit();
}

$html = '';
while ($feedback = mysqli_fetch_assoc($result)) {
    $stars_html = '';
    for ($i = 1; $i <= 5; $i++) {
        $cls = $i <= $feedback['stars'] ? 'text-warning' : 'text-muted';
        $stars_html .= '<i class="fas fa-star ' . $cls . '" style="font-size: 0.8rem;"></i>';
    }

    $html .= '<div class="feedback-item">';
    $html .= '<div class="feedback-header">';
    $html .= '<span class="feedback-name fw-bold">' . htmlspecialchars($feedback['commenter_name']) . '</span>';
    $html .= '<span class="feedback-rating">' . $stars_html . '</span>';
    $html .= '</div>';
    if (!empty($feedback['comment'])) {
        $html .= '<p class="feedback-comment mt-2">' . nl2br(htmlspecialchars($feedback['comment'])) . '</p>';
    }
    $html .= '<div class="feedback-date small text-muted mt-1">';
    $html .= '<i class="fas fa-calendar me-1"></i> ' . date('d M Y', strtotime($feedback['created_at']));
    $html .= '</div>';
    $html .= '</div>';
}

echo json_encode(['success' => true, 'html' => $html]);
?> 