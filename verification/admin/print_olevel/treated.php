<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../../../path.php');

$auth_users = "1,20,21,22,23,24,28";
check_auth($auth_users, $site_root.'/admin');

$cur_session = -1;
if (isset($_GET['sid'])) {
    $cur_session = $_GET['sid'];
} else {
    $cur_session = $_SESSION['sesid'];
}

//get the college
$olv_veri_col = -1;
if (isset($_POST['colid']) && $_POST['colid'] != '') {
    $olv_veri_col = $_SESSION['olv_veri_col'] = $_POST['colid'];
}elseif(isset($_SESSION['olv_veri_col'])){
    $olv_veri_col = $_SESSION['olv_veri_col'];
}


if (isset($_POST['MM_treat']) && $_POST['MM_treat'] == 'form2') {
    $msg = '';
    
    if ($_POST['submit'] == 'Yes') {
        $msg = "<p style='color:green'>Your Olevel Result has been "
            . "PRINTED by the ICT <br/> and it is being forwarded to "
            . "Admission's Office for Verification .</p>";
    }elseif($_POST['submit'] == 'NO') {
        $msg = "<p style='color:red'>ICT could NOT PRINT your O'Level Result "
                . " <br/>Your Card details may be wrong "
                . "or maximum use reached. Please re-submit.</p>";
    }else{
        
        $msg =    "<p style='color:brown'>"
                . "This result verification is pending "
                . " due to 'Card Error'. "
                . "The Examnation or Card details of your "
                . "other O'Level Result was wrongly submitted. </p>";
    }

    $query = sprintf("UPDATE `olevel_veri_data` "
                    . "SET `treated` = 'Yes', approve = %s,"
                    . "return_msg = %s, date_treated=%s, who=%s "
                    . "WHERE id = %s", 
                    GetSQLValueString($_POST['submit'], 'text'),
                    GetSQLValueString($msg, 'text'), 
                    GetSQLValueString(date('Y-m-d'), 'date'), 
                    GetSQLValueString(getSessionValue('uid'), 'text'), 
                    GetSQLValueString($_POST['edit_id'], 'text'));
    $updateVerify = mysql_query($query, $tams) or die(mysql_error());

    if ($updateVerify) {
        $notification->set_notification("Operation Successful", 'success');
    }
    else {
        $notification->set_notification("Operation NOT Successful", 'error');
    }

    header("Location: " . $reroot);
    exit;

}


$query = sprintf("SELECT * FROM session ORDER BY sesid DESC");
$session = mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);

$query_col = sprintf("SELECT coltitle "
                    . "FROM college "
                    . "WHERE colid = %s ", 
                    GetSQLValueString($olv_veri_col, 'int'));
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);

$query = sprintf("SELECT * FROM programme WHERE continued = 'Yes' ORDER BY progname ASC");
$prog = mysql_query($query, $tams) or die(mysql_error());
$row_proramme = mysql_fetch_assoc($prog);

//For searching base on name or jambreg
$query_part1 ="";
if(isset($_POST['search']) && isset($_POST['seed']) && $_POST['seed'] != ''){
    $seed = trim($_POST['seed']);
     $query_part1 = "AND ol.stdid LIKE '%".$seed."%' ";
   
}

$currentPage = $_SERVER["PHP_SELF"];
$maxRows_Rsall = 25;
$pageNum_Rsall = 0;
if (isset($_GET['pageNum_Rsall'])) {
    $pageNum_Rsall = $_GET['pageNum_Rsall'];
}
$startRow_Rsall = $pageNum_Rsall * $maxRows_Rsall;

$query_limit_verify = sprintf("SELECT ol.*, s.sesname "
                            . "FROM olevel_veri_data ol, session s "
                            . "WHERE ol.treated = 'Yes' "
                            . "AND ol.usertype = %s %s "
                            . "AND ol.sesid = s.sesid "
                            . "AND ol.who = %s AND s.sesid = %s "
                            . "ORDER BY ol.print_no, ol.stdid "
                            . "ASC ", 
                            GetSQLValueString($_SESSION['olv_veri_who'], 'text'),
                            $query_part1,
                            GetSQLValueString(getSessionValue('uid'), 'text'), 
                            GetSQLValueString($cur_session, 'int'));

