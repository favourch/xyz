<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('path.php');






?>
<!doctype html>
<html>
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
        <?php include INCPATH."/top_nav_bar_index.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH."/side_nav_bar_login.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH."/page-header.php" ?>
                    <div class="breadcrumbs">
                        <ul>
                            <li>
                                <a href="index.php">Home</a> <i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="department.php">Department</a> <i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="departmentDetails.php">Agricultural Science</a>
                            </li>
                        </ul>
                        <div class="close-bread">
                            <a href="#"><i class="icon-remove"></i></a>
                        </div>
                    </div>

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Agricultural Science Department
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <p>The philosophy of the B.Sc. Ed programme is tied to the national philosophy on agriculture for self reliance based on the provision of skilled manpower adequately endowed with in-depth knowledge required for engaging in both teaching profession and economic agricultural production in an environment characterized by adequate land endowment. Such professional manpower has to be produced in an environment with the widest possible human and material resources.</p>                                                                      
                                    <ul class="Unordered">
                                        <li><a href="#">Agricultural Extension </a></li>
                                        <li><a href="#">Agronomy</a></li>
                                        <li><a href="#">Agricultural Economics</a></li>
                                        <li><a href="#">Animal Production</a></li>
                                        <li><a href="#">Fishery & Wildlife Management </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH."/footer.php" ?>
    </body>
</html>

