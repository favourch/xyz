<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');

$auth_users = "1,20,24,26";
check_auth($auth_users, $site_root . '/admin');


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if (isset($_POST['colid']) && $_POST['colid'] != '') {
    $_SESSION['olv_veri_col'] = $_POST['colid'];
}


if (!isset($_SESSION['olv_veri_col'])) {
    header('Location: index.php');
    exit();
}


$cur_session = -1;
if (isset($_GET['sid'])) {
    $cur_session = $_GET['sid'];
} else {
    $cur_session = $_SESSION['sesid'];
}




$query_ses = sprintf("SELECT * FROM session ORDER BY sesid DESC");
$ses = mysql_query($query_ses, $tams) or die(mysql_error());
$row_ses = mysql_fetch_assoc($ses);




$currentPage = $_SERVER["PHP_SELF"];
$maxRows_Rsall = 25;

$pageNum_Rsall = 0;
if (isset($_GET['pageNum_Rsall'])) {
    $pageNum_Rsall = $_GET['pageNum_Rsall'];
}
$startRow_Rsall = $pageNum_Rsall * $maxRows_Rsall;

//***********************************************



$query_part1 = '';
$conjuction = '';

if (isset($_POST['search']) && $_POST['seed'] != '') {
    $seed = trim($_POST['seed']);
    $conjuction = ' AND ';

    $query_part1 = "AND  (st.fname LIKE '%".$seed."%' "
                    . "OR st.lname LIKE  '%".$seed."%' "
                    . "OR st.mname LIKE '%".$seed."%' "
                    . "OR st.stdid LIKE '%".$seed."%'"
                    . "OR st.jambregid LIKE '%".$seed."%' ) ";
}

$query = sprintf("SELECT st.stdid, st.fname, st.lname, st.mname, st.jambregid,st.green_file, prg.progname FROM student st "
                . "JOIN programme prg ON st.progid = prg.progid "
                . "JOIN department d ON d.deptid = prg.deptid "
                . "JOIN college c ON c.colid = d.colid AND c.colid = %s "
                . "AND st.sesid = %s %s ", 
                GetSQLValueString($_SESSION['olv_veri_col'], 'int'),
                GetSQLValueString($cur_session, 'int'),
                $query_part1);
$query_limit_verify = sprintf("%s ORDER BY st.stdid ASC LIMIT %d, %d ", $query, $startRow_Rsall, $maxRows_Rsall);
$verify = mysql_query($query_limit_verify, $tams) or die(mysql_error());
//$verify_row = mysql_fetch_assoc($verify);
//$verify_row_num = mysql_num_rows($verify);



