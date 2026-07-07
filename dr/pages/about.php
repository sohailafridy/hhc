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

<!-- About Me Section -->
<section class="about-me-section section-padding">
    <div class="container">
        <div class="about-me-wrapper">
            <div class="about-me-blur"></div>
            <div class="about-me-shape about-me-shape-1"></div>
            <div class="about-me-shape about-me-shape-2"></div>
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="about-me-content glass-card">
                        <span class="about-badge">
                            <i class="fas fa-user-shield"></i>
                            Who I Am
                        </span>
                        <h2 class="section-title">About Me</h2>
                        <div class="about-description">
                            <p class="lead">
                                Main aik software developer hoon aur ye platform maine is liye banaya hai taake patients asaani se hospitals, doctors, labs aur blood banks ki maloomat hasil kar saken.
                            </p>
                            <p>
                                Is website ka maqsad logon ka time bachana aur healthcare services ko dhoondhna asaan banana hai.
                            </p>
                            <p>
                                Main is system ko behtar banane ke liye lagataar kaam kar raha hoon, aur users ki feedback hamaray liye bohat qeemti hai.
                            </p>
                        </div>
                        
                        <div class="developer-info">
                            <div class="info-item">
                                <i class="fas fa-heart"></i>
                                <span>Passionate About Healthcare</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-users"></i>
                                <span>Do good, and help spread goodness.</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="about-me-image glass-card">
                        <div class="image-overlay"></div>
                        <img src="<?=BASE_URL?>includes/uploads/about-me.jpg" alt="Software Developer" class="img-fluid rounded-3 shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Mission Section -->
<section class="mission-section section-padding bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="mission-image" data-aos="fade-right">
                    <img src="<?=BASE_URL?>includes/uploads/our-mission.jpg" alt="Our Mission" class="img-fluid rounded-3 shadow">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="mission-content" data-aos="fade-left">
                    <h2 class="section-title">Our Mission</h2>
                    <p class="section-description">
                    Mera mission yeh hai ke hum poore Pakistan ke hospitals, doctors, laboratories aur blood banks ka mukammal aur bharosa-mand record aik hi platform par faraham karein.
                    Is platform ka maqsad yeh hai ke patients asaani se apne qareeb healthcare services dhoondh saken, apna qeemti waqt bacha saken aur bina pareshani ke sahi maloomat hasil kar saken.
                    InshaAllah, hamari koshish rahegi ke is project ko lagataar behtar banaya jaye aur mustaqbil mein mobile application bhi launch ki jaye, taake Pakistan ke har shehar aur ilaqay tak healthcare information asaani se pohanch sake.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Help & Support Section -->
