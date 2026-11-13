<?php 
// ============================================================
// 1. SEO FIX: Title aur Description set karein
// ============================================================
$GLOBALS['page_title'] = "About Us — Pakistan's Trusted Healthcare Directory | SehatNexa";
$GLOBALS['page_description'] = "Learn about SehatNexa's mission to make healthcare accessible in Pakistan. Meet our team and discover how we connect patients with verified doctors, hospitals, and labs.";

// 2. Header Include karein
include '../includes/header.php'; 
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name'], $_POST['email'], $_POST['message'], $_POST['rating'])) {
        $name    = mysqli_real_escape_string($con, $_POST['name']);
        $email   = mysqli_real_escape_string($con, $_POST['email']);
        $message = mysqli_real_escape_string($con, $_POST['message']);
        $rating  = intval($_POST['rating']);
        $insert_query = "INSERT INTO feedback (commenter_name, email, comment, stars, user_id, status, created_at)
                         VALUES ('$name', '$email', '$message', $rating, 1, 1, NOW())";
        if (mysqli_query($con, $insert_query)) {
            $success_message = "Review submitted successfully!";
        } else {
            $error_message = "Error submitting review. Please try again.";
        }
    }
}

$reviews = [];
$reviews_query  = "SELECT * FROM feedback WHERE user_id = 1 ORDER BY created_at DESC";
$reviews_result = mysqli_query($con, $reviews_query);
$total_stars    = 0;
if ($reviews_result) {
    while ($row = mysqli_fetch_assoc($reviews_result)) {
        $reviews[]    = $row;
        $total_stars += isset($row['stars']) ? (int)$row['stars'] : 5;
    }
}
$review_count   = count($reviews);
$average_rating = $review_count > 0 ? number_format($total_stars / $review_count, 1) : '5.0';
?>

<?php include BASE_PATH.'/includes/menu.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>style/about-us.css?v=<?= @filemtime(BASE_PATH . '/style/about-us.css') ?: time() ?>">

<!-- ══ PAGE HEADER ══════════════════════════════════════════ -->
<section class="about-page-header">
    <div class="container text-center">
        <span class="section-chip mb-3">
            <i class="fas fa-heartbeat"></i> About SehatNexa
        </span>
        <h1>Pakistan ka Trusted Healthcare Directory</h1>
        <p>Doctors, hospitals, labs aur blood banks — ek jagah, hamesha available.</p>
    </div>
</section>

<!-- ══ STORY / WELCOME ═══════════════════════════════════════ -->
<section class="about-story-section section-padding">
    <div class="container">
        <div class="row g-4 align-items-stretch">

            <!-- Welcome + founder note -->
            <div class="col-lg-7" data-aos="fade-right">
                <div class="content-panel about-parallax-card story-panel">
                    <span class="section-chip section-chip-soft">
                        <i class="fas fa-hand-holding-heart"></i> Khush Aamdeed
                    </span>
                    <h2 class="section-title about-section-title">SehatNexa mein Khush Aamdeed!</h2>
                    <div class="story-copy">
                        <p class="story-lead">
                            Ye ek mukammal healthcare directory hai jo aapke liye medical services dhoondna asaan banati hai.
                        </p>
                    </div>
                    <blockquote class="story-founder-note">
                        <i class="fas fa-quote-left"></i>
                        <p>
                            Ek software developer hone ke naate, maine ek aam masla mehsoos kiya: jab logon ko medical madad ki zaroorat hoti hai, to unhe apne ilaqe ke doctors, hospitals aur labs ki sahi maloomat ek jagah nahi milti. Maine SehatNexa isi masle ko hal karne ke liye banaya hai.
                        </p>
                        <footer><i class="fas fa-shield-heart"></i> Founder, SehatNexa</footer>
                    </blockquote>
                </div>
            </div>

            <!-- Vision + What we do -->
            <div class="col-lg-5" data-aos="fade-left">
                <div class="insight-stack">
                    <div class="content-panel insight-panel accent-panel about-parallax-card">
                        <span class="mini-label"><i class="fas fa-eye me-1"></i> Hamara Vision (Our Vision)</span>
                        <h3>Behtar healthcare system</h3>
                        <p>
                            Hamara vision ek aisa behtar healthcare system banana hai jahan patients aur unke families bina kisi pareshani ke sahi doctor aur medical facility tak asaani se pohanch saken.
                        </p>
                    </div>
                    <div class="content-panel content-panel-dark insight-panel about-parallax-card">
                        <span class="mini-label"><i class="fas fa-briefcase-medical me-1"></i> Hum Kya Karte Hain (What We Do)</span>
                        <h3>Poora healthcare network, ek jagah</h3>
                        <p>
                            Hum aapke liye poora healthcare network ek jagah par le kar aaye hain. Chahe aapko koi specialist doctor dhoondhna ho, bharosa mand hospital, diagnostic laboratory, ya emergency ke liye blood bank, SehatNexa aapko ye saari maloomat ek hi platform par foran faraham karta hai.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services -->
        <div class="story-services" data-aos="fade-up">
            <div class="value-card">
                <span><i class="fas fa-user-doctor"></i></span>
                <h4>Specialist Doctors</h4>
                <p>Apne ilaqe ke sahi specialist tak asaani se pohanchein.</p>
            </div>
            <div class="value-card">
                <span><i class="fas fa-hospital"></i></span>
                <h4>Bharosa Mand Hospitals</h4>
                <p>Hospitals ki sahi maloomat ek hi jagah par.</p>
            </div>
            <div class="value-card">
                <span><i class="fas fa-flask-vial"></i></span>
                <h4>Diagnostic Laboratories</h4>
                <p>Tests ke liye qareebi labs foran dhoondhein.</p>
            </div>
            <div class="value-card">
                <span><i class="fas fa-droplet"></i></span>
                <h4>Emergency Blood Banks</h4>
                <p>Emergency me blood bank ki maloomat jaldi hasil karein.</p>
            </div>
        </div>

        <div class="support-quote story-closing" data-aos="fade-up">
            <i class="fas fa-heart-pulse me-2"></i>
            Hamara maqsad healthcare ko dhoondhna asaan, tez aur sab ke liye mumkin banana hai.
        </div>
    </div>
