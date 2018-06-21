<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../../../path.php');

$auth_users = "1,20,26,24";
check_auth($auth_users, '/' . $site_root . '/admin');


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


$query_prog = sprintf("SELECT progid, progname "
        . "FROM programme "
        . "WHERE continued = 'Yes' "
        . "ORDER BY progid DESC");
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);

$programs = array();
while ($row_prog = mysql_fetch_assoc($prog)) {
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
$query_part1 = "";
if (isset($_POST['search']) && isset($_POST['seed']) && $_POST['seed'] != '') {
    $seed = trim($_POST['seed']);
    $query_part1 = "AND  (p.fname LIKE '%" . $seed . "%' "
            . "OR p.lname LIKE  '%" . $seed . "%' "
            . "OR p.mname LIKE '%" . $seed . "%' "
            . "OR v.stdid LIKE '%" . $seed . "%' ) ";
}

if (isset($_POST['search']) && isset($_POST['from']) && isset($_POST['to']) && $_POST['from'] != '' && $_POST['to'] != '') {
    $from = GetSQLValueString(trim($_POST['from']), 'date');
    $to = GetSQLValueString(trim($_POST['to']), 'date');

    $query_part = sprintf(" AND v.created_date BETWEEN CAST(%s AS DATE) AND CAST(%s AS DATE)", $from, $to);
}



if ($_SESSION['olv_veri_who'] == 'stud') {
    $student_type = "Returning Students";

    $query = sprintf("SELECT v.*, p.*,c.colid, c.colname, d.deptid, d.deptname, prg.progname "
            . "FROM OLVL_verification v "
            . "JOIN student p ON v.stdid = p.stdid "
            . "JOIN programme prg ON p.progid = prg.progid "
            . "JOIN department d ON d.deptid = prg.deptid "
            . "JOIN college c ON c.colid = d.colid "
            . "AND c.colid = %s "
            . "AND v.olevel_submit = 'TRUE'  %s "
            . "WHERE v.sesid = %s "
            . "AND v.status <> '0' %s ", 
            GetSQLValueString($_SESSION['olv_veri_col'], 'int'), 
            $query_part, 
            GetSQLValueString($cur_session, 'int'), 
            $query_part1);
} else {
    $student_type = "Prospective Students";

    $query = sprintf("SELECT v.*, p.*,c.colid, c.colname, d.deptid, d.deptname, prg.progname FROM OLVL_verification v "
            . "JOIN prospective p ON v.stdid = p.jambregid "
            . "JOIN programme prg ON p.progid1 = prg.progid "
            . "JOIN department d ON d.deptid = prg.deptid "
            . "JOIN college c ON c.colid = d.colid "
            . "AND c.colid = %s "
            . "AND v.olevel_submit = 'TRUE'  %s "
            . "WHERE v.sesid = %s "
            . "AND v.status <> '0' %s ", 
            GetSQLValueString($_SESSION['olv_veri_col'], 'int'), 
            $query_part, 
            GetSQLValueString($cur_session, 'int'), 
            $query_part1);
}

$query_limit_verify = sprintf("%s ORDER BY  v.stdid ASC LIMIT %d, %d  ", $query, $startRow_Rsall, $maxRows_Rsall);
$verify = mysql_query($query_limit_verify, $tams) or die(mysql_error());




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

    $code[] = array(
        'id' => $verify_row['id'],
        'jambregid' => $verify_row['stdid'],
        'fname' => $verify_row['fname'],
        'lname' => $verify_row['lname'],
        'mname' => $verify_row['mname'],
        'pstdid' => $verify_row['pstdid'],
        'ver_code' => $verify_row['ver_code'],
        'progid1' => $verify_row['progoffered'],
        'progname' => $verify_row['progname'],
        'phone' => $verify_row['phone'],
        'email' => $verify_row['email'],
        'verified'=> $verify_row['verified'],
        'status'=> $verify_row['status']
    );
}

$codes = json_encode($code);




$query = sprintf("SELECT colid, colname, coltitle "
        . "FROM college WHERE colid = %s", GetSQLValueString($_SESSION['olv_veri_col'], 'int'));
$college = mysql_query($query, $tams) or die(mysql_error());
$row_college = mysql_fetch_assoc($college);


if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root . '/admin');
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

