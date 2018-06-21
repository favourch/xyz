<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



/* -----------------------------------------------*
 * 
 * Logic of the College/coledit.php Page 
 *
 * *------------------------------------------------
 */
if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}

$MM_authorizedUsers = "2,3";
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

$MM_restrictGoTo = "../index.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}



if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

mysql_select_db($database_tams, $tams);

/*query to build recordsets */

$query_sess = sprintf("SELECT * FROM `session` ORDER BY sesname DESC");
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$cur_SQL = sprintf("SELECT * from curriculum WHERE status = 'active'");
$cur = mysql_query($cur_SQL) or die(mysql_error());
$row_cur = mysql_fetch_assoc($cur);


$sid = $row_sess['sesid'];
if (isset($_GET['sid'])) {
    $sid = $_GET['sid'];
}

$curent_cur = 1;
if (isset($_GET['cur'])) {
    $curent_cur = $_GET['cur'];
}

$query_workingSess = sprintf("SELECT * FROM `session` WHERE sesid = %s", GetSQLValueString($sid, "int"));
$workingSess = mysql_query($query_workingSess, $tams) or die(mysql_error());
$row_workingSess = mysql_fetch_assoc($workingSess);
$totalRows_workingSess = mysql_num_rows($workingSess);

/*form action to submit form data to the teaching table*/

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$Result1 = false;

if ( isset($_POST['submit']) ) {
	
	$dpt = $_POST['deptid'];
	$deleteSQL = sprintf("DELETE FROM teaching "
                            . "WHERE deptid=%s "
                            . "AND sesid=%s "
                            . "AND csid NOT IN "
                            . "(SELECT r.csid "
                            . "FROM result r, student s, programme p "
                            . "WHERE r.stdid = s.stdid "
                            . "AND p.progid = s.progid "
                            . "AND p.deptid=%s)",
                            GetSQLValueString($dpt, "int"),
                            GetSQLValueString($sid, "int"),
                            GetSQLValueString($dpt, "int"));

	$Result1 = mysql_query($deleteSQL, $tams) or die(mysql_error());

	
	for( $i = 0; $i<count($_POST['course']); $i++){
		$lt1 = $_POST['clect'][$i];
		$lt2 = $_POST['alect'][$i];
		$crs = $_POST['course'][$i];	
		$upld = ( isset($_POST['upld'][$i]) )? $_POST['upld'][$i]: "";
		$appr = ( isset($_POST['appr'][$i]) )? $_POST['appr'][$i]: "";
		
		$updateSQL = sprintf("UPDATE teaching "
                                    . "SET lectid1=%s, lectid2=%s, upload=%s, approve=%s "
                                    . "WHERE csid=%s "
                                    . "AND deptid = %s "
                                    . "AND sesid=%s",
                                    GetSQLValueString($lt1, "text"),
                                    GetSQLValueString($lt2, "text"),
                                    GetSQLValueString($upld, "text"),
                                    GetSQLValueString($appr, "text"),
                                    GetSQLValueString($crs, "text"),
                                    GetSQLValueString($dpt, "int"),
                                    GetSQLValueString($sid, "int"));
		
		$Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
		$update_info = mysql_info($tams);
		list($f,$s,$t) = explode(":", $update_info);
					   
		if( strpos($s,"0") ){
		  $insertSQL = sprintf("REPLACE INTO teaching (lectid1, lectid2, csid, deptid, sesid) "
                                        . "VALUES (%s, %s, %s, %s, %s)",
                                        GetSQLValueString($lt1, "text"),
                                        GetSQLValueString($lt2, "text"),
                                        GetSQLValueString($crs, "text"),
                                        GetSQLValueString($dpt, "int"),
                                        GetSQLValueString($sid, "int"));			   
		  $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error()); 
		}
	}
}
 
if($Result1){
    $notification->set_notification('Operation Successfull', 'success');
}

$colname_dept = "-1";
if( getAccess() == 2){
	$colname_dept = getSessionValue('cid');
}

$query_dept = sprintf("SELECT deptid, deptname "
                    . "FROM department "
                    . "WHERE colid = %s ", 
                    GetSQLValueString($colname_dept, "int"));
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$colname_course = "-1";
if( isset($row_dept['deptid'])){
	$colname_course = $row_dept['deptid'];
}

