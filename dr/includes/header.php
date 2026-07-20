<?php include '../config.php'; 
include BASE_PATH.'/tracking.php'; 
$city_id =0 ;
?>
<script>
// Check if we already tried to get location in this session
if(!sessionStorage.getItem('locationAttempted')) {
  sessionStorage.setItem('locationAttempted', 'true');
  
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      function(position) {

        fetch('includes/get-city.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            lat: position.coords.latitude,
            lon: position.coords.longitude
          })
        })
        .then(response => response.text())
        .then(city => {
          console.log("Detected city:", city);
          if(city && city.trim() !== '') {
            location.reload();
          }
        });

      },
      function(error) {
        console.log("Location denied", error);
        // location.reload();
      }
    );
  }
}
</script>
<?php

if(isset($_SESSION['city']) && !empty($_SESSION['city'])) {
        $city = $_SESSION['city'];
        
        $check = mysqli_query($con, "SELECT * FROM cities WHERE city_name = '$city'");
        if(mysqli_num_rows($check) == 0){
            mysqli_query($con, "INSERT INTO cities (city_name) VALUES ('$city')");
            $last_id = $con->insert_id;                 // یہ پراپرٹی last ID دیتی ہے
            $_SESSION['city_id'] = $last_id;
        }else{
            $city_id = mysqli_fetch_assoc($check)['city_id'];
            $_SESSION['city_id'] = $city_id;
        }
}

   $city_id = 0;
if(isset($_SESSION['city_id']) && !empty($_SESSION['city_id'])) {
    $city_id = $_SESSION['city_id'];
}


// // Display city if set in session
// if(isset($_SESSION['city']) && !empty($_SESSION['city'])) {
//     echo "Your city: ".$_SESSION['city'];
//     error_log("City found in session: " . $_SESSION['city']);
//     // exit;
// } else {
//     error_log("No city found in session. Session ID: " . session_id());
//     error_log("Session data: " . print_r($_SESSION, true));
// }

// Handle search and filters
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$hospital = isset($_GET['hospital']) ? (int)$_GET['hospital'] : '';
$specialization = isset($_GET['specialization']) ? (int)$_GET['specialization'] : '';
$city = isset($_GET['city']) ? (int)$_GET['city'] : '';
$doctor_type = isset($_GET['doctor_type']) ? (int)$_GET['doctor_type'] : '';
$experience = isset($_GET['experience']) ? $_GET['experience'] : '';
$lady_doctor = isset($_GET['lady_doctor']) ? 1 : 0;

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query with filters
$where_conditions = ["e.status = 1"];

if (!empty($search)) {
    $where_conditions[] = "(d.doctor_name LIKE '%$search%' OR dct.type LIKE '%$search%')";
}

if (!empty($hospital)) {
    $where_conditions[] = "d.hospital_id = $hospital";
}

if (!empty($specialization)) {
    $where_conditions[] = "d.cat_type_id = $specialization";
}

if (!empty($city)) {
    $where_conditions[] = "d.city_id = $city";
}

if (!empty($doctor_type)) {
    $where_conditions[] = "d.doctor_type = $doctor_type";
}

if (!empty($experience)) {
    if ($experience === '0-5') {
        $where_conditions[] = "d.experience_years <= 5";
    } elseif ($experience === '6-10') {
        $where_conditions[] = "d.experience_years BETWEEN 6 AND 10";
    } elseif ($experience === '11-15') {
        $where_conditions[] = "d.experience_years BETWEEN 11 AND 15";
    } elseif ($experience === '16+') {
        $where_conditions[] = "d.experience_years >= 16";
    }
}

