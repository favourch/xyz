<?php
//initialize the session
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "20,28";
check_auth($auth_users, $site_root);

$sesid = $_GET['sid'];
$csid = $_GET['cid'];

$regList_SQL = sprintf("SELECT distinct (c.stdid), s.lname, s.fname, s.mname, g.coltitle, d.deptname "
                    . "FROM student s, course_reg c, programme p, department d, college g, session ss "
                    . "WHERE s.stdid = c.stdid "
                    . "AND s.disciplinary = 'FALSE' "
                    . "AND s.progid = p.progid AND s.std_status = 'active' "
                    . "AND p.deptid = d.deptid "
                    . "AND d.colid = g.colid "
                    . "AND c.sesid = ss.sesid "
                    . "AND ss.sesid = %s "
                    . "AND c.csid = %s  ORDER BY g.colid, d.deptid",
                    GetSQLValueString($sesid, "int"),
                    GetSQLValueString($csid, "text"));
                    
$regRS = mysql_query($regList_SQL) or die(mysql_error());

// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=reglist.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
fputcsv($output, array('Matric', 'Last Name', 'First Name', 'Middle Name', 'College', 'Department'));

// loop over the rows, outputting them
while ($regRows = mysql_fetch_assoc($regRS)) {
    fputcsv($output, $regRows);
}
exit;