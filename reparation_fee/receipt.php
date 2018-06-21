<?php 
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}



require_once('../path.php');

$auth_users = "10";
check_auth($auth_users, $site_root);



$order_no = "-1";
if(isset($_GET['no'])){
    $order_no = $_GET['no'];
}

mysql_select_db($database_tams, $tams);

$query_history = sprintf('SELECT matric_no, can_name, ordid, status, reference, amt, date_time, pcount '
                        . "FROM reparation_transactions "
                        . "WHERE matric_no = %s "
                        . "AND status = 'APPROVED' "
                        . "AND ordid = %s ", 
                        GetSQLValueString(getSessionValue('uid'), "text"),
                        GetSQLValueString($order_no, "text"));
$history = mysql_query($query_history, $tams) or die(mysql_error());
$row_history = mysql_fetch_assoc($history);
$totalRows_history = mysql_num_rows($history);
 


include("../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4','','',10,10,65,15,10,5); 
$stylesheet = file_get_contents('../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
                <tr>
                    <td width="90%" align="center"><img src="../img/logo/school-logo.png" width="90px" height="90px" /></td>
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
    $html ='<p style="text-align:center; font-size: 20pt; margin-bottom: 30px"><strong>REPARATION PAYMENT RECEIPT</strong></p>
    <div style="text-align:center; width:100%; font-size: 20pt">
        <table align="center" style="width: 60%;" class="table table-bordered table-striped">
            <tr>
         
                
                <td width="120">Name:</td>
                <td width="400">'.$row_history['can_name'].'</td>
                <td rowspan="6">
                    <barcode code="'
                                                    .$row_history['matric_no'].' '
                                                    .$row_history['can_name'].' '
                                                    .$row_history['amt'].'  '
                                                    .'Reparation fee'. '  '
                                                    .$row_history['date_time'].'  '
                                                    .'" type="QR" class="barcode" size="1.5" error="M" />
                </td>
            </tr>

            <tr>
                <td>Reg No:</td>
                <td>'.$row_history['matric_no'].'</td>
            </tr>

           <tr>
            
                <td>Description:</td>
                <td>REPARATION PAYMENT RECEIPT</td>
                
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
        <div>
            <img src="../img/payment/bursary-stamp.jpg" />
            <div style="font-size: 11px">ODUSANYA I.O</div>
            <div style="font-size: 9px">'.$row_history['date_time'].'</div>
        </div>
    </div>';
    }


$mpdf->WriteHTML($html);
$printCountSQL = sprintf("UPDATE reparation_transactions SET pcount = pcount + 1 "
                        . "WHERE matric_no = %s "
                        . "AND status = 'APPROVED' "
                        . "AND ordid = %s ",
                        GetSQLValueString($_SESSION['MM_Username'], "text"), 
                        GetSQLValueString($row_history['ordid'], "int")); 
$printCountRS = mysql_query($printCountSQL, $tams) or die(mysql_error());
$mpdf->Output('receipt.pdf', 'I');
exit();