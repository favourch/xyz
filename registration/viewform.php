<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



/* -----------------------------------------------*
 * 
 * Logic of the College/index.php Page 
 *
 * *------------------------------------------------
 */

$MM_authorizedUsers = "1,2,3,4,5,6,10,20";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) {
    // For security, start by assuming the visitor is NOT authorized. 
    $isValid = False;

    // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
    // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
    if (!empty($UserName)) {
        // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
        // Parse the strings into arrays. 
        $arrUsers = Explode(",", $strUsers);
        $arrGroups = Explode(",", $strGroups);
        if (in_array($UserName, $arrUsers)) {
            $isValid = true;
        }
        // Or, you may restrict access to only certain users based on their username. 
        if (in_array($UserGroup, $arrGroups)) {
            $isValid = true;
        }
        if (($strUsers == "") && false) {
            $isValid = true;
        }
    }
    return $isValid;
}

$MM_restrictGoTo = "../login.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("", $MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {
    $MM_qsChar = "?";
    $MM_referrer = $_SERVER['PHP_SELF'];
    if (strpos($MM_restrictGoTo, "?"))
        $MM_qsChar = "&";
    if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0)
        $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
    $MM_restrictGoTo = $MM_restrictGoTo . $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
    header("Location: " . $MM_restrictGoTo);
    exit;
}



mysql_select_db($database_tams, $tams);
$query_rssess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);


$query = '';
if(getAccess() == 3) {
    $query = "AND p.deptid = ".  GetSQLValueString(getSessionValue('did'), 'int');
}

if(getAccess() == 2) {
    $query = "AND d.colid = ".  GetSQLValueString(getSessionValue('cid'), 'int');
}

// Recordset to populate programme dropdown
$query_prog = sprintf("SELECT p.progid, p.progname, d.colid, p.deptid "
                        . "FROM programme p, department d "
                        . "WHERE d.deptid = p.deptid %s", 
                        GetSQLValueString($query, "defined", $query));
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$level = 1;
$prg = $row_prog['progid'];

if(isset($_GET['lvl'])) {
    $level = $_GET['lvl'];
}

if(isset($_GET['pid'])) {
    $prg = $_GET['pid'];
}

$colname_stud = "-1";
if (isset($_GET['stid'])) {
  $colname_stud = $_GET['stid'];
}

if (getAccess() < 7 && isset($_GET['stid'])) {
  $colname_stud = $_GET['stid'];
}

if (getAccess() < 7 && !isset($_GET['stid'])) {
    $query_std = sprintf("SELECT s.stdid, s.progid, colid, p.deptid, fname, lname, level "
                            . "FROM student s, programme p, department d "
                            . "WHERE s.progid = p.progid AND d.deptid = p.deptid "
                            . "AND s.progid = %s AND s.level = %s", 
                            GetSQLValueString($prg, "text"), 
                            GetSQLValueString($level, "text"));
    $std = mysql_query($query_std, $tams) or die(mysql_error());
    $row_std = mysql_fetch_assoc($std);
    $totalRows_std = mysql_num_rows($std);
    
    if($totalRows_std > 0) {
        $colname_stud = $row_std['stdid'];
    }
}