//Set status of verification to release 
if (isset($_POST['release'])) {

    $msg = "Congratulations! Your O`level result has been"
            . " verified and it met the requirement of the"
            . " programme you applied for i.e <br/>(<strong>{$_POST['progname']}</strong>)<br/>. Copy and paste "
            . "the above  verification code in the "
            . "text box below and click verified so that you can"
            . " proceed with your payment ";

    $SQL = sprintf("UPDATE OLVL_verification "
            . "SET release_code = 'yes', "
            . "status = 'release', "
            . "released_by = %s, "
            . "released_date = NOW(), "
            . "treated = 'yes', "
            . "msg = %s , "
            . " WHERE stdid = %s", GetSQLValueString(getSessionValue('uid'), 'text'), GetSQLValueString($_POST['stdid'], 'text'), GetSQLValueString($msg, 'text')
    );
    mysql_query($SQL, $tams) or die(mysql_error());

    $SQL2 = sprintf("UPDATE prospective "
            . "SET adminstatus = 'yes' "
            . "WHERE jambregid = %s ", GetSQLValueString($_POST['stdid'], 'text'));
    mysql_query($SQL2, $tams) or die(mysql_error());

    $$bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'>O'Level Result Verification </h3><p>%s</p>", $msg);


    if (isset($_POST['email']) && $_POST['email'] != "") {
        sendHtmlEmail($_POST['email'], "Olevel Verification", $bd, 'admission@tasued.edu.ng', "Admission Office (Olevel Result Verification unit)");
    }
    header("Location: treated.php");
    die();
}


if (isset($_POST['refer'])) {
    $msg = " Your O`level result has been verified But "
            . "did not meet the requirement of the programme applied for i.e "
            . " <br/>(<strong>{$_POST['progname']}</strong>)<br/>."
            . "However, Kindly wait for the Admission Office decision on your application. Check back in 2 working days";

    $SQL = sprintf("UPDATE OLVL_verification "
            . "SET status = 'refer', "
            . "refered_date = NOW(), "
            . "refered_by = %s, "
            . "msg = %s, "
            . "refer = 'yes', "
            . "treated = 'yes' "
            . "WHERE stdid = %s ", 
            GetSQLValueString(getSessionValue('uid'), 'text'), 
            GetSQLValueString($msg, 'text'), 
            GetSQLValueString($_POST['stdid'], 'text'));

    mysql_query($SQL, $tams) or die(mysql_error());

    $SQL2 = sprintf("UPDATE prospective "
            . "SET adminstatus = 'yes' "
            . "WHERE jambregid = %s ", 
            GetSQLValueString($_POST['stdid'], 'text'));
    mysql_query($SQL2, $tams) or die(mysql_error());

    $bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'>O'Level Result Verification </h3><p>%s</p>", $msg);

    if (isset($_POST['email']) && $_POST['email'] != "") {
        sendHtmlEmail($_POST['email'], "Olevel Verification", $bd, 'admission@tasued.edu.ng', "Admission Office (Olevel Result Verification unit)");
    }



    header("Location: treated.php");
    die();
}


