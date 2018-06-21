<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "1,20,21,23";
check_auth($auth_users, $site_root.'/admin');

$query_rssess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,6";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

$sesid = $curSes = getSessionValue('sesid');

$query_level = sprintf("SELECT * "
                        . "FROM level_name WHERE active='TRUE' "
                        . "ORDER BY levelid ASC");
$level = mysql_query($query_level) or die(mysql_error());
$totalRows_level = mysql_num_rows($level);

$query_stud = "";
$name = 'Students';
$lvl = '';
$filter = '';
$filter1 = '';
$filter2 = '';

if(isset($_GET['sid'])) {
    $sesid = $_GET['sid'];
}

if(isset($_GET['lvl'])) {
    $lvl = $_GET['lvl'];
    
    if($lvl != 'all') {
        $filter = 'AND r.level = '.  GetSQLValueString($lvl, 'int');
        $filter1 = 'AND st.level = '.  GetSQLValueString($lvl, 'int');
        $filter2 = 'AND r.level = '.  GetSQLValueString($lvl, 'int');
    }
}

if(isset($_GET['did'])) {
    $deptid = $_GET['did'];
    
    $action = '';
    if(isset($_GET['action'])) {
        $action = $_GET['action'];
    }
    
    switch($action) {
            
        case 'reg':
            $query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, at.typename, s.phone, st.stname 
                    FROM student s 
                    JOIN programme p ON p.progid = s.progid 
                    JOIN department d ON d.deptid = p.deptid 
                    JOIN registration r ON r.stdid = s.stdid   
                    JOIN state st on s.stid = st.stid
                    JOIN admissions a on s.admid = a.admid
                    JOIN admission_type at on a.typeid = at.typeid
                    WHERE r.status = 'Registered' 
                    AND r.sesid = %s  
                    AND d.deptid = %s %s ORDER BY s.stdid ASC",
                    GetSQLValueString($sesid, "int"),
                    GetSQLValueString($deptid, "int"),
                    GetSQLValueString($filter, "defined", $filter));
            
            $name = 'Registered students';
            break;
            
        case 'pmal':
            $query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, at.typename, s.phone, st.stname 
                    FROM student s 
                    JOIN programme p ON p.progid = s.progid 
                    JOIN department d ON d.deptid = p.deptid 
                    JOIN registration r ON r.stdid = s.stdid   
                    JOIN state st on s.stid = st.stid
                    JOIN admissions a on s.admid = a.admid
                    JOIN admission_type at on a.typeid = at.typeid
                    WHERE s.sex = 'M'
                    AND r.sesid = %s  
                    AND d.deptid = %s %s ORDER BY s.stdid ASC",
                    GetSQLValueString($sesid, "int"),
                    GetSQLValueString($deptid, "int"),
                    GetSQLValueString($filter, "defined", $filter));
            
            $name = 'Male students';
            break;
            
         case 'pfem':
            $query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, at.typename, s.phone, st.stname, st.stname
                    FROM student s 
                    JOIN programme p ON p.progid = s.progid 
                    JOIN department d ON d.deptid = p.deptid 
                    JOIN registration r ON r.stdid = s.stdid  
                    JOIN state st on s.stid = st.stid
                    JOIN admissions a on s.admid = a.admid
                    JOIN admission_type at on a.typeid = at.typeid
                    WHERE s.sex = 'F'
                    AND r.sesid = %s  
                    AND d.deptid = %s %s ORDER BY s.stdid ASC",
                    GetSQLValueString($sesid, "int"),
                    GetSQLValueString($deptid, "int"),
                    GetSQLValueString($filter, "defined", $filter));
            
            $name = 'Female students';
            break;

        case 'clear':
            $query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, at.typename, s.phone, st.stname
                    FROM student s 
                    JOIN programme p ON p.progid = s.progid 
                    JOIN department d ON d.deptid = p.deptid 
                    JOIN registration r ON r.stdid = s.stdid 
                    JOIN state st on s.stid = st.stid
                    JOIN admissions a on s.admid = a.admid
                    JOIN admission_type at on a.typeid = at.typeid
                    WHERE r.approved = 'TRUE' 
                    AND r.sesid = %s 
                    AND d.deptid = %s %s ORDER BY s.stdid ASC",
                    GetSQLValueString($sesid, "int"),
                    GetSQLValueString($deptid, "int"),
                    GetSQLValueString($filter, "defined", $filter));
            
            $name = 'Cleared students';
            break;

        case 'paid':
            $query_stud = sprintf("SELECT DISTINCT(s.stdid), s.fname, s.lname, at.typename, s.phone, sts.stname
                    FROM student s 
                    JOIN programme p ON p.progid = s.progid 
                    JOIN department d ON d.deptid = p.deptid 
                    JOIN schfee_transactions st ON st.matric_no = s.stdid   
                    JOIN state sts on s.stid = sts.stid
                    JOIN admissions a on s.admid = a.admid
                    JOIN admission_type at on a.typeid = at.typeid
                    WHERE st.sesid = %s 
                    AND st.status = 'APPROVED'
                    AND d.deptid = %s %s ORDER BY s.stdid ASC",
                    GetSQLValueString($sesid, "int"),
                    GetSQLValueString($deptid, "int"),
                    GetSQLValueString($filter1, "defined", $filter1));
            
            $name = 'Paid students';
            break;

        default:
            
            if($sesid == $curSes) {
                if($lvl == 1) {
                    $query_stud = sprintf("SELECT DISTINCT(ps.jambregid), s.stdid as stdid, ps.fname, ps.lname, adt.typename, ps.phone, st.stname  
                                    FROM accfee_transactions at
                                    JOIN prospective ps ON at.can_no = ps.jambregid
                                    LEFT JOIN student s ON ps.jambregid = s.jambregid 
                                    JOIN programme p ON p.progid = ps.progofferd
                                    JOIN department d ON d.deptid = p.deptid
                                    JOIN state st on ps.stid = st.stid
                                    JOIN admissions a on ps.admid = a.admid
                                    JOIN admission_type adt on a.typeid = adt.typeid
                                    WHERE ps.admtype = 'UTME' 
                                    AND at.status = 'APPROVED'  
                                    AND d.deptid = %s  
                                    ORDER BY s.stdid ASC",
                                    GetSQLValueString($deptid, "int"));
                }else if($lvl == 2) {
                    $query_stud = sprintf("(SELECT DISTINCT(s.stdid), s.fname, s.lname, at.typename, s.phone, st.stname 
                                    FROM student s 
                                    JOIN programme p ON p.progid = s.progid 
                                    JOIN department d ON d.deptid = p.deptid                   
                                    JOIN registration r ON r.stdid = s.stdid  
                                    JOIN state st on s.stid = st.stid
                                    JOIN admissions a on s.admid = a.admid
                                    JOIN admission_type at on a.typeid = at.typeid
                                    WHERE d.deptid = %s 
                                    AND r.sesid = %s 
                                    AND r.level = 2 
                                    ORDER BY s.stdid ASC) 
                                    UNION 
                                    (SELECT ps.jambregid, ps.fname, ps.lname, adt.typename, ps.phone, st.stname 
                                    FROM accfee_transactions at
                                    JOIN prospective ps ON at.can_no = ps.jambregid
                                    JOIN programme p ON p.progid = ps.progofferd
                                    JOIN department d ON d.deptid = p.deptid
                                    JOIN state st on ps.stid = st.stid
                                    JOIN admissions a on ps.admid = a.admid
                                    JOIN admission_type adt on a.typeid = adt.typeid
                                    WHERE ps.admtype = 'DE' AND at.status = 'APPROVED'  
                                    AND d.deptid = %s 
                                    ORDER BY ps.jambregid ASC)",
                                    GetSQLValueString($deptid, "int"), 
                                    GetSQLValueString($sesid, "int"),
                                    GetSQLValueString($deptid, "int"));
                }else if($lvl != 'all') {
                    $query_stud = sprintf("SELECT DISTINCT(s.stdid), s.fname, s.lname, at.typename, s.phone, st.stname
                                FROM student s 
                                JOIN programme p ON p.progid = s.progid 
                                JOIN department d ON d.deptid = p.deptid                
                                JOIN registration r ON r.stdid = s.stdid 
                                JOIN state st on s.stid = st.stid
                                JOIN admissions a on s.admid = a.admid
                                JOIN admission_type at on a.typeid = at.typeid
                                WHERE d.deptid = %s 
                                AND r.sesid = %s 
                                %s ORDER BY s.stdid ASC",
                                GetSQLValueString($deptid, "int"),
                                GetSQLValueString($sesid, "int"),
                                GetSQLValueString($filter2, "defined", $filter2));
                }else {
                    $query_stud = sprintf("(SELECT DISTINCT(s.stdid), s.fname, s.lname, at.typename, s.phone, st.stname 
                                    FROM student s 
                                    JOIN programme p ON p.progid = s.progid 
                                    JOIN department d ON d.deptid = p.deptid                     
                                    JOIN registration r ON r.stdid = s.stdid  
                                    JOIN state st on s.stid = st.stid
                                    JOIN admissions a on s.admid = a.admid
                                    JOIN admission_type at on a.typeid = at.typeid
                                    WHERE d.deptid = %s 
                                    AND r.sesid = %s 
                                    ORDER BY s.stdid ASC) 
                                    UNION 
                                    (SELECT ps.jambregid, ps.fname, ps.lname, adt.typename, ps.phone, st.stname
                                    FROM accfee_transactions at
                                    RIGHT JOIN prospective ps ON at.can_no = ps.jambregid
                                    JOIN programme p ON p.progid = ps.progofferd
                                    JOIN department d ON d.deptid = p.deptid
                                    JOIN state st on ps.stid = st.stid
                                    JOIN admissions a on ps.admid = a.admid
                                    JOIN admission_type adt on a.typeid = adt.typeid
                                    WHERE at.status = 'APPROVED'  
                                    AND d.deptid = %s 
                                    AND ps.jambregid 
                                    NOT IN (
                                            SELECT can_no 
                                            FROM schfee_transactions st
                                            JOIN payschedule ps ON st.scheduleid = ps.scheduleid
                                            WHERE ps.sesid = %s
                                            ) 
                                    ORDER BY s.stdid ASC)",
                                    GetSQLValueString($deptid, "int"),
                                    GetSQLValueString($sesid, "int"),
                                    GetSQLValueString($deptid, "int"),
                                    GetSQLValueString($sesid, "int"));
                }
            }else {
                $query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, at.typename, s.phone, st.stname
                                FROM student s 
                                JOIN programme p ON p.progid = s.progid 
                                JOIN department d ON d.deptid = p.deptid                     
                                JOIN registration r ON r.stdid = s.stdid  
                                JOIN state st on s.stid = st.stid
                                JOIN admissions a on s.admid = a.admid
                                JOIN admission_type at on a.typeid = at.typeid
                                WHERE d.deptid = %s 
                                AND r.sesid = %s 
                                %s ORDER BY s.stdid ASC",
                                GetSQLValueString($deptid, "int"),
                                GetSQLValueString($sesid, "int"),
                                GetSQLValueString($filter2, "defined", $filter2));
            } 
    }
        
    
    $query_info = sprintf("SELECT deptname as name
                        FROM department
                        WHERE deptid = %s",
                        GetSQLValueString($deptid, "int"));
}

