<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>
<?php include BASE_PATH.'/admin/inc/header.php';?>
<?php include BASE_PATH.'/admin/inc/top.php';?>
<?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
$rad_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$rad_id) {
    echo '<div class="content-wrapper"><div class="container-fluid"><div class="alert alert-danger">Invalid Radiology ID.</div></div></div>';
    include BASE_PATH.'/admin/inc/footer.php';
    exit;
}

// Fetch radiology + city + user
$q = "SELECT r.*, c.city_name, u.username, u.email AS login_email, u.status AS user_status
      FROM radiology r
      LEFT JOIN cities c ON c.city_id = r.city_id
      LEFT JOIN users u ON u.user_id = r.user_id
      WHERE r.rad_id = $rad_id LIMIT 1";
$res = mysqli_query($con, $q);
$data = $res ? mysqli_fetch_assoc($res) : null;

if (!$data) {
    echo '<div class="content-wrapper"><div class="container-fluid"><div class="alert alert-danger">Radiology not found.</div></div></div>';
    include BASE_PATH.'/admin/inc/footer.php';
    exit;
}

// Fetch assigned hospitals
$hospitals = [];
$hq = "SELECT h.*, ahr.status AS assign_status
       FROM assign_hospital_radiology ahr
       JOIN hospitals h ON h.hospital_id = ahr.hospital_id
       WHERE ahr.user_id = " . (int)$data['user_id'] . " AND ahr.status = 1
       ORDER BY h.hospital_name ASC";
$hr = mysqli_query($con, $hq);
if ($hr) while ($x = mysqli_fetch_assoc($hr)) $hospitals[] = $x;
?>

<style>
:root {
    --brand-navy: #1C3F8F;
    --brand-teal: #00A8CC;
    --brand-green: #27C96A;
    --brand-dark: #0D2B6E;
    --brand-bg: #F5F8FC;
}
.rad-page-wrap { padding: 10px 0 40px; }

/* Header */
.rad-header {
    background: linear-gradient(120deg, var(--brand-dark) 0%, var(--brand-navy) 55%, var(--brand-teal) 100%);
    color: #fff;
    padding: 22px 28px;
    border-radius: 14px;
    margin-bottom: 22px;
    box-shadow: 0 6px 22px rgba(28,63,143,0.25);
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px;
}
.rad-header h4 { margin: 0; color: #fff; font-weight: 700; }
.rad-header p { margin: 4px 0 0; color: rgba(255,255,255,0.8); font-size: 13px; }

/* Cards */
.rad-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e7eef7;
    box-shadow: 0 2px 10px rgba(13,43,110,0.05);
    margin-bottom: 22px;
    overflow: hidden;
}
.rad-card-header {
    background: var(--brand-bg);
    border-bottom: 1px solid #e7eef7;
    padding: 14px 22px;
    display: flex; align-items: center; gap: 10px;
}
.rad-card-header i { color: var(--brand-teal); font-size: 18px; }
.rad-card-header h5 { margin: 0; font-weight: 700; color: var(--brand-dark); font-size: 15px; }
.rad-card-body { padding: 22px; }

/* Buttons */
.rad-btn {
    background: linear-gradient(135deg, var(--brand-teal), var(--brand-green));
    color: #fff !important;
    border: none;
    padding: 10px 22px;
    border-radius: 10px;
    font-weight: 700; font-size: 13px;
    box-shadow: 0 4px 14px rgba(0,168,204,0.3);
    transition: all .2s ease;
    cursor: pointer;
    display: inline-flex; align-items: center; gap: 8px;
    text-decoration: none !important;
}
.rad-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,168,204,0.4); color: #fff !important; }

.rad-btn-outline {
    background: #fff;
    color: var(--brand-dark) !important;
    border: 1.5px solid #dbe6f4;
    padding: 9px 20px;
    border-radius: 10px;
    font-weight: 600; font-size: 13px;
    text-decoration: none !important;
    display: inline-flex; align-items: center; gap: 8px;
    transition: all .2s ease;
}
.rad-btn-outline:hover { border-color: var(--brand-teal); color: var(--brand-teal) !important; }

