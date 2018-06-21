<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');


$auth_users = "1,20,24,26";
check_auth($auth_users, $site_root . '/admin');

$adm = -1;
if (isset($_GET['aid'])) {
    $adm = $_GET['aid'];
} 


$cur_session = -1;
if (isset($_GET['sid'])) {
    $cur_session = $_GET['sid'];
} else {
    $cur_session = $_SESSION['sesid'];
}

$query_part = '';
$cur_college = -1;
if (isset($_GET['cid'])) {
    $cur_college = $_GET['cid']; 
}else{
   $cur_college =  $_SESSION['olv_veri_col'];
}

if($cur_college != -1){
    $query_part = sprintf("AND c.colid = %s ",
            GetSQLValueString($cur_college, 'int'));
}

if(isset($_GET['aid'])){
    $query_part .= sprintf(" AND st.admid = %s ",
            GetSQLValueString($adm, 'int'));
}



$query_ses = sprintf("SELECT * FROM session ORDER BY sesid DESC");
$ses = mysql_query($query_ses, $tams) or die(mysql_error());
$row_ses = mysql_fetch_assoc($ses);

$query_admType = sprintf("SELECT * FROM admission_type");
$admType = mysql_query($query_admType, $tams) or die(mysql_error());
$row_admType = mysql_fetch_assoc($admType);

$query_col = sprintf("SELECT * FROM college ORDER BY colid DESC");
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);

$query_stud = sprintf("SELECT st.stdid, st.fname, st.lname, st.mname, st.jambregid, st.green_file, prg.progname "
                    . "FROM student st "
                    . "JOIN programme prg ON st.progid = prg.progid "
                    . "JOIN department d ON d.deptid = prg.deptid "
                    . "JOIN college c ON c.colid = d.colid %s "
                    . "AND st.sesid = %s AND st.green_file = 'TRUE'", 
                    $query_part,
                    GetSQLValueString($cur_session, 'int') );
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$stud_RS = mysql_fetch_assoc($stud);
$stud_num = mysql_num_rows($stud);


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
                                        Prospective Student Green File Submission Report Page 
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
                                                                <option value="<?= $row_ses['sesid'] ?>" <?= (isset($_GET['sid']) && $row_ses['sesid'] == $_GET['sid'] ) ? 'selected' : ''?>><?= $row_ses['sesname'] ?></option>
                                                            <?php } while ($row_ses = mysql_fetch_assoc($ses)); ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div> 
                                        </div>
                                        <?php if(in_array(getAccess(), ['20','24'])){ ?>
                                        <div class="span4">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">College</label>
                                                <div class="controls controls-row">
                                                    <div class="input-append ">
                                                        <select name="name" input-block-level  onchange="colfilt(this)" >
                                                            <option value="">--Choose--</option>
                                                            <?php do { ?>
                                                                <option value="<?= $row_col['colid'] ?>" <?= (isset($_GET['cid']) && $row_col['colid'] == $_GET['cid'] ) ? 'selected' : ''?>><?= $row_col['colname'] ?></option>
                                                            <?php } while ($row_col = mysql_fetch_assoc($col)); ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div> 
                                        </div>
                                        <?php } ?>
                                        <div class="span4">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Admission Type</label>
                                                <div class="controls controls-row">
                                                    <div class="input-append ">
                                                        <select name="name" input-block-level  onchange="admfilt(this)">
                                                            <option value="">--Choose--</option>
                                                            <?php do { ?>
                                                                <option value="<?= $row_admType['typeid'] ?>" <?= (isset($_GET['aid']) && $row_admType['typeid'] == $_GET['aid'] ) ? 'selected' : ''?>><?= $row_admType['displayname'] ?></option>
                                                            <?php } while ($row_admType = mysql_fetch_assoc($admType)); ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div> 
                                        </div>
                                        <div class="span4">
                                            Total : <div class="badge badge-info"><?= $stud_num;?></div>
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
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php do{?>
                                                    <tr>
                                                        <td><?= $stud_RS['stdid']?></td>
                                                        <td><?= $stud_RS['jambregid']?></td>
                                                        <td><?= $stud_RS['fname']. " ".$stud_RS['lname']." ".$stud_RS['mname'] ?></td>
                                                        <td><?= $stud_RS['progname']?></td>
                                                    </tr>
                                                    <?php } while($stud_RS = mysql_fetch_assoc($stud))?>
                                                </tbody>
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



