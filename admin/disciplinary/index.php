<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../../path.php');

$auth_users = "1,2,3,20,27";
check_auth($auth_users, $site_root);

//Create disciplinary
if(isset($_POST['MM_Submit']) AND $_POST['MM_Submit'] == 'add_culprit'){
    
    mysql_query("BEGIN");
    
    $disSQL1 = sprintf("INSERT INTO disciplinary "
            . "(stdid, status, terms, start_sesid, "
            . "start_sem, end_sesid, end_sem, date_time, created_by, create_sitting, release_sitting ) "
            . "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", 
            GetSQLValueString($_POST['post_stdid'], 'text'),
            GetSQLValueString($_POST['status'], 'text'),
            GetSQLValueString($_POST['terms'], 'text'),
            GetSQLValueString($_POST['start_ses'], 'text'),
            GetSQLValueString($_POST['start_sem'], 'text'),
            GetSQLValueString($_POST['end_ses'], 'text'),
            GetSQLValueString($_POST['end_sem'], 'text'),
            GetSQLValueString(date('Y-m-d, h:i:s'), 'text'),
            GetSQLValueString(getSessionValue('uid'), 'text'),
            GetSQLValueString($_POST['ssn'], 'int'),0);
    $disRS = mysql_query($disSQL1, $tams) or die(mysql_error());
    $insert_id = mysql_insert_id(); 
    
    $studSQL = sprintf("UPDATE student "
                    . "SET disciplinary = 'TRUE' "
                    . "WHERE stdid = %s ", 
                    GetSQLValueString($_POST['post_stdid'], 'text'));
    $studRS = mysql_query($studSQL, $tams) or die(mysql_error());
    $affected_row = mysql_affected_rows();
    
    $cont = array('Disciplinary' => array(
            'stdid' => array("old" => '', "new" => $_POST['post_stdid']),
            'status' => array('old' => '', 'new' => $_POST['status']),
            'terms' => array('old' => '', 'new' => $_POST['terms']),
            'start_ses' => array('old' => '', 'new' => $_POST['start_ses']),
            'start_sem' => array('old' => '', 'new' => $_POST['start_sem']),
            'end_ses' => array('old' => '', 'new' => $_POST['end_ses']),
            'end_sem' => array('old' => '', 'new' => $_POST['end_sem']),
            'ssn' => array('old' => '', 'new' => $_POST['ssn']),
            'created_by' => array('old' => '', 'new' => getSessionValue('uid')),
            'date_created' => array('old' => '', 'new' => date('Y-m-d, h:i:s'))
        )
    );
    
    $param['entid'] = $_POST['post_stdid'];
    $param['enttype'] = 'student';
    $param['action'] = 'create';
    $param['cont'] = json_encode($cont);
    audit_log($param);
    
    mysql_query("COMMIT");
       
        
        
        
     
    header('Location: index.php');
    exit();
}

