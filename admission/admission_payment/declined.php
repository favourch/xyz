<?php 
if (!isset($_SESSION)) {
  session_start();
}

require_once('../../path.php');

$auth_users = "11";
check_auth($auth_users, $site_root);
$sesname = isset($_SESSION['admname'])? $_SESSION['admname']: '';

$query = sprintf("SELECT * FROM prospective WHERE jambregid = %s ", 
        GetSQLValueString(getSessionValue('uid'), "text"));
$rsResult =  mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);

$paydesc = "POST UTME/DE APPLICATION FEE";

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
							{$_SESSION['amt'] = "NGN".$child;
							$amt=$_SESSION['amt'];}
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
				
			$custName= $row_result['lname'].' '.$row_result['fname'].' '.$row_result['mname'];		
			$canNo=$row_result['jambregid'];
			
			$year=date('Y');
  			 }//end for loop
			$xmlmsg = $_POST['xmlmsg'];
			
			
			
//		$sqlget = "UPDATE tranxlog_tbl SET status = 'APPROVED', response = '$res', xml = '$xmlmsg' WHERE ref = '$ref'";
//		$result = mysql_query($sqlget);
	
                        
                //UPDATE DATABASE
                $update = sprintf("UPDATE appfee_transactions 
                        SET status = %s, amt = %s, resp_code = %s, resp_desc = %s, auth_code = %s, pan = %s, xml = %s, name = %s  
                        WHERE ordid=%s", GetSQLValueString("DECLINED", "text"), GetSQLValueString($amt, "text"), GetSQLValueString($rc, "text"), GetSQLValueString($res, "text"), GetSQLValueString($ac, "text"), GetSQLValueString($pan, "text"), GetSQLValueString($xmlmsg, "text"), GetSQLValueString($name, "text"), GetSQLValueString($ordid, "text"));
                        
                //$update= sprintf("UPDATE  appfee_transactions SET resp_code='".$rc."',resp_desc = '".$res."', auth_code='".$ac."', pan='".$pan."', status = 'DECLINED', name='".$name."', date_time='".$dt."', xml = '".$xmlmsg."' WHERE  ordid='".$ordid."'");
                $rs_update = mysql_query($update, $tams) or die(mysql_error());
                 
	}

        
$desc='';
  
switch ($rc) {
    case "058":
        $desc="Invalid Card Status";
        break;
    case "055":
        $desc="Invalid Transaction, Card is either Not Enrolled or Element is missing";
        break;
    case "095":
        $desc="Invalid Transaction, Card is either Not Enrolled or Element is missing";
        break;
     case "211":
        $desc="Wrong card details supplied";
        break;
    default:
        break;
} 

$query_paid= sprintf("SELECT * FROM appfee_transactions WHERE ordid = '%s'",$ordid);
$paid= mysql_query($query_paid, $tams) or die(mysql_error());
$row_paid = mysql_fetch_assoc($paid);
	

if ($row_result['email'] != null) {
    $ref = $row_paid['reference'];
    $name = getSessionValue('lname') . ', ' . getSessionValue('fname') . ' ' . getSessionValue('mname');
    $domain = str_replace('http://www.', '', $school_url);
    $to = $row_result['email'];
    $subject = "DECLINED POST UTME/DE APPLICATION FEE RECEIPT";
    $body = "Good day {$name},<br/>"
            . " Your POST UTME/DE Application fee for {$sesname} is DECLINED <br/><br/>"
            . "==Payment Details==<br/><br/>"
            . "Card PAN: {$pan}<br/>"
            . "Card Holder: {$name}<br/>"
            . "Unique ID: {$ordid}<br/>"
            . "Transaction Date & Time: {$date}<br/>"
            . "Transaction Reference: {$ref}<br/>"
            . "Merchant Name: {$university} <br/>"
            . "Merchant Url: {$school_url} <br/>"
            . "Description: Application Fee ";
    
    
    $bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'> %s Application Form Receipt</h3><p>%s</p>", $sesname , $body);
    $mailstatus = sendHtmlEmail($to, $subject, $bd, 'no-reply@tasued.edu.ng', $university);
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
                                        <table class="table table-bordered table-condensed table-striped" style="width: 90%; alignment-adjust: central">
                                            <tr>
                                                <th width="170">Full Name :</th>
                                                <td><?php echo $custName ?></td>
                                            </tr>
                                            <tr>
                                                <th>UTME Reg. No. :</th>
                                                <td><?php echo $canNo ?></td> 
                                            </tr>
                                            <tr>
                                                <th>Payment Desc.:</th>
                                                <td><?php echo $paydesc ?></td>
                                            </tr>
                                            <tr>
                                                <th>Response Desc. :</th>
                                                <th style="color: red"><?php echo "Your transaction was unsuccessful " . $desc; ?></th>
                                            </tr>
                                            <tr>
                                                <th>Response Code :</th>
                                                <th><?php echo $rc ?></th>
                                            </tr>
                                            <tr>
                                                <th>Transaction Reference :</th>
                                                <th><?php echo $query_paid['reference'] ?></th>
                                            </tr>
                                            <tr>
                                                <th colspan="2">
                                                     <a href="<?= SITEURL."/admission/progress_page.php?stage=app_fee_pay"?>" class="btn btn-primary">Try Again</a>
                                                </th>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <p style="color: blue; font-weight: bold">
                                                        Kindly note your Transaction Reference number as it will be used to track dispute.

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
            </div>
            <?php include INCPATH."/footer.php" ?>
        </div>
    </body>
</html>