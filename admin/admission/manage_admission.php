<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "1,20";
check_auth($auth_users, $site_root.'/admin');

$sesid = $_SESSION['admid'];
$sesname = $_SESSION['admname'];

if(isset($_POST['admission'])) {
    unset($_POST['admission']);    
    
    $valid = true;
    foreach ($_POST as $key => $value) {
        if ($value == '' || $value == NULL) {
            $valid = false;
            break;
        }
    }

    if ($valid) {        
        $insertSQL = sprintf("INSERT INTO admissions (typeid, sesid, status)"
                . " VALUES (%s, %s, %s)", 
                GetSQLValueString($_POST['typeid'], "text"), 
                GetSQLValueString($sesid, "int"), 
                GetSQLValueString($_POST['status'], "text"));
        $Result1 = mysql_query($insertSQL, $tams);
        
        if (mysql_errno() != 0) {
            $not_msg = "There was a problem creating the Admission. Please ensure that a session is active for Admission!";
        }
        
    } else {
        $not_msg = "The Admission could not be created. Invalid field values!";
    }
    
}

if(isset($_POST['editadm'])) {
    unset($_POST['editadm']);    
    
    $valid = true;
    foreach ($_POST as $key => $value) {
        if ($value == '' || $value == NULL) {
            $valid = false;
            break;
        }
    }

    if ($valid) {        
        $insertSQL = sprintf("UPDATE admissions SET typeid = %s, status = %s WHERE admid = %s", 
                GetSQLValueString($_POST['edittypeid'], "text"), 
                GetSQLValueString($_POST['editstatus'], "text"), 
                GetSQLValueString($_POST['admid'], "text"));
        $Result1 = mysql_query($insertSQL, $tams);
        
        if (mysql_errno() != 0) {
            $not_msg = "There was a problem updating the Admission!";
        }
        
    } else {
        $not_msg = "The Admission could not be updated. Invalid field values!";
    }
    
}

if(isset($_POST['admtype'])) {
    unset($_POST['admtype']);

    $valid = true;
    foreach ($_POST as $key => $value) {
        if ($value == '' || $value == NULL) {
            $valid = false;
            break;
        }
    }

    if ($valid) {
        $insertSQL = sprintf("INSERT INTO admission_type (typename, displayname, typedesc)"
                . " VALUES (%s, %s, %s)", 
                GetSQLValueString($_POST['typename'], "text"), 
                GetSQLValueString($_POST['displayname'], "text"), 
                GetSQLValueString($_POST['typedesc'], "text"));
        $Result1 = mysql_query($insertSQL, $tams);
        
        if (mysql_errno() != 0) {
            $not_msg = "There was a problem creating the Admission Type. Please try again!";
        }
    } else {
        $not_msg = "The Admission Type could not be created. Invalid field values!";
    }
}

if(isset($_POST['editadmtype'])) {
    unset($_POST['editadmtype']);

    $valid = true;
    foreach ($_POST as $key => $value) {
        if ($key == 'editdesc') {
            continue;
        }
        
        if ($value == '' || $value == NULL) {
            $valid = false;
            break;
        }
    }

    if ($valid) {
        $insertSQL = sprintf("UPDATE admission_type SET typename = %s, displayname = %s, typedesc = %s WHERE typeid = %s", 
                GetSQLValueString($_POST['editname'], "text"), 
                GetSQLValueString($_POST['editdisp'], "text"), 
                GetSQLValueString($_POST['editdesc'], "text"), 
                GetSQLValueString($_POST['editid'], "text"));
        $Result1 = mysql_query($insertSQL, $tams);
        
        if (mysql_errno() != 0) {
            $not_msg = "There was a problem updating the Admission Type. Please try again!";
        }
    } else {
        $not_msg = "The Admission Type could not be updated. Invalid field values!";
    }
}

