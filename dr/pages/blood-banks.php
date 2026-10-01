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

    // Fetch blood banks with pagination
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
<link rel="stylesheet" href="<?= BASE_URL ?>style/blood-banks.css">
    <!-- Navbar -->
    <?php include BASE_PATH.'/includes/menu.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container position-relative">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">Our Blood Banks</h1>
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

            <!-- Blood Banks Grid -->
            <div class="row g-3" id="bloodBanksContainer">
                <?php
                $blood_card_colors = ['#fff1f1', '#fff6e8', '#fceef3', '#eef6ff', '#f3efff', '#edf8ff'];
                $blood_index = 0;
                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        // Rating (feedback table me user_id se)
                        $bb_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='". (int)$row['user_id'] ."'");
                        $bb_stars = mysqli_fetch_assoc($bb_stars_q);
                        $bb_stars = $bb_stars['stars'];
                        $blood_rating = $bb_stars ? number_format((float)$bb_stars, 1) : 'New';
                        $blood_bg = $blood_card_colors[$blood_index % count($blood_card_colors)];
                        $blood_index++;
                        ?>
                        <div class="col-lg-3 col-md-4 col-6" data-aos="fade-up" data-aos-delay="<?php echo 100 + ($blood_index * 80); ?>">
                            <div class="speciality-card blood-mini-card">
                                <div class="speciality-card-body" style="background-color: <?php echo $blood_bg; ?>;">
                                    <div class="speciality-icon">
                                        <i class="fas fa-tint"></i>
                                    </div>
                                    <h4 class="speciality-title"><?php echo htmlspecialchars($row['bb_name']); ?></h4>
                                    <p class="hospital-mini-location">
                                        <i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($row['city_name']); ?>
                                    </p>
                                    <div class="hospital-mini-rating">
                                        <i class="fas fa-star text-warning"></i><?php echo $blood_rating; ?> Rating
                                    </div>
                                    <a href="blood-bank-detail?blood_bankid=<?php echo $row['bb_id']; ?>" class="speciality-btn">
                                        <i class="fas fa-arrow-right"></i>
                                        DETAILS
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="col-12">
                        <div class="no-blood-banks text-center py-5">
                            <i class="fas fa-tint fa-3x text-primary mb-3"></i>
                            <h3>No Blood Banks Found</h3>
                            <p class="text-muted">No blood banks match your search criteria. Please try different filters.</p>
                        </div>
                    </div>
                    <?php
                }
                ?>
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
    <script>
        function viewBloodBankDetails(bloodBankId) {
            // You can implement this function to show blood bank details
            // For now, just show an alert or redirect to details page
            alert('Blood Bank ID: ' + bloodBankId + ' - Details page coming soon!');
        }
    </script>

   
</body>
</html>