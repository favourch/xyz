<?php

require_once('../../../path.php');
if (!isset($_SESSION)) {
    session_start();
}

$id = -1;
if($_GET['resultid']){
    $id = $_GET['resultid'];
}



        
if($_SESSION['olv_veri_who'] === 'pros')
{
     $olevel_SQL = sprintf("SELECT olvr.*, p.jambregid as userid, p.fname, p.lname, p.mname, prg.progname FROM olevel_veri_data olvr "
        . "JOIN verification v ON v.jambregid = olvr.jambregid "
        . "JOIN prospective p ON olvr.jambregid = p.jambregid "
        . "JOIN session s on v.sesid = s.sesid "
        . "JOIN programme prg ON p.progid1 = prg.progid "
        . "JOIN department d ON prg.deptid = d.deptid "
        . "JOIN college c ON d.colid = c.colid  WHERE olvr.id = %s ", GetSQLValueString($id, 'int'));
}
else
{
    $olevel_SQL = sprintf("SELECT olvr.*, p.stdid AS userid, p.fname, p.lname, p.mname, prg.progname FROM olevel_veri_data olvr "
        . "JOIN verification v ON v.stdid = olvr.stdid "
        . "JOIN student p ON olvr.stdid = p.stdid "
        . "JOIN session s on v.sesid = s.sesid "
        . "JOIN programme prg ON p.progid = prg.progid "
        . "JOIN department d ON prg.deptid = d.deptid "
        . "JOIN college c ON d.colid = c.colid  WHERE olvr.id = %s ", GetSQLValueString($id, 'int'));
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
    

        $html .= "<table border='0'width='100%'>
                        <tr>
                            <td>
                                <table width='100%'>
                                    <tr>
                                        <th>Student ID</th>
                                        <td>" . $olevel_data_row['userid'] . "</td>
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
                                        <th><img src='" . "../../logo/" . getExamLogo($olevel_data_row['exam_name']) . "' style='width: 120px; height: 120px;'></th>
                                        <th align='center' style='text-align: center; vertical-align: middle;' > <h2>" . getExamName($olevel_data_row['exam_name']) . "</h2></th>
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
                                                <img src='../../logo/ictsign.png' width='300px' height='120px'; /><br/>
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
                    </table>";
   
} else {
    $html .= "<table border='1' width='100%'>
                    <tbody>
                        <tr>
                            <td style='color:red'>NO Recod Available</td>
                        </tr>
                    </tbody>
                </table>";
}

$mpdf->WriteHTML($html);
$mpdf->Output('Olevel_Result.pdf', 'I');
die();
?>

