<?php include '../includes/header.php'; ?>

<?php
   
    
    // Handle search and filters
    $search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
    $hospital = isset($_GET['hospital']) ? (int)$_GET['hospital'] : '';
    $specialization = isset($_GET['specialization']) ? (int)$_GET['specialization'] : '';
    $city = isset($_GET['city']) ? (int)$_GET['city'] : $city_id; // Default to Kohat if not set
    $lady_doctor = 0;
    if(isset($_GET['lady_doctor']) && $_GET['lady_doctor'] == 1){
        $lady_doctor = $_GET['lady_doctor'];
    }

    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 12;
    $offset = ($page - 1) * $per_page;




// Fetch doctor categories and types for specialization dropdown
$categories_query = "SELECT dc.dr_cat_id, dc.cat_name, dct.dr_cat_type_id, dct.type 
                   FROM dr_categories dc 
                   LEFT JOIN dr_cat_types dct ON dc.dr_cat_id = dct.dr_cat_id 
                   ORDER BY dc.cat_name, dct.type";
$categories_result = mysqli_query($con, $categories_query);
    // Group categories and types
$categories_data = [];
if ($categories_result) {
    while ($row = mysqli_fetch_assoc($categories_result)) {
        if (!isset($categories_data[$row['dr_cat_id']])) {
            $categories_data[$row['dr_cat_id']] = [
                'cat_name' => $row['cat_name'],
                'types' => []
            ];
        }
        if ($row['dr_cat_type_id']) {
            $categories_data[$row['dr_cat_id']]['types'][] = [
                'dr_cat_type_id' => $row['dr_cat_type_id'],
                'type' => $row['type']
            ];
        }
    }
}
?>
<style>
  /* detail btns */
      .btn-style {
        background: linear-gradient(135deg, var(--primary) 50%, var(--accent) 50%);
        border: none;
        border-radius: 25px;
        padding: 8px 20px;
        font-size: 0.85rem;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        text-decoration: none;
        display: inline-block;
    }

    .btn-style::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.1) 100%);
        transition: left 0.3s ease;
    }

    .btn-style:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(79, 172, 254, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-style:hover::before {
        left: 100%;
    }

    .btn-style:active {
        transform: translateY(0);
        box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
    }
    .txt-color{
        color: var(--dark-blue);
    }

    /* Modern Doctor Card Styles */
    .doctor-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 250, 0.9));
        border-radius: 24px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 
            0 20px 40px rgba(15, 23, 42, 0.08),
            0 8px 16px rgba(79, 172, 254, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(20px);
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
    }

    .doctor-card::before {
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

    .doctor-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 
            0 30px 60px rgba(15, 23, 42, 0.12),
            0 15px 30px rgba(79, 172, 254, 0.2);
        border-color: rgba(79, 172, 254, 0.3);
    }

    .doctor-card:hover::before {
        transform: translate(-20%, -20%) rotate(45deg);
    }

    .doctor-img-wrapper {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto 20px;
        border-radius: 50%;
        overflow: hidden;
        box-shadow: 
            0 15px 35px rgba(79, 172, 254, 0.2),
            0 8px 16px rgba(0, 0, 0, 0.1);
        border: 4px solid rgba(255, 255, 255, 0.9);
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        padding: 3px;
        transition: all 0.4s ease;
    }

    .doctor-card:hover .doctor-img-wrapper {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 
            0 20px 40px rgba(79, 172, 254, 0.3),
            0 10px 20px rgba(0, 0, 0, 0.15);
    }

    .doctor-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        transition: transform 0.4s ease;
    }

    .doctor-card:hover .doctor-img-wrapper img {
        transform: scale(1.05);
    }

    .doctor-info {
        text-align: center;
        position: relative;
        z-index: 2;
    }

    .doctor-name {
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 8px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        transition: all 0.3s ease;
    }

    .doctor-card:hover .doctor-name {
        transform: translateY(-2px);
    }

    .doctor-spec {
        display: inline-block;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        color: white;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 15px;
        box-shadow: 0 4px 12px rgba(79, 172, 254, 0.3);
        transition: all 0.3s ease;
    }

    .doctor-card:hover .doctor-spec {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(79, 172, 254, 0.4);
    }

    .doctor-exp {
        color: var(--dark-blue);
        font-weight: 500;
        margin-bottom: 15px;
        font-size: 0.95rem;
    }

    .doctor-contact {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 20px;
    }

    .contact-item {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 8px 15px;
        background: rgba(79, 172, 254, 0.08);
        border-radius: 12px;
        border: 1px solid rgba(79, 172, 254, 0.15);
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .doctor-card:hover .contact-item {
        background: rgba(79, 172, 254, 0.12);
        border-color: rgba(79, 172, 254, 0.25);
        transform: translateX(5px);
    }

    .doctor-hospital {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-bottom: 20px;
        font-size: 0.9rem;
    }

    .doctor-detail-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        color: white;
        padding: 12px 24px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
        position: relative;
        overflow: hidden;
    }

    .doctor-detail-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.1) 100%);
        transition: left 0.3s ease;
    }

    .doctor-card:hover .doctor-detail-btn {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(79, 172, 254, 0.4);
    }

    .doctor-card:hover .doctor-detail-btn::before {
        left: 100%;
    }

 
