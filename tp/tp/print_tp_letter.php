<?php 
if (!isset($_SESSION)) {
  session_start();
}


require_once('../path.php');


$auth_users = "1,11,20,21,22,23,24";
//check_auth($auth_users, $site_root);


include("../mpdf/mpdf.php");
$mpdf = new mPDF('c','A4','','',10,10,14,15,5,5); 

$stylesheet = file_get_contents('../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

//$mpdf->SetHTMLHeader($header);
              
$stdid = ($_SESSION['MM_Username']);

$query_student = sprintf("SELECT s.*, progname, schname, p.deptid, deptname, d.colid, colname "
        . "FROM student s, programme p, department d, college c, tp_student tps, tp_sch  "
        . "WHERE s.progid = p.progid "
        . "AND p.deptid = d.deptid "
        . "AND d.colid = c.colid "
        . "AND s.stdid = tps.stdid "
        . "AND tp_sch.id = tps.tpschid "
        . "AND tps.accepted = 'true' "
        . "AND s.stdid = %s", GetSQLValueString($stdid, "text"));
$student = mysql_query($query_student, $tams) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);

$fullname = $row_student['lname'].' '.$row_student['fname'];
$dept  = $row_student['progname'];
$programme  = $row_student['deptname'];
$phone  = $row_student['phone'];
$schname  = $row_student['schname'];

 if($totalRows_student == 0){
   $html = '<h1 align="center"> NO RECORD FOUND </h1>';
 }else {
   $html = '<p >
                <div align="center" style="margin: 0 auto"><img src="../img/logo/school-logo.png" width="100px" /></div>
                <h2 align="center">TAI SOLARIN UNIVERSITY OF EDUCATION, IJAGUN</h2>
            </p>
            <p align="center">
                <strong></strong>
            </p>

            <p align="center" style="margin: 15px">
                <strong>UNIVERSITY TEACHING PRACTICE BOARD</strong>
            </p>
                <p align="right">Date: 4<sup>th</sup> August, 2017</p>
            <p style="margin: 15px 0 ">The Principal/Head Teacher</p>
            <p>
                <b><u>'.$schname.'</u></b>
            </p>
            
            <p style="margin: 10px 0 ">
                <strong>Dear Sir/Madam,</strong>
            </p>
            <p align="center">
                <strong>
                    LETTER OF POSTING FOR 2017-2018 TEACHING PRACTICE EXERCISE
                </strong>
            </p>
            <p>
                This is to inform you that the Teaching Practice Exercise of the above
                named University commences on Monday 18<sup>th</sup> September 2017 and
                ends on Friday 15<sup>th</sup> December 2017 in line with the University
                Calendar for 2017/2018 Session.
            </p>
            <p>
                The student whose particulars appear below is to undergo his/her Teaching
                Practice Exercise in your school for the period specified above and should
                be specially monitored throughout the period of Teaching Practice.
            </p>
            <p>
                <strong><u> </u></strong>
                <u></u>
            </p>
            <p>
                Name: <b><u>'.$fullname.'</u></b>
            </p>
            <p>
                Matriculation Number: <b><u>'.$stdid.'</u></b>
            </p>
            <p>
                Department: <b><u>'.$dept.'</u></b>
            </p>
            <p>
                Combination: <b><u>'.$programme.'</u></b>
            </p>
            <p>
                Phone No: <b><u>'.$phone.'</u></b>
            </p>
            <p>
                <strong><u></u></strong>
            </p>
            <p>
                <strong>
                    The student should be made to experience all aspects of school life,
                    resume and close like your permanent teachers and should not be allowed
                    to leave school on flimsy excuses.
                </strong>
                <u></u>
            </p>
            <p>
                Thanking you for your continued cooperation
            </p>
            <p>
                <strong></strong>
            </p>
            <p>
                <strong> Prof. Olugbemiga O. Oworu </strong>
                <strong></strong>
            </p>
            <p>
                <em> <strong>Chairman </strong></em>
            </p>
            <p>
                <strong><em> </em></strong>
                (08037272990)
            </p>
            <p align="center" style="margin: 15px">
                <strong>
                    <u>
                        DO NOT DETACH THIS SLIP, IT MUST BE RETURNED WHOLE AFTER BEING
                        FILLED
                    </u>
                </strong>
            </p>
            <p align="center">
                <strong></strong>
            </p>
            <p align="center">
                <strong>LETTER OF REJECTION</strong>
            </p>
            <p>
            The Management of …………………………………………………………………………………………………    <strong>hereby</strong> <strong>reject</strong>
                ………………………………………………………………………………………….Matric No. ___________________
            Department …………………………………………………………………………….    <strong>Reason(s) for rejection</strong> is/are
            </p>
            <p>
                …………………………………………………………………………………………………………………………………………………………………
            </p>
            <p>
                <strong>Name/Signature of officer-in-charge</strong>
            </p>
            <p>
                <strong>Official School Stamp Phone Number:</strong>
            </p>
            <p align="center">
                <strong></strong>
            </p>
            <p>
                <strong></strong>
            </p>';
    }
   
$mpdf->WriteHTML($html);
$mpdf->Output('Teaching_Letter.pdf', 'I');

exit;