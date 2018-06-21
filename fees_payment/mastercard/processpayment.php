<?php



if (!isset($_SESSION)) {
    session_start();
}
require_once('../../path.php');


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


mysql_select_db($database_tams, $tams);

// All required post data needed for transaction
$paymentParams = $_SESSION['payment'];

$percent = $paymentParams['percent'];
$revhead = $paymentParams['revhead'];
$canNo = getSessionValue('stid');
$canName = getSessionValue('lname') . ' ' . getSessionValue('fname') . ' ' . getSessionValue('mname');
$sesid = $paymentParams['sesid'];
$scheduleid = $paymentParams['scheduleid'];
$level = $paymentParams['level'];

if (checkFees($sesid, $canNo)) {
    header('Location: index.php');
}

if (isset($paymentParams['installment']) && $paymentParams['installment'] != 'complete') {
    $price = $paymentParams['amount'] * round(($paymentParams['percent'] / 100), 2);
}

if ($paymentParams['prev_ses']) {
    $price = $paymentParams['amount'] + $paymentParams['penalty'];
}

if ($paymentParams['additions']) {
    $price = $paymentParams['amount'];
}

$price = $price * 100; // multiply the price by 100 because TWPG deals price in kobo.
$purpose = "SCHOOL FEE";



$description = $revhead . "^SCHOOL FEES/" . $sesid . "/" . $canNo . "^" . $purpose . "^";

//echo $description. "<br/>\n";
$xml = "<?xml version='1.0' encoding='UTF-8'?>
    <TKKPG>
    <Request>
    <Operation>CreateOrder</Operation>
    <Language>EN</Language>
    <Order>
    <Merchant>TASUEDEDU</Merchant>
    <Amount>" . $price . "</Amount>
    <Currency>566</Currency>
    <Description>" . $description . "</Description>
    <ApproveURL>http://portal.tasued.edu.ng/tams/student/fees_payment/paid.php</ApproveURL>
    <CancelURL>http://portal.tasued.edu.ng/tams/student/fees_payment/cancel.php</CancelURL>
    <DeclineURL>http://portal.tasued.edu.ng/tams/student/fees_payment/decline.php</DeclineURL>
    </Order>
    </Request>
    </TKKPG>";

$ch = curl_init();
// former testing url curl_setopt($ch, CURLOPT_URL,"https://196.46.20.36:5443/Exec"); 
curl_setopt($ch, CURLOPT_URL, "196.46.20.33:5444/exec");

curl_setopt($ch, CURLOPT_VERBOSE, '1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, '1');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, '1');
curl_setopt($ch, CURLOPT_CAINFO, getcwd() . '/tasuedcert/CAcert.crt');
curl_setopt($ch, CURLOPT_SSLCERT, getcwd() . '/tasuedcert/TASUEDEDU.pem');
curl_setopt($ch, CURLOPT_SSLKEY, getcwd() . '/tasuedcert/TASUEDEDU.key');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

$response = curl_exec($ch);
echo curl_error($ch);

if (!(curl_errno($ch) > 0)) {

    $parsedxml = simplexml_load_string($response);

    foreach ($parsedxml->children() as $RESPONSENODE) {
        foreach ($RESPONSENODE->children() as $ORDERNODE) {
            foreach ($ORDERNODE->children() as $child) {
                if ($child->getName() == "OrderID")
                    $orderid = $child;

                if ($child->getName() == "SessionID")
                    $sessionid = $child;

                if ($child->getName() == "URL")
                    $url = $child;
            }
        }
    }//end all loop

    $gateway_url = $url . "?ORDERID=" . $orderid . "&SESSIONID=" . $sessionid;
    
    $status = "PENDING";
    date_default_timezone_set('Africa/Lagos');
    //$date = date('d/m/Y h:i:s a', time());
    $date = date('Y-m-d', time());
    $year = date('Y');
    $ref = date("Ymd") . $canNo . time() . TF;

    mysql_select_db($database_tams, $tams);
    $sql = "INSERT INTO "
            . "schfee_transactions (matric_no, can_name, sesid, level, reference, scheduleid, year, status, "
            . "date_time, ordid, sessionid, gatewayurl, percentPaid) "
            . "VALUES('$canNo', '$canName', '$sesid', '$level', '$ref', '$scheduleid', '$year', '$status', "
            . "'$date', '$orderid', '$sessionid', '$gateway_url', '$percent')";
    mysql_query($sql, $tams) or die(mysql_error());


    /*
     *
      THE ABOVE FORMED URL ($gateway_url) IS THE URL USED TO
      CALL THE PAYMENT GATEWAY....
      YOU CAN USE THIS URL IN THE SOURCE OF AN IFRAME.
      E.G
      <iframe src= "<?php echo $gateway_url ?>" frameborder="0" scrolling="no"></iframe>
     *
     */

    header("location: " . $gateway_url);
}