<section class="help-support-section section-padding">
    <div class="container-fluid">
        <div class="help-support-content">
            <div class="help-support-header" data-aos="fade-up">
                <h2 class="section-title">Help & Support</h2>
                <p class="section-subtitle">Aik mission ke liye humara saath dein</p>
            </div>
            
            <div class="help-support-text" data-aos="fade-up" data-aos-delay="200">
                <p class="support-description">
                    Jaisa ke aap sab jaantay hain, is project ke liye bohat zyada data aur maloomat ki zaroorat hai, aur mere liye tanha har jagah jana mumkin nahi.
                </p>
                <p class="support-description">
                    Is liye main aap sab se guzarish karta hoon ke jab bhi aap bazaar, shehar ya kisi aur ilaqay mein hon aur kisi hospital, doctor ka board, laboratory ya blood bank nazar aaye, to us ki aik clear picture nikaal kar mere <a href="https://wa.me/+923371320001">WhatsApp</a> number par send kar dein.
                </p>
                <p class="support-description">
                    Main aap ki bheji hui maloomat ko verify kar ke website par add kar doon ga, taake zyada se zyada log is se faida utha saken.
                </p>
                <p class="support-description">
                    Yeh kaam khidmat aur sawab ki niyyat se karein — InshaAllah iska ajar zaroor mile ga.
                </p>
                <p class="support-call">
                    Aaiye, mil kar is mission ka hissa bante hain aur logon ke liye asaani paida karte hain.
                </p>
            </div>
            
            <div class="help-support-contact" data-aos="fade-up" data-aos-delay="400">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div class="contact-info">
                        <h4>WhatsApp Support</h4>
                        <p class="contact-number">
                            <a href="https://wa.me/+923371320001" class="wtsp">
                                SEND WEB INFO HERE
                            </a>
                        </p>
                    </div>
                </div>
                
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <div class="contact-info">
                        <h4>Support Fixit Kohat</h4>
                        <p>
                            <a href="https://wa.me/+923450333089" class="wtsp">
                                FIXIT KOHAT
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact CTA Section -->
<!-- Reviews Section -->
<section class="reviews-section section-padding bg-light">
    <div class="container">
        <div class="reviews-header" data-aos="fade-up">
            <h2 class="section-title">Reviews</h2>
            <p class="section-subtitle">Kindly review my website and suggest what improvements or additional features I should include.</p>
        </div>

        <div class="review-form-container mt-5">
            <div class="review-form-card" data-aos="fade-up">
                <h3 class="form-title">Leave a Review</h3>
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
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
                    <div class="mb-3">
                        <label for="message" class="form-label">Your Review</label>
                        <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                    </div>
                    <input type="hidden" name="entity_id" value="1">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Submit Review
                    </button>
                </form>
            </div>
        </div>

        <?php
        $query = "SELECT * FROM feedback WHERE entity_id = 1 ORDER BY created_at DESC";
        $result = mysqli_query($con, $query);
        ?>

        <div class="reviews-carousel-section">
            <div class="reviews-carousel-container" data-aos="fade-up">
                <div class="reviews-carousel">
                    <div class="reviews-carousel-inner" id="reviewsCarousel">
                        <?php if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $rating = isset($row['stars']) ? $row['stars'] : 5;
                                $stars = '';
                                for ($i = 1; $i <= 5; $i++) {
                                    $stars .= $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                }
                        ?>
                        <div class="review-carousel-item">
                            <div class="review-card">
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
                            </div>
                        </div>
                        <?php }
                        } else { ?>
                        <div class="no-reviews-carousel">
                            <div class="no-reviews-message">
                                <i class="fas fa-comment-slash fa-3x"></i>
                                <h4>No Reviews Yet</h4>
                                <p>Be the first to share your experience!</p>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>

                <?php if (mysqli_num_rows($result) > 1) { ?>
                <button class="reviews-carousel-control prev" id="reviewsPrevBtn">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="reviews-carousel-control next" id="reviewsNextBtn">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <?php } ?>

                <div class="reviews-carousel-indicators" id="reviewsIndicators"></div>
            </div>
        </div>
    </div>
</section>

<!-- Contact CTA Section -->
<section class="contact-cta-section section-padding">
    <div class="container">
        <div class="contact-cta-content text-center" data-aos="fade-up">
            <h2 class="cta-title">Get in Touch</h2>
            <p class="cta-description">
                Have questions or need help? Our team is here to assist you 24/7
            </p>
            <div class="cta-buttons">
                <a href="mailto:sohail.it99@gmail.com" class="btn email-btn btn-lg me-3">
                    <i class="fas fa-envelope me-2 email-btn"></i>Email Us
                </a>
                <a href="tel:+923371320001" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-phone me-2"></i>Call Us
                </a>
            </div>
        </div>
    </div>
</section>

<style>


/* About Me Section */
.about-me-section {
    position: relative;
    padding: 100px 0;
    background: linear-gradient(135deg, var(--accent) 0%, var(--primary) 100%);
    overflow: hidden;
}

.about-me-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 30%, rgba(86, 204, 242, 0.15), transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(255, 179, 64, 0.12), transparent 38%);
    animation: aboutMeFloat 15s ease-in-out infinite;
    z-index: 0;
}

