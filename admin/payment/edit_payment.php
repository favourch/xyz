<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "1,20,23,28";
check_auth($auth_users, $site_root.'/admin');

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$query_pstd = '';

$msg = null;
$id = '';
$type = '';
$std_name = '';
$params = false;
$gen_matric = false;
$gen_type = 'new';
$has_schedule = false;
$cont = array();

if (isset($_GET['id']) && $_GET['id'] != '' && isset($_GET['type']) && $_GET['type'] != '') {
    $params = true;
    $id = $_GET['id'];
    $type = $_GET['type'];
    $cat = $_GET['cat'];
    $query_stdinfo = '';
    $table = "schfee_transactions";
    $heading = "School Fee";

    switch ($cat) {
        case 'appfee':
            $table = "appfee_transactions";
            $heading = "Application Fee";
            break;

        case 'accfee':
            $table = "accfee_transactions";
            $heading = "Acceptance Fee";
            break;

        case 'jmbfee':
            $table = "jambregul_transactions";
            $heading = "Jamb Regularization Fee";
            break;

        case 'clrfee':
            $table = "clearance_transactions";
            $heading = "Clearance Fee";
            break;
        
        case 'repfee':
            $table = "reparation_transactions";
            $heading = "Reparation Fee";
            break;
        
         case 'regfee':
            $table = "registration_transactions";
            $heading = "Registration Fee";
            break;
        
        case 'olevelfee':
            $table = "olevelverifee_transactions";
            $heading = "O'Level Verification Fee";
            break;
    }

    $query_trans = sprintf("SELECT * "
            . "FROM  $table "
            . "WHERE ordid = %s ", GetSQLValueString($id, "text"));
    $trans = mysql_query($query_trans, $tams) or die(mysql_error());
    $row_trans = mysql_fetch_assoc($trans);
    $totalRows_trans = mysql_num_rows($trans);
    $transtatus = $row_trans['status'];

    if (isset($row_trans['scheduleid'])) {
        $query_schedule = sprintf("SELECT ps.scheduleid, ps.level, "
                                . "at.typename, ps.status, s.sesname "
                                . "FROM payschedule ps "
                . "JOIN session s ON ps.sesid = s.sesid "
                . "JOIN admission_type at ON ps.admid = at.typeid "
                                . "ORDER BY sesname DESC, level ASC");
        $schedule = mysql_query($query_schedule, $tams) or die(mysql_error());
        $totalRows_schedule = mysql_num_rows($schedule);

        $has_schedule = true;
    }
    // process initial page load
    $veri_session;
    switch ($type) {
        case 'reg':
            $enttype = 'regular';
            $stdid = $row_trans['matric_no'];
            $query_stdinfo = sprintf("SELECT stdid, fname, lname "
                                    . "FROM student "
                                    . "WHERE stdid = %s", 
                                    GetSQLValueString($stdid, "text"));
            $regSQL = sprintf("SELECT * "
                    . "FROM registration "
                    . "WHERE stdid = %s "
                    . "ORDER BY sesid "
                    . "DESC LIMIT 1",
                    GetSQLValueString($stdid, "text"));
            $regRS = mysql_query($regSQL, $tams) or die(mysql_error());
            $reg_row = mysql_fetch_assoc($regRS);
            
            break;

        case 'pros':
            $enttype = 'prospective';
            $stdid = $row_trans['can_no'];
            $query_stdinfo = sprintf("SELECT p.jambregid as stdid, p.fname, p.lname, a.sesid "
                                    . "FROM prospective p "
                                    . "JOIN admissions a ON a.admid=p.admid "
                                    . "WHERE p.jambregid = %s", 
                                    GetSQLValueString($stdid, "text"));
            
            break;
    }

    $stdinfo = mysql_query($query_stdinfo, $tams) or die(mysql_error());
    $row_stdinfo = mysql_fetch_assoc($stdinfo);
    $totalRows_stdinfo = mysql_num_rows($stdinfo);

    $std_name = $row_stdinfo['lname'] . ' ' . $row_stdinfo['fname'];
    
    $veri_session = ($type == 'pros')? $row_stdinfo['sesid'] : $reg_row['sesid'];
        
    // Check payment for prospective students.
    $query_payment = sprintf("SELECT * "
                            . "FROM $table "
                            . "WHERE can_no = %s  "
                            . "AND status = 'APPROVED'", 
                            GetSQLValueString($row_stdinfo['stdid'], "text"));
    $payment = mysql_query($query_payment, $tams) or die(mysql_error());
    $row_payment = mysql_fetch_assoc($payment);
    $totalRows_payment = mysql_num_rows($payment);


    $query_pros = sprintf("SELECT * "
                        . "FROM student "
                        . "WHERE jambregid = %s", 
                        GetSQLValueString($row_trans['can_no'], "text"));
    $pros = mysql_query($query_pros, $tams) or die(mysql_error());
    $row_pros = mysql_fetch_assoc($pros);
    $totalRows_pros = mysql_num_rows($pros);

    // Check if matric generation button should be enabled.
    if ($type == 'pros' && $totalRows_payment > 0) {

        // Check if entry exist in the student table.
        if ($totalRows_pros != 0) {
            // If entry exists, check if matric number was generated.
            if ($row_pros['stdid'] == '' || $row_pros['stdid'] == NULL) {
                // indicate generation of matric number and update of entry in the student table.
                $gen_matric = true;
                $gen_type = 'update';
            }
        }
        else {
            // indicate generation of matric number and new entry in the student table.
            $gen_matric = true;
        }

        if ($gen_matric && isset($_GET['gen'])) {
            $query_ses = sprintf("SELECT * "
                                . "FROM payschedule "
                                . "WHERE scheduleid = %s ", 
                                GetSQLValueString($row_payment['scheduleid'], "int"));
            $ses = mysql_query($query_ses, $tams) or die(mysql_error());
            $row_ses = mysql_fetch_assoc($ses);

            mysql_query('START TRANSACTION;', $tams);
            
            $new_matric = migrate_details($row_ses, $id, $row_trans['can_no'], $tams, $gen_type);

            if ($new_matric && $new_matric != '') {
                mysql_query('COMMIT;', $tams);
                $gen_matric = false;
                $msg = "A Matric number has been successfully generated for this student! "
                        . "The matric number is {$new_matric}.";
                 
                
                        
            }
            else {
                mysql_query('ROLLBACK;', $tams);
                $msg = "A Matric number could not be generated for this student! "
                        . "Please try again.";
                
            }
        }
    }

    // Process transaction update
    if (isset($_POST['update'])) {
        if (!empty($_POST)) {

            $update_array = array();

            foreach ($_POST as $name => $value) {
                if ($name == 'update') {
                    continue;
                }

                if (isset($value) && $value != '') {
                    if ($name == 'amt') {
                        $value = str_replace(',', '', $value);

                        if (stripos($value, 'ngn') !== false) {
                            $value = substr($value, 3);
                        }

                        $value = 'NGN' . number_format($value, 2);
                    }

                    $update_text = sprintf('%s = %s', GetSQLValueString($name, 'defined', $name), GetSQLValueString($value, 'text'));

                    array_push($update_array, $update_text);

                    if ($row_trans[$name] != $value) {
                        $cont[] = array($name => array("old" => $row_trans[$name], "new" => $value));
                    }

                    $row_trans[$name] = $value;
                }
            }

            if ($row_trans['status'] == 'APPROVED') {

                if ($type == 'pros') {
                    // Check if entry exist in the student table.
                    if ($totalRows_pros != 0) {
                        // If entry exists, check if matric number was generated.
                        if ($row_pros['stdid'] == '' || $row_pros['stdid'] == NULL) {
                            // indicate generation of matric number and update of entry in the student table.
                            $gen_matric = true;
                            $gen_type = 'update';
                        }
                    }
                    else {
                        // indicate generation of matric number and new entry in the student table.
                        $gen_matric = true;
                    }
                }
                $transtatus = $row_trans['status'];
                
                // update olevel verification
                        //Check if updating o'level fee
                    if($cat == 'olevelfee'){
                        //check existence in verification table
                        $verSQL = sprintf("SELECT * "
                                        . "FROM verification "
                                        . "WHERE stdid = %s ",
                                        GetSQLValueString($stdid, 'text'));
                        $verRS = mysql_query($verSQL, $tams);
                        $ver_num_rows = mysql_num_rows($verRS);
                        
                       
                        
                        if($ver_num_rows < 1){
                            
                            if(explode('.',str_replace(['N', 'G', 'NGN', ','], '', $row_trans['amt']))[0] > 500){
                                $sitting = 2 ;
                            }else{
                                $sitting = 1;
                            }
                            
                          $verificationSQL = sprintf("INSERT INTO verification "
                                                     . "(stdid, sesid, type, ver_code, olevel_sitting ) "
                                                     . "VALUES(%s, %s, %s, UUID(), %s )", 
                                                     GetSQLValueString($stdid, 'text'),
                                                     GetSQLValueString($veri_session, 'int'),
                                                     GetSQLValueString($type, 'text'),
                                                    // GetSQLValueString($row_trans['ordid'], "text"),
                                                     GetSQLValueString($sitting, "int"));
                            $verificationRS = mysql_query($verificationSQL, $tams); 
                        }
                    }
                
            }


            if (!empty($update_array)) {
                $updateSQL = sprintf("UPDATE "
                        . " %s "
                        . "SET %s "
                        . "WHERE ordid = %s", $table, GetSQLValueString('set', "defined", implode(', ', $update_array)), GetSQLValueString($id, "text"));

                $Result = mysql_query($updateSQL, $tams) or die(mysql_error());

                $param['entid'] = $stdid;
                $param['enttype'] = $enttype;
                $param['action'] = 'edit';
                $param['cont'] = json_encode($cont);
                
                
                
                
                if ($Result) {
                    audit_log($param);
                    $msg = 'Transaction Updated Successfully! ';
                    
                }
                else {
                    $param['status'] = 'failed';
                    audit_log($param);
                    $msg = 'Unable to update Transaction! ';
                    
                }
            }
        }
        else {
            $msg = 'The transaction status type selected is incorrect!';
           
        }
    }
}
else {
    $msg = 'The transaction information is incomplete!';
    
}
$notification->set_notification($msg, 'success');

