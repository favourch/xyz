<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



$auth_users = "10";
check_auth($auth_users, $site_root);

$page_title = "Tasued";




mysql_select_db($database_tams,$tams);
$query = sprintf("SELECT * FROM student WHERE stdid=%s", GetSQLValueString(getSessionValue('MM_Username'), "text"));
$rsResult =  mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);

$paydesc = "FINAL YEAR CLEARNCE FEE";
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
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Fee Payment Notification
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="form-horizontal form-bordered">
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Full Name</label>
                                            <div class="controls">
                                                <?php echo $row_result['lname'].' '.$row_result['fname'].' '.$row_result['mname']?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Matric No.</label>
                                            <div class="controls">
                                                <?php echo $row_result['stdid']?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Payment Description</label>
                                            <div class="controls">
                                                <div class="alert alert-error">Your payment transaction has been canceled!</div>
                                            </div>
                                        </div>
                                        
                                    </div> 
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
        </div>
    </body>
</html>