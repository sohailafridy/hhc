<?php 
include 'includes/header.php'; // Path adjust karein agar zaroorat ho

header('Content-Type: application/json');

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = 5;

if ($user_id > 0) {
    $query = "SELECT * FROM feedback 
              WHERE user_id = $user_id AND status = 1 
              ORDER BY feedback_id DESC 
              LIMIT $limit OFFSET $offset";
              
    $result = mysqli_query($con, $query);
    $total_q = mysqli_query($con, "SELECT COUNT(*) as total FROM feedback WHERE user_id = $user_id AND status = 1");
    $total = mysqli_fetch_assoc($total_q)['total'];

    $html = '';
    if (mysqli_num_rows($result) > 0) {
        while ($review = mysqli_fetch_assoc($result)) {
            $stars_html = '';
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $review['stars']) {
                    $stars_html .= '<i class="fas fa-star"></i>';
                } else {
                    $stars_html .= '<i class="far fa-star"></i>';
                }
            }
            
            $name = htmlspecialchars($review['commenter_name']);
            $comment = htmlspecialchars($review['comment']);
            $date = date('M d, Y', strtotime($review['created_at']));

            $html .= '
            <div class="review-card">
                <div class="review-header">
                    <div class="reviewer-info">
                        <div class="reviewer-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="reviewer-details">
                            <h4 class="reviewer-name">' . $name . '</h4>
                            <div class="review-date">
                                <i class="fas fa-calendar-alt me-1"></i> ' . $date . '
                            </div>
                        </div>
                    </div>
                    <div class="review-rating">
                        ' . $stars_html . '
                    </div>
                </div>
                <div class="review-content">
                    <p>' . $comment . '</p>
                </div>
            </div>';
        }
    }

    $has_more = ($offset + $limit) < $total;

    echo json_encode([
        'success' => true,
        'html' => $html,
        'has_more' => $has_more
    ]);
    exit;
}

echo json_encode(['success' => false, 'html' => '', 'has_more' => false]);
exit;
?>