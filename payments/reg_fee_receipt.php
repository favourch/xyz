<?php
//initialize the session
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "10";
check_auth($auth_users, $site_root);

$order_no = "-1";
if (isset($_GET['no'])) {
    $order_no = $_GET['no'];
}

 $query_history = sprintf("SELECT st.*, s.sesname, p.level "
                            . "FROM schfee_transactions st "
                            . "JOIN payschedule p ON st.scheduleid = p.scheduleid "
                            . "JOIN session s ON p.sesid = s.sesid "
                            . "WHERE matric_no = %s"
                            . "AND st.status = 'APPROVED' "
                            . "AND st.reg_fee = 'TRUE' "
                            . "AND st.ordid = %s", 
                         //    GetSQLValueString($_SESSION['uid'], "text"),
                            GetSQLValueString($_SESSION['uid'], "text"),
                            GetSQLValueString($order_no, "int")); 
                                                        
$history = mysql_query($query_history, $tams) or die(mysql_error());
$row_history = mysql_fetch_assoc($history);
$totalRows_history = mysql_num_rows($history);



$levelSQL = sprintf("SELECT levelname FROM level_name WHERE levelid = %s ", GetSQLValueString($row_history['level'], "int") );
$level = mysql_query($levelSQL , $tams) or die(mysql_error());
$row_level = mysql_fetch_assoc($level);


$query_schedule = sprintf("SELECT st.amt, st.percentPaid "
                        . "FROM schfee_transactions st "
                        . "WHERE matric_no = %s "
                        . "AND st.status = 'APPROVED' "
                        . "AND st.scheduleid = %s ",
                        GetSQLValueString($_SESSION['uid'], "text"), 
                        GetSQLValueString($row_history['scheduleid'], "int"));
$schedule = mysql_query($query_schedule, $tams) or die(mysql_error());
$row_schedule = mysql_fetch_assoc($schedule);
$totalRows_schedule = mysql_num_rows($schedule);

$amount = 0;
$percent = 0;

for ($idx = 0; $idx < $totalRows_schedule; ++$idx, $row_schedule = mysql_fetch_assoc($schedule)) {
    
    $value = str_replace(['NGN', ',', 'N'], '', $row_schedule['amt']);

    $percent += $row_schedule['percentPaid'];
    $amount += $value;
}

$percent = $percent > 100 ? 100 : $percent;

// Uncomment below to merge receipts of same schedule id
$row_history['percentPaid'] = $percent;
$row_history['amt'] = 'NGN' . number_format($amount, 2);

$name = getSessionValue('lname') . ' ' . getSessionValue('fname') . ' ' . getSessionValue('mname');

$query_proglevel = sprintf("SELECT std.*, p.progname "
                        . "FROM student std, programme p "
                        . "WHERE std.stdid = %s "
                        . "AND std.progid = p.progid ",
                        GetSQLValueString($_SESSION['MM_Username'], "text"));
$proglevel = mysql_query($query_proglevel, $tams) or die(mysql_error());
$row_proglevel = mysql_fetch_assoc($proglevel);
$totalRows_proglevel = mysql_num_rows($proglevel);

include("../mpdf/mpdf.php");
$mpdf = new mPDF('c', 'A4', '', '', 10, 10, 65, 15, 15, 5);
$stylesheet = file_get_contents('../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="15%" align="left"><img src="../../img/logo/school-logo.png" width="100px" /></td>
<td width="85%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 25pt">'.$university.'</h2>
<h5 style="font-size: 9pt">'.$university_address.'</h5></div>
</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);

$html = '<p style="border-bottom: 1px solid #999999; font-size: 9pt;">There is no known order with this Order No. ' . $order_no . '!</p>';

if ($totalRows_history > 0) {
    $html = '<p style="text-align:center; font-size: 18pt; margin-bottom: 20px"><strong> REGISTRATION FEE RECEIPT</strong></p>
    <div style="text-align:center; width:100%; font-size: 20pt">
        <table align="center" style="width: 60%;" class="table table-bordered table-striped">
            <tr>
                <td width="100">Name:</td>
                <td width="400">' . $name . '</td>
                <td rowspan="6">
                    <barcode code="'
            . $row_history['matric_no'] . ' '
            . $name . ' '
            . 'REGISTRATION FEE' . ' '
            . $row_history['sesname'] . ' '
            . 'NGN25,000.00' . ' '
           
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
                <td>' . $row_level['levelname'] . '</td>
                
            </tr>
            <tr>
                <td>Description:</td>
                <td>REGISTRATION FEE</td>
                
            </tr>
            <tr>
                <td>Session:</td>
                <td>' . $row_history['sesname'] . '</td>
                
            </tr>
            

            <tr>
                <td>Date:</td>
                <td>' . $row_history['process_date'] . '</td>
                
            </tr>

            <tr>
                <td>Amount:</td>
                <td> NGN25,000.00</td>
                
            </tr>
        </table>
        <div style="text-align:center; width:100%;" >
            <img src="../img/payment/bursary-stamp.jpg" />
            <div style="font-size: 11px">ODUSANYA I.O</div>
            <div style="font-size: 9px">'.$row_history['process_date'].'</div>
        </div>

    </div>';
}

$mpdf->WriteHTML($html);


$mpdf->Output('reg_receipt.pdf', 'I');

exit;