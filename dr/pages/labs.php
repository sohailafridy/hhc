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
                    LEFT JOIN entities e ON e.entity_id = l.entity_id
                    WHERE e.status = 1 AND l.approve=1 $where_clause";
    $count_result = mysqli_query($con, $count_query);
    $total_row = mysqli_fetch_assoc($count_result);
    $total_labs = $total_row['total'];
    $total_pages = ceil($total_labs / $per_page);

    // Fetch labs with pagination
    $query = "SELECT l.*, c.city_name 
             FROM laboratories l 
             LEFT JOIN cities c ON l.city_id = c.city_id 
             LEFT JOIN entities e ON e.entity_id = l.entity_id
             WHERE e.status = 1 AND l.approve=1 $where_clause
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
                            <label class="form-label"><i class="fas fa-flask"></i> Search by Laboratory Name</label>
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Enter laboratory name...">
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
                                    <i class="fas fa-search"></i> Search Laboratories
                                </button>
                                <a href="laboratories" class="btn-reset-custom">
                                    <i class="fas fa-redo"></i> Reset All
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Laboratories Grid -->
            <div class="row g-3" id="laboratoriesContainer">
                <?php
                $lab_card_colors = ['#e8fff9', '#eef8ff', '#f3efff', '#eefbf5', '#fff6e8', '#edf8ff'];
                $lab_index = 0;
                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $lab_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE entity_id='". $row['entity_id'] ."'");
                        $lab_stars = mysqli_fetch_assoc($lab_stars_q);
                        $lab_stars = $lab_stars['stars'];
                        $lab_rating = $lab_stars ? number_format((float)$lab_stars, 1) : 'New';
                        $lab_bg = $lab_card_colors[$lab_index % count($lab_card_colors)];
                        $lab_index++;
                        ?>
                        <div class="col-lg-3 col-md-4 col-6" data-aos="fade-up" data-aos-delay="<?php echo 100 + ($lab_index * 80); ?>">
                            <div class="speciality-card lab-mini-card">
                                <div class="speciality-card-body" style="background-color: <?php echo $lab_bg; ?>;">
                                    <div class="speciality-icon">
                                        <i class="fas fa-flask"></i>
                                    </div>
                                    <h4 class="speciality-title"><?php echo htmlspecialchars($row['lab_name']); ?></h4>
                                    <p class="hospital-mini-location">
                                        <i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($row['city_name']); ?>
                                    </p>
                                    <div class="hospital-mini-rating">
                                        <i class="fas fa-star text-warning"></i><?php echo $lab_rating; ?> Rating
                                    </div>
                                    <a href="lab-detail?lab_id=<?php echo $row['lab_id']; ?>" class="speciality-btn">
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
                        <div class="no-laboratories text-center py-5">
                            <i class="fas fa-flask fa-3x text-primary mb-3"></i>
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
        /* Premium Filter Section Custom Styling */
        .filter-section {
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(20px) !important;
            border-radius: 24px !important;
            padding: 30px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05), inset 0 1px 0 rgba(255, 255, 255, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.5) !important;
            margin-bottom: 40px !important;
            transition: all 0.3s ease !important;
        }

        .filter-section:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08) !important;
            background: rgba(255, 255, 255, 0.95) !important;
        }

        .filter-section .form-label {
            font-weight: 700 !important;
            color: #4a5568 !important;
            text-transform: uppercase !important;
            font-size: 0.72rem !important;
            letter-spacing: 0.8px !important;
            margin-bottom: 8px !important;
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
        }

        .filter-section .form-label i {
            color: var(--primary) !important;
            font-size: 0.8rem !important;
        }

        .filter-section .form-control {
            border: 2px solid #e2e8f0 !important;
            border-radius: 12px !important;
            padding: 11px 16px !important;
            font-size: 0.9rem !important;
            font-weight: 500 !important;
            color: #2d3748 !important;
            background-color: rgba(255, 255, 255, 0.9) !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02) !important;
            height: 48px !important;
        }

        .filter-section .form-control:focus {
            border-color: var(--primary) !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15), 0 4px 10px rgba(0, 0, 0, 0.05) !important;
            outline: none !important;
        }

        /* Select2 Premium Customization */
        .filter-section .select2-container--bootstrap-5 {
            display: block;
            width: 100% !important;
        }

        .filter-section .select2-container--bootstrap-5 .select2-selection {
            border: 2px solid #e2e8f0 !important;
            border-radius: 12px !important;
            min-height: 48px !important;
            padding: 8px 16px !important;
            font-size: 0.9rem !important;
            font-weight: 500 !important;
            color: #2d3748 !important;
            background-color: rgba(255, 255, 255, 0.9) !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02) !important;
            display: flex;
            align-items: center;
        }

        .filter-section .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .filter-section .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: var(--primary) !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15), 0 4px 10px rgba(0, 0, 0, 0.05) !important;
            outline: none !important;
        }

        .filter-section .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding-left: 0 !important;
            color: #2d3748 !important;
            font-weight: 500 !important;
            line-height: normal !important;
        }

        .filter-section .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear {
            margin-right: 10px !important;
            font-size: 0.9rem !important;
            color: #a0aec0 !important;
        }

        .filter-section .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear:hover {
            color: #e74c3c !important;
        }

        .filter-section .select2-container--bootstrap-5 .select2-selection__arrow {
            position: absolute !important;
            top: 50% !important;
            right: 15px !important;
            transform: translateY(-50%) !important;
            width: auto !important;
            height: auto !important;
        }

        /* Custom Buttons */
        .filter-section .btn-search-custom {
            background: linear-gradient(135deg, var(--primary) 0%, #0b5ed7 100%) !important;
            color: white !important;
            border: none !important;
            border-radius: 12px !important;
            padding: 10px 24px !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
            letter-spacing: 0.5px !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25) !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
        }

        .filter-section .btn-search-custom:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 18px rgba(13, 110, 253, 0.35) !important;
            background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%) !important;
        }

        .filter-section .btn-search-custom i {
            font-size: 0.85rem !important;
        }

        .filter-section .btn-reset-custom {
            background: #ffffff !important;
            color: #4a5568 !important;
            border: 2px solid #e2e8f0 !important;
            border-radius: 12px !important;
            padding: 10px 24px !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
            letter-spacing: 0.5px !important;
            transition: all 0.3s ease !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            text-decoration: none !important;
        }

        .filter-section .btn-reset-custom:hover {
            background: #f7fafc !important;
            color: #2d3748 !important;
            border-color: #cbd5e0 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05) !important;
        }

        .filter-section .btn-reset-custom:hover i {
            transform: rotate(-180deg) !important;
        }

        .filter-section .btn-reset-custom i {
            transition: transform 0.5s ease !important;
            font-size: 0.85rem !important;
        }

        .filter-section .btn-search-custom:active,
        .filter-section .btn-reset-custom:active {
            transform: translateY(0) !important;
        }

        /* Specialities Cards Styles for Laboratories */
        .speciality-card {
            transition: all 0.3s ease;
            height: 100%;
        }

        .speciality-card-body {
            border-radius: 12px;
            padding: 20px 15px;
            text-align: center;
            height: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            min-height: 220px;
        }

        .speciality-card:hover .speciality-card-body {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .speciality-icon {
            width: 50px;
            height: 50px;
            margin: 0 auto 15px;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .speciality-card:hover .speciality-icon {
            transform: scale(1.1);
            background: rgba(255, 255, 255, 1);
        }

        .speciality-icon i {
            font-size: 1.25rem;
            color: #136f63;
        }

        .speciality-title {
            color: #2c3e50;
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 8px;
            transition: color 0.3s ease;
            line-height: 1.3;
        }

        .speciality-card:hover .speciality-title {
            color: #136f63;
        }

        .hospital-mini-location {
            color: #5f6f81;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 8px;
            line-height: 1.25;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .hospital-mini-location i {
            color: #5f6f81;
        }

        .hospital-mini-rating {
            color: #2c3e50;
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .speciality-btn {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid transparent;
            border-radius: 20px;
            padding: 6px 15px;
            font-size: 0.72rem;
            font-weight: 700;
            color: #136f63;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: auto;
        }

        .speciality-btn i {
            font-size: 0.75rem;
            transition: transform 0.3s ease;
        }

        .speciality-card:hover .speciality-btn {
            background: #136f63;
            color: white;
            border-color: #136f63;
        }

        .speciality-card:hover .speciality-btn i {
            transform: scale(1.2) translateX(2px);
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

        @media (max-width: 992px) {
            .speciality-card-body {
                padding: 15px 10px;
                min-height: 200px;
            }
            
            .speciality-icon {
                width: 45px;
                height: 45px;
                margin-bottom: 10px;
            }
            
            .speciality-title {
                font-size: 0.85rem;
            }
            
            .speciality-btn {
                padding: 4px 12px;
                font-size: 0.65rem;
            }
        }

        @media (max-width: 768px) {
            .speciality-card-body {
                padding: 12px 8px;
                min-height: 180px;
            }
            
            .speciality-icon {
                width: 40px;
                height: 40px;
                margin-bottom: 8px;
            }

            .speciality-icon i {
                font-size: 1.1rem;
            }
            
            .speciality-title {
                font-size: 0.8rem;
            }

            .hospital-mini-location,
            .hospital-mini-rating {
                font-size: 0.7rem;
                margin-bottom: 8px;
            }
            
            .speciality-btn {
                padding: 3px 8px;
                font-size: 0.6rem;
            }
        }
    </style>
</body>
</html>