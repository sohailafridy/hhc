<?php include '../includes/header.php'; ?>

<?php
// Get doctor ID from URL
$doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;

if ($doctor_id > 0) {
    $query = "SELECT d.*, c.city_name, h.hospital_name 
             FROM doctors d 
             LEFT JOIN cities c ON d.city_id = c.city_id 
             LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id 
             LEFT JOIN entities e ON e.entity_id = d.entity_id
             WHERE d.doctor_id = $doctor_id AND e.status = 1 AND d.approve=1";
    $result = mysqli_query($con, $query);
    $doctor = mysqli_fetch_assoc($result);
}

if (isset($_REQUEST['entity_id']) && $_REQUEST['entity_id'] != 0) {
    $entity_id = $_REQUEST['entity_id'];
    $commenter_name = isset($_POST['commenter_name']) ? trim($_POST['commenter_name']) : '';
    $commenter_gmail = isset($_POST['commenter_gmail']) ? trim($_POST['commenter_gmail']) : '';
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $stars = isset($_POST['stars']) ? (int)$_POST['stars'] : 5;

    $insert_query = "INSERT INTO feedback (entity_id, commenter_name, commenter_gmail, comment, stars, status, created_at, updated_at) 
                    VALUES ($entity_id,
                    '" . mysqli_real_escape_string($con, $commenter_name) . "', 
                    '" . mysqli_real_escape_string($con, $commenter_gmail) . "', 
                    '" . mysqli_real_escape_string($con, $comment) . "', 
                    $stars, 1, NOW(), NOW())";
    $feedback_run = mysqli_query($con, $insert_query);
}
?>

<style>
/* ========================================
   ROOT VARIABLES
   ======================================== */
:root {
    --primary: #6366f1;
    --primary-light: #818cf8;
    --primary-dark: #4f46e5;
    --secondary: #8b5cf6;
    --accent: #f59e0b;
    --success: #22c55e;
    --danger: #ef4444;
    --text: #0f172a;
    --text-light: #64748b;
    --bg: #f1f5f9;
    --card: rgba(255,255,255,0.85);
    --glass: rgba(255,255,255,0.6);
    --border: rgba(255,255,255,0.2);
    --shadow: 0 25px 50px -12px rgba(0,0,0,0.15);
    --shadow-glow: 0 0 40px rgba(99,102,241,0.15);
}

/* ========================================
   GLOBAL
   ======================================== */
.section-padding {
    padding: 60px 0;
}

/* ========================================
   HERO SECTION - ANIMATED
   ======================================== */
.doctor-hero {
    position: relative;
    min-height: 90vh;
    display: flex;
    align-items: center;
    overflow: hidden;
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #4f46e5 100%);
}

.doctor-hero .hero-bg-particles {
    position: absolute;
    inset: 0;
    z-index: 0;
    overflow: hidden;
}

.doctor-hero .hero-bg-particles .particle {
    position: absolute;
    width: 6px;
    height: 6px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
    animation: floatParticle 20s infinite linear;
}

.doctor-hero .hero-bg-particles .particle:nth-child(1) { top: 10%; left: 5%; animation-duration: 18s; }
.doctor-hero .hero-bg-particles .particle:nth-child(2) { top: 20%; left: 85%; animation-duration: 22s; }
.doctor-hero .hero-bg-particles .particle:nth-child(3) { top: 60%; left: 10%; animation-duration: 16s; }
.doctor-hero .hero-bg-particles .particle:nth-child(4) { top: 70%; left: 75%; animation-duration: 24s; }
.doctor-hero .hero-bg-particles .particle:nth-child(5) { top: 40%; left: 45%; animation-duration: 20s; }
.doctor-hero .hero-bg-particles .particle:nth-child(6) { top: 85%; left: 30%; animation-duration: 19s; }

@keyframes floatParticle {
    0% { transform: translateY(0) rotate(0deg) scale(1); opacity: 0.3; }
    25% { opacity: 0.8; }
    50% { transform: translateY(-100px) rotate(180deg) scale(1.5); opacity: 0.5; }
    75% { opacity: 0.8; }
    100% { transform: translateY(0) rotate(360deg) scale(1); opacity: 0.3; }
}

