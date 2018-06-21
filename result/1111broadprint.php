<?php
if (!isset($_SESSION)) {
  session_start();
}
require_once('../path.php');

$session = (isset($_POST['session']))? $_POST['session']: getSessionValue('sesid');

$query_rssess = sprintf("SELECT * FROM `session` WHERE sesid = %s",
                        GetSQLValueString($session, "int"));
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

$col = (isset($_POST['col']))? $_POST['col']: '';
$level = (isset($_POST['level']))? $_POST['level']: '';
$semester = (isset($_POST['semester']))? $_POST['semester']: '';
$semester_name = (strtolower($semester) == 'f')? 'First': 'Second';
$prog = (isset($_POST['prog']))? $_POST['prog']: '';

$final = false;

$query_info = sprintf("SELECT colname, c.colid, deptname, d.deptid, progname "
                        . "FROM college c, department d, programme p "
                        . "WHERE c.colid = d.colid "
                        . "AND d.deptid = p.deptid "
                        . "AND p.progid=%s",
                        GetSQLValueString($prog, "int"));
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$query_hod = sprintf("SELECT lname, fname "
                    . "FROM lecturer l, programme p, department d "
                    . "WHERE d.deptid = p.deptid "
                    . "AND l.deptid = p.deptid "
                    . "AND access='3' "
                    . "AND p.progid=%s",
                    GetSQLValueString($prog, "int"));
$hod = mysql_query($query_hod, $tams) or die(mysql_error());
$row_hod = mysql_fetch_assoc($hod);
$totalRows_hod = mysql_num_rows($hod);

$query_dean = sprintf("SELECT lname, fname "
                    . "FROM lecturer l, department d "
                    . "WHERE d.deptid = l.deptid "
                    . "AND access='2' "
                    . "AND d.colid=%s",
                    GetSQLValueString($row_info['colid'], "int"));
$dean = mysql_query($query_dean, $tams) or die(mysql_error());
$row_dean = mysql_fetch_assoc($dean);
$totalRows_dean = mysql_num_rows($dean);

$query_vc = sprintf("SELECT lname, fname FROM lecturer l, appointment a WHERE l.lectid = a.lectid AND postid=1",
                    GetSQLValueString($row_info['colid'], "int"));
$vc = mysql_query($query_vc, $tams) or die(mysql_error());
$row_vc = mysql_fetch_assoc($vc);
$totalRows_vc = mysql_num_rows($vc);

$query_list = sprintf("SELECT * "
                    . "FROM student s "
                    . "JOIN registration r ON s.stdid = r.stdid "
                    . "JOIN admission_type at ON s.admid = at.typeid "
                    . "WHERE s.progid=%s "
                    . "AND r.sesid = %s "
                    . "AND r.level = %s "
                    . "ORDER BY s.stdid ASC",
                    GetSQLValueString($prog, "int"),
                    GetSQLValueString($session, "int"),
                    GetSQLValueString($level, "int"));
$list = mysql_query($query_list, $tams) or die(mysql_error());
$row_list = mysql_fetch_assoc($list);
$totalRows_list = mysql_num_rows($list);

// All courses
if($semester == 'F') {
    $colname_out1 = "AND ((dc.csid LIKE '___1%' AND c.semester = 'F') AND dc.csid NOT LIKE 'VOS%')";
    if ($level > 1) {
        $colname_out1 = 'AND ((';

        for($i = 1; $i <= $level - 1; $i++) {
            $colname_out1 .= "dc.csid LIKE '___{$i}%' OR ";
        } 
        $colname_out1 .= "(dc.csid LIKE '___{$level}%' AND c.semester = 'F')) AND dc.csid NOT LIKE 'VOS%') ";
    }
}else {
    $colname_out1 = "AND ((dc.csid LIKE '___1%') AND dc.csid NOT LIKE 'VOS%')";
    if ($level > 1) {
        $colname_out1 = 'AND ((';

        for($i = 1; $i <= $level - 1; $i++) {
            $colname_out1 .= "dc.csid LIKE '___{$i}%' OR ";
        } 
        $colname_out1 .= "dc.csid LIKE '___{$level}%') AND dc.csid NOT LIKE 'VOS%') ";
    }
}
    