</section>

<!-- Ad Space 1 -->
<div class="container my-4" data-aos="fade-up">
    <div class="ad-slot text-center" style="min-height:90px; background:#f8f9fa; border:1px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center;">
        <!-- SpeedyAds Code Yahan Paste Karein -->
        <span class="text-muted small">Advertisement Space</span>
    </div>
</div>

<!-- ══ TEAM SECTION ═════════════════════════════════════════ -->
<section class="section-padding">
    <div class="container">

        <div class="section-heading text-center" data-aos="fade-up">
            <span class="section-chip section-chip-soft">
                <i class="fas fa-users"></i> Our Team
            </span>
            <h2 class="section-title">MEET THE PEOPLE BEHIND SEHATNEXA</h2>
        </div>

        <div class="row g-4 flex-column flex-lg-row">

            <!-- ── FOUNDER ── -->
            <div class="col-12" data-aos="fade-up">
                <div class="team-shell about-parallax-card">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-5">
                            <div class="team-visual-card">
                                <div class="team-image-wrap">
                                    <img src="<?=BASE_URL?>includes/uploads/founder.jpg" alt="Founder of SehatNexa" class="img-fluid">
                                    <div class="team-image-caption">
                                        <i class="fas fa-shield-heart"></i> Founder of SehatNexa
                                    </div>
                                </div>
                                <div class="team-profile-panel">
                                    <div class="team-avatar" style="background: linear-gradient(135deg,#1C3F8F,#00A8CC);">
                                        <i class="fas fa-code"></i>
                                    </div>
                                    <div>
                                        <span class="mini-label">Founder & Developer</span>
                                        <h3>Sohail Afridy</h3>
                                        <p>Full-stack Web Developer</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="team-copy">
                                <span class="section-chip section-chip-soft mb-3">
                                    <i class="fas fa-user-shield"></i> Founder spotlight
                                </span>
                                <h3 class="team-name">Sohail Afridy — Founder & Lead Developer</h3>
                                <p class="team-bio">
                                    Main ek software developer hoon. SehatNexa ko is vision ke saath build kiya ke patients aur unki families hospitals, doctors, labs aur blood banks ki maloomat ek hi platform par asaani se hasil kar saken.
                                </p>
                                <div class="team-highlights">
                                    <div class="team-highlight-card">
                                        <span><i class="fas fa-laptop-code"></i></span>
                                        <div>
                                            <h4>Full-stack Development</h4>
                                            <p>Web platform ka design, backend aur frontend sab develop kiya.</p>
                                        </div>
                                    </div>
                                    <div class="team-highlight-card">
                                        <span><i class="fas fa-database"></i></span>
                                        <div>
                                            <h4>Data Architecture</h4>
                                            <p>Healthcare data ko structured aur searchable form mein manage kiya.</p>
                                        </div>
                                    </div>
                                    <div class="team-highlight-card">
                                        <span><i class="fas fa-rotate"></i></span>
                                        <div>
                                            <h4>Continuous Improvement</h4>
                                            <p>User feedback se driven updates aur feature additions.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="team-actions mt-3">
                                    <a href="mailto:sohail.it99@gmail.com" class="btn about-btn-primary">
                                        <i class="fas fa-envelope me-2"></i>Contact
                                    </a>
                                    <a href="https://wa.me/+923049744263" class="btn about-btn-outline">
                                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── CO-FOUNDER ── -->
            <div class="col-12" data-aos="fade-up" data-aos-delay="100">
                <div class="team-shell about-parallax-card">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-5">
                            <div class="team-visual-card">
                                <div class="team-image-wrap">
                                    <img src="<?=BASE_URL?>includes/uploads/co-founder.jpeg" alt="Co-Founder" class="img-fluid">
                                    <div class="team-image-caption">
                                        <i class="fas fa-mobile-screen-button"></i> Co-Founder
                                    </div>
                                </div>
                                <div class="team-profile-panel">
                                    <div class="team-avatar" style="background: linear-gradient(135deg,#27C96A,#00C9A7);">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <div>
                                        <span class="mini-label">Co-Founder</span>
                                        <h3>Abdul Qadir Afridi</h3>
                                        <p>Mobile App Developer</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="team-copy">
                                <span class="section-chip section-chip-soft mb-3" style="color:#27C96A; background:rgba(39,201,106,0.09); border-color:rgba(39,201,106,0.14);">
                                    <i class="fas fa-users"></i> Co-Founder spotlight
                                </span>
                                <h3 class="team-name">Abdul Qadir Afridi — Co-Founder</h3>
                                <p class="team-bio">
                                    Abdul Qadir Afridi ek skilled mobile app developer hain jinhon ne is project ke liye mobile application develop ki. App ko is tarah design kiya ke users ko zaroori features asaani se mil sakein.
                                </p>
                                <div class="team-highlights">
                                    <div class="team-highlight-card">
                                        <span style="background:linear-gradient(135deg,rgba(39,201,106,0.14),rgba(0,201,167,0.16)); color:#27C96A;"><i class="fas fa-mobile-screen-button"></i></span>
                                        <div>
                                            <h4>Mobile App Development</h4>
                                            <p>Web platform ko mobile experience ke saath connect kiya.</p>
                                        </div>
                                    </div>
                                    <div class="team-highlight-card">
                                        <span style="background:linear-gradient(135deg,rgba(39,201,106,0.14),rgba(0,201,167,0.16)); color:#27C96A;"><i class="fas fa-comments"></i></span>
                                        <div>
                                            <h4>User-Friendly Features</h4>
                                            <p>Normal user ke liye practical aur useful features include kiye.</p>
                                        </div>
                                    </div>
                                    <div class="team-highlight-card">
                                        <span style="background:linear-gradient(135deg,rgba(39,201,106,0.14),rgba(0,201,167,0.16)); color:#27C96A;"><i class="fas fa-chart-line"></i></span>
                                        <div>
                                            <h4>Digital Growth</h4>
                                            <p>Web se aage barha kar mobile accessibility tak pohanchaya.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="team-actions mt-3">
                                    <a href="https://wa.me/+923049744263" class="btn about-btn-primary" style="background:linear-gradient(135deg,#27C96A,#00C9A7); box-shadow:0 18px 45px rgba(39,201,106,0.28);">
                                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ══ MISSION ══════════════════════════════════════════════ -->
