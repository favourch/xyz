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
    $updateSQL = sprintf("UPDATE payschedule "
                        . "SET level=%s, entrymode=%s, amount=%s,"
                        . "penalty= %s, status = %s, payhead = %s, revhead = %s"
                        . "WHERE scheduleid = %s",
                        GetSQLValueString($_POST['level'], "text"),
                        GetSQLValueString($_POST['entrymode'], "text"),
                        GetSQLValueString($_POST['amount'], "text"),
                        GetSQLValueString($_POST['penalty'], "text"),
                        GetSQLValueString($_POST['status'], "text"),
                        GetSQLValueString($_POST['payhead'], "text"),
                        GetSQLValueString($_POST['revhead'], "text"),
                        GetSQLValueString($_POST['edit_id'], "text"));

    mysql_select_db($database_tams, $tams);
    $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());

    $updateGoTo = "college.php";
    if ($Result1){
        $msg = "Opretaion successful";
        $notification->set_notification($msg, 'success');
    }else{
        $msg = "Opretaion NOT successful";
        $notification->set_notification($msg, 'error');
    }
        

    
}

$colname_editcol = "-1";
if (isset($_GET['id'])) {
    $colname_editpysc = $_GET['id'];
}
mysql_select_db($database_tams, $tams);
$query_editschdl = sprintf("SELECT * "
                        . "FROM payschedule "
                        . "WHERE scheduleid = %s",
                        GetSQLValueString($colname_editpysc, "int"));
$editschdl = mysql_query($query_editschdl, $tams) or die(mysql_error());
$row_editschdl = mysql_fetch_assoc($editschdl);
$totalRows_editschdl = mysql_num_rows($editschdl);

$query_admtype = sprintf("SELECT * FROM admission_type");
$admtype = mysql_query($query_admtype, $tams) or die(mysql_error());
$row_admtype = mysql_fetch_assoc($admtype);


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
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Edit Payment Schedule
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post">
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Level </label>
                                            <div class="controls">
                                                <select name="level">
                                                    <option value="">-- Choose -- </option>
                                                    <option value="0" <?= ($row_editschdl['level'] == '0')? 'selected':''?>>Prospective</option>
                                                    <option value="1" <?= ($row_editschdl['level'] == '1')? 'selected':''?>>100</option>
                                                    <option value="2" <?= ($row_editschdl['level'] == '2')? 'selected':''?>>200</option>
                                                    <option value="3" <?= ($row_editschdl['level'] == '3')? 'selected':''?>>300</option>
                                                    <option value="4" <?= ($row_editschdl['level'] == '4')? 'selected':''?>>400</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Entry Mode</label>
                                            <div class="controls">
                                                <select name="entrymode" class="input-small">
                                                    <option value="">-- Choose --</option>
                                                    <?php do{?>
                                                    <option value="<?= $row_admtype['typeid']?>" <?= ($row_editschdl['entrymode'] == $row_admtype['typeid'])? 'selected':''?>><?= $row_admtype['typename']?></option>
                                                    <?php }while($row_admtype = mysql_fetch_assoc($admtype))?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Amount (NGN)</label>
                                            <div class="controls">
                                                <input type="text" name="amount" class="input-large" required=""  value="<?= $row_editschdl['amount']?>"/>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Penalty (NGN)</label>
                                            <div class="controls">
                                                <input type="text" name="penalty" class="input-large" required="" value="<?= $row_editschdl['penalty']?>"/>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Payment Head</label>
                                            <div class="controls">
                                                <select name="payhead" class="input-large">
                                                    <option value="">-- Choose --</option>
                                                    <option value="app" <?= ($row_editschdl['payhead'] == "app") ? 'selected':''?>>Application Fee</option>
                                                    <option value="acc" <?= ($row_editschdl['payhead'] == "acc") ? 'selected':''?>>Acceptance Fee</option>
                                                    <option value="sch" <?= ($row_editschdl['payhead'] == "sch") ? 'selected':''?>>School Fee</option>
                                                    <option value="dpt" <?= ($row_editschdl['payhead'] == "dpt") ? 'selected':''?>>Departmental Fee</option>
                                                    <option value="jou" <?= ($row_editschdl['payhead'] == "jou") ? 'selected':''?>>Journal Fee</option>
                                                    <option value="ins" <?= ($row_editschdl['payhead'] == "ins") ? 'selected':''?>>Insurance Fee</option>
                                                    <option value="dmg" <?= ($row_editschdl['payhead'] == "dmg") ? 'selected':''?>>Damages Fee</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Status</label>
                                            <div class="controls">
                                                <select name="status" class="input-small">
                                                    <option value="">-- Choose --</option>
                                                    <option value="indigene" <?= ($row_editschdl['status'] == "indigene")? 'selected':''?>>Indigene</option>
                                                    <option value="nonindigene" <?= ($row_editschdl['status'] == "nonindigene")? 'selected':''?>>Non-Indigene</option>
                                                    <option value="coi" <?= ($row_editschdl['status'] == "coi")? 'selected':''?>>COI Applicant</option>
                                                    <option value="regular" <?= ($row_editschdl['status'] == "regular")? 'selected':''?>>Regular Applicant</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Revenue Head</label>
                                            <div class="controls">
                                                <input type="text" name="revhead" class="input-large" required="" value="<?= $row_editschdl['revhead']?>"/>
                                            </div>
                                        </div>
                                        <input type="hidden" name="edit_id" value="<?= $row_editschdl['scheduleid']; ?>" />
                                        <input type="hidden" name="MM_update" value="form1" />
                                        <div class="form-actions">
                                            <input type="submit" value="Update Pay. Schedule" class="btn btn-primary" >
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

