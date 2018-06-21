<?php

if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');

$auth_users = "10";
check_auth($auth_users, $site_root);

// All required post data needed for transaction
$paymentParams = $_SESSION['payment'];

$percent = $paymentParams['percent'];
$revhead = $paymentParams['revhead'];
$canNo = getSessionValue('stid');
$canName = getSessionValue('lname') . ' ' . getSessionValue('fname') . ' ' . getSessionValue('mname');
$sesid = $paymentParams['sesid'];
$scheduleid = $paymentParams['scheduleid'];
$level = $paymentParams['level'];

// if (checkFees($sesid, $canNo)) {
//     //header('Location: index.php');
//     exit;
// }

$price = $price * 100; // multiply the price by 100 because TWPG deals price in kobo.
$purpose = "SCHOOL FEE";

$description = $revhead . "^SCHOOL FEES/" . $sesid . "/" . $canNo . "^" . $purpose . "^";

$path = SITEURL."/payments";
$xml = "<?xml version='1.0' encoding='UTF-8'?>
        <TKKPG>
            <Request>
                <Operation>CreateOrder</Operation>
                <Language>EN</Language>
                <Order>
                    <Merchant>{$merchant_id}</Merchant>
                    <Amount>{$price}</Amount>
                    <Currency>566</Currency>
                    <Description>{$description}</Description>
                    <ApproveURL>{$path}/paid.php</ApproveURL>
                    <CancelURL>{$path}/cancel.php</CancelURL>
                    <DeclineURL>{$path}/declined.php</DeclineURL>
                </Order>
            </Request>
        </TKKPG>";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL,"https://196.46.20.33:5444/Exec"); 
curl_setopt($ch, CURLOPT_VERBOSE, '1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, '1');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, '1');
curl_setopt($ch, CURLOPT_CAINFO, CERTPATH.'/CAcert.crt');
curl_setopt($ch, CURLOPT_SSLCERT, CERTPATH."/{$merchant_id}.pem");
curl_setopt($ch, CURLOPT_SSLKEY, CERTPATH."/{$merchant_id}.key");
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
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
    $amt = 'NGN'.number_format($price);
    
    $sql = "INSERT INTO "
            . "schfee_transactions (matric_no, can_name, sesid, level, reference, amt, scheduleid, year, status, "
            . "date_time, ordid, sessionid, gatewayurl, percentPaid) "
            . "VALUES('$canNo', '$canName', '$sesid', '$level', '$ref', '$amt', '$scheduleid', '$year', '$status', "
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
    exit;
}