<?php include '../includes/header.php'; ?>

<?php
// Get lab ID from URL
$lab_id = isset($_GET['lab_id']) ? (int)$_GET['lab_id'] : 0;


// submit review
if (isset($_REQUEST['entity_id']) AND $_REQUEST['entity_id'] !=0) {
        $entity_id = $_REQUEST['entity_id'];
        $commenter_name = isset($_POST['reviewer_name']) ? trim($_POST['reviewer_name']) : '';
        $commenter_gmail = isset($_POST['reviewer_email']) ? trim($_POST['reviewer_email']) : '';
        $comment = isset($_POST['review_comment']) ? trim($_POST['review_comment']) : '';
        $stars = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;

        $insert_query = "INSERT INTO feedback (entity_id, commenter_name, commenter_gmail, comment, stars, status, created_at, updated_at) 
                        VALUES ($entity_id,
                        '" . mysqli_real_escape_string($con, $commenter_name) . "', 
                        '" . mysqli_real_escape_string($con, $commenter_gmail) . "', 
                        '" . mysqli_real_escape_string($con, $comment) . "', 
                        $stars,1, NOW(), NOW())";

        $feedback_run = mysqli_query($con, $insert_query);
    }



// Fetch lab details
$lab_query = "SELECT l.*, c.city_name 
               FROM laboratories l 
               LEFT JOIN cities c ON l.city_id = c.city_id 
               WHERE l.lab_id = $lab_id AND l.status = 1";
$lab_result = mysqli_query($con, $lab_query);
$lab = mysqli_fetch_assoc($lab_result);
$entity_id = $lab['entity_id'];
// Calculate average rating from feedback table
$rating_query = "SELECT AVG(stars) as avg_rating, COUNT(feedback_id) as total_reviews 
                 FROM feedback 
                 WHERE entity_id = $entity_id AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$total_reviews = $rating_data['total_reviews'] ? $rating_data['total_reviews'] : 0;


?>
 <!-- Navbar -->
    <?php include BASE_PATH.'/includes/menu.php'; ?>
<!-- Lab Profile Section -->
<section class="lab-profile-section">
    <div class="container">
        <div class="lab-profile-card">
            <div class="row align-items-center">
                <div class="col-lg-4 col-md-5">
                    <div class="lab-image-wrapper">
                        <?php if (!empty($lab['lab_pic']) && file_exists(BASE_URL.'admin/inc/uploads/labs/' . $lab['lab_pic'])): ?>
                            <img src="<?php echo BASE_URL; ?>admin/inc/uploads/labs/<?php echo $lab['lab_pic']; ?>" alt="<?php echo $lab['lab_name']; ?>" class="lab-image">
                        <?php else: ?>
                            <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/lab.jpg" alt="<?php echo $lab['lab_name']; ?>" class="lab-image">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-8 col-md-7">
                    <div class="lab-info">
                        <div class="lab-header">
                            <h1 class="lab-name"><?php echo $lab['lab_name']; ?></h1>
                            <div class="lab-rating">
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
                        
                        <div class="lab-details">
                            <div class="detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <div class="detail-content">
                                    <span class="detail-label">Address</span>
                                    <span class="detail-value"><?php echo $lab['lab_address']; ?>, <?php echo $lab['city_name']; ?></span>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-phone"></i>
                                <div class="detail-content">
                                    <span class="detail-label">Contact</span>
                                    <span class="detail-value"><?php echo $lab['lab_phone']; ?></span>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-envelope"></i>
                                <div class="detail-content">
                                    <span class="detail-label">Email</span>
                                    <span class="detail-value"><?php echo $lab['lab_email']; ?></span>
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
                        
                        <div class="lab-actions">
                            <button class="btn btn-outline-primary btn-call-large">
                                <i class="fas fa-phone-alt"></i>
                                Call Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- Lab Reviews Section -->
