<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



/*-----------------------------------------------*
 * 
 * Logic of the College/coledit.php Page 
 *
 **------------------------------------------------
 */


$MM_authorizedUsers = "1,2,3";
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
    $updateSQL = sprintf("UPDATE college "
                        . "SET colname = %s, page_up = %s, page_down = %s "
                        . "WHERE colid=%s",
                        GetSQLValueString($_POST['colname'], "text"),
                        GetSQLValueString($_POST['page_up'], "text"),
                        GetSQLValueString($_POST['page_down'], "text"),
                        GetSQLValueString($_POST['colid'], "int"));
    mysql_select_db($database_tams, $tams);
    $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());

    
    $updateGoTo = "college.php";
    if (isset($_SERVER['QUERY_STRING'])) {
        $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
        $updateGoTo .= $_SERVER['QUERY_STRING'];
    }
    header(sprintf("Location: %s", $updateGoTo));
}

$colname_editcol = "-1";
if (isset($_GET['cid'])) {
    $colname_editcol = $_GET['cid'];
}

mysql_select_db($database_tams, $tams);
$query_editcol = sprintf("SELECT * FROM college WHERE colid = %s", GetSQLValueString($colname_editcol, "int"));
$editcol = mysql_query($query_editcol, $tams) or die(mysql_error());
$row_editcol = mysql_fetch_assoc($editcol);
$totalRows_editcol = mysql_num_rows($editcol);



if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
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
                                        Edit <?php echo $row_editcol['colname']?>
                                    </h3>
                                </div>
                                <div class="box-content nopadding">
                                    <form class="form-horizontal form-bordered" method="POST" action="#">
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">College Name</label>
                                            <div class="controls">
                                                <input type="text" name="colname" class="input-xxlarge" value="<?php echo htmlentities($row_editcol['colname'], ENT_COMPAT, 'utf-8'); ?>" size="50" />
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Top Content</label>
                                            <div class="controls">
                                                <div class="input-append">
                                                    <textarea name="page_up" cols="60" rows="5"  class="input-xxlarge"><?php echo htmlentities($row_editcol['page_up'], ENT_COMPAT, 'utf-8'); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Bottom Content</label>
                                            <div class="controls">
                                                <div class="input-append">
                                                    <textarea name="page_down" cols="60" rows="5"  class="input-xxlarge"><?php echo htmlentities($row_editcol['page_down'], ENT_COMPAT, 'utf-8'); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="colid" value="<?php echo $row_editcol['colid']; ?>" />
                                        <input type="hidden" name="MM_update" value="form1" />
                                        <div class="form-actions">
                                            <button class="btn btn-primary" type="submit">Save changes</button>
                                            <a href="<?= $_SERVER['HTTP_REFERER']?>"  class="btn" > Cancel</a>
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