</style>
<body>

    <!-- Navbar -->
    <?php include BASE_PATH.'/includes/menu.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container position-relative">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">Our Expert Doctors</h1>
                    <p class="lead mb-0" data-aos="fade-up" data-aos-delay="200">Meet our team of qualified medical professionals</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Doctors Section -->
    <section class="section-padding">
        <div class="container">
            <!-- Filter Section -->
            <div class="filter-section" data-aos="fade-up">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Search by Name</label>
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Enter doctor name...">
                        </div>
                        <div class="col-md-3">
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
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Filter by Hospital</label>
                            <select class="form-select" name="hospital" id="hospitalSelect">
                                <option value="">All Hospitals</option>
                                <?php
                                if($city !=0){
                                    $hospital_query = "SELECT DISTINCT h.hospital_id, h.hospital_name 
                                                FROM hospitals h 
                                                WHERE h.status = 1 
                                                AND h.city_id = '$city'
                                                
                                                ORDER BY h.hospital_name ASC"; 
                                }else{
                                    $hospital_query = "SELECT DISTINCT h.hospital_id, h.hospital_name 
                                                FROM hospitals h 
                                                WHERE h.status = 1 
                                                ORDER BY h.hospital_name ASC";
                                }
                                 
                                $hospital_result = mysqli_query($con, $hospital_query);
                                while($hospital_row = mysqli_fetch_assoc($hospital_result)) {
                                    $selected = ($hospital == $hospital_row['hospital_id']) ? 'selected' : '';
                                    echo '<option value="'.$hospital_row['hospital_id'].'" '.$selected.'>'.$hospital_row['hospital_name'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Filter by Specialization</label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="specializationSearch" placeholder="Search specialization..." autocomplete="off">
                                <input type="hidden" name="specialization" id="specializationValue" value="<?php echo $specialization; ?>">
                                <div class="position-absolute w-100 bg-white border border-top-0" id="specializationDropdown" style="display: none; z-index: 1000; max-height: 200px; overflow-y: auto;">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Additional Filters</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="lady_doctor" name="lady_doctor" value="1" <?php echo ($lady_doctor) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="lady_doctor">
                                    <i class="fas fa-venus me-1 text-pink"></i> Lady Doctors Only
                                </label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-style">
                                    <i class="fas fa-search me-2"></i>Search Doctors
                                </button>
                                <a href="doctors" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo me-2"></i>Reset All
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <script>
                // City-Hospital dependency
                document.getElementById('citySelect').addEventListener('change', function() {
                    var cityId = this.value;
                    var hospitalSelect = document.getElementById('hospitalSelect');
                    
                    // Clear current hospital options
                    hospitalSelect.innerHTML = '<option value="">All Hospitals</option>';
                    
                    if (cityId) {
                        // Fetch hospitals for selected city
                        fetch('get_hospitals.php?city_id=' + cityId)
                            .then(response => response.text())
                            .then(data => {
                                hospitalSelect.innerHTML = '<option value="">All Hospitals</option>' + data;
                            })
                            .catch(error => {
                                console.error('Error fetching hospitals:', error);
                            });
                    }
                });

                // Specialization Search functionality
                const specializationSearch = document.getElementById('specializationSearch');
                const specializationValue = document.getElementById('specializationValue');
                const specializationDropdown = document.getElementById('specializationDropdown');
                let allOptions = [];

                // Initialize options
                function initializeSpecializationOptions() {
                    allOptions = [];
                    
                    // Build options from PHP data
                    <?php foreach ($categories_data as $category_id => $category): ?>
                        <?php foreach ($category['types'] as $type): ?>
                            allOptions.push({
                                value: '<?php echo $type['dr_cat_type_id']; ?>',
                                text: '<?php echo addslashes($type['type']); ?>',
                                dataText: '<?php echo addslashes($category['cat_name'] . ' - ' . $type['type']); ?>'
                            });
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    
                    // Set initial value if selected
                    const currentSpecialization = '<?php echo $specialization; ?>';
                    if (currentSpecialization) {
                        const selectedOption = allOptions.find(option => option.value === currentSpecialization);
                        if (selectedOption) {
                            specializationSearch.value = selectedOption.text;
                        }
                    }
                }

                // Filter options based on search
                function filterSpecializations(searchTerm) {
                    const filtered = allOptions.filter(option => 
                        option.text.toLowerCase().includes(searchTerm.toLowerCase()) ||
                        option.dataText.toLowerCase().includes(searchTerm.toLowerCase())
                    );
                    
                    if (searchTerm === '') {
                        specializationDropdown.style.display = 'none';
                        return;
                    }
                    
                    if (filtered.length > 0) {
                        let html = '';
                        filtered.forEach(option => {
                            html += `<div class="dropdown-item py-2 px-3" style="cursor: pointer;" data-value="${option.value}" data-text="${option.text}">
                                        <div class="fw-medium">${option.text}</div>
                                        <small class="text-muted">${option.dataText}</small>
                                    </div>`;
                        });
                        specializationDropdown.innerHTML = html;
                        specializationDropdown.style.display = 'block';
                    } else {
                        specializationDropdown.innerHTML = '<div class="p-3 text-muted">No specializations found</div>';
                        specializationDropdown.style.display = 'block';
                    }
                }

                // Handle search input
                specializationSearch.addEventListener('input', function() {
                    filterSpecializations(this.value);
                });

                // Handle dropdown item click
                specializationDropdown.addEventListener('click', function(e) {
                    const item = e.target.closest('.dropdown-item');
                    if (item) {
                        const value = item.getAttribute('data-value');
                        const text = item.getAttribute('data-text');
                        
                        specializationSearch.value = text;
                        specializationValue.value = value;
                        specializationDropdown.style.display = 'none';
                    }
                });

                // Hide dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.position-relative')) {
                        specializationDropdown.style.display = 'none';
                    }
                });

                // Initialize on page load
                document.addEventListener('DOMContentLoaded', function() {
                    initializeSpecializationOptions();
                });
            </script>

            <!-- Doctors Grid -->
            <div class="doctors-grid" id="doctorsContainer">
                <?php
                $where_clause = '';
                $where_clause = ' AND d.approve = 1';
                if (!empty($search)) {
                    $where_clause .= " AND d.doctor_name LIKE '%$search%' ";
                }
                if (!empty($hospital)) {
                    $where_clause .= " AND d.hospital_id = '$hospital' ";
                }
                if (!empty($specialization)) {
                    $where_clause .= " AND d.cat_type_id = '$specialization' ";
                }
                if (!empty($city) && $city !=0) {
                    $where_clause .= " AND d.city_id = '$city' ";
                }
                
               
                if (!empty($lady_doctor) && $lady_doctor == 1) {
                    $where_clause .= " AND d.gender = 'Female' ";
                }
                   $query = "SELECT d.*, dct.type as doctor_of, c.city_name, h.hospital_name 
                         FROM doctors d 
                         LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id 
                         LEFT JOIN cities c ON d.city_id = c.city_id 
                         LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id 
                         WHERE d.status = 1 $where_clause
                         ORDER BY d.doctor_name ASC
                         LIMIT $per_page OFFSET $offset";

                    $all_doctors = mysqli_query($con,"SELECT d.*, dct.type as doctor_of, c.city_name, h.hospital_name 
                         FROM doctors d 
                         LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id 
                         LEFT JOIN cities c ON d.city_id = c.city_id 
                         LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id 
                         WHERE d.status = 1 $where_clause");
                    $total_doctors = mysqli_num_rows($all_doctors);
                    $total_pages = ceil($total_doctors / $per_page);
                $result = mysqli_query($con, $query);
                
                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $doctor_image = !empty($row['doctor_pic']) ? BASE_URL.'admin/inc/uploads/doctors/'.$row['doctor_pic'] : BASE_URL.'admin/inc/uploads/default/doctor.jpg';
                        ?>
                        <div class="doctor-card" data-aos="fade-up">
                            <div class="doctor-img-wrapper">
                                <img src="<?php echo $doctor_image; ?>" alt="<?php echo $row['doctor_name']; ?>">
                            </div>
                            <div class="doctor-info">
                                <?php
                                if($row['gender'] == 'Female') { ?>
                                <h3 class="doctor-name txt-color">Lady Dr. <?php echo $row['doctor_name']; ?></h3>
                                <?php } else { ?>
                                <h3 class="doctor-name txt-color">Dr. <?php echo $row['doctor_name']; ?></h3>
                                <?php } ?>
                                <span class="doctor-spec txt-color"><?php echo $row['doctor_of']; ?></span>
                                
                                
                                <div class="doctor-contact">
                                    <div class="contact-item txt-color">
                                        <i class="fas fa-phone txt-color"></i>
                                        <span><?php echo $row['doctor_phone']; ?></span>
                                    </div>
                                    
                                </div>
                                
                                
                                <div class="doctor-hospital mb-2">
                                    <i class="fas fa-hospital me-1 text-primary"></i>
                                    <span class="text-muted small txt-color"><?php echo $row['hospital_name']; ?></span>
                                    <i class="fas fa-map-marker-alt txt-color"></i>
                                    <span class="txt-color"><?php echo $row['city_name']; ?></span>
                                </div>
                                
                                
                                <a href="doctor-detail?doctor_id=<?php echo $row['doctor_id']; ?>" class="doctor-detail-btn">
                                    <i class="fas fa-calendar-check"></i>View Details
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="col-12">
                        <div class="no-doctors">
                            <i class="fas fa-user-md"></i>
                            <h3>No Doctors Found</h3>
                            <p class="text-muted">No doctors match your search criteria. Please try different filters.</p>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <nav aria-label="Doctors pagination">
                    <ul class="pagination">
                        <!-- Previous Button -->
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&hospital=<?php echo $hospital; ?>&specialization=<?php echo $specialization; ?>&city=<?php echo $city; ?>&lady_doctor=<?php echo $lady_doctor; ?>">
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
                            echo '<li class="page-item"><a class="page-link" href="?page=1&search='.urlencode($search).'&hospital='.$hospital.'&specialization='.$specialization.'&city='.$city.'&lady_doctor='.$lady_doctor.'">1</a></li>';
                            if ($start_page > 2) {
                                echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                            }
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            if ($i == $page) {
                                echo '<li class="page-item active"><a class="page-link" href="#">'.$i.'</a></li>';
                            } else {
                                echo '<li class="page-item"><a class="page-link" href="?page='.$i.'&search='.urlencode($search).'&hospital='.$hospital.'&specialization='.$specialization.'&city='.$city.'&lady_doctor='.$lady_doctor.'">'.$i.'</a></li>';
                            }
                        }
                        
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'&search='.urlencode($search).'&hospital='.$hospital.'&specialization='.$specialization.'&city='.$city.'&lady_doctor='.$lady_doctor.'">'.$total_pages.'</a></li>';
                        }
                        ?>

                        <!-- Next Button -->
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&hospital=<?php echo $hospital; ?>&specialization=<?php echo $specialization; ?>&city=<?php echo $city; ?>&lady_doctor=<?php echo $lady_doctor; ?>">
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
                        Showing <?php echo ($offset + 1); ?>-<?php echo min($offset + $per_page, $total_doctors); ?> of <?php echo $total_doctors; ?> doctors
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
    
    $('#hospitalSelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search hospital...',
        allowClear: true,
        width: '100%'
    });


});
</script>