<?php
//initialize the session
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');

$auth_users = "1,20,23";
check_auth($auth_users, $site_root);

$regList_SQL = sprintf("SELECT sc.matric_no, s.lname, s.fname, s.mname, p.progname, ss.sesname "
                    . "FROM student s, clearance_transactions sc , programme p, session ss "
                    . "WHERE s.stdid = sc.matric_no "
                    . "AND s.progid = p.progid "
                    . "AND sc.sesid = ss.sesid "
                    . "AND sc.status = 'APPROVED'");
$regRS = mysql_query($regList_SQL) or die(mysql_error());



// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=clearance_list.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
fputcsv($output, array('Matric', 'First Name', 'Last Name', 'Middle Name', 'Programme', 'Session'));

// loop over the rows, outputting them
while ($regRows = mysql_fetch_assoc($regRS)) {
    fputcsv($output, $regRows);
}
exit;