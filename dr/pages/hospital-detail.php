<?php 
// ============================================================
// 1. DB se Hospital ki basic info nikalna (Title & Schema ke liye)
// ============================================================
include_once '../config.php'; 
$hospital_id = isset($_GET['hospital_id']) ? (int)$_GET['hospital_id'] : 0;

$hospital_query = "SELECT h.*, c.city_name 
                   FROM hospitals h 
                   LEFT JOIN cities c ON h.city_id = c.city_id 
                   LEFT JOIN users u ON u.user_id = h.user_id
                   WHERE h.hospital_id = $hospital_id AND u.status = 1 AND h.approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital_info = mysqli_fetch_assoc($hospital_result);

// ============================================================
// AVAILABILITY HELPERS (hospitals.availability)
// ============================================================
function pav_days_order() {
    return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
}

function pav_days_parse($label) {
    $label = trim($label);
    if ($label === 'All Days')  return pav_days_order();
    if ($label === 'Mon - Fri') return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
    if ($label === 'Mon - Sat') return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $parts = array_map('trim', explode(',', $label));
    return array_values(array_intersect(pav_days_order(), $parts));
}

function pav_parse($raw) {
    $view = [
        'type'       => 'none',
        'days'       => [],
        'from'       => '09:00',
        'to'         => '17:00',
        'days_label' => '',
        'time_label' => '',
        'emergency'  => false,
        'note'       => '',
        'open_now'   => null,
        'today'      => ''
    ];

    $tz   = new DateTimeZone('Asia/Karachi');
    $now  = new DateTime('now', $tz);
    $view['today'] = $now->format('D');

    $raw = trim((string)$raw);
    if ($raw === '') return $view;

    $segments = array_map('trim', explode(' | ', $raw));
    $first = array_shift($segments);

    if (strcasecmp($first, 'Open 24/7') === 0 || $first === '24/7') {
        $view['type'] = '247';
        $view['open_now'] = true;
        $view['days'] = pav_days_order();
    } elseif (strcasecmp($first, 'Temporarily Closed') === 0) {
        $view['type'] = 'closed';
        $view['open_now'] = false;
    } else {
        $view['type'] = 'custom';
        $view['days'] = pav_days_parse($first);
        $view['days_label'] = $first;

        if (!empty($segments) && strpos($segments[0], ' - ') !== false
            && stripos($segments[0], 'Note:') !== 0 && stripos($segments[0], 'Emergency') !== 0) {
            $time_seg = array_shift($segments);
            $tp = explode(' - ', $time_seg);
            $f = strtotime($tp[0]);
            $t = isset($tp[1]) ? strtotime($tp[1]) : false;
            if ($f) $view['from'] = date('H:i', $f);
            if ($t) $view['to']   = date('H:i', $t);
            $view['time_label'] = trim($time_seg);
        }

        $cur = $now->format('H:i');
        $yesterday = (clone $now)->modify('-1 day')->format('D');
        $from = $view['from'];
        $to   = $view['to'];

        if ($from <= $to) {
            $view['open_now'] = in_array($view['today'], $view['days']) && $cur >= $from && $cur < $to;
        } else {
            $view['open_now'] = (in_array($view['today'], $view['days']) && $cur >= $from)
                             || (in_array($yesterday, $view['days']) && $cur < $to);
        }
    }

    foreach ($segments as $seg) {
        if (stripos($seg, 'Emergency') === 0) {
            $view['emergency'] = true;
        } elseif (stripos($seg, 'Note:') === 0) {
            $view['note'] = trim(substr($seg, 5));
        }
    }
    return $view;
}

$av = pav_parse($hospital_info['availability'] ?? '');

// ============================================================
// 2. SEO Title, Description aur Schema set karna
// ============================================================
$hosp_name_for_seo = $hospital_info ? htmlspecialchars($hospital_info['hospital_name']) : 'Hospital';
$city_name_for_seo = $hospital_info ? htmlspecialchars($hospital_info['city_name']) : 'Pakistan';

$GLOBALS['page_title'] = "$hosp_name_for_seo — Doctors, Facilities & Reviews | SehatNexa";
$GLOBALS['page_description'] = "View complete details, available beds, facilities, associated doctors, and patient reviews for $hosp_name_for_seo in $city_name_for_seo.";
$GLOBALS['page_image'] = ($hospital_info && !empty($hospital_info['hospital_pic'])) ? BASE_URL.'admin/inc/uploads/hospitals/'.$hospital_info['hospital_pic'] : BASE_URL.'admin/inc/uploads/default/hosp.jpg';

