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
        <?php include INCPATH."/top_nav_bar_profile_student.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH."/side_nav_bar_profile_student.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH."/page-header_student.php" ?>
                    <div class="breadcrumbs">
                        <ul>
                            <li>
                                <a href="more-login.html">Home</a><i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="student.php">Profile</a>
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
                                        Student's Profile
                                    </h3>
                                </div>
                                <div class="box-content nopadding">
                                    <ul class="timeline">
                                        <li>
                                            <div class="timeline-content">
                                                <div class="row-fluid">
                                                    <div class="left">
                                                        <div class="icon lightred">
                                                            <i class="icon-user"></i>
                                                        </div>
                                                        <div class="date">Year 4</div>
                                                    </div>
                                                    <div class="activity">
                                                        <div class="span3">
                                                            <div class="user">
                                                                <strong>Lawal Rasheedat</strong>
                                                                <p>20050201009</p>
                                                            </div>
                                                            <p>
                                                                <img class="timeline-images" style="width: 250px; height: 280px;" src="img/demo/profile.jpg" />
                                                            </p>
                                                        </div>
                                                        <table class="table table-hover table-nomargin span6"> 
                                                            <div class="user"><br><br></div>
                                                            <tbody>
                                                                <tr>
                                                                    <th>Name</th>
                                                                    <td>Lawal Rasheedat</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>College</th>
                                                                    <td><a href="#">College of Science & Information Technology</a></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Department</th>
                                                                    <td><a href="#">Agricultural Science</a></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Programme</th>
                                                                    <td>B.Sc (Ed) Agricultural Extension</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Phone</th>
                                                                    <td>080xxxxxxxx</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Email</th>
                                                                    <td>change@youremail.com</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Entry Mode</th>
                                                                    <td>UTME <em>(presently in Year 4)</em></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="user activity">
                                                    <strong>Personal Statement:</strong>
                                                    <p>Your Profile Information</p>
                                                </div>
                                            </div>
                                            <div class="line"></div>
                                        </li>
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

