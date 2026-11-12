<?php include '../includes/header.php'; ?>

<?php
    // Handle search and filters
    $search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
    $city = isset($_GET['city']) ? (int)$_GET['city'] : $city_id; // Default to session city if not set

    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;

    // Build WHERE clause
    $where_clause = '';
    $where_clause = ' AND r.approve = 1';
    if (!empty($search)) {
        $where_clause .= " AND r.rad_name LIKE '%$search%' ";
    }
    if (!empty($city) && $city !=0) {
        $where_clause .= " AND r.city_id = '$city' ";
    }

    // Count total radiology centers for pagination
    $count_query = "SELECT COUNT(*) as total 
                    FROM radiology r 
                    LEFT JOIN cities c ON r.city_id = c.city_id 
                    LEFT JOIN users u ON u.user_id = r.user_id
                    WHERE u.status = 1 AND r.approve=1 $where_clause";
    $count_result = mysqli_query($con, $count_query);
    $total_row = mysqli_fetch_assoc($count_result);
    $total_rads = $total_row['total'];
    $total_pages = ceil($total_rads / $per_page);

    // Fetch radiology centers with pagination
    $query = "SELECT r.*, c.city_name 
             FROM radiology r 
             LEFT JOIN cities c ON r.city_id = c.city_id 
             LEFT JOIN users u ON u.user_id = r.user_id
             WHERE u.status = 1 AND r.approve=1 $where_clause
             ORDER BY r.rad_name ASC
             LIMIT $per_page OFFSET $offset";
    
    $result = mysqli_query($con, $query);
?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/lab-list.css?v=<?= @filemtime(__DIR__ . '/style/lab-list.css') ?: time() ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>style/entity-card.css?v=<?= @filemtime(__DIR__ . '/style/entity-card.css') ?: time() ?>">

<body>

    <!-- Navbar -->
    <?php include BASE_PATH.'/includes/menu.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container position-relative">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <section class="page-header">
                        <div class="container position-relative">
                            <div class="row">
                                <div class="col-lg-12 text-center">
                                    <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">Our Radiology Centers</h1><br>
                                    <p class="lead mb-0" data-aos="fade-up" data-aos-delay="200">Find top imaging and diagnostic centers near you</p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </section>

    <!-- Radiology Section -->
    <section class="section-padding">
        <div class="container">
            <!-- Filter Section -->
            <div class="filter-section" data-aos="fade-up">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-camera"></i> Search by Center Name</label>
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Enter center name...">
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
                                    echo '<option value="'.$city_row['city_id'].'" '.$selected.'>'.$city_row['city_name'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="d-flex gap-2 mt-2">
                                <button type="submit" class="btn-search-custom">
                                    <i class="fas fa-search"></i> Search Centers
                                </button>
                                <a href="radiology" class="btn-reset-custom">
                                    <i class="fas fa-redo"></i> Reset All
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Radiology Grid -->
            <div class="entity-grid" id="radiologyContainer">
                <?php
                $card_bgs    = ['#E8F7FF','#F0FFF8','#EFF6FF','#F0FDFA','#EEF2FF','#F0F9FF'];
                $card_accents= ['#00A8CC','#27C96A','#1C3F8F','#00C9A7','#1C3F8F','#00A8CC'];
                $rad_index   = 0;

                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $rad_stars_q  = mysqli_query($con, "SELECT AVG(stars) as stars FROM feedback WHERE user_id='".(int)$row['user_id']."'");
                        $rad_rating   = mysqli_fetch_assoc($rad_stars_q)['stars'];
                        $rad_rating   = $rad_rating ? number_format((float)$rad_rating, 1) : 'New';
                        $is_new       = ($rad_rating === 'New');
                        $bg           = $card_bgs[$rad_index   % count($card_bgs)];
                        $accent       = $card_accents[$rad_index % count($card_accents)];
                        $rad_index++;

                        $img_src = (!empty($row['rad_pic']) && file_exists(BASE_PATH.'/admin/inc/uploads/radiology/'.$row['rad_pic']))
                            ? BASE_URL.'admin/inc/uploads/radiology/'.htmlspecialchars($row['rad_pic'])
                            : '';
                ?>
                <div class="e-card" style="--ec-bg:<?= $bg ?>; --ec-accent:<?= $accent ?>;" data-aos="fade-up" data-aos-delay="<?= 80 * ($rad_index % 6) ?>">
                    <div class="e-bg-icon"><i class="fas fa-camera"></i></div>
                    <div class="e-card-top">
                        <div class="e-art"><i class="fas fa-x-ray e-art-icon"></i></div>
                        <span class="e-pill e-pill-rating <?= $is_new ? 'is-new' : '' ?>">
                            <i class="fas <?= $is_new ? 'fa-star' : 'fa-star' ?>"></i> <?= $rad_rating ?>
                        </span>
                        <div class="e-avatar">
                            <?php if ($img_src): ?>
                                <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($row['rad_name']) ?>">
                            <?php else: ?>
                                <div class="e-avatar-icon"><i class="fas fa-camera"></i></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="e-card-body">
                        <div class="e-type"><i class="fas fa-x-ray"></i> Radiology Center</div>
                        <h4 class="e-name"><?= htmlspecialchars($row['rad_name']) ?></h4>
                        <ul class="e-meta">
                            <li><i class="fas fa-map-marker-alt"></i><span><?= htmlspecialchars($row['city_name']) ?></span></li>
                            <?php if (!empty($row['rad_phone'])): ?>
                            <li><i class="fas fa-phone"></i><span><?= htmlspecialchars($row['rad_phone']) ?></span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="e-card-footer">
                        <span class="e-stars">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <i class="fas fa-star <?= (!$is_new && $s <= round($rad_rating)) ? 'on' : '' ?>"></i>
                            <?php endfor; ?>
                        </span>
                        <!-- Link to detail page -->
                        <a href="radiology-detail?rad_id=<?= $row['rad_id'] ?>" class="e-btn">
                            View Details <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php
                    }
                } else { ?>
                    <div class="no-entity">
                        <i class="fas fa-camera"></i>
                        <h3>No Radiology Centers Found</h3>
                        <p>No radiology centers match your search criteria.</p>
                    </div>
                <?php } ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <nav aria-label="Radiology pagination">
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
                        Showing <?php echo ($offset + 1); ?>-<?php echo min($offset + $per_page, $total_rads); ?> of <?php echo $total_rads; ?> radiology centers
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