<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once("../../../path.php");

$auth_users = "11";
check_auth($auth_users, $site_root);

$referer = isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']: '';
if (strpos($referer, 'admission_payment/mastercard/index' == false) && !isset($_SESSION['payment']['gateway'])) {
    header('Location: ../index.php');
    exit;
}

$sesid = $_SESSION['admid'];
$jambregid = getSessionValue('uid');
$amount = $_SESSION['payment']['due'];

$paytype='app';
$pay_status = checkPaymentPros($sesid, $jambregid, $amount, $paytype);
if ($pay_status['status']&& !isset($_SESSION['payment']['gateway'])) {
    header('Location: ../index.php');
    exit;
}

if(!isset($_SESSION['payment']['gateway'])) {
    if (isset($_POST['paynow']) && isset($_SESSION['payment'])) {

        $payment = $_SESSION['payment'];

        // All required post data needed for transaction
        $percent = $payment['percent'];
        $revhead = $payment['revhead'];
        $canNo = $payment['jambregid'];
        $canName = $payment['name'];
        $sesid = $payment['sesid'];

        $price = $payment['amt'];
        $price *= 100; // multiply the price by 100 because TWPG deals price in kobo.
        $purpose = "APPLICATION FEE";

        $description = $revhead . "^POST UTME-DE/" . $canNo . "/" . $sesid . "^" . $purpose . "^";

        $path = SITEURL."/admission/admission_payment";
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, '0');
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
            $date = date('d/m/Y h:i:s a', time());
            $year = date('Y');
            $_SESSION['pay_ref'] = $ref = date("Ymd") . $canNo . time() . TF;

            $sql = sprintf("INSERT INTO appfee_transactions "
                    . "(can_no, can_name, amt, sesid, reference, year, status, date_time, ordid, sessionid, "
                    . "gatewayurl, percentPaid)"
                    . "VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", 
                    GetSQLValueString($canNo, "text"), 
                    GetSQLValueString($canName, "text"), 
                    GetSQLValueString('NGN'.number_format($payment['amt'], 2), "text"), 
                    GetSQLValueString($sesid, "text"), 
                    GetSQLValueString($ref, "text"), 
                    GetSQLValueString($year, "text"), 
                    GetSQLValueString($status, "text"), 
                    GetSQLValueString($date, "text"), 
                    GetSQLValueString($orderid, "text"), 
                    GetSQLValueString($sessionid, "text"), 
                    GetSQLValueString($gateway_url, "text"), 
                    GetSQLValueString($percent, "text"));

            mysql_query($sql, $tams);

            if(mysql_errno() == 0) {
                unset($_SESSION['payment']);
                header("location: " . $gateway_url);
                exit;
            }else {
                $_SESSION['payment']['gateway'] = $gateway_url;
            }        

        }
    }else {
        header('Location: ../index.php');
        exit;
    }
}else {
    $url = $_SESSION['payment']['gateway'];
    unset($_SESSION['payment']);
    header("location: " . $url);
    exit;
}
?>

<a href="processpayment.php">
    <button class="btn btn-primary">Try Again</button>
</a>