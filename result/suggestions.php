<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

if (isset($_GET['q']) && isset($_GET['s']) && isset($_GET['c'])) {

    $query = urldecode($_GET['q']);
    $sesid = urldecode($_GET['s']);    
    $csid = urldecode($_GET['c']);
    
    /**
      Used to generate suggestions
     */
    $query_suggestion = sprintf("SELECT s.stdid, s.lname, s.fname "
                                . "FROM student s "
                                . "LEFT JOIN result r ON s.stdid = r.stdid AND r.sesid = %s AND r.csid = %s "
                                . "WHERE s.stdid = %s "
                                . "AND r.stdid IS NULL", 
                                GetSQLValueString($sesid, "int"), 
                                GetSQLValueString($csid, "text"), 
                                GetSQLValueString($query, "text"));
    $suggestion = mysql_query($query_suggestion, $tams);
    $row_suggestion = mysql_fetch_assoc($suggestion);
    $totalRows_suggestion = mysql_num_rows($suggestion);
    if ($totalRows_suggestion > 0) {
        echo json_encode($row_suggestion);
        return;
    }
} 

echo json_encode([]);