$query_limit_olevel_submission = sprintf("%s LIMIT %d, %d", 
                                        $query_limit_verify, 
                                        $startRow_Rsall, 
                                        $maxRows_Rsall);
$olevel_submissionRS = mysql_query($query_limit_olevel_submission, $tams) or die(mysql_error());
$olevel_submission = mysql_fetch_assoc($olevel_submissionRS);
$olevel_submission_num = mysql_num_rows($olevel_submissionRS);

$view_data = array();
do{
    $view_data[] = $olevel_submission;
}while($olevel_submission = mysql_fetch_assoc($olevel_submissionRS));



if (isset($_GET['totalRows_Rsall'])) {
    $totalRows_Rsall = $_GET['totalRows_Rsall'];
} else {
    $all_Rsall = mysql_query($query_limit_verify);
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

if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root . '/admin');
}


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

?>

<!doctype html>

<html ng-App="tams">

    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="pageController">

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
                                        <?= $row_col['coltitle'] ?> <?= ($_SESSION['olv_veri_who'] == 'pros')? 'Prospective ' : 'Returning '?>O'Level Result Verified By <?= getSessionValue('uid')?> 
                                        
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
                                                                    <option value="<?= $row_session['sesid'] ?>" <?= ($cur_session == $row_session['sesid']) ? 'selected' : '' ?>><?= $row_session['sesname'] ?></option>
                                                                <?php } while ($row_session = mysql_fetch_assoc($session)); ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div> 
                                            </div>
                                            <div class="span3">
                                                <form method="post" action="<?= $editFormAction?>">
                                                    <div class="control-group">
                                                        <label class="control-label" for="textfield">Search By Student ID</label>
                                                        <div class="controls controls-row">
                                                            <div class="input-append">
                                                                <input type="text" ng-model="seed" name="seed" class="input-large" placeholder="...">
                                                                <button type="submit" name="search" class="btn">Search</button>
                                                            </div>
                                                        </div>
                                                    </div>    
                                                </form>
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
                                    </div>
                                    <div class='row-fluid'>
                                        <div class="span12">
                                            <table class="table table-bordered table-condensed table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Reg No</th>
                                                        <th>Exam Type</th>
                                                        <th >Exam Year</th>
                                                        <th>Exam No</th>
                                                        <th>Card S/N</th>
                                                        <th>Card Pin</th>
                                                        <th>Session</th>
                                                        <th ng-if="!active">Status</th>
                                                        <th ng-if="active">Re-Process</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                               
                                                    <tr ng-repeat="dt in data | filter: seed">  
                                                            <td>{{dt.stdid}}</td>
                                                            <td>{{dt.exam_type}}</td> 
                                                            <td>{{dt.exam_year}}</td> 
                                                            <td>{{dt.exam_no}}</td> 
                                                            <td>{{dt.card_no}}</td>
                                                            <td>{{dt.card_pin}}</td>
                                                            <td>{{dt.sesname}}</td> 
                                                            <td ng-if="!active" style="color:green">{{dt.approve}}</td>
                                                            <td ng-if="active">
                                                                <form name='form2' method="POST" action="treated.php">
                                                                    <input type="hidden" name='edit_id' value="{{dt.id}}">
                                                                    <button type="submit" name='submit' class="btn btn-small btn-green" value="Yes">Printed</button>
                                                                    |
                                                                    <button type="submit" name='submit' class="btn btn-small btn-warning" value="Pending">Pend</button>
                                                                    | 
                                                                    <button type="submit" class="btn btn-small btn-red" name='submit' value="No">No</button>
                                                                    <input type="hidden" name='MM_treat' value="form2"/>
                                                                </form>   
                                                            </td>
                                                        </tr>
                                                    
                                                </tbody>
                                            </table>
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
<?php include INCPATH."/footer.php" ?>
    </body>
    <script type="text/javascript">

            app = angular.module('tams', []);
            viewData = <?= ($olevel_submission_num > 0) ? json_encode($view_data) : '[]'?>;
            app.controller('pageController',function($scope){
                
                $scope.data = viewData;
                
                $scope.click = function(){
                    location.href = "index.php";
                };
            });
    </script>

</html>