<?php 
if (!isset($_SESSION)) {
    session_start();
}
require_once('../path.php');

$auth_users = "10";
check_auth($auth_users, $site_root);

$sesid = getSessionValue('sesid');
$stdid = getSessionValue('uid');
$pid = getSessionValue('pid');
$status = ['error' => true, 'type' => 'invalid'];

$query_last_reg = sprintf("SELECT s.sesid, s.sesname "
                            . "FROM registration r "
                            . "JOIN programme p ON p.duration = r.level "
                            . "JOIN session s ON s.sesid = r.sesid "
                            . "WHERE r.stdid = %s "
                            . "AND r.status = 'Registered' "
                            . "AND p.progid = %s "
                            . "ORDER BY sesid DESC",
                            GetSQLValueString($stdid, "text"),
                            GetSQLValueString($pid, "int"));
$last_reg = mysql_query($query_last_reg, $tams) or die(mysql_error());
$row_last_reg = mysql_fetch_assoc($last_reg);
$totalRows_last_reg = mysql_num_rows($last_reg);

if($totalRows_last_reg > 0) {   
    $sesid = $row_last_reg['sesid'];
    
    $query_clear_fee = sprintf("SELECT c.*, s.sesname "
                                . "FROM clearance_transactions c, session s "
                                . "WHERE c.sesid=s.sesid AND c.matric_no = %s AND c.status = 'APPROVED' ", 
                                GetSQLValueString($stdid, "text"));
    $clear_fee = mysql_query($query_clear_fee, $tams) or die(mysql_error());
    $row_clear_fee = mysql_fetch_assoc($clear_fee);
    $totalRows_clear_fee = mysql_num_rows($clear_fee);
    
    if($row_clear_fee['pcount'] >= 3){
        header('Location: ../payments/pay_history.php');
        die();
    }
    
    if($totalRows_clear_fee > 0) {
    
        if(checkFees($sesid, $stdid)) {
            $query_student = sprintf("SELECT s.*, progname, p.deptid, deptname, d.colid, colname "
                    . "FROM student s, programme p, department d, college c "
                    . "WHERE s.progid = p.progid "
                    . "AND p.deptid = d.deptid "
                    . "AND d.colid = c.colid "
                    . "AND stdid = %s", 
                    GetSQLValueString($_SESSION['MM_Username'], "text"));
            $student = mysql_query($query_student, $tams) or die(mysql_error());
            $row_student = mysql_fetch_assoc($student);
            $totalRows_student = mysql_num_rows($student);

            $status = ['error' => false];
        }else {
            $status['type'] = 'owing';
        }   
    }else {
        $status['type'] = 'unclear';
    }
}

if($status['error']) :?>

<!doctype html>
<html>
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
        <?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH."/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH."/page_header.php" ?>
                    
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                    Payment Verification Certificate 
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <p>Unfortunately, you are not eligible to print this payment verification certificate!</p>
                                        <p>REASON: 
                                            <?php 
                                                $responses = [
                                                    'invalid' => 'Your records do not indicate you have met the requirements for graduation!',
                                                    'owing' => 'You have pending school fees payments!',
                                                    'unclear' => 'There is no record of your clearance fees! '
                                                ];
                                            
                                                echo $responses[$status['type']];
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
    </body>
</html>
<?php else :

    
    include("../mpdf/mpdf.php");
    $mpdf = new mPDF('c','A4','','',15,15,40,15,10,10); 
    $stylesheet = file_get_contents('../css/mpdfstyletables.css');
    $mpdf->WriteHTML($stylesheet, 1);

    $header = ' <table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 10pt; color: #000088;">
                    <tr>
                        <td width="15%" align="left"><img src="../img/logo/school-logo.png" width="100px" /></td>
                        <td width="85%" align="center">
                            <div style="font-weight: bold;">
                                <h2 style="font-size: 25pt">'.$university.'</h2>
                                <h5 style="font-size: 9pt">'.$university_address.'</h5>
                            </div>
                        </td>
                    </tr>
                </table>';

    $mpdf->SetHTMLHeader($header);
    
       $html = ' <div style="text-align:center; width:100%; font-size: 20pt">
       <table align="center" width="690">
                    <tr>
                        <td align="center">
                        <span> <p style="alignment-adjust: central"><h2> Payment Verification Certificate </h2></p></span>
                            <table width="670">
                                <tr>
                                    <td size="30">
                                        <p>&nbsp;</p>
                                        <span><p style="font-size: 10pt">This is to certify that the School Fees Receipts of <strong>'.
                                            $row_student['fname'].' '.$row_student['lname'].' '.$row_student['mname'].'</strong> 
                                        with the Matriculation Number <strong>'.$row_student['stdid']
                                        .' </strong><br /> <br />of the Department of 
                                        <strong>'.$row_student['deptname'].'</strong> has been verified during the session  <strong>'.$row_clear_fee['sesname'].' </strong></span></p>
                                    </td>
                                </tr>

                            </table>

                            <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

                            <table width="690">

                                <tr >

                                    <td width="30%" align="left">
                                        <p>
                                            <img src="../img/ictsign.png" width="300px" />
                                            _____________________________<br/><br/>
                                            TASUED ICT Center
                                        </p>
                                    </td>
                                    <td width="40%" align="center">
                                    <barcode code="'.$row_sess['sesname'].' '
                                                    ."Final Payment Verification for "
                                                    .$row_student['stdid'].' '
                                                    .$row_student['lname'].' '
                                                    .$row_student['fname'].' '
                                                    .$row_student['deptname'].' '
                                                    .$row_last_reg['sesname']
                                                    .'" type="QR" class="barcode" size="1.3" error="M" />
                                </td>
                                    <td width="30%" align="right">
                                        <p><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
                                            _____________________________<br/><br/>
                                           Student Affairs Office 
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>  
                    <tr>
                        <td size="30">
                            <p>&nbsp;</p>
                            <p style="font-size: 10pt">NOTE: This verification certificate is only valid for <strong>'
               .$row_last_reg['sesname'].'</strong> academic session. Any decision at the university senate that 
                   requires your graduation session to be changed for whatsoever reason to a different session than 
                   what is stated on this certificate, renders this certificate <strong>INVALID!</strong> </p>
                        </td>
                    </tr>
                </table> 
                <div>
            <img src="../img/payment/bursary-stamp.jpg" />
            <div style="font-size: 11px">ODUSANYA I.O</div>
            <div style="font-size: 9px">'.$row_clear_fee['date_time'].'</div>
        </div>
        </div>
                ';
                


    $mpdf->WriteHTML($html);
 $printCountSQL = sprintf("UPDATE clearance_transactions SET pcount = pcount + 1 "
                        . "WHERE matric_no = %s "
                        . "AND status = 'APPROVED' "
                        . "AND ordid = %s ",
                        GetSQLValueString($_SESSION['MM_Username'], "text"), 
                        GetSQLValueString($row_clear_fee['ordid'], "int")); 
$printCountRS = mysql_query($printCountSQL, $tams) or die(mysql_error());
    $mpdf->Output('Pay_verification_certificate.pdf', 'I');

    exit;
    
endif;