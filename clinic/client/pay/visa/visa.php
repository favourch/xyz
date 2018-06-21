<?php

if (!isset($_SESSION)) {
    session_start();
}
require_once('../../path.php');



$auth_users = "10";
check_auth($auth_users, $site_root);



if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root . '../../student/profile.php');
}

$query = sprintf("SELECT * "
        . "FROM session WHERE status = 'TRUE'"
        . "ORDER BY sesid DESC LIMIT 1");

$session = mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);
$totalRows_session = mysql_num_rows($session);


mysql_select_db($database_tams, $tams);
$query_paid = sprintf("SELECT * "
                    . "FROM clearance_transactions  "
                    . "WHERE  status='APPROVED' "
                    . "AND  matric_no= %s ", 
                    GetSQLValueString(getSessionValue('uid'), 'text'));
$paid = mysql_query($query_paid, $tams) or die(mysql_error());
$row_paid = mysql_fetch_assoc($paid);
$total_paid = mysql_num_rows($paid);

if ($total_paid > 0) {

//means this candidate has paid
    header('Location: ../index.php');
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
                                    <h3><i class="icon-credit-card"></i>
                                        Visa Instruction
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <?php if($total_paid > 0){?>
                                        <div class="alert alert-warning">You have already make this payment</div>
                                        <?php }else{ ?>
                                            <div class="well well-small">
                                                <p>
                                                    This site is protected with Verified by Visa (VbV),
                                                    Visa Password-Protected Identity Checking Service,
                                                    and requires that the card is enrolled to participate
                                                    in the VbV program. If your Visa Card issued by Nigerian
                                                    Banks is not enrolled, kindly follow the steps outlined
                                                    below.
                                                </p>
                                                <ol>
                                                    <li>Locate the nearest VISA/VPAY enabled ATM</li>
                                                    <li>Insert your card and punch in your PIN</li>
                                                    <li>Select the PIN change option</li>
                                                    <li>Select Internet PIN (i-PIN) change option</li>
                                                    <li>Insert any four - six digits of your choice as your
                                                        i-PIN</li>
                                                    <li>Re-enter the digits entered in step 5</li>
                                                    <li>If you have done the above correctly, a message is
                                                        displayed that your PIN was changed successfully.
                                                        This means your card is now enrolled in the VbV program
                                                        and you have an Internet PIN (i-PIN) which can be
                                                        used for any internet related transaction</li>
                                                    <li>Note the the word "<strong>i-PIN</strong>","<strong>Password</strong>"
                                                        and "<strong>VbV Code</strong>" are the same</li>
                                                    <li>You
                                                        can now visit your favourite VbV enabled site to shop
                                                        securely</li>
                                                    <p>
                                                        <strong>Important</strong><br />
                                                        Please note that this is only for internet related
                                                        transactions and it does not change your regular PIN
                                                        on ATM and POS.
                                                    </p>
                                                </ol>
                                            </div>
                                            <div class="text-center">
                                                <a href="index.php">
                                                    <button class="btn btn-primary">Pay Now</button>
                                                </a>
                                            </div>
                                        <?php }?>
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