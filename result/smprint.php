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
$curr = (isset($_POST['curriculum']))? $_POST['curriculum']: ''; 
$final = false;

$tupName = 'CTNUP';
$gpaName = 'CGPA';

if($level == 1 && $semester == 'F') {
    $tupName = substr($tupName, 1);
    $gpaName = substr($gpaName, 1);
}

$query_info = sprintf("SELECT colname, c.colid, deptname, d.deptid, progname "
                        . "FROM college c, department d, programme p "
                        . "WHERE c.colid = d.colid "
                        . "AND d.deptid = p.deptid AND p.progid=%s",
                        GetSQLValueString($prog, "int"));
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$query_hod = sprintf("SELECT title, lname, fname, mname "
                        . "FROM lecturer l, programme p, department d "
                        . "WHERE d.deptid = p.deptid "
                        . "AND l.deptid = p.deptid AND access='3' "
                        . "AND p.progid=%s",
                        GetSQLValueString($prog, "int"));
$hod = mysql_query($query_hod, $tams) or die(mysql_error());
$row_hod = mysql_fetch_assoc($hod);
$totalRows_hod = mysql_num_rows($hod);

$query_dean = sprintf("SELECT title, lname, fname, mname "
                        . "FROM lecturer l, department d "
                        . "WHERE d.deptid = l.deptid "
                        . "AND access='2' AND d.colid=%s",
                        GetSQLValueString($row_info['colid'], "int"));
$dean = mysql_query($query_dean, $tams) or die(mysql_error());
$row_dean = mysql_fetch_assoc($dean);
$totalRows_dean = mysql_num_rows($dean);

$query_vc = sprintf("SELECT title, lname, fname, mname "
                        . "FROM lecturer l, appointment a "
                        . "WHERE l.lectid = a.lectid AND postid=1 AND a.sdate = %s ORDER BY appid DESC LIMIT 0,1",
                        GetSQLValueString($session, "int")); 
$vc = mysql_query($query_vc, $tams) or die(mysql_error());
$row_vc = mysql_fetch_assoc($vc);
$totalRows_vc = mysql_num_rows($vc);

