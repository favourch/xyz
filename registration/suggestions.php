<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

if (isset($_GET['q']) && isset($_GET['l'])) {
    $sesid = getSessionValue('sesid');
    $progid = '';
    
    $colname_stud = "-1";
    if (isset($_SESSION['stid'])) {
        $colname_stud = $_SESSION['stid'];
        $query = sprintf("SELECT progid, curid FROM student WHERE stdid = %s", GetSQLValueString($colname_stud, "text"));
        $registration = mysql_query($query, $tams);
        $row_query = mysql_fetch_assoc($registration);
        $progid = $row_query['progid'];
        $curid = $row_query['curid'];
    }

    if (isset($_GET['stid'])) {
        $colname_stud = $_GET['stid'];
        
        $query = sprintf("SELECT progid, curid FROM student WHERE stdid = %s", GetSQLValueString($colname_stud, "text"));
        $registration = mysql_query($query, $tams);
        $row_query = mysql_fetch_assoc($registration);
        $progid = $row_query['progid'];
        $curid = $row_query['curid'];
        
    }

    $levels = [];
    $level = $_GET['l'];

    for ($idx = 1; $idx <= $_GET['l']; $idx++) {
        array_push($levels, $idx);
    }


    $query = "%" . urldecode($_GET['q']) . "%";
    /**
      Used to generate suggestions
     */
    $query_suggestion = sprintf("SELECT csid, status, csname, unit "
            . "FROM course c "
            . "WHERE (deptid <> %s OR catid NOT IN(3,4,5,8)) "
            . "AND (csid LIKE %s OR csname LIKE %s) "
            . "AND level IN(%s) AND curid = %s "
            . "AND csid NOT IN ( SELECT csid "
            . "FROM course_reg "
            . "WHERE stdid = %s "
            . "AND sesid = %s) LIMIT 20", 
            GetSQLValueString(getSessionValue('did'), "int"), 
            GetSQLValueString($query, "text"), 
            GetSQLValueString($query, "text"), 
            GetSQLValueString(" ", "defined", implode(',', $levels)),
            GetSQLValueString($curid, "int"), 
            GetSQLValueString($colname_stud, "text"), 
            GetSQLValueString($sesid, "int"), 
            GetSQLValueString($level, "int"), 
            GetSQLValueString($progid, "int"));
    $suggestion = mysql_query($query_suggestion, $tams) or die(mysql_error());
    $row_suggestion = mysql_fetch_assoc($suggestion);
    $totalRows_suggestion = mysql_num_rows($suggestion);

    $suggested = array();
    for ($idx = 0; $totalRows_suggestion > $idx; $idx++, $row_suggestion = mysql_fetch_assoc($suggestion)) {
        $row_suggestion['registered'] = false;
        $row_suggestion['selected'] = false;
        $row_suggestion['removed'] = false;
        $suggested[] = $row_suggestion;
    }
    echo json_encode($suggested);
} else {
    echo json_encode(array());
}



