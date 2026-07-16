<?php include '../includes/header.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name'], $_POST['email'], $_POST['message'], $_POST['rating'])) {
        $name = mysqli_real_escape_string($con, $_POST['name']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $message = mysqli_real_escape_string($con, $_POST['message']);
        $rating = intval($_POST['rating']);

        $insert_query = "INSERT INTO feedback (commenter_name, email, comment, stars, entity_id, status, created_at)
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
                            <img src="<?=BASE_URL?>includes/uploads/about-me.jpg" alt="Founder of Doctor App" class="img-fluid">
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
                        <h2 class="section-title about-section-title">Aik mission ke liye humara saath dein</h2>
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

<style>
.about-hero-section,
.about-story-section,
.mission-section,
.support-section,
.reviews-section,
.contact-cta-section {
    position: relative;
    overflow: hidden;
}

.about-hero-section {
    padding: 110px 0 70px;
    background:
        radial-gradient(circle at top left, rgba(25, 135, 84, 0.18), transparent 28%),
        radial-gradient(circle at 85% 15%, rgba(13, 110, 253, 0.22), transparent 24%),
        linear-gradient(135deg, #071124 0%, #0d1c37 40%, #102952 100%);
}

.about-page-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(18px);
    opacity: 0.55;
    pointer-events: none;
    animation: pageFloat 14s ease-in-out infinite;
}

.orb-one {
    width: 280px;
    height: 280px;
    top: 80px;
    left: -60px;
    background: rgba(67, 97, 238, 0.35);
}

.orb-two {
    width: 220px;
    height: 220px;
    right: -40px;
    bottom: 70px;
    background: rgba(25, 135, 84, 0.32);
    animation-delay: -4s;
}

.about-page-grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
    background-size: 44px 44px;
    mask-image: linear-gradient(to bottom, rgba(0,0,0,0.9), transparent);
    pointer-events: none;
}

.about-hero-shell {
    position: relative;
    z-index: 1;
}

.section-chip {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #fff;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.18);
    box-shadow: 0 16px 40px rgba(4, 10, 30, 0.24);
}

.section-chip-soft {
    color: var(--primary);
    background: rgba(13, 110, 253, 0.09);
    border-color: rgba(13, 110, 253, 0.14);
    box-shadow: none;
}

.section-chip-dark {
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
}

.hero-copy {
    color: #f8fbff;
}

.hero-title {
    font-size: clamp(2.8rem, 5vw, 4.8rem);
    line-height: 1.02;
    font-weight: 800;
    letter-spacing: -0.04em;
    margin: 20px 0 22px;
    color: #fff;
    max-width: 11ch;
}

.hero-text,
.hero-subtext {
    max-width: 640px;
    font-size: 1.08rem;
    line-height: 1.85;
    color: rgba(235, 244, 255, 0.86);
}

.hero-subtext {
    margin-bottom: 0;
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    margin: 34px 0 32px;
}

.about-btn-primary,
.about-btn-outline,
.about-btn-outline-light {
    border-radius: 16px;
    padding: 15px 26px;
    font-weight: 700;
    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease, color 0.3s ease;
}

.about-btn-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #198754 100%);
    border: none;
    color: #fff;
    box-shadow: 0 18px 45px rgba(13, 110, 253, 0.32);
}

.about-btn-primary:hover {
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 24px 55px rgba(13, 110, 253, 0.4);
}

.about-btn-outline {
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    background: rgba(255,255,255,0.06);
    backdrop-filter: blur(10px);
}

.about-btn-outline:hover {
    color: #fff;
    transform: translateY(-2px);
    border-color: rgba(255,255,255,0.32);
    background: rgba(255,255,255,0.12);
}

.about-btn-outline-light {
    border: 1px solid rgba(255,255,255,0.22);
    color: #fff;
    background: transparent;
}

.about-btn-outline-light:hover {
    color: #0d1c37;
    background: #fff;
    transform: translateY(-2px);
}

.hero-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    max-width: 650px;
}

.hero-stat-card {
    padding: 20px;
    border-radius: 24px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.12);
    box-shadow: 0 22px 60px rgba(0, 8, 30, 0.18);
    backdrop-filter: blur(12px);
}

