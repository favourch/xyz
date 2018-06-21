<?php
//initialize the session
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "1,20,21,22,23,24,28";
check_auth($auth_users, $site_root.'/admin');

$filter = "";

$sesid = getSessionValue('sesid');
if(isset($_POST['sesid'])){
    $sesid =  $_POST['sesid'];
}


if(isset($_POST['typeid']) && $_POST['typeid'] != 'all'){
    $filter .= sprintf("AND ad.admid = %s ", GetSQLValueString($_POST['typeid'], 'int'));
}


/*
$prosList_SQL = sprintf("SELECT distinct p.formnum, p.jambregid, p.lname, p.fname, p.mname, p.sex, 
                        st.stname, stlga.lganame, adt.typename, c.colname, prg.progname, 
                        jamb_total, 
                        round((jamb_total*0.15),0), round((p.score),0), 
                        (round((jamb_total*0.15),0)+round((p.score),0)) as aggregate, p.phone, p.email
                        FROM prospective p 
                        JOIN programme prg ON p.progid1 = prg.progid 
                        JOIN department d ON prg.deptid = d.deptid
                        JOIN college c ON c.colid = d.colid 
                        JOIN admissions ad ON ad.admid = p.admid 
                        JOIN admission_type adt ON ad.typeid = adt.typeid 
                        JOIN state st ON p.stid = st.stid
                        LEFT JOIN state_lga stlga ON stlga.lgaid = p.lga
                       
                        WHERE p.sesid = %s AND p.formpayment = 'yes' AND p.appbatch IN (8,9) AND score IS NOT NULL %s 
                        ORDER BY adt.typename, c.colid, prg.progname, aggregate DESC ", 
                    GetSQLValueString($sesid, 'int'),
                    $filter); 
$prosRS = mysql_query($prosList_SQL) or die(mysql_error());
*/

$prosList_SQL = sprintf("SELECT distinct p.formnum, p.jambregid, p.lname, p.fname, p.mname, p.sex, 
                        st.stname, stlga.lganame, adt.typename, c.colname, prg.progname, 
                        (jambscore1+jambscore2+jambscore3+jambscore4), 
                        round(((jambscore1+jambscore2+jambscore3+jambscore4)*0.15),0), round((p.score*0.8),0), 
                        (round(((jambscore1+jambscore2+jambscore3+jambscore4)*0.15),0)+round((p.score*0.8),0)) as aggregate, p.phone, p.email
                        FROM prospective p 
                        JOIN programme prg ON p.progid1 = prg.progid 
                        JOIN department d ON prg.deptid = d.deptid
                        JOIN college c ON c.colid = d.colid 
                        JOIN admissions ad ON ad.admid = p.admid 
                        JOIN admission_type adt ON ad.typeid = adt.typeid
                        JOIN state st ON p.stid = st.stid
                        LEFT JOIN state_lga stlga ON stlga.lgaid = p.lga
                        
                        WHERE p.sesid = %s AND p.formsubmit = 'yes' AND p.adminstatus='Yes' AND p.formpayment = 'yes' AND p.score is not null %s 
                        ORDER BY adt.typename, c.colid, prg.progname, aggregate DESC ", 
                    GetSQLValueString($sesid, 'int'),
                    $filter); 
$prosRS = mysql_query($prosList_SQL) or die(mysql_error());

// JOIN screening sc ON sc.jambregid = p.jambregid

// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=admission-list.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
fputcsv($output, array('Form No', 'JAMB Reg', 'Last Name', 'First Name', 'Middle Name', 'Sex', 'State', 'LG', 'Appl-Type', 
    'College', 'Programme', 'JAMB-Score', ' JAMB60', 'P-UTME40', 'Aggregate', 'Phone', 'Email'));
                        
// loop over the rows, outputting them
while ($prosRows = mysql_fetch_assoc($prosRS)) {
    fputcsv($output, $prosRows);
}
exit;