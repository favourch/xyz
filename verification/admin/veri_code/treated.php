<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../../../path.php');

$auth_users = "1,20,26,24";
check_auth($auth_users, $site_root . '/admin');


if(isset($_POST['colid']) && $_POST['colid'] != ''){
   $_SESSION['olv_veri_col'] = $_POST['colid'];  
}

if(!isset($_SESSION['olv_veri_col'])){
  header('Location: index.php');
  exit();
}

$cur_session = -1;
if(isset($_GET['sid'])){
    $cur_session = $_GET['sid'];
}else{
    $cur_session = $_SESSION['sesid'];
}



$query_ses = sprintf("SELECT * FROM session ORDER BY sesid DESC");
$ses = mysql_query($query_ses, $tams) or die(mysql_error());
$row_ses = mysql_fetch_assoc($ses);


$query_prog = sprintf("SELECT progid, progname "
                    . "FROM programme WHERE continued = 'Yes' ORDER BY progid DESC");
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);

$programs = array();
while($row_prog = mysql_fetch_assoc($prog)){
    
    $programs[] = array(
        'progid' => $row_prog['progid'],
        'progname' => $row_prog['progname']
    );
}
$program = json_encode($programs);

$currentPage = $_SERVER["PHP_SELF"];

$maxRows_Rsall = 25;
$pageNum_Rsall = 0;
if (isset($_GET['pageNum_Rsall'])) {
    $pageNum_Rsall = $_GET['pageNum_Rsall'];
}
$startRow_Rsall = $pageNum_Rsall * $maxRows_Rsall;
//***********************************************

$query_part = '';
$query_part1 ="";
if(isset($_POST['search']) && isset($_POST['seed']) && $_POST['seed'] != ''){
    $seed = trim($_POST['seed']);
     $query_part1 = "AND  (p.fname LIKE '%".$seed."%' "
                    . "OR p.lname LIKE  '%".$seed."%' "
                    . "OR p.mname LIKE '%".$seed."%' "
                    . "OR p.jambregid LIKE '%".$seed."%' ) ";
   
}

