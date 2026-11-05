<?php include '../includes/header.php'; ?>
<?php
    // Handle search and filters
    $search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
    $city = isset($_GET['city']) ? (int)$_GET['city'] : $city_id; // Default to Kohat if not set

    // Pagination
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;

    // Build WHERE clause
    $where_clause = '';
    if (!empty($search)) {
        $where_clause .= " AND h.hospital_name LIKE '%$search%' ";
    }
    if (!empty($city) && $city != 0) {
        $where_clause .= " AND h.city_id = '$city' ";
    }

    // Count total hospitals for pagination (status users table se)
    $count_query = "SELECT COUNT(*) as total
                    FROM hospitals h
                    LEFT JOIN cities c ON h.city_id = c.city_id
                    LEFT JOIN users u ON u.user_id = h.user_id
                    WHERE u.status = 1 AND h.approve = 1 $where_clause";
    $count_result = mysqli_query($con, $count_query);
    $total_row = mysqli_fetch_assoc($count_result);
    $total_hospitals = $total_row['total'];
    $total_pages = ceil($total_hospitals / $per_page);

    // Fetch hospitals with pagination
    $query = "SELECT h.*, c.city_name
             FROM hospitals h
             LEFT JOIN cities c ON h.city_id = c.city_id
             LEFT JOIN users u ON u.user_id = h.user_id
             WHERE u.status = 1 AND h.approve = 1 $where_clause
             ORDER BY h.hospital_name ASC
             LIMIT $per_page OFFSET $offset";

    $result = mysqli_query($con, $query);
?>

<body>
<link rel="stylesheet" href="<?= BASE_URL ?>style/hospitals.css?v=<?= @filemtime(__DIR__ . '/style/hospitals.css') ?: time() ?>">



    <!-- Navbar -->
   <?php include BASE_PATH.'/includes/menu.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container position-relative">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <!-- Page Header -->
                    <section class="page-header">
                        <div class="container position-relative">
                            <div class="row">
                                <div class="col-lg-12 text-center">
                                    <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">Our Hospitals</h1><br>
                                    <p class="lead mb-0" data-aos="fade-up" data-aos-delay="200">Find quality healthcare facilities near you</p>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                </div>
            </div>
        </div>
    </section>

    <!-- Hospitals Section -->
    <section class="section-padding">
        <div class="container">
            <!-- Filter Section -->
            <div class="filter-section" data-aos="fade-up">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-search"></i> Search by Hospital Name</label>
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Enter hospital name...">
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
                                    <i class="fas fa-search"></i> Search Hospitals
                                </button>
                                <a href="hospitals" class="btn-reset-custom">
                                    <i class="fas fa-redo"></i> Reset All
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Hospitals Grid -->
            <div class="hospitals-grid" id="hospitalsContainer">
                <?php
                // Brand bg colors (light tints matching logo)
                $card_bgs = ['#E8F7FF','#F0FFF8','#EFF6FF','#F0FDFA','#EEF2FF','#F0F9FF'];
                // Brand accent colors matching logo
                $card_accents = ['#1C3F8F','#27C96A','#00A8CC','#00C9A7','#1C3F8F','#00A8CC'];
                $hospital_index = 0;

                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $hosp_stars_q  = mysqli_query($con, "SELECT AVG(stars) as stars FROM feedback WHERE user_id='".(int)$row['user_id']."'");
                        $hosp_stars    = mysqli_fetch_assoc($hosp_stars_q)['stars'];
                        $hospital_rating = $hosp_stars ? number_format((float)$hosp_stars, 1) : 'New';
                        $is_new        = ($hospital_rating === 'New');
                        $bg            = $card_bgs[$hospital_index   % count($card_bgs)];
                        $accent        = $card_accents[$hospital_index % count($card_accents)];
                        $hospital_index++;

                        // Doctor count
                        $doc_cnt_q = mysqli_query($con, "SELECT COUNT(*) as c FROM doctor_in_hospital WHERE hospital_id=".(int)$row['hospital_id']." AND inactive=0");
                        $doc_cnt   = mysqli_fetch_assoc($doc_cnt_q)['c'];

                        $img_src = (!empty($row['hospital_pic']) && file_exists(BASE_PATH.'/admin/inc/uploads/hospitals/'.$row['hospital_pic']))
                            ? BASE_URL.'admin/inc/uploads/hospitals/'.htmlspecialchars($row['hospital_pic'])
                            : '';
                ?>
                <div class="h-card" style="--hc-bg:<?= $bg ?>; --hc-accent:<?= $accent ?>;" data-aos="fade-up" data-aos-delay="<?= 80 * ($hospital_index % 6) ?>">

                    <!-- Background art -->
                    <div class="h-bg-icon"><i class="fas fa-hospital"></i></div>

                    <!-- Top banner -->
                    <div class="h-card-top">
                        <div class="h-art">
                            <i class="fas fa-hospital-alt h-art-icon"></i>
                        </div>

                        <!-- Rating pill -->
                        <span class="h-pill h-pill-rating <?= $is_new ? 'is-new' : '' ?>">
                            <i class="fas <?= $is_new ? 'fa-sparkles' : 'fa-star' ?>"></i>
                            <?= $hospital_rating ?>
                        </span>

                        <!-- Image / placeholder -->
                        <div class="h-avatar">
                            <?php if ($img_src): ?>
                                <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($row['hospital_name']) ?>">
                            <?php else: ?>
                                <div class="h-avatar-icon"><i class="fas fa-hospital-alt"></i></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="h-card-body">
                        <div class="h-type"><i class="fas fa-hospital-user"></i> Hospital</div>
                        <h4 class="h-name"><?= htmlspecialchars($row['hospital_name']) ?></h4>
                        <ul class="h-meta">
                            <li><i class="fas fa-map-marker-alt"></i><span><?= htmlspecialchars($row['city_name']) ?></span></li>
                            <li><i class="fas fa-user-md"></i><span><?= $doc_cnt ?> Doctor<?= $doc_cnt != 1 ? 's' : '' ?></span></li>
                        </ul>
                    </div>

                    <!-- Footer -->
                    <div class="h-card-footer">
                        <span class="h-stars">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <i class="fas fa-star <?= (!$is_new && $s <= round($hospital_rating)) ? 'on' : '' ?>"></i>
                            <?php endfor; ?>
                        </span>
                        <a href="hospital-detail?hospital_id=<?= $row['hospital_id'] ?>" class="h-btn">
                            View Details <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php
                    }
                } else { ?>
                    <div class="no-hospitals">
                        <i class="fas fa-hospital"></i>
                        <h3>No Hospitals Found</h3>
                        <p>No hospitals match your search criteria. Please try different filters.</p>
                    </div>
                <?php } ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <nav aria-label="Hospitals pagination">
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
                        Showing <?php echo ($offset + 1); ?>-<?php echo min($offset + $per_page, $total_hospitals); ?> of <?php echo $total_hospitals; ?> hospitals
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
    <script>
        function viewHospitalDetails(hospitalId) {
            // You can implement this function to show hospital details
            // For now, just show an alert or redirect to details page
            alert('Hospital ID: ' + hospitalId + ' - Details page coming soon!');
        }
    </script>

  
    </style>
</body>
</html>