$query_courses = sprintf("SELECT c.csid, c.status, c.unit, c.level, dc.status as dstatus, dc.unit as dunit, dc.level as dlevel "
        . "FROM department_course dc, course c "
        . "WHERE dc.csid = c.csid "
        . "AND dc.progid = %s %s ",								
        GetSQLValueString($prog, "int"),
        GetSQLValueString($colname_out1, "defined", $colname_out1));
$courses = mysql_query($query_courses, $tams) or die(mysql_error());
$row_courses = mysql_fetch_assoc($courses);
$totalRows_courses = mysql_num_rows($courses);
     
$outstanding = array(); // hold other courses
$outstanding1 = array(); // hold 100 level courses.
for($i = 0; $i < $totalRows_courses; $i++, $row_courses = mysql_fetch_assoc($courses)) {
    $crslevel = isset($row_courses['level'])? $row_courses['level']: $row_courses['dlevel'];    
    
    if ($crslevel == 1) {
        $outstanding1[] = $row_courses['csid'];
    } else {
        $outstanding[] = $row_courses['csid'];
    }
}

$query_grad = sprintf("SELECT * FROM grading g, session s WHERE g.sesid = s.sesid AND g.colid = %s",
                GetSQLValueString(getSessionValue('cid'), "int"));
$grad = mysql_query($query_grad, $tams) or die(mysql_error());
$row_grad = mysql_fetch_assoc($grad);
$totalRows_grad = mysql_num_rows($grad);

$sesGrad = array();
for($idx =0; $idx < $totalRows_grad; $idx++, $row_grad = mysql_fetch_assoc($grad)) {
    $sesGrad[$row_grad['sesid']] = [
        'gradeA' => $row_grad['gradeA'],
        'gradeB' => $row_grad['gradeB'],
        'gradeC' => $row_grad['gradeC'],
        'gradeD' => $row_grad['gradeD'],
        'gradeE' => $row_grad['gradeE'],
        'gradeF' => $row_grad['gradeF'],
        'passmark' => $row_grad['passmark']
    ];
}

$query_exp = sprintf("SELECT csid, type, passmark, sesname, g.sesid "
                        . "FROM grade_exceptions g, session s "
                        . "WHERE g.sesid = s.sesid "
                        . "AND ((g.unitid = %s AND g.type = 'College') OR (g.unitid = %s AND g.type = 'Department')) "
                        . "AND g.sesid <= %s "
                        . "ORDER BY sesid DESC, csid, type",
                        GetSQLValueString(getSessionValue('cid'), "int"),
                        GetSQLValueString(getSessionValue('did'), "int"),
                        GetSQLValueString($session, "int"));
$exp = mysql_query($query_exp, $tams) or die(mysql_error());
$row_exp = mysql_fetch_assoc($exp);
$totalRows_exp = mysql_num_rows($exp);

$sesExp = array();
for($idx =0; $idx < $totalRows_exp; $idx++, $row_exp = mysql_fetch_assoc($exp)) {
    $sesExp[$row_exp['csid']][$row_exp['sesid']] = $row_exp['passmark'];
}

$students = array();
$course = array();
$courseInfo = array();
$taken = array();
$courseCount = 0;
  
$query_deptcourses = sprintf("SELECT dc.csid, dc.status, dc.unit "
        . "FROM department_course dc JOIN course c ON c.csid = dc.csid "
        . "WHERE dc.progid = %s "
        . "AND c.semester = %s "
        . "AND dc.level = %s",								
        GetSQLValueString($prog, "int"),
        GetSQLValueString($semester, "text"),
        GetSQLValueString($level, "int"));
$deptcourses = mysql_query($query_deptcourses, $tams) or die(mysql_error());
$row_deptcourses = mysql_fetch_assoc($deptcourses);
$totalRows_deptcourses = mysql_num_rows($deptcourses);

for($i = 0; $i < $totalRows_deptcourses; $i++, $row_deptcourses = mysql_fetch_assoc($deptcourses)) {    
    $course[$i] = $row_deptcourses['csid'];
    $courseInfo[$row_deptcourses['csid']] = array(
        'unit' => $row_deptcourses['unit'],
        'status' => substr($row_deptcourses['status'], 0, 1)
    );
}

