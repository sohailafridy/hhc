<?php include '../includes/header.php'; ?>

<?php
    // Get doctor ID from URL
    $doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
    
    if ($doctor_id > 0) {
        // Fetch doctor details
        $query = "SELECT d.*, c.city_name, h.hospital_name 
                 FROM doctors d 
                 LEFT JOIN cities c ON d.city_id = c.city_id 
                 LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id 
                 LEFT JOIN entities e ON e.entity_id = d.entity_id
                 WHERE d.doctor_id = $doctor_id AND e.status = 1 AND d.approve=1";
        $result = mysqli_query($con, $query);
        $doctor = mysqli_fetch_assoc($result);
    }


    if (isset($_REQUEST['entity_id']) AND $_REQUEST['entity_id'] !=0) {
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
                        $stars,1, NOW(), NOW())";

        $feedback_run = mysqli_query($con, $insert_query);
    }


?>
<style>
     /* Clinical Record Cards */
    .clinical-record-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        margin-bottom: 20px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    
    .clinical-record-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
    }
    
    .clinical-record-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        font-weight: 600;
        display: flex;
        align-items: center;
    }
    
    .clinical-record-header h6 {
        font-size: 16px;
        margin: 0;
    }
    
    .clinical-record-body {
        padding: 20px;
    }
    
    .clinical-info-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .clinical-info-item:hover {
        background: #e9ecef;
    }
    
    .clinical-info-item i {
        font-size: 18px;
        margin-right: 12px;
        width: 24px;
        text-align: center;
    }
    
    .clinical-info-item div {
        flex: 1;
    }
    
    .clinical-info-item small {
        display: block;
        font-size: 12px;
        margin-bottom: 2px;
    }
    
    .clinical-info-item strong {
        font-size: 14px;
        color: #495057;
    }
    
    .clinical-contact {
        display: flex;
        align-items: center;
        padding: 15px;
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border-radius: 8px;
        border-left: 4px solid #ffc107;
        margin-top: 10px;
    }
    
    .clinical-contact i {
        font-size: 20px;
        margin-right: 15px;
        color: #f39c12;
    }
    
    .clinical-contact div {
        flex: 1;
    }
    
    .clinical-contact small {
        display: block;
        font-size: 12px;
        color: #856404;
        margin-bottom: 2px;
    }
    
    /* Clinical Information Section */
    .clinical-info-section {
        margin-top: 40px;
    }
    
    .section-header {
        margin-bottom: 40px;
    }
    
    .section-title-main {
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .section-title-main i {
        color: var(--primary);
    }
    
    .section-subtitle {
        font-size: 1.1rem;
        color: #6c757d;
        margin-bottom: 0;
    }
    
    .clinical-record-header h6 a {
        color: white;
        text-decoration: none;
        transition: opacity 0.3s ease;
    }
    
    .clinical-record-header h6 a:hover {
        opacity: 0.8;
    }
    
    .clinical-detail {
        display: flex;
        align-items: center;
        padding: 15px;
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-radius: 8px;
        border-left: 4px solid #2196f3;
        margin-top: 10px;
    }
    
    .clinical-detail i {
        font-size: 20px;
        margin-right: 15px;
        color: #2196f3;
    }
    
    .clinical-detail div {
        flex: 1;
    }
    
    .clinical-detail small {
        display: block;
        font-size: 12px;
        color: #1565c0;
        margin-bottom: 2px;
    }
    
    /* Modern Doctor Detail Section */
    #doctor-detail {
        background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,249,250,0.95) 100%);
        border-radius: 25px;
        overflow: hidden;
        box-shadow: 
            0 20px 60px rgba(0,0,0,0.1),
            0 0 0 1px rgba(255,255,255,0.1) inset;
        backdrop-filter: blur(20px);
        position: relative;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    #doctor-detail::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        border-radius: 25px 25px 0 0;
    }
    
    #doctor-detail:hover {
        transform: translateY(-5px);
        box-shadow: 
            0 30px 80px rgba(0,0,0,0.15),
            0 0 0 1px rgba(255,255,255,0.2) inset;
    }
    
    #doctor-detail .col-lg-4 {
        position: relative;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 25px 0 0 25px;
        overflow: hidden;
    }
    
    #doctor-detail .col-lg-4::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -20px;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
    }
    
    #doctor-detail .col-lg-8 {
        padding: 0;
        background: rgba(255,255,255,0.8);
        border-radius: 0 25px 25px 0;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }
    
    /* Enhanced Profile Section */
    .doctor-profile-section {
        padding: 50px 40px;
        text-align: center;
        background: transparent;
        position: relative;
        z-index: 2;
    }
    
    .doctor-image-wrapper {
        margin-bottom: 35px;
        position: relative;
    }
    
    .doctor-profile-img {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        border: 5px solid rgba(255,255,255,0.3);
        object-fit: cover;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }
    
    .doctor-profile-img:hover {
        transform: scale(1.05);
        border-color: rgba(255,255,255,0.5);
    }
    
    .doctor-avatar-placeholder {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: rgba(255,255,255,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        border: 5px solid rgba(255,255,255,0.3);
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }
    
    .doctor-avatar-placeholder:hover {
        background: rgba(255,255,255,0.25);
        transform: scale(1.05);
    }
    
    .doctor-basic-info {
        color: white;
        position: relative;
        z-index: 2;
    }
    
    .doctor-name {
        color: #E0E7FF !important;
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 15px;
        text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        letter-spacing: -0.5px;
    }
    
    .doctor-specialization {
        font-size: 1.2rem;
        margin-bottom: 20px;
        opacity: 0.95;
        font-weight: 500;
    }
    
    .doctor-experience {
        font-size: 1rem;
        margin: 0;
        opacity: 0.9;
        font-weight: 400;
    }
    
    /* Enhanced Details Section */
    .doctor-details-section {
        padding: 50px 40px;
        background: transparent;
        position: relative;
    }
    
    .doctor-details-section::before {
        content: '';
        position: absolute;
        top: 20px;
        left: -20px;
        width: 4px;
        height: calc(100% - 40px);
        background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
        border-radius: 2px;
    }
    
    /* Enhanced Section Titles */
    .section-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        position: relative;
        padding-left: 20px;
    }
    
    .section-title::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 24px;
        background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
        border-radius: 2px;
    }
    
    /* Enhanced Contact Grid */
    .contact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
    }
    
    .contact-item {
        justify-content: left !important;
        display: flex;
        align-items: center;
        padding: 25px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 15px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(102, 126, 234, 0.1);
        position: relative;
        overflow: hidden;
    }
    
    .contact-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .contact-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        border-color: rgba(102, 126, 234, 0.2);
    }
    
    .contact-item:hover::before {
        left: 100%;
    }
    
    .contact-item i {
        font-size: 1.8rem;
        color: var(--primary);
        margin-right: 20px;
        width: 35px;
        text-align: center;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        padding: 15px;
        border-radius: 12px;
    }
    
    /* Enhanced Professional Info */
    .professional-info {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 15px;
        padding: 30px;
        border: 1px solid rgba(102, 126, 234, 0.1);
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 20px 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-row:hover {
        padding-left: 10px;
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, transparent 100%);
        margin: 0 -10px;
        padding-right: 10px;
    }
    
    .info-row label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.95rem;
    }
    
    .info-row span {
        color: #212529;
        font-weight: 500;
        font-size: 0.95rem;
    }
    
    /* Enhanced About Section */
    .about-content {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 15px;
        padding: 30px;
        line-height: 1.7;
        border: 1px solid rgba(102, 126, 234, 0.1);
        position: relative;
    }
    
    .about-content p {
        margin: 0;
        color: #495057;
        font-size: 1rem;
    }
    
    /* Enhanced Action Buttons */
    .action-buttons {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        margin-top: 40px;
    }
    
    .action-buttons .btn {
        padding: 15px 30px;
        border-radius: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .action-buttons .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .action-buttons .btn:hover::before {
        left: 100%;
    }
    
    .action-buttons .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    /* Responsive Design */
    @media (max-width: 991px) {
        #doctor-detail .col-lg-4 {
            border-radius: 25px 25px 0 0;
        }
        
        #doctor-detail .col-lg-8 {
            border-radius: 0 0 25px 25px;
        }
        
        .doctor-details-section::before {
            display: none;
        }
    }
    
    @media (max-width: 768px) {
        .doctor-profile-section {
            padding: 40px 30px;
        }
        
        .doctor-details-section {
            padding: 40px 30px;
        }
        
        .contact-grid {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .action-buttons .btn {
            width: 100%;
        }
        
        .doctor-name {
            font-size: 1.8rem;
        }
        
        .section-title-main {
            font-size: 1.8rem;
        }
    }
    
    /* Modern Patient Feedback Section */
    .patient-feedback-section {
        background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,249,250,0.95) 100%);
        border-radius: 25px;
        padding: 50px 40px;
        margin-top: 60px;
        border: 1px solid rgba(102, 126, 234, 0.1);
        position: relative;
        overflow: hidden;
    }
    
    .patient-feedback-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #667eea 100%);
        animation: shimmer 3s ease-in-out infinite;
    }
    
    @keyframes shimmer {
        0%, 100% { transform: translateX(-100%); }
        50% { transform: translateX(100%); }
    }
    
    /* Feedback Form Container */
    .feedback-form-container {
        margin-bottom: 50px;
    }
    
    .feedback-form-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 20px;
        padding: 40px;
        box-shadow: 
            0 20px 40px rgba(0,0,0,0.1),
            0 0 0 1px rgba(102, 126, 234, 0.1) inset;
        border: 1px solid rgba(102, 126, 234, 0.05);
        position: relative;
        overflow: hidden;
    }
    
    .feedback-form-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(102, 126, 234, 0.05) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }
    
    .form-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 30px;
        text-align: center;
        position: relative;
        padding-bottom: 15px;
    }
    
    .form-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border-radius: 2px;
    }
    
    .feedback-form .form-group {
        margin-bottom: 25px;
    }
    
    .feedback-form .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
    }
    
    .feedback-form .form-control {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 15px;
        font-size: 0.95rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: rgba(255,255,255,0.8);
        backdrop-filter: blur(10px);
    }
    
    .feedback-form .form-control:focus {
        border-color: var(--primary);
        box-shadow: 
            0 0 0 4px rgba(102, 126, 234, 0.1),
            0 10px 25px rgba(102, 126, 234, 0.15);
        outline: none;
        transform: translateY(-2px);
        background: rgba(255,255,255,0.95);
    }
    
    /* Star Rating */
    .rating-container {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .star-rating {
        display: flex;
        gap: 8px;
        position: relative;
        z-index: 10;
    }
    
    .star-rating .star {
        font-size: 1.5rem;
        color: #dee2e6;
        cursor: pointer;
        transition: all 0.2s ease;
        transform-origin: center;
        pointer-events: auto;
        position: relative;
        z-index: 15;
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }
    
    .star-rating .star:hover {
        color: #ffc107;
        transform: scale(1.2);
    }
    
    .star-rating .star.active {
        color: #ffc107;
        animation: starPulse 0.3s ease;
    }
    
    @keyframes starPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.3); }
        100% { transform: scale(1); }
    }
    
    .rating-text {
        font-weight: 600;
        color: var(--primary);
        font-size: 0.95rem;
        padding: 8px 16px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        border-radius: 20px;
        border: 1px solid rgba(102, 126, 234, 0.2);
    }
    
    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 15px 30px;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .btn-submit::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(102, 126, 234, 0.3);
    }
    
    .btn-submit:hover::before {
        left: 100%;
    }
    
    .btn-reset {
        background: transparent;
        color: #6c757d;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 15px 30px;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }
    
    .btn-reset:hover {
        border-color: #adb5bd;
        background: rgba(108, 117, 125, 0.1);
        transform: translateY(-2px);
    }
    
    /* Feedback Display */
    .feedbacks-display {
        margin-top: 50px;
    }
    
    .feedbacks-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 30px;
        text-align: center;
        position: relative;
        padding-bottom: 15px;
    }
    
    .feedbacks-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 50px;
        height: 3px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border-radius: 2px;
    }
    
    .feedbacks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 25px;
    }
    
    .feedback-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 20px;
        padding: 25px;
        box-shadow: 
            0 10px 30px rgba(0,0,0,0.08),
            0 0 0 1px rgba(102, 126, 234, 0.05) inset;
        border: 1px solid rgba(102, 126, 234, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .feedback-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .feedback-card:hover {
        transform: translateY(-5px);
        box-shadow: 
            0 20px 40px rgba(0,0,0,0.12),
            0 0 0 1px rgba(102, 126, 234, 0.15) inset;
    }
    
    .feedback-card:hover::before {
        opacity: 1;
    }
    
    .feedback-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }
    
    .patient-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .patient-avatar {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    .patient-details {
        flex: 1;
    }
    
    .patient-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: #212529;
        margin-bottom: 5px;
    }
    
    .patient-email {
        font-size: 0.85rem;
        color: #6c757d;
        margin: 0;
    }
    
    .feedback-rating {
        text-align: right;
    }
    
    .feedback-rating .stars {
        display: flex;
        gap: 3px;
        margin-bottom: 5px;
    }
    
    .feedback-rating .stars .fa-star {
        font-size: 0.9rem;
        color: #dee2e6;
    }
    
    .feedback-rating .stars .fa-star.active {
        color: #ffc107;
    }
    
    .rating-number {
        font-size: 0.85rem;
        font-weight: 600;
        color: #495057;
        background: rgba(255, 193, 7, 0.1);
        padding: 4px 8px;
        border-radius: 10px;
    }
    
    .feedback-content {
        margin-bottom: 20px;
    }
    
    .feedback-comment {
        font-size: 0.95rem;
        line-height: 1.6;
        color: #495057;
        background: rgba(248, 249, 250, 0.5);
        padding: 15px;
        border-radius: 12px;
        border-left: 3px solid var(--primary);
    }
    
    .feedback-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 1px solid rgba(0,0,0,0.05);
    }
    
    .feedback-date {
        font-size: 0.8rem;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .feedback-actions {
        display: flex;
        gap: 10px;
    }
    
    .like-btn {
        background: transparent;
        color: #6c757d;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 8px 15px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .like-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: rgba(102, 126, 234, 0.1);
        transform: translateY(-1px);
    }
    
    .like-btn.liked {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    
    /* No Feedback Message */
    .no-feedbacks {
        text-align: center;
        padding: 60px 40px;
        background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,249,250,0.95) 100%);
        border-radius: 20px;
        border: 2px dashed rgba(102, 126, 234, 0.2);
    }
    
    .no-feedbacks i {
        font-size: 3rem;
        color: var(--primary);
        margin-bottom: 20px;
        opacity: 0.6;
    }
    
    .no-feedbacks h5 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #495057;
        margin-bottom: 10px;
    }
    
    .no-feedbacks p {
        color: #6c757d;
        font-size: 0.95rem;
        margin: 0;
    }
    
    /* Mobile Responsive for Feedback Section */
    @media (max-width: 768px) {
        .patient-feedback-section {
            padding: 30px 20px;
            margin-top: 40px;
        }
        
        .feedback-form-card {
            padding: 25px;
        }
        
        .form-title {
            font-size: 1.3rem;
        }
        
        .feedbacks-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .feedback-card {
            padding: 20px;
        }
        
        .feedback-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }
        
        .feedback-rating {
            text-align: left;
        }
        
        .feedback-footer {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn-submit,
        .btn-reset {
            width: 100%;
        }
    }
    .doctor-basic-info{
    text-align:center;
}