.doctor-hero .hero-glow {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.4;
    animation: pulseGlow 6s ease-in-out infinite;
}

.doctor-hero .hero-glow.glow-1 {
    width: 400px;
    height: 400px;
    top: -100px;
    right: -100px;
    background: var(--primary);
    animation-delay: 0s;
}

.doctor-hero .hero-glow.glow-2 {
    width: 300px;
    height: 300px;
    bottom: -50px;
    left: -50px;
    background: var(--secondary);
    animation-delay: -3s;
}

@keyframes pulseGlow {
    0%, 100% { transform: scale(1); opacity: 0.3; }
    50% { transform: scale(1.2); opacity: 0.5; }
}

/* Hero Container */
.doctor-hero .hero-container {
    position: relative;
    z-index: 1;
    width: 100%;
    padding: 40px 0;
}

.doctor-hero .hero-card {
    background: var(--glass);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border-radius: 32px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow), var(--shadow-glow);
    padding: 40px;
    animation: fadeInUp 0.8s ease-out;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Hero Profile */
.hero-profile {
    display: flex;
    gap: 40px;
    align-items: flex-start;
}

.hero-avatar-wrapper {
    position: relative;
    flex-shrink: 0;
}

.hero-avatar-wrapper .avatar-ring {
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    background: conic-gradient(from 0deg, var(--primary), var(--secondary), var(--accent), var(--primary));
    animation: spinRing 6s linear infinite;
    z-index: 0;
}

@keyframes spinRing {
    to { transform: rotate(360deg); }
}

.hero-avatar-wrapper .avatar-ring-inner {
    position: relative;
    z-index: 1;
    padding: 4px;
    border-radius: 50%;
    background: #0f172a;
}

.hero-avatar {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.hero-avatar:hover {
    transform: scale(1.05) rotate(-4deg);
}

.hero-avatar-placeholder {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: white;
}

.hero-avatar-wrapper .online-badge {
    position: absolute;
    bottom: 8px;
    right: 8px;
    z-index: 2;
    width: 24px;
    height: 24px;
    background: var(--success);
    border-radius: 50%;
    border: 3px solid #0f172a;
    animation: pulseDot 2s ease-in-out infinite;
}

@keyframes pulseDot {
    0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(34,197,94,0.4); }
    50% { transform: scale(1.1); box-shadow: 0 0 0 8px rgba(34,197,94,0); }
}

/* Hero Info */
.hero-info {
    flex: 1;
}

.hero-info .name-badge {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 8px;
}

.hero-info h1 {
    font-size: 2.8rem;
    font-weight: 800;
    color: #fff;
    margin: 0;
    text-shadow: 0 2px 20px rgba(0,0,0,0.2);
}

.hero-info .badge-status {
    padding: 4px 16px;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: rgba(34,197,94,0.2);
    color: #4ade80;
    border: 1px solid rgba(34,197,94,0.3);
}

.hero-info .badge-status.emergency {
    background: rgba(239,68,68,0.2);
    color: #f87171;
    border-color: rgba(239,68,68,0.3);
    animation: pulseEmergency 1.5s ease-in-out infinite;
}

@keyframes pulseEmergency {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

.hero-info .specialty-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 18px;
    background: rgba(255,255,255,0.08);
    border-radius: 50px;
    font-size: 0.95rem;
    color: rgba(255,255,255,0.85);
    margin: 6px 0 12px;
    border: 1px solid rgba(255,255,255,0.06);
}

.hero-info .specialty-tag i {
    color: var(--accent);
}

.hero-info .hero-meta {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
    margin: 16px 0 20px;
}

.hero-info .hero-meta .meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: rgba(255,255,255,0.75);
    font-size: 0.9rem;
}

.hero-info .hero-meta .meta-item i {
    color: var(--accent);
    font-size: 0.9rem;
}