<section class="lab-reviews-section section-padding bg-light">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Patient Reviews</h2>
            <p class="section-subtitle">What patients are saying about <?php echo $lab['lab_name']; ?></p>
        </div>
        
        <!-- Review Submission Form -->
        <div class="review-form-card" data-aos="fade-up">
            <div class="review-form-header">
                <h3><i class="fas fa-pen me-2"></i>Share Your Experience</h3>
                <p>Help others by sharing your experience at this laboratory</p>
            </div>
            
            <form class="review-form" method="POST">
                <input type="hidden" name="entity_id" value="<?php echo $entity_id; ?>">
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
            // Fetch lab reviews
            $reviews_query = "SELECT * FROM feedback 
                             WHERE entity_id = $entity_id AND status = 1 
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
                                    $stars = $review['stars'];
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $stars) {
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
                    <p>Be the first to share your experience at <?php echo $lab['lab_name']; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
.lab-profile-section {
    padding: 80px 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
}

.lab-profile-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.lab-image-wrapper {
    position: relative;
    overflow: hidden;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.lab-image {
    width: 100%;
    height: 300px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.lab-image-wrapper:hover .lab-image {
    transform: scale(1.05);
}

.lab-info {
    padding-left: 30px;
}

.lab-header {
    margin-bottom: 30px;
}

.lab-name {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--dark);
    margin-bottom: 10px;
    background: var(--gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.lab-rating {
    display: flex;
    align-items: center;
    gap: 10px;
}

.stars {
    color: #ffc107;
    font-size: 1.2rem;
}

.rating-text {
    color: var(--secondary);
    font-weight: 600;
}

.lab-details {
    margin-bottom: 30px;
}

.detail-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 20px;
}

.detail-item i {
    color: var(--primary);
    font-size: 1.2rem;
    margin-top: 2px;
    width: 24px;
}

.detail-content {
    flex: 1;
}

.detail-label {
    display: block;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 4px;
    font-size: 0.9rem;
}

.detail-value {
    color: var(--secondary);
    font-weight: 500;
    line-height: 1.5;
}

.lab-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.btn-appointment-large {
    background: var(--gradient);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 15px 30px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
}

.btn-appointment-large:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
    color: white;
}

.btn-call-large {
    border: 2px solid var(--primary);
    color: var(--primary);
    border-radius: 12px;
    padding: 15px 30px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: transparent;
}

.btn-call-large:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-2px);
}

.doctors-list-section {
    background: #f8fafc;
}

.section-header {
    text-align: center;
    margin-bottom: 50px;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--dark);
    margin-bottom: 10px;
}

.section-subtitle {
    color: var(--secondary);
    font-size: 1.1rem;
    font-weight: 500;
}

.doctors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

@media (max-width: 768px) {
    .lab-profile-card {
        padding: 20px;
    }
    
    .lab-info {
        padding-left: 0;
        margin-top: 20px;
    }
    
    .lab-name {
        font-size: 2rem;
    }
    
    .lab-actions {
        justify-content: center;
    }
    
    .btn-appointment-large,
    .btn-call-large {
        flex: 1;
        text-align: center;
    }
}

/* Lab Reviews Section Styles */
.lab-reviews-section {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.review-form-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 50px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.review-form-header {
    text-align: center;
    margin-bottom: 30px;
}

.review-form-header h3 {
    color: var(--dark);
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.review-form-header p {
    color: var(--secondary);
    font-size: 1rem;
}

.review-form .form-group {
    margin-bottom: 25px;
}

.review-form .form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--dark);
    font-size: 0.95rem;
}

.review-form .form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.review-form .form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
    outline: none;
}

.rating-input {
    display: flex;
    align-items: center;
    gap: 15px;
}

.star-rating {
    display: flex;
    gap: 5px;
}

.star-rating input[type="radio"] {
    display: none;
}

.star-rating .star {
    font-size: 1.5rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s ease;
}

.star-rating .star:hover {
    color: #ffc107;
}

.star-rating input[type="radio"]:checked ~ .star {
    color: #ddd;
}

.star-rating input[type="radio"]:checked + .star,
.star-rating input[type="radio"]:checked ~ .star {
    color: #ffc107;
}

.rating-text {
    color: var(--secondary);
    font-size: 0.9rem;
}

.btn-submit-review {
       background: linear-gradient(135deg, var(--primary) 0%, var(--medical-blue) 100%);
    color: black;
    /* border: none; */
    padding: 15px 40px;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 5px 15px rgba(74, 144, 226, 0.3);
}

.reviews-grid {
    display: grid;
    gap: 25px;
    margin-bottom: 40px;
}

.review-card {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.review-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.reviewer-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.reviewer-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--medical-blue) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.reviewer-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 5px;
}

.review-date {
    color: var(--secondary);
    font-size: 0.85rem;
}

.review-rating {
    display: flex;
    gap: 2px;
}

.review-rating i {
    color: #ffc107;
    font-size: 1rem;
}

.review-content {
    margin-bottom: 20px;
}

