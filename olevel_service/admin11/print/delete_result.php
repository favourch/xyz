<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');

$auth_users = "1,20, 22, 26,23,24, 28";
check_auth($auth_users, $site_root.'/login');

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$data = array();


if(isset($_GET['search']) && $_GET['search'] != NULL){
    $SQL1 = sprintf("SELECT * FROM olevel_veri_data WHERE status = 'use' AND jambregid = %s OR stdid = %s ", 
                GetSQLValueString($_GET['search'], 'text'), 
                GetSQLValueString($_GET['search'], 'text'));
    $rs = mysql_query($SQL1, $tams) or die(mysql_error());
    $row_rs = mysql_fetch_assoc($rs);
    $olevel_row_num = mysql_num_rows($rs);
    
    
    if($olevel_row_num > 0){
        do{
            $data[] = $row_rs;
        }while($row_rs = mysql_fetch_assoc($rs));
    }
}



if(isset($_POST['purge'])){
    mysql_query("BEGIN");
    
    $updateolvSQL = sprintf("UPDATE olevel_veri_data SET status = 'unuse' WHERE id = %s ",GetSQLValueString($_POST['purge'], 'int') );
    mysql_query($updateolvSQL, $tams) or die(mysql_error());
    
    
    mysql_query("COMMIT");
    header("location: $editFormAction");
    die();
}



$page_title = "Tasued";
?>
<!doctype html>
<html  ng-app="app">
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
                                    <h3>
                                        <i class="icon-reorder"></i> Student O'Level Verification (Delete Unwanted )
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <div class="well">
                                                <form method="get"   action="<?= $editFormAction ?>"> 
                                                    <div class="control-group">
                                                        <input type="text" name="search" class="input-large"  value="<?= (isset($_GET['search'])) ? $_GET['search'] : ''?>" />
                                                        <button type="submit">Search</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <div class="well">
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
                                                           <button type="submit" name="purge"class="btn btn-small btn-danger" value="{{dt.id}}">Delete</submit>
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
                            <p>&nbsp;</p>   
                        </div>
                    </div>   
                </div>
            </div>          
        </div>
        <div id="view_result" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">{{selectedItem.exam_name}} Result for {{selectedItem.exam_no}}</h3>
            </div>
            <div class="modal-body">
                <span>Exam Year : {{selectedItem.exam_year}}</span>
                <div ng-bind-html="renderHTML(selectedItem.result_table)"></div>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
        <?php include INCPATH."/footer.php" ?>
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
    </body>
</html>