if( isset($_GET['did'])){
	$colname_course = $_GET['did'];
}

if ( getAccess() == 3 ) {
  $colname_course = getSessionValue('did');
}

$query_course = sprintf("SELECT c.csid, c.csname FROM course c WHERE c.deptid = %s AND c.curid = %s",
						GetSQLValueString($colname_course, "int"), GetSQLValueString($curent_cur, "int"));
$course = mysql_query($query_course, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($course);
$totalRows_course = mysql_num_rows($course);

$query_lect = sprintf("SELECT lectid, fname, lname "
                    . "FROM lecturer "
                    . "WHERE deptid = %s", GetSQLValueString($colname_course, "int"));
$lect = mysql_query($query_lect, $tams) or die(mysql_error());
$row_lect = mysql_fetch_assoc($lect);
$totalRows_lect = mysql_num_rows($lect);
 
$query_crslist = sprintf("SELECT t.csid, t.lectid1, t.lectid2, upload, approve "
                        . "FROM teaching t "
                        . "WHERE t.deptid = %s "
                        . "AND t.sesid = %s",
                        GetSQLValueString($colname_course, "int"), 
                        GetSQLValueString($sid, "int"));

$crslist = mysql_query($query_crslist, $tams) or die(mysql_error());
$row_crslist = mysql_fetch_assoc($crslist);
$totalRows_crslist = mysql_num_rows($crslist);


$checked = array();
do{
	$checked[] = $row_crslist['csid'];
	$checked[$row_crslist['csid']]['lect1'] = $row_crslist['lectid1'];
	$checked[$row_crslist['csid']]['lect2'] = $row_crslist['lectid2'];
	$checked[$row_crslist['csid']]['upld'] = $row_crslist['upload'];
	$checked[$row_crslist['csid']]['appr'] = $row_crslist['approve'];
}while( $row_crslist = mysql_fetch_assoc($crslist) );


$option = array(); 
$count = 0;
do {  
	$i=0;
	$option[$count][$i++] = $row_dept['deptid'];
	$option[$count][$i] = $row_dept['deptname'];
	/*if(($option[$count][0] == $_GET['deptid']) || $option[$count][0] == $_GET['deptid'])
	$name = $option[$count][1];*/
	$count++;
} while ($row_dept = mysql_fetch_assoc($dept));

$option2 = array(); 
$count = 0;
do {  
    $i=0;
    $option2[$count][$i++] = $row_lect['lectid'];
    $option2[$count][$i] = $row_lect['lname'].", ".substr($row_lect['fname'],0,1);
    $count++;
} while ($row_lect = mysql_fetch_assoc($lect));           




	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}

?>
<!doctype html>
<html>
<?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">

