<?php  ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once('../path.php');

if(isset($_GET['q']) && isset($_GET['l'])) {
    $sesid = getSessionValue('sesid');
    
    $colname_stud = "-1";
    if (isset($_SESSION['stid'])) {
      $colname_stud = $_SESSION['stid'];
    }

    if (isset($_GET['stid'])) {
      $colname_stud = $_GET['stid'];
    }

    $levels = [];
    $level = $_GET['l'];
    
    for($idx = 1; $idx <= $_GET['l']; $idx++) {
        array_push($levels, $idx);
    }
        
    
    $query = "%".urldecode($_GET['q'])."%";
    /**
        Used to generate suggestions
    */
    $query_suggestion = sprintf("SELECT csid, status, csname, unit "
                            . "FROM course c "
                            . "WHERE (deptid <> %s OR catid NOT IN(3,4,5,8)) "
                            . "AND (csid LIKE %s OR csname LIKE %s) "            
                            . "AND level IN(%s)"
                            . "AND csid NOT IN ( SELECT csid "
                            . "FROM result "
                            . "WHERE stdid = %s "
                            . "AND sesid = %s "
                            . "UNION "
                            . "SELECT csid "
                            . "FROM department_course "
                            . "WHERE level = %s "
                            . "AND progid = %s) "
                            . "LIMIT 10",
                            GetSQLValueString(getSessionValue('did'), "int"),
                            GetSQLValueString($query, "text"),
                            GetSQLValueString($query, "text"),                            
                            GetSQLValueString(" ", "defined", implode(',', $levels)),
                            GetSQLValueString($colname_stud, "text"),
                            GetSQLValueString($sesid, "int"),
                            GetSQLValueString($level, "int"),
                            GetSQLValueString($sesid, "int"));
    $suggestion = mysql_query($query_suggestion, $tams) or die(mysql_error());
    $row_suggestion = mysql_fetch_assoc($suggestion);
    $totalRows_suggestion = mysql_num_rows($suggestion);

    $suggested = array();
    for($idx = 0 ; $totalRows_suggestion > $idx; $idx++, $row_suggestion = mysql_fetch_assoc($suggestion)) {
        $row_suggestion['registered'] = false;
        $row_suggestion['selected'] = false;
        $row_suggestion['removed'] = false;
        $suggested[] = $row_suggestion;
    }
    echo json_encode($suggested);
    
}else {
    echo json_encode(array());
}



