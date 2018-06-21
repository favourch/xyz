<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');




$auth_users = "1,20,24";
check_auth($auth_users, $site_root . '/admin');





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
        . "FROM programme WHERE continued = 'Yes' ORDER BY progid DESC");
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
if (isset($_POST['search']) && $_POST['seed'] != '') {
    $seed = trim($_POST['seed']);
    $query_part = " p.fname LIKE '%" . $seed . "%' "
            . "OR p.lname LIKE  '%" . $seed . "%' "
            . "OR p.mname LIKE '%" . $seed . "%' "
            . "OR p.jambregid LIKE '%" . $seed . "%' AND ";
}

$query = sprintf("SELECT v.*, p.jambregid, p.pstdid, p.fname, p.lname, p.mname , pg.progname "
        . "FROM `verification` v "
        . "LEFT JOIN prospective p ON p.jambregid = v.stdid "
        . "LEFT JOIN programme pg ON pg.progid = p.progid1 "
        . "LEFT JOIN session s ON s.sesid = v.sesid "
        . "LEFT JOIN department d ON d.deptid = pg.deptid "
        . "LEFT JOIN college c ON c.colid = d.colid "
        . "WHERE %s s.sesid = %s "
        . "AND c.colid = %s "
        . "AND v.verified <> TRUE "
        . "AND v.release_code <> 'no' "
        . "AND released_by = %s ", 
        $query_part, 
        GetSQLValueString($cur_session, 'int'), 
        GetSQLValueString($_SESSION['olv_veri_col'], 'int'),
        GetSQLValueString(getSessionValue('uid'), 'text'));
echo $query_limit_verify = sprintf("%s LIMIT %d, %d", $query, $startRow_Rsall, $maxRows_Rsall);
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

    $code[] = array(
        'id' => $verify_row['id'],
        'jambregid' => $verify_row['jambregid'],
        'fname' => $verify_row['fname'],
        'lname' => $verify_row['lname'],
        'mname' => $verify_row['mname'],
        'pstdid' => $verify_row['pstdid'],
        'ver_code' => $verify_row['ver_code'],
        'progname' => $verify_row['progname'],
    );
};

$codes = json_encode($code);

if (isset($_POST['MM_Submit']) && $_POST['MM_Submit'] == 'change_prog') {
    $cur_user = $_POST['user'];
    $cur_detais = $_POST['id'];
    $new_prog = $_POST['new_prog'];

    $progQuery = sprintf("SELECT progname "
            . "FROM programme "
            . "WHERE progid = %s ", GetSQLValueString($new_prog, 'int'));
    $prgRS = mysql_query($progQuery) or die(mysql_error());
    $prgRS_row = mysql_fetch_assoc($prgRS);

    if ($_POST['action'] == 'yes') {

        mysql_query("BEGIN");

        $msg = "Congratulations! Your O`level result has being verified "
                . "But it does NOT met the requirement of the programme "
                . "you applied for.However your programme has changed to "
                . "<br/><strong>{$prgRS_row['progname']}</strong><br/> "
                . "Copy and paste the above verification code into the "
                . "bellow text box and click verified so that you"
                . " can proceed with your payment ";

        $query = sprintf("UPDATE verification "
                . "SET release_code = 'yes', msg= %s "
                . "WHERE id = %s ", GetSQLValueString($msg, 'text'), GetSQLValueString($cur_detais, 'text'));
        $verify1 = mysql_query($query, $tams) or die(mysql_error());
        $affected1 = mysql_affected_rows();

        $query1 = sprintf("UPDATE prospective "
                . "SET progoffered = %s "
                . "WHERE pstdid = %s ", GetSQLValueString($new_prog, 'int'), GetSQLValueString($cur_user, 'text'));
        $verify2 = mysql_query($query1, $tams) or die(mysql_error());

        if ($affected1) {
            mysql_query("COMMIT");
            $notification->set_notification('Operation successfull', 'success');
        }
    }

    //header('Location : process.php');
    header('Location: process.php');
    exit();
}

