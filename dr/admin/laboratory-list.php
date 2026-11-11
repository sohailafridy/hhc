<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>
<?php
// ── SehatNexa Brand Colors (fallback) ──
if (!isset($color1)) {
    $color1 = '#1C3F8F'; $color2 = '#00A8CC'; $color3 = '#27C96A'; $color4 = '#00C9A7';
    $color5 = '#0D2B6E'; $color6 = '#E8F7FF'; $color7 = '#F0FFF8'; $color8 = '#FFFFFF';
    $gradient_primary = 'linear-gradient(135deg, #1C3F8F 0%, #00A8CC 100%)';
    $gradient_green   = 'linear-gradient(135deg, #27C96A 0%, #00C9A7 100%)';
}

// Handle delete operation
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $pic_query = "SELECT lab_pic FROM laboratories WHERE user_id = $delete_id";
    $pic_result = mysqli_query($con, $pic_query);
    $lab_pic_data = mysqli_fetch_assoc($pic_result);
    $lab_pic = $lab_pic_data ? $lab_pic_data['lab_pic'] : '';
    $delete_query = "UPDATE users set status=0 WHERE user_id = $delete_id";
    if (mysqli_query($con, $delete_query)) {
        $_SESSION['success_msg'] = "Laboratory deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }
    header('Location: ' . BASE_URL . 'admin/laboratories/list');
    exit();
}
?>

<?php include BASE_PATH.'/admin/inc/header.php';?>
<?php include BASE_PATH.'/admin/inc/top.php';?>
<?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
$records_per_page = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

$search_lab     = isset($_GET['search_lab']) ? mysqli_real_escape_string($con, $_GET['search_lab']) : '';
$search_city    = isset($_GET['search_city']) ? mysqli_real_escape_string($con, $_GET['search_city']) : '';
$search_reg_no  = isset($_GET['search_reg_no']) ? mysqli_real_escape_string($con, $_GET['search_reg_no']) : '';
$filter_city_id = isset($_GET['filter_city_id']) ? mysqli_real_escape_string($con, $_GET['filter_city_id']) : '';
$filter_status  = isset($_GET['filter_status']) ? mysqli_real_escape_string($con, $_GET['filter_status']) : '';
$filter_type    = isset($_GET['filter_type']) ? mysqli_real_escape_string($con, $_GET['filter_type']) : '';

$where_conditions = [];
if (!empty($search_lab))     $where_conditions[] = "(l.lab_name LIKE '%$search_lab%' OR l.lab_email LIKE '%$search_lab%')";
if (!empty($search_city))    $where_conditions[] = "c.city_name LIKE '%$search_city%'";
if (!empty($search_reg_no))  $where_conditions[] = "l.reg_no LIKE '%$search_reg_no%'";
if (!empty($filter_city_id) && is_numeric($filter_city_id)) $where_conditions[] = "l.city_id = $filter_city_id";
if ($filter_status !== '')   $where_conditions[] = "u.status = " . (int)$filter_status;
else                         $where_conditions[] = "u.status = 1";
if ($filter_type !== '')     $where_conditions[] = "l.lab_type = " . (int)$filter_type;
$where_conditions[] = "l.approve = 1";

$cities_query = "SELECT city_id, city_name FROM cities WHERE status = 1 ORDER BY city_name ASC";
$cities_result = mysqli_query($con, $cities_query);

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$count_query = "SELECT COUNT(*) as total FROM laboratories l
                LEFT JOIN users u ON u.user_id = l.user_id
                LEFT JOIN cities c ON l.city_id = c.city_id
                $where_clause";
$count_result = mysqli_query($con, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $records_per_page);

$query = "SELECT l.*, c.city_name, h.hospital_name, u.user_id as u_id, u.status as estatus,
                COUNT(f.feedback_id) as total_feedbacks, AVG(f.stars) as avg_rating
          FROM laboratories l
          LEFT JOIN cities c ON l.city_id = c.city_id
          LEFT JOIN hospitals h ON l.hospital_id = h.hospital_id
          LEFT JOIN feedback f ON l.user_id = f.user_id
          LEFT JOIN users u ON u.user_id = l.user_id
          $where_clause
          GROUP BY l.lab_id
          ORDER BY l.created_at DESC
          LIMIT $offset, $records_per_page";
$result = mysqli_query($con, $query);
?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/lab-list-admin.css">