if(isset($_GET['cid'])) {
    $colid = $_GET['cid'];
    
    $action = '';
    if(isset($_GET['action'])) {
        $action = $_GET['action'];
    }
    switch($action) {
        
        case 'reg':
            $query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, at.typename,s.phone, st.stname
                    FROM student s 
                    JOIN programme p ON p.progid = s.progid 
                    JOIN department d ON d.deptid = p.deptid 
                    JOIN college c ON c.colid = d.colid 
                    JOIN registration r ON r.stdid = s.stdid
                    JOIN state st on s.stid = st.stid
                    JOIN admissions a on s.admid = a.admid
                    JOIN admission_type at on a.typeid = at.typeid
                    WHERE r.status = 'Registered' 
                    AND r.sesid = %s 
                    AND c.colid = %s %s ORDER BY s.stdid ASC",
                    GetSQLValueString($sesid, "int"),
                    GetSQLValueString($colid, "int"),
                    GetSQLValueString($filter, "defined", $filter)); 
            
            $name = 'Registered students';
            break;
            
       case 'colm':
                 $query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, p.progname, at.typename, s.phone, st.stname
                    FROM student s 
                    JOIN programme p ON p.progid = s.progid 
                    JOIN department d ON d.deptid = p.deptid 
                    JOIN college c ON c.colid = d.colid 
                    JOIN registration r ON r.stdid = s.stdid  
                    JOIN state st on s.stid = st.stid
                    JOIN admissions a on s.admid = a.admid
                    JOIN admission_type at on a.typeid = at.typeid
                    WHERE s.sex = 'M' 
                    AND r.sesid = %s 
                    AND c.colid = %s %s ORDER BY s.stdid ASC",
                    GetSQLValueString($sesid, "int"),
                    GetSQLValueString($colid, "int"),
                    GetSQLValueString($filter, "defined", $filter));
            
            $name = 'Male students';
            break;

       case 'colf':
                 $query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, p.progname, at.typename, s.phone, st.stname 
                    FROM student s 
                    JOIN programme p ON p.progid = s.progid 
                    JOIN department d ON d.deptid = p.deptid 
                    JOIN college c ON c.colid = d.colid 
                    JOIN registration r ON r.stdid = s.stdid  
                    JOIN state st on s.stid = st.stid
                    JOIN admissions a on s.admid = a.admid
                    JOIN admission_type at on a.typeid = at.typeid
                    WHERE s.sex = 'F' 
                    AND r.sesid = %s 
                    AND c.colid = %s %s ORDER BY s.stdid ASC",
                    GetSQLValueString($sesid, "int"),
                    GetSQLValueString($colid, "int"),
                    GetSQLValueString($filter, "defined", $filter));
            
            $name = 'Male students';
            break;
            
        case 'clear':
            $query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, at.typename, s.phone, st.stname 
                    FROM student s 
                    JOIN programme p ON p.progid = s.progid 
                    JOIN department d ON d.deptid = p.deptid 
                    JOIN college c ON c.colid = d.colid 
                    JOIN registration r ON r.stdid = s.stdid 
                    JOIN state st on s.stid = st.stid
                    JOIN admissions a on s.admid = a.admid
                    JOIN admission_type at on a.typeid = at.typeid
                    WHERE r.approved = 'TRUE' 
                    AND r.sesid = %s 
                    AND c.colid = %s %s ORDER BY s.stdid ASC",
                    GetSQLValueString($sesid, "int"),
                    GetSQLValueString($colid, "int"),
                    GetSQLValueString($filter, "defined", $filter));
            
            $name = 'Cleared students';
            break;
 
        case 'paid':
            $query_stud = sprintf("SELECT DISTINCT(s.stdid), s.fname, s.lname, at.typename, s.phone, sts.stname
                    FROM student s 
                    JOIN schfee_transactions st ON st.matric_no = s.stdid 
                    JOIN programme p ON p.progid = s.progid 
                    JOIN department d ON d.deptid = p.deptid 
                    JOIN college c ON c.colid = d.colid  
                    JOIN state sts on s.stid = sts.stid
                    JOIN admissions a on s.admid = a.admid
                    JOIN admission_type at on a.typeid = at.typeid
                    WHERE st.sesid = %s 
                    AND st.status = 'APPROVED' 
                    AND c.colid = %s %s ORDER BY s.stdid ASC",
                    GetSQLValueString($sesid, "int"),
                    GetSQLValueString($colid, "int"),
                    GetSQLValueString($filter1, "defined", $filter1));
            
            $name = 'Paid students';
            break;

        default:
           
            if($sesid == $curSes) {
                if($lvl == 1) {
                    $query_stud = sprintf("SELECT DISTINCT(ps.jambregid), s.stdid, ps.fname, ps.lname, adt.typename, ps.phone, st.stname  
                                    FROM accfee_transactions at
                                    JOIN prospective ps ON at.can_no = ps.jambregid
                                    LEFT JOIN student s ON ps.jambregid = s.jambregid 
                                    JOIN programme p ON p.progid = ps.progofferd
                                    JOIN department d ON d.deptid = p.deptid
                                    JOIN college c ON c.colid = d.colid 
                                    JOIN state st on ps.stid = st.stid
                                    JOIN admissions a on ps.admid = a.admid
                                    JOIN admission_type adt on a.typeid = adt.typeid
                                    WHERE ps.admtype = 'UTME' 
                                    AND at.status = 'APPROVED'  
                                    AND c.colid = %s  
                                    ORDER BY s.stdid ASC",
                                    GetSQLValueString($colid, "int"));
                }else if($lvl == 2) {
                    $query_stud = sprintf("(SELECT DISTINCT(s.stdid), s.fname, s.lname, at.typename, s.phone, st.stname 
                                    FROM student s 
                                    JOIN programme p ON p.progid = s.progid 
                                    JOIN department d ON d.deptid = p.deptid 
                                    JOIN college c ON c.colid = d.colid                     
                                    JOIN registration r ON r.stdid = s.stdid  
                                    JOIN state st on s.stid = st.stid
                                    JOIN admissions a on s.admid = a.admid
                                    JOIN admission_type at on a.typeid = at.typeid
                                    WHERE c.colid = %s 
                                    AND r.sesid = %s 
                                    AND r.level = 2 
                                    ORDER BY s.stdid ASC) 
                                    UNION 
                                    (SELECT ps.jambregid, ps.fname, ps.lname, adt.typename, ps.phone, st.stname 
                                    FROM accfee_transactions at
                                    JOIN prospective ps ON at.can_no = ps.jambregid
                                    JOIN programme p ON p.progid = ps.progofferd
                                    JOIN department d ON d.deptid = p.deptid
                                    JOIN college c ON c.colid = d.colid 
                                    JOIN state st on ps.stid = st.stid
                                    JOIN admissions a on ps.admid = a.admid
                                    JOIN admission_type adt on a.typeid = adt.typeid
                                    WHERE ps.admtype = 'DE' AND at.status = 'APPROVED'  
                                    AND c.colid = %s 
                                    ORDER BY ps.jambregid ASC)",
                                    GetSQLValueString($colid, "int"), 
                                    GetSQLValueString($sesid, "int"),
                                    GetSQLValueString($colid, "int"), 
                                    GetSQLValueString($sesid, "int"));
                }else if($lvl != 'all') {
                    $query_stud = sprintf("SELECT DISTINCT(s.stdid), s.fname, s.lname, at.typename, s.phone, st.stname 
                                FROM student s 
                                JOIN programme p ON p.progid = s.progid 
                                JOIN department d ON d.deptid = p.deptid 
                                JOIN college c ON c.colid = d.colid                     
                                JOIN registration r ON r.stdid = s.stdid  
                                JOIN state st on s.stid = st.stid
                                JOIN admissions a on s.admid = a.admid
                                JOIN admission_type at on a.typeid = at.typeid
                                WHERE c.colid = %s 
                                AND r.sesid = %s 
                                %s ORDER BY s.stdid ASC",
                                GetSQLValueString($colid, "int"),
                                GetSQLValueString($sesid, "int"),
                                GetSQLValueString($filter2, "defined", $filter2));
                }else {
                    $query_stud = sprintf("(SELECT DISTINCT(s.stdid), s.fname, s.lname, at.typename, s.phone, st.stname
                                    FROM student s 
                                    JOIN programme p ON p.progid = s.progid 
                                    JOIN department d ON d.deptid = p.deptid 
                                    JOIN college c ON c.colid = d.colid                     
                                    JOIN registration r ON r.stdid = s.stdid  
                                    JOIN state st on s.stid = st.stid
                                    JOIN admissions a on s.admid = a.admid
                                    JOIN admission_type at on a.typeid = at.typeid
                                    WHERE c.colid = %s 
                                    AND r.sesid = %s 
                                    ORDER BY s.stdid ASC) 
                                    UNION 
                                    (SELECT ps.jambregid, ps.fname, ps.lname, adt.typename, ps.phone, st.stname 
                                    FROM accfee_transactions at
                                    RIGHT JOIN prospective ps ON at.can_no = ps.jambregid
                                    JOIN programme p ON p.progid = ps.progofferd
                                    JOIN department d ON d.deptid = p.deptid
                                    JOIN college c ON c.colid = d.colid 
                                    JOIN state st on ps.stid = st.stid
                                    JOIN admissions a on ps.admid = a.admid
                                    JOIN admission_type adt on a.typeid = adt.typeid
                                    WHERE at.status = 'APPROVED'  
                                    AND c.colid = %s 
                                    AND ps.jambregid 
                                    NOT IN (
                                            SELECT can_no 
                                            FROM schfee_transactions st
                                            JOIN payschedule ps ON st.scheduleid = ps.scheduleid
                                            WHERE ps.sesid = %s
                                            ) 
                                    ORDER BY s.stdid ASC)",
                                    GetSQLValueString($colid, "int"),
                                    GetSQLValueString($sesid, "int"),
                                    GetSQLValueString($colid, "int"),
                                    GetSQLValueString($sesid, "int"));
                }
            }else {
                $query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, at.typename, s.phone, st.stname 
                                FROM student s 
                                JOIN programme p ON p.progid = s.progid 
                                JOIN department d ON d.deptid = p.deptid 
                                JOIN college c ON c.colid = d.colid                     
                                JOIN registration r ON r.stdid = s.stdid  
                                JOIN state st on s.stid = st.stid
                                JOIN admissions a on s.admid = a.admid
                                JOIN admission_type at on a.typeid = at.typeid
                                WHERE c.colid = %s 
                                AND r.sesid = %s 
                                %s ORDER BY s.stdid ASC",
                                GetSQLValueString($colid, "int"),
                                GetSQLValueString($sesid, "int"),
                                GetSQLValueString($filter2, "defined", $filter2));
            }            
    }        
    
    $query_info = sprintf("SELECT colname as name 
                        FROM college
                        WHERE colid = %s",
                        GetSQLValueString($colid, "int"));
}

