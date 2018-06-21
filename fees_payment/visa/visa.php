<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');



$page_title = "Tasued";

$MM_authorizedUsers = "10";

$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page

function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) {

    // For security, start by assuming the visitor is NOT authorized. 

    $isValid = False;



    // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
    // Therefore, we know that a user is NOT logged in if that Session variable is blank. 

    if (!empty($UserName)) {

        // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
        // Parse the strings into arrays. 

        $arrUsers = Explode(",", $strUsers);

        $arrGroups = Explode(",", $strGroups);

        if (in_array($UserName, $arrUsers)) {

            $isValid = true;
        }

        // Or, you may restrict access to only certain users based on their username. 

        if (in_array($UserGroup, $arrGroups)) {

            $isValid = true;
        }

        if (($strUsers == "") && false) {

            $isValid = true;
        }
    }

    return $isValid;
}

$MM_restrictGoTo = "../index.php";

if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("", $MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {

    $MM_qsChar = "?";

    $MM_referrer = $_SERVER['PHP_SELF'];

    if (strpos($MM_restrictGoTo, "?"))
        $MM_qsChar = "&";

    if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0)
        $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];

    $MM_restrictGoTo = $MM_restrictGoTo . $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);

    header("Location: " . $MM_restrictGoTo);

    exit;
}





$query = sprintf("SELECT * "
        . "FROM session WHERE status = 'TRUE'"
        . "ORDER BY sesid DESC LIMIT 1");
$session = mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);
$totalRows_session = mysql_num_rows($session);


if (checkFees($row_session['sesid'], getSessionValue('stid'))) {
    header('Location: index.php');
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