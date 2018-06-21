<?php
if (!isset($_SESSION)) {
    session_start();
}


require_once('../../path.php');


$auth_users = "1,11,20,21,22,23,24";
check_auth($auth_users, $site_root);

$query_session = sprintf("SELECT sesid, sesname FROM session WHERE  sesid  = %s", GetSQLValueString($_SESSION['sesid'], "int"));
$session = mysql_query($query_session, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);
$totalRows_session = mysql_num_rows($session);


$query_department = sprintf("SELECT * FROM department WHERE deptid = %s ",GetSQLValueString($_SESSION['did'], "int"));
$department = mysql_query($query_department, $tams) or die(mysql_error());
$row_department = mysql_fetch_assoc($department);
$totalRows_department = mysql_num_rows($department);


$query_pros = sprintf("SELECT * "
                        . "FROM prospective p JOIN programme pr ON pr.progid = p.progoffered JOIN department d ON pr.deptid = d.deptid "
                        . "JOIN admissions a ON p.admid = a.admid "
                        . "JOIN admission_type at ON a.typeid = at.typeid "
                        . "WHERE d.deptid  = %s AND p.sesid = %s",  GetSQLValueString($row_department['deptid'], "int"), GetSQLValueString($row_session['sesid'], "int"));
$prosRS = mysql_query($query_pros, $tams) or die(mysql_error());
$row_pros = mysql_fetch_assoc($prosRS);
$totalRows_pros = mysql_num_rows($prosRS);


include("../../mpdf/mpdf.php");
$mpdf = new mPDF('c', 'A4', '', '', 10, 10, 32, 15, 5, 5);
$stylesheet = file_get_contents('../../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);
$mpdf->shrink_tables_to_fit = 1;

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="15%" align="left"><img src="../../img/logo/school-logo.png" width="100px" /></td>
<td width="85%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 25pt">' . $university . '</h2>
<h5 style="font-size: 9pt">' . $university_address . '</h5></div>
<h4 style="font-size: 9pt">Admitted List for '.$row_session['sesname'].' Department of '. $row_department['deptname'].'</h4></div>
</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);

$html = '
<table class="table table-bordered table-condensed table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Jamb Reg. No.</th>
                                            <th>Form Number</th>
                                            <th>Name</th>
                                            <th>Admission Type</th>
                                            <th>Sex</th>
                                            <th>Programme</th>
                                            
                                        </tr>
                                    </thead>
                                    <tbody style="font-weight: normal">';
                                      
                                            if ($totalRows_pros > 0) {
                                                $i = 1;
                                                do {
                                      
                               $html .=' <tr>
                                            <td>'. $i++ .'</td>
                                            <td align="center">'.strtoupper($row_pros['jambregid']).'</td>
                                            <td align="center">'. $row_pros['formnum'] .'</td>
                                            <td>'. strtoupper("{$row_pros['lname']} {$row_pros['fname']} {$row_pros['mname']}") .'</td>
                                            <td align="center">'. $row_pros['typename'].'</td>
                                            <td align="center">'. $row_pros['Sex'] .'</td>
                                            <td align="center">'. $row_pros['progname'].'</td>
                                        </tr>';
                                         
                                                }while($row_pros = mysql_fetch_assoc($prosRS));
                                                
                                           } else {
                                        
                              $html .='<tr>
                                            <td colspan="8">There are no applicants to display!</td>
                                        </tr>';
                                            }
                              $html .= '</tbody>
                                </table>';

$mpdf->WriteHTML($html);
$mpdf->Output('Application_form.pdf', 'I');

exit;
?>