.hero-info .hero-rating {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 8px 20px;
    background: rgba(255,255,255,0.06);
    border-radius: 50px;
    border: 1px solid rgba(255,255,255,0.06);
    margin-top: 8px;
}

.hero-info .hero-rating .stars {
    color: #fbbf24;
    font-size: 0.9rem;
}

.hero-info .hero-rating .score {
    font-weight: 700;
    color: #fff;
    font-size: 1.1rem;
}

.hero-info .hero-rating .count {
    color: rgba(255,255,255,0.5);
    font-size: 0.85rem;
}

.hero-info .hero-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    flex-wrap: wrap;
}

.btn-hero {
    padding: 12px 28px;
    border-radius: 50px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.btn-hero-primary {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    box-shadow: 0 8px 30px rgba(99,102,241,0.35);
}

.btn-hero-primary:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 12px 40px rgba(99,102,241,0.5);
    color: white;
}

.btn-hero-secondary {
    background: rgba(255,255,255,0.08);
    color: white;
    border: 1px solid rgba(255,255,255,0.12);
}

.btn-hero-secondary:hover {
    background: rgba(255,255,255,0.15);
    transform: translateY(-3px);
    color: white;
}

.btn-hero-outline {
    background: transparent;
    color: white;
    border: 2px solid rgba(255,255,255,0.2);
}

.btn-hero-outline:hover {
    background: rgba(255,255,255,0.08);
    border-color: rgba(255,255,255,0.4);
    transform: translateY(-3px);
    color: white;
}

/* ========================================
   GLASS CARD SECTION
   ======================================== */
.glass-section {
    background: var(--bg);
    padding: 60px 0;
}

.glass-card {
    background: var(--card);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 1px solid rgba(255,255,255,0.5);
    box-shadow: var(--shadow);
    padding: 32px;
    margin-bottom: 30px;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.glass-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow), var(--shadow-glow);
}

.glass-card .card-header-custom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid rgba(0,0,0,0.04);
}

.glass-card .card-header-custom h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.glass-card .card-header-custom h3 i {
    color: var(--primary);
}

/* Contact Grid */
.contact-grid-modern {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
}

.contact-item-modern {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    background: rgba(0,0,0,0.02);
    border-radius: 14px;
    border: 1px solid rgba(0,0,0,0.04);
    transition: all 0.3s ease;
}

.contact-item-modern:hover {
    background: rgba(99,102,241,0.04);
    border-color: var(--primary-light);
    transform: translateX(6px);
}

