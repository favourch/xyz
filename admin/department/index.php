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


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
    $insertSQL = sprintf("INSERT INTO department (deptid, deptname, deptcode, colid, remark) VALUES (%s, %s, %s, %s, %s)", GetSQLValueString($_POST['deptid'], "int"), GetSQLValueString($_POST['deptname'], "text"), GetSQLValueString($_POST['deptcode'], "text"), GetSQLValueString($_POST['colid'], "int"), GetSQLValueString($_POST['remark'], "text"));

    mysql_select_db($database_tams, $tams);
    $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());
    $updateGoTo = "index.php";
    if ($Result1)
        $updateGoTo = ( isset($_GET['success']) ) ? $updateGoTo : $updateGoTo . "?success";
    else
        $updateGoTo = ( isset($_GET['error']) ) ? $updateGoTo : $updateGoTo . "?error";

    if (isset($_SERVER['QUERY_STRING'])) {
        $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
        $updateGoTo .= $_SERVER['QUERY_STRING'];
    }
    header(sprintf("Location: %s", $updateGoTo));
}

mysql_select_db($database_tams, $tams);
$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);

$where = "";
$colname_college = "-1";
if (isset($_GET['cid'])) {
    $colname_college = $_GET['cid'];
    $where = sprintf("WHERE colid = %s", GetSQLValueString($colname_college, "int") );
    
}

mysql_select_db($database_tams, $tams);
$query_college = sprintf("SELECT coltitle FROM college %s ", $where);
$college = mysql_query($query_college, $tams) or die(mysql_error());
$row_college = mysql_fetch_assoc($college);
$totalRows_college = mysql_num_rows($college);

$maxRows_coldept = 10;
$pageNum_coldept = 0;
if (isset($_GET['pageNum_coldept'])) {
    $pageNum_coldept = $_GET['pageNum_coldept'];
}
$startRow_coldept = $pageNum_coldept * $maxRows_coldept;

$where2 = "";
$colname_coldept = "-1";
if (isset($_GET['cid'])) {
    $colname_coldept = $_GET['cid'];
    $where2 = sprintf("WHERE colid = %s ORDER BY deptname ASC ", GetSQLValueString($colname_coldept, "int"));
}

mysql_select_db($database_tams, $tams);
$query_coldept = sprintf("SELECT deptid, deptname, deptcode "
                        . "FROM department %s", $where2);
$coldept = mysql_query($query_coldept, $tams) or die(mysql_error());
$row_coldept = mysql_fetch_assoc($coldept);

if (isset($_GET['totalRows_coldept'])) {
    $totalRows_coldept = $_GET['totalRows_coldept'];
}
else {
    $all_coldept = mysql_query($query_coldept);
    $totalRows_coldept = mysql_num_rows($all_coldept);
}
$totalPages_coldept = ceil($totalRows_coldept / $maxRows_coldept) - 1;


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
                                        Add New Department 
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div id="accordion2" class="accordion">
                                        <div class="accordion-group">
                                            <div class="accordion-heading">
                                                <a href="#collapseOne" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                    <i class="icon-plus"></i> Add New Department <?php if (isset($_GET['cid'])) echo "to " . $row_college['coltitle'] ?>
                                                </a>
                                            </div>
                                            <div class="accordion-body collapse" id="collapseOne" style="height: 0px;">
                                                <div class="accordion-inner">
                                                    
                                                    <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post">
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Department Name </label>
                                                            <div class="controls">
                                                                <input name="deptname" type="text"  class="input-xlarge" required=""/>
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Department Code</label>
                                                            <div class="controls">
                                                                <input name="deptcode" type="text" class="input-medium"  required="" />
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">College Name</label>
                                                            <div class="controls">
                                                                <?php
                                                                if (isset($_GET['cid']))
                                                                    echo $row_college['coltitle'];
                                                                    
                                                                else {
                                                                    ?>
                                                                    <select name="colid">
                                                                        <?php
                                                                        do {
                                                                            ?>
                                                                            <option value="<?php echo $row_col['colid'] ?>"><?php echo $row_col['coltitle'] ?></option>
                                                                            <?php
                                                                        }
                                                                        while ($row_col = mysql_fetch_assoc($col));
                                                                        $rows = mysql_num_rows($col);
                                                                        if ($rows > 0) {
                                                                            mysql_data_seek($col, 0);
                                                                            $row_col = mysql_fetch_assoc($col);
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                        <?php } ?>
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Remark </label>
                                                            <div class="controls">
                                                                <textarea name="remark" class="input-xlarge"></textarea>
                                                            </div>
                                                        </div>
                                                        <?php if (isset($_GET['cid'])) { ?>
                                                            <input type="hidden" name="colid" value="<?php echo ($_GET['cid']) ? $_GET['cid'] : "" ?>" />
                                                        <?php } ?>
                                                        <input type="hidden" name="deptid" value="" />
                                                        <input type="hidden" name="MM_insert" value="form1" />
                                                        <div class="form-actions">
                                                            <input type="submit" value="Add Department" class="btn btn-primary" >
                                                            <button class="btn" type="button">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p>&nbsp;</p>
                                    <table class="table table-condensed table-condensed table-striped">
                                        <thead>
                                            <tr>
                                                <th width="5%">Code</th>
                                                <th width="80" class="colspace">Name</th>
                                                <th width="15%">&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($totalRows_coldept > 0) { // Show if recordset not empty ?>
                                                <?php do { ?>
                                                    <tr>
                                                        <td><?php echo $row_coldept['deptcode'] ?></td>
                                                        <td><a href="../../department/department.php?did=<?= $row_coldept['deptid']?>"><?php echo $row_coldept['deptname'] ?></a></td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-cogs"></i><span class="caret"></span></a>
                                                                <ul class="dropdown-menu">
                                                                    <li>
                                                                        <a href="../programme/?did=<?php echo $row_coldept['deptid']?>">Add Programme</a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="department.php?did=<?php echo $row_coldept['deptid'];?>">Edit</a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="#">Delete</a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php }while ($row_coldept = mysql_fetch_assoc($coldept)); ?>
                                            <?php } // Show if recordset not empty  ?>
                                        </tbody>

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
</html>

