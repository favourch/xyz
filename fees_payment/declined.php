<?php 


if (!isset($_SESSION)) {
  session_start();
}



require_once('../path.php');



mysql_select_db($database_tams,$tams);

$query = sprintf("SELECT * FROM student WHERE stdid=%s", GetSQLValueString(getSessionValue('MM_Username'), "text"));

$rsResult =  mysql_query($query, $tams) or die(mysql_error());

$row_result = mysql_fetch_assoc($rsResult);



$paydesc = "SCHOOL FEES";



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

			$canNo=$row_result['stdid'];

			

			$year=date('Y');

  			 }//end for loop

			$xmlmsg = $_POST['xmlmsg'];

                //UPDATE DATABASE
                $update = sprintf("UPDATE schfee_transactions "

                        . "SET resp_code='".$rc."', resp_desc = '".$res."', auth_code='".$ac."', "

                        . "pan='".$pan."', status = 'DECLINED', name='".$name."', date_time='".$dt."', "

                        . "xml = '".$xmlmsg."' WHERE ordid='".$ordid."'");

                $rs_update = mysql_query($update, $tams) or die(mysql_error());

                 

	}

        

$desc='';

  

switch ($rc) {

    case "058":

        $desc = "Invalid Card Status";

        break;

    case "055":

        $desc = "Invalid Transaction, Card is either Not Enrolled or Element is missing";

        break;

    case "095":

        $desc = "Invalid Transaction, Card is either Not Enrolled or Element is missing";

        break;

     case "211":

        $desc = "Wrong card details supplied";

        break;

    default:

        break;

} 





mysql_select_db($database_tams, $tams);

$query_paid = sprintf("SELECT * FROM schfee_transactions WHERE ordid=%s",$ordid);

$paid = mysql_query($query_paid, $tams) or die(mysql_error());

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
                                                <?php echo $custName?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Matric No.</label>
                                            <div class="controls">
                                                <?php echo $canNo?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Payment Description</label>
                                            <div class="controls">
                                                <div class="alert alert-error">Your transaction was unsuccessful!</div>
                                            </div>
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