<?php 
include 'inc/site_header.php'; 
$doctors_query = $pdo->query("SELECT * FROM doctors d
  LEFT JOIN users u ON d.user_id = u.id 
WHERE u.status = 1");
$doctors = $doctors_query->fetchAll();
?>
<body>
  <div class="page-shell">
    <?php include 'inc/home_menu.php'; ?>

    <main>
      <section class="hero-section" id="home">
        <div class="container-fluid">
          <div class="row align-items-center gy-5">
            <div class="col-lg-5">
              <span class="section-kicker">Welcome to Hhour</span>
              <h1 class="hero-title">Reliable Home Health Care Services</h1>
              <p class="hero-text">
                We provide trusted doctors, nursing staff and home medical support to help families
                receive compassionate healthcare with comfort and confidence.
              </p>
              <div class="hero-buttons">
                <a href="#services" class="btn btn-primary">Get Started</a>
                <a href="#about" class="btn btn-outline-primary">Learn More</a>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="hero-visual">
                <div class="hero-badge">
                  <span>24/7</span>
                  <strong>Emergency Support</strong>
                </div>
                <img src="https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&w=1200&q=80" alt="Home healthcare support">
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="service-strip">
        <div class="container">
          <div class="row g-4">
            <div class="col-md-6 col-lg-3">
              <article class="mini-card fade-in-up">
                <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=800&q=80" alt="Home appointment">
                <div class="mini-card-body">
                  <h3>Home Appointments</h3>
                  <p>Experienced professionals at your doorstep.</p>
                </div>
              </article>
            </div>
            <div class="col-md-6 col-lg-3">
              <article class="mini-card fade-in-up">
                <img src="https://images.unsplash.com/photo-1586773860418-d37222d8fce3?auto=format&fit=crop&w=800&q=80" alt="Ambulance service">
                <div class="mini-card-body">
                  <h3>Emergency Mobile Care</h3>
                  <p>Quick response and patient transport support.</p>
                </div>
              </article>
            </div>
            <div class="col-md-6 col-lg-3">
              <article class="mini-card fade-in-up">
                <img src="<?= BASE_URL; ?>/assets/img/checkup.png" alt="Medical checkup">
                <div class="mini-card-body">
                  <h3>Medical Checkups</h3>
                  <p>Routine monitoring with modern care standards.</p>
                </div>
              </article>
            </div>
            <div class="col-md-6 col-lg-3">
              <article class="mini-card fade-in-up">
                <img src="https://images.unsplash.com/photo-1579154204601-01588f351e67?auto=format&fit=crop&w=800&q=80" alt="Blood pressure monitoring">
                <div class="mini-card-body">
                  <h3>Patient Monitoring</h3>
                  <p>Safe and continuous observation for recovery.</p>
                </div>
              </article>
            </div>
          </div>
        </div>
      </section>

      <section class="feature-section">
        <div class="container">
          <div class="row align-items-center gy-5">
            <div class="col-lg-4">
              <span class="section-kicker">About Us</span>
              <h2 class="section-title">Exceptional in healthcare</h2>
              <p class="section-text">
                Our team combines medical excellence with family-centered care to make healing easier
                at home.
              </p>
              <a href="#contact" class="btn btn-primary">Contact Us</a>
            </div>
            <div class="col-lg-8">
              <div class="feature-media">
                <img src="https://images.unsplash.com/photo-1607619056574-7b8d3ee536b2?auto=format&fit=crop&w=1200&q=80" alt="Doctor consultation">
                <div class="video-pill">
                  <button class="play-btn" type="button" data-bs-toggle="modal" data-bs-target="#videoModal">
                    <i class="bi bi-play-fill"></i>
                  </button>
                  <div>
                    <small>Watch introduction</small>
                    <strong>How home care works</strong>
                  </div>
                </div>
                <div class="review-card">
                  <div class="review-icon"><i class="bi bi-exclamation-circle-fill"></i></div>
                  <strong>Trusted service</strong>
                  <p class="mb-0">Professional home visits, nursing care and specialist support whenever your family needs it.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="gallery-section">
        <div class="container position-relative">
          <!-- <button class="slider-nav slider-prev" type="button" aria-label="Previous"><i class="bi bi-chevron-left"></i></button> -->
          <div class="row g-4 gallery-track">
            <div class="col-md-4">
              <img class="gallery-thumb" src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=900&q=80" alt="Healthcare equipment">
            </div>
            <div class="col-md-4">
              <img class="gallery-thumb" src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=900&q=80" alt="Patient care">
            </div>
            <div class="col-md-4">
              <img class="gallery-thumb" src="<?= BASE_URL; ?>/assets/img/checkup.png" alt="Doctor checking patient">
            </div>
          </div>
          <!-- <button class="slider-nav slider-next" type="button" aria-label="Next"><i class="bi bi-chevron-right"></i></button> -->
        </div>
      </section>

      <section class="blue-banner">
        <div class="container">
          <p>Need reliable home healthcare? We bring hospital-level support to the comfort of your home.</p>
        </div>
      </section>

      <section class="services-section" id="services">
        <div class="container">
          <div class="text-center section-heading">
            <span class="section-kicker">Departments</span>
            <h2 class="section-title">Our Departments</h2>
          </div>
          <div id="departmentsCarousel" class="carousel slide departments-carousel d-none d-md-block" data-bs-ride="carousel">
            <div class="carousel-inner">
              <div class="carousel-item active">
                <div class="row g-4 justify-content-center">
                  <div class="col-md-6 col-lg-4">
                    <article class="service-card service-card-yellow">
                      <div class="service-icon"><i class="bi bi-calendar2-check"></i></div>
                      <h3>Appointments</h3>
                      <p>Book consultations and on-site visits with certified professionals.</p>
                      <a href="#contact" class="btn btn-sm btn-outline-dark rounded-pill px-3">Learn More</a>
                    </article>
                  </div>
                  <div class="col-md-6 col-lg-4">
                    <article class="service-card service-card-blue">
                      <div class="service-icon"><i class="bi bi-hospital"></i></div>
                      <h3>Our Doctors</h3>
                      <p>Trusted physicians for in-home diagnosis, treatment plans and follow-ups.</p>
                      <a href="#doctors" class="btn btn-sm btn-outline-light rounded-pill px-3">Learn More</a>
                    </article>
                  </div>
                  <div class="col-md-6 col-lg-4">
                    <article class="service-card service-card-cyan">
                      <div class="service-icon"><i class="bi bi-geo-alt"></i></div>
                      <h3>Clinic Locator</h3>
                      <p>Connect patients to nearby lab, clinic and emergency assistance options.</p>
                      <a href="#contact" class="btn btn-sm btn-outline-dark rounded-pill px-3">Learn More</a>
                    </article>
                  </div>
                </div>
              </div>

              <div class="carousel-item">
                <div class="row g-4 justify-content-center">
                  <div class="col-md-6 col-lg-4">
                    <article class="service-card service-card-blue">
                      <div class="service-icon"><i class="bi bi-heart-pulse"></i></div>
                      <h3>Cardiology Care</h3>
                      <p>Heart health consultations, monitoring and follow-up plans at home.</p>
                      <a href="#contact" class="btn btn-sm btn-outline-light rounded-pill px-3">Learn More</a>
                    </article>
                  </div>
                  <div class="col-md-6 col-lg-4">
                    <article class="service-card service-card-yellow">
                      <div class="service-icon"><i class="bi bi-bandaid"></i></div>
                      <h3>Nursing Support</h3>
                      <p>Professional nursing assistance for recovery, wound care and elderly support.</p>
                      <a href="#contact" class="btn btn-sm btn-outline-dark rounded-pill px-3">Learn More</a>
                    </article>
                  </div>
                  <div class="col-md-6 col-lg-4">
                    <article class="service-card service-card-cyan">
                      <div class="service-icon"><i class="bi bi-activity"></i></div>
                      <h3>Diagnostics</h3>
                      <p>Routine tests and monitoring services to support accurate treatment decisions.</p>
                      <a href="#contact" class="btn btn-sm btn-outline-dark rounded-pill px-3">Learn More</a>
                    </article>
                  </div>
                </div>
              </div>
            </div>
            <div class="carousel-indicators departments-indicators">
              <button type="button" data-bs-target="#departmentsCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
              <button type="button" data-bs-target="#departmentsCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            </div>
            <button class="carousel-control-prev departments-control" type="button" data-bs-target="#departmentsCarousel" data-bs-slide="prev" aria-label="Previous">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next departments-control" type="button" data-bs-target="#departmentsCarousel" data-bs-slide="next" aria-label="Next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
          </div>

          <div id="departmentsCarouselMobile" class="carousel slide departments-carousel departments-carousel-mobile d-md-none" data-bs-ride="carousel">
            <div class="carousel-inner">
              <div class="carousel-item active">
                <article class="service-card service-card-yellow">
                  <div class="service-icon"><i class="bi bi-calendar2-check"></i></div>
                  <h3>Appointments</h3>
                  <p>Book consultations and on-site visits with certified professionals.</p>
                  <a href="#contact" class="btn btn-sm btn-outline-dark rounded-pill px-3">Learn More</a>
                </article>
              </div>
              <div class="carousel-item">
                <article class="service-card service-card-blue">
                  <div class="service-icon"><i class="bi bi-hospital"></i></div>
                  <h3>Our Doctors</h3>
                  <p>Trusted physicians for in-home diagnosis, treatment plans and follow-ups.</p>
                  <a href="#doctors" class="btn btn-sm btn-outline-light rounded-pill px-3">Learn More</a>
                </article>
              </div>
              <div class="carousel-item">
                <article class="service-card service-card-cyan">
                  <div class="service-icon"><i class="bi bi-geo-alt"></i></div>
                  <h3>Clinic Locator</h3>
                  <p>Connect patients to nearby lab, clinic and emergency assistance options.</p>
                  <a href="#contact" class="btn btn-sm btn-outline-dark rounded-pill px-3">Learn More</a>
                </article>
              </div>
              <div class="carousel-item">
                <article class="service-card service-card-blue">
                  <div class="service-icon"><i class="bi bi-heart-pulse"></i></div>
                  <h3>Cardiology Care</h3>
                  <p>Heart health consultations, monitoring and follow-up plans at home.</p>
                  <a href="#contact" class="btn btn-sm btn-outline-light rounded-pill px-3">Learn More</a>
                </article>
              </div>
              <div class="carousel-item">
                <article class="service-card service-card-yellow">
                  <div class="service-icon"><i class="bi bi-bandaid"></i></div>
                  <h3>Nursing Support</h3>
                  <p>Professional nursing assistance for recovery, wound care and elderly support.</p>
                  <a href="#contact" class="btn btn-sm btn-outline-dark rounded-pill px-3">Learn More</a>
                </article>
              </div>
              <div class="carousel-item">
                <article class="service-card service-card-cyan">
                  <div class="service-icon"><i class="bi bi-activity"></i></div>
                  <h3>Diagnostics</h3>
                  <p>Routine tests and monitoring services to support accurate treatment decisions.</p>
                  <a href="#contact" class="btn btn-sm btn-outline-dark rounded-pill px-3">Learn More</a>
                </article>
              </div>
            </div>

            <div class="carousel-indicators departments-indicators">
              <button type="button" data-bs-target="#departmentsCarouselMobile" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Department 1"></button>
              <button type="button" data-bs-target="#departmentsCarouselMobile" data-bs-slide-to="1" aria-label="Department 2"></button>
              <button type="button" data-bs-target="#departmentsCarouselMobile" data-bs-slide-to="2" aria-label="Department 3"></button>
              <button type="button" data-bs-target="#departmentsCarouselMobile" data-bs-slide-to="3" aria-label="Department 4"></button>
              <button type="button" data-bs-target="#departmentsCarouselMobile" data-bs-slide-to="4" aria-label="Department 5"></button>
              <button type="button" data-bs-target="#departmentsCarouselMobile" data-bs-slide-to="5" aria-label="Department 6"></button>
            </div>
          </div>
        </div>
      </section>

      <section class="about-section" id="about">
        <div class="container">
          <div class="row align-items-center gy-5">
            <div class="col-lg-5">
              <div class="about-stack">
                <img class="about-main" src="https://images.unsplash.com/photo-1631815589968-fdb09a223b1e?auto=format&fit=crop&w=1000&q=80" alt="Modern hospital room">
                <img class="about-small" src="https://images.unsplash.com/photo-1581056771107-24ca5f033842?auto=format&fit=crop&w=900&q=80" alt="Nurse helping patient">
                <div class="about-badge">
                  <i class="bi bi-shield-check"></i>
                  <span>Safe Care</span>
                </div>
              </div>
            </div>
            <div class="col-lg-7">
              <span class="section-kicker">About Team</span>
              <h2 class="section-title">Having an in-house team of expert.</h2>
              <p class="section-text">
                We offer complete home healthcare support backed by trained nurses, specialist doctors
                and patient-first service coordination.
              </p>
              <div class="row g-3 about-list">
                <div class="col-sm-6">
                  <div class="about-list-item"><i class="bi bi-check-circle-fill"></i>Qualified staff</div>
                </div>
                <div class="col-sm-6">
                  <div class="about-list-item"><i class="bi bi-check-circle-fill"></i>24/7 availability</div>
                </div>
                <div class="col-sm-6">
                  <div class="about-list-item"><i class="bi bi-check-circle-fill"></i>Trusted equipment</div>
                </div>
                <div class="col-sm-6">
                  <div class="about-list-item"><i class="bi bi-check-circle-fill"></i>Easy follow-up support</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="doctors-section" id="doctors">
        <div class="container">
          <div class="row align-items-end justify-content-between gy-3 section-heading text-white">
            <div class="col-lg-5">
              <span class="section-kicker text-warning">Specialist</span>
              <h2 class="section-title text-white mb-0">Our Perfect Doctors</h2>
            </div>
            <div class="col-lg-5">
              <p class="mb-0 doctors-copy">Meet specialists dedicated to recovery, comfort and long-term wellbeing with caring home visits.</p>
            </div>
          </div>
          <div id="doctorsCarousel" class="carousel slide doctors-carousel d-none d-md-block" data-bs-ride="carousel">
            <div class="carousel-inner">
              <?php 
              $doctorChunks = array_chunk($doctors, 3);
              foreach ($doctorChunks as $index => $chunk): 
              ?>
              <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <div class="row g-4">
                  <?php foreach ($chunk as $doctor): ?>
                  <div class="col-md-6 col-lg-4">
                    <article class="doctor-card">
                      <img src="<?php
                        if (empty($doctor['img'])) {
                            echo BASE_URL . '/assets/img/dr-avatar.png';
                        } elseif (strpos($doctor['img'], 'http') === 0) {
                            echo htmlspecialchars($doctor['img']);
                        } else {
                            echo BASE_URL . '/' . htmlspecialchars($doctor['img']);
                        }
                        ?>" alt="<?php echo htmlspecialchars($doctor['name']); ?>">
                      <div class="doctor-card-body">
                        <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                        <p><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                      </div>
                    </article>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="carousel-indicators doctors-indicators">
              <?php foreach ($doctorChunks as $index => $chunk): ?>
              <button type="button" data-bs-target="#doctorsCarousel" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
              <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev doctors-control" type="button" data-bs-target="#doctorsCarousel" data-bs-slide="prev" aria-label="Previous">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next doctors-control" type="button" data-bs-target="#doctorsCarousel" data-bs-slide="next" aria-label="Next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
          </div>

          <div id="doctorsCarouselMobile" class="carousel slide doctors-carousel doctors-carousel-mobile d-md-none" data-bs-ride="carousel">
            <div class="carousel-inner">
              <?php foreach ($doctors as $index => $doctor): ?>
              <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <article class="doctor-card">
                  <img src="<?php 
                    if (strpos($doctor['img'], 'http') === 0) {
                      echo htmlspecialchars($doctor['img']);
                    } else {
                      echo htmlspecialchars($doctor['img']);
                    }
                  ?>" alt="<?php echo htmlspecialchars($doctor['name']); ?>">
                  <div class="doctor-card-body">
                    <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                    <p><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                  </div>
                </article>
              </div>
              <?php endforeach; ?>
            </div>

            <div class="carousel-indicators doctors-indicators">
              <?php foreach ($doctors as $index => $doctor): ?>
              <button type="button" data-bs-target="#doctorsCarouselMobile" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-label="Doctor <?php echo $index + 1; ?>"></button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </section>

      <section class="reviews-section" id="reviews">
        <div class="container">
          <div class="row align-items-center gy-4 mb-5">
            <div class="col-lg-4">
              <span class="section-kicker">Testimonial</span>
              <h2 class="section-title mb-0">Our client say</h2>
            </div>
          </div>

          <div id="reviewsCarousel" class="carousel slide reviews-carousel d-none d-md-block" data-bs-ride="carousel">
            <div class="carousel-inner">
              <div class="carousel-item active">
                <div class="row g-4">
                  <div class="col-md-6 col-lg-4">
                    <article class="testimonial-card active">
                      <div class="testimonial-top">
                        <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80" alt="Client Sarah">
                        <div>
                          <h3>Sarah Khan</h3>
                          <p>Patient Family</p>
                        </div>
                      </div>
                      <p>The nursing team was attentive, punctual and very reassuring throughout my mother's recovery.</p>
                      <div class="star-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                      </div>
                    </article>
                  </div>
                  <div class="col-md-6 col-lg-4">
                    <article class="testimonial-card active">
                      <div class="testimonial-top">
                        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=300&q=80" alt="Client Ahmed">
                        <div>
                          <h3>Ahmed Raza</h3>
                          <p>Home Visit Patient</p>
                        </div>
                      </div>
                      <p>Doctor consultation at home saved us time and gave us clear guidance for treatment and follow-up care.</p>
                      <div class="star-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                      </div>
                    </article>
                  </div>
                  <div class="col-md-6 col-lg-4">
                    <article class="testimonial-card active">
                      <div class="testimonial-top">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=300&q=80" alt="Client Ayesha">
                        <div>
                          <h3>Ayesha Malik</h3>
                          <p>Care Coordinator</p>
                        </div>
                      </div>
                      <p>Booking appointments was smooth and the support staff stayed connected with us after the visit too.</p>
                      <div class="star-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                      </div>
                    </article>
                  </div>
                </div>
              </div>

              <div class="carousel-item">
                <div class="row g-4">
                  <div class="col-md-6 col-lg-4">
                    <article class="testimonial-card active">
                      <div class="testimonial-top">
                        <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=300&q=80" alt="Client Hina">
                        <div>
                          <h3>Hina Tariq</h3>
                          <p>Patient Daughter</p>
                        </div>
                      </div>
                      <p>The follow-up support was excellent and the staff kept us informed at every step of treatment.</p>
                      <div class="star-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                      </div>
                    </article>
                  </div>
                  <div class="col-md-6 col-lg-4">
                    <article class="testimonial-card active">
                      <div class="testimonial-top">
                        <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=300&q=80" alt="Client Bilal">
                        <div>
                          <h3>Bilal Ahmed</h3>
                          <p>Recovery Patient</p>
                        </div>
                      </div>
                      <p>From appointment booking to home visit, everything was smooth, timely and highly professional.</p>
                      <div class="star-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                      </div>
                    </article>
                  </div>
                  <div class="col-md-6 col-lg-4">
                    <article class="testimonial-card active">
                      <div class="testimonial-top">
                        <img src="https://images.unsplash.com/photo-1546961329-78bef0414d7c?auto=format&fit=crop&w=300&q=80" alt="Client Sana">
                        <div>
                          <h3>Sana Javed</h3>
                          <p>Family Caregiver</p>
                        </div>
                      </div>
                      <p>We felt confident and supported because the team handled our home care needs with real compassion.</p>
                      <div class="star-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                      </div>
                    </article>
                  </div>
                </div>
              </div>
            </div>
            <div class="carousel-indicators reviews-indicators">
              <button type="button" data-bs-target="#reviewsCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
              <button type="button" data-bs-target="#reviewsCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            </div>
            <button class="carousel-control-prev reviews-control" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="prev" aria-label="Previous">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next reviews-control" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="next" aria-label="Next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
          </div>

          <div id="reviewsCarouselMobile" class="carousel slide reviews-carousel reviews-carousel-mobile d-md-none" data-bs-ride="carousel">
            <div class="carousel-inner">
              <div class="carousel-item active">
                <article class="testimonial-card active">
                  <div class="testimonial-top">
                    <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80" alt="Client Sarah">
                    <div>
                      <h3>Sarah Khan</h3>
                      <p>Patient Family</p>
                    </div>
                  </div>
                  <p>The nursing team was attentive, punctual and very reassuring throughout my mother's recovery.</p>
                  <div class="star-rating">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                  </div>
                </article>
              </div>
              <div class="carousel-item">
                <article class="testimonial-card active">
                  <div class="testimonial-top">
                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=300&q=80" alt="Client Ahmed">
                    <div>
                      <h3>Ahmed Raza</h3>
                      <p>Home Visit Patient</p>
                    </div>
                  </div>
                  <p>Doctor consultation at home saved us time and gave us clear guidance for treatment and follow-up care.</p>
                  <div class="star-rating">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                  </div>
                </article>
              </div>
              <div class="carousel-item">
                <article class="testimonial-card active">
                  <div class="testimonial-top">
                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=300&q=80" alt="Client Ayesha">
                    <div>
                      <h3>Ayesha Malik</h3>
                      <p>Care Coordinator</p>
                    </div>
                  </div>
                  <p>Booking appointments was smooth and the support staff stayed connected with us after the visit too.</p>
                  <div class="star-rating">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                  </div>
                </article>
              </div>
              <div class="carousel-item">
                <article class="testimonial-card active">
                  <div class="testimonial-top">
                    <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=300&q=80" alt="Client Hina">
                    <div>
                      <h3>Hina Tariq</h3>
                      <p>Patient Daughter</p>
                    </div>
                  </div>
                  <p>The follow-up support was excellent and the staff kept us informed at every step of treatment.</p>
                  <div class="star-rating">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                  </div>
                </article>
              </div>
              <div class="carousel-item">
                <article class="testimonial-card active">
                  <div class="testimonial-top">
                    <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=300&q=80" alt="Client Bilal">
                    <div>
                      <h3>Bilal Ahmed</h3>
                      <p>Recovery Patient</p>
                    </div>
                  </div>
                  <p>From appointment booking to home visit, everything was smooth, timely and highly professional.</p>
                  <div class="star-rating">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                  </div>
                </article>
              </div>
              <div class="carousel-item">
                <article class="testimonial-card active">
                  <div class="testimonial-top">
                    <img src="https://images.unsplash.com/photo-1546961329-78bef0414d7c?auto=format&fit=crop&w=300&q=80" alt="Client Sana">
                    <div>
                      <h3>Sana Javed</h3>
                      <p>Family Caregiver</p>
                    </div>
                  </div>
                  <p>We felt confident and supported because the team handled our home care needs with real compassion.</p>
                  <div class="star-rating">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                  </div>
                </article>
              </div>
            </div>
            <div class="carousel-indicators reviews-indicators">
              <button type="button" data-bs-target="#reviewsCarouselMobile" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Review 1"></button>
              <button type="button" data-bs-target="#reviewsCarouselMobile" data-bs-slide-to="1" aria-label="Review 2"></button>
              <button type="button" data-bs-target="#reviewsCarouselMobile" data-bs-slide-to="2" aria-label="Review 3"></button>
              <button type="button" data-bs-target="#reviewsCarouselMobile" data-bs-slide-to="3" aria-label="Review 4"></button>
              <button type="button" data-bs-target="#reviewsCarouselMobile" data-bs-slide-to="4" aria-label="Review 5"></button>
              <button type="button" data-bs-target="#reviewsCarouselMobile" data-bs-slide-to="5" aria-label="Review 6"></button>
            </div>
          </div>
        </div>
      </section>

      <section class="subscribe-section" id="contact">
        <div class="container">
          <div class="subscribe-card">
            <div class="row align-items-center gy-4">
              <div class="col-lg-7">
                <span class="section-kicker">Newsletter</span>
                <h2 class="section-title text-white">Get every updates</h2>
                <p class="text-white-50 mb-4">
                  Subscribe to get appointment reminders, health guidance and updates about our latest services.
                </p>
                <form class="subscribe-form">
                  <input type="email" class="form-control" placeholder="Enter your email">
                  <button class="btn btn-primary" type="submit">Subscribe</button>
                </form>
              </div>
              <div class="col-lg-5 text-center">
                <img class="subscribe-image" src="https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=900&q=80" alt="Doctor smiling">
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <?php include 'inc/site_footer.php'; ?>
  </div>

  <div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4">
        <div class="modal-body p-4 text-center">
          <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
          <h3 class="mb-3">Home Care Intro</h3>
          <p class="text-muted mb-0">Yahan aap apna introduction video ya embedded YouTube video laga sakte hain.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Back to Top Button -->
  <button class="back-to-top" id="backToTop" aria-label="Back to Top">
    <i class="bi bi-chevron-up"></i>
  </button>

  <script>
    // Header scroll effect
    window.addEventListener('scroll', function() {
      const header = document.querySelector('.site-header');
      if (window.scrollY > 50) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
      
      // Back to top button visibility
      const backToTop = document.getElementById('backToTop');
      if (window.scrollY > 300) {
        backToTop.classList.add('show');
      } else {
        backToTop.classList.remove('show');
      }
    });

    // Back to top button click handler
    document.getElementById('backToTop').addEventListener('click', function() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });

    // Scroll animations (Intersection Observer)
    const observerOptions = {
      root: null,
      rootMargin: '0px',
      threshold: 0.1
    };

    const observer = new IntersectionObserver(function(entries) {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '0';
          entry.target.style.transform = 'translateY(40px)';
          setTimeout(() => {
            entry.target.classList.add('fade-in-up');
          }, 100);
          observer.unobserve(entry.target);
        }
      });
    }, observerOptions);

    // Observe all elements that should fade in
    document.querySelectorAll('.mini-card, .service-card, .doctor-card, .testimonial-card, .about-list-item, .feature-media, .hero-visual').forEach(el => {
      observer.observe(el);
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });

    // Newsletter form submission
    const subscribeForm = document.querySelector('.subscribe-form');
    if (subscribeForm) {
      subscribeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const emailInput = this.querySelector('.form-control');
        if (emailInput.value) {
          alert('Thank you for subscribing!');
          emailInput.value = '';
        }
      });
    }
  </script>
</body>
</html>