//Update disciplinary table
if (isset($_POST['MM_Submit']) AND $_POST['MM_Submit'] == 'edit_culprit') {
    
    $old_rec = json_decode($_POST['prev_record']);
    
    mysql_query("BEGIN");

    $disSQL1 = sprintf("UPDATE disciplinary  SET "
                    . "stdid =%s , status =%s, terms =%s, start_sesid =%s, "
                    . "start_sem =%s, end_sesid =%s, end_sem =%s, date_time =%s,"
                    . "created_by =%s, create_sitting = %s "
                    . "WHERE disid = %s ", 
                    GetSQLValueString($_POST['post_stdid'], 'text'), 
                    GetSQLValueString($_POST['status'], 'text'), 
                    GetSQLValueString($_POST['terms2'], 'text'), 
                    GetSQLValueString($_POST['start_ses'], 'int'), 
                    GetSQLValueString($_POST['start_sem'], 'int'), 
                    GetSQLValueString($_POST['end_ses'], 'text'), 
                    GetSQLValueString($_POST['end_sem'], 'text'), 
                    GetSQLValueString(date('Y-m-d, h:i:s'), 'text'), 
                    GetSQLValueString(getSessionValue('uid'), 'text'),
                    GetSQLValueString($_POST['ssn'], 'text'),
                    GetSQLValueString($_POST['edit_id'], 'int'));
    $disRS = mysql_query($disSQL1, $tams) or die(mysql_error());
   
    
    $cont = array('Disciplinary' => array(
            'stdid' => array("old" => $old_rec->stdid, "new" => $_POST['post_stdid']),
            'status' => array('old' => $old_rec->status, 'new' => $_POST['status']),
            'terms' => array('old' => $old_rec->terms, 'new' => $_POST['terms']),
            'start_ses' => array('old' => $old_rec->start_sesid, 'new' => $_POST['start_ses']),
            'start_sem' => array('old' => $old_rec->start_sem, 'new' => $_POST['start_sem']),
            'end_ses' => array('old' => $old_rec->end_sesid, 'new' => $_POST['end_ses']),
            'end_sem' => array('old' => $old_rec->end_sem, 'new' => $_POST['end_sem']),
            'ssn' => array('old' => $old_rec->create_sitting, 'new' => $_POST['ssn']),
            'created_by' => array('old' => $old_rec->created_by, 'new' => getSessionValue('uid')),
            'date_created' => array('old' => $old_rec->date_time, 'new' => date('Y-m-d, h:i:s'))
        )
    );

    $param['entid'] = $_POST['post_stdid'];
    $param['enttype'] = 'student';
    $param['action'] = 'edit';
    $param['cont'] = json_encode($cont);
    audit_log($param);

    mysql_query("COMMIT");





    header('Location: index.php');
    exit();
}


//Release from disciolinary 
if (isset($_POST['MM_Submit']) AND $_POST['MM_Submit'] == 'release_culprit') {
    
   // mysql_query("BEGIN");
    
    $updateSQL = sprintf("UPDATE student "
                        . "SET disciplinary = 'FALSE' "
                        . "WHERE stdid = %s", 
                        GetSQLValueString($_POST['stdid'], 'text'));
    $updateRS = mysql_query($updateSQL, $tams) or die(mysql_error());
    
   $updateSQL1 = sprintf("UPDATE disciplinary "
                        . "SET released = 'yes', "
                        . "released_by = %s, release_sitting = %s, "
                        . "release_date = %s "
                        . "WHERE disid = %s ", 
                        GetSQLValueString(getSessionValue('uid'), 'text'),
                        GetSQLValueString($_POST['ssn'], 'int'),
                        GetSQLValueString(date('Y-m-d, h:i:s'), 'text'),
                        GetSQLValueString($_POST['edit_id'], 'int'));

    $updateRS = mysql_query($updateSQL1, $tams) or die(mysql_error());
    
    $val = array();
    foreach ($_POST['sessions'] as  $session) {
        $val[]   =  sprintf( "( %s, %s, "
                            . "'Registered', "
                            . "'Unregistered',"
                            . " 'FALSE', "
                            . " %s )", 
                            GetSQLValueString($_POST['stdid'], 'text'),
                            GetSQLValueString($session, 'int'),
                            GetSQLValueString($_POST['level'], 'int'));
    }
    $values = implode(',', $val);
    $insertSQL = sprintf("INSERT INTO registration "
                        . "(stdid, sesid, status, course, approved, level )"
                        . " VALUES %s",$values );
    $insertRS = mysql_query($insertSQL, $tams);
    
    
    $cont = array('Registration' => array(
            'stdid' => array("old" => '', "new" => $_POST['stdid']),
            'status' => array('old' => $old_rec->status, 'new' => 'Registered'),
            'session' => array('old' => '', 'new' => implode(',', $_POST['sessions'])),
            'course' => array('old' => '', 'new' => 'Unregistered'),
            'approved' => array('old' => '', 'new' => 'FALSE'),
            'level' => array('old' => '', 'new' => $_POST['level']),
            'created_by' => array('old' => '', 'new' => getSessionValue('uid')),
            'date_created' => array('old' => '', 'new' => date('Y-m-d, h:i:s'))
        )
    );

    $param['entid'] = $_POST['stdid'];
    $param['enttype'] = 'student';
    $param['action'] = 'create';
    $param['cont'] = json_encode($cont);
    audit_log($param);

    //mysql_query("COMMIT");





    header('Location: index.php');
    exit();
}


