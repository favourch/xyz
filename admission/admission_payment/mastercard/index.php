<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');



$auth_users = "11";
check_auth($auth_users, $site_root);

$referer = isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']: '';
if(strpos($referer, 'admission_payment/mastercard/mastercard') == false) {
    header('Location: ../index.php');
    exit;
}

$sesid = $_SESSION['admid'];
$jambregid = getSessionValue('uid');
$amount = $_SESSION['payment']['due'];
$amt = $_SESSION['payment']['amt'];
$name = $_SESSION['payment']['name'];
$admtype = $_SESSION['admode'];

$paytype='app';
$pay_status = checkPaymentPros($sesid, $jambregid, $amount, $paytype); // change to just scheduleid
if($pay_status['status']) {
    header('Location: ../index.php');
    exit;
}

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
                                        Payment Confirmation
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <p>Below are the details of the payment transaction you are
                                            about to execute, click the <strong>Pay Now</strong> 
                                            button to proceed your payment or <strong>Cancel</strong> button to terminate.
                                        </p>
                                        <table class="table table-striped table-bordered table-condensed">
                                            <tr>
                                                <th width="150">UTME No. : </th>
                                                <td><?php echo $jambregid ?></td>
                                            </tr>
                                            <tr>
                                                <th>Full Name : </th>
                                                <td><?php echo strtoupper($name) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Application type : </th>
                                                <td><?php echo $admtype ?></td>
                                            </tr>
                                            <tr>
                                                <th>Amount to be paid : </th>
                                                <th style="color: #CC0000"><?php echo '=N= ' . number_format($amt); ?></th>
                                            </tr>
                                            <tr>
                                                <td colspan="2">&nbsp; </td>  
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <div class="text-center">
                                                        <form name="form1" method="post" action="processpayment.php">                                                       
                                                            <input type="submit" class="btn btn-primary" name="paynow" value="Pay Now"/>
                                                            <a href="../../termsandcon.php">
                                                                <button class="btn btn-inverse">Cancel</button>
                                                            </a>                                                            
                                                            <input name="form_trig" type="hidden" value="form1"/>
                                                        </form> 
                                                    </div>
                                                </td>
                                            </tr>
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