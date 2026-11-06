<?php
include '../config.php';

// ── DOCTOR AUTH ────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'lab') {
    header("Location: " . BASE_URL . "login"); exit();
}

$user_id   = (int)$_SESSION['user_id'];

// Doctor city (default filter)
$lq  = mysqli_query($con, "SELECT lab_name FROM laboratories WHERE user_id = $user_id LIMIT 1");
$labs = mysqli_fetch_assoc($lq);
$labotName = $labs['lab_name'];
if (!$labs) { session_destroy(); header("Location: " . BASE_URL . "login"); exit(); }

$lab_city_id = (int)($doctor['city_id'] ?? 0);

// ── CITIES ─────────────────────────────────────────────────
$cities_r = mysqli_query($con, "SELECT city_id, city_name FROM cities WHERE status=1 ORDER BY city_name ASC");
$cities   = [];
while ($row = mysqli_fetch_assoc($cities_r)) $cities[] = $row;

// ── FILTERS ────────────────────────────────────────────────
$search      = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$filter_city = isset($_GET['city'])   ? (int)$_GET['city'] : $lab_city_id;

// ── PAGINATION ─────────────────────────────────────────────
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset   = ($page - 1) * $per_page;

$where = "l.approve=1 AND u.status=1";
if (!empty($search))     $where .= " AND l.lab_name LIKE '%$search%'";
if ($filter_city > 0)    $where .= " AND l.city_id=$filter_city";

$total_records = (int)mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM laboratories l LEFT JOIN users u ON u.user_id=l.user_id WHERE $where"))['c'];
$total_pages   = ceil($total_records / $per_page);

$lab_result = mysqli_query($con, "SELECT l.*, c.city_name, u.status as ustatus,
    (SELECT AVG(stars) FROM feedback WHERE user_id=l.user_id AND status=1) as avg_rating,
    (SELECT COUNT(*) FROM feedback WHERE user_id=l.user_id AND status=1) as total_reviews
    FROM laboratories l
    LEFT JOIN cities c ON l.city_id=c.city_id
    LEFT JOIN users u ON u.user_id=l.user_id
    WHERE $where ORDER BY l.lab_name ASC LIMIT $offset,$per_page");
?>
<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/lab/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-lab-admin.css">

<div class="content-wrapper">

    <div class="page-header">
        <h4>
            <i class="fas fa-flask me-2"></i> Laboratories
            <span class="badge-hospital ms-2"><?= htmlspecialchars($labotName) ?></span>
        </h4>
        <span class="text-muted">Total: <?= $total_records ?></span>
    </div>

    <div class="filter-section">
        <form method="GET">
            <div class="filter-row">
                <div class="filter-group">
                    <label><i class="fas fa-search"></i> Search</label>
                    <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name...">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-city"></i> City</label>
                    <select class="form-select" name="city">
                        <option value="0">All Cities</option>
                        <?php foreach ($cities as $c): ?>
                            <option value="<?= $c['city_id'] ?>" <?= $filter_city==$c['city_id']?'selected':'' ?>><?= htmlspecialchars($c['city_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group" style="flex:0 0 auto; display:flex; gap:8px;">
                    <button type="submit" class="btn-filter"><i class="fas fa-search me-1"></i> Filter</button>
                    <a href="<?= BASE_URL ?>doctor/labs" class="btn-reset"><i class="fas fa-redo me-1"></i> Reset</a>
                </div>
            </div>
            <input type="hidden" name="page" value="1">
        </form>
    </div>

    <?php if (mysqli_num_rows($lab_result) > 0): ?>
        <div class="lab-grid">
            <?php while ($lab = mysqli_fetch_assoc($lab_result)):
                $avg = $lab['avg_rating'] ? round($lab['avg_rating'],1) : 0; ?>
                <div class="lab-card">
                    <div class="card-body">
                        <div class="lab-icon"><i class="fas fa-flask"></i></div>
                        <div class="lab-name"><?= htmlspecialchars($lab['lab_name']) ?></div>
                        <div class="lab-city"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($lab['city_name']) ?></div>
                        <?php if (!empty($lab['lab_phone'])): ?>
                            <div class="lab-contact"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($lab['lab_phone']) ?></div>
                        <?php endif; ?>
                        <div class="lab-rating">
                            <span class="stars"><?php for($i=1;$i<=5;$i++): ?><i class="fas fa-star <?= $i<=$avg?'text-warning':'text-muted' ?>"></i><?php endfor; ?></span>
                            <span class="count">(<?= $lab['total_reviews']??0 ?>)</span>
                        </div>
                        <div class="mt-2">
                            <span class="lab-status <?= $lab['ustatus']==1?'active':'inactive' ?>"><?= $lab['ustatus']==1?'Active':'Inactive' ?></span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <nav><ul class="pagination">
                    <li class="page-item <?= $page<=1?'disabled':'' ?>"><a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&city=<?= $filter_city ?>"><i class="fas fa-chevron-left"></i></a></li>
                    <?php for($i=1;$i<=$total_pages;$i++): ?><li class="page-item <?= $i==$page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&city=<?= $filter_city ?>"><?= $i ?></a></li><?php endfor; ?>
                    <li class="page-item <?= $page>=$total_pages?'disabled':'' ?>"><a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&city=<?= $filter_city ?>"><i class="fas fa-chevron-right"></i></a></li>
                </ul></nav>
                <div class="pagination-info">Showing <?= $offset+1 ?>-<?= min($offset+$per_page,$total_records) ?> of <?= $total_records ?></div>
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
<?php include BASE_PATH.'/admin/inc/footer.php'; ?>
