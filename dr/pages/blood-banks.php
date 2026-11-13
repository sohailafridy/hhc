<?php 
// ============================================================
// 1. SEO FIX: Title aur Description set karein
// ============================================================
$GLOBALS['page_title'] = "Blood Banks in Pakistan — Find Blood Donors & Banks | SehatNexa";
$GLOBALS['page_description'] = "Find verified blood banks near you. Check available blood groups, stock, emergency contacts, and directions. Save lives by connecting with the nearest blood bank.";

// 2. Header Include karein
include '../includes/header.php'; 
?>

<?php
    // Handle search and filters
    $search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
    $city = isset($_GET['city']) ? (int)$_GET['city'] : $city_id; 

    // Pagination
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;

    // Build WHERE clause
    $where_clause = '';
    if (!empty($search)) {
        $where_clause .= " AND bb.bb_name LIKE '%$search%' ";
    }
    if (!empty($city) && $city != 0) {
        $where_clause .= " AND bb.city_id = '$city' ";
    }

    // Count total blood banks for pagination
    $count_query = "SELECT COUNT(*) as total
                    FROM blood_bank bb
                    LEFT JOIN cities c ON bb.city_id = c.city_id
                    LEFT JOIN users u ON u.user_id = bb.user_id
                    WHERE u.status = 1 AND bb.approve = 1 $where_clause";
    $count_result = mysqli_query($con, $count_query);
    $total_row = mysqli_fetch_assoc($count_result);
    $total_blood_banks = $total_row['total'];
    $total_pages = ceil($total_blood_banks / $per_page);

    // Fetch blood banks with pagination (BUG FIX: Added bb.approve = 1)
    $query = "SELECT bb.*, c.city_name
             FROM blood_bank bb
             LEFT JOIN cities c ON bb.city_id = c.city_id
             LEFT JOIN users u ON u.user_id = bb.user_id
             WHERE u.status = 1 AND bb.approve = 1 $where_clause
             ORDER BY bb.bb_name ASC
             LIMIT $per_page OFFSET $offset";

    $result = mysqli_query($con, $query);
?>

<body>

