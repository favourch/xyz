<?php 
if (!isset($_SESSION)) {
    session_start();
}

require_once '../inc/con.php';

function getStatus($transref,$mertid, $type='', $sign){
	$request = 'mertid='.$mertid.'&transref='.$transref.'&respformat='.$type.'&signature='.$sign; //initialize the request variables
	$url = 'https://www.cashenvoy.com/sandbox2/?cmd=requery'; //this is the url of the gateway's test api
	//$url = 'https://www.cashenvoy.com/webservice/?cmd=requery'; //this is the url of the gateway's live api
	$ch = curl_init(); //initialize curl handle
	curl_setopt($ch, CURLOPT_URL, $url); //set the url
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true); //return as a variable
	curl_setopt($ch, CURLOPT_POST, 1); //set POST method
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request); //set the POST variables
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//change to true when live
	$response = curl_exec($ch); // grab URL and pass it to the browser. Run the whole process and return the response
	curl_close($ch); //close the curl handle
	return $response;	
}

if(isset($_POST['pay_status'])){
    
    $key = $_SESSION['merchant_key'];
    $transref = $_POST['ref'];
    $mertid = $_SESSION['merchant_id'];;
    $type = 'json'; //Data return format. Options are xml or json. leave blank if you want data returned in string format.
    $cdata = $key.$transref.$mertid;
    $signature = hash_hmac('sha256', $cdata, $key, false);
    $response = getStatus($transref,$mertid,$type,$signature);
    
    $res = json_decode($response, true);
    
    
    $updateCashenvoy = sprintf("UPDATE transactions "
                            . "SET status = %s, amount = %s  "
                            . "WHERE ref = %s ", 
                            GetSQLValueString($res['TransactionStatus'], 'text'), 
                            GetSQLValueString($res['TransactionAmount'], 'double'),
                            GetSQLValueString($res['TransactionId'], 'text'));
    $rs = mysql_query($updateCashenvoy, $con) or die(mysql_error());
    
    header(sprintf("location: %s", $_SERVER['HTTP_REFERER']));
    die();
    
}

if(isset($_POST['ce_transref'])){
    
    $key = $_SESSION['merchant_key'];
    $transref = $_POST['ce_transref'];
    $mertid = $_SESSION['merchant_id'];;
    $type = 'json'; //Data return format. Options are xml or json. leave blank if you want data returned in string format.
    $cdata = $key.$transref.$mertid;
    $signature = hash_hmac('sha256', $cdata, $key, false);
    $response = getStatus($transref,$mertid,$type,$signature);
    
    $res = json_decode($response, true);
    
    $updateCashenvoy = sprintf("UPDATE transactions "
                            . "SET status = %s, amount = %s  "
                            . "WHERE ref = %s ", 
                            GetSQLValueString($res['TransactionStatus'], 'text'), 
                            GetSQLValueString($res['TransactionAmount'], 'double'),
                            GetSQLValueString($res['TransactionId'], 'text'));
    $rs = mysql_query($updateCashenvoy, $con) or die(mysql_error());
    
}

?>
<html>
    <head>
        <title>Cashenvoy Response page</title>
        <!-- Bootstrap core CSS -->
        <link href="../../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="alert">
            <p>
                 <?= getPayStatus($res['TransactionStatus'])?>
            </p>
        </div>
        <button type="button" onclick="return refreshParent();">Close payment</button>
    </body>
</html>




<script>
    function refreshParent() {
        window.opener.location.reload();
        window.self.close();
    }
</script>