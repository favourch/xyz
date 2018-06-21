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
    <div style="text-align:center; width:100%; font-size: 15pt">CLEARANCE FORM FOR DEGREE RESULT</div>
</div>
<h2><u>SECTION A:</u></h2>
<p>
    <b>NAME OF STUDENT </b>: '.$row_student['lname'].' '.$row_student['fname'].' '.$row_student['mname'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>MATRIC NUMBER</b> : '. $row_student['stdid']. '<br/><br/>
    <b>COLLEGE </b>: '.$row_student['colname'].' &nbsp;&nbsp;&nbsp;<b>DEPARTMENT</b> : '. $row_student['deptname']. '&nbsp;&nbsp;&nbsp;<b>UNIT</b>: '.$row_student['progname'].'<br/><br/>
    <b>TELEPHOPNE </b>: '.$row_student['phone'].' &nbsp;&nbsp;&nbsp;<b>PERMANENT HOME ADDRESS</b> : '. $row_student['addr']. '<br/><br/>
    <b>YEAR OF ADMISSION </b>: '.$row_student['adm_session'].' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>YEAR OF GRADUATION</b> : '. $row_student['grad_session']. '<br/><br/>
    <b>SIGNATURE OF STUDENT </b>:..................................................... &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>DATE </b> :...........................................<br/><br/>
</p>
<p>
    The above named student is deemed to have satisfied the university&apos;s requirements for the award of a degree except where fresh indications to the contrary emerge. Grateful if you could please indicate below if he/she is not indebted to the University through your Unit.
</p>

<p>
    <strong> </strong>
</p>
<p>
    <table width="730" cellspacing="0" cellpadding="0" border="1">
    <tbody>
        <tr>
            <td width="62" valign="top">
                <p>
                    <strong>S/NO</strong>
                </p>
            </td>
            <td width="250" valign="top">
                <p>
                    <strong>DEPARTMENT</strong>
                </p>
            </td>
            <td colspan="2" width="163" valign="top">
                <p>
                    <strong>COMMENT</strong>
                </p>
            </td>
            <td width="138" valign="top">
                <p>
                    <strong>SIGNATURE AND DATE</strong>
                </p>
            </td>
            <td width="117" valign="top">
                <p>
                    <strong>REMARKS</strong>
                </p>
            </td>
        </tr>
        <tr>
            <td rowspan="6" width="62" valign="top">
                <p>
                    1
                </p>
            </td>
            <td rowspan="6" width="250" valign="top">
                <p>
                    Bursary(<strong>100LEVEL-600LEVEL)</strong>
                </p>
            </td>
            <td width="57" valign="top">
                <p>
                    <strong>100L</strong>
                </p>
            </td>
            <td width="106" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
            <td width="138" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
            <td rowspan="6" width="117" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
        </tr>
        <tr>
            <td width="57" valign="top">
                <p>
                    <strong>200L</strong>
                </p>
            </td>
            <td width="106" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
            <td width="138" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
        </tr>
        <tr>
            <td width="57" valign="top">
                <p>
                    <strong>300L</strong>
                </p>
            </td>
            <td width="106" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
            <td width="138" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
        </tr>
        <tr>
            <td width="57" valign="top">
                <p>
                    <strong>400L</strong>
                </p>
            </td>
            <td width="106" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
            <td width="138" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
        </tr>
        <tr>
            <td width="57" valign="top">
                <p>
                    <strong>500L</strong>
                </p>
            </td>
            <td width="106" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
            <td width="138" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
        </tr>
        <tr>
            <td width="57" valign="top">
                <p>
                    <strong>600L</strong>
                </p>
            </td>
            <td width="106" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
            <td width="138" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
        </tr>
        <tr>
            <td width="62" valign="top">
                <p>
                    2
                </p>
            </td>
            <td width="250" valign="top">
                <p>
                    University Library
                </p>
            </td>
            <td colspan="2" width="163" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
            <td width="138" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
            <td width="117" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
        </tr>
        <tr>
            <td width="62" valign="top">
                <p>
                    3
                </p>
            </td>
            <td width="250" valign="top">
                <p>
                    Admissions Office
                </p>
            </td>
            <td colspan="2" width="163" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
            <td width="138" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
            <td width="117" valign="top">
                <p>
                    <strong> </strong>
                </p>
            </td>
        </tr>
    </tbody>
</table>
</p>

<p>
    <strong>4 Student&apos;s Affairs Office</strong>
</p>
<p>
    4a Confirm if the student has any Disciplinary issue with University YES( ) &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; NO( )
</p>
<p>
    4b has the student been cleared by Senate YES( ) &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; NO( )
</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="730">
    <tr>
        <td>
            <p>
                ...............................................................................<br/></br/>
                <strong>Name, Signature and Stamp of Officer (EMC) Date</strong>
            </p>
        </td>
        <td>
            <p>
                ...............................................................................<br/></br/>
                <strong>Name, Signature and Stamp of Officer (SDC) Date</strong>
            </p>
        </td>
    </tr>
</table>

<pagebreak>

<h2 align="center"><u>SECTION B:</u></h2>

<p align="center">
    <strong>
        TO BE PRESENTED FOR CLEARANCE AT THE DEPARTMENT AND OTHER SEGMENTS OF
        THE UNIVERSITY.
    </strong>
</p>
<p>
    Countersigning Officer should state whether the student has fulfilled the necessary requirements to enable him/her graduate.
</p>

<p>
    <ol>
        <li>
            This student graduated with:..........................................................................class of degree.<br/><br/><br/>
            <p align="center">
                .........................................................................................................................<br/><br/>
            Name, Signature  and Stamp of HOD &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; & Date 
            </p>
           
        </li>
        
        <li>
            <b>CENTRE FOR VOCATIONAL SKILLS</b> (Please indicate the Vocational Course  PASSED and sign with VOS stamp)<br/><br/><br/>
            VOS COURSE PASSED ...................................................................
            <p align="center">
                .............................................................................................................................<br/><br/>
            Name, Signature  and Stamp of HOD &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; & Date
            </p>
        </li>
        
        <li>
            <b>UNIVERSITY SENATE</b>
            <p>Any Disciplinary Matter?           YES (  )   NO(  ) <br/><br/>
                IF YES, Please state nature &date  .........................................................................................................................................................................................<br/><br/>
            </p>
            <p>&nbsp;</p>
            <p align="center">
                .............................................................................................................................<br/>
                    Name, Signature  and Stamp of HOD &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; & Date
            </p>
        </li>
    </ol>
</p>

<p>
    <h2><u>For Exams and Records Unit Use</u></h2>
    <p>&nbsp;</p>
    
    Cleanrce checked by &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ............................................................................................<br/>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Name of liaison Officer
</p>
<p>&nbsp;</p>
<p align="center">
    .............................................................................................................................<br/>
        Signature of liaison Officer &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; & Date
</p>

';

 //echo utf8_encode($html);
 $mpdf->WriteHTML(utf8_encode($html));
 $mpdf->Output('clinic_response.pdf', 'I');
 exit();
?>