<section class="mission-section section-padding">
    <div class="container">
        <div class="section-heading text-center" data-aos="fade-up">
            <span class="section-chip section-chip-soft">
                <i class="fas fa-bullseye"></i> Mission
            </span>
            <h2 class="section-title">Poore Pakistan ke liye ek mukammal Healthcare Directory</h2>
            <p class="section-intro">Hospitals, doctors, labs aur blood banks ka bharosa-mand record ek hi platform par.</p>
        </div>
        <div class="row g-4 align-items-center">
            <div class="col-lg-5" data-aos="fade-right">
                <div class="mission-visual about-parallax-card">
                    <img src="<?=BASE_URL?>admin/inc/uploads/default/hosp.jpg" alt="Our Mission" class="img-fluid">
                    <div class="mission-badge">
                        <i class="fas fa-globe-asia"></i> Nationwide growth vision
                    </div>
                </div>
            </div>
            <div class="col-lg-7" data-aos="fade-left">
                <div class="mission-card-grid">
                    <div class="mission-feature-card">
                        <span class="feature-icon"><i class="fas fa-hospital-user"></i></span>
                        <h4>Easy Local Discovery</h4>
                        <p>Patients apne qareeb relevant healthcare services bina pareshani ke dhoondh saken.</p>
                    </div>
                    <div class="mission-feature-card">
                        <span class="feature-icon"><i class="fas fa-shield-check"></i></span>
                        <h4>Reliable Information</h4>
                        <p>Listings verify aur organize karke better trust aur decision support.</p>
                    </div>
                    <div class="mission-feature-card">
                        <span class="feature-icon"><i class="fas fa-mobile-screen-button"></i></span>
                        <h4>Future-Ready Expansion</h4>
                        <p>Dedicated mobile app tak progressively expand kiya ja raha hai.</p>
                    </div>
                    <div class="mission-feature-card">
                        <span class="feature-icon"><i class="fas fa-clock-rotate-left"></i></span>
                        <h4>Time-Saving Experience</h4>
                        <p>Quick access se users ka qeemti waqt bachaya jaye.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ SUPPORT ══════════════════════════════════════════════ -->
