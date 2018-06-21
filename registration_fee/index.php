<?php 
if (!isset($_SESSION)) {
    session_start();
}
require_once('../path.php');



$auth_users = "11";
check_auth($auth_users, $site_root);


$studentSQL = sprintf("SELECT * "
                    . "FROM prospective "
                    . "WHERE jambregid = %s ", 
                    GetSQLValueString(getSessionValue('uid'), "text"));
$stud = mysql_query($studentSQL, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);




$query_paid = sprintf("SELECT * "
                    . "FROM registration_transactions  "
                    . "WHERE status='APPROVED' "
                    . "AND can_no= %s ",
                    GetSQLValueString(getSessionValue('uid'), 'text')); 
$paid = mysql_query($query_paid, $tams) or die(mysql_error());
$row_paid = mysql_fetch_assoc($paid);
$total_paid = mysql_num_rows($paid); 

if ($total_paid > 0 ) {

//means this candidate has paid
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
                                         Registration Fee Payment
                                    </h3>
                                </div>
                                <div class="box-content">    
                                    <?php if($total_paid > 0){?>
                                    
                                    <div class="alert alert-warning">You have already made this payment. </div>
                                    
                                    <?php } else{?>
                                    
                                    <table  class="table ">
                                            <tr>
                                                <td colspan="2">
                                                    <p>
                                                        Your Registration fee is to be paid by selecting a card type below and using our webpay platform.
                                                    </p>

                                                    <p>
                                                        Payment will be made using Debit/Credit Cards (ATM Card)<br>
                                                        Your Card can be from <u>any of the Nigerian Banks</u>
                                            <br>Ensure that your card has been enabled for internet transactions
                                            by your bank (kindly enquire from your bank if you must).
                                            </p> 
                                            <p>
                                                <b style="color :red">Fees paid to Tai Solarin University of Education are non-refundable</b>
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
                                    <?php }?>
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