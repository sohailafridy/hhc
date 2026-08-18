<?php include '../includes/header.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name'], $_POST['email'], $_POST['message'], $_POST['rating'])) {
        $name = mysqli_real_escape_string($con, $_POST['name']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $message = mysqli_real_escape_string($con, $_POST['message']);
        $rating = intval($_POST['rating']);

        $insert_query = "INSERT INTO feedback (commenter_name, email, comment, stars, entity_id, status, created_at)
                       VALUES ('$name', '$email', '$message', $rating, 1, 1, NOW())";

        if (mysqli_query($con, $insert_query)) {
            $success_message = "Review submitted successfully!";
        } else {
            $error_message = "Error submitting review. Please try again.";
        }
    }
}
?>

<?php include BASE_PATH.'/includes/menu.php'; ?>

<?php
$reviews = [];
$reviews_query = "SELECT * FROM feedback WHERE entity_id = 1 ORDER BY created_at DESC";
$reviews_result = mysqli_query($con, $reviews_query);
$total_stars = 0;

if ($reviews_result) {
    while ($row = mysqli_fetch_assoc($reviews_result)) {
        $reviews[] = $row;
        $total_stars += isset($row['stars']) ? (int) $row['stars'] : 5;
    }
}

$review_count = count($reviews);
$average_rating = $review_count > 0 ? number_format($total_stars / $review_count, 1) : '5.0';
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
    --text: #0f172a;
    --text-light: #64748b;
    --bg: #f1f5f9;
    --glass: rgba(255,255,255,0.75);
    --glass-dark: rgba(255,255,255,0.15);
    --shadow: 0 25px 50px -12px rgba(0,0,0,0.15);
    --shadow-glow: 0 0 40px rgba(99,102,241,0.12);
}

/* ========================================
   GLOBAL
   ======================================== */
.section-padding {
    padding: 80px 0;
}

/* ========================================
   HERO SECTION - ANIMATED
   ======================================== */
.about-hero {
    position: relative;
    min-height: 85vh;
    display: flex;
    align-items: center;
    overflow: hidden;
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #4f46e5 100%);
}

.about-hero .hero-particles {
    position: absolute;
    inset: 0;
    z-index: 0;
    overflow: hidden;
}

.about-hero .hero-particles .particle {
    position: absolute;
    width: 8px;
    height: 8px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
    animation: floatParticle 25s infinite linear;
}

.about-hero .hero-particles .particle:nth-child(1) { top: 5%; left: 5%; animation-duration: 20s; }
.about-hero .hero-particles .particle:nth-child(2) { top: 15%; left: 90%; animation-duration: 25s; }
.about-hero .hero-particles .particle:nth-child(3) { top: 50%; left: 8%; animation-duration: 18s; }
.about-hero .hero-particles .particle:nth-child(4) { top: 65%; left: 85%; animation-duration: 22s; }
.about-hero .hero-particles .particle:nth-child(5) { top: 30%; left: 50%; animation-duration: 28s; }
.about-hero .hero-particles .particle:nth-child(6) { top: 80%; left: 25%; animation-duration: 20s; }
.about-hero .hero-particles .particle:nth-child(7) { top: 45%; left: 75%; animation-duration: 24s; }
.about-hero .hero-particles .particle:nth-child(8) { top: 10%; left: 40%; animation-duration: 30s; }

@keyframes floatParticle {
    0% { transform: translateY(0) rotate(0deg) scale(1); opacity: 0.2; }
    25% { opacity: 0.6; }
    50% { transform: translateY(-120px) rotate(180deg) scale(1.8); opacity: 0.3; }
    75% { opacity: 0.6; }
    100% { transform: translateY(0) rotate(360deg) scale(1); opacity: 0.2; }
}

.about-hero .hero-glow {
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    opacity: 0.3;
    animation: pulseGlow 8s ease-in-out infinite;
}

.about-hero .hero-glow.glow-1 {
    width: 500px;
    height: 500px;
    top: -150px;
    right: -100px;
    background: var(--primary);
    animation-delay: 0s;
}

