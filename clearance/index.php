<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "10";
check_auth($auth_users, $site_root);

fillAccomDetails($site_root, $tams);

$stdid = getSessionValue('uid');
$pid = getSessionValue('pid');

$studentSQL = sprintf("SELECT *, s.status "
                        . "FROM student s "
                        . "JOIN registration r ON s.stdid = r.stdid "
                        . "JOIN programme p ON p.duration = r.level "
                        . "JOIN session sn ON sn.sesid = r.sesid "
                        . "WHERE r.stdid = %s "
                        . "AND r.status = 'Registered' ",
                        GetSQLValueString($stdid, "text"));
$stud = mysql_query($studentSQL, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

if($row_stud['status'] != 'Graduating') {
    header('Location: ../student/profile.php');
}

$query_paid = sprintf("SELECT * "
                    . "FROM clearance_transactions  "
                    . "WHERE status='APPROVED' "
                    . "AND matric_no= %s ",
                    GetSQLValueString($stdid, 'text'));
$paid = mysql_query($query_paid, $tams) or die(mysql_error());
$row_paid = mysql_fetch_assoc($paid);
$total_paid = mysql_num_rows($paid);

if($total_paid > 0) {
    header("Location: receipt.php?no={$row_paid['ordid']}");
    exit();
}
?>
<!doctype html>
<html ng-app="tams-mod">
    <?php include INCPATH."/header.php" ?>

    <body ng-controller="PayController" data-layout-sidebar="fixed" data-layout-topbar="fixed">
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
                                    <h3><i class="icon-money"></i>
                                         Clearance Fee Payment
                                    </h3>
                                </div>
                                <div class="box-content">    
                                    <?php if($totalRows_stud < 0) :?>
                                        <p>                                                        
                                            You are not eligible to make this payment!
                                        </p>
                                    <?php elseif($total_paid > 0) :?>
                                    
                                    <div class="alert alert-warning">You have already made this payment!</div>
                                    
                                    <?php  else :?>
                                    
                                    <table  class="table ">
                                            <tr>
                                                <td colspan="2">
                                                    <p>                                                        
                                                        Your final year Final Clearance Fee is to be paid at 
                                                        the TASUED MICROFINACE BANK after the payment, you expected to 
                                                        return the payment teller to the University Bursary Department for further processing 
                                                        
                                                    </p>
                                                    
                                                    <div>
                                                        <h5>
                                                            Please be informed this payment is valid for <strong><?php echo $row_stud['sesname']?></strong> academic session. 
                                                            If for any reason your actual graduation session differs from the session stated on this page, your 
                                                            payment will not be acknowledged and will not be refunded! 
                                                             
                                                        </h5>
                                                    </div>
                                                    
                                                   
                                            
                                            </td> 
                                            </tr>
                                            
                                        </table>
                                    <?php endif;?>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
    </body>
</html>