@keyframes aboutMeFloat {
    0%, 100% { 
        transform: translateX(0) translateY(0) rotate(0deg); 
        opacity: 0.6;
    }
    25% { 
        transform: translateX(10px) translateY(-5px) rotate(1deg); 
        opacity: 0.8;
    }
    50% { 
        transform: translateX(-5px) translateY(10px) rotate(-1deg); 
        opacity: 0.7;
    }
    75% { 
        transform: translateX(8px) translateY(8px) rotate(0.5deg); 
        opacity: 0.9;
    }
}

.about-me-wrapper {
    position: relative;
    border-radius: 30px;
    padding: 40px;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(255,255,255,0.16), rgba(255,255,255,0.06));
    border: 1px solid rgba(255,255,255,0.15);
    box-shadow: 0 25px 80px rgba(0,0,0,0.12);
    backdrop-filter: blur(14px);
}

.about-me-blur {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, rgba(255,255,255,0.12), rgba(255,255,255,0.02));
    filter: blur(8px);
    pointer-events: none;
}

.about-me-shape {
    position: absolute;
    width: 240px;
    height: 240px;
    border-radius: 50%;
    filter: blur(30px);
    opacity: 0.35;
    pointer-events: none;
}

.about-me-shape-1 {
    top: -60px;
    left: -40px;
    background: linear-gradient(135deg, rgba(52,152,219,0.35), rgba(86,204,242,0.2));
}

.about-me-shape-2 {
    bottom: -80px;
    right: -40px;
    background: linear-gradient(135deg, rgba(255,107,53,0.32), rgba(255,179,64,0.18));
}

.glass-card {
    position: relative;
    background: linear-gradient(160deg, rgba(255,255,255,0.18), rgba(255,255,255,0.06));
    border: 1px solid rgba(255,255,255,0.18);
    border-radius: 24px;
    padding: 32px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.12), inset 0 1px 0 rgba(255,255,255,0.35);
    backdrop-filter: blur(10px);
    z-index: 1;
}

.about-me-content .section-title {
    color: var(--dark);
    margin-bottom: 18px;
}

.about-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, rgba(52,152,219,0.2), rgba(255,107,53,0.2));
    color: var(--primary);
    padding: 8px 14px;
    border-radius: 999px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    font-size: 0.8rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

.about-description p {
    color: rgba(0,0,0,0.72);
}

.developer-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    margin-top: 22px;
}

.developer-info .info-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: 14px;
    background: rgba(255,255,255,0.55);
    border: 1px solid rgba(255,255,255,0.35);
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    color: var(--dark);
}

.developer-info .info-item i {
    color: var(--primary);
}

.about-me-image {
    position: relative;
    overflow: hidden;
    padding: 12px;
}

.about-me-image .image-overlay {
    position: absolute;
    inset: 12px;
    border-radius: 18px;
    background: linear-gradient(145deg, rgba(52,152,219,0.18), rgba(255,107,53,0.16));
    filter: blur(16px);
    z-index: 0;
}

.about-me-image img {
    position: relative;
    width: 100%;
    border-radius: 18px;
    border: 2px solid rgba(255,255,255,0.35);
    box-shadow: 0 25px 60px rgba(0,0,0,0.18);
    z-index: 1;
}

@media (max-width: 991px) {
    .about-me-wrapper {
        padding: 28px 22px;
    }
    .about-me-section {
        padding: 70px 0;
    }
}



.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: white;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 1rem;
    opacity: 0.9;
    margin: 0;
}

.about-hero-image {
    position: relative;
    z-index: 1;
}

.about-hero-image img {
    width: 100%;
    height: auto;
    border: 5px solid rgba(255, 255, 255, 0.2);
}

/* Mission Section */
.mission-section {
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.08) 0%, rgba(255, 107, 53, 0.06) 100%);
    position: relative;
    overflow: hidden;
}

.mission-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 10% 20%, rgba(52, 152, 219, 0.12), transparent 30%),
                radial-gradient(circle at 90% 10%, rgba(255, 107, 53, 0.1), transparent 28%);
    z-index: 0;
}

.mission-image {
    margin-bottom: 30px;
}

