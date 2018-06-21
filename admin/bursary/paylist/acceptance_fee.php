<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');



$auth_users = "1,2,20,21,22,23";
check_auth($auth_users, $site_root . '/admin');

$query_rssess = "SELECT * FROM `session`  ORDER BY sesid DESC";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

$sesname = $row_rssess['sesname'];

$query_level = sprintf("SELECT * FROM level_name");
$lvl = mysql_query($query_level, $tams) or die(mysql_error());
$row_level = mysql_fetch_assoc($lvl);


$query_prog = sprintf("SELECT  p.progname,  p.progid "
        . "FROM programme p  ");
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$level = 'all';
$filter = '';
$pid = 'all';

$ses = $row_rssess['sesid'];

if (isset($_GET['sid'])) {
    $ses = $_GET['sid'];
}

//if (isset($_GET['lvl'])) {
//    $level = $_GET['lvl'];
//
//    if ($level != 'all') {
//        $filter = 'AND st.level = ' . GetSQLValueString($level, 'int');
//    }
//}

if (isset($_GET['pid'])) {
    $pid = $_GET['pid'];

    if ($pid != 'all') {
        $filter .= ' AND s.progid1 = ' . GetSQLValueString($pid, 'int');
    }
}




$query_stud = sprintf("SELECT st.can_no, s.fname, s.lname, s.mname, adt.typename, s.sex, st.percentPaid, adt.typename , st.amt "
        . "FROM prospective s "
        . "JOIN programme p ON p.progid = s.progoffered "
        . "JOIN accfee_transactions st "
        . "ON st.can_no = s.jambregid "
        . "JOIN admissions ad ON ad.admid = s.admid "
        . "JOIN admission_type adt ON adt.typeid = ad.typeid "
        . "WHERE st.sesid = %s "
        . "AND st.status = 'APPROVED' "
        . " %s "
        . "ORDER BY s.lname ASC ", GetSQLValueString($ses, "int"), GetSQLValueString($filter, "defined", $filter));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

$data = array();

do {

    $data[] = $row_stud;
} while ($row_stud = mysql_fetch_assoc($stud));

$data = json_encode($data);

$name = 'Paid students';

if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}

$page_title = "Tasued";
?>

<!doctype html>
<html ng-app="list">
    <?php include INCPATH . "/header.php" ?>
    <script>
        var app = angular.module('list', []);

        var data = <?php echo $data ?>

        app.controller('pageCtrl', function ($scope) {

            $scope.rec = data;

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
                                    <h3><i class="icon-money"></i>
                                        Acceptance Fee Payment List
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <div class="span3">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Search by <?= $programme_name ?></label>
                                                <div class="controls controls-row">
                                                    <select name='prog' onchange="progfilt(this)">
                                                        <option value="all" <?= ($pid == "all") ? 'selected' : ''; ?>>All</option>
                                                        <?php for (; $row_prog != false; $row_prog = mysql_fetch_assoc($prog)) { ?>
                                                            <option value="<?php echo $row_prog['progid'] ?>" <?= ($pid == $row_prog['progid']) ? 'selected' : '' ?>><?php echo $row_prog['progname']; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="span3">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Session</label>
                                                <div class="controls controls-row">
                                                    <select name='ses' onchange="sesfilt(this)">
                                                        <?php for (; $row_rssess != false; $row_rssess = mysql_fetch_assoc($rssess)) { ?>
                                                            <option value="<?php echo $row_rssess['sesid'] ?>" <?= ($ses == $row_rssess['sesid']) ? 'selected' : '' ?>><?php echo $row_rssess['sesname']; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="span3">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">&nbsp;</label>
                                                <div class="controls controls-row">

                                                    <button class="btn"><i class="icon-user"></i> Total Paid <span class="label label-lightred"><?= $totalRows_stud ?></span></button>

                                                </div>
                                            </div>
                                        </div>
                                    </div>  
                                    <table width="670" class="table table-striped table-condensed">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Jamb Reg.ID</th>
                                                <th>Name</th>
                                                <th>Sex</th>
                                                <th>Admission Mode</th>
                                                <th>Level</th>
                                                <th>Amount</th>
                                                <th>Percentage Paid</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr ng-repeat="dt in rec">
                                                <td>{{$index + 1}}</td>
                                                <td>
                                                    <a href="../../../admission/viewform.php?stid={{dt.can_no}}">
                                                        {{dt.can_no}}
                                                    </a>
                                                </td>
                                                <td>
                                                    {{dt.lname}} {{dt.fname}} {{dt.mname}}
                                                </td>
                                                <td>{{dt.sex}}</td>
                                                <td>{{dt.typename}}</td>
                                                <td>Prospective</td>
                                                <td>{{dt.amt}}</td>
                                                <td>{{dt.percentPaid + "  %"}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH . "/footer.php" ?>
    </body>
</html>