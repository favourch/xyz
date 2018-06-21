<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');

$paydesc = "O'LEVEL VERIFICATION FEE";

$query = "";


if(getSessionValue('accttype') == 'pros'){
    $query = sprintf("SELECT * "
                    . "FROM prospective "
                    . "WHERE jambregid = %s", 
                    GetSQLValueString(getSessionValue('uid'), "text"));
    
}else{
    $query = sprintf("SELECT * "
                    . "FROM student "
                    . "WHERE stdid = %s", 
                    GetSQLValueString(getSessionValue('uid'), "text"));
}
$rsResult = mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);

$prog = $row_result = $row_result['progid'];
if(getSessionValue('accttype') == 'pros'){
    $prog = $row_result['progid1'];
}


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
    
    if(($rawAmount/100) > 500){
        $sitting = 2;
    }else{
        $sitting = 1;
    }
       

    $query_paid = sprintf("UPDATE olevelverifee_transactions 
                        SET status = %s, amt = %s, 
                        resp_code = %s, resp_desc = %s, 
                        auth_code = %s, pan = %s,
                        xml = %s, name = %s  
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
    $paid = mysql_query($query_paid, $tams) or die(mysql_error()) ;
    
    
    $confVeriSQL = sprintf("SELECT * "
                    . "FROM OLVL_verification "
                    . "WHERE stdid = %s ",
                    GetSQLValueString(getSessionValue('uid'), 'text'));
    $confVeriRS = mysql_query($confVeriSQL, $tams);
    $confVeriCount = mysql_num_rows($confVeriRS);
    
    if($confVeriCount > 0){
        
        
        $query = sprintf("INSERT INTO olevel_veri_data "
                            . "(stdid, usertype, sesid, progid) "
                            . "VALUES(%s, %s, %s, %s)", 
                            GetSQLValueString(getSessionValue('uid'), 'text'), 
                            GetSQLValueString(getSessionValue('accttype'), 'text'),
                            GetSQLValueString(getSessionValue('sesid'), 'text'),
                            GetSQLValueString($prog, 'int'),
                            GetSQLValueString($form_fields['level'], 'text'));
        $olevel = mysql_query($query, $tams) or die(mysql_error());
        
    }else{
        
        
        
        $verificationSQL = sprintf("INSERT INTO verification "
                            . "(stdid, sesid, type, ver_code, olevel_sitting ) "
                            . "VALUES(%s, %s, %s, UUID(),' %s )", 
                            GetSQLValueString(getSessionValue('uid'), 'text'),
                            GetSQLValueString(getSessionValue('admid'), 'int'),
                            GetSQLValueString(getSessionValue('accttype'), 'text'),
                            GetSQLValueString($sitting, "int"));
        $verificationRS = mysql_query($verificationSQL, $tams) or die(mysql_error());
        
        for($i = 0; $i < $sitting; $i++){
            $sit = 'Sitting '.$i++;
            $query = sprintf("INSERT INTO olevel_veri_data "
                            . "(stdid, usertype, sesid, progid, label) "
                            . "VALUES(%s, %s, %s, %s, %s)", 
                            GetSQLValueString(getSessionValue('uid'), 'text'), 
                            GetSQLValueString(getSessionValue('accttype'), 'text'),
                            GetSQLValueString(getSessionValue('sesid'), 'text'),
                            GetSQLValueString($prog, 'int'),
                            GetSQLValueString($form_fields['level'], 'text'),
                            GetSQLValueString($sit, 'text'));
            $olevel = mysql_query($query, $tams) or die(mysql_error());
        }
    }
    
    
}


$query_paid = sprintf("SELECT * "
                    . "FROM olevelverifee_transactions "
                    . "WHERE ordid = %s", GetSQLValueString($ordid, "text"));
$paid = mysql_query($query_paid, $tams) or die(mysql_error());
$row_paid = mysql_fetch_assoc($paid);


if ($row_result['email'] != null) {

    $to = $row_result['email'];
    $subject = "TASUED Portal: OLEVEL VERIFICATION Payment";
    $from = "noreply@tasued.edu.ng";
    $headers = "From: TASUED " . $from;
    $body = "Good day {$portalName},\nYou have successfully paid for your OLEVEL VERIFICATION fee\n==Payment Details==\n"
            . "Card PAN: {$pan}\nCard Holder: {$name}\nUnique ID: {$ordid}"
            . "\nTransaction Date & Time: {$date}\nTransaction Reference: {$ref}\nAmount: {$_SESSION['amt']}"
            . "\nAuthorization Code: {$_SESSION['approvalcode']}"
            . "\nOlevel Verification Fee\nTai Solarin University of Education, "
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
                                            <td>
                                                <a target="_blank" href="receipt.php?no=<?php echo $ordid ?>">
                                                    <button>Print Receipt</button>
                                                </a>
                                            </td>
                                            <td>
                                                <a target="_blank" href="../index.php">
                                                    <button>Submit WAEC/NECO Result Card Details</button>
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