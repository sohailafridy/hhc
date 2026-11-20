<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>
<?php include_once BASE_PATH . '/includes/colors.php'; ?>
<?php

// Handle delete operation - MUST be before any HTML output
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];

    // First, get the hospital picture to delete the file
    $pic_query = "SELECT hospital_pic FROM hospitals WHERE user_id = $delete_id";
    $pic_result = mysqli_query($con, $pic_query);
    $hospital_pic_data = mysqli_fetch_assoc($pic_result);
    $hospital_pic = $hospital_pic_data ? $hospital_pic_data['hospital_pic'] : '';

    // Delete the hospital from database
    $delete_query = "UPDATE users SET status = 0 WHERE user_id = $delete_id";

    if (mysqli_query($con, $delete_query)) {
        // Delete the picture file if it exists
        //   if (!empty($hospital_pic)) {
        //       $pic_path = BASE_PATH."/admin/inc/uploads/hospitals/".$hospital_pic;
        //       if (file_exists($pic_path)) {
        //           unlink($pic_path);
        //       }
        //   }
        $_SESSION['success_msg'] = "Hospital deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }

    // Redirect to remove delete_id from URL
    header('Location: ' . BASE_URL . 'admin/hospitals/list');
    exit();
}
?>

<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
<?php include BASE_PATH.'/admin/inc/top.php';?>
<!-- Side-Nav-->
<?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
// Pagination variables
$records_per_page = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Search filters
$search_hospital = isset($_GET['search_hospital']) ? mysqli_real_escape_string($con, trim($_GET['search_hospital'])) : '';
$search_city     = isset($_GET['search_city'])     ? mysqli_real_escape_string($con, trim($_GET['search_city']))     : '';
$filter_status   = isset($_GET['filter_status'])   ? mysqli_real_escape_string($con, $_GET['filter_status'])         : '';

// Build WHERE clause
$where_conditions = [];
if (!empty($search_hospital)) {
    $where_conditions[] = "h.hospital_name LIKE '%$search_hospital%'";
}
if (!empty($search_city)) {
    $where_conditions[] = "c.city_name LIKE '%$search_city%'";
}
if ($filter_status !== '') {
    $where_conditions[] = "u.status = " . (int)$filter_status;
}
$where_conditions[] = "h.approve = 1";
$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Count total records
$count_query = "SELECT COUNT(*) as total FROM hospitals h LEFT JOIN cities c ON h.city_id = c.city_id LEFT JOIN users u ON u.user_id = h.user_id $where_clause";
$count_result = mysqli_query($con, $count_query);
$total_records = (int)mysqli_fetch_assoc($count_result)['total'];
$total_pages = max(1, (int)ceil($total_records / $records_per_page));
$page   = min($page, $total_pages);
$offset = ($page - 1) * $records_per_page;

// Fetch hospitals data
$query = "SELECT h.user_id, h.hospital_id, h.hospital_name, h.hospital_address, h.hospital_phone, h.hospital_pic, u.status, h.created_at, c.city_name 
          FROM hospitals h 
          LEFT JOIN cities c ON h.city_id = c.city_id 
          LEFT JOIN users u ON u.user_id = h.user_id
          $where_clause
          ORDER BY h.created_at DESC 
          LIMIT $offset, $records_per_page";

$result = mysqli_query($con, $query);

// Query string for pagination links
$qs = http_build_query([
    'search_hospital' => $_GET['search_hospital'] ?? '',
    'search_city'     => $_GET['search_city'] ?? '',
    'filter_status'   => $_GET['filter_status'] ?? '',
]);
function page_link($qs, $p) { return '?page=' . (int)$p . '&' . $qs; }

$has_filters = ($search_hospital !== '' || $search_city !== '' || $filter_status !== '');
?>

<style>
:root {
    --navy-deep:   <?= $color5 ?>;
    --navy:        <?= $color1 ?>;
    --teal:        <?= $color2 ?>;
    --green:       <?= $color3 ?>;
    --teal-green:  <?= $color4 ?>;
    --tint-blue:   <?= $color6 ?>;
    --tint-green:  <?= $color7 ?>;
    --white:       <?= $color8 ?>;
    --g-navy:      linear-gradient(135deg, <?= $color5 ?> 0%, <?= $color1 ?> 100%);
    --g-navy-teal: linear-gradient(135deg, <?= $color5 ?> 0%, <?= $color1 ?> 62%, <?= $color2 ?> 135%);
    --g-accent:    linear-gradient(90deg, <?= $color2 ?>, <?= $color3 ?>, <?= $color4 ?>);
    --line:        #dbe4f2;
    --muted:       #5b6b86;
    --shadow:      0 10px 30px rgba(13, 43, 110, .10);
    --ring:        0 0 0 4px rgba(0, 168, 204, .18);
}