$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);


$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$name .= ' in '.$row_info['name'];
?>
<!doctype html>
<html>
    <?php include INCPATH."/header.php" ?>
    <body  data-layout-sidebar="fixed" data-layout-topbar="fixed">
        <?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH."/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH."/page_header.php" ?>
                    
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        <?php echo $name ?>
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <div class="span4">
                                            <select name='ses' onchange="sesfilt(this)">
                                                <?php for (; $row_rssess != false; $row_rssess = mysql_fetch_assoc($rssess)) { ?>
                                                    <option value="<?php echo $row_rssess['sesid'] ?>" <?php if ($sesid == $row_rssess['sesid']) echo 'selected' ?>>
                                                        <?php echo $row_rssess['sesname']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        
                                        <div class="span4">
                                            Level
                                            <select onChange="lvlfilt(this)">
                                                <option value="all" <?php if ($lvl == "all") echo 'selected'; ?>>All</option>
                                                <?php for (; $row_level = mysql_fetch_assoc($level);) { ?>
                                                    <option value="<?php echo $row_level['levelid'] ?>" 
                                                        <?php if ($lvl == $row_level['levelid']) echo 'selected' ?>>
                                                        <?php echo $row_level['levelname']; ?>
                                                    </option>
                                                <?php } ?>                                                
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row-fluid">
                                        
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>S/N</th>
                                                    <th>Matric</th>
                                                    <th>Name</th>
                                                    <th>Phone</th>
                                                    <th>Admission Mode</th>
                                                    <th>State of Origin</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if ($totalRows_stud > 0) {
                                                    $i = 1;
                                                    do {
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $i++; ?></td>
                                                            <td>
                                                                <?php
                                                                $matric = '';
                                                                $link = '';
                                                                if ($row_stud['stdid'] != '') {
                                                                    $matric = $row_stud['stdid'];
                                                                    $link = '../../student/profile.php?stid=' . $matric;
                                                                } else {
                                                                    $matric = $row_stud['jambregid'];
                                                                }
                                                                ?>
                                                                <a href="<?php echo $link ?>">

                                                                    <?php echo $matric ?>
                                                                </a>
                                                            </td>
                                                            <td>
                                                                <?php echo "{$row_stud['fname']} {$row_stud['lname']}"; ?>
                                                            </td>
                                                            <td><?php echo (isset($row_stud['phone'])) ? $row_stud['phone'] : '-'; ?></td>
                                                            <td><?php echo (isset($row_stud['typename'])) ? $row_stud['typename'] : '-'; ?></td>
                                                            <td><?php echo (isset($row_stud['stname'])) ? $row_stud['stname'] : '-'; ?></td>
                                                        </tr>
                                                        <?php
                                                    } while ($row_stud = mysql_fetch_assoc($stud));
                                                } else {
                                                    ?>
                                                    <tr>
                                                        <td colspan="5">No record available!</td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
        </div>
    </body>
</html>