if(isset($_POST['appbatch'])) {
    unset($_POST['appbatch']);

    $valid = true;
    foreach ($_POST as $key => $value) {
        if ($value == '' || $value == NULL) {
            $valid = false;
            break;
        }
    }

    if ($valid) {
        
        $updateStatus = 0;
        mysql_query("START TRANSACTION;");
        
        if($_POST['status'] == 'active') {
            $updateSQL = sprintf("UPDATE application_batch SET status = 'inactive' WHERE admid = %s", 
                GetSQLValueString($_POST['admid'], "text"));
            $updateResult = mysql_query($updateSQL, $tams);
            $updateStatus = mysql_errno();
        }
        
        $insertSQL = sprintf("INSERT INTO application_batch (batchname, admid, status)"
                . " VALUES (%s, %s, %s)", 
                GetSQLValueString($_POST['batchname'], "text"), 
                GetSQLValueString($_POST['admid'], "text"), 
                GetSQLValueString($_POST['status'], "text"));
        $Result1 = mysql_query($insertSQL, $tams);
        
        if ($updateStatus == 0 && mysql_errno() == 0) {
            mysql_query("COMMIT;");
        }else {
            mysql_query("ROLLBACK;");
            $not_msg = "There was a problem creating the Application batch. Please try again!";
        }
    } else {
        $not_msg = "The Application batch could not be created. Invalid field values!";
    }
}

if(isset($_POST['editappbatch'])) {
    unset($_POST['editappbatch']);

    $valid = true;
    foreach ($_POST as $key => $value) {
        if ($value == '' || $value == NULL) {
            $valid = false;
            break;
        }
    }

    if ($valid) {
        
        $updateStatus = 0;
        mysql_query("START TRANSACTION;");
            
        if($_POST['status'] == 'active') {            
            $updateSQL = sprintf("UPDATE application_batch SET status = 'inactive' WHERE admid = %s", 
                GetSQLValueString($_POST['admid'], "text"));
            $updateResult = mysql_query($updateSQL, $tams);
            $updateStatus = mysql_errno();
        }
        
        $insertSQL = sprintf("UPDATE application_batch SET batchname = %s, admid = %s, status = %s WHERE appbatchid = %s", 
                GetSQLValueString($_POST['batchname'], "text"), 
                GetSQLValueString($_POST['admid'], "text"), 
                GetSQLValueString($_POST['status'], "text"), 
                GetSQLValueString($_POST['editid'], "text"));
        $Result1 = mysql_query($insertSQL, $tams);
        
        if ($updateStatus == 0 && mysql_errno() == 0) {
            mysql_query("COMMIT;");
        }else {
            mysql_query("ROLLBACK;");
            $not_msg = "There was a problem creating the Application batch. Please try again!";
        }
    } else {
        $not_msg = "The Application batch could not be created. Invalid field values!";
    }
}

if(isset($_POST['regtype'])) {
    unset($_POST['regtype']);

    $valid = true;
    foreach ($_POST as $key => $value) {
        if ($value == '' || $value == NULL) {
            $valid = false;
            break;
        }
    }

    if ($valid) {
        $insertSQL = sprintf("INSERT INTO registration_type (regtypename, displayname, typedesc)"
                . " VALUES (%s, %s, %s)", 
                GetSQLValueString($_POST['typename'], "text"), 
                GetSQLValueString($_POST['displayname'], "text"), 
                GetSQLValueString($_POST['typedesc'], "text"));
        $Result1 = mysql_query($insertSQL, $tams);
        
        if (mysql_errno() != 0) {
            $not_msg = "There was a problem creating the Registration Type. Please try again!";
        }
    } else {
        $not_msg = "The Registration Type could not be created. Invalid field values!";
    }
}

if(isset($_POST['editregtype'])) {
    unset($_POST['editregtype']);
    
    $valid = true;
    foreach ($_POST as $key => $value) {
        if ($key == 'editdesc') {
            continue;
        }
        
        if ($value == '' || $value == NULL) {
            $valid = false;
            break;
        }
    }

    if ($valid) {
        $insertSQL = sprintf("UPDATE registration_type SET regtypename = %s, displayname = %s, typedesc = %s WHERE regtypeid = %s", 
                GetSQLValueString($_POST['editname'], "text"), 
                GetSQLValueString($_POST['editdisp'], "text"), 
                GetSQLValueString($_POST['editdesc'], "text"), 
                GetSQLValueString($_POST['editid'], "text"));
        $Result1 = mysql_query($insertSQL, $tams);
        
        if (mysql_errno() != 0) {
            $not_msg = "There was a problem updating the Registration Type. Please try again!";
        }
    } else {
        $not_msg = "The Registration Type could not be updated. Invalid field values!";
    }
}

