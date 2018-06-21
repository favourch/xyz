<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');
$auth_users = "10,11";
check_auth($auth_users, $site_root);

fillAccomDetails($site_root, $tams);

//Get current user details 
if (getAccess() == '10') {

    mysql_select_db($database_tams, $tams);
    $query_rspros = sprintf("SELECT * "
                            . "FROM student "
                            . "WHERE stdid = %s", 
                            GetSQLValueString(getSessionValue('uid'), "text"));
    $rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
    $row_rspros = mysql_fetch_assoc($rspros);
    $totalRows_rspros = mysql_num_rows($rspros);
} else {

    mysql_select_db($database_tams, $tams);
    $query_rspros = sprintf("SELECT p.* "
                            . "FROM prospective p "
                            . "WHERE p.jambregid = %s", 
                            GetSQLValueString(getSessionValue('uid'), "text"));
    $rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
    $row_rspros = mysql_fetch_assoc($rspros);
    $totalRows_rspros = mysql_num_rows($rspros);
}


$query = sprintf("SELECT * "
                . "FROM verification "
                . "WHERE stdid = %s", 
                GetSQLValueString(getSessionValue('uid'), "text"));
$rsResult = mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);
$num_row_result = mysql_num_rows($rsResult);

$status = ($row_result['verified'] == "TRUE") ? "<p style=' color: green; font-size: 20px; font-weight: bold'>VERIFIED</p>" : "<p style=' color: red; font-size: 20px; font-weight: bold'>NOT YET VERIFIED</p>";

$Query_prog_opt = sprintf("SELECT * "
                        . "FROM prog_options "
                        . "WHERE jambregid = %s ", 
                        GetSQLValueString(getSessionValue('uid'), 'text'));
$prog_opt = mysql_query($Query_prog_opt, $tams) or die(mysql_error());
$prog_opt_row = mysql_fetch_assoc($prog_opt);
$prog_opt_row_num = mysql_num_rows($prog_opt);


if ($prog_opt_row_num > 0) {

    $progQuery = sprintf("SELECT progid, progname "
                        . "FROM programme "
                        . "WHERE progid IN ( " . str_replace(["'", '"'], "", $prog_opt_row['choice']) . " )");
                $prgRS = mysql_query($progQuery) or die(mysql_error());
    $prgRS_row = mysql_fetch_assoc($prgRS);
}


$query1 = sprintf("SELECT *  "
        . "FROM olevel_veri_data  "
        . "WHERE  stdid = %s", GetSQLValueString(getSessionValue('uid'), 'text'));
$veri_data = mysql_query($query1, $tams) or die(mysql_error());
$veri_data_row = mysql_fetch_assoc($veri_data);
$veri_data_row_num = mysql_num_rows($veri_data);

$data = array();
do{
    $data[] = $veri_data_row;
}while($veri_data_row = mysql_fetch_assoc($veri_data));

//Submit o'level verification details 
if (isset($_POST['MM_Submit']) && $_POST['MM_Submit'] == 'form1') {
    mysql_query("BEGIN", $tams);
    
    foreach ($_POST['entry'] AS $entry) {
        $form_fields = $entry;
        
        
        $stat = "<p style='color:blue'>Submitted for further processing "
                . "<br/>Please check back after 2 working days </p>";
        
        $query = sprintf("INSERT INTO olevel_veri_data "
                . "(stdid, usertype, exam_type, exam_year, "
                . "exam_no, card_no, card_pin, date, "
                . "sesid, level, return_msg, progid, approve ) "
                . "VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 'Submitted')", 
                GetSQLValueString(getSessionValue('uid'), 'text'), 
                GetSQLValueString(getSessionValue('accttype'), 'text'), 
                GetSQLValueString($form_fields['exam_type'], 'text'), 
                GetSQLValueString($form_fields['exam_year'], 'text'), 
                GetSQLValueString($form_fields['exam_no'], 'text'), 
                GetSQLValueString($form_fields['card_no'], 'text'),
                GetSQLValueString($form_fields['card_pin'], 'text'),
                GetSQLValueString(date('Y-m-d'), 'date'), 
                GetSQLValueString($form_fields['sesid'], 'text'),
                GetSQLValueString($form_fields['level'], 'text'),
                GetSQLValueString($stat, 'text'), 
                GetSQLValueString($form_fields['progid'], 'text'));
        $olevel = mysql_query($query, $tams) or die(mysql_error());
    }

    $verificationSQL = sprintf("UPDATE verification "
                            . "SET olevel_submit = 'TRUE' "
                            . "WHERE stdid = %s ", 
                            GetSQLValueString(getSessionValue('uid'), 'text'));
    $verificationRS = mysql_query($verificationSQL, $tams) or die(mysql_error());

    if ($verificationRS && $olevel) {
        mysql_query("COMMIT", $tams);
        $notification->set_notification("Card Details Submited Succesfully", 'success');
    } else {
        mysql_query("ROLLBACK", $tams);
        $notification->set_notification("Unable to submit Card Details ", 'error');
    }
    
    header('Location: index.php');
    exit();
}