.mission-image img {
    width: 100%;
    height: auto;
}

.mission-content .section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 20px;
}

.section-description {
    text-align: justify;
    font-size: 1.1rem;
    line-height: 1.8;
    color: var(--dark-blue);
    margin-bottom: 40px;
}

.mission-features {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    gap: 20px;
}

.feature-icon {
    width: 60px;
    height: 60px;
    background: var(--gradient);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.feature-content h4 {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 8px;
}

.feature-content p {
    color: var(--dark-blue);
    line-height: 1.6;
    margin: 0;
}


/* Help & Support Section */
.help-support-section {
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.06) 0%, rgba(255, 107, 53, 0.04) 100%);
    position: relative;
    overflow: hidden;
}

.help-support-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 15% 25%, rgba(52, 152, 219, 0.1), transparent 35%),
                radial-gradient(circle at 85% 75%, rgba(255, 107, 53, 0.08), transparent 32%);
    z-index: 0;
}

.help-support-content {
    position: relative;
    z-index: 1;
    max-width: 900px;
    margin: 0 auto;
    padding: 0 20px;
}

.help-support-header {
    text-align: center;
    margin-bottom: 50px;
}

.help-support-header .section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--light);
    margin-bottom: 15px;
    background: linear-gradient(135deg, #3498db 0%, #FF6B35 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.help-support-header .section-subtitle {
    font-size: 1.2rem;
    color: var(--dark-blue);
    margin: 0;
    opacity: 0.9;
}

.help-support-text {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 40px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(52, 152, 219, 0.1);
    backdrop-filter: blur(10px);
}

.support-description {
    font-size: 1.1rem;
    line-height: 1.8;
    color: var(--dark-blue);
    margin-bottom: 20px;
    text-align: justify;
}

.support-description:last-child {
    margin-bottom: 0;
}

.support-call {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--primary);
    text-align: center;
    margin: 30px 0 0;
    padding: 20px;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.1), rgba(255, 107, 53, 0.08));
    border-radius: 15px;
    border-left: 4px solid #FF6B35;
}

.help-support-contact {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-top: 30px;
}

.contact-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 18px;
    padding: 30px;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(52, 152, 219, 0.15);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.contact-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #3498db, #FF6B35);
}

.contact-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(52, 152, 219, 0.15);
    border-color: rgba(255, 107, 53, 0.25);
}

.contact-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #3498db, #FF6B35);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
    margin: 0 auto 20px;
    box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
}

.contact-info h4 {
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 10px;
}

.contact-info p {
    color: var(--dark-blue);
    margin: 0;
    font-size: 1rem;
}

.wtsp {
    color: #25D366 !important;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
}

.wtsp:hover {
    color: #128C7E !important;
    text-decoration: underline;
    transform: translateY(-1px);
}

/* Contact CTA Section */
.contact-cta-section {
    background: var(--gradient);
    color: white;
    position: relative;
    overflow: hidden;
}

/* Reviews Section */
.reviews-section {
    background: var(--secondary);
    position: relative;
    overflow: hidden;
    padding: 100px 0;
}

.reviews-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.4;
}

.reviews-header {
    text-align: center;
    margin-bottom: 60px;
    position: relative;
    z-index: 1;
}

.reviews-header .section-title {
    font-size: 3.5rem;
    font-weight: 800;
    color: white;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 3px;
    text-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    position: relative;
}

.reviews-header .section-title::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 4px;
    background: linear-gradient(90deg, #FFD700, #FFA500);
    border-radius: 2px;
}

.reviews-header .section-subtitle {
    font-size: 1.4rem;
    color: var(--dark-blue);
    margin: 0;
    font-weight: 300;
    letter-spacing: 1px;
}

.review-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 25px;
    padding: 35px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(20px);
    position: relative;
    overflow: hidden;
}

.review-card::before {
    content: '"';
    position: absolute;
    top: -20px;
    left: 20px;
    font-size: 120px;
    color: rgba(103, 126, 234, 0.1);
    font-family: Georgia, serif;
    z-index: 0;
}

