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
                                <a href="college.php">College</a> <i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="cosit.php">Cosit</a>
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
                                        College of Science & Information Technology
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <p>The College of Sciences and Information Technology constitutes part of the main Colleges established in the University. The college has seven Departments which include: Department of Biological Sciences, Department of Physics and Telecommunication, Department of Human Kinetics and Health Education, Department of Computer and Information Technology, Department of Petroleum and Petro-Chemical Sciences and Department of Agricultural Production and Management Science, Department of Mathematics. The College offers degree options in education with capacity to combine honours with the desire to make the graduates of the College in the University to have broad based educational training for effective professional capabilities.</p>                                                                      
                                    <ul class="Unordered">
                                        <li><a href="#">Agricultural Science </a></li>
                                        <li><a href="#">Biological Sciences </a></li>
                                        <li><a href="#">Chemical Sciences </a></li>
                                        <li><a href="#">Computer and Information Science </a></li>
                                        <li><a href="">Human Kinetic & Health Education </a></li>
                                        <li><a href="">Mathematics</a></li>
                                        <li><a href="">Physics & Telecommunication</a></li>
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

