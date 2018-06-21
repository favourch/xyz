<?php 
    require_once('../../../path.php');
if (!isset($_SESSION)) {
    session_start();
}
    
    
$and = "";
$colid = -1;


if (isset($_POST['cid']) && $_POST['cid'] != "") {
    $colid = $_POST['cid'];
    $and .= sprintf("AND c.colid = %s ", GetSQLValueString($colid, 'int'));
}

$sesid = -1;
if (isset($_POST['sid']) && $_POST['sid'] != "") {
    $sesid = $_POST['sid'];
    $and .= sprintf("AND s.sesid = %s ", GetSQLValueString($sesid, 'int'));
}

$progid = -1;
if (isset($_POST['pid']) && $_POST['pid'] != "") {
    $progid = $_POST['pid'];
    $and .= sprintf("AND prg.progid = %s ", GetSQLValueString($progid, 'int'));
}

$from = date('Y-m-d');
$to = date('Y-m-d');
if ((isset($_POST['from']) && $_POST['from'] != "" )|| (isset($_POST['to']) && $_POST['to'] != "")) {
    $from = $_POST['from'];
    $to = $_POST['to'];

    $and .= sprintf(" AND date_fetched BETWEEN CAST(%s AS DATE) AND CAST(%s AS DATE)", GetSQLValueString($from, 'text'), GetSQLValueString($to, 'text'));
}


if($_SESSION['olv_veri_who'] == 'stud'){
    $student_type = "Returning Students";
    
    $olevel_SQL = sprintf("SELECT olvr.*, p.fname, p.lname, p.mname, prg.progname FROM olevel_veri_data olvr "
                    . "JOIN verification v ON v.stdid = olvr.stdid "
                    . "JOIN student p ON olvr.stdid = p.stdid "
                    . "JOIN session s on v.sesid = s.sesid "
                    . "JOIN programme prg ON p.progid = prg.progid "
                    . "JOIN department d ON prg.deptid = d.deptid "
                    . "JOIN college c ON d.colid = c.colid %s "
                    . "AND olvr.status = 'use' "
                    . "AND olvr.approve = 'Yes' AND olvr.old = 'no' AND date_fetched IS NOT NULL  "
                    . "AND v.olevel_submit = 'TRUE' ORDER BY olvr.stdid ASC ", $and); 
    
}else{
    $student_type = "Prospective Students";
    $olevel_SQL = sprintf("SELECT olvr.*, p.fname, p.lname, p.mname, prg.progname,p.jambregid AS stdid "
                    . "FROM olevel_veri_data olvr "
                    . "JOIN verification v ON v.jambregid = olvr.jambregid "
                    . "JOIN prospective p ON olvr.jambregid = p.jambregid "
                    . "JOIN session s on v.sesid = s.sesid "
                    . "JOIN programme prg ON p.progid1 = prg.progid "
                    . "JOIN department d ON prg.deptid = d.deptid "
                    . "JOIN college c ON d.colid = c.colid %s "
                    . "AND olvr.status = 'use' "
                    . "AND olvr.approve = 'Yes' AND olvr.old = 'no' AND date_fetched IS NOT NULL  "
                    . "AND v.olevel_submit = 'TRUE' ORDER BY olvr.jambregid ASC ", $and); 
}




$olevel = mysql_query($olevel_SQL, $tams) or die(mysql_error());
$olevel_row_num = mysql_num_rows($olevel);
$olevel_data_row = mysql_fetch_assoc($olevel);


include("../../../mpdf/mpdf.php");
$mpdf = new mPDF('c', 'A4', '', '', 10, 10, 32, 15, 5, 5);

//$mpdf->allow_charset_conversion = true;
//$mpdf->charset_in = 'iso-8859-4';

$stylesheet = file_get_contents('../../../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

$html = "";

if ($olevel_data_row > 0) {
    do {

        $html .= "<table border='0' width='100%'>
                        <tr>
                            <td>
                                <table width='100%'>
                                    <tr>
                                        <th>Student ID</th>
                                        <td>" . $olevel_data_row['stdid'] . "</td>
                                        <th>Full Name</th>
                                        <td>" . $olevel_data_row['lname'] . " " . $olevel_data_row['fname'] . " " . $olevel_data_row['mname'] . "</td>
                                        <th>Programme</th>
                                        <td>" . $olevel_data_row['progname'] . "</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table width='100%'>
                                    <tr>
                                        <th align='left'><img src='" . "../../logo/" . getExamLogo($olevel_data_row['exam_name']) . "' style='width: 100px; height: 110px;'></th>
                                        <th align='left' style='text-align: center; vertical-align: middle' > <h2>" . getExamName($olevel_data_row['exam_name']) . "</h2></th>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table width='100%'>
                                    <tr>
                                        <td align='left'>" . htmlspecialchars_decode($olevel_data_row['result_table']) . "</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table width='100%'>
                                    <tr>
                                        <td>
                                            <p>
                                                <img src='../../logo/ictsign.png' width='300px' height='120px'/><br/>
                                                _____________________________<br/>
                                                TASUED ICT Center
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                         <td colspan='6' style='color:red'>NOTE! This result was fetched with our crawler technology from the " . $olevel_data_row['exam_name'] . " Result Site on " . $olevel_data_row['date_fetched'] . " </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table> <p>&nbsp;</p> <pagebreak /> ";
                    
    }while ($olevel_data_row = mysql_fetch_assoc($olevel));
    
    
    mysql_data_seek ( $olevel , 0 );
    
    $html .= "<table>
                <tr>
                    <th>#</th>
                    <th>User ID</th>
                    <th>Full Name</th>
                    <th>Programme</th>
                    <th>Exam No.</th>
                    <th>Exam.</th>
                    <th>Exam Year</th>
                    <th>Date Fetched</th>
                </tr>";
            $i = 0; 
              do{
                 
                $html .= "  <tr>
                                <td>".$i++."</td>
                                <td>".$olevel_data_row['stdid'] ."</td>
                                <td>".$olevel_data_row['fname'] . " " . $olevel_data_row['lname'] . " " . $olevel_data_row['mname'] ."</td>
                                <td>".$olevel_data_row['progname']."</td>
                                <td>".$olevel_data_row['exam_no']."</td> 
                                <td>".$olevel_data_row['exam_name']."</td>
                                <td>".$olevel_data_row['date_fetched']."</td>
                            </tr>";  
              }while($olevel_data_row = mysql_fetch_assoc($olevel)); 
    $html.="</table>";
    
} else {
    $html .= "<table border='1' width='100%'>
                    <tbody>
                        <tr>
                            <td style='color:red'>NO Recod Available</td>
                        </tr>
                    </tbody>
                </table>";
}

//echo $html;
$mpdf->WriteHTML($html);
$mpdf->Output('Olevel_Result.pdf', 'D');
die();
?>

