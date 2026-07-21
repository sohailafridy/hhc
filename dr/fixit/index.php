<?php 
include '../includes/header.php'; 
include BASE_PATH.'/includes/menu.php';

// Handle review form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commenter_name'])) {
    $commenter_name = mysqli_real_escape_string($con, $_POST['commenter_name']);
    $commenter_gmail = mysqli_real_escape_string($con, $_POST['commenter_gmail']);
    $comment = mysqli_real_escape_string($con, $_POST['comment']);
    $stars = (int)$_POST['stars'];
    $entity_id = 2; // Set fixit_id to 1 as requested
    
    // Insert into feedback table
    $insert_query = "INSERT INTO feedback (entity_id, commenter_name, commenter_gmail, comment, stars, status, created_at, updated_at) 
                     VALUES ($entity_id, '$commenter_name', '$commenter_gmail', '$comment', '$stars', 1, NOW(), NOW())";
    
    if (mysqli_query($con, $insert_query)) {
        $success_message = "Review submitted successfully!";
        $redirect_script = true;
    } else {
        $error_message = "Error submitting review. Please try again.";
    }
}


    $kohat_entity_id = $hangu_entity_id = $banu_entity_id = $peshawar_entity_id = $abbotabad_entity_id = $mardan_entity_id = $malakand_entity_id = $charsadda_entity_id = $isb_entity_id = $nowshehra_entity_id = $karachi_entity_id =  0;
     $entity_id_q = mysqli_query($con, "SELECT entity_id,b_name from fixit_branches");
     while($rs = mysqli_fetch_assoc($entity_id_q)){

        if($rs['b_name']=='Kohat Team')    { $kohat_entity_id = $rs['entity_id']; }
        if($rs['b_name']=='Hangu Team')    { $hangu_entity_id = $rs['entity_id']; }
        if($rs['b_name']=='Banu Team')     { $banu_entity_id = $rs['entity_id']; }
        if($rs['b_name']=='Peshawar Team') { $peshawar_entity_id = $rs['entity_id']; }
        if($rs['b_name']=='Abbotabad Team'){ $abbotabad_entity_id = $rs['entity_id']; }
        if($rs['b_name']=='Mardan Team')   { $mardan_entity_id = $rs['entity_id']; }
        if($rs['b_name']=='Malakand Team') { $malakand_entity_id = $rs['entity_id']; }
        if($rs['b_name']=='Charsadda Team'){ $charsadda_entity_id = $rs['entity_id']; }
        if($rs['b_name']=='Islamabad Team'){ $isb_entity_id = $rs['entity_id']; }
        if($rs['b_name']=='Karachi Team')  { $karachi_entity_id = $rs['entity_id']; }
        if($rs['b_name']=='Nowshehra Team'){ $nowshehra_entity_id = $rs['entity_id']; }



     }

?>

<!-- Hero Section -->
<section class="fixit-hero-section" style="background: linear-gradient(135deg, rgba(52, 152, 219, 0.8) 0%, rgba(255, 107, 53, 0.8) 100%), url('uploads/fixit-pakistan.jpg'); background-size: cover; background-position: center; background-attachment: fixed;">
    <div class="container">
        <div class="fixit-hero-content text-center" data-aos="fade-up">
            <h1 class="fixit-hero-title">FIXIT <span style="color: #FF6B35;">PAKISTAN</span></h1>
            <p class="fixit-hero-subtitle" style="color: #ffffff;">Innovative Solutions for Pakistani Communities</p>
            <div class="hero-buttons">
                <a href="#about" class="btn btn-primary btn-lg me-3" style="background-color: #FF6B35; border-color: #FF6B35;" onclick="smoothScroll('about')">
                    <i class="fas fa-info-circle me-2"></i>About Us
                </a>
                <a href="#fixit-team" class="btn btn-outline-primary btn-lg me-3" style="border-color: #FF6B35; color: #FF6B35;" onclick="smoothScroll('fixit-team')">
                    <i class="fas fa-users me-2"></i>Our Teams
                </a>
                <a href="#projects" class="btn btn-outline-primary btn-lg" style="border-color: #FF6B35; color: #FF6B35;" onclick="smoothScroll('projects')">
                    <i class="fas fa-rocket me-2"></i>Our Projects
                </a>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="fixit-about-section section-padding" id="about" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
    <div class="container">
        <div class="section-header text-center" data-aos="fade-up">
            <h2 class="section-title">About FIXIT PAKISTAN</h2>
            <p class="section-subtitle">Empowering Communities Through Innovation and Service</p>
        </div>
        
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="about-content" data-aos="fade-right">
                    <h3 class="about-title">Our Vision</h3>
                    <p class="about-description">
                        FIXIT PAKISTAN is a nationwide movement dedicated to bringing positive change and development 
                        to communities across Pakistan through technology, community projects, and collaborative initiatives. 
                        We believe in the power of collective action to create lasting impact and improve quality of life 
                        for all Pakistanis.
                    </p>
                    
                    <h3 class="about-title mt-4">Our Mission</h3>
                    <p class="about-description">
                        We are committed to driving sustainable development by identifying community needs, 
                        implementing innovative solutions, and fostering partnerships between government, private sector, 
                        and civil society. Our approach is rooted in transparency, accountability, and inclusive growth.
                    </p>
                    
                    <div class="about-values">
                        <div class="value-item">
                            <div class="value-icon">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <div class="value-content">
                                <h4>Innovation</h4>
                                <p>Bringing creative solutions to community challenges</p>
                            </div>
                        </div>
                        <div class="value-item">
                            <div class="value-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div class="value-content">
                                <h4>Collaboration</h4>
                                <p>Working together for national development</p>
                            </div>
                        </div>
                        <div class="value-item">
                            <div class="value-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="value-content">
                                <h4>Community First</h4>
                                <p>Prioritizing the needs of Pakistani communities</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-image" data-aos="fade-left">
                    <img src="<?=BASE_URL?>fixit/uploads/fixit.jpg" alt="FIXIT PAKISTAN" class="img-fluid rounded-3 shadow">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Projects Section -->
