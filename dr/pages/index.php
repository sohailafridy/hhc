<?php include '../includes/header.php'; 
?>

<style>
    
    body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--light);
        overflow-x: hidden;
    }
    
    /* Hero Section */
    .hero-section {
        height: 85vh;
        min-height: 500px;
        position: relative;
        overflow: hidden;
        background: var(--gradient);
    }
    
    .hero-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }
    
    .hero-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.3;
    }
    
    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.4));
        z-index: 2;
    }
    
    .hero-content {
        position: relative;
        z-index: 3;
        height: 100%;
        display: flex;
        align-items: center;
    }
    
    .hero-title {
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 20px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        color: white;
    }
    
    .hero-text {
        font-size: 1.2rem;
        margin-bottom: 30px;
        max-width: 600px;
        color: rgba(255, 255, 255, 0.9);
    }
    
    .btn-hero {
        padding: 12px 35px;
        border-radius: 50px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        background: var(--secondary);
        color: var(--primary);
        border: none;
        transition: all 0.3s;
    }
    
    .btn-hero:hover {
        background: var(--primary);
        color: var(--secondary);
        transform: translateY(-3px);
        box-shadow: var(--hover-shadow);
    }

    .hero-city-wrap {
        position: relative;
        display: inline-block;
        vertical-align: middle;
        min-width: 260px;
    }

    .hero-city-wrap .hero-city-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        color: var(--primary);
        font-size: 1rem;
        pointer-events: none;
        transition: color 0.3s;
    }

    .hero-city-wrap .select2-container {
        width: 100% !important;
    }

    .hero-city-wrap .select2-container--bootstrap-5 .select2-selection {
        border-radius: 50px !important;
        border: none !important;
        background: var(--secondary) !important;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 12px 45px 12px 42px !important;
        min-height: auto !important;
        height: auto !important;
        transition: all 0.3s;
        box-shadow: none;
    }

    .hero-city-wrap .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        color: var(--primary) !important;
        padding-left: 0;
        line-height: 1.5;
    }

    .hero-city-wrap .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
        color: var(--primary) !important;
        opacity: 0.85;
    }

    .hero-city-wrap .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 100%;
        right: 15px;
    }

    .hero-city-wrap .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow b {
        border-color: var(--primary) transparent transparent transparent;
    }

    .hero-city-wrap:hover .select2-container--bootstrap-5 .select2-selection,
    .hero-city-wrap:has(.select2-container--open) .select2-container--bootstrap-5 .select2-selection,
    .hero-city-wrap:has(.select2-container--focus) .select2-container--bootstrap-5 .select2-selection {
        background: var(--primary) !important;
        transform: translateY(-3px);
        box-shadow: var(--hover-shadow);
    }

    .hero-city-wrap:hover .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered,
    .hero-city-wrap:has(.select2-container--open) .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered,
    .hero-city-wrap:has(.select2-container--focus) .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        color: var(--secondary) !important;
    }

    .hero-city-wrap:hover .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder,
    .hero-city-wrap:has(.select2-container--open) .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
        color: var(--secondary) !important;
    }

    .hero-city-wrap:hover .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow b,
    .hero-city-wrap:has(.select2-container--open) .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow b {
        border-color: var(--secondary) transparent transparent transparent;
    }

    .hero-city-wrap:hover .hero-city-icon,
    .hero-city-wrap:has(.select2-container--open) .hero-city-icon,
    .hero-city-wrap:has(.select2-container--focus) .hero-city-icon {
        color: var(--secondary);
    }

    .select2-dropdown.hero-city-dropdown {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        margin-top: 8px;
    }

    .select2-dropdown.hero-city-dropdown .select2-search--dropdown {
        padding: 10px;
    }

    .select2-dropdown.hero-city-dropdown .select2-search--dropdown .select2-search__field {
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        padding: 8px 12px;
    }

    .select2-dropdown.hero-city-dropdown .select2-search--dropdown .select2-search__field:focus {
        border-color: var(--primary);
        outline: none;
    }

    .select2-dropdown.hero-city-dropdown .select2-results__option--highlighted {
        background: var(--primary) !important;
    }

    /* Hero Form Styles */
    .hero-form {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.3s ease;
        border-radius: 20px;
        padding: 30px;
    }
    
    .hero-form:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    
    .hero-form .form-control,
    .hero-form .form-select {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 12px 15px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    
    .hero-form .form-control:focus,
    .hero-form .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.1);
    }
    
    .hero-form .form-label {
        font-weight: 600;
        margin-bottom: 5px;
        color: var(--dark);
    }
    
    .hero-form .btn-primary {
        background: var(--gradient);
        border: none;
        transition: all 0.3s ease;
    }
    
    .hero-form .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
    }

    /* Section Styles */
    .section-padding {
        padding: 80px 0;
    }
    
    .section-header {
        text-align: center;
        margin-bottom: 50px;
    }
    
    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 15px;
    }
    
    .section-subtitle {
        font-size: 1.1rem;
        color: var(--secondary);
        max-width: 600px;
        margin: 0 auto;
    }

    /* Card Styles */
    .service-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        text-align: center;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .service-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--hover-shadow);
    }
    
    .service-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: var(--gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
    }
    
    .service-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 15px;
    }
    
    .service-description {
        color: var(--secondary);
        line-height: 1.6;
    }

    .about-us-img {
        border-radius: 28px;
        /* border: 8px solid rgba(255, 255, 255, 0.9); */
        box-shadow:
            0 40px 90px rgba(15, 23, 42, 0.2),
            0 18px 40px rgba(102, 126, 234, 0.25),
            0 0 40px rgba(255, 160, 120, 0.25);
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(248, 249, 250, 0.6));
        backdrop-filter: blur(14px);
        transition: transform 0.4s ease, box-shadow 0.4s ease;
    }

    .about-us-img:hover {
        transform: translateY(-8px) scale(1.01);
        box-shadow:
            0 50px 110px rgba(15, 23, 42, 0.25),
            0 22px 45px rgba(102, 126, 234, 0.3),
            0 0 50px rgba(255, 160, 120, 0.35);
    }

    /* Hospital/Doctor/Lab Cards */
    .modern-cards .entity-card {
        background: rgba(255, 255, 255, 0.92);
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 30px 80px rgba(15, 23, 42, 0.12), 0 6px 20px rgba(102, 126, 234, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(16px);
        transition: all 0.4s ease;
        height: 100%;
        position: relative;
        padding-bottom: 10px;
    }

    .modern-cards .entity-card::before {
        content: '';
        position: absolute;
        inset: -50% 10% auto;
        height: 180px;
        background: radial-gradient(circle, rgba(255, 191, 148, 0.45), transparent 70%);
        opacity: 0.9;
        pointer-events: none;
        transition: transform 0.4s ease;
    }
    
    .modern-cards .entity-card:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: 0 35px 90px rgba(15, 23, 42, 0.18), 0 12px 28px rgba(102, 126, 234, 0.2);
    }

    .modern-cards .entity-card:hover::before {
        transform: translateY(-12px) scale(1.1);
    }
    
    .modern-cards .entity-image {
        height: 240px;
        background: linear-gradient(145deg, rgba(255, 160, 120, 0.25), rgba(82, 168, 255, 0.2));
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 22px 20px 10px;
    }
    
    .modern-cards .entity-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 26px;
        border: 6px solid rgba(255, 255, 255, 0.95);
        box-shadow: 0 20px 40px rgba(102, 126, 234, 0.18), 0 10px 22px rgba(255, 160, 120, 0.22);
    }
    
    .modern-cards .entity-content {
        padding: 26px 26px 20px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 250, 0.85));
        text-align: center;
    }
    
    .entity-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #4a8bd6;
        margin-bottom: 12px;
    }
    
    .entity-description {
        color: #6d737c;
        font-size: 0.95rem;
        margin-bottom: 18px;
    }
    
    .entity-meta {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 12px;
        font-size: 0.85rem;
        color: #2f3b4a;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 16px;
        padding: 10px 14px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.8);
    }

    /* Premium Doctor Cards */
    .doctor-card-premium {
        isolation: isolate;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(245, 248, 255, 0.94));
    }

    .doctor-card-premium::after {
        content: '';
        position: absolute;
        inset: auto -20% -35% auto;
        width: 180px;
        height: 180px;
        background: radial-gradient(circle, rgba(79, 172, 254, 0.22), transparent 68%);
        z-index: 0;
        transition: transform 0.45s ease, opacity 0.45s ease;
        opacity: 0.85;
    }

    .doctor-card-premium:hover::after {
        transform: scale(1.12) translate(-10px, -12px);
        opacity: 1;
    }

    .doctor-card-badge {
        position: absolute;
        top: 18px;
        left: 18px;
        z-index: 3;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 14px;
        border-radius: 999px;
        background: rgba(9, 30, 66, 0.66);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        border: 1px solid rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(14px);
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
    }

    .doctor-card-badge i {
        color: #ffd166;
    }

    .doctor-card-image {
        height: 210px;
        padding: 14px;
    }

    .doctor-card-image::before {
        content: '';
        position: absolute;
        inset: 14px;
        border-radius: 24px;
        background:
            linear-gradient(180deg, rgba(6, 24, 44, 0.02), rgba(6, 24, 44, 0.42)),
            linear-gradient(135deg, rgba(79, 172, 254, 0.12), rgba(120, 119, 198, 0.12));
        z-index: 1;
        pointer-events: none;
    }

    .doctor-card-image::after {
        content: '';
        position: absolute;
        top: -10%;
        right: -10%;
        width: 170px;
        height: 170px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.38), transparent 70%);
        z-index: 1;
        animation: doctorCardFloat 7s ease-in-out infinite;
        pointer-events: none;
    }

    .doctor-card-premium:hover .doctor-card-image img {
        transform: scale(1.07);
        filter: saturate(1.08) contrast(1.02);
    }

    .doctor-card-image img {
        position: relative;
        z-index: 0;
        transition: transform 0.6s ease, filter 0.6s ease;
    }

    .doctor-card-floating {
        position: absolute;
        right: 22px;
        bottom: 22px;
        z-index: 2;
        display: inline-flex;
    }

    .doctor-card-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.92);
        color: #17324d;
        font-size: 0.78rem;
        font-weight: 600;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
    }

    .doctor-card-chip i {
        color: var(--primary);
    }

    .doctor-card-content {
        position: relative;
        z-index: 1;
        padding: 18px 18px 16px;
        text-align: left;
    }

    .doctor-card-specialty {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        margin-bottom: 10px;
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(79, 172, 254, 0.12), rgba(103, 114, 229, 0.12));
        color: #315f97;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .doctor-card-specialty i {
        color: var(--accent);
    }

    .doctor-card-title {
        font-size: 1.18rem;
        line-height: 1.25;
        margin-bottom: 10px;
        min-height: 0;
    }

    .doctor-card-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 14px;
        padding: 10px 12px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.86);
        border: 1px solid rgba(79, 172, 254, 0.1);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
    }

    .doctor-card-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #17324d;
        font-size: 0.84rem;
        font-weight: 700;
    }

    .doctor-card-meta-item i {
        color: var(--primary);
    }

    .doctor-card-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 12px;
        border-top: 1px solid rgba(79, 172, 254, 0.12);
    }

    .doctor-detail-btn {
        padding: 8px 18px;
        border-radius: 999px;
        box-shadow: 0 14px 30px rgba(79, 172, 254, 0.28);
        font-size: 0.82rem;
    }

    @keyframes doctorCardFloat {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(12px);
        }
    }

    /* Review Section */
    .review-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .review-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--hover-shadow);
    }
    
    .review-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .review-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: var(--gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        margin-right: 15px;
    }
    
    .review-info h5 {
        margin: 0;
        font-size: 1rem;
        color: var(--dark);
    }
    
    .review-rating {
        color: #ffc107;
        font-size: 0.9rem;
    }
    
    .review-text {
        color: var(--dark);
        line-height: 1.6;
        font-style: italic;
    }

    .btn-view-all {
        color: white;
        border: none;
        padding: 8px 18px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.82rem;
        letter-spacing: 0.02em;
        transition: all 0.3s ease;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
    }

    .btn-view-all i {
        font-size: 0.8rem;
    }
    
    .btn-view-all:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.16);
        color: white;
    }

    .btn-view-specialities {
        background: linear-gradient(135deg, #e74c3c, #ff7b72);
    }

    .btn-view-hospitals {
        background: linear-gradient(135deg, #5b6df6, #7f8cff);
    }

    .btn-view-doctors {
        background: linear-gradient(135deg, #315f97, #4a8be0);
    }

    .btn-view-labs {
        background: linear-gradient(135deg, #136f63, #24a38f);
    }

    .btn-view-blood {
        background: linear-gradient(135deg, #c0392b, #ef6a5b);
    }

    .no-record-found {
        width: 100%;
        padding: 18px 16px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.75);
        border: 1px dashed rgba(79, 172, 254, 0.2);
        text-align: center;
        color: #5f6f81;
        font-weight: 700;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
    }

    /* Entity Actions Styling */
    .entity-actions {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid rgba(79, 172, 254, 0.1);
    }

   

    /* detail btns */
      .detail-btns {
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        border: none;
        border-radius: 25px;
        padding: 8px 20px;
        font-size: 0.85rem;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        text-decoration: none;
        display: inline-block;
    }

    .detail-btns::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.1) 100%);
        transition: left 0.3s ease;
    }

    .detail-btns:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(79, 172, 254, 0.4);
        color: white;
        text-decoration: none;
    }

    .detail-btns:hover::before {
        left: 100%;
    }

    .detail-btns:active {
        transform: translateY(0);
        box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
    }




    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .hero-section {
            height: auto;
            min-height: 100vh;
            padding: 100px 0 50px;
        }
        
        .hero-content {
            padding: 20px 0;
            width: 100%;
        }
        
        .hero-title {
            font-size: 2rem;
            line-height: 1.2;
            margin-bottom: 15px;
        }
        
        .hero-text {
            font-size: 1rem;
            margin-bottom: 20px;
        }
        
        .hero-form {
            margin-top: 30px;
            padding: 20px;
            width: 100%;
            max-width: 100%;
        }
        
        .btn-hero {
            padding: 10px 25px;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .section-padding {
            padding: 50px 0;
        }
        
        .section-title {
            font-size: 1.8rem;
        }
        
        .section-subtitle {
            font-size: 1rem;
        }
        
        .entity-card {
            margin-bottom: 20px;
        }
        
        .review-card {
            margin-bottom: 20px;
        }
        
        .service-icon {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }
        
        .container {
            padding-left: 15px;
            padding-right: 15px;
            max-width: 100%;
        }

        .doctor-card-image {
            height: 190px;
        }

        .doctor-card-title {
            min-height: auto;
            font-size: 1.08rem;
        }

        .doctor-card-actions {
            justify-content: center;
        }

        .doctor-detail-btn {
            text-align: center;
        }
    }

    @media (max-width: 576px) {
        .hero-title {
            font-size: 1.8rem;
        }
        
        .hero-form {
            padding: 15px;
        }
        
        .section-padding {
            padding: 40px 0;
        }
        
        .section-title {
            font-size: 1.6rem;
        }
        
        .container {
            padding-left: 10px;
            padding-right: 10px;
        }
        .service-icon {
            margin: 0;
        }
        .mgt{
            margin-top: 10px;
        }

        .doctor-card-content {
            padding: 16px 16px 14px;
        }

        .doctor-card-image {
            height: 180px;
            padding: 12px;
        }

        .doctor-card-image::before {
            inset: 12px;
        }

        .doctor-card-floating {
            right: 18px;
            bottom: 18px;
        }

        .doctor-card-chip {
            font-size: 0.72rem;
            padding: 7px 10px;
        }

        .doctor-card-meta {
            flex-direction: column;
            align-items: flex-start;
        }
        .mob-layout{
            margin-top: 10px !important;
            margin-left: 32% !important;
        }
        .hero-city-wrap{
            width: 100% !important;
        }
    }
    .heading-color{
        color: var(--dark-blue);
    }
    .normal-color{
        color: var(--dark-blue);
    }

    /* Modern Hospital Cards Styles */
    .hospital-card-modern {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(79, 172, 254, 0.12);
        transition: all 0.4s ease;
        height: 100%;
        border: 2px solid transparent;
        position: relative;
    }

    .hospital-card-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .hospital-card-modern:hover::before {
        transform: scaleX(1);
    }

    .hospital-card-modern:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 15px 40px rgba(79, 172, 254, 0.25);
        border-color: var(--primary);
    }

    .hospital-image-modern {
        position: relative;
        height: 160px;
        overflow: hidden;
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    }

    .hospital-image-modern img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: all 0.4s ease;
        filter: brightness(0.95);
    }

    .hospital-card-modern:hover .hospital-image-modern img {
        transform: scale(1.1);
        filter: brightness(1);
    }

    .hospital-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        padding: 6px 12px;
        border-radius: 25px;
        font-size: 0.8rem;
        font-weight: 700;
        color: white;
        box-shadow: 0 4px 15px rgba(238, 90, 36, 0.4);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .hospital-badge i {
        margin-right: 5px;
        color: #ffd700;
        font-size: 0.9rem;
    }

    .hospital-content-modern {
        padding: 20px;
        text-align: center;
        background: white;
        position: relative;
    }

    .hospital-title-modern {
        font-size: 1rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 10px;
        line-height: 1.4;
        height: 40px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        text-transform: capitalize;
    }

    .hospital-location-modern {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-weight: 500;
    }

    .hospital-location-modern i {
        color: var(--primary);
        font-size: 0.8rem;
    }

    .hospital-btn-modern {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s ease;
        margin: 0 auto;
        box-shadow: 0 6px 20px rgba(79, 172, 254, 0.4);
        position: relative;
        overflow: hidden;
    }

    .hospital-btn-modern::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: width 0.4s ease, height 0.4s ease;
    }

    .hospital-btn-modern:hover::before {
        width: 100%;
        height: 100%;
    }

    .hospital-btn-modern:hover {
        transform: scale(1.15) rotate(90deg);
        box-shadow: 0 8px 25px rgba(79, 172, 254, 0.5);
        color: white;
        text-decoration: none;
    }

    .hospital-btn-modern i {
        font-size: 0.9rem;
        transition: transform 0.3s ease;
        position: relative;
        z-index: 1;
    }

    .hospital-btn-modern:hover i {
        transform: translateX(3px);
    }
    .mob-layout{
            padding: 10px 20px !important;
        }

    @media (max-width: 992px) {
        .hospital-image-modern {
            height: 140px;
        }
        
        .hospital-title-modern {
            font-size: 0.95rem;
        }
        
        .hospital-content-modern {
            padding: 15px;
        }

    }

    @media (max-width: 768px) {
        .hospital-image-modern {
            height: 120px;
        }
        
        .hospital-title-modern {
            font-size: 0.9rem;
            height: 35px;
        }
        
        .hospital-location-modern {
            font-size: 0.8rem;
        }
        
        .hospital-btn-modern {
            width: 35px;
            height: 35px;
        }
    }

    /* Service Cards Styles */
    .service-card {
        display: block;
        transition: all 0.3s ease;
        height: 100%;
    }

    .service-card-body {
        background: white;
        border-radius: 20px;
        padding: 30px 25px;
        text-align: center;
        height: 100%;
        transition: all 0.4s ease;
        border: 2px solid transparent;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        position: relative;
        overflow: hidden;
    }

    .service-card-body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .service-card:hover .service-card-body {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(79, 172, 254, 0.2);
        border-color: var(--primary);
    }

    .service-card:hover .service-card-body::before {
        transform: scaleX(1);
    }

    .service-icon-wrapper {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        transition: all 0.3s ease;
        position: relative;
    }

    .service-icon-wrapper::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        opacity: 0.3;
        animation: pulse 2s infinite;
    }

    .service-card:hover .service-icon-wrapper {
        transform: scale(1.1) rotate(5deg);
    }

    .service-title {
        color: var(--dark-blue);
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 15px;
        transition: color 0.3s ease;
    }

    .service-card:hover .service-title {
        color: var(--primary);
    }

    .service-description {
        color: #6c757d;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 20px;
        min-height: 50px;
    }

    .service-arrow {
        width: 40px;
        height: 40px;
        margin: 0 auto;
        background: rgba(79, 172, 254, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 1rem;
        transition: all 0.3s ease;
        opacity: 0;
        transform: translateX(-10px);
    }

    .service-card:hover .service-arrow {
        opacity: 1;
        transform: translateX(0);
        background: var(--primary);
        color: white;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 0.3;
        }
        50% {
            transform: scale(1.1);
            opacity: 0.1;
        }
        100% {
            transform: scale(1);
            opacity: 0.3;
        }
    }

    @media (max-width: 768px) {
        .service-card-body {
            padding: 25px 20px;
        }
        
        .service-icon-wrapper {
            width: 70px;
            height: 70px;
            font-size: 1.8rem;
        }
        
        .service-title {
            font-size: 1.2rem;
        }
        
        .service-description {
            font-size: 0.9rem;
        }
    }

    /* Specialities Cards Styles */
    .speciality-card {
        transition: all 0.3s ease;
        height: 100%;
    }

    .speciality-card-body {
        border-radius: 12px;
        padding: 15px 10px;
        text-align: center;
        height: 100%;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        position: relative;
        overflow: hidden;
    }

    .speciality-card:hover .speciality-card-body {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .speciality-icon {
        width: 45px;
        height: 45px;
        margin: 0 auto 10px;
        background: rgba(255, 255, 255, 0.8);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .speciality-emoji {
        font-size: 1.4rem;
        line-height: 1;
    }

    .speciality-card:hover .speciality-icon {
        transform: scale(1.1);
        background: rgba(255, 255, 255, 1);
    }

    .speciality-title {
        color: #2c3e50;
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 10px;
        transition: color 0.3s ease;
        line-height: 1.2;
    }

    .speciality-card:hover .speciality-title {
        color: #e74c3c;
    }

    .speciality-btn {
        background: rgba(255, 255, 255, 0.9);
        border: 2px solid transparent;
        border-radius: 15px;
        padding: 4px 10px;
        font-size: 0.65rem;
        font-weight: 600;
        color: #e74c3c;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .speciality-btn i {
        font-size: 0.7rem;
        transition: transform 0.3s ease;
    }

    .speciality-card:hover .speciality-btn {
        background: #e74c3c;
        color: white;
        border-color: #e74c3c;
    }

    .speciality-card:hover .speciality-btn i {
        transform: scale(1.2);
    }

    .hospital-mini-card .speciality-icon i {
        font-size: 1.15rem;
        color: #e74c3c;
    }

    .hospital-mini-location {
        color: #5f6f81;
        font-size: 0.72rem;
        font-weight: 500;
        margin-bottom: 8px;
        line-height: 1.25;
    }

    .hospital-mini-location i,
    .hospital-mini-rating i {
        margin-right: 4px;
    }

    .hospital-mini-rating {
        color: #2c3e50;
        font-size: 0.7rem;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .doctor-mini-card .speciality-card-body {
        border: 1px solid rgba(79, 172, 254, 0.15);
        padding: 0;
        overflow: hidden;
    }

    .doctor-mini-media {
        height: 140px;
        background: linear-gradient(135deg, rgba(79, 172, 254, 0.16), rgba(103, 114, 229, 0.16));
        overflow: hidden;
    }

    .doctor-mini-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .doctor-mini-content {
        padding: 14px 10px 15px;
    }

    .doctor-mini-icon-wrap {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.55);
    }

    .doctor-mini-icon {
        font-size: 2.2rem;
        color: #3f7edb;
    }

    .doctor-mini-specialty {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        margin-bottom: 10px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.9);
        color: #315f97;
        font-size: 0.62rem;
        font-weight: 700;
        text-transform: uppercase;
        line-height: 1.2;
    }

    .doctor-mini-specialty i {
        color: #e74c3c;
    }

    .doctor-mini-meta {
        color: #5f6f81;
        font-size: 0.72rem;
        font-weight: 500;
        margin-bottom: 8px;
        line-height: 1.25;
    }

    .doctor-mini-meta i,
    .doctor-mini-rating i {
        margin-right: 4px;
    }

    .doctor-mini-rating {
        color: #2c3e50;
        font-size: 0.7rem;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .doctor-mini-card .speciality-btn {
        color: #315f97;
    }

    .doctor-mini-card:hover .speciality-btn {
        background: #315f97;
        border-color: #315f97;
    }

    .service-mini-card .speciality-card-body {
        border: 1px solid rgba(79, 172, 254, 0.12);
        padding: 0;
        overflow: hidden;
    }

    .service-mini-media {
        height: 140px;
        background: linear-gradient(135deg, rgba(79, 172, 254, 0.16), rgba(103, 114, 229, 0.16));
        overflow: hidden;
    }

    .service-mini-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .service-mini-icon-wrap {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.55);
    }

    .service-mini-icon {
        font-size: 2.2rem;
        color: #3f7edb;
    }

    .service-mini-content {
        padding: 14px 10px 15px;
    }

    .service-mini-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        margin-bottom: 10px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.9);
        color: #315f97;
        font-size: 0.62rem;
        font-weight: 700;
        text-transform: uppercase;
        line-height: 1.2;
    }

    .service-mini-badge i {
        color: #e74c3c;
    }

    .service-mini-meta {
        color: #5f6f81;
        font-size: 0.72rem;
        font-weight: 500;
        margin-bottom: 8px;
        line-height: 1.3;
    }

    .service-mini-meta i,
    .service-mini-rating i {
        margin-right: 4px;
    }

    .service-mini-rating {
        color: #2c3e50;
        font-size: 0.7rem;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .lab-mini-card .service-mini-badge {
        color: #136f63;
    }

    .lab-mini-card .service-mini-badge i,
    .lab-mini-card .service-mini-icon {
        color: #136f63;
    }

    .lab-mini-card:hover .speciality-btn {
        background: #136f63;
        border-color: #136f63;
    }

    .blood-mini-card .service-mini-badge {
        color: #c0392b;
    }

    .blood-mini-card .service-mini-badge i,
    .blood-mini-card .service-mini-icon {
        color: #c0392b;
    }

    .blood-mini-card:hover .speciality-btn {
        background: #c0392b;
        border-color: #c0392b;
    }

    @media (max-width: 992px) {
        .speciality-card-body {
            padding: 12px 8px;
        }
        
        .speciality-icon {
            width: 40px;
            height: 40px;
        }
        
        .speciality-emoji {
            font-size: 1.2rem;
        }
        
        .speciality-title {
            font-size: 0.75rem;
        }
        
        .speciality-btn {
            padding: 3px 8px;
            font-size: 0.6rem;
        }
    }

    @media (max-width: 768px) {
        .speciality-card-body {
            padding: 12px 6px;
        }
        
        .speciality-icon {
            width: 35px;
            height: 35px;
        }
        
        .speciality-emoji {
            font-size: 1rem;
        }
        
        .speciality-title {
            font-size: 0.7rem;
        }

        .hospital-mini-location,
        .hospital-mini-rating {
            font-size: 0.65rem;
        }

        .doctor-mini-specialty {
            font-size: 0.58rem;
            padding: 4px 8px;
        }

        .doctor-mini-media {
            height: 120px;
        }

        .doctor-mini-content {
            padding: 12px 8px 14px;
        }

        .service-mini-media {
            height: 120px;
        }

        .service-mini-content {
            padding: 12px 8px 14px;
        }

        .doctor-mini-meta,
        .doctor-mini-rating,
        .service-mini-meta,
        .service-mini-rating {
            font-size: 0.65rem;
        }

        .service-mini-badge {
            font-size: 0.58rem;
            padding: 4px 8px;
        }
        
        .speciality-btn {
            padding: 3px 6px;
            font-size: 0.55rem;
        }
    }
   
</style>

    <!-- Navbar -->
    <?php include BASE_PATH.'/includes/menu.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-background">
            <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/hosp.jpg" alt="Healthcare" class="hero-image">
            <div class="hero-overlay"></div>
        </div>
        <div class="hero-content">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-7">
                        <span class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill" data-aos="fade-down">WELCOME TO DOCTORAPP</span>
                        <h1 class="hero-title" data-aos="fade-up" data-aos-delay="200">We Care About <br>Your Health</h1>
                        <p class="hero-text" data-aos="fade-up" data-aos-delay="400">Experience world-class healthcare with our team of expert doctors and state-of-the-art facilities designed for your comfort and recovery.</p>
                        <div data-aos="fade-up" data-aos-delay="600">
                            <div class="hero-city-wrap me-3">
                                <i class="fas fa-map-marker-alt hero-city-icon"></i>
                                <select class="hero-city-select" id="heroCitySelect">
                                    <option value="">Select City</option>
                                    <?php
                                    $hero_cities_query = "SELECT city_id, city_name FROM cities ORDER BY city_name ASC";
                                    $hero_cities_result = mysqli_query($con, $hero_cities_query);
                                    if ($hero_cities_result && mysqli_num_rows($hero_cities_result) > 0) {
                                        while ($hero_city = mysqli_fetch_assoc($hero_cities_result)) {
                                            $hero_selected = ($hero_city['city_id'] == $city_id) ? 'selected' : '';
                                            echo '<option value="' . $hero_city['city_id'] . '" ' . $hero_selected . '>' . htmlspecialchars($hero_city['city_name']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <a href="about" class="btn btn-outline-light rounded-pill px-4 fw-bold d-inline-block mob-layout">Learn More</a>
                        </div>
                    </div>
                    <div class="col-lg-5" data-aos="fade-left" data-aos-delay="400">
                        <div class="hero-form">
                            <h4 class="mb-4 text-dark fw-bold">Find Your Doctor</h4>
                            <form action="doctors" method="GET" class="search-form">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Select City</label>
                                        <select class="form-select" name="city" required id="cityselect">
                                            <option value="">Choose City</option>
                                            <?php
                                            $cities_query = "SELECT * FROM cities ORDER BY city_name ASC";
                                            $cities_result = mysqli_query($con, $cities_query);
                                            if ($cities_result && mysqli_num_rows($cities_result) > 0) {
                                                while($city = mysqli_fetch_assoc($cities_result)) {
                                                    $selected = ($city['city_id'] == $city_id) ? 'selected' : '';
                                                    echo '<option value="' . $city['city_id'] . '" ' . $selected . '>' . htmlspecialchars($city['city_name']) . '</option>';
                                                }
                                            } else {
                                                echo '<option value="" disabled>No Record Found</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Search Doctor</label>
                                        <input type="text" class="form-control" name="search" placeholder="Enter doctor name..." required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold mt-3">
                                    <i class="fas fa-search me-2"></i>Search Doctors
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Tabs Section -->
    <section id="top-services" class="section-padding" style="background: linear-gradient(135deg, #f8f9ff 0%, #e8f4fd 100%);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color">Our Healthcare Services</h2>
                <p class="section-subtitle normal-color">Comprehensive medical care at your fingertips</p>
            </div>
            <div class="row g-4 justify-content-center" data-aos="fade-up" data-aos-delay="200">
                <div class="col-lg-3 col-md-6">
                    <a href="<?=BASE_URL?>hospitals" class="service-card text-decoration-none">
                        <div class="service-card-body">
                            <div class="service-icon-wrapper">
                                <i class="fas fa-hospital"></i>
                            </div>
                            <h4 class="service-title">Hospitals</h4>
                            <p class="service-description">Find top-rated hospitals with advanced medical facilities</p>
                            <div class="service-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="<?=BASE_URL?>doctors" class="service-card text-decoration-none">
                        <div class="service-card-body">
                            <div class="service-icon-wrapper">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <h4 class="service-title">Doctors</h4>
                            <p class="service-description">Connect with experienced medical specialists</p>
                            <div class="service-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="<?=BASE_URL?>labs" class="service-card text-decoration-none">
                        <div class="service-card-body">
                            <div class="service-icon-wrapper">
                                <i class="fas fa-flask"></i>
                            </div>
                            <h4 class="service-title">Laboratories</h4>
                            <p class="service-description">Advanced diagnostic and testing services</p>
                            <div class="service-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="<?=BASE_URL?>blood-banks" class="service-card text-decoration-none">
                        <div class="service-card-body">
                            <div class="service-icon-wrapper">
                                <i class="fas fa-tint"></i>
                            </div>
                            <h4 class="service-title">Blood Banks</h4>
                            <p class="service-description">Life-saving blood donation and services</p>
                            <div class="service-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Specialities Section -->
    <section class="section-padding" style="background: linear-gradient(135deg, #e8f4fd 0%, #d4eafc 100%);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color"><i class="fas fa-stethoscope me-3"></i>Specialities</h2>
                <p class="section-subtitle normal-color">Explore our specialized medical departments</p>
            </div>
            <div class="row g-3 justify-content-center" data-aos="fade-up" data-aos-delay="200">
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="speciality-card">
                        <div class="speciality-card-body" style="background-color: #fce4ec;">
                            <div class="speciality-icon">
                                <span class="speciality-emoji">👴</span>
                            </div>
                            <h4 class="speciality-title">Pulmonologist (Lungs)</h4>
                            <a href="<?=BASE_URL?>doctors?search=&city=<?=$city_id?>&hospital=&specialization=3" class="speciality-btn">
                                <i class="fas fa-heart"></i>
                                DEPARTMENT
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="speciality-card">
                        <div class="speciality-card-body" style="background-color: #ffebee;">
                            <div class="speciality-icon">
                                <span class="speciality-emoji">❤️</span>
                            </div>
                            <h4 class="speciality-title">Cardiology</h4>
                            <a href="<?=BASE_URL?>doctors?search=&city=<?=$city_id?>&hospital=&specialization=1" class="speciality-btn">
                                <i class="fas fa-heart"></i>
                                DEPARTMENT
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="speciality-card">
                        <div class="speciality-card-body" style="background-color: #f3e5f5;">
                            <div class="speciality-icon">
                                <span class="speciality-emoji">🧠</span>
                            </div>
                            <h4 class="speciality-title">Nephrologist (Kidney)</h4>
                            <a href="<?=BASE_URL?>doctors?search=&city=<?=$city_id?>&hospital=&specialization=5" class="speciality-btn">
                                <i class="fas fa-heart"></i>
                                DEPARTMENT
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="speciality-card">
                        <div class="speciality-card-body" style="background-color: #fff9c4;">
                            <div class="speciality-icon">
                                <span class="speciality-emoji">🧪</span>
                            </div>
                            <h4 class="speciality-title">Gynecologist</h4>
                            <a href="<?=BASE_URL?>doctors?search=&city=<?=$city_id?>&hospital=&specialization=20" class="speciality-btn">
                                <i class="fas fa-heart"></i>
                                DEPARTMENT
                                        </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="speciality-card">
                        <div class="speciality-card-body" style="background-color: #e1f5fe;">
                            <div class="speciality-icon">
                                <span class="speciality-emoji">🫁</span>
                            </div>
                            <h4 class="speciality-title">Psychiatrist</h4>
                            <a href="<?=BASE_URL?>doctors?search=&city=<?=$city_id?>&hospital=&specialization=25" class="speciality-btn">
                                <i class="fas fa-heart"></i>
                                DEPARTMENT
                                        </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="speciality-card">
                        <div class="speciality-card-body" style="background-color: #e0f2f1;">
                            <div class="speciality-icon">
                                <span class="speciality-emoji">🫀</span>
                            </div>
                            <h4 class="speciality-title">ENT Specialist</h4>
                            <a href="<?=BASE_URL?>doctors?search=&city=<?=$city_id?>&hospital=&specialization=66" class="speciality-btn">
                                <i class="fas fa-heart"></i>
                                DEPARTMENT
                                        </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-4" data-aos="fade-up">
                <a href="doctors" class="btn-view-all btn-view-specialities">
                    <i class="fas fa-stethoscope"></i>
                    Explore All Specialities
                </a>
            </div>
    </section>

    <!-- About Section -->
    <!-- <section id="about" class="section-padding" style="background-color: var(--bg-light);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color">About DoctorApp</h2>
                <p class="section-subtitle normal-color">Your trusted healthcare platform connecting you with the best medical professionals and facilities</p>
            </div>
            <div class="row">
                <div class="col-lg-6" data-aos="fade-right">
                    <img src="<?=BASE_URL?>includes/uploads/home-about.png" alt="About Us img" class="img-fluid about-us-img">
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <h3 class="mb-4 heading-color mgt">Your Health, Our Priority</h3>
                    <p class="mb-4 normal-color">DoctorApp is a comprehensive healthcare platform designed to make medical services accessible to everyone. We connect patients with qualified doctors, hospitals, laboratories, and blood banks across the country.</p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="service-icon me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                    <i class="fas fa-user-md"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 heading-color">Expert Doctors</h5>
                                    <p class="mb-0 text-muted normal-color">Qualified medical professionals</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="service-icon me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                    <i class="fas fa-hospital"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 heading-color">Top Hospitals</h5>
                                    <p class="mb-0 text-muted normal-color">Best medical facilities</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="service-icon me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                    <i class="fas fa-flask"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 heading-color">Laboratories</h5>
                                    <p class="mb-0 text-muted normal-color">Advanced diagnostic services</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="service-icon me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                    <i class="fas fa-tint"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 heading-color">Blood Banks</h5>
                                    <p class="mb-0 text-muted normal-color">Life-saving blood services</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section> -->

    <!-- Top Hospitals Section -->
    <section class="modern-cards section-padding" style="background-color: var(--light);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color"><i class="fas fa-hospital-alt me-3"></i>Top Hospitals</h2>
                <p class="section-subtitle normal-color">Find the best healthcare facilities in your area</p>
            </div>
            <div class="row g-3 justify-content-center">
                <?php
                $hospital_card_colors = ['#fce4ec', '#ffebee', '#f3e5f5', '#fff9c4', '#e1f5fe', '#e0f2f1'];
                if($city_id > 0){
                    $hospitals_query = "SELECT h.*, c.city_name FROM hospitals h LEFT JOIN cities c ON h.city_id = c.city_id LEFT JOIN entities e ON e.entity_id = h.entity_id WHERE e.status = 1 AND h.approve = 1 AND h.city_id = $city_id ORDER BY h.hospital_id DESC LIMIT 6";
                }else{
                    $hospitals_query = "SELECT h.*, c.city_name FROM hospitals h LEFT JOIN cities c ON h.city_id = c.city_id LEFT JOIN entities e ON e.entity_id = h.entity_id WHERE e.status = 1 AND h.approve = 1 ORDER BY h.hospital_id DESC LIMIT 6";
                }
                  
                 $hospitals_result = mysqli_query($con, $hospitals_query);
                $hospital_index = 0;
                $has_hospitals = $hospitals_result && mysqli_num_rows($hospitals_result) > 0;
                if ($has_hospitals) {
                while($hospital = mysqli_fetch_assoc($hospitals_result)) {
                    $hosp_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE entity_id='". $hospital['entity_id'] ."'");
                    $hosp_stars = mysqli_fetch_assoc($hosp_stars_q);
                    $hosp_stars = $hosp_stars['stars'];
                    $hospital_rating = $hosp_stars ? number_format((float)$hosp_stars, 1) : 'New';
                    $hospital_bg = $hospital_card_colors[$hospital_index % count($hospital_card_colors)];
                    $hospital_index++;
                ?>
                <div class="col-lg-2 col-md-4 col-6" data-aos="fade-up" data-aos-delay="<?php echo 100 + ($hospital_index * 80); ?>">
                    <div class="speciality-card hospital-mini-card">
                        <div class="speciality-card-body" style="background-color: <?php echo $hospital_bg; ?>;">
                            <div class="speciality-icon">
                                <i class="fas fa-hospital-alt"></i>
                            </div>
                            <h4 class="speciality-title"><?php echo htmlspecialchars(substr($hospital['hospital_name'], 0, 25)); ?></h4>
                            <p class="hospital-mini-location">
                                <i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($hospital['city_name']); ?>
                            </p>
                            <div class="hospital-mini-rating">
                                <i class="fas fa-star text-warning"></i><?php echo $hospital_rating; ?> Rating
                            </div>
                            <a href="hospital-detail?hospital_id=<?php echo $hospital['hospital_id']; ?>" class="speciality-btn">
                                <i class="fas fa-arrow-right"></i>
                                DETAILS
                            </a>
                        </div>
                    </div>
                </div>
                <?php }
                } else { ?>
                <div class="col-12">
                    <div class="no-record-found">No Record Found</div>
                </div>
                <?php } ?>
            </div>
            <?php if ($has_hospitals): ?>
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="hospitals" class="btn-view-all btn-view-hospitals">
                    <i class="fas fa-hospital-alt"></i>
                    View All Hospitals
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Top Doctors Section -->
    <section class="modern-cards section-padding" style="background-color: var(--bg-light);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color">Top Doctors</h2>
                <p class="section-subtitle normal-color">Connect with experienced medical professionals</p>
            </div>
            <div class="row g-3 justify-content-center">
                <?php
                $doctor_card_colors = ['#eef6ff', '#f3efff', '#eefbf5', '#fff6e8', '#fceef3', '#edf8ff'];
                if($city_id > 0){
                     $doctors_query = "SELECT d.*, c.city_name, dct.type as specialization FROM doctors d LEFT JOIN cities c ON d.city_id = c.city_id LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_id LEFT JOIN entities e ON d.entity_id = e.entity_id WHERE e.status = 1 AND d.approve=1 AND d.city_id=$city_id ORDER BY d.doctor_id DESC LIMIT 4";
                }else{
                     $doctors_query = "SELECT d.*, c.city_name, dct.type as specialization FROM doctors d LEFT JOIN cities c ON d.city_id = c.city_id LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_id LEFT JOIN entities e ON d.entity_id = e.entity_id WHERE e.status = 1 AND d.approve=1 ORDER BY d.doctor_id DESC LIMIT 6";
                }
                
                $doctors_result = mysqli_query($con, $doctors_query);
                $doctor_index = 0;
                $has_doctors = $doctors_result && mysqli_num_rows($doctors_result) > 0;
                if ($has_doctors) {
                while($doctor = mysqli_fetch_assoc($doctors_result)) {
                    $doct_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE entity_id='". $doctor['entity_id'] ."'");
                    $doct_stars = mysqli_fetch_assoc($doct_stars_q);
                    $doct_stars = $doct_stars['stars'];
                    $doctor_index++;
                    $doctor_rating = $doct_stars ? number_format((float)$doct_stars, 1) : 'New';
                    $doctor_specialization = !empty($doctor['specialization']) ? $doctor['specialization'] : 'Medical Specialist';
                    $doctor_bg = $doctor_card_colors[($doctor_index - 1) % count($doctor_card_colors)];
                ?>
                <div class="col-lg-2 col-md-3 col-6" data-aos="fade-up" data-aos-delay="<?php echo 100 + ($doctor_index * 80); ?>">
                    <div class="speciality-card doctor-mini-card">
                        <div class="speciality-card-body" style="background-color: <?php echo $doctor_bg; ?>;">
                            <div class="doctor-mini-media">
                                <?php if (!empty($doctor['doctor_pic'])): ?>
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" 
                                        alt="<?php echo $doctor['doctor_name']; ?>" class="img-fluid">
                                <?php else: ?>
                                    <div class="doctor-mini-icon-wrap">
                                        <i class="fas fa-user-md doctor-mini-icon"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="doctor-mini-content">
                                <div class="doctor-mini-specialty">
                                    <i class="fas fa-user-md"></i><?php echo htmlspecialchars($doctor_specialization); ?>
                                </div>
                                <h4 class="speciality-title"><?php echo htmlspecialchars($doctor['doctor_name']); ?></h4>
                                <div class="doctor-mini-meta">
                                    <i class="fas fa-location-dot"></i><?php echo htmlspecialchars($doctor['city_name']); ?>
                                </div>
                                <div class="doctor-mini-rating">
                                    <i class="fas fa-star text-warning"></i><?php echo $doctor_rating; ?> Rating
                                </div>
                                <a href="doctor-detail?doctor_id=<?php echo $doctor['doctor_id']; ?>" class="speciality-btn">
                                    <i class="fas fa-stethoscope"></i>
                                    PROFILE
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php }
                } else { ?>
                <div class="col-12">
                    <div class="no-record-found">No Record Found</div>
                </div>
                <?php } ?>
            </div>
            <?php if ($has_doctors): ?>
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="doctors" class="btn-view-all btn-view-doctors">
                    <i class="fas fa-user-md"></i>
                    View All Doctors
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Laboratories Section -->
    <section class="modern-cards section-padding" style="background-color: var(--light);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color">Laboratories</h2>
                <p class="section-subtitle normal-color">Advanced diagnostic and testing services</p>
            </div>
            <div class="row g-3 justify-content-center">
                <?php
                $lab_card_colors = ['#e8fff9', '#eef8ff', '#f3efff', '#eefbf5', '#fff6e8', '#edf8ff'];
                if($city_id > 0){
                    $labs_query = "SELECT l.*, c.city_name FROM laboratories l LEFT JOIN cities c ON l.city_id = c.city_id LEFT JOIN entities e ON e.entity_id = l.entity_id WHERE e.status AND l.approve = 1 AND l.city_id=$city_id ORDER BY l.lab_id DESC LIMIT 6";
                }else{
                    $labs_query = "SELECT l.*, c.city_name FROM laboratories l LEFT JOIN cities c ON l.city_id = c.city_id LEFT JOIN entities e ON e.entity_id = l.entity_id WHERE e.status AND l.approve = 1 ORDER BY l.lab_id DESC LIMIT 6";
                }
                
                $labs_result = mysqli_query($con, $labs_query);
                $lab_index = 0;
                $has_labs = $labs_result && mysqli_num_rows($labs_result) > 0;
                if ($has_labs) {
                while($lab = mysqli_fetch_assoc($labs_result)) {
                    $lab_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE entity_id='". $lab['entity_id'] ."'");
                    $lab_stars = mysqli_fetch_assoc($lab_stars_q);
                    $lab_stars = $lab_stars['stars'];
                    $lab_index++;
                    $lab_rating = $lab_stars ? number_format((float)$lab_stars, 1) : 'New';
                    $lab_bg = $lab_card_colors[($lab_index - 1) % count($lab_card_colors)];
                ?>
                <div class="col-lg-2 col-md-4 col-6" data-aos="fade-up" data-aos-delay="<?php echo 100 + ($lab_index * 80); ?>">
                    <div class="speciality-card service-mini-card lab-mini-card">
                        <div class="speciality-card-body" style="background-color: <?php echo $lab_bg; ?>;">
                            <div class="service-mini-media">
                                <?php if (!empty($lab['lab_pic'])): ?>
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/labs/<?php echo $lab['lab_pic']; ?>" 
                                        alt="<?php echo $lab['lab_name']; ?>" class="img-fluid">
                                <?php else: ?>
                                    <div class="service-mini-icon-wrap">
                                        <i class="fas fa-flask service-mini-icon"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="service-mini-content">
                                <div class="service-mini-badge">
                                    <i class="fas fa-flask"></i>LABORATORY
                                </div>
                                <h4 class="speciality-title"><?php echo htmlspecialchars($lab['lab_name']); ?></h4>
                                <div class="service-mini-meta">
                                    <i class="fas fa-location-dot"></i><?php echo htmlspecialchars($lab['city_name']); ?>
                                </div>
                                <div class="service-mini-rating">
                                    <i class="fas fa-star text-warning"></i><?php echo $lab_rating; ?> Rating
                                </div>
                                <a href="lab-detail?lab_id=<?php echo $lab['lab_id']; ?>" class="speciality-btn">
                                    <i class="fas fa-vial"></i>
                                    DETAILS
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php }
                } else { ?>
                <div class="col-12">
                    <div class="no-record-found">No Record Found</div>
                </div>
                <?php } ?>
            </div>
            <?php if ($has_labs): ?>
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="labs" class="btn-view-all btn-view-labs">
                    <i class="fas fa-flask"></i>
                    View All Laboratories
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Blood Banks Section -->
    <section class="modern-cards section-padding" style="background-color: var(--bg-light);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color">Blood Banks</h2>
                <p class="section-subtitle normal-color">Life-saving blood donation and supply services</p>
            </div>
            <div class="row g-3 justify-content-center">
                <?php
                $blood_card_colors = ['#fff1f1', '#fff6e8', '#fceef3', '#eef6ff', '#f3efff', '#edf8ff'];
                if($city_id > 0){
                    $blood_banks_query = "SELECT bb.*, c.city_name FROM blood_bank bb LEFT JOIN cities c ON bb.city_id = c.city_id LEFT JOIN entities e ON e.entity_id = bb.entity_id WHERE e.status = 1 AND bb.approve = 1 AND bb.city_id=$city_id ORDER BY bb.bb_id DESC LIMIT 6";
                }else{
                    $blood_banks_query = "SELECT bb.*, c.city_name FROM blood_bank bb LEFT JOIN cities c ON bb.city_id = c.city_id LEFT JOIN entities e ON e.entity_id = bb.entity_id WHERE e.status = 1 AND bb.approve = 1 ORDER BY bb.bb_id DESC LIMIT 6";
                }
                
                $blood_banks_result = mysqli_query($con, $blood_banks_query);
                $blood_index = 0;
                $has_blood_banks = $blood_banks_result && mysqli_num_rows($blood_banks_result) > 0;
                if ($has_blood_banks) {
                while($blood_bank = mysqli_fetch_assoc($blood_banks_result)) {
                    $bb_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE entity_id='". $blood_bank['entity_id'] ."'");
                    $bb_stars = mysqli_fetch_assoc($bb_stars_q);
                    $bb_stars = $bb_stars['stars'];
                    $blood_index++;
                    $blood_rating = $bb_stars ? number_format((float)$bb_stars, 1) : 'New';
                    $blood_bg = $blood_card_colors[($blood_index - 1) % count($blood_card_colors)];
                ?>
                <div class="col-lg-2 col-md-4 col-6" data-aos="fade-up" data-aos-delay="<?php echo 100 + ($blood_index * 80); ?>">
                    <div class="speciality-card service-mini-card blood-mini-card">
                        <div class="speciality-card-body" style="background-color: <?php echo $blood_bg; ?>;">
                            <div class="service-mini-media">
                                <?php if (!empty($blood_bank['bb_pic'])): ?>
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/blood-banks/<?php echo $blood_bank['bb_pic']; ?>" 
                                        alt="<?php echo $blood_bank['bb_name']; ?>" class="img-fluid">
                                <?php else: ?>
                                    <div class="service-mini-icon-wrap">
                                        <i class="fas fa-tint service-mini-icon"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="service-mini-content">
                                <div class="service-mini-badge">
                                    <i class="fas fa-tint"></i>BLOOD BANK
                                </div>
                                <h4 class="speciality-title"><?php echo htmlspecialchars($blood_bank['bb_name']); ?></h4>
                                <div class="service-mini-meta">
                                    <i class="fas fa-location-dot"></i><?php echo htmlspecialchars($blood_bank['city_name']); ?>
                                </div>
                                <div class="service-mini-rating">
                                    <i class="fas fa-star text-warning"></i><?php echo $blood_rating; ?> Rating
                                </div>
                                <a href="blood-bank-detail?blood_bankid=<?php echo $blood_bank['bb_id']; ?>" class="speciality-btn">
                                    <i class="fas fa-droplet"></i>
                                    DETAILS
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php }
                } else { ?>
                <div class="col-12">
                    <div class="no-record-found">No Record Found</div>
                </div>
                <?php } ?>
            </div>
            <?php if ($has_blood_banks): ?>
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="blood-banks" class="btn-view-all btn-view-blood">
                    <i class="fas fa-tint"></i>
                    View All Blood Banks
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="section-padding d-none" id="reviews" style="background-color: var(--light);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color">Patient Reviews</h2>
                <p class="section-subtitle normal-color">What our patients say about their experience</p>
            </div>
            
            <!-- Reviews Carousel -->
            <div id="reviewsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000" data-aos="fade-up" data-aos-delay="200">
                <div class="carousel-inner">
                    <?php
                    $reviews_query = "SELECT dr.* FROM feedback dr WHERE dr.status = 1 and entity_id=1 ORDER BY dr.feedback_id DESC LIMIT 6";
                    $reviews_result = mysqli_query($con, $reviews_query);
                    $reviews_array = [];
                    while($review = mysqli_fetch_assoc($reviews_result)) {
                        $reviews_array[] = $review;
                    }
                    
                    // Group reviews in sets of 3
                    $grouped_reviews = array_chunk($reviews_array, 3);
                    foreach($grouped_reviews as $index => $review_group) {
                        $active_class = $index == 0 ? 'active' : '';
                    ?>
                    <div class="carousel-item <?php echo $active_class; ?>">
                        <div class="row">
                            <?php foreach($review_group as $review) { ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="review-card text-center h-100">
                                    <div class="review-header justify-content-center">
                                        <div class="review-avatar">
                                            <?php echo strtoupper(substr($review['commenter_name'], 0, 1)); ?>
                                        </div>
                                        <div class="review-info">
                                            <h5><?php echo htmlspecialchars($review['commenter_name']); ?></h5>
                                            <div class="review-rating">
                                                <?php for($i = 1; $i <= 5; $i++) { ?>
                                                    <i class="fas fa-star <?php echo $i <= $review['stars'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="review-text">"<?php echo htmlspecialchars($review['comment']); ?>"</p>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                
                <!-- Carousel Controls -->
                <button class="carousel-control-prev" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                
                <!-- Carousel Indicators -->
                <div class="carousel-indicators">
                    <?php 
                    $total_groups = count($grouped_reviews);
                    for($i = 0; $i < $total_groups; $i++) { 
                        $active_class = $i == 0 ? 'active' : '';
                    ?>
                    <button type="button" data-bs-target="#reviewsCarousel" data-bs-slide-to="<?php echo $i; ?>" class="<?php echo $active_class; ?>" aria-current="true" aria-label="Slide <?php echo $i + 1; ?>"></button>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include BASE_PATH.'/includes/footer.php';?>
<script>
$(document).ready(function() {
    $('#cityselect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search city...',
        allowClear: true,
        width: '100%'
    });

    $('#heroCitySelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select City',
        allowClear: false,
        width: '100%',
        minimumResultsForSearch: 0,
        dropdownCssClass: 'hero-city-dropdown'
    }).on('change', function() {
        var cityId = $(this).val();
        if (!cityId) return;

        fetch('<?php echo BASE_URL; ?>includes/set-city.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ city_id: parseInt(cityId) })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                location.reload();
            }
        });
    });
});
</script>