<section class="support-section section-padding">
    <div class="container">
        <div class="support-shell">
            <div class="row g-4 align-items-start">
                <div class="col-lg-7" data-aos="fade-up">
                    <div class="content-panel support-copy-panel about-parallax-card">
                        <span class="section-chip section-chip-soft">
                            <i class="fas fa-hands-helping"></i> Help & Support
                        </span>
                        <h2 class="section-title about-section-title">Is mission ke liye humara saath dein</h2>
                        <p class="support-lead">Is project ke liye authentic maloomat ki zaroorat hai. Jab bhi koi hospital ya clinic ka board dikhe — photo lein aur bhejein.</p>
                        <div class="support-process">
                            <div class="support-step">
                                <div class="support-step-number">01</div>
                                <div><h4>Observe</h4><p>Hospital, doctor, lab ya blood bank ka board dekh kar clear photo lein.</p></div>
                            </div>
                            <div class="support-step">
                                <div class="support-step-number">02</div>
                                <div><h4>Share</h4><p>WhatsApp par send karein taake information platform tak aa sake.</p></div>
                            </div>
                            <div class="support-step">
                                <div class="support-step-number">03</div>
                                <div><h4>Publish</h4><p>Maloomat verify hokar website par add ho jaye gi — logon ke fayde ke liye.</p></div>
                            </div>
                        </div>
                        <div class="support-quote">
                            Yeh kaam khidmat aur sawab ki niyyat se karein — InshaAllah ajar bhi mile ga aur logon ki asaani bhi hogi۔
                        </div>
                    </div>
                </div>
                <div class="col-lg-5" data-aos="fade-up" data-aos-delay="150">
                    <div class="support-contact-grid">
                        <a href="https://wa.me/+923049744263" class="contact-action-card about-parallax-card">
                            <div class="contact-action-icon"><i class="fab fa-whatsapp"></i></div>
                            <span class="mini-label">Primary Support</span>
                            <h4>Healthcare Info bhejein</h4>
                            <p>Board photos aur listing details direct WhatsApp par bhejein.</p>
                            <strong>+92 304 9744263</strong>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Ad Space 2 -->
<div class="container my-4" data-aos="fade-up">
    <div class="ad-slot text-center" style="min-height:90px; background:#f8f9fa; border:1px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center;">
        <!-- SpeedyAds Code Yahan Paste Karein -->
        <span class="text-muted small">Advertisement Space</span>
    </div>
</div>

