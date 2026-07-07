<?php 
// Get current URL path
$current_url = $_SERVER['REQUEST_URI'];
$url_parts = explode('/', trim($current_url, '/'));

// Set active and open variables
$active = '';
$open = '';

// teams
if (in_array('teams', $url_parts)) {
    $open = 'teams';
    if (in_array('add', $url_parts)) {
        $active = 'add';
    } elseif (in_array('list', $url_parts)) {
        $active = 'list';
    }
}

// donors
if (in_array('donors', $url_parts)) {
    $open = 'donors';
    if (in_array('add', $url_parts)) {
        $active = 'add';
    } elseif (in_array('list', $url_parts)) {
        $active = 'list';
    }
}


// expenses
if (in_array('expenses', $url_parts)) {
    $open = 'expenses';
    if (in_array('add', $url_parts)) {
        $active = 'add';
    } elseif (in_array('list', $url_parts)) {
        $active = 'list';
    }
    elseif (in_array('assign-donors', $url_parts)) {
        $active = 'assign-donors';
    }
}


// activities
if (in_array('activities', $url_parts)) {
    $open = 'activities';
    if (in_array('add', $url_parts)) {
        $active = 'add';
    } elseif (in_array('list', $url_parts)) {
        $active = 'list';
    }
}

// BLOOD BANKS
if (in_array('donations', $url_parts)) {
    $open = 'donations';
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
                    <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>fixit/dashboard">
                        <i class="icon-speedometer"></i><span> Dashboard</span>
                    </a>                
                </li>
                <?php
                    if($type == 'admin') { ?>
                        <!-- TEAMS MENU -->
                        <li class="treeview <?php echo ($open == 'teams') ? 'active' : ''; ?>"><a class="waves-effect waves-dark" href="#!"><i class="icon-book-open">
                            </i><span> Teams</span><i class="icon-arrow-down"></i></a>
                            <ul class="treeview-menu">
                                <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                                    <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>fixit/teams/add">
                                        <i class="icon-arrow-right"></i>New</a>
                                    </li>
                                
                                <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                                    <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>fixit/teams/list">
                                        <i class="icon-arrow-right"></i> List</a>
                                </li>
                            </ul>
                        </li>
                    <?php }
                ?>
                    

                <!-- DONORS MENU -->
                <li class="treeview <?php echo ($open == 'donors') ? 'active' : ''; ?>"><a class="waves-effect waves-dark" href="#!"><i class="icon-book-open">
                    </i><span> Donors </span><i class="icon-arrow-down"></i></a>
                    <ul class="treeview-menu">
                        <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>fixit/donors/add">
                                <i class="icon-arrow-right"></i>New</a>
                            </li>
                        
                        <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>fixit/donors/list">
                                <i class="icon-arrow-right"></i> List</a>
                        </li>
                    </ul>
                </li>
                
                <!-- EXPENSES MENU -->
                <li class="treeview <?php echo ($open == 'expenses') ? 'active' : ''; ?>"><a class="waves-effect waves-dark" href="#!"><i class="icon-book-open">
                    </i><span> Expenses </span><i class="icon-arrow-down"></i></a>
                    <ul class="treeview-menu">
                        <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>fixit/expenses/add">
                                <i class="icon-arrow-right"></i>New</a>
                            </li>
                        
                        <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>fixit/expenses/list">
                                <i class="icon-arrow-right"></i> List</a>
                        </li>
                    </ul>
                </li>

                <!-- ACTIVITIES MENU -->
                <li class="treeview <?php echo ($open == 'activities') ? 'active' : ''; ?>"><a class="waves-effect waves-dark" href="#!"><i class="icon-book-open">
                    </i><span> Activities </span><i class="icon-arrow-down"></i></a>
                    <ul class="treeview-menu">
                        <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>fixit/activities/add">
                                <i class="icon-arrow-right"></i>New</a>
                            </li>
                        
                        <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>fixit/activities/list">
                                <i class="icon-arrow-right"></i> List</a>
                        </li>
                    </ul>
                </li>
               
                <!-- DONATIONS MENU -->
                <li class="treeview <?php echo ($open == 'donations') ? 'active' : ''; ?>"><a class="waves-effect waves-dark" href="#!"><i class="icon-book-open">
                    </i><span> Donations </span><i class="icon-arrow-down"></i></a>
                    <ul class="treeview-menu">
                        <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>fixit/donations/add">
                                <i class="icon-arrow-right"></i>New</a>
                            </li>
                        
                        <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                            <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>fixit/donations/list">
                                <i class="icon-arrow-right"></i> List</a>
                        </li>
                    </ul>
                </li>
               


                <li class="nav-level">--- More</li>
                <li class="treeview <?php echo ($open == '') ? 'active' : ''; ?>">
                    <a class="waves-effect waves-dark text-danger" href="<?php echo BASE_URL; ?>fixit/logout">
                        <i class="icon-speedometer"></i><span> Logout</span>
                    </a>                
                </li>

            </ul>
         </section>
      </aside>