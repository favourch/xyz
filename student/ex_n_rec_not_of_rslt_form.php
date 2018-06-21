<?php
if (!isset($_SESSION)) {
   session_start();
}

require_once('../path.php');

//DEMO STUDENT 

$stdid = getSessionValue('uid');

$img_dir = "../img/user/student";
$image_url = get_pics($stdid, $img_dir);


if(isset($_GET['uid'])){
   $stdid =  $_GET['uid'];
}

$clr_transSQl = sprintf("SELECT * FROM `clearance_transactions` WHERE matric_no = %s AND status = 'APPROVED' ",GetSQLValueString($stdid, "text"));
$clr_transRS = mysql_query($clr_transSQl, $tams) or die(mysql_error());
$row_clr_trans = mysql_fetch_assoc($clr_transRS);
$totalRows_clr_trans = mysql_num_rows($clr_transRS);

if($totalRows_clr_trans < 1){
    header("Location: ../clearance/index.php");
    exit();
}

$img_dir = "../img/user/student";
$image_url = get_pics($stdid, $img_dir);

$array_student = []; 
 $query_student = sprintf("SELECT *, s.email as smail, s1.sesname AS adm_session, s2.sesid AS grad_session FROM student s JOIN passlist pl on s.stdid = pl.stdid JOIN session s1 ON s.sesid = s1.sesid  LEFT JOIN session s2 ON s.grad_sesid = s2.sesid  JOIN accom_student_location asl ON s.stdid = asl.stdid JOIN programme prg ON s.progid = prg.progid JOIN department d ON prg.deptid = d.deptid JOIN college c ON d.colid = c.colid AND s.stdid = %s",GetSQLValueString($stdid, "text"));  
$student = mysql_query($query_student, $tams) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);       


if($row_student['passlist'] == 'No'){
    header('Location: profile.php');
    exit();
}

if($row_student['nysc_form'] == 'no'){
    header('Location: nysc_form.php');
    exit();
}


include("../mpdf/mpdf.php");
$mpdf = new mPDF('c','A4','','',15,15,40,15,5,5); 
//$mpdf=new mPDF('c','A4','','',10,15,55,30,5,1);
$stylesheet = file_get_contents('../css/mpdfstylesheet.css');
$mpdf->WriteHTML($stylesheet, 1);

$html = '';

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
            <tr>
                <td width="15%" align="left"><img src="../img/logo/school-logo.png" width="100px" /></td>
                <td width="85%" align="center">
                    <div style="font-weight: bold;">
                        <h2 style="font-size: 20pt">'.$university.'</h2>
                        <h5 style="font-size: 9pt">'.$university_address.'</h5></div>
                        <h3 style="font-size: 17pt">ACADEMIC AFFAIRS DIVISION,<br></h3>
                        <h4 style="font-size: 13pt">EXAMS AND RECORDS OFFICE</h4>
                    </div>
                </td>
            </tr>
            </table>';
            
            
$mpdf->SetHTMLHeader($header);

$html .='
<div style="text-align:center; width:100%; font-size: 10pt">
    <div style="text-align:center; width:100%; font-size: 15pt">FORM FOR THE COLLECTION OF NOTIFICATION OF RESULTS</div>
</div>
<p style="font-size: 11pt">
    <table width="100%">
        <tr>
            <td width="80%"><b>FULL NAME<b>: '.$row_student['lname'].' '. $row_student['fname'].' '.$row_student['mname'].' </td>
            <td width="20%" rowspan="5">
                <img class="timeline-images" style="width: 200px; height: 220px;" src="'.$image_url.'" />
            </td>
        </tr>
        <tr>
            <td><b>COLLEGE</b>: '.$row_student['colname'].' </td>
        </tr>
        <tr>
            <td><b>DEPARTMENT</b>: '.$row_student['deptname'].' </td>
        </tr>
        <tr>
            <td><b>MATRIC NO.</b>: '.$row_student['stdid'].' </td>
        </tr>
        <tr>
            <td><b>PHONE.</b>: '.$row_student['stdid'].' </td>
        </tr>
        <tr>
            <td><b>ADDRESS.</b>: '.$row_student['addr'].' </td>
        </tr>
    </table>
</p>
<p>
    I ATTACH A COPY OF EACH OF THESE UNDERLISTED DOCUMENTS:
    <p>
        <ol>
            <li>100-400 School Fees Receipts</li>
            <li>Course Form</li>
            <li>Admission Letter</li>
            <li>100L Admission Clearance</li>
            <li>Online  O’Level Clearance from ICT (Ijagun) or Certificate </li>
        </ol>
    </p>
</p>
<h2>FOR OFFICIAL USE</h2>
<p>
    <ul>
        <li>SIGNATURE OF HEAD, EXAMS & RECORDS .........................................................................................................................</li>
        <li>OFFICER TO TREAT .........................................................................................................................</li>
        <li>DATE OF REQUEST .........................................................................................................................</li>
    </ul>
</p>


';

//echo utf8_encode($html);
$mpdf->WriteHTML(utf8_encode($html));
$mpdf->Output('Notification_of_result_request_form.pdf', 'I');
exit();
?>