.review-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2, #FFD700);
    border-radius: 25px 25px 0 0;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
    position: relative;
    z-index: 1;
}

.reviewer-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.reviewer-name {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
    text-transform: capitalize;
}

.review-rating {
    color: #FFD700;
    font-size: 1.1rem;
    display: flex;
    gap: 3px;
}

.review-rating .far {
    color: #ddd;
}

.review-date {
    color: #7f8c8d;
    font-size: 0.9rem;
    font-weight: 500;
    background: rgba(103, 126, 234, 0.1);
    padding: 5px 12px;
    border-radius: 15px;
    white-space: nowrap;
}

.review-content {
    position: relative;
    z-index: 1;
}

.review-content p {
    color: #34495e;
    line-height: 1.8;
    font-size: 1.1rem;
    margin: 0;
    font-style: italic;
}

.review-form-container {
    position: relative;
    z-index: 1;
}

.review-form-card {
    background: rgba(255, 255, 255, 0.98);
    border-radius: 30px;
    padding: 50px;
    box-shadow: 0 25px 70px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(20px);
    max-width: 700px;
    margin: 0 auto 50px;
    position: relative;
    overflow: hidden;
}

.review-form-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #667eea, #764ba2, #FFD700);
}

.form-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 40px;
    text-align: center;
    position: relative;
}

.form-title::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 2px;
}

.form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 10px;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-control {
    border: 2px solid rgba(103, 126, 234, 0.2);
    border-radius: 15px;
    padding: 15px 20px;
    transition: all 0.3s ease;
    font-size: 1rem;
    background: rgba(255, 255, 255, 0.9);
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(103, 126, 234, 0.25);
    background: white;
}

/* Reviews Carousel (Fixit style) */
.reviews-carousel {
    padding: 0 40px;
}
.reviews-carousel-section {
    margin-bottom: 50px;
}

.reviews-carousel-container {
    position: relative;
    max-width: 900px;
    margin: 0 auto;
}

.reviews-carousel {
    position: relative;
    overflow: hidden;
    border-radius: 20px;
    width: 100%;
}

.reviews-carousel-inner {
    display: flex;
    transition: transform 0.5s ease-in-out;
    will-change: transform;
    width: auto;
    height: auto;
    position: relative;
}

.review-carousel-item {
    min-width: 100%;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0 10px;
    flex-shrink: 0;
    box-sizing: border-box;
}

.review-carousel-item .review-card {
    width: 100%;
    margin: 0 auto;
}

.reviews-carousel-control {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 10;
    box-shadow: 0 5px 15px rgba(103, 126, 234, 0.3);
    font-size: 1.2rem;
}

.reviews-carousel-control:hover {
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 8px 20px rgba(103, 126, 234, 0.4);
}

.reviews-carousel-control.prev {
    left: 0;
}

.reviews-carousel-control.next {
    right: 0;
}

.reviews-carousel-indicators {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 20px;
}

.reviews-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(103, 126, 234, 0.3);
    border: 2px solid rgba(103, 126, 234, 0.5);
    cursor: pointer;
    transition: all 0.3s ease;
}

.reviews-indicator.active {
    background: #667eea;
    transform: scale(1.2);
}

.no-reviews-carousel {
    text-align: center;
    padding: 60px 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    border: 2px dashed rgba(103, 126, 234, 0.2);
}

.no-reviews-message {
    color: #667eea;
    font-size: 1.2rem;
    margin-bottom: 15px;
}

.contact-cta-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.cta-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    position: relative;
    z-index: 1;
}

.cta-description {
    font-size: 1.2rem;
    line-height: 1.6;
    margin-bottom: 40px;
    opacity: 0.9;
    position: relative;
    z-index: 1;
}

.cta-buttons {
    position: relative;
    z-index: 1;
}

.btn-lg {
    padding: 15px 30px;
    font-size: 1.1rem;
    font-weight: 600;
}

.btn-outline-primary {
    border-color: white;
    color: white;
}

