<?php 
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

require_once('../../path.php');

$auth_users = "11";
check_auth($auth_users, $site_root);

$order_no = "-1";
if(isset($_GET['no'])) {
    $order_no = $_GET['no'];
}

 $query_history = sprintf("SELECT st.*, s.sesname, l.levelname "
                            . "FROM schfee_transactions st "
                            . "JOIN payschedule p ON st.scheduleid = p.scheduleid "
                            . "JOIN level_name l ON p.level = CAST(l.levelid as CHAR(1))  "
                            . "JOIN session s ON p.sesid = s.sesid "
                            . "WHERE can_no = %s "
                            . "AND st.status = 'APPROVED' "
                            . "AND st.ordid = %s", 
                            GetSQLValueString($_SESSION['MM_Username'], "text"),
                            GetSQLValueString($order_no, "int"));
$history = mysql_query($query_history, $tams) or die(mysql_error());
$row_history = mysql_fetch_assoc($history);
$totalRows_history = mysql_num_rows($history);

$name = getSessionValue('lname').' '.getSessionValue('fname').' '.getSessionValue('mname');

 $query_proglevel = sprintf("SELECT std.*, p.progname "
        . "FROM student std, programme p "
        . "WHERE std.jambregid = %s "
        . "AND std.progid = p.progid ",
        GetSQLValueString($_SESSION['MM_Username'], "text"));  
$proglevel = mysql_query($query_proglevel, $tams) or die(mysql_error());
$row_proglevel = mysql_fetch_assoc($proglevel);
$totalRows_proglevel = mysql_num_rows($proglevel);

include("../../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4','','',10,10,65,15,15,5);
$stylesheet = file_get_contents('../../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="90%" align="center"><img src="../../img/logo/school-logo.png" width="100px" /></td></tr>
<tr>
<td width="90%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 21pt">'.$university.'</h2>

<h5 style="font-size: 9pt">'.$university_address.'</h5></div>
</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);

$html = '<p style="border-bottom: 1px solid #999999; font-size: 9pt;">There is no known order with this Order No. '.$order_no.'!</p>';

if($totalRows_history > 0) {
    $html ='<p style="text-align:center; font-size: 18pt; margin-bottom: 20px"><strong> SCHOOL FEE RECEIPT</strong></p>
    <div style="text-align:center; width:100%; font-size: 20pt">
        <table align="center" style="width: 60%;" class="table table-bordered table-striped">
            <tr>
                <td width="100">Name:</td>
                <td width="400">'.$name.'</td>
                <td rowspan="6">
                    <barcode code="'
                        .$row_history['matric_no'].' '
                        .$name.' '
                        .'SCHOOL FEE'.' '
                        .$row_history['sesname'].' '
                        .$row_history['amt'].' ('
                        .$row_history['percentPaid'].' %)'
                        .'" type="QR" class="barcode" size="1.3" error="M" />
                </td>
            </tr>

            <tr>
                <td>Matric No.:</td>
                <td >'.$row_history['matric_no'].'</td>
            </tr>
            <tr>
                <td>Programme:</td>
                <td>'.$row_proglevel['progname'].'</td>
                
            </tr>
            <tr>
                <td>Level:</td>
                <td>'.$row_history['levelname'].'</td>
                
            </tr>
            <tr>
                <td>Description:</td>
                <td>SCHOOL FEE</td>
                
            </tr>
            <tr>
                <td>Session:</td>
                <td>'.$row_history['sesname'].'</td>
                
            </tr>
            <tr>
                <td>Transaction Reference:</td>
                <td>'.$row_history['reference'].'</td>
                <td rowspan="3"><p style="text-align:center; font-size: 40pt;"><strong>'.$row_history['percentPaid'].'%</strong></p></td> 
            </tr>

            <tr>
                <td>Date:</td>
                <td>'.$row_history['date_time'].'</td>
                
            </tr>

            <tr>
                <td>Amount:</td>
                <td> '.$row_history['amt'].'</td>
                
            </tr>
        </table>

    </div>';
}

$mpdf->WriteHTML($html);
$mpdf->Output('receipt.pdf', 'I');

exit;
