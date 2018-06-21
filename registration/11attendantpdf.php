<?php
require_once('../path.php');



$courseSQL = sprintf("SELECT cs.csid, cs.csname "
                    . "FROM course cs "
                    . "WHERE  cs.csid = %s", 
                    GetSQLValueString($_POST['course'], 'text'));
$courseRS = mysql_query($courseSQL, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($courseRS);
$totalRows_course = mysql_num_rows($courseRS);


$query_rssess = sprintf("SELECT * FROM `session` WHERE sesid = %s", GetSQLValueString($_POST['sesid'], 'int'));
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);



// Recordset to populate programme dropdown
$query_atten = $_POST['query'];
$attenRS = mysql_query($query_atten, $tams) or die(mysql_error());
$row_atten = mysql_fetch_assoc($attenRS);
$totalRows_prog = mysql_num_rows($attenRS);

?>
<?php 
//$university = 'TAI SOLARIN UNIVERSITY OF EDUCATION, IJAGUN, IJEBU-ODE';

include("../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4','','',10,10,50,15,5,5); 
$stylesheet = file_get_contents('../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);


$header = ' <table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
                <tr>
                    <td width="15%" align="left"><img src="../img/logo/school-logo.png" width="100px" /></td>
                    <td width="85%" align="center">
                        <div style="font-weight: bold;">
                            <h2 style="font-size: 20pt">'.$university.'</h2>
                            <h5 style="font-size: 9pt">'.$university_address.'</h5>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" width="85%" align="center">
                        <div style="font-weight: bold;">
                            <h4 style="font-size: 14pt"> Attendance Sheet For  ' . $row_rssess['sesname'] . ' </h4> 
                            <h6 style="font-size: 12pt"> ' . $row_course['csid'] . ' - ' . $row_course['csname'] . ' </h4>     
                        </div>
                    </td>
                </tr>
            </table>';

$mpdf->SetHTMLHeader($header);
        

          $html = '<table width="700" class="table table-bordered table-condensed">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="20%">MATRIC NO</th>
                                <th width="40%">FULL NAME</th>
                                <th width="5%">LEVEL</th>
                                <th width="15%">SIGN IN </th>
                                <th width="15%">SIGN OUT </th>
                            </tr>
                        </thead>
                        <tbody>';
          
                           $i = 1;
                            do{
                           
                  $html .= '<tr>
                                <td>'. $i++ .'</td>
                                <td>'.$row_atten['stdid'].'</td>
                                <td>'.$row_atten['lname'].' '.$row_atten['fname'].''.$row_atten['mname'].'</td>
                                <td>'.$row_atten['level'].'</td>
                                <td></td>
                                <td></td>
                            </tr>';  
                               
                            }while($row_atten = mysql_fetch_assoc($attenRS)); 
                  
              $html .= '</tbody>
                    </table>';


$mpdf->WriteHTML($html);

$mpdf->Output('Attendance.pdf', 'I');

exit;
?>

