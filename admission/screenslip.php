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

$query_rschk = sprintf("SELECT p.lname, p.fname, p.mname, p.jambregid, p.regtype, p.admtype, p.formsubmit, 
                        p.formpayment, p.formnum, p.batch, s.sesname, at.typename
                        FROM prospective p 
                        JOIN admissions ad ON ad.admid = p.admid
                        JOIN admission_type at ON ad.typeid = at.typeid
                        LEFT JOIN session s ON p.sesid = s.sesid 
                        WHERE p.jambregid=%s",
                        GetSQLValueString($jambregid, "text"));
$rschk = mysql_query($query_rschk, $tams) or die(mysql_error());
$row_rschk = mysql_fetch_assoc($rschk);
$totalRows_rschk = mysql_num_rows($rschk);

$query_screen = sprintf("SELECT *
                        FROM screening
                        WHERE jambregid=%s",
                        GetSQLValueString($jambregid, "text"));
$screen = mysql_query($query_screen, $tams) or die(mysql_error());
$row_screen = mysql_fetch_assoc($screen);
$totalRows_screen = mysql_num_rows($screen);

if ($totalRows_screen < 0) {
    header('Location: progress.php');
    exit;
}
//echo $totalRows_screen; exit();

$year = explode('/', $row_rschk['sesname']);

$regtype = $row_rschk['regtype'] == 'coi'? 'Change of Institution': 'Regular';

//$venue = 'E-Learning/Computer Laboratory Building, Ijagun Campus';
//$room = "Cluster Room 1";
//$date =  'Sunday, 19th June, 2016';
//$time = '7.00am - 9.00am';
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
           <span> <p style="alignment-adjust: central"><h1>'.$_SESSION['stdsesname'].' Screening Slip ('.strtoupper($row_rschk['typename']).') </h1></p></span>
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
                            
                        </table>
                    </td>
                </tr>';
                

                           $html.= '<tr>
                           
                          <td colspan="2">
                                    
                                        <table width="670" class="table table-hover table-striped table-bordered">
                                            <tr>
                                                <th colspan="2">Screening Schedule Information</th>
                                            </tr>
                                             <tr>
                                                <th>Date: </th>
                                                <td>'.$row_screen['date'].'</td>
                                            </tr>
                                            <tr>
                                                <th width="150">Venue: </th>
                                                <td>'.$row_screen['venue'].'</td>
                                            </tr>
                                            <tr>
                                                <th width="150">Cluster Room: </th>
                                                <td>'.$row_screen['room'].'</td>
                                            </tr>
                                           
                                            <tr>
                                                <th>Time Slot: </th>
                                                <td>'.$row_screen['time'].' prompt</td>
                                            </tr>
                                            <tr>
                                                <th>Queue No: </th>
                                                <td>'.$row_screen['queue'].' </td>
                                            </tr>
                                        </table>
                                        
                                        
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <table width="670" class="table table-hover table-striped table-bordered">
                                            
                                            <tr>
                                                <th colspan="2"><h3> Applicants are advised to adhere strictly to the Post-UTME Screening arrangement 
                                                as indicated in the schedule information that is provided above. You are only required to come along with 
                                                stapled copies of your Application Form, Screening Fee receipt, O/Level Results, 
                                                JAMB Result slip and NCE/ND/HND Result/Transcript for DE applicants; they are to be submitted.  <br /><br />
                                                Be advised that you are required to use the Online Result Verification System on the portal to submit your O&apos;Level 
                                                Results for VERIFICATION on/before the Screening Date, to be considered for offer of admission. <br /><br />
                                            Best of Luck ! </h3></th>
                                            </tr>
                                            
                                        </table>
                                    </td>
                                </tr>
                                   
            </table>
        </td>
      </tr>
     
    </table>';  
   
$mpdf->WriteHTML($html);
$mpdf->Output('UtmeExamSlip.pdf', 'I');

exit;
