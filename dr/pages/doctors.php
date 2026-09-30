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
<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-list.css">
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
                    <a href="<?php echo BASE_URL; ?>/pages/outside/add-doctor.php" class="btn btn-primary btn-lg add-new-doctor" style='display: none;'>Add New Doctor</a>
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
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label text-muted small">Search by Name</label>
                            <div class="input-with-icon">
                                <i class="fas fa-search input-icon"></i>
                                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Enter doctor name...">
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label text-muted small">Filter by City</label>
                            <div class="input-with-icon">
                                <i class="fas fa-map-marker-alt input-icon"></i>
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
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label text-muted small">Filter by Hospital</label>
                            <div class="input-with-icon">
                                <i class="fas fa-hospital input-icon"></i>
                                <select class="form-select" name="hospital" id="hospitalSelect">
                                    <option value="">All Hospitals</option>
                                    <?php
                                    if($city !=0){
                                        $hospital_query = "SELECT DISTINCT h.hospital_id, h.hospital_name 
                                                    FROM hospitals h 
                                                    LEFT JOIN users u ON u.user_id = h.user_id
                                                    WHERE u.status = 1 
                                                    AND h.city_id = '$city'
                                                    
                                                    ORDER BY h.hospital_name ASC"; 
                                    }else{
                                        $hospital_query = "SELECT DISTINCT h.hospital_id, h.hospital_name 
                                                    FROM hospitals h 
                                                    LEFT JOIN users u ON u.user_id = h.user_id
                                                    WHERE u.status = 1 
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
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label text-muted small">Filter by Specialization</label>
                            <div class="input-with-icon position-relative">
                                <i class="fas fa-stethoscope input-icon"></i>
                                <input type="text" class="form-control" id="specializationSearch" placeholder="Search specialization..." autocomplete="off">
                                <input type="hidden" name="specialization" id="specializationValue" value="<?php echo $specialization; ?>">
                                <div class="position-absolute w-100 bg-white border border-top-0" id="specializationDropdown" style="display: none; z-index: 1000; max-height: 200px; overflow-y: auto;">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Additional Filters</label>
                            <div class="form-check custom-checkbox-toggle">
                                <input class="form-check-input" type="checkbox" id="lady_doctor" name="lady_doctor" value="1" <?php echo ($lady_doctor) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="lady_doctor">
                                    <i class="fas fa-venus text-pink"></i> Lady Doctors Only
                                </label>
                            </div>
                        </div>
                        <div class="col-md-12 mt-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-style">
                                    <i class="fas fa-search me-1"></i>Search Doctors
                                </button>
                                <a href="doctors" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo me-1"></i>Reset All
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
                         LEFT JOIN users u ON u.user_id = d.user_id
                         WHERE u.status = 1 AND d.approve = 1 $where_clause
                         ORDER BY d.doctor_name ASC
                         LIMIT $per_page OFFSET $offset";



                    $all_doctors = mysqli_query($con,"SELECT d.*, dct.type as doctor_of, c.city_name, h.hospital_name 
                         FROM doctors d 
                         LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id 
                         LEFT JOIN cities c ON d.city_id = c.city_id 
                         LEFT JOIN users u ON d.user_id = u.user_id 
                         LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id 
                         WHERE u.status = 1 AND d.approve = 1 $where_clause");

                    $total_doctors = mysqli_num_rows($all_doctors);
                    $total_pages = ceil($total_doctors / $per_page);
                $result = mysqli_query($con, $query);
                
                if (mysqli_num_rows($result) > 0) {
                    $doctor_index = 0;
                    while($row = mysqli_fetch_assoc($result)) {
                        $doctor_index++;
                        $doct_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='". (int)$row['user_id'] ."'");
                        $doct_stars = mysqli_fetch_assoc($doct_stars_q);
                        $doct_stars = $doct_stars['stars'];
                        $doctor_rating = $doct_stars ? number_format((float)$doct_stars, 1) : 'New';
                        
                        $doctor_specialization = !empty($row['doctor_of']) ? $row['doctor_of'] : 'Medical Specialist';
                        $doctor_card_colors = ['#eef6ff', '#f3efff', '#eefbf5', '#fff6e8', '#fceef3', '#edf8ff'];
                        $doctor_bg = $doctor_card_colors[($doctor_index - 1) % count($doctor_card_colors)];
                        ?>
                        <div class="speciality-card doctor-mini-card" data-aos="fade-up">
                            <div class="speciality-card-body" style="background-color: <?php echo $doctor_bg; ?>;">
                                <div class="doctor-mini-media">
                                    <?php if (!empty($row['doctor_pic'])): ?>
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $row['doctor_pic']; ?>" 
                                            alt="<?php echo $row['doctor_name']; ?>" class="img-fluid">
                                    <?php else: ?>
                                        <div class="doctor-mini-icon-wrap">
                                            <i class="fas fa-user-md doctor-mini-icon"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="doctor-mini-content">
                                    <div class="doctor-mini-specialty">
                                        <i class="fas fa-user-md"></i><?php echo htmlspecialchars($doctor_specialization); ?>
                                    </div>
                                    <h4 class="speciality-title"><?php echo htmlspecialchars($row['doctor_name']); ?></h4>
                                    <div class="doctor-mini-meta">
                                        <i class="fas fa-location-dot"></i><?php echo htmlspecialchars($row['city_name']); ?>
                                    </div>
                                    <div class="doctor-mini-rating">
                                        <i class="fas fa-star text-warning"></i><?php echo $doctor_rating; ?> Rating
                                    </div>
                                    <a href="doctor-detail?doctor_id=<?php echo $row['doctor_id']; ?>" class="speciality-btn">
                                        <i class="fas fa-stethoscope"></i>
                                        PROFILE
                                    </a>
                                </div>
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