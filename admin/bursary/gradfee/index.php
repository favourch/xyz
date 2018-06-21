<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../../../path.php');

$auth_users = "1,20,21,23";
check_auth($auth_users, $site_root . '/admin');

$cur_session = -1;
if(isset($_GET['sid'])){
    $cur_session = $_GET['sid'];
}else{
    $cur_session = $_SESSION['sesid'];
}



$query_ses = sprintf("SELECT * FROM session WHERE status = 'TRUE' ORDER BY sesid DESC");
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
    
     $query_part1 = "AND  (s.fname LIKE '%".$seed."%' "
                    . "OR s.lname LIKE  '%".$seed."%' "
                    . "OR s.mname LIKE '%".$seed."%' "
                    . "OR s.stdid LIKE '%".$seed."%' ) ";
   
}

 $query = sprintf("SELECT * FROM student s "
                    . "JOIN programme p ON s.progid = p.progid "
                    . "WHERE s.level > 3 "
                    . "AND (s.status = 'Graduating' OR s.status = 'Graduated') "
                    . "%s ", $query_part1);

$query_limit_verify = sprintf("%s ORDER BY s.stdid ASC LIMIT %d, %d  ", $query, $startRow_Rsall, $maxRows_Rsall);
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


while ($verify_row = mysql_fetch_assoc($verify)) {
   
    $code[] = $verify_row;
        
}

$codes = json_encode($code);




if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root . '/ict');
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


//Update transaction 
if(isset($_POST['update']) && $_POST['update'] == 'yes'){
    $transExistSQL = sprintf("SELECT can_no "
                        . "FROM gradfees_transactions "
                        . "WHERE can_no = %s "
                        . "AND status = 'APPROVED'", 
                        GetSQLValueString($_POST['stdid'], 'text'));
    $transExistRS = mysql_query($transExistSQL) or die(mysql_error());
    $transExist_num_row = mysql_num_rows($transExistRS);
    
    if($transExist_num_row > 0){
        $msg = "This payment has already been Updated ";
        $notification->set_notification($msg, 'error');
    }else{
        $sql = sprintf("INSERT INTO gradfees_transactions ( "
                    . " can_no, "
                    . "can_name, reference, "
                    . "year, status, "
                    . "date_time, ordid, "
                    . "sessionid, percent, "
                    . " amt, processed_by, sesid ) "
                    . "VALUES(%s, %s, %s, %s , %s ,%s ,%s ,%s ,%s ,%s, %s, %s)", 
                    GetSQLValueString($_POST['stdid'], 'text'), 
                    GetSQLValueString($_POST['fullname'], 'text'), 
                    GetSQLValueString(uniqid('tmfb_ref_'), 'text'), 
                    GetSQLValueString(date('Y'), 'text'), 
                    GetSQLValueString('APPROVED', 'text'), 
                    GetSQLValueString(date('d-m-Y'), 'text'), 
                    GetSQLValueString(uniqid('tmfb_ord'), 'text'), 
                    GetSQLValueString(uniqid('tmfb_ses_'), 'text'), 
                    GetSQLValueString(100, 'int'), 
                    GetSQLValueString('NGN5,000.00', 'text'), 
                    GetSQLValueString(getSessionValue('uid'), 'text'),
                    GetSQLValueString($row_ses['sesid'], 'int'));
        mysql_query($sql, $tams) or die(mysql_error());

        header(sprintf("Location: %s ", $editFormAction));
        die();
    }
    
            

    
    
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
                                    <h3><i class="icon-barcode"></i>
                                        Graduation/Gown Fees Payments 
                                    </h3>
                                    <ul class="tabs">
                                        <li class="active">
                                            <a href="paylist.php" target="_new">Pay List</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="box-content">
                                    <div class="well well-small">
                                        <div class="row-fluid">
<!--                                            <div class="span3">
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
                                            </div>-->
                                            <div class="span3">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">Search by Name or Matric No</label>
                                                    <div class="controls"> 
                                                        <form method="post" name="search" action="<?= $editFormAction?>">
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
                                    
                                    <div class='row-fluid'>
                                            <div class="span12">
                                                <div ng-if="loading">
                                                    <span>Processing</span> please wait <img src="../../../img/loading.gif">
                                                </div>
                                                <table class="table  table-condensed table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Student ID</th>
                                                            <th>Full Name</th>
                                                            <th>Programme</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody> 
                                                        <tr ng-repeat="cd in ver_code | filter : seed" class="fade in">
                                                            <td>
                                                                <a  target="_blank" href="#">{{cd.stdid}} </a>    
                                                            </td>
                                                            <td>{{cd.lname}} {{cd.fname}} {{cd.mname}}</td>
                                                            <td>{{cd.progname}}</td>
                                                            <td>
                                                                <?php if(in_array(getAccess(), ['20','23'])){?>
                                                                    <a  
                                                                       ng-click="setSelected(cd)" 
                                                                       class="btn btn-small btn-success" 
                                                                       data-toggle="modal"
                                                                       role="button" 
                                                                       href="#modal-2">Pay</a>  
                                                                <?php } ?>
                                                            </td>  
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
        <div id="modal-2" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="width: 600px">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">Confirm Payment </h3>
            </div>
            <form method="post" action="<?= $editFormAction; ?>">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        You have choose to activate Graduation/Gown Fee Payment for the student with the information below:
                    </div>
                    <div class="well well-small">
                        <span><b>Student ID :</b> {{seletedItem.stdid}}</span><br/>
                        <span><b>Full Name :</b> {{seletedItem.lname}} {{seletedItem.fname}} {{seletedItem.mname}}</span><br/>
                        <span><b>Department :</b> {{seletedItem.progname}} </span><br/>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="stdid" value="{{seletedItem.stdid}}">
                    <input type="hidden" name="fullname" value="{{seletedItem.lname}} {{seletedItem.fname}} {{seletedItem.mname}}">
                    <input type="hidden" name="update" value="yes">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn btn-primary" type="submit">Set to Paid</button>
                </div>
            </form>
        </div>
<?php include INCPATH . "/footer.php" ?>
        
        <script>
            var veri_codes = <?= $codes ?>;
            var progs = <?= $program ?>;

            var app = angular.module('just', []);

            app.controller('pageCtrl', function($scope, $http){
                
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
