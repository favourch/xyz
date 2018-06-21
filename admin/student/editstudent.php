<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "1,20, 28";
check_auth($auth_users, $site_root);

define ('MAX_FILE_SIZE', 2048 * 258);
define('UPLOAD_DIR', '../../img/user/student/');

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$colname_editstud = "-1";
if (isset($_GET['stid'])) {
    $colname_editstud = $_GET['stid'];
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
//    var_dump($edit);
//    exit;
    $updateSQL = sprintf("UPDATE student "
            . "SET stdid= %s, jambregid=%s, fname=%s, lname=%s, mname=%s,"
            . " progid=%s, phone=%s, email=%s, addr=%s,"
            . " sex=%s, dob=%s, sesid=%s, `level`=%s, "
            . "stid=%s, admid=%s, %s status=%s, "
            . "`access`=%s, credit=%s, profile=%s, curid = %s "
            . " WHERE stdid=%s", 
            GetSQLValueString($_POST['stdid'], "text"),
            GetSQLValueString($_POST['jambregid'], "text"),
            GetSQLValueString($_POST['fname'], "text"), 
            GetSQLValueString($_POST['lname'], "text"), 
            GetSQLValueString($_POST['mname'], "text"), 
            GetSQLValueString($_POST['progid'], "int"), 
            GetSQLValueString($_POST['phone'], "text"), 
            GetSQLValueString($_POST['email'], "text"), 
            GetSQLValueString($_POST['addr'], "text"), 
            GetSQLValueString($_POST['sex'], "text"), 
            GetSQLValueString($_POST['dob'], "date"), 
            GetSQLValueString($_POST['sesid'], "int"), 
            GetSQLValueString($_POST['levelid'], "int"), 
            GetSQLValueString($_POST['stid'], "int"),
            GetSQLValueString($_POST['typeid'], "int"), 
            GetSQLValueString($password, "defined", $password), 
            GetSQLValueString($_POST['status'], "text"), 
            GetSQLValueString($_POST['access'], "int"), 
            GetSQLValueString($_POST['credit'], "int"), 
            GetSQLValueString($_POST['profile'], "text"), 
            GetSQLValueString($_POST['curid'], "int"), 
            GetSQLValueString($colname_editstud, "text"));

    $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
    $entid = mysql_insert_id();

    $upload = "";
    if ($Result1 && isset($_FILES['filename']) &&  $_FILES['filename']['name'] != '') {
        
        $upload = uploadFile(UPLOAD_DIR, "student", MAX_FILE_SIZE);
    }

    $params['entid'] = $colname_editstud;
    $params['enttype'] = 'student';
    $params['action'] = 'edit';
    $params['cont'] = json_encode($edit);
    audit_log($params);

    $insertGoTo = "index.php";
    if (isset($_SERVER['QUERY_STRING'])) {
        $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
        $insertGoTo .= $_SERVER['QUERY_STRING'];
    }
    
     if ($Result1)
       
        $notification->set_notification('Operation Successful', 'success');
    else
       
    $notification->set_notification('Operation NOT Successful', 'error');
    
    //header(sprintf("Location: %s", $insertGoTo));
}

$query_editstud = sprintf("SELECT * FROM student WHERE stdid = %s", GetSQLValueString($colname_editstud, "text"));
$editstud = mysql_query($query_editstud, $tams) or die(mysql_error());
$row_editstud = mysql_fetch_assoc($editstud);
$totalRows_editstud = mysql_num_rows($editstud);

$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,7";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$query_prog = "SELECT progid, progname FROM programme";
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);


//Get list of all curriculum 
$query_cur = "SELECT * FROM curriculum";
$cur = mysql_query($query_cur, $tams) or die(mysql_error());
$row_cur = mysql_fetch_assoc($cur);


$query_state = "SELECT * FROM `state` ";
$state = mysql_query($query_state, $tams) or die(mysql_error());
$row_state = mysql_fetch_assoc($state);
$totalRows_state = mysql_num_rows($state);

$query_admType = "SELECT typeid, displayname FROM admission_type";
$admType = mysql_query($query_admType, $tams) or die(mysql_error());
$row_admType = mysql_fetch_assoc($admType);
$totalRows_admType = mysql_num_rows($admType);