.hero-stat-value {
    font-size: 2.35rem;
    font-weight: 800;
    color: #fff;
    line-height: 1;
    margin-bottom: 8px;
}

.hero-stat-card p {
    margin: 0;
    color: rgba(229, 239, 255, 0.82);
    line-height: 1.5;
    font-size: 0.95rem;
}

.hero-visual-card,
.content-panel,
.review-form-card,
.reviews-showcase,
.contact-cta-card {
    position: relative;
    border-radius: 30px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.08);
}

.hero-visual-card {
    padding: 26px;
    min-height: 640px;
    background: linear-gradient(180deg, rgba(255,255,255,0.14), rgba(255,255,255,0.06));
    box-shadow: 0 28px 90px rgba(2, 7, 22, 0.42);
    backdrop-filter: blur(16px);
}

.hero-visual-glow {
    position: absolute;
    inset: 0;
    background: linear-gradient(160deg, rgba(13,110,253,0.18), transparent 38%, rgba(25,135,84,0.14));
    pointer-events: none;
}

.hero-profile-label {
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 11px 16px;
    border-radius: 999px;
    background: rgba(255,255,255,0.14);
    color: #fff;
    font-weight: 600;
}

.hero-profile-image {
    position: relative;
    margin-top: 22px;
    z-index: 1;
}

.hero-profile-image img {
    width: 100%;
    min-height: 480px;
    object-fit: cover;
    border-radius: 26px;
    border: 1px solid rgba(255,255,255,0.18);
    box-shadow: 0 24px 60px rgba(0, 8, 30, 0.35);
}

.hero-floating-card {
    position: absolute;
    z-index: 2;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    width: min(270px, calc(100% - 36px));
    padding: 16px 18px;
    border-radius: 22px;
    background: rgba(9, 20, 39, 0.82);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.08);
    box-shadow: 0 24px 50px rgba(4, 8, 22, 0.35);
    backdrop-filter: blur(18px);
    animation: floatCard 7s ease-in-out infinite;
}

.floating-card-top {
    top: 100px;
    left: -28px;
}

.floating-card-bottom {
    right: -18px;
    bottom: 34px;
    animation-delay: -2.5s;
}

.floating-card-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0d6efd, #198754);
    flex-shrink: 0;
}

.hero-floating-card strong,
.contact-action-card strong {
    display: block;
    font-size: 0.98rem;
    margin-bottom: 4px;
}

.hero-floating-card p,
.contact-action-card p,
.mission-feature-card p,
.value-card p,
.story-copy p,
.insight-panel p,
.roadmap-panel p,
.support-step p,
.support-side-note p,
.review-summary-card p,
.review-content p,
.review-alert,
.cta-description,
.section-intro,
.support-lead {
    margin: 0;
    line-height: 1.8;
}

.about-story-section {
    background: linear-gradient(180deg, #f6f9ff 0%, #eff5ff 100%);
}

.content-panel {
    padding: 34px;
    background: rgba(255,255,255,0.84);
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(14px);
}

.content-panel-dark {
    background: linear-gradient(180deg, rgba(255,255,255,0.94), rgba(244,248,255,0.94));
}

.about-section-title,
.section-heading .section-title,
.cta-title {
    font-size: clamp(2rem, 4vw, 3.2rem);
    line-height: 1.12;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #0f172a;
    margin: 18px 0 18px;
}

.section-heading {
    max-width: 860px;
    margin: 0 auto 46px;
}

.section-intro {
    font-size: 1.08rem;
    color: #475569;
}

.story-copy {
    display: grid;
    gap: 14px;
    color: #475569;
    margin-bottom: 28px;
}

.value-grid,
.mission-card-grid,
.review-summary-grid {
    display: grid;
    gap: 18px;
}

.value-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.value-card,
.mission-feature-card,
.review-summary-card {
    padding: 24px;
    border-radius: 24px;
    background: #fff;
    border: 1px solid rgba(148, 163, 184, 0.16);
    box-shadow: 0 18px 35px rgba(15, 23, 42, 0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.value-card:hover,
.mission-feature-card:hover,
.review-summary-card:hover,
.contact-action-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 24px 45px rgba(15, 23, 42, 0.1);
}

.value-card span,
.feature-icon,
.contact-action-icon {
    width: 54px;
    height: 54px;
    border-radius: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(13,110,253,0.14), rgba(25,135,84,0.16));
    color: var(--primary);
    font-size: 1.3rem;
    margin-bottom: 16px;
}

.value-card h4,
.mission-feature-card h4,
.contact-action-card h4,
.reviews-showcase-header h3,
.insight-panel h3,
.support-side-note h4 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 10px;
}

