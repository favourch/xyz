<?php

//initialize the session
if (!isset($_SESSION)) {
    session_start();
}
require_once('../../../../path.php');

$auth_users = "1, 20,23";
check_auth($auth_users, $site_root);

$stdid = -1;
if (isset($_GET['stdid']) && $_GET['stdid'] != "") {
    $stdid = $_GET['stdid'];
}

$order_no = "-1";
if (isset($_GET['no'])) {
    $order_no = $_GET['no'];
}


$query_history = sprintf("SELECT st.*, s.sesname, p.level "
                        . "FROM schfee_transactions st, payschedule p, session s "
                        . "WHERE matric_no = %s "
                        . "AND p.scheduleid = st.scheduleid "
                        . "AND st.status = 'APPROVED' "
                        . "AND st.ordid = %s "
                        . "AND p.sesid = s.sesid ", 
                        GetSQLValueString($stdid, "text"), 
                        GetSQLValueString($order_no, "int"));
$history = mysql_query($query_history, $tams) or die(mysql_error());
$row_history = mysql_fetch_assoc($history);
$totalRows_history = mysql_num_rows($history);


$query_schedule = sprintf("SELECT st.amt, st.percentPaid "
        . "FROM schfee_transactions st "
        . "WHERE matric_no = %s "
        . "AND st.status = 'APPROVED' "
        . "AND st.scheduleid = %s ", 
        GetSQLValueString($stdid, "text"), 
        GetSQLValueString($row_history['scheduleid'], "int"));
$schedule = mysql_query($query_schedule, $tams) or die(mysql_error());
$row_schedule = mysql_fetch_assoc($schedule);
$totalRows_schedule = mysql_num_rows($schedule);

$amount = 0;
$percent = 0;

for ($idx = 0; $idx < $totalRows_schedule; ++$idx, $row_schedule = mysql_fetch_assoc($schedule)) {

    $value = str_replace(',', '', strtolower($row_schedule['amt']));

    if (stripos($value, 'ngn') !== false) {
        $value = substr($value, 3);
    }

    $percent += $row_schedule['percentPaid'];
    $amount += $value;
} 

$percent = $percent > 100 ? 100 : $percent;


$row_history['percentPaid'] = $percent = 100;
$row_history['amt'] = 'NGN' . number_format($amount, 2);

$name = getSessionValue('lname') . ' ' . getSessionValue('fname') . ' ' . getSessionValue('mname');

$query_proglevel = sprintf("SELECT std.*, p.progname "
        . "FROM student std, programme p "
        . "WHERE std.stdid = %s "
        . "AND std.progid = p.progid ", GetSQLValueString($stdid, "text"));
$proglevel = mysql_query($query_proglevel, $tams) or die(mysql_error());
$row_proglevel = mysql_fetch_assoc($proglevel);
$totalRows_proglevel = mysql_num_rows($proglevel);



$university = 'TAI SOLARIN UNIVERSITY OF EDUCATION';


include("../../../../mpdf/mpdf.php");
$mpdf = new mPDF('c', 'A4', '', '', 10, 10, 65, 15, 15, 5);

$stylesheet = file_get_contents('../../../../css/mpdfstyletables.css');

$mpdf->WriteHTML($stylesheet, 1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="90%" align="center"><img src="../../../../img/logo/school-logo.png" width="100px" /></td></tr>
<tr>
<td width="90%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 21pt">' . $university . '</h2>

<h5 style="font-size: 9pt">' . $university_address . '</h5></div>
</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);

$html = '<p style="border-bottom: 1px solid #999999; font-size: 9pt;">There is no known order with this Order No. ' . $order_no . '!</p>';

if ($totalRows_history > 0) {
    $html = '<p style="text-align:center; font-size: 18pt; margin-bottom: 5px"><strong> SCHOOL FEE RECEIPT</strong></p>
    <div style="text-align:center; width:100%; font-size: 20pt">
        <table align="center" style="width: 60%;" class="table table-bordered table-striped">
            <tr>
                <td width="100">Name:</td>
                <td width="400">' . $row_history['can_name']. '</td>
                <td rowspan="6">
                    <barcode code="'
            . $row_history['matric_no'] . ' '
            . $name . ' '
            . 'SCHOOL FEE' . ' '
            . $row_history['sesname'] . ' '
            . $row_history['amt'] . ' ('
            . $row_history['percentPaid'] . ' %)'
            . '" type="QR" class="barcode" size="1.3" error="M" />
                </td>
            </tr>

            <tr>
                <td>Matric No.:</td>
                <td >' . $row_history['matric_no'] . '</td>
            </tr>
            <tr>
                <td>Programme:</td>
                <td>' . $row_proglevel['progname'] . '</td>
                
            </tr>
            <tr>
                <td>Level:</td>
                <td>' . $row_history['level'] . '00</td>
                
            </tr>
            <tr>
                <td>Description:</td>
                <td>SCHOOL FEE</td>
                
            </tr>
            <tr>
                <td>Session:</td>
                <td>' . $row_history['sesname'] . '</td>
                
            </tr>
            <tr>
                <td>Transaction Reference:</td>
                <td>' . $row_history['reference'] . '</td>
                <td rowspan="3"><p style="text-align:center; font-size: 40pt;"><strong>' . $row_history['percentPaid'] . '%</strong></p></td> 
            </tr>

            <tr>
                <td>Date:</td>
                <td>' . $row_history['date_time'] . '</td>
                
            </tr>

            <tr>
                <td>Amount:</td>
                <td> ' . $row_history['amt'] . '</td>
                
            </tr>
        </table>
        <p style="font-size: 30px; text-align:center">Bursary Reprint</p>
    </div>
    <div style="text-align:center; width:100%;" >
            <img src="../../../../img/payment/bursary-stamp.jpg" />
            <div style="font-size: 11px">ODUSANYA I.O</div>
            <div style="font-size: 9px">'.$row_history['date_time'].'</div>
        </div>
        ';
    
}

$mpdf->WriteHTML($html);
$mpdf->Output('receipt.pdf', 'I');

exit();
