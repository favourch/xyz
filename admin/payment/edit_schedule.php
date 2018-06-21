<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "1, 20";
check_auth($auth_users, $site_root . '/admin');

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
    $status = (in_array($_POST[status], ['Indigene', 'Nonindigene']))? 'status': 'regtype';
    
    $updateSQL = sprintf("UPDATE payschedule "
                        . "SET sesid = %s, level=%s, admid=%s, amount=%s, "
                        . "penalty= %s, %s = %s, payhead = %s, revhead = %s,"
                        . "paystatus = %s, regstatus = %s "
                        . "WHERE scheduleid = %s",
                        GetSQLValueString($_POST['sesid'], "text"),
                        GetSQLValueString($_POST['level'], "text"),
                        GetSQLValueString($_POST['admid'], "text"),
                        GetSQLValueString($_POST['amount'], "text"),
                        GetSQLValueString($_POST['penalty'], "text"),
                        GetSQLValueString($status, "defined", $status),
                        GetSQLValueString($_POST['status'], "text"),
                        GetSQLValueString($_POST['payhead'], "text"),
                        GetSQLValueString($_POST['revhead'], "text"),
                        GetSQLValueString($_POST['paystatus'], "text"),
                        GetSQLValueString($_POST['regstatus'], "text"),
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

$query_level = sprintf("SELECT * FROM level_name");
$level = mysql_query($query_level, $tams) or die(mysql_error());
$row_level = mysql_fetch_assoc($level);

$query_ses = sprintf("SELECT * FROM session ORDER BY sesid DESC");
$ses = mysql_query($query_ses, $tams) or die(mysql_error());
$row_ses = mysql_fetch_assoc($ses);

$query_regtype = "SELECT * "
        . "FROM registration_type";
$regtype = mysql_query($query_regtype, $tams) or die(mysql_error());
$totalRows_regtype = mysql_num_rows($regtype);

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
                                    <ul class="tabs">
                                        <li class="active">
                                            <a href="set_schedule.php"><i class=" icon-arrow-left"></i> Back</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="box-content">
                                    <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post">
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Session</label>
                                            <div class="controls">
                                                <select name="sesid" required="" class="input-large">
                                                    <option value="">-- Choose --</option>
                                                    <?php do { ?>
                                                        <option value="<?= $row_ses['sesid'] ?>" <?= ($row_editschdl['sesid'] ==  $row_ses['sesid'])? 'selected' : ''?>><?= $row_ses['sesname'] ?></option>
                                                    <?php
                                                    }
                                                    while ($row_ses = mysql_fetch_assoc($ses));
                                                    $rows = mysql_num_rows($ses);
                                                    if ($rows > 0) {
                                                        mysql_data_seek($ses, 0);
                                                        $row_ses = mysql_fetch_assoc($ses);
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Level </label>
                                            <div class="controls">
                                                <select name="level" class="input-large" required="">
                                                    <option value="">-- Choose --</option>
                                                    <option value="0" <?= ($row_editschdl['level'] == '0')? 'selected':''?>>Prospective</option>
                                                    <?php do { ?>
                                                        <option value="<?= $row_level['levelid'] ?>"  <?= ($row_editschdl['level'] == $row_level['levelid'])? 'selected':''?>><?= $row_level['levelname'] ?></option>
                                                    <?php }while ($row_level = mysql_fetch_assoc($level)); ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Entry Mode</label>
                                            <div class="controls">
                                                <select name="admid" class="input-large" required="">
                                                    <option value="">-- Choose --</option>
                                                    <?php do{?>
                                                    <option value="<?= $row_admtype['typeid']?>" <?= ($row_editschdl['admid'] == $row_admtype['typeid'])? 'selected':''?>><?= $row_admtype['typename']?></option>
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
                                                <input type="text" name="penalty" required="" class="input-large"  value="<?= $row_editschdl['penalty']?>"/>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Payment Head</label>
                                            <div class="controls">
                                                <select name="payhead" class="input-large" required="">
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
                                                <select name="status" class="input-large" required="">
                                                    <?php if(!isset($row_editschdl['regtype']) || $row_editschdl['regtype'] < 1) :?>
                                                    <option value="indigene" <?= ($row_editschdl['status'] == "Indigene")? 'selected':''?>>Indigene</option>
                                                    <option value="nonindigene" <?= ($row_editschdl['status'] == "Nonindigene")? 'selected':''?>>Non-Indigene</option>
	                                            <?php else:
						             for (;$row_regtype = mysql_fetch_assoc($regtype);) :
						    ?>
					            <option value="<?php echo $row_regtype['regtypeid']?>" 
					            	<?= ($row_editschdl['regtype'] == $row_regtype['regtypeid'])? 'selected':''?>>
					            	<?php echo $row_regtype['displayname']?>
					            </option>                                            
                                                    <?php    endfor;
                                                      	  endif;
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Revenue Head</label>
                                            <div class="controls">
                                                <input type="text" name="revhead" class="input-large" required="" value="<?= $row_editschdl['revhead']?>"/>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Pay Status</label>
                                            <div class="controls">
                                                <select name="paystatus" class="input-large" required="">
                                                    <option value="active" <?= ($row_editschdl['regstatus'] == "active")? 'selected':''?>>Active</option>
                                                    <option value="inactive" <?= ($row_editschdl['regstatus'] == "inactive")? 'selected':''?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Reg. Status</label>
                                            <div class="controls">
                                                <select name="regstatus" class="input-large" required="">
                                                    <option value="active" <?= ($row_editschdl['regstatus'] == "active")? 'selected':''?>>Active</option>
                                                    <option value="inactive" <?= ($row_editschdl['regstatus'] == "inactive")? 'selected':''?>>Inactive</option>
                                                </select>
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