.about-hero .hero-glow.glow-2 {
    width: 400px;
    height: 400px;
    bottom: -100px;
    left: -80px;
    background: var(--secondary);
    animation-delay: -4s;
}

.about-hero .hero-glow.glow-3 {
    width: 300px;
    height: 300px;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: var(--accent);
    animation-delay: -2s;
    opacity: 0.1;
}

@keyframes pulseGlow {
    0%, 100% { transform: scale(1) translate(0, 0); opacity: 0.25; }
    50% { transform: scale(1.2) translate(20px, -20px); opacity: 0.4; }
}

/* Hero Container */
.about-hero .hero-container {
    position: relative;
    z-index: 1;
    width: 100%;
    padding: 30px 0;
}

.about-hero .hero-content {
    text-align: center;
    max-width: 900px;
    margin: 0 auto;
}

.about-hero .hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 24px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 50px;
    font-size: 0.85rem;
    color: rgba(255,255,255,0.7);
    margin-bottom: 24px;
    animation: fadeInUp 0.8s ease-out;
}

.about-hero .hero-badge i {
    color: var(--accent);
}

.about-hero h1 {
    font-size: 4rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 16px;
    text-shadow: 0 4px 30px rgba(0,0,0,0.2);
    animation: fadeInUp 0.8s ease-out 0.2s backwards;
}

.about-hero h1 .highlight {
    background: linear-gradient(135deg, var(--primary-light), var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.about-hero .hero-subtitle {
    font-size: 1.2rem;
    color: rgba(255,255,255,0.7);
    max-width: 650px;
    margin: 0 auto 32px;
    line-height: 1.8;
    animation: fadeInUp 0.8s ease-out 0.4s backwards;
}

.about-hero .hero-stats {
    display: flex;
    justify-content: center;
    gap: 60px;
    flex-wrap: wrap;
    animation: fadeInUp 0.8s ease-out 0.6s backwards;
}

.about-hero .hero-stats .stat-item {
    text-align: center;
}

.about-hero .hero-stats .stat-item .number {
    font-size: 2.8rem;
    font-weight: 800;
    color: #fff;
    display: block;
    background: linear-gradient(135deg, var(--primary-light), var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.about-hero .hero-stats .stat-item .label {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.5);
    font-weight: 500;
    letter-spacing: 0.5px;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ========================================
   FOUNDER SECTION - GLASSMORPHISM
   ======================================== */
.founder-section {
    background: var(--bg);
    padding: 80px 0;
    position: relative;
}

.founder-section .section-label {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 6px 18px;
    background: rgba(99,102,241,0.08);
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--primary);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 12px;
}

.founder-card {
    background: var(--glass);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border-radius: 32px;
    border: 1px solid rgba(255,255,255,0.5);
    box-shadow: var(--shadow), var(--shadow-glow);
    padding: 40px;
    transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
}

.founder-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(99,102,241,0.04), transparent 70%);
    pointer-events: none;
}

.founder-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow), var(--shadow-glow);
}

.founder-card .founder-avatar-wrapper {
    position: relative;
    width: 140px;
    height: 140px;
    margin: 0 auto 20px;
}

.founder-card .founder-avatar-wrapper .avatar-ring {
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    background: conic-gradient(from 0deg, var(--primary), var(--secondary), var(--accent), var(--primary));
    animation: spinRing 8s linear infinite;
}

@keyframes spinRing {
    to { transform: rotate(360deg); }
}

.founder-card .founder-avatar-wrapper .avatar-inner {
    position: relative;
    z-index: 1;
    padding: 4px;
    border-radius: 50%;
    background: #fff;
}

.founder-card .founder-avatar-wrapper img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    display: block;
}

.founder-card .founder-avatar-wrapper .avatar-badge {
    position: absolute;
    bottom: 4px;
    right: 4px;
    z-index: 2;
    background: var(--accent);
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    border: 3px solid #fff;
    animation: pulseDot 2s ease-in-out infinite;
}

