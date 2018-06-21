<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');


$auth_users = "1,2,3,4,5,6,10";
check_auth($auth_users, $site_root);

$query_session = "SELECT sesid, sesname FROM session ORDER BY sesid DESC";
$session = mysql_query($query_session, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);
$totalRows_session = mysql_num_rows($session);

$query_rssess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,6";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

$query = '';
if (getAccess() == 3) {
    $query = "AND p.deptid = " . GetSQLValueString(getSessionValue('did'), 'int');
}

if (getAccess() == 2) {
    $query = "AND d.colid = " . GetSQLValueString(getSessionValue('cid'), 'int');
}
// Recordset to populate programme dropdown
$query_prog = sprintf("SELECT p.progid, p.progname, p.duration, d.colid, p.deptid "
        . "FROM programme p, department d "
        . "WHERE d.deptid = p.deptid %s", GetSQLValueString($query, "defined", $query));
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$duration = $row_prog['duration'];

$level = 1;
$prg = $row_prog['progid'];

if (isset($_GET['lvl'])) {
    $level = $_GET['lvl'];
}

if (isset($_GET['pid'])) {
    $prg = $_GET['pid'];
}

$colname_stud = "-1";
if (getAccess() < 7 && isset($_GET['stid'])) {
    $colname_stud = $_GET['stid'];
}

if (getAccess() < 7 && !isset($_GET['stid'])) {
    $colname_stud = '';

    $query_std = sprintf("SELECT s.stdid, s.progid, colid, p.deptid, fname, lname, level "
            . "FROM student s, programme p, department d "
            . "WHERE s.progid = p.progid AND d.deptid = p.deptid "
            . "AND s.progid = %s AND s.level = %s", GetSQLValueString($prg, "text"), GetSQLValueString($level, "text"));
    $std = mysql_query($query_std, $tams) or die(mysql_error());
    $row_std = mysql_fetch_assoc($std);
    $totalRows_std = mysql_num_rows($std);

    if ($totalRows_std > 0) {
        $colname_stud = $row_std['stdid'];
    }
}

if (getAccess() == 10) {
    $colname_stud = getSessionValue('stid');
}

