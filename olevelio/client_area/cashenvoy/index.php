<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once '../inc/con.php';
//session_destroy();


$cashevoyConfig = array();

$cashenvoySQL = sprintf("SELECT * FROM cashenvoy_config ");
$cashenvoyRS = mysql_query($cashenvoySQL, $con) or die(mysql_error());
$row_cashenvoy = mysql_fetch_assoc($cashenvoyRS);

do{
    $cashevoyConfig[$row_cashenvoy['param']] = $_SESSION[$row_cashenvoy['param']] = $row_cashenvoy['data'] ;
}while($row_cashenvoy = mysql_fetch_assoc($cashenvoyRS));



// this file shows how you can call the CashEnvoy payment interface from your online store

// your CashEnvoy merchant id
//$cemertid = '6365';
$cemertid = $cashevoyConfig['merchant_id'];

// your merchant key (login to your cashenvoy account, your merchant key is displayed on the dashboard page)
//$key = '615ee4a3cb909aa50aed9a057f7198e4';
$key = $cashevoyConfig['merchant_key'];

$ref = uniqid();
// transaction reference which must not contain any special characters. Numbers and alphabets only.
$cetxref = $ref;

// transaction amount
//$ceamt = 1000.44;
$ceamt = doubleval($_SESSION['charges']);
// customer id does not have to be an email address but must be unique to the customer
$cecustomerid = $_SESSION['user_id']; 

// a description of the transaction
$cememo = 'Olevel result verificaton fee';

// notify url - absolute url of the page to which the user should be directed after payment
// an example of the code needed in this type of page can be found in example_requery_usage.php
//$cenurl = 'http://www.youronlinestore.com/paymentcomplete.php'; 

//$cenurl = 'http://localhost/olevelio/client_area/index.php?school='.$_SESSION['school_id'].'&result_id='.$_SESSION['result_id'].'&key='.$_SESSION['key'].'&user_id='.$_SESSION['user_id'].'/#!/pay';
$cenurl = 'https://my.tasued.edu.ng/olevelio/client_area/cashenvoy/response.php';
// generate request signature
$data = $key.$cetxref.$ceamt;
$signature = hash_hmac('sha256', $data, $key, false);


$InsertcashenvoySQL = sprintf("INSERT INTO "
        . "transactions (school_id, user_id, result_id, ref, amount, pay_used, status, created_at) "
        . "VALUE(%s, %s, %s,%s, %s, 'no', %s, NOW())",
        GetSQLValueString($_SESSION['school_id'], 'text'), 
        GetSQLValueString($_SESSION['user_id'], 'text'),
        GetSQLValueString($_SESSION['result_id'], 'text'),
        GetSQLValueString($cetxref, 'text'),
        GetSQLValueString($ceamt, 'text'),
        GetSQLValueString('pending', 'text'));
$InsertcashenvoyRS = mysql_query($InsertcashenvoySQL, $con) or die(mysql_error());


?>
<body onLoad="document.submit2cepay_form.submit()">
<!-- 
Note: Replace https://www.cashenvoy.com/sandbox2/?cmd=cepay with https://www.cashenvoy.com/webservice/?cmd=cepay once you have been switched to the live environment.
-->
<form method="post" name="submit2cepay_form" action="https://www.cashenvoy.com/sandbox2/?cmd=cepay" target="_self">
<input type="hidden" name="ce_merchantid" value="<?= $cemertid ?>"/>
<input type="hidden" name="ce_transref" value="<?= $cetxref ?>"/>
<input type="hidden" name="ce_amount" value="<?= $ceamt ?>"/>
<input type="hidden" name="ce_customerid" value="<?= $cecustomerid ?>"/>
<input type="hidden" name="ce_memo" value="<?= $cememo ?>"/>
<input type="hidden" name="ce_notifyurl" value="<?= $cenurl ?>"/>
<input type="hidden" name="ce_window" value="parent"/><!-- self or parent -->
<input type="hidden" name="ce_signature" value="<?= $signature ?>"/>
<!-- <input type="submit" name="submit" value="Submt"/> -->
</form>
</body>