<?php include_once '../config.php';
include BASE_PATH.'/tracking.php'; 
include 'colors.php'; 
$city_id = 0;
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
      }
    );
  }
}
</script>
<?php

if(isset($_SESSION['city']) && !empty($_SESSION['city'])) {
        $city = mysqli_real_escape_string($con, $_SESSION['city']);
        
        $check = mysqli_query($con, "SELECT * FROM cities WHERE city_name = '$city'");
        if(mysqli_num_rows($check) == 0){
            mysqli_query($con, "INSERT INTO cities (city_name) VALUES ('$city')");
            $last_id = $con->insert_id;
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

// ============================================================
// SEO: $GLOBALS se values uthayein (agar page ne set ki hon)
// ============================================================
$page_title       = $GLOBALS['page_title']       ?? "SehatNexa — Pakistan's Trusted Healthcare Directory";
$page_description = $GLOBALS['page_description'] ?? "Find verified doctors, hospitals, laboratories, radiology centers and blood banks across Pakistan. Read patient reviews, check timings and contact details — all in one place.";
$page_image       = $GLOBALS['page_image']       ?? BASE_URL . "admin/inc/uploads/default/hosp.jpg";
$page_canonical   = $GLOBALS['page_canonical']   ?? strtok(BASE_URL . ltrim($_SERVER['REQUEST_URI'] ?? '', '/'), '?');
$page_robots      = $GLOBALS['page_robots']      ?? "index, follow";
$page_schema      = $GLOBALS['page_schema']      ?? '';

// Active menu detect
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ===== SEO: Title & Meta Description ===== -->
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <meta name="robots" content="<?= htmlspecialchars($page_robots) ?>">

    <!-- ===== SEO: Canonical ===== -->
    <link rel="canonical" href="<?= htmlspecialchars($page_canonical) ?>">

    <!-- ===== Open Graph (Social Media) ===== -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_description) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($page_image) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($page_canonical) ?>">
    <meta property="og:site_name" content="SehatNexa">

    <!-- ===== Twitter Card ===== -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($page_description) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($page_image) ?>">

    <!-- ===== Schema: Organization + WebSite (sab pages par) ===== -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "Organization",
                "@id": "<?= BASE_URL ?>#organization",
                "name": "SehatNexa",
                "url": "<?= BASE_URL ?>",
                "logo": "<?= BASE_URL ?>includes/uploads/logo.png",
                "description": "Pakistan's trusted healthcare directory for doctors, hospitals, labs, radiology centers and blood banks.",
                "contactPoint": {
                    "@type": "ContactPoint",
                    "telephone": "+92-337-1320001",
                    "contactType": "customer service",
                    "areaServed": "PK",
                    "availableLanguage": ["English", "Urdu"]
                },
                "sameAs": [
                    "https://www.facebook.com/sohail.fixit",
                    "https://www.tiktok.com/@sehatnexa.official"
                ]
            },
            {
                "@type": "WebSite",
                "@id": "<?= BASE_URL ?>#website",
                "url": "<?= BASE_URL ?>",
                "name": "SehatNexa",
                "publisher": { "@id": "<?= BASE_URL ?>#organization" },
                "potentialAction": {
                    "@type": "SearchAction",
                    "target": "<?= BASE_URL ?>doctors?search={search_term_string}",
                    "query-input": "required name=search_term_string"
                }
            }
        ]
    }
    </script>

    <!-- Page-specific schema (agar kisi page ne set kiya ho) -->
    <?php if (!empty($page_schema)): ?>
        <script type="application/ld+json"><?= $page_schema ?></script>
    <?php endif; ?>

    <link rel="icon" type="image/jpeg" href="<?php echo BASE_URL; ?>includes/fav.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="<?php echo BASE_URL; ?>includes/uploads/fav.jpg">
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
      :root {
  /* SehatNexa Brand Colors */
  --primary    : #1C3F8F;   /* deep navy */
  --primary-dark: #0D2B6E;  /* dark navy */
  --secondary  : #FFFFFF;
  --accent     : #00A8CC;   /* sky teal */
  --green      : #27C96A;   /* bright green */
  --tgreen     : #00C9A7;   /* teal green */
  --dark       : #0D2B6E;
  --dark-blue  : #1C3F8F;
  --light      : #E8F7FF;   /* light blue bg */
  --light-green: #F0FFF8;   /* light green bg */
  --gradient   : linear-gradient(135deg, #1C3F8F 0%, #00A8CC 100%);
  --gradient-g : linear-gradient(135deg, #27C96A 0%, #00C9A7 100%);
  --gradient-f : linear-gradient(135deg, #1C3F8F 0%, #00A8CC 50%, #27C96A 100%);
  --bg-light   : #E8F7FF;
  --bg-light-green: #F0FFF8;
  --card-shadow : 0 4px 16px rgba(28,63,143,0.08), 0 2px 6px rgba(0,0,0,0.04);
  --hover-shadow: 0 16px 32px rgba(28,63,143,0.14), 0 6px 12px rgba(0,168,204,0.1);
}

        html { overflow-x: hidden; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #C8E6F5;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            width: 100%;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background:
                radial-gradient(ellipse at top left,  rgba(28,63,143,0.18)  0%, transparent 55%),
                radial-gradient(ellipse at top right, rgba(0,168,204,0.15)  0%, transparent 55%),
                radial-gradient(ellipse at bottom,    rgba(39,201,106,0.10) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }
        
        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.97) !important;
            backdrop-filter: blur(20px);
            border-bottom: 2px solid rgba(0,168,204,0.12);
            padding: 12px 0;
            box-shadow: 0 2px 16px rgba(28,63,143,0.08);
            position: relative;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-size: 22px;
            font-weight: 800;
            background: linear-gradient(135deg, #1C3F8F 0%, #00A8CC 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }
        
        .nav-link {
            font-weight: 600;
            color: #0D2B6E !important;
            margin: 0 8px;
            position: relative;
            transition: color 0.2s;
        }
        
        .nav-link:hover { color: #1C3F8F !important; }
        .nav-link.active { color: #1C3F8F !important; }
        
        .btn-login {
            background: linear-gradient(135deg, #1C3F8F 0%, #00A8CC 100%);
            color: white !important;
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(28,63,143,0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(28,63,143,0.4);
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
        .pagination-container { margin-top: 40px; }
        
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
    
        @media (min-width: 1200px) { .doctors-grid { grid-template-columns: repeat(3, 1fr) !important; } }
        @media (min-width: 992px) and (max-width: 1199px) { .doctors-grid { grid-template-columns: repeat(4, 1fr) !important; } }
        @media (min-width: 768px) and (max-width: 991px) { .doctors-grid { grid-template-columns: repeat(3, 1fr) !important; } }
        @media (min-width: 576px) and (max-width: 767px) { .doctors-grid { grid-template-columns: repeat(2, 1fr) !important; } }
        @media (max-width: 575px) { .doctors-grid { grid-template-columns: 1fr !important; } }

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
            top: 0; left: 0; right: 0;
            height: 4px;
            background: var(--gradient);
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .doctor-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--hover-shadow);
        }
        
        .doctor-card:hover::before { opacity: 1; }
        
        .doctor-img-wrapper {
            height: 180px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #1C3F8F 0%, #00A8CC 100%);
        }
        
        .doctor-img-wrapper::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to bottom, transparent 60%, rgba(0,0,0,0.3));
            opacity: 0.8;
        }
        
        .doctor-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .doctor-card:hover .doctor-img-wrapper img { transform: scale(1.1); }
        
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
        
        .doctor-card:hover .doctor-name { color: var(--primary); }
        
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
        
        .doctor-hospital { margin-bottom: 12px; }
        
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
        
        .footer p { margin-bottom: 0; font-weight: 500; }
        .footer a { color: #cbd5e1; text-decoration: none; font-weight: 500; transition: color 0.2s; }
        .footer a:hover { color: white; }

        /* Preloader Styles */
        #preloader {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(135deg, #1C3F8F 0%, #00A8CC 60%, #27C96A 100%);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s ease;
        }

        .preloader-content { text-align: center; animation: fadeInUp 0.8s ease; }
        .preloader-spinner { display: flex; justify-content: center; gap: 8px; margin-bottom: 30px; }

        .spinner-circle {
            width: 12px; height: 12px;
            border-radius: 50%;
            background: white;
            animation: bounce 1.4s infinite ease-in-out both;
        }
        .spinner-circle:nth-child(1) { animation-delay: -0.32s; }
        .spinner-circle:nth-child(2) { animation-delay: -0.16s; }
        .spinner-circle:nth-child(3) { animation-delay: 0s; }

        .preloader-text {
            color: white;
            font-size: 24px;
            font-weight: 700;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .preloader-text i { color: #ff6b6b; margin-right: 10px; animation: pulse 1.5s ease-in-out infinite; }

        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .loaded #preloader { opacity: 0; pointer-events: none; }
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
                <img src="<?php echo BASE_URL; ?>includes/uploads/logo.png" alt="SehatNexa" style="height:80px; width:auto; object-fit:contain;">
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