$filter = 'cr.sesid <= '.GetSQLValueString($session, "int");
if($semester == 'F') {
    $filter = 'cr.sesid < '.
          GetSQLValueString($session, "int").' OR (cr.sesid = '.
          GetSQLValueString($session, "int").' AND c.semester='.  
          GetSQLValueString($semester, 'text').')';
}
  
for($i = 0; $i < $totalRows_list; $i++, $row_list = mysql_fetch_assoc($list)) {
    //$merged_outstanding = ($row_list['typename'] == 'DE')? $outstanding: array_merge($outstanding, $outstanding1);
    $type = $row_list['typename'];
    $stud = array();
    $studid = $row_list['stdid'];
    $stud['matric'] = $studid;
    $stud['sex'] = $row_list['sex'];
    $stud['name'] = $row_list['lname'].' '.$row_list['fname'].' '.$row_list['mname'];   
    $cgpa = getCgpa($studid, $prog, $session, $semester, $tams, $examined, $outstanding, $outstanding1, $type, $sesGrad);
    $stud['prev'] = $cgpa['prev'];
    $stud['cur'] = $cgpa['cur'];
    $stud['cum'] = $cgpa['cum'];
    $stud['crs'] = $cgpa['crs'];    
    $stud['resit'] = $cgpa['resit'];
    //$ref = getRef($studid, $session, $semester, $filter, $tams, $row_info['colid']);
    $stud['ref'] = (empty($cgpa['ref']))? 'PASSED': $cgpa['ref'];
    
    $cgpa = [];
    $students[] = $stud;
}

function gradepoint($unit, $score, $ses, $sesGrad, $tf) {

    $gp = 0;
    $grades = $sesGrad[$ses];

    // Checks for technical failure
    if ($tf)
        return $gp;

    if ($score < $grades['gradeF'])
            $gp = 0;
    else if ($score < $grades['gradeE'])
            $gp = 0;
    else if ($score < $grades['gradeD'])
            $gp = 2;
    else if ($score < $grades['gradeC'])
            $gp = 3;
    else if ($score < $grades['gradeB'])
            $gp = 4;
    else if ($score < $grades['gradeA'])
            $gp = 5;

    return $gp * $unit;
}

function grade($score, $tf){
    $value = '';
    global $sesGrad;
    global $session;

    $grades = $sesGrad[$session];

    if($tf)
        return 'TF';
        
    if( $score < $grades['gradeF'])
            $value = 'F0';
    else if ( $score < $grades['gradeE'])	
            $value = 'F0';
    else if ( $score < $grades['gradeD'])	
            $value = 'D2';
    else if ( $score < $grades['gradeC'])	
            $value = 'C3';
    else if ( $score < $grades['gradeB'])	
            $value = 'B4';
    else if ( $score < $grades['gradeA'])	
            $value = 'A5';

    return $value;
}

function getPassmark($sesid, $csid, $status, $firstYear = FALSE) {
    global $sesGrad;
    global $sesExp; 
    
    $default = $sesGrad[$sesid]['passmark'];
    
    // OOU specific requirements
//    if($status == 'Required') {
//        return 30;
//    }elseif($status == 'Elective') {
//        return 0;
//    }
    
    // Enforce normal grade in 100 level
    //if($firstYear) {
    //    $default = $sesGrad[$sesid]['gradeF'];
    //    return $default;
    //}
        
    if(empty($sesExp[$csid])) {
        return $default;
    }
    
    $expMark = NULL;
    $keys = array_keys($sesExp[$csid]);
    
    foreach($keys as $value) {
        if($value > $sesid)             
            continue;
        
        if(isset($sesExp[$csid][$value]))
            $expMark = $sesExp[$csid][$value];
        
        break;        
    }
    
    return (isset($expMark))? $expMark: $default;
}

