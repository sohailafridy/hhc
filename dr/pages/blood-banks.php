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
    $where_clause = ' AND bb.approve = 1';
    if (!empty($search)) {
        $where_clause .= " AND bb.bb_name LIKE '%$search%' ";
    }
    if (!empty($city) && $city !=0) {
        $where_clause .= " AND bb.city_id = '$city' ";
    }

    // Count total blood banks for pagination
    $count_query = "SELECT COUNT(*) as total 
                    FROM blood_bank bb 
                    LEFT JOIN cities c ON bb.city_id = c.city_id 
                    WHERE bb.status = 1 $where_clause";
    $count_result = mysqli_query($con, $count_query);
    $total_row = mysqli_fetch_assoc($count_result);
    $total_blood_banks = $total_row['total'];
    $total_pages = ceil($total_blood_banks / $per_page);

    // Fetch blood banks with pagination
    $query = "SELECT bb.*, c.city_name 
             FROM blood_bank bb 
             LEFT JOIN cities c ON bb.city_id = c.city_id 
             WHERE bb.status = 1 $where_clause
             ORDER BY bb.bb_name ASC
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
                            <label class="form-label text-muted small">Search by Blood Bank Name</label>
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Enter blood bank name...">
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
                                    <i class="fas fa-search me-2 "></i>Search Blood Banks
                                </button>
                                <a href="blood-banks" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo me-2"></i>Reset All
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Blood Banks Grid -->
            <div class="blood-banks-grid" id="bloodBanksContainer">
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $blood_bank_image = !empty($row['bb_pic']) ? BASE_URL.'admin/inc/uploads/blood-banks/'.$row['bb_pic'] : BASE_URL.'admin/inc/uploads/default/bb.jpg';
                        ?>
                        <div class="blood-bank-card" data-aos="fade-up">
                            <div class="blood-bank-img-wrapper">
                                <img src="<?php echo $blood_bank_image; ?>" alt="<?php echo $row['bb_name']; ?>">
                            </div>
                            <div class="blood-bank-info">
                                <h3 class="blood-bank-name txt-color"><?php echo $row['bb_name']; ?></h3>
                                
                                <div class="blood-bank-contact">
                                    <div class="contact-item">
                                        <i class="fas fa-map-marker-alt txt-color"></i>
                                        <span><?php echo $row['city_name']; ?></span>
                                    </div>
                                    <?php if(!empty($row['bb_contact'])) { ?>
                                    <div class="contact-item">
                                        <i class="fas fa-phone"></i>
                                        <span class="txt-color"><?php echo $row['bb_contact']; ?></span>
                                    </div>
                                    <?php } ?>
                                </div>
                                
                                <?php if(!empty($row['bb_address'])) { ?>
                                <div class="blood-bank-address mb-2">
                                    <i class="fas fa-location-dot me-1 text-primary"></i>
                                    <span class="text-muted small txt-color"><?php echo $row['bb_address']; ?></span>
                                </div>
                                <?php } ?>
                                
                                <a class="btn btn-appointment w-100" href="blood-bank-detail?blood_bankid=<?php echo $row['bb_id']; ?>">
                                    <i class="fas fa-calendar-check me-1"></i>Detail
                                </a>    
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="col-12">
                        <div class="no-blood-banks">
                            <i class="fas fa-tint"></i>
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

    <style>
        .search-btn{
            background: var(--gradient);
            color: #fff;
            font-weight: 600;
        }
        .blood-banks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .blood-bank-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 250, 0.9));
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 
                0 20px 40px rgba(15, 23, 42, 0.08),
                0 8px 16px rgba(79, 172, 254, 0.12);
            transition: all 0.4s ease;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            position: relative;
        }

        .blood-bank-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(79, 172, 254, 0.05) 0%, transparent 70%);
            pointer-events: none;
            transition: transform 0.6s ease;
        }

        .blood-bank-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 
                0 30px 60px rgba(15, 23, 42, 0.12),
                0 15px 30px rgba(79, 172, 254, 0.2);
            border-color: rgba(79, 172, 254, 0.3);
        }

        .blood-bank-card:hover::before {
            transform: translate(-20%, -20%) rotate(45deg);
        }

        .blood-bank-img-wrapper {
            height: 200px;
            overflow: hidden;
            position: relative;
            border-radius: 24px;
            margin-bottom: 20px;
            box-shadow: 
                0 15px 35px rgba(79, 172, 254, 0.2),
                0 8px 16px rgba(0, 0, 0, 0.1);
            border: 4px solid rgba(255, 255, 255, 0.9);
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            padding: 3px;
            transition: all 0.4s ease;
        }

        .blood-bank-card:hover .blood-bank-img-wrapper {
            transform: scale(1.05) rotate(2deg);
            box-shadow: 
                0 20px 40px rgba(79, 172, 254, 0.3),
                0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .blood-bank-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 20px;
            transition: transform 0.4s ease;
        }

        .blood-bank-card:hover .blood-bank-img-wrapper img {
            transform: scale(1.05);
        }

        .blood-bank-info {
            padding: 30px;
            position: relative;
            z-index: 2;
        }

        .blood-bank-name {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: all 0.3s ease;
        }

        .blood-bank-card:hover .blood-bank-name {
            transform: translateY(-2px);
        }

        .blood-bank-contact {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 8px;
            padding: 8px 15px;
            background: rgba(79, 172, 254, 0.08);
            border-radius: 12px;
            border: 1px solid rgba(79, 172, 254, 0.15);
            font-size: 0.9rem;
            transition: all 0.3s ease;
            color: var(--dark-blue);
        }

        .blood-bank-card:hover .contact-item {
            background: rgba(79, 172, 254, 0.12);
            border-color: rgba(79, 172, 254, 0.25);
            transform: translateX(5px);
        }

        .contact-item i {
            width: 20px;
            margin-right: 0;
            color: var(--primary);
        }

        .blood-bank-address {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            background: rgba(79, 172, 254, 0.08);
            border-radius: 12px;
            border: 1px solid rgba(79, 172, 254, 0.15);
            font-size: 0.9rem;
            transition: all 0.3s ease;
            color: var(--dark-blue);
            margin-bottom: 20px;
        }

        .blood-bank-card:hover .blood-bank-address {
            background: rgba(79, 172, 254, 0.12);
            border-color: rgba(79, 172, 254, 0.25);
            transform: translateX(5px);
        }

        .blood-bank-address i {
            width: 20px;
            margin-right: 0;
            color: var(--primary);
        }

        .btn-appointment {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
            position: relative;
            overflow: hidden;
            text-decoration: none;
        }

        .btn-appointment::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.1) 100%);
            transition: left 0.3s ease;
        }

        .btn-appointment:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(79, 172, 254, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-appointment:hover::before {
            left: 100%;
        }

        .no-blood-banks {
            text-align: center;
            padding: 60px 20px;
            color: var(--dark);
        }

        .no-blood-banks i {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .no-blood-banks h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .blood-banks-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
        .txt-color{
            color: var(--dark-blue);
        }
    </style>
</body>
</html>