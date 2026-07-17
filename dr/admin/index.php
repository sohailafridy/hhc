<?php include '../config.php'; ?>

<?php
// Fetch counts for dashboard
$cities_count_query = "SELECT COUNT(*) as total FROM cities WHERE status = 1";
$cities_count_result = mysqli_query($con, $cities_count_query);
$cities_count = mysqli_fetch_assoc($cities_count_result)['total'];

$doctors_count_query = "SELECT COUNT(*) as total FROM doctors LEFT JOIN entities e ON e.entity_id = doctors.entity_id WHERE e.status = 1 AND doctors.approve =1";
$doctors_count_result = mysqli_query($con, $doctors_count_query);
$doctors_count = mysqli_fetch_assoc($doctors_count_result)['total'];

$hospitals_count_query = "SELECT COUNT(*) as total FROM hospitals LEFT JOIN entities e ON e.entity_id = hospitals.entity_id WHERE e.status = 1 AND hospitals.approve =1";
$hospitals_count_result = mysqli_query($con, $hospitals_count_query);
$hospitals_count = mysqli_fetch_assoc($hospitals_count_result)['total'];

$labs_count_query = "SELECT COUNT(*) as total FROM laboratories LEFT JOIN entities e ON e.entity_id = laboratories.entity_id WHERE e.status = 1 AND laboratories.approve =1";
$labs_count_result = mysqli_query($con, $labs_count_query);
$labs_count = mysqli_fetch_assoc($labs_count_result)['total'];

$blood_banks_count_query = "SELECT COUNT(*) as total FROM blood_bank LEFT JOIN entities e ON e.entity_id = blood_bank.entity_id WHERE e.status = 1 AND blood_bank.approve =1";
$blood_banks_count_result = mysqli_query($con, $blood_banks_count_query);
$blood_banks_count = mysqli_fetch_assoc($blood_banks_count_result)['total'];

// Fetch recently added doctors
$recent_doctors_query = "SELECT d.*, c.city_name, h.hospital_name 
                         FROM doctors d 
                         LEFT JOIN cities c ON d.city_id = c.city_id 
                         LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id 
                         LEFT JOIN entities e ON e.entity_id = d.entity_id
                         WHERE e.status = 1 AND d.approve=1
                         ORDER BY d.created_at DESC 
                         LIMIT 5";
$recent_doctors_result = mysqli_query($con, $recent_doctors_query);
?>

