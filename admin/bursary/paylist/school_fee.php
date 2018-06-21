<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');

$auth_users = "1,2,20,21,23";
check_auth($auth_users, $site_root . '/admin');

$query_rssess = "SELECT * FROM `session` ORDER BY sesid DESC";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

$query_prog = sprintf("SELECT  p.progname,  p.progid "
        . "FROM programme p ");
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$query_level = sprintf("SELECT * FROM level_name");
$lvl = mysql_query($query_level, $tams) or die(mysql_error());
$row_level = mysql_fetch_assoc($lvl);

$level = 'all';
$filter = '';
$pid = 'all';

$ses = $row_rssess['sesid'];

if (isset($_GET['sid'])) {
    $ses = $_GET['sid'];
}

if (isset($_GET['lvl'])) {
    $level = $_GET['lvl'];

    if ($level != 'all') {
        $filter = 'AND ps.level = ' . GetSQLValueString($level, 'int');
    }
}

if (isset($_GET['pid'])) {
    $pid = $_GET['pid'];

    if ($pid != 'all') {
        $filter .= ' AND s.progid = ' . GetSQLValueString($pid, 'int');
    }
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$query_part = "";
if(isset($_POST['MM_Search']) && $_POST['MM_Search'] == 'search'){
    $query_part = sprintf(" AND st.date_time BETWEEN DATE(%s) AND DATE(%s) ",
                        GetSQLValueString($_POST['from'], 'text'),
                        GetSQLValueString($_POST['to'], 'text'));
}


$query_stud = sprintf("SELECT s.stdid, st.ordid, s.fname, s.lname, at.typename, ps.level, s.sex, se.stname, st.amt, st.percentPaid, st.date_time "
                    . "FROM student s "
                    . "JOIN state se ON s.stid = se.stid "
                    . "JOIN programme p ON p.progid = s.progid "
                    . "JOIN department d ON d.deptid = p.deptid "
                    . "JOIN schfee_transactions st ON st.matric_no = s.stdid "
                    . "JOIN payschedule ps ON ps.scheduleid = st.scheduleid "
                    . "JOIN admissions ad ON s.admid = ad.admid "
                    . "JOIN admission_type at ON ad.typeid = at.typeid "
                    . "WHERE st.sesid = %s "
                    . "AND st.status = 'APPROVED' "
                    . " %s %s "
                    . "ORDER BY s.stdid ASC ", 
                    GetSQLValueString($ses, "int"), 
                    $query_part,
                    GetSQLValueString($filter, "defined", $filter ));
    $stud = mysql_query($query_stud, $tams) or die(mysql_error());
    $row_stud = mysql_fetch_assoc($stud);
    $totalRows_stud = mysql_num_rows($stud);

$data = array();
do{
$data[] = $row_stud;
}while($row_stud = mysql_fetch_assoc($stud));

$data = json_encode($data);

// echo "stops here"; exit();

//while(list($key, $value) = each($data)){

//var_dump($value['stdid']);
//}


//
//exit();

//$query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, at.typename, ps.level, s.sex, st.amt, st.percentPaid "
//        . "FROM student s JOIN programme p "
//        . "ON p.progid = s.progid "
//        . "JOIN department d "
//        . "ON d.deptid = p.deptid "
//        . "JOIN schfee_transactions st "
//        . "ON st.matric_no = s.stdid "
//        . "JOIN payschedule ps "
//        . "ON ps.scheduleid = st.scheduleid "
//        . "JOIN admissions ad "
//        . "ON s.admid = ad.admid "
//        . "JOIN admission_type at "
//        . "ON ad.typeid = at.typeid "
//        . "WHERE st.sesid = %s "
//        . "AND st.status = 'APPROVED' "
//        . " %s "
//        . "ORDER BY s.stdid ASC ", 
//        GetSQLValueString($ses, "int"), 
//        GetSQLValueString($filter, "defined", $filter));
//$stud = mysql_query($query_stud, $tams) or die(mysql_error());
//$row_stud = mysql_fetch_assoc($stud);
//$totalRows_stud = mysql_num_rows($stud);



$name = 'Paid students';
?>
<!doctype html>
<html ng-app="list">
    <?php include INCPATH . "/header.php" ?>
<script>
var app = angular.module('list', []);

var data = <?php echo $data?>

app.controller('pageCtrl', function($scope){

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
                                        School Fee Payment List
                                    </h3>
                                    <ul class="tabs">
                                        <li class="active">
                                            <button class="btn"><i class="icon-user"></i> Total Paid <span class="label label-lightred"><?php echo$totalRows_stud ?></span></button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <div class="span3">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Search by <?= $programme_name ?></label>
                                                <div class="controls controls-row">
                                                    <select name='prog' onchange="progfilt(this)">
                                                        <option value="all" <?= ($pid == "all") ? 'selected' : ''; ?>>All</option>
                                                        <?php do{ ?>
                                                            <option value="<?php echo $row_prog['progid'] ?>" <?= ($pid == $row_prog['progid']) ? 'selected' : '' ?>><?php echo $row_prog['progname']; ?></option>
                                                        <?php }while($row_prog = mysql_fetch_assoc($prog))?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="span2">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Session</label>
                                                <div class="controls controls-row">
                                                    <select class="input input-medium" name='ses' onchange="sesfilt(this)">
                                                        <?php do{?>
                                                        <option value="<?php echo $row_rssess['sesid'] ?>" <?= ($ses == $row_rssess['sesid']) ? 'selected' : '' ?>><?php echo $row_rssess['sesname']; ?></option>
                                                        <?php }while($row_rssess = mysql_fetch_assoc($rssess))?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="span2">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Level </label>
                                                <div class="controls controls-row">
                                                    <select class="input input-small" onChange="lvlfilt(this)">
                                                        <option value="all" <?php if ($level == "all") echo 'selected'; ?>>All</option>
                                                        <?php do { ?>
                                                            <option value="<?= $row_level['levelid'] ?>" <?= ($level == $row_level['levelid']) ? 'selected' : '' ?>><?= $row_level['levelname']; ?></option>
                                                        <?php } while ($row_level = mysql_fetch_assoc($lvl)) ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="span3">
                                            <form action="<?= $editFormAction; ?>" method="post">
                                                <input type="hidden" name="MM_Search" value="search">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">Search by Date</label>
                                                    <div class="controls">
                                                        <div class="input-append input-prepend">
                                                            <input type="text" name="from" class="input-small datepick" placeholder="From" data-date-format="yyyy-mm-dd">
                                                            <input type="text" name="to" class="input-small datepick" placeholder="To" data-date-format="yyyy-mm-dd">
                                                            <button type="submit" class="btn ">Search</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>  
                                    <table width="670" class="table table-striped table-condensed">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Matric</th>
                                                <th>Name</th>
                                                <th>Sex</th>
                                                <th>State</th>
                                                <th>Ordid</th>
                                                <th>Admission Mode</th>
                                                <th>Level</th>
                                                <th>Amount Paid</th>
                                                <th>Percentage Paid</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody> 
                                            <tr ng-repeat="dt in rec">
                                                <td>{{$index + 1}}</td>
                                                <td>
                                                    <a href="../student/profile.php?stid={{dt.stdid}}">{{dt.stdid}}</a>
                                                </td>
                                                <td>
                                                    {{dt.lname}} {{dt.fname}} {{dt.mname}}
                                                </td>
                                                <td>{{dt.sex}}</td>
                                                <td>{{dt.stname}}</td>
                                                <td>{{dt.ordid}}</td>
                                                <td>{{dt.typename}}</td>
                                                <td>{{dt.level}}</td>
                                                <td>{{dt.amt}}</td>
                                                <td>{{dt.percentPaid}}</td>
                                                <td>{{dt.date_time}}</td>
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