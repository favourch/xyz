<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "10";
check_auth($auth_users, $site_root);

$stdid = getSessionValue('uid');
$pid = getSessionValue('pid');

$studentSQL = sprintf("SELECT *, s.status "
                        . "FROM student s "
                        . "JOIN registration r ON s.stdid = r.stdid "
                        . "JOIN programme p ON p.duration = r.level "
                        . "JOIN session sn ON sn.sesid = r.sesid "
                        . "WHERE r.stdid = %s "
                        . "AND r.status = 'Registered' "
                        . "AND p.progid = %s", 
                        GetSQLValueString($stdid, "text"),
                        GetSQLValueString($pid, "int")); 
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

if ($total_paid > 0) {
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
                                    <?php if($totalRows_stud < 1) :?>
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
                                                        Your Final year final clearance fee is to be paid by selecting a card type below and using our webpay platform.
                                                        
                                                    </p>
                                                    
                                                    <div>
                                                        <h5>
                                                            Please be informed this payment is valid for <strong><?php echo $row_stud['sesname']?></strong> academic session. 
                                                            If for any reason your actual graduation session differs from the session stated on this page, your 
                                                            payment will not be acknowledged and will not be refunded! 
                                                             
                                                        </h5>
                                                    </div>
                                                    
                                                    <p>
                                                        Payment will be made using Debit/Credit Cards (ATM Card)<br>
                                                        Your Card can be from <u>any of the Nigerian Banks</u>
                                            <br>Ensure that your card has been enabled for internet transactions
                                            by your bank (kindly enquire from your bank if you must).
                                            </p> 
                                            <p>
                                                <b style="color :red">Fees paid to <?php echo $university?> are non-refundable</b>
                                            <h4>Are you using Internet explorer browser?</h4>
                                            Avoid browser issues, uncheck support for Use SSL2.0 by following the steps below:<br/>
                                            1. Click on Tool option on the menu bar<br/>
                                            2. Select Internet Options<br/>
                                            3. Click Advance tab<br/>
                                            4. Scroll down to Security option and uncheck Use SSL 2.0<br/>
                                            </p>
                                            </td> 
                                            </tr>
                                            <tr>
                                                <td >
                                                    <table width="400" align="center" class="table table-bordered table-striped table-condensed">
                                                        <tr>
                                                            <th colspan="2">Select a payment method to continue</th>
                                                        </tr>
                                                        <tr>
                                                            <td align="center" width="50%" style=" "><a href="mastercard/mastercard.php"><img src="<?php echo SITEIMGURL?>/payment/mastercard.png"></a></td>
                                                            <td align="center" width="50%"><a href="visa/visa.php"><img src="<?php echo SITEIMGURL?>/payment/visa.jpg"></a></td>
                                                        </tr>
                                                    </table>
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