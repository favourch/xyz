<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

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


$Result1 = FALSE;

if ((isset($_POST["MM_Insert"])) && ($_POST["MM_Insert"] == "installment")) {
    
    $inst = $_POST['installment'];
    
    
    if(($inst['inst1'] + $inst['inst2']) != 100){
        
       $message = "Ensure that the sum of the 2 instalment is equal to 100" ;
       $notification->set_notification($message, '');
    }else{
        
        $inserSQL = sprintf("INSERT INTO "
                . "installment (sesid, instal1, instal2) "
                . "VALUES(%s, %s, %s)",
                GetSQLValueString($inst['sesid'], "int"),
                GetSQLValueString($inst['inst1'], "int"), 
                GetSQLValueString($inst['inst2'], "int"));
        $Result1 = mysql_query($inserSQL, $tams) or $notification->set_notification(mysql_error(), 'error');
    }
    
    
    if ( $Result1 == TRUE){
        $message = "Operation Successful" ;
        $notification->set_notification($message, 'success');
    }    
    else{
        
        $message = "Operation NOT Successful";
        $notification->set_notification($message, 'error');
    }
        

}

$insertSQL = '';

if((isset($_POST["MM_Insert"])) && ($_POST["MM_Insert"] == "schedule")){
    
    $form_data = $_POST['schedule'];
    
    $insertVal = array();
    $Result2 = '';
    
    if($form_data['usertype'] == 1){
        
        for ($idx = 0; $idx < count($form_data['entry']); $idx++) {

            $insertVal[$idx] = sprintf("( %s, %s, %s, %s, %s, %s, %s, %s, %s )", 
                                        GetSQLValueString($form_data['sesid'], 'int'), 
                                        GetSQLValueString('0', "text"), 
                                        GetSQLValueString($form_data['entry'][$idx], "text"),
                                        GetSQLValueString(0, "int"),
                                        GetSQLValueString($form_data['amount'][$idx], "text"),
                                        GetSQLValueString($form_data['penalty'][$idx], "text"),
                                        GetSQLValueString($form_data['status'][$idx], "text"),
                                        GetSQLValueString($form_data['payhead'][$idx], "text"),
                                        GetSQLValueString($form_data['revhead'][$idx], "text"));
        }

        $final = implode(",", $insertVal);

        $insertSQL = sprintf("INSERT INTO payschedule "
                            . "(sesid, level, admid,"
                            . "minpay,  amount,  penalty,"
                            . "status, payhead, revhead) "
                            . "VALUES %s", 
                            $final);
        
    }
    else{
        
        for ($idx = 0; $idx < count($form_data['entry']); $idx++) {

            $insertVal[$idx] = sprintf("( %s, %s, %s, %s, %s, %s, %s, %s, %s )", 
                                        GetSQLValueString($form_data['sesid'], 'int'), 
                                        GetSQLValueString($form_data['level'][$idx], "text"), 
                                        GetSQLValueString($form_data['entry'][$idx], "text"),
                                        GetSQLValueString(0, "int"),
                                        GetSQLValueString($form_data['amount'][$idx], "text"),
                                        GetSQLValueString($form_data['penalty'][$idx], "text"),
                                        GetSQLValueString($form_data['status'][$idx], "text"),
                                        GetSQLValueString($form_data['payhead'][$idx], "text"),
                                        GetSQLValueString($form_data['revhead'][$idx], "text"));
        }

        $final = implode(",", $insertVal);

        $insertSQL = sprintf("INSERT INTO payschedule "
                            . "(sesid, level, admid,"
                            . "minpay,  amount,  penalty,"
                            . "status, payhead, revhead) "
                            . "VALUES %s", 
                            $final);  
    }
    
    
    $Result1 = mysql_query($insertSQL, $tams) or $notification->set_notification(mysql_error(), 'error');
    
    if ( $Result1 == TRUE){
        $message = "Operation Successful" ;
        $notification->set_notification($message, 'success');
    }    
    else{
        
        $message = "Operation NOT Successful";
        $notification->set_notification($message, 'error');
    }
}



mysql_select_db($database_tams, $tams);

