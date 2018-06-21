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
    $updateSQL = sprintf("UPDATE `session` "
            . "SET sesname=%s, tnumin=%s, tnumax=%s "
            . "WHERE sesid=%s", 
            GetSQLValueString($_POST['sesname'], "text"), 
            GetSQLValueString($_POST['tnumin'], "int"), 
            GetSQLValueString($_POST['tnumax'], "int"), 
            GetSQLValueString($_POST['sesid'], "int"));

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

$colname_editses = "-1";
if (isset($_GET['sid'])) {
    $colname_editses = $_GET['sid'];
}
mysql_select_db($database_tams, $tams);
$query_editses = sprintf("SELECT * FROM `session` WHERE sesid = %s", GetSQLValueString($colname_editses, "int"));
$editses = mysql_query($query_editses, $tams) or die(mysql_error());
$row_editses = mysql_fetch_assoc($editses);
$totalRows_editses = mysql_num_rows($editses);





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
                    <div class="span6">
                        <?php statusMsg(); ?>
                    </div>

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-calendar"></i>
                                        Edit Session <?php echo $row_editses['sesname']; ?>
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>s" method="post">
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Session Name </label>
                                            <div class="controls">
                                                <input name="sesname" type="text"  class="input-xlarge" required="" value="<?= $row_editses['sesname']?>"/>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Mininum Unit</label>
                                            <div class="controls">
                                                <input name="tnumin" type="number" class="input-medium"  min="0"  max="500" required="" value="<?= $row_editses['tnumin']?>" />
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Maximum Unit</label>
                                            <div class="controls">
                                                <input name="tnumax" type="number" class="input-medium"  min="0"  max="500" required="" value="<?= $row_editses['tnumax']?>" />
                                            </div>
                                        </div>
                                        <input type="hidden" name="sesid" value="<?php echo $row_editses['sesid']; ?>" />
                                        <input type="hidden" name="MM_update" value="form1" />
                                        <div class="form-actions">
                                            <input type="submit" value="Update Session" class="btn btn-primary" >
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