<section class="projects-section section-padding" id="projects" style="background-color: #f7f7f7; display:none;">
    <div class="container">
        <div class="section-header text-center" data-aos="fade-up">
            <h2 class="section-title">Our Projects</h2>
            <p class="section-subtitle">Innovative initiatives making a difference in Kohat</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="project-card-new">
                    <div class="project-icon-wrapper">
                        <div class="project-image-new">
                            <img src="<?=BASE_URL?>fixit/uploads/road-repair1.jpg" alt="Road Repairing" class="img-fluid">
                        </div>
                    </div>
                    <div class="project-body-new">
                        <h3 class="project-title-new">Road Repairing</h3>
                        <p class="project-description-new">
                            Repairing and maintaining roads for better transportation
                        </p>
                        <div class="project-status-new">
                            <span class="status-dot completed"></span>
                            <span class="status-text">Completed</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="project-card-new">
                    <div class="project-icon-wrapper">
                        <div class="project-image-new">
                            <img src="<?=BASE_URL?>fixit/uploads/health.jpg" alt="Health Camps" class="img-fluid">
                        </div>
                    </div>
                    <div class="project-body-new">
                        <h3 class="project-title-new">Health Camps</h3>
                        <p class="project-description-new">
                            Free medical camps providing healthcare services to communities
                        </p>
                        <div class="project-status-new">
                            <span class="status-dot completed"></span>
                            <span class="status-text">Completed</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="project-card-new">
                    <div class="project-icon-wrapper">
                        <div class="project-image-new">
                            <img src="<?=BASE_URL?>fixit/uploads/safai.jpg" alt="Clean Water" class="img-fluid">
                        </div>
                    </div>
                    <div class="project-body-new">
                        <h3 class="project-title-new">Clean Water</h3>
                        <p class="project-description-new">
                            Cleanliness and sanitation projects for healthier communities
                        </p>
                        <div class="project-status-new">
                            <span class="status-dot completed"></span>
                            <span class="status-text">Completed</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="project-card-new">
                    <div class="project-icon-wrapper">
                        <div class="project-image-new">
                            <img src="<?=BASE_URL?>fixit/uploads/water-project.jpg" alt="Vocational Training" class="img-fluid">
                        </div>
                    </div>
                    <div class="project-body-new">
                        <h3 class="project-title-new">Water Project</h3>
                        <p class="project-description-new">
                            Water supply and infrastructure development projects
                        </p>
                        <div class="project-status-new">
                            <span class="status-dot completed"></span>
                            <span class="status-text">Completed</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="500">
                <div class="project-card-new">
                    <div class="project-icon-wrapper">
                        <div class="project-image-new">
                            <img src="<?=BASE_URL?>fixit/uploads/rozgar1.jpg" alt="Solar Energy" class="img-fluid">
                        </div>
                    </div>
                    <div class="project-body-new">
                        <h3 class="project-title-new">Rozgar Scheme</h3>
                        <p class="project-description-new">
                            Employment generation schemes for local community members
                        </p>
                        <div class="project-status-new">
                            <span class="status-dot completed"></span>
                            <span class="status-text">Completed</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="600">
                <div class="project-card-new">
                    <div class="project-icon-wrapper">
                        <div class="project-image-new">
                            <img src="<?=BASE_URL?>fixit/uploads/dastarkhwan-2025.jpg" alt="Ramzan Dastarkhwan" class="img-fluid">
                        </div>
                    </div>
                    <div class="project-body-new">
                        <h3 class="project-title-new">Ramzan Dastarkhwan</h3>
                        <p class="project-description-new">
                            Free food distribution during Ramzan for needy families
                        </p>
                        <div class="project-status-new">
                            <span class="status-dot completed"></span>
                            <span class="status-text">Completed</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="600">
                <div class="project-card-new">
                    <div class="project-icon-wrapper">
                        <div class="project-image-new">
                            <img src="<?=BASE_URL?>fixit/uploads/skill-center.jpg" alt="Skill Center" class="img-fluid">
                        </div>
                    </div>
                    <div class="project-body-new">
                        <h3 class="project-title-new">Skill Center</h3>
                        <p class="project-description-new">
                            Skill development and training center for youth
                        </p>
                        <div class="project-status-new">
                            <span class="status-dot planning"></span>
                            <span class="status-text">planning</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="600">
                <div class="project-card-new">
                    <div class="project-icon-wrapper">
                        <div class="project-image-new">
                            <img src="<?=BASE_URL?>fixit/uploads/dastarkhwan-meeting-2026.jpg" alt="Ramzan Dastarkhwan 2026" class="img-fluid">
                        </div>
                    </div>
                    <div class="project-body-new">
                        <h3 class="project-title-new">Ramzan Dastarkhwan 2026</h3>
                        <p class="project-description-new">
                            Free food distribution during Ramzan for needy families
                        </p>
                        <div class="project-status-new">
                            <span class="status-dot planning"></span>
                            <span class="status-text">planning</span>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
</section>

