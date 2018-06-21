<?php 
if (!isset($_SESSION)) {
  session_start();
}
require_once('../path.php');

$auth_users = "10";
check_auth($auth_users, $site_root);

$acl = array(2,3);

$query_rssess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);


$query = '';
if(getAccess() == 3) {
    $query = "AND p.deptid = ".  GetSQLValueString(getSessionValue('did'), 'int');
}

if(getAccess() == 2) {
    $query = "AND d.colid = ".  GetSQLValueString(getSessionValue('cid'), 'int');
}

// Recordset to populate programme dropdown
$query_prog = sprintf("SELECT p.progid, p.progname, d.colid, p.deptid "
                        . "FROM programme p, department d "
                        . "WHERE d.deptid = p.deptid %s", 
                        GetSQLValueString($query, "defined", $query));
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$level = 1;
$prg = $row_prog['progid'];
$sid = -1;

if(isset($_GET['lvl'])) {
    $level = $_GET['lvl'];
}

if(isset($_GET['pid'])) {
    $prg = $_GET['pid'];
}

if (isset($_GET['sid'])) {
  $sid = $_GET['sid'];
}

$colname_stud = "-1";
if (isset($_GET['stid'])) {
  $colname_stud = $_GET['stid'];
}

if (getAccess() < 7 && isset($_GET['stid'])) {
  $colname_stud = $_GET['stid'];
}

if (getAccess() < 7 && !isset($_GET['stid'])) {
    $query_std = sprintf("SELECT s.stdid, s.progid, colid, p.deptid, fname, lname, level "
                            . "FROM student s, programme p, department d "
                            . "WHERE s.progid = p.progid AND d.deptid = p.deptid "
                            . "AND s.progid = %s AND s.level = %s", 
                            GetSQLValueString($prg, "text"), 
                            GetSQLValueString($level, "text"));
    $std = mysql_query($query_std, $tams) or die(mysql_error());
    $row_std = mysql_fetch_assoc($std);
    $totalRows_std = mysql_num_rows($std);
    
    if($totalRows_std > 0) {
        $colname_stud = $row_std['stdid'];
    }
}