.contact-item-modern .icon-box {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.contact-item-modern .label {
    font-size: 0.7rem;
    color: var(--text-light);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.contact-item-modern .value {
    font-weight: 600;
    color: var(--text);
    font-size: 0.95rem;
}

/* About Text */
.about-text-modern {
    color: var(--text-light);
    line-height: 1.9;
    font-size: 1rem;
}

/* ========================================
   CLINICAL CARDS - ANIMATED
   ======================================== */
.clinical-grid-modern {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}

.clinical-card-modern {
    background: rgba(255,255,255,0.6);
    backdrop-filter: blur(12px);
    border-radius: 18px;
    padding: 22px;
    border: 1px solid rgba(255,255,255,0.3);
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
}

.clinical-card-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
    opacity: 0;
    transition: opacity 0.4s;
}

.clinical-card-modern:hover::before {
    opacity: 1;
}

.clinical-card-modern:hover {
    transform: translateY(-6px) scale(1.01);
    box-shadow: var(--shadow), var(--shadow-glow);
}

.clinical-card-modern .hospital-name {
    font-weight: 700;
    font-size: 1.05rem;
    color: var(--primary-dark);
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.clinical-card-modern .hospital-name i {
    font-size: 0.9rem;
}

.clinical-item-modern {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 4px 0;
    font-size: 0.9rem;
    color: var(--text);
}

.clinical-item-modern i {
    width: 18px;
    color: var(--primary);
    font-size: 0.8rem;
}

.clinical-item-modern .clabel {
    color: var(--text-light);
    font-weight: 500;
    min-width: 85px;
    font-size: 0.8rem;
}

.clinical-item-modern .cvalue {
    font-weight: 500;
}

/* ========================================
   REVIEWS
   ======================================== */
.reviews-grid-modern {
    display: grid;
    gap: 16px;
}

.review-item-modern {
    padding: 16px 0;
    border-bottom: 1px solid rgba(0,0,0,0.04);
    transition: all 0.3s ease;
}

.review-item-modern:last-child {
    border-bottom: none;
}

.review-item-modern:hover {
    padding-left: 12px;
}

.review-item-modern .review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.review-item-modern .reviewer-name {
    font-weight: 700;
    color: var(--text);
}

.review-item-modern .reviewer-email {
    font-size: 0.8rem;
    color: var(--text-light);
}

.review-item-modern .review-stars {
    color: #fbbf24;
    font-size: 0.85rem;
}

.review-item-modern .review-comment {
    color: var(--text-light);
    font-size: 0.95rem;
    line-height: 1.7;
    margin: 4px 0 0;
}

.review-item-modern .review-date {
    font-size: 0.75rem;
    color: var(--text-light);
    margin-top: 4px;
}

/* Review Form */
.review-form-modern {
    background: rgba(255,255,255,0.5);
    backdrop-filter: blur(12px);
    border-radius: 18px;
    padding: 28px;
    border: 1px solid rgba(255,255,255,0.3);
    margin-bottom: 30px;
}

.review-form-modern .form-control {
    border-radius: 12px;
    border: 2px solid rgba(0,0,0,0.06);
    padding: 10px 16px;
    transition: all 0.3s ease;
    background: rgba(255,255,255,0.8);
}

.review-form-modern .form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(99,102,241,0.08);
}

.rating-select-modern {
    display: flex;
    gap: 6px;
    margin: 8px 0 12px;
}

.rating-select-modern .star {
    font-size: 2rem;
    color: #d1d5db;
    cursor: pointer;
    transition: all 0.2s ease;
}

.rating-select-modern .star:hover,
.rating-select-modern .star.active {
    color: #f59e0b;
    transform: scale(1.15);
}

.btn-submit-glass {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    border: none;
    padding: 12px 32px;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 8px 30px rgba(99,102,241,0.3);
}

.btn-submit-glass:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 40px rgba(99,102,241,0.5);
    color: white;
}

/* ========================================
   ANIMATIONS
   ======================================== */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in-up {
    animation: fadeInUp 0.7s ease-out forwards;
}

.fade-in-up-delay-1 { animation-delay: 0.1s; opacity: 0; }
.fade-in-up-delay-2 { animation-delay: 0.2s; opacity: 0; }
.fade-in-up-delay-3 { animation-delay: 0.3s; opacity: 0; }
.fade-in-up-delay-4 { animation-delay: 0.4s; opacity: 0; }

/* ========================================
   RESPONSIVE
   ======================================== */
