<?php include '../includes/header.php'; ?>

<?php
// Get blood bank ID from URL
$bb_id = isset($_GET['blood_bankid']) ? (int)$_GET['blood_bankid'] : 0;

// submit review
if (isset($_POST['user_id']) && (int)$_POST['user_id'] != 0) {
    $user_id = (int)$_POST['user_id'];
    $commenter_name = isset($_POST['reviewer_name']) ? trim($_POST['reviewer_name']) : '';
    $commenter_gmail = isset($_POST['reviewer_email']) ? trim($_POST['reviewer_email']) : '';
    $comment = isset($_POST['review_comment']) ? trim($_POST['review_comment']) : '';
    $stars = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;

    $insert_query = "INSERT INTO feedback (user_id, commenter_name, commenter_gmail, comment, stars, status, created_at, updated_at)
                    VALUES ($user_id,
                    '" . mysqli_real_escape_string($con, $commenter_name) . "',
                    '" . mysqli_real_escape_string($con, $commenter_gmail) . "',
                    '" . mysqli_real_escape_string($con, $comment) . "',
                    $stars,1, NOW(), NOW())";

    $feedback_run = mysqli_query($con, $insert_query);
}

// Fetch blood bank details (status users table se)
$bb_query = "SELECT bb.*, c.city_name
               FROM blood_bank bb
               LEFT JOIN cities c ON bb.city_id = c.city_id
               LEFT JOIN users u ON u.user_id = bb.user_id
               WHERE bb.bb_id = $bb_id AND u.status = 1 AND bb.approve = 1";
$bb_result = mysqli_query($con, $bb_query);
$bb = mysqli_fetch_assoc($bb_result);

// Blood bank na mile (ya inactive ho) to page yahin band
if (!$bb) {
    include BASE_PATH.'/includes/menu.php';
    echo '<div class="container section-padding text-center"><h3>Blood Bank not found.</h3><a href="blood-banks" class="btn btn-primary mt-3">Back to Blood Banks</a></div>';
    include BASE_PATH.'/includes/footer.php';
    exit();
}

$user_id = (int)$bb['user_id'];

// Calculate average rating from feedback table
$rating_query = "SELECT AVG(stars) as avg_rating, COUNT(feedback_id) as total_reviews
                 FROM feedback
                 WHERE user_id = $user_id AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$total_reviews = $rating_data['total_reviews'] ? $rating_data['total_reviews'] : 0;

// ============================================
// FETCH BLOOD STOCK (Available Blood Bags)
// ============================================
$stock_query = "SELECT bb_available_blood.avail_b_id, bb_available_blood.stock, blood_groups.bg_name AS blood_group
                FROM bb_available_blood
                LEFT JOIN blood_groups ON blood_groups.bg_id = bb_available_blood.bg_id
                WHERE bb_available_blood.bb_id = $bb_id
                ORDER BY blood_groups.bg_id ASC";
$stock_result = mysqli_query($con, $stock_query);

// ============================================
// FETCH BLOOD BANK NOTES (Admin Message)
// ============================================
$notes_query = "SELECT * FROM notes WHERE user_id = $user_id LIMIT 1";
$notes_result = mysqli_query($con, $notes_query);
$bb_note = ($notes_result && mysqli_num_rows($notes_result) > 0) ? mysqli_fetch_assoc($notes_result) : null;

// Check condition for notes: show only if not empty and not just dash '-'
$show_notes = ($bb_note && !empty(trim($bb_note['notes'])) && trim($bb_note['notes']) !== '-');
?>

<!-- Navbar -->
<?php include BASE_PATH.'/includes/menu.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/blood-bank-detail.css?v=<?= @filemtime(__DIR__ . '/style/blood-bank-detail.css') ?: time() ?>">

