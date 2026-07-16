<?php include '../includes/header.php'; ?>
<?php
    // Get Kohat city ID
    // $kohat_result = mysqli_query($con, "SELECT city_id FROM cities WHERE city_name = '$city'");
    // $kohat_city_data = mysqli_fetch_assoc($kohat_result);
    // $cityid = $kohat_city_data ? $kohat_city_data['city_id'] : 0;
    
   
    
    // Handle search and filters
    $search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
    $city = isset($_GET['city']) ? (int)$_GET['city'] : $city_id; // Default to Kohat if not set

    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;

    // Build WHERE clause
    $where_clause = '';
    $where_clause = ' AND h.approve = 1';
    if (!empty($search)) {
        $where_clause .= " AND h.hospital_name LIKE '%$search%' ";
    }
    if (!empty($city) && $city !=0) {
        $where_clause .= " AND h.city_id = '$city' ";
    }

    // Count total hospitals for pagination
    $count_query = "SELECT COUNT(*) as total 
                    FROM hospitals h 
                    LEFT JOIN cities c ON h.city_id = c.city_id 
                    WHERE h.status = 1 $where_clause";
    $count_result = mysqli_query($con, $count_query);
    $total_row = mysqli_fetch_assoc($count_result);
    $total_hospitals = $total_row['total'];
    $total_pages = ceil($total_hospitals / $per_page);

    // Fetch hospitals with pagination
    $query = "SELECT h.*, c.city_name 
             FROM hospitals h 
             LEFT JOIN cities c ON h.city_id = c.city_id 
             WHERE h.status = 1 $where_clause
             ORDER BY h.hospital_name ASC
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
                    <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">Our Hospitals</h1>
                    <p class="lead mb-0" data-aos="fade-up" data-aos-delay="200">Find quality healthcare facilities near you</p>
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
                            <label class="form-label text-muted small">Search by Hospital Name</label>
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Enter hospital name...">
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
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Search Hospitals
                                </button>
                                <a href="hospitals" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo me-2"></i>Reset All
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Hospitals Grid -->
            <div class="hospitals-grid" id="hospitalsContainer">
                <?php
                $hospital_card_colors = ['#fce4ec', '#ffebee', '#f3e5f5', '#fff9c4', '#e1f5fe', '#e0f2f1'];
                $hospital_index = 0;
                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $hosp_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE entity_id='". $row['entity_id'] ."'");
                        $hosp_stars = mysqli_fetch_assoc($hosp_stars_q);
                        $hosp_stars = $hosp_stars['stars'];
                        $hospital_rating = $hosp_stars ? number_format((float)$hosp_stars, 1) : 'New';
                        $hospital_bg = $hospital_card_colors[$hospital_index % count($hospital_card_colors)];
                        $hospital_index++;
                        ?>
                        <div class="speciality-card hospital-mini-card" data-aos="fade-up">
                            <div class="speciality-card-body" style="background-color: <?php echo $hospital_bg; ?>;">
                                <div class="speciality-icon">
                                    <i class="fas fa-hospital-alt"></i>
                                </div>
                                <h4 class="speciality-title"><?php echo htmlspecialchars($row['hospital_name']); ?></h4>
                                <p class="hospital-mini-location">
                                    <i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($row['city_name']); ?>
                                </p>
                                <div class="hospital-mini-rating">
                                    <i class="fas fa-star text-warning"></i><?php echo $hospital_rating; ?> Rating
                                </div>
                                <a href="hospital-detail?hospital_id=<?php echo $row['hospital_id']; ?>" class="speciality-btn">
                                    <i class="fas fa-arrow-right"></i>
                                    DETAILS
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="col-12">
                        <div class="no-hospitals">
                            <i class="fas fa-hospital"></i>
                            <h3>No Hospitals Found</h3>
                            <p class="text-muted">No hospitals match your search criteria. Please try different filters.</p>
                        </div>
                    </div>
                    <?php
                }
                ?>
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

    <style>
        .hospitals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 24px;
            margin-top: 40px;
        }

        /* Hospital card — matches "Top Hospitals" speciality cards */
        .speciality-card {
            transition: all 0.3s ease;
            height: 100%;
        }

        .speciality-card-body {
            border-radius: 20px;
            padding: 30px 20px;
            text-align: center;
            height: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }

        .speciality-card:hover .speciality-card-body {
            transform: translateY(-5px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.15);
        }

        .speciality-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        .speciality-card:hover .speciality-icon {
            transform: scale(1.1);
            background: rgba(255, 255, 255, 1);
        }

        .hospital-mini-card .speciality-icon i {
            font-size: 1.6rem;
            color: #e74c3c;
        }

        .speciality-title {
            color: #2c3e50;
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 12px;
            transition: color 0.3s ease;
            line-height: 1.3;
        }

        .speciality-card:hover .speciality-title {
            color: #e74c3c;
        }

        .hospital-mini-location {
            color: #5f6f81;
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 10px;
            line-height: 1.25;
        }

        .hospital-mini-location i,
        .hospital-mini-rating i {
            margin-right: 6px;
        }

        .hospital-mini-rating {
            color: #2c3e50;
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 18px;
        }

        .speciality-btn {
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid transparent;
            border-radius: 999px;
            padding: 8px 20px;
            font-size: 0.85rem;
            font-weight: 700;
            color: #e74c3c;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06);
        }

        .speciality-btn i {
            font-size: 0.85rem;
            transition: transform 0.3s ease;
        }

        .speciality-card:hover .speciality-btn {
            background: #e74c3c;
            color: #fff;
            border-color: #e74c3c;
        }

        .speciality-card:hover .speciality-btn i {
            transform: translateX(3px);
        }

        .no-hospitals {
            text-align: center;
            padding: 60px 20px;
            color: var(--dark);
        }

        .no-hospitals i {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .no-hospitals h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .hospitals-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
</body>
</html>