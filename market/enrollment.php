<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "1,2,3,4,5,6,7,8,9,10,20,21,22,23";
check_auth($auth_users, $site_root);

//Check if user already enrol 
$enroledSQL = sprintf("SELECT user_id "
                    . "FROM market_user "
                    . "WHERE user_id = %s ", 
                    GetSQLValueString(getSessionValue('uid'), 'text'));
$enroledRS = mysql_query($enroledSQL, $tams) or die(mysql_error());

if(mysql_num_rows($enroledRS) > 0){
    header('Location: index.php');
    exit();
}

$userSQL = "";

switch (getSessionValue('accttype')) {
    
    case 'stud':
        $userSQL = sprintf("SELECT s.* , c.colid, c.colname, d.deptname, d.deptid, p.progid "
                        . "FROM student s "
                        . "JOIN programme p ON s.progid = p.progid "
                        . "JOIN department d ON p.deptid = d.deptid "
                        . "JOIN college c ON d.colid = c.colid "
                        . "WHERE s.stdid = %s ", 
                        GetSQLValueString(getSessionValue('uid'), 'text'));
        break;
    
    case 'staff':
        $userSQL = sprintf("SELECT s.* , c.colid, c.colname, d.deptname, d.deptid "
                        . "FROM lecturer s "
                        . "JOIN department d ON s.deptid = d.deptid "
                        . "JOIN college c ON d.colid = c.colid "
                        . "WHERE s.lectid = %s ", 
                        GetSQLValueString(getSessionValue('uid'), 'text'));

        break;

    default:
        break;
}


$userRS = mysql_query($userSQL, $tams) or die(mysql_error());
$user_row = mysql_fetch_assoc($userRS);


$deptColRS = sprintf("SELECT d.deptname, c.colname "
                . "FROM college c, department d "
                . "WHERE d.colid = c.colid "
                . "AND c.colid = %s ", 
                GetSQLValueString( getSessionValue('cid'), 'int'));
$deptColRS = mysql_query($deptColRS, $tams) or die(mysql_error());
$deptCol_row = mysql_fetch_assoc($deptColRS);

if(isset($_POST['checkbox'])){
    $selerInsertSQL = sprintf("INSERT "
                            . "INTO market_user "
                            . "(user_id, user_type_id, status) "
                            . "VALUES "
                            . "(%s, %s,  'approve')",
                            GetSQLValueString($_POST['uid'], 'text'),
                            GetSQLValueString(getSessionValue('accttype'), 'text'));
    $selerRS = mysql_query($selerInsertSQL, $tams) or die(mysql_error());
    $insertID = mysql_insert_id();
    
    header('Location:index.php');
    exit();
}

?>

<!doctype html>
<html ng-app="app">
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="PageCtrl">
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
                                    <h3>
                                        <i class="icon-reorder"></i>
                                        Market Enrollment
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <div class="span12" >
                                            <h3>Terms and Condition</h3>
                                            <div class="alert alert-info">
                                                <p>
                                                    You are enrolling for the 
                                                    Tasued market module bellow 
                                                    is the contact information 
                                                    you will be using to post 
                                                    your product online. 
                                                    ensure that the phone 
                                                    number is correct as 
                                                    this is what the buyer 
                                                    of your product will contact you with 
                                                    However if the phone number is incorrect 
                                                    kindly go to your profile to update 
                                                    your profile before proceeding 
                                                </p>
                                                <div class="row-fluid">
                                                    <div class="span6">
                                                        <table class="table table-bordered ">
                                                            <tr>
                                                                <th>Full Name</th>
                                                                <td><?= $user_row['fname'] . ' ' . $user_row['mname'] . ' ' . $user_row['lname']?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>E-Mail</th>
                                                                <td><?= $user_row['email']  ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Phone</th>
                                                                <td><?= $user_row['phone'] ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>College</th>
                                                                <td><?= $user_row['colname'] ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Department</th>
                                                                <td><?= $user_row['deptname'] ?></td>
                                                            </tr>
                                                            <?php if(getSessionValue('accttype') == 'stud'){?>
                                                            <tr>
                                                                <th>Level</th>
                                                                <td><?= $user_row['level'] ?></td>
                                                            </tr>
                                                            <?php } ?>
                                                        </table>
                                                    </div>
                                                </div>
                                                <p>&nbsp;</p>
                                                <form method="post" action="<?= $_SERVER['PHP_SELF']?>">
                                                    <p>
                                                        <label class="checkbox" style="color: red">
                                                            <input type="checkbox" name="checkbox" required="" value="yes"> I have read and understand the terms and condition 
                                                        </label>
                                                        <input type="hidden" value="<?= getSessionValue('uid') ?>" name="uid">
                                                        <button type="submit" class="btn btn-primary">Proceed</button>
                                                    </p>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
            <script type="text/javascript">
                var app = angular.module('app', []);
                app.controller('PageCtrl', function($scope){
                    $scope.data = '';
                });
            </script>
    </body>
</html>