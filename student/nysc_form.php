<?php



if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



define('MAX_FILE_SIZE', 2048 * 258);
define('UPLOAD_DIR', '../img/user/student/');

/* -----------------------------------------------*
 * 
 * Logic of the College/index.php Page 
 *
 * *------------------------------------------------
 */

$auth_users = "10";

check_auth($auth_users, $site_root);

mysql_select_db($database_tams, $tams);

fillAccomDetails($site_root, $tams);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

mysql_select_db($database_tams, $tams);

$colname_editstud = "-1";
if (isset($_SESSION['MM_Username'])) {
    $colname_editstud = $_SESSION['MM_Username'];
}else{
    
}

$clr_transSQl = sprintf("SELECT * FROM `clearance_transactions` WHERE matric_no = %s AND status = 'APPROVED' ",GetSQLValueString($colname_editstud, "text"));
$clr_transRS = mysql_query($clr_transSQl, $tams) or die(mysql_error());
$row_clr_trans = mysql_fetch_assoc($clr_transRS);
$totalRows_clr_trans = mysql_num_rows($clr_transRS);

if($totalRows_clr_trans < 1){
    header("Location: ../clearance_fee/index.php");
    exit();
}


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {

    $query_editstud = sprintf("SELECT * FROM student WHERE stdid = %s", GetSQLValueString($colname_editstud, "text"));
    $editstud = mysql_query($query_editstud, $tams) or die(mysql_error());
    $row_editstud = mysql_fetch_assoc($editstud);
    $totalRows_editstud = mysql_num_rows($editstud);

    $edit = array();
    $fields = array_keys($row_editstud);
    foreach ($_POST as $key => $fld) {
        if (in_array($key, $fields)) {
            if (trim($fld) != trim($row_editstud[$key]))
                $edit[$key] = array('old' => trim($row_editstud[$key]), 'new' => trim($fld));
        }
    }

    unset($edit['password']);

    $password = '`password`=' . GetSQLValueString($row_editstud['password'], "text") . ',';
    if (isset($_POST['password']) && ($_POST['password'] != '')) {
        $password = '`password`=' . GetSQLValueString(md5($_POST['password']), "text") . ',';
        $edit['password'] = array('old' => '', 'new' => '');
    }

    
    $updateSQL = sprintf("UPDATE student "
                        . "SET  mname = %s,  phone=%s, jambregid = %s, "
                        . "email = %s, addr = %s, sex=%s, dob = %s, "
                        . "maritalStatus = %s, sponsorname = %s, sponsorphn = %s, sponsoradrs = %s, military_personel = %s, nysc_form = 'yes' "
                        . "WHERE stdid = %s",  
                        GetSQLValueString($_POST['mname'], "text"),
                        GetSQLValueString($_POST['phone'], "text"), 
                        GetSQLValueString($_POST['jambregid'], "text"), 
                        GetSQLValueString($_POST['email'], "text"), 
                        GetSQLValueString($_POST['addr'], "text"), 
                        GetSQLValueString($_POST['sex'], "text"), 
                        GetSQLValueString($_POST['dob'], "date"),
                        GetSQLValueString($_POST['maritalStatus'], "text"), 
                        GetSQLValueString($_POST['sponsorname'], "text"),
                        GetSQLValueString($_POST['sponsorphn'], "text"),
                        GetSQLValueString($_POST['sponsoradrs'], "text"),
                        GetSQLValueString($_POST['military_personel'], "text"),
                        GetSQLValueString($colname_editstud, "text"));
    $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
    $entid = mysql_insert_id();

    $upload = "";
    

    $params['entid'] = $colname_editstud;
    $params['enttype'] = 'student';
    $params['action'] = 'edit';
    $params['cont'] = json_encode($edit);
    audit_log($params);

    $insertGoTo = "nysc_form.php";
    if (isset($_SERVER['QUERY_STRING'])) {
        $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
        $insertGoTo .= $_SERVER['QUERY_STRING'];
    }

    if ($Result1){
        
        //$insertGoTo = ( isset($_GET['success']) ) ? $insertGoTo : $insertGoTo . "?success";
        $msg = 'Operation Successfull';
        $notification->set_notification($msg, 'success');
        
    }else{
        
        //$insertGoTo = ( isset($_GET['error']) ) ? $insertGoTo : $insertGoTo . "?error";
        $msg = 'Operation Not Successfull';
        $notification->set_notification($msg, 'error');
        
    }
    header(sprintf("Location: %s", $insertGoTo));
    
    
}

$query_editstud = sprintf("SELECT * FROM student WHERE stdid = %s", GetSQLValueString($colname_editstud, "text"));
$editstud = mysql_query($query_editstud, $tams) or die(mysql_error());
$row_editstud = mysql_fetch_assoc($editstud);
$totalRows_editstud = mysql_num_rows($editstud);

$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,6";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);



