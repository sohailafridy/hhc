<?php include '../includes/header.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name'], $_POST['email'], $_POST['message'], $_POST['rating'])) {
        $name = mysqli_real_escape_string($con, $_POST['name']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $message = mysqli_real_escape_string($con, $_POST['message']);
        $rating = intval($_POST['rating']);

        $insert_query = "INSERT INTO feedback (commenter_name, email, comment, stars, user_id, status, created_at)
                       VALUES ('$name', '$email', '$message', $rating, 1,1, NOW())";

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
$reviews_query = "SELECT * FROM feedback WHERE user_id = 1 ORDER BY created_at DESC";
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

<section class="about-hero-section">
    <div class="about-page-orb orb-one"></div>
    <div class="about-page-orb orb-two"></div>
    <div class="about-page-grid"></div>
    <div class="container position-relative">
        <div class="about-hero-shell">
            <div class="row align-items-center g-4">
                <div class="col-lg-7" data-aos="fade-right">
                    <div class="hero-copy">
                        <span class="section-chip">
                            <i class="fas fa-heartbeat"></i>
                            Building trusted healthcare discovery
                        </span>
                        <h1 class="hero-title">Founder & Developer of Doctor App</h1>
                        <p class="hero-text">
                            Main aik software developer hoon aur Doctor App ko is vision ke saath build kiya hai ke patients aur unki families hospitals, doctors, laboratories aur blood banks ki maloomat aik hi platform par professionally, quickly aur asaani se hasil kar saken.
                        </p>
                        <p class="hero-subtext">
                            Yeh platform sirf directory nahi, balkeh aik evolving healthcare information ecosystem hai jahan reliability, usability aur continuous improvement sab se aham priority hai.
                        </p>

                        <div class="hero-actions">
                            <a href="mailto:sohail.it99@gmail.com" class="btn about-btn-primary btn-lg">
                                <i class="fas fa-envelope me-2"></i>Contact Founder
                            </a>
                            <a href="https://wa.me/+923371320001" class="btn about-btn-outline btn-lg">
                                <i class="fab fa-whatsapp me-2"></i>Share Healthcare Info
                            </a>
                        </div>

                        <div class="hero-stats-grid">
                            <div class="hero-stat-card" data-countup>
                                <div class="hero-stat-value" data-target="1">1</div>
                                <p>Unified platform</p>
                            </div>
                            <div class="hero-stat-card" data-countup>
                                <div class="hero-stat-value" data-target="4">4</div>
                                <p>Core healthcare categories</p>
                            </div>
                            <div class="hero-stat-card" data-countup>
                                <div class="hero-stat-value" data-target="24">24</div>
                                <p>Hours accessibility mindset</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5" data-aos="fade-left">
                    <div class="hero-visual-card about-parallax-card">
                        <div class="hero-visual-glow"></div>
                        <div class="hero-profile-label">
                            <i class="fas fa-shield-heart"></i>
                            Vision-led product design
                        </div>
                        <div class="hero-profile-image">
                            <img src="<?=BASE_URL?>includes/uploads/founder.jpg" alt="Founder of Doctor App" class="img-fluid">
                        </div>
                        <div class="hero-floating-card floating-card-top">
                            <span class="floating-card-icon"><i class="fas fa-bolt"></i></span>
                            <div>
                                <strong>Continuous improvement</strong>
                                <p>User feedback se driven updates</p>
                            </div>
                        </div>
                        <div class="hero-floating-card floating-card-bottom">
                            <span class="floating-card-icon"><i class="fas fa-location-dot"></i></span>
                            <div>
                                <strong>Pakistan focused</strong>
                                <p>Healthcare discovery ko simplify karna</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="cofounder-section section-padding">
    <div class="container">
        <div class="cofounder-shell about-parallax-card" data-aos="fade-up">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6" data-aos="fade-right" data-aos-delay="50">
                    <div class="cofounder-copy">
                        <span class="section-chip section-chip-soft">
                            <i class="fas fa-users"></i>
                            Co-Founder spotlight
                        </span>
                        <h2 class="section-title about-section-title">Abdul Qadir Afridi ki mobile app development mein aham contribution</h2>
                        <p class="support-lead">
                            Co-Founder Abdul Qadir Afridi aik skilled mobile app developer hain jinhon ne is project ke liye mobile application develop ki hai. Unhon ne app ko is tarah design aur build kiya ke aik user ko jo essential features darkar hote hain, woh sab us mein asaani ke saath available hon.
                        </p>

                        <div class="cofounder-highlights">
                            <div class="cofounder-highlight-card">
                                <span><i class="fas fa-handshake"></i></span>
                                <div>
                                    <h4>App-focused collaboration</h4>
                                    <p>Web platform ko mobile experience ke saath connect karne mein unka role bohat important raha hai.</p>
                                </div>
                            </div>
                            <div class="cofounder-highlight-card">
                                <span><i class="fas fa-comments"></i></span>
                                <div>
                                    <h4>User-friendly features</h4>
                                    <p>Mobile app mein woh tamam zaroori features include kiye gaye hain jo aik normal user ke liye useful aur practical hain.</p>
                                </div>
                            </div>
                            <div class="cofounder-highlight-card">
                                <span><i class="fas fa-chart-line"></i></span>
                                <div>
                                    <h4>Digital growth support</h4>
                                    <p>Unki development support ne Doctor App ko web se aage barha kar mobile accessibility tak pohanchaya hai.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="100">
                    <div class="cofounder-visual-card">
                        <div class="cofounder-image-wrap">
                            <img src="<?=BASE_URL?>includes/uploads/co-founder.jpeg" alt="Co-Founder" class="img-fluid">
                            <div class="cofounder-image-caption">
                                <i class="fas fa-user-tie"></i>
                                Co-Founder Profile
                            </div>
                        </div>

                        <div class="cofounder-profile-panel">
                            <div class="cofounder-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <span class="mini-label">Leadership role</span>
                                <h3>Abdul Qadir Afridi</h3>
                                <p>Mobile app developer</p>
                            </div>
                        </div>

                        <div class="cofounder-metrics">
                            <div class="cofounder-metric">
                                <strong>Vision</strong>
                                <p>Useful mobile experience for every user</p>
                            </div>
                            <div class="cofounder-metric">
                                <strong>Support</strong>
                                <p>Mobile app design and development</p>
                            </div>
                            <div class="cofounder-metric">
                                <strong>Impact</strong>
                                <p>User needs ke mutabiq feature delivery</p>
                            </div>
                        </div>

                        <div class="cofounder-note">
                            Abdul Qadir Afridi ne is project ke mobile side ko strong banaya hai, taake users ko web ke saath aik complete app-based experience bhi mil sake.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="about-story-section section-padding">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-7" data-aos="fade-up">
                <div class="content-panel content-panel-dark about-parallax-card">
                    <span class="section-chip section-chip-soft">
                        <i class="fas fa-user-shield"></i>
                        About the founder
                    </span>
                    <h2 class="section-title about-section-title">A focused digital mission for easier healthcare access</h2>
                    <div class="story-copy">
                        <p class="lead">
                            Doctor App ka maqsad healthcare information ko zyada accessible, organized aur reliable banana hai, taake users apna qeemti waqt bachate hue behtar decisions le saken.
                        </p>
                        <p>
                            Main is system ko lagataar improve karne par kaam kar raha hoon, aur users ki feedback mere liye bohat aham hai kyun ke isi ki bunyaad par platform ko aur zyada useful, modern aur user-friendly banaya ja sakta hai.
                        </p>
                    </div>

                    <div class="value-grid">
                        <div class="value-card">
                            <span><i class="fas fa-lightbulb"></i></span>
                            <h4>Purpose-driven thinking</h4>
                            <p>Har feature ko real user need aur practical use-case ke saath plan kiya jata hai.</p>
                        </div>
                        <div class="value-card">
                            <span><i class="fas fa-users"></i></span>
                            <h4>User-first experience</h4>
                            <p>Simple discovery, clean information flow aur accessible design is product ka core hai.</p>
                        </div>
                        <div class="value-card">
                            <span><i class="fas fa-layer-group"></i></span>
                            <h4>Organized healthcare data</h4>
                            <p>Different service categories ko aik jagah structured form mein dikhaya jata hai.</p>
                        </div>
                        <div class="value-card">
                            <span><i class="fas fa-rotate"></i></span>
                            <h4>Always evolving</h4>
                            <p>Platform ko future mobile app aur wider coverage ke liye continuously scale kiya ja raha hai.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5" data-aos="fade-up" data-aos-delay="150">
                <div class="insight-stack">
                    <div class="content-panel insight-panel about-parallax-card">
                        <span class="mini-label">Platform mindset</span>
                        <h3>Healthcare search should feel fast, clear and dependable</h3>
                        <p>Directory, discovery aur trust ko ek hi user journey mein merge karna Doctor App ki strongest value proposition hai.</p>
                    </div>
                    <div class="content-panel insight-panel accent-panel about-parallax-card">
                        <span class="mini-label">Feedback loop</span>
                        <h3><?= htmlspecialchars($average_rating) ?> / 5 average sentiment</h3>
                        <p><?= $review_count > 0 ? $review_count . ' users ne direct feedback share kiya hai.' : 'Abhi reviews ka silsila start ho raha hai.' ?></p>
                    </div>
                    <div class="content-panel roadmap-panel about-parallax-card">
                        <div class="roadmap-point">
                            <span class="roadmap-dot"></span>
                            <div>
                                <h5>Current phase</h5>
                                <p>Reliable healthcare listing experience</p>
                            </div>
                        </div>
                        <div class="roadmap-point">
                            <span class="roadmap-dot"></span>
                            <div>
                                <h5>Next step</h5>
                                <p>Wider city coverage and stronger data verification</p>
                            </div>
                        </div>
                        <div class="roadmap-point">
                            <span class="roadmap-dot"></span>
                            <div>
                                <h5>Future vision</h5>
                                <p>Dedicated mobile application for easier nationwide access</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<section class="mission-section section-padding">
    <div class="container">
        <div class="section-heading text-center" data-aos="fade-up">
            <span class="section-chip section-chip-soft">
                <i class="fas fa-bullseye"></i>
                Mission & direction
            </span>
            <h2 class="section-title">A smarter, wider and more trusted healthcare directory for Pakistan</h2>
            <p class="section-intro">
                Mera mission yeh hai ke poore Pakistan ke hospitals, doctors, laboratories aur blood banks ka mukammal aur bharosa-mand record aik hi platform par faraham kiya jaye.
            </p>
        </div>

        <div class="row g-4 align-items-center">
            <div class="col-lg-5" data-aos="fade-right">
                <div class="mission-visual about-parallax-card">
                    <img src="<?=BASE_URL?>includes/uploads/our-mission.jpg" alt="Our Mission" class="img-fluid">
                    <div class="mission-badge">
                        <i class="fas fa-globe-asia"></i>
                        Nationwide growth vision
                    </div>
                </div>
            </div>

            <div class="col-lg-7" data-aos="fade-left">
                <div class="mission-card-grid">
                    <div class="mission-feature-card">
                        <span class="feature-icon"><i class="fas fa-hospital-user"></i></span>
                        <h4>Easy local discovery</h4>
                        <p>Patients apne qareeb relevant healthcare services bina pareshani ke dhoondh saken.</p>
                    </div>
                    <div class="mission-feature-card">
                        <span class="feature-icon"><i class="fas fa-badge-check"></i></span>
                        <h4>Reliable information</h4>
                        <p>Listings ko verify aur organize kar ke better trust aur decision support diya jaye.</p>
                    </div>
                    <div class="mission-feature-card">
                        <span class="feature-icon"><i class="fas fa-mobile-screen-button"></i></span>
                        <h4>Future-ready expansion</h4>
                        <p>Project ko progressively improve karte hue dedicated mobile app tak expand kiya jaye.</p>
                    </div>
                    <div class="mission-feature-card">
                        <span class="feature-icon"><i class="fas fa-clock-rotate-left"></i></span>
                        <h4>Time-saving experience</h4>
                        <p>Healthcare information tak quick access de kar users ka qeemti waqt bachaya jaye.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="support-section section-padding">
    <div class="container">
        <div class="support-shell">
            <div class="row g-4 align-items-start">
                <div class="col-lg-7" data-aos="fade-up">
                    <div class="content-panel support-copy-panel about-parallax-card">
                        <span class="section-chip section-chip-soft">
                            <i class="fas fa-hands-helping"></i>
                            Help & support
                        </span>
                        <h2 class="section-title about-section-title">Es mission ke liye humara saath dein</h2>
                        <p class="support-lead">
                            Is project ke liye bohat zyada data aur authentic maloomat ki zaroorat hoti hai, aur har jagah physically jana mere liye mumkin nahi hota.
                        </p>

                        <div class="support-process">
                            <div class="support-step">
                                <div class="support-step-number">01</div>
                                <div>
                                    <h4>Observe</h4>
                                    <p>Jab bhi aap kisi hospital, doctor, laboratory ya blood bank ka board dekhen, us ki clear image lein.</p>
                                </div>
                            </div>
                            <div class="support-step">
                                <div class="support-step-number">02</div>
                                <div>
                                    <h4>Share</h4>
                                    <p>Image ko WhatsApp par send karein taake information central platform tak aa sake.</p>
                                </div>
                            </div>
                            <div class="support-step">
                                <div class="support-step-number">03</div>
                                <div>
                                    <h4>Verify & publish</h4>
                                    <p>Main maloomat verify kar ke website par add kar doon ga taake zyada log faida utha saken.</p>
                                </div>
                            </div>
                        </div>

                        <div class="support-quote">
                            Yeh kaam khidmat aur sawab ki niyyat se karein. InshaAllah iska ajar bhi mile ga aur logon ke liye asaani bhi paida hogi.
                        </div>
                    </div>
                </div>

                <div class="col-lg-5" data-aos="fade-up" data-aos-delay="150">
                    <div class="support-contact-grid">
                        <a href="https://wa.me/+923371320001" class="contact-action-card about-parallax-card">
                            <div class="contact-action-icon">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <span class="mini-label">Primary support</span>
                            <h4>Send web info here</h4>
                            <p>Healthcare board photos aur listing details direct WhatsApp par bhejein.</p>
                            <strong>+92 337 1320001</strong>
                        </a>

                        <a href="https://wa.me/+923450333089" class="contact-action-card about-parallax-card">
                            <div class="contact-action-icon">
                                <i class="fas fa-handshake-angle"></i>
                            </div>
                            <span class="mini-label">Community support</span>
                            <h4>Support Fixit Kohat</h4>
                            <p>Collaboration aur local help ke liye alternate support channel available hai.</p>
                            <strong>+92 345 0333089</strong>
                        </a>

                        <div class="content-panel support-side-note about-parallax-card">
                            <span class="mini-label">Why it matters</span>
                            <h4>Better information means better healthcare decisions</h4>
                            <p>Community contribution se platform zyada complete, updated aur practically useful banta hai.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="reviews-section section-padding">
    <div class="container">
        <div class="section-heading text-center" data-aos="fade-up">
            <span class="section-chip section-chip-soft">
                <i class="fas fa-star"></i>
                Feedback & reviews
            </span>
            <h2 class="section-title">Review the website and help shape the next improvements</h2>
            <p class="section-intro">
                Kindly review my website and suggest what improvements or additional features I should include.
            </p>
        </div>

        <div class="review-summary-grid" data-aos="fade-up" data-aos-delay="100">
            <div class="review-summary-card">
                <small>Average rating</small>
                <strong><?= htmlspecialchars($average_rating) ?>/5</strong>
                <p>Direct feedback se generated community sentiment.</p>
            </div>
            <div class="review-summary-card">
                <small>Total reviews</small>
                <strong><?= $review_count; ?></strong>
                <p>Users ne platform ke experience par apni rai share ki hai.</p>
            </div>
            <div class="review-summary-card">
                <small>Improvement style</small>
                <strong>Feedback-led</strong>
                <p>Suggestions aur real usage insights future upgrades ko guide karte hain.</p>
            </div>
        </div>

        <div class="row g-4 align-items-start mt-2">
            <div class="col-lg-5" data-aos="fade-right">
                <div class="review-form-card about-parallax-card">
                    <h3 class="form-title">Leave a Review</h3>

                    <?php if (!empty($success_message)) { ?>
                        <div class="alert alert-success review-alert" role="alert">
                            <i class="fas fa-circle-check me-2"></i><?= htmlspecialchars($success_message); ?>
                        </div>
                    <?php } ?>

                    <?php if (!empty($error_message)) { ?>
                        <div class="alert alert-danger review-alert" role="alert">
                            <i class="fas fa-triangle-exclamation me-2"></i><?= htmlspecialchars($error_message); ?>
                        </div>
                    <?php } ?>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating</label>
                            <select class="form-control" id="rating" name="rating">
                                <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                                <option value="4">⭐⭐⭐⭐ Very Good</option>
                                <option value="3">⭐⭐⭐ Good</option>
                                <option value="2">⭐⭐ Fair</option>
                                <option value="1">⭐ Poor</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="message" class="form-label">Your Review</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <input type="hidden" name="entity_id" value="1">
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
                            <span class="mini-label">Community voice</span>
                            <h3>What visitors are saying</h3>
                        </div>
                        <?php if ($review_count > 1) { ?>
                            <div class="reviews-nav-inline">
                                <button class="reviews-carousel-control prev" id="reviewsPrevBtn" type="button" aria-label="Previous review">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button class="reviews-carousel-control next" id="reviewsNextBtn" type="button" aria-label="Next review">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="reviews-carousel-section">
                        <div class="reviews-carousel-container">
                            <div class="reviews-carousel">
                                <div class="reviews-carousel-inner" id="reviewsCarousel">
                                    <?php if ($review_count > 0) {
                                        foreach ($reviews as $row) {
                                            $rating = isset($row['stars']) ? (int) $row['stars'] : 5;
                                            $stars = '';
                                            for ($i = 1; $i <= 5; $i++) {
                                                $stars .= $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                            }
                                    ?>
                                        <div class="review-carousel-item">
                                            <article class="review-card">
                                                <div class="review-header">
                                                    <div class="reviewer-info">
                                                        <h5 class="reviewer-name"><?php echo htmlspecialchars($row['commenter_name']); ?></h5>
                                                        <div class="review-rating"><?php echo $stars; ?></div>
                                                    </div>
                                                    <div class="review-date">
                                                        <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                                    </div>
                                                </div>
                                                <div class="review-content">
                                                    <p><?php echo htmlspecialchars($row['comment']); ?></p>
                                                </div>
                                            </article>
                                        </div>
                                    <?php }
                                    } else { ?>
                                        <div class="review-carousel-item">
                                            <div class="no-reviews-carousel">
                                                <div class="no-reviews-message">
                                                    <i class="fas fa-comment-slash"></i>
                                                    <h4>No Reviews Yet</h4>
                                                    <p>Be the first to share your experience and help improve the platform.</p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
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

<section class="contact-cta-section section-padding">
    <div class="container">
        <div class="contact-cta-card about-parallax-card text-center" data-aos="zoom-in">
            <span class="section-chip section-chip-dark">
                <i class="fas fa-paper-plane"></i>
                Let’s connect
            </span>
            <h2 class="cta-title">Need help, want to collaborate, or have a suggestion?</h2>
            <p class="cta-description">
                Agar aap ke paas healthcare listing information, product ideas ya direct support request hai, to contact channels hamesha open hain.
            </p>
            <div class="cta-buttons">
                <a href="mailto:sohail.it99@gmail.com" class="btn about-btn-primary btn-lg">
                    <i class="fas fa-envelope me-2"></i>Email Us
                </a>
                <a href="tel:+923371320001" class="btn about-btn-outline-light btn-lg">
                    <i class="fas fa-phone me-2"></i>Call Us
                </a>
            </div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="<?= BASE_URL ?>style/about-us.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reviewsCarousel = document.getElementById('reviewsCarousel');
    const reviewItems = document.querySelectorAll('.review-carousel-item');
    const reviewsPrevBtn = document.getElementById('reviewsPrevBtn');
    const reviewsNextBtn = document.getElementById('reviewsNextBtn');
    const reviewsIndicators = document.getElementById('reviewsIndicators');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reviewsCarousel && reviewItems.length > 0) {
        let currentReviewIndex = 0;
        const totalReviewItems = reviewItems.length;
        let autoPlayInterval = null;
        let isTransitioning = false;

        function createReviewIndicators() {
            if (!reviewsIndicators) return;
            reviewsIndicators.innerHTML = '';

            for (let i = 0; i < totalReviewItems; i++) {
                const indicator = document.createElement('button');
                indicator.type = 'button';
                indicator.className = 'reviews-indicator' + (i === 0 ? ' active' : '');
                indicator.setAttribute('aria-label', 'Go to review ' + (i + 1));
                indicator.addEventListener('click', function() {
                    if (!isTransitioning) {
                        goToReviewSlide(i);
                    }
                });
                reviewsIndicators.appendChild(indicator);
            }
        }

        function updateReviewIndicators() {
            if (!reviewsIndicators) return;
            const indicators = reviewsIndicators.querySelectorAll('.reviews-indicator');
            indicators.forEach(function(indicator, index) {
                indicator.classList.toggle('active', index === currentReviewIndex);
            });
        }

        function goToReviewSlide(index) {
            if (isTransitioning || index < 0 || index >= totalReviewItems) return;

            isTransitioning = true;
            currentReviewIndex = index;
            reviewsCarousel.style.transform = 'translateX(' + (-index * 100) + '%)';
            updateReviewIndicators();

            window.setTimeout(function() {
                isTransitioning = false;
            }, 550);
        }

        function nextReviewSlide() {
            goToReviewSlide((currentReviewIndex + 1) % totalReviewItems);
        }

        function prevReviewSlide() {
            goToReviewSlide((currentReviewIndex - 1 + totalReviewItems) % totalReviewItems);
        }

        function startAutoPlay() {
            if (prefersReducedMotion || totalReviewItems <= 1) return;
            stopAutoPlay();
            autoPlayInterval = window.setInterval(nextReviewSlide, 5000);
        }

        function stopAutoPlay() {
            if (autoPlayInterval) {
                window.clearInterval(autoPlayInterval);
                autoPlayInterval = null;
            }
        }

        if (reviewsNextBtn) {
            reviewsNextBtn.addEventListener('click', function() {
                stopAutoPlay();
                nextReviewSlide();
                startAutoPlay();
            });
        }

        if (reviewsPrevBtn) {
            reviewsPrevBtn.addEventListener('click', function() {
                stopAutoPlay();
                prevReviewSlide();
                startAutoPlay();
            });
        }

        const carouselContainer = document.querySelector('.reviews-carousel-container');
        if (carouselContainer) {
            carouselContainer.addEventListener('mouseenter', stopAutoPlay);
            carouselContainer.addEventListener('mouseleave', startAutoPlay);
        }

        createReviewIndicators();
        goToReviewSlide(0);
        startAutoPlay();
    }

    const statValues = document.querySelectorAll('[data-countup] .hero-stat-value');
    if ('IntersectionObserver' in window && statValues.length && !prefersReducedMotion) {
        const countObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (!entry.isIntersecting) return;

                const element = entry.target;
                const target = parseInt(element.getAttribute('data-target'), 10) || 0;
                const duration = 1200;
                const startTime = performance.now();

                function animateCount(now) {
                    const progress = Math.min((now - startTime) / duration, 1);
                    element.textContent = Math.floor(progress * target);
                    if (progress < 1) {
                        requestAnimationFrame(animateCount);
                    } else {
                        element.textContent = target;
                    }
                }

                requestAnimationFrame(animateCount);
                observer.unobserve(element);
            });
        }, { threshold: 0.55 });

        statValues.forEach(function(value) {
            countObserver.observe(value);
        });
    }

    if (!prefersReducedMotion) {
        const parallaxCards = document.querySelectorAll('.about-parallax-card');

        parallaxCards.forEach(function(card) {
            card.addEventListener('mousemove', function(event) {
                const rect = card.getBoundingClientRect();
                const rotateX = ((event.clientY - rect.top) / rect.height - 0.5) * -6;
                const rotateY = ((event.clientX - rect.left) / rect.width - 0.5) * 6;
                card.style.transform = 'perspective(1200px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg) translateY(-2px)';
            });

            card.addEventListener('mouseleave', function() {
                card.style.transform = '';
            });
        });
    }
});
</script>

<?php include BASE_PATH.'/includes/footer.php';?>
