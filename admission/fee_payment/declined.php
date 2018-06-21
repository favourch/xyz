<?php 
if (!isset($_SESSION)) {
  session_start();
}

require_once('../../path.php');

$auth_users = "11";
check_auth($auth_users, $site_root);

$query = sprintf("SELECT * FROM prospective WHERE jambregid=%s", GetSQLValueString(getSessionValue('uid'), "text"));
$rsResult =  mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);

$paydesc = "UTME/DE SCHOOL FEE";

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
                        
                $update= sprintf("UPDATE  schfee_transactions SET resp_code='".$rc."',resp_desc = '".$res."', auth_code='".$ac."', pan='".$pan."', status = 'DECLINED', name='".$name."', date_time='".$dt."', xml = '".$xmlmsg."' WHERE  ordid='".$ordid."'");
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

$query_paid= sprintf("SELECT * FROM schfee_transactions WHERE ordid=%s",$ordid);
$paid= mysql_query($query_paid, $tams) or die(mysql_error());
$row_paid = mysql_fetch_assoc($paid);

//if($rsResult['email']!=null){
//    
//    $to = $rsResult['email'];
//    $subject = "TASUED Portal: POST UTME/DE APPLICATION Payment";          
//    $from = "noreply@tasued.edu.ng";
//    $headers = "From: TASUED ".$from; 
//    $body ="Good day {$portalName},\nYou have successfully paid form your POST UTME/DE Application fee\n==Payment Details==\n"
//                . "Card PAN: {$pan}\nCard Holder: {$name}\nUnique ID: {$ordid}"
//                . "\nTransaction Date & Time: {$date}\nTransaction Reference: {$ref}\nAmount: {$_SESSION['amt']}"
//                . "\nAuthorization Code: {$_SESSION['approvalcode']}"
//                . "\nTuition Fee\nTai Solarin University of Education, "
//                . "Ijebu Ode, Ogun State, Nigeria \nWebsite: www.tasued.edu.ng";
//    mail($to,$subject,$body,$headers);
//
//}
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
                                                <th><?php echo $ref ?></th>
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