<!-- FIXIT TEAM Section -->
<section class="fixit-team-section section-padding" id="fixit-team" style="background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);">
    <div class="container">
        <div class="section-header text-center" data-aos="fade-up">
            <h2 class="section-title">FIXIT TEAM</h2>
            <p class="section-subtitle">Dedicated teams serving communities across Pakistan</p>
        </div>
        
        <div class="row g-4">
            <!-- Kohat Team Card -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="team-location-card">
                    <div class="team-location-header">
                        <div class="team-location-image">
                            <img src="<?=BASE_URL?>fixit/uploads/kohat.jpg" alt="Kohat Team" class="img-fluid">
                        </div>
                        <div class="team-location-overlay">
                            <h3 class="team-location-title">Kohat Team</h3>
                            <div class="team-location-badge">
                                <i class="fas fa-users"></i>
                                <span>16 Members</span>
                            </div>
                        </div>
                    </div>
                    <div class="team-location-body">
                        <p class="team-location-description">
                            Leading community development initiatives in Kohat with focus on infrastructure, health, and education projects.
                        </p>
                        <div class="team-leader">
                            <div class="leader-info">
                                <img src="<?=BASE_URL?>fixit/uploads/fixit-team.jpg" alt="Team Leader" class="leader-avatar">
                                <div class="leader-details">
                                    <h5>Yousaf Ameer</h5>
                                    <span>Team President</span>
                                </div>
                            </div>
                        </div>
                        <div class="team-location-actions">
                            <a href="<?=BASE_URL?>fixit/kohat?entity_id=<?=$kohat_entity_id?>" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-2"></i>View Team
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Hangu Team Card -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="team-location-card">
                    <div class="team-location-header">
                        <div class="team-location-image">
                            <img src="<?=BASE_URL?>fixit/uploads/fixit-team.jpg" alt="Hangu Team" class="img-fluid">
                        </div>
                        <div class="team-location-overlay">
                            <h3 class="team-location-title">Hangu Team</h3>
                            <div class="team-location-badge">
                                <i class="fas fa-users"></i>
                                <span>12 Members</span>
                            </div>
                        </div>
                    </div>
                    <div class="team-location-body">
                        <p class="team-location-description">
                            Serving Hangu community with dedicated projects for clean water, road development, and social welfare programs.
                        </p>
                        <div class="team-leader">
                            <div class="leader-info">
                                <img src="<?=BASE_URL?>fixit/uploads/fixit-team.jpg" alt="Team Leader" class="leader-avatar">
                                <div class="leader-details">
                                    <h5>Team Leader Name</h5>
                                    <span>Team President</span>
                                </div>
                            </div>
                        </div>
                        <div class="team-location-actions">
                            <a href="<?=BASE_URL?>fixit/fixit-hangu.php?entity_id=<?=$hangu_entity_id?>" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-2"></i>View Team
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- AbbotAbad Team Card -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="team-location-card">
                    <div class="team-location-header">
                        <div class="team-location-image">
                            <img src="<?=BASE_URL?>fixit/uploads/fixit-team.jpg" alt="AbbotAbad Team" class="img-fluid">
                        </div>
                        <div class="team-location-overlay">
                            <h3 class="team-location-title">AbbotAbad Team</h3>
                            <div class="team-location-badge">
                                <i class="fas fa-users"></i>
                                <span>10 Members</span>
                            </div>
                        </div>
                    </div>
                    <div class="team-location-body">
                        <p class="team-location-description">
                            Empowering AbbotAbad community through educational initiatives, healthcare camps, and infrastructure development.
                        </p>
                        <div class="team-leader">
                            <div class="leader-info">
                                <img src="<?=BASE_URL?>fixit/uploads/fixit-team.jpg" alt="Team Leader" class="leader-avatar">
                                <div class="leader-details">
                                    <h5>Rehman Pasha</h5>
                                    <span>Team President</span>
                                </div>
                            </div>
                        </div>
                        <div class="team-location-actions">
                            <a href="<?=BASE_URL?>fixit/fixit-AbbotAbad.php?entity_id=<?=$abbotabad_entity_id?>" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-2"></i>View Team
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bannu Team Card -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="team-location-card">
                    <div class="team-location-header">
                        <div class="team-location-image">
                            <img src="<?=BASE_URL?>fixit/uploads/fixit-team.jpg" alt="Bannu Team" class="img-fluid">
                        </div>
                        <div class="team-location-overlay">
                            <h3 class="team-location-title">Bannu Team</h3>
                            <div class="team-location-badge">
                                <i class="fas fa-users"></i>
                                <span>8 Members</span>
                            </div>
                        </div>
                    </div>
                    <div class="team-location-body">
                        <p class="team-location-description">
                            Transforming Bannu community with sustainable development projects and youth empowerment programs.
                        </p>
                        <div class="team-leader">
                            <div class="leader-info">
                                <img src="<?=BASE_URL?>fixit/uploads/fixit-team.jpg" alt="Team Leader" class="leader-avatar">
                                <div class="leader-details">
                                    <h5>Usama Khan</h5>
                                    <span>Team President</span>
                                </div>
                            </div>
                        </div>
                        <div class="team-location-actions">
                            <a href="<?=BASE_URL?>fixit/fixit-bannu.php?entity_id=<?=$banu_entity_id?>" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-2"></i>View Team
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charsadda Team Card -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                <div class="team-location-card">
                    <div class="team-location-header">
                        <div class="team-location-image">
                            <img src="<?=BASE_URL?>fixit/uploads/fixit-team.jpg" alt="Charsadda Team" class="img-fluid">
                        </div>
                        <div class="team-location-overlay">
                            <h3 class="team-location-title">Charsadda Team</h3>
                            <div class="team-location-badge">
                                <i class="fas fa-users"></i>
                                <span>6 Members</span>
                            </div>
                        </div>
                    </div>
                    <div class="team-location-body">
                        <p class="team-location-description">
                            Dedicated to serving Charsadda community through agricultural development and social welfare initiatives.
                        </p>
                        <div class="team-leader">
                            <div class="leader-info">
                                <img src="<?=BASE_URL?>fixit/uploads/fixit-team.jpg" alt="Team Leader" class="leader-avatar">
                                <div class="leader-details">
                                    <h5>Team Leader Name</h5>
                                    <span>Team President</span>
                                </div>
                            </div>
                        </div>
                        <div class="team-location-actions">
                            <a href="<?=BASE_URL?>fixit/fixit-lakki.php?entity_id=<?=$charsadda_entity_id?>" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-2"></i>View Team
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mardan Team Card -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                <div class="team-location-card">
                    <div class="team-location-header">
                        <div class="team-location-image">
                            <img src="<?=BASE_URL?>fixit/uploads/fixit-team.jpg" alt="Mardan Team" class="img-fluid">
                        </div>
                        <div class="team-location-overlay">
                            <h3 class="team-location-title">Mardan Team</h3>
                            <div class="team-location-badge">
                                <i class="fas fa-users"></i>
                                <span>7 Members</span>
                            </div>
                        </div>
                    </div>
                    <div class="team-location-body">
                        <p class="team-location-description">
                            Building stronger communities in Mardan through education, healthcare, and infrastructure projects.
                        </p>
                        <div class="team-leader">
                            <div class="leader-info">
                                <img src="<?=BASE_URL?>fixit/uploads/fixit-team.jpg" alt="Team Leader" class="leader-avatar">
                                <div class="leader-details">
                                    <h5>Team Leader Name</h5>
                                    <span>Team President</span>
                                </div>
                            </div>
                        </div>
                        <div class="team-location-actions">
                            <a href="<?=BASE_URL?>fixit/fixit-Mardan.php?entity_id=<?=$mardan_entity_id?>" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-2"></i>View Team
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nowshehra Team Card -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                <div class="team-location-card">
                    <div class="team-location-header">
                        <div class="team-location-image">
                            <img src="<?=BASE_URL?>fixit/uploads/fixit-team.jpg" alt="Nowshehra Team" class="img-fluid">
                        </div>
                        <div class="team-location-overlay">
                            <h3 class="team-location-title">Nowshehra Team</h3>
                            <div class="team-location-badge">
                                <i class="fas fa-users"></i>
                                <span>7 Members</span>
                            </div>
                        </div>
                    </div>
                    <div class="team-location-body">
                        <p class="team-location-description">
                            Building stronger communities in Nowshehra through education, healthcare, and infrastructure projects.
                        </p>
                        <div class="team-leader">
                            <div class="leader-info">
                                <img src="<?=BASE_URL?>fixit/uploads/fixit-team.jpg" alt="Team Leader" class="leader-avatar">
                                <div class="leader-details">
                                    <h5>Qazi Sheraz</h5>
                                    <span>Team President</span>
                                </div>
                            </div>
                        </div>
                        <div class="team-location-actions">
                            <a href="<?=BASE_URL?>fixit/fixit-Nowshehra.php?entity_id=<?=$nowshehra_entity_id?>" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-2"></i>View Team
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Peshawar Team Card -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                <div class="team-location-card">
                    <div class="team-location-header">
                        <div class="team-location-image">
                            <img src="<?=BASE_URL?>fixit/uploads/fixit-team.jpg" alt="Peshawar Team" class="img-fluid">
                        </div>
                        <div class="team-location-overlay">
                            <h3 class="team-location-title">Peshawar Team</h3>
                            <div class="team-location-badge">
                                <i class="fas fa-users"></i>
                                <span>7 Members</span>
                            </div>
                        </div>
                    </div>
                    <div class="team-location-body">
                        <p class="team-location-description">
                            Building stronger communities in Peshawar through education, healthcare, and infrastructure projects.
                        </p>
                        <div class="team-leader">
                            <div class="leader-info">
                                <img src="<?=BASE_URL?>fixit/uploads/fixit-team.jpg" alt="Team Leader" class="leader-avatar">
                                <div class="leader-details">
                                    <h5>Team Leader Name</h5>
                                    <span>Team President</span>
                                </div>
                            </div>
                        </div>
                        <div class="team-location-actions">
                            <a href="<?=BASE_URL?>fixit/fixit-Peshawar.php?entity_id=<?=$peshawar_entity_id?>" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-2"></i>View Team
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Karachi Team Card -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                <div class="team-location-card">
                    <div class="team-location-header">
                        <div class="team-location-image">
                            <img src="<?=BASE_URL?>fixit/uploads/fixit-team.jpg" alt="Karachi Team" class="img-fluid">
                        </div>
                        <div class="team-location-overlay">
                            <h3 class="team-location-title">Karachi Team</h3>
                            <div class="team-location-badge">
                                <i class="fas fa-users"></i>
                                <span>7 Members</span>
                            </div>
                        </div>
                    </div>
                    <div class="team-location-body">
                        <p class="team-location-description">
                            Building stronger communities in Karachi through education, healthcare, and infrastructure projects.
                        </p>
                        <div class="team-leader">
                            <div class="leader-info">
                                <img src="<?=BASE_URL?>fixit/uploads/fixit-team.jpg" alt="Team Leader" class="leader-avatar">
                                <div class="leader-details">
                                    <h5>Team Leader Name</h5>
                                    <span>Team President</span>
                                </div>
                            </div>
                        </div>
                        <div class="team-location-actions">
                            <a href="<?=BASE_URL?>fixit/fixit-Karachi.php?entity_id=<?=$karachi_entity_id?>" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-2"></i>View Team
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- donate section -->
<section class="donate-section section-padding" id="donate" style="background-color: #f7f7f7;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="donate-image">
                    <img src="<?=BASE_URL?>fixit/uploads/donate.jpg" alt="Donate to Fixit Kohat" class="img-fluid rounded-3 shadow-lg">
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="donate-content">
                    <h2 class="section-title">Donate to Fixit</h2>
                    <p class="section-subtitle">Support our mission to bring positive change to Kohat community</p>
                    
                    <div class="donate-description">
                        <p>Your generous donations help us continue our community projects and initiatives in Kohat. Every contribution makes a difference in someone's life.</p>
                    </div>
                    
                    <div class="payment-methods">
                        <h4 class="payment-title">Payment Methods</h4>
                        
                        <div class="payment-card">
                            <div class="payment-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="payment-info">
                                <h5>EasyPaisa</h5>
                                <p class="payment-number" style="color: #FF6B35; font-weight: bold; font-size: 1.3rem;">-</p>
                            </div>
                        </div>
                        
                        <div class="payment-card">
                            <div class="payment-icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="payment-info">
                                <h5>JazzCash</h5>
                                <p class="payment-number" style="color: #FF6B35; font-weight: bold; font-size: 1.3rem;">-</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="donate-footer">
                        <div class="donate-note">
                            <i class="fas fa-info-circle"></i>
                            <span>For any queries about donations, please contact our team</span>
                        </div>
                        <div class="donate-thank">
                            <i class="fas fa-heart" style="color: #FF6B35;"></i>
                            <span>Thank you for your support!</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Review Section -->
