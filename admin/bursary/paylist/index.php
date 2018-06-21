<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');




if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}
$deptname = "";



$page_title = "Tasued";
?>
<!doctype html>
<html>
<?php include INCPATH . "/header.php" ?>

<body data-layout-sidebar="fixed" data-layout-topbar="fixed">
    <?php include INCPATH . "/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
        <?php include INCPATH . "/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH . "/page_header.php" ?>
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
                                <li class="teal">
                                    <a  target="_blank" href="application_fee.php"><span><i class="icon-money"></i></span><span class="name">Application Fee</span></a>
                                </li>
                                <li class="purple">
                                    <a target="_blank" href="acceptance_fee.php"><span><i class="icon-money"></i></span><span class="name">Acceptance Fee</span></a>
                                </li>
                                <li class="orange">
                                    <a target="_blank" href="school_fee.php"><span><i class="icon-money"></i></span><span class="name">School Fee</span></a>
                                </li>
                                <li class="darkblue">
                                    <a target="_blank" href="clearance_fee.php"><span><i class="icon-money"></i></span><span class="name">Clearance Fee</span></a>
                                </li>
                                <li class="brown">
                                    <a  target="_blank" href="reparation_fee.php"><span><i class="icon-money"></i></span><span class="name">Reparation Fee</span></a>
                                </li>
                                <li class="lightred">
                                    <a  target="_blank" href="convocation_fee.php"><span><i class="icon-money"></i></span><span class="name">Convocation Fee</span></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH . "/footer.php" ?>
    </body>
</html>
