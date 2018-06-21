<?php


if (!isset($_SESSION)) {
    session_start();
}



require_once('../../path.php');



/* -----------------------------------------------*
 * 
 * Logic of the College/index.php Page 
 *
 * *------------------------------------------------
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
    $updateSQL = sprintf("UPDATE department "
                        . "SET deptname = %s, deptcode = %s, colid = %s, remark = %s WHERE deptid = %s",
                        GetSQLValueString($_POST['deptname'], "text"),
                        GetSQLValueString($_POST['deptcode'], "text"),
                        GetSQLValueString($_POST['colid'], "int"),
                        GetSQLValueString($_POST['remark'], "text"),
                        GetSQLValueString($_POST['deptid'], "int"));
    mysql_select_db($database_tams, $tams);
    $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());


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

$colname_editdept = "-1";
if (isset($_GET['did'])) {
    $colname_editdept = $_GET['did'];
}

mysql_select_db($database_tams, $tams);
$query_editdept = sprintf("SELECT * FROM department "
                        . "WHERE deptid = %s", 
                        GetSQLValueString($colname_editdept, "int"));
$editdept = mysql_query($query_editdept, $tams) or die(mysql_error());
$row_editdept = mysql_fetch_assoc($editdept);
$totalRows_editdept = mysql_num_rows($editdept);

$maxRows_coldept = 10;
$pageNum_coldept = 0;
if (isset($_GET['pageNum_coldept'])) {
    $pageNum_coldept = $_GET['pageNum_coldept'];
}
$startRow_coldept = $pageNum_coldept * $maxRows_coldept;

mysql_select_db($database_tams, $tams);
$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);
$collegename = ""; 

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
                    <?php statusMsg(); ?>
                    </div>

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                       Update <?php echo $row_editdept['deptname'];?> Department
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post">
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Department Name </label>
                                            <div class="controls">
                                                <input name="deptname" type="text"  class="input-xlarge" required="" value="<?php echo htmlentities($row_editdept['deptname'], ENT_COMPAT, 'utf-8'); ?>" />
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Department Code</label>
                                            <div class="controls">
                                                <input name="deptcode" type="text" class="input-medium"  required="" value="<?php echo htmlentities($row_editdept['deptcode'], ENT_COMPAT, 'utf-8'); ?>" />
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">College Name</label>
                                            <div class="controls">
                                                <select name="colid" class="input-xlarge">
                                                    <?php
                                                    do {
                                                        ?>
                                                        <option value="<?php echo $row_col['colid'] ?>" <?= ($row_col['colid'] ==  $row_editdept['colid']) ? "selected" : "" ?>><?php echo $row_col['coltitle'] ?></option>
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
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Remark </label>
                                            <div class="controls">
                                                <textarea name="remark" class="input-xlarge"><?php echo htmlentities($row_editdept['remark'], ENT_COMPAT, 'utf-8'); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Page Up </label>
                                            <div class="controls">
                                                <textarea name="page_up" class="input-xlarge"><?php echo htmlentities($row_editdept['page_up'], ENT_COMPAT, 'utf-8'); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Page Down </label>
                                            <div class="controls">
                                                <textarea name="page_down" class="input-xlarge"><?php echo htmlentities($row_editdept['page_down'], ENT_COMPAT, 'utf-8'); ?></textarea>
                                            </div>
                                        </div>
                                        
                                        <?php if (isset($_GET['cid'])) { ?>
                                            <input type="hidden" name="colid" value="<?php echo ($_GET['cid']) ? $_GET['cid'] : "" ?>" />
                                        <?php } ?>
                                            <input type="hidden" name="deptid" value="<?php echo $row_editdept['deptid']; ?>" />
                                            <input type="hidden" name="MM_update" value="form1" />
                                        <div class="form-actions">
                                            <input type="submit" value="Update Department" class="btn btn-primary" >
                                            <a href="<?= $_SERVER['HTTP_REFERER']?>" class="btn"> Cancel </a>
                                        </div>
                                    </form>
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