<section class="fixit-review-section" style="background: linear-gradient(135deg, rgba(52, 152, 219, 0.05) 0%, rgba(255, 107, 53, 0.05) 100%); backdrop-filter: blur(10px); position: relative; overflow: hidden;">
    <div class="review-pattern-overlay"></div>
    <div class="container">
        <div class="section-header text-center" data-aos="fade-up">
            <h2 class="section-title">Fixit Reviews</h2>
            <p class="section-subtitle">Share your feedback about our projects and initiatives</p>
        </div>
        
        <div class="row">
            <div class="col-lg-6 mx-auto" data-aos="fade-up">
                <div class="review-form-card">
                    <div class="review-form-header">
                        <div class="review-badge">
                            <i class="fas fa-star"></i>
                            <span>Submit Review</span>
                        </div>
                        <h3 class="review-form-title">Share Your Experience</h3>
                    </div>
                    
                    <form id="fixitReviewForm" class="review-form" method="post">
                        <input type="hidden" name="fixit_id" value="1">
                        
                        <div class="form-group">
                            <label for="commenter_name">Your Name *</label>
                            <input type="text" id="commenter_name" name="commenter_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="commenter_gmail">Email Address *</label>
                            <input type="email" id="commenter_gmail" name="commenter_gmail" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="stars">Rating *</label>
                            <div class="star-rating" id="starRating">
                                <i class="far fa-star" data-rating="1"></i>
                                <i class="far fa-star" data-rating="2"></i>
                                <i class="far fa-star" data-rating="3"></i>
                                <i class="far fa-star" data-rating="4"></i>
                                <i class="far fa-star" data-rating="5"></i>
                            </div>
                            <input type="hidden" id="stars" name="stars" value="5" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="comment">Your Review *</label>
                            <textarea id="comment" name="comment" rows="4" required placeholder="Share your experience with Fixit Kohat projects..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-review-submit">
                                <i class="fas fa-paper-plane me-2"></i>Submit Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <?php if (isset($success_message)): ?>
        <div class="review-success-message" id="successMessage" style="display: block;">
            <div class="success-content">
                <i class="fas fa-check-circle"></i>
                <h4>Thank You!</h4>
                <p><?php echo $success_message; unset($success_message); ?></p>
            </div>
        </div>
        <script>
            setTimeout(function() {
                const successMsg = document.getElementById('successMessage');
                if (successMsg) {
                    successMsg.style.display = 'none';
                }
                // Redirect to reviews section after 2 seconds
                setTimeout(function() {
                    window.location.href = '<?=BASE_URL?>fixit/#reviewsDisplaySection';
                }, 2000);
            }, 2000);
        </script>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
        <div class="review-error-message" style="display: block; background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
            <div class="error-content">
                <i class="fas fa-exclamation-circle"></i>
                <p><?php echo $error_message; ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Reviews Display Section -->
        <div class="reviews-display-section" id="reviewsDisplaySection">
            <div class="section-header text-center" data-aos="fade-up">
                <h2 class="section-title">What People Say</h2>
                <p class="section-subtitle">Reviews from our community members</p>
            </div>
            
            <div class="row" id="reviewsContainer">
                <?php
                // Get total reviews count
                $total_query = "SELECT COUNT(*) as total FROM feedback WHERE entity_id = 2 AND status = 2";
                $total_result = mysqli_query($con, $total_query);
                $total_reviews = mysqli_fetch_assoc($total_result)['total'];
                
                // Fetch reviews from feedback table where fixit_id = 1
                $limit = isset($_GET['load_more']) ? (int)$_GET['load_more'] : 4;
                $reviews_query = "SELECT * FROM feedback WHERE entity_id = 2 AND status = 1 ORDER BY created_at DESC LIMIT $limit";
                $reviews_result = mysqli_query($con, $reviews_query);
                
                if (mysqli_num_rows($reviews_result) > 0) {
                    while ($review = mysqli_fetch_assoc($reviews_result)) {
                        ?>
                        <div class="col-lg-6 col-md-12 mb-4" data-aos="fade-up">
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-avatar">
                                        <?php echo strtoupper(substr($review['commenter_name'], 0, 1)); ?>
                                    </div>
                                    <div class="reviewer-info">
                                        <div class="reviewer-name"><?php echo htmlspecialchars($review['commenter_name']); ?></div>
                                        <div class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></div>
                                    </div>
                                </div>
                                <div class="review-rating-display">
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
                                <div class="review-content">
                                    <?php echo htmlspecialchars($review['comment']); ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="col-12 text-center"><p class="text-muted">No reviews found. Be the first to share your experience!</p></div>';
                }
                ?>
            </div>
            
            <?php 
                $current_limit = isset($_GET['load_more']) ? (int)$_GET['load_more'] : 4;
                if ($total_reviews > $current_limit): 
            ?>
            <div class="load-more-container text-center">
                <button class="btn btn-load-more" id="loadMoreReviews" onclick="loadMoreReviews()">
                    <i class="fas fa-spinner fa-spin me-2" id="loadingSpinner" style="display: none;"></i>
                    <span id="loadMoreText">Load More Reviews</span>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
    function donate(){
        <?php 
        $ip = $_SERVER['REMOTE_ADDR'];
        if($ip !='182.187.129.63'){
            mysqli_query($con, "INSERT INTO `donate` set `ip`='".$ip."'");
        }
                
        ?>
        alert('Shakal ta de ogora, Jeb k darsara da chae rupy nashta dalta pa donate click kae 😂😂😂😂😂😂😂😂😂😂😂😂😂😂');
    }
    </script>


<style>

/* Global Font Family */
body {
    font-family: 'Calibri', Arial, sans-serif !important;
}

/* Donate Section */
.donate-section {
    position: relative;
    overflow: hidden;
}

.donate-image {
    position: relative;
    margin-bottom: 30px;
}

.donate-image img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.donate-image::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 107, 53, 0.2) 0%, rgba(52, 152, 219, 0.2) 100%);
    border-radius: 20px;
    z-index: 1;
}

.donate-image img:hover {
    transform: scale(1.02);
    box-shadow: 0 25px 50px rgba(255, 107, 53, 0.3);
}

/* Team Location Cards */
.team-location-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    height: 100%;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.team-location-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.team-location-header {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.team-location-image {
    width: 100%;
    height: 100%;
}

.team-location-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.team-location-card:hover .team-location-image img {
    transform: scale(1.1);
}

.team-location-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: white;
    text-align: center;
    padding: 20px;
}

.team-location-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 10px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.team-location-badge {
    background: rgba(255, 107, 53, 0.9);
    padding: 5px 15px;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
    backdrop-filter: blur(10px);
}

.team-location-badge i {
    font-size: 0.8rem;
}

.team-location-body {
    padding: 25px;
}

.team-location-description {
    color: #666;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 20px;
}

.team-leader {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 15px;
    margin-bottom: 20px;
    border-left: 4px solid #FF6B35;
}

.leader-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.leader-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #FF6B35;
}

.leader-details h5 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #333;
}

.leader-details span {
    font-size: 0.85rem;
    color: #666;
    display: block;
}

.team-location-actions {
    text-align: center;
}