.value-card p,
.mission-feature-card p,
.story-copy p,
.insight-panel p,
.roadmap-panel p,
.support-step p,
.support-side-note p,
.review-summary-card p {
    color: #475569;
}

.insight-stack {
    display: grid;
    gap: 18px;
    height: 100%;
}

.insight-panel {
    min-height: 180px;
}

.accent-panel {
    background: linear-gradient(135deg, rgba(13,110,253,0.96), rgba(25,135,84,0.92));
}

.accent-panel,
.accent-panel h3,
.accent-panel p,
.accent-panel .mini-label {
    color: #fff;
}

.roadmap-panel {
    display: grid;
    gap: 18px;
}

.mini-label {
    display: inline-block;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary);
    margin-bottom: 10px;
}

.roadmap-point {
    display: flex;
    gap: 16px;
    align-items: flex-start;
}

.roadmap-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    margin-top: 8px;
    background: linear-gradient(135deg, #0d6efd, #198754);
    box-shadow: 0 0 0 8px rgba(13,110,253,0.08);
    flex-shrink: 0;
}

.roadmap-point h5 {
    margin: 0 0 4px;
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
}

.mission-section {
    background:
        radial-gradient(circle at 12% 25%, rgba(13,110,253,0.08), transparent 24%),
        radial-gradient(circle at 82% 12%, rgba(25,135,84,0.1), transparent 18%),
        linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
}

.mission-visual {
    position: relative;
    padding: 18px;
    border-radius: 30px;
    background: linear-gradient(160deg, rgba(13,110,253,0.08), rgba(25,135,84,0.12));
    border: 1px solid rgba(13,110,253,0.1);
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
}

.mission-visual img {
    width: 100%;
    min-height: 500px;
    object-fit: cover;
    border-radius: 24px;
}

.mission-badge {
    position: absolute;
    left: 30px;
    bottom: 30px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    border-radius: 999px;
    background: rgba(9, 20, 39, 0.78);
    color: #fff;
    font-weight: 600;
    backdrop-filter: blur(12px);
}

