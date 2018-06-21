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

$MM_authorizedUsers = "20,28";
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

define ('MAX_FILE_SIZE', 1024 * 128);
define('UPLOAD_DIR', '../../images/staff/');

$colname_editprof = "-1";
if (isset($_GET['lid'])) {
  $colname_editprof = $_GET['lid'];
}

mysql_select_db($database_tams, $tams);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {

    $query_editprof = sprintf("SELECT * FROM lecturer WHERE lectid = %s", GetSQLValueString($colname_editprof, "text"));
    $editprof = mysql_query($query_editprof, $tams) or die(mysql_error());
    $row_editprof = mysql_fetch_assoc($editprof);
    $totalRows_editprof = mysql_num_rows($editprof);

    $edit = array();
    $fields = array_keys($row_editprof);
    foreach ($_POST as $key => $fld) {
        if (in_array($key, $fields)) {
            if (trim($fld) != trim($row_editprof[$key]))
                $edit[$key] = array('old' => trim($row_editprof[$key]), 'new' => trim($fld));
        }
    }

    unset($edit['password']);

    $password = '';
    if (isset($_POST['password']) && $_POST['password'] != '') {
        $password = 'password=' . GetSQLValueString(md5($_POST['password']), "text") . ',';
        $edit['password'] = array('old' => '', 'new' => $_POST['password']);
    }
    
    if (isset($_POST['email']) && $_POST['email'] ){
        $email = 'email=' . GetSQLValueString($_POST['email'], "text") . ',';
        $edit['email'] = array('old' => $row_editprof['email'], 'new' => $_POST['email']);
    }
    

    $updateSQL = sprintf("UPDATE lecturer "
                . "SET title = %s, fname = %s, lname = %s, mname = %s, "
                . "phone = %s, deptid = %s, addr = %s, sex = %s, "
                . "%s %s profile=%s "
                . "WHERE lectid=%s", 
                GetSQLValueString($_POST['title'], "text"), 
                GetSQLValueString($_POST['fname'], "text"), 
                GetSQLValueString($_POST['lname'], "text"), 
                GetSQLValueString($_POST['mname'], "text"),
                GetSQLValueString($_POST['phone'], "text"), 
                GetSQLValueString($_POST['deptid'], "int"), 
                GetSQLValueString($_POST['email'], "text"), 
                GetSQLValueString($_POST['addr'], "text"), 
                GetSQLValueString($_POST['sex'], "text"), 
                $password, 
                $email,
                GetSQLValueString($_POST['profile'], "text"), 
                GetSQLValueString($colname_editprof, "text"));

    mysql_select_db($database_tams, $tams);
    $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
    
    $upload = "";

    if ($Result1 && isset($_FILES['filename']) &&  $_FILES['filename']['name'] != '') {
        $upload = uploadFile(UPLOAD_DIR, "staff", MAX_FILE_SIZE);
    }
    
    
    
    $params['entid'] = $colname_editprof;
    $params['enttype'] = 'lecturer';
    $params['action'] = 'edit';
    $params['cont'] = json_encode($edit);
    audit_log($params);

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

$query_editprof = sprintf("SELECT * FROM lecturer WHERE lectid = %s", GetSQLValueString($colname_editprof, "text"));
$editprof = mysql_query($query_editprof, $tams) or die(mysql_error());
$row_editprof = mysql_fetch_assoc($editprof);
$totalRows_editprof = mysql_num_rows($editprof);


mysql_select_db($database_tams, $tams);
$query_dept = "SELECT deptid, deptname FROM department";
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
                        <?php statusMsg(); ?>
                    </div>

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Edit Profile of <?php echo $row_editprof['lname'] . ", " . substr($row_editprof['fname'], 0, 1) ?>
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data">
                                        <div class="controls-group">
                                            <label class="control-label" for="textfield">Image</label>
                                            <div data-provides="fileupload" class="fileupload fileupload-new controls">
                                                <div style="width: 200px; height: 150px;" class="fileupload-new thumbnail"><img style="width: 200px; height: 150px;" src="<?= get_pics($colname_editprof, '../../images/staff')?>"></div>
                                                <div style="max-width: 200px; max-height: 150px; line-height: 20px;" class="fileupload-preview fileupload-exists thumbnail"></div>
                                                <div>
                                                    <span class="btn btn-file"><span class="fileupload-new">Select image</span><span class="fileupload-exists">Change</span><input type="file" name="filename"></span>
                                                    <a data-dismiss="fileupload" class="btn fileupload-exists" href="#">Remove</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Title</label>
                                            <div class="controls">
                                                <select name="title">
                                                    <option value="Prof" <?php if (!(strcmp("Prof", htmlentities($row_editprof['title'], ENT_COMPAT, 'utf-8')))) {
                                                        echo "selected=\"selected\"";
                                                    } ?>>Prof.</option>
                                                    <option value="Dr" <?php if (!(strcmp("Dr", htmlentities($row_editprof['title'], ENT_COMPAT, 'utf-8')))) {
                                                        echo "selected=\"selected\"";
                                                    } ?>>Dr.</option>
                                                    <option value="Mr" <?php if (!(strcmp("Mr", htmlentities($row_editprof['title'], ENT_COMPAT, 'utf-8')))) {
                                                            echo "selected=\"selected\"";
                                                        } ?>>Mr.</option>
                                                    <option value="Mrs" <?php if (!(strcmp("Mrs", htmlentities($row_editprof['title'], ENT_COMPAT, 'utf-8')))) {
                                                            echo "selected=\"selected\"";
                                                        } ?>>Mrs.</option>
                                                    <option value="Miss" <?php if (!(strcmp("Miss", htmlentities($row_editprof['title'], ENT_COMPAT, 'utf-8')))) {
                                                        echo "selected=\"selected\"";
                                                    } ?>>Miss</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">First Name</label>
                                            <div class="controls">
                                                <input type="text" name="fname" class="input-large" value="<?php echo htmlentities($row_editprof['fname'], ENT_COMPAT, 'utf-8'); ?>" size="32" />
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Last Name</label>
                                            <div class="controls">
                                                <input type="text" name="lname" class="input-large" value="<?php echo htmlentities($row_editprof['lname'], ENT_COMPAT, 'utf-8'); ?>" size="32" />
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Middle Name</label>
                                            <div class="controls">
                                                <input type="text" name="mname" class="input-large" value="<?php echo htmlentities($row_editprof['mname'], ENT_COMPAT, 'utf-8'); ?>" size="32" />
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Department</label>
                                            <div class="controls">
                                                <select name="deptid" id="deptid">
                                                <?php
                                                    do {
                                                        ?>
                                                        <option value="<?php echo $row_dept['deptid'] ?>"<?php if (!(strcmp($row_dept['deptid'], htmlentities($row_editprof['deptid'], ENT_COMPAT, 'utf-8')))) {
                                                        echo "selected=\"selected\"";
                                                    } ?>><?php echo $row_dept['deptname'] ?></option>
                                                        <?php
                                                    }
                                                    while ($row_dept = mysql_fetch_assoc($dept));
                                                    $rows = mysql_num_rows($dept);
                                                    if ($rows > 0) {
                                                        mysql_data_seek($dept, 0);
                                                        $row_dept = mysql_fetch_assoc($dept);
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Phone No.</label>
                                            <div class="controls">
                                                <input type="text" class="input-large" name="phone" value="<?php echo htmlentities($row_editprof['phone'], ENT_COMPAT, 'utf-8'); ?>" />
                                            </div>
                                        </div>
                                        <?php if(getAccess() == 20){?>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">E-mail.</label>
                                            <div class="controls">
                                                <input type="text" class="input-large" name="email" value="<?php echo htmlentities($row_editprof['email'], ENT_COMPAT, 'utf-8'); ?>" />
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Password.</label>
                                            <div class="controls">
                                                <input type="text" class="input-large" name="password"  />
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Address</label>
                                            <div class="controls">
                                                <textarea name="addr" class="input-xlarge"><?php echo htmlentities($row_editprof['addr'], ENT_COMPAT, 'utf-8'); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Sex </label>
                                            <div class="controls">
                                                <select name="sex">
                                                    <option value="M" <?php if (!(strcmp("M", htmlentities($row_editprof['sex'], ENT_COMPAT, 'utf-8')))) {
                                                                                echo "SELECTED";
                                                                            } ?>>Male</option>
                                                    <option value="F" <?php if (!(strcmp("F", htmlentities($row_editprof['sex'], ENT_COMPAT, 'utf-8')))) {
                                                        echo "SELECTED";
                                                    } ?>>Female</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Research Area </label>
                                            <div class="controls">
                                                <textarea name="profile" class="input-xlarge"><?= htmlentities($row_editprof['profile'], ENT_COMPAT, 'utf-8'); ?></textarea>
                                            </div>
                                        </div>
                                        <input type="hidden" name="MM_update" value="form1" />
                                        <input type="hidden" name="lectid" value="<?php echo $row_editprof['lectid']; ?>" />
                                        <div class="form-actions">
                                            <input type="submit" value="Update Staff" class="btn btn-primary" >
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

