<?php 
require_once('../path.php');

if (!isset($_SESSION)) {
    session_start();
}


$auth_users = "1,2,3,4,5,6";
check_auth($auth_users, $site_root);


$tot_pass = 0;
$tot_fail = 0;
$highest_scr = '';
$lowest_scr = '';
$scores = [];

function getRmk($val, $tscore, $psmk){
    $result = 'FAIL';
    if (!is_numeric($tscore)) {
        $result = 'TF';
    } else if ($val >= $psmk){        
        $result = 'PASS';
    } 
    return $result;
}

$query2 = sprintf("SELECT sesname, sesid "
                . "FROM session "
                . "WHERE sesid = %s", 
                GetSQLValueString($_POST['sesid'], "text"));
$session = mysql_query($query2, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);

$deptid = $_POST['deptid'];
$filter1 = '';
$filter2 = '';
if(isset($_POST['progid'])) {
    $prog_query = sprintf("SELECT deptid "
                . "FROM programme "
                . "WHERE progid = %s ", 
                GetSQLValueString($_POST['progid'], "int"));
    $prog = mysql_query($prog_query, $tams) or die(mysql_error());
    $row_prog = mysql_fetch_assoc($prog);

    $deptid = $row_prog['deptid'];
    $filter1 = 'and progid = '. GetSQLValueString($_POST['progid'], "text");
    $filter2 = 'and st.progid = '. GetSQLValueString($_POST['progid'], "text");
}

$query1 = sprintf("SELECT c.colid "
                . "FROM department d, college c "
                . "WHERE  d.colid = c.colid "
                . "AND d.deptid = %s ", 
                GetSQLValueString($deptid, "text"));
$dept = mysql_query($query1, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);


$query1 = sprintf("SELECT csname, c.status, c.unit, d.status as 'dstatus', d.unit as 'dunit' "
                . "FROM course c "
                . "LEFT JOIN department_course d ON c.csid = d.csid and c.deptid = d.deptid and c.deptid = %s %s "
                . "WHERE c.csid = %s", 
                GetSQLValueString($deptid, "text"), 
                $filter1, 
                GetSQLValueString($_POST['csid'], "text"));
$crs = mysql_query($query1, $tams) or die(mysql_error());
$row_crs = mysql_fetch_assoc($crs);

$status = ($row_crs['dstatus'] != NULL)? $row_crs['dstatus']: $row_crs['status'];
$unit = ($row_crs['dunit'] != NULL && $row_crs['dunit'] != 0)? $row_crs['dunit']: $row_crs['unit'];

$query3 = sprintf("SELECT passmark "
                . "FROM grading "
                . "WHERE sesid = %s "
                . "AND colid = %s LIMIT 1",
                GetSQLValueString($_POST['sesid'], "text"),
                GetSQLValueString($row_dept['colid'], "text"));
$grading = mysql_query($query3, $tams) or die(mysql_error());
$row_grading = mysql_fetch_assoc($grading);

if (isset($_POST['MM_Insert']) && ($_POST['MM_Insert'] == 'form1')) {
    
    $query = sprintf("SELECT DISTINCT rs.*, st.fname, st.lname,st.mname, p.progname, s.sesname, st.sex "
                    . "FROM result rs, student st, programme p, schfee_transactions sf, session s, teaching t "
                    . "WHERE rs.stdid = st.stdid "
                    . "AND st.progid = p.progid "
                    . "AND rs.sesid = s.sesid "
                    . "AND rs.csid = %s "
                    . "AND s.sesid= %s "
                    . "AND rs.stdid = sf.matric_no "
                    . "AND sf.status = 'APPROVED' "
                    . "AND rs.sesid = sf.sesid "
                    . "AND t.csid = rs.csid "
                    . "AND t.sesid = rs.sesid "
                    //. "AND t.accepted = 'yes' "
                    . "%s "
                    . "ORDER BY rs.stdid ASC",
                    GetSQLValueString($_POST['csid'], "text"), 
                    GetSQLValueString($_POST['sesid'], "text"),
                    $filter2); 
    $result = mysql_query($query, $tams) or die(mysql_error()); 
    $row_result = mysql_fetch_assoc($result);
    $totalRows_result = mysql_num_rows($result); 
   

     do{
          
            $tot_scr = $row_result['tscore'] + $row_result['escore'];
            if($tot_scr >= $row_grading['passmark'] ){
                $tot_pass = $tot_pass + 1;                
             }else{
                $tot_fail = $tot_fail + 1;                 
             } 
            
            array_push($scores, $tot_scr);
            
        }while($row_result = mysql_fetch_assoc($result));
        
        $pcent1 = $tot_pass * 100 / $totalRows_result;
        $pcent2 = $tot_fail * 100 / $totalRows_result;
        
        $highest_scr = max($scores);
        $lowest_scr = min($scores);
                
        mysql_data_seek($result, 0);
       $row_result = mysql_fetch_assoc($result);
        
  
}