<?php include BASE_PATH.'/admin/inc/header.php';?>
   
      <!-- Navbar top-->
      <?php include BASE_PATH.'/admin/inc/top.php';?>
      <!-- Side-Nav-->
      <?php include BASE_PATH.'/admin/inc/nav.php';?>
      <!-- Sidebar chat start -->
      <div id="sidebar" class="p-fixed header-users showChat">
         <div class="had-container">
            <div class="card card_main header-users-main">
               <div class="card-content user-box">
                  <div class="md-group-add-on p-20">
                     <span class="md-add-on">
                                    <i class="icofont icofont-search-alt-2 chat-search"></i>
                                 </span>
                     <div class="md-input-wrapper">
                        <input type="text" class="md-form-control" name="username" id="search-friends">
                        <label>Search</label>
                     </div>

                  </div>
                  <div class="media friendlist-main">

                     <h6>Friend List</h6>

                  </div>
                  <div class="main-friend-list">
                     <div class="media friendlist-box" data-id="1" data-status="online" data-username="Josephin Doe" data-toggle="tooltip" data-placement="left" title="Josephin Doe">

                        <a class="media-left" href="#!">
                           <img class="media-object img-circle" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-1.png" alt="Generic placeholder image">
                           <div class="live-status bg-success"></div>
                        </a>
                        <div class="media-body">
                           <div class="friend-header">Josephin Doe</div>
                           <span>20min ago</span>
                        </div>
                     </div>
                     <div class="media friendlist-box" data-id="3" data-status="online" data-username="Alice" data-toggle="tooltip" data-placement="left" title="Alice">
                        <a class="media-left" href="#!">
                           <img class="media-object img-circle" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-2.png" alt="Generic placeholder image">
                           <div class="live-status bg-success"></div>
                        </a>
                        <div class="media-body">
                           <div class="friend-header">Alice</div>
                           <span>1 hour ago</span>
                        </div>
                     </div>
                     <div class="media friendlist-box" data-id="7" data-status="offline" data-username="Michael Scofield" data-toggle="tooltip" data-placement="left" title="Michael Scofield">
                        <a class="media-left" href="#!">
                           <img class="media-object img-circle" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-3.png" alt="Generic placeholder image">
                           <div class="live-status bg-danger"></div>
                        </a>
                        <div class="media-body">
                           <div class="friend-header">Michael Scofield</div>
                           <span>3 hours ago</span>
                        </div>
                     </div>
                     <div class="media friendlist-box" data-id="5" data-status="online" data-username="Irina Shayk" data-toggle="tooltip" data-placement="left" title="Irina Shayk">
                        <a class="media-left" href="#!">
                           <img class="media-object img-circle" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-4.png" alt="Generic placeholder image">
                           <div class="live-status bg-success"></div>
                        </a>
                        <div class="media-body">
                           <div class="friend-header">Irina Shayk</div>
                           <span>1 day ago</span>
                        </div>
                     </div>
                     <div class="media friendlist-box" data-id="6" data-status="offline" data-username="Sara Tancredi" data-toggle="tooltip" data-placement="left" title="Sara Tancredi">
                        <a class="media-left" href="#!">
                           <img class="media-object img-circle" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-5.png" alt="Generic placeholder image">
                           <div class="live-status bg-danger"></div>
                        </a>
                        <div class="media-body">
                           <div class="friend-header">Sara Tancredi</div>
                           <span>2 days ago</span>
                        </div>
                     </div>
                     <div class="media friendlist-box" data-id="1" data-status="online" data-username="Josephin Doe" data-toggle="tooltip" data-placement="left" title="Josephin Doe">
                        <a class="media-left" href="#!">
                           <img class="media-object img-circle" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-1.png" alt="Generic placeholder image">
                           <div class="live-status bg-success"></div>
                        </a>
                        <div class="media-body">
                           <div class="friend-header">Josephin Doe</div>
                           <span>20min ago</span>
                        </div>
                     </div>
                     <div class="media friendlist-box" data-id="3" data-status="online" data-username="Alice" data-toggle="tooltip" data-placement="left" title="Alice">
                        <a class="media-left" href="#!">
                           <img class="media-object img-circle" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-2.png" alt="Generic placeholder image">
                           <div class="live-status bg-success"></div>
                        </a>
                        <div class="media-body">
                           <div class="friend-header">Alice</div>
                           <span>1 hour ago</span>
                        </div>
                     </div>
                     <div class="media friendlist-box" data-id="1" data-status="online" data-username="Josephin Doe" data-toggle="tooltip" data-placement="left" title="Josephin Doe">

                        <a class="media-left" href="#!">
                           <img class="media-object img-circle" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-1.png" alt="Generic placeholder image">
                           <div class="live-status bg-success"></div>
                        </a>
                        <div class="media-body">
                           <div class="friend-header">Josephin Doe</div>
                           <span>20min ago</span>
                        </div>
                     </div>
                     <div class="media friendlist-box" data-id="3" data-status="online" data-username="Alice" data-toggle="tooltip" data-placement="left" title="Alice">
                        <a class="media-left" href="#!">
                           <img class="media-object img-circle" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-2.png" alt="Generic placeholder image">
                           <div class="live-status bg-success"></div>
                        </a>
                        <div class="media-body">
                           <div class="friend-header">Alice</div>
                           <span>1 hour ago</span>
                        </div>
                     </div>
                     <div class="media friendlist-box" data-id="1" data-status="online" data-username="Josephin Doe" data-toggle="tooltip" data-placement="left" title="Josephin Doe">

                        <a class="media-left" href="#!">
                           <img class="media-object img-circle" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-1.png" alt="Generic placeholder image">
                           <div class="live-status bg-success"></div>
                        </a>
                        <div class="media-body">
                           <div class="friend-header">Josephin Doe</div>
                           <span>20min ago</span>
                        </div>
                     </div>
                     <div class="media friendlist-box" data-id="3" data-status="online" data-username="Alice" data-toggle="tooltip" data-placement="left" title="Alice">
                        <a class="media-left" href="#!">
                           <img class="media-object img-circle" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-2.png" alt="Generic placeholder image">
                           <div class="live-status bg-success"></div>
                        </a>
                        <div class="media-body">
                           <div class="friend-header">Alice</div>
                           <span>1 hour ago</span>
                        </div>
                     </div>
                     <div class="media friendlist-box" data-id="1" data-status="online" data-username="Josephin Doe" data-toggle="tooltip" data-placement="left" title="Josephin Doe">

                        <a class="media-left" href="#!">
                           <img class="media-object img-circle" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-1.png" alt="Generic placeholder image">
                           <div class="live-status bg-success"></div>
                        </a>
                        <div class="media-body">
                           <div class="friend-header">Josephin Doe</div>
                           <span>20min ago</span>
                        </div>
                     </div>
                     <div class="media friendlist-box" data-id="1" data-status="online" data-username="Josephin Doe" data-toggle="tooltip" data-placement="left" title="Josephin Doe">

                        <a class="media-left" href="#!">
                           <img class="media-object img-circle" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-1.png" alt="Generic placeholder image">
                           <div class="live-status bg-success"></div>
                        </a>
                        <div class="media-body">
                           <div class="friend-header">Josephin Doe</div>
                           <span>20min ago</span>
                        </div>
                     </div>
                     <div class="media friendlist-box" data-id="1" data-status="online" data-username="Josephin Doe" data-toggle="tooltip" data-placement="left" title="Josephin Doe">

                        <a class="media-left" href="#!">
                           <img class="media-object img-circle" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-1.png" alt="Generic placeholder image">
                           <div class="live-status bg-success"></div>
                        </a>
                        <div class="media-body">
                           <div class="friend-header">Josephin Doe</div>
                           <span>20min ago</span>
                        </div>
                     </div>
                     <div class="media friendlist-box" data-id="1" data-status="online" data-username="Josephin Doe" data-toggle="tooltip" data-placement="left" title="Josephin Doe">

                        <a class="media-left" href="#!">
                           <img class="media-object img-circle" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-1.png" alt="Generic placeholder image">
                           <div class="live-status bg-success"></div>
                        </a>
                        <div class="media-body">
                           <div class="friend-header">Josephin Doe</div>
                           <span>20min ago</span>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>

      </div>
      <div class="showChat_inner">
         <div class="media chat-inner-header">
            <a class="back_chatBox">
               <i class="icofont icofont-rounded-left"></i> Josephin Doe
            </a>
         </div>
         <div class="media chat-messages">
            <a class="media-left photo-table" href="#!">
               <img class="media-object img-circle m-t-5" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-1.png" alt="Generic placeholder image">
               <div class="live-status bg-success"></div>
            </a>
            <div class="media-body chat-menu-content">
               <div class="">
                  <p class="chat-cont">I'm just looking around. Will you tell me something about yourself?</p>
                  <p class="chat-time">8:20 a.m.</p>
               </div>
            </div>
         </div>
         <div class="media chat-messages">
            <div class="media-body chat-menu-reply">
               <div class="">
                  <p class="chat-cont">I'm just looking around. Will you tell me something about yourself?</p>
                  <p class="chat-time">8:20 a.m.</p>
               </div>
            </div>
            <div class="media-right photo-table">
               <a href="#!">
                  <img class="media-object img-circle m-t-5" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-2.png" alt="Generic placeholder image">
                  <div class="live-status bg-success"></div>
               </a>
            </div>
         </div>
         <div class="media chat-reply-box">
            <div class="md-input-wrapper">
               <input type="text" class="md-form-control" id="inputEmail" name="inputEmail">
               <label>Share your thoughts</label>
               <span class="highlight"></span>
               <span class="bar"></span> <button type="button" class="chat-send waves-effect waves-light">
                     <i class="icofont icofont-location-arrow f-20 "></i>
                 </button>

               <button type="button" class="chat-send waves-effect waves-light">
                    <i class="icofont icofont-location-arrow f-20 "></i>
                </button>
            </div>

         </div>
      </div>
      <!-- Sidebar chat end-->
      <div class="content-wrapper">
         <!-- Container-fluid starts -->
         <!-- Main content starts -->
         <div class="container-fluid">
            <div class="row">
               <div class="main-header">
                  <h4>Dashboard</h4>
               </div>
            </div>
            <!-- 4-blocks row start -->
            <div class="row dashboard-header">
               <div class="col-lg-3 col-md-6">
                  <div class="card dashboard-product">
                     <span>Registered Cities</span>
                     <h2 class="dashboard-total-products"><?php echo $cities_count; ?></h2>
                     <span class="label label-success">Active</span>Total Cities
                     <div class="side-box">
                        <i class="icofont icofont-location-pin text-success-color"></i>
                     </div>
                  </div>
               </div>
               <div class="col-lg-3 col-md-6">
                  <div class="card dashboard-product">
                     <span>Registered Doctors</span>
                     <h2 class="dashboard-total-products"><?php echo $doctors_count; ?></h2>
                     <span class="label label-primary">Active</span>Total Doctors
                     <div class="side-box ">
                        <i class="icofont icofont-doctor text-primary-color"></i>
                     </div>
                  </div>
               </div>
               <div class="col-lg-3 col-md-6">
                  <div class="card dashboard-product">
                     <span>Registered Hospitals</span>
                     <h2 class="dashboard-total-products"><?php echo $hospitals_count; ?></h2>
                     <span class="label label-info">Active</span>Total Hospitals
                     <div class="side-box">
                        <i class="icofont icofont-hospital text-info-color"></i>
                     </div>
                  </div>
               </div>
               <div class="col-lg-3 col-md-6">
                  <div class="card dashboard-product">
                     <span>Registered Labs</span>
                     <h2 class="dashboard-total-products"><?php echo $labs_count; ?></h2>
                     <span class="label label-warning">Active</span>Total Labs
                     <div class="side-box">
                        <i class="icofont icofont-laboratory text-warning-color"></i>
                     </div>
                  </div>
               </div>
            </div>
            <!-- 4-blocks row end -->

            <!-- 1-3-block row start -->
            <div class="row">
               <div class="col-lg-4">
                  <div class="card">
                     <div class="user-block-2">
                        <img class="img-fluid" src="<?=BASE_URL?>includes/uploads/about-me.jpg" alt="owner">
                        <h5>Sohail Afridy</h5>
                        <h6>Software Engineer</h6>
                     </div>
                     <div class="card-block">
                        <div class="user-block-2-activities">
                           <div class="user-block-2-active">
                              <i class="icofont icofont-clock-time"></i> Recent Activities
                              <label class="label label-primary">480</label>
                           </div>
                        </div>
                        <div class="user-block-2-activities">
                           <div class="user-block-2-active">
                              <i class="icofont icofont-users"></i> Current Employees
                              <label class="label label-primary">390</label>
                           </div>
                        </div>

                        <div class="user-block-2-activities">
                           <div class="user-block-2-active">
                              <i class="icofont icofont-ui-user"></i> Following
                              <label class="label label-primary">485</label>
                           </div>

                        </div>
                        <div class="user-block-2-activities">
                           <div class="user-block-2-active">
                              <i class="icofont icofont-picture"></i> Pictures
                              <label class="label label-primary">506</label>
                           </div>
                        </div>
                        <div class="text-center">
                           <button type="button" class="btn btn-warning waves-effect waves-light text-uppercase m-r-30">
                                    Follows
                                </button>
                           <button type="button" class="btn btn-primary waves-effect waves-light text-uppercase">
                                    Message
                                </button>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-lg-8">
                  <div class="card">
                     <div class="card-header">
                        <h5 class="card-header-text">Entities Overview</h5>
                     </div>
                     <div class="card-block">
                        <div id="barchart" style="min-width: 250px; height: 330px; margin: 0 auto"></div>
                     </div>
                  </div>
               </div>
               <div class="col-xl-4 col-lg-12 grid-item">
                  <div class="card">
                     <div class="card-block horizontal-card-img d-flex">
                        <img class="media-object img-circle" src="assets/images/avatar-3.png" alt="Generic placeholder image">
                        <div class="d-inlineblock  p-l-20">
                          <h6>Josephin Doe</h6>
                          <a href="#">contact@admin.com</a>
                       </div>
                        <h6 class="txt-warning rotate-txt">Designer</h6>
                     </div>
                  </div>
               </div>
               <div class="col-xl-4 col-lg-12 grid-item">
                  <div class="card">
                     <div class="card-block horizontal-card-img d-flex">
                        <img class="media-object img-circle" src="assets/images/lockscreen.png" alt="Generic placeholder image">
                        <div class="d-inlineblock  p-l-20">
                          <h6>Josephin Doe</h6>
                          <a href="#">contact@admin.com</a>
                       </div>
                        <h6 class="txt-danger rotate-txt">Developer</h6>
                     </div>
                  </div>
               </div>
            </div>
            <!-- 1-3-block row end -->

            <!-- 2-1 block start -->
            <div class="row">
               <div class="col-xl-8 col-lg-12">
                  <div class="card">
                     <div class="card-block">
                        <div class="table-responsive">
                           <table class="table m-b-0 photo-table">
                              <thead>
                                 <tr class="text-uppercase">
                                    <th>Photo</th>
                                    <th>Doctor Name</th>
                                    <th>Hospital/Clinic</th>
                                    <th>Status</th>
                                    <th>Added Date</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 <?php if (mysqli_num_rows($recent_doctors_result) > 0): ?>
                                    <?php while ($doctor = mysqli_fetch_assoc($recent_doctors_result)): ?>
                                       <tr>
                                          <th>
                                             <img class="img-fluid img-circle" src="<?php echo !empty($doctor['doctor_pic']) ? BASE_URL . 'admin/inc/uploads/doctors/' . $doctor['doctor_pic'] : BASE_URL . 'admin/inc/uploads/default/doctor.jpg'; ?>" alt="Doctor">
                                          </th>
                                          <td>
                                             <strong><?php echo htmlspecialchars($doctor['doctor_name']); ?></strong>
                                             <p><i class="icofont icofont-user-md"></i> <?php echo htmlspecialchars($doctor['specialization'] ?? 'N/A'); ?></p>
                                          </td>
                                          <td>
                                             <?php if ($doctor['doctor_type'] == 1 && !empty($doctor['hospital_name'])): ?>
                                                <span class="badge bg-info">
                                                   <i class="icofont icofont-hospital me-1"></i>
                                                   <?php echo htmlspecialchars($doctor['hospital_name']); ?>
                                                </span>
                                             <?php elseif ($doctor['doctor_type'] == 2): ?>
                                                <span class="badge bg-success">
                                                   <i class="icofont icofont-clinic-medical me-1"></i>
                                                   <?php echo htmlspecialchars($doctor['clinic_name'] ?? 'Private Clinic'); ?>
                                                </span>
                                             <?php else: ?>
                                                <span class="badge bg-secondary">N/A</span>
                                             <?php endif; ?>
                                             <p><i class="icofont icofont-location-pin"></i> <?php echo htmlspecialchars($doctor['city_name'] ?? 'N/A'); ?></p>
                                          </td>
                                          <td>
                                             <?php if ($doctor['status'] == 1): ?>
                                                <span class="badge bg-success">Active</span>
                                             <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                             <?php endif; ?>
                                          </td>
                                          <td>
                                             <?php echo date('M d, Y', strtotime($doctor['created_at'])); ?>
                                          </td>
                                       </tr>
                                    <?php endwhile; ?>
                                 <?php else: ?>
                                    <tr>
                                       <td colspan="5" class="text-center">
                                          <p class="text-muted">No doctors found</p>
                                       </td>
                                    </tr>
                                 <?php endif; ?>
                              </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-xl-4 col-lg-12">
                  <div class="card">
                     <div class="card-header">
                        <h5 class="card-header-text">Distribution Chart</h5>
                     </div>
                     <div class="card-block">
                        <div id="piechart" style="min-width: 250px; height: 460px; margin: 0 auto"></div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- 2-1 block end -->
         </div>
         <!-- Main content ends -->
         <!-- Container-fluid ends -->
         <div class="fixed-button">
            <a href="#!" class="btn btn-md btn-primary">
              <i class="fa fa-shopping-cart" aria-hidden="true"></i> Upgrade To Pro
            </a>
         </div>
      </div>
   </div>