@keyframes pulseDot {
    0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(245,158,11,0.4); }
    50% { transform: scale(1.1); box-shadow: 0 0 0 10px rgba(245,158,11,0); }
}

.founder-card h3 {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 4px;
}

.founder-card .founder-title {
    color: var(--primary);
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 16px;
}

.founder-card .founder-bio {
    color: var(--text-light);
    line-height: 1.9;
    font-size: 1rem;
}

/* Founder Stats Grid */
.founder-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-top: 24px;
}

.founder-stats-grid .stat-box {
    background: rgba(0,0,0,0.02);
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    border: 1px solid rgba(0,0,0,0.04);
    transition: all 0.3s ease;
}

.founder-stats-grid .stat-box:hover {
    background: rgba(99,102,241,0.04);
    border-color: var(--primary-light);
    transform: translateY(-3px);
}

.founder-stats-grid .stat-box .number {
    font-size: 2rem;
    font-weight: 800;
    color: var(--primary);
}

.founder-stats-grid .stat-box .label {
    font-size: 0.85rem;
    color: var(--text-light);
    font-weight: 500;
}

/* ========================================
   CO-FOUNDER SECTION
   ======================================== */
.cofounder-section {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
    padding: 80px 0;
    color: white;
    position: relative;
    overflow: hidden;
}

.cofounder-section .section-label-light {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 6px 18px;
    background: rgba(255,255,255,0.06);
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    color: rgba(255,255,255,0.6);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 12px;
}

.cofounder-section .section-label-light i {
    color: var(--accent);
}

.cofounder-card {
    background: var(--glass-dark);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 28px;
    border: 1px solid rgba(255,255,255,0.06);
    padding: 35px;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.cofounder-card:hover {
    transform: translateY(-4px);
    border-color: rgba(255,255,255,0.12);
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4);
}

.cofounder-card h3 {
    font-size: 1.6rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 4px;
}

.cofounder-card .cofounder-title {
    color: rgba(255,255,255,0.5);
    font-weight: 500;
    font-size: 1rem;
    margin-bottom: 16px;
}

.cofounder-card .cofounder-bio {
    color: rgba(255,255,255,0.65);
    line-height: 1.9;
    font-size: 1rem;
}

.cofounder-card .cofounder-highlights {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 20px;
}

.cofounder-card .cofounder-highlights .highlight-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: rgba(255,255,255,0.04);
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.04);
}

.cofounder-card .cofounder-highlights .highlight-item i {
    color: var(--accent);
    font-size: 0.9rem;
}

.cofounder-card .cofounder-highlights .highlight-item span {
    color: rgba(255,255,255,0.7);
    font-size: 0.85rem;
}

/* ========================================
   MISSION SECTION
   ======================================== */
.mission-section {
    background: var(--bg);
    padding: 80px 0;
}

.mission-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-top: 30px;
}

.mission-card {
    background: var(--glass);
    backdrop-filter: blur(16px);
    border-radius: 24px;
    padding: 30px;
    border: 1px solid rgba(255,255,255,0.3);
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
}

.mission-card::before {
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

.mission-card:hover::before {
    opacity: 1;
}

.mission-card:hover {
    transform: translateY(-6px) scale(1.01);
    box-shadow: var(--shadow), var(--shadow-glow);
}

.mission-card .icon-box {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    margin-bottom: 16px;
}

.mission-card h4 {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--text);
    margin-bottom: 8px;
}

.mission-card p {
    color: var(--text-light);
    font-size: 0.95rem;
    line-height: 1.7;
    margin: 0;
}

/* ========================================
   REVIEWS SECTION
   ======================================== */
.reviews-section {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
    padding: 80px 0;
    color: white;
    position: relative;
    overflow: hidden;
}

.reviews-section .section-label-light {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 6px 18px;
    background: rgba(255,255,255,0.06);
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    color: rgba(255,255,255,0.6);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 12px;
}

