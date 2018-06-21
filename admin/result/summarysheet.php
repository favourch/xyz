<?php 
if (!isset($_SESSION)) {
  session_start();
}
require_once('../../path.php');

$auth_users = "1,2,3,4,5,20,30";
check_auth($auth_users, $site_root);


$query_prog = sprintf("SELECT progid, progname, duration "
                            . "FROM programme p "
                            . "ORDER BY progname ASC");

if(getAccess() == 2) {
    $query_prog = sprintf("SELECT progid, progname, duration "
                            . "FROM programme p, department d "
                            . "WHERE d.deptid = p.deptid "
                            . "AND d.colid=%s "
                            . "ORDER BY progname ASC",
                            GetSQLValueString(getSessionValue('cid'), 'int'));
}elseif(getAccess() == 3) {
    $query_prog = sprintf("SELECT progid, progname, duration "
                            . "FROM programme p, department d "
                            . "WHERE d.deptid = p.deptid "
                            . "AND p.deptid=%s "
                            . "ORDER BY progname ASC",
                            GetSQLValueString(getSessionValue('did'), 'int'));
}

$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);

$query_level = "SELECT * FROM level_name";
$level = mysql_query($query_level, $tams) or die(mysql_error());
$totalRows_level = mysql_num_rows($level);

$totalRows_student = "";
$student ="";
if( isset( $_GET['filter'] ) && $_GET['filter'] != "col"){

$query_student = createFilter("stud");
$student = mysql_query($query_student, $tams) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);
}

 $lvl = "";
 $filtername = "The University";
 if( isset($_GET['filter'])){
 	if( $_GET['filter'] == "dept") {		
            do { 
                if( $_GET['did'] == $row_dept['deptid'] )
                $filtername = $row_dept['deptname'];
            } while ($row_dept = mysql_fetch_assoc($dept)); 	
	}
	
	if( $_GET['filter'] == "lvl" ) {
            $lvl = $_GET['lvl'];
            if( isset( $_GET['did'] ) ) {		
                do { 
                    if( $_GET['did'] == $row_dept['deptid'] )
                    $filtername = $row_dept['deptname'];
                } while ($row_dept = mysql_fetch_assoc($dept)); 
                $filtername .= " (".$_GET['lvl']."00 Level)";	
            }
		
	}
 }

$did = "-1";
if( isset( $_GET['pid'] ) )	
$did = $_GET['pid'];

$pid = "-1";
if( isset( $_GET['pid'] ) )	
$pid = $_GET['pid'];

$sid = "-1";
if( isset( $_GET['sid'] ) )	
$sid = $_GET['sid'];

$cid = "-1";
if( isset( $_GET['cid'] ) )	
$cid = $_GET['cid'];

$prog_list = array();
$options = '';
$duration = 0;

for($idx = 0; $idx < $totalRows_prog; $idx++, $row_prog = mysql_fetch_assoc($prog)) {
    $selected = '';
    if(!(strcmp($row_prog['progid'], $pid))) {
        $selected = 'selected';
        $duration = $row_prog['duration'];
    }
    
    $options .= "<option value=\"{$row_prog['progid']}\" {$selected}>{$row_prog['progname']}</option>";
    
    $prog_list[$row_prog['progid']] = $row_prog['duration'];
}

$allow = false;
$acl = array(4,5);
if( getAccess() == 1 || (getAccess() == 2 && getSessionValue('cid') == $cid) || (getAccess() == 3 && getSessionValue('did') == $did) || (in_array(getAccess(), $acl) && getSessionValue('did') == $did) ){
	 $allow = true;
}


$query_Rssess = "SELECT * FROM `session` ORDER BY sesname DESC";
$Rssess = mysql_query($query_Rssess, $tams) or die(mysql_error());
$row_Rssess = mysql_fetch_assoc($Rssess);
$totalRows_Rssess = mysql_num_rows($Rssess);
?>
<!doctype html>
<html>
    <?php include INCPATH . "/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
        <?php include INCPATH . "/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH . "/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH . "/page_header.php" ?>

                    <div class="row-fluid">                        
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3>Summary Sheet</h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <form name="summarysheet" method="post" target='_blank' action="smprint.php">
                                            <div class="row-fluid">
                                                <div class="span3">Choose Programme: </div>
                                                <div class="span4">   
                                                    <select name="prog" id="prog" onchange="progFilter(this)">
                                                        <option value="-1" <?php if (!(strcmp(-1, $pid))) {
                                                        echo "selected=\"selected\"";} ?>>
                                                            ---Select A Programme---
                                                        </option>
                                                        <?php echo $options ?>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="row-fluid">
                                                <div class="span3">Choose Session: </div>
                                                <div class="span4">  
                                                    <select name="session" id="session" onchange="sesFilter(this)">
                                                        <option value="-1">--Session--</option>
                                                        <?php do {?>                                                        
                                                        <option value="<?php echo $row_Rssess['sesid'] ?>"  
                                                            <?php if (!(strcmp($row_Rssess['sesid'], $sid))) {
                                                                echo "selected=\"selected\"";}?>>
                                                            <?php echo $row_Rssess['sesname'] ?>
                                                        </option>
                                                        <?php } while ($row_Rssess = mysql_fetch_assoc($Rssess));?>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="row-fluid">
                                                <div class="span3">Choose Level: </div>
                                                <div class="span4">   
                                                    <select name="level" id="level">
                                                        <option value="-1">--Level--</option>
                                                        <?php for (; $row_level = mysql_fetch_assoc($level);) : ?>
                                                            <option value="<?php echo $row_level['levelid'] ?>">
                                                                <?php echo $row_level['levelname'] ?>
                                                            </option>
                                                        <?php endfor; ?>
                                                        <!--<?php for ($idx = 1; $idx <= $duration; $idx++) { ?>
                                                        <option value="<?php echo $idx ?>"><?php echo $idx . '00' ?></option>
                                                        
                                                        <?php } if ($duration > 0) {?>          
                                                        
<!--                                                        <option value="<?php echo $duration + 1 ?>">Extra Year 1</option>
                                                        <option value="<?php echo $duration + 2 ?>">Extra Year 2</option>
                                                        <?php } ?>-->
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="row-fluid">
                                                <div class="span3">Choose Semester: </div>
                                                <div class="span4">   
                                                    <select name="semester" id="semester2">
                                                        <option value="-1">--Semester--</option>
                                                        <option value="F">First</option>
                                                        <option value="S">Second</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <input type="submit" name="submit" id="submit" value="Process Summary Sheet" 
                                                   class="btn btn-primary"/>
                                            
                                            <input type="hidden" name="sid" value="<?php echo $sid ?>" />
                                            <input type="hidden" name="pid" value="<?php echo $pid ?>" />
                                            <input type="hidden" name="cid" value="<?php echo $cid ?>" />
                                        </form>
                                    </div>                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH . "/footer.php" ?>
        </div>
    </body>
</html>