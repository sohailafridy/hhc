<?php
// ============================================
// START SESSION & INCLUDE CONFIG
// ============================================

include '../config.php';

// ============================================
// HOSPITAL AUTHENTICATION CHECK
// ============================================
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'hospital') {
    header("Location: " . BASE_URL . "login");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Get hospital data
$hospital_query = "SELECT * FROM hospitals WHERE user_id = $user_id AND approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital_data = mysqli_fetch_assoc($hospital_result);

if (!$hospital_data) {
    session_destroy();
    header("Location: " . BASE_URL . "login");
    exit();
}

$hospital_id = $hospital_data['hospital_id'];
$hospital_city_id = $hospital_data['city_id'];
$hospital_name = $hospital_data['hospital_name'];

// ============================================
// FETCH CITIES FOR FILTER
// ============================================
$cities_query = "SELECT city_id, city_name FROM cities WHERE status = 1 ORDER BY city_name ASC";
$cities_result = mysqli_query($con, $cities_query);
$cities = [];
while ($row = mysqli_fetch_assoc($cities_result)) {
    $cities[] = $row;
}

// ============================================
// FILTERS
// ============================================
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$filter_city = isset($_GET['city']) ? (int)$_GET['city'] : $hospital_city_id;

// ============================================
// PAGINATION
// ============================================
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// ============================================
// BUILD WHERE CLAUSE - CORRECTED FOR LABORATORIES
// ============================================
$where = "l.approve = 1 AND u.status = 1";
if (!empty($search)) {
    $where .= " AND l.lab_name LIKE '%$search%'";
}
if ($filter_city > 0) {
    $where .= " AND l.city_id = $filter_city";
}

// ============================================
// COUNT TOTAL
// ============================================
$count_query = "SELECT COUNT(*) as total 
                 FROM laboratories l
                 LEFT JOIN users u ON u.user_id = l.user_id
                 WHERE $where";
$count_result = mysqli_query($con, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $per_page);

// ============================================
// FETCH LABORATORIES - CORRECTED
// ============================================
$query = "SELECT l.*, c.city_name, u.status as ustatus,
                 (SELECT AVG(stars) FROM feedback WHERE user_id = l.user_id AND status = 1) as avg_rating,
                 (SELECT COUNT(*) FROM feedback WHERE user_id = l.user_id AND status = 1) as total_reviews
          FROM laboratories l
          LEFT JOIN cities c ON l.city_id = c.city_id
          LEFT JOIN users u ON u.user_id = l.user_id
          WHERE $where
          ORDER BY l.lab_name ASC
          LIMIT $offset, $per_page";

$result = mysqli_query($con, $query);
?>

<?php include BASE_PATH . '/admin/inc/header.php'; ?>
<?php include BASE_PATH . '/admin/inc/top.php'; ?>
<?php include BASE_PATH . '/hospital/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-lab-admin.css">

<div class="content-wrapper">

    <!-- ===== PAGE HEADER ===== -->
    <div class="page-header">
        <h4>
            <i class="fas fa-flask me-2"></i> Laboratories
            <span class="badge-hospital ms-2"><?php echo htmlspecialchars($hospital_name); ?></span>
        </h4>
        <span class="text-muted">Total: <?php echo $total_records; ?></span>
    </div>

    <!-- ===== FILTER SECTION ===== -->
    <div class="filter-section">
        <form method="GET" action="" id="filterForm">
            <div class="filter-row">
                <div class="filter-group">
                    <label><i class="fas fa-search"></i> Search</label>
                    <input type="text" class="form-control" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search by name...">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-city"></i> City</label>
                    <select class="form-select" name="city">
                        <option value="0">All Cities</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?php echo $city['city_id']; ?>" 
                                    <?php echo ($filter_city == $city['city_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($city['city_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group" style="flex: 0 0 auto; display: flex; gap: 8px;">
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="<?php echo BASE_URL; ?>hospital/labs" class="btn-reset">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </div>
            <input type="hidden" name="page" value="1">
        </form>
    </div>

    <!-- ===== LABORATORIES GRID ===== -->
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="lab-grid">
            <?php while ($lab = mysqli_fetch_assoc($result)): 
                $avg_rating = $lab['avg_rating'] ? round($lab['avg_rating'], 1) : 0;
            ?>
                <div class="lab-card">
                    <div class="card-body">
                        <div class="lab-icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div class="lab-name"><?php echo htmlspecialchars($lab['lab_name']); ?></div>
                        <div class="lab-city"><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($lab['city_name']); ?></div>
                        <?php if (!empty($lab['lab_phone'])): ?>
                            <div class="lab-contact"><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($lab['lab_phone']); ?></div>
                        <?php endif; ?>
                        <div class="lab-rating">
                            <span class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $avg_rating ? 'text-warning' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                            </span>
                            <span class="count">(<?php echo $lab['total_reviews'] ?? 0; ?>)</span>
                        </div>
                        <div class="mt-2">
                            <span class="lab-status <?php echo $lab['ustatus'] == 1 ? 'active' : 'inactive'; ?>">
                                <?php echo $lab['ustatus'] == 1 ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- ===== PAGINATION ===== -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <nav>
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&city=<?php echo $filter_city; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a></li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&city=<?php echo $filter_city; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&city=<?php echo $filter_city; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <div class="pagination-info">
                    Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $per_page, $total_records); ?> of <?php echo $total_records; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="no-records">
            <i class="fas fa-flask"></i>
            <h5>No Laboratories Found</h5>
            <p>No laboratories match your search criteria.</p>
        </div>
    <?php endif; ?>

</div>

<?php include BASE_PATH . '/admin/inc/footer.php'; ?>