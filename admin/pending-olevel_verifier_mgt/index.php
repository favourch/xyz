<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');



/* -----------------------------------------------*
 * 
 * Logic of the College/index.php Page 
 *
 * *------------------------------------------------
 */

$auth_users = "1,20,21,22,23,24";
check_auth($auth_users, $site_root.'/admin');

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
                    <!--                    <div class="breadcrumbs">
                                            <ul>
                                                <li>
                                                    <a href="index.php">Home</a> <i class="icon-angle-right"></i>
                                                </li>
                                                <li>
                                                    <a href="college.php">College</a>
                                                </li>
                                            </ul>
                                            <div class="close-bread">
                                                <a href="#"><i class="icon-remove"></i></a>
                                            </div>
                                        </div>
                                        <br/>-->


                    <div class="row-fluid">
                        <div class="span12">
                            <ul class="tiles">

                                <li class="orange">
                                    <a href="returning/index.php"><span><i class="icon-user"></i></span><span class="name">Returning</span></a>
                                </li>
                                <li class="teal">
                                    <a href="prospective/index.php"><span><i class=" icon-user"></i></span><span class="name">Prospective</span></a>
                                </li>
                                <li class="purple">
                                    <a href="reports.php"><span><i class="icon-money"></i></span><span class="name">Reports</span></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH."/footer.php" ?>
    </body>
</html>