$query_admtype = "SELECT * FROM admission_type";
$admtype = mysql_query($query_admtype, $tams) or die(mysql_error());
$totalRows_admtype = mysql_num_rows($admtype);

$query_admission = "SELECT a.*, s.sesname, at.typeid, at.typename "
                    . "FROM admissions a "
                    . "LEFT JOIN admission_type at ON a.typeid = at.typeid "
                    . "LEFT JOIN session s ON a.sesid = s.sesid "
                    . "ORDER BY sesid DESC";
$admission = mysql_query($query_admission, $tams) or die(mysql_error());
$totalRows_admission = mysql_num_rows($admission);

$query_appbatch = "SELECT appbatchid, batchname, typename, sesname, b.status, a.admid "
        . "FROM application_batch b "
        . "JOIN admissions a ON b.admid = a.admid "
        . "JOIN session s ON a.sesid = s.sesid "
        . "JOIN admission_type at ON a.typeid = at.typeid "
        . "ORDER BY appbatchid DESC";
$appbatch = mysql_query($query_appbatch, $tams) or die(mysql_error());
$totalRows_appbatch = mysql_num_rows($appbatch);

$query_regtype = "SELECT * "
        . "FROM registration_type";
$regtype = mysql_query($query_regtype, $tams) or die(mysql_error());
$totalRows_regtype = mysql_num_rows($regtype);
?>
<!doctype html>
<html ng-app="tams">
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="PageController">
        <?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH."/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH."/page_header.php" ?>
                                        
                    <div class="row-fluid">
                        <div class="box box-bordered box-color">
                            <div class="box-title">
                                <h3>
                                    <i class="icon-reorder"></i>
                                    Admission Management
                                </h3>

                                <ul class="tabs">
                                    <li class="active">
                                        <a data-toggle="tab" href="#admissions">Admissions</a>
                                    </li>
                                    <li>
                                        <a data-toggle="tab" href="#admtypes">Admission Types</a>
                                    </li>
                                    <li>
                                        <a data-toggle="tab" href="#appbatch">Application Batches</a>
                                    </li>
                                    <li>
                                        <a data-toggle="tab" href="#regtype">Registration Type</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="box-content">  
                                <div class="tab-content">
                                    <div id="admissions" class="tab-pane active">
                                        <div class="row-fluid pad-1">
                                            <a data-toggle='modal' href='#create_adm' class="btn btn-primary">
                                                Create A New Admission
                                            </a>
                                        </div>
                                        <div class="row-fluid">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Type Name</th>
                                                        <th>Session</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>  
                                                </thead>
                                                <tbody>
                                                    <?php if ($totalRows_admission > 0) :
                                                        for (;$row_admission = mysql_fetch_assoc($admission);) :?>
                                                    <tr>
                                                        <td>
                                                            <?php echo $row_admission['typename'] ?>
                                                        </td>
                                                        <td><?php echo $row_admission['sesname'] ?></td>
                                                        <td><?php echo $row_admission['status'] == 'TRUE'? 'OPEN':'CLOSED' ?></td>
                                                        <td>
                                                            <a href="../../admission/printadmletter.php?type=<?php echo $row_admission['admid']?>" target="_blank" class="btn btn-primary">
                                                                View Letter
                                                            </a>
                                                            <a href="editadmletter.php?type=<?php echo $row_admission['admid']?>" target="_blank" class="btn btn-primary">
                                                                Edit Letter
                                                            </a>
                                                            <a ng-click="edit($event, 'edit_admission', ['<?php echo $row_admission['typeid']?>','<?php echo $row_admission['admid']?>','<?php echo $row_admission['typename'] ?>','<?php echo $row_admission['sesname'] ?>','<?php echo $row_admission['status'] ?>'])" data-toggle="modal" class="btn btn-primary">
                                                                Edit Admission
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php endfor;
                                                            mysql_data_seek($admission, 0);
                                                        else :?>
                                                    <tr>
                                                        <td colspan="4">No Admission has been created!</td>
                                                    </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>                                        
                                    </div>
                                    
                                    <div id="admtypes" class="tab-pane">
                                        <div class='row-fluid pad-1'>
                                            <a data-toggle='modal' href='#create_admtype' class="btn btn-primary">
                                                Create A New Admission Type
                                            </a>
                                        </div>
                                        <div class="row-fluid">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Type Name</th>
                                                        <th>Display Name </th>
                                                        <th>Description</th>
                                                        <th>Action</th>
                                                    </tr>  
                                                </thead>
                                                <tbody>
                                                    <?php if ($totalRows_admtype > 0) :
                                                        for (; $row_admtype = mysql_fetch_assoc($admtype);) :
                                                            ?>
                                                    <tr>
                                                        <td><?php echo $row_admtype['typename'] ?></td>
                                                        <td><?php echo $row_admtype['displayname'] ?></td>
                                                        <td><?php echo $row_admtype['typedesc'] ?></td>
                                                        <td>
                                                            <a ng-click="edit($event, 'edit_admtype', ['<?php echo $row_admtype['typeid'] ?>','<?php echo $row_admtype['typename'] ?>','<?php echo $row_admtype['displayname'] ?>','<?php echo $row_admtype['typedesc'] ?>'])" class="btn btn-primary">
                                                                Edit Admission Type
                                                            </a>
                                                            <a class="btn btn-primary" href="adm_prog.php?type=<?php echo $row_admtype['typeid']?>">
                                                                Edit Programmes
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php endfor;
                                                            mysql_data_seek($admtype, 0);
                                                           
                                                        else :
                                                    ?>
                                                    <tr>
                                                        <td colspan="4">No Admission Type has been created!</td>
                                                    </tr>
                                                    <?php endif;?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <div id="appbatch" class="tab-pane">
                                        <div class='row-fluid pad-1'>
                                            <a data-toggle='modal' href='#create_appbatch' class="btn btn-primary">
                                                Create A New Application Batch
                                            </a>
                                        </div>
                                        <div class="row-fluid">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Batch Name</th>
                                                        <th>Type Name </th>
                                                        <th>Session Name</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>  
                                                </thead>
                                                <tbody>
                                                    <?php if ($totalRows_appbatch > 0) :
                                                        for (; $row_appbatch = mysql_fetch_assoc($appbatch);) :
                                                            ?>
                                                    <tr>
                                                        <td><?php echo $row_appbatch['batchname'] ?></td>
                                                        <td><?php echo $row_appbatch['typename'] ?></td>
                                                        <td><?php echo $row_appbatch['sesname'] ?></td>
                                                        <td><?php echo ucfirst($row_appbatch['status']) ?></td>
                                                        <td>
                                                            <a ng-click="edit($event, 'edit_appbatch', ['<?php echo $row_appbatch['appbatchid'] ?>','<?php echo $row_appbatch['batchname'] ?>','<?php echo $row_appbatch['admid'] ?>','<?php echo $row_appbatch['sesname'] ?>','<?php echo $row_appbatch['status'] ?>'])" class="btn btn-primary">
                                                                Edit Application Batch
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php endfor;
                                                        else :
                                                    ?>
                                                    <tr>
                                                        <td colspan="4">No Application Batch has been created!</td>
                                                    </tr>
                                                    <?php endif;?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <div id="regtype" class="tab-pane">
                                        <div class='row-fluid pad-1'>
                                            <a data-toggle='modal' href='#create_regtype' class="btn btn-primary">
                                                Create A New Registration Type
                                            </a>
                                        </div>
                                        <div class="row-fluid">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Type Name</th>
                                                        <th>Display Name</th>
                                                        <th>Type Description</th>
                                                        <th></th>
                                                    </tr>  
                                                </thead>
                                                <tbody>
                                                    <?php if ($totalRows_regtype > 0) :
                                                        for (; $row_regtype = mysql_fetch_assoc($regtype);) :
                                                            ?>
                                                    <tr>
                                                        <td><?php echo $row_regtype['regtypename'] ?></td>
                                                        <td><?php echo $row_regtype['displayname'] ?></td>
                                                        <td><?php echo $row_regtype['typedesc'] ?></td>
                                                        <td>
                                                            <a ng-click="edit($event, 'edit_regtype', ['<?php echo $row_regtype['regtypeid'] ?>','<?php echo $row_regtype['regtypename'] ?>','<?php echo $row_regtype['displayname'] ?>','<?php echo $row_regtype['typedesc'] ?>'])" class="btn btn-primary">
                                                                Edit Registration Type
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php endfor;
                                                        else :
                                                    ?>
                                                    <tr>
                                                        <td colspan="4">No registration type has been created!</td>
                                                    </tr>
                                                    <?php endif;?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>  
            </div>
            <?php include INCPATH."/footer.php" ?>     
            
        </div>

        <div aria-hidden="false" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="modal hide fade" id="create_adm">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h3 id="myModalLabel">Set-Up A New Admission</h3>
            </div>
            <form method='post' id='form_adm' novalidate class="form-horizontal" action="">
                <div class="modal-body" style="min-height: 300px">
                    <div class="control-group">
                        <label for="typeid" class="control-label">Type Name:</label>
                        <div class="controls">
                            <div class="input-medium">
                            <select name="typeid" id="typeid" class='chosen-select' data-rule-required='true'>
                                <?php for (; $row_admtype = mysql_fetch_assoc($admtype);) : ?>
                                <option value='<?php echo $row_admtype['typeid'] ?>'>
                                    <?php echo $row_admtype['typename'] ?>
                                </option>
                                <?php endfor;
                                    mysql_data_seek($admtype, 0);?>
                            </select>
                            </div>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="sesid" class="control-label">Active Session:</label>
                        <div class="controls">
                            <?php echo $sesname?>
                            <input type="hidden" name="sesid" id="sesid" value='<?php echo $sesid ?>'>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="status" class="control-label">Status:</label>
                        <div class="controls">
                            <div class="input-small">
                            <select name="status" class="chosen-select" id="status" data-rule-required='true'>
                                <option value='TRUE'>Open</option>
                                <option value='FALSE'>Closed</option>
                            </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
                    <button type="submit" name="admission" class="btn btn-primary">Create Admission</button>
                </div>
            </form>
        </div>

        <div aria-hidden="false" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="modal hide fade" id="edit_admission">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h3 id="myModalLabel">Edit {{current.name}} Admission for {{current.session}}</h3>
            </div>
            <form method='post' id='form_editadm' novalidate class="form-horizontal" action="">
                <div class="modal-body" style="min-height: 300px">
                    <div class="control-group">
                        <label for="edittypeid" class="control-label">Type Name:</label>
                        <div class="controls">
                            <div class="input-medium">
                            <select name="edittypeid" id="edittypeid" class='chosen-select' data-rule-required='true'>
                                <?php for (; $row_admtype = mysql_fetch_assoc($admtype);) : ?>
                                <option value='<?php echo $row_admtype['typeid'] ?>' 
                                        ng-selected="current.type == '<?php echo $row_admtype['typeid'] ?>'">
                                <?php echo $row_admtype['typename'] ?>
                                </option>
                                <?php endfor; 
                                    mysql_data_seek($admtype, 0);?>
                            </select>
                            </div>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="sesid" class="control-label">Admission Session:</label>
                        <div class="controls">
                            {{current.session}}                            
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="editstatus" class="control-label">Status:</label>
                        <div class="controls">
                            <div class="input-small">
                            <select name="editstatus" class="chosen-select" id="editstatus" data-rule-required='true'>
                                <option value='TRUE' ng-selected="current.status == 'TRUE'">Open</option>
                                <option value='FALSE' ng-selected="current.status == 'FALSE'">Closed</option>
                            </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="admid" id="admid" value='{{current.admid}}'>
                    <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
                    <button type="submit" name="editadm" class="btn btn-primary">Edit Admission</button>
                </div>
            </form>
        </div>

        <div aria-hidden="false" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="modal hide fade" id="create_admtype">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h3 id="myModalLabel">Create A New Admission Type</h3>
            </div>
            <form method='post' id='form_admtype' novalidate class="form-horizontal" action="">
                <div class="modal-body" style="min-height: 300px">
                    <div class="control-group">
                        <label for="typename" class="control-label">Type Name:</label>
                        <div class="controls">
                            <input type="text" name="typename" id="typename" class="input-xlarge" 
                                   placeholder="e.g DE" data-rule-required="true">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="displayname" class="control-label">Display Name:</label>
                        <div class="controls">
                            <input type="text" name="displayname" id="displayname" class="input-xlarge" 
                                   placeholder="e.g Direct Entry" data-rule-required="true">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="typedesc" class="control-label">Description:</label>
                        <div class="controls">
                            <input type="text" name="typedesc" id="typedesc" class="input-xlarge">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
                    <button type="submit" name="admtype" class="btn btn-primary">Create Admission Type</button>
                </div>
            </form>
        </div>

        <div aria-hidden="false" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="modal hide fade" id="edit_admtype">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h3 id="myModalLabel">Edit {{current.name}} Admission Type</h3>
            </div>
            <form method='post' id='form_editadmtype' novalidate class="form-horizontal" action="">
                <div class="modal-body" style="min-height: 300px">
                    <div class="control-group">
                        <label for="editname" class="control-label">Type Name:</label>
                        <div class="controls">
                            <input type="text" name="editname" id="editname" class="input-xlarge" 
                                   placeholder="e.g DE" data-rule-required="true" value="{{current.name}}">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="editname" class="control-label">Display Name:</label>
                        <div class="controls">
                            <input type="text" name="editdisp" id="editdisp" class="input-xlarge" 
                                   placeholder="e.g Direct Entry" data-rule-required="true" value="{{current.dispname}}">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="editdesc" class="control-label">Description:</label>
                        <div class="controls">
                            <input type="text" name="editdesc" id="editdesc" class="input-xlarge" value="{{current.desc}}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="editid" id="editid" value='{{current.type}}'>
                    <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
                    <button type="submit" name="editadmtype" class="btn btn-primary">Edit Admission Type</button>
                </div>
            </form>
        </div>

        <div aria-hidden="false" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="modal hide fade" id="create_appbatch">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h3 id="myModalLabel">Create A New Application Batch</h3>
            </div>
            <form method='post' id='form_appbatch' novalidate class="form-horizontal" action="">
                <div class="modal-body" style="min-height: 300px">
                    <div class="control-group">
                        <label for="batchname" class="control-label">Batch Name:</label>
                        <div class="controls">
                            <input type="text" name="batchname" id="batchname" class="input-xlarge" 
                                   placeholder="e.g First batch" data-rule-required="true">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="admid" class="control-label">Type Name:</label>
                        <div class="controls">
                            <div class="input-medium">
                            <select name="admid" id="batchadmid" class='chosen-select' data-rule-required='true'>
                                <?php for (; $row_admission = mysql_fetch_assoc($admission), $row_admission['sesid'] == $sesid;) : ?>
                                <option value='<?php echo $row_admission['admid'] ?>'>
                                    <?php echo $row_admission['typename'] ?>
                                </option>
                                <?php endfor;
                                    mysql_data_seek($admission, 0);?>
                            </select>
                            </div>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="sesid" class="control-label">Active Session:</label>
                        <div class="controls">
                            <?php echo $sesname?>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="status" class="control-label">Status:</label>
                        <div class="controls">
                            <div class="input-small">
                            <select name="status" class="chosen-select" id="batchstatus" data-rule-required='true'>
                                <option value='active'>Active</option>
                                <option value='inactive' selected="selected">Inactive</option>
                            </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
                    <button type="submit" name="appbatch" class="btn btn-primary">Create Application Batch</button>
                </div>
            </form>
        </div>

        <div aria-hidden="false" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="modal hide fade" id="edit_appbatch">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h3 id="myModalLabel">Edit Application Batch</h3>
            </div>
            <form method='post' id='form_appbatch' novalidate class="form-horizontal" action="">
                <div class="modal-body" style="min-height: 300px">
                    <div class="control-group">
                        <label for="batchname" class="control-label">Batch Name:</label>
                        <div class="controls">
                            <input type="text" name="batchname" id="batchname" class="input-xlarge" 
                                   placeholder="e.g First batch" data-rule-required="true" value="{{current.batchname}}">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="typeid" class="control-label">Type Name:</label>
                        <div class="controls">
                            <div class="input-medium">
                            <select name="admid" id="editbatchadmid" class='chosen-select' data-rule-required='true'>
                                <?php for (; $row_admission = mysql_fetch_assoc($admission), $row_admission['sesid'] == $sesid;) : ?>
                                <option value='<?php echo $row_admission['admid'] ?>' 
                                        ng-selected="current.admid == '<?php echo $row_admission['admid'] ?>'">
                                    <?php echo $row_admission['typename'] ?>
                                </option>
                                <?php endfor;
                                    mysql_data_seek($admission, 0);?>
                            </select>
                            </div>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="sesid" class="control-label">Active Session:</label>
                        <div class="controls">
                            {{current.session}}
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="status" class="control-label">Status:</label>
                        <div class="controls">
                            <div class="input-small">
                            <select name="status" class="chosen-select" id="editbatchstatus" data-rule-required='true'>
                                <option value='active' ng-selected="current.status == 'active'">Active</option>
                                <option value='inactive' ng-selected="current.status == 'inactive'">Inactive</option>
                            </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="editid" id="editid" value='{{current.batchid}}'>
                    <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
                    <button type="submit" name="editappbatch" class="btn btn-primary">Edit Application Batch</button>
                </div>
            </form>
        </div>
        
        <div aria-hidden="false" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="modal hide fade" id="create_regtype">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h3 id="myModalLabel">Create A New Registration Type</h3>
            </div>
            <form method='post' id='form_regtype' novalidate class="form-horizontal" action="">
                <div class="modal-body" style="min-height: 300px">
                    <div class="control-group">
                        <label for="typename" class="control-label">Type Name:</label>
                        <div class="controls">
                            <input type="text" name="typename" id="typename" class="input-xlarge" 
                                   placeholder="e.g First Choice" data-rule-required="true">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="displayname" class="control-label">Display Name:</label>
                        <div class="controls">
                            <input type="text" name="displayname" id="displayname" class="input-xlarge" 
                                   placeholder="e.g Most Preferred Institution" data-rule-required="true">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="typedesc" class="control-label">Description:</label>
                        <div class="controls">
                            <input type="text" name="typedesc" id="typedesc" class="input-xlarge">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
                    <button type="submit" name="regtype" class="btn btn-primary">Create Registration Type</button>
                </div>
            </form>
        </div>

        <div aria-hidden="false" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="modal hide fade" id="edit_regtype">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h3 id="myModalLabel">Edit {{current.name}} Registration Type</h3>
            </div>
            <form method='post' id='form_editregtype' novalidate class="form-horizontal" action="">
                <div class="modal-body" style="min-height: 300px">
                    <div class="control-group">
                        <label for="editname" class="control-label">Type Name:</label>
                        <div class="controls">
                            <input type="text" name="editname" id="editname" class="input-xlarge" 
                                   placeholder="e.g First Choice" data-rule-required="true" value="{{current.name}}">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="editname" class="control-label">Display Name:</label>
                        <div class="controls">
                            <input type="text" name="editdisp" id="editdisp" class="input-xlarge" 
                                   placeholder="e.g Most Preferred Institution" data-rule-required="true" value="{{current.dispname}}">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="editdesc" class="control-label">Description:</label>
                        <div class="controls">
                            <input type="text" name="editdesc" id="editdesc" class="input-xlarge" value="{{current.desc}}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="editid" id="editid" value='{{current.type}}'>
                    <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
                    <button type="submit" name="editregtype" class="btn btn-primary">Edit Registration Type</button>
                </div>
            </form>
        </div>
    </body>
    
    <script type="text/javascript">
        var appModule = angular.module('tams', []);

        appModule.controller('PageController', function ($scope) {
    
            var $j = angular.element;

            $j('.modal').on('shown', function() {
                $j(this).find('.chosen-select').trigger('liszt:updated');
            });

            $scope.current = {};

            $scope.edit = function(evt, name, data) {
                var href = '#'+name+'';   

                switch(name) {
                    case 'edit_admission':
                        $scope.current = {
                            "type": data[0],
                            "admid": data[1],
                            "name": data[2],
                            "session": data[3],
                            "status": data[4]
                        };
                        break;

                    case 'edit_admtype':
                        $scope.current = {
                            "type": data[0],
                            "name": data[1],
                            "dispname": data[2],
                            "desc": data[3]                 
                        };
                        break;
                    
                    case 'edit_appbatch':
                        $scope.current = {
                            "batchid": data[0],
                            "batchname": data[1],
                            "admid": data[2],
                            "session": data[3],
                            "status": data[4]                 
                        };
                        break;
                        
                    case 'edit_regtype':
                        $scope.current = {
                            "type": data[0],
                            "name": data[1],
                            "dispname": data[2],
                            "desc": data[3]                 
                        };
                        break;
                }        

                $scope.openDialog(href, evt);
            };

            $scope.openDialog = function(href, e) {            
                $j(href).modal('show');         
                e.preventDefault();
            };
        });

    </script>
    
</html>