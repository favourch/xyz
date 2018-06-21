<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');




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
                                <li class="darkblue">
                                    <a target="_blank" href="paylist/index.php"><span><i class="icon-reorder"></i></span><span class="name">Payment List</span></a>
                                </li>
                                <li class="brown">
                                    <a  target="_blank" href="acct_mgt/index.php"><span><i class="icon-money"></i></span><span class="name">Account MGT</span></a>
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