$query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, r.level, s.progid, p.progname, d.colid, d.deptname, s.curid "
                    . "FROM student s, programme p, department d, registration r "
                    . "WHERE s.progid = p.progid "
                    . "AND p.deptid = d.deptid "
                    . "AND r.stdid = s.stdid "
                    . "AND s.stdid = %s "
                    . "AND r.sesid = %s ", 
                    GetSQLValueString($colname_stud, "text"), 
                    GetSQLValueString($sid, "text"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

 $query_hod = sprintf("SELECT title, lname, fname "
                        . "FROM lecturer l, programme p, department d "
                        . "WHERE d.deptid = p.deptid "
                        . "AND l.deptid = p.deptid AND access='3' "
                        . "AND p.progid=%s",
                        GetSQLValueString($row_stud['progid'], "int"));
$hod = mysql_query($query_hod, $tams) or die(mysql_error());
$row_hod = mysql_fetch_assoc($hod);
$totalRows_hod = mysql_num_rows($hod);

$query_dean = sprintf("SELECT title, lname, fname "
                        . "FROM lecturer l, department d "
                        . "WHERE d.deptid = l.deptid "
                        . "AND access='2' AND d.colid=%s",
                        GetSQLValueString($row_stud['colid'], "int"));
$dean = mysql_query($query_dean, $tams) or die(mysql_error());
$row_dean = mysql_fetch_assoc($dean);
$totalRows_dean = mysql_num_rows($dean);

if ( getAccess() < 10 ) {
    $prg = ($row_stud['progid'] != null)? $row_stud['progid']: $prg;
    $level = ($row_stud['level'] != null)? $row_stud['level']: $level;
}

$query_studs = sprintf("SELECT stdid, fname, lname "
                    . "FROM student "
                    . "WHERE level = %s "
                    . "AND progid = %s"
                        , GetSQLValueString($level, "int")
                        , GetSQLValueString($prg, "int"));
$studs = mysql_query($query_studs, $tams) or die(mysql_error());
$row_studs = mysql_fetch_assoc($studs);
$total = $totalRows_studs = mysql_num_rows($studs);

$query_regsess = sprintf("SELECT s.* FROM session s, registration r "
                            . "WHERE r.sesid = s.sesid "
                            . "AND r.status=%s "
                            . "AND r.stdid=%s "
                            . "ORDER BY sesname DESC", 
                            GetSQLValueString("Registered", "text"), 
                            GetSQLValueString($colname_stud, "text"));
$regsess = mysql_query($query_regsess, $tams) or die(mysql_error());
$row_regsess = mysql_fetch_assoc($regsess);
$totalRows_regsess = mysql_num_rows($regsess);

$colname_course = "-1";
if (isset($colname_stud)) {
  $colname_course = $colname_stud;
}

$colname1_course = "-1";
if (isset($row_regsess['sesid'])) {
  $colname1_course = $row_regsess['sesid'];
}

if (isset($_GET['sid'])) {
  $colname1_course = $_GET['sid'];
}

$query_cursess = sprintf("SELECT * FROM `session` WHERE sesid=%s", GetSQLValueString($colname1_course, "int"));
$cursess = mysql_query($query_cursess, $tams) or die(mysql_error());
$row_cursess = mysql_fetch_assoc($cursess);
$totalRows_cursess = mysql_num_rows($cursess);

$colname2_course = "-1";
if (isset($row_stud['progid'])) {
  $colname2_course = $row_stud['progid'];
}

$query_course1 = sprintf("SELECT distinct(r.csid), c.semester, c.csname, dc.status, dc.unit "
                        . "FROM course_reg r, course c, department_course dc "
                        . "WHERE r.cleared = 'TRUE' "
                        . "AND c.csid = r.csid AND r.csid = dc.csid AND c.semester='F' "
                        . "AND r.stdid = %s AND c.curid = dc.curid AND dc.curid = %s "
                        . "AND dc.progid = %s "
                        . "AND r.sesid = %s "
                        
                        . "UNION "
                        
                        . "SELECT distinct(r.csid), c.semester, c.csname, c.status, c.unit "
                        . "FROM course_reg r, course c "
                        . "WHERE r.cleared = 'TRUE' "
                        . "AND c.csid = r.csid AND c.semester='F' "
                        . "AND r.stdid = %s AND c.curid = %s "
                        . "AND r.sesid = %s "
                        . "AND r.csid NOT IN "
                        
                        . "(SELECT r.csid "
                        . "FROM course_reg r, course c, department_course dc "
                        . "WHERE r.cleared = 'TRUE' "
                        . "AND c.csid = r.csid AND r.csid = dc.csid AND c.semester='F' "
                        . "AND r.stdid = %s AND c.curid = dc.curid AND dc.curid = %s "
                        . "AND dc.progid = %s "
                        . "AND r.sesid = %s ) ",
    
                        GetSQLValueString($colname_stud, "text"), 
                        GetSQLValueString($row_stud['curid'], "int"),
                        GetSQLValueString(getSessionValue('pid'), "text"),
                        GetSQLValueString($colname1_course, "int"),
                        
                        GetSQLValueString($colname_stud, "text"), 
                        GetSQLValueString($row_stud['curid'], "int"),
                        GetSQLValueString($colname1_course, "int"),
                        
                        GetSQLValueString($colname_stud, "text"), 
                        GetSQLValueString($row_stud['curid'], "int"),
                        GetSQLValueString(getSessionValue('pid'), "text"),
                        GetSQLValueString($colname1_course, "int")); 
$course1 = mysql_query($query_course1, $tams) or die(mysql_error());
$row_course1 = mysql_fetch_assoc($course1);
$totalRows_course1 = mysql_num_rows($course1);

// second semester courses
$query_course2 = sprintf("SELECT distinct(r.csid), c.semester, c.csname, dc.status, dc.unit "
                        . "FROM course_reg r, course c, department_course dc "
                        . "WHERE r.cleared = 'TRUE' "
                        . "AND c.csid = r.csid AND r.csid = dc.csid AND c.semester='S' "
                        . "AND r.stdid = %s AND c.curid = dc.curid AND dc.curid = %s "
                        . "AND dc.progid = %s "
                        . "AND r.sesid = %s "
                        
                        . "UNION "
                        
                        . "SELECT distinct(r.csid), c.semester, c.csname, c.status, c.unit "
                        . "FROM course_reg r, course c "
                        . "WHERE r.cleared = 'TRUE' "
                        . "AND c.csid = r.csid AND c.semester='S' "
                        . "AND r.stdid = %s AND c.curid = %s "
                        . "AND r.sesid = %s "
                        . "AND r.csid NOT IN "
                        
                        . "(SELECT r.csid "
                        . "FROM course_reg r, course c, department_course dc "
                        . "WHERE r.cleared = 'TRUE' "
                        . "AND c.csid = r.csid AND r.csid = dc.csid AND c.semester='S' "
                        . "AND r.stdid = %s AND c.curid = dc.curid AND dc.curid = %s "
                        . "AND dc.progid = %s "
                        . "AND r.sesid = %s ) ",
                        
                         
                        GetSQLValueString($colname_stud, "text"), 
                        GetSQLValueString($row_stud['curid'], "int"),
                        GetSQLValueString(getSessionValue('pid'), "text"),
                        GetSQLValueString($colname1_course, "int"),
                        
                        GetSQLValueString($colname_stud, "text"), 
                        GetSQLValueString($row_stud['curid'], "int"),
                        GetSQLValueString($colname1_course, "int"),
                        
                        GetSQLValueString($colname_stud, "text"), 
                        GetSQLValueString($row_stud['curid'], "int"),
                        GetSQLValueString(getSessionValue('pid'), "text"),
                        GetSQLValueString($colname1_course, "int"));
                        
$course2 = mysql_query($query_course2, $tams) or die(mysql_error());
$row_course2 = mysql_fetch_assoc($course2);
$totalRows_course2 = mysql_num_rows($course2);



$query_reg = sprintf("SELECT r.stdid "
                    . "FROM registration r, student s "
                    . "WHERE s.stdid = r.stdid "
                    . "AND r.sesid = %s "
                    . "AND s.level = %s "
                    . "AND s.progid = %s "
                    . "AND course = 'Registered'",  
                    GetSQLValueString($row_rssess['sesid'], "int"), 
                    GetSQLValueString($level, "int"), 
                    GetSQLValueString($prg, "int"));
$reg = mysql_query($query_reg, $tams) or die(mysql_error());
$row_reg = mysql_fetch_assoc($reg);
$totalReg = $totalRows_reg = mysql_num_rows($reg); 

$query_appr = sprintf("SELECT r.stdid "
                    . "FROM registration r, student s "
                    . "WHERE s.stdid = r.stdid "
                    . "AND r.sesid = %s "
                    . "AND s.level = %s "
                    . "AND s.progid = %s "
                    . "AND approved = 'TRUE'",  
                    GetSQLValueString($row_rssess['sesid'], "int"), 
                    GetSQLValueString($level, "int"), 
                    GetSQLValueString($prg, "int"));
$appr = mysql_query($query_appr, $tams) or die(mysql_error());
$row_appr = mysql_fetch_assoc($appr);
$totalApprd = $totalRows_appr = mysql_num_rows($appr);

$approve = false;
$query_approved = sprintf("SELECT * "
        . "FROM registration "
        . "WHERE stdid = %s "
        . "AND sesid = %s "
        . "AND status = 'Registered'",
                        GetSQLValueString($colname_course, "text"), 
                        GetSQLValueString($colname1_course, "int"));
$approved = mysql_query($query_approved, $tams) or die(mysql_error());
$row_approved = mysql_fetch_assoc($approved);
$totalRows_approved = mysql_num_rows($approved);

$registered = false;
if (isset($row_approved['status']) ) {
  $registered = true;
}

if(($row_approved['approved'] == 'TRUE' && $colname1_course == $row_rssess['sesid']) 
        || ($colname1_course > 0 && $colname1_course != $row_rssess['sesid'])) {
    $approve = true;
}

$name = ( isset($row_stud['lname']) ) ? "for ".$row_stud['lname']." ".$row_stud['fname']." (".$row_stud['stdid'].")": "";

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}
?>
<?php 

