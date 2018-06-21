<?php 
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "11";
check_auth($auth_users, $site_root);

$jambregid = getSessionValue('uid');
$sesid = getSessionValue("admid");

 $query_rspros = sprintf("SELECT p.*, s.matric_no, st.stname, pr.progname, d.deptname, c.coltitle, l.levelname "
        . "FROM prospective p "
        . "JOIN state st ON p.stid = st.stid "
        . "JOIN programme pr ON p.progoffered = pr.progid "
        . "JOIN department d ON pr.deptid = d.deptid "
        . "JOIN college c ON d.colid = c.colid "
        . "JOIN admissions a ON p.admid = a.admid "
        . "JOIN admission_type at ON a.typeid = at.typeid "
        . "JOIN level_name l ON at.entrylevel = l.levelid "
        . "JOIN schfee_transactions s ON p.jambregid = s.can_no "
        . "JOIN payschedule ps ON s.scheduleid = ps.scheduleid "
        . "WHERE p.jambregid = %s AND ps.sesid = %s AND s.status = 'APPROVED'", 
        GetSQLValueString($jambregid, "text"), 
        GetSQLValueString($sesid, "int"));
$rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
$row_rspros = mysql_fetch_assoc($rspros);
$totalRows_rspros = mysql_num_rows($rspros);

$html = 'You have not paid school fees for this session!';
$session = getSessionValue("admname");
$fullname = $row_rspros['lname'].', '.$row_rspros['fname'].' '.$row_rspros['mname'];

$ses_folder = explode('/', $session);
$image_url = get_pics($jambregid, "../img/user/prospective/{$ses_folder[0]}", FALSE);

if($totalRows_rspros > 0) {
    $date = $row_rspros['printdate'];
    
    if($date == NULL || $date == '') {
        $date = date("Y-m-d");
        $updateSql = sprintf("UPDATE prospective SET printdate = %s "
                            . "WHERE jambregid = %s",
                            GetSQLValueString($date, "date"),
                            GetSQLValueString($jambregid, "text"));
        $rsUpdate = mysql_query($updateSql, $tams) or die(mysql_error());

    }
    
    $html = $content = ' 
        <div style="text-align: center">  
            <h4>TAI SOLARIN UNIVERSITY OF EDUCATION, IJAGUN, IJEBU-ODE</h4>
            <h4>ADMISSION OFFICE</h4>
        </div>
        <div>
            DEAN<br/>COLLEGE: '.$row_rspros['coltitle'].'
            <img style="float:right; margin-top: -50px" src="'.$image_url.'" height="100px" width="100px"/>
        </div>
        <h4 style="margin-top: -10px">'.$session.' CLEARANCE CERTIFICATE</h4>
        <p style="text-align: justify">
        This is to certify that the candidate whose particulars are given below, has duly accepted the provisional 
        offer of admission and still undergoing documentation process at the Admission Office.</p>
        <div>NAME: '.$fullname.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        JAMB REG. NO: '.$row_rspros['jambregid'].'</div>
        <div>DEPT: '.$row_rspros['deptname'].' <br/>COURSE: '.$row_rspros['progname'].' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        LEVEL: '.$row_rspros['levelname'].'</div>
        <div>MATRIC NO: '.$row_rspros['matric_no'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;STATE: '.$row_rspros['stname'].'&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        DATE: '.date("d-m-Y",strtotime($date)).'</div>
        <p style="text-align: justify">
        Please allow him/her to undergo the documentation process at the Department level as well as the course 
        registration formalities.</p>
        <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        Cleared by&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        Ratified by HOD</p>
        <p style="margin-bottom:-10px">&nbsp;&nbsp;&nbsp;&nbsp;..............................................&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;..............................................</p>
        <p style="margin-top:-5px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;FOR REGISTRAR&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;NAME, SIGN & DATE</p>
    ';
    
    $html .= '<p style="margin-top:-5px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...................................'
            . '..................................................................................................'
            . '...................&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>';
    $html .= $content;
}
//echo $html;
include("../mpdf/mpdf.php");
$mpdf = new mPDF(); 

$mpdf->WriteHTML(utf8_encode($html));
$mpdf->Output('Clearance Certificate.pdf', 'I');

exit;