$query_stud = sprintf("SELECT s.progid, colid, p.deptid, fname, lname, level FROM student s, programme p, department d WHERE s.progid = p.progid AND d.deptid = p.deptid AND stdid = %s", GetSQLValueString($colname_stud, "text"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

if (getAccess() < 10) {
    $prg = $row_stud['progid'];
    $level = (isset($row_stud['level'])) ? $row_stud['level'] : $level;
}

$query_studs = sprintf("SELECT stdid, fname, lname FROM student WHERE level = %s AND progid = %s"
        , GetSQLValueString($level, "int")
        , GetSQLValueString($prg, "int"));
$studs = mysql_query($query_studs, $tams) or die(mysql_error());
$row_studs = mysql_fetch_assoc($studs);
$totalRows_studs = mysql_num_rows($studs);

$colname1_year1f = "-1";
if (isset($row_stud['progid'])) {
    $colname1_year1f = $row_stud['progid'];
}

$query_year1f = sprintf("SELECT r.csid, r.sesid, c.csname, c.semester, r.tscore+ r.escore as score, sesname, c.status, unit "
        . "FROM `result` r, course c, session s, teaching t "
        . "WHERE stdid = %s "
        . "AND c.csid = r.csid "
        . "AND r.csid = t.csid "
        . "AND s.sesid = t.sesid "
        . "AND t.approve = 'yes' "
        . "AND s.sesid = r.sesid AND r.csid NOT LIKE %s", GetSQLValueString($colname_stud, "text"), GetSQLValueString("VOS%", "text"));
$year1f = mysql_query($query_year1f, $tams) or die(mysql_error());
$row_year1f = mysql_fetch_assoc($year1f);
$totalRows_year1f = mysql_num_rows($year1f);

$colname_attn = $colname_stud;

$query_attn = sprintf("SELECT s.sesid, s.sesname "
        . "FROM session s, registration r "
        . "WHERE r.status = 'Registered' "
        . "AND s.sesid = r.sesid "
        . "AND stdid = %s "
        . "ORDER BY s.sesname ASC", GetSQLValueString($colname_attn, "text"));
$attn = mysql_query($query_attn, $tams) or die(mysql_error());
$row_attn = mysql_fetch_assoc($attn);
$totalRows_attn = mysql_num_rows($attn);

/* mysql_select_db($database_tams, $tams);
  $query_rsrefs = sprintf("SELECT `result`.csid, course.csname FROM `result`, course WHERE course.csid = `result`.csid AND `result`.tscore+ `result`.escore < 40 AND `result`.stdid =%s AND `result`.csid NOT IN (SELECT csid FROM result WHERE stdid=%s AND `result`.tscore+ `result`.escore > 39)", GetSQLValueString($colname_attn, "text"), GetSQLValueString($colname_attn, "text"));
  $rsrefs = mysql_query($query_rsrefs, $tams) or die(mysql_error());
  $row_rsrefs = mysql_fetch_assoc($rsrefs);
  $totalRows_rsrefs = mysql_num_rows($rsrefs);
 */
$name = "";
if ($_SESSION['MM_UserGroup'] < 6 && isset($_GET['Name'])) {
    $name = "for " . $_GET['Name'];
}

$sCount = 0;
$regSes = array();
$lastSes = 0;

if ($totalRows_attn > 0) {
    $regSes[$sCount] = 0;
    do {
        $regSes[$sCount++] = $row_attn['sesid'];
        $lastSes = ($lastSes > $row_attn['sesid']) ? $lastSes : $row_attn['sesid'];
        $results[$row_attn['sesname']]["first"] = array();
        $results[$row_attn['sesname']]["second"] = array();
    }
    while ($row_attn = mysql_fetch_assoc($attn));
}

// Grading conditions
$query_grad = sprintf("SELECT * FROM grading g, session s WHERE g.sesid = s.sesid AND g.colid = %s", GetSQLValueString(getSessionValue('cid'), "int"));
$grad = mysql_query($query_grad, $tams) or die(mysql_error());
$row_grad = mysql_fetch_assoc($grad);
$totalRows_grad = mysql_num_rows($grad);

$sesGrad = array();
for ($idx = 0; $idx < $totalRows_grad; $idx++, $row_grad = mysql_fetch_assoc($grad)) {
    $sesGrad[$row_grad['sesid']] = array(
        'gradeA' => $row_grad['gradeA'],
        'gradeB' => $row_grad['gradeB'],
        'gradeC' => $row_grad['gradeC'],
        'gradeD' => $row_grad['gradeD'],
        'gradeE' => $row_grad['gradeE'],
        'gradeF' => $row_grad['gradeF'],
        'passmark' => $row_grad['passmark']
    );
}

$sesLimit = (isset($regSes[$sCount - 1])) ? $regSes[$sCount - 1] : 0;
$query_exp = sprintf("SELECT csid, type, passmark, sesname, g.sesid "
        . "FROM grade_exceptions g, session s "
        . "WHERE g.sesid = s.sesid "
        . "AND ((g.unitid = %s AND g.type = 'College') OR (g.unitid = %s AND g.type = 'Department')) "
        . "AND g.sesid <= %s "
        . "ORDER BY sesid DESC, csid, type", GetSQLValueString(getSessionValue('cid'), "int"), GetSQLValueString(getSessionValue('did'), "int"), GetSQLValueString($sesLimit, "int"));
$exp = mysql_query($query_exp, $tams) or die(mysql_error());
$row_exp = mysql_fetch_assoc($exp);
$totalRows_exp = mysql_num_rows($exp);

$sesExp = array();
for ($idx = 0; $idx < $totalRows_exp; $idx++, $row_exp = mysql_fetch_assoc($exp)) {
    $sesExp[$row_exp['csid']][$row_exp['sesid']] = $row_exp['passmark'];
}

// Outstanding courses
$colname_out1 = "AND ((csid LIKE '___0%') AND csid NOT LIKE 'VOS%')";
if ($row_stud['level'] > 1) {
    $colname_out1 = 'AND ((';
    for ($i = 1; $i <= $row_stud['level'] - 1; $i++) {
        $colname_out1 .= "csid LIKE '___{$i}%' OR ";
    }
    $colname_out1 .= "csid LIKE '___{$row_stud['level']}%') AND csid NOT LIKE 'VOS%')";
}

$query_courses = sprintf("SELECT * "
        . "FROM department_course "
        . "WHERE progid = %s %s ",
//        . "AND csid "
//        . "NOT IN(SELECT DISTINCT csid FROM result WHERE stdid=%s)",									
        GetSQLValueString($row_stud['progid'], "int"), GetSQLValueString($colname_out1, "defined", $colname_out1));
//        GetSQLValueString($colname_attn, "text"));
$courses = mysql_query($query_courses, $tams) or die(mysql_error());
$row_courses = mysql_fetch_assoc($courses);
$totalRows_courses = mysql_num_rows($courses);

$outstanding = array();
for ($i = 0; $i < $totalRows_courses; $i++, $row_courses = mysql_fetch_assoc($courses)) {
    $outstanding[] = $row_courses['csid'];
}

do {
    if (in_array($row_year1f['sesid'], $regSes)) {
        $mark = getPassmark($row_year1f['sesid'], $row_year1f['csid']);

        if ($mark <= $row_year1f['score']) {
            $key = array_search($row_year1f['csid'], $outstanding);
            unset($outstanding[$key]);
        }
        if ($row_year1f['semester'] == "F") {
            $results[$row_year1f['sesname']]['first'][] = $row_year1f;
        }
        else {
            $results[$row_year1f['sesname']]['second'][] = $row_year1f;
        }
    }
}
while ($row_year1f = mysql_fetch_assoc($year1f));

// Failed courses
//$query_rsrefs = sprintf("SELECT DISTINCT r.csid, d.status, unit "
//        . "FROM `result` r, department_course d, student s, department dt, grading g "
//        . "WHERE d.csid = r.csid "
//        . "AND r.stdid = s.stdid "
//        . "AND dt.deptid = d.deptid "
//        . "AND g.sesid = r.sesid "
//        . "AND g.colid = dt.colid "
//        . "AND d.progid = s.progid "
//        . "AND tscore + escore <= gradeF "
//        . "AND r.stdid = %s "
//        . "AND r.sesid <> %s "
//        . "AND r.csid "
//        . "NOT IN (SELECT csid FROM result WHERE stdid = %s AND tscore + escore > gradeF AND sesid <> %s)", 
//        GetSQLValueString($colname_attn, "text"), 
//        GetSQLValueString($row_rssess['sesid'], "int"),
//        GetSQLValueString($colname_attn, "text"), 
//        GetSQLValueString($row_rssess['sesid'], "int"));
//$rsrefs = mysql_query($query_rsrefs, $tams) or die(mysql_error());
//$row_rsrefs = mysql_fetch_assoc($rsrefs);
//$totalRows_rsrefs = mysql_num_rows($rsrefs);
// To reset the recordset to be used in the transcript table
$rows = mysql_num_rows($attn);
if ($rows > 0) {
    mysql_data_seek($attn, 0);
    $row_attn = mysql_fetch_assoc($attn);
}

$pcgp = 0;
$put = 0;
$pup = 0;

if (isset($_GET['sesid']) && $_GET['sesid'] == $_SESSION['sesid']) {
    $pcgp = 0;
    $put = 0;
    $pup = 0;
}

$tgp1 = 0;
$tgp2 = 0;
$tut1 = 0;
$tut2 = 0;
$tup1 = 0;
$tup2 = 0;

function gradepoint($unit, $score, $sesid) {
    global $sesGrad;

    if (!isset($sesid)) {
        return '-';
    }

    $grades = $sesGrad[$sesid];
    $gp = 0;
    if ($score < $grades['gradeF'])
        $gp = 0;
    else if ($score < $grades['gradeE'])
        $gp = 1;
    else if ($score < $grades['gradeD'])
        $gp = 2;
    else if ($score < $grades['gradeC'])
        $gp = 3;
    else if ($score < $grades['gradeB'])
        $gp = 4;
    else if ($score <= $grades['gradeA'])
        $gp = 5;

    return $gp * $unit;
}

function getRem($score, $sesid, $csid) {
    if (!isset($sesid)) {
        return '-';
    }

    $passmark = getPassmark($sesid, $csid);
    $remark = 'Failed';

    if ($score >= $passmark)
        $remark = 'Passed';

    return $remark;
}

function unitPassed($unit, $score, $sesid, $csid) {

    if (!isset($sesid)) {
        return '-';
    }

    $passmark = getPassmark($sesid, $csid);

    return ($score < $passmark) ? 0 : $unit;
}

function getPassmark($sesid, $csid) {
    global $sesGrad;
    global $sesExp;

    $default = (isset($sesGrad[$sesid]['passmark'])) ? $sesGrad[$sesid]['passmark'] : $sesGrad[$sesid]['gradeF'] + 1;

    // Enforce normal grade in 100 level
    if (substr($csid, 3, 1) == '1') {
        $default = $sesGrad[$sesid]['gradeF'] + 1;
    }

    if (empty($sesExp[$csid])) {
        return $default;
    }

    $expMark = NULL;
    $keys = array_keys($sesExp[$csid]);

    foreach ($keys as $value) {
        if ($value > $sesid)
            continue;

        if (isset($sesExp[$csid][$value]))
            $expMark = $sesExp[$csid][$value];

        break;
    }

    return (isset($expMark)) ? $expMark : $default;
}

function calculateGpa($points, $units) {
    return number_format(round(($points) / ($units), 2), 2);
}

//$option = array();
//$count = 0;
//
//if($totalRows_rsrefs > 0) {
//    do {  
//        $option[$count] = $row_rsrefs['csid'];         
//        $count++;
//    } while ($row_rsrefs = mysql_fetch_assoc($rsrefs));
//}
// Recordset to check if a college is medical or not
$query_special = sprintf("SELECT c.special "
        . "FROM programme p, department d, college c "
        . "WHERE d.deptid = p.deptid AND p.progid= %s", GetSQLValueString($prg, "int"));
$special = mysql_query($query_special, $tams) or die(mysql_error());
$row_special = mysql_fetch_assoc($special);
$totalRows_special = mysql_num_rows($special);

$special_college = $row_special['special'];

$did = "-1";
if (isset($row_stud['deptid'])) {
    $did = $row_stud['deptid'];
}

$cid = "-1";
if (isset($row_stud['colid'])) {
    $cid = $row_stud['colid'];
}

$allow = false;
$acl = array(4, 5, 6);
if (getAccess() == 1 || (getAccess() == 2 && getSessionValue('cid') == $cid) || (getAccess() == 3 && getSessionValue('did') == $did) || (in_array(getAccess(), $acl) && getSessionValue('did') == $did) || getSessionValue('stid') == $colname_stud) {
    $allow = true;
}



$page_title = "Tasued";
?>
<!doctype html>
<html>
    <?php include INCPATH."/header.php" ?>

    <style>        
        /*transcript table hiding*/
        @media screen and (max-width: 420px) {
            .hidecol { display: none; }
        }
    </style>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
        <?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH."/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH."/page_header.php" ?>
<!--                    <div class="breadcrumbs">
                        <ul>
                            <li>
                                <a href="more-login.html">Home</a><i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="student.php">Profile</a><i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="transcript.php">Transcript</a>
                            </li>
                        </ul>
                        <div class="close-bread">
                            <a href="#"><i class="icon-remove"></i></a>
                        </div>
                    </div>-->

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box nopadding">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Statement of Result
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <?php if ($allow) { ?>
                                    <?php if (in_array(getAccess(), [1,2,3,4,5,6])) { ?>
                                        <div class=" form form-vertical">
                                            <div class="row-fluid">
                                                <form method="post" action="gentranscript.php" target="_blank">
                                                    <input type="hidden" name="stid" value="<?php echo $colname_stud ?>"/>
                                                    <input type="submit" class="btn btn-darkblue" value="Print Transcript"/>
                                                </form>
                                            </div>

                                            <div class="row-fluid">
                                                <div class="span4">
                                                    <div class="control-group">
                                                        <label class="control-label" for="textfield">Search by Programme</label>
                                                        <div class="controls controls-row ">
                                                            <select  onChange="progfilt(this)" name="stdid" style="width:200px">
                                                                <?php
                                                                do {
                                                                    if ($row_prog['duration'] > $duration) {
                                                                        $duration = $row_prog['duration'];
                                                                    }
                                                                    ?>
                                                                    <option <?php if ($prg == $row_prog['progid']) echo "selected"; ?> value="<?php echo $row_prog['progid'] ?>"><?php echo $row_prog['progname'] ?></option>
                                                                    <?php
                                                                } while ($row_prog = mysql_fetch_assoc($prog));
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="span4">
                                                    <div class="control-group">
                                                        <label class="control-label" for="textfield">Search by level</label>
                                                        <div class="controls controls-row">
                                                            <select onChange="lvlfilt(this)">
                                                                <?php for ($idx = 1; $idx <= $duration; $idx++) { ?>
                                                                    <option value="<?php echo $idx ?>" <?php if ($level == $idx) echo 'selected'; ?>>
                                                                        <?php echo $idx . '00' ?>
                                                                    </option>
                                                                    <?php
                                                                }

                                                                if ($duration > 0) {
                                                                    ?>          
                                                                    <option value="<?php echo $duration + 1 ?>" <?php if ($level == $duration + 1) echo 'selected'; ?>>
                                                                        Extra Year 1
                                                                    </option>
                                                                    <option value="<?php echo $duration + 2 ?>" <?php if ($level == $duration + 2) echo 'selected'; ?>>
                                                                        Extra Year 2
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="span4">
                                                    <div class="control-group">
                                                        <label class="control-label" for="textfield">Search By Student</label>
                                                        <div class="controls controls-row">
                                                            <select onChange="studfilt(this)" name="stdid">
                                                                <?php
                                                                do {
                                                                    ?>
                                                                    <option <?php if ($colname_stud == $row_studs['stdid']) echo "selected"; ?> value="<?php echo $row_studs['stdid'] ?>"><?php echo ucwords(strtolower($row_studs['fname'] . " " . $row_studs['lname'])) . "(" . $row_studs['stdid'] . ")" ?></option>
                                                                    <?php
                                                                } while ($row_studs = mysql_fetch_assoc($studs));
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    
                                    
                                    <div class="row-fluid">
                                        
                                        <!--First Table-->
                                        <?php if ($sCount > 0) { ?>
                                        
                                        <div class="box box box-color">
                                            <div class="box-title">
                                                <h3><i class="icon-reorder"></i>
                                                    Statement of Result for <?php echo $row_attn['sesname'] ?>
                                                </h3>
                                            </div>
                                            <div class="box-content nopadding">                                            
                                                <div class="span12">
                                                    <div class="box box-bordered box-color span6">                                                            
                                                        <div class="box-title">
                                                            <h3>
                                                                <i class="icon-table"></i>
                                                                First Semester
                                                            </h3>
                                                        </div>
                                                        <div class="box-content">
                                                            <div class="row-fluid">
                                                                <table class="table table-hover table-striped">                                                             
                                                                    <thead>
                                                                        <th>Code</th>
                                                                        <th>Score</th>
                                                                        <th>Unit</th>
                                                                        <th class="hidecol">ST</th>
                                                                        <th>GP</th>
                                                                        <th class="hidecol">NUP</th>                                                                    
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php
                                                                            for ($i = 0; $i < count($results[$row_attn['sesname']]['first']); $i++) {
                                                                                $result = $results[$row_attn['sesname']]['first'][$i];
                                                                                ?>
                                                                        <tr>
                                                                            <td><?= strtoupper($result['csid']); ?></td>
                                                                            <td><?= $result['score']; ?></td>
                                                                            <td><?php $tut1 += $result['unit'];echo $result['unit']; ?></td>
                                                                            <td class="hidecol"><?= getStatusAlpha($result['status']) ?></td>
                                                                            <td> 
                                                                                <?php
                                                                                $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']);
                                                                                $tgp1 += $val;
                                                                                echo $val;
                                                                                ?>
                                                                            </td>
                                                                            <td class="hidecol">
                                                                                <?php
                                                                                $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']);
                                                                                $tup1 += $val;
                                                                                echo $val;
                                                                                ?>
                                                                            </td>
                                                                        </tr>
                                                                        <?php } ?>
                                                                    </tbody>
                                                                </table> 
                                                            </div>
                                                            <br>
                                                            <div class="row-fluid">
                                                                <table class="table table-hover table-nomargin">                                                                
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="hidecol"> </th>
                                                                            <th>TUT</th>
                                                                            <th>TUP</th>
                                                                            <th>TGP</th>
                                                                            <th>GPA</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <th class="hidecol">PREV</th>
                                                                            <td><?= $put; ?></td>
                                                                            <td><?= $pup; ?></td>
                                                                            <td><?= $pcgp; ?></td>
                                                                            <td><?= "0.00"; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="hidecol">CUR</th>
                                                                            <td><?= $tut1; ?></td>
                                                                            <td><?= $tup1; ?> </td>
                                                                            <td><?= $tgp1; ?></td>
                                                                            <td><?= ($tut1 > 0) ? calculateGpa($tgp1, $tut1) : "0.00"; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="hidecol">CUM</th>
                                                                            <td><?= $put += $tut1; ?></td>
                                                                            <td><?= $pup += $tup1; ?></td>
                                                                            <td><?= $pcgp += $tgp1; ?></td>
                                                                            <td><?= ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>                                                             
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="box box-bordered box-color span6">                                                            
                                                        <div class="box-title">
                                                            <h3>
                                                                <i class="icon-table"></i>
                                                                Second Semester
                                                            </h3>
                                                        </div>
                                                        <div class="box-content">
                                                            <div class="row-fluid">
                                                                <table class="table table-hover table-striped">                                                             
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Code</th>
                                                                            <th>Score</th>
                                                                            <th>Unit</th>
                                                                            <th class="hidecol">ST</th>
                                                                            <th>GP</th>
                                                                            <th class="hidecol">NUP</th>  
                                                                        </tr>                                                                     
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php
                                                                            for ($i = 0; $i < count($results[$row_attn['sesname']]['second']); $i++) {
                                                                            $result = $results[$row_attn['sesname']]['second'][$i];
                                                                        ?>
                                                                        <tr>
                                                                            <td><?= strtoupper($result['csid']); ?></td>
                                                                            <td><?= $result['score']; ?></td>
                                                                            <td><?php $tut2 += $result['unit']; echo $result['unit']; ?></td>
                                                                            <td class="hidecol"><?= getStatusAlpha($result['status']) ?></td>
                                                                            <td>
                                                                                <?php
                                                                                $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']);
                                                                                $tgp2 += $val;
                                                                                echo $val;
                                                                                ?>
                                                                            </td>
                                                                            <td class="hidecol">
                                                                                <?php
                                                                                $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']);
                                                                                $tup2 += $val;
                                                                                echo $val;
                                                                                ?>
                                                                            </td>
                                                                        </tr>
                                                                        <?php } ?>
                                                                    </tbody>                                                                
                                                                </table> 
                                                            </div>
                                                            <br>
                                                            <div class="row-fluid">
                                                                <table class="table table-hover table-nomargin">                                                                
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="hidecol"> </th>
                                                                            <th>TUT</th>
                                                                            <th>TUP</th>
                                                                            <th>TGP</th>
                                                                            <th>GPA</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <th class="hidecol">PREV</th>
                                                                            <td><?= $put; ?></td>
                                                                            <td><?= $pup; ?></td>
                                                                            <td><?= $pcgp; ?></td>
                                                                            <td><?= ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="hidecol">CUR</th>
                                                                            <td><?= $tut2; ?></td>
                                                                            <td><?= $tup2; ?> </td>
                                                                            <td><?= $tgp2; ?></td>
                                                                            <td><?= ($tut2 > 0) ? calculateGpa($tgp2, $tut2) : "0.00"; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="hidecol">CUM</th>
                                                                            <td><?= $put += $tut2; ?></td>
                                                                            <td><?= $pup += $tup2; ?></td>
                                                                            <td><?= $pcgp += $tgp2; ?></td>
                                                                            <td><?= ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>                                                             
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>                                                                                                                            
                                            </div>
                                        </div>
                                        <?php } ?>
                                        
                                        
                                        <p>&nbsp;</p>
                                        
                                    <!--Second table-->
                                    
                                    <?php
                                    $row_attn = mysql_fetch_assoc($attn);
                                    if ($sCount > 1) {
                                        $tgp1 = 0;
                                        $tgp2 = 0;
                                        $tut1 = 0;
                                        $tut2 = 0;
                                        $tup1 = 0;
                                        $tup2 = 0;
                                        ?>
                                        <div class="box box-color">
                                            <div class="box-title">
                                                <h3><i class="icon-reorder"></i>
                                                    Statement of Result for <?php echo $row_attn['sesname']; ?>
                                                </h3>
                                            </div>
                                            <div class="box-content nopadding">                                            
                                                <div class="span12">
                                                    <div class="box box-bordered box-color span6">                                                            
                                                        <div class="box-title">
                                                            <h3>
                                                                <i class="icon-table"></i>
                                                                First Semester
                                                            </h3>
                                                        </div>
                                                        <div class="box-content">
                                                            <div class="row-fluid">
                                                                <table class="table table-hover table-striped"> 
                                                                    <thead>
                                                                        <th>Code</th>
                                                                        <th>Score</th>
                                                                        <th>Unit</th>
                                                                        <th class="hidecol">ST</th>
                                                                        <th>GP</th>
                                                                        <th class="hidecol">NUP</th>                                                                    
                                                                    </thead>     
                                                                    <tbody>
                                                                        <?php
                                                                        for ($i = 0; $i < count($results[$row_attn['sesname']]['first']); $i++) {
                                                                            $result = $results[$row_attn['sesname']]['first'][$i];
                                                                            ?>
                                                                        <tr>
                                                                            <td><?= strtoupper($result['csid']); ?></td>
                                                                            <td><?= $result['score']; ?></td>
                                                                            <td><?php $tut1 += $result['unit']; echo $result['unit']; ?></td>
                                                                            <td class="hidecol"><?= getStatusAlpha($result['status']) ?></td>
                                                                            <td>
                                                                                <?php
                                                                                $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']);
                                                                                $tgp1 += $val;
                                                                                echo $val;
                                                                                ?>
                                                                            </td>
                                                                            <td class="hidecol">
                                                                                <?php
                                                                                $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']);
                                                                                $tup1 += $val;
                                                                                echo $val;
                                                                                ?>
                                                                            </td>
                                                                        <?php }?>
                                                                        </tr>
                                                                    </tbody>
                                                                </table> 
                                                            </div>
                                                            <br>
                                                            <div class="row-fluid">
                                                                <table class="table table-hover table-nomargin">                                                                
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="hidecol"> </th>
                                                                            <th>TUT</th>
                                                                            <th>TUP</th>
                                                                            <th>TGP</th>
                                                                            <th>GPA</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <th class="hidecol">PREV</th>
                                                                            <td><?= $put; ?></td>
                                                                            <td><?= $pup; ?></td>
                                                                            <td><?= $pcgp; ?></td>
                                                                            <td><?= "0.00"; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="hidecol">CUR</th>
                                                                            <td><?= $tut1; ?></td>
                                                                            <td><?= $tup1; ?> </td>
                                                                            <td><?= $tgp1; ?></td>
                                                                            <td><?= ($tut1 > 0) ? calculateGpa($tgp1, $tut1) : "0.00"; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="hidecol">CUM</th>
                                                                            <td><?= $put += $tut1; ?></td>
                                                                            <td><?= $pup += $tup1; ?></td>
                                                                            <td><?= $pcgp += $tgp1; ?></td>
                                                                            <td><?= ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                        </tr>
                                                                        
                                                                    </tbody>
                                                                </table>                                                             
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="box box-bordered box-color span6">                                                            
                                                        <div class="box-title">
                                                            <h3>
                                                                <i class="icon-table"></i>
                                                                Second Semester
                                                            </h3>
                                                        </div>
                                                        <div class="box-content">
                                                            <div class="row-fluid">
                                                                <table class="table table-hover table-striped">
                                                                    <thead>
                                                                        <th>Code</th>
                                                                        <th>Score</th>
                                                                        <th>Unit</th>
                                                                        <th class="hidecol">ST</th>
                                                                        <th>GP</th>
                                                                        <th class="hidecol">NUP</th>                                                                    
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php 
                                                                        for ($i = 0; $i < count($results[$row_attn['sesname']]['second']); $i++) {
                                                                            $result = $results[$row_attn['sesname']]['second'][$i];
                                                                        ?>
                                                                        <tr>
                                                                            <td><?= strtoupper($result['csid']); ?></td>
                                                                            <td><?= $result['score']; ?></td>
                                                                            <td><?php $tut2 += $result['unit']; echo $result['unit']; ?></td>
                                                                            <td class="hidecol"><?= getStatusAlpha($result['status']) ?></td>
                                                                            <td>
                                                                                <?php
                                                                                $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']);
                                                                                $tgp2 += $val;
                                                                                echo $val;
                                                                                ?>
                                                                            </td>
                                                                            <td class="hidecol">
                                                                                <?php
                                                                                $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']);
                                                                                $tup2 += $val;
                                                                                echo $val;
                                                                                ?>
                                                                            </td>
                                                                        </tr> 
                                                                        <?php }?>
                                                                    </tbody>
                                                                </table> 
                                                            </div>
                                                            <br>
                                                            <div class="row-fluid">
                                                                <table class="table table-hover table-nomargin">                                                                
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="hidecol"> </th>
                                                                            <th>TUT</th>
                                                                            <th>TUP</th>
                                                                            <th>TGP</th>
                                                                            <th>GPA</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <th class="hidecol">PREV</th>
                                                                            <td><?php echo $put; ?></td>
                                                                            <td><?php echo $pup; ?></td>
                                                                            <td><?php echo $pcgp; ?></td>
                                                                            <td><?php echo ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="hidecol">CUR</th>
                                                                            <td><?php echo $tut2; ?></td>
                                                                            <td><?php echo $tup2; ?> </td>
                                                                            <td><?php echo $tgp2; ?></td>
                                                                            <td><?php echo ($tut2 > 0) ? calculateGpa($tgp2, $tut2) : "0.00"; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="hidecol">CUM</th>
                                                                            <td><?php echo $put += $tut2; ?></td>
                                                                            <td><?php echo $pup += $tup2; ?></td>
                                                                            <td><?php echo $pcgp += $tgp2; ?></td>
                                                                            <td><?php echo ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>                                                             
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>                                                                                                                            
                                            </div>
                                        </div>
                                        
                                        <?php } ?>
                                        
                                        <p>&nbsp;</p>
                                        
                                        
                                        <!--third table-->
                                        <?php
                                        $row_attn = mysql_fetch_assoc($attn);
                                        if ($sCount > 2) {
                                            $tgp1 = 0;
                                            $tgp2 = 0;
                                            $tut1 = 0;
                                            $tut2 = 0;
                                            $tup1 = 0;
                                            $tup2 = 0;
                                            ?>
                                        <div class="box box-color">
                                            <div class="box-title">
                                                <h3><i class="icon-reorder"></i>
                                                    Statement of Result for <?php echo $row_attn['sesname']; ?>
                                                </h3>
                                            </div>
                                            <div class="box-content nopadding">                                            
                                                <div class="span12">
                                                    <div class="box box-bordered box-color span6">                                                            
                                                        <div class="box-title">
                                                            <h3>
                                                                <i class="icon-table"></i>
                                                                First Semester
                                                            </h3>
                                                        </div>
                                                        <div class="box-content">
                                                            <div class="row-fluid">
                                                                <table class="table table-hover table-striped table-condensed">
                                                                    <thead>
                                                                        <th>Code</th>
                                                                        <th>Score</th>
                                                                        <th>Unit</th>
                                                                        <th class="hidecol">ST</th>
                                                                        <th>GP</th>
                                                                        <th class="hidecol">NUP</th>                                                                    
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php
                                                                        for ($i = 0; $i < count($results[$row_attn['sesname']]['first']); $i++) {
                                                                            $result = $results[$row_attn['sesname']]['first'][$i];
                                                                            ?>
                                                                        <tr>
                                                                            <td><?php echo strtoupper($result['csid']); ?></td>
                                                                            <td><?php echo $result['score']; ?></td>
                                                                            <td>
                                                                                <?php $tut1 += $result['unit'];
                                                                                echo $result['unit'];?>
                                                                            </td>
                                                                            <td><?php echo getStatusAlpha($result['status']) ?></td>
                                                                            <td width="19" align="center">
                                                                                <?php
                                                                                $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']);
                                                                                $tgp1 += $val;
                                                                                echo $val;
                                                                                ?>
                                                                            </td>
                                                                            <td class="hidecol">
                                                                                <?php
                                                                                $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']);
                                                                                $tup1 += $val;
                                                                                echo $val;
                                                                                ?>
                                                                            </td>
                                                                        </tr> 
                                                                        <?php }?>
                                                                    </tbody>
                                                                </table> 
                                                            </div>
                                                            <br>
                                                            <div class="row-fluid">
                                                                <table class="table table-hover table-nomargin">                                                                
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="hidecol"> </th>
                                                                            <th>TUT</th>
                                                                            <th>TUP</th>
                                                                            <th>TGP</th>
                                                                            <th>GPA</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <th class="hidecol">PREV</th>
                                                                            <td><?php echo $put; ?></td>
                                                                            <td><?php echo $pup; ?></td>
                                                                            <td><?php echo $pcgp; ?></td>
                                                                            <td><?php echo ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="hidecol">CUR</th>
                                                                            <td><?php echo $tut1; ?></td>
                                                                            <td><?php echo $tup1; ?> </td>
                                                                            <td><?php echo $tgp1; ?></td>
                                                                            <td><?php echo ($tut1 > 0) ? calculateGpa($tgp1, $tut1) : "0.00"; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="hidecol">CUM</th>
                                                                            <td><?php echo $put += $tut1; ?></td>
                                                                            <td><?php echo $pup += $tup1; ?></td>
                                                                            <td><?php echo $pcgp += $tgp1; ?></td>
                                                                            <td><?php echo ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>                                                             
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="box box-bordered box-color span6">                                                            
                                                        <div class="box-title">
                                                            <h3>
                                                                <i class="icon-table"></i>
                                                                Second Semester
                                                            </h3>
                                                        </div>
                                                        <div class="box-content">
                                                            <div class="row-fluid">
                                                                <table class="table table-hover table-striped">
                                                                    <thead>
                                                                        <th>Code</th>
                                                                        <th>Score</th>
                                                                        <th>Unit</th>
                                                                        <th class="hidecol">ST</th>
                                                                        <th>GP</th>
                                                                        <th class="hidecol">NUP</th>                                                                    
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php
                                                                        for ($i = 0; $i < count($results[$row_attn['sesname']]['second']); $i++) {
                                                                            $result = $results[$row_attn['sesname']]['second'][$i];
                                                                            ?>
                                                                            <tr>
                                                                                <td><?php echo strtoupper($result['csid']); ?></td>
                                                                                <td><?php echo $result['score']; ?></td>
                                                                                <td>
                                                                                    <?php $tut2 += $result['unit'];
                                                                                    echo $result['unit']; ?>
                                                                                </td>
                                                                                <td class="hidecol"><?php echo getStatusAlpha($result['status']) ?></td>
                                                                                <td width="19" align="center">
                                                                                    <?php
                                                                                    $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']);
                                                                                    $tgp2 += $val;
                                                                                    echo $val;
                                                                                    ?>
                                                                                </td>
                                                                                <td class="hidecol">
                                                                                    <?php
                                                                                    $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']);
                                                                                    $tup2 += $val;
                                                                                    echo $val;
                                                                                    ?>
                                                                                </td>
                                                                            </tr>
                                                                         <?php } ?>
                                                                    </tbody>
                                                                </table> 
                                                            </div>
                                                            <br>
                                                            <div class="row-fluid">
                                                                <table class="table table-hover table-nomargin">                                                                
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="hidecol"> </th>
                                                                            <th>TUT</th>
                                                                            <th>TUP</th>
                                                                            <th>TGP</th>
                                                                            <th>GPA</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <th class="hidecol">PREV</th>
                                                                            <td><?php echo $put; ?></td>
                                                                            <td><?php echo $pup; ?></td>
                                                                            <td><?php echo $pcgp; ?></td>
                                                                            <td><?php echo ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="hidecol">CUR</th>
                                                                            <td><?php echo $tut2; ?></td>
                                                                            <td><?php echo $tup2; ?> </td>
                                                                            <td><?php echo $tgp2; ?></td>
                                                                            <td><?php echo ($tut2 > 0) ? calculateGpa($tgp2, $tut2) : "0.00"; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="hidecol">CUM</th>
                                                                            <td><?php echo $put += $tut2; ?></td>
                                                                            <td><?php echo $pup += $tup2; ?></td>
                                                                            <td><?php echo $pcgp += $tgp2; ?></td>
                                                                            <td><?php echo ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>                                                             
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>                                                                                                                            
                                            </div>
                                        </div>
                                        
                                        <?php }?>
                                        
                                        
                                        <!--fourth table-->
                                        <?php
                                        $row_attn = mysql_fetch_assoc($attn);
                                        if ($sCount > 3) {
                                        $tgp1 = 0;
                                        $tgp2 = 0;
                                        $tut1 = 0;
                                        $tut2 = 0;
                                        $tup1 = 0;
                                        $tup2 = 0;
                                        ?>
                                        
                                        <div class="box box-color">
                                                    <div class="box-title">
                                                        <h3><i class="icon-reorder"></i>
                                                            Statement of Result for <?php echo $row_attn['sesname']; ?>
                                                        </h3>
                                                    </div>
                                                    <div class="box-content nopadding">                                            
                                                        <div class="span12">
                                                            <div class="box box-bordered box-color span6">                                                            
                                                                <div class="box-title">
                                                                    <h3>
                                                                        <i class="icon-table"></i>
                                                                        First Semester
                                                                    </h3>
                                                                </div>
                                                                <div class="box-content">
                                                                    <div class="row-fluid">
                                                                        <table class="table table-hover table-striped">
                                                                            <thead>
                                                                            <th>Code</th>
                                                                            <th>Score</th>
                                                                            <th>Unit</th>
                                                                            <th class="hidecol">ST</th>
                                                                            <th>GP</th>
                                                                            <th class="hidecol">NUP</th>                                                                    
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php
                                                                                for ($i = 0; $i < count($results[$row_attn['sesname']]['first']); $i++) {
                                                                                    $result = $results[$row_attn['sesname']]['first'][$i];
                                                                                    ?>
                                                                                    <tr>
                                                                                        <td><?php echo strtoupper($result['csid']); ?></td>
                                                                                        <td><?php echo $result['score']; ?></td>
                                                                                        <td>
                                                                                            <?php $tut1 += $result['unit'];
                                                                                            echo $result['unit'];
                                                                                            ?>
                                                                                        </td>
                                                                                        <td><?php echo getStatusAlpha($result['status']) ?></td>
                                                                                        <td width="19" align="center">
                                                                                            <?php
                                                                                            $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']);
                                                                                            $tgp1 += $val;
                                                                                            echo $val;
                                                                                            ?>
                                                                                        </td>
                                                                                        <td class="hidecol">
                                                                                            <?php
                                                                                            $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']);
                                                                                            $tup1 += $val;
                                                                                            echo $val;
                                                                                            ?>
                                                                                        </td>
                                                                                    </tr> 
                                                                                <?php } ?>
                                                                            </tbody>
                                                                        </table> 
                                                                    </div>
                                                                    <br>
                                                                    <div class="row-fluid">
                                                                        <table class="table table-hover table-nomargin">                                                                
                                                                            <thead>
                                                                                <tr>
                                                                                    <th class="hidecol"> </th>
                                                                                    <th>TUT</th>
                                                                                    <th>TUP</th>
                                                                                    <th>TGP</th>
                                                                                    <th>GPA</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <th class="hidecol">PREV</th>
                                                                                    <td><?php echo $put; ?></td>
                                                                                    <td><?php echo $pup; ?></td>
                                                                                    <td><?php echo $pcgp; ?></td>
                                                                                    <td><?php echo ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th class="hidecol">CUR</th>
                                                                                    <td><?php echo $tut1; ?></td>
                                                                                    <td><?php echo $tup1; ?> </td>
                                                                                    <td><?php echo $tgp1; ?></td>
                                                                                    <td><?php echo ($tut1 > 0) ? calculateGpa($tgp1, $tut1) : "0.00"; ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th class="hidecol">CUM</th>
                                                                                    <td><?php echo $put += $tut1; ?></td>
                                                                                    <td><?php echo $pup += $tup1; ?></td>
                                                                                    <td><?php echo $pcgp += $tgp1; ?></td>
                                                                                    <td><?php echo ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>                                                             
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="box box-bordered box-color span6">                                                            
                                                                <div class="box-title">
                                                                    <h3>
                                                                        <i class="icon-table"></i>
                                                                        Second Semester
                                                                    </h3>
                                                                </div>
                                                                <div class="box-content">
                                                                    <div class="row-fluid">
                                                                        <table class="table table-hover table-striped">
                                                                            <thead>
                                                                            <th>Code</th>
                                                                            <th>Score</th>
                                                                            <th>Unit</th>
                                                                            <th class="hidecol">ST</th>
                                                                            <th>GP</th>
                                                                            <th class="hidecol">NUP</th>                                                                    
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php
                                                                                for ($i = 0; $i < count($results[$row_attn['sesname']]['second']); $i++) {
                                                                                    $result = $results[$row_attn['sesname']]['second'][$i];
                                                                                    ?>
                                                                                    <tr>
                                                                                        <td><?php echo strtoupper($result['csid']); ?></td>
                                                                                        <td><?php echo $result['score']; ?></td>
                                                                                        <td>
                                                                                            <?php $tut2 += $result['unit'];
                                                                                            echo $result['unit'];
                                                                                            ?>
                                                                                        </td>
                                                                                        <td class="hidecol"><?php echo getStatusAlpha($result['status']) ?></td>
                                                                                        <td>
                                                                                            <?php
                                                                                            $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']);
                                                                                            $tgp2 += $val;
                                                                                            echo $val;
                                                                                            ?>
                                                                                        </td>
                                                                                        <td class="hidecol">
                                                                                            <?php
                                                                                            $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']);
                                                                                            $tup2 += $val;
                                                                                            echo $val;
                                                                                            ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                <?php } ?>
                                                                            </tbody>
                                                                        </table> 
                                                                    </div>
                                                                    <br>
                                                                    <div class="row-fluid">
                                                                        <table class="table table-hover table-nomargin">                                                                
                                                                            <thead>
                                                                                <tr>
                                                                                    <th class="hidecol"> </th>
                                                                                    <th>TUT</th>
                                                                                    <th>TUP</th>
                                                                                    <th>TGP</th>
                                                                                    <th>GPA</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <th class="hidecol">PREV</th>
                                                                                    <td><?php echo $put; ?></td>
                                                                                    <td><?php echo $pup; ?></td>
                                                                                    <td><?php echo $pcgp; ?></td>
                                                                                    <td><?php echo ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th class="hidecol">CUR</th>
                                                                                    <td><?php echo $tut2; ?></td>
                                                                                    <td><?php echo $tup2; ?> </td>
                                                                                    <td><?php echo $tgp2; ?></td>
                                                                                    <td><?php echo ($tut2 > 0) ? calculateGpa($tgp2, $tut2) : "0.00"; ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th class="hidecol">CUM</th>
                                                                                    <td><?php echo $put += $tut2; ?></td>
                                                                                    <td><?php echo $pup += $tup2; ?></td>
                                                                                    <td><?php echo $pcgp += $tgp2; ?></td>
                                                                                    <td><?php echo ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>                                                             
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>                                                                                                                            
                                                    </div>
                                                </div>
                                        
                                    <?php } ?>
                                        
                                    <!--Fifth table--> 
                                    <?php
                                    $row_attn = mysql_fetch_assoc($attn);
                                    if ($sCount > 4) {
                                        $tgp1 = 0;
                                        $tgp2 = 0;
                                        $tut1 = 0;
                                        $tut2 = 0;
                                        $tup1 = 0;
                                        $tup2 = 0;
                                        ?>
                                        
                                        <div class="box box-color">
                                                    <div class="box-title">
                                                        <h3><i class="icon-reorder"></i>
                                                            Statement of Result for <?php echo $row_attn['sesname']; ?>
                                                        </h3>
                                                    </div>
                                                    <div class="box-content nopadding">                                            
                                                        <div class="span12">
                                                            <div class="box box-bordered box-color span6">                                                            
                                                                <div class="box-title">
                                                                    <h3>
                                                                        <i class="icon-table"></i>
                                                                        First Semester
                                                                    </h3>
                                                                </div>
                                                                <div class="box-content">
                                                                    <div class="row-fluid">
                                                                        <table class="table table-hover table-striped">
                                                                            <thead>
                                                                            <th>Code</th>
                                                                            <th>Score</th>
                                                                            <th>Unit</th>
                                                                            <th class="hidecol">ST</th>
                                                                            <th>GP</th>
                                                                            <th class="hidecol">NUP</th>                                                                    
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php
                                                                                for ($i = 0; $i < count($results[$row_attn['sesname']]['first']); $i++) {
                                                                                    $result = $results[$row_attn['sesname']]['first'][$i];
                                                                                    ?>
                                                                                    <tr>
                                                                                        <td><?php echo strtoupper($result['csid']); ?></td>
                                                                                        <td><?php echo $result['score']; ?></td>
                                                                                        <td>
                                                                                            <?php
                                                                                            $tut1 += $result['unit'];
                                                                                            echo $result['unit'];
                                                                                            ?>
                                                                                        </td>
                                                                                        <td><?php echo getStatusAlpha($result['status']) ?></td>
                                                                                        <td width="19" align="center">
                                                                                            <?php
                                                                                            $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']);
                                                                                            $tgp1 += $val;
                                                                                            echo $val;
                                                                                            ?>
                                                                                        </td>
                                                                                        <td class="hidecol">
                                                                                            <?php
                                                                                            $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']);
                                                                                            $tup1 += $val;
                                                                                            echo $val;
                                                                                            ?>
                                                                                        </td>
                                                                                    </tr> 
        <?php } ?>
                                                                            </tbody>
                                                                        </table> 
                                                                    </div>
                                                                    <br>
                                                                    <div class="row-fluid">
                                                                        <table class="table table-hover table-nomargin">                                                                
                                                                            <thead>
                                                                                <tr>
                                                                                    <th class="hidecol"> </th>
                                                                                    <th>TUT</th>
                                                                                    <th>TUP</th>
                                                                                    <th>TGP</th>
                                                                                    <th>GPA</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <th class="hidecol">PREV</th>
                                                                                    <td><?php echo $put; ?></td>
                                                                                    <td><?php echo $pup; ?></td>
                                                                                    <td><?php echo $pcgp; ?></td>
                                                                                    <td><?php echo ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th class="hidecol">CUR</th>
                                                                                    <td><?php echo $tut1; ?></td>
                                                                                    <td><?php echo $tup1; ?> </td>
                                                                                    <td><?php echo $tgp1; ?></td>
                                                                                    <td><?php echo ($tut1 > 0) ? calculateGpa($tgp1, $tut1) : "0.00"; ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th class="hidecol">CUM</th>
                                                                                    <td><?php echo $put += $tut1; ?></td>
                                                                                    <td><?php echo $pup += $tup1; ?></td>
                                                                                    <td><?php echo $pcgp += $tgp1; ?></td>
                                                                                    <td><?php echo ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>                                                             
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="box box-bordered box-color span6">                                                            
                                                                <div class="box-title">
                                                                    <h3>
                                                                        <i class="icon-table"></i>
                                                                        Second Semester
                                                                    </h3>
                                                                </div>
                                                                <div class="box-content">
                                                                    <div class="row-fluid">
                                                                        <table class="table table-hover table-striped">
                                                                            <thead>
                                                                            <th>Code</th>
                                                                            <th>Score</th>
                                                                            <th>Unit</th>
                                                                            <th class="hidecol">ST</th>
                                                                            <th>GP</th>
                                                                            <th class="hidecol">NUP</th>                                                                    
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php
                                                                                for ($i = 0; $i < count($results[$row_attn['sesname']]['second']); $i++) {
                                                                                    $result = $results[$row_attn['sesname']]['second'][$i];
                                                                                    ?>
                                                                                    <tr>
                                                                                        <td><?php echo strtoupper($result['csid']); ?></td>
                                                                                        <td><?php echo $result['score']; ?></td>
                                                                                        <td>
                                                                                            <?php
                                                                                            $tut2 += $result['unit'];
                                                                                            echo $result['unit'];
                                                                                            ?>
                                                                                        </td>
                                                                                        <td class="hidecol"><?php echo getStatusAlpha($result['status']) ?></td>
                                                                                        <td>
                                                                                            <?php
                                                                                            $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']);
                                                                                            $tgp2 += $val;
                                                                                            echo $val;
                                                                                            ?>
                                                                                        </td>
                                                                                        <td class="hidecol">
                                                                                            <?php
                                                                                            $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']);
                                                                                            $tup2 += $val;
                                                                                            echo $val;
                                                                                            ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                <?php } ?>
                                                                            </tbody>
                                                                        </table> 
                                                                    </div>
                                                                    <br>
                                                                    <div class="row-fluid">
                                                                        <table class="table table-hover table-nomargin">                                                                
                                                                            <thead>
                                                                                <tr>
                                                                                    <th class="hidecol"> </th>
                                                                                    <th>TUT</th>
                                                                                    <th>TUP</th>
                                                                                    <th>TGP</th>
                                                                                    <th>GPA</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <th class="hidecol">PREV</th>
                                                                                    <td><?php echo $put; ?></td>
                                                                                    <td><?php echo $pup; ?></td>
                                                                                    <td><?php echo $pcgp; ?></td>
                                                                                    <td><?php echo ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th class="hidecol">CUR</th>
                                                                                    <td><?php echo $tut2; ?></td>
                                                                                    <td><?php echo $tup2; ?> </td>
                                                                                    <td><?php echo $tgp2; ?></td>
                                                                                    <td><?php echo ($tut2 > 0) ? calculateGpa($tgp2, $tut2) : "0.00"; ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th class="hidecol">CUM</th>
                                                                                    <td><?php echo $put += $tut2; ?></td>
                                                                                    <td><?php echo $pup += $tup2; ?></td>
                                                                                    <td><?php echo $pcgp += $tgp2; ?></td>
                                                                                    <td><?php echo ($put > 0) ? calculateGpa(($pcgp), ($put)) : "0.00"; ?></td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>                                                             
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>                                                                                                                            
                                                    </div>
                                                </div>   
                                    <?php }?> 
                                    </div>
                                   
                                  
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <div class="box">
                                                <div class="box-title" style="text-align: center;">
                                                    <h5>Preferences and Outstanding</h5>
                                                    <div class="alert alert-danger">
                                                            <?php
                                                            echo implode(', ', $outstanding);
                                                            ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    
                                    <?php } else { ?>
                                    <div class="alert alert-danger">You can not view this transcript</div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH."/footer.php" ?>
    </body>
</html>