<link rel="stylesheet" href="<?= BASE_URL ?>style/blood-banks.css?v=<?= @filemtime(__DIR__ . '/style/blood-banks.css') ?: time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>style/entity-card.css?v=<?= @filemtime(__DIR__ . '/style/entity-card.css') ?: time() ?>">

    <!-- Navbar -->
    <?php include BASE_PATH.'/includes/menu.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container position-relative">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <!-- SEO: Only one H1 -->
                    <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">Our Blood Banks</h1><br>
                    <p class="lead mb-0" data-aos="fade-up" data-aos-delay="200">Find quality blood banks near you</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Blood Banks Section -->
    <section class="section-padding">
        <div class="container">
            <!-- Filter Section -->
            <div class="filter-section" data-aos="fade-up">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-tint"></i> Search by Blood Bank Name</label>
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Enter blood bank name...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-map-marker-alt"></i> Filter by City</label>
                            <select class="form-select select2-bootstrap-5-theme" name="city" id="citySelect" data-dropdown-css-class="select2-bootstrap-5-dropdown">
                                <option value="">All Cities</option>
                                <?php
                                $city_query = "SELECT city_id, city_name FROM cities WHERE status = 1 ORDER BY city_name ASC";
                                $city_result = mysqli_query($con, $city_query);
                                while($city_row = mysqli_fetch_assoc($city_result)) {
                                    $selected = ($city == $city_row['city_id']) ? 'selected' : '';
                                    echo '<option value="'.$city_row['city_id'].'" '.$selected.'>'.htmlspecialchars($city_row['city_name']).'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="d-flex gap-2 mt-2">
                                <button type="submit" class="btn-search-custom">
                                    <i class="fas fa-search"></i> Search Blood Banks
                                </button>
                                <a href="blood-banks" class="btn-reset-custom">
                                    <i class="fas fa-redo"></i> Reset All
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Ad Space 1 -->
            <div class="my-4" data-aos="fade-up">
                <div class="ad-slot text-center" style="min-height:90px; background:#f8f9fa; border:1px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                    <!-- SpeedyAds Code Yahan Paste Karein -->
                    <span class="text-muted small">Advertisement Space</span>
                </div>
            </div>

            <!-- Blood Banks Grid -->
            <div class="entity-grid" id="bloodBanksContainer">
                <?php
                $card_bgs    = ['#FFF0F0','#E8F7FF','#F0FFF8','#FFF8E8','#F0FDFA','#EFF6FF'];
                $card_accents= ['#1C3F8F','#00A8CC','#27C96A','#00C9A7','#1C3F8F','#00A8CC'];
                $blood_index = 0;

                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $bb_stars_q  = mysqli_query($con, "SELECT AVG(stars) as stars FROM feedback WHERE user_id='".(int)$row['user_id']."'");
                        $blood_rating = mysqli_fetch_assoc($bb_stars_q)['stars'];
                        $blood_rating = $blood_rating ? number_format((float)$blood_rating, 1) : 'New';
                        $is_new       = ($blood_rating === 'New');
                        $bg           = $card_bgs[$blood_index   % count($card_bgs)];
                        $accent       = $card_accents[$blood_index % count($card_accents)];
                        $blood_index++;

                        $img_src = (!empty($row['bb_pic']) && file_exists(BASE_PATH.'/admin/inc/uploads/blood-banks/'.$row['bb_pic']))
                            ? BASE_URL.'admin/inc/uploads/blood-banks/'.htmlspecialchars($row['bb_pic'])
                            : '';
                ?>
                <div class="e-card" style="--ec-bg:<?= $bg ?>; --ec-accent:<?= $accent ?>;" data-aos="fade-up" data-aos-delay="<?= 80 * ($blood_index % 6) ?>">
                    <div class="e-bg-icon"><i class="fas fa-tint"></i></div>
                    <div class="e-card-top">
                        <div class="e-art"><i class="fas fa-tint e-art-icon"></i></div>
                        <span class="e-pill e-pill-rating <?= $is_new ? 'is-new' : '' ?>">
                            <i class="fas fa-star"></i> <?= $blood_rating ?>
                        </span>
                        <div class="e-avatar">
                            <?php if ($img_src): ?>
                                <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($row['bb_name']) ?>">
                            <?php else: ?>
                                <div class="e-avatar-icon"><i class="fas fa-tint"></i></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="e-card-body">
                        <div class="e-type"><i class="fas fa-tint"></i> Blood Bank</div>
                        <!-- SEO: H4 to H3 -->
                        <h3 class="e-name"><?= htmlspecialchars($row['bb_name']) ?></h3>
                        <ul class="e-meta">
                            <li><i class="fas fa-map-marker-alt"></i><span><?= htmlspecialchars($row['city_name']) ?></span></li>
                            <?php if (!empty($row['bb_contact'])): ?>
                            <li><i class="fas fa-phone"></i><span><?= htmlspecialchars($row['bb_contact']) ?></span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="e-card-footer">
                        <span class="e-stars">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <i class="fas fa-star <?= (!$is_new && $s <= round($blood_rating)) ? 'on' : '' ?>"></i>
                            <?php endfor; ?>
                        </span>
                        <a href="blood-bank-detail?blood_bankid=<?= $row['bb_id'] ?>" class="e-btn">
                            View Details <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php
                    }
                } else { ?>
                    <div class="no-entity">
                        <i class="fas fa-tint"></i>
                        <h3>No Blood Banks Found</h3>
                        <p>No blood banks match your search criteria.</p>
                    </div>
                <?php } ?>
            </div>

            <!-- Ad Space 2 -->
            <div class="my-4" data-aos="fade-up">
                <div class="ad-slot text-center" style="min-height:90px; background:#f8f9fa; border:1px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                    <!-- SpeedyAds Code Yahan Paste Karein -->
                    <span class="text-muted small">Advertisement Space</span>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <nav aria-label="Blood Banks pagination">
                    <ul class="pagination">
                        <!-- Previous Button -->
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&city=<?php echo $city; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);

                        if ($start_page > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?page=1&search='.urlencode($search).'&city='.$city.'">1</a></li>';
                            if ($start_page > 2) {
                                echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                            }
                        }

                        for ($i = $start_page; $i <= $end_page; $i++) {
                            if ($i == $page) {
                                echo '<li class="page-item active"><a class="page-link" href="#">'.$i.'</a></li>';
                            } else {
                                echo '<li class="page-item"><a class="page-link" href="?page='.$i.'&search='.urlencode($search).'&city='.$city.'">'.$i.'</a></li>';
                            }
                        }

                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'&search='.urlencode($search).'&city='.$city.'">'.$total_pages.'</a></li>';
                        }
                        ?>

                        <!-- Next Button -->
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&city=<?php echo $city; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <!-- Results Info -->
                <div class="text-center mt-3">
                    <p class="text-muted">
                        Showing <?php echo ($offset + 1); ?>-<?php echo min($offset + $per_page, $total_blood_banks); ?> of <?php echo $total_blood_banks; ?> blood banks
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
   <?php include BASE_PATH.'/includes/footer.php';?>
   
<script>
$(document).ready(function() {
    $('#citySelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search city...',
        allowClear: true,
        width: '100%'
    });
});
</script>
</body>
</html>