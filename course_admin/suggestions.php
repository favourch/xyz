<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

if (isset($_GET['q'])) {
    $sesid = getSessionValue('sesid');
        
    $query = "%" . urldecode($_GET['q']) . "%";
    
    /**
      Used to generate suggestions
     */
    $query_suggestion = sprintf("SELECT csid, status, csname, unit "
                            . "FROM course c "
                            . "WHERE csid LIKE %s OR csname LIKE %s "
                            . "LIMIT 20", 
                            GetSQLValueString($query, "text"), 
                            GetSQLValueString($query, "text"));
    $suggestion = mysql_query($query_suggestion, $tams) or die(mysql_error());
    $row_suggestion = mysql_fetch_assoc($suggestion);
    $totalRows_suggestion = mysql_num_rows($suggestion);

    $suggested = array();
    for ($idx = 0; $totalRows_suggestion > $idx; $idx++, $row_suggestion = mysql_fetch_assoc($suggestion)) {
        $row_suggestion['selected'] = false;
        $suggested[] = $row_suggestion;
    }
    
    echo json_encode($suggested);
    
} else {
    echo json_encode([]);
}