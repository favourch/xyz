<?php 
if (!isset($_SESSION)) {
  session_start();
}

$colname_student = '-1';
if(isset($_GET['stdid'])){
    $colname_student = $_GET['stdid'];
}


require_once('../path.php');


$auth_users = "10";
check_auth($auth_users, $site_root);

fillAccomDetails($site_root, $tams);

$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$test_session = ($row_sess['sesid'] - 1); 

$query_pay = sprintf("SELECT SUM(percentPaid) AS total_percent "
                    . "FROM schfee_transactions "
                    . "WHERE matric_no = %s "
                    . "AND sesid = %s "
                    . "AND status = 'APPROVED'",
                    GetSQLValueString($_SESSION['MM_Username'], "text"),
                    GetSQLValueString($test_session, "text"));
$pay = mysql_query($query_pay, $tams) or die(mysql_error());
$row_pay = mysql_fetch_assoc($pay);
$totalRows_pay = mysql_num_rows($pay);

$query_student = sprintf("SELECT s.*, progname, p.deptid, deptname, d.colid, colname "
                        . "FROM student s, programme p, department d, college c "
                        . "WHERE s.progid = p.progid "
                        . "AND p.deptid = d.deptid "
                        . "AND d.colid = c.colid "
                        . "AND stdid = %s", GetSQLValueString($_SESSION['MM_Username'], "text"));
$student = mysql_query($query_student, $tams) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);

$university = 'Tai Solarin University of Education';

include("../mpdf/mpdf.php");
$mpdf = new mPDF('c','A4','','',15,15,40,15,10,10); 
$stylesheet = file_get_contents('../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

 $header = ' <table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
                <tr>
                    <td width="15%" align="left"><img src="../images/logo.jpg" width="100px" /></td>
                    <td width="85%" align="center">
                        <div style="font-weight: bold;">
                            <h2 style="font-size: 25pt">'.$university.'</h2>
                            <h5 style="font-size: 9pt">'.$university_address.'</h5>
                        </div>
                    </td>
                </tr>
            </table>';

$mpdf->SetHTMLHeader($header);
 if($row_pay['total_percent'] == 100){
     
   $html = '<table align="center" width="690">
                <tr>
                    <td align="center">
                    <span> <p style="alignment-adjust: central"><h2> Payment Verification Certificate </h2></p></span>
                        <table width="670">
                            <tr>
                                <td size="30">
                                    <p>&nbsp;</p>
                                    <span><p style="font-size: 10pt">This is to certify that the School Fees Receipts of <strong>'.$row_student['fname'].' '.$row_student['lname'].' '.$row_student['mname'].'</strong> 
                                    with the Matriculation Number <br /> <br /><strong>'.$row_student['stdid'].' </strong>of the Department of 
                                    <strong>'.$row_student['deptname'].'</strong> have been verified </span></p>
                                </td>
                            </tr>
                            
                        </table>
                        
                        <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
                        
                        <table width="690">
                        
                            <tr >
                            
                                <td width="30%" align="left">
                                    <p>
                                        <img src="../images/ictsign.jpg" width="150px" />_____________________________<br/><br/>
                                        TASUED ICT Center
                                    </p>
                                </td>
                                <td width="40%" align="center">
                                <barcode code="'.$row_sess['sesname'].' '
                                                ."Final Payment Verification for "
                                                .$row_student['stdid'].' '
                                                .$row_student['lname'].' '
                                                .$row_student['fname'].' '
                                                .$row_student['deptname']
                                                .'" type="QR" class="barcode" size="1.3" error="M" />
                            </td>
                                <td width="30%" align="right">
                                    <p><br /><br /><br /><br /><br />
                                        _____________________________<br/><br/>
                                       Student Affairs Office 
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>   
            </table> ';
   
 }
 if($row_pay['total_percent'] < 100){
     $html = '<table align="center" width="690">
                <tr>
                    <td align="center">
                    <span> <p style="alignment-adjust: central"> Payment Verification Certificate </p></span>
                        <table width="670">
                            <tr>
                                <td>
                                    <p>&nbsp;</p>
                                    <p style="color:red">SORRY! You Still Have some pendding School Fee Payment to make </p>
                                </td>
                            </tr>
                            
                        </table>
                        <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
                        <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
                        <p>&nbsp;</p><p>&nbsp;</p>
                    </td>
                </tr>   
            </table>';
 }
   
$mpdf->WriteHTML($html);
$mpdf->Output('Pay_verification_certificate.pdf', 'I');

exit;
