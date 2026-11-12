<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>
<?php include BASE_PATH.'/admin/inc/header.php';?>
<?php include BASE_PATH.'/admin/inc/top.php';?>
<?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
// ============================================================
// FILTERS
// ============================================================
$f_name    = mysqli_real_escape_string($con, trim($_GET['name'] ?? ''));
$f_reg     = mysqli_real_escape_string($con, trim($_GET['reg_no'] ?? ''));
$f_status  = $_GET['status'] ?? '';

$where = "1=1";
if ($f_name !== '')   $where .= " AND r.rad_name LIKE '%$f_name%'";
if ($f_reg !== '')    $where .= " AND r.reg_no LIKE '%$f_reg%'";
if ($f_status !== '') $where .= " AND r.approve = " . (int)$f_status;

$sql = "SELECT r.*, c.city_name
        FROM radiology r
        LEFT JOIN cities c ON c.city_id = r.city_id
        WHERE $where
        ORDER BY r.created_at DESC";
$res = mysqli_query($con, $sql);
?>

<style>
.status-badge { padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; }
.status-approved   { background: #d4edda; color: #155724; }
.status-pending    { background: #fff3cd; color: #856404; }
</style>

<div class="content-wrapper">
   <div class="container-fluid">

      <div class="row">
         <div class="main-header">
            <h4>Radiology List</h4>
         </div>
      </div>

      <!-- =============================================== -->
      <!-- FILTER                                          -->
      <!-- =============================================== -->
      <div class="card">
         <div class="card-block">
            <form method="GET" class="form-inline row">
               <div class="col-md-3 mb-2">
                  <input type="text" class="form-control w-100" name="name"
                         placeholder="Filter by Name" value="<?php echo htmlspecialchars($f_name); ?>">
               </div>
               <div class="col-md-3 mb-2">
                  <input type="text" class="form-control w-100" name="reg_no"
                         placeholder="Filter by Reg No" value="<?php echo htmlspecialchars($f_reg); ?>">
               </div>
               <div class="col-md-2 mb-2">
                  <select class="form-control w-100" name="status">
                     <option value="">All Status</option>
                     <option value="1" <?php echo ($f_status==='1'?'selected':''); ?>>Approved</option>
                     <option value="0" <?php echo ($f_status==='0'?'selected':''); ?>>Pending</option>
                  </select>
               </div>
               <div class="col-md-4 mb-2">
                  <button type="submit" class="btn btn-primary"><i class="ti-search"></i> Filter</button>
                  <a href="<?php echo BASE_URL; ?>admin/radiology/list" class="btn btn-secondary">Reset</a>
                  <a href="<?php echo BASE_URL; ?>admin/radiology/add" class="btn btn-success float-right">
                     <i class="ti-plus"></i> Add Radiology
                  </a>
               </div>
            </form>
         </div>
      </div>

      <!-- =============================================== -->
      <!-- LIST TABLE                                      -->
      <!-- =============================================== -->
      <div class="card">
         <div class="card-block">
            <div class="table-responsive">
               <table class="table table-striped table-bordered" id="radiology-table">
                  <thead>
                     <tr>
                        <th>#</th>
                        <th>Reg No</th>
                        <th>Picture</th>
                        <th>Name</th>
                        <th>City</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th style="width:100px;">Action</th>
                     </tr>
                  </thead>
                  <tbody>
                  <?php if (mysqli_num_rows($res) === 0): ?>
                     <tr><td colspan="10" class="text-center text-muted">No radiology records found.</td></tr>
                  <?php else: ?>
                     <?php $i = 1; while ($row = mysqli_fetch_assoc($res)): ?>
                        <tr>
                           <td><?php echo $i++; ?></td>
                           <td><?php echo htmlspecialchars($row['reg_no']); ?></td>
                           <td>
                              <?php if (!empty($row['rad_pic'])): ?>
                                 <img src="<?php echo BASE_URL; ?>admin/inc/uploads/radiology/<?php echo htmlspecialchars($row['rad_pic']); ?>"
                                      width="40" height="40" style="object-fit:cover;border-radius:6px;">
                              <?php else: ?>
                                 <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/radiology.jpg"
                                      width="40" height="40" style="object-fit:cover;border-radius:6px;">
                              <?php endif; ?>
                           </td>
                           <td><?php echo htmlspecialchars($row['rad_name']); ?></td>
                           <td><?php echo htmlspecialchars($row['city_name'] ?? '—'); ?></td>
                           <td><?php echo htmlspecialchars($row['rad_phone']); ?></td>
                           <td>
                              <?php if ($row['approve'] == 1): ?>
                                 <span class="status-badge status-approved">Approved</span>
                              <?php else: ?>
                                 <span class="status-badge status-pending">Pending</span>
                              <?php endif; ?>
                           </td>
                           <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                           <td>
                              <a href="<?php echo BASE_URL; ?>admin/radiology/add?id=<?php echo $row['rad_id']; ?>"
                                 class="btn btn-primary btn-sm" title="Edit">
                                 <i class="ti-pencil"></i>
                              </a>
                              <a href="<?php echo BASE_URL; ?>admin/radiology/detail?id=<?php echo $row['rad_id']; ?>"
                                 class="btn btn-info btn-sm" title="View">
                                 <i class="ti-eye"></i>
                              </a>
                           </td>
                        </tr>
                     <?php endwhile; ?>
                  <?php endif; ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>

   </div>
</div>

<?php include BASE_PATH.'/admin/inc/footer.php';?>