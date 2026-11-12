<?php
include '../config.php';

// ── DOCTOR AUTH ────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'radiology') {
    header("Location: " . BASE_URL . "login"); exit();
}

$user_id   = (int)$_SESSION['user_id'];

// Doctor city (default filter)
// Doctor city (default filter)
$rq  = mysqli_query($con, "SELECT rad_name FROM radiology WHERE user_id = $user_id LIMIT 1");
$rad = mysqli_fetch_assoc($rq);
$blabName = $rad['rad_name'];

if (!$rad) { session_destroy(); header("Location: " . BASE_URL . "login"); exit(); }

$rad_city_id = (int)($doctor['city_id'] ?? 0);

// ── CITIES ─────────────────────────────────────────────────
$cities_r = mysqli_query($con, "SELECT city_id, city_name FROM cities WHERE status=1 ORDER BY city_name ASC");
$cities   = [];
while ($row = mysqli_fetch_assoc($cities_r)) $cities[] = $row;

// ── FILTERS ────────────────────────────────────────────────
$search      = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$filter_city = isset($_GET['city'])   ? (int)$_GET['city'] : $rad_city_id;

// ── PAGINATION ─────────────────────────────────────────────
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset   = ($page - 1) * $per_page;

$where = "bb.approve=1 AND u.status=1";
if (!empty($search))    $where .= " AND bb.bb_name LIKE '%$search%'";
if ($filter_city > 0)   $where .= " AND bb.city_id=$filter_city";

$total_records = (int)mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM blood_bank bb LEFT JOIN users u ON u.user_id=bb.user_id WHERE $where"))['c'];
$total_pages   = ceil($total_records / $per_page);

$bbank_result = mysqli_query($con, "SELECT bb.*, c.city_name, u.status as ustatus,
    (SELECT AVG(stars) FROM feedback WHERE user_id=bb.user_id AND status=1) as avg_rating,
    (SELECT COUNT(*) FROM feedback WHERE user_id=bb.user_id AND status=1) as total_reviews
    FROM blood_bank bb
    LEFT JOIN cities c ON bb.city_id=c.city_id
    LEFT JOIN users u ON u.user_id=bb.user_id
    WHERE $where ORDER BY bb.bb_name ASC LIMIT $offset,$per_page");
?>
<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/radiology/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-blood-bank-admin.css">

<div class="content-wrapper">

    <div class="page-header">
        <h4>
            <i class="fas fa-tint me-2"></i> Blood Banks
            <span class="badge-hospital ms-2"><?= htmlspecialchars($blabName) ?></span>
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
                    <a href="<?= BASE_URL ?>lab/blood-banks" class="btn-reset"><i class="fas fa-redo me-1"></i> Reset</a>
                </div>
            </div>
            <input type="hidden" name="page" value="1">
        </form>
    </div>

    <?php if (mysqli_num_rows($bbank_result) > 0): ?>
        <div class="blood-bank-grid">
            <?php while ($bb = mysqli_fetch_assoc($bbank_result)):
                $avg = $bb['avg_rating'] ? round($bb['avg_rating'],1) : 0; ?>
                <div class="blood-bank-card">
                    <div class="card-body">
                        <div class="bb-icon"><i class="fas fa-tint"></i></div>
                        <div class="bb-name"><?= htmlspecialchars($bb['bb_name']) ?></div>
                        <div class="bb-city"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($bb['city_name']) ?></div>
                        <?php if (!empty($bb['bb_contact'])): ?>
                            <div class="bb-contact"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($bb['bb_contact']) ?></div>
                        <?php endif; ?>
                        <div class="bb-rating">
                            <span class="stars"><?php for($i=1;$i<=5;$i++): ?><i class="fas fa-star <?= $i<=$avg?'text-warning':'text-muted' ?>"></i><?php endfor; ?></span>
                            <span class="count">(<?= $bb['total_reviews']??0 ?>)</span>
                        </div>
                        <div class="mt-2">
                            <span class="bb-status <?= $bb['ustatus']==1?'active':'inactive' ?>"><?= $bb['ustatus']==1?'Active':'Inactive' ?></span>
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
            <i class="fas fa-tint"></i>
            <h5>No Blood Banks Found</h5>
            <p>No blood banks match your search criteria.</p>
        </div>
    <?php endif; ?>

</div>
<?php include BASE_PATH.'/admin/inc/footer.php'; ?>
