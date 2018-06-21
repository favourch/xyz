<?php 
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "11";
check_auth($auth_users, $site_root);

$jambregid = getSessionValue('uid');

 $query_rspros = sprintf("SELECT * FROM prospective WHERE jambregid = %s ", 
        GetSQLValueString($jambregid, "text"));
$rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
$row_rspros = mysql_fetch_assoc($rspros);
$totalRows_rspros = mysql_num_rows($rspros);


if($row_rspros['clinic_clear'] == 'no'){
    header('Location: ../../admission/progress.php');
    exit();
}

include("../../mpdf/mpdf.php");
//$mpdf = new mPDF('c','A4','','',15,15,40,15,10,10); 
$mpdf = new mPDF();
$stylesheet = file_get_contents('../../css/mpdfstylesheet.css');
$mpdf->WriteHTML($stylesheet, 1);

$html = '';


$html .=  '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
            <tr>
                <td width="15%" align="left"><img src="../../img/logo/school-logo.png" width="100px" /></td>
                <td width="85%" align="center">
                    <div style="font-weight: bold;">
                        <h2 style="font-size: 25pt">'.$university.'</h2>
                        <h5 style="font-size: 15pt">MEDICAL CENTRE</h5>
                        <h5 style="font-size: 12pt">MEDICAL CERTIFICATE OF FITNESS</h5>
                    </div>
                </td>
            </tr>
            </table>';
$html .="<p>
        <br />
            RE: ....................................... ". $row_rspros['lname'] ." ".$row_rspros['fname']." ".$row_rspros['mname'] ."............................................. <br /><br />
            This is to certify that i have today examined the above named person in my own Opinion, he/she is both physically and mentally fit
            <br /><br />
            He/She Shows evidence of successful vaccination.
        </p>
        <p style='text-align:center'>
            <div >
                <img height='150' width='200' src='../../img/clinic_stamp.jpg' />
                <div style='font-size: 11px'>Dr. OKUSANYA O.O</div>
                <div style='font-size: 9px'>".$row_rspros['clinic_date']."</div>
            </div>
        </p>
       <br /><br /> ";

$html .= "--------------------------------------------------------------------------------------------------------------------------------------------- <br/><br/><br/>";


$html .=  '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
            <tr>
                <td width="15%" align="left"><img src="../../img/logo/school-logo.png" width="100px" /></td>
                <td width="85%" align="center">
                    <div style="font-weight: bold;">
                        <h2 style="font-size: 25pt">'.$university.'</h2>
                        <h5 style="font-size: 15pt">MEDICAL CENTRE</h5>
                        <h5 style="font-size: 12pt">MEDICAL CERTIFICATE OF FITNESS</h5>
                    </div>
                </td>
            </tr>
            </table>';
$html .="<p>
        <br />
            RE: ....................................... ". $row_rspros['lname'] ." ".$row_rspros['fname']." ".$row_rspros['mname'] ."............................................. <br /><br />
            This is to certify that i have today examined the above named person in my own Opinion, he/she is both physically and mentally fit
            <br /><br />
            He/She Shows evidence of successful vaccination.
        </p>
        <p style='text-align:center'>
            <div >
                <img height='150' width='200' src='../../img/clinic_stamp.jpg' />
                <div style='font-size: 11px'>Dr. OKUSANYA O.O</div>
                <div style='font-size: 9px'>".$row_rspros['clinic_date']."</div>
            </div>
        </p>
       <br /> ";


$mpdf->WriteHTML(utf8_encode($html));
$mpdf->Output('Clearance Certificate.pdf', 'I');

exit;