<?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
        <?php include INCPATH."/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
            <?php include INCPATH."/page_header.php" ?>
                    <!--                    <div class="breadcrumbs">
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
                                        </div>-->

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Course Allocation to Lecturers for <?php echo $row_workingSess['sesname']?> Session
                                    </h3>
                                </div>
                                <div class="box-content nopadding">
                                    <table class="table ">
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td>        
                                                <table class="table table-striped">
                                                    <tr>
                                                        <td>
                                                            <div class="span3">
                                                                <select onchange="sesfilt(this)">
                                                                <?php do { ?>
                                                                    <option value="<?php echo $row_sess['sesid'] ?>" 
                                                                            <?php if (!(strcmp($row_sess['sesid'], $sid))) {
                                                                                echo "selected=\"selected\"";
                                                                            } ?>>
                                                                    <?php echo $row_sess['sesname'] ?>
                                                                    </option>
                                                                    <?php }while ($row_sess = mysql_fetch_assoc($sess)); ?>
                                                            </select>
                                                            </div>
                                                            
                                                        </td>

                                                            <?php if (getAccess() == 2) { ?>
                                                            <td>
                                                                <select name="deptid" onChange="deptfilt(this)">
                                                                        <?php foreach ($option as $options) { ?>
                                                                            <option value="<?php echo $options[0] ?>" <?php if ($options[0] == $colname_course) echo "selected"; ?>>
                                                                            <?php echo $options[1] ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                </select>
                                                            </td>
                                                            <?php } ?>
                                                            <td>
                                                                <select name="deptid" onChange="curfilt(this)">
                                                                        <?php do { ?>
                                                                            <option value="<?= $row_cur['curid'] ?>" <?php if ($row_cur['curid'] == $curent_cur) echo "selected"; ?>>
                                                                            <?=  $row_cur['curname']?>
                                                                            </option>
                                                                        <?php }while($row_cur = mysql_fetch_assoc($cur)); ?>
                                                                </select>
                                                            </td>
                                                    </tr>              
                                                </table>

                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <form name="assignform" action="<?php echo $editFormAction ?>" method="post">
                                                    <fieldset>
                                                        <legend>Departmental Courses</legend>
                                                        <table class="table table-condensed table-striped table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Course Code</th>
                                                                    <th>Course Title</th>
                                                                    <th>Convener</th>
                                                                    <th>Assistant</th>
                                                                    <th>Enable</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if ($totalRows_course > 0) { // Show if recordset not empty  ?>
                                                                    <?php
                                                                    do {
                                                                        $conv = "";
                                                                        $asst = "";
                                                                        $check = "";
                                                                        if (in_array($row_course['csid'], $checked)) {
                                                                            $conv = $checked[$row_course['csid']]['lect1'];
                                                                            $asst = $checked[$row_course['csid']]['lect2'];
                                                                            $upld = $checked[$row_course['csid']]['upld'];
                                                                            $appr = $checked[$row_course['csid']]['appr'];
                                                                            $check = true;
                                                                        }
                                                                        ?>
                                                                <tr>
                                                                    <td><?php echo $row_course['csid'] ?></td>
                                                                    <td><?php echo ucwords(strtolower($row_course['csname'])) ?></td>
                                                                    <td>
                                                                        <select name="clect[]" style="width:70px">                              
                                                                            <?php foreach ($option2 as $options) { ?>
                                                                                <option value="<?php echo $options[0] ?>" <?php
                                                                                if (!(strcmp($options[0], $conv))) {
                                                                                    echo "selected=\"selected\"";
                                                                                }
                                                                                ?>><?php echo $options[1] ?></option>
                                                                        <?php } ?>
                                                                        </select>
                                                                    </td>
                                                                    <td>
                                                                        <select name="alect[]" style="width:70px">
                                                                            <option value="">---</option>
                                                                            <?php foreach ($option2 as $options) { ?>
                                                                                <option value="<?php echo $options[0] ?>" <?php
                                                                                if (!(strcmp($options[0], $asst))) {
                                                                                    echo "selected=\"selected\"";
                                                                                }
                                                                                ?>><?php echo $options[1] ?></option>
                                                                        <?php } ?>
                                                                        </select> 
                                                                    </td>
                                                                    <td>
                                                                        <input type="checkbox" class="cbox" name="course[]" value="<?php echo $row_course['csid'] ?>" <?php if ($check) echo "checked" ?>/>
                                                                    </td>
                                                                    <?php if ($check) { ?>
                                                                        <input type="hidden" name="upld[]" value="<?php echo $upld; ?>" />
                                                                        <input type="hidden" name="appr[]" value="<?php echo $appr; ?>" />
                                                                    <?php } ?>
                                                                </tr>
                                                                
                                                                <?php }while ($row_course = mysql_fetch_assoc($course)); ?>
                                                                <?php } ?>
                                                            </tbody>
                                                        </table>
                                                    </fieldset>
                                                    <p style="padding:0 230px"><input class="btn btn-primary" type="submit" name="submit" value="Allocate Courses to Lecturers" /></p>
                                                    <input type="hidden" name="deptid" value="<?php echo $colname_course ?>" />
                                                    <input type="hidden" name="MM_insert" value="form1" />
                                                </form>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                    </table>
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
    <script type="text/javascript">
    	$(function(){
			courseaallocate();			
		});
    </script>
</html>
<?php
mysql_free_result($sess);

mysql_free_result($dept);

mysql_free_result($course);

mysql_free_result($lect);

mysql_free_result($crslist);
?>