$query_ses = "SELECT * FROM session ";
$ses = mysql_query($query_ses, $tams) or die(mysql_error());
$row_ses = mysql_fetch_assoc($ses);
$totalRows_ses = mysql_num_rows($ses);

//Get list of disciplinary 
$query_disc = "SELECT s.fname, s.lname, s.mname, s.level, d.*, s1.sesname AS st_ses, s2.sesname AS nd_ses, p.progname, dpt.deptname, c.colname "
            . "FROM disciplinary d "
            . "JOIN student s "
            . "ON s.stdid = d.stdid "
            . "JOIN session s1 "
            . "ON s1.sesid = d.start_sesid "
            . "JOIN session s2 "
            . "ON s2.sesid = d.end_sesid "
            . "JOIN programme p "
            . "ON p.progid = s.progid "
            . "JOIN department dpt "
            . "ON dpt.deptid = p.deptid "
            . "JOIN college c "
            . "ON c.colid = dpt.colid";
$desRS = mysql_query($query_disc, $tams) or die(mysql_error());
$row_disc = mysql_fetch_assoc($desRS);
$totalRows_disc = mysql_num_rows($desRS);

$data1 = array();
do {
    $data1[] = $row_disc;
} while ($row_disc = mysql_fetch_assoc($desRS));

$disciplinary = json_encode($data1);


$data = array();
do{
    $data[] = $row_ses;
    
}while ($row_ses = mysql_fetch_assoc($ses));

$session = json_encode($data);




