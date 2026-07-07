<?php 
// Get current URL path
$current_url = $_SERVER['REQUEST_URI'];
$url_parts = explode('/', trim($current_url, '/'));

// Set active and open variables
$active = '';
$open = '';

// CITIES
if (in_array('cities', $url_parts)) {
    $open = 'cities';
    if (in_array('add', $url_parts)) {
        $active = 'add';
    } elseif (in_array('list', $url_parts)) {
        $active = 'list';
    }
}

// HOSPITALS
if (in_array('hospitals', $url_parts)) {
    $open = 'hospitals';
    if (in_array('add', $url_parts)) {
        $active = 'add';
    } elseif (in_array('list', $url_parts)) {
        $active = 'list';
    }
}


// DOCTORS
if (in_array('doctors', $url_parts)) {
    $open = 'doctors';
    if (in_array('add', $url_parts)) {
        $active = 'add';
    } elseif (in_array('list', $url_parts)) {
        $active = 'list';
    }
    elseif (in_array('assign-hospitals', $url_parts)) {
        $active = 'assign-hospitals';
    }
}


// LABORATORIES
if (in_array('laboratories', $url_parts)) {
    $open = 'laboratories';
    if (in_array('add', $url_parts)) {
        $active = 'add';
    } elseif (in_array('list', $url_parts)) {
        $active = 'list';
    }
}

// BLOOD BANKS
if (in_array('blood-banks', $url_parts)) {
    $open = 'blood-banks';
    if (in_array('add', $url_parts)) {
        $active = 'add';
    } elseif (in_array('list', $url_parts)) {
        $active = 'list';
    }
}


 




?>
<aside class="main-sidebar hidden-print ">
         <section class="sidebar" id="sidebar-scroll">
            <!-- Sidebar Menu-->
            <ul class="sidebar-menu">
                <li class="nav-level">--- Navigation</li>
                <li class="treeview <?php echo ($open == '') ? 'active' : ''; ?>">
                    <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin">
                        <i class="icon-speedometer"></i><span> Dashboard</span>
                    </a>                
                </li>

                <li class="treeview <?php echo ($open == 'cities') ? 'active' : ''; ?>"><a class="waves-effect waves-dark" href="#!"><i class="icon-book-open">
                    </i><span> Cities</span><i class="icon-arrow-down"></i></a>
                    <ul class="treeview-menu">
                        <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/cities/add">
                                <i class="icon-arrow-right"></i>New</a>
                            </li>
                        
                        <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/cities/list">
                                <i class="icon-arrow-right"></i> List</a>
                        </li>
                    </ul>
                </li>

                <li class="treeview <?php echo ($open == 'hospitals') ? 'active' : ''; ?>"><a class="waves-effect waves-dark" href="#!"><i class="icon-book-open">
                    </i><span> Hospitals </span><i class="icon-arrow-down"></i></a>
                    <ul class="treeview-menu">
                        <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/hospitals/add">
                                <i class="icon-arrow-right"></i>New</a>
                            </li>
                        
                        <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/hospitals/list">
                                <i class="icon-arrow-right"></i> List</a>
                        </li>
                    </ul>
                </li>
                
                <li class="treeview <?php echo ($open == 'doctors') ? 'active' : ''; ?>"><a class="waves-effect waves-dark" href="#!"><i class="icon-book-open">
                    </i><span> Doctors </span><i class="icon-arrow-down"></i></a>
                    <ul class="treeview-menu">
                        <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/doctors/add">
                                <i class="icon-arrow-right"></i>New</a>
                            </li>
                        
                        <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/doctors/list">
                                <i class="icon-arrow-right"></i> List</a>
                        </li>
                    </ul>
                </li>

                <li class="treeview <?php echo ($open == 'laboratories') ? 'active' : ''; ?>"><a class="waves-effect waves-dark" href="#!"><i class="icon-book-open">
                    </i><span> Laboratories </span><i class="icon-arrow-down"></i></a>
                    <ul class="treeview-menu">
                        <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/laboratories/add">
                                <i class="icon-arrow-right"></i>New</a>
                            </li>
                        
                        <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/laboratories/list">
                                <i class="icon-arrow-right"></i> List</a>
                        </li>
                    </ul>
                </li>
               
                <li class="treeview <?php echo ($open == 'blood-banks') ? 'active' : ''; ?>"><a class="waves-effect waves-dark" href="#!"><i class="icon-book-open">
                    </i><span> Blood Banks </span><i class="icon-arrow-down"></i></a>
                    <ul class="treeview-menu">
                        <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/blood-banks/add">
                                <i class="icon-arrow-right"></i>New</a>
                            </li>
                        
                        <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/blood-banks/list">
                                <i class="icon-arrow-right"></i> List</a>
                        </li>
                    </ul>
                </li>
               


                <li class="nav-level">--- More</li>
                <li class="treeview <?php echo ($open == '') ? 'active' : ''; ?>">
                    <a class="waves-effect waves-dark text-danger" href="<?php echo BASE_URL; ?>logout">
                        <i class="icon-speedometer"></i><span> Logout</span>
                    </a>                
                </li>

            </ul>
         </section>
      </aside>