@media (max-width: 992px) {
    .hero-profile {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .hero-info .name-badge {
        justify-content: center;
    }
    .hero-info .hero-meta {
        justify-content: center;
    }
    .hero-info .hero-actions {
        justify-content: center;
    }
    .clinical-grid-modern {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .doctor-hero .hero-card {
        padding: 24px;
    }
    .hero-info h1 {
        font-size: 2rem;
    }
    .hero-avatar {
        width: 120px;
        height: 120px;
    }
    .hero-avatar-placeholder {
        width: 120px;
        height: 120px;
        font-size: 3rem;
    }
    .contact-grid-modern {
        grid-template-columns: 1fr;
    }
    .glass-card {
        padding: 20px;
    }
    .hero-info .hero-meta {
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
}

@media (max-width: 480px) {
    .doctor-hero .hero-card {
        padding: 16px;
        border-radius: 20px;
    }
    .hero-info h1 {
        font-size: 1.6rem;
    }
    .hero-avatar {
        width: 100px;
        height: 100px;
    }
    .hero-avatar-placeholder {
        width: 100px;
        height: 100px;
        font-size: 2.5rem;
    }
    .btn-hero {
        padding: 10px 20px;
        font-size: 0.85rem;
        width: 100%;
        justify-content: center;
    }
    .hero-info .hero-actions {
        flex-direction: column;
        width: 100%;
    }
    .glass-card {
        padding: 16px;
        border-radius: 16px;
    }
}
</style>

<!-- ======================================== -->
<!-- NAVBAR -->
<!-- ======================================== -->
<?php include BASE_PATH . '/includes/menu.php'; ?>

<!-- ======================================== -->
<!-- HERO SECTION - ANIMATED -->
<!-- ======================================== -->
<?php if ($doctor && !empty($doctor)): 

    // Get specialization
    $spec_query = "SELECT `type` FROM dr_cat_types WHERE dr_cat_type_id = " . $doctor['cat_type_id'];
    $spec_result = mysqli_query($con, $spec_query);
    $spec = mysqli_fetch_assoc($spec_result);
    $specialization = $spec ? $spec['type'] : 'General Practitioner';

    // Get rating
    $rating_query = "SELECT AVG(stars) as avg_rating, COUNT(*) as total_reviews 
                  FROM feedback WHERE entity_id = " . $doctor['entity_id'] . " AND status = 1";
    $rating_result = mysqli_query($con, $rating_query);
    $rating_data = mysqli_fetch_assoc($rating_result);
    $avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
    $total_reviews = $rating_data['total_reviews'] ? $rating_data['total_reviews'] : 0;
?>

<section class="doctor-hero">
    <!-- Background Particles -->
    <div class="hero-bg-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Glow Orbs -->
    <div class="hero-glow glow-1"></div>
    <div class="hero-glow glow-2"></div>

    <div class="container hero-container">
        <div class="hero-card">
            <div class="hero-profile">
                <!-- Avatar -->
                <div class="hero-avatar-wrapper">
                    <div class="avatar-ring"></div>
                    <div class="avatar-ring-inner">
                        <?php if (!empty($doctor['doctor_pic'])): ?>
                            <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" 
                                 alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" class="hero-avatar">
                        <?php else: ?>
                            <div class="hero-avatar-placeholder">
                                <i class="fas fa-user-md"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="online-badge"></div>
                </div>

                <!-- Info -->
                <div class="hero-info">
                    <div class="name-badge">
                        <h1>Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></h1>
                        <?php if ($doctor['emergency_status'] == 1): ?>
                            <span class="badge-status emergency">
                                <i class="fas fa-exclamation-triangle"></i> Emergency
                            </span>
                        <?php else: ?>
                            <span class="badge-status">Available</span>
                        <?php endif; ?>
                    </div>

                    <div class="specialty-tag">
                        <i class="fas fa-stethoscope"></i> <?php echo htmlspecialchars($specialization); ?>
                    </div>

                    <div class="hero-meta">
                        <span class="meta-item">
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($doctor['city_name']); ?>
                        </span>
                        <?php if (!empty($doctor['experience_years']) && $doctor['experience_years'] != 0): ?>
                            <span class="meta-item">
                                <i class="fas fa-briefcase"></i> <?php echo $doctor['experience_years']; ?> Years
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($doctor['doctor_phone'])): ?>
                            <span class="meta-item">
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($doctor['doctor_phone']); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($avg_rating > 0): ?>
                        <div class="hero-rating">
                            <span class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $avg_rating ? '' : 'text-muted'; ?>" style="opacity: <?php echo $i <= $avg_rating ? '1' : '0.3'; ?>;"></i>
                                <?php endfor; ?>
                            </span>
                            <span class="score"><?php echo $avg_rating; ?></span>
                            <span class="count">(<?php echo $total_reviews; ?> reviews)</span>
                        </div>
                    <?php endif; ?>

                    <div class="hero-actions">
                        <a href="tel:<?php echo $doctor['doctor_phone']; ?>" class="btn-hero btn-hero-primary">
                            <i class="fas fa-phone-alt"></i> Call Now
                        </a>
                        <a href="#reviews" class="btn-hero btn-hero-secondary">
                            <i class="fas fa-star"></i> Write Review
                        </a>
                        <a href="<?php echo BASE_URL; ?>doctors" class="btn-hero btn-hero-outline">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ======================================== -->
<!-- DETAIL SECTION - GLASS CARDS -->
<!-- ======================================== -->
<section class="glass-section">
    <div class="container">

        <!-- ===== ABOUT & CONTACT ===== -->
        <div class="row g-4">
            <div class="col-lg-6 fade-in-up fade-in-up-delay-1">
                <div class="glass-card">
                    <div class="card-header-custom">
                        <h3><i class="fas fa-info-circle"></i> About Doctor</h3>
                    </div>
                    <p class="about-text-modern">
                        <?php echo nl2br(htmlspecialchars($doctor['short_detail'] ?? 'No details available.')); ?>
                        <?php if (!empty($doctor['other'])): ?>
                            <br><br><?php echo nl2br(htmlspecialchars($doctor['other'])); ?>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($doctor['static_clinical_info'])): ?>
                        <div style="margin-top:16px; padding:16px; background:rgba(99,102,241,0.04); border-radius:12px; border-left:4px solid var(--primary);">
                            <p style="font-size:0.9rem; color:var(--text-light); margin:0;">
                                <strong>Clinical Notes:</strong> <?php echo nl2br(htmlspecialchars($doctor['static_clinical_info'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-6 fade-in-up fade-in-up-delay-2">
                <div class="glass-card">
                    <div class="card-header-custom">
                        <h3><i class="fas fa-address-card"></i> Contact</h3>
                    </div>
                    <div class="contact-grid-modern">
                        <div class="contact-item-modern">
                            <div class="icon-box"><i class="fas fa-phone"></i></div>
                            <div>
                                <div class="label">Phone</div>
                                <div class="value"><?php echo htmlspecialchars($doctor['doctor_phone'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="contact-item-modern">
                            <div class="icon-box"><i class="fas fa-envelope"></i></div>
                            <div>
                                <div class="label">Email</div>
                                <div class="value"><?php echo htmlspecialchars($doctor['doctor_email'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="contact-item-modern">
                            <div class="icon-box"><i class="fas fa-map-marker-alt"></i></div>
                            <div>
                                <div class="label">City</div>
                                <div class="value"><?php echo htmlspecialchars($doctor['city_name'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <?php if (!empty($doctor['hospital_name'])): ?>
                            <div class="contact-item-modern">
                                <div class="icon-box"><i class="fas fa-hospital"></i></div>
                                <div>
                                    <div class="label">Hospital</div>
                                    <div class="value"><?php echo htmlspecialchars($doctor['hospital_name']); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== CLINICAL INFORMATION ===== -->
        <?php
        $clinical_query = "SELECT ci.*, hospitals.hospital_name, hospitals.hospital_id
                        FROM clinical_info ci 
                        INNER JOIN doctor_in_hospital dih ON ci.doctor_in_hosp_id = dih.doctor_in_hosp_id 
                        LEFT JOIN hospitals ON dih.hospital_id = hospitals.hospital_id
                        WHERE dih.doctor_id = " . $doctor['doctor_id'] . "
                        ORDER BY ci.season, ci.shift";
        $clinical_result = mysqli_query($con, $clinical_query);
        ?>

        <?php if (mysqli_num_rows($clinical_result) > 0): ?>
            <div class="glass-card fade-in-up fade-in-up-delay-3" style="margin-top:30px;">
                <div class="card-header-custom">
                    <h3><i class="fas fa-clock"></i> Clinical Schedule</h3>
                    <span class="badge bg-primary"><?php echo mysqli_num_rows($clinical_result); ?> Records</span>
                </div>

                <div class="clinical-grid-modern">
                    <?php while ($clinical = mysqli_fetch_assoc($clinical_result)): ?>
                        <div class="clinical-card-modern">
                            <div class="hospital-name">
                                <i class="fas fa-hospital"></i>
                                <?php
                                if (!empty($clinical['hospital_name'])) {
                                    echo htmlspecialchars($clinical['hospital_name']);
                                } else {
                                    echo 'Personal Clinic';
                                }
                                ?>
                            </div>

                            <?php if (!empty($clinical['morning_opening_time']) || !empty($clinical['morning_closing_time'])): ?>
                                <div class="clinical-item-modern">
                                    <i class="fas fa-sun text-warning"></i>
                                    <span class="clabel">Morning</span>
                                    <span class="cvalue">
                                        <?php echo date('h:i A', strtotime($clinical['morning_opening_time'])); ?>
                                        - 
                                        <?php echo date('h:i A', strtotime($clinical['morning_closing_time'])); ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($clinical['evening_opening_time']) || !empty($clinical['evening_closing_time'])): ?>
                                <div class="clinical-item-modern">
                                    <i class="fas fa-moon text-primary"></i>
                                    <span class="clabel">Evening</span>
                                    <span class="cvalue">
                                        <?php echo date('h:i A', strtotime($clinical['evening_opening_time'])); ?>
                                        - 
                                        <?php echo date('h:i A', strtotime($clinical['evening_closing_time'])); ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <div class="clinical-item-modern">
                                <i class="fas fa-calendar-day text-success"></i>
                                <span class="clabel">Working</span>
                                <span class="cvalue"><?php echo htmlspecialchars($clinical['days'] ?? 'N/A'); ?></span>
                            </div>

                            <div class="clinical-item-modern">
                                <i class="fas fa-calendar-times text-danger"></i>
                                <span class="clabel">Off</span>
                                <span class="cvalue"><?php echo htmlspecialchars($clinical['off_days'] ?? 'None'); ?></span>
                            </div>

                            <?php if (!empty($clinical['contact'])): ?>
                                <div class="clinical-item-modern">
                                    <i class="fas fa-phone"></i>
                                    <span class="clabel">Contact</span>
                                    <span class="cvalue"><?php echo htmlspecialchars($clinical['contact']); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($clinical['detail'])): ?>
                                <div class="clinical-item-modern" style="border-top:1px dashed rgba(0,0,0,0.06); padding-top:8px; margin-top:4px;">
                                    <i class="fas fa-info-circle text-info"></i>
                                    <span class="clabel">Detail</span>
                                    <span class="cvalue" style="font-size:0.8rem;"><?php echo htmlspecialchars($clinical['detail']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ===== REVIEWS ===== -->
        <div id="reviews" class="glass-card fade-in-up fade-in-up-delay-4" style="margin-top:30px;">
            <div class="card-header-custom">
                <h3><i class="fas fa-star"></i> Patient Reviews</h3>
                <?php if ($total_reviews > 0): ?>
                    <div style="display:flex; align-items:center; gap:8px; padding:6px 16px; background:#fef3c7; border-radius:50px;">
                        <i class="fas fa-star" style="color:#f59e0b;"></i>
                        <span style="font-weight:700;"><?php echo $avg_rating; ?></span>
                        <span style="opacity:0.7;">(<?php echo $total_reviews; ?>)</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Review Form -->
            <div class="review-form-modern">
                <h5 style="font-weight:700; margin-bottom:16px;">
                    <i class="fas fa-pen" style="color:var(--primary);"></i> Share Your Experience
                </h5>
                <form method="POST">
                    <input type="hidden" name="entity_id" value="<?php echo $doctor['entity_id']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Your Name *</label>
                            <input type="text" class="form-control" name="commenter_name" required placeholder="Enter your name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="commenter_gmail" required placeholder="your@email.com">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rating *</label>
                        <div class="rating-select-modern">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <span class="star" data-value="<?php echo $i; ?>" onclick="setRatingModern(this)">
                                    <i class="fas fa-star"></i>
                                </span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="stars" id="ratingValueModern" value="5">
                        <span class="text-muted" id="ratingLabelModern">Excellent</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Your Review *</label>
                        <textarea class="form-control" name="comment" rows="4" required placeholder="Share your experience..."></textarea>
                    </div>

                    <button type="submit" class="btn-submit-glass">
                        <i class="fas fa-paper-plane me-2"></i> Submit Review
                    </button>
                </form>
            </div>

            <!-- Reviews List -->
            <?php
            $feedback_query = "SELECT * FROM feedback WHERE entity_id = " . $doctor['entity_id'] . " AND status = 1 
                              ORDER BY created_at DESC LIMIT 10";
            $feedback_result = mysqli_query($con, $feedback_query);
            ?>

            <?php if (mysqli_num_rows($feedback_result) > 0): ?>
                <div class="reviews-grid-modern">
                    <?php while ($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                        <div class="review-item-modern">
                            <div class="review-header">
                                <div>
                                    <span class="reviewer-name"><?php echo htmlspecialchars($feedback['commenter_name']); ?></span>
                                    <span class="reviewer-email"><?php echo htmlspecialchars($feedback['commenter_gmail']); ?></span>
                                </div>
                                <div class="review-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $feedback['stars'] ? '' : 'text-muted'; ?>" style="opacity: <?php echo $i <= $feedback['stars'] ? '1' : '0.3'; ?>;"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <?php if (!empty($feedback['comment'])): ?>
                                <p class="review-comment"><?php echo nl2br(htmlspecialchars($feedback['comment'])); ?></p>
                            <?php endif; ?>
                            <div class="review-date">
                                <i class="fas fa-calendar me-1"></i> <?php echo date('d M Y', strtotime($feedback['created_at'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4" style="color:var(--text-light);">
                    <i class="fas fa-comment-slash fa-2x mb-2" style="color:#cbd5e1;"></i>
                    <h5 style="font-weight:600;">No Reviews Yet</h5>
                    <p>Be the first to share your experience!</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php else: ?>
    <!-- ===== NOT FOUND ===== -->
    <section style="min-height:60vh; display:flex; align-items:center; justify-content:center; background:var(--bg);">
        <div class="container text-center" style="animation: fadeInUp 0.7s ease-out;">
            <i class="fas fa-user-md fa-4x" style="color:#cbd5e1; margin-bottom:20px;"></i>
            <h3 style="font-weight:700;">Doctor Not Found</h3>
            <p style="color:var(--text-light);">The doctor you're looking for doesn't exist.</p>
            <a href="<?php echo BASE_URL; ?>doctors" class="btn-hero btn-hero-primary" style="margin-top:20px; display:inline-flex;">
                <i class="fas fa-arrow-left me-2"></i> Back to Doctors
            </a>
        </div>
    </section>
<?php endif; ?>

<!-- ======================================== -->
<!-- FOOTER -->
<!-- ======================================== -->
<?php include BASE_PATH . '/includes/footer.php'; ?>

<script>
// ============================================
// MODERN STAR RATING
// ============================================
function setRatingModern(el) {
    const stars = document.querySelectorAll('.rating-select-modern .star');
    const value = el.dataset.value;
    
    stars.forEach(star => {
        star.classList.remove('active');
        if (star.dataset.value <= value) {
            star.classList.add('active');
        }
    });
    
    document.getElementById('ratingValueModern').value = value;
    
    const labels = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
    document.getElementById('ratingLabelModern').textContent = labels[value];
}

// Initialize rating
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.rating-select-modern .star');
    const container = document.querySelector('.rating-select-modern');
    
    if (container) {
        container.addEventListener('mouseleave', function() {
            const current = document.getElementById('ratingValueModern').value;
            stars.forEach(star => {
                star.classList.remove('active');
                if (star.dataset.value <= current) {
                    star.classList.add('active');
                }
            });
        });
        
        stars.forEach(star => {
            star.addEventListener('mouseenter', function() {
                const value = this.dataset.value;
                stars.forEach(s => {
                    s.classList.remove('active');
                    if (s.dataset.value <= value) {
                        s.classList.add('active');
                    }
                });
            });
        });
        
        // Set default (5 stars)
        stars.forEach(star => {
            if (star.dataset.value <= 5) {
                star.classList.add('active');
            }
        });
    }
});
</script>