if ($lady_doctor) {
    // Note: This would need a gender field in database for proper implementation
    // For now, we'll use a placeholder condition
    $where_conditions[] = "d.doctor_name LIKE '%Dr.%' OR d.doctor_name LIKE '%Miss%' OR d.doctor_name LIKE '%Mrs.%'";
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Get total doctors count for pagination
$count_query = "SELECT COUNT(*) as total 
                FROM doctors d 
                LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_id 
                LEFT JOIN cities c ON d.city_id = c.city_id 
                LEFT JOIN entities e ON e.entity_id = d.entity_id 
                LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id 
                $where_clause";

$count_result = mysqli_query($con, $count_query);
$total_doctors = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_doctors / $per_page);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Doctors - <?php echo SITE_TITLE; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        /* :root {
            --primary: #0D6EFD;
            --primary-dark: #4f46e5;
            --secondary: #fff;
            --accent: #198754;
            --dark: #1e293b;
            --dark-blue: #782a54;
            --light: #f8fafc;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --hover-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        } */
      :root {
  /* Aapke existing colors */
  --primary: #0D6EFD;
  --primary-dark: #0B5ED7;
  --secondary: #FFFFFF;
  --light: #F8FAFC;
  --accent: #198754;
  --dark: #1E293B;
  --dark-blue: #0B3C5D;
  --gradient: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);

  /* New background suggestions */
  --bg-primary-gradient: var(--gradient);
  --bg-light: #E6F0FF;           /* Recommended light BG */
  --bg-light-green: #E8F5E9;
  --bg-medium: #0B5ED7;
  --bg-dark: #0B3C5D;


}

        
        html {
            overflow-x: hidden;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); 
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            width: 100%;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff10" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,133.3C960,128,1056,96,1152,90.7C1248,85,1344,107,1392,117.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            pointer-events: none;
            z-index: 0;
        }
        
        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px 0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-size: 22px;
            font-weight: 800;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }
        
        .nav-link {
            font-weight: 600;
            color: var(--dark) !important;
            margin: 0 8px;
            position: relative;
            transition: color 0.2s;
        }
        
        .nav-link:hover {
            color: var(--primary) !important;
        }
        
        .nav-link.active {
            color: var(--primary) !important;
        }
        
        .btn-login {
            background: var(--gradient);
            color: white !important;
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
        }
        
        .fixit-btn {
            background: black !important;
            color: #FF6B35 !important;
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
            border: 2px solid #FF6B35;
        }
        
        .fixit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(255, 107, 53, 0.4);
            background: #FF6B35 !important;
            color: black !important;
        }

        /* Page Header */
        .page-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 100px 0 60px;
            color: white;
            position: relative;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .page-header h1 {
            font-weight: 800;
            font-size: 3rem;
            letter-spacing: -1px;
            margin-bottom: 16px;
        }
        
        .page-header p {
            font-size: 1.25rem;
            opacity: 0.9;
            font-weight: 400;
        }

        /* Section */
        .section-padding {
            padding: 60px 0;
            position: relative;
            z-index: 1;
        }

        /* Filter Section */
        .filter-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 32px;
            box-shadow: var(--hover-shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 40px;
        }
        
        .filter-section .form-control,
        .filter-section .form-select {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px 16px;
            font-weight: 500;
            transition: all 0.2s;
            background: white;
        }
        
        .filter-section .form-control:focus,
        .filter-section .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }
        
        .filter-section .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 6px;
            font-size: 14px;
        }

        /* Pagination Styles */
        .pagination-container {
            margin-top: 40px;
        }
        
        .pagination {
            justify-content: center;
            gap: 8px;
        }
        
        .pagination .page-link {
            border: none;
            color: var(--dark);
            padding: 12px 16px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.2s;
            background: white;
            box-shadow: var(--card-shadow);
            border: 2px solid transparent;
        }
        
        .pagination .page-link:hover {
            background: var(--gradient);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--hover-shadow);
            border-color: transparent;
        }
        
        .pagination .page-item.active .page-link {
            background: var(--gradient);
            color: white;
            box-shadow: var(--hover-shadow);
            border-color: transparent;
        }
        
        .pagination .page-item.disabled .page-link {
            background: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
        }

        /* 5 Column Grid Layout - Override Bootstrap */
        .doctors-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important;
            gap: 24px !important;
            margin-bottom: 40px !important;
        }
    
        @media (min-width: 1200px) {
            .doctors-grid {
                grid-template-columns: repeat(3, 1fr) !important;
            }
        }
    
        @media (min-width: 992px) and (max-width: 1199px) {
            .doctors-grid {
                grid-template-columns: repeat(4, 1fr) !important;
            }
        }
    
        @media (min-width: 768px) and (max-width: 991px) {
            .doctors-grid {
                grid-template-columns: repeat(3, 1fr) !important;
            }
        }
    
        @media (min-width: 576px) and (max-width: 767px) {
            .doctors-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
    
        @media (max-width: 575px) {
            .doctors-grid {
                grid-template-columns: 1fr !important;
            }
        }

        /* Override Bootstrap row behavior */
        .doctors-grid .col-lg-4,
        .doctors-grid .col-md-6 {
            flex: 0 0 auto !important;
            max-width: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Doctor Cards - New Design */
        .doctor-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            margin-bottom: 0 !important;
            width: 100% !important;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .doctor-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient);
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .doctor-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--hover-shadow);
        }
        
        .doctor-card:hover::before {
            opacity: 1;
        }
        
        .doctor-img-wrapper {
            height: 180px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .doctor-img-wrapper::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 60%, rgba(0,0,0,0.3));
            opacity: 0.8;
        }
        
        .doctor-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .doctor-card:hover .doctor-img-wrapper img {
            transform: scale(1.1);
        }
        
        .doctor-info {
            padding: 20px 16px;
            text-align: center;
        }
        
        .doctor-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 4px;
            transition: color 0.2s;
            line-height: 1.3;
        }
        
        .doctor-card:hover .doctor-name {
            color: var(--primary);
        }
        
        .doctor-spec {
            color: var(--dark-blue);
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: bold;
        }
        
        .doctor-exp {
            color: var(--secondary);
            font-size: 0.75rem;
            margin-bottom: 12px;
            font-weight: 500;
        }
        
        .doctor-contact {
            flex-direction: column;
            gap: 6px;
            margin-bottom: 12px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 0.75rem;
            color: var(--dark-blue);
            font-weight: 500;
        }
        
        .contact-item i {
            color: var(--dark-blue);
            font-size: 0.8rem;
        }
        
        .doctor-hospital {
            margin-bottom: 12px;
        }
        
        /* .doctor-hospital span {
            color: var(--dark-blue);
            font-size: 0.75rem;
            font-weight: 500;
        } */
        
        .btn-appointment {
            background: var(--gradient);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 10px 16px;
            font-weight: 600;
            font-size: 0.8rem;
            transition: all 0.2s;
            width: 100%;
            box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
        }
        
        .btn-appointment:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
            color: white;
        }

        /* No Doctors Message */
        .no-doctors {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .no-doctors i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 16px;
        }
        
        .no-doctors h3 {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        .no-doctors p {
            color: var(--secondary);
            font-weight: 500;
        }
        
        /* Footer */
        .footer {
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(20px);
            color: #cbd5e1;
            padding: 60px 0 30px;
            margin-top: 80px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
        }
        
        .footer p {
            margin-bottom: 0;
            font-weight: 500;
        }
        
        .footer a {
            color: #cbd5e1;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .footer a:hover {
            color: white;
        }

        /* Preloader Styles */
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--dark-blue) 0%, var(--light) 100%);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s ease;
        }

        .preloader-content {
            text-align: center;
            animation: fadeInUp 0.8s ease;
        }

        .preloader-spinner {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 30px;
        }

        .spinner-circle {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: white;
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .spinner-circle:nth-child(1) {
            animation-delay: -0.32s;
        }

        .spinner-circle:nth-child(2) {
            animation-delay: -0.16s;
        }

        .spinner-circle:nth-child(3) {
            animation-delay: 0s;
        }

        .preloader-text {
            color: white;
            font-size: 24px;
            font-weight: 700;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .preloader-text i {
            color: #ff6b6b;
            margin-right: 10px;
            animation: pulse 1.5s ease-in-out infinite;
        }

        /* Preloader Animations */
        @keyframes bounce {
            0%, 80%, 100% {
                transform: scale(0);
            }
            40% {
                transform: scale(1);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        /* Hide preloader when page loads */
        .loaded #preloader {
            opacity: 0;
            pointer-events: none;
        }
    </style>
</head>
<body>

    <!-- Preloader -->
    <div id="preloader">
        <div class="preloader-content">
            <div class="preloader-spinner">
                <div class="spinner-circle"></div>
                <div class="spinner-circle"></div>
                <div class="spinner-circle"></div>
            </div>
            <div class="preloader-text">
                <i class="fas fa-heartbeat"></i>
                <span>DoctorApp</span>
            </div>
        </div>
    </div>

    <!-- Preloader Hide Script -->
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.body.classList.add('loaded');
                setTimeout(function() {
                    var preloader = document.getElementById('preloader');
                    if (preloader) {
                        preloader.style.display = 'none';
                    }
                }, 500);
            }, 1000);
        });
    </script>
