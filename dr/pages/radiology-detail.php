<?php include '../includes/header.php'; ?>

<?php 
// Get radiology ID from URL
$rad_id = isset($_GET['rad_id']) ? (int)$_GET['rad_id'] : 0;

// Submit review
if (isset($_POST['user_id']) AND (int)$_POST['user_id'] != 0) {
    $target_user_id = (int)$_POST['user_id'];
    $commenter_name = isset($_POST['reviewer_name']) ? trim($_POST['reviewer_name']) : '';
    $commenter_gmail = isset($_POST['reviewer_email']) ? trim($_POST['reviewer_email']) : '';
    $comment = isset($_POST['review_comment']) ? trim($_POST['review_comment']) : '';
    $stars = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;

    $insert_query = "INSERT INTO feedback (user_id, commenter_name, commenter_gmail, comment, stars, status, created_at, updated_at) 
                    VALUES ($target_user_id,
                    '" . mysqli_real_escape_string($con, $commenter_name) . "', 
                    '" . mysqli_real_escape_string($con, $commenter_gmail) . "', 
                    '" . mysqli_real_escape_string($con, $comment) . "', 
                    $stars, 1, NOW(), NOW())";

    $feedback_run = mysqli_query($con, $insert_query);
}

// Fetch radiology details
$rad_query = "SELECT r.*, c.city_name 
               FROM radiology r 
               LEFT JOIN cities c ON r.city_id = c.city_id
               LEFT JOIN users u ON u.user_id = r.user_id 
               WHERE r.rad_id = $rad_id AND u.status = 1 AND r.approve = 1";
$rad_result = mysqli_query($con, $rad_query);
$rad = mysqli_fetch_assoc($rad_result);

// If no record found
if (!$rad) {
    echo "<script>window.location.href='".BASE_URL."radiology';</script>";
    exit;
}

$user_id = (int)$rad['user_id'];

// Calculate average rating from feedback table
$rating_query = "SELECT AVG(stars) as avg_rating, COUNT(feedback_id) as total_reviews 
                 FROM feedback 
                 WHERE user_id = $user_id AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$total_reviews = $rating_data['total_reviews'] ? $rating_data['total_reviews'] : 0;

// Clean phone for WhatsApp
$rad_phone_clean = preg_replace('/[^0-9]/', '', $rad['rad_phone']);
if (strpos($rad_phone_clean, '0') === 0) {
    $rad_phone_clean = '92' . substr($rad_phone_clean, 1);
}

// Fetch Notes/Message
$notes_query = "SELECT notes FROM notes WHERE user_id = $user_id LIMIT 1";
$notes_result = mysqli_query($con, $notes_query);
$notes_data = mysqli_fetch_assoc($notes_result);
$rad_notes = isset($notes_data['notes']) ? trim($notes_data['notes']) : '';

// Check condition: Show only if not empty and not just a dash '-'
$show_notes_section = (!empty($rad_notes) && $rad_notes !== '-');