.review-content p {
    color: var(--dark);
    line-height: 1.6;
    font-size: 1rem;
}

.review-footer {
    border-top: 1px solid #f1f3f4;
    padding-top: 15px;
}

.review-actions {
    display: flex;
    gap: 15px;
}

.btn-helpful,
.btn-report {
    background: none;
    border: 1px solid #e9ecef;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s ease;
    color: var(--secondary);
}

.btn-helpful:hover {
    border-color: #28a745;
    color: #28a745;
    background: rgba(40, 167, 69, 0.1);
}

.btn-report:hover {
    border-color: #dc3545;
    color: #dc3545;
    background: rgba(220, 53, 69, 0.1);
}

.load-more-container {
    text-align: center;
}

.btn-load-more {
    background: linear-gradient(135deg, var(--secondary) 0%, #4caf50 100%);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-load-more:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(80, 200, 120, 0.3);
}

.no-reviews {
    text-align: center;
    padding: 60px 20px;
    color: var(--secondary);
}

.no-reviews i {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

.no-reviews h3 {
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.no-reviews p {
    font-size: 1rem;
}

@media (max-width: 768px) {
    .review-form-card {
        padding: 25px;
    }
    
    .review-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .reviewer-info {
        width: 100%;
    }
    
    .review-rating {
        align-self: flex-start;
    }
    
    .review-actions {
        justify-content: center;
    }
    
    .btn-helpful,
    .btn-report {
        flex: 1;
        text-align: center;
    }
}
</style>

<!-- Footer -->
<?php include BASE_PATH.'/includes/footer.php';?>

<script>
// Star Rating Functionality
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
    
    // Handle success/error messages from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const success = urlParams.get('success');
    
    if (error) {
        let errorMessage = '';
        switch(error) {
            case 'empty':
                errorMessage = 'Please fill in all required fields.';
                break;
            case 'email':
                errorMessage = 'Please enter a valid email address.';
                break;
            case 'rating':
                errorMessage = 'Please select a valid rating.';
                break;
            case 'database':
                errorMessage = 'An error occurred while submitting your review. Please try again.';
                break;
            default:
                errorMessage = 'An error occurred. Please try again.';
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
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }
    
    // Load More Reviews functionality
    const loadMoreBtn = document.getElementById('loadMoreReviews');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const labId = <?php echo $lab_id; ?>;
            const currentReviews = document.querySelectorAll('.review-card').length;
            
            fetch(`load-more-reviews.php?lab_id=${labId}&offset=${currentReviews}`)
                .then(response => response.json())
                .then(data => {
                    if (data.reviews && data.reviews.length > 0) {
                        const reviewsGrid = document.querySelector('.reviews-grid');
                        data.reviews.forEach(review => {
                            const reviewCard = createReviewCard(review);
                            reviewsGrid.appendChild(reviewCard);
                        });
                        
                        if (data.reviews.length < 5) {
                            loadMoreBtn.style.display = 'none';
                        }
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
    
    // Helpful and Report buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-helpful')) {
            e.preventDefault();
            const reviewId = e.target.dataset.reviewId;
            
            // Send AJAX request to mark as helpful
            fetch('mark-helpful.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `review_id=${reviewId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    e.target.innerHTML = '<i class="fas fa-thumbs-up me-1"></i>Helpful ✓';
                    e.target.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error marking as helpful:', error);
            });
        }
        
        if (e.target.classList.contains('btn-report')) {
            e.preventDefault();
            const reviewId = e.target.dataset.reviewId;
            
            if (confirm('Are you sure you want to report this review?')) {
                // Send AJAX request to report review
                fetch('report-review.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `review_id=${reviewId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        e.target.innerHTML = '<i class="fas fa-flag me-1"></i>Reported';
                        e.target.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error reporting review:', error);
                });
            }
        }
    });
    
    function createReviewCard(review) {
        const card = document.createElement('div');
        card.className = 'review-card';
        card.setAttribute('data-aos', 'fade-up');
        
        // Generate star rating HTML
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
                    <div class="reviewer-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="reviewer-details">
                        <h4 class="reviewer-name">${review.commenter_name}</h4>
                        <div class="review-date">
                            <i class="fas fa-calendar-alt me-1"></i>
                            ${new Date(review.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                        </div>
                    </div>
                </div>
                <div class="review-rating">
                    ${starsHTML}
                </div>
            </div>
            <div class="review-content">
                <p>${review.comment}</p>
            </div>
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
