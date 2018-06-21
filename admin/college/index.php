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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2")) {
    $insertSQL = sprintf("INSERT INTO college "
            . "(colid, colname, colcode, coltitle, remark, page_up) "
            . "VALUES (%s, %s, %s, %s, %s, %s)", 
            GetSQLValueString($_POST['colid'], "int"), 
            GetSQLValueString($_POST['colname'], "text"), 
            GetSQLValueString($_POST['colcode'], "text"), 
            GetSQLValueString($_POST['coltitle'], "text"), 
            GetSQLValueString($_POST['remark'], "text"),
            GetSQLValueString($_POST['page_up'], "text"));

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

$maxRows_rscol = 10;
$pageNum_rscol = 0;
if (isset($_GET['pageNum_rscol'])) {
    $pageNum_rscol = $_GET['pageNum_rscol'];
}
$startRow_rscol = $pageNum_rscol * $maxRows_rscol;

mysql_select_db($database_tams, $tams);
$query_rscol = "SELECT * FROM college";
$query_limit_rscol = sprintf("%s LIMIT %d, %d", $query_rscol, $startRow_rscol, $maxRows_rscol);
$rscol = mysql_query($query_limit_rscol, $tams) or die(mysql_error());
$row_rscol = mysql_fetch_assoc($rscol);

if (isset($_GET['totalRows_rscol'])) {
    $totalRows_rscol = $_GET['totalRows_rscol'];
}
else {
    $all_rscol = mysql_query($query_rscol);
    $totalRows_rscol = mysql_num_rows($all_rscol);
}
$totalPages_rscol = ceil($totalRows_rscol / $maxRows_rscol) - 1;



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
                                        Update <?php echo $college_name; ?> in the University
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div id="accordion2" class="accordion">
                                        <div class="accordion-group">
                                            <div class="accordion-heading">
                                                <a href="#collapseOne" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                    <i class="icon-plus"></i> Create New <?php echo $college_name; ?>
                                                </a>
                                            </div>
                                            <div class="accordion-body collapse" id="collapseOne" style="height: 0px;">
                                                <div class="accordion-inner">
                                                    
                                                    <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post">
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield"><?php echo $college_name; ?> Name </label>
                                                            <div class="controls">
                                                                <input name="colname" type="text"  class="input-xlarge" required=""/>
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield"><?php echo $college_name;?> Code</label>
                                                            <div class="controls">
                                                                <input name="colcode" type="text" class="input-medium"  required="" />
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield"><?php echo $college_name;?> Title</label>
                                                            <div class="controls">
                                                                <input type="text" name="coltitle" class="input-large" required="" />
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
                                                        <input type="hidden" name="colid" value="" />
                                                        <input type="hidden" name="MM_insert" value="form2" />
                                                        <div class="form-actions">
                                                            <input type="submit" value="Add <?php echo $college_name;?>" class="btn btn-primary" >
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
                                            <?php if ($totalRows_rscol > 0) { // Show if recordset not empty ?>
                                                <?php do { ?>
                                                    <tr>
                                                        <td><?php echo $row_rscol['colcode']; ?></td>
                                                        <td><a href="../../college/college.php?cid=<?= $row_rscol['colid']?>"><?php echo $row_rscol['colname']; ?></a></td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-cogs"></i><span class="caret"></span></a>
                                                                <ul class="dropdown-menu">
                                                                    <li>
                                                                        <a href="../department/?cid=<?php echo $row_rscol['colid']; ?>">Add Department</a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="college.php?cid=<?php echo $row_rscol['colid']; ?>">Edit</a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="#">Delete</a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php }while ($row_rscol = mysql_fetch_assoc($rscol)); ?>
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