.doctor-name{
    color:#fff;
    font-size:34px;
    font-weight:700;
    margin-bottom:10px;
}

.specialization-badge{
    display:inline-flex;
    align-items:center;
    gap:8px;
    background:rgba(255,255,255,.18);
    color:#fff;
    padding:8px 18px;
    border-radius:30px;
    font-size:17px;
    font-weight:600;
    border:1px solid rgba(255,255,255,.35);
    margin-bottom:15px;
}

.doctor-degree{
    color:#F8FAFC;
    font-size:20px;
    font-weight:600;
    margin-bottom:12px;
}

.static-clinical-info{
    color:#E2E8F0;
    font-size:16px;
    margin-bottom:15px;
}

.doctor-experience{
    color:#fff;
    font-size:17px;
    font-weight:500;
}

.doctor-rating{
    margin-top:15px;
    color:#FFD700;
    font-weight:600;
}
</style>
<body>

    <!-- Navbar -->
    <?php include BASE_PATH.'/includes/menu.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container position-relative">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">Doctor Profile</h1>
                    <p class="lead mb-0" data-aos="fade-up" data-aos-delay="200">Complete doctor information and details</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Doctor Detail Section -->
    <section class="section-padding">
        <div class="container">
            <?php if ($doctor && !empty($doctor)): ?>
                <div class="doctor-detail-card" data-aos="fade-up">
                    <div class="row" id="doctor-detail">
                        <div class="col-lg-4">
                            <div class="doctor-profile-section">
                                <div class="doctor-image-wrapper">
                                    <?php if(!empty($doctor['doctor_pic'])): ?>
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" 
                                             alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" class="doctor-profile-img">
                                    <?php else: ?>
                                        <div class="doctor-avatar-placeholder">
                                            <i class="fas fa-user-md fa-4x"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="doctor-basic-info">

                                    <h2 class="doctor-name">
                                        Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?>
                                    </h2>

                                    <div class="specialization-badge">
                                        <i class="fas fa-user-md"></i>
                                        <?php
                                        $spec_query = "SELECT `type` FROM dr_cat_types WHERE dr_cat_type_id = " . $doctor['cat_type_id'];
                                        $spec_result = mysqli_query($con, $spec_query);
                                        $spec = mysqli_fetch_assoc($spec_result);
                                        echo $spec ? htmlspecialchars($spec['type']) : 'General Practitioner';
                                        ?>
                                    </div>

                                    <p class="doctor-degree">
                                        <?php echo htmlspecialchars($doctor['short_detail']); ?>
                                    </p>

                                    <p class="static-clinical-info">
                                        <?php echo htmlspecialchars($doctor['static_clinical_info']); ?>
                                    </p>
                                    <?php
                                        if ($doctor['experience_years'] !='' && $doctor['experience_years'] !=0) { ?>
                                            <p class="doctor-experience">
                                            <i class="fas fa-briefcase me-2"></i>
                                            <?php echo $doctor['experience_years']; ?> Years Experience
                                        </p>
                                       <?php }
                                    ?>

                                     <?php 
                                        // Calculate average rating
                                        $rating_query = "SELECT AVG(stars) as avg_rating, COUNT(*) as total_reviews 
                                                      FROM feedback WHERE entity_id = " . $doctor['entity_id'];
                                        $rating_result = mysqli_query($con, $rating_query);
                                        $rating_data = mysqli_fetch_assoc($rating_result);
                                        $avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
                                        $total_reviews = $rating_data['total_reviews'] ? $rating_data['total_reviews'] : 0;
                                        $rating_percentage = ($avg_rating / 5) * 100;
                                        if($avg_rating !=0){
                                        ?>
                                            <p class="doctor-rating">
                                                <i class="fas fa-star me-2"></i>
                                                <span class="rating-score"><?php echo $avg_rating; ?></span>/5.0 
                                                <span class="rating-percentage">(<?php echo $rating_percentage; ?>%)</span>
                                                <span class="total-reviews">based on <?php echo $total_reviews; ?> reviews</span>
                                            </p>
                                        <?php } ?>


                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-8">
                            <div class="doctor-details-section">
                                <!-- Contact Information -->
                                <div class="detail-section">
                                    <h3 class="section-title">
                                        <i class="fas fa-contact-card me-2"></i>Contact Information
                                    </h3>
                                    <div class="contact-grid">
                                        <?php if(!empty($doctor['doctor_phone'])): ?>
                                        <div class="contact-item">
                                            <i class="fas fa-phone"></i>
                                            <div>
                                                <label>Phone</label>
                                                <span><?php echo htmlspecialchars($doctor['doctor_phone']); ?></span>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if(!empty($doctor['doctor_email'])): ?>
                                        <div class="contact-item">
                                            <i class="fas fa-envelope"></i>
                                            <div>
                                                <label>Email</label>
                                                <span><?php echo htmlspecialchars($doctor['doctor_email']); ?></span>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="contact-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <div>
                                                <label>City</label>
                                                <span><?php echo htmlspecialchars($doctor['city_name']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Professional Information -->
                              <!--   <div class="detail-section">
                                    <h3 class="section-title">
                                        <i class="fas fa-user-md me-2"></i>Professional Information
                                    </h3>
                                    <div class="professional-info">
                                        <div class="info-row">
                                            <label>Doctor Type</label>
                                            <span><?php echo htmlspecialchars($spec['type']); ?></span>
                                        </div>
                                        
                                        <?php if(!empty($doctor['clinic_name'])): ?>
                                        <div class="info-row">
                                            <label>Clinic Name</label>
                                            <span><?php echo htmlspecialchars($doctor['clinic_name']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if(!empty($doctor['clinic_address'])): ?>
                                        <div class="info-row">
                                            <label>Clinic Address</label>
                                            <span><?php echo htmlspecialchars($doctor['clinic_address']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div> -->
                                
                                <!-- About Section -->
                                <?php if(!empty($doctor['short_detail'])): ?>
                                <div class="detail-section">
                                    <h3 class="section-title">
                                        <i class="fas fa-info-circle me-2"></i>About Doctor
                                    </h3>
                                    <div class="about-content">
                                        <p>
                                            <?php echo nl2br(htmlspecialchars($doctor['short_detail'])); ?> <br>
                                            <?php echo nl2br(htmlspecialchars($doctor['other'])); ?>
                                        </p>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Action Buttons -->
                                <div class="action-buttons">
                                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Doctors
                                    </a>
                                    <!-- <button class="btn btn-primary" onclick="bookAppointment(<?php echo $doctor['doctor_id']; ?>)">
                                        <i class="fas fa-calendar-check me-2"></i>Book Appointment
                                    </button> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Clinical Information Section -->
                    <div class="clinical-info-section mt-5" data-aos="fade-up">
                        <div class="section-header text-center mb-4">
                            <h2 class="section-title-main">
                                <i class="fas fa-hospital-alt me-3"></i>Clinical Information
                            </h2>
                            <p class="section-subtitle">Doctor's clinical schedule and availability at different hospitals</p>
                        </div>
                        
                        <div class="row g-4">
                            <?php 
                            // Get clinical information for this doctor
                            $clinical_query = "SELECT ci.* ,hospitals.hospital_name,hospitals.hospital_id
                                            FROM clinical_info ci 
                                            INNER JOIN doctor_in_hospital dih ON ci.doctor_in_hosp_id = dih.doctor_in_hosp_id 
                                            left join hospitals on dih.hospital_id = hospitals.hospital_id
                                            WHERE dih.doctor_id = " . $doctor['doctor_id'] . " ";
                            $clinical_result = mysqli_query($con, $clinical_query); ?>
                            <div class="row m-2">
                                <?php while($clinical = mysqli_fetch_assoc($clinical_result)){ ?>

                                    <div class="col-lg-6 col-md-6 mb-4">
    <div class="clinical-record-card h-100">

        <div class="clinical-record-header">
            <i class="fas fa-hospital me-2"></i>
            <h6>
                <?php
                if ($clinical['hospital_name'] != '') {
                    echo '<a href="' . BASE_URL . 'admin/hospitals/detail?id=' . $clinical['hospital_id'] . '" target="_blank">Hospital ' . htmlspecialchars($clinical['hospital_name']) . '</a>';
                } else {
                    echo 'Personal Clinic';
                }
                ?>
            </h6>
        </div>

        <div class="clinical-record-body">

            <div class="row">

                <!-- Left Column -->
                <div class="col-md-6">

                    <div class="clinical-info-item">
                        <i class="fas fa-clock text-primary"></i>
                        <div>
                            <small class="text-muted">Timing</small><br>
                            <strong>
                                <?php
                                if (!empty($clinical['morning_opening_time'])) {
                                    echo date('h:i A', strtotime($clinical['morning_opening_time']));
                                    echo " - ";
                                    echo date('h:i A', strtotime($clinical['morning_closing_time']));
                                }

                                if (!empty($clinical['evening_opening_time'])) {
                                    echo "<br>";
                                    echo date('h:i A', strtotime($clinical['evening_opening_time']));
                                    echo " - ";
                                    echo date('h:i A', strtotime($clinical['evening_closing_time']));
                                }
                                ?>
                            </strong>
                        </div>
                    </div>

                    <div class="clinical-info-item">
                        <i class="fas fa-calendar-day text-success"></i>
                        <div>
                            <small class="text-muted">Working Days</small><br>
                            <strong><?php echo htmlspecialchars($clinical['days']); ?></strong>
                        </div>
                    </div>

                </div>

                <!-- Right Column -->
                <div class="col-md-6">

                    <div class="clinical-info-item">
                        <i class="fas fa-user-md text-info"></i>
                        <div>
                            <small class="text-muted">Shift</small><br>
                            <strong>
                                <?php
                                if (!empty($clinical['morning_opening_time'])) {
                                    echo 'Morning';
                                }

                                if (!empty($clinical['evening_opening_time'])) {
                                    echo '<br>Evening';
                                }
                                ?>
                            </strong>
                        </div>
                    </div>

                    <div class="clinical-info-item">
                        <i class="fas fa-phone text-warning"></i>
                        <div>
                            <small class="text-muted">Contact</small><br>
                            <strong><?php echo htmlspecialchars($clinical['contact']); ?></strong>
                        </div>
                    </div>

                </div>

            </div>

            <?php if (!empty($clinical['detail'])) { ?>
                <div class="clinical-detail mt-3">
                    <i class="fas fa-info-circle text-primary"></i>
                    <div>
                        <small class="text-muted">Detail</small><br>
                        <strong><?php echo htmlspecialchars($clinical['detail']); ?></strong>
                    </div>
                </div>
            <?php } ?>

        </div>

    </div>
</div>

                                <?php } ?>
                            </div>
                            
                    </div>

                    <!-- Patient Feedback Section -->
                    <div class="patient-feedback-section mt-5" data-aos="fade-up">
                        <div class="section-header text-center mb-4">
                            <h2 class="section-title-main">
                                <i class="fas fa-comments me-3"></i>Patient Feedback
                            </h2>
                            <p class="section-subtitle">Share your experience and read reviews from other patients</p>
                        </div>
                        
                        <!-- Feedback Form -->
                        <div class="feedback-form-container mb-5">
                            <div class="feedback-form-card">
                                <h4 class="form-title">
                                    <i class="fas fa-pen me-2"></i>Share Your Feedback
                                </h4>
                                <form class="feedback-form" method="POST">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="commenterName" class="form-label">
                                                    <i class="fas fa-user me-1"></i>Your Name
                                                </label>
                                                <input type="text" class="form-control" id="commenterName" 
                                                       name="commenter_name" required 
                                                       placeholder="Enter your full name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="commenterEmail" class="form-label">
                                                    <i class="fas fa-envelope me-1"></i>Email Address
                                                </label>
                                                <input type="email" class="form-control" id="commenterEmail" 
                                                       name="commenter_gmail" required 
                                                       placeholder="your.email@example.com">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="rating" class="form-label">
                                            <i class="fas fa-star me-1"></i>Rating
                                        </label>
                                        <div class="rating-container">
                                            <div class="star-rating" id="starRating">
                                                <i class="fas fa-star star" data-rating="1"></i>
                                                <i class="fas fa-star star" data-rating="2"></i>
                                                <i class="fas fa-star star" data-rating="3"></i>
                                                <i class="fas fa-star star" data-rating="4"></i>
                                                <i class="fas fa-star star" data-rating="5"></i>
                                            </div>
                                            <input type="hidden" id="rating" name="stars" value="5" required>
                                            <span class="rating-text">Excellent</span>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="comment" class="form-label">
                                            <i class="fas fa-comment me-1"></i>Your Feedback
                                        </label>
                                        <textarea class="form-control" id="comment" name="comment" 
                                                  rows="4" required 
                                                  placeholder="Share your experience with this doctor..."></textarea>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <input type="hidden" name="entity_id" value="<?php echo $doctor['entity_id']; ?>">
                                        <button type="submit" class="btn btn-primary btn-submit">
                                            <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                                        </button>
                                        <button type="reset" class="btn btn-outline-secondary btn-reset">
                                            <i class="fas fa-redo me-2"></i>Reset
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- All Feedbacks Display -->
                        <div class="feedbacks-display">
                            <h4 class="feedbacks-title">
                                <i class="fas fa-comments me-2"></i>Patient Reviews
                            </h4>
                            
                            <?php 
                            // Get all feedbacks for this doctor with pagination
                            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                            $per_page = 10;
                            $offset = ($page - 1) * $per_page;
                            
                            $feedback_query = "SELECT * FROM feedback WHERE entity_id = " . $doctor['entity_id'] . " 
                                           ORDER BY created_at DESC 
                                           LIMIT $offset, $per_page";
                            $feedback_result = mysqli_query($con, $feedback_query);
                            
                            // Get total feedback count for pagination
                            $count_query = "SELECT COUNT(*) as total FROM feedback WHERE entity_id = " . $doctor['entity_id'];
                            $count_result = mysqli_query($con, $count_query);
                            $total_feedbacks = mysqli_fetch_assoc($count_result)['total'];
                            $total_pages = ceil($total_feedbacks / $per_page);
                            
                            if(mysqli_num_rows($feedback_result) > 0): ?>
                                <div class="feedbacks-grid">
                                    <?php while($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                                        <div class="feedback-card">
                                            <div class="feedback-header">
                                                <div class="patient-info">
                                                    <div class="patient-avatar">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div class="patient-details">
                                                        <h5 class="patient-name">
                                                            <?php echo htmlspecialchars($feedback['commenter_name']); ?>
                                                        </h5>
                                                        <p class="patient-email">
                                                            <?php echo htmlspecialchars($feedback['commenter_gmail']); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="feedback-rating">
                                                    <div class="stars">
                                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star <?php echo $i <= $feedback['stars'] ? 'active' : ''; ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <span class="rating-number"><?php echo $feedback['stars']; ?>/5</span>
                                                </div>
                                            </div>
                                            
                                            <div class="feedback-content">
                                                <p class="feedback-comment">
                                                    <?php echo nl2br(htmlspecialchars($feedback['comment'])); ?>
                                                </p>
                                            </div>
                                            
                                            <div class="feedback-footer">
                                                <div class="feedback-date">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?php echo date('M d, Y', strtotime($feedback['created_at'])); ?>
                                                </div>
                                                <div class="feedback-actions">
                                                    <button class="btn btn-sm btn-outline-primary like-btn">
                                                        <i class="fas fa-thumbs-up me-1"></i>Helpful
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="no-feedbacks">
                                    <i class="fas fa-comment-slash"></i>
                                    <h5>No Reviews Yet</h5>
                                    <p>Be the first to share your experience with this doctor!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if($total_pages > 1): ?>
                            <div class="feedback-pagination mt-4">
                                <nav aria-label="Feedback pagination">
                                    <ul class="pagination justify-content-center">
                                        <?php
                                        // Previous page link
                                        if($page > 1):
                                            $prev_page = $page - 1;
                                            echo '<li class="page-item">
                                                <a class="page-link" href="?doctor_id=' . $doctor['doctor_id'] . '&page=' . $prev_page . '">
                                                    <i class="fas fa-chevron-left"></i>
                                                    Previous
                                                </a>
                                              </li>';
                                        endif;
                                        
                                        // Page numbers
                                        for($i = 1; $i <= $total_pages; $i++):
                                            $active_class = ($i == $page) ? 'active' : '';
                                            echo '<li class="page-item ' . $active_class . '">
                                                <a class="page-link" href="?doctor_id=' . $doctor['doctor_id'] . '&page=' . $i . '">' . $i . '</a>
                                              </li>';
                                        endfor;
                                        
                                        // Next page link
                                        if($page < $total_pages):
                                            $next_page = $page + 1;
                                            echo '<li class="page-item">
                                                <a class="page-link" href="?doctor_id=' . $doctor['doctor_id'] . '&page=' . $next_page . '">
                                                    Next
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                              </li>';
                                        endif;
                                        ?>
                                    </ul>
                                </nav>
                                
                                <div class="pagination-info text-center mt-3">
                                    <small class="text-muted">
                                        Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $per_page, $total_feedbacks); ?> 
                                        of <?php echo $total_feedbacks; ?> reviews
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            <?php else: ?>
                <div class="no-doctor-found" data-aos="fade-up">
                    <i class="fas fa-user-md"></i>
                    <h3>Doctor Not Found</h3>
                    <p class="text-muted">The doctor you're looking for doesn't exist or has been removed.</p>
                    <a href="doctors.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Doctors
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <?php include BASE_PATH.'/includes/footer.php';?>

    <script>
        function bookAppointment(doctorId) {
            // You can implement appointment booking functionality
            alert('Booking appointment for Doctor ID: ' + doctorId + ' - Feature coming soon!');
        }
        
        // Make updateStars function globally accessible
        function updateStars(rating) {
            console.log('Updating stars to rating:', rating);
            const stars = document.querySelectorAll('.star');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }
        
        // Star Rating Functionality
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Star rating initializing...');
            
            const stars = document.querySelectorAll('.star');
            const ratingInput = document.getElementById('rating');
            const ratingText = document.querySelector('.rating-text');
            const ratingTexts = ['Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
            
            console.log('Found stars:', stars.length);
            console.log('Rating input:', ratingInput);
            console.log('Rating text:', ratingText);
            
            // Initialize stars with default rating
            const initialRating = parseInt(ratingInput.value) || 5;
            console.log('Initial rating:', initialRating);
            updateStars(initialRating);
            ratingText.textContent = ratingTexts[initialRating - 1];
            
            // Add click event to each star
            stars.forEach((star, index) => {
                console.log('Adding events to star:', index + 1);
                
                star.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const rating = parseInt(this.dataset.rating);
                    console.log('Star clicked, rating:', rating);
                    
                    ratingInput.value = rating;
                    ratingText.textContent = ratingTexts[rating - 1];
                    updateStars(rating);
                    
                    // Add visual feedback
                    stars.forEach(s => s.style.transform = 'scale(1)');
                    this.style.transform = 'scale(1.3)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 200);
                    
                    console.log('Rating updated to:', ratingInput.value);
                });
                
                star.addEventListener('mouseenter', function() {
                    const rating = parseInt(this.dataset.rating);
                    updateStars(rating);
                });
                
                // Make stars explicitly clickable
                star.style.pointerEvents = 'auto';
                star.style.cursor = 'pointer';
            });
            
            // Reset to selected rating when mouse leaves the rating container
            const starRatingContainer = document.querySelector('.star-rating');
            if (starRatingContainer) {
                starRatingContainer.addEventListener('mouseleave', function() {
                    const currentRating = parseInt(ratingInput.value) || 5;
                    updateStars(currentRating);
                });
            }
        });
        
      
        
        // Notification Function
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Show notification
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Hide notification after 3 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
        
        // Like Button Functionality
        document.querySelectorAll('.like-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const feedbackId = this.closest('.feedback-card').dataset.feedbackId;
                
                if (this.classList.contains('liked')) {
                    this.classList.remove('liked');
                    this.innerHTML = '<i class="fas fa-thumbs-up me-1"></i>Helpful';
                } else {
                    this.classList.add('liked');
                    this.innerHTML = '<i class="fas fa-thumbs-up me-1"></i>Liked';
                }
                
                // Here you can send an AJAX request to update the database
                // For now, we'll just toggle the UI state
            });
        });
    </script>
    
    <style>
        /* Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            z-index: 9999;
            transform: translateX(400px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            max-width: 350px;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification-success {
            border-left: 4px solid #28a745;
        }
        
        .notification-error {
            border-left: 4px solid #dc3545;
        }
        
        .notification-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .notification-success i {
            color: #28a745;
        }
        
        .notification-error i {
            color: #dc3545;
        }
        
        .notification span {
            color: #495057;
            font-weight: 500;
        }
        
        /* Like Button Styles */
        .like-btn.liked {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .like-btn.liked:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        
        /* Feedback Pagination Styles */
        .feedback-pagination {
            background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,249,250,0.95) 100%);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid rgba(102, 126, 234, 0.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .pagination .page-item {
            display: inline-block;
        }
        
        .pagination .page-link {
            display: inline-block;
            padding: 10px 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
        }
        
        .pagination .page-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        
        .pagination .page-link i {
            margin-right: 5px;
            font-size: 0.8rem;
        }
        
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transform: scale(1.05);
        }
        
        .pagination-info {
            text-align: center;
            margin-top: 15px;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .pagination-info small {
            color: #495057;
            font-weight: 500;
        }
        
        /* Mobile Responsive for Pagination */
        @media (max-width: 768px) {
            .feedback-pagination {
                padding: 15px;
            }
            
            .pagination {
                flex-wrap: wrap;
                gap: 8px;
            }
            
            .pagination .page-link {
                padding: 8px 12px;
                font-size: 0.8rem;
            }
            
            .pagination .page-link i {
                font-size: 0.7rem;
                margin-right: 3px;
            }
        }
    </style>

    <style>
        .doctor-detail-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 40px;
        }

        .doctor-profile-section {
            padding: 40px;
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .doctor-image-wrapper {
            margin-bottom: 30px;
        }

        .doctor-profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.3);
            object-fit: cover;
        }

        .doctor-avatar-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            border: 4px solid rgba(255, 255, 255, 0.3);
        }

        .doctor-basic-info {
            color: white;
        }

        .doctor-name {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .doctor-specialization {
            font-size: 1.1rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .doctor-experience {
            font-size: 0.95rem;
            margin: 0;
        }

        .doctor-rating {
            font-size: 0.95rem;
            margin: 15px 0 0 0;
            padding: 15px;
            background: linear-gradient(135deg, rgba(255,215,0,0.1) 0%, rgba(255,193,7,0.1) 100%);
            border-radius: 12px;
            border-left: 3px solid #ffc107;
        }
        
        .doctor-rating i {
            color: #ffc107;
            margin-right: 8px;
        }
        
        .rating-score {
            font-weight: 700;
            font-size: 1.1rem;
            color: #212529;
        }
        
        .rating-percentage {
            font-weight: 600;
            color: #28a745;
            margin-left: 5px;
        }
        
        .total-reviews {
            display: block;
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
            font-style: italic;
        }

        .doctor-details-section {
            padding: 40px;
        }

        .detail-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .contact-item:hover {
            transform: translateY(-2px);
        }

        .contact-item i {
            font-size: 1.5rem;
            color: var(--primary);
            margin-right: 15px;
            width: 30px;
        }

        .contact-item label {
            display: block;
            font-weight: 600;
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 5px;
        }

        .contact-item span {
            color: #212529;
            font-weight: 500;
        }

        .professional-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row label {
            font-weight: 600;
            color: #6c757d;
        }

        .info-row span {
            color: #212529;
            font-weight: 500;
        }

        .about-content {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .no-doctor-found {
            text-align: center;
            padding: 80px 20px;
            color: var(--dark);
        }

        .no-doctor-found i {
            font-size: 5rem;
            color: var(--primary);
            margin-bottom: 30px;
        }

        .no-doctor-found h3 {
            font-size: 2rem;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .doctor-detail-card .row {
                flex-direction: column;
            }
            
            .doctor-profile-section {
                padding: 30px 20px;
            }
            
            .doctor-details-section {
                padding: 30px 20px;
            }
            
            .contact-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons .btn {
                width: 100%;
            }
        }
    </style>
</body>
</html>