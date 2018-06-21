<?php 
if (!isset($_SESSION)) {
  session_start();
}


require_once('../path.php');


$auth_users = "11";
check_auth($auth_users, $site_root);

$jambregid = getSessionValue('uid');
$sesid = $_SESSION['admid'];

$query_info = sprintf("SELECT * "
        . "FROM payschedule "
        . "WHERE level = '0' "
        . "AND sesid = %s "
        . "AND admid = %s "
        . "AND status = %s "
        . "AND payhead = %s",
        GetSQLValueString($sesid, 'int'),
        GetSQLValueString($_SESSION['admtype'], 'int'),
        GetSQLValueString($_SESSION['regmode'] ,'text'),
        GetSQLValueString('app', 'text'));
$info = mysql_query($query_info, $tams) or die(mysql_error());

$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);
$amt = $row_info['amount'];

$pay_status = checkPaymentPros($sesid, $jambregid, $amt, 'app');
if (!$pay_status['status']) {
    header('Location: admission_payment/index.php');
    exit;
}

$query_rschk = sprintf("SELECT lname, fname, mname, jambregid, regtype, admtype, formsubmit, 
                        formpayment, formnum, batch, sesname 
                        FROM prospective p 
                        LEFT JOIN session s ON p.sesid = s.sesid 
                        WHERE p.jambregid=%s",
                        GetSQLValueString($jambregid, "text"));
$rschk = mysql_query($query_rschk, $tams) or die(mysql_error());
$row_rschk = mysql_fetch_assoc($rschk);
$totalRows_rschk = mysql_num_rows($rschk);

$year = explode('/', $row_rschk['sesname']);

$regtype = $row_rschk['regtype'] == 'coi'? 'Change of Institution': 'Regular';

$venue = $university.' Ijagun Campus';
$date =  'Monday, 2nd November, 2015';
$time = '12:00pm';
$amt = $pay_status['paid'];

$ses_folder = explode('/', $_SESSION['admname']);
$image_url = get_pics($jambregid, "../img/user/prospective/{$ses_folder[0]}", FALSE);


include("../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4','','',10,10,32,15,5,5); 
$stylesheet = file_get_contents('../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="15%" align="left"><img src="../img/logo/school-logo.png" width="100px" /></td>
<td width="85%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 25pt">'.$university.'</h2>
<h5 style="font-size: 9pt">'.$university_address.'</h5></div>
</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);
 
   $html .= '<table align="center" width="690">
       <tr>
        <td align="center">
           <span> <p style="alignment-adjust: central">'.$_SESSION['admses']['name'].' Examination Slip </p></span>
            <table width="670" class="table  table-bordered">
                <tr>
                    <td colspan="2">
                        <table width="670" class="table table-hover table-striped table-bordered">
                            <thead>
                            <tr>
                                <th colspan="4"> BIO-DATA</th>
                            </tr>
                            </thead> 
                            <tr>
                                <th width="130">Form No. :</th>
                                <td colspan="2">'.$row_rschk['formnum'].'</td>
                                <td rowspan="7" align="center"> <img  style="alignment-adjust: central" src="'.$image_url.'" alt="Image"  id="placeholder" name="placeholder" width="160" height="160" align="top"/></td> 
                            </tr>
                            <tr>
                                <th width="130">UTME No. :</th>
                                <td colspan="2">'.$row_rschk['jambregid'].'</td>
                            </tr>
                            <tr>
                                <th width="130">Surname :</th>
                                <td colspan="2">'.$row_rschk['lname'].'</td>
                            </tr>
                            <tr>
                                <th>First Name :</th>
                                <td colspan="2">'.$row_rschk['fname'].' </td>
                            </tr>
                            <tr>
                                <th>Middle Name :</th>
                                <td colspan="2">'.$row_rschk['mname'].'</td>
                            </tr>
                            <tr>
                                <th>Registration Type :</th>
                                <td colspan="2">'.$regtype.'</td>
                            </tr>
                            <tr>
                                <th>Admission Type :</th>
                                <td colspan="2">'.strtoupper($row_rschk['admtype']).'</td>
                            </tr>
                        </table>
                    </td>
                </tr>';
   
   
                           $html.= '<tr>
                                    <td colspan="2">
                                        <table width="670" class="table table-hover table-striped table-bordered">
                                            <tr>
                                                <th colspan="2">Examination Information</th>
                                            </tr>
                                            <tr>
                                                <th width="150">Venue: </th>
                                                <td>'.$venue.'</td>
                                            </tr>
                                            <tr>
                                                <th>Date: </th>
                                                <td>'.$date.'</td>
                                            </tr>
                                            <tr>
                                                <th>Time: </th>
                                                <td>'.$time.' prompt</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <table width="670" class="table table-hover table-striped table-bordered">
                                            <tr>
                                                <th colspan="2">Payment Information</th>
                                            </tr>
                                            <tr>
                                                <th width="120">Amount: </th>
                                                <td>N '.number_format($amt).'</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>';
                                
             
               $html .= '
             
            </table>
        </td>
      </tr>
    </table>';
   
$mpdf->WriteHTML($html);
$mpdf->Output('UtmeExamSlip.pdf', 'I');

exit;
