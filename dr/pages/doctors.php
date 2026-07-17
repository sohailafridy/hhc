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

    /* Specialities Cards Styles */
    .speciality-card {
        transition: all 0.3s ease;
        height: 100%;
    }

    .speciality-card-body {
        border-radius: 12px;
        padding: 15px 10px;
        text-align: center;
        height: 100%;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        position: relative;
        overflow: hidden;
    }

    .speciality-card:hover .speciality-card-body {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .speciality-icon {
        width: 45px;
        height: 45px;
        margin: 0 auto 10px;
        background: rgba(255, 255, 255, 0.8);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .speciality-emoji {
        font-size: 1.4rem;
        line-height: 1;
    }

    .speciality-card:hover .speciality-icon {
        transform: scale(1.1);
        background: rgba(255, 255, 255, 1);
    }

    .speciality-title {
        color: #2c3e50;
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 10px;
        transition: color 0.3s ease;
        line-height: 1.2;
    }

    .speciality-card:hover .speciality-title {
        color: #e74c3c;
    }

    .speciality-btn {
        background: rgba(255, 255, 255, 0.9);
        border: 2px solid transparent;
        border-radius: 15px;
        padding: 4px 10px;
        font-size: 0.65rem;
        font-weight: 600;
        color: #e74c3c;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        text-decoration: none;
    }

    .speciality-btn i {
        font-size: 0.7rem;
        transition: transform 0.3s ease;
    }

    .speciality-card:hover .speciality-btn {
        background: #e74c3c;
        color: white;
        border-color: #e74c3c;
    }

    .speciality-card:hover .speciality-btn i {
        transform: scale(1.2);
    }

    .doctor-mini-card .speciality-card-body {
        border: 1px solid rgba(79, 172, 254, 0.15);
        padding: 0;
        overflow: hidden;
    }

    .doctor-mini-media {
        height: 140px;
        background: linear-gradient(135deg, rgba(79, 172, 254, 0.16), rgba(103, 114, 229, 0.16));
        overflow: hidden;
    }

    .doctor-mini-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .doctor-mini-content {
        padding: 14px 10px 15px;
    }

    .doctor-mini-icon-wrap {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.55);
    }

    .doctor-mini-icon {
        font-size: 2.2rem;
        color: #3f7edb;
    }

    .doctor-mini-specialty {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        margin-bottom: 10px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.9);
        color: #315f97;
        font-size: 0.62rem;
        font-weight: 700;
        text-transform: uppercase;
        line-height: 1.2;
    }

    .doctor-mini-specialty i {
        color: #e74c3c;
    }

    .doctor-mini-meta {
        color: #5f6f81;
        font-size: 0.72rem;
        font-weight: 500;
        margin-bottom: 8px;
        line-height: 1.25;
    }

    .doctor-mini-meta i,
    .doctor-mini-rating i {
        margin-right: 4px;
    }

    .doctor-mini-rating {
        color: #2c3e50;
        font-size: 0.7rem;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .doctor-mini-card .speciality-btn {
        color: #315f97;
    }

    .doctor-mini-card:hover .speciality-btn {
        background: #315f97;
        border-color: #315f97;
        color: white;
    }

    @media (max-width: 992px) {
        .speciality-card-body {
            padding: 12px 8px;
        }
        
        .speciality-icon {
            width: 40px;
            height: 40px;
        }
        
        .speciality-emoji {
            font-size: 1.2rem;
        }
        
        .speciality-title {
            font-size: 0.75rem;
        }
        
        .speciality-btn {
            padding: 3px 8px;
            font-size: 0.6rem;
        }
    }

    @media (max-width: 768px) {
        .speciality-card-body {
            padding: 12px 6px;
        }
        
        .speciality-icon {
            width: 35px;
            height: 35px;
        }
        
        .speciality-emoji {
            font-size: 1rem;
        }
        
        .speciality-title {
            font-size: 0.7rem;
        }

        .doctor-mini-specialty {
            font-size: 0.58rem;
            padding: 4px 8px;
        }

        .doctor-mini-media {
            height: 120px;
        }

        .doctor-mini-content {
            padding: 12px 8px 14px;
        }

        .doctor-mini-meta,
        .doctor-mini-rating {
            font-size: 0.65rem;
        }
        
        .speciality-btn {
            padding: 3px 6px;
            font-size: 0.55rem;
        }
    }

    /* Modernized Filter Form Styles */
    .filter-section {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(15px);
        border-radius: 24px;
        padding: 30px;
        box-shadow: 0 15px 35px rgba(15, 23, 42, 0.06), 0 5px 15px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.8);
        margin-bottom: 40px;
    }

    .filter-section .form-label {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 8px;
    }

    .filter-section .input-with-icon {
        position: relative;
    }

    .filter-section .input-with-icon .input-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        z-index: 10;
        font-size: 0.95rem;
        pointer-events: none;
    }

    .filter-section .input-with-icon .form-control,
    .filter-section .input-with-icon .form-select {
        padding-left: 42px !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 14px !important;
        font-size: 0.9rem !important;
        font-weight: 500 !important;
        height: 48px !important;
        transition: all 0.3s ease !important;
        background-color: #ffffff !important;
        color: #334155 !important;
    }

    .filter-section .input-with-icon .form-control:focus,
    .filter-section .input-with-icon .form-select:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1) !important;
    }

    /* Select2 custom input positioning with icon */
    .filter-section .input-with-icon .select2-container--bootstrap-5 .select2-selection {
        padding-left: 42px !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 14px !important;
        height: 48px !important;
        display: flex !important;
        align-items: center !important;
        background-color: #ffffff !important;
        transition: all 0.3s ease !important;
    }

    .filter-section .input-with-icon .select2-container--bootstrap-5.select2-container--focus .select2-selection {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1) !important;
    }

    .filter-section .input-with-icon .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        padding-left: 0px !important;
        color: #334155 !important;
        font-size: 0.9rem !important;
        font-weight: 500 !important;
    }

    .filter-section .input-with-icon .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
        color: #94a3b8 !important;
    }

    /* Custom Checkbox Toggle */
    .custom-checkbox-toggle {
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        padding: 11px 18px;
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01);
        height: 48px;
        margin-top: 0 !important;
    }

    .custom-checkbox-toggle:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .custom-checkbox-toggle .form-check-input {
        width: 18px;
        height: 18px;
        margin-right: 10px;
        border: 1.5px solid #cbd5e1;
        border-radius: 6px;
        cursor: pointer;
        margin-top: 0;
        transition: all 0.2s ease;
    }

    .custom-checkbox-toggle .form-check-input:checked {
        background-color: #e21b7f !important;
        border-color: #e21b7f !important;
    }

    .custom-checkbox-toggle .form-check-label {
        cursor: pointer;
        font-weight: 600;
        color: #475569;
        font-size: 0.9rem;
        user-select: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .text-pink {
        color: #e21b7f !important;
    }

    /* Buttons Modern styles */
    .filter-section .btn-style {
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%) !important;
        border: none !important;
        border-radius: 30px !important;
        padding: 12px 28px !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        color: white !important;
        box-shadow: 0 4px 14px rgba(13, 110, 253, 0.2) !important;
        transition: all 0.3s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        height: 48px;
    }

    .filter-section .btn-style:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 20px rgba(13, 110, 253, 0.3) !important;
        filter: brightness(1.05);
    }

    .filter-section .btn-outline-secondary {
        border: 1.5px solid #cbd5e1 !important;
        background-color: transparent !important;
        color: #64748b !important;
        border-radius: 30px !important;
        padding: 12px 28px !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        text-decoration: none !important;
        height: 48px;
    }

    .filter-section .btn-outline-secondary:hover {
        background-color: #f1f5f9 !important;
        color: #334155 !important;
        border-color: #94a3b8 !important;
        transform: translateY(-2px) !important;
    }

    #specializationDropdown {
        border-radius: 14px !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
        border: 1px solid #e2e8f0 !important;
        margin-top: 5px;
        overflow: hidden;
    }
    #specializationDropdown .dropdown-item {
        padding: 10px 16px;
        transition: background-color 0.2s ease;
    }
    #specializationDropdown .dropdown-item:hover {
        background-color: #f1f5f9;
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
                    <a href="<?php echo BASE_URL; ?>/pages/outside/add-doctor.php" class="btn btn-primary btn-lg">Add New Doctor</a>
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
                                                    LEFT JOIN entities e ON e.entity_id = h.entity_id
                                                    WHERE e.status = 1 
                                                    AND h.city_id = '$city'
                                                    
                                                    ORDER BY h.hospital_name ASC"; 
                                    }else{
                                        $hospital_query = "SELECT DISTINCT h.hospital_id, h.hospital_name 
                                                    FROM hospitals h 
                                                    LEFT JOIN entities e ON e.entity_id = h.entity_id
                                                    WHERE e.status = 1 
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
                         LEFT JOIN entities e ON e.entity_id = d.entity_id
                         WHERE e.status = 1 AND d.approve = 1 $where_clause
                         ORDER BY d.doctor_name ASC
                         LIMIT $per_page OFFSET $offset";



                    $all_doctors = mysqli_query($con,"SELECT d.*, dct.type as doctor_of, c.city_name, h.hospital_name 
                         FROM doctors d 
                         LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id 
                         LEFT JOIN cities c ON d.city_id = c.city_id 
                         LEFT JOIN entities e ON d.entity_id = e.entity_id 
                         LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id 
                         WHERE e.status = 1 AND d.approve = 1 $where_clause");

                    $total_doctors = mysqli_num_rows($all_doctors);
                    $total_pages = ceil($total_doctors / $per_page);
                $result = mysqli_query($con, $query);
                
                if (mysqli_num_rows($result) > 0) {
                    $doctor_index = 0;
                    while($row = mysqli_fetch_assoc($result)) {
                        $doctor_index++;
                        $doct_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE entity_id='". $row['entity_id'] ."'");
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