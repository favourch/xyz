<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');
$img_dir = "../img/user/student";

$auth_users = "1,2,3,4,5,6,20,21,28";
check_auth($auth_users, $site_root);


$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$colname_crs = "-1";
if (isset($row_sess['sesid'])) {
    $colname_crs = $row_sess['sesid'];
}

if (isset($_GET['sid'])) {
    $colname_crs = $_GET['sid'];
}

if (isset($_GET['curid'])) {
    $colname_cur = $_GET['curid'];
}

$colname1_crs = "-1";
if (isset($_GET['csid'])) {
    $colname1_crs = $_GET['csid'];
}


$partialSQL = "";
$colname_dpt = "-1";
if (isset($_GET['did'])) {
    $colname_dpt = $_GET['did'];
    $partialSQL = sprintf("AND d.deptid = %s ", GetSQLValueString($colname_dpt, 'int'));
}

//Get Course Type
$deptSQL = "";
$courseSQL = sprintf("SELECT cs.csid, cs.type, cs.deptid, c.colid "
                    . "FROM course cs "
                    . "JOIN department d "
                    . "ON d.deptid = cs.deptid "
                    . "JOIN college c "
                    . "ON c.colid = d.colid "
                    . "AND cs.csid = %s",
                    GetSQLValueString($colname1_crs, 'text'));
$courseRS = mysql_query($courseSQL, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($courseRS);
$totalRows_course = mysql_num_rows($courseRS);

switch ($row_course['type']){
    
    case 'College':
        $deptSQL = sprintf("SELECT d.deptid, d.deptname "
                        . "FROM department d "
                        . "JOIN college c "
                        . "ON c.colid = d.colid "
                        . "AND c.colid = %s ", GetSQLValueString($row_course['colid'], 'int'));

        break;
    
    case 'Departmental':
        $deptSQL = sprintf("SELECT d.deptid, d.deptname "
                        . "FROM department d "
                        . "WHERE  d.deptid = %s ", GetSQLValueString($row_course['deptid'], 'int'));
        break;
    
    
    default :
        $deptSQL = sprintf("SELECT d.deptid, d.deptname "
                        . "FROM department d ");
        break;
    
    
}

$deptRS = mysql_query($deptSQL, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($deptRS);
$totalRows_dept = mysql_num_rows($deptRS);

 $query_crs = sprintf(" SELECT DISTINCT (cr.stdid), s.fname, s.lname, s.mname, s.level "
                    . "FROM course_reg cr "
                    . "JOIN student s "
                    . "ON s.stdid = cr.stdid "
                    . "JOIN programme p "
                    . "ON p.progid = s.progid "
                    . "JOIN department d "
                    . "ON d.deptid = p.deptid AND s.disciplinary = 'FALSE' AND cr.sesid = %s AND cr.csid = %s %s ORDER BY cr.stdid ASC",
                    GetSQLValueString($colname_crs, "int"), 
                    GetSQLValueString($colname1_crs, "text"),
                    $partialSQL);
$crs = mysql_query($query_crs, $tams) or die(mysql_error());
$row_crs = mysql_fetch_assoc($crs);
$totalRows_crs = mysql_num_rows($crs);

$data = array();

do{
    $data[] = $row_crs;    
}while($row_crs = mysql_fetch_assoc($crs));

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
                                    <h3>
                                        <i class="icon-reorder"></i>
                                        Registered Students for <?php echo $_GET['csid'] ?>
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <form method="post" target="tabs" action="attendantpdf.php?curid=<?php echo $colname_cur ?>" >
                                            <table width="100%" class="table ">
                                                <input type="hidden" name="query" value="<?= $query_crs?>">
                                                <tr>
                                                    <td >
                                                        Session &nbsp;    
                                                        <select style="width:120px" name="sesid" onchange="sesfilt(this)">
                                                            <?php
                                                            do {
                                                                ?>
                                                                <option value="<?php echo $row_sess['sesid'] ?>"<?php
                                                                if (!(strcmp($row_sess['sesid'], $colname_crs))) {
                                                                    echo "selected=\"selected\"";
                                                                }
                                                                ?>><?php echo $row_sess['sesname'] ?></option>
                                                                        <?php
                                                                    } while ($row_sess = mysql_fetch_assoc($sess));
                                                                    $rows = mysql_num_rows($sess);
                                                                    if ($rows > 0) {
                                                                        mysql_data_seek($sess, 0);
                                                                        $row_sess = mysql_fetch_assoc($sess);
                                                                    }
                                                                    ?>
                                                        </select>
                                                        &nbsp; &nbsp;
                                                        <?php echo $totalRows_crs ?> students
                                                    </td>
                                                    <td >
                                                        Departments &nbsp;           
                                                        <select name="deptid" onchange="deptfilt(this)">
                                                            <?php
                                                            do {
                                                                ?>
                                                                <option value="<?= $row_dept['deptid'] ?>" <?= (!strcmp($row_dept['deptid'], $colname_dpt)) ? 'selected' : '' ?>><?= $row_dept['deptname'] ?></option>
                                                                <?php
                                                            } while ($row_dept = mysql_fetch_assoc($deptRS));
                                                            $rows = mysql_num_rows($deptRS);
                                                            if ($rows > 0) {
                                                                mysql_data_seek($deptRS, 0);
                                                                $row_dept = mysql_fetch_assoc($deptRS);
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="course" value="<?= $_GET['csid']?>">
                                                        <button type="submit" class="btn btn-blue"><i class="icon-print"></i> Print Attendance Sheet</button> 
                                                        
                                                        <?php if (getAccess() ==20 || getAccess() ==28) { ?>
                                                            &nbsp; &nbsp; &nbsp; <a href="reglist.php?sid=<?= $colname_crs ?>&cid=<?= $colname1_crs ?>" target="_new"><i class="icon-download-alt" ></i></a>
                                                        <?php } ?>
                                                        
                                                    </td>
                                                </tr>
                                            </table>
                                        </form>
                                    </div> 
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <table class="table table-condensed table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Matric Number</th>
                                                        <th>Full Name</th>
                                                        <th>Full Name</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr ng-repeat="r in rec">
                                                        <td>{{$index + 1}}</td>
                                                        <td>{{r.stdid}}</td>
                                                        <td>{{r.lname}} {{r.fname}} {{r.mname}}</td>
                                                        <td><a href="viewform.php?stid={{r.stdid}} " target="_blank">View Form</a></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>  
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH . "/footer.php" ?>
            <script>
                var data = <?= ($totalRows_crs > 0)? json_encode($data) : '[]'; ?>
                
                var app = angular.module('app', []);
                
                app.controller('pageCtrl', function($scope){
                    $scope.rec = data;
                });
            </script>
        </div>
    </body>
</html>