function getCgpa($studid, $progid, $ses, $sem, $tams, &$examined, $courses, $courses1, $type, $grad) {
    $prev = '';
    $cur = '';
    $cum = '';

    global $course;
    global $courseInfo;
    global $courseCount;
    global $taken;
    global $filter;    
    global $level;
    
    $query_cgpa = sprintf("SELECT distinct cr.csid, c.semester, r.tscore, r.escore, r.tscore+ r.escore as score, cr.sesid, "
            . "dc.status, dc.unit, c.status as cstatus, c.unit as cunit "
            . "FROM course_reg cr "
            . "LEFT JOIN result r ON cr.stdid = r.stdid AND cr.sesid = r.sesid AND cr.csid = r.csid "
            . "JOIN course c ON cr.csid = c.csid "
            . "JOIN registration rg ON cr.sesid = rg.sesid AND cr.stdid = rg.stdid "
            . "LEFT JOIN department_course dc ON rg.level = dc.level AND rg.progid = dc.progid AND cr.csid = dc.csid "
            . "LEFT JOIN teaching t ON cr.csid = t.csid AND cr.sesid = t.sesid AND t.accepted = 'yes' "
            . "WHERE cr.stdid = %s "
            . "AND cr.csid NOT LIKE 'VOS___' "
            . "AND (%s) ORDER BY r.sesid ASC", 
            GetSQLValueString($studid, "text"),  
            GetSQLValueString($filter, "defined", $filter));
    $cgpa = mysql_query($query_cgpa, $tams) or die(mysql_error());
    $row_cgpa = mysql_fetch_assoc($cgpa);
    $totalRows_cgpa = mysql_num_rows($cgpa);

    
    $query_firstYear = sprintf("SELECT sesid, progid "
            . "FROM registration r "
            . "WHERE level = 1 "
            . "AND stdid = %s ", GetSQLValueString($studid, "text"));
    $firstYear = mysql_query($query_firstYear, $tams) or die(mysql_error());
    $row_firstYear = mysql_fetch_assoc($firstYear);
    
    if($row_firstYear['progid'] != $progid) {
    	$filt = "";
    	if($level == 1 && $sem == 'F') {
    	    $filt = "AND c.semester = ".getSQLValueString($semester, 'text');
    	}
    	
        $query_deptC = sprintf("SELECT dc.csid, dc.status, dc.unit "
                . "FROM department_course dc JOIN course c ON c.csid = dc.csid "
                . "WHERE dc.progid = %s "
                . "%s "
                . "AND dc.level = 1", 
                GetSQLValueString($row_firstYear['progid'], "int"), 
                GetSQLValueString("filt", "defined", $filt));
        $deptC = mysql_query($query_deptC, $tams) or die(mysql_error());
        $row_deptC = mysql_fetch_assoc($deptC);
        $totalRows_deptC = mysql_num_rows($deptC);
        
        for ($i = 0; $i < $totalRows_deptC; $i++, $row_deptC = mysql_fetch_assoc($deptC)) {
            $courses[] = $row_deptC['csid'];
        }
    }else if($type == 'UTME') {
        $courses = array_merge($courses, $courses1);
    }
    
    if($totalRows_cgpa > 0) {
        $examined++;
    }

    $curValue = 0;
    $prevValue = 0;
    $curUnit = 0;
    $prevUnit = 0;
    $curPassed = 0;
    $prevPassed = 0;
    $crs = array();
    $resit = array();
    
    for($i = 0; $i < $totalRows_cgpa; $i++, $row_cgpa = mysql_fetch_assoc($cgpa)) {
        
        $unit = (isset($row_cgpa['unit']) && $row_cgpa['unit'] != "")? $row_cgpa['unit']: $row_cgpa['cunit'];
        //$unit = $row_cgpa['unit'];
        $status = (isset($row_cgpa['status']) && $row_cgpa['status'] != "")? $row_cgpa['status']: $row_cgpa['cstatus'];
        //$status = $row_cgpa['status'];
        $sesn = $row_cgpa['sesid'];
        $first = ($row_firstYear['sesid'] == $sesn)? true: false;
        $tf = is_numeric($row_cgpa['tscore'])? false: true;
        
        if(!is_numeric($row_cgpa['tscore']) && is_numeric($row_cgpa['escore'])) {
            $row_cgpa['score'] = $row_cgpa['escore'];
        }else if(is_numeric($row_cgpa['tscore']) && !is_numeric($row_cgpa['escore'])){
            $row_cgpa['score'] = $row_cgpa['tscore'];
        }else if(!is_numeric($row_cgpa['tscore']) && !is_numeric($row_cgpa['escore'])) {
            $row_cgpa['score'] = '-';
        }
        
        // Remove courses whose score is above passmark from outstanding list
        $passmark = getPassmark($sesn, $row_cgpa['csid'], $status, $first);
        if(!$tf && $passmark <= $row_cgpa['score']) {            
            $key = array_search($row_cgpa['csid'], $courses);
            unset($courses[$key]);
        }else if(($tf || $passmark > $row_cgpa['score']) && !in_array($row_cgpa['csid'], $courses)) {
            array_push($courses, $row_cgpa['csid']);
        }
        
        if($sesn == $ses) {
            
            if(strtolower($row_cgpa['semester']) == strtolower($sem)) {
                $gp = gradepoint($unit, $row_cgpa['score'], $sesn, $grad, $tf);
                $curValue += $gp;
                $curUnit +=  $unit;
            
                if($gp > 0)
                    $curPassed += $unit;
                
                if(!in_array($row_cgpa['csid'], $course)) {
                    $resit[] = $row_cgpa['csid'].'-'
                            .$unit.substr($status, 0, 1)
                            .'('.$row_cgpa['score'].')';
                }
                
                $crs[$row_cgpa['csid']] = [$row_cgpa['score'], $tf];
            }else{
                $gp = gradepoint($unit, $row_cgpa['score'], $sesn, $grad, $tf);
                $prevValue += $gp;
                $prevUnit +=  $unit;

                if($gp > 0)
                    $prevPassed += $unit;
            }            
            
        }else {
            $taken[] = $row_cgpa['csid'];
            $gp = gradepoint($unit, $row_cgpa['score'], $sesn, $grad, $tf);
            $prevValue += $gp;
            $prevUnit +=  $unit;
            
            if($gp > 0)
                $prevPassed += $unit;
        }	
    }
    $cumUnit = $prevUnit + $curUnit; 
    $cumValue = $prevValue + $curValue;
    $cumPassed = $prevPassed + $curPassed;
    
    $prev = ($prevUnit == 0)? '0.00': @number_format(round(($prevValue)/($prevUnit), 2), 2);
    $cur = ($curUnit == 0)? '0.00': @number_format(round(($curValue)/($curUnit), 2), 2);

    $cum = ($cumUnit == 0)? '0.00': @number_format(round(($cumValue)/($cumUnit), 2), 2);

    return array('prev' => array('gpa' => $prev, 'tgp' => $prevValue, 'tut' => $prevUnit, 'tup' => $prevPassed),
                 'cur' => array('gpa' => $cur, 'tgp' => $curValue, 'tut' => $curUnit, 'tup' => $curPassed),
                 'cum' => array('gpa' => $cum, 'tgp' => $cumValue, 'tut' => $cumUnit, 'tup' => $cumPassed),
                 'crs' => $crs,
                 'resit' => implode(', ', $resit),
                 'ref' => implode(', ', $courses)
        );
}

