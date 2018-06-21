<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');

$auth_users = "1,20,26,24";
check_auth($auth_users, $site_root.'/login');

unset($_SESSION['olv_veri_col']);


if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}
$deptname = "";



$page_title = "Tasued";
?>
<!doctype html>
<html>
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
        <?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH."/side_nav_bar.php" ?>

            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH."/page_header.php" ?>
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3>
                                        <i class="icon-reorder"></i> Student O'Level Verification Code Management Page
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <ul class="tiles">
                                                <?php if(in_array(getAccess(), ['20'])) {?>
                                               <li class="blue">
                                                    <a href="filter.php?who=stud"><span><i class="icon-user"></i></span><span class="name">Returning</span></a>
                                                </li>
                                                <?php } ?>
                                                <li class="teal">
                                                    <a href="filter.php?who=pros"><span><i class="icon-user"></i></span><span class="name">Prospective</span></a>
                                                </li>
                                                <li class="purple">
                                                    <a href="reports.php"><span><i class="icon-money"></i></span><span class="name">Reports</span></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p>&nbsp;</p>   
                        </div>
                    </div>
                    
                    
                </div>
            </div>          
        </div>
        <?php include INCPATH."/footer.php" ?>
    </body>
</html>

