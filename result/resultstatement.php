<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');


$auth_users = "20,2,3,4,5,6,10, 1";
check_auth($auth_users, $site_root);



$query_session = "SELECT sesid, sesname FROM session WHERE listing = 'TRUE' ORDER BY sesid DESC";
$session = mysql_query($query_session, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);
$totalRows_session = mysql_num_rows($session);

$query_passlist = sprintf("SELECT * FROM passlist WHERE stdid = %s ",GetSQLValueString(getSessionValue('uid'), 'int'));
$passlist = mysql_query($query_passlist, $tams) or die(mysql_error());
$row_passlist = mysql_fetch_assoc($passlist);
$totalRows_passlist = mysql_num_rows($passlist);

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
    $lvl = $level = $_GET['lvl'];
}

if (isset($_GET['pid'])) {
    $prg = $_GET['pid'];
}

if (isset($_GET['sid'])) {
    $sid = $_GET['sid'];
}else{
    $sid = $_SESSION['sesid'];
}

$colname_stud = "-1";
if (getAccess() < 7 && isset($_GET['stid'])) {
    $colname_stud = $_GET['stid'];
}


if (getAccess() < 7 && !isset($_GET['stid'])) {
    $colname_stud = '';

    $query_std = sprintf("SELECT s.stdid, s.progid, colid, p.deptid, fname, lname, level, s.curid "
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



$query_stud = sprintf("SELECT s.stdid, s.progid, c.colid, c.dean_email AS col_email, d.email AS dept_email, p.deptid, fname, lname, level, s.curid FROM student s, programme p, department d, college c   WHERE s.progid = p.progid AND d.deptid = p.deptid AND d.colid = c.colid  AND s.stdid = %s AND s.passlist = 'No'  ", GetSQLValueString($colname_stud, "text"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

if (getAccess() < 10) {
    $prg = (isset($row_stud['progid'])) ? $row_stud['progid'] : $prg ;
    $level = (isset($row_stud['level'])) ? $row_stud['level'] : $level;
}

$query_studs = sprintf("SELECT s.stdid, s.fname, s.lname FROM student s JOIN registration r ON s.stdid = r.stdid  WHERE r.level = %s AND s.progid = %s AND r.sesid = %s AND s.passlist = 'No' "
        , GetSQLValueString($level, "int")
        , GetSQLValueString($prg, "int"),GetSQLValueString($sid, "int"));
$studs = mysql_query($query_studs, $tams) or die(mysql_error());
$row_studs = mysql_fetch_assoc($studs);
$totalRows_studs = mysql_num_rows($studs);

$colname1_year1f = "-1";
if (isset($row_stud['progid'])) {
    $colname1_year1f = $row_stud['progid'];
}


if(isset($_POST['action']) && $_POST['action'] == 'update_result'){
    mysql_query("BEGIN");
    $queryRes = sprintf("SELECT * FROM result r JOIN session s ON r.sesid = s.sesid where resultid = %s ", 
            GetSQLValueString($_POST['edit_id'], 'int'));
    $rs_Res = mysql_query($queryRes, $tams) or die(mysql_error());
    $row_Res = mysql_fetch_assoc($rs_Res);
    
    
    $sql = sprintf("UPDATE result SET tscore = %s, escore = %s WHERE resultid = %s ", 
                    GetSQLValueString($_POST['tscore'], 'int'),
                    GetSQLValueString($_POST['escore'], 'int'),
                    GetSQLValueString($_POST['edit_id'], 'int'));
    $rs_SQL = mysql_query($sql, $tams) or die(mysql_error());
    
    $cont = array('Result' => array(
            'tscore' => array('old' => $row_Res['tscore'], 'new' => $_POST['tscore']),
            'escore' => array('old' => $row_Res['escore'], 'new' => $_POST['escore']),
            'created_by' => array('old' => '', 'new' => getSessionValue('uid')),
            'date_created' => array('old' => '', 'new' => date('Y-m-d, h:i:s'))
        )
    );

    $param['entid'] = $row_Res['stdid'];
    $param['enttype'] = 'student';
    $param['action'] = 'edit';
    $param['cont'] = json_encode($cont);
    audit_log($param);
    mysql_query("COMMIT");
    
    $mail_to = $row_stud['col_email'];
    $cc = array($row_stud['dept_email'], 'results@tasued.edu.ng');
    $subject = "Student Result Alteration Activity Log";
    $sender = $school_short_name;
    $message = "Dear Dean,<br/>   
            There has been an alteration to the academic result of a student in your College. Find the details  below; <br/>
            Student ID : {$row_Res['stdid']}<br/>
            Session : {$row_Res['sesname']}<br/>
            Course Code : {$row_Res['csid']}<br/>
            Previous Test Score: {$row_Res['tscore']}<br/>
            Previous Exam Score: {$row_Res['escore']}<br/>
            New Test Score: {$_POST['tscore']}<br/>
            New Exam Score: {$_POST['escore']}<br/>
            Initator : ".getSessionValue('uid') ." <br/>
            Date Initiated : ".date('Y-m-d H:i:s')."
            ";
    $body = $message;
    
    
    $bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'> Student Result Altered </h3><p>%s</p>",  $message);


    $mailstatus = sendHtmlEmail($mail_to, $subject, $bd, 'no-reply@tasued.edu.ng', $university, $cc);
    
    $redir = sprintf("location:resultstatement.php?stid=%s", $colname_stud);
    header($redir);
    exit();
}

if(isset($_POST['action']) && $_POST['action'] == 'add_result'){
    
    $query_session1 = sprintf("SELECT sesid, sesname FROM session WHERE sesid = %s", GetSQLValueString($_POST['sesid'], 'text'));
    $session1 = mysql_query($query_session1, $tams) or die(mysql_error());
    $row_session1 = mysql_fetch_assoc($session1);
    

    mysql_query("BEGIN");
    $queryRes = sprintf("DELETE FROM result WHERE csid = %s AND sesid = %s AND stdid = %s ", 
                        GetSQLValueString($_POST['csid'], 'text'),
                        GetSQLValueString($_POST['sesid'], 'text'),
                        GetSQLValueString($_POST['stdid'], 'text'));
    $rs_Res = mysql_query($queryRes, $tams) or die(mysql_error());
    
    
    $queryReg = sprintf("DELETE FROM course_reg WHERE csid = %s AND sesid = %s AND stdid = %s ", 
                        GetSQLValueString($_POST['csid'], 'text'),
                        GetSQLValueString($_POST['sesid'], 'text'),
                        GetSQLValueString($_POST['stdid'], 'text'));
    $rs_Reg = mysql_query($queryReg, $tams) or die(mysql_error());
    
    
    
    $sql = sprintf("INSERT into result (csid, sesid, stdid, tscore, escore, upload_date) VALUES (%s, %s, %s, %s, %s, %s) ",
                    GetSQLValueString($_POST['csid'], 'text'),
                    GetSQLValueString($_POST['sesid'], 'text'),
                    GetSQLValueString($_POST['stdid'], 'text'),
                    GetSQLValueString($_POST['tscore'], 'int'), 
                    GetSQLValueString($_POST['escore'], 'int'),
                    GetSQLValueString(date('Y-m-d, h:i:s'), 'date'));
    $rs_SQL = mysql_query($sql, $tams) or die(mysql_error());
    
    $sqlreg = sprintf("INSERT into course_reg (csid, sesid, stdid) VALUES (%s, %s, %s) ",
                    GetSQLValueString($_POST['csid'], 'text'),
                    GetSQLValueString($_POST['sesid'], 'text'),
                    GetSQLValueString($_POST['stdid'], 'text'));
                    //GetSQLValueString($_POST['tscore'], 'int'),
                    //GetSQLValueString($_POST['escore'], 'int'));
    $rg_SQL = mysql_query($sqlreg, $tams) or die(mysql_error());
    
    $cont = array('Result' => array(
            'stdid' => array('old' => '', 'new' => $_POST['stdid']),
            'sesid' => array('old' => '', 'new' => $_POST['sesid']),
            'csid' => array('old' => '', 'new' => $_POST['csid']),
            'tscore' => array('old' => '', 'new' => $_POST['tscore']),
            'escore' => array('old' => '', 'new' => $_POST['escore']),
            'created_by' => array('old' => '', 'new' => getSessionValue('uid')),
            'date_created' => array('old' => '', 'new' => date('Y-m-d, h:i:s'))
        )
    );

    $param['entid'] = $_POST['stdid'];
    $param['enttype'] = 'student';
    $param['action'] = 'edit';
    $param['cont'] = json_encode($cont);
    audit_log($param);
    mysql_query("COMMIT");
    
    
    $mail_to = $row_stud['col_email'];
    $cc = array($row_stud['dept_email'], 'results@tasued.edu.ng');
    $subject = "Student Result Alteration Activity Log";
    $sender = $school_short_name;
    $message = "Dear Dean,<br/>   
            A new score has been added to the academic result of a student in your College. Find the details below; <br/>
            Student ID : {$_POST['stdid']}<br/>
            Session : {$row_session1['sesname']}<br/>
            Course Code : {$_POST['csid']}<br/>
            New Test Score: {$_POST['tscore']}<br/>
            New Exam Score: {$_POST['escore']}<br/>
            Initator : ".getSessionValue('uid')."<br/>
            Date Initiated : ".date('Y-m-d H:i:s')."
            ";
    $body = $message;
    
    
    $bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'> Student Result Altered </h3><p>%s</p>",  $message);


    $mailstatus = sendHtmlEmail($mail_to, $subject, $bd, 'no-reply@tasued.edu.ng', $university, $cc);
    
    $redir = sprintf("location:resultstatement.php?stid=%s", $colname_stud);
    header($redir);
    exit();
}


if(isset($_POST['action']) && $_POST['action'] == 'delete_result'){
    mysql_query("BEGIN");
    
    $sql1 = sprintf("SELECT * FROM result WHERE resultid = %s  ",
                    GetSQLValueString($_POST['edit_id'], 'int'));
    $rs_SQL1 = mysql_query($sql1, $tams) or die(mysql_error());
    $row_rs1 = mysql_fetch_assoc($rs_SQL1); 
    
    $query_session1 = sprintf("SELECT sesid, sesname FROM session WHERE sesid = %s", GetSQLValueString($row_rs1['sesid'], 'int'));
    $session1 = mysql_query($query_session1, $tams) or die(mysql_error());
    $row_session1 = mysql_fetch_assoc($session1);
    
    $sql = sprintf("DELETE FROM result WHERE resultid = %s  ",
                    GetSQLValueString($_POST['edit_id'], 'int'));
    $rs_SQL = mysql_query($sql, $tams) or die(mysql_error());
    
    $sql2 = sprintf("DELETE FROM course_reg WHERE csid = %s  AND sesid = %s AND stdid = %s ",
                    GetSQLValueString($_POST['csid'], 'text'),
                    GetSQLValueString($_POST['sesid'], 'int'),
                    GetSQLValueString($row_rs1['stdid'], 'text'));
    $rs_SQL2 = mysql_query($sql2, $tams) or die(mysql_error());
    
    mysql_query("COMMIT");
    
    $mail_to = $row_stud['col_email'];
    $cc = array($row_stud['dept_email'], 'results@tasued.edu.ng');
    $subject = "Student Result Alteration Activity Log";
    $sender = $school_short_name;
    $message = "Dear Dean,<br/>   
            A score has been deleted from the academic result of a student in your College find details below; <br/>
            Student ID : {$row_rs1['stdid']}<br/>
            Session : {$row_session1['sesname']}<br/>
            Course Code : {$_POST['csid']}<br/>
            Initator : ".getSessionValue('uid')." <br/>
            Date Initiated : ".date('Y-m-d H:i:s')."
            ";
    $body = $message;
    
    $bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'> Student Result Altered </h3><p>%s</p>",  $message);

    $mailstatus = sendHtmlEmail($mail_to, $subject, $bd, 'no-reply@tasued.edu.ng', $university, $cc);
    
    $redir = sprintf("location:resultstatement.php?stid=%s", $colname_stud);
    header($redir);
    exit();
}

  $query_year1f = sprintf("SELECT distinct (r.csid), r.sesid, c.csname, c.semester, r.tscore+ r.escore as score, s.sesname, dc.status, dc.unit "
        . "FROM `result` r, department_course dc, course c, session s, teaching t "
        . "WHERE stdid = %s "
        . "AND c.csid = r.csid "
        . "AND c.csid = dc.csid AND dc.progid = %s AND dc.curid = c.curid "
        . "AND r.csid = t.csid "
        . "AND s.sesid = t.sesid "
        . "AND t.accepted = 'yes' "
        . "AND s.sesid = r.sesid "
        . "AND c.curid = %s AND r.csid NOT LIKE %s "
        
        . "UNION "
        
        . "SELECT distinct (r.csid), r.sesid, c.csname, c.semester, r.tscore+ r.escore as score, s.sesname, c.status, c.unit "
        . "FROM `result` r, course c, session s, teaching t "
        . "WHERE stdid = %s "
        . "AND c.csid = r.csid "
        . "AND r.csid = t.csid "
        . "AND s.sesid = t.sesid "
        . "AND t.accepted = 'yes' "
        . "AND s.sesid = r.sesid "
        . "AND c.curid = %s AND r.csid NOT LIKE %s "
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
        . "AND c.curid = %s AND r.csid NOT LIKE %s) " ,
        
        GetSQLValueString($colname_stud, "text"), 
        GetSQLValueString($row_stud['progid'], "int"),
        GetSQLValueString($row_stud['curid'], "int"),
        GetSQLValueString("VOS%", "text"),
        
        GetSQLValueString($colname_stud, "text"), 
        GetSQLValueString($row_stud['curid'], "int"),
        GetSQLValueString("VOS%", "text"),
        
        GetSQLValueString($colname_stud, "text"), 
        GetSQLValueString($row_stud['progid'], "int"),
        GetSQLValueString($row_stud['curid'], "int"),
        GetSQLValueString("VOS%", "text")); 

$year1f = mysql_query($query_year1f, $tams) or die(mysql_error());
$row_year1f = mysql_fetch_assoc($year1f);
$totalRows_year1f = mysql_num_rows($year1f);

$colname_attn = $colname_stud;

$query_attn = sprintf("SELECT s.sesid, s.sesname, r.level "
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
        . "WHERE curid = %s AND progid = %s %s ",
//        . "AND csid "
//        . "NOT IN(SELECT DISTINCT csid FROM result WHERE stdid=%s)",	
        GetSQLValueString($row_stud['curid'], "int"),
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
        $gp = 0;
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

    $default = $sesGrad[$sesid]['passmark'];

    // Enforce normal grade in 100 level
    //if (substr($csid, 3, 1) == '1') {
    //    $default = $sesGrad[$sesid]['gradeF'] + 1;
    //}

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
    return round($points / $units, 2);
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

$query_leve = "SELECT * FROM `level_name` WHERE active = 'TRUE' ";
$levelRs = mysql_query($query_leve, $tams) or die(mysql_error());
$row_level = mysql_fetch_assoc($levelRs);

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

$allow = true;
$acl = array(4, 5, 6);
if (getAccess() == 20 || (getAccess() == 2 && getSessionValue('cid') == $cid) || (getAccess() == 3 && getSessionValue('did') == $did) || (in_array(getAccess(), $acl) && getSessionValue('did') == $did) || getSessionValue('stid') == $colname_stud) {
    $allow = true;
}

// obtaining the current session for CBT e-Test Result in an active semester
$query_curses = "SELECT sesid, sesname, SUBSTRING(semester, 1,1) as semester FROM session WHERE status = 'TRUE' ";
$curses = mysql_query($query_curses, $tams) or die(mysql_error());
$row_curses = mysql_fetch_assoc($curses);
$totalRows_curses = mysql_num_rows($curses);

// collating CBT scores in General Courses - GNS, ENT, EDU for active semester
// joined the teaching to clear etest score once exam is uploaded.

    $query_cbt = sprintf("SELECT distinct cr.csid, cr.sesid, cr.tscore, c.semester FROM course_reg cr, course c, teaching t "
    . "WHERE cr.csid = c.csid AND cr.csid = t.csid AND c.semester = %s "
    . "AND cr.stdid = %s AND cr.sesid = t.sesid AND cr.sesid = %s AND t.upload !='yes' "
    . "AND (cr.csid like 'edu___' or cr.csid like 'ent___' or cr.csid like 'gns___') "
    . "AND cr.tscore IS NOT NULL", 
        GetSQLValueString($row_curses['semester'], "text"),
        GetSQLValueString($colname_stud, "text"),
        GetSQLValueString($row_curses['sesid'], "int")); 
    $cbt = mysql_query($query_cbt, $tams) or die(mysql_error());
    $row_cbt = mysql_fetch_assoc($cbt);
    $totalRows_cbt = mysql_num_rows($cbt); 
?>
<!doctype html>
<html ng-app="res" >
    <?php include INCPATH."/header.php" ?>

    <style>        
        /*transcript table hiding*/
        @media screen and (max-width: 420px) {
            .hidecol { display: none; }
        }
    </style>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="pageCtrl">
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
                                
                                
                                
                                <?php if($totalRows_passlist == 0){?>
                                <div class="box-content">
                                    <?php if ($allow) { ?>
                                    <?php if (in_array(getAccess(), [20,2,3,4,5,6, 1])) { ?>
                                        <div class=" form form-vertical">
                                            <form method="post" action="print_result.php" target="_tab">
                                                <div class="row-fluid">
                                                <div class="span3">
                                                    <div class="control-group">
                                                        <label class="control-label" for="textfield">Search by Session</label>
                                                        <div class="controls controls-row ">
                                                            <select  onChange="sesfilt(this)" name="sesid" style="width:200px" required="">
                                                                <?php
                                                                do {
                                                                    
                                                                    ?>
                                                                    <option <?php if ($sid == $row_session['sesid']) echo "selected"; ?> value="<?php echo $row_session['sesid'] ?>"><?php echo $row_session['sesname'] ?></option>
                                                                    <?php
                                                                } while ($row_session = mysql_fetch_assoc($session));
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="span3">
                                                    <div class="control-group">
                                                        <label class="control-label" for="textfield">Search by Programme</label>
                                                        <div class="controls controls-row ">
                                                            <select  onChange="progfilt(this)" name="progid" style="width:200px" required="">
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
                                                <div class="span3">
                                                    <div class="control-group">
                                                        <label class="control-label" for="textfield">Search by level</label>
                                                        <div class="controls controls-row">
                                                            <select onChange="lvlfilt(this)" name="level" required="">
                                                                <?php do{  ?>
                                                                    <option value="<?= $row_level['levelid'] ?>" <?php if ($lvl == $row_level['levelid']) echo 'selected'; ?>>
                                                                        <?= $row_level['levelname']?>
                                                                    </option>
                                                                    <?php }while($row_level = mysql_fetch_assoc($levelRs)) ;?>
                                                                
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="span3">
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
                                                
                                            <button type="submit" name="MM_list">Print all Transcript</button>
                                                
                                            </div>
                                            </form>
                                            
                                            
                                        </div>
                                    <?php } ?>
                                    
                                    
                                    <div class="row-fluid">
                                        
                                        <!--First Table-->
                                        <?php if ($sCount > 0) { ?>
                                        
                                        <div class="box box box-color">
                                            <div class="box-title">
                                                <h3><i class="icon-reorder"></i>
                                                    Statement of Result  <?= ($totalRows_stud > 0) ?  'of '.$row_stud['stdid'] .' ( '. $row_stud['lname']. ' '.$row_stud['fname']. ' '. $row_stud['mname']. '  )' : 'for '. $row_attn['sesname'] .' ('.$row_attn['level'].'00 Level )'?>
                                                </h3>
                                                <ul class="tabs">
                                                    <li class="active">
                                                        <form method="post" action="transprint.php" target="_blank">
                                                            <input type="hidden" name="stdid" value="<?php echo $colname_stud ?>">
                                                            <input type="submit" class="btn btn-darkblue " value="Print Result Profile">
                                                        </form>
                                                    </li>
                                        <!--
                                                    <li class="active">
                                                        <a href="#show_test"  class="blue" data-toggle="modal" >View Genreal Course Test Score</a>
                                                    </li> -->
                                                    <?php if (in_array(getAccess(), ['3000']) ){ ?>
                                                    <li class="active">
                                                        <a href="#add_score" ng-click="setSelectedItem('<?= $row_attn['sesid'] ?>', '<?= $colname_stud ?>')" class="brown" data-toggle="modal" ><i class="icon icon-edit"></i></a>
                                                    </li> 
                                                     <?php } ?>       
                                                </lu>
                                              </div>
                                              
                            <!-- display CBT e_test scores -->  
                            <!-- only if tscore uploaded for active semester -->
                                <?php if ($totalRows_cbt >0) {
                                echo $row_curses['sesname']; ?> 
                                &nbsp; e-Test Scores for General Courses
                                <div class="alert alert-info">
                                    <?php  do { 
                                         echo $row_cbt['csid'].'-' ;
                                         echo '{'.$row_cbt['tscore'].'}'; ?>
                                       ;&nbsp;
                                  <?php  } while($row_cbt = mysql_fetch_assoc($cbt))?>
                                  <?php } ?>
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
                                                                            <?php if(in_array(getAccess(), ['3000'])){?>
                                                                            <td>
                                                                                <a href="#edit_score" class="brown" data-toggle="modal" ng-click="selectResult('<?= $result['csid'] ?>','<?= $row_attn['sesid'] ?>', '<?= $colname_stud ?>')"><i class="icon icon-edit"></i></a>
                                                                                &nbsp;&nbsp;
                                                                                <a href="#delete_score" class="green"  data-toggle="modal" ng-click="selectResult('<?= $result['csid'] ?>','<?= $row_attn['sesid'] ?>', '<?= $colname_stud ?>')"><i class="icon icon-trash"></i></a>  
                                                                            </td>
                                                                            <?php } ?>
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
                                                                            <?php if(in_array(getAccess(), ['3000'])){?>
                                                                            <td>
                                                                                <a href="#edit_score" class="brown" data-toggle="modal" ng-click="selectResult('<?= $result['csid'] ?>','<?= $row_attn['sesid'] ?>', '<?= $colname_stud ?>')"><i class="icon icon-edit"></i></a>
                                                                                 &nbsp;&nbsp;
                                                                                <a href="#delete_score" class="green"  data-toggle="modal" ng-click="selectResult('<?= $result['csid'] ?>','<?= $row_attn['sesid'] ?>', '<?= $colname_stud ?>')"><i class="icon icon-trash"></i></a>  
                                                                            </td>
                                                                            <?php }?>
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
                                                    Statement of Result for <?php echo $row_attn['sesname']; ?> (<?php echo $row_attn['level']?>00 Level)
                                                </h3>
                                                <?php if (in_array(getAccess(), ['3000']) ){ ?>
                                                <ul class="tabs">
                                                    <li class="active">
                                                        <a href="#add_score" ng-click="setSelectedItem('<?= $row_attn['sesid'] ?>', '<?= $colname_stud ?>')" class="brown" data-toggle="modal" ><i class="icon icon-edit"></i></a>
                                                    </li>
                                                </lu>
                                                <?php } ?>
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
                                                                            <?php if(in_array(getAccess(), ['3000'])){?>
                                                                            <td>
                                                                                <a href="#edit_score" class="brown" data-toggle="modal" ng-click="selectResult('<?= $result['csid'] ?>','<?= $row_attn['sesid'] ?>', '<?= $colname_stud ?>')"><i class="icon icon-edit"></i></a>
                                                                                &nbsp;&nbsp;
                                                                                <a href="#delete_score" class="green"  data-toggle="modal" ng-click="selectResult('<?= $result['csid'] ?>','<?= $row_attn['sesid'] ?>', '<?= $colname_stud ?>')"><i class="icon icon-trash"></i></a>  
                                                                            </td>
                                                                            <?php }?>
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
                                                                            <?php if(in_array(getAccess(), ['3000']) ){?>
                                                                            <td>
                                                                                <a href="#edit_score" class="brown" data-toggle="modal" ng-click="selectResult('<?= $result['csid'] ?>','<?= $row_attn['sesid'] ?>', '<?= $colname_stud ?>')"><i class="icon icon-edit"></i></a>
                                                                                 &nbsp;&nbsp;
                                                                                <a href="#delete_score" class="green"  data-toggle="modal" ng-click="selectResult('<?= $result['csid'] ?>','<?= $row_attn['sesid'] ?>', '<?= $colname_stud ?>')"><i class="icon icon-trash"></i></a>  
                                                                            </td>
                                                                            <?php }?>
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
                                                    Statement of Result for <?php echo $row_attn['sesname']; ?> (<?php echo $row_attn['level']?>00 Level)
                                                </h3>
                                                <?php if (in_array(getAccess(), ['3000']) ){ ?>
                                                <ul class="tabs">
                                                    <li class="active">
                                                        <a href="#add_score" ng-click="setSelectedItem('<?= $row_attn['sesid'] ?>', '<?= $colname_stud ?>')" class="brown" data-toggle="modal" ><i class="icon icon-edit"></i></a>
                                                    </li>
                                                </lu>
                                                <?php } ?>
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
                                                                            <?php if(in_array(getAccess(), ['3000']) ){?>
                                                                            <td>
                                                                                <a href="#edit_score" class="brown" data-toggle="modal" ng-click="selectResult('<?= $result['csid']?>','<?= $row_attn['sesid']?>', '<?= $colname_stud?>')"><i class="icon icon-edit"></i></a>
                                                                                 &nbsp;&nbsp;
                                                                                <a href="#delete_score" class="green"  data-toggle="modal" ng-click="selectResult('<?= $result['csid']?>','<?= $row_attn['sesid']?>', '<?= $colname_stud?>')"><i class="icon icon-trash"></i></a>  
                                                                            </td>
                                                                            <?php }?>
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
                                                                                <?php if(in_array(getAccess(), ['3000'])){?>
                                                                                <td>
                                                                                    <a href="#edit_score" class="brown" data-toggle="modal" ng-click="selectResult('<?= $result['csid']?>','<?= $row_attn['sesid']?>', '<?= $colname_stud?>')"><i class="icon icon-edit"></i></a>
                                                                                     &nbsp;&nbsp;
                                                                                    <a href="#delete_score" class="green"  data-toggle="modal" ng-click="selectResult('<?= $result['csid']?>','<?= $row_attn['sesid']?>', '<?= $colname_stud?>')"><i class="icon icon-trash"></i></a>  
                                                                                </td>
                                                                                <?php }?>
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
                                                            Statement of Result for <?php echo $row_attn['sesname']; ?> (<?php echo $row_attn['level']?>00 Level)
                                                        </h3>
                                                        <?php if (in_array(getAccess(), ['3000'])){ ?>
                                                            <ul class="tabs">
                                                                <li class="active">
                                                                    <a href="#add_score" ng-click="setSelectedItem('<?= $row_attn['sesid'] ?>', '<?= $colname_stud ?>')" class="brown" data-toggle="modal" ><i class="icon icon-edit"></i></a>
                                                                </li>
                                                            </lu>
                                                        <?php } ?>
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
                                                                                        <?php if(in_array(getAccess(), ['3000']) ){?>
                                                                                        <td>
                                                                                            <a href="#edit_score" class="brown" data-toggle="modal" ng-click="selectResult('<?= $result['csid']?>','<?= $row_attn['sesid']?>', '<?= $colname_stud?>')"><i class="icon icon-edit"></i></a>
                                                                                             &nbsp;&nbsp;
                                                                                            <a href="#delete_score" class="green"  data-toggle="modal" ng-click="selectResult('<?= $result['csid']?>','<?= $row_attn['sesid']?>', '<?= $colname_stud?>')"><i class="icon icon-trash"></i></a>  
                                                                                        </td>
                                                                                        <?php }?>
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
                                                                                        <?php if(in_array(getAccess(), ['3000']) ){?>
                                                                                        <td>
                                                                                            <a href="#edit_score" class="brown" data-toggle="modal" ng-click="selectResult('<?= $result['csid']?>','<?= $row_attn['sesid']?>', '<?= $colname_stud?>')"><i class="icon icon-edit"></i></a>
                                                                                             &nbsp;&nbsp;
                                                                                            <a href="#delete_score" class="green"  data-toggle="modal" ng-click="selectResult('<?= $result['csid']?>','<?= $row_attn['sesid']?>', '<?= $colname_stud?>')"><i class="icon icon-trash"></i></a>  
                                                                                        </td>
                                                                                        <?php }?>
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
                                                            Statement of Result for <?php echo $row_attn['sesname']; ?> (<?php echo $row_attn['level']?>00 Level)
                                                        </h3>
                                                        <?php if (in_array(getAccess(), ['3000']) ){ ?>
                                                            <ul class="tabs">
                                                                <li class="active">
                                                                    <a href="#add_score" ng-click="setSelectedItem('<?= $row_attn['sesid'] ?>', '<?= $colname_stud ?>')" class="brown" data-toggle="modal" ><i class="icon icon-edit"></i></a>
                                                                </li>
                                                            </lu>
                                                        <?php } ?>
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
                                                                                        <?php if(in_array(getAccess(), ['3000']) ){?>
                                                                                        <td>
                                                                                            <a href="#edit_score" class="brown" data-toggle="modal" ng-click="selectResult('<?= $result['csid']?>','<?= $row_attn['sesid']?>', '<?= $colname_stud?>')"><i class="icon icon-edit"></i></a>
                                                                                             &nbsp;&nbsp;
                                                                                            <a href="#delete_score" class="green"  data-toggle="modal" ng-click="selectResult('<?= $result['csid']?>','<?= $row_attn['sesid']?>', '<?= $colname_stud?>')"><i class="icon icon-trash"></i></a>  
                                                                                        </td>
                                                                                        <?php }?>
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
                                                                                        <?php if(in_array(getAccess(), ['3000']) ){?>
                                                                                        <td>
                                                                                            <a href="#edit_score" class="brown" data-toggle="modal" ng-click="selectResult('<?= $result['csid']?>','<?= $row_attn['sesid']?>', '<?= $colname_stud?>')"><i class="icon icon-edit"></i></a>
                                                                                             &nbsp;&nbsp;
                                                                                            <a href="#delete_score" class="green"  data-toggle="modal" ng-click="selectResult('<?= $result['csid']?>','<?= $row_attn['sesid']?>', '<?= $colname_stud?>')"><i class="icon icon-trash"></i></a>  
                                                                                        </td>
                                                                                        <?php }?>
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
                                                    <h5>References and Outstanding</h5>
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
                                <?php } else { ?>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <div class="alert alert-warning">
                                                <p>Congratulations you have made the passlist. Your pass grade is shown below.</p>
                                                <b>GPA.</b> <?= $row_passlist['gpa']?><br/>
                                                <b>TUP.</b> <?= $row_passlist['tup']?><br/>
                                                <b>CLASS.</b> <?= $row_passlist['cdegree']?><br/>
                                                <p>Note! To request for your Transcript, please contact The Exams & Record Department.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH."/footer.php" ?>
        <div aria-hidden="false" 
             aria-labelledby="myModalLabel" 
             role="dialog" tabindex="-1" 
             class="modal hide fade" 
             id="edit_score">

            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h3 id="myModalLabel">Update {{current.course}} Score for {{current.student}}</h3>
            </div>
            <form class="form-horizontal form-bordered" method="post" action="resultstatement.php?stid=<?= $colname_stud?>" >
                <div class="modal-body">
                    <div class="center" style="align-content: center" ng-if="current.loading" >
                        <img src="giphy.gif" width="80px" height="80px"> Fetching Result please wait ...
                    </div>
                    <div ng-if="!current.loading && result.status == 'success'">
                        <div class="control-group">
                            <label class="control-label" for="de">Course Code</label>
                            <div class="controls" class="input-block-level">
                                <input type="text" name="csid" class="input input-medium" disabled="" value="{{result.rs.csid}}">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="de">Test Score</label>
                            <div class="controls" class="input-block-level">
                                <input type="text" name="tscore" class="input input-medium" required="" value="{{result.rs.tscore}}">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="de">Exam Score</label>
                            <div class="controls" class="input-block-level">
                                <input type="text" name="escore" class="input input-medium" required="" value="{{result.rs.escore}}">
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="action" value="update_result">
                <input type="hidden" name="edit_id" value="{{result.rs.resultid}}">
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit" >Submit</button>
                    <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
                </div>
            </form>
        </div>
        
        <!--Delete--> 
        <div aria-hidden="false" 
             aria-labelledby="myModalLabel" 
             role="dialog" tabindex="-1" 
             class="modal hide fade" 
             id="delete_score">

            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h3 id="myModalLabel">Delete {{current.course}} Score for {{current.student}}</h3>
            </div>
            <form class="form-horizontal form-bordered" method="post" action="resultstatement.php?stid=<?= $colname_stud?>" >
                <div class="modal-body">
                    <div class="center" style="align-content: center" ng-if="current.loading" >
                        <img src="giphy.gif" width="80px" height="80px"> Fetching Result please wait ...
                    </div>
                    <div ng-if="!current.loading && result.status == 'success'">
                        <div class="alert alert-danger">
                            You have chosen to delete this record..Are you sure you want to proceed?
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="de">Course Code</label>
                            <div class="controls" class="input-block-level">
                                <input type="text" name="csid" class="input input-medium" readonly="" value="{{result.rs.csid}}">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="de">Test Score</label>
                            <div class="controls" class="input-block-level">
                                <input type="text" name="tscore" class="input input-medium" readonly="" value="{{result.rs.tscore}}">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="de">Exam Score</label>
                            <div class="controls" class="input-block-level">
                                <input type="text" name="escore" class="input input-medium" readonly="" value="{{result.rs.escore}}">
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="action" value="delete_result">
                <input type="hidden" name="sesid" value="{{result.rs.sesid}}">
                <input type="hidden" name="edit_id" value="{{result.rs.resultid}}">
                <div class="modal-footer">
                    <button class="btn btn-danger" type="submit" >Delete</button>
                    <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
                </div>
            </form>
        </div>
        
        
    
        <div aria-hidden="false" 
             aria-labelledby="myModalLabel" 
             role="dialog" tabindex="-1" 
             class="modal hide fade" 
             id="show_test">

            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h3 id="myModalLabel">General Course Test Score </h3>
            </div>
            <form class="form-horizontal form-bordered" method="post" action="resultstatement.php?stid=<?= $colname_stud?>" >
                <div class="modal-body">
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <td>#</td>
                                <td>Code Code</td>
                                <td>Test Score</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($totalRows_cbt > 0 ){ ?>
                            <?php $i = 1; do{ ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= $row_cbt['csid']?></td>
                                <td><?= $row_cbt['tscore']?></td>
                            </tr>
                            <?php }while($row_cbt = mysql_fetch_assoc($cbt))?>
                            <?php } else{ ?>
                            <tr>
                                <td colspan="3"><div class="alert alert-info">No score found for this session</div></td>
                            </tr>
                            <?php }?>
                            
                           
                        </tbody>
                    </table>
                </div>
                
                <div class="modal-footer">
                    
                    <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
                </div>
            </form>
        </div>
        
        <div aria-hidden="false" 
             aria-labelledby="myModalLabel" 
             role="dialog" tabindex="-1" 
             class="modal hide fade" 
             id="add_score">

            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h3 id="myModalLabel">Add Score for <?= $colname_stud?></h3>
            </div>
            <form class="form-horizontal form-bordered" method="post" action="resultstatement.php?stid=<?= $colname_stud?>" >
                <div class="modal-body">
                    <div class="center" style="align-content: center" ng-if="current.loading" >
                        <img src="giphy.gif" width="80px" height="80px"> Fetching Result please wait ...
                    </div>
                     <div class="input-append input-prepend">
                        <span class="add-on"><i class="icon-search"></i> Course Code</span>
                        <input placeholder="Search here..." class="input-medium" type="text" ng-model="scid" >
                        <button class="btn" type="button" ng-click="fetchCourse(scid)">Search!</button>
                    </div>
                    <div ng-if="!current.loading && result.status == 'success'">
                        <div class="alert alert-success">
                            You have choosen to add {{result.rs.csid}} Result for <?= $colname_stud?>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="de">Course Code</label>
                            <div class="controls" class="input-block-level">
                                <input type="text" name="csid" class="input input-medium" readonly="" value="{{result.rs.csid}}">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="de">Test Score</label>
                            <div class="controls" class="input-block-level">
                                <input type="text" name="tscore" class="input input-medium" required value="{{result.rs.tscore}}">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="de">Exam Score</label>
                            <div class="controls" class="input-block-level">
                                <input type="text" name="escore" class="input input-medium" required value="{{result.rs.escore}}">
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="action" value="add_result">
                <input type="hidden" name="sesid" value="{{selectedItem.sesid}}">
                <input type="hidden" name="stdid" value="{{selectedItem.stdid}}">
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit" >Add Result</button>
                    <button aria-hidden="true" data-dismiss="modal" class="btn">Cancel</button>
                </div>
            </form>
        </div>
        
        <script>
            var res = angular.module('res', []);
            res.controller('pageCtrl', function ($scope, $http) {
                $scope.selectedItem = {};
                $scope.current = {};
                $scope.current.loading = false;
                
                
                $scope.setSelectedItem = function(s,v){
                    
                    $scope.selectedItem = {
                        sesid : s,
                        stdid : v
                    };
                    
                };
                
                $scope.fetchCourse = function(csid){
                    $scope.current.loading = true; 
                    $scope.current = {
                        course : csid,
                    };
                    
                    $http({
                            method : "POST",
                            url : "api.php?action=course",
                            data: $scope.current, 
                        }).then(function mySucces(response) {
                            $scope.result = response.data;
                            $scope.current.loading = false; 
                        }, function myError(response) {
                            $scope.result = response.statusText;
                            $scope.current.loading = false; 
                        });
                }
                
                $scope.selectResult = function (csid, sesid, std){
                     $scope.current.loading = true; 
                    $scope.current = {
                        course : csid,
                        session : sesid,
                        student : std
                    };
                    
                     
                    $http({
                            method : "POST",
                            url : "api.php?action=result",
                            data: $scope.current, 
                        }).then(function mySucces(response) {
                            if(response.data.status == 'success'){
                                $scope.result = response.data;
                                $scope.current.loading = false; 
                            }else{
                                alert("Unable to fecth course");
                            }
                            
                        }, function myError(response) {
                            $scope.result = response.statusText;
                            $scope.current.loading = false; 
                        });
                    
                }
            });
        </script>
    </body>
</html>