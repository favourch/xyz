<?php 
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}



require_once('../../../../path.php');

$auth_users = "1, 20,23";
check_auth($auth_users, $site_root);

$order_no = "-1";
if(isset($_GET['no'])){
    $order_no = $_GET['no'];
}

$stdid = "-1";
if(isset($_GET['stdid'])){
    $stdid = $_GET['stdid'];
}


$query_history = sprintf('SELECT p.fname, p.lname, p.mname, at.can_no, at.can_name, at.ordid, '
                        . 'at.status, at.reference, at.amt, at.date_time, ad.typename '
                        . "FROM appfee_transactions at "
                        . "LEFT JOIN prospective p ON p.jambregid = at.can_no "
                        . "LEFT JOIN admissions a ON p.admid = a.admid "
                        . "LEFT JOIN admission_type ad ON ad.typeid = a.typeid "
                        . "WHERE at.can_no = %s "
                        . "AND at.status = 'APPROVED' "
                        . "AND at.ordid = %s ", 
        GetSQLValueString($stdid, "text"),
        GetSQLValueString($order_no, "text"));
$history = mysql_query($query_history, $tams) or die(mysql_error());
$row_history = mysql_fetch_assoc($history);
$totalRows_history = mysql_num_rows($history);


include("../../../../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4','','',10,10,50,15,5,5); 
$stylesheet = file_get_contents('../../../../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="15%" align="left"><img src="../../../../img/logo/school-logo.png" width="100px" /></td>
<td width="85%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 25pt">'.$university.'</h2>
<h5 style="font-size: 9pt">'.$university_address.'</h5></div>
</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);

$html = '<p style="border-bottom: 1px solid #999999; font-size: 9pt;">There is no known order with this Order No. '.$order_no.'!</p>';

if($totalRows_history > 0) {
    $html ='<p style="text-align:center; font-size: 20pt; margin-bottom: 30px"><strong>'.$row_history['typename'].' APPLICATION RECEIPT</strong></p>
    <div style="text-align:center; width:100%; font-size: 20pt">
        <table align="center" style="width: 60%;" class="table table-bordered table-striped">
            <tr>
                <td width="150">Name:</td>
                <td width="400">'.$row_history['lname'].', '.$row_history['fname'].' '.$row_history['mname'].'</td>
                <td rowspan="4">
                    <barcode code="'
                                                    .$row_history['can_no'].' '
                                                    .$row_history['lname'].', '.$row_history['fname'].' '.$row_history['mname'].' '
                                                    .$row_history['amt'].'  '
                                                    .'Application Fees'. '  '
                                                    .$row_history['date_time'].'  '
                                                    .'" type="QR" class="barcode" size="1.5" error="M" />
                </td>
            </tr>

            <tr>
                <td>UTME Reg No :</td>
                <td>'.$row_history['can_no'].'</td>
            </tr>
            <tr>
                <td>Description:</td>
                <td>POST UTME/DE APPLICATION PAYMENT</td>
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
        <p style="font-size: 30px; text-align:center">Bursary Reprint</p>
    </div>';
}

$mpdf->WriteHTML($html);
$mpdf->Output('receipt.pdf', 'I');

exit;
?>