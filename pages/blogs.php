<?php
require_once '../config.php';
$title = "Health Blogs - HHC";
?>
<?php include_once BASE_PATH . '/inc/site_header.php'; ?>
<body>
  <div class="page-shell">
    <?php include_once BASE_PATH . '/inc/home_menu.php'; ?>

    <main>
      <section class="hero-section" id="blog-hero">
        <div class="container">
          <div class="row align-items-center g-5">
            <div class="col-lg-6">
              <div class="hero-content">
                <span class="section-kicker">Health & Wellness</span>
                <h1 class="hero-title">Our Health Blog</h1>
                <p class="hero-text">Stay informed with the latest health tips, medical news, and wellness advice from our team of experts.</p>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="hero-visual">
                <img src="https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=800&q=80" alt="Health Blog" class="w-100">
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="blog-section section-padding" id="blog">
        <div class="container">
          <div class="section-heading">
            <span class="section-kicker">Latest Articles</span>
            <h2 class="section-title">Health & Wellness Tips</h2>
          </div>
          
          <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="blog-card">
                <div class="blog-thumb">
                  <img src="https://images.unsplash.com/photo-1498837167922-ddd27525d352?auto=format&fit=crop&w=400&q=80" alt="Healthy Eating" class="w-100">
                  <span class="blog-category">Nutrition</span>
                </div>
                <div class="blog-body">
                  <h3>10 Tips for a Healthy Diet</h3>
                  <p>Discover simple and effective tips to improve your eating habits and maintain a balanced diet.</p>
                  <a href="#" class="blog-link">Read More <i class="bi bi-arrow-right"></i></a>
                </div>
              </article>
            </div>
            
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="blog-card">
                <div class="blog-thumb">
                  <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&w=400&q=80" alt="Fitness" class="w-100">
                  <span class="blog-category">Fitness</span>
                </div>
                <div class="blog-body">
                  <h3>Daily Exercise Routine for Beginners</h3>
                  <p>Start your fitness journey with these easy-to-follow exercises that you can do at home.</p>
                  <a href="#" class="blog-link">Read More <i class="bi bi-arrow-right"></i></a>
                </div>
              </article>
            </div>
            
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="blog-card">
                <div class="blog-thumb">
                  <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=400&q=80" alt="Mental Health" class="w-100">
                  <span class="blog-category">Wellness</span>
                </div>
                <div class="blog-body">
                  <h3>5 Ways to Improve Mental Health</h3>
                  <p>Learn practical strategies to boost your mental well-being and reduce stress in daily life.</p>
                  <a href="#" class="blog-link">Read More <i class="bi bi-arrow-right"></i></a>
                </div>
              </article>
            </div>
            
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="blog-card">
                <div class="blog-thumb">
                  <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=400&q=80" alt="Preventive Care" class="w-100">
                  <span class="blog-category">Healthcare</span>
                </div>
                <div class="blog-body">
                  <h3>The Importance of Regular Check-ups</h3>
                  <p>Why preventive healthcare is crucial and how regular doctor visits can save lives.</p>
                  <a href="#" class="blog-link">Read More <i class="bi bi-arrow-right"></i></a>
                </div>
              </article>
            </div>
            
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="blog-card">
                <div class="blog-thumb">
                  <img src="https://images.unsplash.com/photo-1493934558415-9d19f9642b4d2?auto=format&fit=crop&w=400&q=80" alt="Sleep Health" class="w-100">
                  <span class="blog-category">Wellness</span>
                </div>
                <div class="blog-body">
                  <h3>Sleep Better, Live Better</h3>
                  <p>Essential tips for improving your sleep quality and maintaining a healthy sleep schedule.</p>
                  <a href="#" class="blog-link">Read More <i class="bi bi-arrow-right"></i></a>
                </div>
              </article>
            </div>
            
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="blog-card">
                <div class="blog-thumb">
                  <img src="https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=400&q=80" alt="Health Tech" class="w-100">
                  <span class="blog-category">Technology</span>
                </div>
                <div class="blog-body">
                  <h3>Future of Healthcare Technology</h3>
                  <p>Exploring the latest innovations in medical technology and how they're changing healthcare.</p>
                  <a href="#" class="blog-link">Read More <i class="bi bi-arrow-right"></i></a>
                </div>
              </article>
            </div>
          </div>
        </div>
      </section>
    </main>

    <?php include_once BASE_PATH . '/inc/site_footer.php'; ?>
  </div>
</body>
</html>