<!-- ══ REVIEWS ═══════════════════════════════════════════════ -->
<section class="reviews-section section-padding" id="reviews">
    <div class="container">
        <div class="section-heading text-center" data-aos="fade-up">
            <span class="section-chip section-chip-soft">
                <i class="fas fa-star"></i> Reviews
            </span>
            <h2 class="section-title">Website ka review karein</h2>
            <p class="section-intro">Aapki feedback se platform better hota hai۔</p>
        </div>

        <div class="review-summary-grid mb-4" data-aos="fade-up">
            <div class="review-summary-card">
                <small>Average Rating</small>
                <strong><?= htmlspecialchars($average_rating) ?>/5</strong>
                <p>Community sentiment.</p>
            </div>
            <div class="review-summary-card">
                <small>Total Reviews</small>
                <strong><?= $review_count ?></strong>
                <p>Users ne rai share ki.</p>
            </div>
            <div class="review-summary-card">
                <small>Improvement Style</small>
                <strong>Feedback-led</strong>
                <p>Suggestions future updates guide karte hain.</p>
            </div>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-lg-5" data-aos="fade-right">
                <div class="review-form-card about-parallax-card">
                    <h3 class="form-title">Leave a Review</h3>
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success review-alert"><i class="fas fa-circle-check me-2"></i><?= htmlspecialchars($success_message) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger review-alert"><i class="fas fa-triangle-exclamation me-2"></i><?= htmlspecialchars($error_message) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Your Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <select class="form-control" name="rating">
                                <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                                <option value="4">⭐⭐⭐⭐ Very Good</option>
                                <option value="3">⭐⭐⭐ Good</option>
                                <option value="2">⭐⭐ Fair</option>
                                <option value="1">⭐ Poor</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Your Review</label>
                            <textarea class="form-control" name="message" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn about-btn-primary btn-lg w-100">
                            <i class="fas fa-paper-plane me-2"></i>Submit Review
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-lg-7" data-aos="fade-left">
                <div class="reviews-showcase about-parallax-card">
                    <div class="reviews-showcase-header">
                        <div>
                            <span class="mini-label">Community Voice</span>
                            <h3>What visitors are saying</h3>
                        </div>
                        <?php if ($review_count > 1): ?>
                        <div class="reviews-nav-inline">
                            <button class="reviews-carousel-control prev" id="reviewsPrevBtn" type="button"><i class="fas fa-chevron-left"></i></button>
                            <button class="reviews-carousel-control next" id="reviewsNextBtn" type="button"><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="reviews-carousel-section">
                        <div class="reviews-carousel-container">
                            <div class="reviews-carousel">
                                <div class="reviews-carousel-inner" id="reviewsCarousel">
                                    <?php if ($review_count > 0):
                                        foreach ($reviews as $row):
                                            $rating = isset($row['stars']) ? (int)$row['stars'] : 5;
                                            $stars  = '';
                                            for ($i = 1; $i <= 5; $i++) {
                                                $stars .= $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                            }
                                    ?>
                                    <div class="review-carousel-item">
                                        <article class="review-card">
                                            <div class="review-header">
                                                <div class="reviewer-info">
                                                    <h5 class="reviewer-name"><?= htmlspecialchars($row['commenter_name']) ?></h5>
                                                    <div class="review-rating"><?= $stars ?></div>
                                                </div>
                                                <div class="review-date"><?= date('M d, Y', strtotime($row['created_at'])) ?></div>
                                            </div>
                                            <div class="review-content">
                                                <p><?= htmlspecialchars($row['comment']) ?></p>
                                            </div>
                                        </article>
                                    </div>
                                    <?php endforeach; else: ?>
                                    <div class="review-carousel-item">
                                        <div class="no-reviews-carousel">
                                            <div class="no-reviews-message">
                                                <i class="fas fa-comment-slash"></i>
                                                <h4>No Reviews Yet</h4>
                                                <p>Be the first to share your experience.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="reviews-carousel-indicators" id="reviewsIndicators"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ CTA ══════════════════════════════════════════════════ -->
<section class="contact-cta-section section-padding">
    <div class="container">
        <div class="contact-cta-card about-parallax-card text-center" data-aos="zoom-in">
            <span class="section-chip section-chip-dark"><i class="fas fa-paper-plane"></i> Let's Connect</span>
            <h2 class="cta-title">Need help, want to collaborate, or have a suggestion?</h2>
            <p class="cta-description">Contact channels hamesha open hain — healthcare info, ideas ya direct support.</p>
            <div class="cta-buttons">
                <a href="mailto:sohail.it99@gmail.com" class="btn about-btn-primary btn-lg"><i class="fas fa-envelope me-2"></i>Email Us</a>
                <a href="tel:+923049744263" class="btn about-btn-outline-light btn-lg"><i class="fas fa-phone me-2"></i>Call Us</a>
            </div>
        </div>
    </div>
