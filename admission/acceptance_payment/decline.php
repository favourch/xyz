<?php 


if (!isset($_SESSION)) {
  session_start();
}



require_once('../../path.php');


if (!function_exists("GetSQLValueString")) {
    function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = ""){
        if (PHP_VERSION < 6) {
          $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
        }

        $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

        switch ($theType) {
            case "text":
                $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
                break;    
            case "long":
            case "int":
                $theValue = ($theValue != "") ? intval($theValue) : "NULL";
                break;
            case "double":
                $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
                break;
            case "date":
                $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
                break;
            case "defined":
                $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
                break;
        }
        return $theValue;
    }
}
mysql_select_db($database_tams,$tams);
$query = sprintf("SELECT * FROM prospective WHERE jambregid=%s", GetSQLValueString(getSessionValue('uid'), "text"));
$rsResult =  mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);

$paydesc = "POST UTME/DE ACCEPTANCE FEE";

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
                        
                $update= sprintf("UPDATE  accfee_transactions SET amt='0',resp_code='".$rc."',resp_desc = '".$res."', auth_code='".$ac."', pan='".$pan."', status = 'DECLINED', name='".$name."', date_time='".$dt."', xml = '".$xmlmsg."' WHERE  ordid='".$ordid."'");
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


mysql_select_db($database_tams, $tams);
$query_paid= sprintf("SELECT * FROM accfee_transactions WHERE ordid=%s",$ordid);
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


	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root.'/prospective' );   
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php  ?>
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="../../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="../../css/menulink.css" rel="stylesheet" type="text/css" />
<link href="../../css/footer.css" rel="stylesheet" type="text/css" />
<link href="../../css/sidemenu.css" rel="stylesheet" type="text/css" />
</head>

<body data-layout-sidebar="fixed" data-layout-topbar="fixed">
<div class="container">
  <div class="header">
    <!-- end .header -->
</div>
  <div class="topmenu">
<?php include '../../include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include '../../include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
      Payment Notification
<!--    <table width="600">
      <tr>
          <td> InstanceBeginEditable name="pagetitle"  <img src="img/visa.jpg" width="70px" height="30px" />  Visa Instruction  InstanceEndEditable </td>
      </tr>
    </table>-->
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
        <table width="690" >
            <tr>
                <td align="center" style="font-weight: bolder"><p> <?php echo $paydesc ?> NOTIFICATION </p></td>
            </tr>
            <tr >
                <td align="center">
                    <table class="table table-bordered table-condensed table-striped" style="width: 90%; alignment-adjust: central">
                        <tr>
                            <th width="170">Full Name :</th>
                            <td><?php echo $custName?></td>
                        </tr>
                        <tr>
                            <th>UTME Reg. No. :</th>
                            <td><?php echo $canNo?></td> 
                        </tr>
                        <tr>
                            <th>Payment Desc.:</th>
                            <td><?php echo $paydesc?></td>
                        </tr>
                        <tr>
                            <th>Response Desc. :</th>
                            <th style="color: red"><?php echo "Your transaction was unsuccessful ". $desc;?></th>
                        </tr>
                        <tr>
                            <th>Response Code :</th>
                            <th><?php echo $rc?></th>
                        </tr>
                        <tr>
                            <th>Transaction Reference :</th>
                            <th><?php echo $row_paid['reference']?></th>
                        </tr>
                        
                        <tr>
                            <td colspan="2">
                                <p style="color: blue; font-weight: bold">
                                    Kindly note your Transaction Reference number as it will be used to track dispute.
                                    
                                </p>
                            </td>
                        </tr>
                    </table>
                </td> 
            </tr>
        </table>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>