<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');

$auth_users = "1,20,24,26,28";
check_auth($auth_users, $site_root . '/admin');


$cur_session = -1;
if (isset($_GET['sid'])) {
    $cur_session = $_GET['sid'];
} else {
    $cur_session = $_SESSION['sesid'];
}

//get the college
$olv_veri_col = -1;
if (isset($_POST['colid']) && $_POST['colid'] != '') {
    $olv_veri_col =$_SESSION['olv_veri_col'] = $_POST['colid'];
}elseif(isset($_SESSION['olv_veri_col'])){
    $olv_veri_col = $_SESSION['olv_veri_col'];
}

//For searching base on name or jambreg
$query_part1 ="";
if(isset($_POST['search']) && isset($_POST['seed']) && $_POST['seed'] != ''){
    $seed = trim($_POST['seed']);
     $query_part1 = "AND  (p.fname LIKE '%".$seed."%' "
                    . "OR p.lname LIKE  '%".$seed."%' "
                    . "OR p.mname LIKE '%".$seed."%' "
                    . "OR v.stdid LIKE '%".$seed."%' ) ";
   
}

$currentPage = $_SERVER["PHP_SELF"];
$maxRows_Rsall = 25;
$pageNum_Rsall = 0;
if (isset($_GET['pageNum_Rsall'])) {
    $pageNum_Rsall = $_GET['pageNum_Rsall'];
}
$startRow_Rsall = $pageNum_Rsall * $maxRows_Rsall;

$query_ses = sprintf("SELECT * FROM session ORDER BY sesid DESC");
$ses = mysql_query($query_ses, $tams) or die(mysql_error());
$row_ses = mysql_fetch_assoc($ses);

$query_col = sprintf("SELECT coltitle "
                    . "FROM college "
                    . "WHERE colid = %s ", 
                    GetSQLValueString($olv_veri_col, 'int'));
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);

$query_part = "";
if($_SESSION['olv_veri_who'] == 'pros'){
    
    $olevel_submissionSQL = sprintf("SELECT v.*,c.coltitle, p.fname, p.lname, p.mname, COUNT(ovd.stdid) AS sitting "
                                    . "FROM verification v "
                                    . "JOIN prospective p ON v.stdid = p.jambregid %s "
                                    . "JOIN programme pr ON pr.progid = p.progoffered "
                                    . "JOIN department d ON d.deptid = pr.deptid "
                                    . "JOIN college c ON c.colid = d.colid AND c.colid = %s "
                                    . "JOIN olevel_veri_data ovd ON v.stdid = ovd.stdid "
                                    . "WHERE ovd.sesid = %s AND ovd.treated = 'Yes' AND ovd.treated_by  = %s "
                                    . "AND ovd.approve <> 'Submitted' "
                                    . "GROUP BY (v.stdid) ",
                                    $query_part1,
                                    GetSQLValueString($olv_veri_col, 'int'),
                                    GetSQLValueString(getSessionValue('uid'), 'text'),
                                    GetSQLValueString($cur_session, 'int'));
    
    $query_part ="JOIN prospective p ON ovd.stdid = p.jambregid "
               . "JOIN programme pr ON pr.progid = p.progoffered ";
}else{
    $olevel_submissionSQL = sprintf("SELECT v.*,c.coltitle, p.fname, p.lname, p.mname, COUNT(ovd.stdid) AS sitting "
                              . "FROM verification v "
                              . "JOIN student p ON v.stdid = p.stdid %s "
                              . "JOIN programme pr ON pr.progid = p.progid "
                              . "JOIN department d ON d.deptid = pr.deptid "
                              . "JOIN college c ON c.colid = d.colid AND c.colid = %s "
                              . "JOIN olevel_veri_data ovd ON v.stdid = ovd.stdid "
                              . "WHERE ovd.sesid = %s AND ovd.treated = 'Yes' AND ovd.treated_by  = %s "
                              . "AND ovd.approve <> 'Submitted'"
                              . "GROUP BY (v.stdid) ", 
                                $query_part1,
                              GetSQLValueString($olv_veri_col, 'int'),
                                GetSQLValueString(getSessionValue('uid'), 'text'),
                               GetSQLValueString($cur_session, 'int'));
    
    $query_part ="JOIN student p ON ovd.stdid = p.stdid "
               . "JOIN programme pr ON pr.progid = p.progid ";
}


echo $query_limit_olevel_submission = sprintf("%s LIMIT %d, %d", 
                                        $olevel_submissionSQL, 
                                        $startRow_Rsall, 
                                        $maxRows_Rsall); die();
$olevel_submissionRS = mysql_query($query_limit_olevel_submission, $tams) or die(mysql_error());
$olevel_submission = mysql_fetch_assoc($olevel_submissionRS);


if (isset($_GET['totalRows_Rsall'])) {
    $totalRows_Rsall = $_GET['totalRows_Rsall'];
} else {
    $all_Rsall = mysql_query($olevel_submissionSQL);
    $totalRows_Rsall = mysql_num_rows($all_Rsall);
}
$totalPages_Rsall = ceil($totalRows_Rsall / $maxRows_Rsall) - 1;

