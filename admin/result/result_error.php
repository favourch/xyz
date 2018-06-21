<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "20,2,3,4,5,6";
check_auth($auth_users, $site_root);

if (isset($_POST['submit']) && $_POST['submit'] == "") {
    
}

$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$csid = '';
if (isset($_GET['csid'])) {
    $csid = $_GET['csid'];
}

$sesid = $row_sess['sesid'];
if (isset($_GET['sid'])) {
    $sesid = $_GET['sid'];
}

$status = NULL;
$type = 'error';

if (isset($_POST['restore']) && $_POST['restore'] == "Restore Results") {
//var_dump($_POST);
    if (isset($_POST['stdid']) && is_array($_POST['stdid'])) {
        mysql_query("BEGIN", $tams);
        
        $insert_string = sprintf("REPLACE INTO course_reg (stdid, csid, sesid) "
                . "SELECT stdid, csid, sesid, tscore, escore "
                . "FROM result_error "
                . "WHERE sesid = %s "
                . "AND csid = %s "
                . "AND stdid IN ('%s')", GetSQLValueString($sesid, "int"), GetSQLValueString($csid, "text"), GetSQLValueString('stdid', "defined", implode("','", $_POST['stdid'])));
        $ret = mysql_query($insert_string, $tams);
        
        $insert_string = sprintf("REPLACE INTO result (stdid, csid, sesid, tscore, escore) "
                . "SELECT stdid, csid, sesid, tscore, escore "
                . "FROM result_error "
                . "WHERE sesid = %s "
                . "AND csid = %s "
                . "AND stdid IN ('%s')", GetSQLValueString($sesid, "int"), GetSQLValueString($csid, "text"), GetSQLValueString('stdid', "defined", implode("','", $_POST['stdid'])));
        $ret = mysql_query($insert_string, $tams);

        if ($ret) {
            $delete_string = sprintf("DELETE FROM result_error "
                    . "WHERE sesid = %s "
                    . "AND csid = %s "
                    . "AND stdid IN ('%s')", GetSQLValueString($sesid, "int"), GetSQLValueString($csid, "text"), GetSQLValueString('stdid', "defined", implode("','", $_POST['stdid'])));
            $ret2 = mysql_query($delete_string, $tams);

            if ($ret2) {
                mysql_query("COMMIT", $tams);
                $status = count($_POST['stdid']) . ' result(s) restored successfully!';
                $type = 'success';
            } else {
                mysql_query("ROLLBACK", $tams);
                $status = 'There was a problem restoring the result(s) selected!';
            }
        } else {
            $status = 'There was a problem restoring the result(s) the selected!';
        }
    } else {
        $status = 'You did not select any result to restore!';
    }
    //exit;
}

$query_course = sprintf("SELECT SUM(percentPaid) AS total, c.csname, c.csid, s.lname, s.fname, r.stdid, r.date, "
        . "r.tscore, r.escore "
        . "FROM course c "
        . "JOIN result_error r ON c.csid = r.csid AND r.csid = %s AND r.sesid = %s "
        . "LEFT JOIN student s ON s.stdid = r.stdid "
        . "LEFT JOIN schfee_transactions st ON r.stdid = st.matric_no "
        . "LEFT JOIN payschedule ps ON st.scheduleid = ps.scheduleid AND ps.sesid = r.sesid "
        . "AND st.status = 'APPROVED' GROUP BY r.stdid", GetSQLValueString($csid, "text"), GetSQLValueString($sesid, "int"));
$course = mysql_query($query_course, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($course);
$totalRows_course = mysql_num_rows($course);

$checks = [];
$paidCount = 0;
?>
<!doctype html>
<html ng-app="tams">
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
        <?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH."/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH."/page_header.php" ?>
                    
                    <div class="row-fluid">
                        <?php if(isset($status)) :?>
                        <div class="span12 alert alert-<?php echo $type?>">
                            <?php echo $status?>
                        </div>
                        <?php endif;?>
                    </div>
                    
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Result Error (<?php echo $csid?>)
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <form method="post" action="" novalidate>                                            
                                            <table class="table table-striped" ng-controller="CheckController">
                                                <tr>
                                                    <td colspan="7"></td>
                                                </tr>
                                                <thead>
                                                    <tr>
                                                        <th>S/N</th>
                                                        <th>Matric No.</th>
                                                        <th>Name</th>
                                                        <th>Test</th>
                                                        <th>Exam</th>
                                                        <th>Date</th>                
                                                        <th>
                                                            <input type="checkbox" ng-model="checkAll" ng-click="notify()"/>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <?php
                                                if ($totalRows_course > 0) :
                                                    for ($idx = 0; $idx < $totalRows_course; $idx++, $row_course = mysql_fetch_assoc($course)):
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $idx + 1 ?></td>
                                                            <td><?php echo $row_course['stdid'] ?></td>
                                                            <td><?php echo $row_course['lname'] . ' ' . $row_course['fname'] ?></td>
                                                            <td><?php echo $row_course['tscore'] ?></td>
                                                            <td><?php echo $row_course['escore'] ?></td>
                                                            <td><?php echo date('D jS M, Y H:ia', strtotime($row_course['date'])) ?></td>
                                                            <td>
                                                                <?php if ($row_course['total'] != NULL && $row_course['total'] > 99): array_push($checks, 'false') ?>
                                                                    <input type="checkbox" ng-model="checks[<?php echo $paidCount++ ?>]" 
                                                                           name="stdid[]" value="<?php echo $row_course['stdid'] ?>"/>           
                                                                       <?php endif ?>
                                                            </td>
                                                        </tr>
                                                    <?php endfor; ?>        
                                                    <tr>
                                                        <td colspan="7" align="center">
                                                            <input type="submit"  class="btn btn-primary" name="restore" value="Restore Results"/>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7">There are no errors uploaded for this course!</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </table>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>

            <script type="text/javascript">
    
                angular.module('tams', []).controller('CheckController', function($scope) {
                    var state = $scope.checkAll = false;
                    $scope.checks = [<?php echo implode(',', $checks) ?>];
        
                    $scope.notify = function() {
                        angular.forEach($scope.checks, function(value, key) {
                            this[key] = !state;
                        }, $scope.checks);
                        state = !state;
                    };
        
                });
            </script>
    </body>
</html>