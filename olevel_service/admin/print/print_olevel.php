<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../../../path.php');

$auth_users = "1,20, 22, 26,23,24, 28";
check_auth($auth_users, $site_root . '/login.php');

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$and = "";
$colid = -1;
if(isset($_GET['cid']) AND $_GET['cid'] !=''){
    $colid = $_GET['cid'];
    $and .= sprintf("AND c.colid = %s ", GetSQLValueString($colid, 'int'));
}elseif(isset($_POST['colid'])){
    $colid = $_POST['colid'];
    $and .= sprintf("AND c.colid = %s ", GetSQLValueString($colid, 'int'));
}

$sesid = getSessionValue('sesid');
if(isset($_GET['sid']) AND $_GET['sid'] !='' ){
    $sesid = $_GET['sid'];
    $and .= sprintf("AND s.sesid = %s ", GetSQLValueString($sesid, 'int'));
}

$progid = -1;
if(isset($_GET['pid']) AND $_GET['pid'] !=''){
    $progid = $_GET['pid'];
    $and .= sprintf("AND prg.progid = %s ", GetSQLValueString($progid, 'int'));
}


if($_POST['seed']){
    if($_SESSION['olv_veri_who'] == 'stud'){
        $and .= sprintf("AND v.stdid = %s ", GetSQLValueString($_POST['seed'], 'text'));
    }
    else
    {
        $and .= sprintf("AND v.jambregid = %s ", GetSQLValueString($_POST['seed'], 'text'));
    }
}   

$query2 = sprintf("SELECT sesname, sesid "
                . "FROM session ORDER BY sesid DESC");
$session = mysql_query($query2, $tams) or die(mysql_error());


$query1 = sprintf("SELECT coltitle, colid "
                . "FROM college ");
$college = mysql_query($query1, $tams) or die(mysql_error());

$query3 = sprintf("SELECT progid, progname "
                . "FROM programme ORDER BY progname ASC ");
$prog = mysql_query($query3, $tams) or die(mysql_error());

if(isset($_POST['purge'])){
    mysql_query("BEGIN");
    
    $updateolvSQL = sprintf("UPDATE olevel_veri_data SET approve = 'submitted' WHERE id = %s ",GetSQLValueString($_POST['purge'], 'int') );
    mysql_query($updateolvSQL, $tams) or die(mysql_error());
    
    $updateveriSQL = sprintf("UPDATE verification SET olevel_submit = 'FALSE' WHERE jambregid = %s OR stdid = %s ",GetSQLValueString($_POST['purge'], 'int'), GetSQLValueString($_POST['purge'], 'int') );
    mysql_query($updateveriSQL, $tams) or die(mysql_error());
    
    mysql_query("COMMIT");
    header("location: $editFormAction");
    die();
}


$olevel_SQL = "";
if($_SESSION['olv_veri_who'] == 'stud'){
    $student_type = "Returning Students";
    $search = 'matric Number';
    $olevel_SQL = sprintf("SELECT *, olvr.id AS resultid FROM olevel_veri_data olvr "
            . "JOIN verification v ON v.stdid = olvr.stdid "
            . "JOIN student p ON olvr.stdid = p.stdid "
            . "JOIN session s on v.sesid = s.sesid "
            . "JOIN programme prg ON p.progid = prg.progid "
            . "JOIN department d ON prg.deptid = d.deptid "
            . "JOIN college c ON d.colid = c.colid %s "
            . "AND olvr.status = 'use' AND olvr.approve = 'Yes' AND olvr.old = 'no' AND date_fetched IS NOT NULL "
            . "AND v.olevel_submit = 'TRUE' ORDER BY olvr.stdid ", $and);
    
}else{
    $student_type = "Prospective Students";
    $search = 'JAMB Reg Number';
    $olevel_SQL = sprintf("SELECT *, p.jambregid AS stdid, olvr.id AS resultid FROM olevel_veri_data olvr "
            . "JOIN verification v ON v.jambregid = olvr.jambregid "
            . "JOIN prospective p ON olvr.jambregid = p.jambregid "
            . "JOIN session s on v.sesid = s.sesid "
            . "JOIN programme prg ON p.progid1 = prg.progid "
            . "JOIN department d ON prg.deptid = d.deptid "
            . "JOIN college c ON d.colid = c.colid %s "
            . "AND olvr.status = 'use' AND olvr.approve = 'Yes' AND olvr.old = 'no' AND date_fetched IS NOT NULL "
            . "AND v.olevel_submit = 'TRUE' ORDER BY olvr.jambregid ", $and);
}


