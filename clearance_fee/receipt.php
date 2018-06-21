<?php 
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

require_once('../path.php');

$auth_users = "10";
check_auth($auth_users, $site_root);


$sesid = getSessionValue('sesid');
$stdid = getSessionValue('uid');
$pid = getSessionValue('pid');

$order_no = "-1";
if(isset($_GET['no'])){
    $order_no = $_GET['no'];
}

$query_history = sprintf('SELECT matric_no, can_name, ordid, status, reference, amt, date_time '
                        . "FROM clearance_transactions "
                        . "WHERE matric_no = %s "
                        . "AND status = 'APPROVED' "
                        . "AND ordid = %s ", 
                        GetSQLValueString($stdid, "text"),
                        GetSQLValueString($order_no, "text"));
$history = mysql_query($query_history, $tams) or die(mysql_error());
$row_history = mysql_fetch_assoc($history);
$totalRows_history = mysql_num_rows($history);

$query_last_reg = sprintf("SELECT s.sesid, s.sesname "
                            . "FROM registration r "
                            . "JOIN programme p ON p.duration = r.level "
                            . "JOIN session s ON s.sesid = r.sesid "
                            . "WHERE r.stdid = %s "
                            . "AND r.status = 'Registered' "
                            . "AND p.progid = %s "
                            . "ORDER BY sesid DESC",
                            GetSQLValueString($stdid, "text"),
                            GetSQLValueString($pid, "int"));
$last_reg = mysql_query($query_last_reg, $tams) or die(mysql_error());
$row_last_reg = mysql_fetch_assoc($last_reg);
$totalRows_last_reg = mysql_num_rows($last_reg);

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
    $html ='<p style="text-align:center; font-size: 20pt; margin-bottom: 30px"><strong>FIINAL YEAR CLEARANCE PAYMENT RECEIPT</strong></p>
    <div style="text-align:center; width:100%; font-size: 20pt">
        <table align="center" style="width: 60%;" class="table table-bordered table-striped">
            <tr>
         
                
                <td width="120">Name:</td>
                <td width="400">'.$row_history['can_name'].'</td>
                <td rowspan="5">
                    <barcode code="'
                                                    .$row_history['matric_no'].' '
                                                    .$row_history['can_name'].' '
                                                    .$row_history['amt'].'  '
                                                    .'Final Year Clearance'. '  '
                                                    .$row_history['date_time'].'  '
                                                    .$row_last_reg['sesname'].'  '
                                                    .'" type="QR" class="barcode" size="1.5" error="M" />
                </td>
            </tr>

            <tr>
                <td>Reg No:</td>
                <td>'.$row_history['matric_no'].'</td>
            </tr>
            <tr>
                <td>Clearance Session:</td>
                <td>'.$row_last_reg['sesname'].'</td>
            </tr>

           <tr>
            
                <td>Description:</td>
                <td>FINAL YEAR CLEARANCE PAYMENT RECEIPT</td>
                
            </tr>

            <tr>
                <td>Transaction Reference:</td>
                <td>'.$row_history['reference'].'</td>
                
            </tr>

            <tr>
                <td>Date:</td>
                <td>'.$row_history['date_time'].'</td>
                <td></td>
            </tr>

            <tr>
                <td>Amount:</td>
                <td>'.$row_history['amt'].'</td>
                <td></td>
            </tr>
            
        </table>
        <div style="text-align:center; width:100%;" >
            <img src="../img/payment/bursary-stamp.jpg" />
            <div style="font-size: 11px">ODUSANYA I.O</div>
            <div style="font-size: 9px">'.$row_history['date_time'].'</div>
        </div>
<p><h6> Students are to note that the payment for the Final year clearance is Non-Refundable and Non-Transferable. </h6> </p>
    </div>';
}

$mpdf->WriteHTML($html);
$mpdf->Output('receipt.pdf', 'I');

exit();
?>