</section>

<style>
/* Page header */
.about-page-header {
    padding: 120px 0 60px;
    background: linear-gradient(135deg, rgba(13,43,110,0.9) 0%, rgba(0,168,204,0.85) 100%),
                url('<?= BASE_URL ?>admin/inc/uploads/default/hosp.jpg') center/cover no-repeat;
    color: white;
    text-align: center;
}
.about-page-header h1 {
    font-size: clamp(1.8rem, 4vw, 3rem);
    font-weight: 800;
    margin: 14px 0 12px;
}
.about-page-header p {
    font-size: 1.05rem;
    opacity: 0.88;
    max-width: 560px;
    margin: 0 auto;
}

/* Team shell — mirrors cofounder-shell */
.team-shell {
    padding: 12px;
    border-radius: 34px;
    background: linear-gradient(135deg, rgba(28,63,143,0.08), rgba(0,168,204,0.08));
    box-shadow: 0 24px 60px rgba(15,23,42,0.08);
    height: 100%;
}

.team-visual-card {
    height: 100%;
    padding: 20px;
    border-radius: 28px;
    background: rgba(255,255,255,0.92);
    border: 1px solid rgba(148,163,184,0.14);
    box-shadow: 0 20px 50px rgba(15,23,42,0.08);
}

.team-image-wrap {
    position: relative;
    overflow: hidden;
    border-radius: 22px;
    margin-bottom: 18px;
    border: 1px solid rgba(148,163,184,0.14);
    box-shadow: 0 16px 40px rgba(15,23,42,0.1);
}

.team-image-wrap img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    object-position: top center;
    display: block;
}

@media (max-width: 767px) {
    .team-image-wrap img {
        height: auto;
        max-height: 380px;
        object-fit: contain;
        object-position: center;
        background: #f0f4f8;
    }
}

.team-image-caption {
    position: absolute;
    left: 14px; bottom: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 999px;
    background: rgba(9,20,39,0.78);
    color: #fff;
    font-weight: 700;
    font-size: 0.8rem;
    backdrop-filter: blur(12px);
}

.team-profile-panel {
    display: flex;
    gap: 14px;
    align-items: center;
    padding: 16px;
    border-radius: 20px;
    background: #fff;
    border: 1px solid rgba(148,163,184,0.14);
    box-shadow: 0 14px 30px rgba(15,23,42,0.06);
}

.team-avatar {
    width: 52px; height: 52px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.4rem;
    flex-shrink: 0;
}