$query_list = sprintf("SELECT * "
                        . "FROM student s "
                        . "JOIN registration r ON s.stdid = r.stdid "
                        . "JOIN admission_type at ON s.admid = at.typeid "
                        . "WHERE s.progid=%s "
                        . "AND r.sesid = %s "
                        . "AND r.course = 'Registered' "
                        . "AND SUBSTR(s.stdid,1,4) > 2012 "  // remove extra year students
                        . "AND r.level = %s "
                        . "AND s.passlist = 'No' "
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
        . "AND c.curid = dc.curid AND dc.curid = %s "
        . "AND dc.progid = %s "
        . "AND c.status != 'Elective' AND dc.status != 'Elective' %s ",	
        GetSQLValueString($curr, "int"),
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
    $sesGrad[$row_grad['sesid']][$row_grad['level']] = [
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

$passlist = array();
$faillist = array();
$commendation = array();
$probation = array();
$counselling = array();
$examined = 0;
$suspension = array();
$withdraw = array();
$studentship = array();

//$filter = 'cr.sesid <= '.GetSQLValueString($session, "int");
//if($semester == 'F') {
//    $filter = 'cr.sesid < '.
//          GetSQLValueString($session, "int").' OR (cr.sesid = '.
//          GetSQLValueString($session, "int").' AND c.semester='.  
//          GetSQLValueString($semester, 'text').')';
//}

$filter = 'r.sesid <= '.GetSQLValueString($session, "int");
if($semester == 'F') {
    $filter = 'r.sesid < '.
          GetSQLValueString($session, "int").' OR (r.sesid = '.
          GetSQLValueString($session, "int").' AND c.semester='.  
          GetSQLValueString($semester, 'text').')';
}

for($i = 0; $i < $totalRows_list; $i++, $row_list = mysql_fetch_assoc($list)) {
    //$merged_outstanding = ($row_list['typename'] == 'DE')? $outstanding: array_merge($outstanding, $outstanding1);
    $type = $row_list['typename'];
    $stud = array();
    $studid = $row_list['stdid'];
    $stud['matric'] = $studid;
    $stud['name'] = strtoupper($row_list['lname']).' '.ucwords(strtolower($row_list['fname'])).' '.ucwords(strtolower($row_list['mname']));    
    $stud['sex'] = $row_list['sex'];
    $cgpa = getCgpa($studid, $prog, $session, $semester, $tams, $examined, $outstanding, $outstanding1, $type, $sesGrad, $curr);
    
//  $stud['prev'] = $cgpa['prev'];
    $stud['cur'] = $cgpa['cur'];
    $stud['cum'] = $cgpa['cum'];
    $stud['withdraw'] = $cgpa['withdraw'];
    $stud['suspend'] = $cgpa['suspend'];
    $stud['student'] = $cgpa['student'];
    // $ref = getRef($studid, $session, $semester, $filter, $tams, $row_info['colid']);
    $stud['ref'] = $cgpa['ref'];
//    $disc = getDisc($studid, $session , $tams);
//    if($disc != '') {
//        $stud['disc'] = $disc;
//        $suspension[] = $stud;
//    }

    if($stud['cum']['gpa'] >= 1.0 && empty($stud['ref'])) {
               
        $passlist[] = $stud;
        
        if($stud['cum']['gpa'] >= 4.5) {
            $commendation[] = $stud;
        }elseif($stud['cum']['gpa'] < 1.0) {
            $counselling[] = $stud;
        }
        
    }elseif($stud['cum']['gpa'] >= 1.0 && !empty($stud['ref'])) {
                
        $faillist[] = $stud; 
        
        if($stud['cur']['gpa'] < 1.0) {
            $counselling[] = $stud;        
        }
        
    }elseif($stud['cum']['gpa'] < 1.0) {
        $faillist[] = $stud;
        
        if($level == 1 && $semester == 'F') {
            
        }else {
            $probation[] = $stud;
        }
    }

    if($stud['withdraw']) {
        $withdraw[] = $stud;
    }
    
    if($stud['suspend']) {
        $suspension[] = $stud;
    }
    
    if($stud['student']) {
        $studentship[] = $stud;
    }
}

function getDisc($studid, $sesid, $tams) {
     $query_disc = sprintf("SELECT status FROM disciplinary d WHERE stdid=%s and sesid=%s",
                    GetSQLValueString($studid, "int"),
                    GetSQLValueString($sesid, "int"));
    $disc = mysql_query($query_disc, $tams) or die(mysql_error());
    $row_disc = mysql_fetch_assoc($disc);
    $totalRows_disc = mysql_num_rows($disc);
    
    if($totalRows_disc > 0) {
        return $row_disc['status'];
    }
    
    return '';
}

function gradepoint($unit, $score, $level, $ses, $sesGrad, $tf) {
    $gp = 0;
    $grades = $sesGrad[$ses][$level];

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

function getPassmark($sesid, $csid, $status, $level, $firstYear = FALSE) {
    global $sesGrad;
    global $sesExp;  
    
    if (in_array($status, ['Elective', 'Required'])) {
        return 0;
    }
    
    $default = $sesGrad[$sesid][$level]['passmark'];
       
    // Enforce normal grade in 100 level
//    if($firstYear) {
//        $default = $sesGrad[$sesid]['gradeF'];
//        return $default;
//    }
  
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

function getCgpa($studid, $progid, $ses, $sem, $tams, &$examined, $courses, $courses1, $type, $grad, $curid) {
    $prev = '';
    $cur = '';
    $cum = '';

    global $course;
    global $courseInfo;
    global $courseCount;
    global $taken;
    global $filter;
    global $level;
    
    
    //"AND c.curid = st.curid"
    
    
    $query_cgpa = sprintf("
       SELECT a.* FROM (
       SELECT distinct (r.csid), r.sesid, c.csname, c.semester, r.tscore, r.escore, r.tscore + r.escore as score, s.sesname, dc.status, dc.unit, c.status as cstatus, c.unit as cunit, dc.level "
        . "FROM `result` r, department_course dc, course c, session s, teaching t "
        . "WHERE stdid = %s "
        . "AND c.csid = r.csid "
        . "AND c.csid = dc.csid AND dc.progid = %s AND dc.curid = c.curid "
        . "AND r.csid = t.csid "
        . "AND s.sesid = t.sesid "
        . "AND t.accepted = 'yes' "
        . "AND s.sesid = r.sesid "
        . "AND c.curid = %s "
       // . "AND %s "
        . "AND r.csid NOT LIKE %s "
        
        . "UNION "
        
        . "SELECT distinct (r.csid), r.sesid, c.csname, c.semester, r.tscore, r.escore, r.tscore + r.escore as score, s.sesname, c.status, c.unit, c.status as cstatus, c.unit as cunit, c.level "
        . "FROM `result` r, course c, session s, teaching t "
        . "WHERE stdid = %s "
        . "AND c.csid = r.csid "
        . "AND r.csid = t.csid "
        . "AND s.sesid = t.sesid "
        . "AND t.accepted = 'yes' "
        . "AND s.sesid = r.sesid "
        . "AND c.curid = %s "
       // . "AND %s "
        . "AND r.csid NOT LIKE %s "
        . "AND r.csid NOT IN "
        
        . "(SELECT distinct (r.csid) "
        . "FROM `result` r, department_course dc, course c, session s, teaching t "
        . "WHERE stdid = %s "
        . "AND c.csid = r.csid "
        . "AND c.csid = dc.csid AND dc.progid = %s AND dc.curid = c.curid "
        . "AND r.csid = t.csid "
        . "AND s.sesid = t.sesid "
        . "AND t.accepted = 'yes' "
        . "AND s.sesid = r.sesid "
        . "AND c.curid = %s "
      //  . "AND %s " 
        . "AND r.csid NOT LIKE %s ) ) a order by a.sesid  " ,
       
        GetSQLValueString($studid, "text"), 
        GetSQLValueString($progid, "int"),
        GetSQLValueString($curid, "int"), 
      //  GetSQLValueString($filter, "defined", $filter),
        GetSQLValueString("VOS%", "text"),
        
        GetSQLValueString($studid, "text"), 
        GetSQLValueString($curid, "int"), 
     //   GetSQLValueString($filter, "defined", $filter),
        GetSQLValueString("VOS%", "text"),
        
        GetSQLValueString($studid, "text"), 
        GetSQLValueString($progid, "int"),
        GetSQLValueString($curid, "int"), 
      //  GetSQLValueString($filter, "defined", $filter),
        GetSQLValueString("VOS%", "text")); 
        
    //dc is used for status and unit to force department course data
//    $query_cgpa = sprintf("SELECT distinct cr.csid, c.semester, r.tscore, r.escore, r.tscore+ r.escore as score, cr.sesid, "
//            . "rg.level, dc.status, dc.unit, c.status as cstatus, c.unit as cunit "
//            . "FROM course_reg cr JOIN student st ON cr.stdid = st.stdid "
//            . "LEFT JOIN result r ON cr.stdid = r.stdid AND cr.sesid = r.sesid AND cr.csid = r.csid "
//            . "JOIN course c ON cr.csid = c.csid  "
//            . "JOIN registration rg ON cr.sesid = rg.sesid AND cr.stdid = rg.stdid  "
//            . "LEFT JOIN department_course dc ON rg.level = dc.level AND rg.progid = dc.progid AND cr.csid = dc.csid "
//            . "LEFT JOIN teaching t ON cr.csid = t.csid AND cr.sesid = t.sesid AND t.accepted = 'yes' "
//            . "WHERE cr.stdid = %s "
//            . "AND cr.csid NOT LIKE 'VOS___' "
//           // . "AND rg.course = 'Registered' "
//            . "AND (%s) ORDER BY cr.sesid ASC", 
//            GetSQLValueString($studid, "text"), 
//            GetSQLValueString($filter, "defined", $filter)); 

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
    $lastSem = false;
    $withdraw = false;
    
    $lastStud = false;
    $student = false;
    
    $lastSus = false;
    $suspend = false;

    $result = array();
    
    for($i = 0; $i < $totalRows_cgpa; $i++, $row_cgpa = mysql_fetch_assoc($cgpa)) {
        
        $unit = (isset($row_cgpa['unit']) && $row_cgpa['unit'] != "")? $row_cgpa['unit']: $row_cgpa['cunit'];
        //$unit = $row_cgpa['unit'];
        $status = (isset($row_cgpa['status']) && $row_cgpa['status'] != "")? $row_cgpa['status']: $row_cgpa['cstatus'];
        //$status = $row_cgpa['status'];
        $sesn = $row_cgpa['sesid'];
        $first = ($row_firstYear['sesid'] == $sesn)? true: false;
        $tf = is_numeric($row_cgpa['tscore'])? false: true;
        $crs_level = $row_cgpa['level'];
        
        if(!is_numeric($row_cgpa['tscore']) && is_numeric($row_cgpa['escore'])) {
            $row_cgpa['score'] = $row_cgpa['escore'];
        }else if(is_numeric($row_cgpa['tscore']) && !is_numeric($row_cgpa['escore'])){
            $row_cgpa['score'] = $row_cgpa['tscore'];
        }else if(!is_numeric($row_cgpa['tscore']) && !is_numeric($row_cgpa['escore'])) {
            $row_cgpa['score'] = '-';
        }
        
        // Remove courses whose score is above passmark from outstanding list
        $passmark = getPassmark($sesn, $row_cgpa['csid'], $status, $crs_level, $first);
        
        if(!$tf && $passmark <= $row_cgpa['score'] || in_array($status, ['Elective', 'Required'])) {            
            $key = array_search($row_cgpa['csid'], $courses);
            unset($courses[$key]);
        }else if(!in_array($status, ['Elective', 'Required']) && ($tf || $passmark > $row_cgpa['score']) && !in_array($row_cgpa['csid'], $courses)) {
            array_push($courses, $row_cgpa['csid']);
        }
        
        $gp = gradepoint($unit, $row_cgpa['score'], $crs_level, $sesn, $grad, $tf);

        if($row_cgpa['semester'] == 'F') {
            if($sesn == $ses) {
                $result[$sesn]['cur'] = true;
                
                if($gp > 0)
                    $curPassed += $unit;
            }else {
                $result[$sesn]['cur'] = false;

                if($gp > 0)
                    $prevPassed += $unit;
            }

            if(isset($result[$sesn]['first']['gp'])) {
                $result[$sesn]['first']['gp'] += $gp;
            }
            else {
                $result[$sesn]['first']['gp'] = $gp;
            }
            
            if(isset($result[$sesn]['first']['unit'])) {
                $result[$sesn]['first']['unit'] += $unit;
            }
            else {
                $result[$sesn]['first']['unit'] = $unit;
            }
            
        }else {
            if($sesn == $ses) {
                $result[$sesn]['cur'] = true;
                
                if($gp > 0)
                    $curPassed += $unit;
            }else {
                $result[$sesn]['cur'] = false;

                if($gp > 0)
                    $prevPassed += $unit;
            }
            
            if(isset($result[$sesn]['second']['gp'])) {
                $result[$sesn]['second']['gp'] += $gp;
            }
            else {
                $result[$sesn]['second']['gp'] = $gp;
            }
            
            if(isset($result[$sesn]['second']['unit'])) {
                $result[$sesn]['second']['unit'] += $unit;
            }
            else {
                $result[$sesn]['second']['unit'] = $unit;
            }
            
        }

    }

    ksort($result, SORT_NUMERIC);
    
    foreach($result as $res) {
        
        if(!empty($res['first'])) {
            $sesValue = @number_format(round(($res['first']['gp'])/($res['first']['unit']), 2), 2);
            
            if($res['cur']) {
                $curValue += $res['first']['gp'];
                $curUnit += $res['first']['unit'];
                
                if($sem == 'F' && $lastSem && $sesValue < 1.0) {
                    $withdraw = true;
                }
            }else {
                $prevValue += $res['first']['gp'];
                $prevUnit += $res['first']['unit'];
            }
             
             if($sesValue < 1.0) {
                 $lastSem = true;
             }else {
                 $lastSem = false;
             }
             
             $lastStud = false;
        }else {
            if($lastStud) {
                 $student = true;
             }else {
                 $lastStud = true;
             }
        }
        
        if(!empty($res['second'])) {
            $sesValue = @number_format(round(($res['second']['gp'])/($res['second']['unit']), 2), 2);
            
            if($res['cur']) {
                $curValue += $res['second']['gp'];
                $curUnit += $res['second']['unit'];                
                
                if($lastSem && $sesValue < 1.0) {
                     $withdraw = true;
                 }
            }else {
                $prevValue += $res['second']['gp'];
                $prevUnit += $res['second']['unit'];
            }
            
             if($sesValue < 1.0) {
                 $lastSem = true;
             }else {
                 $lastSem = false;
             }
        }
    }
    
    $cumUnit = $prevUnit + $curUnit; 
    $cumValue = $prevValue + $curValue;
    $cumPassed = $prevPassed + $curPassed;
    if($curUnit == 0)
            $curUnit = 1;
    if($prevUnit == 0)
            $prevUnit = 1;
    if($cumUnit == 0)
            $cumUnit = 1;

    $prev = @number_format(round(($prevValue)/($prevUnit), 2), 2);
    $cur = @number_format(round(($curValue)/($curUnit), 2), 2);

    $cum = @number_format(round(($cumValue)/($cumUnit), 2), 2);

    return array('prev' => array('gpa' => $prev, 'tgp' => $prevValue, 'tut' => $prevUnit, 'tup' => $prevPassed),
                 'cur' => array('gpa' => $cur, 'tgp' => $curValue, 'tut' => $curUnit, 'tup' => $curPassed),
                 'cum' => array('gpa' => $cum, 'tgp' => $cumValue, 'tut' => $cumUnit, 'tup' => $cumPassed),
                 'withdraw' => $withdraw,
                 'student' => $student,
                 'suspend' => $suspend,
                 'ref' => implode(', ', $courses)
        );
}


    
$semester = $_POST['semester'];
$sem = 'first semester';
if($semester == 'S') 
    $sem = 'second semester';
      
include("../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4','','',10,15,55,30,5,1); 
$stylesheet = file_get_contents('../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet,1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="5%" align="left"><img src="../img/logo/school-logo.png" width="100px" /></td>
<td width="95%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 25pt">'.$university.'</h2>
<h5 style="font-size: 9pt">'.$university_address.'</h5></div>
<h3 style="font-size: 17pt">'.$row_info['colname'].'<br></h3>
<h4 style="font-size: 13pt">Department of '.$row_info['deptname'].'</h4>
<h4 style="font-size: 13pt">'.$row_info['progname'].'</h4>

</td>
</tr>
</table>
<div style="text-align:center; width:100%; font-size: 16pt">
    <div style="float:left;text-align:left; width:30%; font-size: 16pt">'.$row_rssess['sesname'].' ('.strtoupper($semester_name).')'.'</div>
    <div style="float:left;text-align:center; width:50%; font-size: 16pt">Summary Sheet</div>
    <div style="float:right;text-align:left; width:20%; font-size: 16pt">'.$level.'00 Level</div>
    <div style="clear:both"></div>
</div>';

 $footer = "<table width='100%' border='0'>
      <tr>
        <td width='{$width}' align='center'>................................<br /><br />{$row_hod['title']}. {$row_hod['fname']} {$row_hod['lname']}</td>
        <td width='{$width}' align='center'>................................<br /><br />{$row_dean['title']}. {$row_dean['fname']} {$row_dean['lname']}</td>
        <td width='{$width}' align='center'>............................. </td>
        <td width='{$width}' align='center'>................................<br /><br />{$row_vc['title']}. {$row_vc['fname']} {$row_vc['lname']}</td>";
        
        
        
    $footer .= "</tr>
      <tr>
        <td width='{$width}' align='center'>HOD</td>
        <td width='{$width}' align='center'>DEAN</td>
        <td width='{$width}' align='center'>CHAIRMAN<br />(Vetting Committee)</td>
        <td width='{$width}' align='center'>Vice Chancellor</td>";
    $footer .= '</tr></table>
     <span style="page-break-after: always;"></span>
        ';      
$footer .= '<div align="center">{PAGENO}</div>';


$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLFooter($footer);


  $html = '<p>Summary of Result:</p> <br/> <br />
  
    <p>Number of Students in the Class: '.$totalRows_list.'</p>
    <p>Number of Student Examined: '.$examined.'</p>
    <p>Number of Students that Passed: '.count($passlist).'</p>
    <p>Number of Students to be Commended: '.count($commendation).'</p>
    <p>Number of Students with References: '.count($faillist).'</p>
    <br/><br/><br/><br/><br/><br/>';
   
        
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
        
    $html .= '</tr></table>
     <span style="page-break-after: always;"></span>
        <pagebreak /> ';      



$html .= '
<p>A. Pass</p>
<p>THE FOLLOWING '.count($passlist).' CANDIDATE(S) 
    HAVE PASSED THE COMPULSORY, ATTEMPTED THE REQUIRED COURSES AND 
    FULFILLED ALL OTHER UNIVERSITY REQUIREMENTS:

 </p>';
        
    if(!empty($passlist)) {
        $html .= "<table width='850' class='table table-striped table-bordered'>
                <thead>
                <tr>
                  <th>S/N</th>
                  <th>Matric</th>
                  <th>Name</th>
                  <th>Sex</th>                    
                  <th>{$gpaName}</th>
                  <th>{$tupName}</th>
                  <td>Class of Degree</td>
                  <th width='20%'>Remark</th>
                </tr>
                
                </thead>";
        
        foreach($passlist as $count => $std) {
            // Determining Class of Degree for PASSED Students 

                if ($std['cum']['gpa'] >= 4.50 && $std['cum']['gpa'] <= 5.00){
                    $cdegree = "1<sup>st</sup> Class";
                    }
                    elseif ($std['cum']['gpa'] >= 3.50 && $std['cum']['gpa'] <= 4.49){
                        $cdegree = "2<sup>nd</sup> Class Upper";
                    }
                    elseif ($std['cum']['gpa'] >= 2.40 && $std['cum']['gpa'] <= 3.49){
                        $cdegree = "2<sup>nd</sup> Class Lower";
                    }
                    elseif ($std['cum']['gpa'] >= 1.50 && $std['cum']['gpa'] <= 2.39){
                        $cdegree = "3<sup>rd</sup> Class";
                    }
                    else{
                        $cdegree = " ";
                    }
            $html .= "<tr>
              <td>".($count+1)."</td>
              <td><a href='../student/profile.php?stid={$std['matric']}' target= '_blank '>{$std['matric']}</a></td> 
              <td>{$std['name']}</td>
              <td>{$std['sex']}</td>
              <td>{$std['cum']['gpa']}</td>   
              <td>{$std['cum']['tup']}</td> 
              <td>{$cdegree} </td>
              <td>PASSED</td>
            </tr>";

        }
      
        $html .= '</table><br/><br/>';
    }else {
      $html .= '<p>NIL</p><br/><br/>';
    }

    
    $html .= '
    <p>B. Recommended for Commendation</p>
    <p>THE FOLLOWING '.count($commendation).' CANDIDATE(S)
        ARE RECOMMENDED FOR COMMENDATION FOR HAVING A CURRENT 
        CGPA OF 4.50 AND ABOVE:
</p>';
    if(!empty($commendation)) {
        $html .= "<table width='850' class='table table-striped table-bordered'>
                <thead>                
                    <tr>
                      <th>S/N</th>
                      <th>Matric</th>
                      <th>Name</th>
                      <th>Sex</th>                    
                      <th>CGPA</th>
                      <th>TNUP</th>
                      <th width='40%'>Remark</th>
                    </tr>
                </thead>";
        
        foreach($commendation as $count => $std) {

            $html .= "<tr>
              <td>".($count+1)."</td>
              <td><a href='../student/profile.php?stid={$std['matric']}' target= '_blank '>{$std['matric']}</a></td>   
              <td>{$std['name']}</td>
              <td>{$std['sex']}</td>
              <td>{$std['cum']['gpa']}</td>   
              <td>{$std['cum']['tup']}</td> 
              <td>1<sup>st</sup> Class</td>
            </tr>";

        }
      
        $html .= '</table>
        <span style="page-break-after: always;"></span>
        <pagebreak />';
    }else {
      $html .= '<p>NIL</p>
      <span style="page-break-after: always;"></span>
        <pagebreak />';
    }

    $html .= '
    <p>C. References</p>
    <p>THE FOLLOWING '.count($faillist).' CANDIDATE(S) ARE TO '
            . 'REPEAT/TAKE THE COURSE(S) LISTED AGAINST THEIR NAMES '
            . 'AT THE NEXT AVAILABLE OPPORTUNITY:</p>';
    if(!empty($faillist)) {
        $html .= "<table width='850' class='table table-striped table-bordered'>
                <thead>
                    <tr>
                      <th>S/N</th>
                      <th>Matric</th>
                      <th>Name</th>
                      <th>Sex</th>                    
                      <th>{$gpaName}</th>
                      <th>{$tupName}</th>
                      <th width='40%'>Outstanding/Carry Over</th>
                    </tr>
                </thead>";
        
        foreach($faillist as $count => $std) {

            $html .= "<tr>
              <td>".($count+1)."</td>
              <td><a href='../student/profile.php?stid={$std['matric']}' target= '_blank '>{$std['matric']}</a></td>   
              <td>{$std['name']}</td>
              <td>{$std['sex']}</td>   
              <td>{$std['cum']['gpa']}</td> 
              <td>{$std['cum']['tup']}</td>
              <td>{$std['ref']}</td>
            </tr>";

        }
      
        $html .= '</table><br/><br/>';
    }else {
      $html .= '<p>NIL</p><br/><br/>';
    }
   // these are not necessary
   /*
    $html .= '
    <p>D. Recommended for Counselling</p>
    <p>THE FOLLOWING'.count($counselling).' CANDIDATE(S) 
        ARE RECOMMENDED FOR WITHDRAWAL FOR 
        HAVING A CUMMULATIVE G.P.A. OF LESS THAN 1.00:</p>';
    if(!empty($counselling)) {
        $html .= "<table width='850' class='table table-s triped table-bordered'>
                <thead> 
                    <tr>
                      <th>S/N</th>
                      <th>Matric</th>
                      <th>Name</th>
                      <th>Sex</th>                    
                      <th>GPA</th>
                      <th>TNUP</th>
                      <th width='40%'>Outstanding/Carry Over</th>
                    </tr>
                </thead>";
        
        foreach($counselling as $count => $std) {

            $html .= "<tr>
              <td>".($count+1)."</td>
              <td><a href='../student/profile.php?stid={$std['matric']}' target= '_blank '>{$std['matric']}</a></td>   
              <td>{$std['name']}</td>
              <td>{$std['sex']}</td>
              <td>{$std['cur']['gpa']}</td> 
              <td>{$std['cur']['tup']}</td>
              <td>{$std['ref']}</td>
            </tr>";

        }
      
        $html .= '</table><br/><br/>';
    }else {
      $html .= '<p>NIL</p><br/><br/>';
    }
    
    $html .= '
    <p>E. Recommended for University Probation</p>
    <p>THE FOLLOWING '.count($probation).' CANDIDATE(S) IS RECOMMENDED FOR WITHDRAWAL'
            . ' FOR HAVING A CUMMULATIVE GPA OF LESS THAN 1.00 IN THE SEMESTER:</p>';
    if(!empty($probation)) {
        $html .= "<table width='850' class='table table-striped table-bordered'>
                <thead>
                    <tr>
                      <th>S/N</th>
                      <th>Matric</th>
                      <th>Name</th>
                      <th>Sex</th>                    
                      <th>{$gpaName}</th>
                      <th>{$tupName}</th>
                      <th width='40%'>Outstanding/Carry Over</th>
                    </tr>
                </thead>";
        
        foreach($probation as $count => $std) {

            $html .= "<tr>
              <td>".($count+1)."</td>
              <td><a href='../student/profile.php?stid={$std['matric']}' target= '_blank '>{$std['matric']}</a></td>   
              <td>{$std['name']}</td>
              <td>{$std['sex']}</td>
              <td>{$std['cum']['gpa']}</td> 
              <td>{$std['cum']['tup']}</td>
              <td>{$std['ref']}</td>
            </tr>";

        }
      
        $html .= '</table><br/><br/>';
    }else {
      $html .= '<p>NIL</p><br/><br/>';
    }
      
    $html .= '
    <p>F. Recommended for Withdrawal</p>
    <p>THE FOLLOWING '.count($withdraw).' CANDIDATE(S) ARE ADVISED TO WITHDRAW 
    FOR HAVING A CUMULATIVE GPA OF LESS THAN 1.00 ON TWO CONSECUTIVE SEMESTERS:</p>';
    if(!empty($withdraw)) {
        $html .= "<table width='850' class='table table-striped table-bordered'>
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Matric</th>
                        <th>Name</th>
                        <th>Sex</th>                    
                        <th>GPA</th>
                        <th>TNUP</th>
                        <th width='40%'>Outstanding/Carry Over</th>
                      </tr>
                </thead>";
        
        foreach($withdraw as $count => $std) {

            $html .= "<tr>
              <td>".($count+1)."</td>
              <td><a href='../student/profile.php?stid={$std['matric']}' target= '_blank '>{$std['matric']}</a></td>   
              <td>{$std['name']}</td>
              <td>{$std['sex']}</td>   
              <td>{$std['cur']['gpa']}</td> 
              <td>{$std['cur']['tup']}</td>
              <td>{$std['ref']}</td>
            </tr>";

        }
      
        $html .= '</table><br/><br/>';
    }else {
      $html .= '<p>NIL</p><br/><br/>';
    }
    
    $html .= '
    <p>G. Suspension of Studentship</p>
    <p>THE STUDENTSHIP OF THE FOLLOWING ONE ('.count($suspension).') CANDIDATE 
        IS RECOMMENDED FOR SUSPENSION FOR FAILURE TO REGISTER FOR THE SEMESTER:</p>';
    if(!empty($suspension)) {
        $html .= "<table width='850' class='table table-striped table-bordered'>
                <thead>
                    <tr>
                      <th>S/N</th>
                      <th>Matric</th>
                      <th>Name</th>
                      <th>Sex</th>                    
                      <th>{$gpaName}</th>
                      <th>{$tupName}</th>
                      <th width='40%'>Outstanding/Carry Over</th>
                    </tr>
                </thead>";
        
        foreach($suspension as $count => $std) {

            $html .= "<tr>
              <td>".($count+1)."</td>
              <td><a href='../student/profile.php?stid={$std['matric']}' target= '_blank '>{$std['matric']}</a></td>   
              <td>{$std['name']}</td>
              <td>{$std['sex']}</td>
              <td>{$std['cum']['gpa']}</td> 
              <td>{$std['cum']['tup']}</td>
              <td>{$std['ref']}</td>
            </tr>";

        }
      
        $html .= '</table><br/><br/>';
    }else {
      $html .= '<p>NIL</p><br/><br/>';
    }
        
    $html .= '
    <p>H. Determination of Studentship</p>
    <p>THE STUDENTSHIP OF THE FOLOWING '.count($studentship).'CANDIDATES IS RECOMMENDED 
    FOR DETERMINATION FOR FAILING TO REGISTER FOR TWO CONSECUTIVE SEMESTERS:</p>';
    if(!empty($suspension)) {
        $html .= "<table width='850' class='table table-striped table-bordered'>
                <thead>
                    <tr>
                      <th>S/N</th>
                      <th>Matric</th>
                      <th>Name</th>
                      <th>Sex</th>                    
                      <th>{$gpaName}</th>
                      <th>{$tupName}</th>
                      <th width='40%'>Outstanding/Carry Over</th>
                    </tr>
                </thead>";
        
        foreach($suspension as $count => $std) {

            $html .= "<tr>
              <td>".($count+1)."</td>
              <td><a href='../student/profile.php?stid={$std['matric']}' target= '_blank '>{$std['matric']}</a></td>   
              <td>{$std['name']}</td>
              <td>{$std['sex']}</td>
              <td>{$std['cum']['gpa']}</td> 
              <td>{$std['cum']['tup']}</td>
              <td>{$std['ref']}</td>
            </tr>";

        }
      
        $html .= '</table><br/><br/>';
    }else {
      $html .= '<p>NIL</p><br/><br/>';
    }
   */ 
    $html .= '<p>H. Summary of Result:</p>
    <p>Number of Students in the Class: '.$totalRows_list.'</p>
    <p>Number of Student Examined: '.$examined.'</p>
    <p>Number of Students that Passed: '.count($passlist).'</p>
    <p>Number of Students to be Commended: '.count($commendation).'</p>
    <p>Number of Students with References: '.count($faillist).'</p>
    <br/><br/><br/><br/><br/><br/>';
    
    /*removed ffrom above
    
    <p>Number of Student recommended for Counselling: '.count($counselling).'</p>
    <p>Number of Students on Probation:  '.count($probation).'</p>
    <p>Number of Students recommended for Suspension:  '.count($suspension).'</p>
    <p>Number of Students recommended for Withdrawal: '.count($withdraw).'</p>
    */
    
        
    $width = ($final)? '33%': '50%';
    
    /*
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
        
    */
    
    
    $html .= "
      </tr>
    </table>";      
        

$mpdf->WriteHTML($html);

$mpdf->Output('summary-sheet.pdf', 'I');
exit;