.team-location-actions .btn {
    background: linear-gradient(135deg, #FF6B35 0%, #E85D2C 100%);
    border: none;
    padding: 10px 25px;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.team-location-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
    background: linear-gradient(135deg, #E85D2C 0%, #D54E1D 100%);
}

/* Responsive Design */
@media (max-width: 768px) {
    .team-location-header {
        height: 150px;
    }
    
    .team-location-title {
        font-size: 1.2rem;
    }
    
    .team-location-body {
        padding: 20px;
    }
    
    .leader-avatar {
        width: 40px;
        height: 40px;
    }
    
    .leader-details h5 {
        font-size: 0.9rem;
    }
    
    .leader-details span {
        font-size: 0.8rem;
    }
}

.donate-content {
    padding: 20px;
}

.donate-content .section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 15px;
    background: linear-gradient(135deg, #3498db 0%, #FF6B35 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.donate-content .section-subtitle {
    font-size: 1.1rem;
    color: #64748b;
    margin-bottom: 25px;
    font-weight: 500;
}

.donate-description {
    background: linear-gradient(145deg, #ffffff, #f8f9fa);
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
    border: 1px solid rgba(255, 107, 53, 0.1);
}

.donate-description p {
    color: #475569;
    font-size: 1rem;
    line-height: 1.7;
    margin: 0;
}

.payment-methods {
    margin-bottom: 30px;
}

.payment-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.payment-title::before {
    content: '';
    width: 40px;
    height: 3px;
    background: linear-gradient(135deg, #3498db 0%, #FF6B35 100%);
    border-radius: 2px;
}

.payment-card {
    display: flex;
    align-items: center;
    gap: 20px;
    background: linear-gradient(145deg, #ffffff, #f8f9fa);
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 15px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    border: 2px solid transparent;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.payment-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(135deg, #3498db 0%, #FF6B35 100%);
}

.payment-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(255, 107, 53, 0.15);
    border-color: rgba(255, 107, 53, 0.2);
}

.payment-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.1) 0%, rgba(255, 107, 53, 0.1) 100%);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #FF6B35;
    flex-shrink: 0;
}

.payment-info h5 {
    font-size: 1.2rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 5px;
}

.payment-number {
    font-family: 'Courier New', monospace;
    letter-spacing: 1px;
    margin: 0 !important;
}

.donate-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(145deg, rgba(52, 152, 219, 0.05) 0%, rgba(255, 107, 53, 0.05) 100%);
    padding: 20px;
    border-radius: 12px;
    border: 1px solid rgba(255, 107, 53, 0.1);
}

.donate-note, .donate-thank {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    color: #64748b;
}

.donate-note i, .donate-thank i {
    font-size: 1rem;
}

@media (max-width: 768px) {
    .donate-content .section-title {
        font-size: 2rem;
    }
    
    .donate-image img {
        height: 300px;
    }
    
    .donate-footer {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .payment-card {
        padding: 15px;
    }
    
    .payment-icon {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
}

/* Fixit Hero Section */
.fixit-hero-section {
    height: 100vh;
    min-height: 600px;
    background: linear-gradient(135deg, #3498db 0%, #FF6B35 100%);
    color: white;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
}

.fixit-hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.06), rgba(0,0,0,0.06));
    opacity: 0.3;
}

.fixit-hero-content {
    position: relative;
    z-index: 1;
    max-width: 800px;
    margin: 0 auto;
}

.fixit-hero-title {
    font-size: 4rem;
    font-weight: 800;
    margin-bottom: 20px;
    line-height: 1.2;
}

.fixit-hero-subtitle {
    font-size: 1.8rem;
    margin-bottom: 30px;
    opacity: 0.9;
}

.fixit-hero-description {
    font-size: 1.2rem;
    line-height: 1.8;
    margin-bottom: 40px;
    opacity: 0.9;
}

.hero-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Mission Section */
.mission-image {
    margin-bottom: 30px;
}

.mission-image img {
    width: 100%;
    height: auto;
    border-radius: 15px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

.mission-content .section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 20px;
}

.mission-values {
    display: flex;
    flex-direction: column;
    gap: 25px;
    margin-top: 30px;
}

.value-item {
    display: flex;
    align-items: flex-start;
    gap: 20px;
}

.value-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #FF6B35 0%, #FF6B35 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.value-content h4 {
    font-size: 1.2rem;
    font-weight: 600;
    color: #FF6B35;
    color: #3498db;
    color: var(--dark);
    margin-bottom: 8px;
}

.value-content p {
    color: var(--dark-blue);
    line-height: 1.6;
    margin: 0;
}

/* Projects Section */
.project-card-new {
    background: linear-gradient(145deg, #ffffff, #f8f9fa);
    border-radius: 25px;
    padding: 40px 25px 25px;
    box-shadow: 
        20px 20px 60px rgba(0, 0, 0, 0.1),
        -20px -20px 60px rgba(255, 255, 255, 0.7),
        inset 5px 5px 10px rgba(0, 0, 0, 0.05),
        inset -5px -5px 10px rgba(255, 255, 255, 0.5);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    overflow: visible;
    height: 100%;
    position: relative;
    text-align: center;
    transform-style: preserve-3d;
    animation: floatAnimation 6s ease-in-out infinite;
}

@keyframes floatAnimation {
    0%, 100% {
        transform: translateY(0px) rotateX(0deg) rotateY(0deg);
    }
    25% {
        transform: translateY(-10px) rotateX(2deg) rotateY(2deg);
    }
    75% {
        transform: translateY(-5px) rotateX(-1deg) rotateY(-1deg);
    }
}

.project-card-new:hover {
    transform: translateY(-15px) scale(1.02) rotateX(5deg) rotateY(5deg);
    box-shadow: 
        25px 25px 80px rgba(0, 0, 0, 0.15),
        -25px -25px 80px rgba(255, 255, 255, 0.8),
        inset 8px 8px 15px rgba(0, 0, 0, 0.08),
        inset -8px -8px 15px rgba(255, 255, 255, 0.6);
}

.project-card-new:nth-child(odd) {
    animation-delay: 0s;
}

.project-card-new:nth-child(even) {
    animation-delay: 1s;
}

.project-icon-wrapper {
    position: relative;
    margin-bottom: 30px;
    display: flex;
    justify-content: center;
}

.project-image-new {
    width: 220px;
    height: 220px;
    border-radius: 20%;
    overflow: hidden;
    position: relative;
    z-index: 2;
    box-shadow: 
        15px 15px 30px rgba(52, 152, 219, 0.4),
        -15px -15px 30px rgba(255, 107, 53, 0.3),
        inset 5px 5px 10px rgba(255, 255, 255, 0.2);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    animation: imagePulse 3s ease-in-out infinite;
    border: 4px solid rgba(255, 255, 255, 0.8);
}

.project-image-new img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.4s ease;
}

@keyframes imagePulse {
    0%, 100% {
        transform: scale(1) rotate(0deg);
        box-shadow: 
            15px 15px 30px rgba(52, 152, 219, 0.4),
            -15px -15px 30px rgba(255, 107, 53, 0.3),
            inset 5px 5px 10px rgba(255, 255, 255, 0.2);
    }
    50% {
        transform: scale(1.05) rotate(5deg);
        box-shadow: 
            20px 20px 40px rgba(52, 152, 219, 0.5),
            -20px -20px 40px rgba(255, 107, 53, 0.4),
            inset 8px 8px 15px rgba(255, 255, 255, 0.3);
    }
}

.project-card-new:hover .project-image-new {
    transform: scale(1.15) rotate(10deg);
    box-shadow: 
        20px 20px 50px rgba(52, 152, 219, 0.6),
        -20px -20px 50px rgba(255, 107, 53, 0.5),
        inset 10px 10px 20px rgba(255, 255, 255, 0.4);
}

.project-card-new:hover .project-image-new img {
    transform: scale(1.1);
    filter: brightness(1.1);
}

.project-image-new::before {
    content: '';
    position: absolute;
    top: -10px;
    left: -10px;
    right: -10px;
    bottom: -10px;
    background: linear-gradient(145deg, #3498db, #FF6B35);
    border-radius: 50%;
    opacity: 0.3;
    z-index: -1;
    animation: imageGlow 4s ease-in-out infinite;
}

@keyframes imageGlow {
    0%, 100% {
        transform: scale(1);
        opacity: 0.2;
        filter: blur(8px);
    }
    50% {
        transform: scale(1.2);
        opacity: 0.4;
        filter: blur(12px);
    }
}

.project-body-new {
    position: relative;
    z-index: 1;
}

.project-title-new {
    font-size: 1.4rem;
    font-weight: 800;
    color: var(--dark);
    margin-bottom: 18px;
    text-align: center;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.project-card-new:hover .project-title-new {
    color: #3498db;
    transform: translateY(-2px);
    text-shadow: 3px 3px 6px rgba(52, 152, 219, 0.3);
}

.project-description-new {
    color: var(--dark-blue);
    line-height: 1.7;
    margin-bottom: 25px;
    text-align: center;
    font-size: 0.95rem;
    min-height: 70px;
    transition: all 0.3s ease;
}

.project-card-new:hover .project-description-new {
    color: #555;
    transform: translateY(-1px);
}

.project-status-new {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 15px 0 10px;
    border-top: 2px solid rgba(0, 0, 0, 0.05);
    background: linear-gradient(145deg, rgba(255, 255, 255, 0.5), rgba(248, 249, 250, 0.5));
    border-radius: 15px;
    margin: 0 -10px -10px;
    backdrop-filter: blur(5px);
}

.status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    position: relative;
    animation: statusBlink 2s ease-in-out infinite;
}

@keyframes statusBlink {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.2);
        opacity: 0.8;
    }
}

