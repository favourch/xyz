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
                                <a href="college.php">College</a>
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
                                        Colleges in the University
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <p>The academic programmes of the Tai Solarin University of Education are organized into Departments and a group of which shall constitute a College. Each Departments has one or more options in academic Programmes. The University operates the College system of Administration. There are four main College with departments and the Postgraduate School. The Colleges, Departments and Programmes are listed below</p>                                                                      
                                    <ul class="Unordered">
                                        <li><a href="collegeDetails.php">College of Science & Information Technology</a></li>
                                        <li><a href="#">College of Applied Education and Vocational Technology</a></li>
                                        <li><a href="#">College of Humanities</a></li>
                                        <li><a href="#">College of Social and Management Sciences</a></li>
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

