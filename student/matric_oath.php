<?php 
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "10";
check_auth($auth_users, $site_root);

 $jambregid = getSessionValue('uid'); 
$sesid = getSessionValue("admid");

mysql_query("SET SQL_BIG_SELECTS=1");

  $query_rspros = sprintf("SELECT * FROM student s 
                          JOIN prospective p ON s.jambregid = p.jambregid 
                          JOIN programme pg ON pg.progid = s.progid
                          JOIN admissions ad ON ad.admid = p.admid
                          JOIN session ses ON ses.sesid = ad.sesid
                          JOIN admission_type adt ON adt.typeid = ad.typeid
                          JOIN level_name lvl ON lvl.levelid = adt.entrylevel
                          JOIN schfee_transactions scf ON scf.matric_no = s.stdid 
                          WHERE scf.status='APPROVED' AND s.stdid =  %s ", 
                          GetSQLValueString($jambregid, "text")); 
$rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
$row_rspros = mysql_fetch_assoc($rspros);
$totalRows_rspros = mysql_num_rows($rspros);

$html = 'You have not paid school fees for this session!';
$session = getSessionValue("admname");
$fullname = $row_rspros['lname'].', '.$row_rspros['fname'].' '.$row_rspros['mname'];

//$ses_folder = explode('/', $session);
//echo $image_url = get_pics($jambregid, "../img/user/student/{$ses_folder[0]}", FALSE); exit();

$pictureId = $jambregid;

if ($row_rspros['jambregid'] != NULL) {
    $pictureId = strtoupper($row_rspros['jambregid']);
}


$img_dir = "../img/user/student";
 $image_url = get_pics($jambregid, $img_dir); 

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
            <h2 style="font-size: 22pt">'.$university.'</h2>
            <h3 style="font-size: 10pt">'.$university_address.'</h3>
        </div>
        <div style="text-align:center">
            <table>
                <tr>
                    <td width="85%" style="text-align:center">
                        <img src="../img/logo/school-logo.png" width="130px" height="130px" /><br/><br/>
                        <h3 style="font-size: 20pt"> MATRICULATION OATH</h3>
                    </td>
                    <td width="15%">
                        <img style="float:right; margin-top: 30px" src="'.$image_url.'" height="130px" width="130px"/>
                    </td>
                </tr>
            </table>
        </div>
        <p style="text-align: justify; font-size:14pt">
            I, '.strtoupper($fullname).', solemnly and sincerely promise and declare, that I will 
            pay due respect and obedience to the Vice Chancellor and other 
            Officers of the University and that I will faithfully observe 
            all regulations which may, from time to time, be issued by 
            them for the good order and governance of the University including 
            an order that I should make restitution for any damage done by 
            students to public property. I faithfully promise to refrain from 
            any act of violence and other actions calculated to disrupt the 
            work of the University or likely to bring the University into 
            disrepute. In addition, I accept that should I be found wanting 
            in character, the University reserves the right to withhold the
            award of a degree to me. So help me God.
        </p>
        <div style="font-size: 14pt">
            NAME: '.$fullname.' <br/><br/>
            MATRICULATION NO: '.$row_rspros['stdid'].' <br/><br/>
            COURSE: '.strtoupper($row_rspros['progname']).' <br/><br/>
            LEVEL : '.$row_rspros['levelname'].'<br/><br/>
            YEAR OF ADMISSION: '.$row_rspros['sesname'].' SESSION
                <br/><br/>
        </div>
        <div style="font-size: 12pt">
        <br/><br/>
            SIGNATURE:....................................&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            DATE: ......................................
        </div>
        <br/>
    ';
    
//    $html .= '<p style="margin-top:-5px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...................................'
//            . '..................................................................................................'
//            . '...................&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>';
//    //$html .= $content;
}
//echo $html;
include("../mpdf/mpdf.php");
$mpdf = new mPDF('c','A4','','',20,20,20,15,15,15); 

$mpdf->WriteHTML(utf8_encode($html));
$mpdf->Output('Clearance Certificate.pdf', 'I');

exit;