/* ================= Page header ================= */
.content-wrapper .main-header {
    display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 20px;
}
.content-wrapper .main-header h4 { margin: 0; font-weight: 800; font-size: 1.5rem; color: var(--navy-deep); }
.content-wrapper .main-header .btn-primary {
    float: none !important; background: var(--g-navy); border: 0; color: var(--white);
    border-radius: 10px; font-weight: 700; padding: 10px 20px;
    box-shadow: 0 6px 16px rgba(13,43,110,.28); transition: transform .2s, box-shadow .2s, filter .2s;
}
.content-wrapper .main-header .btn-primary:hover { transform: translateY(-1px); filter: brightness(1.12); box-shadow: 0 10px 22px rgba(13,43,110,.35); }

/* ================= Alerts ================= */
.content-wrapper .alert { border: 0; border-radius: 12px; padding: 14px 18px; font-weight: 500; box-shadow: 0 4px 14px rgba(13,43,110,.06); }
.content-wrapper .alert-success { background: var(--tint-green); color: #14703a; border-left: 5px solid var(--green); }
.content-wrapper .alert-danger  { background: #fef2f2; color: #b91c1c; border-left: 5px solid #dc2626; }

/* ================= Cards ================= */
.content-wrapper .card { border: 1px solid var(--line); border-radius: 18px; box-shadow: var(--shadow); overflow: hidden; margin-bottom: 24px; }
.content-wrapper .card > .card-header { background: var(--g-navy-teal); border: 0; padding: 18px 26px; position: relative; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.content-wrapper .card > .card-header::after { content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 4px; background: var(--g-accent); }
.content-wrapper .card > .card-header .card-header-text { margin: 0; color: var(--white); font-weight: 700; font-size: 1.05rem; display: flex; align-items: center; gap: 10px; }
.content-wrapper .card > .card-header .card-header-text i { color: var(--green); }
.count-pill { background: rgba(255,255,255,.16); color: var(--white); border: 1px solid rgba(255,255,255,.3); border-radius: 999px; padding: 4px 14px; font-size: 13px; font-weight: 600; }
.card .card-block { padding: 24px 26px; }

/* ================= Filters ================= */
.card-block .form-group { margin-bottom: 0; }
.card-block label { font-size: 13px; font-weight: 600; color: var(--navy-deep); margin-bottom: 7px; }
.card-block .form-control {
    height: 46px; border: 1.5px solid #d3deef; border-radius: 10px; padding: 0 14px;
    font-size: 14px; color: var(--navy-deep); background: var(--white); transition: border-color .2s, box-shadow .2s;
}
.card-block .form-control::placeholder { color: #94a3b8; }
.card-block .form-control:hover { border-color: #b7c6e2; }
.card-block .form-control:focus { border-color: var(--teal); box-shadow: var(--ring); outline: 0; }
.filter-actions { display: flex; gap: 10px; align-items: flex-end; height: 100%; }
.filter-actions .btn { height: 46px; border-radius: 10px; font-weight: 700; padding: 0 22px; display: inline-flex; align-items: center; gap: 8px; }
.btn-search { background: var(--g-navy); color: var(--white); border: 0; box-shadow: 0 6px 16px rgba(13,43,110,.25); }
.btn-search:hover { color: var(--white); filter: brightness(1.12); }
.btn-reset { background: var(--white); color: var(--navy); border: 1.5px solid #c3cfe6; }
.btn-reset:hover { background: var(--tint-blue); border-color: var(--navy); color: var(--navy); }

/* ================= Table ================= */
.hosp-table { width: 100%; border-collapse: separate; border-spacing: 0; margin: 0; }
.hosp-table thead th {
    background: var(--tint-blue); color: var(--navy-deep); font-size: 13px; font-weight: 700;
    padding: 14px 16px; border: 0; border-bottom: 2px solid var(--line); white-space: nowrap;
}
.hosp-table thead th:first-child { border-top-left-radius: 12px; }
.hosp-table thead th:last-child  { border-top-right-radius: 12px; }
.hosp-table tbody td { padding: 14px 16px; border: 0; border-bottom: 1px solid #edf1f8; vertical-align: middle; color: var(--navy-deep); font-size: 14px; }
.hosp-table tbody tr { transition: background .15s; }
.hosp-table tbody tr:hover { background: #f6f9fe; }
.hosp-table tbody tr:last-child td { border-bottom: 0; }

.row-num { color: var(--muted); font-weight: 700; }
.hosp-cell { display: flex; align-items: center; gap: 14px; min-width: 220px; }
.hosp-cell img { width: 52px; height: 52px; border-radius: 12px; object-fit: cover; border: 2px solid var(--tint-blue); box-shadow: 0 4px 10px rgba(13,43,110,.12); flex-shrink: 0; }
.hosp-name { font-weight: 700; color: var(--navy-deep); line-height: 1.3; }
.hosp-sub  { font-size: 12px; color: var(--muted); margin-top: 2px; }
.meta { display: inline-flex; align-items: center; gap: 7px; color: var(--navy-deep); }
.meta i { color: var(--teal); font-size: 12px; }

.status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; }
.status-pill::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
.status-pill.active   { background: var(--tint-green); color: #14903f; }
.status-pill.inactive { background: #fef2f2; color: #dc2626; }

.date-cell small { display: block; color: var(--muted); font-size: 12px; }

.action-group { display: flex; gap: 6px; }
.act-btn {
    width: 36px; height: 36px; border-radius: 10px; border: 0; display: inline-flex; align-items: center; justify-content: center;
    font-size: 14px; text-decoration: none; cursor: pointer; transition: transform .15s, filter .15s, box-shadow .15s;
}
.act-btn:hover { transform: translateY(-2px); filter: brightness(1.08); box-shadow: 0 6px 14px rgba(13,43,110,.2); text-decoration: none; }
.act-btn:focus-visible { outline: 3px solid var(--teal); outline-offset: 2px; }
.act-view   { background: var(--tint-blue);  color: var(--navy); }
.act-edit   { background: var(--tint-green); color: #14903f; }
.act-delete { background: #fef2f2; color: #dc2626; }

.empty-state { text-align: center; padding: 48px 16px; color: var(--muted); }
.empty-state i { font-size: 40px; margin-bottom: 12px; display: block; background: var(--g-navy-teal); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
.empty-state strong { display: block; color: var(--navy-deep); font-size: 16px; margin-bottom: 4px; }

/* ================= Pagination ================= */
.pager-wrap { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 20px; }
.pager-info { font-size: 13px; color: var(--muted); }
.pager-wrap .pagination { margin: 0; gap: 6px; flex-wrap: wrap; }
.pager-wrap .page-link {
    border: 1.5px solid var(--line); border-radius: 10px !important; color: var(--navy); font-weight: 600;
    min-width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; padding: 0 12px; background: var(--white);
}
.pager-wrap .page-link:hover { background: var(--tint-blue); border-color: var(--navy); color: var(--navy); }
.pager-wrap .page-item.active .page-link { background: var(--g-navy); border-color: var(--navy-deep); color: var(--white); box-shadow: 0 4px 12px rgba(13,43,110,.3); }
.pager-wrap .page-item.disabled .page-link { opacity: .45; pointer-events: none; }

/* ================= Responsive: table -> cards ================= */
@media (max-width: 991px) {
    .hosp-table thead { display: none; }
    .hosp-table, .hosp-table tbody, .hosp-table tr, .hosp-table td { display: block; width: 100%; }
    .hosp-table tbody tr {
        background: var(--white); border: 1px solid var(--line); border-radius: 14px; margin-bottom: 14px;
        padding: 14px; box-shadow: 0 4px 14px rgba(13,43,110,.06);
    }
    .hosp-table tbody tr:hover { background: var(--white); }
    .hosp-table tbody td { border: 0; padding: 8px 0; display: flex; align-items: center; justify-content: space-between; gap: 12px; text-align: right; }
    .hosp-table tbody td::before { content: attr(data-label); font-size: 12px; font-weight: 700; color: var(--muted); text-align: left; flex-shrink: 0; }
    .hosp-table tbody td.td-hospital { display: block; text-align: left; padding-top: 0; }
    .hosp-table tbody td.td-hospital::before, .hosp-table tbody td.td-num::before { display: none; }
    .hosp-table tbody td.td-num { display: none; }
    .hosp-table tbody td.td-actions { border-top: 1px dashed var(--line); margin-top: 6px; padding-top: 12px; }
    .hosp-cell { min-width: 0; }
    .card .card-block { padding: 18px 16px; }
    .table-responsive { overflow: visible; border: 0; }
}
@media (max-width: 767px) {
    .filter-actions { margin-top: 14px; height: auto; }
    .filter-actions .btn { flex: 1; justify-content: center; }
    .card-block .col-md-3, .card-block .col-md-2, .card-block .col-md-1 { margin-bottom: 14px; }
    .pager-wrap { justify-content: center; }
}
@media (prefers-reduced-motion: reduce) {
    .act-btn, .content-wrapper .main-header .btn-primary { transition: none; }
}
</style>

<div class="content-wrapper">
   <!-- Container-fluid starts -->
   <div class="container-fluid">

      <!-- ===== PAGE HEADER ===== -->
      <div class="row">
         <div class="main-header">
            <h4>Hospitals List</h4>
            <a href="<?php echo BASE_URL; ?>admin/hospitals/add" class="btn btn-primary pull-right">
               <i class="fas fa-plus me-1"></i> Add Hospital
            </a>
         </div>
      </div>

      <?php
      // Display session messages
      if (isset($_SESSION['success_msg'])): ?>
         <div class="alert alert-success"><i class="fas fa-check-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['success_msg']); unset($_SESSION['success_msg']); ?></div>
      <?php endif; ?>

      <?php if (isset($_SESSION['error_msg'])): ?>
         <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['error_msg']); unset($_SESSION['error_msg']); ?></div>
      <?php endif; ?>

      <!-- ===== SEARCH & FILTER ===== -->
      <div class="row">
         <div class="col-lg-12">
            <div class="card">
               <div class="card-header">
                  <h5 class="card-header-text"><i class="fas fa-filter"></i> Search &amp; Filter</h5>
               </div>
               <div class="card-block">
                  <form method="GET" action="">
                     <div class="row">
                        <div class="col-md-3">
                           <div class="form-group">
                              <label for="search_hospital">Hospital Name</label>
                              <input type="text" class="form-control" id="search_hospital" name="search_hospital"
                                     value="<?php echo htmlspecialchars($_GET['search_hospital'] ?? ''); ?>" placeholder="Enter hospital name">
                           </div>
                        </div>
                        <div class="col-md-3">
                           <div class="form-group">
                              <label for="search_city">City Name</label>
                              <input type="text" class="form-control" id="search_city" name="search_city"
                                     value="<?php echo htmlspecialchars($_GET['search_city'] ?? ''); ?>" placeholder="Enter city name">
                           </div>
                        </div>
                        <div class="col-md-3">
                           <div class="form-group">
                              <label for="filter_status">Status</label>
                              <select class="form-control" id="filter_status" name="filter_status">
                                 <option value="">All Status</option>
                                 <option value="1" <?php echo ($filter_status === '1') ? 'selected' : ''; ?>>Active</option>
                                 <option value="0" <?php echo ($filter_status === '0') ? 'selected' : ''; ?>>Inactive</option>
                              </select>
                           </div>
                        </div>
                        <div class="col-md-3">
                           <div class="filter-actions">
                              <button type="submit" class="btn btn-search"><i class="fas fa-search"></i> Search</button>
                              <a href="<?php echo BASE_URL; ?>admin/hospitals/list" class="btn btn-reset"><i class="fas fa-undo"></i> Reset</a>
                           </div>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>

      <!-- ===== HOSPITALS TABLE ===== -->
      <div class="row">
         <div class="col-lg-12">
            <div class="card">
               <div class="card-header">
                  <h5 class="card-header-text"><i class="fas fa-hospital"></i> Hospitals</h5>
                  <span class="count-pill"><?php echo $total_records; ?> records</span>
               </div>
               <div class="card-block">
                  <div class="table-responsive">
                     <table class="table hosp-table">
                        <thead>
                           <tr>
                              <th>#</th>
                              <th>Hospital</th>
                              <th>City</th>
                              <th>Helpline No</th>
                              <th>Status</th>
                              <th>Created At</th>
                              <th>Actions</th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php if (mysqli_num_rows($result) > 0): ?>
                              <?php $serial = $offset + 1; ?>
                              <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                 <tr>
                                    <td class="td-num row-num" data-label="#"><?php echo $serial++; ?></td>
                                    <td class="td-hospital" data-label="Hospital">
                                       <div class="hosp-cell">
                                          <?php if (!empty($row['hospital_pic'])): ?>
                                             <img src="<?php echo BASE_URL; ?>admin/inc/uploads/hospitals/<?php echo htmlspecialchars($row['hospital_pic']); ?>"
                                                  alt="<?php echo htmlspecialchars($row['hospital_name']); ?>">
                                          <?php else: ?>
                                             <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/hosp.jpg" alt="No Image">
                                          <?php endif; ?>
                                          <div>
                                             <div class="hosp-name"><?php echo htmlspecialchars($row['hospital_name']); ?></div>
                                             <?php if (!empty($row['hospital_address'])): ?>
                                                <div class="hosp-sub"><?php echo htmlspecialchars(mb_strimwidth($row['hospital_address'], 0, 48, '…')); ?></div>
                                             <?php endif; ?>
                                          </div>
                                       </div>
                                    </td>
                                    <td data-label="City">
                                       <span class="meta"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['city_name'] ?? '—'); ?></span>
                                    </td>
                                    <td data-label="Helpline">
                                       <span class="meta"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['hospital_phone'] ?: '—'); ?></span>
                                    </td>
                                    <td data-label="Status">
                                       <?php if ($row['status'] == 1): ?>
                                          <span class="status-pill active">Active</span>
                                       <?php else: ?>
                                          <span class="status-pill inactive">Inactive</span>
                                       <?php endif; ?>
                                    </td>
                                    <td class="date-cell" data-label="Created">
                                       <div><?php echo date('d M Y', strtotime($row['created_at'])); ?><small><?php echo date('h:i A', strtotime($row['created_at'])); ?></small></div>
                                    </td>
                                    <td class="td-actions" data-label="Actions">
                                       <div class="action-group">
                                          <a href="<?php echo BASE_URL; ?>admin/hospitals/detail?id=<?php echo $row['hospital_id']; ?>&user_id=<?php echo $row['user_id']; ?>"
                                             class="act-btn act-view" title="View Details" aria-label="View details">
                                             <i class="fas fa-eye"></i>
                                          </a>
                                          <a href="<?php echo BASE_URL; ?>admin/hospitals/add?id=<?php echo $row['hospital_id']; ?>"
                                             class="act-btn act-edit" title="Edit" aria-label="Edit">
                                             <i class="fas fa-pen"></i>
                                          </a>
                                          <a href="javascript:void(0)" onclick="deleteHospital(<?php echo (int)$row['user_id']; ?>)"
                                             class="act-btn act-delete" title="Delete" aria-label="Delete">
                                             <i class="fas fa-trash"></i>
                                          </a>
                                       </div>
                                    </td>
                                 </tr>
                              <?php endwhile; ?>
                           <?php else: ?>
                              <tr>
                                 <td colspan="7">
                                    <div class="empty-state">
                                       <i class="fas fa-hospital"></i>
                                       <strong>No hospitals found</strong>
                                       <?php echo $has_filters ? 'Try changing or resetting your filters.' : 'Add your first hospital to get started.'; ?>
                                    </div>
                                 </td>
                              </tr>
                           <?php endif; ?>
                        </tbody>
                     </table>
                  </div>

                  <!-- Pagination -->
                  <?php if ($total_records > 0): ?>
                     <div class="pager-wrap">
                        <div class="pager-info">
                           Showing <?php echo $offset + 1; ?>–<?php echo min($offset + $records_per_page, $total_records); ?> of <?php echo $total_records; ?> hospitals
                        </div>

                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                           <ul class="pagination">
                              <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                 <a class="page-link" href="<?php echo htmlspecialchars(page_link($qs, $page - 1)); ?>" aria-label="Previous"><i class="fas fa-chevron-left"></i></a>
                              </li>

                              <?php
                              $win = [];
                              for ($p = 1; $p <= $total_pages; $p++) {
                                  if ($p == 1 || $p == $total_pages || abs($p - $page) <= 2) $win[] = $p;
                              }
                              $prev = 0;
                              foreach ($win as $p):
                                  if ($p - $prev > 1): ?>
                                     <li class="page-item disabled"><span class="page-link">…</span></li>
                                  <?php endif; ?>
                                  <li class="page-item <?php echo ($page == $p) ? 'active' : ''; ?>">
                                     <a class="page-link" href="<?php echo htmlspecialchars(page_link($qs, $p)); ?>"><?php echo $p; ?></a>
                                  </li>
                              <?php $prev = $p; endforeach; ?>

                              <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                 <a class="page-link" href="<?php echo htmlspecialchars(page_link($qs, $page + 1)); ?>" aria-label="Next"><i class="fas fa-chevron-right"></i></a>
                              </li>
                           </ul>
                        </nav>
                        <?php endif; ?>
                     </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- Container-fluid ends -->
</div>

<script>
function deleteHospital(user_id) {
    if (confirm('Are you sure you want to delete this hospital?')) {
        window.location.href = '?delete_id=' + user_id;
    }
}
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>