//Submit Extra o'level verification details 
if (isset($_POST['MM_Extra']) && $_POST['MM_Extra'] == 'extra') {
    mysql_query("BEGIN", $tams);
    
    $stat = "<p style='color:blue'>Submitted for further processing "
                . "<br/>Please check back after 2 working days </p>";
        
        $query = sprintf("INSERT INTO olevel_veri_data "
                        . "(stdid, usertype, exam_type, exam_year, "
                        . "exam_no, card_no, card_pin, date, "
                        . "sesid, level, return_msg, progid, approve ) "
                        . "VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 'Submitted')", 
                        GetSQLValueString(getSessionValue('uid'), 'text'), 
                        GetSQLValueString(getSessionValue('accttype'), 'text'), 
                        GetSQLValueString($_POST['exam_type'], 'text'), 
                        GetSQLValueString($_POST['exam_year'], 'text'), 
                        GetSQLValueString($_POST['exam_no'], 'text'), 
                        GetSQLValueString($_POST['card_no'], 'text'),
                        GetSQLValueString($_POST['card_pin'], 'text'),
                        GetSQLValueString(date('Y-m-d'), 'date'), 
                        GetSQLValueString($_POST['sesid'], 'text'),
                        GetSQLValueString($_POST['level'], 'text'),
                        GetSQLValueString($stat, 'text'), 
                        GetSQLValueString($_POST['progid'], 'text')); 
        $olevel = mysql_query($query, $tams) or die(mysql_error());

    $verificationSQL = sprintf("UPDATE verification "
                            . "SET extra = '0', olevel_sitting  = olevel_sitting + 1 "
                            . "WHERE stdid = %s ", 
                            GetSQLValueString(getSessionValue('uid'), 'text'));
    $verificationRS = mysql_query($verificationSQL, $tams) or die(mysql_error());

    if ($verificationRS && $olevel) {
        mysql_query("COMMIT", $tams);
        $notification->set_notification("Card Details Submited Succesfully", 'success');
    } else {
        mysql_query("ROLLBACK", $tams);
        $notification->set_notification("Unable to submit Card Details ", 'error');
    }
    
    header('Location: index.php');
    exit();
}



if(isset($_POST['MM_Update']) && $_POST['MM_Update'] == 'form2'){
    $form_fields = $_POST;
    mysql_query('BEGIN');
    $stat = "<p style='color:blue'>Update Successful for"
            . " further processing "
            . "<br/>Please check back later </p>";
    
    $update_olevelSQL2 = sprintf("UPDATE olevel_veri_data "
                                . "SET approve = 'Submitted', treated='No' "
                                . "WHERE stdid = %s ",
                                GetSQLValueString(getSessionValue('uid'), 'text'));
    $Update_olevel2 = mysql_query($update_olevelSQL2, $tams) or die(mysql_error());
    
    $update_olevelSQL = sprintf("UPDATE olevel_veri_data "
                                . "SET  exam_type =%s , exam_year = %s,"
                                . " exam_no =%s, card_no = %s, approve = 'Submitted', "
                                . "treated='No', card_pin = %s, date = %s,"
                                . " return_msg = %s WHERE id = %s " ,
                                GetSQLValueString($form_fields['exam_type'], 'text'), 
                                GetSQLValueString($form_fields['exam_year'], 'text'), 
                                GetSQLValueString($form_fields['exam_no'], 'text'), 
                                GetSQLValueString($form_fields['card_no'], 'text'), 
                                GetSQLValueString($form_fields['card_pin'], 'text'), 
                                GetSQLValueString(date('Y-m-d'), 'date'), 
                                GetSQLValueString($stat, 'text'), 
                                GetSQLValueString($form_fields['edit_id'], 'text'));
    $Update_olevel = mysql_query($update_olevelSQL, $tams) or die(mysql_error());

    if($Update_olevel && $Update_olevel2){
         mysql_query('COMMIT');
    }else{
         mysql_query('ROLLBACK');
    }
    header('Location: index.php');
    exit();
}


