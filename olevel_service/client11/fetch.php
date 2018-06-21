<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');
require_once('../ResultCHK/index.php');

$auth_users = "10,11";
check_auth($auth_users, $site_root );

$id = -1;
if(isset($_GET['id'])){
    $id = $_GET['id'];
}

$result = NULL; 
if(isset($_POST['fetch_result'])){
    $resultChecker = new Result();
    
    $exam_name = $_POST['exam_name'];
    $exam_type = $_POST['exam_type'];
    $exam_year = $_POST['exam_year'];
    $exam_number = $_POST['exam_num'];
    $card_pin = $_POST['card_pin'];
    $card_sn = $_POST['card_sn'];
    
    $result = $resultChecker->getResult($exam_name, $exam_type, $exam_year, $exam_number, $card_pin, $card_sn);
    
}

if(isset($_POST['save_result'])){
   
    $olevelResultSQL  = sprintf("UPDATE olevel_veri_data "
                              . "SET exam_name = %s, exam_no = %s,  "
                              . "exam_type = %s, exam_year = %s, "
                              . "result_plain = %s, result_table = %s, card_no = %s, card_pin = %s, approve = 'Yes', date_fetched = NOW() WHERE id = %s ", 
                              GetSQLValueString($_POST['exam_name'], 'text'),
                              GetSQLValueString($_POST['exam_number'], 'text'),
                              GetSQLValueString($_POST['exam_type'], 'text'),
                              GetSQLValueString($_POST['exam_year'], 'text'),
                              GetSQLValueString(htmlentities($_POST['result_plain']), 'text'),
                              GetSQLValueString(htmlentities($_POST['result_table']), 'text'),
                              GetSQLValueString($_POST['card_sn'], 'text'),
                              GetSQLValueString($_POST['card_pin'], 'text'),
                              GetSQLValueString($id, 'int'));
    mysql_query($olevelResultSQL, $tams) or die(mysql_error());
    header('location:index.php');
    die();
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
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
                                    <div class="alert alert-info">
                                        <i class="glyphicon-circle_info"></i> 
                                        Provide your O&apos;Level Result details and your Exam Result checker pin...  
                                    </div>
                                    <?php if($result != NULL){?>
                                    <div class="well" >
                                        <div class="row-fluid">
                                            <div class="span12">
                                                <form action="<?= $editFormAction?>" method="POST" class="form-horizontal form-bordered">
                                                    <?php if($result->get_result_status() == '1'){ ?>
                                                    
                                                    <div>
                                                        <?= $result->get_tabled_result()?>
                                                    </div>                                  
                                                    <div class="form-actions">                                      
                                                        <input type="hidden" name="exam_name" value="<?= $result->get_exam_name()?>">
                                                        <input type="hidden" name="exam_year" value="<?= $result->get_exam_year()?>">
                                                        <input type="hidden" name="exam_type" value="<?= $result->get_exam_type()?>">
                                                        <input type="hidden" name="exam_number" value="<?= $result->get_exam_number()?>">
                                                        <input type="hidden" name="result_plain" value="<?= $result->get_plain_result()?>">
                                                        <input type="hidden" name="result_table" value="<?= $result->get_tabled_result()?>">
                                                        <input type="hidden" name="card_sn" value="<?= $_POST['card_sn']?>">
                                                        <input type="hidden" name="card_pin" value="<?= $_POST['card_pin']?>">
                                                        <button type="submit" name="save_result"class="btn btn-primary" >Yes This is my result</button>
                                                        <a href="<?= $editFormAction?>" class="btn btn-warning">No This is NOT my result</a>
                                                    </div>
                                                    <?php }else{ ?>
                                                    
                                                    <div class="alert alert-danger">
                                                        <?= $result->get_result_response()?>
                                                        <br/>
                                                        <a href="<?= $editFormAction?>" class="btn btn-warning">Re-Try</a>
                                                    </div>
                                                    <?php }?>
                                                </form>
                                            </div>
                                        </div>             
                                    </div>
                                    <?php }?>
                                    <?php if($result == NULL) {?>
                                    <form action="<?= $editFormAction?>" method="post" class="form-horizontal form-bordered" >
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Exam</label>
                                            <div class="controls">
                                                <select class="input-xlarge" name="exam_name" ng-model="olevel.exam" required="">
                                                    <option value="waec">WAEC</option>
                                                    <option value="neco">NECO</option>
                                                    <option value="nabteb">NABTEB</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Exam Type</label>
                                            <div class="controls">
                                                <select class="input-xlarge" name="exam_type" ng-if="olevel.exam == 'waec'" ng-model="olevel.exam_type" required="">
                                                    <option value="MAY/JUN">SCHOOL CANDIDATE RESULTS</option>
                                                    <option value="NOV/DEC">PRIVATE CANDIDATE RESULTS</option>
                                                </select>
                                                <select class="input-xlarge" name="exam_type" ng-if="olevel.exam == 'neco'" ng-model="olevel.exam_type" required="">
                                                    <option value="1">June / July</option>
                                                    <option value="2">Nov / Dec</option>
                                                    <option value="3">BECE</option>
                                                    <option value="4">NCEE</option>
                                                </select>
                                                <select class="input-xlarge" name="exam_type" ng-if="olevel.exam == 'nabteb'" ng-model="olevel.exam_type" required="">
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
                                                <select name='exam_year' class="input-large" ng-model="olevel.exam_year" required="">
                                                    <option value="">--Choose--</option>
                                                    <?php
                                                    $date = date('Y');
                                                    for ($i = 0; $i < 39; $i++) {
                                                        ?>
                                                        <option value="<?= $date - $i ?>"> <?= $date - $i ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Exam Number</label>
                                            <div class="controls">
                                                <input type="text" name="exam_num" ng-model="olevel.exam_num" required="">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Card SN</label>
                                            <div class="controls">
                                                <input type="text" name="card_sn" ng-model="olevel.card_sn" required="">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Card Pin</label>
                                            <div class="controls">
                                                <input type="text" name="card_pin" ng-model="olevel.card_pin" required="">
                                            </div>
                                        </div>
                                        <div class="form-actions">
                                            <button type="submit" name="fetch_result" class="btn btn-primary" >Fetch Result</button>
                                        </div>
                                    </form> 
                                    <?php }?>
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
                };
                
                $scope.selectedItem = '';
                $scope.setSelectedItem = function(item){
                    $scope.selectedItem = item;
                };
                
            });
        </script>
    </body>
</html>