$queryString_Rsall = "";
if (!empty($_SERVER['QUERY_STRING'])) {
    $params = explode("&", $_SERVER['QUERY_STRING']);
    $newParams = array();
    foreach ($params as $param) {
        if (stristr($param, "pageNum_Rsall") == false &&
                stristr($param, "totalRows_Rsall") == false) {
                array_push($newParams, $param);
        }
    }
    if (count($newParams) != 0) {
        $queryString_Rsall = "&" . htmlentities(implode("&", $newParams));
    }
}
$queryString_Rsall = sprintf("&totalRows_Rsall=%d%s", $totalRows_Rsall, $queryString_Rsall);



$dataLevel1 =[];
$olevel_submission4json = array();
do{
    $dataLevel1['id'] = $olevel_submission['id'];
    $dataLevel1['stdid'] = $olevel_submission['stdid'];
    $dataLevel1['fname'] = $olevel_submission['fname'];
    $dataLevel1['lname'] = $olevel_submission['lname'];
    $dataLevel1['mname'] = $olevel_submission['mname'];
    $dataLevel1['sesdid'] = $olevel_submission['sesid'];
    $dataLevel1['type'] = $olevel_submission['type'];
    $dataLevel1['ver_code'] = $olevel_submission['ver_code'];
    $dataLevel1['verified'] = $olevel_submission['verified'];
    $dataLevel1['release_code'] = $olevel_submission['release_code'];
    $dataLevel1['olevel_sitting'] = $olevel_submission['olevel_sitting'];
   
    
    $olevel_submission_dataSQL = sprintf("SELECT ovd.*, c.colid "
                                . "FROM olevel_veri_data ovd  %s "
                                . "JOIN department d ON pr.deptid = d.deptid "
                                . "JOIN college c ON d.colid = c.colid "
                                . "WHERE ovd.stdid = %s AND ovd.treated = 'Yes' "
                                . "AND ovd.approve = 'Submitted'", 
                                 $query_part, GetSQLValueString($olevel_submission['stdid'], 'int'));
    $olevel_submission_dataRS = mysql_query($olevel_submission_dataSQL, $tams) or die(mysql_error());
    $olevel_data = mysql_fetch_assoc($olevel_submission_dataRS);
    $num_olevel_data = mysql_num_rows($olevel_submission_dataRS);
    
    if($num_olevel_data > 0){
        $dataLevel2= array();
        do{
            $dataLevel2[] = $olevel_data;
        }while($olevel_data = mysql_fetch_assoc($olevel_submission_dataRS));
        $dataLevel1['olevel_data'] = $dataLevel2;
        
    }else{
        $dataLevel1['olevel_data'] = [];
    }
    
    $olevel_submission4json[] = $dataLevel1;
    
}while($olevel_submission = mysql_fetch_assoc($olevel_submissionRS));