.status-dot.active {
    background: linear-gradient(145deg, #28a745, #20c997);
    box-shadow: 
        0 0 20px rgba(40, 167, 69, 0.6),
        0 0 40px rgba(40, 167, 69, 0.3),
        inset 2px 2px 4px rgba(255, 255, 255, 0.3);
}

.status-dot.planning {
    background: linear-gradient(145deg, #ffc107, #fd7e14);
    box-shadow: 
        0 0 20px rgba(255, 193, 7, 0.6),
        0 0 40px rgba(255, 193, 7, 0.3),
        inset 2px 2px 4px rgba(255, 255, 255, 0.3);
}

.status-dot.completed {
    background: linear-gradient(145deg, #17a2b8, #007bff);
    box-shadow: 
        0 0 20px rgba(23, 162, 184, 0.6),
        0 0 40px rgba(23, 162, 184, 0.3),
        inset 2px 2px 4px rgba(255, 255, 255, 0.3);
}

.status-text {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--dark-blue);
    text-transform: uppercase;
    letter-spacing: 1px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.project-card-new:hover .status-text {
    color: var(--dark);
    transform: translateX(2px);
}

/* Modern Projects Section */
.project-card-modern {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
    height: 100%;
    position: relative;
}

/* Team Section - New Design */
.team-card-new {
    background: linear-gradient(145deg, #ffffff, #f8f9fa);
    border-radius: 25px;
    padding: 40px 25px 25px;
    box-shadow: 
        20px 20px 60px rgba(0, 0, 0, 0.1),
        -20px -20px 60px rgba(255, 255, 255, 0.7),
        inset 5px 5px 10px rgba(0, 0, 0, 0.05),
        inset -5px -5px 10px rgba(255, 255, 255, 0.5);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    overflow: visible;
    height: 100%;
    position: relative;
    text-align: center;
    transform-style: preserve-3d;
    animation: teamFloatAnimation 6s ease-in-out infinite;
}

@keyframes teamFloatAnimation {
    0%, 100% {
        transform: translateY(0px) rotateX(0deg) rotateY(0deg);
    }
    25% {
        transform: translateY(-10px) rotateX(2deg) rotateY(2deg);
    }
    75% {
        transform: translateY(-5px) rotateX(-1deg) rotateY(-1deg);
    }
}

.team-card-new:hover {
    transform: translateY(-15px) scale(1.02) rotateX(5deg) rotateY(5deg);
    box-shadow: 
        25px 25px 80px rgba(0, 0, 0, 0.15),
        -25px -25px 80px rgba(255, 255, 255, 0.8),
        inset 8px 8px 15px rgba(0, 0, 0, 0.08),
        inset -8px -8px 15px rgba(255, 255, 255, 0.6);
}

.team-card-new:nth-child(odd) {
    animation-delay: 0.5s;
}

.team-card-new:nth-child(even) {
    animation-delay: 1.5s;
}

.team-icon-wrapper {
    position: relative;
    margin-bottom: 30px;
    display: flex;
    justify-content: center;
}

.team-image-new {
    width: 220px;
    height: 220px;
    border-radius: 20%;
    overflow: hidden;
    position: relative;
    z-index: 2;
    box-shadow: 
        15px 15px 30px rgba(52, 152, 219, 0.4),
        -15px -15px 30px rgba(255, 107, 53, 0.3),
        inset 5px 5px 10px rgba(255, 255, 255, 0.2);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    animation: teamImagePulse 3s ease-in-out infinite;
    border: 4px solid rgba(255, 255, 255, 0.8);
}

.team-image-new img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.4s ease;
}

@keyframes teamImagePulse {
    0%, 100% {
        transform: scale(1) rotate(0deg);
        box-shadow: 
            15px 15px 30px rgba(52, 152, 219, 0.4),
            -15px -15px 30px rgba(255, 107, 53, 0.3),
            inset 5px 5px 10px rgba(255, 255, 255, 0.2);
    }
    50% {
        transform: scale(1.05) rotate(5deg);
        box-shadow: 
            20px 20px 40px rgba(52, 152, 219, 0.5),
            -20px -20px 40px rgba(255, 107, 53, 0.4),
            inset 8px 8px 15px rgba(255, 255, 255, 0.3);
    }
}

.team-card-new:hover .team-image-new {
    transform: scale(1.15) rotate(10deg);
    box-shadow: 
        20px 20px 50px rgba(52, 152, 219, 0.6),
        -20px -20px 50px rgba(255, 107, 53, 0.5),
        inset 10px 10px 20px rgba(255, 255, 255, 0.4);
}

.team-card-new:hover .team-image-new img {
    transform: scale(1.1);
    filter: brightness(1.1);
}

.team-image-new::before {
    content: '';
    position: absolute;
    top: -10px;
    left: -10px;
    right: -10px;
    bottom: -10px;
    background: linear-gradient(145deg, #3498db, #FF6B35);
    border-radius: 50%;
    opacity: 0.3;
    z-index: -1;
    animation: teamImageGlow 4s ease-in-out infinite;
}

@keyframes teamImageGlow {
    0%, 100% {
        transform: scale(1);
        opacity: 0.2;
        filter: blur(8px);
    }
    50% {
        transform: scale(1.2);
        opacity: 0.4;
        filter: blur(12px);
    }
}

.team-body-new {
    position: relative;
    z-index: 1;
}

.team-title-new {
    font-size: 1.4rem;
    font-weight: 800;
    color: var(--dark);
    margin-bottom: 8px;
    text-align: center;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.team-card-new:hover .team-title-new {
    color: #3498db;
    transform: translateY(-2px);
    text-shadow: 3px 3px 6px rgba(52, 152, 219, 0.3);
}

.team-role-new {
    color: #FF6B35;
    font-weight: 700;
    margin-bottom: 15px;
    font-size: 1rem;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.team-card-new:hover .team-role-new {
    color: #3498db;
    transform: translateY(-1px);
}

.team-description-new {
    color: var(--dark-blue);
    line-height: 1.6;
    margin-bottom: 20px;
    text-align: center;
    font-size: 0.9rem;
    min-height: 60px;
    transition: all 0.3s ease;
}

.team-card-new:hover .team-description-new {
    color: #555;
    transform: translateY(-1px);
}

.team-social-new {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 15px 0 10px;
    border-top: 2px solid rgba(0, 0, 0, 0.05);
    background: linear-gradient(145deg, rgba(255, 255, 255, 0.5), rgba(248, 249, 250, 0.5));
    border-radius: 15px;
    margin: 0 -10px -10px;
    backdrop-filter: blur(5px);
}

.social-link-new {
    width: 35px;
    height: 35px;
    background: linear-gradient(145deg, #3498db, #FF6B35);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 
        5px 5px 10px rgba(52, 152, 219, 0.3),
        -5px -5px 10px rgba(255, 107, 53, 0.2);
}

.social-link-new:hover {
    transform: scale(1.2) rotate(15deg);
    box-shadow: 
        8px 8px 15px rgba(52, 152, 219, 0.4),
        -8px -8px 15px rgba(255, 107, 53, 0.3);
    color: white;
}

/* Team Carousel */
.team-carousel-container {
    position: relative;
    max-width: 400px;
    margin: 40px auto;
    /*padding: 20px 60px;*/
}

.team-carousel {
    position: relative;
    overflow: hidden !important;
    border-radius: 20px;
    width: 100%;
}

.team-carousel-inner {
    display: flex !important;
    transition: transform 0.5s ease-in-out;
    will-change: transform;
    width: auto;
    height: auto;
    position: relative;
}

.team-carousel-item {
    min-width: 100% !important;
    width: 100% !important;
    display: flex !important;
    justify-content: center;
    align-items: center;
    padding: 0 10px;
    flex-shrink: 0 !important;
    box-sizing: border-box;
    opacity: 1;
    visibility: visible;
}

.team-carousel-item .team-card-new {
    width: 100%;
    max-width: 350px;
    margin: 0 auto;
}

.team-carousel-control {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: linear-gradient(135deg, #3498db 0%, #FF6B35 100%);
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
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
    font-size: 1.2rem;
}

.team-carousel-control:hover {
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
}

.team-carousel-control.prev {
    left: 0;
}

.team-carousel-control.next {
    right: 0;
}

.team-carousel-indicators {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 20px;
}

.team-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(52, 152, 219, 0.3);
    border: 2px solid rgba(52, 152, 219, 0.5);
    cursor: pointer;
    transition: all 0.3s ease;
}

.team-indicator.active {
    background: #3498db;
    transform: scale(1.2);
}

.team-indicator:hover {
    background: rgba(52, 152, 219, 0.7);
}

/* Team Section */
.team-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
    height: 100%;
}

.team-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.team-image {
    position: relative;
    overflow: hidden;
}

.team-image img {
    width: 100%;
    height: 250px;
    object-fit: cover;
}

.team-social {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.team-card:hover .team-social {
    opacity: 1;
}

.social-link {
    width: 35px;
    height: 35px;
    background: linear-gradient(135deg, #3498db 0%, #FF6B35 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-link:hover {
    transform: scale(1.1);
    color: white;
}

.team-content {
    padding: 25px;
    text-align: center;
}

.team-name {
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 8px;
}

.team-role {
    color: var(--primary);
    font-weight: 600;
    margin-bottom: 15px;
    font-size: 1rem;
}

.team-description {
    color: var(--dark-blue);
    margin: 0;
}

/* Review Section */
.fixit-review-section {
    padding: 100px 0;
    position: relative;
    z-index: 1;
}

.review-pattern-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="review-pattern" width="30" height="30" patternUnits="userSpaceOnUse"><circle cx="15" cy="15" r="2" fill="rgba(52, 152, 219, 0.05)"/><circle cx="5" cy="5" r="1" fill="rgba(255, 107, 53, 0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23review-pattern)"/></svg>');
    opacity: 0.3;
    animation: reviewPatternMove 25s linear infinite;
}

@keyframes reviewPatternMove {
    0% { transform: translateX(0) translateY(0); }
    100% { transform: translateX(30px) translateY(30px); }
}

.review-form-card {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 25px;
    padding: 40px;
    box-shadow: 
        0 20px 40px rgba(0, 0, 0, 0.1),
        0 10px 20px rgba(52, 152, 219, 0.1);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(52, 152, 219, 0.2);
    position: relative;
    overflow: hidden;
}

.review-form-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3498db, #FF6B35);
    animation: formHeaderGlow 3s ease-in-out infinite;
}

@keyframes formHeaderGlow {
    0%, 100% { opacity: 0.8; }
    50% { opacity: 1; }
}

.review-form-header {
    text-align: center;
    margin-bottom: 30px;
}

.review-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.2) 0%, rgba(255, 107, 53, 0.2) 100%);
    border: 2px solid rgba(52, 152, 219, 0.3);
    border-radius: 50px;
    padding: 8px 20px;
    margin-bottom: 20px;
    backdrop-filter: blur(10px);
    color: #3498db;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    animation: reviewBadgeGlow 3s ease-in-out infinite;
}

@keyframes reviewBadgeGlow {
    0%, 100% { 
        box-shadow: 0 0 15px rgba(52, 152, 219, 0.3);
        transform: scale(1);
    }
    50% { 
        box-shadow: 0 0 25px rgba(255, 107, 53, 0.4);
        transform: scale(1.05);
    }
}

.review-form-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 10px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

.review-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 5px;
    font-size: 0.95rem;
}

.form-group input,
.form-group textarea {
    padding: 12px 15px;
    border: 2px solid rgba(52, 152, 219, 0.2);
    border-radius: 10px;
    font-size: 1rem;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(5px);
    transition: all 0.3s ease;
    color: var(--dark);
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 15px rgba(52, 152, 219, 0.2);
    background: rgba(255, 255, 255, 0.95);
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.star-rating {
    display: flex;
    gap: 5px;
    font-size: 1.5rem;
    cursor: pointer;
}

.star-rating i {
    color: #ddd;
    transition: all 0.2s ease;
}

.star-rating i:hover,
.star-rating i.active {
    color: #FFD700;
    transform: scale(1.1);
}

.btn-review-submit {
    background: linear-gradient(135deg, #3498db 0%, #FF6B35 100%);
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
    position: relative;
    overflow: hidden;
}

.btn-review-submit::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s ease;
}

.btn-review-submit:hover::before {
    left: 100%;
}

.btn-review-submit:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
}

.review-success-message {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9999;
    animation: successMessageShow 0.5s ease;
}

@keyframes successMessageShow {
    0% { 
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.8);
    }
    100% { 
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
}

.success-content {
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    text-align: center;
    border: 2px solid #28a745;
}

.success-content i {
    font-size: 3rem;
    color: #28a745;
    margin-bottom: 15px;
}

.success-content h4 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 10px;
}

.success-content p {
    color: var(--dark-blue);
    margin: 0;
}

/* Reviews Display Section */
.reviews-display-section {
    padding: 80px 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(248, 249, 250, 0.1) 100%);
    position: relative;
}

