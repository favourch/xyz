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
                    <?php include INCPATH."/page-header.php" ?>
                    <div class="breadcrumbs">
                        <ul>
                            <li>
                                <a href="more-login.html">Home</a><i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="student.php">Profile</a><i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="complaint.php.php">Complaint</a>
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
                                        Complaint Form
                                    </h3>
                                </div>
                                <div class="box-content nopadding">
                                    <form action="#" method="POST" class='form-horizontal form-bordered'>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Name</label>
                                            <div class="controls">
                                                <span class="uneditable-input">Lawal Rasheedat</span>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Email</label>
                                            <div class="controls">
                                                <span class="uneditable-input">change@youremail.com</span>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Department</label>
                                            <div class="controls">
                                                <span class="uneditable-input">Agricultural Science</span>
                                            </div>
                                        </div>                                        
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Telephone</label>
                                            <div class="controls">
                                                <span class="uneditable-input">080xxxxxxxx</span>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label for="select" class="control-label">Complaint Category</label>
                                            <div class="controls">
                                                <select name="select" id="select" class="input-large">
                                                    <option value="1">--Choose Category--</option>
                                                    <option value="2">General</option>
                                                    <option value="2">Result/Transcript</option>
                                                    <option value="3">Course Registration</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label for="textarea" class="control-label">Complain</label>
                                            <div class="controls">
                                                <textarea name="textarea" id="textarea" rows="5" class="input-block-level"> </textarea>
                                            </div>
                                        </div>
                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>
                                    </form>                           
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

