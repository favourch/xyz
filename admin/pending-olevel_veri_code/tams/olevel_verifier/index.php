<?php
if (!isset($_SESSION)) {
    session_start();
}


require_once('../path.php');



$auth_users = "10,11";
check_auth($auth_users, $site_root);

$sts = "";
$reroot = "index.php";
$reroot2 = "../admission/status.php";


if (getAccess() == '11') {
    mysql_select_db($database_tams, $tams);
    $query_student = sprintf("SELECT *  "
                            . "FROM prospective  "
                            . "WHERE  jambregid = %s ", 
                            GetSQLValueString(getSessionValue('uid'), 'text'));
    $student = mysql_query($query_student, $tams) or die(mysql_error());
    $row_student = mysql_fetch_assoc($student);
    $veri_data_row_num = mysql_num_rows($student);
}
else {
    mysql_select_db($database_tams, $tams);
    $query_student = sprintf("SELECT *  "
                            . "FROM student  "
                            . "WHERE  stdid = %s ",
                            GetSQLValueString(getSessionValue('uid'), 'text'));
    $student = mysql_query($query_student, $tams) or die(mysql_error());
    $row_student = mysql_fetch_assoc($student);
    $veri_data_row_num = mysql_num_rows($student);
}

if (isset($row_student['adminstatus']) && $row_student['adminstatus'] == 'No') {
    header("Location: " . $reroot2);
    exit;
}

mysql_select_db($database_tams, $tams);
$query1 = sprintf("SELECT *  "
                . "FROM olevel_veri_data  "
                . "WHERE  stdid = %s", GetSQLValueString(getSessionValue('uid'), 'text'));
$veri_data = mysql_query($query1, $tams) or die(mysql_error());
$veri_data_row = mysql_fetch_assoc($veri_data);
$veri_data_row_num = mysql_num_rows($veri_data);


$query = sprintf("SELECT *  "
                . "FROM verification  "
                . "WHERE  stdid = %s", 
                GetSQLValueString(getSessionValue('uid'), 'text'));
$verify = mysql_query($query, $tams) or die(mysql_error());
$verify_row = mysql_fetch_assoc($verify);
$verify_row_num = mysql_num_rows($verify);


$Query_prog_opt = sprintf("SELECT * "
                        . "FROM prog_options "
                        . "WHERE jambregid = %s ",
                        GetSQLValueString(getSessionValue('uid'), 'text') );
$prog_opt = mysql_query($Query_prog_opt, $tams) or die(mysql_error());
$prog_opt_row = mysql_fetch_assoc($prog_opt);
$prog_opt_row_num = mysql_num_rows($prog_opt);


if($prog_opt_row_num > 0){
    
    $progQuery = sprintf("SELECT progid, progname "
                        . "FROM programme "
                        . "WHERE progid IN ( " . str_replace(["'", '"'], "", $prog_opt_row['choice']) . " )");
    $prgRS = mysql_query($progQuery) or die(mysql_error());
    $prgRS_row = mysql_fetch_assoc($prgRS);
}
    
    
$status = ($verify_row['verified'] == "TRUE") ? "<p style=' color: green; font-size: 20px; font-weight: bold'>VERIFIED</p>" : "<p style=' color: red; font-size: 20px; font-weight: bold'>NOT YET VERIFIED</p>";

if (isset($_POST['ver_code']) && $_POST['ver_code'] != NULL) {

    if ($_POST['ver_code'] == $verify_row['ver_code']) {
        
        $query2 = sprintf("UPDATE verification "
                        . "SET verified = 'TRUE' "
                        . "WHERE  stdid=%s",
                        GetSQLValueString(getSessionValue('uid'), 'text'));
        $updateverify = mysql_query($query2, $tams) or die(mysql_error());
        if($updateverify){
            $notification->set_notification('Verification successfull.', 'success');
            
            header("Location: index.php");
            exit;
            
        }else{
            $notification->set_notification('Unable to verify please try again.', 'error');
        }
        
    }
    else {
       $notification->set_notification('Incorrect verification Code Entered', 'error');
    }  
   
}


