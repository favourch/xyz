<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');



$auth_users = "1,20,26,24";
check_auth($auth_users, $site_root.'/admin');

/* -----------------------------------------------*
 * 
 * Logic of the College/index.php Page 
 *
 * *------------------------------------------------
 */

if (isset($_POST['MM_treat']) && $_POST['MM_treat'] == 'form2') {
    $msg = '';

    if ($_POST['submit'] == 'Yes') {
        $msg = "<p style='color:green'>Your Olevel Result has been PRINTED by the ICT <br/> and it is being forwarded to Admission's Office for Verification .</p>";
    }
    else {
        $msg = "<p style='color:red'>ICT could NOT PRINT your O'Level Result  <br/>Your Card details may be wrong or maximum use reached. Please re-submit.</p>";
    }
    
    $query = sprintf("UPDATE `olevel_veri_data` "
            . "SET `treated` = 'Yes', approve = %s, return_msg = %s, date_treated=%s, who=%s "
            . "WHERE id = %s", GetSQLValueString($_POST['submit'], 'text'), GetSQLValueString($msg, 'text'), GetSQLValueString(date('Y-m-d H:i:s'), 'date'), GetSQLValueString(getSessionValue('uid'), 'text'), GetSQLValueString($_POST['edit_id'], 'text'));
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

mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * FROM session ORDER BY sesid DESC");
$session = mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);

mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * FROM programme WHERE continued = 'Yes' ORDER BY progname ASC");
$prog = mysql_query($query, $tams) or die(mysql_error());
$row_proramme = mysql_fetch_assoc($prog);


mysql_select_db($database_tams, $tams);
mysql_select_db($database_tams, $tams);
$query_limit_verify = sprintf("SELECT ol.*, s.sesname "
                            . "FROM olevel_veri_data ol, session s "
                            . "WHERE ol.treated = 'Yes' "
                            . "AND ol.sesid = s.sesid "
                            . "AND ol.usertype = 'stud' "
                            . "AND ol.who = %s "
                            . "ORDER BY date_treated "
                            . "DESC ", GetSQLValueString(getSessionValue('uid'), 'text'));
$verify = mysql_query($query_limit_verify, $tams) or die(mysql_error());
$verify_row = mysql_fetch_assoc($verify);
$verify_row_num = mysql_num_rows($verify);


if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root . '/admin');
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
                    <!--                    <div class="breadcrumbs">
                                            <ul>
                                                <li>
                                                    <a href="index.php">Home</a> <i class="icon-angle-right"></i>
                                                </li>
                                                <li>
                                                    <a href="college.php">College</a>
                                                </li>
                                            </ul>
                                            <div class="close-bread">
                                                <a href="#"><i class="icon-remove"></i></a>
                                            </div>
                                        </div>
                                        <br/>-->
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        O'Level Result Verified By <?= getSessionValue('uid')?>
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <div class="form form-horizontal float-right">
                                            <label class="checkbox">
                                                <input type="checkbox" ng-model="active" name="checkbox">  Enable Re-process
                                            </label>
                                        </div>
                                    </div>
                                    <div class='row-fluid'>
                                        <table class="table table-bordered dataTable table-condensed table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Reg No</th>
                                                    <th>Exam Type</th>
                                                    <th >Exam Year</th>
                                                    <th>Exam No</th>
                                                    <th>Card S/N</th>
                                                    <th>Card Pin</th>
                                                    <th>Session</th>
                                                    <th ng-if="!active">Printed</th>
                                                    <th ng-if="active">Re-Process</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php do{?>
                                                <tr>  
                                                    <td><?= $verify_row['stdid']?></td>
                                                    <td><?= $verify_row['exam_type']?></td> 
                                                    <td><?= $verify_row['exam_year']?></td> 
                                                    <td><?= $verify_row['exam_no']?></td> 
                                                    <td><?= $verify_row['card_no']?></td>
                                                    <td><?= $verify_row['card_pin']?></td>
                                                    <td><?= $verify_row['sesname']?></td> 
                                                    <td ng-if="!active" style="color: <?=($verify_row['approve'] == 'Yes')? 'green' : 'red'?>"><?= $verify_row['approve']?></td>
                                                    <td ng-if="active">
                                                        <form name='form2' method="POST" action="my_treated.php">
                                                            <input type="hidden" name='edit_id' value="<?= $verify_row['id']?>">
                                                            <input type="submit" name='submit' class="btn btn-small btn-green" value="Yes"/> | <input type="submit" class="btn btn-small btn-red" name='submit' value="No"/>
                                                            <input type="hidden" name='MM_treat' value="form2"/>
                                                        </form>   
                                                    </td>
                                                </tr>
                                                <?php }while($verify_row = mysql_fetch_assoc($verify))?>
                                            </tbody>
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
<?php include INCPATH."/footer.php" ?>
    </body>
    <script type="text/javascript">
            
            app = angular.module('tams', []);
            
            app.controller('pageController',function($scope){
                
                
                $scope.click = function(){
                    location.href = "index.php";
                };
                
            });
           
    </script>
</html>