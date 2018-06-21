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
                                <a href="department.php">Department</a>
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
                                        Departments in the University
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <form action="#" method="POST" class='form-horizontal'>
                                        <div class="control-group">
                                            <label for="select" class="control-label">View by College</label>
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
                                    </form>
                                    <table class="table dataTable dataTable-scroll-x">                                                                                                                            
                                        <thead>
                                            <tr>
                                                <th>List of Departments</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><a href="departmentDetails.php">Agricultural Science</a></td>
                                            </tr>
                                            <tr>
                                                <td><a href="">Biological Sciences</a></td>
                                            </tr>
                                            <tr>
                                                <td><a href="">Business Management</a></td>
                                            </tr>
                                            <tr>
                                                <td><a href="">Chemical Sciences</a></td>
                                            </tr>
                                            <tr>
                                                <td><a href="">Computer and Information Science</a></td>
                                            </tr>
                                            <tr>
                                                <td><a href="">Counselling Psychology</a></td>
                                            </tr>
                                            <tr>
                                                <td><a href="">Creative Arts</a></td>
                                            </tr>
                                            <tr>
                                                <td><a href="">Economics</a></td>
                                            </tr>
                                            <tr>
                                                <td><a href="">Education Foundations & Instructional Technology</a></td>
                                            </tr>
                                            <tr>
                                                <td><a href="">Educational Management</a></td>
                                            </tr>
                                            <tr>
                                                <td><a href="">English Language</a></td>
                                            </tr>
                                            <tr>
                                                <td><a href="">French Language</a></td>
                                            </tr>
                                            <tr>
                                                <td><a href="">Geography and Environmental Management</a></td>
                                            </tr>
                                            <tr>
                                                <td><a href="">History and Diplomatic Studies</a></td>
                                            </tr>                                                                                       
                                        </tbody>
                                    </table>
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

