<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "1,20,21,22,23";
check_auth($auth_users, $site_root);

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
                    <br/>
                    
                    
                    <div class="row-fluid">
                        <div class="span12">
                            <ul class="tiles">
                                
                                <li class="orange">
                                    <a href="college/index.php"><span><i class="glyphicon-building"></i></span><span class="name"><?= $college_name?></span></a>
                                </li>
                                <li class="blue">
                                    <a href="department/index.php"><span><i class="glyphicon-bank"></i></span><span class="name"><?= $department_name?></span></a>
                                </li>
                                <li class="teal">
                                    <a href="programme/index.php"><span><i class="icon-book"></i></span><span class="name"><?= $programme_name?></span></a>
                                </li>
                                <li class="pink">
                                    <a href="session/index.php"><span><i class="icon-calendar"></i></span><span class="name">Session</span></a>
                                </li>
                                <li class="magenta">
                                    <a href="staff/index.php"><span><i class="icon-user"></i></span><span class="name">Staffs</span></a>
                                </li>
                                <li class="brown">
                                    <a href="student/index.php"><span><i class="icon-group"></i></span><span class="name">students</span></a>
                                </li>
                                <li class="lime">
                                    <a href="course/index.php"><span><i class="icon-book"></i></span><span class="name">Course</span></a>
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

