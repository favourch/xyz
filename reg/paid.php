<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');




$paydesc = "REPARATION FEE ";

mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * FROM student WHERE stdid=%s", GetSQLValueString(getSessionValue('uid'), "text"));
$rsResult = mysql_query($query, $tams) or die(mysql_error());
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

    
    mysql_select_db($database_tams, $tams);
    mysql_query('BEGIN', $tams);

    $query_paid = sprintf("UPDATE reparation_transactions "
                        . "SET status = %s, amt = %s, resp_code = %s, "
                        . "resp_desc = %s, auth_code = %s, pan = %s,"
                        . " xml = %s, name = %s  "
                        . "WHERE ordid=%s", 
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

    mysql_query('COMMIT', $tams);
}

mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * "
                . "FROM student "
                . "WHERE stdid = %s", 
                GetSQLValueString(getSessionValue('uid'), "text"));
$rsResult = mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);


mysql_select_db($database_tams, $tams);
$query_paid = sprintf("SELECT * "
                    . "FROM reparation_transactions "
                    . "WHERE ordid = %s", $ordid);
$paid = mysql_query($query_paid, $tams) or die(mysql_error());
$row_paid = mysql_fetch_assoc($paid);

if ($rsResult['email'] != null) {

    $to = $rsResult['email'];
    $subject = "TASUED Portal: REPARATION FEE Payment";
    $from = "noreply@tasued.edu.ng";
    $headers = "From: TASUED " . $from;
    $body = "Good day {$portalName},\nYou have successfully paid for your REPARATION fee\n==Payment Details==\n"
            . "Card PAN: {$pan}\n Card Holder: {$name}\nUnique ID: {$ordid}"
            . "\nTransaction Date & Time: {$date} \nTransaction Reference: {$ref}\nAmount: {$_SESSION['amt']}"
            . "\nAuthorization Code: {$_SESSION['approvalcode']}"
            . "\nTai Solarin University of Education, "
            . "Ijebu Ode, Ogun State, Nigeria \nWebsite: www.tasued.edu.ng";
    mail($to, $subject, $body, $headers);
}




if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root . '/prospective');
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
                                        Fee Payment Notification
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <table class="table table-bordered table-condensed table-striped" style="width: 90%; alignment-adjust: central">
                                        <tr>
                                            <th width="170">Full Name :</th>
                                            <td><?php echo $row_result['lname'] . ' ' . $row_result['fname'] . ' ' . $row_result['mname'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Reg. No. :</th>
                                            <td><?php echo $row_result['stdid'] ?></td> 
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
                                                <p class="alert">
                                                    Kindly note your Transaction Reference number as it will be used to track dispute.
                                                    A copy of this receipt has been sent to the email address you provided.
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
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