.reviews-section .section-label-light i {
    color: var(--accent);
}

.reviews-grid {
    display: grid;
    gap: 20px;
    margin-top: 30px;
}

.review-card-glass {
    background: var(--glass-dark);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-radius: 20px;
    padding: 24px 28px;
    border: 1px solid rgba(255,255,255,0.04);
    transition: all 0.3s ease;
}

.review-card-glass:hover {
    border-color: rgba(255,255,255,0.1);
    transform: translateX(6px);
}

.review-card-glass .review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.review-card-glass .review-name {
    font-weight: 700;
    color: #fff;
}

.review-card-glass .review-email {
    color: rgba(255,255,255,0.3);
    font-size: 0.85rem;
}

.review-card-glass .review-stars {
    color: #f59e0b;
    font-size: 0.9rem;
}

.review-card-glass .review-comment {
    color: rgba(255,255,255,0.6);
    font-size: 0.95rem;
    line-height: 1.7;
    margin: 6px 0 0;
}

.review-card-glass .review-date {
    color: rgba(255,255,255,0.2);
    font-size: 0.8rem;
    margin-top: 8px;
}

/* Review Form Glass */
.review-form-glass {
    background: var(--glass-dark);
    backdrop-filter: blur(16px);
    border-radius: 20px;
    padding: 30px;
    border: 1px solid rgba(255,255,255,0.04);
    margin-top: 30px;
}

.review-form-glass h4 {
    font-weight: 700;
    color: #fff;
    margin-bottom: 20px;
}

.review-form-glass .form-control {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 12px 16px;
    color: #fff;
    transition: all 0.3s ease;
}

.review-form-glass .form-control:focus {
    background: rgba(255,255,255,0.06);
    border-color: var(--primary-light);
    box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
}

.review-form-glass .form-control::placeholder {
    color: rgba(255,255,255,0.3);
}

.review-form-glass .form-label {
    color: rgba(255,255,255,0.6);
    font-weight: 500;
    font-size: 0.85rem;
}

.review-form-glass .rating-select {
    display: flex;
    gap: 6px;
    margin: 8px 0 16px;
}

.review-form-glass .rating-select .star {
    font-size: 1.8rem;
    color: rgba(255,255,255,0.1);
    cursor: pointer;
    transition: all 0.2s ease;
}

.review-form-glass .rating-select .star:hover,
.review-form-glass .rating-select .star.active {
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
   CONTACT CTA
   ======================================== */
.cta-section {
    background: var(--bg);
    padding: 60px 0;
}

.cta-card {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border-radius: 28px;
    padding: 50px;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(79,70,229,0.3);
}

.cta-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
}

.cta-card::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.03);
    border-radius: 50%;
}

.cta-card h2 {
    font-size: 2.2rem;
    font-weight: 800;
    margin-bottom: 12px;
    position: relative;
    z-index: 1;
}

.cta-card p {
    opacity: 0.85;
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto 24px;
    position: relative;
    z-index: 1;
}

.cta-card .cta-buttons {
    display: flex;
    justify-content: center;
    gap: 14px;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
}

.cta-card .btn-cta-primary {
    background: white;
    color: var(--primary);
    border: none;
    padding: 12px 32px;
    border-radius: 50px;
    font-weight: 700;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.cta-card .btn-cta-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}

