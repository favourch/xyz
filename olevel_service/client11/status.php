<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');
$auth_users = "10,11";
check_auth($auth_users, $site_root );


?>
<!doctype html>
<html ng-app="app">
<?php include INCPATH . "/header.php" ?>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="pageCtrl">
    <?php include INCPATH . "/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
        <?php include INCPATH . "/side_nav_bar.php" ?>

            <div id="main">
                <div class="container-fluid nav-fixed">
            <?php include INCPATH . "/page_header.php" ?>

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        O'Level Verification Module (Data Submission)
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="well well-small">
                                        <div class="alert alert-info">
                                            <i class="glyphicon-circle_info"></i> 
                                            Please Note that you will be charged for your individual O'Level Verification submission
                                            select your number of sitting and click the proceed button 
                                        </div>
                                        <form class="form-vertical" method="post" action="confirm_sitting.php">
                                            <div class="row-fluid">
                                                <div class="span3">
                                                    <div class="control-group">
                                                        <label class="control-label" for="textfield">Number of sitting</label>
                                                        <div class="controls controls-row">
                                                            <div class="input-append input-prepend">
                                                                <span class="add-on">Exam Sitting</span>
                                                                <select class="input-medium" required="" name="sitting">
                                                                    <option value="">--sitting--</option>
                                                                    <option value="1">1 sitting</option>
                                                                    <option value="2">2 sittings</option>
                                                                </select>
                                                                <button type="submit" class="btn">proceed</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        
                                    </div>
                                </div>
                            </div>
                            <p>&nbsp;</p>   
                        </div>
                    </div>
                </div>
            </div>          
        </div>
<?php include INCPATH . "/footer.php" ?>
        
    </body>
</html>