.reviews-display-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="reviews-bg" width="40" height="40" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="1" fill="rgba(52, 152, 219, 0.03)"/><circle cx="10" cy="10" r="0.5" fill="rgba(255, 107, 53, 0.03)"/></pattern></defs><rect width="100" height="100" fill="url(%23reviews-bg)"/></svg>');
    opacity: 0.4;
    animation: reviewsBgMove 30s linear infinite;
}

@keyframes reviewsBgMove {
    0% { transform: translateX(0) translateY(0); }
    100% { transform: translateX(40px) translateY(40px); }
}

.review-item {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 
        0 10px 30px rgba(0, 0, 0, 0.08),
        0 5px 15px rgba(52, 152, 219, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(52, 152, 219, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.review-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #3498db, #FF6B35);
    animation: reviewItemGlow 4s ease-in-out infinite;
}

@keyframes reviewItemGlow {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 1; }
}

.review-item:hover {
    transform: translateY(-5px);
    box-shadow: 
        0 15px 40px rgba(0, 0, 0, 0.12),
        0 8px 25px rgba(52, 152, 219, 0.15);
    background: rgba(255, 255, 255, 0.95);
}

.review-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.reviewer-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3498db, #FF6B35);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.reviewer-info {
    flex: 1;
}

.reviewer-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 5px;
}

.review-date {
    font-size: 0.85rem;
    color: var(--dark-blue);
    margin-bottom: 10px;
}

.review-rating-display {
    display: flex;
    gap: 3px;
    margin-bottom: 15px;
}

.review-rating-display .fas {
    color: #FFD700;
    font-size: 0.9rem;
}

.review-rating-display .far {
    color: #ddd;
    font-size: 0.9rem;
}

.review-content {
    color: var(--dark-blue);
    line-height: 1.6;
    font-size: 0.95rem;
    margin: 0;
}

.load-more-container {
    margin-top: 40px;
    padding: 20px;
}

.btn-load-more {
    background: linear-gradient(135deg, #3498db 0%, #FF6B35 100%);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
    position: relative;
    overflow: hidden;
}

.btn-load-more:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
}

.btn-load-more::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s ease;
}

.btn-load-more:hover::before {
    left: 100%;
}

.fa-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Modern CTA Section */
.fixit-cta-section {
    padding: 100px 0;
    position: relative;
    z-index: 1;
}

.cta-pattern-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="cta-pattern" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(52, 152, 219, 0.1)"/><circle cx="5" cy="5" r="0.5" fill="rgba(255, 107, 53, 0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23cta-pattern)"/></svg>');
    opacity: 0.5;
    animation: patternMove 20s linear infinite;
}

@keyframes patternMove {
    0% { transform: translateX(0) translateY(0); }
    100% { transform: translateX(20px) translateY(20px); }
}