$query_schedule = sprintf("SELECT * FROM payschedule ORDER BY sesid DESC");
$sched = mysql_query($query_schedule, $tams) or die(mysql_error());
$row_colschdl = mysql_fetch_assoc($sched);
$row_num_colschdl = mysql_num_rows($sched);


$query_admtype = sprintf("SELECT * FROM admission_type");
$admtype = mysql_query($query_admtype, $tams) or die(mysql_error());
$row_admtype = mysql_fetch_assoc($admtype);


$query_instlmnt = sprintf("SELECT * FROM installment JOIN session USING (sesid)");
$instl = mysql_query($query_instlmnt, $tams) or die(mysql_error());
$row_instlmnt = mysql_fetch_assoc($instl);
$row_num_instlmnt = mysql_num_rows($instl);


$query_ses = sprintf("SELECT * FROM session ORDER BY sesid DESC");
$ses = mysql_query($query_ses, $tams) or die(mysql_error());
$row_ses = mysql_fetch_assoc($ses);

$deptname = "";

//$notification->set_notification("Hello2", 'success');

        
function getPayHead($abrev){
    
    $param = array(
        'app' => "Application Fee",
        'acc' => "Acceptance Fee",
        'sch' => "School Fee",
        'dpt' => "Departmental Fee",
        'jou' => "Journal Fee",
        'ins' => "Insurance Fee",
        'dmg' => "Damages Fee",
    );
    
    return $param[$abrev];
}

