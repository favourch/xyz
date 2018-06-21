<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../path.php');


$pstid = -1;
if(isset($_GET['pstid'])){
    $pstid = $_GET['pstid'];
}

$token = -1;
if(isset($_GET['token'])){
    $token = $_GET['token'];
}


$SQL = sprintf("SELECT * "
            . "FROM prospective "
            . "WHERE jambregid = %s "
            . "AND act_token = %s ",
            GetSQLValueString($pstid, 'text'),
            GetSQLValueString($token, 'text'));
$Rs = mysql_query($SQL, $tams) or die(mysql_error());
$row_Rs = mysql_fetch_assoc($Rs);
$num_row = mysql_num_rows($Rs);

$mesg = FALSE;

if($num_row < 1){
    $mesg = "Sorry! Unable to activate your Account. Activation Token Mismatch !";
}

if($num_row > 0 && $row_Rs['activate'] == 'true'){
    $mesg = 'Your account is already activated. Please <a href="progress.php">Click here</a> to login to your account. ';
}


if($num_row > 0 && $row_Rs['activate'] == 'false'){
    $SQL1 = sprintf("UPDATE "
                . "prospective  SET activate = 'true'"
                . "WHERE jambregid = %s ", GetSQLValueString($pstid, 'text'));
    $Rs1 = mysql_query($SQL1, $tams) or die(mysql_error());
    
    doLogin(1, $row_Rs['jambregid'], $row_Rs['lname'], "progress.php");
}
?>

<!doctype html>
<html>
    <?php include INCPATH . "/header.php"?>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
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
                                    Applicant Account Activation 
                                    </h3>
                                </div>
                                <div class="box-content ">
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <div class="alert alert-warning">
                                                <?php if($mesg){?>
                                                <h4><?= $mesg; ?></h4>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH . "/footer.php" ?>
        </div> 
    </body>
</html>