<?php 
if (!isset($_SESSION)) {
  session_start();
}

require_once('../path.php');

$paydesc = "SCHOOL FEES PAYMENT";

$query = sprintf("SELECT * "
                    . "FROM student "
                    . "WHERE stdid=%s", 
                    GetSQLValueString(getSessionValue('MM_Username'), "text"));
$rsResult =  mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);

// unified payment code

if(isset($_POST["xmlmsg"])) {
    $xml = simplexml_load_string($_POST["xmlmsg"]);			

    foreach($xml->children() as $child) {	
        if ($child->getName() == "ResponseDescription") {
            $_SESSION['response'] = $child;
            $res = $child;                         
        }elseif ($child->getName() == "PurchaseAmountScr") {
            $_SESSION['amt'] = "NGN".$child;
            
            $amt=$_SESSION['amt'];                        
        }
        if ($child->getName() == "ApprovalCode")
            $_SESSION['approvalcode'] = $child;
        
        if ($child->getName() == "OrderID")
            $ordid = $child;

        if ($child->getName() == "PAN")
            $pan = $child;
        
        if ($child->getName() == "TranDateTime")
            $date=$child;
        
        if ($child->getName() == "OrderStatus")
            $status=$child;
        
        if ($child->getName() == "Brand")
            $brand=$child;

        if ($child->getName() == "PurchaseAmount"){
            $rawAmount=$child;
            $charges = 0.005 ;
        }
        if ($child->getName() == "Name")
            $name=$child;

        if ($child->getName() == "ResponseCode")
            $rc = $child;

        if ($child->getName() == "ApprovalCode")
            $ac = $child;

        if ($child->getName() == "TranDateTime")
            $dt = $child;

//			$portalName=$row_rs_personal["Last_Name"].', '.$row_rs_personal["First_Name"].' '.$row_rs_personal["Other_Names"];		
//			$canNo=$row_rs_personal['Candidate_no'];
    }//end for loop
    
    $year=date('Y');
    $xmlmsg = $_POST['xmlmsg'];

    $query_paid= sprintf("UPDATE schfee_transactions
        SET status = %s, amt = %s, resp_code = %s, resp_desc = %s, auth_code = %s, pan = %s, xml = %s, name = %s, trans_date = %s, charges = %s 
        WHERE ordid=%s", 
        GetSQLValueString("APPROVED", "text"), 
        GetSQLValueString($amt, "text"),  
        GetSQLValueString($rc, "text"),  
        GetSQLValueString($res, "text"),  
        GetSQLValueString($ac, "text"),  
        GetSQLValueString($pan, "text"),  
        GetSQLValueString($xmlmsg, "text"),   
        GetSQLValueString($name, "text"),
        GetSQLValueString($dt, "text"), 
        GetSQLValueString($charges, "double"), 
        GetSQLValueString($ordid, "text"));
    $paid = mysql_query($query_paid, $tams);

}

if($rsResult['email'] != NULL){
    
    $to = $rsResult['email'];
    $subject = "TASUED Portal: School Fees Payment";          
    $from = "noreply@tasued.edu.ng";
    $headers = "From: TASUED ".$from; 
    $body ="Good day {$portalName},\nYou have successfully paid your school fee\n==Payment Details==\n"
                . "Card PAN: {$pan}\nCard Holder: {$name}\nUnique ID: {$ordid}"
                . "\nTransaction Date & Time: {$date}\nTransaction Reference: {$ref}\nAmount: {$_SESSION['amt']}"
                . "\nAuthorization Code: {$_SESSION['approvalcode']}"
                . "\nTuition Fee\nTai Solarin University of Education, "
                . "Ijebu Ode, Ogun State, Nigeria \nWebsite: www.tasued.edu.ng";
    mail($to,$subject,$body,$headers);

}

$query_paid= sprintf("SELECT * FROM schfee_transactions WHERE ordid=%s",$ordid);
$paid= mysql_query($query_paid, $tams) or die(mysql_error());
$row_paid = mysql_fetch_assoc($paid);
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
                                    <div class="form-horizontal form-bordered">
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Full Name</label>
                                            <div class="controls">
                                                <?php echo $row_result['lname'].' '.$row_result['fname'].' '.$row_result['mname']?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Matric No.</label>
                                            <div class="controls">
                                                <?php echo $row_result['stdid']?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Response Desc.</label>
                                            <div class="controls">
                                                <?php echo $res;?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Transaction Reference .</label>
                                            <div class="controls">
                                                <?php echo $row_paid['reference'];?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Date & Time.</label>
                                            <div class="controls">
                                                <?php echo $row_paid['date_time']?>
                                            </div>
                                        </div>
                                        <div class="form-actions">
                                            <a class="btn"target="_blank" href="receipt.php?no=<?php echo $ordid?>">
                                                <i class="icon-print">Print Receipt</i>
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
        </div>
    </body>
</html>