if (isset($_POST['prog_choice']) && $_POST['prog_choice'] != NULL){
    
    $progQuery1 = sprintf("SELECT progid, progname "
                    . "FROM programme "
                    . "WHERE progid = '{$_POST['prog_choice']}'");
    $prg = mysql_query($progQuery1) or die(mysql_error());
    $prg_row = mysql_fetch_assoc($prg);

    $msg = "Congratulations! Your new choice of programme "
            . "is accepted and your o'level result met the "
            . "requirement of the programme "
            . " you selected  i.e <br/>(<strong>{$prg_row['progname']}</strong>)<br/>. Copy and paste "
            . "the above  verification code in the "
            . "text box below and click verified so that you can"
            . " proceed with your payment ";

    mysql_query("BEGIN");
    
    $Query_prosp = sprintf("UPDATE prospective "
                        . "SET progoffered = %s "
                        . "WHERE jambregid = %s", 
                        GetSQLValueString($_POST['prog_choice'], 'text'),
                        GetSQLValueString(getSessionValue('uid'), 'text'));
    $prosRS = mysql_query($Query_prosp) or die(mysql_error());
    
    
    $Query_veri = sprintf("UPDATE verification "
                        . "SET msg = %s, release_code = 'yes' "
                        . "WHERE stdid = %s ", GetSQLValueString($msg, 'text') ,
                        GetSQLValueString(getSessionValue('uid'), 'text'));
    $veriRS = mysql_query($Query_veri) or die(mysql_error());
    $affected = mysql_affected_rows();
    
    if($affected > 0){
        
        mysql_query("COMMIT");
        
    }else{
        mysql_query("ROLLBACK");
    }
    
    header("Location: index.php");
    exit;
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
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        O'Level Result Verifier
                                    </h3>
                                </div>
                                <div class="box-content">
                                    
                                    <div class="row-fluid">
                                        <h4>O'LEVEL VERIFICATION STATUS</h4>
                                        <?php if($veri_data_row_num > 0){ ?>
                                        <table class="table table-bordered table-condensed table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>S/n</th>
                                                    <th>Exam Type </th>
                                                    <th>Exam year</th>
                                                    <th>Exam No</th> 
                                                    <th >Date Submitted</th>
                                                    <th>Receipt </th>
                                                    <th>Status </th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $i = 1;do { ?>
                                                    <tr>
                                                        <td><?= $i++; ?></td>
                                                        <td><?= $veri_data_row['exam_type'] ?></td>
                                                        <td><?= $veri_data_row['exam_year'] ?></td>
                                                        <td><?= $veri_data_row['exam_no'] ?></td>
                                                        <td><?= $veri_data_row['date'] ?></td>
                                                        <td>
                                                            <a target="_blank" href="olevel_veri_payment/receipt.php?no=<?php echo $veri_data_row['ordid'] ?>">
                                                                <button type="button"> Print</button>
                                                            </a>
                                                        </td>
                                                        <td><div class="alert-info"><?php echo $veri_data_row['return_msg'] ?></div></td>
                                                    </tr>
                                                <?php }while ($veri_data_row = mysql_fetch_assoc($veri_data));?>
                                            </tbody>    
                                        </table> 
                                        <?php } else {?>
                                            <div class="alert alert-danger">You have Not submit any O'level result for verification</div>
                                        <?php }?>
                                    </div>
                                    <p>&nbsp;</p>
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <div class='alert' style="text-align: center" >
                                            <?php echo $status;?>
                                            </div>
                                        </div>
                                    </div>
                                    <p>&nbsp;</p>
                                    <?php if($verify_row['verified'] == "FALSE"){?>
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <div class='alert' style="text-align: center" >
                                                
                                                <?php if($verify_row['release_code'] == "yes"  &&  $verify_row['verified'] == "FALSE") { ?>
                                                <p style=' color: green; font-size: 20px; font-weight: bold'> <?= $verify_row['ver_code'] ?> </p>
                                                <?php } ?>
                                                
                                                <?php if($verify_row['msg'] != ""){ ?>
                                                <div class="alert alert-info">
                                                    <p><?= $verify_row['msg'] ?></p>
                                                </div>
                                                <?php } ?>
                                                    
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <div class="row-fluid">
                                        <?php if ($prog_opt_row_num > 0 && $verify_row['refer'] && $verify_row['release_code'] == 'no' && $verify_row['treaded_yes'] == 'no') { ?>
                                            <div class="span6">
                                                <form  class="form form-horizontal" name="form" method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>"> 
                                                    <div class="control-group">
                                                        <label class="control-label" for="textfield">Choose Programme</label>
                                                        <div class="controls">
                                                            <div class="input-append">
                                                                <select name="prog_choice" class="input-medium" required="">
                                                                    <option value="">--Choose--</option>
                                                                    
                                                                    <?php do { ?>
                                                                        <option value="<?= $prgRS_row['progid'] ?>"><?= $prgRS_row['progname'] ?></option>
                                                                    <?php } while ($prgRS_row = mysql_fetch_assoc($prgRS)) ?>
                                                                        
                                                                </select>
                                                                <button type='submit' class="btn btn-blue">Accept programme</button>
                                                            </div>   
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="row-fluid">
                                        <?php if($verify_row['verified'] == "FALSE" || empty($verify_row)){?>
                                        <div class="span6">
                                            <form  class="form form-horizontal" name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>"> 
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">Verification Code</label>
                                                    <div class="controls">
                                                        <div class="input-append">
                                                            <input type="text" class="input-block-level"  id="textfield" name="ver_code">
                                                            <button type='submit' name='submit' class="btn btn-purple">Verify</button>
                                                        </div>   
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <?php }else {?>
                                            <?php if (getAccess() == '11') { ?>
                                                <a href="../admission/fee_payment/index.php" class="btn btn-primary">Pay School Fee</a>
                                            <?php } ?>
                                        <?php }?>
                                    </div>
                                    
                                    
                                    <div class="row-fluid">
                                        <div class='span6'>
                                            <a href="form.php" class="btn btn-blue"><i class="icon-credit-card"></i> Submit O'Level Result Checker Card Details</a>
                                        </div>
                                        <div class='span6'>
                                            <a href="olevel_veri_payment/index.php" class="btn btn-brown"><i class="icon-money"></i> Pay O'Level Verification Fee</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>          
            </div>
            
           
        <?php include INCPATH."/footer.php" ?>
    </body>
</html>