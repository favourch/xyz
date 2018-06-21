<?php
if (!isset($_SESSION)) {
   session_start();
}

require_once('../path.php');

//DEMO STUDENT 

$stdid = getSessionValue('uid');

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
 $query_student = sprintf("SELECT *, s.email as smail, st.stname FROM student s 
    JOIN passlist pl on s.stdid = pl.stdid 
    JOIN state st ON s.stid = st.stid
    JOIN accom_student_location asl ON s.stdid = asl.stdid 
    JOIN programme prg ON s.progid = prg.progid 
    JOIN department d ON prg.deptid = d.deptid 
    JOIN college c ON d.colid = c.colid AND s.stdid = %s",
    GetSQLValueString($stdid, "text"));  
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
                        <h3 style="font-size: 17pt">'.$row_student['colname'].'<br></h3>
                        <h4 style="font-size: 13pt">Department of '.$row_student['deptname'].'</h4>
                    </div>
                </td>
            </tr>
            </table>';
            
            
$mpdf->SetHTMLHeader($header);

$html .='
<div style="text-align:center; width:100%; font-size: 10pt">
    <div style="text-align:center; width:100%; font-size: 15pt">NYSC MOBILIZATION FORM</div>
</div>
<br/>
    <table class="table table-condensed table-bordered" style="font-size: 10pt">
        <tr>
            <th colspan="3" style="text-align:center">BIO DATA</th>
        </tr>
        <tr>
            <td width="50%">MATRIC No:  '.$row_student['stdid'].' </td>
            <td rowspan="4" colspan="2" style="text-align:right">  <img src="'.$image_url.'" width="100px" /> <br/> GENDER: &nbsp;&nbsp;&nbsp;'.GetSex($row_student['sex']).'</td>
        </tr>
        <tr>
            <td>JAMB REG. No:  '.$row_student['jambregid'].' </td>
        </tr>
        <tr>
            <td>SURNAME:  '.$row_student['lname'].' </td>
        </tr>
        <tr>
            <td>FIRST NAME:  '.$row_student['fname'].' </td>
        </tr>
        <tr>
            <td>MIDDLE NAME:  '.$row_student['mname'].' </td>
            <td colspan="2">MARITAL STATUS:  '.$row_student['maritalStatus'].' </td>
            
        </tr>
        <tr>
            <td>DATE of BIRTH.:  '.$row_student['dob'].' </td>
            <td colspan="2"> STATE OF ORIGIN: '.$row_student['stname'].' </td>
        </tr>
        <tr>
            <th colspan="3" style="text-align:center">ACADEMIC RECORD</th>
        </tr>
        <tr>
            <td>COLLEGE.:  '.$row_student['colname'].' </td>
            <td colspan="2">CLASS of DEGREE.:  '.$row_student['cdegree'].' </td>
        </tr>
        <tr>
            <td>DEPARTMENT.:  '.$row_student['deptname'].' </td>
            <td colspan="2">STUDY MODE:  FULL TIME </td>
        </tr>
        <tr>
            <td>COURSE of STUDY.:  '.$row_student['progname'].' </td>
            <td colspan="2">DATE of GRADUATION:  .......................... </td>
        </tr>
        <tr>
            <th colspan="3" style="text-align:center">CONTACT INFORMATION</th>
        </tr>
        <tr>
            <th style="text-align:center">Parent/Guardian </t>
            <th colspan="2" style="text-align:center">Address</th>
        </tr>
        <tr>
            <td>NAME.:  '.$row_student['sponsorname'].' </td>
            <td colspan="2">PHONE :  '.$row_student['phone'].' </td>
        </tr>
        <tr>
            <td>ADDRESS.:  '.$row_student['sponsoradrs'].' </td>
            <td colspan="2">E-MAIL :  '.$row_student['smail'].' </td>
        </tr>
        <tr>
            <td>PHONE NO..:  '.$row_student['sponsorphn'].' </td>
            <td colspan="2">HOME ADDRESS:  '.$row_student['addr'].' </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan="2">SCHOOL ADDRESS:  '.$row_student['building_address'].' </td>
        </tr>
        <tr>
            <td>Indicate if Military Personel or Member of any Law Enforcement:   </td>
            <td colspan="2"> '.$row_student['military_personel'].'</td>
        </tr>
    </table>
    <p style="font-size: 10pt">
        I confirm that the information provided by me above and the attached document are true and authentic <br/><br/> Applicants Signature and Date ............................................... <br />
    </p>
    
    ......................................................................................................................................................................<br/><br/>
        <h3 style="text-align:center">FOR OFFICIAL USE ONLY</h3><br/>
        
        
        
        <p style="font-size: 10pt"> 
        
            <table class="table  table-bordered">
                <tr>
                    <th width="20%"></th>
                    <th width="60%">Comment</th>
                    <th>Signature and Date</th>
                </tr>
                <tr>
                    <th>HOD &apos;s <br/><br/></th>
                    <td><p>&nbsp;</p></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <th>Accademic Affairs</th>
                    <td><p>&nbsp;</p></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <th> Desk Officer </th>
                    <td><p>&nbsp;</p></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <th> SAO&apos;s </th>
                    <td><p>&nbsp;</p></td>
                    <td>&nbsp;</td>
                </tr>
            </table>    
        </p> ';

 //echo utf8_encode($html);
 $mpdf->WriteHTML(utf8_encode($html));
 $mpdf->Output('clinic_response.pdf', 'I');
 exit();
?>