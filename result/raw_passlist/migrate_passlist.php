<?php
if (!isset($_SESSION)) {
  session_start();
}
require_once('../../path.php');

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
    $stud['name'] = strtoupper($row_list['lname']).' '.ucwords(strtolower($row_list['fname'])).' '.ucwords(strtolower($row_list['mname']));    
    $stud['sex'] = $row_list['sex'];
    $cgpa = getCgpa($studid, $prog, $session, $semester, $tams, $examined, $outstanding, $outstanding1, $type, $sesGrad,$row_list['curid']);
    
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
    
    //dc is used for status and unit to force department course data
    $query_cgpa = sprintf("SELECT distinct cr.csid, c.semester, r.tscore, r.escore, r.tscore+ r.escore as score, cr.sesid, "
            . "rg.level, dc.status, dc.unit, c.status as cstatus, c.unit as cunit "
            . "FROM course_reg cr JOIN student st ON cr.stdid = st.stdid "
            . "LEFT JOIN result r ON cr.stdid = r.stdid AND cr.sesid = r.sesid AND cr.csid = r.csid "
            . "JOIN course c ON cr.csid = c.csid  AND c.curid = st.curid "
            . "JOIN registration rg ON cr.sesid = rg.sesid AND cr.stdid = rg.stdid  "
            . "LEFT JOIN department_course dc ON rg.level = dc.level AND rg.progid = dc.progid AND cr.csid = dc.csid "
            . "LEFT JOIN teaching t ON cr.csid = t.csid AND cr.sesid = t.sesid AND t.accepted = 'yes' "
            . "WHERE cr.stdid = %s "
            . "AND cr.csid NOT LIKE 'VOS___' "
           // . "AND rg.course = 'Registered' "
            . "AND (%s) ORDER BY cr.sesid ASC", 
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

if(isset($_POST['migrate']) ){
    
mysql_query("BEGIN", $tams);
   for($i = 0; $i < count($_POST['plist']['stdid']); $i++ ){
       echo $_POST['plist']['stdid'][$i];
      
       
       //Store user record to passlist Table
       $SQL1 = sprintf("INSERT INTO passlist (stdid, gpa, tup, cdegree, sesid) VALUES(%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['plist']['stdid'][$i], "text"),
                       GetSQLValueString($_POST['plist']['gpa'][$i], "text"),
                       GetSQLValueString($_POST['plist']['tup'][$i], "text"),
                       GetSQLValueString($_POST['plist']['cdegree'][$i], "text"),
                       GetSQLValueString($row_rssess['sesid'], "text"));
       mysql_query( $SQL1, $tams) or die(mysql_error());
       
       
       //migrate student course reg to passlist
       $SQL2 = sprintf("INSERT INTO course_reg_passlist (SELECT * FROM course_reg WHERE stdid = %s) ",
                       GetSQLValueString($_POST['plist']['stdid'][$i], "text"));
       mysql_query( $SQL2, $tams) or die(mysql_error());
       
       //migrate student result to passlist
       $SQL3 = sprintf("INSERT INTO result_passlist (SELECT * FROM result WHERE stdid = %s) ",
                       GetSQLValueString($_POST['plist']['stdid'][$i], "text"));
       mysql_query( $SQL3, $tams) or die(mysql_error());
       
       //update student  to passlist
       $SQL4 = sprintf("UPDATE student SET  passlist = 'Yes' WHERE stdid = %s  ",
                       GetSQLValueString($_POST['plist']['stdid'][$i], "text"));
       mysql_query( $SQL4, $tams) or die(mysql_error());
       
       
      
    }
    mysql_query("COMMIT", $tams);
    
   header("location: migrate_passlist.php");
   exit();
}



?>
<!doctype html>
<html>
    <?php include INCPATH . "/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
        <?php include INCPATH . "/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH . "/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH . "/page_header.php" ?>

                    <div class="row-fluid">                        
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3>Migrate Passlist for <?= $row_rssess['sesname']?> <?=$row_info['progname'] ?> </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <form  method="post"  action="migrate_passlist.php">
                                            <div class="row-fluid">
                                                <div class="span12">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                              <th>S/N</th>
                                                              <th>Matric</th>
                                                              <th>Name</th>
                                                              <th>Sex</th>                    
                                                              <th><?=  $gpaName?></th>
                                                              <th><?= $tupName?></th>
                                                              <th>Class of Degree</th>
                                                              <th width='20%'>Remark</th>
                                                            </tr>
                                                        </thead>
                                                        <?php if(!empty($passlist)) { ?>
                                                        <tbody>
                                                        <?php 
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
                                                        ?>
                                                            <tr>
                                                                <td><?= ($count+1)?></td>
                                                                <td><?= $std['matric']?></td>
                                                                <td><?= $std['name'] ?></td>
                                                                <td><?= $std['sex']?></td>                    
                                                                <td><?= $std['cum']['gpa']?></td>
                                                                <td><?= $std['cum']['tup']?></td>
                                                                <td><?= $cdegree ?></td>
                                                                <td>
                                                                    PASSED
                                                                    
                                                                    <input type="hidden" name="plist[stdid][]" value="<?= $std['matric'] ?>"/>
                                                                    <input type="hidden" name="plist[gpa][]" value="<?= $std['cum']['gpa']?>"/>
                                                                    <input type="hidden" name="plist[tup][]" value="<?= $std['cum']['tup'] ?>"/>
                                                                    <input type="hidden" name="plist[cdegree][]" value = "<?= $cdegree ?>"/>
                                                                </td>
                                                            </tr>
                                                            
                                                            <?php } ?>
                                                        </tbody>
                                                        <?php } else { ?>
                                                        <tbody>
                                                            <tr>
                                                                <td colspan="8"> No Pass List to be migrated </td>
                                                            </tr>
                                                        </tbody>
                                                        <?php } ?>
                                                    </table>
                                                </div>
                                            </div>
                                            <?php if(!empty($passlist)) { ?>
                                            <input type="submit" name="migrate" id="submit" value="Migrate To Buffer" class="btn btn-primary"/>
                                            <?php } ?>
                                        </form>
                                    </div>                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH . "/footer.php" ?>
        </div>
    </body>
</html>