/* Hero Image */
.rad-pic-hero {
    text-align: center;
    padding: 26px 16px;
    background: linear-gradient(180deg, var(--brand-bg) 0%, #fff 100%);
}
.rad-pic-hero img {
    width: 130px; height: 130px;
    object-fit: cover;
    border-radius: 16px;
    border: 3px solid #fff;
    box-shadow: 0 8px 24px rgba(13,43,110,0.12);
}
.rad-pic-hero .empty {
    width: 130px; height: 130px;
    border-radius: 16px;
    background: var(--brand-bg);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 42px; color: #b8c5d6;
    border: 3px solid #fff;
    box-shadow: 0 8px 24px rgba(13,43,110,0.12);
}
.rad-pic-hero h5 { margin: 14px 0 4px; font-weight: 700; color: var(--brand-dark); }
.rad-pic-hero p { color: #8894a8; margin: 0 0 10px; font-size: 13px; }

/* Badges */
.rad-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px; font-weight: 700;
    display: inline-block;
}
.rad-badge-approved { background: #e8f9f0; color: #0d7a45; }
.rad-badge-pending  { background: #fff4e5; color: #b45309; }
.rad-badge-active   { background: #e8f9f0; color: #0d7a45; }
.rad-badge-inactive { background: #f0f0f0; color: #666; }

/* Info Tables */
.rad-info-table { width: 100%; }
.rad-info-table tr td { padding: 9px 0; border-bottom: 1px dashed #eef2f7; font-size: 13.5px; vertical-align: top; }
.rad-info-table tr:last-child td { border-bottom: none; }
.rad-info-table tr td:first-child { color: #8894a8; font-weight: 600; width: 35%; }

/* Data Tables (Hospitals) */
.rad-table { width: 100%; }
.rad-table thead th {
    background: var(--brand-bg);
    color: var(--brand-dark);
    font-size: 12px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.5px;
    padding: 11px 12px;
    border-bottom: 2px solid #e7eef7;
}
.rad-table tbody td {
    padding: 11px 12px;
    border-bottom: 1px solid #f0f4f9;
    font-size: 13px;
}
</style>

<div class="content-wrapper">
   <div class="container-fluid">
      <div class="rad-page-wrap">

         <!-- HEADER -->
         <div class="rad-header">
            <div>
               <h4><i class="ti-camera"></i> Radiology Detail</h4>
               <p><?php echo htmlspecialchars($data['rad_name']); ?></p>
            </div>
            <div>
               <a href="<?php echo BASE_URL; ?>admin/radiology-list.php" class="rad-btn-outline" style="margin-right:8px;">
                  <i class="ti-arrow-left"></i> Back
               </a>
               <a href="<?php echo BASE_URL; ?>admin/radiology-add.php?id=<?php echo $rad_id; ?>" class="rad-btn">
                  <i class="ti-pencil"></i> Edit
               </a>
            </div>
         </div>

         <!-- HERO + BASIC INFO -->
         <div class="row">
            <div class="col-md-4">
               <div class="rad-card">
                  <div class="rad-pic-hero">
                     <?php if (!empty($data['rad_pic'])): ?>
                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/radiology/<?php echo htmlspecialchars($data['rad_pic']); ?>">
                     <?php else: ?>
                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/radiology.jpg">
                     <?php endif; ?>
                     <h5><?php echo htmlspecialchars($data['rad_name']); ?></h5>
                     <?php if ($data['approve'] == 1): ?>
                        <span class="rad-badge rad-badge-approved">✓ Approved</span>
                     <?php else: ?>
                        <span class="rad-badge rad-badge-pending">⏳ Pending</span>
                     <?php endif; ?>
                  </div>
               </div>
            </div>

            <div class="col-md-8">
               <div class="rad-card">
                  <div class="rad-card-header">
                     <i class="ti-info-alt"></i>
                     <h5>Basic Information</h5>
                  </div>
                  <div class="rad-card-body">
                     <table class="rad-info-table">
                        <tr>
                           <td>Registration No</td>
                           <td><strong><?php echo htmlspecialchars($data['reg_no'] ?: '—'); ?></strong></td>
                        </tr>
                        <tr>
                           <td>City</td>
                           <td><?php echo htmlspecialchars($data['city_name'] ?? '—'); ?></td>
                        </tr>
                        <tr>
                           <td>Address</td>
                           <td><?php echo $data['rad_address'] ? nl2br(htmlspecialchars($data['rad_address'])) : '—'; ?></td>
                        </tr>
                        <tr>
                           <td>Phone</td>
                           <td><?php echo htmlspecialchars($data['rad_phone'] ?: '—'); ?></td>
                        </tr>
                        <tr>
                           <td>Public Email</td>
                           <td><?php echo htmlspecialchars($data['rad_email'] ?: '—'); ?></td>
                        </tr>
                        <tr>
                           <td>Description</td>
                           <td><?php echo $data['description'] ? nl2br(htmlspecialchars($data['description'])) : '—'; ?></td>
                        </tr>
                        <tr>
                           <td>Created</td>
                           <td><?php echo date('d M Y, h:i A', strtotime($data['created_at'])); ?></td>
                        </tr>
                     </table>
                  </div>
               </div>
            </div>
         </div>

         <!-- LOGIN INFO -->
         <div class="rad-card">
            <div class="rad-card-header">
               <i class="ti-lock"></i>
               <h5>Login Account</h5>
            </div>
            <div class="rad-card-body">
               <div class="row">
                  <div class="col-md-4">
                     <table class="rad-info-table">
                        <tr><td>Username</td><td><strong><?php echo htmlspecialchars($data['username'] ?? '—'); ?></strong></td></tr>
                     </table>
                  </div>
                  <div class="col-md-4">
                     <table class="rad-info-table">
                        <tr><td>Login Email</td><td><?php echo htmlspecialchars($data['login_email'] ?? '—'); ?></td></tr>
                     </table>
                  </div>
                  <div class="col-md-4">
                     <table class="rad-info-table">
                        <tr>
                           <td>Status</td>
                           <td>
                              <?php if ($data['user_status'] == 1): ?>
                                 <span class="rad-badge rad-badge-active">Active</span>
                              <?php else: ?>
                                 <span class="rad-badge rad-badge-inactive">Inactive</span>
                              <?php endif; ?>
                           </td>
                        </tr>
                     </table>
                  </div>
               </div>
            </div>
         </div>

         <!-- ASSIGNED HOSPITALS -->
         <div class="rad-card">
            <div class="rad-card-header">
               <i class="ti-hospital"></i>
               <h5>Assigned Hospitals</h5>
            </div>
            <div class="rad-card-body">
               <?php if (empty($hospitals)): ?>
                  <p class="text-muted mb-0">No hospitals assigned yet.</p>
               <?php else: ?>
                  <div class="table-responsive">
                     <table class="rad-table">
                        <thead>
                           <tr>
                              <th>#</th>
                              <th>Hospital Name</th>
                              <th>City ID</th>
                              <th>Status</th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php foreach ($hospitals as $i => $h): ?>
                              <tr>
                                 <td><strong><?php echo $i+1; ?></strong></td>
                                 <td><?php echo htmlspecialchars($h['hospital_name'] ?? $h['name'] ?? '—'); ?></td>
                                 <td><?php echo htmlspecialchars($h['city_id'] ?? '—'); ?></td>
                                 <td>
                                    <?php if ($h['assign_status'] == 1): ?>
                                       <span class="rad-badge rad-badge-active">Active</span>
                                    <?php else: ?>
                                       <span class="rad-badge rad-badge-inactive">Inactive</span>
                                    <?php endif; ?>
                                 </td>
                              </tr>
                           <?php endforeach; ?>
                        </tbody>
                     </table>
                  </div>
               <?php endif; ?>
            </div>
         </div>

      </div>
   </div>
</div>

<?php include BASE_PATH.'/admin/inc/footer.php';?>