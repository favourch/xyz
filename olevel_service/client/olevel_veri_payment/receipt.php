<?php 
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}



require_once('../../../path.php');

$auth_users = "10,11";
check_auth($auth_users, $site_root);



$order_no = "-1";
if(isset($_GET['no'])){
    $order_no = $_GET['no'];
}

mysql_select_db($database_tams, $tams);

$query_history = sprintf('SELECT p.fname, p.lname,p.mname, can_no, can_name, ordid, status, reference, amt, date_time '
                        . "FROM olevelverifee_transactions o JOIN prospective p ON o.can_no = p.jambregid "
                        . "WHERE can_no = %s "
                        . "AND status = 'APPROVED' "
                        . "AND ordid = %s ", 
                        GetSQLValueString(getSessionValue('uid'), "text"),
                        GetSQLValueString($order_no, "text"));
$history = mysql_query($query_history, $tams) or die(mysql_error());
$row_history = mysql_fetch_assoc($history);
$totalRows_history = mysql_num_rows($history);

//$ch = curl_init();
//$url="https://cipg.diamondbank.com/cipg/MerchantServices/UpayTransactionStatus.ashx?MERCHANT_ID=00456&ORDER_ID={$row_history['can_no']}";
//curl_setopt($ch, CURLOPT_URL, $url);
//
//// Set so curl_exec returns the result instead of outputting it.
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//
//// Get the response and close the channel.
//$response = curl_exec($ch);
//$response=(string)$response;
//
//list($id,$Mid, $canNoo, $stat, $statcode, $amt, $mydate, $tranref, $payref, $paygw,$responseCode,$responseDes,$currCode) = explode("  ", $response);



include("../../../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4','','',10,10,65,15,10,5); 
$stylesheet = file_get_contents('../../../../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
                <tr>
                    <td width="90%" align="center"><img src="../../../../img/logo/school-logo.png" width="90px" height="90px" /></td>
                </tr>
                <tr>
                    <td width="90%" align="center">
                        <div style="font-weight: bold;">
                            <h2 style="font-size: 25pt">'.$university.'</h2>
                            <h5 style="font-size: 9pt">'.$university_address.'</h5><br/>
                        </div>
                    </td>
                </tr>
            </table>';

$mpdf->SetHTMLHeader($header);

$html = '<p style="border-bottom: 1px solid #999999; font-size: 9pt;">There is no known order with this Order No. '.$order_no.'!</p>';

if($totalRows_history > 0) {
    $html ='<p style="text-align:center; font-size: 20pt; margin-bottom: 30px"><strong>O`LEVEL VERIFICATION PAYMENT RECEIPT</strong></p>
    <div style="text-align:center; width:100%; font-size: 20pt">
        <table align="center" style="width: 60%;" class="table table-bordered table-striped">
            <tr>
         
                
                <td width="120">Name:</td>
                <td width="400">'.$row_history['lname'].' '.$row_history['fname'].' '.$row_history['mname'].'</td>
                <td rowspan="6">
                    <barcode code="'
                                                    .$row_history['can_no'].' '
                                                    .$row_history['can_name'].' '
                                                    .$row_history['amt'].'  '
                                                    .'Olevel Verification fee'. '  '
                                                    .$row_history['date_time'].'  '
                                                    .'" type="QR" class="barcode" size="1.5" error="M" />
                </td>
            </tr>

            <tr>
                <td>Reg No:</td>
                <td>'.$row_history['can_no'].'</td>
            </tr>

           <tr>
            
                <td>Description:</td>
                <td>O`LEVEL VERIFICATION PAYMENT RECEIPT</td>
                
            </tr>

            <tr>
                <td>Transaction Reference:</td>
                <td>'.$row_history['reference'].'</td>
                
            </tr>

            <tr>
                <td>Date:</td>
                <td>'.$row_history['date_time'].'</td>
                
            </tr>

            <tr>
                <td>Amount:</td>
                <td>'.$row_history['amt'].'</td>
                
            </tr>
            
        </table>
<p><h6> Students are to note that the payment for the O\'Level Verification is Non-Refundable and Non-Transferable. Each payment is tied to the submission of a WAEC/NECO Result Scratch Card details. The University ICT Centre will not be held responsible for submission of wrong card details. Thanks.</h6> </p>
    </div>
    
    <div style="text-align:center" >
            <img src="../../../img/payment/bursary-stamp.jpg" />
            <div style="font-size: 11px">ODUSANYA I.O</div>
            <div style="font-size: 9px">'.$row_history['date_time'].'</div>
        </div>';
}

$mpdf->WriteHTML($html);
$mpdf->Output('receipt.pdf', 'I');

exit();
?>