if (isset($_GET['totalRows_Rsall'])) {
    $totalRows_Rsall = $_GET['totalRows_Rsall'];
} else {
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


if (isset($_POST['MM_Submit']) && $_POST['MM_Submit'] == 'change_prog') {
    
    $cur_detals = $_POST['stdid'];

    if ($_POST['action'] == 'yes') {

        $query = sprintf("UPDATE student "
                        . "SET  green_file = 'TRUE' "
                        . "WHERE stdid = %s ",
                        GetSQLValueString($cur_detals, 'text'));
        $verify1 = mysql_query($query, $tams) or die(mysql_error());
        $affected1 = mysql_affected_rows();



        if ($affected1) {
            mysql_query("COMMIT");
            $notification->set_notification('Operation successfull', 'success');
        }
    }

    //header('Location : process.php');
    header('Location: process.php');
    exit();
}

if(isset($_POST['revert']) && $_POST['revert'] == "revert"){
    $query = sprintf("UPDATE student "
                    . "SET  green_file = 'FALSE' "
                    . "WHERE stdid = %s ", 
                    GetSQLValueString($_POST['user'], 'text'));
    $verify1 = mysql_query($query, $tams) or die(mysql_error());
    $affected1 = mysql_affected_rows();
    
    header('Location: process.php');
    exit();
}


$queryCol = sprintf("SELECT colid, colname, coltitle "
                . "FROM college WHERE colid = %s ", 
                GetSQLValueString($_SESSION['olv_veri_col'], 'int'));
$college = mysql_query($queryCol, $tams) or die(mysql_error());
$row_college = mysql_fetch_assoc($college);


if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root . '/ict');
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
                                        Prospective Student Green File Management Page (<?= $row_college['coltitle'] ?>)
                                    </h3>
                                    <ul class="tabs">
                                        <li class="active">
                                            <a href="treated.php" target="_tab">Submitted </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <div class="span4">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Session</label>
                                                <div class="controls controls-row">
                                                    <div class="input-append ">
                                                        <select name="name" input-block-level  onchange="sesfilt(this)">
                                                            <option value="">--Choose--</option>
                                                            <?php do { ?>
                                                                <option value="<?= $row_ses['sesid'] ?>" <?= ($row_ses['sesid'] == $cur_session) ? 'selected': ''?>><?= $row_ses['sesname'] ?></option>
                                                            <?php } while ($row_ses = mysql_fetch_assoc($ses)); ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div> 
                                        </div>
                                        <div class="span6">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Search by Name or Jamregid</label>
                                                <div class="controls">
                                                    <form method="post" name="search" action="<?= $editFormAction ?>">
                                                        <div class="input-append">
                                                            <input type="text" class="input-xlarge" name="seed" ng-model="seed" >
                                                            <button type="submit" name="search" class="btn btn-blue">Search</button>
                                                        </div>  
                                                    </form>
                                                </div>
                                            </div> 
                                        </div>
                                    </div>
                                    <div class='row-fluid'>
                                        <div class="span12">
                                            <table class="table  table-condensed table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Matric No</th>
                                                        <th>Jamb Reg No</th>
                                                        <th>Full Name</th>
                                                        <th>Programme Choice</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr ng-repeat="cd in ver_code| filter : seed">
                                                        <td>
                                                            <a  target="_blank" href="'../../../student/profile.php?stid={{cd.stdid}}">{{cd.stdid}} </a>    
                                                        </td>
                                                        <td>
                                                            <a  target="_blank" href="'../../../admission/viewform.php?stid={{cd.jambregid}}">{{cd.jambregid}} </a>    
                                                        </td>
                                                        <td>{{cd.lname}} {{cd.fname}} {{cd.mname}}</td>
                                                        <td>{{cd.progname}}</td>
                                                        <td>
                                                            <form method="post" action="process.php">
                                                                <a href='#change_prog' data-toggle="modal" ng-if="cd.green_file == 'FALSE'"  ng-click="setSelected(cd)"class="btn btn-small btn-green">Enable Green File</a>
                                                                <span class="badge badge-success" ng-if="cd.green_file == 'TRUE'">Submitted</span>
                                                                <button type="submit" name="revert" value="revert" class="btn btn-small btn-danger" ng-if="cd.green_file == 'TRUE'">Revert</button> 
                                                                <input type="hidden" name="user" value="{{cd.stdid}}">
                                                            </form>
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
        <div aria-hidden="false" 
             aria-labelledby="myModalLabel" 
             role="dialog" tabindex="-1" 
             class="modal hide fade" 
             id="change_prog">

            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h3 id="myModalLabel">Activate Green File for {{seletedItem.stdid}}</h3>
            </div>
            <form class="form-vertical" method="post" action="process.php">
                <div class="modal-body" style="min-height: 300px">
                    <div class="alert alert-info" style="text-align: center">You have chose to enable Green File for the student whose information is below <br/>Are you sure you want to proceed ?.</div>
                    <table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <th>Student Reg. ID</th>
                                <td>{{seletedItem.stdid}}</td>
                            </tr>
                            <tr>
                                <th>Full Name</th>
                                <td>{{seletedItem.lname}} {{seletedItem.fname}} {{seletedItem.mname}}</td>
                            </tr>
                            <tr>
                                <th>Programme Choice</th>
                                <td>{{seletedItem.progname}}</td>
                            </tr>
                        </tbody>
                    </table>
                    <input type="hidden" name="stdid" value="{{seletedItem.stdid}}">
                    <input type='hidden' name='action' value='yes'>
                    <input type="hidden" name="MM_Submit" value="change_prog">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Yes</button>
                    <button aria-hidden="true" data-dismiss="modal" class="btn btn-red">No</button>
                </div>
            </form>
        </div>
        <?php include INCPATH . "/footer.php" ?>
        <script>
            var veri_codes = <?= $codes ?>;
            var app = angular.module('just', []);
            app.controller('pageCtrl', function ($scope, $http) {
                $scope.ver_code = veri_codes;
                
                $scope.seletedItem = '';
                $scope.setSelected = function (val) {
                    $scope.seletedItem = val;
                };
                
            });
        </script> 
    </body>
</html>