$query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, s.level, s.progid, p.progname, d.deptname "
        . "FROM student s, programme p, department d "
        . "WHERE s.progid = p.progid "
        . "AND p.deptid = d.deptid "
        . "AND stdid = %s", GetSQLValueString($colname_stud, "text"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

if ( getAccess() < 10 ) {
    $prg = ($row_stud['progid'] != null)? $row_stud['progid']: $prg;
    $level = ($row_stud['level'] != null)? $row_stud['level']: $level;
}

$query_studs = sprintf("SELECT stdid, fname, lname "
        . "FROM student "
        . "WHERE level = %s "
        . "AND progid = %s"
                        , GetSQLValueString($level, "int")
                        , GetSQLValueString($prg, "int"));
$studs = mysql_query($query_studs, $tams) or die(mysql_error());
$row_studs = mysql_fetch_assoc($studs);
$total = $totalRows_studs = mysql_num_rows($studs);

$query_regsess = sprintf("SELECT s.* FROM session s, registration r "
                            . "WHERE r.sesid = s.sesid "
                            . "AND r.status=%s "
                            . "AND r.stdid=%s "
                            . "ORDER BY sesname DESC", 
                            GetSQLValueString("Registered", "text"), 
                            GetSQLValueString($colname_stud, "text"));
$regsess = mysql_query($query_regsess, $tams) or die(mysql_error());
$row_regsess = mysql_fetch_assoc($regsess);
$totalRows_regsess = mysql_num_rows($regsess);

$colname_course = "-1";
if (isset($colname_stud)) {
  $colname_course = $colname_stud;
}

$colname1_course = "-1";
if (isset($row_regsess['sesid'])) {
  $colname1_course = $row_regsess['sesid'];
}

if (isset($_GET['sid'])) {
  $colname1_course = $_GET['sid'];
}

$query_cursess = sprintf("SELECT * FROM `session` WHERE sesid=%s", GetSQLValueString($colname1_course, "int"));
$cursess = mysql_query($query_cursess, $tams) or die(mysql_error());
$row_cursess = mysql_fetch_assoc($cursess);
$totalRows_cursess = mysql_num_rows($cursess);

$colname2_course = "-1";
if (isset($row_stud['progid'])) {
  $colname2_course = $row_stud['progid'];
}

$query_course = sprintf("SELECT distinct r.csid, c.semester, c.csname, dc.status, dc.unit "
                        . "FROM course_reg r, course c, department_course dc "
                        . "WHERE r.cleared = 'TRUE' "
                        . "AND c.csid = r.csid AND r.csid = dc.csid "
                        . "AND r.stdid = %s "
                        . "AND r.sesid = %s "
                        . "ORDER BY c.semester ASC", 
                        GetSQLValueString($colname_course, "text"), 
                        GetSQLValueString($colname1_course, "int"));
$course = mysql_query($query_course, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($course);
$totalRows_course = mysql_num_rows($course);

$query_reg = sprintf("SELECT r.stdid "
        . "FROM registration r, student s "
        . "WHERE s.stdid = r.stdid "
        . "AND r.sesid = %s "
        . "AND s.level = %s "
        . "AND s.progid = %s "
        . "AND course = 'Registered'",  
                        GetSQLValueString($row_rssess['sesid'], "int"), 
                        GetSQLValueString($level, "int"), 
                        GetSQLValueString($prg, "int"));
$reg = mysql_query($query_reg, $tams) or die(mysql_error());
$row_reg = mysql_fetch_assoc($reg);
$totalReg = $totalRows_reg = mysql_num_rows($reg); 

$query_appr = sprintf("SELECT r.stdid "
        . "FROM registration r, student s "
        . "WHERE s.stdid = r.stdid "
        . "AND r.sesid = %s "
        . "AND s.level = %s "
        . "AND s.progid = %s "
        . "AND approved = 'TRUE'",  
                        GetSQLValueString($row_rssess['sesid'], "int"), 
                        GetSQLValueString($level, "int"), 
                        GetSQLValueString($prg, "int"));
$appr = mysql_query($query_appr, $tams) or die(mysql_error());
$row_appr = mysql_fetch_assoc($appr);
$totalApprd = $totalRows_appr = mysql_num_rows($appr);

$approve = false;
$query_approved = sprintf("SELECT * "
        . "FROM registration "
        . "WHERE stdid = %s "
        . "AND sesid = %s "
        . "AND status = 'Registered'",
                        GetSQLValueString($colname_course, "text"), 
                        GetSQLValueString($colname1_course, "int"));
$approved = mysql_query($query_approved, $tams) or die(mysql_error());
$row_approved = mysql_fetch_assoc($approved);
$totalRows_approved = mysql_num_rows($approved);

$registered = false;
if (isset($row_approved['status']) ) {
  $registered = true;
}

if(($row_approved['approved'] == 'TRUE' && $colname1_course == $row_rssess['sesid']) 
        || ($colname1_course > 0 && $colname1_course != $row_rssess['sesid'])) {
    $approve = true;
}

$name = ( isset($row_stud['lname']) ) ? "for ".$row_stud['lname']." ".$row_stud['fname']." (".$row_stud['stdid'].")": "";


$page_title = "Tasued";
?>
<!doctype html>
<html >
<?php include INCPATH."/header.php" ?>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
<?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
<?php include INCPATH."/side_nav_bar.php" ?>

            <div id="main">
                <div class="container-fluid nav-fixed">
<?php include INCPATH."/page_header.php" ?>
                    <div class="breadcrumbs">
                        <ul>
                            <li>
                                <a href="index.php">Home</a> <i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="college.php">College</a>
                            </li>
                        </ul>
                        <div class="close-bread">
                            <a href="#"><i class="icon-remove"></i></a>
                        </div>
                    </div>
                    <br/>
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-calendar"></i>
                                        Registered Courses <?php echo $name?>
                                    </h3>
                                </div>
                                <div class="box-content">
                                    
                                    <div class="row-fluid">
                                        <?php if( getAccess() < 4 ){  ?>
                                        <div class="span4 well">
                                            Population: <?php echo $total ?>&nbsp;&nbsp;
                                            Registered: <?php echo $totalReg ?>&nbsp;&nbsp;
                                            Cleared: <?php echo $totalApprd ?>&nbsp;&nbsp;  
                                        </div>
                                        <?php }?>
                                        <?php if($approve){ 
                                                if( getAccess() < 4 || getAccess() == 10 ){?>
                                        <div class="span4">
                                            <a class="btn"  href="editform.php?stid=<?php echo $colname_stud?>">Add/Delete</a>
                                        </div>
                                        <?php }}?>
                                    </div>
                                    
                                    <?php if(getSessionValue("MM_UserGroup") < 7 ){?>
                                    <div class="row-fluid">
                                        <?php if(getSessionValue("MM_UserGroup") < 4 ){?>
                                        <div class="span3">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Filter By <?= $programme_name?></label>
                                                <div class="controls controls-row">
                                                    <select onChange="progfilt(this)">
                                                        <?php
                                                            do {  
                                                        ?>
                                                        <option <?php if($prg == $row_prog['progid'])echo "selected";?> value="<?php echo $row_prog['progid']?>"><?php echo $row_prog['progname']?></option>
                                                        <?php
                                                            } while ($row_prog = mysql_fetch_assoc($prog));
                                                        ?>
                                                  </select>
                                                </div>
                                            </div>
                                        </div>
                                        <?php }?>
                                        <div class="span3">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Filter by Level</label>
                                                <div class="controls controls-row">
                                                    <select onChange="lvlfilt(this)">
                                                        <option value="1" <?php if($level == 1) echo 'selected';?>>100</option>
                                                        <option value="2" <?php if($level == 2) echo 'selected';?>>200</option>
                                                        <option value="3" <?php if($level == 3) echo 'selected';?>>300</option>
                                                        <option value="4" <?php if($level == 4) echo 'selected';?>>400</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="span3">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Search by Student</label>
                                                <div class="controls controls-row">
                                                    <select onChange="studfilt(this)" name="stdid">
                                                        <?php do { ?>
                                                        <option <?php if($colname_stud == $row_studs['stdid'])echo "selected";?> value="<?php echo $row_studs['stdid']?>"><?php echo ucwords(strtolower($row_studs['lname']." ".$row_studs['fname']))." (".$row_studs['stdid'].")"?></option>
                                                        <?php } while ($row_studs = mysql_fetch_assoc($studs));?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="span3">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Filter by Session </label>
                                                <div class="controls controls-row">
                                                    <select name="sesid" onchange="sesfilt(this)">
                                                            <?php
                                                            do {
                                                                ?>
                                                                <option value="<?php echo $row_regsess['sesid'] ?>"
                                                                        <?php if (!(strcmp($row_regsess['sesid'], $colname1_course))) {
                                                                            echo "selected=\"selected\"";
                                                                        } ?>>
                                                                <?php echo $row_regsess['sesname'] ?>
                                                                </option>
                                                                <?php
                                                            }
                                                            while ($row_regsess = mysql_fetch_assoc($regsess));
                                                            $rows = mysql_num_rows($regsess);
                                                            if ($rows > 0) {
                                                                mysql_data_seek($regsess, 0);
                                                                $row_regsess = mysql_fetch_assoc($regsess);
                                                            }
                                                            ?>
                                                        </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php }?>
                                    <div class="row-fluid">
                                        <?php 
                                        if($registered) {
                                            if(true){
                                        ?>
                                        <table class="table table-condensed">
                                            <thead>
                                                <tr>
                                                    <th width="100" align="center">COURSE CODE</th>
                                                    <th width="410" align="center">COURSE NAME</th>
                                                    <th width="80" align="center">STATUS</th>
                                                    <th width="30">UNIT</th>
                                                    <th width="70" align="center">SEMESTER</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $tunits = 0; do{?>
                                                <tr>
                                                    <td><div align="center"><?php echo strtoupper($row_course['csid']); ?></div></td>
                                                    <td><?php echo ucwords(strtolower($row_course['csname'])); ?></td>
                                                    <td><div align="center"><?php echo $row_course['status']; ?></div></td>
                                                    <td><div align="center"><?php echo $row_course['unit'];
                                                            $tunits += $row_course['unit']; ?></div></td>
                                                    <td><div align="center"><?php echo (strtolower($row_course['semester']) == "f") ? "First" : "Second"; ?></div></td>
                                                </tr>
                                                <?php }while($row_course = mysql_fetch_assoc($course))?>
                                                <tr>
                                                    <th colspan="3" align="right" >Total Units</th>
                                                    <th align="center"><?php echo $tunits; ?></th>
                                                    <th></th>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <?php } else{?>
                                        <div class="alert alert-info">Your course form is awaiting your course adviser's approval!</div>
                                        <?php }
                                        
                                        }else{ ?>
                                        <div class="alert alert-error">You have not registered for this session <?php echo $row_cursess['sesname']?>!</div>
                                        
                                        <?php }?>
                                    </div>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
    <?php include INCPATH."/footer.php" ?>
    </body>
</html>