if (isset($_POST['ver_code']) && $_POST['ver_code'] != NULL) {

    if ($_POST['ver_code'] == $row_result['ver_code']) {
        
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
                        . "SET msg = %s, status = 'release' "
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
<html ng-app="app">
    <?php include INCPATH . "/header.php" ?>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="pageCtrl">
        <?php include INCPATH . "/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH . "/side_nav_bar.php" ?>

            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH . "/page_header.php" ?>
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        O'Level Verification Module (Status Page)
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <?php if ($num_row_result > 0) { ?>
                                    <?php if($row_result['extra'] == '1'){ ?>
                                        <div class="well well-small">
                                            <div class="alert alert-info">
                                                You have been activated to submit additional O'Level Details. 
                                                Click the proceed button
                                            </div>
                                            <a href="olevel_veri_payment/" class="btn btn-small btn-purple">proceed</a>
                                        </div>
                                     <?php }?>
                                    <?php if($row_result['extra'] == '2'){ ?>
                                        <div class="well well-small">
                                            <a  
                                                class="btn btn-small btn-blue" 
                                                data-toggle="modal"
                                                role="button" 
                                                href="#modal-2">Submit Other O'Level Details</a>  
                                        </div>
                                     <?php }?>
                                        <?php if ($row_result['olevel_submit'] == "FALSE") { ?>
                                    
                                            <div class="well well-small">
                                                <div class="alert alert-info">
                                                    <i class="glyphicon-circle_info"></i> 
                                                    You have chose and pay for the submission of <?= $row_result['olevel_sitting']?> O'Level result details. 
                                                    Bellow <?= ($row_result['olevel_sitting'] == 1)? ' is the form ': ' are the forms '?> to submit you details. Ensure you 
                                                    are submitting the right information as failure to comply will result in verification error
                                                </div>
                                            </div>
                                            <!--Olevel result Submission Form-->
                                            <form method="POST" action="index.php" >
                                                <div class="row-fluid">
                                                    <?php
                                                    $idx = 1;
                                                    do {
                                                        ?>

                                                        <div class="span6">
                                                            <h4>O'Level Result Details <?= $idx ?></h4>
                                                            <div class="well well-large">
                                                                <div class="row-fluid">
                                                                    <div class="span12">
                                                                        <div class="form-horizontal form-striped">
                                                                            <div class="control-group">
                                                                                <label class="control-label" for="textfield">Exam Type</label>
                                                                                <div class="controls">
                                                                                    <select name='entry[<?= $idx?>][exam_type]' class="input-large" required>
                                                                                        <option value="">--Choose--</option>
                                                                                        <option value="WASSCE (May/Jun)">WASSCE (May/Jun)</option>
                                                                                        <option value="WASSCE (Nov/Dec)">WASSCE (Nov/Dec)</option>
                                                                                        <option value="NECO (May/Jun)">NECO (May/Jun)</option>
                                                                                        <option value="NECO (Nov/Dec)">NECO (Nov/Dec) </option>
                                                                                        <option value="NABTEB (May/Jun)">NABTEB (May/Jun)</option>
                                                                                        <option value="NABTEB (Nov/Dec)">NABTEB (Nov/Dec)</option>
                                                                                        <option value="NABTEB Mod.(Mar)">NABTEB Mod.(Mar)</option>
                                                                                        <option value="NABTEB Mod.(Dec)">NABTEB Mod.(Dec)</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="control-group">
                                                                                <label class="control-label" for="textfield">Exam Year</label>
                                                                                <div class="controls">
                                                                                    <select name='entry[<?= $idx?>][exam_year]' class="input-large" required>
                                                                                        <option value="">--Choose--</option>
                                                                                        <?php
                                                                                        $date = date('Y');
                                                                                        for ($i = 0; $i < 25; $i++) {
                                                                                            ?>
                                                                                            <option value="<?= $date - $i ?>"> <?= $date - $i ?></option>
                                                                                        <?php } ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="control-group">
                                                                                <label class="control-label" for="textarea">Exam No</label>
                                                                                <div class="controls">
                                                                                    <input type="text" class="input-large" name='entry[<?= $idx?>][exam_no]' maxlength="10" required="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="control-group">
                                                                                <label class="control-label" for="textarea">Card Pin</label>
                                                                                <div class="controls">
                                                                                    <input type="text" name='entry[<?= $idx?>][card_pin]' class="input-large" required="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="control-group">
                                                                                <label class="control-label" for="textarea">Card Serial No.</label>
                                                                                <div class="controls">
                                                                                    <input type="text" name='entry[<?= $idx?>][card_no]' class="input-large" required="">
                                                                                </div>
                                                                            </div>
                                                                            <input type="hidden" name="MM_Submit" value="form1">
                                                                            <input type="hidden" name='entry[<?= $idx?>][sesid]' value="<?= getSessionValue('sesid') ?>"/>
                                                                            <input type="hidden" name='entry[<?= $idx?>][utype]' value="<?= getSessionValue('accttype') ?>"/>
                                                                            <input type="hidden" name='entry[<?= $idx?>][level]' value="<?php echo (getAccess() == 10) ? $row_rspros['level'] : "0" ?>"/>
                                                                            <input type="hidden" name='entry[<?= $idx?>][progid]' value="<?php echo (getAccess() == 10) ? $row_rspros['progid'] : $row_rspros['progoffered'] ?>"/>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <?php
                                                        $idx++;
                                                    } while ($idx <= $row_result['olevel_sitting']);
                                                    ?>

                                                </div>
                                                <div class="row-fluid">
                                                    <div class="span12">
                                                        <div class="well well-small" style="align-content: center">
                                                            <input type="hidden" name='MM_Submit' value="form1">
                                                            <button class="btn btn-primary" type="submit" name="submit">Submit Card</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                    
                                        <?php } else { ?>
                                            
                                            <h4>Submitted O'Level Details / Status</h4>
                                            <div class="well well-small">
                                                <div class="row-fluid">
                                                    <div class="span12">
                                                            
                                                        <table class="table table-bordered table-condensed table-striped table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>S/n</th>
                                                                    <th>Exam Type </th>
                                                                    <th>Exam year</th>
                                                                    <th>Exam No</th> 
                                                                    <th >Date Submitted</th>
                                                                    <th>Status </th>
                                                                    <th>Actions </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>

                                                                <tr ng-repeat="dt in submission">
                                                                    <td>{{$index +1 }}</td>
                                                                    <td>{{dt.exam_type}}</td>
                                                                    <td>{{dt.exam_year}}</td>
                                                                    <td>{{dt.exam_no}}</td>
                                                                    <td>{{dt.date}}</td>
                                                                    <td>
                                                                        <div class="alert-info" ng-bind-html="dt.return_msg"></div>
                                                                    </td>
                                                                    <td> 
                                                                        <a ng-if="dt.approve == 'No' && dt.treated == 'Yes'" 
                                                                           ng-click="setSelectedItem(dt)" 
                                                                           class="btn btn-small btn-warning" 
                                                                           data-toggle="modal"
                                                                           role="button" 
                                                                           href="#modal-1">Edit</a>               
                                                                    </td>
                                                                </tr>

                                                            </tbody>    
                                                        </table> 
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if($row_result['status'] != '0' ){ ?>
                                            <h4>Verification Code / Status</h4>
                                            <div class="well well-small">
                                                <div class='alert alert-info' style="text-align: center" >
                                                    <?= $status; ?>
                                                </div>
                                                <?php if ($row_result['verified'] == "FALSE") { ?>
                                                    <div class='alert' style="text-align: center" >
                                                        <?php if ($row_result['status'] == "release" && $row_result['verified'] == "FALSE") { ?>
                                                            <p style=' color: green; font-size: 20px; font-weight: bold'> <?= $row_result['ver_code'] ?> </p>
                                                        <?php } ?>

                                                        <?php if ($row_result['msg'] != "") { ?>
                                                            <div class="alert alert-info">
                                                                <p><?= $row_result['msg'] ?></p>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                <?php } ?>
                                                <!--Change \programme--> 
                                                <div class="row-fluid">
                                                    <?php if ( $row_result['status'] == 'change_prog') { ?>
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
                                                    <?php if ($row_result['verified'] == "FALSE" && $row_result['status'] == 'release') { ?>
                                                        <div class="span6">
                                                            <form  class="form form-horizontal" name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>"> 
                                                                <div class="control-group">
                                                                    <label class="control-label" for="textfield">Verification Code</label>
                                                                    <div class="controls">
                                                                        <div class="input-append">
                                                                            <input type="text" class="input-block-level"  id="textfield" name="ver_code">
                                                                            <input type="hidden" name="verify" value="verify">
                                                                            <button type='submit' name='submit' class="btn btn-purple">Verify</button>
                                                                        </div>   
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    <?php } else { ?>
                                                        <?php if ($row_result['verified'] == "TRUE" && $row_result['status'] == 'release') { ?>
                                                            <a href="../../admission/fee_payment/index.php" class="btn btn-primary">Pay School Fee</a>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <?php }?>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <div class="row-fluid">
                                            <div class="span12">
                                                <div class="well well-small">
                                                    <div class="alert alert-info">
                                                        <i class="glyphicon-circle_info"></i> 
                                                        You are to pay for your O'Level Result Verification Click the proceed button bellow to continue
                                                    </div>
                                                    <a href="status.php" class='btn btn-primary'>Proceed </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    
                                </div>
                            </div>
                            <p>&nbsp;</p>   
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH . "/footer.php" ?>
        
        <script>
            var data = <?= json_encode($data) ?>;
          
            var app = angular.module('app', ['ngSanitize']);
            app.controller('pageCtrl', function($scope){
                $scope.submission = data;
                $scope.selectedItem = '';
                
                $scope.setSelectedItem = function(item){
                    $scope.selectedItem = item;
                };
            });
        </script>
        
        <div id="modal-1" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="width: 600px">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">Edit O'Level Result Details</h3>
            </div>
            <form method="post" action="index.php">
		<div class="modal-body">
                    <div class="well well-small">
                        <div class="row-fluid">
                            <div class="form-horizontal form-striped">
                                <div class="control-group">
                                    <label class="control-label" for="textfield">Exam Type</label>
                                    <div class="controls">
                                        <select name='exam_type' class="input-large" required>
                                            <option value="">--Choose--</option>
                                            <option value="WASSCE (May/Jun)" ng-selected="selectedItem.exam_type == 'WASSCE (May/Jun)'">WASSCE (May/Jun)</option>
                                            <option value="WASSCE (Nov/Dec)" ng-selected="selectedItem.exam_type == 'WASSCE (Nov/Dec)'">WASSCE (Nov/Dec)</option>
                                            <option value="NECO (May/Jun)" ng-selected="selectedItem.exam_type == 'NECO (May/Jun)'">NECO (May/Jun)</option>
                                            <option value="NECO (Nov/Dec)" ng-selected="selectedItem.exam_type == 'NECO (Nov/Dec)'">NECO (Nov/Dec) </option>
                                            <option value="NABTEB (May/Jun)" ng-selected="selectedItem.exam_type == 'NABTEB (May/Jun)'">NABTEB (May/Jun)</option>
                                            <option value="NABTEB (Nov/Dec)" ng-selected="selectedItem.exam_type == 'NABTEB (Nov/Dec)'">NABTEB (Nov/Dec)</option>
                                            <option value="NABTEB Mod.(Mar)" ng-selected="selectedItem.exam_type == 'NABTEB Mod.(Mar)'">NABTEB Mod.(Mar)</option>
                                            <option value="NABTEB Mod.(Dec)" ng-selected="selectedItem.exam_type == 'NABTEB Mod.(Dec)'">NABTEB Mod.(Dec)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="textfield">Exam Year</label>
                                    <div class="controls">
                                        <select name='exam_year' class="input-large" required>
                                            <option value="">--Choose--</option>
                                            <?php
                                            $date = date('Y');
                                            for ($i = 0; $i < 25; $i++) {
                                                ?>
                                            <option value="<?= $date - $i ?>" ng-selected="selectedItem.exam_year == <?= $date - $i; ?>"> <?= $date - $i ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="textarea">Exam No</label>
                                    <div class="controls">
                                        <input type="text" value="{{selectedItem.exam_no}}" class="input-large" name='exam_no' maxlength="10" required="">
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="textarea">Card Pin</label>
                                    <div class="controls">
                                        <input type="text" value="{{selectedItem.card_pin}}" name='card_pin' class="input-large" required="">
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="textarea">Card Serial No.</label>
                                    <div class="controls">
                                        <input type="text" value="{{selectedItem.card_no}}" name='card_no' class="input-large" required="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
		</div>
		<div class="modal-footer">
                        <input type="hidden" name="MM_Update" value="form2">
                        <input type="hidden" name="edit_id" value="{{selectedItem.id}}">
			<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                        <button class="btn btn-primary" type="submit">Update changes</button>
		</div>
            </form>
	</div>
        
        
        <div id="modal-2" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="width: 600px">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">Extra O'Level Result Details</h3>
            </div>
            <form method="post" action="index.php">
		<div class="modal-body">
                    <div class="well well-small">
                        <div class="row-fluid">
                            <div class="form-horizontal form-striped">
                                <div class="control-group">
                                    <label class="control-label" for="textfield">Exam Type</label>
                                    <div class="controls">
                                        <select name='exam_type' class="input-large" required>
                                            <option value="">--Choose--</option>
                                            <option value="WASSCE (May/Jun)" >WASSCE (May/Jun)</option>
                                            <option value="WASSCE (Nov/Dec)">WASSCE (Nov/Dec)</option>
                                            <option value="NECO (May/Jun)" >NECO (May/Jun)</option>
                                            <option value="NECO (Nov/Dec)" >NECO (Nov/Dec) </option>
                                            <option value="NABTEB (May/Jun)" >NABTEB (May/Jun)</option>
                                            <option value="NABTEB (Nov/Dec)" >NABTEB (Nov/Dec)</option>
                                            <option value="NABTEB Mod.(Mar)" >NABTEB Mod.(Mar)</option>
                                            <option value="NABTEB Mod.(Dec)">NABTEB Mod.(Dec)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="textfield">Exam Year</label>
                                    <div class="controls">
                                        <select name='exam_year' class="input-large" required>
                                            <option value="">--Choose--</option>
                                            <?php
                                            $date = date('Y');
                                            for ($i = 0; $i < 25; $i++) {
                                                ?>
                                            <option value="<?= $date - $i ?>"> <?= $date - $i ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="textarea">Exam No</label>
                                    <div class="controls">
                                        <input type="text" value="" class="input-large" name='exam_no' maxlength="10" required="">
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="textarea">Card Pin</label>
                                    <div class="controls">
                                        <input type="text" value="" name='card_pin' class="input-large" required="">
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="textarea">Card Serial No.</label>
                                    <div class="controls">
                                        <input type="text" value="" name='card_no' class="input-large" required="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
		</div>
		<div class="modal-footer">
                    <input type="hidden" name="MM_Extra" value="extra">
                    <input type="hidden" name='sesid' value="<?= getSessionValue('sesid') ?>"/>
                    <input type="hidden" name='utype' value="<?= getSessionValue('accttype') ?>"/>
                    <input type="hidden" name='level' value="<?php echo (getAccess() == 10) ? $row_rspros['level'] : "0" ?>"/>
                    <input type="hidden" name='progid' value="<?php echo (getAccess() == 10) ? $row_rspros['progid'] : $row_rspros['progoffered'] ?>"/>
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn btn-primary" type="submit">Submit</button>
		</div>
            </form>
	</div>
    </body>
</html>