$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if($row_editstud['passlist'] == 'No'){
    header('Location: profile.php');
    exit();
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
                    

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Update Student profile for NYSC Mobilization
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <?php if($row_editstud['nysc_form'] == 'no'){ ?>
                                    <div class="alert alert-info">
                                        You are required to UPDATE your profile, especially those in asterisk (*). Should you notice error in the spelling of your name, contact the Helpdesk for immediate update. <br/>
                                        Be sure that the information you provide is CORRECT as it will be used for the NYSC Mobilisation.<br/><br/>
                                    </div>
                                    <div class="row-fluid">
                                        <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data">
                                            
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Matric No </label>
                                                <div class="controls">
                                                    <input name="stdid" type="text"  class="input-large"  value="<?php echo $row_editstud['stdid']; ?>"  disabled="" readonly=""/>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Jambreg ID </label>
                                                <div class="controls">
                                                    <input name="jambregid" type="text"  class="input-large"  value="<?php echo $row_editstud['jambregid']; ?>"  required=""/>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">First Name</label>
                                                <div class="controls">
                                                    <input name="fname" readonly="" disabled="" value="<?php echo htmlentities($row_editstud['fname'], ENT_COMPAT, 'utf-8'); ?>" type="text" class="input-xlarge"  required="" />
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Last Name</label>
                                                <div class="controls">
                                                    <input name="lname"  type="text" disabled="" readonly="" value="<?php echo htmlentities($row_editstud['lname'], ENT_COMPAT, 'utf-8'); ?>" class="input-xlarge"  required="" />
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Middle Name</label>
                                                <div class="controls">
                                                    <input name="mname"  type="text" class="input-xlarge" readonly="" value="<?php echo htmlentities($row_editstud['mname'], ENT_COMPAT, 'utf-8'); ?>" required="" />
                                                </div>
                                            </div>
                                            
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Phone No</label>
                                                <div class="controls">
                                                    <input name="phone"  type="text" class="input-xlarge"  value="<?php echo htmlentities($row_editstud['phone'], ENT_COMPAT, 'utf-8'); ?>" required="" />&nbsp;*
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Email </label>
                                                <div class="controls">
                                                    <input name="email"  type="email" class="input-xlarge" value="<?php echo htmlentities($row_editstud['email'], ENT_COMPAT, 'utf-8'); ?>" required="" />&nbsp;*
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Address  </label>
                                                <div class="controls">
                                                    <textarea name="addr" class="input-xlarge" required=""><?php echo htmlentities($row_editstud['addr'], ENT_COMPAT, 'utf-8'); ?></textarea>&nbsp;*
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Sex</label>
                                                <div class="controls" class="input-xlarge">
                                                    <select name="sex">
                                                        <option value="M" <?php if (!(strcmp("M", htmlentities($row_editstud['sex'], ENT_COMPAT, 'utf-8')))) {
    echo "SELECTED";
} ?>>Male</option>
                                                        <option value="F" <?php if (!(strcmp("F", htmlentities($row_editstud['sex'], ENT_COMPAT, 'utf-8')))) {
    echo "SELECTED";
} ?>>Female</option>
                                                    </select>&nbsp;*
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Marital Status </label>
                                                <div class="controls">
                                                     <select name="maritalStatus">
                                                        <option value="Single" <?= ('Single' == $row_editstud['maritalStatus']) ? 'selected': ''?>>Single</option>
                                                        <option value="Married" <?= ('Married' == $row_editstud['maritalStatus']) ? 'selected': ''?>>Married</option>
                                                    </select>&nbsp;*
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Are you a Military Personel or Member of any Law Enforcement agency? </label>
                                                <div class="controls">
                                                     <select name="military_personel" >
                                                        <option value="Yes" <?= ('Yes' == $row_editstud['military_personel']) ? 'selected': ''?>>Yes</option>
                                                        <option value="No" <?= ('No' == $row_editstud['military_personel']) ? 'selected': ''?>>No</option>
                                                    </select>&nbsp;*
                                                </div>
                                            </div>
                                            
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Date of Birth </label>
                                                <div class="controls">
                                                    <input name="dob"  type="date" class="input-xlarge datepick"  value="<?php echo htmlentities($row_editstud['dob'], ENT_COMPAT, 'utf-8'); ?>" required="" />&nbsp;*
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Parent/Guardian Name </label>
                                                <div class="controls">
                                                    <input name="sponsorname"  type="text" class="input-xlarge"  value="<?php echo htmlentities($row_editstud['sponsorname'], ENT_COMPAT, 'utf-8'); ?>" required="" />&nbsp;*
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Parent/Guardian Phone </label>
                                                <div class="controls">
                                                    <input name="sponsorphn"  type="text" class="input-xlarge "  value="<?php echo htmlentities($row_editstud['sponsorphn'], ENT_COMPAT, 'utf-8'); ?>" required="" />&nbsp;*
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Parent/Guardian Address </label>
                                                <div class="controls">
                                                    <textarea name="sponsoradrs"  class="input-xlarge" ><?php echo htmlentities($row_editstud['sponsoradrs'], ENT_COMPAT, 'utf-8'); ?></textarea>&nbsp;*
                                                </div>
                                            </div>
                                            
                                            <input type="hidden" name="MM_update" value="form1" />
                                            <div class="form-actions">
                                                <input type="submit" value="Update Student Profile" class="btn btn-primary" >
                                                <a class="btn" href="<?= (isset($_SERVER['HTTP_REFERER']))?$_SERVER['HTTP_REFERER']:'#' ?>">Cancel</a>
                                            </div>
                                        </form>
                                    </div>
                                    <?php }else{?>
                                        <div class="alert alert-success center">You have successfully completed your NYSC Mobilization From Click the Print From Button below to print out your form<br/><br/><a href="nysc_mob_form.php" target="tabs" class="btn btn-primary">Print Form</a></div>
                                    <?php }?>
                                </div>
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

