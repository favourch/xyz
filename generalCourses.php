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
                                <a href="courses.php">Courses</a> <i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="generalCourses.php">General Courses</a>
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
                                        General University Courses
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <form action="#" method="POST" class='form-horizontal'>
                                        <div class="control-group">
                                            <label for="select" class="control-label">Chooses Category</label>
                                            <div class="controls">
                                                <select name="select" id="select" class='input-large'>
                                                    <option value="1">--Select A Category--</option>
                                                    <option value="2">Vocation</option>
                                                    <option value="3">Entrepreneurial</option>
                                                    <option value="4">General</option>
                                                    <option value="5">Educational</option>
                                                </select>
                                            </div>
                                        </div>
                                    </form>
                                    <table class="table dataTable dataTable-scroll-x">                                                                                                                            
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Title</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>VOS111</td>
                                                <td><a href="">Introduction to fashion Design, measurement and Sewing Process.</a></td>
                                            </tr>    
                                            <tr>
                                                <td>VOS112</td>
                                                <td><a href="">Introduction to Food & Nutrition & Pastry Production.</a></td>
                                            </tr> 
                                            <tr>
                                                <td>VOS113</td>
                                                <td><a href="">Introduction to Textile Studies </a></td>
                                            </tr> 
                                            <tr>
                                                <td>VOS114</td>
                                                <td><a href="">Introduction to Computer System and Safety Precaution</a></td>
                                            </tr> 
                                            <tr>
                                                <td>VOS115</td>
                                                <td><a href="">Introduction to Woodwork workshop & Wood Processing.</a></td>
                                            </tr> 
                                            <tr>
                                                <td>VOS116</td>
                                                <td><a href="">Introduction to fruit Juice Production, Food Additives & Preservation</a></td>
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