include("../mpdf/mpdf.php");
$mpdf = new mPDF('c', 'A4', '', '', 15, 15, 80, 15, 5, 45);
$stylesheet = file_get_contents('../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="90%" align="center"><img src="../img/logo/school-logo.png" width="90px" height="90px"/></td>
</tr>
<tr>
<td width="90%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 25pt">' . $university . '</h2>
<h5 style="font-size: 9pt">' . $university_address . '</h5><br /><br /></div>
</td>
</tr>
</table>
<p style="text-align:center; font-size: 15pt; margin-bottom: 10px"><strong>' . $row_session['sesname'] . ' &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; EXAM RESULT &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;' . $_POST['csid'] . ' ('. $unit.substr($status, 0, 1).') </strong></p>
<p style="text-align:center; font-size: 15pt; margin-bottom: 10px"><strong>' . $row_crs['csname'].' </strong></p>
<p style="text-align:center; font-size: 11pt; margin-bottom: 5px">' .strtoupper($row_dept['deptname']) . ' </p> <br />';

$mpdf->SetHTMLHeader($header);
$html = '
        <div style="text-align:center; width:100%; font-size: 20pt">
        <table class="table table-bordered table-condensed table-striped table-hover">
        <tr>
            <td>Total Student : ' . $totalRows_result . '</td>
            <td>Total Pass : ' . $tot_pass . ' - (' . number_format($pcent1, 2) . '%)' . '</td>
            <td>Total Fail : ' . $tot_fail . ' - (' . number_format($pcent2, 2) . ' %)' . '</td>
            <td>Highest Score : ' . $highest_scr . ' </td>
            <td>Lowest Score : ' . $lowest_scr . '</td>
        </tr>
        </table>
        <table class="table table-bordered table-condensed table-striped table-hover">
                      <thead>
                          <tr>
                              <th width="50">S/N</th>
                              <th  width="100">Matric</th>
                              <th>Full Name</th>
                              <th width="60" align="center">Sex</th>
                              <th width="60" align="center">C.A</th>
                              <th width="60" align="center">Exam</th>
                              <th width="60" align="center">Total</th>
                              <th width="70">Remark</th>
                          </tr>
                      </thead>
                      <tbody >';
if ($totalRows_result > 0) {


    $i = 1;
    do {
        $tot = $row_result['tscore'] + $row_result['escore'];
        $html .= '<tr>  
                                            <td>' . $i++ . '</td>
                                            <td>' . $row_result['stdid'] . '</td>
                                            <td>' . strtoupper($row_result['lname']) . ',  ' . ucfirst(strtolower($row_result['fname'])) .' '. ucfirst(strtolower($row_result['mname'])) .'</td>
                                            <td align="center">' . $row_result['sex'] . '</td>
                                            <td align="center">' . $row_result['tscore'] . '</td>    
                                            <td align="center">' . $row_result['escore'] . '</td> 
                                            <td align="center">' . $tot . '</td> 
                                            <td>' . getRmk($tot, $row_result['tscore'], $row_grading['passmark']) . '</td>    
                                        </tr>';
    }
    while ($row_result = mysql_fetch_assoc($result));
}
else {
    $html .='<tr><td colspan="7" align="center"><p style="color: red"> No Result Available for the Query you selected </p></td></tr>';
}

 $html .='</tbody>
                    </table>

    </div>';


$mpdf->WriteHTML($html);
$mpdf->Output('' . $_POST['csid'] . '_exam_result' . '.pdf', 'I');

exit;