<script>
// Bar Chart Data
Highcharts.chart('barchart', {
    chart: {
        type: 'column'
    },
    title: {
        text: 'Registered Entities Overview'
    },
    xAxis: {
        categories: ['Cities', 'Doctors', 'Hospitals', 'Labs']
    },
    yAxis: {
        min: 0,
        title: {
            text: 'Total Count'
        }
    },
    legend: {
        enabled: false
    },
    tooltip: {
        pointFormat: 'Total: <b>{point.y}</b>'
    },
    series: [{
        name: 'Entities',
        data: [<?php echo $cities_count; ?>, <?php echo $doctors_count; ?>, <?php echo $hospitals_count; ?>, <?php echo $labs_count; ?>],
        colors: ['#28a745', '#007bff', '#17a2b8', '#ffc107']
    }]
});

// Pie Chart Data
Highcharts.chart('piechart', {
    chart: {
        type: 'pie'
    },
    title: {
        text: 'Entities Distribution'
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    accessibility: {
        point: {
            valueSuffix: '%'
        }
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                format: '<b>{point.name}</b>: {point.percentage:.1f} %'
            },
            showInLegend: true
        }
    },
    series: [{
        name: 'Distribution',
        colorByPoint: true,
        data: [
            {
                name: 'Cities',
                y: <?php echo $cities_count; ?>,
                color: '#28a745'
            },
            {
                name: 'Doctors',
                y: <?php echo $doctors_count; ?>,
                color: '#007bff'
            },
            {
                name: 'Hospitals',
                y: <?php echo $hospitals_count; ?>,
                color: '#17a2b8'
            },
            {
                name: 'Labs',
                y: <?php echo $labs_count; ?>,
                color: '#ffc107'
            }
        ]
    }]
});
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>