.mission-card-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.support-section {
    background: linear-gradient(180deg, #eff6ff 0%, #f8fbff 100%);
}

.support-shell {
    position: relative;
    border-radius: 34px;
    padding: 12px;
    background: linear-gradient(135deg, rgba(13,110,253,0.08), rgba(25,135,84,0.08));
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
}

.support-copy-panel {
    height: 100%;
}

.support-lead {
    font-size: 1.05rem;
    color: #475569;
    margin-bottom: 26px;
}

.support-process {
    display: grid;
    gap: 18px;
}

.support-step {
    display: flex;
    gap: 18px;
    padding: 18px;
    border-radius: 22px;
    background: rgba(248, 250, 252, 0.95);
    border: 1px solid rgba(148, 163, 184, 0.14);
}

.support-step-number {
    width: 54px;
    height: 54px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0d6efd, #198754);
    color: #fff;
    font-weight: 800;
    flex-shrink: 0;
}

.support-step h4 {
    margin: 0 0 6px;
    color: #0f172a;
    font-size: 1.08rem;
    font-weight: 700;
}

.support-quote {
    margin-top: 24px;
    padding: 22px 24px;
    border-radius: 24px;
    background: linear-gradient(135deg, rgba(13,110,253,0.08), rgba(25,135,84,0.1));
    color: #0f172a;
    font-weight: 600;
    border-left: 4px solid #198754;
}

.support-contact-grid {
    display: grid;
    gap: 18px;
}

.contact-action-card {
    display: block;
    text-decoration: none;
    padding: 28px;
    border-radius: 28px;
    background: rgba(255,255,255,0.92);
    border: 1px solid rgba(148, 163, 184, 0.14);
    box-shadow: 0 24px 50px rgba(15, 23, 42, 0.08);
    color: inherit;
}

.contact-action-card strong {
    color: #0f172a;
    font-size: 1rem;
}

.support-side-note {
    background: linear-gradient(180deg, rgba(13,110,253,0.95), rgba(15, 23, 42, 0.95));
}

.support-side-note,
.support-side-note .mini-label,
.support-side-note h4,
.support-side-note p {
    color: #fff;
}

.reviews-section {
    background:
        radial-gradient(circle at top right, rgba(25,135,84,0.1), transparent 22%),
        radial-gradient(circle at 15% 20%, rgba(13,110,253,0.08), transparent 20%),
        linear-gradient(180deg, #ffffff 0%, #f5f9ff 100%);
}

.review-summary-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-bottom: 18px;
}

.review-summary-card small {
    display: block;
    font-size: 0.86rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    margin-bottom: 14px;
}

.review-summary-card strong {
    display: block;
    font-size: 2rem;
    line-height: 1.1;
    color: #0f172a;
    margin-bottom: 10px;
}

.review-form-card,
.reviews-showcase {
    padding: 32px;
    background: rgba(255,255,255,0.92);
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(16px);
}

.form-title {
    margin: 0 0 24px;
    font-size: 1.8rem;
    font-weight: 800;
    color: #0f172a;
}

.review-alert {
    border-radius: 16px;
    border: none;
    margin-bottom: 20px;
}

.form-label {
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 10px;
}

.form-control {
    border-radius: 16px;
    min-height: 56px;
    border: 1px solid rgba(148, 163, 184, 0.28);
    padding: 14px 18px;
    background: rgba(248, 250, 252, 0.95);
    color: #0f172a;
    box-shadow: none;
}

textarea.form-control {
    min-height: 150px;
    resize: vertical;
}

.form-control:focus {
    border-color: rgba(13,110,253,0.45);
    box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.12);
    background: #fff;
}

.reviews-showcase-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 22px;
}

.reviews-showcase-header h3 {
    margin-bottom: 0;
}

.reviews-nav-inline {
    display: flex;
    gap: 10px;
}

.reviews-carousel-section {
    position: relative;
}

.reviews-carousel-container {
    position: relative;
}

.reviews-carousel {
    overflow: hidden;
    border-radius: 26px;
}

.reviews-carousel-inner {
    display: flex;
    transition: transform 0.55s ease;
    will-change: transform;
}

.review-carousel-item {
    min-width: 100%;
    width: 100%;
}

.review-card {
    position: relative;
    min-height: 330px;
    padding: 32px;
    border-radius: 28px;
    background:
        radial-gradient(circle at top right, rgba(13,110,253,0.12), transparent 25%),
        linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid rgba(148,163,184,0.18);
    overflow: hidden;
}

.review-card::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,0.75), transparent);
    transform: translateX(-120%);
    animation: cardShine 8s linear infinite;
}

.review-header,
.review-content {
    position: relative;
    z-index: 1;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 18px;
    margin-bottom: 22px;
}

.reviewer-name {
    margin: 0 0 8px;
    font-size: 1.24rem;
    font-weight: 700;
    color: #0f172a;
}

.review-rating {
    display: flex;
    gap: 4px;
    color: #f59e0b;
}

.review-rating .far {
    color: #cbd5e1;
}

.review-date {
    padding: 10px 14px;
    border-radius: 999px;
    background: rgba(13,110,253,0.08);
    color: #1e3a8a;
    font-size: 0.86rem;
    font-weight: 700;
    white-space: nowrap;
}

.review-content p {
    font-size: 1.06rem;
    color: #334155;
    font-style: italic;
}