if(isset($_POST['search']) 
        && isset($_POST['from']) 
        && isset($_POST['to']) 
        && $_POST['from'] != '' 
        && $_POST['to'] != ''){
    $from = GetSQLValueString(trim($_POST['from']), 'date');
    $to = GetSQLValueString(trim($_POST['to']), 'date');
    $query_part = sprintf("AND
                            v.date_treated >= {$from} 
                            AND
                            v.date_treated <= {$to} ");
}

$partiall ='';
if(!in_array(getAccess(), ['20,24'])){
    $partiall = sprintf("AND v.treated_by = %s ", GetSQLValueString(getSessionValue('uid'), 'text') );
}

$query = sprintf("SELECT v.*, 
                ict1.fname AS trtd_fname, ict1.lname AS trtd_lname, 
                ict2.fname AS rlsd_fname, ict2.lname AS rlsd_lname,
                p.pstdid, p.fname, 
                p.lname, p.mname, p.progoffered, prg.progname, 
                COUNT(ovd.stdid) AS sittings 
                FROM verification v 
                LEFT JOIN ictstaff ict1 ON ict1.stfid = v.treated_by
                LEFT JOIN ictstaff ict2 ON ict2.stfid = v.released_by
                JOIN prospective p ON v.stdid = p.jambregid
                JOIN programme prg ON p.progoffered = prg.progid
                JOIN department d ON d.deptid = prg.deptid 
                JOIN college c ON c.colid = d.colid AND c.colid = %s
                JOIN olevel_veri_data ovd ON ovd.stdid = v.stdid 
                    AND ovd.approve = 'Yes' 
                    AND ovd.treated = 'yes' %s 
                WHERE (v.status <> '0') 
                      %s 
                      AND v.sesid = %s  %s
                    GROUP BY(v.stdid)
               ",   GetSQLValueString($_SESSION['olv_veri_col'], 'int'),
                    $query_part,
                    $partiall,
                    GetSQLValueString($cur_session, 'int'),
                    $query_part1);

$query_limit_verify = sprintf("%s ORDER BY v.id, v.stdid ASC LIMIT %d, %d  ", $query, $startRow_Rsall, $maxRows_Rsall);
$verify = mysql_query($query_limit_verify, $tams) or die(mysql_error());
//$verify_row = mysql_fetch_assoc($verify);
//$verify_row_num = mysql_num_rows($verify);



if (isset($_GET['totalRows_Rsall'])) {
    
    $totalRows_Rsall = $_GET['totalRows_Rsall'];
}
else {
    
    $all_Rsall = mysql_query($query);
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

$code = array();

//set Counter for all status
$release_status = 0;
$refere_status = 0;
$contact_status = 0;
$total_count = 0;

while ($verify_row = mysql_fetch_assoc($verify)) {
    
    $total_count = $total_count + 1;
    
    switch ($verify_row['status']){
        case 'release':
            $release_status = $release_status + 1;
            break;
        case 'refer':
            $refere_status = $refere_status + 1;
            break;
        case 'contact':
            $contact_status = $contact_status + 1;
            break;
        default :
            break;
    }
    
    $code[] = array(
            'id'        =>   $verify_row['id'],
            'jambregid' =>   $verify_row['stdid'],
            'fname'     =>   $verify_row['fname'],
            'lname'     =>   $verify_row['lname'],
            'mname'     =>   $verify_row['mname'],
            'pstdid'    =>   $verify_row['pstdid'],
            'ver_code'  =>   $verify_row['ver_code'],
            'progid1'   =>   $verify_row['progoffered'],
            'progname'  =>   $verify_row['progname'],
            'status'    =>   $verify_row['status'],
            'sittings'  =>   $verify_row['sittings'],
            'status'    =>   $verify_row['status'],
            'verified'  =>   $verify_row['verified'],
            'released_by'=>  $verify_row['released_by'],
            'treated_by'=>  $verify_row['treated_by'],
            'released_by_name' => $verify_row['rlsd_lname']." ". $verify_row['rlsd_fname'],
            'treated_by_name' => $verify_row['trtd_lname']." ". $verify_row['trtd_fname'],
        );  
}

$codes = json_encode($code);




$query = sprintf("SELECT colid, colname, coltitle "
                . "FROM college WHERE colid = %s", 
        GetSQLValueString($_SESSION['olv_veri_col'], 'int'));
$college = mysql_query($query, $tams) or die(mysql_error());
$row_college = mysql_fetch_assoc($college);


if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root . '/ict');
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
?>
<!doctype html>
<html ng-app="just">
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
                                        Prospective Student O'Level Verification Code Generation Page (<?= $row_college['coltitle']?>) Treated by  <?= (getAccess() == 20 || getAccess() == 24) ? "ALL": getSessionValue('lname')." ".getSessionValue('fname')?>
                                    </h3>
<!--                                    <ul class="tabs">
                                        <li class="active">
                                            <a href="treated.php" target="_new">My Treaded</a>
                                        </li>
                                    </ul>-->
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
                                                    <label class="control-label" for="textfield">Search by Name or Jamregid</label>
                                                    <div class="controls"> 
                                                        <form method="post" name="search" action="<?= $editFormAction; ?>">
                                                            <div class="input-append">
                                                                <input type="text" class="input-medium" name="seed" ng-model="seed" >
                                                                <button type="submit" name="search" class="btn btn-blue">Search</button>
                                                            </div>  
                                                        </form>
                                                    </div>
                                                </div> 
                                            </div>
                                            <div class="span6">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">Search by Date</label>
                                                    <div class="controls"> 
                                                        <form method="post" class="form-inline"name="search" action="<?= $editFormAction;?>">
                                                            <div class="input-append">
                                                                <input type="text" class="input-medium datepick" data-date-format="yyyy-mm-dd" value="<?= (isset($_POST['from'])) ? $_POST['from'] : '' ?>" id="textfield" name="from" placeholder="From">
                                                                <input type="text" class="input-medium datepick" data-date-format="yyyy-mm-dd" value="<?= (isset($_POST['to'])) ? $_POST['to'] : '' ?>" id="textfield" name="to"  placeholder="To">
                                                                <button type="submit" name="search" class="btn btn-blue">Search</button>
                                                            </div>  
                                                        </form>
                                                    </div>
                                                </div> 
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row-fluid">
                                        <div class="span3">
                                            <div class="form form-horizontal float-right">
                                                <label class="checkbox">
                                                    <input type="checkbox" ng-model="active" name="checkbox">  Enable Re-process
                                                </label>
                                            </div>
                                        </div>
                                        <div class="span2"><b>Total Released  : </b><?= $release_status ?></div>
                                        <div class="span2"><b>Total Refer : </b><?= $refere_status ?></div>
                                        <div class="span2"><b>Total Contact  : </b><?= $contact_status ?></div>
                                        <div class="span2"><b>Total Treated : </b><?= $totalRows_Rsall ?></div>
                                    </div>
                                    <div class='row-fluid'>
                                            <div class="span12">
                                                <div ng-if="loading">
                                                    <span>Processing</span> please wait <img src="../../../img/loading.gif">
                                                </div>
                                                <table class="table  table-condensed table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Jamb Reg No</th>
                                                            <th>Full Name</th>
                                                            <th>Sittings</th>
                                                            <th>Programme Choice</th>
                                                            <th>Verification Code</th>
                                                            <th>Status</th>
                                                            <th ng-if="active">Re-Process</th>
                                                            <?php if(in_array(getAccess(), ['20','24'])){?>
                                                            <th>Treated By</th>
                                                            <th>Released By</th>
                                                            <?php } ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        
                                                        <tr ng-repeat="cd in ver_code | filter : seed" class="fade in">
                                                                <td>
                                                                    <a  target="_blank" href="'../../../admission/viewform.php?stid={{cd.jambregid}}">{{cd.jambregid}} </a>    
                                                                </td>
                                                                <td>{{cd.lname}} {{cd.fname}} {{cd.mname}}</td>
                                                                <td>{{cd.sittings}}</td>
                                                                <td>{{cd.progname}}</td>
                                                                <td>{{cd.ver_code}}</td>
                                                                <td> <span class="badge badge-info">{{cd.status | uppercase}}</span></td>
                                                                <td ng-if="active">
                                                                    <div ng-if="!loading ">
                                                                        <input ng-if="cd.verified == 'FALSE'" type="button" name='submit' ng-click="revert($index, cd)" class="btn btn-small btn-warning" value="Revert Process">
                                                                        <span ng-if="cd.verified == 'TRUE'" class=" badge badge-success"> Verified</span>
                                                                    </div>
                                                                </td>
                                                                <?php if(in_array(getAccess(), ['20','24'])){?>
                                                                <td title="{{cd.treated_by_name}}">{{cd.treated_by}}</td>
                                                                <td title="{{cd.released_by_name}}">{{cd.released_by}}</td>
                                                                <?php } ?>
                                                            </tr>
                                                    </tbody>
                                                </table> 
                                            </div>
                                    </div>
                                    <p>&nbsp;</p>
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
                            <p>&nbsp;</p>   
                        </div>
                    </div>
                </div>
            </div>          
        </div>
<?php include INCPATH . "/footer.php" ?>
        
        <script>
            var veri_codes = <?= $codes ?>;
            var progs = <?= $program ?>;

            var app = angular.module('just', []);

            app.controller('pageCtrl', function($scope, $http){

                $scope.releaseCode = function(index, obj){
                          $scope.loading = true;
                          $http({
                              method : "POST",
                              url : "../../api/index.php?action=release_code",
                              data: {
                                  id :obj.id,
                                  user: obj.jambregid,
                                  prog_choice: obj.progname
                              }
                          }).then(function mySucces(response) {
                              deletItem(index);
                              $scope.loading = false;
                              alert('Operation successful');  
                          }, function myError(response) {
                              $scope.loading = false;
                              console.log(response);
                              alert('Unable to perform operation'+ response);
                          });    
                      };

                $scope.referToAdmin = function(index, obj){
                          $scope.loading = true;
                          $http({
                              method : "POST",
                              url : "../../api/index.php?action=refer",
                              data: {
                                  id :obj.id,
                                  user: obj.jambregid,
                                  prog_choice: obj.progname
                              }
                          }).then(function mySucces(response) {
                              deletItem(index);
                              $scope.loading = false;
                              alert(response.data);  
                          }, function myError(response) {
                              $scope.loading = false;
                              alert('Unable to perform operation'+ response.data);
                              console.log(response);
                          });    
                      };

                $scope.revert = function(index, obj){
                        $scope.loading = true;
                        $http({
                            method : "POST",
                            url : "../../api/index.php?action=revert",
                            data: {
                                id :obj.id,
                                stdid: obj.stdid,
                                colid: obj.colid,
                                ordid: obj.ordid
                                
                            }
                        }).then(function mySucces(response) {
                            deletItem(index);
                            $scope.loading = false;
                            alert(response.data); 
                        }, function myError(response) {
                            $scope.loading = false;
                             alert(response.data);
                            
                        });
                    };
                    
                    
                function deletItem(index){
                    $scope.ver_code.splice(index, 1);
                };
                
                
                
                $scope.ver_code = veri_codes;
                $scope.programme = progs;
                $scope.seletedItem = '';
                
                $scope.setSelected = function(val){
                   $scope.seletedItem  = val;
                };
            });
        </script>
    </body>
</html>
