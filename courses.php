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
                                <a href="courses.php">Courses</a>
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
                                        Courses in the University
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <form action="#" method="POST" class='form-horizontal row-fluid'>
                                        <div class="control-group span6">
                                            <label for="select" class="control-label">Choose College</label>
                                            <div class="controls">
                                                <select name="select" id="select" class='input-large'>
                                                    <option value="1">--Select A College--</option>
                                                    <option value="2">COSIT</option>
                                                    <option value="3">COAEVOT</option>
                                                    <option value="4">COHUM</option>
                                                    <option value="5">COSMAS</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group span6">
                                            <label for="select" class="control-label">View by Department</label>
                                            <div class="controls">
                                                <select name="select" id="select" class='input-large'>
                                                    <option value="1">--Select A Department--</option>
                                                    <option value="2">COSIT</option>
                                                    <option value="3">COAEVOT</option>
                                                    <option value="4">COHUM</option>
                                                    <option value="5">COSMAS</option>
                                                </select>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="alert alert-danger alert-nomargin">                                        
                                        <h4>Important!</h4>
                                        Apart from the departmental courses, students are expected to offer some courses in other departments within their college and some general <strong><a href="generalCourses.php">university course</a></strong>.
                                    </div><br>
                                    <table class="table dataTable dataTable-scroll-x">                                                                                                                            
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Title</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>AER311</td>
                                                <td><a href="">Extention Teaching, Learning Process and Package Recommendation</a></td>
                                            </tr>    
                                            <tr>
                                                <td>AER312</td>
                                                <td><a href="">Principle and Practice of Agric. Extension and Rural Sociology</a></td>
                                            </tr> 
                                            <tr>
                                                <td>AER321</td>
                                                <td><a href="">Group Dynamics in Extension</a></td>
                                            </tr> 
                                            <tr>
                                                <td>AER322</td>
                                                <td><a href="">Research Methods</a></td>
                                            </tr> 
                                            <tr>
                                                <td>AER323</td>
                                                <td><a href="">Production and Use of Audio-Visual Aids in Agricultural Com</a></td>
                                            </tr> 
                                            <tr>
                                                <td>AER324</td>
                                                <td><a href="">Social Processes and Systems</a></td>
                                            </tr> 
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
    </body>
</html>