.team-profile-panel h3 { margin: 4px 0 4px; font-size: 1.1rem; font-weight: 800; color: #0f172a; }
.team-profile-panel p  { margin: 0; color: #64748b; font-size: 0.85rem; }

/* Team copy */
.team-copy { padding: 10px 6px; }
.team-name  { font-size: 1.25rem; font-weight: 800; color: #0D2B6E; margin: 12px 0 10px; }
.team-bio   { color: #475569; line-height: 1.8; font-size: 0.93rem; margin-bottom: 18px; }

.team-highlights { display: grid; gap: 12px; }

.team-highlight-card {
    display: flex;
    gap: 14px;
    padding: 14px 16px;
    border-radius: 18px;
    background: rgba(248,250,252,0.95);
    border: 1px solid rgba(148,163,184,0.12);
}

.team-highlight-card span {
    width: 44px; height: 44px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(28,63,143,0.14), rgba(0,168,204,0.16));
    color: #1C3F8F;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.team-highlight-card h4 { margin: 0 0 4px; color: #0f172a; font-size: 0.95rem; font-weight: 700; }
.team-highlight-card p  { margin: 0; color: #64748b; font-size: 0.85rem; line-height: 1.6; }

.team-actions { display: flex; gap: 10px; flex-wrap: wrap; }

/* ── Story / Welcome section ───────────────────────────── */
.story-panel { height: 100%; }
.story-panel .about-section-title { margin-top: 14px; }

.story-lead {
    font-size: 1.12rem;
    font-weight: 600;
    color: #0D2B6E !important;
    line-height: 1.8;
}

.story-founder-note {
    position: relative;
    margin: 6px 0 0;
    padding: 24px 24px 20px 28px;
    border-radius: 22px;
    background: linear-gradient(135deg, rgba(28,63,143,0.07), rgba(39,201,106,0.09));
    border-left: 4px solid #1C3F8F;
}

.story-founder-note > i {
    position: absolute;
    top: 14px; right: 18px;
    font-size: 1.6rem;
    color: rgba(0,168,204,0.28);
}

.story-founder-note p {
    margin: 0 0 14px;
    color: #334155;
    font-size: 1rem;
    line-height: 1.85;
}

.story-founder-note footer {
    font-size: 0.85rem;
    font-weight: 700;
    color: #1C3F8F;
}

.story-founder-note footer i { color: #27C96A; margin-right: 6px; }

.insight-panel .mini-label { margin-bottom: 12px; }
.insight-panel h3 { margin-bottom: 10px; }

.story-services {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
    margin-top: 28px;
}

.story-services .value-card { padding: 22px; }
.story-services .value-card h4 { font-size: 1.05rem; }
.story-services .value-card p { font-size: 0.9rem; line-height: 1.7; }

.story-closing {
    margin-top: 28px;
    text-align: center;
    font-size: 1.05rem;
}

.story-closing i { color: #27C96A; }

@media (max-width: 991px) {
    .story-services { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 575px) {
    .story-services { grid-template-columns: 1fr; }
    .story-founder-note { padding: 20px 18px 16px 20px; }
    .story-lead { font-size: 1.02rem; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reviewsCarousel   = document.getElementById('reviewsCarousel');
    const reviewItems       = document.querySelectorAll('.review-carousel-item');
    const reviewsPrevBtn    = document.getElementById('reviewsPrevBtn');
    const reviewsNextBtn    = document.getElementById('reviewsNextBtn');
    const reviewsIndicators = document.getElementById('reviewsIndicators');
    const prefersReduced    = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reviewsCarousel && reviewItems.length > 0) {
        let current = 0, total = reviewItems.length, timer = null, busy = false;

        function buildDots() {
            if (!reviewsIndicators) return;
            reviewsIndicators.innerHTML = '';
            for (let i = 0; i < total; i++) {
                const d = document.createElement('button');
                d.type = 'button';
                d.className = 'reviews-indicator' + (i === 0 ? ' active' : '');
                d.addEventListener('click', function() { if (!busy) go(i); });
                reviewsIndicators.appendChild(d);
            }
        }

        function updateDots() {
            if (!reviewsIndicators) return;
            reviewsIndicators.querySelectorAll('.reviews-indicator').forEach(function(d, i) {
                d.classList.toggle('active', i === current);
            });
        }

        function go(idx) {
            if (busy || idx < 0 || idx >= total) return;
            busy = true; current = idx;
            reviewsCarousel.style.transform = 'translateX(' + (-idx * 100) + '%)';
            updateDots();
            setTimeout(function() { busy = false; }, 550);
        }

        function startTimer() {
            if (prefersReduced || total <= 1) return;
            stopTimer();
            timer = setInterval(function() { go((current + 1) % total); }, 5000);
        }
        function stopTimer() { if (timer) { clearInterval(timer); timer = null; } }

        if (reviewsNextBtn) reviewsNextBtn.addEventListener('click', function() { stopTimer(); go((current+1)%total); startTimer(); });
        if (reviewsPrevBtn) reviewsPrevBtn.addEventListener('click', function() { stopTimer(); go((current-1+total)%total); startTimer(); });

        const wrap = document.querySelector('.reviews-carousel-container');
        if (wrap) { wrap.addEventListener('mouseenter', stopTimer); wrap.addEventListener('mouseleave', startTimer); }

        buildDots(); go(0); startTimer();
    }

    // Parallax tilt
    if (!prefersReduced) {
        document.querySelectorAll('.about-parallax-card').forEach(function(card) {
            card.addEventListener('mousemove', function(e) {
                const r = card.getBoundingClientRect();
                const rx = ((e.clientY - r.top)  / r.height - 0.5) * -5;
                const ry = ((e.clientX - r.left) / r.width  - 0.5) *  5;
                card.style.transform = 'perspective(1200px) rotateX('+rx+'deg) rotateY('+ry+'deg) translateY(-2px)';
            });
            card.addEventListener('mouseleave', function() { card.style.transform = ''; });
        });
    }
});
</script>

<?php include BASE_PATH.'/includes/footer.php'; ?>