if (isset($_POST['terminate'])) {
    $msg = "Sorry your O'Level Result does not met the minimum admission requirement for the "
            . " programme you applied for i.e <br/>(<strong>{$_POST['progname']}</strong>)<br/>. "
            . "therefor your application is terminated. Contact the Admission Office for advice";

    $SQL = sprintf("UPDATE OLVL_verification "
            . "SET  "
            . "status = 'terminate', "
            . "refered_by = %s, "
            . "refered_date = NOW(), "
            . "treated = 'yes', "
            . "msg = %s "
            . " WHERE stdid = %s ", GetSQLValueString(getSessionValue('uid'), 'text'), GetSQLValueString($msg, 'text'), GetSQLValueString($_POST['stdid'], 'text')
    );
    mysql_query($SQL, $tams) or die(mysql_error());

    $SQL2 = sprintf("UPDATE prospective "
            . "SET adminstatus = 'no' "
            . "WHERE jambregid = %s ", GetSQLValueString($_POST['stdid'], 'text'));
    mysql_query($SQL2, $tams) or die(mysql_error());
    $$bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'>O'Level Result Verification </h3><p>%s</p>", $msg);


    if (isset($_POST['email']) && $_POST['email'] != "") {
        sendHtmlEmail($_POST['email'], "Olevel Verification", $bd, 'admission@tasued.edu.ng', "Admission Office (Olevel Result Verification unit)");
    }

    header("Location: treated.php");
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
<?= $student_type ?> O'Level Verification Code Generation Page (<?= $row_college['coltitle'] ?>) Treated
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="well well-small">
                                        <div class="row-fluid">
                                            <div class="span2">
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
                                                    <label class="control-label" for="textfield">Search by Name or Jambregid</label>
                                                    <div class="controls"> 
                                                        <form method="post" name="search" action="<?= $editFormAction ?>">
                                                            <div class="input-append">
                                                                <input type="text" class="input-medium" name="seed" ng-model="seed" >
                                                                <button type="submit" name="search" class="btn btn-blue">Search</button>
                                                            </div>  
                                                        </form>
                                                    </div>
                                                </div> 
                                            </div>
                                            <div class="span7">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">Search by Date</label>
                                                    <div class="controls"> 
                                                        <form method="post" class="form-inline"name="search" action="<?= $editFormAction ?>">
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

                                    <div class='row-fluid'>

                                        <div class="span12">
                                            <div ng-if="loading">
                                                <span>Processing</span> please wait <img src="../../../img/loading.gif">
                                            </div>
                                            <table class="table  table-condensed table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th width="5%">#</th>
                                                        <th width="10%">Jamb Reg No</th>
                                                        <th width="20%">Full Name</th>
                                                        <th width="30%">Programme Choice</th>
                                                        <th width="10%">Verification Code</th>
                                                        <th width="10%">Use Code</th>
                                                        <th width="5%">Status</th>
                                                        <th width="10%">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    <tr ng-repeat="cd in ver_code| filter : seed" class="fade in">
                                                        <td>{{$index + 1}}</td>
                                                        <td>
                                                            <a  target="_blank" href="'../../../admission/viewform.php?stid={{cd.jambregid}}">{{cd.jambregid}} </a>    
                                                        </td>
                                                        <td>{{cd.lname}} {{cd.fname}} {{cd.mname}}</td>
                                                        <td>{{cd.progname}}</td>
                                                        <td>{{cd.ver_code}}</td>
                                                        <td>{{cd.verified}}</td>
                                                        <td>{{cd.status}}</td>
                                                        <td>
                                                            <a class="btn btn-small btn-blue" 
                                                               href="#view_result" role="button" 
                                                               data-toggle="modal" ng-click="fetchResult(cd); setSelected(cd)"><i class="icon icon-eye-open"></i>View</a>
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
<?php include INCPATH . "/footer.php" ?>
        <div id="view_result" class="modal hide fade" style="width: 80%; margin-left: -655px !important;" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">O&apos;Level Result for ( {{seletedItem.jambregid}} ) {{seletedItem.fname}} {{seletedItem.lname}} {{seletedItem.mname}} - {{seletedItem.progname}}</h3>
            </div>
            <form method="post" action="<?= $editFormAction ?>">
                <div class="modal-body">
                    <div class="row-fluid" ng-if="loading">
                        <div>
                            <img src="../../giphy.gif">
                        </div>
                    </div>
                    <div class="row-fluid" ng-if="fetched_result" >
                        <div class="span6 well well-large" ng-repeat="res in fetched_result" ng-bind-html="renderHTML(res.result_table)"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <input type="hidden" name="stdid" value="{{seletedItem.jambregid}}">
                    <input type="hidden" name="email" value="{{seletedItem.email}}">
                    <input type="hidden" name="phone" value="{{seletedItem.phone}}">
                    <input type="hidden" name="progname" value="{{seletedItem.progname}}">
                    <?php if (in_array(getAccess(), ['20', '24', '26', '28'])) { ?>
                    <button type="submit" name="release" class="btn btn-primary">Release Code</button>
                    <button type="submit" name="refer" class="btn btn-warning">Refer to Admin.</button>
                    <button type="submit" name="terminate" class="btn btn-danger">Deny Admission</button>
                    <?php } ?>
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                </div>
            </form>
        </div>
        <script>
                    var veri_codes = <?= $codes ?>;
                    var progs = <?= $program ?>;

                    var app = angular.module('just', ['ngSanitize']);

                    app.controller('pageCtrl', function ($scope, $http, $sce) {



                        $scope.ver_code = veri_codes;
                        $scope.programme = progs;
                        $scope.seletedItem = '';

                        $scope.setSelected = function (val) {
                            $scope.seletedItem = val;
                        };

                        $scope.loading = false;
                        $scope.fetched_result = false;
                        $scope.fetchResult = function (obj) {
                            $scope.loading = true;
                            $http({
                                method: "POST",
                                url: "../../api/index.php?action=fetch_result",
                                data: {
                                    user: obj.jambregid,
                                }
                            }).then(function mySucces(response) {
                                $scope.fetched_result = response.data;
                                $scope.loading = false;

                            }, function myError(response) {
                                $scope.loading = false;
                                alert('Unable to perform operation' + response);
                            });
                        };

                        $scope.renderHTML = function (html_code) {
                            var decoded = angular.element('<textarea />').html(html_code).text();
                            return $sce.trustAsHtml(decoded);
                        };
                    });
        </script>
    </body>
</html>