$outstanding = $outstanding1 = [];

$semester = $_POST['semester'];
$sem = 'first semester';
if($semester == 'S') 
    $sem = 'second semester';
      

include("../mpdf/mpdf.php");
$mpdf=new mPDF('c','Legal-L','','',10,10,50,15,5,5); 
$stylesheet = file_get_contents('../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet,1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="15%" align="left"><img src="../img/logo/school-logo.png" width="100px" /></td>
<td width="85%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 25pt">'.$university.'</h2>
<h3 style="font-size: 17pt">'.$row_info['colname'].'<br></h3>
<h4 style="font-size: 13pt">'.$row_info['deptname'].'</h4>
<h5 style="font-size: 9pt">'.$university_address.'</h5></div>
</td>
</tr>
</table>
<div style="text-align:center; width:100%; font-size: 16pt">
    <div style="float:left;text-align:left; width:19%; font-size: 16pt">'.$row_rssess['sesname'].' ('.strtoupper($semester_name).')'.'</div>
    <div style="float:left;text-align:center; width:60%; font-size: 16pt">Broadsheet</div>
    <div style="float:right;text-align:left; width:20%; font-size: 16pt">'.$level.'00 Level</div>
    <div style="clear:both"></div>
</div>';

$footer = '<div align="center">{PAGENO}</div>';


$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLFooter($footer);

$html = '';
        
if(!empty($students)) {
    $html .= "<table width='850' class='table table-striped table-bordered table-condensed'>
            <thead style='font-size:8pt'>
            <tr>
              <th rowspan='2' align='center' valign='top'>S/N</th>
              <th rowspan='2' align='center' valign='top'>Matric</th>
              <th rowspan='2' align='center' valign='top'>Name</th>
              <th rowspan='2' align='center' valign='top'>Sex</th>";
    
    foreach($course as $c) {
        $html .= "<th rowspan='2' valign='top' align='center'>".substr($c, 0, 3).
                    "<br/>".substr($c, 3).
                    "<br/>
                    {$courseInfo[$c]['unit']}<br/>{$courseInfo[$c]['status']}</th>";
    }
    
    $html .="<th rowspan='2' valign='top' align='center' width='24%'>Courses retaken or repeated</th>
              <th colspan='4' valign='top' align='center' width='10%'>Previous</th>
              <th colspan='4' valign='top' align='center' width='10%'>Current</th>
              <th colspan='4' valign='top' align='center' width='10%'>Cummulative</th>
              <th rowspan='2' valign='top' align='center' width='30%'>Remark</th>
          </tr>
            <tr>
                <th style='font-size:6pt'>TCP</th> 
                <th style='font-size:6pt'>TNU</th>                  
                <th style='font-size:6pt'>GPA</th>
                <th style='font-size:6pt'>TNUP</th> 
                <th style='font-size:6pt'>TCP</th>
                <th style='font-size:6pt'>TNU</th>                    
                <th style='font-size:6pt'>GPA</th>
                <th style='font-size:6pt'>TNUP</th>
                <th style='font-size:6pt'>TCP</th>
                <th style='font-size:6pt'>TNU</th>                   
                <th style='font-size:6pt'>CGPA</th>
                <th style='font-size:6pt'>TNUP</th> 
            </tr>
            </thead>";

    $courseInfo = [];
    
    foreach($students as $count => $std) {
        
        $html .= "<tr>
          <td>".($count+1)."</td>
          <td><a href='../student/profile.php?stid={$std['matric']}' target= '_blank '>{$std['matric']}</a></td>
          <td>{$std['name']}</td>
          <td>{$std['sex']}</td>";
        
        foreach($course as $c) {
            
            if(isset($std['crs'][$c]) && $std['crs'][$c] != '') {
                $html .= "<td align='center'>{$std['crs'][$c][0]}<br/>".grade($std['crs'][$c][0], $std['crs'][$c][1])."</td>";
                continue;
            }
            
            $html .= "<td align='center'> - </td>";
        }
        
        $html .= "
          <td>{$std['resit']}</td>  
          <td>{$std['prev']['tgp']}</td> 
          <td>{$std['prev']['tut']}</td>
          <td>{$std['prev']['gpa']}</td>
          <td>{$std['prev']['tup']}</td>  
          <td>{$std['cur']['tgp']}</td>  
          <td>{$std['cur']['tut']}</td>
          <td>{$std['cur']['gpa']}</td>
          <td>{$std['cur']['tup']}</td> 
          <td>{$std['cum']['tgp']}</td>  
          <td>{$std['cum']['tut']}</td> 
          <td>{$std['cum']['gpa']}</td>
          <td>{$std['cum']['tup']}</td> 
          <td>{$std['ref']}</td>
        </tr>";

    }

    $html .= '</table><br/><br/><br/><br/><br/><br/><br/><br/>';
    
    $width = ($final)? '33%': '50%';
    
    $html .= "<table width='100%' border='0'>
      <tr>
        <td width='{$width}' align='center'>{$row_hod['fname']} {$row_hod['lname']}</td>
        <td width='{$width}' align='center'>{$row_dean['fname']} {$row_dean['lname']}</td>";
        
        if($final) {
            $html .= "<td width='{$width}' align='center'>{$row_vc['fname']} {$row_vc['lname']}</td>";
        }
        
        
    $html .= "</tr>
      <tr>
        <td width='{$width}' align='center'>HOD</td>
        <td width='{$width}' align='center'>DEAN</td>";
        
        if($final) {
            $html .= "<td width='{$width}' align='center'>External Examiner</td>";
        }
    $html .= "
      </tr>
    </table>"; 
}

     
$mpdf->WriteHTML($html);

$mpdf->Output('broadsheet.pdf', 'I');
exit;