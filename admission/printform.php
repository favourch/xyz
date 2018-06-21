<?php
if (!isset($_SESSION)) {
    session_start();
}


require_once('../path.php');


$auth_users = "1,11,20,21,22,23,24";
check_auth($auth_users, $site_root);

$jambregid = getSessionValue('uid');

$query_rschk = sprintf("SELECT jambregid, at.typename, formsubmit, formpayment, s.sesid "
        . "FROM prospective p "
        . "LEFT JOIN admissions a ON p.admid = a.admid "
        . "LEFT JOIN admission_type at ON a.typeid = at.typeid "
        . "LEFT JOIN session s ON a.sesid = s.sesid "
        . "WHERE p.jambregid = %s", GetSQLValueString($jambregid, "text"));
$rschk = mysql_query($query_rschk, $tams) or die(mysql_error());
$row_rschk = mysql_fetch_assoc($rschk);
$totalRows_rschk = mysql_num_rows($rschk);

if ($row_rschk['formpayment'] == 'No') {
    header('Location: admission_payment/index.php');
    exit;
}

//Rereive all previous school attended 
$prevSchool_query = sprintf("SELECT * FROM applicant_prev_school "
        . "WHERE pstdid = %s", GetSQLValueString($jambregid, 'text'));
$prevSchool = mysql_query($prevSchool_query, $tams);
$totalRows_prevSchool = mysql_num_rows($prevSchool);



$sesid = $row_rschk['sesid'];