$deptname = "";

if(isset($_POST['reset_print'])){
   
    $pcountSQL  = false;
   
    if($_GET['cat'] == 'schfee')
    {
       
        $pScheduleSQL = sprintf("SELECT scheduleid  FROM %s  WHERE ordid = %s ",
                    $table,
                    GetSQLValueString($_POST['ordid'], 'text'));
        $pScheduleRST = mysql_query($pScheduleSQL, $tams) or die(mysql_error());
        $pScheduleRow = mysql_fetch_assoc($pScheduleRST);
       
        $pcountSQL = sprintf("UPDATE %s "
                    . "SET pcount = 0 "
                    . "WHERE scheduleid = %s "
                    . "AND status = 'APPROVED' ",
                    $table,
                    GetSQLValueString($pScheduleRow['scheduleid'], 'text'));
       
    }
    else
    {
        $pcountSQL = sprintf("UPDATE %s "
                    . "SET pcount = 0 "
                    . "WHERE ordid = %s ",
                    $table,
                    GetSQLValueString($_POST['ordid'], 'text'));
       
    }
   
   
    if($pcountSQL)
    {
        $pCountRST = mysql_query($pcountSQL, $tams) or die(mysql_error());
        $pCountTreated = mysql_affected_rows();
       
        if($pCountTreated > 0)
        {
            $msg = "Print Counter reset successful";
            $notification->set_notification($msg, 'success');
        }
        else
        {
            $msg = "No changes made. Print counter is in it Zero state ";
            $notification->set_notification($msg, 'error');
        } 
    }
    else
    {
        $msg = "Unable to perform this Operation. Please try again later";
        $notification->set_notification($msg, 'error');
    }
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
                    </div>
                    <br/>-->
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-credit-card"></i>
                                        Edit Payment
                                    </h3>
                                    <form method="POST" action="<?php echo $editFormAction; ?>" >
                                        <ul class="tabs">
                                            <input type="hidden" name="ordid" value="<?=$row_trans['ordid']?>">
                                            <li class="active"><button class="btn btn-small btn-blue" type="submit" name="reset_print" value="yes">Reset Print Count</button></li>
                                        </ul>
                                    </form>
                                </div>
                                <div class="box-content">
                                    <?php if($params){?>
                                    <?php if(in_array(getAccess(), [20])) {?>
                                    <form class="form-horizontal form-bordered" method="POST" action="<?php echo $editFormAction; ?>" >
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Candidate Number</label>
                                            <div class="controls">
                                                <input type="text" class="input-xlarge" value="<?=$row_trans['can_no']?>" id="textfield" name="can_no">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Matric Number</label>
                                            <div class="controls">
                                                <input type="text" class="input-xlarge" value="<?=$row_trans['matric_no']?>" id="textfield" name="matric_no">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Name </label>
                                            <div class="controls">
                                                <input type="text" class="input-xxlarge" value="<?php echo $std_name?>" id="textfield" readonly="">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Reference </label>
                                            <div class="controls">
                                                <input type="text" class="input-xlarge" value="<?php echo $row_trans['reference']?>" id="textfield" readonly="">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Datetime </label>
                                            <div class="controls">
                                                <input type="text" class="input-xlarge" value="<?php echo $row_trans['date_time']?>" id="textfield" readonly="">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Year </label>
                                            <div class="controls">
                                                <input type="text" class="input-xlarge" value="<?php echo $row_trans['year']?>" id="textfield" readonly="">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textarea">Status</label>
                                            <div class="controls">
                                                <select name="status">
                                                    <option value="CANCELLED" <?php if ($transtatus == 'CANCELLED') echo 'selected' ?>>Canceled</option>
                                                    <option value="DECLINED" <?php if ($transtatus == 'DECLINED') echo 'selected' ?>>Declined</option>
                                                    <option value="PENDING" <?php if ($transtatus == 'PENDING') echo 'selected' ?>>Pending</option>
                                                    <option value="REFUNDED" <?php if ($transtatus == 'REFUNDED') echo 'selected' ?>>Refunded</option>
                                                    <option value="APPROVED" <?php if ($transtatus == 'APPROVED') echo 'selected' ?>>Approved</option>
                                                </select>
                                            </div>
                                        </div>
                                        <?php if($has_schedule) :?>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Schedule </label>
                                            <div class="controls">
                                                <select name="scheduleid" class="input-xxlarge">
                                                    <option>No Schedule Selected</option>
                                                    <?php for (; $row_schedule = mysql_fetch_assoc($schedule);) : ?>
                                                        <option value="<?php echo $row_schedule['scheduleid'] ?>" 
                                                                <?php if ($row_schedule['scheduleid'] == $row_trans['scheduleid']) echo 'selected' ?>>
                                                                    <?php
                                                                    echo sprintf("%s, Level %s, %s, %s", $row_schedule['sesname'], $row_schedule['level'], $row_schedule['typename'], $row_schedule['status'])
                                                                    ?>
                                                        </option>
                                                                <?php endfor; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php endif;?>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Amount </label>
                                            <div class="controls">
                                                <input type="text" class="input-xlarge" value="<?= $row_trans['amt']?>" id="textfield" name="amt">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Order ID</label>
                                            <div class="controls">
                                                <input type="text" class="input-xlarge" value="<?=$row_trans['ordid']?>" id="textfield" name="ordid">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">PAN </label>
                                            <div class="controls">
                                                <input type="text" class="input-xlarge" value="<?= $row_trans['pan'] ?>" id="textfield" name='pan'>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Response Description </label>
                                            <div class="controls">
                                                <input type="text" class="input-xlarge" value="<?=$row_trans['resp_desc']?>"  name="resp_desc">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Response Code </label>
                                            <div class="controls">
                                                <input type="text" class="input-xlarge" name="resp_code" value="<?= $row_trans['resp_code']?>" >
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Authentication Code </label>
                                            <div class="controls">
                                                <input type="text" class="input-xlarge" name="auth_code" value="<?= $row_trans['auth_code']?>" >
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Percentage </label>
                                            <div class="controls">
                                                <input type="number" class="input-xlarge" min="0" max="100"value="<?php echo $row_trans['percentPaid']?>" name="percentPaid">
                                            </div>
                                        </div>
                                        
                                        <div class="form-actions">
                                            <button class="btn btn-primary" name="update" type="submit">Update Transaction</button>
                                            <a class="btn" href="search_payment.php">Close</a>
                                        </div>
                                    </form>
                                    <?php } ?>
                                    <?php if($gen_matric) {?>
                                    <div class="row-fluid">
                                        <div class="well">
                                            <div class="alert alert-info">
                                                <i class="icon-info-sign"></i>
                                                This student does not have a matric number, but has successfully paid the school fees! 
                                               Click the button below to generate a matric number for the student. 
                                            </div>
                                            <div>
                                                <button class="btn btn-brown" type="button" 
                                                        onclick="generate_matric()">
                                                    Generate Matric
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php }?>
                                    <?php }else{?>
                                    <div class="alert alert-error">
                                        The requested parameters to generate the payment edit for can not be found click on the Search Payment Button to 
                                        <a href="search_payment.php" class="btn btn-small btn-darkblue">Search Payment</a>
                                    </div>
                                    <?php }?>
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
    <script type="text/javascript">
        function generate_matric() {
            if(location.search.indexOf('gen') === -1) {
                location.search = location.search+<?php echo "'&gen'" ?>;
            }else {
                location.search = location.search;
            }
        }   
    </script>
</html>

