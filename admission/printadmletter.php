<?php 
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "1,11,20";
check_auth($auth_users, $site_root);

$jambregid = getSessionValue('uid');

$query_rspros = sprintf("SELECT p.*, s.*, pr.progname, c.colname, a.admletter "
        . "FROM prospective p "
        . "LEFT JOIN admissions a ON p.admid = a.admid "
        . "LEFT JOIN admission_type at ON a.typeid = at.typeid "
        . "LEFT JOIN session s ON a.sesid = s.sesid "
        . "LEFT JOIN programme pr ON p.progoffered = pr.progid "
        . "LEFT JOIN department d ON pr.deptid = d.deptid "
        . "LEFT JOIN college c ON d.colid = c.colid "
        . "WHERE p.jambregid = %s", 
        GetSQLValueString($jambregid, "text"));
$rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
$row_rspros = mysql_fetch_assoc($rspros);
$totalRows_rspros = mysql_num_rows($rspros);

$resumption = " ";
$submission = " ";  
if ($row_rspros['batch'] ==1) {
    $resumption = " Tuesday, 15th September, 2015 ";
    $submission = "Thursday, 24th September, 2015";
}
else {
    $resumption = " Monday 26th October, 2015 ";
    $submission = "Monday, 2nd November, 2015 ";
}


$query_info = sprintf("SELECT * "
        . "FROM payschedule "
        . "WHERE level = '0' "
        . "AND sesid = %s "
        . "AND admid = %s "
        . "AND status = %s "
        . "AND payhead = %s",
        GetSQLValueString($sesid, 'int'), 
        GetSQLValueString($row_rspros['typeid'], 'text'), 
        GetSQLValueString($row_rspros['regmode'], 'text'),
//        GetSQLValueString('app', 'text'));
        GetSQLValueString('acc', 'text'));
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$sesid = $_SESSION['admid'];
$amt = $row_info['amount'];

$pay_status = checkPaymentPros($sesid, $jambregid, $amt, 'acc');
if (!$pay_status['status'] && !in_array(getAccess(), [1, 20])) {
    header('Location: acceptance_payment/index.php');
    exit;
}

$search_array = [
    '[:uniname]', '[:address]', '[:firstname]', '[:lastname]', '[:middlename]', '[:admode]', '[:formnum]', '[:progoffered]', 
    '[:utmeno]', '[:postutmescore]', '[:session]', '[:fullname]', '[:jambscore]', '[:college]'
];


if(in_array(getAccess(), [1, 20])) {
    
    $type = isset($_GET['type'])? $_GET['type']: '';
    
    $search_array = [
        '[:uniname]', '[:admode]', '[:session]'
    ];
    
    $query_rspros = sprintf("SELECT * "
                            . "FROM admissions a "
                            . "LEFT JOIN admission_type at ON a.typeid = at.typeid "
                            . "LEFT JOIN session s ON a.sesid = s.sesid "
                            . "WHERE a.admid = %s",
                            GetSQLValueString($type, 'int'));
    $rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
    $row_rspros = mysql_fetch_assoc($rspros);
    $totalRows_rspros = mysql_num_rows($rspros);
    
    $replace_array = [
        $university, $row_rspros['typename'], $row_rspros['sesname']       
    ];
    
}else {
    
    if ($row_rspros['adminstatus'] == 'No') {
        header('Location: status.php');
        exit;
    }
    
    $jamb_score = $row_rspros['jambscore1'] + $row_rspros['jambscore2'] + $row_rspros['jambscore3'] 
            + $row_rspros['jambscore4']; 
  
    if($jamb_score < 180 && $row_rspros['admid'] == 3) {
        $jamb_score = 'Regularization';
    }

    $replace_array = [
        $university, $row_rspros['address'], $row_rspros['fname'], $row_rspros['lname'], $row_rspros['mname'], 
        $row_rspros['typename'], $row_rspros['formnum'],
        $row_rspros['progname'], $row_rspros['jambregid'], $row_rspros['score'], $row_rspros['sesname'],
        $row_rspros['lname'] . ', ' . $row_rspros['fname'] . ' ' . $row_rspros['mname'], $jamb_score, 
        $row_rspros['colname']
    ];
}


include("../mpdf/mpdf.php");
$mpdf = new mPDF('c','A4','','',15,15,70,15,10,10); 
$stylesheet = file_get_contents('../css/mpdfstylesheet.css');
$mpdf->WriteHTML($stylesheet, 1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 14pt; color: #000088;">
<tr>
<td width="100%" align="center"><img src="../img/logo/school-logo.png" width="100px" /></td>
</tr>
<tr>
<td width="100%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 28pt;">'.$university.'</h2><br/>
<h5 style="font-size: 9pt;">'.$university_address.'</h5>
</div>
</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);

$html = str_ireplace($search_array, $replace_array, $row_rspros['admletter']);

$mpdf->WriteHTML(utf8_encode($html));
$mpdf->Output('Admission Letter.pdf', 'I');

exit;