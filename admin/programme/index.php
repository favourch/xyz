<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');



/*-----------------------------------------------*
 * 
 * Logic of the College/index.php Page 
 *
 **------------------------------------------------
 */

$MM_authorizedUsers = "1,20";
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
$query_opts = "SELECT * FROM programme";
$opts = mysql_query($query_opts, $tams) or die(mysql_error());
$row_opts = mysql_fetch_assoc($opts);
$totalRows_opts = mysql_num_rows($opts);


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
    $insertSQL = sprintf("INSERT INTO "
            . "programme (progname, deptid, duration, progcode, "
            . "remark, page_up, Page_down, continued) "
            . "VALUES (%s, %s, %s, %s, %s,%s, %s, %s)",
            GetSQLValueString($_POST['progname'], "text"), 
            GetSQLValueString($_POST['deptid'], "int"), 
            GetSQLValueString($_POST['duration'], "int"), 
            GetSQLValueString($_POST['progcode'], "text"),
            GetSQLValueString($_POST['remark'], "text"),
            GetSQLValueString($_POST['page_up'], "text"),
            GetSQLValueString($_POST['page_down'], "text"),
            GetSQLValueString($_POST['continued'], "text"));

    mysql_select_db($database_tams, $tams);
    $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());

    $insertGoTo = "index.php";
    if ($Result1)
        $insertGoTo = ( isset($_GET['success']) ) ? $insertGoTo : $insertGoTo . "?success";
    else
        $insertGoTo = ( isset($_GET['error']) ) ? $insertGoTo : $insertGoTo . "?error";

    if (isset($_SERVER['QUERY_STRING'])) {
        $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
        $insertGoTo .= $_SERVER['QUERY_STRING'];
    }
    header(sprintf("Location: %s", $insertGoTo));
}


mysql_select_db($database_tams, $tams);
$query_dept = sprintf("SELECT deptid, deptname "
                    . "FROM department ");
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);




if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}
$deptname = "";



$page_title = "Tasued";
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
                    </div>
                    <br/>-->
                    <div class="span6">
                        <?php statusMsg();?>
                    </div>
                    
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Add New Programme 
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div id="accordion2" class="accordion">
                                        <div class="accordion-group">
                                            <div class="accordion-heading">
                                                <a href="#collapseOne" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                    <i class="icon-plus"></i> Add New Programme <?php if (isset($_GET['cid'])) echo "to " . $row_college['coltitle'] ?>
                                                </a>
                                            </div>
                                            <div class="accordion-body collapse" id="collapseOne" style="height: 0px;">
                                                <div class="accordion-inner">
                                                    
                                                    <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post">
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Programme Name </label>
                                                            <div class="controls">
                                                                <input name="progname" type="text"  class="input-xxlarge" required=""/>
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Programme Code</label>
                                                            <div class="controls">
                                                                <input name="progcode" type="text" class="input-mini" required="" />
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Duration</label>
                                                            <div class="controls">
                                                                <input name="duration" type="number" class="input-mini" max="10" min="1" required="" />
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Continued Programme</label>
                                                            <div class="controls">
                                                                <select name="continued" class="input-medium" required="">
                                                                    <option value="">-- Choose --</option>
                                                                    <option value="yes">Yes</option>
                                                                    <option value="No">No</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Department </label>
                                                            <div class="controls">
                                                                <select name="deptid" class="choosen-select" required="">
                                                                    <?php do { ?>
                                                                        <option value="<?= $row_dept['deptid'] ?>"><?= $row_dept['deptname'] ?></option>
                                                                        <?php } while ($row_dept = mysql_fetch_assoc($dept)) ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Remark </label>
                                                            <div class="controls">
                                                                <textarea name="remark" class="input-xlarge"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Page Up </label>
                                                            <div class="controls">
                                                                <textarea name="page_up" class="input-xlarge"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Page Down </label>
                                                            <div class="controls">
                                                                <textarea name="page_down" class="input-xlarge"></textarea>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="MM_insert" value="form1" />
                                                        <div class="form-actions">
                                                            <input type="submit" value="Add Programme" class="btn btn-primary" >
                                                            <button class="btn" type="button">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p>&nbsp;</p>
                                    <table class="table table-hover table-nomargin dataTable table-bordered dataTables_wrapper">
                                        <thead>
                                            <tr>
                                                <th width="5%">Code</th>
                                                <th width="80">Name</th>
                                                <th width="15%">&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($totalRows_opts > 0) { // Show if recordset not empty ?>
                                                <?php do { ?>
                                                    <tr>
                                                        <td><?php echo $row_opts['progcode'] ?></td>
                                                        <td><a href="../../programme/programme.php?pid=<?= $row_opts['progid']?>"><?php echo $row_opts['progname'] ?></a></td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-cogs"></i><span class="caret"></span></a>
                                                                <ul class="dropdown-menu">
<!--                                                                    <li>
                                                                        <a href="../programme/?did=<?php echo $row_opts['deptid']?>">Add Programme</a>
                                                                    </li>-->
                                                                    <li>
                                                                        <a href="programme.php?pid=<?php echo $row_opts['progid'];?>">Edit</a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="#">Delete</a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php }while ($row_opts = mysql_fetch_assoc($opts)); ?>
                                            <?php } // Show if recordset not empty  ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH."/footer.php" ?>
    </body>
</html>