.btn-outline-primary:hover {
    background: white;
    color: var(--primary);
    border-color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .about-hero-title {
        font-size: 2.5rem;
    }
    
    .about-hero-subtitle {
        font-size: 1.2rem;
    }
    
    .about-hero-stats {
        justify-content: center;
        gap: 30px;
    }
    
    .mission-content .section-title {
        font-size: 2rem;
    }
    
    .cta-title {
        font-size: 2rem;
    }
    
    .cta-buttons {
        display: flex;
        flex-direction: column;
        gap: 15px;
        align-items: center;
    }
    
    .cta-buttons .btn {
        width: 100%;
        max-width: 300px;
    }
}

/* Reviews Section Responsive Design */
@media (max-width: 768px) {
    .reviews-header .section-title {
        font-size: 2.5rem;
    }

    .review-card {
        padding: 25px;
    }

    .review-form-card {
        padding: 30px;
    }

    .review-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .reviewer-info {
        width: 100%;
    }
}
.email-btn{
    background: var(--gradient);
    font-weight: 600;
    color: #fff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reviewsCarousel = document.getElementById('reviewsCarousel');
    const reviewItems = document.querySelectorAll('.review-carousel-item');
    const reviewsPrevBtn = document.getElementById('reviewsPrevBtn');
    const reviewsNextBtn = document.getElementById('reviewsNextBtn');
    const reviewsIndicators = document.getElementById('reviewsIndicators');

    if (reviewsCarousel && reviewItems.length > 0) {
        let currentReviewIndex = 0;
        const totalReviewItems = reviewItems.length;
        let autoPlayInterval = null;
        let isTransitioning = false;

        reviewsCarousel.style.transform = 'translateX(0%)';

        function createReviewIndicators() {
            if (!reviewsIndicators) return;
            reviewsIndicators.innerHTML = '';
            for (let i = 0; i < totalReviewItems; i++) {
                const indicator = document.createElement('div');
                indicator.className = `reviews-indicator ${i === 0 ? 'active' : ''}`;
                indicator.addEventListener('click', () => {
                    if (!isTransitioning) {
                        goToReviewSlide(i);
                    }
                });
                reviewsIndicators.appendChild(indicator);
            }
        }

        function goToReviewSlide(index) {
            if (isTransitioning || index < 0 || index >= totalReviewItems) return;

            isTransitioning = true;
            currentReviewIndex = index;
            const offset = -index * 100;
            reviewsCarousel.style.transform = `translateX(${offset}%)`;
            updateReviewIndicators();

            setTimeout(() => {
                isTransitioning = false;
            }, 500);
        }

        function updateReviewIndicators() {
            if (!reviewsIndicators) return;
            const indicators = reviewsIndicators.querySelectorAll('.reviews-indicator');
            indicators.forEach((indicator, index) => {
                indicator.classList.toggle('active', index === currentReviewIndex);
            });
        }

        function nextReviewSlide() {
            if (isTransitioning) return;
            const nextIndex = (currentReviewIndex + 1) % totalReviewItems;
            goToReviewSlide(nextIndex);
        }

        function prevReviewSlide() {
            if (isTransitioning) return;
            const prevIndex = (currentReviewIndex - 1 + totalReviewItems) % totalReviewItems;
            goToReviewSlide(prevIndex);
        }

        function startAutoPlay() {
            if (autoPlayInterval) clearInterval(autoPlayInterval);
            autoPlayInterval = setInterval(() => {
                if (!isTransitioning) {
                    nextReviewSlide();
                }
            }, 5000);
        }

        function stopAutoPlay() {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
                autoPlayInterval = null;
            }
        }

        if (reviewsNextBtn) {
            reviewsNextBtn.addEventListener('click', () => {
                stopAutoPlay();
                nextReviewSlide();
                startAutoPlay();
            });
        }

        if (reviewsPrevBtn) {
            reviewsPrevBtn.addEventListener('click', () => {
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
        startAutoPlay();
        goToReviewSlide(0);
    }
});
</script>

<?php include BASE_PATH.'/includes/footer.php';?>