.cta-card .btn-cta-secondary {
    background: rgba(255,255,255,0.1);
    color: white;
    border: 1px solid rgba(255,255,255,0.2);
    padding: 12px 32px;
    border-radius: 50px;
    font-weight: 700;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.cta-card .btn-cta-secondary:hover {
    background: rgba(255,255,255,0.15);
    transform: translateY(-3px);
}

/* ========================================
   RESPONSIVE
   ======================================== */
@media (max-width: 992px) {
    .about-hero h1 { font-size: 3rem; }
    .about-hero .hero-stats { gap: 30px; }
    .founder-card .founder-avatar-wrapper { width: 120px; height: 120px; }
    .cofounder-card .cofounder-highlights { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
    .about-hero { min-height: 70vh; }
    .about-hero h1 { font-size: 2.2rem; }
    .about-hero .hero-subtitle { font-size: 1rem; }
    .about-hero .hero-stats { gap: 20px; }
    .about-hero .hero-stats .stat-item .number { font-size: 2rem; }
    .founder-card { padding: 24px; }
    .cofounder-card { padding: 24px; }
    .cta-card { padding: 30px 20px; }
    .cta-card h2 { font-size: 1.6rem; }
    .mission-grid { grid-template-columns: 1fr; }
    .founder-stats-grid { grid-template-columns: 1fr 1fr; }
}

@media (max-width: 480px) {
    .about-hero h1 { font-size: 1.8rem; }
    .about-hero .hero-stats { flex-direction: column; gap: 12px; }
    .founder-card .founder-avatar-wrapper { width: 100px; height: 100px; }
    .review-card-glass { padding: 18px; }
    .review-form-glass { padding: 20px; }
    .founder-stats-grid { grid-template-columns: 1fr; }
}
</style>

<!-- ======================================== -->
<!-- HERO SECTION -->
<!-- ======================================== -->
<section class="about-hero">
    <!-- Particles -->
    <div class="hero-particles">
        <div class="particle"></div>
        <div class="particle"></div>
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
    <div class="hero-glow glow-3"></div>

    <div class="container hero-container">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-heartbeat"></i> Building Trusted Healthcare Discovery
            </div>
            <h1>
                Founder & Developer of <br>
                <span class="highlight">DoctorApp</span>
            </h1>
            <p class="hero-subtitle">
                Aik software developer hoon aur DoctorApp ko is vision ke saath build kiya hai ke patients aur unki families hospitals, doctors, laboratories aur blood banks ki maloomat aik hi platform par professionally, quickly aur asaani se hasil kar saken.
            </p>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="number">1</span>
                    <span class="label">Unified Platform</span>
                </div>
                <div class="stat-item">
                    <span class="number">4</span>
                    <span class="label">Core Healthcare Categories</span>
                </div>
                <div class="stat-item">
                    <span class="number">24/7</span>
                    <span class="label">Accessibility</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ======================================== -->
<!-- FOUNDER SECTION -->
<!-- ======================================== -->
<section class="founder-section">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="founder-card">
                    <div class="founder-avatar-wrapper">
                        <div class="avatar-ring"></div>
                        <div class="avatar-inner">
                            <img src="<?=BASE_URL?>includes/uploads/founder.jpg" alt="Founder">
                        </div>
                        <div class="avatar-badge">
                            <i class="fas fa-shield-heart"></i>
                        </div>
                    </div>
                    <h3>Sohail Afridy</h3>
                    <div class="founder-title">Founder & Lead Developer</div>
                    <p class="founder-bio">
                        Main is system ko lagataar improve karne par kaam kar raha hoon, aur users ki feedback mere liye bohat aham hai kyun ke isi ki bunyaad par platform ko aur zyada useful, modern aur user-friendly banaya ja sakta hai.
                    </p>

                    <div class="founder-stats-grid">
                        <div class="stat-box">
                            <div class="number">25+</div>
                            <div class="label">Years Experience</div>
                        </div>
                        <div class="stat-box">
                            <div class="number">100%</div>
                            <div class="label">User Focused</div>
                        </div>
                        <div class="stat-box">
                            <div class="number">24/7</div>
                            <div class="label">Support Mindset</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6" data-aos="fade-left">
                <div class="founder-card" style="background: var(--glass-dark); color: white; border-color: rgba(255,255,255,0.06);">
                    <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px;">
                        <div style="width:60px; height:60px; border-radius:50%; background:linear-gradient(135deg, var(--primary), var(--secondary)); display:flex; align-items:center; justify-content:center; font-size:1.8rem; color:white; flex-shrink:0;">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div>
                            <h3 style="font-size:1.2rem; color:#fff; margin:0;">Mission & Direction</h3>
                            <p style="color:rgba(255,255,255,0.4); margin:0; font-size:0.85rem;">Vision-led product design</p>
                        </div>
                    </div>
                    <p style="color:rgba(255,255,255,0.6); line-height:1.8; font-size:1rem;">
                        DoctorApp ka maqsad healthcare information ko zyada accessible, organized aur reliable banana hai, taake users apna qeemti waqt bachate hue behtar decisions le saken.
                    </p>
                    <div style="display:grid; gap:12px; margin-top:20px;">
                        <div style="display:flex; align-items:center; gap:12px; padding:12px 16px; background:rgba(255,255,255,0.04); border-radius:12px; border:1px solid rgba(255,255,255,0.04);">
                            <i class="fas fa-lightbulb" style="color:var(--accent);"></i>
                            <span style="color:rgba(255,255,255,0.6);">Purpose-driven thinking</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:12px; padding:12px 16px; background:rgba(255,255,255,0.04); border-radius:12px; border:1px solid rgba(255,255,255,0.04);">
                            <i class="fas fa-users" style="color:var(--accent);"></i>
                            <span style="color:rgba(255,255,255,0.6);">User-first experience</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:12px; padding:12px 16px; background:rgba(255,255,255,0.04); border-radius:12px; border:1px solid rgba(255,255,255,0.04);">
                            <i class="fas fa-rotate" style="color:var(--accent);"></i>
                            <span style="color:rgba(255,255,255,0.6);">Always evolving</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ======================================== -->
<!-- CO-FOUNDER SECTION -->
<!-- ======================================== -->
<section class="cofounder-section">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="cofounder-card">
                    <div class="section-label-light">
                        <i class="fas fa-users"></i> Co-Founder Spotlight
                    </div>
                    <h3>Abdul Qadir Afridi</h3>
                    <div class="cofounder-title">Mobile App Developer</div>
                    <p class="cofounder-bio">
                        Co-Founder Abdul Qadir Afridi aik skilled mobile app developer hain jinhon ne is project ke liye mobile application develop ki hai. Unhon ne app ko is tarah design aur build kiya ke aik user ko jo essential features darkar hote hain, woh sab us mein asaani ke saath available hon.
                    </p>
                    <div class="cofounder-highlights">
                        <div class="highlight-item">
                            <i class="fas fa-handshake"></i>
                            <span>App-focused collaboration</span>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-comments"></i>
                            <span>User-friendly features</span>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-chart-line"></i>
                            <span>Digital growth support</span>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-mobile-screen"></i>
                            <span>Mobile accessibility</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6" data-aos="fade-left">
                <div class="cofounder-card" style="display:flex; flex-direction:column; align-items:center; text-align:center;">
                    <div style="width:180px; height:180px; border-radius:50%; overflow:hidden; border:4px solid rgba(255,255,255,0.06); margin-bottom:20px;">
                        <img src="<?=BASE_URL?>includes/uploads/co-founder.jpeg" alt="Co-Founder" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; width:100%; margin-top:12px;">
                        <div style="padding:16px; background:rgba(255,255,255,0.04); border-radius:12px; border:1px solid rgba(255,255,255,0.04);">
                            <div style="font-size:1.4rem; font-weight:800; color:var(--accent);">Vision</div>
                            <div style="color:rgba(255,255,255,0.4); font-size:0.75rem;">Useful mobile experience</div>
                        </div>
                        <div style="padding:16px; background:rgba(255,255,255,0.04); border-radius:12px; border:1px solid rgba(255,255,255,0.04);">
                            <div style="font-size:1.4rem; font-weight:800; color:var(--accent);">Support</div>
                            <div style="color:rgba(255,255,255,0.4); font-size:0.75rem;">App design & development</div>
                        </div>
                        <div style="padding:16px; background:rgba(255,255,255,0.04); border-radius:12px; border:1px solid rgba(255,255,255,0.04);">
                            <div style="font-size:1.4rem; font-weight:800; color:var(--accent);">Impact</div>
                            <div style="color:rgba(255,255,255,0.4); font-size:0.75rem;">User needs feature delivery</div>
                        </div>
                    </div>
                    <p style="color:rgba(255,255,255,0.4); font-size:0.9rem; margin-top:16px; line-height:1.6;">
                        Abdul Qadir Afridi ne is project ke mobile side ko strong banaya hai, taake users ko web ke saath aik complete app-based experience bhi mil sake.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ======================================== -->
<!-- MISSION SECTION -->
<!-- ======================================== -->
<section class="mission-section">
    <div class="container">
        <div style="text-align:center; max-width:700px; margin:0 auto 20px;">
            <div class="section-label" style="justify-content:center;">
                <i class="fas fa-bullseye"></i> Mission & Core Values
            </div>
            <h2 style="font-size:2.2rem; font-weight:800; color:var(--text); margin-bottom:12px;">
                A Smarter Healthcare Directory for Pakistan
            </h2>
            <p style="color:var(--text-light); font-size:1.05rem; line-height:1.8;">
                Mera mission yeh hai ke poore Pakistan ke hospitals, doctors, laboratories aur blood banks ka mukammal aur bharosa-mand record aik hi platform par faraham kiya jaye.
            </p>
        </div>

        <div class="mission-grid">
            <div class="mission-card" data-aos="fade-up" data-aos-delay="0">
                <div class="icon-box"><i class="fas fa-hospital-user"></i></div>
                <h4>Easy Local Discovery</h4>
                <p>Patients apne qareeb relevant healthcare services bina pareshani ke dhoondh saken.</p>
            </div>

            <div class="mission-card" data-aos="fade-up" data-aos-delay="100">
                <div class="icon-box"><i class="fas fa-badge-check"></i></div>
                <h4>Reliable Information</h4>
                <p>Listings ko verify aur organize kar ke better trust aur decision support diya jaye.</p>
            </div>

            <div class="mission-card" data-aos="fade-up" data-aos-delay="200">
                <div class="icon-box"><i class="fas fa-mobile-screen-button"></i></div>
                <h4>Future-Ready Expansion</h4>
                <p>Project ko progressively improve karte hue dedicated mobile app tak expand kiya jaye.</p>
            </div>

            <div class="mission-card" data-aos="fade-up" data-aos-delay="300">
                <div class="icon-box"><i class="fas fa-clock-rotate-left"></i></div>
                <h4>Time-Saving Experience</h4>
                <p>Healthcare information tak quick access de kar users ka qeemti waqt bachaya jaye.</p>
            </div>
        </div>
    </div>
</section>

<!-- ======================================== -->
<!-- REVIEWS SECTION -->
<!-- ======================================== -->
<section class="reviews-section">
    <div class="container">
        <div style="text-align:center; max-width:700px; margin:0 auto;">
            <div class="section-label-light" style="justify-content:center;">
                <i class="fas fa-star"></i> Community Feedback
            </div>
            <h2 style="font-size:2.2rem; font-weight:800; color:#fff; margin-bottom:8px;">
                <?= htmlspecialchars($average_rating) ?> / 5 Average Sentiment
            </h2>
            <p style="color:rgba(255,255,255,0.4); font-size:1rem;">
                <?= $review_count > 0 ? $review_count . ' users ne direct feedback share kiya hai.' : 'Abhi reviews ka silsila start ho raha hai.' ?>
            </p>
        </div>

        <div class="reviews-grid">
            <?php if ($review_count > 0): ?>
                <?php foreach ($reviews as $row): ?>
                    <div class="review-card-glass" data-aos="fade-up">
                        <div class="review-header">
                            <div>
                                <span class="review-name"><?= htmlspecialchars($row['commenter_name']); ?></span>
                                <span class="review-email"><?= htmlspecialchars($row['email'] ?? ''); ?></span>
                            </div>
                            <div class="review-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= $row['stars'] ? '' : 'text-muted'; ?>" style="opacity: <?= $i <= $row['stars'] ? '1' : '0.2'; ?>;"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="review-comment"><?= nl2br(htmlspecialchars($row['comment'])); ?></p>
                        <div class="review-date">
                            <i class="fas fa-calendar me-1"></i> <?= date('d M Y', strtotime($row['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center; padding:40px 20px; background:var(--glass-dark); border-radius:20px; border:1px solid rgba(255,255,255,0.04);">
                    <i class="fas fa-comment-slash" style="font-size:2.5rem; color:rgba(255,255,255,0.1);"></i>
                    <h4 style="color:rgba(255,255,255,0.4); margin-top:12px;">No Reviews Yet</h4>
                    <p style="color:rgba(255,255,255,0.2);">Be the first to share your experience!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Review Form -->
        <div class="review-form-glass" data-aos="fade-up">
            <h4><i class="fas fa-pen me-2" style="color:var(--accent);"></i> Leave a Review</h4>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" style="background:rgba(34,197,94,0.1); border:none; color:#4ade80; border-radius:12px; padding:12px 20px;">
                    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" style="background:rgba(239,68,68,0.1); border:none; color:#f87171; border-radius:12px; padding:12px 20px;">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Your Name</label>
                        <input type="text" class="form-control" name="name" required placeholder="Enter your name">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required placeholder="your@email.com">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rating</label>
                    <div class="rating-select">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <span class="star" data-value="<?= $i; ?>" onclick="setRatingAbout(this)">
                                <i class="fas fa-star"></i>
                            </span>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="ratingValueAbout" value="5">
                    <span style="color:rgba(255,255,255,0.3);" id="ratingLabelAbout">Excellent</span>
                </div>

                <div class="mb-3">
                    <label class="form-label">Your Review</label>
                    <textarea class="form-control" name="message" rows="4" required placeholder="Share your experience..."></textarea>
                </div>

                <input type="hidden" name="entity_id" value="1">
                <button type="submit" class="btn-submit-glass">
                    <i class="fas fa-paper-plane me-2"></i> Submit Review
                </button>
            </form>
        </div>
    </div>
</section>

<!-- ======================================== -->
<!-- CTA SECTION -->
<!-- ======================================== -->
<section class="cta-section">
    <div class="container">
        <div class="cta-card" data-aos="zoom-in">
            <h2>Need Help or Want to Collaborate?</h2>
            <p>Agar aap ke paas healthcare listing information, product ideas ya direct support request hai, to contact channels hamesha open hain.</p>
            <div class="cta-buttons">
                <a href="mailto:sohail.it99@gmail.com" class="btn-cta-primary">
                    <i class="fas fa-envelope me-2"></i> Email Us
                </a>
                <a href="tel:+923371320001" class="btn-cta-secondary">
                    <i class="fas fa-phone me-2"></i> Call Us
                </a>
                <a href="https://wa.me/+923371320001" class="btn-cta-secondary">
                    <i class="fab fa-whatsapp me-2"></i> WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ======================================== -->
<!-- FOOTER -->
<!-- ======================================== -->
<?php include BASE_PATH.'/includes/footer.php'; ?>

<script>
// ============================================
// ABOUT PAGE RATING
// ============================================
function setRatingAbout(el) {
    const stars = document.querySelectorAll('.review-form-glass .rating-select .star');
    const value = el.dataset.value;
    
    stars.forEach(star => {
        star.classList.remove('active');
        if (star.dataset.value <= value) {
            star.classList.add('active');
        }
    });
    
    document.getElementById('ratingValueAbout').value = value;
    
    const labels = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
    document.getElementById('ratingLabelAbout').textContent = labels[value];
}

document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.review-form-glass .rating-select .star');
    const container = document.querySelector('.review-form-glass .rating-select');
    
    if (container) {
        container.addEventListener('mouseleave', function() {
            const current = document.getElementById('ratingValueAbout').value;
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