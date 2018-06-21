<?php 
if (!isset($_SESSION)) {
  session_start();
}

require_once('../../path.php');

$auth_users = "11";
check_auth($auth_users, $site_root);

$paydesc = "POST UTME/DE APPLICATION FEE";

$query = sprintf("SELECT * FROM prospective WHERE jambregid=%s", 
        GetSQLValueString(getSessionValue('uid'), "text"));
$rsResult =  mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);

// unified payment code
if (isset($_POST["xmlmsg"])) {
    $xml = simplexml_load_string($_POST["xmlmsg"]);

    foreach ($xml->children() as $child) {
        if ($child->getName() == "ResponseDescription") {
            $_SESSION['response'] = $child;
            $res = $child;
        }

        if ($child->getName() == "PurchaseAmountScr") {
            $_SESSION['amt'] = "NGN" . $child;
            $amt = $_SESSION['amt'];
        }
        if ($child->getName() == "ApprovalCode")
            $_SESSION['approvalcode'] = $child;

        if ($child->getName() == "OrderID")
            $ordid = $child;

        if ($child->getName() == "PAN")
            $pan = $child;
        if ($child->getName() == "TranDateTime")
            $date = $child;
        if ($child->getName() == "OrderStatus")
            $status = $child;
        if ($child->getName() == "Brand")
            $brand = $child;

        if ($child->getName() == "PurchaseAmount")
            $rawAmount = $child;
        if ($child->getName() == "Name")
            $name = $child;

        if ($child->getName() == "ResponseCode")
            $rc = $child;

        if ($child->getName() == "ApprovalCode")
            $ac = $child;

        if ($child->getName() == "TranDateTime")
            $dt = $child;

        $year = date('Y');
    }//end for loop

    $xmlmsg = $_POST['xmlmsg'];

    mysql_query('BEGIN', $tams);

    $query_paid = sprintf("UPDATE appfee_transactions 
    SET status = %s, amt = %s, resp_code = %s, resp_desc = %s, auth_code = %s, pan = %s, xml = %s, name = %s  
    WHERE ordid=%s", 
            GetSQLValueString("APPROVED", "text"), 
            GetSQLValueString($amt, "text"), 
            GetSQLValueString($rc, "text"), 
            GetSQLValueString($res, "text"), 
            GetSQLValueString($ac, "text"), 
            GetSQLValueString($pan, "text"), 
            GetSQLValueString($xmlmsg, "text"), 
            GetSQLValueString($name, "text"), 
            GetSQLValueString($ordid, "text"));
    $paid = mysql_query($query_paid, $tams);

    $query_paid = sprintf("UPDATE prospective SET formpayment = %s, appbatch = %s WHERE jambregid=%s", 
            GetSQLValueString("Yes", "text"), 
            GetSQLValueString(getSessionValue('appbatch'), "int"), 
            GetSQLValueString(getSessionValue('uid'), "text"));
    $paid1 = mysql_query($query_paid, $tams);

    if($paid && $paid1) { 
        mysql_query('COMMIT', $tams);    
    }else {
        mysql_query('ROLLBACK', $tams); 
    }
   
}

$query = sprintf("SELECT * FROM prospective WHERE jambregid=%s", GetSQLValueString(getSessionValue('uid'), "text"));
$rsResult =  mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);

$query_paid= sprintf("SELECT * FROM appfee_transactions WHERE ordid=%s",$ordid);
$paid= mysql_query($query_paid, $tams) or die(mysql_error());
$row_paid = mysql_fetch_assoc($paid);

if($row_result['email']!=null){
    $ref = $row_paid['reference'];
    $name = getSessionValue('lname').', '.getSessionValue('fname'). ' '.getSessionValue('mname');
    $domain = str_replace('http://www.', '', $school_url);
    $to = $row_result['email'];
    $subject = "{$school_short_name} Portal:  APPROVED POST UTME/DE APPLICATION PAYMENT";          
    $from = "noreply@{$domain}";
    $headers = "From: {$school_short_name} ".$from; 
    $body ="Good day {$name},\nYou have successfully paid for your POST UTME/DE Application fee\n==Payment Details==\n"
                . "Card PAN: {$pan}\nCard Holder: {$name}\nUnique ID: {$ordid}"
                . "\nTransaction Date & Time: {$date}\nTransaction Reference: {$ref}\nAmount: {$_SESSION['amt']}"
                . "\nCurrency: NGN (Nigerian Naira)"
                . "\nAuthorization Code: {$_SESSION['approvalcode']}"
                . "\nMerchant Name: {$university}"
                . "\nMerchant Url: {$school_url}"
                . "\nDescription: Tuition Fee\n{$university}, "
                . "{$university_address} \n{$school_url}";
    mail($to,$subject,$body,$headers);
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
                                        Payment Notification
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <table>
                                            <tr>
                                                <td align="center" style="font-weight: bolder"><p> <?php echo $paydesc ?> RECEIPT </p></td>
                                            </tr>
                                            <tr >
                                                <td align="center">
                                                    <table class="table table-bordered table-condensed table-striped">
                                                        <tr>
                                                            <th width="170">Full Name :</th>
                                                            <td><?php echo $row_result['lname'] . ' ' . $row_result['fname'] . ' ' . $row_result['mname'] ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>UTME Reg. No. :</th>
                                                            <td><?php echo $row_result['jambregid'] ?></td> 
                                                        </tr>
                                                        <tr>
                                                            <th>Payment Desc.:</th>
                                                            <td><?php echo $paydesc ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Response Desc. :</th>
                                                            <th><?php echo $res; ?></th>
                                                        </tr>
                                                        <tr>
                                                            <th>Amount :</th>
                                                            <th><?php echo $amt ?></th>
                                                        </tr>
                                                        <tr>
                                                            <th>Transaction Reference :</th>
                                                            <th><?php echo $row_paid['reference']; ?></th>
                                                        </tr>
                                                        <tr>
                                                            <th>Date & Time :</th>
                                                            <th><?php echo $date ?></th>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2">
                                                                <a target="_blank" href="receipt.php?no=<?php echo $ordid ?>">
                                                                    <button>Print Receipt</button>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2">
                                                                <p style="color: blue; font-weight: bold">
                                                                    Kindly note your Transaction Reference number as it will be used to track dispute.
                                                                    A copy of this receipt has been sent to the email address you provided.
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td> 
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
        </div>
    </body>
</html>