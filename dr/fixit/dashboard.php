<?php include '../config.php'; ?>
<?php include BASE_PATH.'/fixit/inc/header.php';?> 
<?php include BASE_PATH.'/fixit/inc/top.php';?>
<?php include BASE_PATH.'/fixit/inc/nav.php';?>



      <div class="content-wrapper">
         <!-- Main content starts -->
         <div class="container-fluid">
            <div class="row">
               <div class="main-header">
                  <h4>
                    <?php
                        if(isset($_SESSION['team_id']) && $_SESSION['team_id'] != 0){
                            echo $_SESSION['team_name'];
                        }else{
                            echo "Admin Dashboard";
                        }
                    ?>
                  </h4>
               </div>
            </div>
            <!-- 4-blocks row start -->
            <div class="row dashboard-header">
               <div class="col-lg-3 col-md-6">
                  <div class="card dashboard-product">
                     <span>Registered Cities</span>
                     <h2 class="dashboard-total-products">--</h2>
                     <span class="label label-success">Active</span>Total Cities
                     <div class="side-box">
                        <i class="icofont icofont-location-pin text-success-color"></i>
                     </div>
                  </div>
               </div>
               <div class="col-lg-3 col-md-6">
                  <div class="card dashboard-product">
                     <span>Registered Doctors</span>
                     <h2 class="dashboard-total-products">--</h2>
                     <span class="label label-primary">Active</span>Total Doctors
                     <div class="side-box ">
                        <i class="icofont icofont-doctor text-primary-color"></i>
                     </div>
                  </div>
               </div>
               <div class="col-lg-3 col-md-6">
                  <div class="card dashboard-product">
                     <span>Registered Hospitals</span>
                     <h2 class="dashboard-total-products">--</h2>
                     <span class="label label-info">Active</span>Total Hospitals
                     <div class="side-box">
                        <i class="icofont icofont-hospital text-info-color"></i>
                     </div>
                  </div>
               </div>
               <div class="col-lg-3 col-md-6">
                  <div class="card dashboard-product">
                     <span>Registered Labs</span>
                     <h2 class="dashboard-total-products">--</h2>
                     <span class="label label-warning">Active</span>Total Labs
                     <div class="side-box">
                        <i class="icofont icofont-laboratory text-warning-color"></i>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <!-- Main content ends -->
      </div>
   </div>






<?php include BASE_PATH.'/admin/inc/footer.php';?>