include("../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4','','',10,10,40,5,5,5); 
$stylesheet = file_get_contents('../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);


$header = ' <table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
                <tr>
                    <td width="15%" align="left"><img src="../img/logo/school-logo.png" width="100px" /></td>
                    <td width="85%" align="center">
                        <div style="font-weight: bold;">
                            <h2 style="font-size: 20pt">'.$university.'</h2>
                            <h5 style="font-size: 9pt">'.$university_address.'</h5>
                            <h4 style="font-size: 14pt"> Course Registration Form ('.$row_cursess['sesname'].') </h4> 
                        </div>
                    </td>
                </tr>
            </table>';

$mpdf->SetHTMLHeader($header);
$html ='<table>
            <tr>
                <td colspan="3">
                    <table width="700" class="table table-bordered table-condensed">
                        <tr>
                            <td><strong>Name : </strong>'.$row_stud['lname'].' '. $row_stud['fname'].'</td>
                            <td><strong>Matric No : </strong>'.$row_stud['stdid'].'</td>
                        </tr>
                        <tr>
                            <td><strong>Department : </strong>'.$row_stud['deptname'].'</td>
                            <td><strong>Level : </strong>'.$row_stud['level'].'00L</td>
                        </tr>
                    </table>';
          $html .= '<table width="700" class="table table-bordered table-condensed">
                        <thead>
                            <tr>
                                 <th colspan = "5" align="center">FIRST SEMESTER COURSES</th>
                            </tr>
                            <tr>
                                <th>COURSE CODE</th>
                                <th>COURSE TITLE</th>
                                <th>STATUS</th>
                                <th>UNIT</th>
                                <th>REMARKS</th>
                            </tr>
                        </thead>
                        <tbody>';
          
                            $tunits = 0;
                            do{
                            $semester = (strtolower($row_course1['semester']) == "f")? 'First': 'Second';
                  $html .= '
                            <tr>
                                <td>'.strtoupper($row_course1['csid']).'</td>
                                <td>'.ucwords(strtolower($row_course1['csname'])).'</td>
                                <td>'.$row_course1['status'].'</td>
                                <td>'.$row_course1['unit'].'</td>
                                <td></td>
                            </tr>';  
                             $tunits += $row_course1['unit'];  
                            }while($row_course1 = mysql_fetch_assoc($course1));
                            
                  $html .= '<tr>
                                <th colspan="3" >Total Units </th>
                                <th colspan="2" >'.$tunits.'</th>
                            </tr>';  
                  
              $html .= '</tbody>
                    </table>';
                    
                    
        // Second Semester Courses
        
        $html .= '<table width="700" class="table table-bordered table-condensed">
                        <thead>
                            <tr>
                                 <th colspan = "5" align="center">SECOND SEMESTER COURSES</th>
                            </tr>
                            <tr>
                                <th>COURSE CODE</th>
                                <th>COURSE TITLE</th>
                                <th>STATUS</th>
                                <th>UNIT</th>
                                <th>REMARKS</th>
                            </tr>
                        </thead>
                        <tbody>';
          
                            $tunits = 0;
                            do{
                            $semester2 = (strtolower($row_course2['semester']) == "f")? 'First': 'Second';
                  $html .= '
                            <tr>
                                <td>'.strtoupper($row_course2['csid']).'</td>
                                <td>'.ucwords(strtolower($row_course2['csname'])).'</td>
                                <td>'.$row_course2['status'].'</td>
                                <td>'.$row_course2['unit'].'</td>
                                <td></td>
                            </tr>';  
                             $tunits2 += $row_course2['unit'];  
                            }while($row_course2 = mysql_fetch_assoc($course2));
                            
                  $html .= '<tr>
                                <th colspan="3" >Total Units </th>
                                <th colspan="2" >'.$tunits2.'</th>
                            </tr>';  
                  
              $html .= '</tbody>
                    </table>';
        
        
        
                    
      $html .= '</td>
            </tr>
            <tr>
                <td width="200">
                    <p align="center">&nbsp;</p>
                    
                    <p align="center">&nbsp;</p>
                    <p align="center">_______________________________</p><br/>
                    <p align="center">Signature / Date</p>
                    <p align="center">'.$row_hod['title'].' '.$row_hod['fname'].' '. $row_hod['lname'].'</p>
                    <p align="center">H.O.D</p>
                </td>
                <td width="300">
                  
                </td>
                <td width="200">
                    <p align="center">&nbsp;</p>
                    
                    <p align="center">&nbsp;</p>
                    <p align="center">_______________________________</p><br/>
                    <p align="center">Signature / Date</p>
                    <p align="center">'.$row_dean['title'].' '.$row_dean['fname'].' '. $row_dean['lname'].'</p>
                    <p align="center">DEAN</p>
                </td>   
            </tr>
        </table>';


$mpdf->WriteHTML($html);
$mpdf->Output('Course_Registration_form.pdf', 'I');

exit;
?>

