<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');
require_once('../ResultCHK/index.php');

$auth_users = "10,11";
check_auth($auth_users, $site_root );

if(isset($_POST['save_result'])){
    var_dump($_POST);
    die();
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
                                        O'Level Result fetcher
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="well well-small">
                                        <div class="alert alert-info">
                                            <i class="glyphicon-circle_info"></i> 
                                            Please Note that you will be charged for your individual O'Level Verification submission
                                            select your number of sitting and click the proceed button 
                                        </div>
                                    </div>
                                    <div class="" ng-if="loading">
                                        <img src="../giphy.gif">
                                    </div>
                                    
                                    <div class="well" ng-if="olevel_response">
                                        <div class="row-fluid">
                                            <div class="span12">
                                                <form action="fetch_result.php" method="POST" class="form-horizontal form-bordered">
                                                    
                                                    <div ng-bind-html="olevel_response.html_result" ></div>
                                                     
                                                    <div class="form-actions">
                                                        <button type="button" name="save_result"class="btn btn-primary" ng-click="saveResult()">Yes This is my result</button>
                                                        <button type="button" class="btn btn-warning" ng-click="notMyResult()">No This is NOT my result</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div action="#" class="form-horizontal form-bordered" ng-if="showform" >
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Exam</label>
                                            <div class="controls">
                                                <select class="input-xlarge" name="exam" ng-model="olevel.exam">
                                                    <option value="waec">WAEC</option>
                                                    <option value="neco">NECO</option>
                                                    <option value="nabteb">NABTEB</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Exam Type</label>
                                            <div class="controls">
                                                <select class="input-xlarge" name="exam_type" ng-if="olevel.exam == 'waec'" ng-model="olevel.exam_type">
                                                    <option value="MAY/JUN">SCHOOL CANDIDATE RESULTS</option>
                                                    <option value="NOV/DEC">PRIVATE CANDIDATE RESULTS</option>
                                                </select>
                                                <select class="input-xlarge" name="exam_type" ng-if="olevel.exam == 'neco'" ng-model="olevel.exam_type">
                                                    <option value="1">June / July</option>
                                                    <option value="2">Nov / Dec</option>
                                                    <option value="3">BECE</option>
                                                    <option value="4">NCEE</option>
                                                </select>
                                                <select class="input-xlarge" name="exam_type" ng-if="olevel.exam == 'nabteb'" ng-model="olevel.exam_type">
                                                    <option value="01" selected="">MAY/JUN</option>
                                                    <option value="02">NOV/DEC</option>
                                                    <option value="03">Modular (March)</option>
                                                    <option value="04">Modular (December)</option>
                                                    <option value="05">Modular (July)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Exam Year</label>
                                            <div class="controls">
                                                <select name='exam_year' class="input-large" ng-model="olevel.exam_year">
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
                                            <label for="textfield" class="control-label">Exam Number</label>
                                            <div class="controls">
                                                <input type="text" name="exam_num" ng-model="olevel.exam_num">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Card Pin</label>
                                            <div class="controls">
                                                <input type="text" name="card_pin" ng-model="olevel.card_pin">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Card Sn</label>
                                            <div class="controls">
                                                <input type="text" name="card_sn" ng-model="olevel.card_sn">
                                            </div>
                                        </div>
                                        <div class="form-actions">
                                            <button type="button" class="btn btn-primary" ng-click="fetchResult(olevel)">Fetch Result</button>
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
<?php include INCPATH . "/footer.php" ?>
        <script>
            var app = angular.module('app', ['ngSanitize']);
            app.controller('pageCtrl', function($scope, $http, $sce){
                
                $scope.loading = false;
                $scope.showform = true;
                
                $scope.notMyResult = function(){
                    $scope.showform = true;
                    $scope.olevel_response = false;
                }
                
                
                
                $scope.fetchResult = function(){
                    var con = confirm('Are you sure you want to fetch result for the infomation you have provide.');
                    if(con){
                        $scope.loading = true;
                        $scope.showform = false;
                        $http({
                            method : "POST",
                            url : "http://localhost/ResultCHK/index.php",
                            data: $scope.olevel,
                        }).then(function mySucces(response) {
                            //$scope.olevel_response = $sce.trustAsHtml(response.data);
                            $scope.olevel_response = {
                                status       : response.data.status,
                                plain_result : response.data.result_plain,
                                html_result  : response.data.result_html,
                                table_result : response.data.result_table,
                                exam_name    : response.data.exam_name,
                                exam_type    : response.data.exam_type,
                                exam_number  : response.data.exam_number,
                                candidate_name: response.data.candidate_name,
                                exam_center   : response.data.exam_center,
                                result        : response.data.result
                            };
                            
                            console.log($scope.olevel_response);
                            $scope.loading = false;

                        }, function myError(response) {
                            $scope.loading = false;
                            $scope.olevel_response = response.data;
                        });
                    }               
                    
                };
                
                
                $scope.saveResult = function(){
                    $http({
                            method : "POST",
                            url : "api.php",
                            data: $scope.olevel_response,
                        }).then(function mySucces(response) {
                            //$scope.olevel_response = $sce.trustAsHtml(response.data);
                            
                            console.log(response);
                            $scope.loading = false;

                        }, function myError(response) {
                            $scope.loading = false;
                            console.log(response);
                        });
                }
                
                $scope.selectedItem = '';
                $scope.setSelectedItem = function(item){
                    $scope.selectedItem = item;
                };
            });
        </script>
    </body>
</html>