if ($hospital_info) {
    $schema = [
        "@context" => "https://schema.org",
        "@type" => "Hospital",
        "name" => $hospital_info['hospital_name'],
        "image" => $GLOBALS['page_image'],
        "telephone" => $hospital_info['hospital_phone'],
        "address" => [
            "@type" => "PostalAddress",
            "streetAddress" => $hospital_info['hospital_address'],
            "addressLocality" => $hospital_info['city_name'],
            "addressCountry" => "PK"
        ]
    ];

    if ($av['type'] === '247') {
        $schema['openingHours'] = ['Mo-Su 00:00-23:59'];
    } elseif ($av['type'] === 'custom' && !empty($av['days'])) {
        $day_map = ['Mon' => 'Mo', 'Tue' => 'Tu', 'Wed' => 'We', 'Thu' => 'Th', 'Fri' => 'Fr', 'Sat' => 'Sa', 'Sun' => 'Su'];
        $codes = [];
        foreach ($av['days'] as $d) {
            if (isset($day_map[$d])) $codes[] = $day_map[$d];
        }
        if (!empty($codes)) {
            $schema['openingHours'] = [implode(',', $codes) . ' ' . $av['from'] . '-' . $av['to']];
        }
    }
    if ($av['emergency']) {
        $schema['hasMap'] = null;
        unset($schema['hasMap']);
        $schema['availableService'] = ["@type" => "MedicalProcedure", "name" => "Emergency Services 24/7"];
    }

    $GLOBALS['page_schema'] = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

// ============================================================
// 3. Header Include karein
// ============================================================
include '../includes/header.php'; 

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
                    $stars, 1, NOW(), NOW())";
    $feedback_run = mysqli_query($con, $insert_query);
}

$hospital = $hospital_info;

if (!$hospital) {
    header('Location: ' . BASE_URL . 'hospitals');
    exit();
}

$user_id = (int)$hospital['user_id'];

// Fetch beds data
$beds_query = "SELECT * FROM hospital_beds WHERE hospital_id = $hospital_id";
$beds_result = mysqli_query($con, $beds_query);
$beds = mysqli_fetch_assoc($beds_result);

// Fetch facilities data
$facilities_query = "SELECT * FROM hospital_facilities WHERE hospital_id = $hospital_id";
$facilities_result = mysqli_query($con, $facilities_query);
$facilities = [];
while ($row = mysqli_fetch_assoc($facilities_result)) {
    $facilities[] = $row;
}
$total_facilities = count($facilities);
$available_facilities = 0;
foreach ($facilities as $fac) {
    if ($fac['is_available'] == 1) $available_facilities++;
}

// Fetch doctors in this hospital
$doctors_query = "SELECT DISTINCT d.*, dct.type AS specialization
                           FROM doctor_in_hospital dih
                           LEFT JOIN doctors d ON d.doctor_id = dih.doctor_id
                           LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                           LEFT JOIN users u ON d.user_id = u.user_id
                           WHERE dih.hospital_id = $hospital_id
                           AND u.status = 1
                           AND d.approve = 1
                           AND dih.inactive = 0
                           ORDER BY d.doctor_name ASC";

$doctors_result = mysqli_query($con, $doctors_query);
$total_doctors = mysqli_num_rows($doctors_result);

// FETCH REGISTERED LABS
$labs_query = "SELECT l.*, c.city_name 
               FROM assign_hospital ah 
               INNER JOIN laboratories l ON l.user_id = ah.user_id
               LEFT JOIN cities c ON l.city_id = c.city_id
               LEFT JOIN users u ON u.user_id = l.user_id
               WHERE ah.hospital_id = $hospital_id AND ah.status = 1 AND l.approve = 1 AND u.status = 1
               ORDER BY l.lab_name ASC";
$labs_result = mysqli_query($con, $labs_query);
$total_labs = mysqli_num_rows($labs_result);

// FETCH REGISTERED RADIOLOGY
$rad_query = "SELECT r.*, c.city_name 
              FROM assign_hospital_radiology ahr 
              INNER JOIN radiology r ON r.user_id = ahr.user_id
              LEFT JOIN cities c ON r.city_id = c.city_id
              LEFT JOIN users u ON u.user_id = r.user_id
              WHERE ahr.hospital_id = $hospital_id AND ahr.status = 1 AND r.approve = 1 AND u.status = 1
              ORDER BY r.rad_name ASC";
$rad_result = mysqli_query($con, $rad_query);
$total_radiology = $rad_result ? mysqli_num_rows($rad_result) : 0;

// Fetch feedbacks (5 initial)
$feedback_query = "SELECT f.* FROM feedback f WHERE f.user_id = $user_id AND f.status = 1 ORDER BY f.created_at DESC LIMIT 5";
$feedback_result = mysqli_query($con, $feedback_query);

// Calculate average rating
$rating_query = "SELECT AVG(stars) as avg_rating, COUNT(feedback_id) as total_reviews 
                 FROM feedback WHERE user_id = $user_id AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$total_reviews = $rating_data['total_reviews'] ? $rating_data['total_reviews'] : 0;

// Fetch HOSPITAL NOTES
$notes_query = "SELECT * FROM notes WHERE user_id = $user_id LIMIT 1";
$notes_result = mysqli_query($con, $notes_query);
$hospital_note = ($notes_result && mysqli_num_rows($notes_result) > 0)
    ? mysqli_fetch_assoc($notes_result)
    : null;

