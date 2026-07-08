<?php include '../includes/header.php'; ?>

<?php
   
    
    // Handle search and filters
    $search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
    $city = isset($_GET['city']) ? (int)$_GET['city'] : $city_id; // Default to Kohat if not set

    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;

    // Build WHERE clause
    $where_clause = '';
    $where_clause = ' AND l.approve = 1';
    if (!empty($search)) {
        $where_clause .= " AND l.lab_name LIKE '%$search%' ";
    }
    if (!empty($city) && $city !=0) {
        $where_clause .= " AND l.city_id = '$city' ";
    }

    // Count total labs for pagination
    $count_query = "SELECT COUNT(*) as total 
                    FROM laboratories l 
                    LEFT JOIN cities c ON l.city_id = c.city_id 
                    WHERE l.status = 1 $where_clause";
    $count_result = mysqli_query($con, $count_query);
    $total_row = mysqli_fetch_assoc($count_result);
    $total_labs = $total_row['total'];
    $total_pages = ceil($total_labs / $per_page);

    // Fetch labs with pagination
    $query = "SELECT l.*, c.city_name 
             FROM laboratories l 
             LEFT JOIN cities c ON l.city_id = c.city_id 
             WHERE l.status = 1 $where_clause
             ORDER BY l.lab_name ASC
             LIMIT $per_page OFFSET $offset";
    
    $result = mysqli_query($con, $query);
?>

<body>

    <!-- Navbar -->
    <?php include BASE_PATH.'/includes/menu.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container position-relative">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">Our Laboratories</h1>
                    <p class="lead mb-0" data-aos="fade-up" data-aos-delay="200">Find quality diagnostic laboratories near you</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Laboratories Section -->
    <section class="section-padding">
        <div class="container">
            <!-- Filter Section -->
            <div class="filter-section" data-aos="fade-up">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Search by Laboratory Name</label>
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Enter laboratory name...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Filter by City</label>
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
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn search-btn">
                                    <i class="fas fa-search me-2"></i>Search Laboratories
                                </button>
                                <a href="laboratories" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo me-2"></i>Reset All
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Laboratories Grid -->
            <div class="laboratories-grid" id="laboratoriesContainer">
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $laboratory_image = !empty($row['laboratory_pic']) ? BASE_URL.'admin/inc/uploads/laboratories/'.$row['laboratory_pic'] : BASE_URL.'admin/inc/uploads/default/lab.jpg';
                        ?>
                        <div class="laboratory-card" data-aos="fade-up">
                            <div class="laboratory-img-wrapper">
                                <img src="<?php echo $laboratory_image; ?>" alt="<?php echo $row['lab_name']; ?>">
                            </div>
                            <div class="laboratory-info">
                                <h3 class="laboratory-name" style="text-align: center;"><?php echo $row['lab_name']; ?></h3>
                                
                                <div class="laboratory-contact">
                                    <div class="contact-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo $row['city_name']; ?></span>
                                    </div>
                                    <?php if(!empty($row['lab_phone'])) { ?>
                                    <div class="contact-item">
                                        <i class="fas fa-phone"></i>
                                        <span><?php echo $row['lab_phone']; ?></span>
                                    </div>
                                    <?php } ?>
                                    <?php if(!empty($row['lab_email'])) { ?>
                                    <div class="contact-item">
                                        <i class="fas fa-envelope"></i>
                                        <span><?php echo $row['lab_email']; ?></span>
                                    </div>
                                    <?php } ?>
                                </div>
                                
                                <?php if(!empty($row['lab_address'])) { ?>
                                <div class="laboratory-address mb-2">
                                    <i class="fas fa-location-dot me-1 text-primary"></i>
                                    <span class="text-muted small"><?php echo $row['lab_address']; ?></span>
                                </div>
                                <?php } ?>
                                
                                <a class="btn btn-appointment w-100" href="lab-detail?lab_id=<?php echo $row['lab_id']; ?>">
                                    <i class="fas fa-calendar-check me-1"></i>Detail
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="col-12">
                        <div class="no-laboratories">
                            <i class="fas fa-flask"></i>
                            <h3>No Laboratories Found</h3>
                            <p class="text-muted">No laboratories match your search criteria. Please try different filters.</p>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <nav aria-label="Laboratories pagination">
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
                        Showing <?php echo ($offset + 1); ?>-<?php echo min($offset + $per_page, $total_labs); ?> of <?php echo $total_labs; ?> laboratories
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
        function viewLaboratoryDetails(laboratoryId) {
            // You can implement this function to show laboratory details
            // For now, just show an alert or redirect to details page
            alert('Laboratory ID: ' + laboratoryId + ' - Details page coming soon!');
        }
    </script>

    <style>
        .laboratories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .laboratory-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .laboratory-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .laboratory-img-wrapper {
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .laboratory-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .laboratory-card:hover .laboratory-img-wrapper img {
            transform: scale(1.1);
        }

        .laboratory-info {
            padding: 25px;
        }

        .laboratory-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-blue);
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .laboratory-contact {
            margin-bottom: 20px;
        }

        .laboratory-address {
            margin-bottom: 20px;
        }

        .no-laboratories {
            text-align: center;
            padding: 60px 20px;
            color: var(--dark);
        }

        .no-laboratories i {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .no-laboratories h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .laboratories-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
        .search-btn{
            background: var(--gradient);
            color: #fff;
            font-weight: 600;
        }
    </style>
</body>
</html>