if (isset($_POST['submit'])) {

    $cur_user = $_POST['user'];
    $cur_detais = $_POST['id'];

    if ($_POST['action'] == 'yes') {
        mysql_query("BEGIN");

        $msg = "Congratulations! Your O`level result has being"
                . " verified and it met the requirement of the"
                . " programme you applied for. Copy and paste "
                . "the above  verification code in the bellow "
                . "text box and click verified so that you can"
                . " proceed with your payment ";

        $query = sprintf("UPDATE verification "
                . "SET release_code = 'yes', msg= %s "
                . "WHERE id = %s ", GetSQLValueString($msg, 'text'), GetSQLValueString($cur_detais, 'text'));
        $verify1 = mysql_query($query, $tams) or die(mysql_error());
        $affected1 = mysql_affected_rows();

        $query1 = sprintf("UPDATE prospective "
                . "SET progoffered = progid1 "
                . "WHERE pstdid = %s ", GetSQLValueString($cur_user, 'text'));
        $verify2 = mysql_query($query1, $tams) or die(mysql_error());

        if ($affected1) {
            mysql_query("COMMIT");
            $notification->set_notification('Operation successfull', 'success');
        }
    }

    //header('Location : process.php');
    header('Location: process.php');
    exit();
}

$query = sprintf("SELECT colid, colname, coltitle "
        . "FROM college WHERE colid = %s", GetSQLValueString($_SESSION['olv_veri_col'], 'int'));
$college = mysql_query($query, $tams) or die(mysql_error());
$row_college = mysql_fetch_assoc($college);


if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root . '/ict');
}
?>
<!doctype html>
<html ng-app="just">
<?php include INCPATH . "/header.php" ?>

    <script>
        var veri_codes = <?= $codes ?>;
        var progs = <?= $program ?>;

        var app = angular.module('just', []);

        app.controller('pageCtrl', function ($scope) {
            $scope.ver_code = veri_codes;
            $scope.programme = progs;
            $scope.seletedItem = '';
            $scope.setSelected = function (val) {
                $scope.seletedItem = val;
            };
        });
    </script>

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
                                        Prospective Student O'Level Verification Code Released by  (<?= getSessionValue('uid')?>)
                                    </h3>
                                    
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
                                                            <option value="<?= $row_ses['sesid'] ?>"><?= $row_ses['sesname'] ?></option>
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
                                                    <form method="post" name="search" action="my_treated.php">
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
                                                        <th>Jamb Reg No</th>
                                                        <th>Full Name</th>
                                                        <th>Programme Choice</th>
                                                        <th>Verification Code</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    <tr ng-repeat="cd in ver_code| filter : seed">
                                                        <td>
                                                            <a  target="_blank" href="'../../../admission/viewform.php?stid={{cd.jambregid}}">{{cd.jambregid}} </a>    
                                                        </td>
                                                        <td>{{cd.lname}} {{cd.fname}} {{cd.mname}}</td>
                                                        <td>{{cd.progname}}</td>
                                                        <td>{{cd.ver_code}}</td>
                                                        <td>
                                                            <form  method="POST" action="my_treated.php">
                                                                <input type="hidden" name='id' value="{{cd.id}}">
                                                                <input type="hidden" name='user' value="{{cd.pstdid}}">
                                                                <input type='hidden' name='action' value='yes'>
                                                                <input type="submit" name='submit' class="btn btn-small btn-green" value="Release Code"/>&nbsp;&nbsp; | &nbsp;&nbsp;<a href='#change_prog' data-toggle="modal"  ng-click="setSelected(cd)"class="btn btn-small btn-purple">Change Programme</a>
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
                <h3 id="myModalLabel">Change Programme {{seletedItem.jambregid}}</h3>
            </div>
            <form class="form-vertical" method="post" action="process.php">
                <div class="modal-body" style="min-height: 300px">
                    <table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <th>Student Reg. ID</th>
                                <td>{{seletedItem.jambregid}}</td>
                            </tr>
                            <tr>
                                <th>Full Name</th>
                                <td>{{seletedItem.lname}} {{seletedItem.fname}} {{seletedItem.mname}}</td>
                            </tr>
                            <tr>
                                <th>Programme Choice</th>
                                <td>{{seletedItem.progname}}</td>
                            </tr>
                            <tr>
                                <th>New Programme</th>
                                <td>
                                    <select name="new_prog"  id="new_prog" class="input-block-level" required="" >
                                        <option value="">--Choose--</option>
                                        <option ng-repeat="p in programme" value="{{p.progid}}" ng-selected="seletedItem.progid1 == p.progid">{{p.progname}}</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <input type="hidden" name="id" value="{{seletedItem.id}}">
                    <input type="hidden" name='user' value="{{seletedItem.jambregid}}">
                    <input type='hidden' name='action' value='yes'>
                    <input type="hidden" name="MM_Submit" value="change_prog">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Update and release Code</button>
                    <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
                </div>
            </form>
        </div>
<?php include INCPATH . "/footer.php" ?>
    </body>
</html>