$page_title = "Tasued";
?>
<!doctype html>
<html ng-app="disciplinary">
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="pageCtrl">
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
                                        Disciplinary Module
                                    </h3>
                                </div>
                                <div class="box-content nopadding">
                                    <ul class="tabs tabs-inline tabs-top">
                                        <li class="active">
                                            <a data-toggle="tab" href="#first11"><i class="icon-inbox"></i> Active </a>
                                        </li>
                                        <li class="">
                                            <a data-toggle="tab" href="#second22"><i class="icon-share-alt"></i> Released</a>
                                        </li>
                                        <li class="">
                                            <a data-toggle="tab" href="#thirds3322"><i class="icon-tag"></i>All Disciplinary</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content padding tab-content-inline tab-content-bottom">
                                        <div id="first11" class="tab-pane active">
                                            <div class="row-fluid">
                                                <div class="span12">
                                                    <div class="pull-right right">
                                                        <a href="#add_culprit" data-toggle="modal"  class="btn btn-small btn-purple" ng-click="">Add New Disciplinary Action</a>
                                                    </div>
                                                </div>
                                            </div>
                                           
                                            <div class="row-fluid">
                                                <table class="table table-condensed table-bordered table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th width="5%">#</th>
                                                            <th width="10%">Matric No.</th>
                                                            <th width="30%">Full Name</th>
                                                            <th width="10%">Senate Sitting</th>
                                                            <th width="15%">From</th>
                                                            <th width="15%">To</th>
                                                            <th width="10%">Status</th>
                                                            <th width="5%">&nbsp;</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr ng-repeat="disc1 in disciplinary |filter :{released : 'no'}" >
                                                            <td>{{$index + 1 }}</td>
                                                            <td>{{disc1.stdid }}</td>
                                                            <td>{{disc1.lname}} {{disc1.fname}} {{disc1.mname}}</td>
                                                            <td>{{disc1.create_sitting }}</td>
                                                            <td>{{disc1.st_ses}}-{{disc1.start_sem}} </td>
                                                            <td>{{disc1.nd_ses}}-{{disc1.end_sem}} </td>
                                                            <td>{{disc1.status}}</td>
                                                            <td> 
                                                                <div class="btn-group">
                                                                    <button data-toggle="dropdown" class="btn dropdown-toggle">
                                                                        Action
                                                                        <span class="caret"></span>
                                                                    </button>
                                                                    <ul class="dropdown-menu">
                                                                        <li>
                                                                            <a href="#edit_culprit" data-toggle="modal" ng-click="setSelectedItem(disc1)">Edit</a>
                                                                        </li>
                                                                        <li ng-show="disc1.released == 'no'">
                                                                            <a href="#release_culprit" data-toggle="modal" ng-click="setSelectedItem(disc1)" >Release</a>
                                                                        </li>
                                                                    </ul>
                                                                </div> 
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div id="second22" class="tab-pane">
                                            <div class="row-fluid">
                                                <div class="span12">
                                                    <div class="input-prepend">
                                                        <span class="add-on">Filter</span>
                                                        <input type="text" class="input input-large" ng-model="seed1">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row-fluid">
                                                <table class="table table-condensed table-bordered table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th width="5%">#</th>
                                                            <th width="10%">Matric No.</th>
                                                            <th width="35%">Full Name</th>
                                                            <th width="10%">Senate Sitting</th>
                                                            <th width="15%">From</th>
                                                            <th width="15%">To</th>
                                                            <th width="10%">Status</th>
                                                            
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr ng-repeat="disc2 in disciplinary |filter :{released : 'yes', $ : seed1} " >
                                                            <td>{{$index + 1 }}</td>
                                                            <td>{{disc2.stdid }}</td>
                                                            <td>{{disc2.lname}} {{disc2.fname}} {{disc2.mname}}</td>
                                                            <td>{{disc2.release_sitting }}</td>
                                                            <td>{{disc2.st_ses}}-{{disc2.start_sem}} </td>
                                                            <td>{{disc2.nd_ses}}-{{disc2.end_sem}} </td>
                                                            <td>{{disc2.status}}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div id="thirds3322" class="tab-pane">
                                            <div class="row-fluid">
                                                <div class="span12"> 
                                                    <div class="input-prepend">
                                                        <span class="add-on">Filter</span>
                                                        <input type="text" class="input input-large" ng-model="seed3">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row-fluid">
                                                <table class="table table-condensed table-bordered table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th width="5%">#</th>
                                                            <th width="10%">Matric No.</th>
                                                            <th width="35%">Full Name</th>
                                                            <th width="5%">S.Rec</th>
                                                            <th width="5%">S.App</th>
                                                            <th width="15%">From</th>
                                                            <th width="15%">To</th>
                                                            <th width="10%">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr ng-repeat="disc3 in disciplinary |filter :{$ : seed3}" >
                                                            <td>{{$index + 1 }}</td>
                                                            <td>{{disc3.stdid }}</td>
                                                            <td>{{disc3.lname}} {{disc3.fname}} {{disc3.mname}}</td>
                                                            <td>{{disc3.create_sitting }}</td>
                                                            <td>{{disc3.release_sitting }}</td>
                                                            <td>{{disc3.st_ses}}-{{disc3.start_sem}} </td>
                                                            <td>{{disc3.nd_ses}}-{{disc3.end_sem}} </td>
                                                            <td>{{disc3.status}}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div> 
            </div>          
        </div>
        <script type="text/javascript">
            var session = <?= $session ;?>;
            var disciplinary = <?= $disciplinary ;?>;
        </script>
        <script src="controller.js"></script>
        <?php require "partials/index.php"; ?>
        <?php include INCPATH."/footer.php" ?>
        
    </body>
</html>

