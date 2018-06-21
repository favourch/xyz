<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "11";
check_auth($auth_users, $site_root);

$referer = isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']: '';
if(strpos($referer, 'acceptance_payment/index') == false) {
    header('Location: index.php');
    exit;
}

$sesid = $_SESSION['admid'];
$jambregid = getSessionValue('uid');
$amount = $_SESSION['payment']['due'];

$paytype='acc';
 $pay_status = checkPaymentPros($sesid, $jambregid, $amount, $paytype); 
  if($pay_status['status']) { 
   header('Location: index.php');
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
                                        <?php echo $_SESSION['admname']?> Acceptance Payment Instruction
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <div>
                                            <p>
                                                Your Acceptance fee is to be paid by selecting a card type below and using our webpay platform.
                                            </p>

                                            <p>
                                                Payment will be made using Debit/Credit Cards (ATM Card)<br>
                                                Your Card can be from <u>any of the Nigerian Banks</u><br>
                                                Ensure that your card has been enabled for internet transactions
                                                by your bank (kindly enquire from your bank if you must).
                                            </p> 
                                            <p>
                                                <b style="color :red">Fees paid to <?php echo $university?> are non-refundable</b>
                                            <h4>Are you using Internet explorer browser?</h4>
                                            <p>
                                                Avoid browser issues, uncheck support for Use SSL2.0 by following the steps below:<br/>
                                                1. Click on Tool option on the menu bar<br/>
                                                2. Select Internet Options<br/>
                                                3. Click Advance tab<br/>
                                                4. Scroll down to Security option and uncheck Use SSL 2.0<br/>
                                            </p>
                                        </div>
                                        
                                        <div class="center">
                                            <div class="text-center span10">
                                                Select a card type to continue
                                            </div>
                                            <div class="span5 text-right">
                                                <a href="mastercard/mastercard.php"><img src="<?php echo SITEIMGURL?>/payment/mastercard.png"></a>
                                            </div>
                                            <div class="span5">
                                                <a href="visa/visa.php"><img src="<?php echo SITEIMGURL?>/payment/visa.jpg"></a>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
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