$olevel = mysql_query($olevel_SQL, $tams) or die(mysql_error());
$olevel_row_num = mysql_num_rows($olevel);
$olevel_data_row = mysql_fetch_assoc($olevel);

$data = array();
if($olevel_row_num > 0){
    do{
        $olevel_data_row['result_plain'] = json_decode($olevel_data_row['result_plain'], true);
        $data[] = $olevel_data_row;
    }while($olevel_data_row = mysql_fetch_assoc($olevel));
}

if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}
$deptname = "";



$page_title = "Tasued";
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
                                    <h3><i class="icon-print"></i>
                                        <?= $student_type ?> O&apos;Level Result Verification Module
                                    </h3>
                                    <ul class="tabs">
                                        <li class="active"><a href="../../../../verification/admin/print_olevel/process.php" target="new">Old O&apos;Level Print Out</a></li>
                                    </ul>
                                </div>
                                <div class="box-content"> 
                                    
                                            <div class="well well-small">
                                                <form class="form-vertical" method="post"  action="olevellistpdf.php" target="tab">
                                                    <div class="row-fluid">
                                                    <div class="span2">
                                                        <select name="sid" class="input input-medium " onchange="sesfilt(this)">
                                                            <option value="">--Session--</option>
                                                            <?php for(;$row_session = mysql_fetch_assoc($session);){?>
                                                            <option value="<?= $row_session['sesid']?>" <?= ($row_session['sesid'] == $sesid) ? 'selected': ''?>><?= $row_session['sesname']?></option>
                                                            <?php }?>
                                                        </select>
                                                    </div>
                                                    <div class="span2">
                                                        <select name="cid" class="input input-medium " onchange="colfilt(this)">
                                                            <option value="">--College--</option>
                                                            <?php for(;$row_college = mysql_fetch_assoc($college);){?>
                                                            <option value="<?= $row_college['colid']?>" <?= ($row_college['colid'] == $colid) ? 'selected': ''?> ><?= $row_college['coltitle']?></option>
                                                            <?php }?>
                                                        </select>
                                                    </div>
                                                    <div class="span2">
                                                        <select name="pid" class="input input-medium " onchange="progfilt(this)">
                                                            <option value="">--Programme--</option>
                                                            <?php for(;$row_prog = mysql_fetch_assoc($prog);){?>
                                                            <option value="<?= $row_prog['progid']?>" <?= ($row_prog['progid'] == $progid) ? 'selected': ''?> ><?= $row_prog['progname']?></option>
                                                            <?php }?>
                                                        </select>
                                                    </div>
                                                    <div class="span2">
                                                        <input type="date" name="from" placeholder="Date From" class="prev_date input input-medium">
                                                    </div>
                                                    <div class="span2">
                                                        <input type="date" name="to" placeholder="Date To" class="prev_date input input-medium">
                                                    </div>
                                                    <div class="span2">
                                                        <button type="submit" name="print_olevel" class="btn btn-small btn-gray"> Print O'Level Results</button>
                                                    </div>
                                                </div>
                                                </form>
                                                <form method="post" action="<?= $editFormAction?>">
                                                    <div class="row-fluid">
                                                        <div class="span2"></div>
                                                        <div class="span2"></div>
                                                        <div class="span4">
                                                            <div class="input-append input-prepend">
                                                                <span class="add-on"><i class="icon-search"></i></span>
                                                                <input placeholder="Search with <?= $search?> ..." class="input-medium" type="text" name="seed" ng-model='seed'>
                                                                <button class="btn" type="submit">Search!</button>
                                                            </div>
                                                        </div>
                                                        <div class="span2"></div>
                                                        <div class="span2"></div>
                                                    </div>
                                                </form>
                                            </div>
                                        
                                        
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <table class="table table-bordered table-condensed">
                                                <thead>
                                                    <tr>
                                                        <th width='5%'>#</th>
                                                        <th width='10%'>Student ID</th>
                                                        <th width='5%'>Label</th>
                                                        <th width='10%'>Exam Name</th>
                                                        <th width='10%'>Exam number</th>
                                                        <th width='15%'>Exam Type</th>
                                                        <th width='15%'>Exam Year</th>
                                                        <th width='5%'>Status</th>
                                                        <th width='15%'>&nbsp;</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    
                                                    <tr ng-repeat="dt in submission | filter:seed" ng-cloak="" ng-if="submission.length > 0">
                                                        <td>{{$index + 1}}</td>
                                                        <td>{{dt.stdid}}</td>
                                                        <td>{{dt.label}}</td>
                                                        <td>{{dt.exam_name}}</td>
                                                        <td>{{dt.exam_no}}</td>
                                                        <td>{{dt.exam_type}}</td>
                                                        <td>{{dt.exam_year}}</td>
                                                        <td>
                                                            <div class="badge badge-success" ng-if="dt.approve == 'Yes'">Submitted</div>
                                                            <div class="badge badge-warning" ng-if="dt.approve != 'Yes'">Not Submitted </div>
                                                        </td>
                                                        <td>
                                                            <form method="post" action="<?= $editFormAction; ?>">
                                                            <a class="btn btn-small btn-blue" 
                                                                       href="#view_result" role="button" 
                                                                       data-toggle="modal" ng-click="setSelectedItem(dt)"><i class="icon icon-eye-open"></i>View</a>
                                                           <a class="btn btn-small btn-pink" 
                                                                       href="olevel_pdf.php?resultid={{dt.resultid}}" target='tabs'><i class="icon icon-printer"></i>Print</a>
                                                                       
                                                           <a class="btn btn-small btn-brown" 
                                                                       href="refetch_result.php?resultid={{dt.resultid}}" target='tabs'><i class="icon icon-printer"></i>Re Fetch</a>
                                                                       
                                                                       <input type="hidden" name="stdid" value="{{dt.stdid}}">
                                                                       <button type="submit" name="purge"class="btn btn-small btn-danger" value="{{dt.resultid}}">Purge</submit>
                                                                       
                                                           </form>
                                                        </td>
                                                    </tr>              
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
        </div>
        <?php include INCPATH . "/footer.php" ?>
        <div id="view_result" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">{{selectedItem.exam_name}} Result for {{selectedItem.exam_no}}</h3>
            </div>
            <div class="modal-body">
                
                <div class="row-fuild">
                    <div class="span6">
                        <b>Exam Name :</b> {{selectedItem.result_plain.result.exam_name}} <br/>
                        <b>Exam Type :</b> {{selectedItem.result_plain.result.exam_type}} <br/>
                        <b>Exam Year :</b> {{selectedItem.result_plain.result.exam_year}} <br/>
                        <b>Exam Number :</b> {{selectedItem.result_plain.result.exam_number}} <br/>
                        <b>Candidate Name :</b> {{selectedItem.result_plain.result.candidate_name}} <br/>
                        <b>Exam Center :</b> {{selectedItem.result_plain.result.exam_center}} <br/>
                        <br/>
                        <b>Subject/Score</b> 
                        <table class="table table-sm ">
                            <tbody>
                                <tr ng-repeat="rs in selectedItem.result_plain.result.result">
                                    <td>{{rs.subject}}</td>
                                    <td>{{rs.score}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
    </body>
    <script>
        $('.prev_date').datepicker({
            'format':'yyyy-mm-dd'
        });
        
        var data = <?= json_encode($data) ;?>;
          
        var app = angular.module('app', ['ngSanitize']);
        app.controller('pageCtrl', function($scope, $sce){
            $scope.submission = data;
            
            $scope.selectedItem = '';
            $scope.setSelectedItem = function(item){
                $scope.selectedItem = item;
            };
            
            $scope.renderHTML = function(html_code){
                var decoded = angular.element('<textarea />').html(html_code).text();
                return $sce.trustAsHtml(decoded);
            };
        });
    </script>
</html>