// Working in Hospitals (using assign_hospital_radiology)
$working_in_q = mysqli_query($con, "SELECT h.hospital_id, h.hospital_name, h.hospital_phone, c.city_name
    from assign_hospital_radiology asg
    LEFT JOIN hospitals h ON h.hospital_id=asg.hospital_id
    LEFT JOIN cities c ON h.city_id=c.city_id
    LEFT JOIN users u ON u.user_id=asg.user_id
    WHERE u.status=1 AND h.approve=1 and asg.user_id = '". $user_id ."' AND asg.status = 1
    ORDER BY h.hospital_name");
?>

<!-- Navbar -->
<?php include BASE_PATH.'/includes/menu.php'; ?>

<!-- Radiology Profile Section -->
<section class="lab-profile-section">
    <div class="container">
        <div class="lab-profile-card">
            <div class="row align-items-center">
                <div class="col-lg-4 col-md-5">
                    <div class="lab-image-wrapper">
                        <?php if (!empty($rad['rad_pic']) && file_exists(BASE_PATH.'/admin/inc/uploads/radiology/' . $rad['rad_pic'])): ?>
                            <img src="<?php echo BASE_URL; ?>admin/inc/uploads/radiology/<?php echo $rad['rad_pic']; ?>" alt="<?php echo htmlspecialchars($rad['rad_name']); ?>" class="lab-image">
                        <?php else: ?>
                            <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/radiology.jpg" alt="<?php echo htmlspecialchars($rad['rad_name']); ?>" class="lab-image">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-8 col-md-7">
                    <div class="lab-info">
                        <div class="lab-header">
                            <h1 class="lab-name"><?php echo htmlspecialchars($rad['rad_name']); ?></h1>
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
                                    <span class="detail-value "><?php echo htmlspecialchars($rad['rad_address']); ?>, <?php echo htmlspecialchars($rad['city_name']); ?></span>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-phone"></i>
                                <div class="detail-content">
                                    <span class="detail-label">Contact</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($rad['rad_phone']); ?></span>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-envelope"></i>
                                <div class="detail-content">
                                    <span class="detail-label">Email</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($rad['rad_email']); ?></span>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-clock"></i>
                                <div class="detail-content">
                                    <span class="detail-label">Working Hours</span>
                                    <span class="detail-value">24/7 Services</span>
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

<!-- Notes / Message Section -->
<?php if ($show_notes_section): ?>
<section class="lab-notes-section section-padding bg-light">
    <div class="container">
        <div class="notes-card" data-aos="fade-up">
            <div class="notes-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="notes-content">
                <h3 class="notes-title">Message from <?php echo htmlspecialchars($rad['rad_name']); ?></h3>
                <div class="notes-text">
                    <?php echo nl2br(htmlspecialchars($rad_notes)); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Working in Hospitals -->
<?php if (mysqli_num_rows($working_in_q) > 0): ?>
<section class="lab-hospitals-section section-padding bg-white">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Working in Hospitals</h2>
            <p class="section-subtitle">Hospitals associated with <?php echo htmlspecialchars($rad['rad_name']); ?></p>
        </div>

        <div class="info-card" data-aos="fade-up">
            <div class="info-card-header">
                <h5><i class="fas fa-hospital-alt"></i> Working in </h5>
            </div>
            <div class="info-card-body">
                <?php while ($h = mysqli_fetch_assoc($working_in_q)): ?>
                    <a target="_blank" href="<?=BASE_URL?>hospital-detail?hospital_id=<?=$h['hospital_id']?>" style="text-decoration: none;">
                    <div class="hosp-chip-item">
                        <div class="hc-icon"><i class="fas fa-hospital"></i></div>
                        <div>
                            <div class="hc-name"><?= htmlspecialchars($h['hospital_name']) ?></div>
                            <div class="hc-city">
                                <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($h['city_name']) ?>
                                <?php if (!empty($h['hospital_phone'])): ?>
                                    &nbsp;·&nbsp;<i class="fas fa-phone me-1"></i><?= htmlspecialchars($h['hospital_phone']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Available Categories & Tests Section -->
<?php
// Pehle data fetch kar lein taake check kar sakein ke data hai ya nahi
$tests_query = "SELECT t.test_name, c.category_name, ct.price 
                FROM tbl_radiology_clinic_tests ct 
                INNER JOIN tbl_radiology_tests t ON ct.test_id = t.test_id 
                INNER JOIN tbl_radiology_categories c ON ct.category_id = c.category_id
                WHERE ct.clinic_id = $rad_id AND ct.status = 1 AND t.status = 1 AND c.status = 1
                ORDER BY c.category_name ASC, t.test_name ASC";

$tests_result = mysqli_query($con, $tests_query);

// Group array banayein
$grouped_tests = [];
if ($tests_result && mysqli_num_rows($tests_result) > 0) {
    while($row = mysqli_fetch_assoc($tests_result)) {
        $cat_name = $row['category_name'];
        if (!isset($grouped_tests[$cat_name])) {
            $grouped_tests[$cat_name] = [];
        }
        $grouped_tests[$cat_name][] = $row;
    }
}

// AGAR ARRAY KHALI NAHI HAI TOHI SECTION SHOW KAREIN
if (!empty($grouped_tests)): 
?>
<section class="lab-tests-section section-padding bg-white">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Available Tests & Services</h2>
            <p class="section-subtitle">Tests offered by <?php echo htmlspecialchars($rad['rad_name']); ?></p>
        </div>

        <div class="tests-list-card" data-aos="fade-up">
            <div class="table-responsive">
                <table class="table tests-table align-middle">
                    <thead>
                        <tr>
                            <th>Category / Test Name</th>
                            <th>Price</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Grouped array par loop chalayein
                        foreach($grouped_tests as $cat_name => $tests): 
                        ?>
                            <!-- Category Row -->
                            <tr style="background-color: #f0f9ff;">
                                <td colspan="3" style="font-weight: 700; color: #0D2B6E; padding: 12px 15px;">
                                    <i class="fas fa-folder-open me-2" style="color: #00A8CC;"></i> <?php echo htmlspecialchars($cat_name); ?>
                                </td>
                            </tr>
                            <!-- Tests under this category -->
                            <?php foreach($tests as $test): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-caret-right text-muted me-2"></i>
                                        <strong class="test-name"><?php echo htmlspecialchars($test['test_name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if(!empty($test['price']) && $test['price'] > 0): ?>
                                            <span class="test-price">Rs. <?php echo number_format($test['price'], 0); ?></span>
                                        <?php else: ?>
                                            <span class="price-na">Price not mentioned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if(empty($test['price']) || $test['price'] == 0): ?>
                                            <!-- WhatsApp Ask Button -->
                                            <?php 
                                            $wa_msg = urlencode("Hello " . $rad['rad_name'] . ", I want to ask the price for the test: " . $test['test_name']);
                                            $wa_link = "https://wa.me/" . $rad_phone_clean . "?text=" . $wa_msg;
                                            ?>
                                            <a href="<?php echo $wa_link; ?>" target="_blank" class="btn-wa-ask">
                                                <i class="fab fa-whatsapp"></i> Ask Price
                                            </a>
                                        <?php else: ?>
                                            <!-- Book/Contact Button -->
                                            <?php 
                                            $wa_msg_book = urlencode("Hello " . $rad['rad_name'] . ", I want to book the test: " . $test['test_name'] . " (Price: Rs. " . number_format($test['price'], 0) . ")");
                                            $wa_link_book = "https://wa.me/" . $rad_phone_clean . "?text=" . $wa_msg_book;
                                            ?>
                                            <a href="<?php echo $wa_link_book; ?>" target="_blank" class="btn-book-test">
                                                <i class="fas fa-calendar-check"></i> Book Test
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
<!-- Reviews Section -->
<section class="lab-reviews-section section-padding bg-light">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Patient Reviews</h2>
            <p class="section-subtitle">What patients are saying about <?php echo htmlspecialchars($rad['rad_name']); ?></p>
        </div>
        
        <!-- 1. REVIEWS LIST (PEHLE SHOW HOGA) -->
        <div class="reviews-list" data-aos="fade-up" data-aos-delay="100">
            <?php
            // Fetch first 5 reviews
            $reviews_query = "SELECT * FROM feedback 
                             WHERE user_id = $user_id AND status = 1 
                             ORDER BY feedback_id DESC LIMIT 5";
            $reviews_result = mysqli_query($con, $reviews_query);
            $total_reviews_q = mysqli_query($con, "SELECT COUNT(*) as total FROM feedback WHERE user_id = $user_id AND status = 1");
            $total_reviews_count = mysqli_fetch_assoc($total_reviews_q)['total'];
            ?>
            
            <?php if (mysqli_num_rows($reviews_result) > 0): ?>
                <div class="reviews-grid" id="reviewsGrid">
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
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- LOAD MORE BUTTON -->
                <?php if ($total_reviews_count > 5): ?>
                    <div class="text-center mt-4 mb-5" id="loadMoreContainer">
                        <button class="btn btn-outline-primary rounded-pill px-4" id="loadMoreReviewsBtn" data-offset="5" data-userid="<?= $user_id ?>">
                            <i class="fas fa-spinner fa-spin d-none me-2"></i> Load More Reviews
                        </button>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-reviews">
                    <i class="fas fa-comments"></i>
                    <h3>No Reviews Yet</h3>
                    <p>Be the first to share your experience at <?php echo htmlspecialchars($rad['rad_name']); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- 2. REVIEW SUBMISSION FORM (END ME SHOW HOGA) -->
        <div class="review-form-card" data-aos="fade-up">
            <div class="review-form-header">
                <h3><i class="fas fa-pen me-2"></i>Share Your Experience</h3>
                <p>Help others by sharing your experience at this radiology center</p>
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
    </div>
</section>

<link rel="stylesheet" href="<?= BASE_URL ?>style/lab-detail.css?v=<?= @filemtime(__DIR__ . '/style/lab-detail.css') ?: time() ?>">

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
    
    if(document.querySelector('.star-rating')) {
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
    }
    
    function updateStarDisplay(rating) {
        stars.forEach((star, index) => {
            if (5 - index <= rating) {
                star.style.color = '#ffc107';
            } else {
                star.style.color = '#ddd';
            }
        });
    }
});





// Load More Reviews AJAX
document.addEventListener('DOMContentLoaded', function() {
    const loadMoreBtn = document.getElementById('loadMoreReviewsBtn');
    
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const btn = this;
            const originalText = btn.innerHTML;
            const offset = btn.getAttribute('data-offset');
            const userId = btn.getAttribute('data-userid');
            const spinner = '<i class="fas fa-spinner fa-spin me-2"></i> Loading...';
            
            btn.innerHTML = spinner;
            btn.disabled = true;

            // AJAX call
            fetch(`<?= BASE_URL ?>load-more-radiology-reviews.php?user_id=${userId}&offset=${offset}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.html) {
                        // Naye reviews ko existing grid mein append karein
                        document.getElementById('reviewsGrid').insertAdjacentHTML('beforeend', data.html);
                        
                        // Naya offset set karein
                        const newOffset = parseInt(offset) + 5;
                        btn.setAttribute('data-offset', newOffset);

                        // Agar mazeed reviews nahi bache toh button hata dein
                        if (data.has_more === false) {
                            document.getElementById('loadMoreContainer').style.display = 'none';
                        } else {
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        }
                    } else {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                        document.getElementById('loadMoreContainer').style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        });
    }
});
</script>