//var_dump($olevel_submission4json);die();


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
                                        <?= ($_SESSION['olv_veri_who'] == 'pros')? 'Prospective ' : 'Returning '?> Student O'Level Result print for (<?= $row_col['coltitle'] ?>) Treated by (<?= getSessionValue('uid')?>)
                                    </h3>
                                    
                                </div>
                                <div class="box-content">
                                    <div class="well well-small">
                                        <div class="row-fluid">
                                            <div class="span3">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">Session</label>
                                                    <div class="controls controls-row">
                                                        <div class="input-append ">
                                                            <select name="name" class="input-block-level"  onchange="sesfilt(this)">
                                                                <option value="">--Choose--</option>
                                                                <?php do { ?>
                                                                    <option value="<?= $row_ses['sesid'] ?>" <?= ($cur_session == $row_ses['sesid']) ? 'selected' : '' ?>><?= $row_ses['sesname'] ?></option>
                                                                <?php } while ($row_ses = mysql_fetch_assoc($ses)); ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div> 
                                            </div>
                                            <div class="span3">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">Filter by College</label>
                                                    <div class="controls"> 
                                                        <form method="post" name="search" action="process.php">
                                                            <div class="input-append">
                                                                <input type="text" class="input-medium" name="seed" ng-model="seed" >
                                                                <button type="submit" name="search" class="btn btn-blue">Search</button>
                                                            </div>  
                                                        </form>
                                                    </div>
                                                </div> 
                                            </div>
                                            <div class="span3">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">Search by Name or Jamregid</label>
                                                    <div class="controls"> 
                                                        <form method="post" name="search" action="process.php">
                                                            <div class="input-append">
                                                                <input type="text" class="input-medium" name="seed" ng-model="seed" >
                                                                <button type="submit" name="search" class="btn btn-blue">Search</button>
                                                            </div>  
                                                        </form>
                                                    </div>
                                                </div> 
                                            </div>
                                        </div>
                                    </div>
                                    <div id="accordion2" class="accordion">
                                        <div class="accordion-group" ng-if="sub.olevel_data.length != 0" ng-repeat="sub in submission | filter : seed">
                                            <div class="accordion-heading">
                                                <a href="#collapse{{$index}}" data-parent="#accordion{{$index}}" data-toggle="collapse" class="accordion-toggle collapsed">
                                                    {{$index + 1}} &nbsp; &nbsp;&Tab; | {{sub.stdid}} | <b>{{sub.lname}} {{sub.fname}} {{sub.mname}} </b> | {{sub.olevel_sitting}} Sitting(s)
                                                </a>
                                            </div>
                                            <div class="accordion-body collapse" id="collapse{{$index}}" style="height: 0px;">
                                                <div class="accordion-inner">
                                                    <table class="table table-condensed table-striped table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>S/N </th>
                                                                <th>Exam_no</th>
                                                                <th>Exam_Type</th>
                                                                <th>Exam_Year</th>
                                                                <th>Card_SN</th>
                                                                <th>Card_PIN</th>
                                                                <th>Status</th>
                                                                <th>action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr ng-repeat="olvdt in sub.olevel_data">
                                                                <td>{{$index + 1}}</td>
                                                                <td>{{olvdt.exam_no}}</td>
                                                                <td>{{olvdt.exam_type}}</td>
                                                                <td>{{olvdt.exam_year}}</td>
                                                                <td>{{olvdt.card_sn}}</td>
                                                                <td>{{olvdt.card_pin}}</td>
                                                                <td>
                                                                    <div class="badge badge-important">status</div>
                                                                </td>
                                                                <td>
                                                                    <input type="button"  ng-click="processYes($parent.$index, $index, olvdt)" class="btn btn-small btn-green" value="Printed"/>
                                                                    &nbsp;&nbsp; | &nbsp;&nbsp;
                                                                    <input type="button" ng-click="processNo($parent.$index, $index, olvdt)" class="btn btn-small btn-red" value="Card Problem"/>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="well well-small">
                                        <div class="row-fluid">
                                            <table  class="table table-condensed table-striped">
                                                <tr width="50" align="center">
                                                    <td style="text-align: center"><a class="btn btn-small btn-blue" href="<?php printf("%s?pageNum_Rsall=%d%s", $currentPage, max(0, $pageNum_Rsall - 1), $queryString_Rsall); ?>"><i class='icon-fast-backward'></i> Prev</a></td>
                                                    <td style="text-align: center"><?php echo 'Page ' . ($pageNum_Rsall + 1) . " of " . ($totalPages_Rsall + 1); ?></td>
                                                    <td style="text-align: center"><a class="btn btn-small btn-blue" href="<?php printf("%s?pageNum_Rsall=%d%s", $currentPage, min($totalPages_Rsall, $pageNum_Rsall + 1), $queryString_Rsall); ?>">Next <i class='icon-fast-forward'></i></a></td>
                                                </tr>
                                            </table>
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
            var data = <?= json_encode($olevel_submission4json)?>;
            var session = <?= $cur_session; ?>;
            var app = angular.module('app', []);
            app.controller('pageCtrl', function($scope, $http, $window){
                $scope.submission = data;
                
                $scope.processYes = function(parentIdx, index, obj){
                        $scope.loading = true;
                        $http({
                            method : "POST",
                            url : "../../api/index.php?action=yes",
                            data: {
                                id :obj.id,
                                stdid: obj.stdid,
                                colid: obj.colid,
                                ordid: obj.ordid,
                                sesid: session
                            }
                        }).then(function mySucces(response) {
                            deletItem(parentIdx, index);
                            $scope.loading = false;
                            alert(response.data); 
                        }, function myError(response) {
                            $scope.loading = false;
                             alert(response.data);
                            
                        });    
                    };
              
                $scope.processNo = function(parentIdx, index, obj){
                        $scope.loading = true;
                        $http({
                            method : "POST",
                            url : "../../api/index.php?action=no",
                            data: {
                                id :obj.id,
                                stdid: obj.stdid,
                                colid: obj.colid,
                                ordid: obj.ordid,
                                sesid: session
                            }
                        }).then(function mySucces(response) {
                            deleteGroup(parentIdx);
                            $scope.loading = false;
                            alert(response.data); 
                        }, function myError(response) {
                            $scope.loading = false;
                             alert(response.data);
                            
                        });
                    };
                    
                $scope.processPend = function(parentIdx, index, obj){
                        $scope.loading = true;
                        $http({
                            method : "POST",
                            url : "../../api/index.php?action=pend",
                            data: {
                                id :obj.id,
                                stdid: obj.stdid,
                                colid: obj.colid,
                                ordid: obj.ordid,
                                sesid: session
                            }
                        }).then(function mySucces(response) {
                            deletItem(parentIdx, index);
                            $scope.loading = false;
                            alert(response.data); 
                        }, function myError(response) {
                            $scope.loading = false;
                             alert(response.data);
                            
                        });
                    };    
                    
                function deletItem(parent, index){
                    //console.log($scope.submission[parent].olevel_data[index]);
                    $scope.submission[parent].olevel_data.splice(index, 1);
                    //$window.location.reload();
                };
                
                function deleteGroup(parent){
                    $scope.submission.splice(parent,1);
                }
            });
        </script>
    </body>
</html>

