<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../../path.php');

$auth_users = "1,20, 22, 26,23,24";
check_auth($auth_users, $site_root . '/login.php');

$and = "";
$colid = -1;
if(isset($_GET['cid']) AND $_GET['cid'] !=''){
    $colid = $_GET['cid'];
    $and .= sprintf("AND c.colid = %s ", GetSQLValueString($colid, 'int'));
}

$sesid = -1;
if(isset($_GET['sid']) AND $_GET['sid'] !='' ){
    $sesid = $_GET['sid'];
    $and .= sprintf("AND s.sesid = %s ", GetSQLValueString($sesid, 'int'));
}

$progid = -1;
if(isset($_GET['pid']) AND $_GET['pid'] !=''){
    $progid = $_GET['pid'];
    $and .= sprintf("AND prg.progid = %s ", GetSQLValueString($progid, 'int'));
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



$olevel_SQL = sprintf("SELECT * FROM olevel_veri_data olvr "
                    . "JOIN verification v ON v.stdid = olvr.stdid "
                    . "JOIN prospective p ON olvr.stdid = p.jambregid "
                    . "JOIN session s on v.sesid = s.sesid "
                    . "JOIN programme prg ON p.progoffered = prg.progid "
                    . "JOIN department d ON prg.deptid = d.deptid "
                    . "JOIN college c ON d.colid = c.colid %s "
                    . "AND olvr.status = 'use' AND olvr.approve = 'Yes' "
                    . "AND v.olevel_submit = 'TRUE' ORDER BY olvr.stdid ", $and); 
$olevel = mysql_query($olevel_SQL, $tams) or die(mysql_error());
$olevel_row_num = mysql_num_rows($olevel);
$olevel_data_row = mysql_fetch_assoc($olevel);

$data = array();
if($olevel_row_num > 0){
    do{
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
                                        O'Level Result Verification Module
                                    </h3>
                                </div>
                                <div class="box-content"> 
                                    <form class="form-vertical" method="post"  action="olevellistpdf.php">
                                            <div class="well well-small">
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
                                            </div>
                                        </form>
                                        
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <table class="table table-bordered table-condensed">
                                                <thead>
                                                    <tr>
                                                        <th width='5%'>#</th>
                                                        <th width='10%'>Student ID</th>
                                                        <th width='10%'>Label</th>
                                                        <th width='10%'>Exam Name</th>
                                                        <th width='10%'>Exam number</th>
                                                        <th width='15%'>Exam Type</th>
                                                        <th width='15%'>Status</th>
                                                        <th width='5%'>&nbsp;</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    
                                                    <tr ng-repeat="dt in submission" ng-cloak="" ng-if="submission.length > 0">
                                                        <td>{{$index + 1}}</td>
                                                        <td>{{dt.stdid}}</td>
                                                        <td>{{dt.label}}</td>
                                                        <td>{{dt.exam_name}}</td>
                                                        <td>{{dt.exam_no}}</td>
                                                        <td>{{dt.exam_type}}</td>
                                                        <td>
                                                            <div class="badge badge-success" ng-if="dt.approve == 'Yes'">Submitted</div>
                                                            <div class="badge badge-warning" ng-if="dt.approve != 'Yes'">Not Submitted </div>
                                                        </td>
                                                        <td>
                                                            <a class="btn btn-small btn-blue" 
                                                                       href="#view_result" role="button" 
                                                                       data-toggle="modal" ng-click="setSelectedItem(dt)"><i class="icon icon-eye-open"></i>View</a>
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
                <div ng-bind-html="renderHTML(selectedItem.result_table)"></div>
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