// ============================================================
// AVAILABILITY DISPLAY VALUES
// ============================================================
$av_has = ($av['type'] !== 'none');
$av_class = 'av-none';
$av_icon  = 'fa-clock';
$av_title = '';
$av_sub   = '';
$av_live  = '';

if ($av['type'] === '247') {
    $av_class = 'av-open';
    $av_icon  = 'fa-infinity';
    $av_title = 'Open 24/7';
    $av_sub   = 'Open 24 hours, all 7 days';
    $av_live  = 'Open Now';
} elseif ($av['type'] === 'custom') {
    $av_class = $av['open_now'] ? 'av-open' : 'av-shut';
    $av_icon  = 'fa-calendar-check';
    $av_title = $av['days_label'];
    $av_sub   = $av['time_label'];
    $av_live  = $av['open_now'] ? 'Open Now' : 'Closed Now';
} elseif ($av['type'] === 'closed') {
    $av_class = 'av-shut';
    $av_icon  = 'fa-door-closed';
    $av_title = 'Temporarily Closed';
    $av_sub   = 'Currently not accepting visits';
    $av_live  = 'Closed';
}
?>

<!-- Navbar -->
<?php include BASE_PATH . '/includes/menu.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>style/hospital-detail.css?v=<?= @filemtime(__DIR__ . '/style/hospital-detail.css') ?: time() ?>">

