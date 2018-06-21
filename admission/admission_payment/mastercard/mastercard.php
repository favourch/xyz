<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');



$auth_users = "11";
check_auth($auth_users, $site_root);

$referer = isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']: '';
if(strpos($referer, 'admission_payment/paymentinfo') == false) {
    header('Location: ../index.php');
    exit;
}

$sesid = $_SESSION['admid'];
$jambregid = getSessionValue('uid');
$amount = $_SESSION['payment']['due'];

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
                                        MasterCard Instruction
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <div>
                                            <p>
                                                This site is a MasterCard SecureCode (MCSC) participating
                                                Merchant's website. MCSC is designed to enable you (cardholder) make safer internet 
                                                purchase transactions by authenticating your identity at the time of purchase in order to protect you from unauthorized usage of your MasterCard.
                                            </p>
                                            <p>
                                                MasterCard SecureCode password is strictly for
                                                online transactions and it is different from your
                                                regular Personal Identification Number (PIN) used
                                                for ATM and POS transactions.
                                            </p>
                                            <p>
                                                Please follow the steps below to obtain and use your MasterCard SecureCode:
                                            </p>
                                            <ol>
                                                <li>Click on the <strong>Pay Now</strong> button below to proceed to the next page</li>
                                                <li>
                                                    Enter your MasterCard card details such as Card Number,
                                                    CVV2, Name on card, Expiry date and click OK</li>
                                                <li>
                                                    You will be redirected to your bank's website,
                                                    kindly follow the process to completion as advised by your bank
                                                </li>
                                                <li>
                                                    The next time you make purchase on the website
                                                    of a participating Merchant, simply enter the MCSC
                                                    Password and any Secret Questions (if any)  you created if
                                                    required by your bank.
                                                </li>

                                                <p>
                                                    <strong>Important</strong><br />
                                                    The activation process is determined by your bank.
                                                    Should you encounter any problem, please contact your
                                                    bank
                                                </p>
                                            </ol>
                                        </div>
                                        <div class="text-center">
                                            <a href="index.php">
                                                <button class="btn btn-primary">Pay Now</button>
                                            </a>
                                            <a href="../../termsandcon.php">
                                                <button class="btn btn-inverse">Cancel</button>
                                            </a>
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