?>
<!doctype html>
<html ng-app="tams-app">
    <?php include INCPATH."/header.php" ?>

    <body ng-controller="PageController" data-layout-sidebar="fixed" data-layout-topbar="fixed">
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
                            <div class="box box-color box-bordered">
                                <div class="box-title">
                                    <h3>
                                        <i class="icon-money"></i>
                                       Payment Management
                                    </h3>
                                    <ul class="tabs">
                                        <li class="active">
                                            <a data-toggle="tab" href="#t7">Set Installment</a>
                                        </li>
                                        <li class="">
                                            <a data-toggle="tab" href="#t8">Set Payment Schedule</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="box-content">
                                    <div class="tab-content">
                                        <div id="t7" class="tab-pane active">
                                            <h4>Set Installment</h4>
                                            <div id="accordion2" class="accordion">
                                                <div class="accordion-group">
                                                    <div class="accordion-heading">
                                                        <a href="#collapseOne" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                            Create new Installment Type
                                                        </a>
                                                    </div>
                                                    <div class="accordion-body collapse" id="collapseOne" style="height: 0px;">
                                                        <div class="accordion-inner">
                                                            <form class="form-horizontal  form-validate" action="<?= $editFormAction ?>" method="post">
                                                                <div class="control-group">
                                                                    <label class="control-label" for="textfield">Session</label>
                                                                    <div class="controls">
                                                                        <select name="installment[sesid]" required="">
                                                                            <option value="">-- Choose --</option>
                                                                            <?php do{?>
                                                                            <option value="<?= $row_ses['sesid']?>"><?= $row_ses['sesname']?></option>
                                                                            <?php }while($row_ses = mysql_fetch_assoc($ses));
                                                                                    $rows = mysql_num_rows($ses);
                                                                                    if ($rows > 0) {
                                                                                        mysql_data_seek($ses, 0);
                                                                                        $row_ses = mysql_fetch_assoc($ses);
                                                                                    }?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="control-group">
                                                                    <label class="control-label" for="textfield">1st Installment</label>
                                                                    <div class="controls">
                                                                        <input type="number" class="input-small" id="textfield" name="installment[inst1]">
                                                                    </div>
                                                                </div>
                                                                <div class="control-group">
                                                                    <label class="control-label" for="textfield">2nd Installment</label>
                                                                    <div class="controls">
                                                                        <input type="number" class="input-small" id="textfield" name="installment[inst2]">
                                                                    </div>
                                                                </div>
                                                                <input type="hidden" name="MM_Insert" value="installment">
                                                                <div class="form-actions">
                                                                    <input type="submit" value="create Installment" class="btn btn-primary" >
                                                                    <button class="btn" type="button">Cancel</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p>&nbsp;</p>
                                                <div class="row-fluid">
                                                    <?php if($row_num_instlmnt > 0){?>
                                                        <table class="table table-condensed table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>S/n</th>
                                                                    <th>Session</th>
                                                                    <th>1st Installment</th>
                                                                    <th>2nd Installment</th>
                                                                    <th>&nbsp;</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $i= 1; do{?>
                                                                <tr>
                                                                    <td><?= $i++; ?></td>
                                                                    <td><?= $row_instlmnt['sesname'] ?></td>
                                                                    <td><?= $row_instlmnt['instal1']?></td>
                                                                    <td><?= $row_instlmnt['instal2']?></td>
                                                                    <td> <a href="#" class="btn"> <i class="icon-cogs"></i> Edit</a></td>
                                                                </tr>
                                                                <?php }while($row_instlmnt = mysql_fetch_assoc($instl));?>
                                                            </tbody>

                                                        </table>
                                                    <?php }else{?>
                                                    <div class="alert alert-error">No Record Found!</div>
                                                    <?php }?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div id="t8" class="tab-pane">
                                            <h4>Set Payment Schedule</h4>
                                            <div id="accordion2" class="accordion">
                                                <div class="accordion-group">
                                                    <div class="accordion-heading">
                                                        <a href="#collapseTwo" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                            Create Pay Schedule
                                                        </a>
                                                    </div>
                                                    <div class="accordion-body collapse" id="collapseTwo" style="height: 0px;">
                                                        <div class="accordion-inner">
                                                            <form class="form-vertical form-validate" action="<?= $editFormAction ?>" method="post">
                                                                <div class="row-fluid">
                                                                    <div class="span4">
                                                                        <div class="control-group">
                                                                            <div class="input-prepend">
                                                                                <span class="add-on">Number of Fields </span>
                                                                                <input type="number" min="1" class="input-small" ng-model="unit">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="span4">
                                                                        <div class="control-group">
                                                                            <div class="input-prepend">
                                                                                <span class="add-on">Session  </span>
                                                                                <select name="schedule[sesid]" required="">
                                                                                    <option value="">-- Choose --</option>
                                                                                    <?php do{?>
                                                                                    <option value="<?= $row_ses['sesid']?>"><?= $row_ses['sesname']?></option>
                                                                                    <?php }while($row_ses = mysql_fetch_assoc($ses));
                                                                                            $rows = mysql_num_rows($ses);
                                                                                            if ($rows > 0) {
                                                                                                mysql_data_seek($ses, 0);
                                                                                                $row_ses = mysql_fetch_assoc($ses);
                                                                                            }?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="span4">
                                                                        <div class="control-group">
                                                                            <div class="input-prepend input-medium">
                                                                                <span class="add-on">User Type {{type}}</span>
                                                                                <select name="schedule[usertype]" required="" ng-model="type">
                                                                                    <option value="">-- Choose --</option>
                                                                                    <option value="1">Prospective</option>
                                                                                    <option value="2">Returning</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <table class="table table-condensed table-striped table-hover">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>S/N</th>
                                                                            <th ng-if="type == 2">Level</th>
                                                                            <th>Entry Mode</th>
                                                                            <th>Amount</th>
                                                                            <th>Penalty</th>
                                                                            <th>Pay.Head</th>
                                                                            <th ng-if="type">Status</th>
                                                                            <th>Revenue Head</th>
                                                                        </tr>   
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr ng-repeat="d in dt">
                                                                            <td>{{$index +1 }}</td>
                                                                            <td ng-if="type == 2">
                                                                                <div class="span2">
                                                                                    <div class="control-group">
                                                                                        <div class="controls controls-row" >
                                                                                            <select name='schedule[level][]' class="input-small">
                                                                                                <option value="1">100</option>
                                                                                                <option value="2">200</option>
                                                                                                <option value="3">300</option>
                                                                                                <option value="4">400</option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <div class="span2">
                                                                                    <div class="control-group">
                                                                                        <div class="controls controls-row">
                                                                                            <select name="schedule[entry][]" class="input-small">
                                                                                                <option value="">-- Choose --</option>
                                                                                                <?php do{?>
                                                                                                <option value="<?= $row_admtype['typeid']?>"><?= $row_admtype['typename']?></option>
                                                                                                <?php }while($row_admtype = mysql_fetch_assoc($admtype))?>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <div class="span2">
                                                                                    <div class="control-group">
                                                                                        <div class="controls controls-row">
                                                                                            <input type="number" class="input-small"  name="schedule[amount][]" min="0">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <div class="span2">
                                                                                    <div class="control-group">
                                                                                        <div class="controls controls-row">
                                                                                            <input type="number" class="input-small"  name="schedule[penalty][]" min="0">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <div class="span2">
                                                                                    <div class="control-group">
                                                                                        <div class="controls controls-row">
                                                                                            <select name="schedule[payhead][]" class="input-small">
                                                                                                <option value="">-- Choose --</option>
                                                                                                <option value="app">Application Fee</option>
                                                                                                <option value="acc">Acceptance Fee</option>
                                                                                                <option value="sch">School Fee</option>
                                                                                                <option value="dpt">Departmental Fee</option>
                                                                                                <option value="jou">Journal Fee</option>
                                                                                                <option value="ins">Insurance Fee</option>
                                                                                                <option value="dmg">Damages Fee</option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td ng-if="type">
                                                                                <div class="span2">
                                                                                    <div class="control-group">
                                                                                        <div class="controls controls-row">
                                                                                            <select name="schedule[status][]" class="input-small" ng-if="type == 2">
                                                                                                <option value="">-- Choose --</option>
                                                                                                <option value="indigene">Indigene</option>
                                                                                                <option value="nonindigene">Non-Indigene</option>
                                                                                            </select>
                                                                                            <select name="schedule[status][]" class="input-small" ng-if="type == 1">
                                                                                                <option value="">-- Choose --</option>
                                                                                                <option value="coi">COI Applicant</option>
                                                                                                <option value="regular">Regular Applicant</option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <div class="span2">
                                                                                    <div class="control-group">
                                                                                        <div class="controls controls-row">
                                                                                            <input type="text" class="input-small"  name="schedule[revhead][]">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                                <input type='hidden' name="MM_Insert" value="schedule">
                                                                <div class="form-actions">
                                                                    <input type="submit" value="create Schedule" class="btn btn-primary" >
                                                                    <button class="btn" type="button">Cancel</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <h4>Payment Schedules </h4>
                                            <div class="row-fluid">
                                                <?php if($row_num_colschdl > 0){?>
                                                <table class="table table-condensed table-hover table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>S/N</th>
                                                            <th>Level</th>
                                                            <th>Entry Mode</th>
                                                            <th>Amount (NGN)</th>
                                                            <th>Penalty (NGN)</th>
                                                            <th>Status</th>
                                                            <th>Pay. Head</th>
                                                            <th>Revenue Head</th>
                                                            <th>&nbsp;</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $i=1; do{?>
                                                        <tr>
                                                            <td><?= $i++; ?></td>
                                                            <td><?= ($row_colschdl['level'])? $row_colschdl['level'].'00L' : "-" ?></td>
                                                            <td><?= $row_colschdl['entrymode'] ?></td>
                                                            <td><?= number_format($row_colschdl['amount'], 2) ?></td>
                                                            <td><?= number_format($row_colschdl['penalty'], 2) ?></td>
                                                            <td><?= $row_colschdl['status'] ?></td>
                                                            <td><?= getPayHead($row_colschdl['payhead']) ?></td>
                                                            <td><?= $row_colschdl['revhead'] ?></td>
                                                            <td><a href="edit_schedule.php?id=<?=$row_colschdl['scheduleid']?>" class="btn btn-small btn-purple">Edit</a></td>
                                                        </tr>
                                                        <?php }while($row_colschdl = mysql_fetch_assoc($sched));?>
                                                    </tbody>

                                                </table>
                                                <?php }else{?>
                                                <div class="alert alert-error">No Record Found!</div>
                                                <?php }?>
                                            </div>
                                        </div>
                                    </div>
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
    
    <script src="/<?= $site_root?>/js/angular/angular.min.js"></script>
    <script src="/<?= $site_root?>/js/angular/angular-payment.js"></script>
</html>

