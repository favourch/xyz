<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');
//$auth_users = "1,20,24,26, 10,11";
//check_auth($auth_users, $site_root . '/admin');

$referer = $_SERVER['HTTP_REFERER'];



if(isset($_POST['sitting'])){
    $_SESSION['olevel_sitting'] = $_POST['sitting'];
}else{
   header(sprintf("Location: %s", $referer));
    die();
}



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
                                            You have choose to submit <?= $_SESSION['olevel_sitting'] ?> 
                                            Sitting of O'Level result
                                            click the Proceed button or Cancel to go back 
                                        </div> 
                                        <div class="well well-small">
                                            <h2>O'Level Sitting</h2>
                                            <p>
                                                Sitting(s ) = <?= $_SESSION['olevel_sitting'] ?>
                                            </p>
                                        </div>
                                        <div class="row-fluid">
                                            <div class="span6">
                                                <div class="pull-left left"><a href="" class="btn btn-warning">Cancel</a></div>        
                                            </div>
                                            <div class="span6">
                                                <div class="pull-right right"><a href="olevel_veri_payment/index.php" class="btn btn-success">Proceed</a></div>                
                                            </div>
                                        </div>
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

