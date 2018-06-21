<?php

if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$paydesc = "REGISTRATION FEE ";

//mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * FROM prospective WHERE jambregid = %s", GetSQLValueString(getSessionValue('uid'), "text"));
$rsResult = mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);

// unified payment code


	if(isset($_POST["xmlmsg"]))
	{
		$xml = simplexml_load_string($_POST["xmlmsg"]);

			

			foreach($xml->children() as $child)
 			 {	
			 		if ($child->getName() == "ResponseDescription")
							{$_SESSION['response'] = $child;
							 $res = $child;}
					
					if ($child->getName() == "PurchaseAmountScr")
							$_SESSION['amt'] = "NGN" . $child;
                            $charges = 0.005;
                            $amt = $_SESSION['amt'];
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

					if ($child->getName() == "PurchaseAmount")
					$rawAmount=$child;
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
                    		
			        $year=date('Y');
  			 }//end for loop
  			 
			$xmlmsg = $_POST['xmlmsg'];
			
                        mysql_query('BEGIN', $tams);
                        
                        $query_paid= sprintf("UPDATE registration_transactions 
                        SET status = %s, amt = %s, resp_code = %s, resp_desc = %s, auth_code = %s, pan = %s, xml = %s, name = %s, charges = %s   
                        WHERE ordid=%s", 
                        GetSQLValueString("APPROVED", "text"), 
                        GetSQLValueString($amt, "text"),  
                        GetSQLValueString($rc, "text"),  
                        GetSQLValueString($res, "text"),  
                        GetSQLValueString($ac, "text"),  
                        GetSQLValueString($pan, "text"),  
                        GetSQLValueString($xmlmsg, "text"),   
                        GetSQLValueString($name, "text"), 
                        GetSQLValueString($charges, "double"), 
                        GetSQLValueString($ordid, "text"));
                        $paid= mysql_query($query_paid, $tams);
                        
                        $query_paid= sprintf("UPDATE prospective SET clinic_pay='yes' WHERE jambregid=%s",  GetSQLValueString(getSessionValue('uid'), "text"));
                        $paid= mysql_query($query_paid, $tams);
                        
                         mysql_query('COMMIT', $tams);
                        
	}
        

//mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * "
                . "FROM prospective "
                . "WHERE jambregid = %s", 
                GetSQLValueString(getSessionValue('uid'), "text"));
$rsResult = mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);


//mysql_select_db($database_tams, $tams);
$query_paid = sprintf("SELECT * "
                    . "FROM registration_transactions "
                    . "WHERE ordid = %s", $ordid);
$paid = mysql_query($query_paid, $tams) or die(mysql_error());
$row_paid = mysql_fetch_assoc($paid);

if ($row_result['email'] != null) {
    $ref = $row_paid['reference'];
    $name = getSessionValue('lname') . ', ' . getSessionValue('fname') . ' ' . getSessionValue('mname');
    $domain = str_replace('http://www.', '', $school_url);
    $to = $row_result['email'];
    $subject = "APPROVED REGISTRATION FEE RECEIPT";
    $body = "Good day {$name},<br/>"
            . " Your REGISTRATION FEE Payment is SUCCESSFUL <br/><br/>"
            . "==Payment Details==<br/><br/>"
            . "Card PAN: {$pan}<br/>"
            . "Card Holder: {$name}<br/>"
            . "Unique ID: {$ordid}<br/>"
            . "Transaction Date & Time: {$date}<br/>"
            . "Transaction Reference: {$ref}<br/>"
            . "Amount: {$amt} <br/>"
            . "Currency: NGN (Nigerian Naira) <br/>"
            . "Authorization Code: {$ac} <br/>"
            . "Merchant Name: {$university} <br/>"
            . "Merchant Url: {$school_url} <br/>"
            . "Description: REGISTRATION FEE <br /> You are required to proceed with your Health Information & Laboratory Tests <br />";
    
    
    $bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'> Registration Fee Payment Status</h3><p>%s</p>", $body);
    sendHtmlEmail($to, $subject, $bd, 'no-reply@tasued.edu.ng', $university);
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
                                            <td colspan="2">
                                                <a target="_blank" href="receipt.php?no=<?php echo $ordid ?>">
                                                    <button>Print Receipt</button>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <p class="alert alert-info">
                                                    Kindly note your Transaction Reference number as it will be used to track dispute.
                                                    A copy of this receipt has been sent to the email address you provided.<br /><br />
                                                    Kindly proceed with your Health Information & Laboratory Tests. 
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