<style>
/* ── SehatNexa Brand Overrides ─────────────────────────── */
:root{
   --sn-navy: <?= $color1 ?>; --sn-teal: <?= $color2 ?>; --sn-green: <?= $color3 ?>;
   --sn-tealgreen: <?= $color4 ?>; --sn-dark: <?= $color5 ?>;
   --sn-lightblue: <?= $color6 ?>; --sn-lightgreen: <?= $color7 ?>;
}
body{ background: var(--sn-lightblue); }

.main-header h4{ color: var(--sn-navy); font-weight:700; }
.main-header .btn-primary{
   background: <?= $gradient_primary ?>; border:none; border-radius:10px;
   padding:9px 18px; font-weight:600; box-shadow:0 4px 14px rgba(28,63,143,.25);
}
.main-header .btn-primary:hover{ transform:translateY(-2px); box-shadow:0 6px 18px rgba(28,63,143,.35); }

/* ── SEARCH FILTER CARD (compact, no overlap) ─────────── */
.search-filter-card{
   background: <?= $gradient_primary ?>;
   box-shadow:0 10px 40px rgba(28,63,143,.22);
   padding:22px 24px;
   border-radius:18px;
}
.search-filter-card h5{ font-size:1.05rem; margin-bottom:16px !important; }

/* Uniform compact input sizing */
.search-filter-card .form-label{
   font-size:.72rem; margin-bottom:5px; font-weight:600;
   white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.search-filter-card .form-control,
.search-filter-card .form-select{
   padding:7px 10px;
   font-size:.82rem;
   border-radius:9px;
   height:38px;
   width:100%;
   box-sizing:border-box;
}
.search-filter-card .btn{
   padding:8px 14px;
   font-size:.78rem;
   border-radius:9px;
   height:38px;
   white-space:nowrap;
}
.search-filter-card .btn-primary{ background: <?= $gradient_green ?>; }
.search-filter-card .btn-primary:hover{ box-shadow:0 6px 20px rgba(39,201,106,.4); }
.search-filter-card .btn-secondary{
   background: linear-gradient(135deg, <?= $color5 ?> 0%, <?= $color1 ?> 100%);
}
.search-filter-card .form-check-input:checked{ background-color: var(--sn-green); border-color: var(--sn-green); }

/* Compact row spacing */
.search-filter-card .row{ --bs-gutter-x: .75rem; --bs-gutter-y: .75rem; }
.search-filter-card .form-group{ margin-bottom:0; }

/* ── LABORATORY CARDS (wider + cleaner spacing) ───────── */
.laboratory-profile-card{
   background: var(--sn-lightgreen);
   border:1px solid rgba(28,63,143,.08);
   border-radius:12px;
}
.laboratory-profile-card:hover{ box-shadow:0 10px 28px rgba(28,63,143,.18); }

.laboratory-profile-header{
   background: <?= $gradient_primary ?>;
   padding:14px 12px;
}
.laboratory-avatar{ border-color: <?= $color8 ?>; }
.laboratory-profile-header h5{
   font-size:.88rem;
   line-height:1.25;
   margin-bottom:6px;
   overflow:hidden;
   text-overflow:ellipsis;
   display:-webkit-box;
   -webkit-line-clamp:2;
   -webkit-box-orient:vertical;
}
.laboratory-profile-body{ padding:12px; }

.info-label{ color: var(--sn-dark); font-size:.7rem; }
.info-value{ color:#212529; font-size:.82rem; word-break:break-word; }
.laboratory-info-item{ border-bottom:1px solid rgba(28,63,143,.1); padding-bottom:7px; margin-bottom:7px; }

/* Card header */
.card-header{ background: <?= $color6 ?>; border-bottom:2px solid rgba(28,63,143,.08); }
.card-header-text{ color: var(--sn-navy); font-weight:700; }

/* Pagination */
.page-link{ color: var(--sn-navy); border-color: rgba(28,63,143,.15); }
.page-link:hover{ background: var(--sn-lightblue); color: var(--sn-navy); }
.page-item.active .page-link{
   background: <?= $gradient_primary ?>; border-color: var(--sn-navy); color:#fff;
}

/* ── Action buttons row (no overlap, fixed flex) ──────── */
.lab-actions{
   display:flex;
   align-items:center;
   justify-content:space-between;
   gap:6px;
   margin-top:10px;
}
.lab-actions-right{
   display:flex;
   align-items:center;
   gap:6px;
}
.btn-view-lab,
.btn-edit-lab,
.btn-delete-lab{
   display:inline-flex;
   align-items:center;
   justify-content:center;
   gap:4px;
   font-size:.7rem;
   padding:6px 9px;
   border-radius:7px;
   border:none;
   line-height:1;
   white-space:nowrap;
}
.btn-view-lab{ background: <?= $gradient_green ?>; color:#fff !important; }
.btn-view-lab:hover{ transform:translateY(-1px); box-shadow:0 4px 10px rgba(39,201,106,.4); }
.btn-edit-lab{ background: <?= $color2 ?>; color:#fff !important; padding:6px 8px; }
.btn-edit-lab:hover{ transform:translateY(-1px); box-shadow:0 4px 10px rgba(0,168,204,.4); }
.btn-delete-lab{ background: <?= $color1 ?>; color:#fff !important; padding:6px 8px; }
.btn-delete-lab:hover{ transform:translateY(-1px); background: <?= $color5 ?>; }

/* RegNo pill */
.regno-pill{
   display:inline-block;
   background: <?= $color6 ?>;
   color: <?= $color1 ?>;
   border:1px dashed <?= $color2 ?>;
   font-size:.65rem;
   font-weight:700;
   padding:2px 7px;
   border-radius:20px;
   letter-spacing:.3px;
   max-width:100%;
   overflow:hidden;
   text-overflow:ellipsis;
   white-space:nowrap;
}

/* Wider cards: 2 per row on xl, 3 on lg, 4 on md */
@media (min-width: 1400px) { .lab-col { flex: 0 0 20%; max-width: 20%; } }
@media (min-width: 1200px) and (max-width: 1399px) { .lab-col { flex: 0 0 25%; max-width: 25%; } }
@media (min-width: 992px) and (max-width: 1199px) { .lab-col { flex: 0 0 33.3333%; max-width: 33.3333%; } }
@media (min-width: 768px) and (max-width: 991px) { .lab-col { flex: 0 0 33.3333%; max-width: 33.3333%; } }
@media (max-width: 767px) { .lab-col { flex: 0 0 50%; max-width: 50%; } }

/* Remove old fixed height that was cropping cards */
.height{ height:auto !important; margin-bottom:16px !important; }
</style>

<div class="content-wrapper">
   <div class="container-fluid">
      <div class="row">
         <div class="main-header">
            <h4><i class="fas fa-flask me-2" style="color: <?= $color3 ?>;"></i>Laboratories List</h4>
            <a href="<?php echo BASE_URL; ?>admin/laboratories/add" class="btn btn-primary pull-right">
               <i class="icon-plus"></i> Add Laboratory
            </a>
         </div>
      </div>
      
      <?php if (isset($_SESSION['success_msg'])): ?>
         <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
         </div>
      <?php endif; ?>
      <?php if (isset($_SESSION['error_msg'])): ?>
         <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
         </div>
      <?php endif; ?>
      
      <!-- Search and Filter Section -->
      <div class="row">
         <div class="col-lg-12">
            <div class="search-filter-card">
               <h5 class="mb-3"><i class="fas fa-search me-2"></i>Search & Filter</h5>
               <form method="GET" action="">
                  <div class="row g-2 align-items-end">
                     <!-- Search Laboratory -->
                     <div class="col-xl-3 col-lg-4 col-md-6">
                        <label for="search_lab" class="form-label">
                           <i class="fas fa-flask me-1"></i>Laboratory
                        </label>
                        <input type="text" class="form-control" id="search_lab" name="search_lab"
                               value="<?php echo htmlspecialchars($search_lab); ?>"
                               placeholder="Name or Email">
                     </div>

                     <!-- Reg No -->
                     <div class="col-xl-2 col-lg-4 col-md-6">
                        <label for="search_reg_no" class="form-label">
                           <i class="fas fa-id-card me-1"></i>Reg No
                        </label>
                        <input type="text" class="form-control" id="search_reg_no" name="search_reg_no"
                               value="<?php echo htmlspecialchars($search_reg_no); ?>"
                               placeholder="e.g. LAB-001">
                     </div>

                     <!-- City -->
                     <div class="col-xl-2 col-lg-2 col-md-4">
                        <label for="filter_city_id" class="form-label">
                           <i class="fas fa-city me-1"></i>City
                        </label>
                        <select class="form-select" id="filter_city_id" name="filter_city_id">
                           <option value="">All</option>
                           <?php mysqli_data_seek($cities_result, 0);
                           while ($city = mysqli_fetch_assoc($cities_result)): ?>
                              <option value="<?php echo $city['city_id']; ?>"
                                      <?php echo ($filter_city_id == $city['city_id']) ? 'selected' : ''; ?>>
                                 <?php echo htmlspecialchars($city['city_name']); ?>
                              </option>
                           <?php endwhile; ?>
                        </select>
                     </div>

                     <!-- Status -->
                     <div class="col-xl-1 col-lg-2 col-md-4">
                        <label for="filter_status" class="form-label">
                           <i class="fas fa-toggle-on me-1"></i>Status
                        </label>
                        <select class="form-select" id="filter_status" name="filter_status">
                           <option value="">All</option>
                           <option value="1" <?php echo ($filter_status == '1') ? 'selected' : ''; ?>>Active</option>
                           <option value="0" <?php echo ($filter_status == '0') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                     </div>

                     <!-- Type -->
                     <div class="col-xl-2 col-lg-3 col-md-4">
                        <label for="filter_type" class="form-label">
                           <i class="fas fa-hospital me-1"></i>Type
                        </label>
                        <select class="form-select" id="filter_type" name="filter_type">
                           <option value="">All</option>
                           <option value="1" <?php echo ($filter_type == '1') ? 'selected' : ''; ?>>Hospital Lab</option>
                           <option value="2" <?php echo ($filter_type == '2') ? 'selected' : ''; ?>>Independent</option>
                        </select>
                     </div>

                     <!-- Buttons -->
                     <div class="col-xl-2 col-lg-3 col-md-12">
                        <div class="d-flex gap-2">
                           <button type="submit" class="btn btn-primary flex-fill">
                              <i class="fas fa-search me-1"></i>Search
                           </button>
                           <a href="<?php echo BASE_URL; ?>admin/laboratories/list" class="btn btn-secondary flex-fill">
                              <i class="fas fa-redo me-1"></i>Reset
                           </a>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
      
      <!-- Laboratories List -->
      <div class="row">
         <div class="col-lg-12">
            <div class="card">
               <div class="card-header">
                  <h5 class="card-header-text">
                     <i class="fas fa-list me-2"></i>Laboratories (<?php echo $total_records; ?> records)
                  </h5>
               </div>
               <div class="card-block">
                  <?php if (mysqli_num_rows($result) > 0): ?>
                     <div class="row">
                        <?php while ($lab = mysqli_fetch_assoc($result)): ?>
                           <div class="col-lg-3 col-md-3 col-sm-4 col-6 mb-3 lab-col height">
                              <div class="laboratory-profile-card text-center">
                                 <div class="laboratory-profile-header">
                                    <?php if (!empty($lab['lab_pic'])): ?>
                                       <img src="<?php echo BASE_URL; ?>admin/inc/uploads/laboratories/<?php echo $lab['lab_pic']; ?>"
                                            alt="<?php echo htmlspecialchars($lab['lab_name']); ?>"
                                            class="laboratory-avatar mb-2">
                                    <?php else: ?>
                                       <div class="laboratory-avatar bg-white d-flex align-items-center justify-content-center mb-2">
                                          <i class="fas fa-flask fa-3x" style="color: <?= $color3 ?>;"></i>
                                       </div>
                                    <?php endif; ?>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($lab['lab_name']); ?></h5>

                                    <?php if (!empty($lab['reg_no'])): ?>
                                       <div class="mb-2">
                                          <span class="regno-pill">
                                             <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($lab['reg_no']); ?>
                                          </span>
                                       </div>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-center flex-wrap gap-1">
                                       <?php if ($lab['estatus'] == 1): ?>
                                          <span class="badge-modern badge" style="background: <?= $color3 ?>;">Active</span>
                                       <?php else: ?>
                                          <span class="badge-modern badge bg-danger">Inactive</span>
                                       <?php endif; ?>
                                       <?php if ($lab['lab_type'] == 1): ?>
                                          <span class="badge-modern badge" style="background-color: <?= $color2 ?>;">
                                             <i class="fas fa-hospital me-1"></i>Hospital
                                          </span>
                                       <?php else: ?>
                                          <span class="badge-modern badge" style="background-color: <?= $color4 ?>; color: #0D2B6E;">
                                             <i class="fas fa-building me-1"></i>Independent
                                          </span>
                                       <?php endif; ?>
                                       <span class="badge-modern badge" style="background: <?= $color1 ?>;">
                                          <i class="fas fa-comments me-1"></i><?php echo (int)$lab['total_feedbacks']; ?>
                                       </span>
                                    </div>
                                 </div>
                                 
                                 <div class="laboratory-profile-body">
                                    <div class="laboratory-info-item text-start">
                                       <div class="info-label">
                                          <i class="fas fa-city me-1" style="color: <?= $color3 ?>;"></i>City
                                       </div>
                                       <div class="info-value"><?php echo htmlspecialchars($lab['city_name'] ?? 'N/A'); ?></div>
                                    </div>
                                    
                                    <?php if ($lab['lab_type'] == 1 && !empty($lab['hospital_name'])): ?>
                                       <div class="laboratory-info-item text-start">
                                          <div class="info-label">
                                             <i class="fas fa-hospital me-1" style="color: <?= $color2 ?>;"></i>Hospital
                                          </div>
                                          <div class="info-value"><?php echo htmlspecialchars($lab['hospital_name']); ?></div>
                                       </div>
                                    <?php endif; ?>
                                    
                                    <div class="laboratory-info-item text-start">
                                       <div class="info-label">
                                          <i class="fas fa-phone me-1" style="color: <?= $color4 ?>;"></i>Contact
                                       </div>
                                       <div class="info-value"><?php echo htmlspecialchars($lab['lab_phone']); ?></div>
                                    </div>
                                    
                                    <div class="lab-actions">
                                       <a href="<?php echo BASE_URL; ?>admin/laboratories/detail?id=<?php echo $lab['lab_id']; ?>"
                                          class="btn-view-lab">
                                          <i class="fas fa-eye"></i> View
                                       </a>
                                       <div class="lab-actions-right">
                                          <a href="<?php echo BASE_URL; ?>admin/laboratories/add?id=<?php echo $lab['lab_id']; ?>"
                                             class="btn-edit-lab" title="Edit">
                                             <i class="icon-pencil"></i>
                                          </a>
                                          <a href="javascript:void(0)" onclick="deleteLaboratory(<?php echo $lab['user_id']; ?>)"
                                             class="btn-delete-lab" title="Delete">
                                             <i class="icon-trash"></i>
                                          </a>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        <?php endwhile; ?>
                     </div>
                  <?php else: ?>
                     <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>No laboratories found.
                     </div>
                  <?php endif; ?>
                  
                  <!-- Pagination -->
                  <?php if ($total_pages > 1): ?>
                     <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                           <?php if ($page > 1): ?>
                              <li class="page-item">
                                 <a class="page-link" href="?page=<?php echo $page - 1; ?>&search_lab=<?php echo urlencode($search_lab); ?>&search_reg_no=<?php echo urlencode($search_reg_no); ?>&search_city=<?php echo urlencode($search_city); ?>&filter_city_id=<?php echo $filter_city_id; ?>&filter_status=<?php echo $filter_status; ?>&filter_type=<?php echo $filter_type; ?>">
                                    <i class="fas fa-chevron-left me-1"></i>Previous
                                 </a>
                              </li>
                           <?php endif; ?>
                           <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                              <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                 <a class="page-link" href="?page=<?php echo $i; ?>&search_lab=<?php echo urlencode($search_lab); ?>&search_reg_no=<?php echo urlencode($search_reg_no); ?>&search_city=<?php echo urlencode($search_city); ?>&filter_city_id=<?php echo $filter_city_id; ?>&filter_status=<?php echo $filter_status; ?>&filter_type=<?php echo $filter_type; ?>">
                                    <?php echo $i; ?>
                                 </a>
                              </li>
                           <?php endfor; ?>
                           <?php if ($page < $total_pages): ?>
                              <li class="page-item">
                                 <a class="page-link" href="?page=<?php echo $page + 1; ?>&search_lab=<?php echo urlencode($search_lab); ?>&search_reg_no=<?php echo urlencode($search_reg_no); ?>&search_city=<?php echo urlencode($search_city); ?>&filter_city_id=<?php echo $filter_city_id; ?>&filter_status=<?php echo $filter_status; ?>&filter_type=<?php echo $filter_type; ?>">
                                    Next<i class="fas fa-chevron-right ms-1"></i>
                                 </a>
                              </li>
                           <?php endif; ?>
                        </ul>
                     </nav>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<script>
function deleteLaboratory(user_id) {
    if (confirm('Are you sure you want to delete this laboratory? This action cannot be undone.')) {
        window.location.href = '?delete_id=' + user_id;
    }
}
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>