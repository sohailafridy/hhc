<?php
include '../config.php';

// ============================================
// HOSPITAL AUTHENTICATION
// ============================================
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'hospital') {
    header("Location: " . BASE_URL . "login");
    exit();
}

$user_id = $_SESSION['user_id'];

$hospital_query = "SELECT * FROM hospitals WHERE user_id = $user_id AND approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital_data = mysqli_fetch_assoc($hospital_result);

if (!$hospital_data) {
    session_destroy();
    header("Location: " . BASE_URL . "login");
    exit();
}

$hospital_id = $hospital_data['hospital_id'];
$entity_id = $hospital_data['entity_id'];

// ============================================
// GET FEEDBACK
// ============================================
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$count_query = "SELECT COUNT(*) as total FROM feedback WHERE entity_id = $entity_id AND status = 1";
$count_result = mysqli_query($con, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $per_page);

$query = "SELECT * FROM feedback 
          WHERE entity_id = $entity_id AND status = 1
          ORDER BY created_at DESC
          LIMIT $offset, $per_page";
$result = mysqli_query($con, $query);

// Rating stats
$rating_query = "SELECT AVG(stars) as avg_rating, COUNT(*) as total 
                 FROM feedback WHERE entity_id = $entity_id AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$total_reviews = $rating_data['total'] ?? 0;

// Rating distribution
$rating_distribution = [];
for ($i = 1; $i <= 5; $i++) {
    $dist_query = "SELECT COUNT(*) as count FROM feedback 
                   WHERE entity_id = $entity_id AND status = 1 AND stars = $i";
    $dist_result = mysqli_query($con, $dist_query);
    $dist_data = mysqli_fetch_assoc($dist_result);
    $rating_distribution[$i] = $dist_data['count'];
}
?>

<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/hospital/inc/nav.php'; ?>

<style>
.content-wrapper {
    background: #f8fafc;
    min-height: 100vh;
    padding: 24px 32px 60px;
}

.page-header {
    margin-bottom: 28px;
}

.page-header h4 {
    font-size: 1.6rem;
    font-weight: 700;
    color: #1e293b;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 16px 20px;
    border: 1px solid #e2e8f0;
    text-align: center;
}

.stat-card .number {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
}

.stat-card .label {
    font-size: 0.8rem;
    color: #64748b;
}

.table-responsive {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.table th {
    background: #f8fafc;
    font-weight: 700;
    font-size: 0.8rem;
    text-transform: uppercase;
    color: #64748b;
    padding: 12px 16px;
}

.table td {
    padding: 10px 16px;
    vertical-align: middle;
}

.star-rating {
    color: #f59e0b;
    font-size: 0.85rem;
}

.pagination-container {
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    border-top: 1px solid #e2e8f0;
}
</style>

<div class="content-wrapper">

    <div class="page-header">
        <h4><i class="fas fa-star me-2"></i> Patient Reviews</h4>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="number"><?php echo $avg_rating > 0 ? $avg_rating : 'N/A'; ?></div>
            <div class="label">Average Rating</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $total_reviews; ?></div>
            <div class="label">Total Reviews</div>
        </div>
        <div class="stat-card">
            <div class="number">
                <?php 
                $five_star = $rating_distribution[5] ?? 0;
                $total = $total_reviews > 0 ? $total_reviews : 1;
                echo round(($five_star / $total) * 100) . '%';
                ?>
            </div>
            <div class="label">5-Star Reviews</div>
        </div>
        <div class="stat-card">
            <div class="number">
                <?php 
                $one_star = $rating_distribution[1] ?? 0;
                $total = $total_reviews > 0 ? $total_reviews : 1;
                echo round(($one_star / $total) * 100) . '%';
                ?>
            </div>
            <div class="label">1-Star Reviews</div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $serial = $offset + 1; ?>
                    <?php while ($review = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $serial++; ?></td>
                            <td><strong><?php echo htmlspecialchars($review['commenter_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($review['commenter_gmail']); ?></small>
                            </td>
                            <td>
                                <div class="star-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $review['stars'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td><?php echo nl2br(htmlspecialchars($review['comment'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($review['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No reviews yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <nav>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>">Prev</a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div>
                <small class="text-muted">Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $per_page, $total_records); ?> of <?php echo $total_records; ?></small>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>