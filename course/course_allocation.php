<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



$auth_users = "2,3";
check_auth($auth_users, $site_root);

require_once('../path.php');


$prog = "";

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
    if (isset($_POST['course'])) {
        for ($i = 0; $i < count($_POST['course']); $i++) {
            $crs = $_POST['course'][$i];
            $sts = $_POST['status'][$i];
            $unt = $_POST['unit'][$i];
            $lvl = $_POST['level'][$i];

            // Update course table
            $updateSQL = sprintf("UPDATE course SET status=%s, unit=%s, level = %s WHERE deptid = %s AND csid = %s", GetSQLValueString($sts, "text"), GetSQLValueString($unt, "int"), GetSQLValueString($lvl, "int"), GetSQLValueString($_POST['deptid'], "int"), GetSQLValueString($crs, "text"));

            $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
            $update_info = mysql_info($tams);
        }
    }
}

$colname_dept = "-1";
if (getSessionValue('cid') != NULL) {
    $colname_dept = getSessionValue('cid');
}

$query_dept = sprintf("SELECT deptid, deptname FROM department WHERE colid = %s", GetSQLValueString($colname_dept, "int"));
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$colname_courses = "-1";
if (isset($row_dept['deptid'])) {
    $colname_courses = $row_dept['deptid'];
}

if (isset($_GET['did'])) {
    $colname_courses = $_GET['did'];
}

if (getAccess() == 3) {
    $colname_courses = getSessionValue('did');
}

$query_info = sprintf("SELECT MAX(duration) AS max "
        . "FROM programme "
        . "WHERE deptid = %s", GetSQLValueString($colname_courses, "int"));
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$duration = ($totalRows_info > 0) ? $row_info['max'] : 0;

$query_courses = sprintf("SELECT c.csid, c.csname, d.colid, ct.catname, c.status, c.unit, c.level "
        . "FROM course c, category ct, department d "
        . "WHERE d.deptid = c.deptid AND ct.catid = c.catid "
        . "AND c.deptid = %s "
        . "ORDER BY c.csid", GetSQLValueString($colname_courses, "int"));