<style>
.hero-avail {
    display: inline-flex; align-items: center; gap: 12px;
    margin: 12px 0 4px; padding: 10px 16px; border-radius: 14px;
    background: rgba(255,255,255,.96); border: 1.5px solid #e2e8f0;
    box-shadow: 0 8px 22px rgba(0,0,0,.12); max-width: 100%;
}
.hero-avail .ha-ic {
    width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.05rem; background: #94a3b8;
}
.hero-avail .ha-title { font-size: .95rem; font-weight: 800; color: #0D2B6E; line-height: 1.2; }
.hero-avail .ha-sub { font-size: .76rem; font-weight: 600; color: #64748b; margin-top: 2px; }
.hero-avail .ha-live {
    margin-left: 6px; padding: 4px 11px; border-radius: 20px; font-size: .68rem;
    font-weight: 800; letter-spacing: .5px; text-transform: uppercase; color: #fff; white-space: nowrap;
}
.hero-avail.av-open { border-color: rgba(39,201,106,.55); }
.hero-avail.av-open .ha-ic { background: linear-gradient(135deg, #27C96A, #00C9A7); }
.hero-avail.av-open .ha-live { background: #22c55e; }
.hero-avail.av-shut { border-color: rgba(239,68,68,.5); }
.hero-avail.av-shut .ha-ic { background: linear-gradient(135deg, #dc2626, #ef4444); }
.hero-avail.av-shut .ha-live { background: #ef4444; }
.hero-avail .ha-dot {
    width: 8px; height: 8px; border-radius: 50%; background: #fff;
    display: inline-block; margin-right: 5px; animation: avPulse 1.6s infinite;
}
@keyframes avPulse { 0%,100% { opacity: 1; } 50% { opacity: .35; } }

.avail-card { border: 2px solid #e2e8f0; overflow: hidden; }
.avail-card.av-open { border-color: rgba(39,201,106,.55); }
.avail-card.av-shut { border-color: rgba(239,68,68,.45); }

.avail-main { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.avail-main .am-ic {
    width: 58px; height: 58px; border-radius: 16px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; color: #fff; background: #94a3b8;
}
.avail-card.av-open .am-ic { background: linear-gradient(135deg, #27C96A, #00C9A7); box-shadow: 0 8px 20px rgba(39,201,106,.35); }
.avail-card.av-shut .am-ic { background: linear-gradient(135deg, #dc2626, #ef4444); box-shadow: 0 8px 20px rgba(239,68,68,.3); }
.avail-main .am-text { flex: 1; min-width: 160px; }
.avail-main .am-title { font-size: 1.3rem; font-weight: 800; color: #0D2B6E; line-height: 1.2; }
.avail-main .am-sub { font-size: .95rem; font-weight: 600; color: #475569; margin-top: 3px; }
.avail-main .am-live {
    padding: 7px 16px; border-radius: 30px; font-size: .78rem; font-weight: 800;
    letter-spacing: .6px; text-transform: uppercase; color: #fff; white-space: nowrap;
}
.avail-card.av-open .am-live { background: #22c55e; }
.avail-card.av-shut .am-live { background: #ef4444; }

.avail-days { display: flex; flex-wrap: wrap; gap: 7px; margin-top: 16px; }
.avail-days span {
    min-width: 48px; text-align: center; padding: 7px 10px; border-radius: 10px;
    font-size: .78rem; font-weight: 700; background: #f1f5f9; color: #94a3b8;
    border: 2px solid transparent;
}
.avail-days span.on { background: #E8F7FF; color: #1C3F8F; border-color: rgba(0,168,204,.35); }
.avail-days span.today { background: linear-gradient(135deg, #1C3F8F, #00A8CC); color: #fff; border-color: transparent; box-shadow: 0 6px 14px rgba(0,168,204,.35); }

.avail-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
.avail-tag {
    display: inline-flex; align-items: center; gap: 7px; padding: 7px 14px;
    border-radius: 30px; font-size: .8rem; font-weight: 700;
}
.avail-tag.em { background: #fee2e2; color: #b91c1c; border: 1px solid rgba(239,68,68,.35); }
.avail-tag.note { background: #fff7ed; color: #c2410c; border: 1px solid rgba(249,115,22,.35); }

/* Load More Reviews */
.load-more-wrap { text-align: center; padding: 18px 0 6px; }
.btn-load-more {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 28px; border-radius: 30px; border: 2px solid #1C3F8F;
    background: #fff; color: #1C3F8F; font-weight: 700; font-size: .9rem;
    cursor: pointer; transition: all .25s ease;
}
.btn-load-more:hover { background: #1C3F8F; color: #fff; }
.btn-load-more:disabled { opacity: .5; cursor: not-allowed; }
.btn-load-more .spinner-border { width: 16px; height: 16px; border-width: 2px; }

/* ============================================================
   PAGE SEARCH BAR
   ============================================================ */
.page-search-wrap {
    position: sticky; top: 70px; z-index: 50;
    margin: 14px 0 20px;
}
.page-search-box {
    display: flex; align-items: center; gap: 10px;
    background: #fff; border: 2px solid #e2e8f0;
    border-radius: 14px; padding: 10px 16px;
    box-shadow: 0 6px 18px rgba(13,43,110,.08);
    transition: border-color .2s ease, box-shadow .2s ease;
}
.page-search-box:focus-within {
    border-color: #00A8CC;
    box-shadow: 0 6px 22px rgba(0,168,204,.18);
}
.page-search-box i.search-icon { color: #1C3F8F; font-size: 1.05rem; }
.page-search-box input {
    flex: 1; border: none; outline: none; background: transparent;
    font-size: .95rem; font-weight: 600; color: #0D2B6E;
}
.page-search-box input::placeholder { color: #94a3b8; font-weight: 500; }
.page-search-clear {
    border: none; background: #f1f5f9; color: #64748b;
    width: 30px; height: 30px; border-radius: 50%;
    cursor: pointer; display: none; align-items: center; justify-content: center;
    transition: all .2s ease;
}
.page-search-clear:hover { background: #e2e8f0; color: #1C3F8F; }
.page-search-clear.show { display: flex; }
.page-search-count {
    font-size: .72rem; font-weight: 700; color: #64748b;
    padding: 3px 10px; border-radius: 20px; background: #f1f5f9;
    white-space: nowrap;
}
.page-search-count.has-results { background: #dcfce7; color: #15803d; }
.page-search-count.no-results  { background: #fee2e2; color: #b91c1c; }

/* Search highlight + hide */
.search-hidden { display: none !important; }
mark.search-hit {
    background: #fef08a; color: #854d0e;
    padding: 1px 3px; border-radius: 3px;
}
.info-card.search-empty-card { display: none !important; }

@media (max-width: 576px) {
    .hero-avail { width: 100%; }
    .avail-main .am-title { font-size: 1.1rem; }
    .page-search-wrap { position: relative; top: auto; }
    .page-search-count { display: none; }
}
</style>

<!-- ===== HERO SECTION ===== -->
<section class="hospital-hero">
    <div class="container">
        <div class="hospital-hero-content">
            <div class="hospital-hero-image">
                <?php if (!empty($hospital['hospital_pic']) && file_exists(BASE_PATH . '/admin/inc/uploads/hospitals/' . $hospital['hospital_pic'])): ?>
                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/hospitals/<?php echo $hospital['hospital_pic']; ?>" 
                         alt="<?php echo htmlspecialchars($hospital['hospital_name']); ?> in <?php echo htmlspecialchars($hospital['city_name']); ?>">
                <?php else: ?>
                    <div class="placeholder"><i class="fas fa-hospital"></i></div>
                <?php endif; ?>
            </div>
            <div class="hospital-hero-info">
                <h1><?php echo htmlspecialchars($hospital['hospital_name']); ?></h1>
                <p class="sub-info"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hospital['city_name']); ?></p>
                <p class="sub-info"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($hospital['hospital_phone']); ?></p>
                <div class="rating-badge">
                    <i class="fas fa-star text-warning"></i>
                    <?php echo $avg_rating > 0 ? $avg_rating : 'New'; ?>
                    <span>(<?php echo $total_reviews; ?> reviews)</span>
                </div>

                <?php if ($av_has): ?>
                    <div class="hero-avail <?php echo $av_class; ?>">
                        <div class="ha-ic"><i class="fas <?php echo $av_icon; ?>"></i></div>
                        <div>
                            <div class="ha-title"><?php echo htmlspecialchars($av_title); ?></div>
                            <?php if ($av_sub !== ''): ?>
                                <div class="ha-sub"><?php echo htmlspecialchars($av_sub); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if ($av_live !== ''): ?>
                            <span class="ha-live"><span class="ha-dot"></span><?php echo $av_live; ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="hospital-hero-actions">
                    <a href="tel:<?php echo htmlspecialchars($hospital['hospital_phone']); ?>" class="btn-call-hero">
                        <i class="fas fa-phone-alt me-2"></i> Call Now
                    </a>
                    <a href="#reviews" class="btn-appointment-hero">
                        <i class="fas fa-star me-2"></i> Write a Review
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== PAGE SEARCH BAR ===== -->
<div class="container">
    <div class="page-search-wrap">
        <div class="page-search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="pageSearchInput" 
                   placeholder="Search doctors, labs, facilities, reviews, address..." 
                   autocomplete="off"
                   aria-label="Search this page">
            <span class="page-search-count" id="pageSearchCount">0 results</span>
            <button type="button" class="page-search-clear" id="pageSearchClear" aria-label="Clear search">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<!-- ===== STATS BAR ===== -->
<div class="container">
    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-number"><?php echo $total_doctors; ?></div>
            <div class="stat-label">Doctors</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo ($beds && isset($beds['total_beds'])) ? $beds['total_beds'] : 0; ?></div>
            <div class="stat-label">Total Beds</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $available_facilities; ?>/<?php echo $total_facilities; ?></div>
            <div class="stat-label">Facilities</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $total_reviews; ?></div>
            <div class="stat-label">Reviews</div>
        </div>
    </div>
</div>

<!-- Ad Space 1 -->
<div class="container my-4" data-aos="fade-up">
    <div class="ad-slot text-center" style="min-height:90px; background:#f8f9fa; border:1px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center;">
        <span class="text-muted small">Advertisement Space</span>
    </div>
</div>

<!-- ===== DETAIL SECTION ===== -->
<section class="section-padding">
    <div class="container">
        <div class="detail-grid">

            <!-- ===== LEFT COLUMN ===== -->
            <div class="left-column">

                <?php if ($av_has): ?>
                    <div class="info-card avail-card <?php echo $av_class; ?>" id="availability">
                        <div class="info-card-header">
                            <h2><i class="fas fa-clock"></i> Hospital Timing</h2>
                        </div>
                        <div class="info-card-body">
                            <div class="avail-main">
                                <div class="am-ic"><i class="fas <?php echo $av_icon; ?>"></i></div>
                                <div class="am-text">
                                    <div class="am-title"><?php echo htmlspecialchars($av_title); ?></div>
                                    <?php if ($av_sub !== ''): ?>
                                        <div class="am-sub"><?php echo htmlspecialchars($av_sub); ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($av_live !== ''): ?>
                                    <span class="am-live"><?php echo $av_live; ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if ($av['type'] === 'custom' || $av['type'] === '247'): ?>
                                <div class="avail-days">
                                    <?php foreach (pav_days_order() as $dy): 
                                        $cls = in_array($dy, $av['days']) ? 'on' : '';
                                        if ($dy === $av['today'] && $cls === 'on') $cls = 'today';
                                    ?>
                                        <span class="<?php echo $cls; ?>"><?php echo $dy; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($av['emergency'] || $av['note'] !== ''): ?>
                                <div class="avail-tags">
                                    <?php if ($av['emergency']): ?>
                                        <span class="avail-tag em"><i class="fas fa-ambulance"></i> Emergency Services 24/7</span>
                                    <?php endif; ?>
                                    <?php if ($av['note'] !== ''): ?>
                                        <span class="avail-tag note"><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($av['note']); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Address -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h2><i class="fas fa-map-pin"></i> Address</h2>
                    </div>
                    <div class="info-card-body">
                        <p><?php echo nl2br(htmlspecialchars($hospital['hospital_address'])); ?></p>
                    </div>
                </div>

                <!-- NOTES -->
                <?php if ($hospital_note && !empty(trim($hospital_note['notes']))): ?>
                    <div class="info-card notes-card">
                        <div class="info-card-header">
                            <h2><i class="fas fa-sticky-note"></i> Notes</h2>
                            <?php if (!empty($hospital_note['updated_at'])): ?>
                                <small class="notes-updated">
                                    <i class="fas fa-clock me-1"></i>
                                    Updated: <?php echo date('d M Y, h:i A', strtotime($hospital_note['updated_at'])); ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="info-card-body">
                            <div class="notes-content">
                                <?php echo nl2br(htmlspecialchars($hospital_note['notes'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Beds -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h2><i class="fas fa-bed"></i> Bed Availability</h2>
                    </div>
                    <div class="info-card-body">
                        <?php if ($beds && (($beds['total_beds'] ?? 0) > 0 || ($beds['icu_beds'] ?? 0) > 0 || ($beds['general_beds'] ?? 0) > 0 || ($beds['private_beds'] ?? 0) > 0)): ?>
                            <div class="beds-grid">
                                <div class="bed-item">
                                    <div class="bed-number"><?php echo $beds['total_beds']; ?></div>
                                    <div class="bed-label">Total Beds</div>
                                </div>
                                <div class="bed-item">
                                    <div class="bed-number"><?php echo $beds['icu_beds']; ?></div>
                                    <div class="bed-label">ICU Beds</div>
                                </div>
                                <div class="bed-item">
                                    <div class="bed-number"><?php echo $beds['general_beds']; ?></div>
                                    <div class="bed-label">General Beds</div>
                                </div>
                                <div class="bed-item">
                                    <div class="bed-number"><?php echo $beds['private_beds']; ?></div>
                                    <div class="bed-label">Private Beds</div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-2">No bed information available</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Facilities -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h2><i class="fas fa-concierge-bell"></i> Facilities & Services</h2>
                    </div>
                    <div class="info-card-body">
                        <?php if ($total_facilities > 0): ?>
                            <div class="facilities-grid">
                                <?php foreach ($facilities as $facility): ?>
                                    <div class="facility-item">
                                        <span class="facility-status <?php echo $facility['is_available'] == 1 ? 'available' : 'unavailable'; ?>"></span>
                                        <span class="facility-name"><?php echo htmlspecialchars($facility['facility_name']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-2">No facilities available</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- REGISTERED LABS -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h2><i class="fas fa-flask"></i> Registered Labs (<?php echo $total_labs; ?>)</h2>
                    </div>
                    <div class="info-card-body">
                        <?php if ($total_labs > 0): ?>
                            <?php while ($lab = mysqli_fetch_assoc($labs_result)): ?>
                                <?php 
                                $lab_pic_val = trim($lab['lab_pic'] ?? ''); 
                                $lab_img_path = BASE_PATH . '/admin/inc/uploads/labs/' . $lab_pic_val;
                                $has_valid_pic = (!empty($lab_pic_val) && $lab_pic_val !== '0' && strtolower($lab_pic_val) !== 'null' && file_exists($lab_img_path));
                                ?>
                                <div class="doctor-mini-card">
                                    <?php if ($has_valid_pic): ?>
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/labs/<?php echo htmlspecialchars($lab_pic_val); ?>" 
                                             alt="<?php echo htmlspecialchars($lab['lab_name']); ?>" class="doctor-avatar">
                                    <?php else: ?>
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/lab.jpg" 
                                             alt="<?php echo htmlspecialchars($lab['lab_name']); ?>" class="doctor-avatar">
                                    <?php endif; ?>
                                    <div>
                                        <h4 class="mb-0 fs-6 fw-bold"><?php echo htmlspecialchars($lab['lab_name']); ?></h4>
                                        <span class="doctor-spec"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($lab['city_name']); ?></span>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>lab-detail?lab_id=<?php echo $lab['lab_id']; ?>" 
                                       class="btn btn-sm btn-primary">View</a>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-2">No labs registered at this hospital</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- REGISTERED RADIOLOGY -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h2><i class="fas fa-x-ray"></i> Registered Radiology (<?php echo $total_radiology; ?>)</h2>
                    </div>
                    <div class="info-card-body">
                        <?php if ($total_radiology > 0): ?>
                            <?php while ($rad = mysqli_fetch_assoc($rad_result)): ?>
                                <?php
                                $rad_pic_val = trim($rad['rad_pic'] ?? '');

                                 if(!empty($rad_pic_val) && $rad_pic_val !== '0' && strtolower($rad_pic_val) !== 'null'){
                                    $path = $rad_pic_val;
                                }else{
                                    $path = BASE_URL . 'admin/inc/uploads/default/radiology.jpg';
                                }

                                ?>
                                <div class="doctor-mini-card">
                                    <img src="<?php echo $path ?>" 
                                             alt="<?php echo htmlspecialchars($rad['rad_name']); ?>" class="doctor-avatar">
                                    <div>
                                        <h4 class="mb-0 fs-6 fw-bold"><?php echo htmlspecialchars($rad['rad_name']); ?></h4>
                                        <span class="doctor-spec"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($rad['city_name'] ?? 'N/A'); ?></span>
                                    </div>
                                    <a target="_blank" href="<?php echo BASE_URL; ?>radiology-detail?rad_id=<?php echo $rad['rad_id']; ?>" 
                                       class="btn btn-sm btn-primary">Profile</a>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-2">No radiology centers registered at this hospital</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Doctors -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h2><i class="fas fa-user-md"></i> Doctors (<?php echo $total_doctors; ?>)</h2>
                    </div>
                    <div class="info-card-body">
                        <?php if ($total_doctors > 0): ?>
                            <?php while ($doctor = mysqli_fetch_assoc($doctors_result)): ?>
                                <div class="doctor-mini-card">
                                    <?php if (!empty($doctor['doctor_pic'])): ?>
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" 
                                             alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" class="doctor-avatar">
                                    <?php else: ?>
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/doctor.jpg" 
                                             alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" class="doctor-avatar">
                                    <?php endif; ?>
                                    <div>
                                        <h4 class="mb-0 fs-6 fw-bold">Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></h4>
                                        <span class="doctor-spec"><?php echo htmlspecialchars($doctor['specialization'] ?? 'General'); ?></span>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>doctor-detail?doctor_id=<?php echo $doctor['doctor_id']; ?>" 
                                       class="btn btn-sm btn-primary">View</a>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-2">No doctors registered at this hospital</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- ===== RIGHT COLUMN ===== -->
            <div class="right-column">

                <!-- Reviews -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h2><i class="fas fa-star"></i> Patient Reviews (<?php echo $total_reviews; ?>)</h2>
                    </div>
                    <div class="info-card-body">
                        <div id="reviews-list">
                            <?php if ($total_reviews > 0): ?>
                                <?php while ($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                                    <div class="feedback-item">
                                        <div class="feedback-header">
                                            <span class="feedback-name fw-bold"><?php echo htmlspecialchars($feedback['commenter_name']); ?></span>
                                            <span class="feedback-rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $feedback['stars'] ? 'text-warning' : 'text-muted'; ?>" style="font-size: 0.8rem;"></i>
                                                <?php endfor; ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($feedback['comment'])): ?>
                                            <p class="feedback-comment mt-2"><?php echo nl2br(htmlspecialchars($feedback['comment'])); ?></p>
                                        <?php endif; ?>
                                        <div class="feedback-date small text-muted mt-1">
                                            <i class="fas fa-calendar me-1"></i> <?php echo date('d M Y', strtotime($feedback['created_at'])); ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-comments fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No reviews yet</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($total_reviews > 5): ?>
                            <div class="load-more-wrap">
                                <button type="button" class="btn-load-more" id="loadMoreReviews" 
                                        data-user-id="<?php echo $user_id; ?>" 
                                        data-offset="5" 
                                        data-total="<?php echo $total_reviews; ?>">
                                    <i class="fas fa-chevron-down"></i> Load More Reviews
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Review Form -->
                <div id="reviews" class="review-form-card">
                    <h3 class="mb-3"><i class="fas fa-pen me-2"></i> Write a Review</h3>
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Your Name *</label>
                                <input type="text" name="reviewer_name" class="form-control" required placeholder="Enter your name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" name="reviewer_email" class="form-control" required placeholder="your@email.com">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Rating *</label>
                            <div class="rating-input">
                                <select name="rating" class="form-control" required>
                                    <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                                    <option value="4">⭐⭐⭐⭐ Very Good</option>
                                    <option value="3">⭐⭐⭐ Good</option>
                                    <option value="2">⭐⭐ Fair</option>
                                    <option value="1">⭐ Poor</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Your Review *</label>
                            <textarea name="review_comment" class="form-control" rows="4" required placeholder="Share your experience..."></textarea>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-paper-plane me-2"></i> Submit Review
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Ad Space 2 -->
<div class="container my-4" data-aos="fade-up">
    <div class="ad-slot text-center" style="min-height:90px; background:#f8f9fa; border:1px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center;">
        <span class="text-muted small">Advertisement Space</span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    /* =========================================================
       LOAD MORE REVIEWS
       ========================================================= */
    const loadBtn = document.getElementById('loadMoreReviews');
    if (loadBtn) {
        loadBtn.addEventListener('click', function() {
            const btn = this;
            const userId = btn.dataset.userId;
            let offset = parseInt(btn.dataset.offset, 10);
            const total = parseInt(btn.dataset.total, 10);
            const limit = 5;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Loading...';

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo BASE_URL; ?>ajax/load-reviews.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.success && res.html) {
                            const list = document.getElementById('reviews-list');
                            list.insertAdjacentHTML('beforeend', res.html);
                            offset += limit;
                            btn.dataset.offset = offset;
                            btn.disabled = false;
                            if (offset >= total) {
                                btn.remove();
                            } else {
                                btn.innerHTML = '<i class="fas fa-chevron-down"></i> Load More Reviews';
                            }
                        } else {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-chevron-down"></i> Load More Reviews';
                        }
                    } catch (e) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-chevron-down"></i> Load More Reviews';
                    }
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-chevron-down"></i> Load More Reviews';
                }
            };
            xhr.onerror = function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-chevron-down"></i> Load More Reviews';
            };
            xhr.send('user_id=' + encodeURIComponent(userId) + '&offset=' + offset + '&limit=' + limit);
        });
    }

    /* =========================================================
       PAGE SEARCH (client-side, highlights + filters)
       ========================================================= */
    const searchInput = document.getElementById('pageSearchInput');
    const searchClear = document.getElementById('pageSearchClear');
    const searchCount = document.getElementById('pageSearchCount');

    if (!searchInput) return;

    // Target elements we search through
    const searchSelectors = [
        '.doctor-mini-card',          // doctors, labs, radiology
        '.feedback-item',             // reviews
        '.facility-item',             // facilities
        '.bed-item',                  // beds
        '.avail-tag',                 // availability tags
        '.info-card p',               // address / notes / paragraphs
        '.info-card-header h2',       // section headings
        '.avail-main .am-title',
        '.avail-main .am-sub'
    ];

    // Card-level selectors we hide if all children hidden
    const containerSelectors = [
        '.info-card',
        '.avail-card'
    ];

    // Save original HTML for clearing
    const nodes = [];
    document.querySelectorAll(searchSelectors.join(',')).forEach(el => {
        if (el.dataset.searchOriginal === undefined) {
            el.dataset.searchOriginal = el.innerHTML;
        }
        nodes.push(el);
    });

    // Escape regex
    function escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // Strip existing <mark> safely
    function stripMarks(el) {
        if (el.dataset.searchOriginal !== undefined) {
            el.innerHTML = el.dataset.searchOriginal;
        }
    }

    // Highlight matches inside element (only for text nodes)
    function highlight(el, term) {
        if (!term) return;
        const regex = new RegExp('(' + escapeRegex(term) + ')', 'gi');
        const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, null);
        const textNodes = [];
        let n;
        while (n = walker.nextNode()) {
            // Skip script/style/input/textarea content
            const tag = n.parentNode.tagName;
            if (tag === 'SCRIPT' || tag === 'STYLE' || tag === 'INPUT' || tag === 'TEXTAREA') continue;
            if (n.nodeValue.trim() === '') continue;
            textNodes.push(n);
        }
        textNodes.forEach(node => {
            const val = node.nodeValue;
            if (!regex.test(val)) return;
            regex.lastIndex = 0;
            const span = document.createElement('span');
            span.innerHTML = val.replace(regex, '<mark class="search-hit">$1</mark>');
            node.parentNode.replaceChild(span, node);
        });
    }

    // Perform search
    function runSearch(rawTerm) {
        const term = rawTerm.trim();
        const lower = term.toLowerCase();

        // Reset all
        nodes.forEach(el => {
            el.classList.remove('search-hidden');
            stripMarks(el);
        });

        // Clear previous empty-state
        document.querySelectorAll('.search-no-results-msg').forEach(e => e.remove());
        document.querySelectorAll('.info-card.search-empty-card').forEach(e => e.classList.remove('search-empty-card'));

        if (!term) {
            searchClear.classList.remove('show');
            searchCount.textContent = '0 results';
            searchCount.classList.remove('has-results', 'no-results');
            return;
        }

        searchClear.classList.add('show');

        let matchCount = 0;

        nodes.forEach(el => {
            const text = (el.textContent || '').toLowerCase();
            if (text.indexOf(lower) !== -1) {
                matchCount++;
                el.classList.remove('search-hidden');
                highlight(el, term);
            } else {
                el.classList.add('search-hidden');
            }
        });

        // Hide cards where everything is hidden
        document.querySelectorAll(containerSelectors.join(',')).forEach(card => {
            const visibleKids = card.querySelectorAll(
                '.doctor-mini-card:not(.search-hidden), .feedback-item:not(.search-hidden), .facility-item:not(.search-hidden), .bed-item:not(.search-hidden), .avail-tag:not(.search-hidden), p:not(.search-hidden), h2:not(.search-hidden)'
            );
            if (visibleKids.length === 0 && matchCount > 0) {
                // Only hide container if it's a list-like card
                const isListCard = card.querySelector('.doctor-mini-card, .feedback-item, .facility-item');
                if (isListCard) card.classList.add('search-empty-card');
            }
        });

        // Update count badge
        searchCount.textContent = matchCount + ' result' + (matchCount === 1 ? '' : 's');
        searchCount.classList.remove('has-results', 'no-results');
        searchCount.classList.add(matchCount > 0 ? 'has-results' : 'no-results');

        // Global no-results message
        if (matchCount === 0) {
            const grid = document.querySelector('.detail-grid');
            if (grid && !grid.querySelector('.search-no-results-msg')) {
                const msg = document.createElement('div');
                msg.className = 'search-no-results-msg text-center py-5';
                msg.innerHTML = '<i class="fas fa-search fa-2x text-muted mb-3"></i>' +
                                '<h4 class="fw-bold" style="color:#0D2B6E;">No results found</h4>' +
                                '<p class="text-muted mb-0">No content on this page matches "<strong>' +
                                term.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</strong>".</p>';
                grid.parentNode.insertBefore(msg, grid);
            }
        }
    }

    // Debounce
    let debounceTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const val = this.value;
        debounceTimer = setTimeout(() => runSearch(val), 200);
    });

    // Clear button
    searchClear.addEventListener('click', function() {
        searchInput.value = '';
        runSearch('');
        searchInput.focus();
    });

    // Esc key clears
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            searchInput.value = '';
            runSearch('');
        }
    });
});
</script>

<!-- Footer -->
<?php include BASE_PATH . '/includes/footer.php'; ?>