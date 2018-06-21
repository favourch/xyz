<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



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

$page_title = "Tasued";

//Get current session 

mysql_select_db($database_tams, $tams);

$query = sprintf("SELECT * "
        . "FROM session WHERE status = 'TRUE'"
        . "ORDER BY sesid DESC LIMIT 1");

$session = mysql_query($query, $tams) or die(mysql_error());

$row_session = mysql_fetch_assoc($session);

$totalRows_session = mysql_num_rows($session);



if (checkFees($row_session['sesid'], getSessionValue('stid'))) {

    header('Location: index.php');
}



if ($_SESSION['payment']['prev_ses']) {

    $_SESSION['payment']['percent'] = 100;

    //$_SESSION['payment']['percent'] = $_GET['pc'];
}
else {

    $validPercent = array($_SESSION['payment']['instalpercent'][0], $_SESSION['payment']['instalpercent'][1], 100);



    if ($_SESSION['payment']['installment'] == 'none') {

        if (isset($_GET['pc']) && in_array($_GET['pc'], $validPercent)) {

            $_SESSION['payment']['percent'] = $_GET['pc'];
            
        }else{
            
            header('Location: index.php');
            
        }
    }
    elseif ($_SESSION['payment']['installment'] == 'incomplete') {
        
        if (isset($_GET['pc']) && in_array($_GET['pc'], $validPercent)) {

            $_SESSION['payment']['percent'] = $_GET['pc'];
            
        }else{
            
            header('Location: index.php');
            
        }
        
    }else{
        
        if (isset($_GET['pc']) && in_array($_GET['pc'], $validPercent)) {

            $_SESSION['payment']['percent'] = 100;
            
        }else{
            
            header('Location: index.php');
            
        }
        
    }
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
                                    <h3><i class="icon-money"></i>
                                        Fee Payment Instruction
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <div class="well well-small">
                                            <p>
                                                Your School fee is to be paid by selecting a card type below and using our webpay platform.
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
                                            <div class="span5 text-center ">
                                                <a href="mastercard/mastercard.php" class="hover"><img src="img/mastercard.png"></a>
                                            </div>
                                            <div class="span5 text-center">
                                                <a href="visa/visa.php" class="hover"><img src="img/visa.jpg"></a>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
    </body>
</html>