<!-- Blood Bank Profile Section -->
<section class="bb-profile-section">
    <div class="container">
        <div class="bb-profile-card">
            <div class="row align-items-center">
                <div class="col-lg-4 col-md-5">
                    <div class="bb-image-wrapper">
                        <?php if (!empty($bb['bb_pic']) && file_exists(BASE_PATH.'/admin/inc/uploads/blood-banks/' . $bb['bb_pic'])): ?>
                            <img src="<?php echo BASE_URL; ?>admin/inc/uploads/blood-banks/<?php echo htmlspecialchars($bb['bb_pic']); ?>" alt="<?php echo htmlspecialchars($bb['bb_name']); ?>" class="bb-image">
                        <?php else: ?>
                            <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/bb.jpg" alt="<?php echo htmlspecialchars($bb['bb_name']); ?>" class="bb-image">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-8 col-md-7">
                    <div class="bb-info">
                        <div class="bb-header">
                            <h1 class="bb-name txt-color"><?php echo htmlspecialchars($bb['bb_name']); ?></h1>
                            <div class="bb-rating">
                                <div class="stars">
                                    <?php
                                    for($i = 1; $i <= 5; $i++) {
                                        if($i <= $avg_rating) {
                                            echo '<i class="fas fa-star"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                                <span class="rating-text"><?php echo $avg_rating; ?> (<?php echo $total_reviews; ?> Reviews)</span>
                            </div>
                        </div>

                        <div class="bb-details">
                            <div class="detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <div class="detail-content">
                                    <span class="detail-label">Address</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($bb['bb_address']); ?>, <?php echo htmlspecialchars($bb['city_name']); ?></span>
                                </div>
                            </div>

                            <div class="detail-item">
                                <i class="fas fa-phone"></i>
                                <div class="detail-content">
                                    <span class="detail-label">Emergency Contact</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($bb['bb_contact']); ?></span>
                                </div>
                            </div>

                            <div class="detail-item">
                                <i class="fas fa-clock"></i>
                                <div class="detail-content">
                                    <span class="detail-label">Working Hours</span>
                                    <span class="detail-value">24/7 Emergency Services</span>
                                </div>
                            </div>
                        </div>

                        <div class="bb-actions">
                            <a href="tel:+923371320001" class="btn btn-outline-primary btn-call-large">
                                <i class="fas fa-phone-alt"></i>
                                Emergency Call
                                </a>
                            <button class="btn btn-outline-primary btn-call-large">
                                <i class="fas fa-tint"></i>
                                Donate Blood
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- NEW SECTION: Available Blood Bags            -->
<!-- ============================================ -->
<section class="bb-stock-section section-padding bg-white">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title txt-color">Available Blood Bags</h2>
            <p class="section-subtitle txt-color">Current stock availability at <?php echo htmlspecialchars($bb['bb_name']); ?></p>
        </div>

        <?php if (mysqli_num_rows($stock_result) > 0): ?>
            <div class="blood-stock-grid">
                <?php while ($stock = mysqli_fetch_assoc($stock_result)): 
                    $is_available = ($stock['stock'] > 0);
                ?>
                    <div class="blood-stock-card <?php echo $is_available ? 'in-stock' : 'out-stock'; ?>">
                        <div class="blood-group-icon">
                            <i class="fas fa-tint"></i>
                        </div>
                        <div class="blood-group-name"><?php echo htmlspecialchars($stock['blood_group']); ?></div>
                        <div class="blood-stock-count">
                            <?php if ($is_available): ?>
                                <span class="stock-available"><?php echo (int)$stock['stock']; ?> Bags</span>
                            <?php else: ?>
                                <span class="stock-unavailable">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-stock text-center">
                <i class="fas fa-tint-slash"></i>
                <h3>No Stock Information</h3>
                <p>This blood bank has not updated its blood stock yet.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================ -->
<!-- NEW SECTION: Blood Bank Admin Message        -->
<!-- ============================================ -->
<?php if ($show_notes): ?>
<section class="bb-notes-section section-padding">
    <div class="container">
        <div class="notes-card-bb" data-aos="fade-up">
            <div class="notes-icon-bb">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div class="notes-content-bb">
                <h3 class="notes-title-bb">Message from Admin</h3>
                <div class="notes-text-bb">
                    <?php echo nl2br(htmlspecialchars($bb_note['notes'])); ?>
                </div>
                <?php if (!empty($bb_note['updated_at'])): ?>
                    <div class="notes-date-bb">
                        <i class="fas fa-clock me-1"></i>
                        Updated: <?php echo date('d M Y, h:i A', strtotime($bb_note['updated_at'])); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Blood Bank Reviews Section -->
<section class="bb-reviews-section section-padding bg-light">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title txt-color">Donor & Patient Reviews</h2>
            <p class="section-subtitle txt-color">What people are saying about <?php echo htmlspecialchars($bb['bb_name']); ?></p>
        </div>

        <!-- Review Submission Form -->
        <div class="review-form-card" data-aos="fade-up">
            <div class="review-form-header">
                <h3><i class="fas fa-pen me-2 txt-color"></i>Share Your Experience</h3>
                <p class="txt-color">Help others by sharing your experience at this blood bank</p>
            </div>

            <form class="review-form" method="POST">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Your Name *</label>
                            <input type="text" name="reviewer_name" class="form-control" placeholder="Enter your name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="reviewer_email" class="form-control" placeholder="your@email.com" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Rating *</label>
                    <div class="rating-input">
                        <div class="star-rating">
                            <input type="radio" id="star5" name="rating" value="5" required>
                            <label for="star5" class="star"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star4" name="rating" value="4">
                            <label for="star4" class="star"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star3" name="rating" value="3">
                            <label for="star3" class="star"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star2" name="rating" value="2">
                            <label for="star2" class="star"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star1" name="rating" value="1">
                            <label for="star1" class="star"><i class="fas fa-star"></i></label>
                        </div>
                        <span class="rating-text">Click to rate</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Your Review *</label>
                    <textarea name="review_comment" class="form-control" rows="4" placeholder="Share your experience, mention quality of service, staff behavior, facilities, etc..." required></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-submit-review">
                        <i class="fas fa-paper-plane me-2"></i>Submit Review
                    </button>
                </div>
            </form>
        </div>

        <!-- Reviews List -->
        <div class="reviews-list" data-aos="fade-up" data-aos-delay="100">
            <?php
            // Fetch blood bank reviews
            $reviews_query = "SELECT * FROM feedback
                             WHERE user_id = $user_id AND status = 1
                             ORDER BY feedback_id DESC LIMIT 10";
            $reviews_result = mysqli_query($con, $reviews_query);
            ?>

            <?php if (mysqli_num_rows($reviews_result) > 0): ?>
                <div class="reviews-grid">
                    <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="reviewer-details">
                                        <h4 class="reviewer-name"><?php echo htmlspecialchars($review['commenter_name']); ?></h4>
                                        <div class="review-date">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="review-rating">
                                    <?php
                                    $review_stars = $review['stars'];
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $review_stars) {
                                            echo '<i class="fas fa-star"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="review-content">
                                <p><?php echo htmlspecialchars($review['comment']); ?></p>
                            </div>
                            <div class="review-footer">
                                <div class="review-actions">
                                    <button class="btn-helpful" data-review-id="<?php echo $review['feedback_id']; ?>">
                                        <i class="fas fa-thumbs-up me-1"></i>Helpful
                                    </button>
                                    <button class="btn-report" data-review-id="<?php echo $review['feedback_id']; ?>">
                                        <i class="fas fa-flag me-1"></i>Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Load More Button -->
                <div class="load-more-container">
                    <button class="btn btn-load-more" id="loadMoreReviews">
                        <i class="fas fa-plus-circle me-2"></i>Load More Reviews
                    </button>
                </div>
            <?php else: ?>
                <div class="no-reviews">
                    <i class="fas fa-comments"></i>
                    <h3>No Reviews Yet</h3>
                    <p>Be the first to share your experience at <?php echo htmlspecialchars($bb['bb_name']); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Footer -->
<?php include BASE_PATH.'/includes/footer.php';?>

<script>
// (Aapka purana JavaScript code yahan waise hi rahega)
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-rating .star');
    const ratingText = document.querySelector('.rating-text');
    const ratingInputs = document.querySelectorAll('.star-rating input[type="radio"]');

    const ratingTexts = {
        1: 'Poor',
        2: 'Fair',
        3: 'Good',
        4: 'Very Good',
        5: 'Excellent'
    };

    stars.forEach((star, index) => {
        star.addEventListener('click', function() {
            const rating = 5 - index;
            ratingInputs[rating - 1].checked = true;
            updateStarDisplay(rating);
            ratingText.textContent = ratingTexts[rating];
        });

        star.addEventListener('mouseenter', function() {
            const rating = 5 - index;
            updateStarDisplay(rating);
            ratingText.textContent = ratingTexts[rating];
        });
    });

    document.querySelector('.star-rating').addEventListener('mouseleave', function() {
        const checkedInput = document.querySelector('.star-rating input[type="radio"]:checked');
        if (checkedInput) {
            updateStarDisplay(checkedInput.value);
            ratingText.textContent = ratingTexts[checkedInput.value];
        } else {
            updateStarDisplay(0);
            ratingText.textContent = 'Click to rate';
        }
    });

    function updateStarDisplay(rating) {
        stars.forEach((star, index) => {
            if (5 - index <= rating) {
                star.style.color = '#ffc107';
            } else {
                star.style.color = '#ddd';
            }
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const success = urlParams.get('success');

    if (error) {
        let errorMessage = '';
        switch(error) {
            case 'empty': errorMessage = 'Please fill in all required fields.'; break;
            case 'email': errorMessage = 'Please enter a valid email address.'; break;
            case 'rating': errorMessage = 'Please select a valid rating.'; break;
            case 'database': errorMessage = 'An error occurred while submitting your review. Please try again.'; break;
            default: errorMessage = 'An error occurred. Please try again.';
        }
        showAlert(errorMessage, 'error');
    }

    if (success === 'review') {
        showAlert('Thank you! Your review has been submitted successfully.', 'success');
    }

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => {
            if (alertDiv.parentNode) alertDiv.parentNode.removeChild(alertDiv);
        }, 5000);
    }

    const loadMoreBtn = document.getElementById('loadMoreReviews');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const bbId = <?php echo $bb_id; ?>;
            const currentReviews = document.querySelectorAll('.review-card').length;

            fetch(`load-more-reviews.php?bloodb_id=${bbId}&offset=${currentReviews}`)
                .then(response => response.json())
                .then(data => {
                    if (data.reviews && data.reviews.length > 0) {
                        const reviewsGrid = document.querySelector('.reviews-grid');
                        data.reviews.forEach(review => {
                            const reviewCard = createReviewCard(review);
                            reviewsGrid.appendChild(reviewCard);
                        });
                        if (data.reviews.length < 5) loadMoreBtn.style.display = 'none';
                    } else {
                        loadMoreBtn.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error loading more reviews:', error);
                    showAlert('Error loading more reviews. Please try again.', 'error');
                });
        });
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-helpful')) {
            e.preventDefault();
            const reviewId = e.target.dataset.reviewId;
            fetch('mark-helpful.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `review_id=${reviewId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    e.target.innerHTML = '<i class="fas fa-thumbs-up me-1"></i>Helpful ✓';
                    e.target.disabled = true;
                }
            })
            .catch(error => console.error('Error marking as helpful:', error));
        }

        if (e.target.classList.contains('btn-report')) {
            e.preventDefault();
            const reviewId = e.target.dataset.reviewId;
            if (confirm('Are you sure you want to report this review?')) {
                fetch('report-review.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `review_id=${reviewId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        e.target.innerHTML = '<i class="fas fa-flag me-1"></i>Reported';
                        e.target.disabled = true;
                    }
                })
                .catch(error => console.error('Error reporting review:', error));
            }
        }
    });

    function createReviewCard(review) {
        const card = document.createElement('div');
        card.className = 'review-card';
        card.setAttribute('data-aos', 'fade-up');
        let starsHTML = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= review.stars) {
                starsHTML += '<i class="fas fa-star"></i>';
            } else {
                starsHTML += '<i class="far fa-star"></i>';
            }
        }
        card.innerHTML = `
            <div class="review-header">
                <div class="reviewer-info">
                    <div class="reviewer-avatar"><i class="fas fa-user"></i></div>
                    <div class="reviewer-details">
                        <h4 class="reviewer-name">${review.commenter_name}</h4>
                        <div class="review-date">
                            <i class="fas fa-calendar-alt me-1"></i>
                            ${new Date(review.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                        </div>
                    </div>
                </div>
                <div class="review-rating">${starsHTML}</div>
            </div>
            <div class="review-content"><p>${review.comment}</p></div>
            <div class="review-footer">
                <div class="review-actions">
                    <button class="btn-helpful" data-review-id="${review.feedback_id}">
                        <i class="fas fa-thumbs-up me-1"></i>Helpful
                    </button>
                    <button class="btn-report" data-review-id="${review.feedback_id}">
                        <i class="fas fa-flag me-1"></i>Report
                    </button>
                </div>
            </div>
        `;
        return card;
    }
});
</script>