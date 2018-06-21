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
    $updateSQL = sprintf("UPDATE programme "
                        . "SET progname = %s, progcode = %s, deptid = %s, "
                        . "remark = %s, duration =%s, continued = %s, "
                        . "page_up = %s, page_down = %s  "
                        . "WHERE deptid = %s",
                        GetSQLValueString($_POST['progname'], "text"),
                        GetSQLValueString($_POST['progcode'], "text"),
                        GetSQLValueString($_POST['deptid'], "int"),
                        GetSQLValueString($_POST['remark'], "text"),
                        GetSQLValueString($_POST['duration'], "int"),
                        GetSQLValueString($_POST['continued'], "text"),
                        GetSQLValueString($_POST['page_up'], "text"),
                        GetSQLValueString($_POST['page_down'], "text"),
                        GetSQLValueString($_POST['progid'], "int"));
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

$colname_editprog = "-1";
if (isset($_GET['pid'])) {
    $colname_editprog = $_GET['pid'];
}

mysql_select_db($database_tams, $tams);
$query_editprog = sprintf("SELECT * FROM programme "
                        . "WHERE progid = %s", 
                        GetSQLValueString($colname_editprog, "int"));
$editprog = mysql_query($query_editprog, $tams) or die(mysql_error());
$row_editprog = mysql_fetch_assoc($editprog);
$totalRows_editprog = mysql_num_rows($editprog);

$maxRows_coldept = 10;
$pageNum_coldept = 0;
if (isset($_GET['pageNum_coldept'])) {
    $pageNum_coldept = $_GET['pageNum_coldept'];
}
$startRow_coldept = $pageNum_coldept * $maxRows_coldept;

mysql_select_db($database_tams, $tams);
$query_dept = "SELECT deptid, deptname FROM department";
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);
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
                                       Update Programme
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post">
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Programme Name </label>
                                            <div class="controls">
                                                <input name="progname" type="text"  class="input-xxlarge" required="" value="<?= $row_editprog['progname']?>"/>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Programme Code</label>
                                            <div class="controls">
                                                <input name="progcode" type="text" class="input-mini" required="" value="<?= $row_editprog['progcode']?>"/>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Duration</label>
                                            <div class="controls">
                                                <input name="duration" type="number" class="input-mini" max="10" min="1" required="" value="<?= $row_editprog['duration']?>" />
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Continued Programme</label>
                                            <div class="controls">
                                                <select name="continued" class="input-medium" required="">
                                                    <option value="">-- Choose --</option>
                                                    <option value="yes" <?= ($row_editprog['continued'] == 'Yes')? "selected" : "" ?>>Yes</option>
                                                    <option value="No" <?= ($row_editprog['continued'] == 'No')? "selected" : "" ?>>No</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Department </label>
                                            <div class="controls">
                                                <select name="deptid" class="choosen-select" required="">
                                                    <?php do { ?>
                                                        <option value="<?= $row_dept['deptid'] ?>" <?=($row_editprog['deptid'] == $row_dept['deptid'] ) ? "selected" : "" ?>><?= $row_dept['deptname'] ?></option>
                                                    <?php }
                                                    while ($row_dept = mysql_fetch_assoc($dept)) ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Remark </label>
                                            <div class="controls">
                                                <textarea name="remark" class="input-xlarge"><?= $row_editprog['remark']?></textarea>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Page Up </label>
                                            <div class="controls">
                                                <textarea name="page_up" class="input-xlarge"><?= $row_editprog['page_up']?></textarea>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Page Down </label>
                                            <div class="controls">
                                                <textarea name="page_down" class="input-xlarge"><?= $row_editprog['page_down']?></textarea>
                                            </div>
                                        </div>
                                        <input type="hidden" name="MM_update" value="form1" />
                                        <input type="hidden" name="progid" value="<?= $row_editprog['progid']?>" />
                                        <div class="form-actions">
                                            <input type="submit" value="Update Programme" class="btn btn-primary" >
                                            <button class="btn" type="button">Cancel</button>
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