.reviews-carousel-control {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0d6efd, #198754);
    color: #fff;
    box-shadow: 0 14px 30px rgba(13, 110, 253, 0.24);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.reviews-carousel-control:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 40px rgba(13, 110, 253, 0.32);
}

.reviews-carousel-indicators {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
}

.reviews-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: none;
    background: rgba(13,110,253,0.16);
    transition: transform 0.3s ease, background 0.3s ease, width 0.3s ease;
}

.reviews-indicator.active {
    width: 34px;
    border-radius: 999px;
    background: linear-gradient(90deg, #0d6efd, #198754);
}

.no-reviews-carousel {
    min-height: 330px;
    padding: 34px;
    border-radius: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    background: linear-gradient(180deg, rgba(13,110,253,0.06), rgba(25,135,84,0.06));
    border: 1px dashed rgba(13,110,253,0.2);
}

.no-reviews-message i {
    font-size: 2.4rem;
    color: var(--primary);
    margin-bottom: 14px;
}

.no-reviews-message h4 {
    margin-bottom: 10px;
    color: #0f172a;
}

.no-reviews-message p {
    color: #475569;
    margin: 0;
}

.contact-cta-section {
    padding-top: 0;
    background: linear-gradient(180deg, #f5f9ff 0%, #ffffff 100%);
}

.contact-cta-card {
    padding: 48px 34px;
    color: #fff;
    background:
        radial-gradient(circle at top right, rgba(255,255,255,0.14), transparent 22%),
        linear-gradient(135deg, #071124 0%, #0d1c37 45%, #0d6efd 100%);
    box-shadow: 0 30px 90px rgba(2, 7, 22, 0.28);
}

.contact-cta-card .cta-title,
.contact-cta-card .cta-description {
    color: #fff;
}

.cta-description {
    max-width: 760px;
    margin: 0 auto 28px;
    color: rgba(241, 245, 249, 0.88);
}

.cta-buttons {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 14px;
}

.about-parallax-card {
    transform-style: preserve-3d;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

@keyframes pageFloat {
    0%, 100% { transform: translate3d(0, 0, 0); }
    50% { transform: translate3d(12px, -16px, 0); }
}

@keyframes floatCard {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

@keyframes cardShine {
    0% { transform: translateX(-120%); }
    100% { transform: translateX(120%); }
}

@media (max-width: 1199px) {
    .hero-stats-grid,
    .value-grid,
    .mission-card-grid,
    .review-summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .hero-visual-card {
        min-height: auto;
    }

    .hero-profile-image img,
    .mission-visual img {
        min-height: auto;
    }
}

@media (max-width: 991px) {
    .about-hero-section {
        padding: 90px 0 50px;
    }

    .hero-title {
        max-width: none;
    }

    .floating-card-top,
    .floating-card-bottom {
        position: relative;
        inset: auto;
        width: 100%;
        margin-top: 16px;
    }

    .hero-profile-image {
        margin-bottom: 12px;
    }

    .review-header,
    .reviews-showcase-header {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 767px) {
    .section-padding {
        padding: 48px 0;
    }

    .hero-actions,
    .cta-buttons {
        flex-direction: column;
    }

    .hero-actions .btn,
    .cta-buttons .btn {
        width: 100%;
    }

    .hero-stats-grid,
    .value-grid,
    .mission-card-grid,
    .review-summary-grid {
        grid-template-columns: 1fr;
    }

    .content-panel,
    .review-form-card,
    .reviews-showcase,
    .contact-cta-card,
    .hero-visual-card {
        padding: 24px;
        border-radius: 24px;
    }

    .hero-profile-image img,
    .mission-visual img,
    .review-card,
    .no-reviews-carousel {
        border-radius: 22px;
    }

    .review-card,
    .no-reviews-carousel {
        min-height: auto;
        padding: 24px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .about-page-orb,
    .hero-floating-card,
    .review-card::before {
        animation: none;
    }

    .about-parallax-card,
    .value-card,
    .mission-feature-card,
    .review-summary-card,
    .contact-action-card,
    .reviews-carousel-control,
    .about-btn-primary,
    .about-btn-outline,
    .about-btn-outline-light {
        transition: none;
    }
}
</style>

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