$query_level = "SELECT * FROM level_name";
$level = mysql_query($query_level, $tams) or die(mysql_error());
$row_level = mysql_fetch_assoc($level);
$totalRows_level = mysql_num_rows($level);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$deptname = "";
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
                                    <h3><i class="icon-reorder"></i>
                                        Update Student profile
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data">
                                            <div class="controls-group">
                                                <label class="control-label" for="textfield">Passport</label>
                                                <div data-provides="fileupload" class="fileupload fileupload-new controls">
                                                    <div style="width: 200px; height: 150px;" class="fileupload-new thumbnail"><img style="width: 200px; height: 150px;" src="<?= get_pics($colname_editstud, '../../img/user/student') ?>"></div>
                                                    <div style="max-width: 200px; max-height: 150px; line-height: 20px;" class="fileupload-preview fileupload-exists thumbnail"></div>
                                                    <div>
                                                        <span class="btn btn-file"><span class="fileupload-new">Select image</span><span class="fileupload-exists">Change</span><input type="file" name="filename"></span>
                                                        <a data-dismiss="fileupload" class="btn fileupload-exists" href="#">Remove</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Matric No </label>
                                                <div class="controls">
                                                    <input name="stdid" type="text"  class="input-large"  value="<?php echo $row_editstud['stdid']; ?>" 
                                                                                                <?php echo (getAccess() > 20)? "readonly=\"readonly\"": "" ?> required=""/>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">UTME No </label>
                                                <div class="controls">
                                                    <input name="jambregid" type="text"  class="input-large"  value="<?php echo $row_editstud['jambregid']; ?>" 
                                                                                                <?php echo (getAccess() > 20)? "readonly=\"readonly\"": "" ?> required=""/>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">First Name</label>
                                                <div class="controls">
                                                    <input name="fname"  value="<?php echo htmlentities($row_editstud['fname'], ENT_COMPAT, 'utf-8'); ?>" type="text" class="input-xlarge"  required="" />
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Last Name</label>
                                                <div class="controls">
                                                    <input name="lname"  type="text" value="<?php echo htmlentities($row_editstud['lname'], ENT_COMPAT, 'utf-8'); ?>" class="input-xlarge"  required="" />
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Middle Name</label>
                                                <div class="controls">
                                                    <input name="mname"  type="text" class="input-xlarge"  value="<?php echo htmlentities($row_editstud['mname'], ENT_COMPAT, 'utf-8'); ?>" required="" />
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Programme</label>
                                                <div class="controls">
                                                    <select name="progid" required="">
                                                        <?php
                                                        do {
                                                            ?>
                                                            <option value="<?php echo $row_prog['progid'] ?>" <?php if (!(strcmp($row_prog['progid'], htmlentities($row_editstud['progid'], ENT_COMPAT, 'utf-8')))) {
                                                            echo "SELECTED";
                                                        } ?>><?php echo $row_prog['progname'] ?></option>
                                                            <?php
                                                        }
                                                        while ($row_prog = mysql_fetch_assoc($prog));
                                                        $rows = mysql_num_rows($prog);
                                                        if ($rows > 0) {
                                                            mysql_data_seek($prog, 0);
                                                            $row_prog = mysql_fetch_assoc($prog);
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Phone No</label>
                                                <div class="controls">
                                                    <input name="phone"  type="text" class="input-xlarge"  value="<?php echo htmlentities($row_editstud['phone'], ENT_COMPAT, 'utf-8'); ?>" required="" />
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Email </label>
                                                <div class="controls">
                                                    <input name="email"  type="email" class="input-xlarge" value="<?php echo htmlentities($row_editstud['email'], ENT_COMPAT, 'utf-8'); ?>" required="" />
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Address  </label>
                                                <div class="controls">
                                                    <textarea name="addr" class="input-xlarge" required="">
                                                        <?php echo htmlentities($row_editstud['addr'], ENT_COMPAT, 'utf-8'); ?>
                                                    </textarea>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Password </label>
                                                <div class="controls">
                                                    <input name="password"  type="text" class="input-xlarge" />
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Sex</label>
                                                <div class="controls" class="input-xlarge">
                                                    <select name="sex">
                                                        <option value="M" <?php if (!(strcmp("M", htmlentities($row_editstud['sex'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Male</option>
                                                        <option value="F" <?php if (!(strcmp("F", htmlentities($row_editstud['sex'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Female</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Date of Birth </label>
                                                <div class="controls">
                                                    <input name="dob"  type="date" class="input-xlarge"  value="<?php echo htmlentities($row_editstud['dob'], ENT_COMPAT, 'utf-8'); ?>" required="" />
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Session</label>
                                                <div class="controls" class="input-xlarge" required="">
                                                    <select name="sesid" required="">
                <?php
                                                                    do {  
                                                                    ?>
                                                    <option value="<?php echo $row_sess['sesid']?>" <?php if (!(strcmp($row_sess['sesid'], htmlentities($row_editstud['sesid'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $row_sess['sesname']?></option>
                                                    <?php
                                                                    } while ($row_sess = mysql_fetch_assoc($sess));
                                                                      $rows = mysql_num_rows($sess);
                                                                      if($rows > 0) {
                                                                              mysql_data_seek($sess, 0);
                                                                              $row_sess = mysql_fetch_assoc($sess);
                                                                      }
                                                                    ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Level</label>
                                                <div class="controls" class="input-xlarge">
                                                    <select name="levelid" required="">
                                                         <?php
                                                               do {  
                                                                    ?>
                                                            
                                                    <option value="<?php echo $row_level['levelid']?>" <?php if(!(strcmp($row_level['levelid'], htmlentities($row_editstud['level'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $row_level['levelname']?></option>
                                                    <?php
                                                                    } while ($row_level = mysql_fetch_assoc($level));
                                                                      $rows = mysql_num_rows($level);
                                                                      if($rows > 0) {
                                                                              mysql_data_seek($level, 0);
                                                                              $row_level = mysql_fetch_assoc($level);
                                                                      }
                                                                    ?>
                                                    </select>


                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">State of Origin</label>
                                                <div class="controls" class="input-xlarge">
                                                    <select name="stid" required="">
                                                         <?php
                                                               do {  
                                                                    ?>
                                                            
                                                    <option value="<?php echo $row_state['stid']?>" <?php if(!(strcmp($row_state['stid'], htmlentities($row_editstud['stid'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $row_state['stname']?></option>
                                                    <?php
                                                                    } while ($row_state = mysql_fetch_assoc($state));
                                                                      $rows = mysql_num_rows($state);
                                                                      if($rows > 0) {
                                                                              mysql_data_seek($state, 0);
                                                                              $row_state = mysql_fetch_assoc($state);
                                                                      }
                                                                    ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Admission Mode</label>
                                                <div class="controls" class="input-xlarge">
                                                    <select name="typeid" required="">
                                                         <?php
                                                               do {  
                                                                    ?>
                                                            
                                                    <option value="<?php echo $row_admType['typeid']?>" <?php if(!(strcmp($row_admType['typeid'], htmlentities($row_editstud['admid'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $row_admType['displayname']?></option>
                                                    <?php
                                                                    } while ($row_admType = mysql_fetch_assoc($admType));
                                                                      $rows = mysql_num_rows($admType);
                                                                      if($rows > 0) {
                                                                              mysql_data_seek($admType, 0);
                                                                              $row_admType = mysql_fetch_assoc($admType);
                                                                      }
                                                                    ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Profile</label>
                                                <div class="controls" class="input-xlarge">
                                                    <textarea name="profile" class="input-xlarge">
                                                        <?php echo htmlentities($row_editstud['profile'], ENT_COMPAT, 'utf-8'); ?>
                                                    </textarea>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Status</label>
                                                <div class="controls" class="input-xlarge">
                                                    <select name="status" required="">
                                                        <option value="Undergrad" <?php if (!(strcmp("Undergrad", htmlentities($row_editstud['status'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Undergraduate</option>
                                                         <option value="Graduating" <?php if (!(strcmp("Graduating", htmlentities($row_editstud['status'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Graduating</option>
                                                        <option value="Graduate" <?php if (!(strcmp("Graduate", htmlentities($row_editstud['status'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Graduate</option>
                                                        
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Curriculum</label>
                                                <div class="controls" class="input-xlarge">
                                                    <select name="curid" required="">
                                                        <?php do{ ?>
                                                        <option value="<?= $row_cur['curid']?>"  <?= ($row_editstud['curid'] == $row_cur['curid'] ) ? 'selected': ''?> ><?= $row_cur['curname']?></option>
                                                        <?php }while($row_cur = mysql_fetch_assoc($cur))?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Credit</label>
                                                <div class="controls" class="input-xlarge">
                                                    <input type="number" class="input-large" name="credit" value="<?php echo htmlentities($row_editstud['credit'], ENT_COMPAT, 'utf-8'); ?>" />
                                                </div>
                                            </div>
                                            <input type="hidden" name="access" value="<?php echo htmlentities($row_editstud['access'], ENT_COMPAT, 'utf-8'); ?>" />
                                            <input type="hidden" name="MM_update" value="form1" />
                                            <div class="form-actions">
                                                <input type="submit" value="Update Student Profile" class="btn btn-primary" >
                                                <a class="btn" href="<?= $_SERVER['HTTP_REFERER']?>">Cancel</a>
                                            </div>
                                        </form>
                                </div>
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