.cta-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.2) 0%, rgba(255, 107, 53, 0.2) 100%);
    border: 2px solid rgba(52, 152, 219, 0.3);
    border-radius: 50px;
    padding: 8px 20px;
    margin-bottom: 30px;
    backdrop-filter: blur(10px);
    color: #3498db;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    animation: badgeGlow 3s ease-in-out infinite;
}

@keyframes badgeGlow {
    0%, 100% { 
        box-shadow: 0 0 20px rgba(52, 152, 219, 0.3);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 25px rgba(255, 107, 53, 0.4);
        transform: scale(1.05);
    }
}

@media (max-width: 768px) {
    .fixit-hero-subtitle {
        font-size: 1.4rem;
    }
    .hero-buttons {
        flex-direction: column;
        align-items: center;
    }
    .mission-content .section-title {
        font-size: 2rem;
    }
    .project-card,
    .team-card {
        margin-bottom: 30px;
    }
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
    .cta-buttons .btn {
        width: 100%;
        max-width: 300px;
    }
}


<?php include BASE_PATH.'/includes/footer.php';?>
<script>
// Team Carousel JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Team Carousel functionality
    const teamCarousel = document.getElementById('teamCarousel');
    const teamItems = document.querySelectorAll('.team-carousel-item');
    const teamPrevBtn = document.getElementById('teamPrevBtn');
    const teamNextBtn = document.getElementById('teamNextBtn');
    const teamIndicators = document.getElementById('teamIndicators');
    
    if (teamCarousel && teamItems.length > 0) {
        let currentTeamIndex = 0;
        const totalTeamItems = teamItems.length;
        let autoPlayInterval = null;
        let isTransitioning = false;
        
        // Ensure carousel is properly initialized
        teamCarousel.style.transform = 'translateX(0%)';
        
        // Create indicators
        function createTeamIndicators() {
            if (!teamIndicators) return;
            teamIndicators.innerHTML = '';
            for (let i = 0; i < totalTeamItems; i++) {
                const indicator = document.createElement('div');
                indicator.className = `team-indicator ${i === 0 ? 'active' : ''}`;
                indicator.addEventListener('click', () => {
                    if (!isTransitioning) {
                        goToTeamSlide(i);
                    }
                });
                teamIndicators.appendChild(indicator);
            }
        }
        
        // Go to specific slide
        function goToTeamSlide(index) {
            if (isTransitioning || index < 0 || index >= totalTeamItems) return;
            
            isTransitioning = true;
            currentTeamIndex = index;
            const offset = -index * 100;
            teamCarousel.style.transform = `translateX(${offset}%)`;
            updateTeamIndicators();
            
            // Reset transition flag after animation completes
            setTimeout(() => {
                isTransitioning = false;
            }, 500);
        }
        
        // Update indicators
        function updateTeamIndicators() {
            if (!teamIndicators) return;
            const indicators = teamIndicators.querySelectorAll('.team-indicator');
            indicators.forEach((indicator, index) => {
                indicator.classList.toggle('active', index === currentTeamIndex);
            });
        }
        
        // Next slide
        function nextTeamSlide() {
            if (isTransitioning) return;
            const nextIndex = (currentTeamIndex + 1) % totalTeamItems;
            goToTeamSlide(nextIndex);
        }
        
        // Previous slide
        function prevTeamSlide() {
            if (isTransitioning) return;
            const prevIndex = (currentTeamIndex - 1 + totalTeamItems) % totalTeamItems;
            goToTeamSlide(prevIndex);
        }
        
        // Start auto-play
        function startAutoPlay() {
            if (autoPlayInterval) clearInterval(autoPlayInterval);
            autoPlayInterval = setInterval(() => {
                if (!isTransitioning) {
                    nextTeamSlide();
                }
            }, 5000);
        }
        
        // Stop auto-play
        function stopAutoPlay() {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
                autoPlayInterval = null;
            }
        }
        
        // Event listeners
        if (teamNextBtn) {
            teamNextBtn.addEventListener('click', () => {
                stopAutoPlay();
                nextTeamSlide();
                startAutoPlay();
            });
        }
        
        if (teamPrevBtn) {
            teamPrevBtn.addEventListener('click', () => {
                stopAutoPlay();
                prevTeamSlide();
                startAutoPlay();
            });
        }
        
        // Pause on hover
        const carouselContainer = document.querySelector('.team-carousel-container');
        if (carouselContainer) {
            carouselContainer.addEventListener('mouseenter', stopAutoPlay);
            carouselContainer.addEventListener('mouseleave', startAutoPlay);
        }
        
        // Initialize carousel
        createTeamIndicators();
        startAutoPlay();
        
        // Ensure first slide is visible
        goToTeamSlide(0);
    }
    
    // Star rating functionality for review form
    const starRating = document.getElementById('starRating');
    const stars = starRating.querySelectorAll('i');
    const starsInput = document.getElementById('stars');
    
    if (stars && starsInput) {
        // Star rating functionality
        stars.forEach((star, index) => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.dataset.rating);
                starsInput.value = rating;
                updateStars(rating);
            });
            
            star.addEventListener('mouseenter', function() {
                const rating = parseInt(this.dataset.rating);
                updateStars(rating);
            });
        });
        
        starRating.addEventListener('mouseleave', function() {
            updateStars(parseInt(starsInput.value));
        });
        
        function updateStars(rating) {
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.remove('far');
                    star.classList.add('fas', 'active');
                } else {
                    star.classList.remove('fas', 'active');
                    star.classList.add('far');
                }
            });
        }
    }
    
    // Smooth Scroll function
    window.smoothScroll = function(targetId) {
        event.preventDefault();
        const targetElement = document.getElementById(targetId);
        if (targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
    
    // Load More Reviews function
    window.loadMoreReviews = function() {
        const currentUrl = new URL(window.location);
        const currentLimit = parseInt(currentUrl.searchParams.get('load_more')) || 4;
        const newLimit = currentLimit + 4;
        
        // Show loading spinner
        const spinner = document.getElementById('loadingSpinner');
        const loadText = document.getElementById('loadMoreText');
        if (spinner) spinner.style.display = 'inline';
        if (loadText) loadText.textContent = 'Loading...';
        
        // Update URL and reload page
        currentUrl.searchParams.set('load_more', newLimit.toString());
        window.location.href = currentUrl.toString();
    }
    
    const loadMoreBtnEl = document.getElementById('loadMoreReviews');
    if (loadMoreBtnEl) {
        loadMoreBtnEl.removeEventListener('click', function(e){
            e.preventDefault();
            loadReviews();
        });
        loadMoreBtnEl.addEventListener('click', function(e){
            e.preventDefault();
            loadMoreReviews();
        });
    }
});
</script>
<?php
    // tracking code
    // User IP
// $ip = $_SERVER['REMOTE_ADDR'];


// $page = $_SERVER['REQUEST_URI'];

// $referrer = $_SERVER['HTTP_REFERER'] ?? 'Direct';

// $user_agent = $_SERVER['HTTP_USER_AGENT'];



// // Location API
// $api_url = "http://ip-api.com/json/$ip";
// $response = @file_get_contents($api_url);
// $data = json_decode($response, true);

// if ($data && $data['status'] == 'success') {
//     $country = $data['country'];
//     $city    = $data['city'];
//     $region  = $data['regionName'];
// } else {
//     $country = $city = $region = 'Unknown';
// }

// // Check IP exists
// $check = "SELECT id FROM history WHERE ip_address='$ip' LIMIT 1";
// $result = mysqli_query($con, $check);

// if (mysqli_num_rows($result) == 0) {

//     // 🔹 INSERT (first visit)
//     $insert = "INSERT INTO history 
//     (ip_address, country, city, region, visit, visit_date,referrer,page_url,browser,fixit_id)
//     VALUES 
//     ('$ip', '$country', '$city', '$region', 1, NOW(), '$referrer','$page','$user_agent', 1)";

//     mysqli_query($con, $insert);

// } else {

//     // 🔹 UPDATE (repeat visit)
//     $update = "UPDATE history 
//     SET visit = visit + 1, visit_date = NOW()
//     WHERE ip_address = '$ip'";

//     mysqli_query($con, $update);
// }
?>