$query_info = sprintf("SELECT * "
        . "FROM payschedule "
        . "WHERE level = '0' "
        . "AND sesid = %s "
        . "AND admid = %s "
        . "AND regtype = %s "
        . "AND payhead = %s", GetSQLValueString($sesid, 'int'), 
        GetSQLValueString($_SESSION['admtype'], 'text'), 
        GetSQLValueString($_SESSION['regmode'], 'text'), 
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

$jambtotal = 0;

// Removed the DE/UTME check, should be fixed using admission type
$query_rspros = sprintf("SELECT p.*, s.sesid, st.stname, formsubmit, at.typeid, regtype, pr.progname AS prog1, lga.lganame, apft.reference, apft.ordid, apft.date_time, "
        . "pr2.progname AS prog2, at.typename, s1.subjname as jamb1, s2.subjname as jamb2, s3.subjname as jamb3, s4.subjname as jamb4 "
        . "FROM prospective p "
        . "LEFT JOIN appfee_transactions apft ON p.jambregid = apft.can_no AND apft.status = 'APPROVED'"
        . "LEFT JOIN admissions a ON p.admid = a.admid "
        . "LEFT JOIN admission_type at ON a.typeid = at.typeid "
        . "LEFT JOIN session s ON a.sesid = s.sesid "
        . "LEFT JOIN programme pr ON p.progid1 = pr.progid "
        . "LEFT JOIN programme pr2 ON p.progid2 = pr2.progid "
        . "LEFT JOIN subject s1 ON p.jambsubj1 = s1.subjid "
        . "LEFT JOIN subject s2 ON p.jambsubj2 = s2.subjid "
        . "LEFT JOIN subject s3 ON p.jambsubj3 = s3.subjid "
        . "LEFT JOIN subject s4 ON p.jambsubj4 = s4.subjid "
        . "LEFT JOIN state st ON st.stid = p.stid "
        . "LEFT JOIN state_lga lga ON lga.lgaid = p.lga "
        . "WHERE p.jambregid = %s", GetSQLValueString($jambregid, "text")); 
;
$rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
$row_rspros = mysql_fetch_assoc($rspros);
$totalRows_rspros = mysql_num_rows($rspros);

$query_rssit1 = sprintf("SELECT * 
                        FROM olevel o 
                        JOIN olevelresult l ON o.olevelid = l.olevelid 
                        JOIN subject s ON l.subject = s.subjid 
                        JOIN grade g ON l.grade = g.grdid 
                        WHERE o.jambregid=%s
                        AND sitting='first'", GetSQLValueString(getSessionValue('uid'), "text"));
$rssit1 = mysql_query($query_rssit1, $tams) or die(mysql_error());
$row_rssit1 = mysql_fetch_assoc($rssit1);
$totalRows_rssit1 = mysql_num_rows($rssit1);

$query_rssit2 = sprintf("SELECT * 
                        FROM olevel o 
                        JOIN olevelresult l ON o.olevelid = l.olevelid 
                        JOIN subject s ON l.subject = s.subjid 
                        JOIN grade g ON l.grade = g.grdid 
                        WHERE o.jambregid=%s
                        AND sitting='second'", GetSQLValueString(getSessionValue('uid'), "text"));

$rssit2 = mysql_query($query_rssit2, $tams) or die(mysql_error());
$row_rssit2 = mysql_fetch_assoc($rssit2);
$totalRows_rssit2 = mysql_num_rows($rssit2);

$olvel_veri_data = sprintf("SELECT * FROM olevel_veri_data olv JOIN verification v ON olv.jambregid  = v.jambregid AND olv.approve = 'yes' AND olv.status = 'use' AND v.olevel_submit = 'TRUE' WHERE olv.jambregid = %s ",
                        GetSQLValueString($jambregid, "text"));
$olevel_verRS = mysql_query($olvel_veri_data, $tams) or die(mysql_error());
$row_olevel_rs = mysql_fetch_assoc($olevel_verRS);
$num_rows_olevel = mysql_num_rows($olevel_verRS);

$year = explode('/', $row_rspros['sesname']);

$jambtotal = ($row_rspros['jambscore1'] + $row_rspros['jambscore2'] + $row_rspros['jambscore3'] + $row_rspros['jambscore4']);

$ses_folder = explode('/', $_SESSION['admname']);
$image_url = get_pics($jambregid, "../img/user/prospective/{$ses_folder[0]}", FALSE);

include("../mpdf/mpdf.php");
$mpdf = new mPDF('c', 'A4', '', '', 10, 10, 32, 15, 5, 5);
$stylesheet = file_get_contents('../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);
$mpdf->shrink_tables_to_fit = 1;

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="15%" align="left"><img src="../img/logo/school-logo.png" width="100px" /></td>
<td width="85%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 25pt">' . $university . '</h2>
<h5 style="font-size: 9pt">' . $university_address . '</h5></div>
</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);

$html .= '<table align="center" class="table table-bordered" style="font-size: 8pt" >
            <tr>
                <th colspan="2" style="text-align: center; font-size: 18px"><span> <p style="alignment-adjust: central">'.$_SESSION['admname'].' '.$row_rspros['typename']
           .' APPLICATION FORM - '.$row_rspros['jambregid'].'</p></span></th>
            </tr>
            <tr>
                <td colspan="2">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="5"> PERSONAL INFORMATION</th>
                            </tr>
                        </thead> 
                        <tbody>
                            <tr>
                                <th width="15%">Surname :</th>
                                <td width="25%">'. $row_rspros['lname'].'</td>
                                <th width="15%">First name :</th>
                                <td width="25%">'.$row_rspros['fname'].'</td>
                                <td width="20%" rowspan="4">
                                    <img width="140" height="160" align="top" name="placeholder" alt="Image" src="'.$image_url .'" style="alignment-adjust: central">
                                </td>
                            </tr>
                            <tr>
                                <th>Middle Name :</th>
                                <td>'.$row_rspros['mname'].'</td>
                                <th>E-Mail :</th>
                                <td>'.$row_rspros['email'] .'</td>
                            </tr>
                            <tr>
                                <th>Phone :</th>
                                <td>'. $row_rspros['phone'].'</td>
                                <th>State of Origin:</th>
                                <td>'. $row_rspros['stname'] .'</td>
                            </tr>
                            <tr>
                                <th>Address :</th>
                                <td>'. $row_rspros['address'].'</td>
                                <th>Local Govt :</th>
                                <td>'.$row_rspros['lganame'] .'</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <table  class="table table-condensed table-bordered">
                        <thead>
                            <tr>
                                <td colspan="6">Educational Background</td>
                            </tr>
                            <tr>
                                <th width="5%">#</th>
                                <th width="30%">School Name</th>
                                <th width="30%">School Address</th>
                                <th width="10%">From</th>
                                <th width="10%">To</th>
                                <th width="15%">Certificate Obtains</th>
                            </tr>
                        </thead>
                        <tbody>';
                            for ($i =0; $prevSchoolRS = mysql_fetch_assoc($prevSchool); $i++) {
                            $html.= ' <tr>
                                        <td>'.($i+1).'</td>
                                        <td>'.$prevSchoolRS['school_name'].'</td>
                                        <td>'.$prevSchoolRS['school_address'].'</td>
                                        <td>'.$prevSchoolRS['start_date'].'</td>
                                        <td>'.$prevSchoolRS['end_date'].'</td>
                                        <td>'.$prevSchoolRS['cert_obtain'].'</td>
                                    </tr>';
                                }    
                $html .='</tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table  class="table table-condensed table-bordered">
                        <thead>
                            <tr>
                                <td colspan="2">'.$row_rspros['typename'].' Result</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td width="30%">UTME Reg No. :</td>
                                <td width="70%" align="left">'.$row_rspros['jambregid'].'</td>
                            </tr>
                            <tr>
                                <td>UTME Year. : </td>
                                <td align="left">'.$row_rspros['jambyear'].'</td>
                            </tr>';
                        if($row_rspros['typeid']== 2){
                   $html .='<tr>
                                <th colspan="2" align="center">Subjects / Scores </th>
                            </tr>
                            <tr>
                                <td>'.$row_rspros['jamb1'].'</td>
                                <td align="left">'.$row_rspros['jambscore1'].'</td>
                            </tr>
                            <tr>
                                <td>'.$row_rspros['jamb2'].'</td>
                                <td align="left">'.$row_rspros['jambscore2'].'</td>
                            </tr>
                            <tr>
                                <td>'.$row_rspros['jamb3'].'</td>
                                <td align="left">'.$row_rspros['jambscore3'].'</td>
                            </tr>
                            <tr>
                                <td>'.$row_rspros['jamb4'].'</td>
                                <td align="left">'.$row_rspros['jambscore4'].'</td>
                            </tr>
                             <tr>
                                <th>Aggregate </th>
                                <td style="color:green; font-weight: bold">'.$jambtotal.'</td>
                            </tr>';
                            }
                $html .='</tbody>
                    </table>
                </td>
                <td>
                    <table  class="table table-condensed table-bordered">
                        <thead>
                            <tr>
                                <th colspan="2"> Course of Study</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th width="30%">First Choice  </th>
                                <td width="70%">' . $row_rspros['prog1'] . '</td>
                            </tr>
                            <tr>
                                <th>Second Choice</th>
                                <td>' . $row_rspros['prog2'] . '</td>
                            </tr>
                        </tbody>
                    </table>
                    <table  class="table table-condensed table-bordered">
                        <thead>
                            <tr>
                                <th colspan="2">Application Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th width="30%">Refrence No.</th>
                                <td width="70%">'.$row_rspros['reference'].'</td>
                            </tr>
                            <tr>
                                <th >Order ID</th>
                                <td>'.$row_rspros['ordid'].'</td>
                            </tr>
                            <tr>
                                <th>Date Time</th>
                                <td>'.$row_rspros['date_time'].'</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            
            <tr>
                <td width="50%">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="2"> SPONSOR INFORMATION</th>
                            </tr>
                        </thead> 
                        <tbody>
                            <tr>
                                <th width="40%">Sponsor&apos;s Name.</th>
                                <td width="60%">'.$row_rspros['sponsorname'].'</td>
                            </tr>
                            <tr>
                                <th width="40%">Sponsor&apos;s Phone.</th>
                                <td width="60%">'.$row_rspros['sponsorphn'].'</td>
                            </tr>
                            <tr>
                                <th width="40%">Sponsor&apos;s email.</th>
                                <td width="60%">'.$row_rspros['sponsoremail'] .'</td>
                            </tr>
                            <tr>
                                <th width="40%">Sponsor&apos;s Address.</th>
                                <td width="60%">'.$row_rspros['sponsoradrs'].'</td>
                            </tr>
                            <tr>
                                <th width="40%">Sponsor&apos;s Relationship.</th>
                                <td width="60%">'.$row_rspros['sponsorrelation'].'</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td width="50%">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="2"> NEXT OF KIN INFORMATION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th width="40%">Next OF Kin  Name.</th>
                                <td width="60%">'.$row_rspros['nxt_kin_fullname'] .'</td>
                            </tr>
                            <tr>
                                <th width="40%">Next OF Kin Phone.</th>
                                <td width="60%">'.$row_rspros['nxt_kin_phone'] .'</td>
                            </tr>
                            <tr>
                                <th width="40%">Next OF Kin Email.</th>
                                <td width="60%">'.$row_rspros['nxt_kin_email'] .'</td>
                            </tr>
                            <tr>
                                <th width="40%">Next OF Kin Address.</th>
                                <td width="60%">'.$row_rspros['nxt_kin_address'] .'</td>
                            </tr>
                            <tr>
                                <th width="40%">Next OF Kin Relationship.</th>
                                <td width="60%">'.$row_rspros['nxt_kin_relation'].'</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            
            <tr>
                <th colspan="2">O&apos;Level Result</th>
            </tr>
            <tr>
                <td colspan="2">';
                    if($num_rows_olevel > 0) {
                    $html .= '<table width="100%" class="table table-hover table-striped table-bordered">
                        <tbody>
                            <tr>';
                                do{ $dt = json_decode($row_olevel_rs['result_plain'], TRUE) ;
                                $html .= '<td width="50%">
                                    <table width="320" class="table table-hover table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th colspan="2">'.$row_olevel_rs['label'].'</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th>Exam</th>
                                                <td>'.$dt['result']['exam_name'] .'</td>
                                            </tr>
                                            <tr>
                                                <th>Candidate Name</th>
                                                <td>'.$dt['result']['candidate_name'].'</td>
                                            </tr>
                                            <tr>
                                                <th>Exam number</th>
                                                <td>'.$dt['result']['exam_number'].'</td>
                                            </tr>
                                            
                                            <tr>
                                                <th>Exam Type</th>
                                                <td>'.$dt['result']['exam_type'].'</td>
                                            </tr>
                                            <tr>
                                                <th>Exam Year</th>
                                                <td>'.$dt['result']['exam_year'].'</td>
                                            </tr>
                                            <tr>
                                                <th>Exam Center</th>
                                                <td>'.$dt['result']['exam_center'].'</td>
                                            </tr>
                                        <thead>
                                            <tr>
                                                <th colspan="2">Subject / Grade</th>
                                            </tr>
                                        </thead>';
                                        foreach ($dt['result']['result'] as $res) {
                                        $html.='<tr>
                                            <td>'.$res['subject'].'</td>
                                            <td>'.$res['score'].'</td>
                                        </tr>';
                                         } 
                                    $html.= '</tbody>
                                    </table>                    
                                </td>';
                                 } while($row_olevel_rs = mysql_fetch_assoc($olevel_verRS));
                            $html .= '</tr>
                        </tbody>
                    
                    </table>';
                     }else{
                   $html .= '<div class="alert alert-danger"><p style="font-size:20px; text-align:center">You are required to verify your O&apos;Level result Click the button below to verify your O&apos;Level result <br/><br/><a href="../olevel_service/index.php" class="btn btn-primary">Verify my O&apos;Level Result</a></p></div>';
                     }
                $html .= '</td>
            </tr>
            
            
        </table> <br/>
        I affirm that the information provided in this '.$_SESSION['admname'].' '.$row_rspros['typename'].'
        Application Form is true and correct to the best of my Knowledge.  
        I take full responsibility for any error of omission or commission, understanding that such error, 
        if committed, will affect my consideration for Admission in to '.$university.'.
        ';

$mpdf->WriteHTML($html);
$mpdf->Output('Application_form.pdf', 'I');

exit;
?>
