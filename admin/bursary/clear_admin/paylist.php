<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../../../path.php');

$auth_users = "1,20,23";
check_auth($auth_users, $site_root . '/admin');

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
                    . "OR p.stdid LIKE '%".$seed."%' ) ";
   
}

if(isset($_POST['search']) 
        && isset($_POST['from']) 
        && isset($_POST['to']) 
        && $_POST['from'] != '' 
        && $_POST['to'] != ''){
    $from = GetSQLValueString(trim($_POST['from']), 'date');
    $to = GetSQLValueString(trim($_POST['to']), 'date');
    $query_part = sprintf("AND
                            ovd.date_treated >= {$from} 
                            AND
                            ovd.date_treated <= {$to} ");
}

$query = sprintf("SELECT * "
                . "FROM clearance_transactions st "
                . "JOIN student p ON p.stdid = st.matric_no "
                . "AND st.status = 'APPROVED' "
                . "JOIN programme pgm ON p.progid = pgm.progid  "
                . "WHERE  st.sesid = %s %s",  
                GetSQLValueString($cur_session, 'int'), $query_part1);

$query_limit_verify = sprintf("%s ORDER BY st.can_no ASC LIMIT %d, %d  ", $query, $startRow_Rsall, $maxRows_Rsall);
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
    $updateSQL = sprintf("UPDATE  schfee_transactions "
            . "SET reg_fee = 'TRUE' "
            . "WHERE ordid = %s ", GetSQLValueString($_POST['ordid'], 'text'));
    $updateRS = mysql_query($updateSQL) or die(mysql_error());

    header(sprintf("Location: %s ", $editFormAction));
    die();
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
                                        Final Year Clearance Fees Payments (Pay List) 
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
                                                    <label class="control-label" for="textfield">Search by Name or Jamregid</label>
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
                                                            <th>Amount Paid</th>
                                                            <th>Date Paid</th>
<!--                                                            <th>Actions</th>-->
                                                        </tr>
                                                    </thead>
                                                    <tbody> 
                                                        <tr ng-repeat="cd in ver_code | filter : seed" class="fade in">
                                                            <td>
                                                                <a  target="_blank" href="#">{{cd.stdid}} </a>    
                                                            </td>
                                                            <td>{{cd.lname}} {{cd.fname}} {{cd.mname}}</td>
                                                            <td>{{cd.progname}}</td>
                                                            <td>{{cd.amt}}</td>
                                                            <td>{{cd.date_time}}</td>
<!--                                                            <td>
                                                                <?php if(in_array(getAccess(), ['20','24', '26'])){?>
                                                                    <a  
                                                                       ng-click="setSelected(cd)" 
                                                                       class="btn btn-small btn-success" 
                                                                       data-toggle="modal"
                                                                       role="button" 
                                                                       href="#modal-2">Pay</a>  
                                                                <?php } ?>
                                                            </td>  -->
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
<!--        <div id="modal-2" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="width: 600px">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">Confirm Payment </h3>
            </div>
            <form method="post" action="<?$editFormAction; ?>">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        You have choose to activate payment for the student with the bellow information
                    </div>
                    <div class="well well-small">
                        <span><b>Studnet ID :</b> {{seletedItem.can_no}}</span><br/>
                        <span><b>Full Name :</b> {{seletedItem.lname}} {{seletedItem.fname}} {{seletedItem.mname}}</span><br/>
                        <span><b>Department :</b> {{seletedItem.progname}} </span><br/>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="ordid" value="{{seletedItem.ordid}}">
                    <input type="hidden" name="update" value="yes">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn btn-primary" type="submit">Set to Paid</button>
                </div>
            </form>
        </div>-->
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