$courses = mysql_query($query_courses, $tams) or die(mysql_error());
$row_courses = mysql_fetch_assoc($courses);
$totalRows_courses = mysql_num_rows($courses);

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

                    <div class="row-fluid">
                        <?php if (isset($uploadstat)) : ?>
                        <div class="span12 alert alert-<?php echo $type ?>">
                            <?php echo $uploadstat ?>
                        </div>
                        <?php endif; ?>
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Course Status
                                    </h3>                                   
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <table align="center">
                                            <?php if (getAccess() == 2) { ?>
                                            <tr>
                                                <td nowrap="nowrap" align="right">Department</td>
                                                <td><label for="deptid"></label>
                                                    <select name="deptid" id="deptid" onchange="deptfilt(this)">
                                                        <?php
                                                        do {
                                                            ?>
                                                            <option value="<?php echo $row_dept['deptid'] ?>" 
                                                                    <?php if (!(strcmp($row_dept['deptid'], $colname_courses))) {
                                                                        echo "selected=\"selected\"";
                                                                    } ?>>
                                                            <?php echo $row_dept['deptname'] ?>
                                                            </option>
                                                            <?php
                                                        } while ($row_dept = mysql_fetch_assoc($dept));
                                                        $rows = mysql_num_rows($dept);
                                                        if ($rows > 0) {
                                                            mysql_data_seek($dept, 0);
                                                            $row_dept = mysql_fetch_assoc($dept);
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                        <?php } ?>             
                                        </table>
                                    </div>   
                                    
                                    <div class="row-fluid">
                                        <form name="assignform" method="post">
                                            <fieldset>
                                                <legend>Departmental Courses</legend>
                                                
                                                        
                                                <?php if ($totalRows_courses > 0) { // Show if recordset not empty
                                                    do {
                                                        $stat = $row_courses['status'];
                                                        $unit = $row_courses['unit'];
                                                        $level = $row_courses['level'];
                                                        ?>
                                                        <div class="row-fluid">
                                                            <div class='span6'>
                                                                <?php echo $row_courses['csid'] ?> - <?php echo ucwords(strtolower($row_courses['csname'])) ?>
                                                            </div>

                                                            <div class='span1'>
                                                                <select name="unit[]" class="input-small">
                                                                    <option value="1" <?php if ($unit == 1) echo "selected" ?>>1</option>
                                                                    <option value="2" <?php if ($unit == 2) echo "selected" ?>>2</option>
                                                                    <option value="3" <?php if ($unit == 3) echo "selected" ?>>3</option>
                                                                    <option value="4" <?php if ($unit == 4) echo "selected" ?>>4</option>
                                                                    <option value="5" <?php if ($unit == 5) echo "selected" ?>>5</option>
                                                                    <option value="6" <?php if ($unit == 6) echo "selected" ?>>6</option>
                                                                </select>
                                                            </div>

                                                            <div class='span2'>
                                                                <select name="status[]" class="input-medium">
                                                                    <option value="Compulsory" <?php if ($stat == "Compulsory") echo "selected" ?>>Compulsory</option>
                                                                    <option value="Required" <?php if ($stat == "Required") echo "selected" ?>>Required</option>                
                                                                    <option value="Elective" <?php if ($stat == "Elective") echo "selected" ?>>Elective</option>
                                                                </select>
                                                            </div>

                                                            <div class='span1'>
                                                                <select name="level[]" class="input-small">
                                                                    <?php for ($idx = 1; $idx <= $duration; $idx++) { ?>
                                                                    <option value="<?php echo $idx ?>" <?php if ($idx == $level) echo "selected" ?>>
                                                                    <?php echo $idx . '00' ?>
                                                                    </option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>

                                                            <div class='span1'>
                                                                <input type="checkbox" class="cbox" name="course[]" value="<?php echo $row_courses['csid'] ?>"/>
                                                            </div>   
                                                            <div style="clear:both;"></div>

                                                        </div>

                                                    <?php }while ($row_courses = mysql_fetch_assoc($courses)); } ?>
                                            </fieldset>
                                            <p style="padding:0 260px">
                                                <input type="submit" name="submit" value="Assign Courses" class='btn btn-primary'/>
                                            </p>
                                            <input type="hidden" name="deptid" value="<?php echo $colname_courses ?>" />
                                            <input type="hidden" name="MM_insert" value="form1" />
                                                    
                                        </form>
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
    <script type="text/javascript">
        $(function(){
            courseassign();	
        });
	
    </script>
</html>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php  ?>
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<script src="../SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<link href="../SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/tams.js"></script>
<!-- InstanceEndEditable -->
<link href="../css/menulink.css" rel="stylesheet" type="text/css" />
<link href="../css/footer.css" rel="stylesheet" type="text/css" />
<link href="../css/sidemenu.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container">
  <div class="header">
    <!-- end .header -->
</div>
  <div class="topmenu">
<?php include '../include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include '../include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" -->Course Allocation to Lecturers for <?php echo $row_workingSess['sesname']?> Session<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>        
            <table>
                <tr>
                    <td>
                        <select onchange="sesfilt(this)">
                            <?php do{?>
                            <option value="<?php echo $row_sess['sesid']?>" 
                                <?php if (!(strcmp($row_sess['sesid'], $sid))) {echo "selected=\"selected\"";} ?>>
                                    <?php echo $row_sess['sesname']?>
                            </option>
                            <?php }while($row_sess = mysql_fetch_assoc($sess));?>
                        </select>
                    </td>
                    
                    <?php if( getAccess() == 2 ){?>
                    <td>
                        <select name="deptid" onChange="deptfilt(this)">
                        <?php  	  
                            foreach( $option as $options){						
                       ?>
                            <option value="<?php echo $options[0]?>" <?php if($options[0]==$colname_course) echo "selected";?>>
                            <?php echo $options[1]?>
                            </option>
                        <?php } ?>
                        </select>
                    </td>
                    <?php }?>
                </tr>              
            </table>
        
        </td>
      </tr>
        
      <tr>
        <td>
        	<form name="assignform" action="<?php echo $editFormAction?>" method="post">
                <fieldset>
                    <legend>Departmental Courses</legend>
                    <div style="font-size:inherit">
                        <p style="float:left;">
                            Code
                        </p>
                        
                        <p style="float:right;">
                            Enable
                        </p>            
                        
                        <p style="float:right;">Assistant</p>
                        
                        <p style="float:right;">Convener</p>
                        
                        <p style="float:right; width:40%; text-align:left">
                            Course Title
                        </p>
                        <div style="clear:both;"></div>
                        
                    </div>
                    <?php if ($totalRows_course > 0) { // Show if recordset not empty  ?>
                    <?php do{
                        $conv = "";
                        $asst = "";
                        $check = "";
                        if( in_array($row_course['csid'],$checked)){
                            $conv = $checked[$row_course['csid']]['lect1'];
                            $asst = $checked[$row_course['csid']]['lect2'];
							$upld = $checked[$row_course['csid']]['upld'];
							$appr = $checked[$row_course['csid']]['appr'];
                            $check = true;
                        }
                    ?>
                    <div style="font-size:inherit">
                        <p style="float:left;"><?php echo $row_course['csid']?>
                        </p>
                        
                        <p style="float:right;">
                            <input type="checkbox" class="cbox" name="course[]" value="<?php echo $row_course['csid']?>" <?php if( $check ) echo "checked"?>/>
                        </p>            
                        
                        <p style="float:right;">
                            <select name="alect[]" style="width:70px">
                            	<option value="">---</option>
                            <?php foreach( $option2 as $options){ ?>
                              <option value="<?php echo $options[0]?>" <?php if (!(strcmp($options[0], $asst))) {echo "selected=\"selected\"";} ?>><?php echo $options[1]?></option>
                              <?php }?>
                            </select>
                        </p>
                        
                        <p style="float:right;">
                            <select name="clect[]" style="width:70px">                              
                              <?php foreach( $option2 as $options){ ?>
                              <option value="<?php echo $options[0]?>" <?php if (!(strcmp($options[0], $conv))) {echo "selected=\"selected\"";} ?>><?php echo $options[1]?></option>
                              <?php }?>
                            </select>
                        </p>
                        
                        <p style="float:right; width:45%;">
                            <?php echo ucwords(strtolower($row_course['csname']))?>
                        </p>
                        <div style="clear:both;"></div>
                        <?php if( $check ){?>
                        	<input type="hidden" name="upld[]" value="<?php echo $upld;?>" />
                            <input type="hidden" name="appr[]" value="<?php echo $appr;?>" />
                        <?php }?>
                    </div>
                    
                    <?php }while( $row_course = mysql_fetch_assoc($course) );?>
                    <?php }?>
                </fieldset>
                <p style="padding:0 230px"><input type="submit" name="submit" value="Allocate Courses to Lecturers" /></p>
                <input type="hidden" name="deptid" value="<?php echo $colname_course?>" />
                <input type="hidden" name="MM_insert" value="form1" />
            </form>
        </td>
      </tr>
      <tr>
        <td></td>
      </tr>
    </table>
    <script type="text/javascript">
    	$(function(){
			courseaallocate();			
		});
    </script>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($sess);

mysql_free_result($dept);

mysql_free_result($course);

mysql_free_result($lect);

mysql_free_result($crslist);
?>