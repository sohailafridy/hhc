<?php
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
// AJAX: ASSIGN / REMOVE RADIOLOGY
// ============================================
if (isset($_POST['action']) && $_POST['action'] == 'toggle_radiology') {
    header('Content-Type: application/json');
    
    $rad_user_id = (int)$_POST['rad_user_id'];
    $assign_type = $_POST['assign_type']; // 'register' or 'remove'
    
    if ($assign_type == 'register') {
        // Check if already assigned
        $check = mysqli_query($con, "SELECT assign_hosp_id FROM assign_hospital_radiology WHERE user_id = $rad_user_id AND hospital_id = $hospital_id");
        if (mysqli_num_rows($check) > 0) {
            // If exists but status = 0, update to 1
            $existing = mysqli_fetch_assoc($check);
            $update = "UPDATE assign_hospital_radiology SET status = 1, updated_at = NOW() WHERE assign_hosp_id = {$existing['assign_hosp_id']}";
            mysqli_query($con, $update);
            echo json_encode(['success' => true, 'message' => 'Radiology center re-activated successfully!']);
        } else {
            $insert = "INSERT INTO assign_hospital_radiology (user_id, hospital_id, status, created_at) VALUES ($rad_user_id, $hospital_id, 1, NOW())";
            if (mysqli_query($con, $insert)) {
                echo json_encode(['success' => true, 'message' => 'Radiology center successfully registered to your hospital!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($con)]);
            }
        }
    } elseif ($assign_type == 'remove') {
        // Delete record
        $delete = "DELETE FROM assign_hospital_radiology WHERE user_id = $rad_user_id AND hospital_id = $hospital_id";
        if (mysqli_query($con, $delete)) {
            echo json_encode(['success' => true, 'message' => 'Radiology center removed from your hospital.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($con)]);
        }
    }
    exit();
}

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
$search_reg_no = isset($_GET['reg_no']) ? mysqli_real_escape_string($con, $_GET['reg_no']) : '';
$filter_city = isset($_GET['city']) ? (int)$_GET['city'] : $hospital_city_id;
$is_registered = isset($_GET['is_registered']) ? (int)$_GET['is_registered'] : 0; 

// ============================================
// PAGINATION
// ============================================
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// ============================================
// BUILD WHERE CLAUSE
// ============================================
$where = "r.approve = 1 AND u.status = 1";
if (!empty($search)) {
    $where .= " AND r.rad_name LIKE '%$search%'";
}
if (!empty($search_reg_no)) {
    $where .= " AND r.reg_no LIKE '%$search_reg_no%'";
}
if ($filter_city > 0) {
    $where .= " AND r.city_id = $filter_city";
}

// Logic for Registered vs Unregistered
if ($is_registered == 1) {
    $where .= " AND ahr.assign_hosp_id IS NOT NULL AND ahr.status = 1";
} else {
    $where .= " AND (ahr.assign_hosp_id IS NULL OR ahr.status = 0)";
}

// ============================================
// COUNT TOTAL
// ============================================
$count_query = "SELECT COUNT(*) as total 
                 FROM radiology r
                 LEFT JOIN users u ON u.user_id = r.user_id
                 LEFT JOIN assign_hospital_radiology ahr ON ahr.user_id = r.user_id AND ahr.hospital_id = $hospital_id
                 WHERE $where";
$count_result = mysqli_query($con, $count_query);
if (!$count_result) die("SQL Error Count: " . mysqli_error($con));

$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $per_page);

// ============================================
// FETCH RADIOLOGY
// ============================================
$query = "SELECT r.*, c.city_name, u.status as ustatus,
                 (SELECT AVG(stars) FROM feedback WHERE user_id = r.user_id AND status = 1) as avg_rating,
                 (SELECT COUNT(*) FROM feedback WHERE user_id = r.user_id AND status = 1) as total_reviews,
                 ahr.assign_hosp_id
          FROM radiology r
          LEFT JOIN cities c ON r.city_id = c.city_id
          LEFT JOIN users u ON u.user_id = r.user_id
          LEFT JOIN assign_hospital_radiology ahr ON ahr.user_id = r.user_id AND ahr.hospital_id = $hospital_id
          WHERE $where
          ORDER BY r.rad_name ASC
          LIMIT $offset, $per_page";

$rad_result = mysqli_query($con, $query);

if (!$rad_result) {
    die("SQL Error Main: " . mysqli_error($con));
}
?>

<?php include BASE_PATH . '/admin/inc/header.php'; ?>
<?php include BASE_PATH . '/admin/inc/top.php'; ?>
<?php include BASE_PATH . '/hospital/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-lab-admin.css">

<style>
/* Custom styles for action buttons */
.lab-actions { display: flex; gap: 8px; margin-top: 12px; }
.btn-action-lab { flex: 1; padding: 8px; border-radius: 8px; font-weight: 600; font-size: 0.8rem; text-align: center; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 5px; }
.btn-register { background: #e0f2fe; color: #0284c7; }
.btn-register:hover { background: #0284c7; color: #fff; }
.btn-remove { background: #fee2e2; color: #ef4444; }
.btn-remove:hover { background: #ef4444; color: #fff; }
.btn-detail { background: #f1f5f9; color: #475569; }
.btn-detail:hover { background: #475569; color: #fff; }
.btn-add-rad { background: linear-gradient(135deg,#1C3F8F,#00A8CC); color: #fff; padding: 8px 16px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem; border:none;}
.btn-add-rad:hover { opacity: 0.9; color: #fff; }

/* Checkbox styling for filter */
.filter-checkbox-group {
    display: flex;
    align-items: center;
    margin-top: 22px;
    gap: 8px;
}
.filter-checkbox-group input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #0284c7;
    cursor: pointer;
}
.filter-checkbox-group label {
    font-weight: 600;
    cursor: pointer;
    color: #475569;
    font-size: 0.85rem;
    margin-bottom: 0;
}
</style>

<div class="content-wrapper">

    <!-- ===== PAGE HEADER ===== -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <h4>
            <i class="fas fa-camera me-2"></i> Radiology Centers
            <span class="badge-hospital ms-2"><?php echo htmlspecialchars($hospital_name); ?></span>
        </h4>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted">Total: <?php echo $total_records; ?></span>
            <!-- Add New Radiology Button -->
            <a href="<?php echo BASE_URL; ?>hospital/radiology-add" class="btn-add-rad">
                <i class="fas fa-plus me-1"></i> Add New Center
            </a>
        </div>
    </div>

    <!-- ===== FILTER SECTION ===== -->
    <div class="filter-section">
        <form method="GET" action="" id="filterForm">
            <div class="filter-row">
                <div class="filter-group">
                    <label><i class="fas fa-search"></i> Search Name</label>
                    <input type="text" class="form-control" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search by name...">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-id-card"></i> Reg No</label>
                    <input type="text" class="form-control" name="reg_no" 
                           value="<?php echo htmlspecialchars($search_reg_no); ?>" 
                           placeholder="Search by Reg No...">
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
                
                <!-- Filter: Registered Centers Checkbox -->
                <div class="filter-group filter-checkbox-group">
                    <input type="checkbox" name="is_registered" id="is_registered" value="1" <?php echo ($is_registered == 1) ? 'checked' : ''; ?>>
                    <label for="is_registered">Registered Centers</label>
                </div>

                <div class="filter-group" style="flex: 0 0 auto; display: flex; gap: 8px; margin-top: 22px;">
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="<?php echo BASE_URL; ?>hospital/radiology" class="btn-reset">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </div>
            <input type="hidden" name="page" value="1">
        </form>
    </div>

    <!-- ===== RADIOLOGY GRID ===== -->
    <?php if (mysqli_num_rows($rad_result) > 0): ?>
        <div class="lab-grid">
            <?php while ($rad = mysqli_fetch_assoc($rad_result)): 
                $avg_rating = $rad['avg_rating'] ? round($rad['avg_rating'], 1) : 0;
            ?>
                <div class="lab-card">
                    <div class="card-body">
                        <div class="lab-icon" style="background:#e0f2fe; color:#0284c7;">
                            <i class="fas fa-camera"></i>
                        </div>
                        <div class="lab-name"><?php echo htmlspecialchars($rad['rad_name']); ?></div>
                        <div class="lab-city"><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($rad['city_name']); ?></div>
                        
                        <?php if (!empty($rad['reg_no'])): ?>
                            <div class="lab-city mt-1"><i class="fas fa-id-card me-1"></i> Reg No: <?php echo htmlspecialchars($rad['reg_no']); ?></div>
                        <?php endif; ?>

                        <?php if (!empty($rad['rad_phone'])): ?>
                            <div class="lab-contact"><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($rad['rad_phone']); ?></div>
                        <?php endif; ?>
                        
                        <div class="lab-rating">
                            <span class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $avg_rating ? 'text-warning' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                            </span>
                            <span class="count">(<?php echo $rad['total_reviews'] ?? 0; ?>)</span>
                        </div>
                        <div class="mt-2">
                            <span class="lab-status <?php echo $rad['ustatus'] == 1 ? 'active' : 'inactive'; ?>">
                                <?php echo $rad['ustatus'] == 1 ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="lab-actions">
                            <?php if ($is_registered == 1): ?>
                                <!-- If showing registered, show Remove button -->
                                <button type="button" class="btn-action-lab btn-remove" onclick="toggleRadiology(<?= $rad['user_id'] ?>, 'remove')">
                                    <i class="fas fa-trash-alt"></i> Remove from Hospital
                                </button>
                                <a href="<?php echo BASE_URL; ?>hospital/radiology-detail?id=<?= $rad['rad_id'] ?>" class="btn-action-lab btn-detail">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            <?php else: ?>
                                <!-- If showing unregistered, show Register button -->
                                <button type="button" class="btn-action-lab btn-register" onclick="toggleRadiology(<?= $rad['user_id'] ?>, 'register')">
                                    <i class="fas fa-hospital-user"></i> Register In Hospital
                                </button>
                                
                            <?php endif; ?>
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
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&reg_no=<?php echo urlencode($search_reg_no); ?>&city=<?php echo $filter_city; ?>&is_registered=<?php echo $is_registered; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a></li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&reg_no=<?php echo urlencode($search_reg_no); ?>&city=<?php echo $filter_city; ?>&is_registered=<?php echo $is_registered; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&reg_no=<?php echo urlencode($search_reg_no); ?>&city=<?php echo $filter_city; ?>&is_registered=<?php echo $is_registered; ?>">
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
            <i class="fas fa-camera"></i>
            <h5>No Radiology Centers Found</h5>
            <p>No centers match your search criteria.</p>
        </div>
    <?php endif; ?>

</div>

<?php include BASE_PATH . '/admin/inc/footer.php'; ?>

<!-- ============================================ -->
<!-- JAVASCRIPT FOR AJAX TOGGLE -->
<!-- ============================================ -->
<script>
function toggleRadiology(radUserId, actionType) {
    let confirmMsg = actionType === 'register' 
        ? "Are you sure you want to register this radiology center in your hospital?" 
        : "Are you sure you want to remove this radiology center from your hospital?";

    if (confirm(confirmMsg)) {
        let formData = new URLSearchParams();
        formData.append('action', 'toggle_radiology');
        formData.append('rad_user_id', radUserId);
        formData.append('assign_type', actionType);

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload(); 
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Something went wrong. Please try again.");
        });
    }
}
</script>