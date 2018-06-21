<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "1,2,3";
check_auth($auth_users, $site_root);

$query_sess = "SELECT * FROM `session` WHERE status = 'TRUE' ORDER BY sesname DESC LIMIT 0,5";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$query_cur = "SELECT * FROM curriculum ";
$cur = mysql_query($query_cur, $tams) or die(mysql_error());
$row_cur = mysql_fetch_assoc($cur);
$totalRows_cur = mysql_num_rows($cur);

$colname_dept = "-1";
if ( getSessionValue('cid') != NULL ) {
  $colname_dept = getSessionValue('cid');
}

// Release result	
if (isset($_POST['submit'])) { 
    $sesid = $_POST['sesid'];
    
    $msg = "The results you selected could not be released! Please try again or contact the system administrator.";
    $type = 'error';
    
    if(!empty($_POST['results'])) {
        $updateSql = sprintf("UPDATE teaching SET released = 'yes' WHERE sesid = %s AND csid IN ('%s')",
                            GetSQLValueString($sesid, "int"),
                            GetSQLValueString("update", "defined", implode("','", $_POST['results'])));
        mysql_query($updateSql, $tams);
        
        if(mysql_errno() == 0) {
            $msg = "The results you selected have been released for viewing!";
            $type = 'success';
        }
    }    
    
    $notification->set_Notification($msg, $type);
    
    $_POST = [];
}

$query_dept = sprintf("SELECT deptid, deptname, coltitle "
                        . "FROM department d, college c "
                        . "WHERE d.colid = c.colid "
                        . "AND d.colid = %s",
                        GetSQLValueString($colname_dept, "int"));
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);


$colname_prog = "-1";
if (isset($row_dept['deptid'])) 
  $colname_prog = $row_dept['deptid'];
	
if (isset($_GET['did']))
  $colname_prog = $_GET['did'];

if(getAccess() == 3) {
    $colname_prog = getSessionValue('did');
}

$query_prog = sprintf("SELECT progid, progname, p.deptid, deptname "
                        . "FROM programme p, department d "
                        . "WHERE d.deptid = p.deptid "
                        . "AND p.deptid = %s", GetSQLValueString($colname_prog, "int"));
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$colname1_deptcrs = "-1";
if (isset($row_sess['sesid'])) {
  $colname1_deptcrs = $row_sess['sesid'];
}

if (isset($_GET['sid'])) {
  $colname1_deptcrs = $_GET['sid'];
}

$colname1_curid = 1;
if (isset($_GET['cur'])) {
  $colname1_curid = $_GET['cur'];
}

$query_deptcrs = sprintf("SELECT distinct (c.csid), csname, upload, accepted, approve, released "
                        . "FROM course c "
                        . "LEFT JOIN department_course dc ON c.csid = dc.csid "
                        . "LEFT JOIN teaching t ON c.csid = t.csid AND c.deptid = t.deptid "
                        . "WHERE c.deptid = %s "
                        . "AND t.sesid = %s AND c.curid = %s "
                        . "ORDER BY t.csid ASC", 
                        GetSQLValueString($colname_prog, "int"), 
                        GetSQLValueString($colname1_deptcrs, "int"),
                        GetSQLValueString($colname1_curid, "int"));
$deptcrs = mysql_query($query_deptcrs, $tams) or die(mysql_error());
$row_deptcrs = mysql_fetch_assoc($deptcrs);
$totalRows_deptcrs = mysql_num_rows($deptcrs);

$name = "";
if(getAccess() == 3) {
    $name = $row_prog['deptname'];
}elseif(getAccess() == 2) {
    $name = $row_dept['coltitle'];
}

if(isset($_GET['did']) ) {
    $name = $row_prog['deptname'];
}elseif(isset($_GET['cid']) ) {
    $name = $row_dept['coltitle'];
}

$checks = [];
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
                        <?php if(isset($uploadstat)) :?>
                        <div class="span12 alert alert-<?php echo $type?>">
                            <?php echo $uploadstat?>
                        </div>
                        <?php endif;?>
                    </div>
                    
                    <div class="row-fluid">                        
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Consider Result for <?php echo $name;?>
                                    </h3>
                                </div>
                                <div class="box-content" ng-controller="CheckController">                                   
                                    <div class="row-fluid">
                                        <?php if (getAccess() == 1 || getAccess() == 2) { ?>
                                        <div class="span4">
                                            Choose Department
                                            <select name="deptid" id="deptid" onchange="deptfilt(this)">
                                            <?php do {?>
                                                <option value="<?php echo $row_dept['deptid'] ?>" 
                                                    <?php if (!(strcmp($row_dept['deptid'], $colname_prog))) {
                                                        echo "selected=\"selected\"";
                                                    }?>>
                                                    <?php echo $row_dept['deptname'] ?>
                                                </option>
                                            
                                            <?php } while ($row_dept = mysql_fetch_assoc($dept));?>
                                            </select>
                                        </div>
                                        <?php } ?>
                                        
                                        <div class="span4">
                                            <?php if (getAccess() == 1 || getAccess() == 2 || getAccess() == 3) { ?>
                                                Session
                                                <select name="sesid" id="sesid" onchange="sesfilt(this)">
                                                    <?php do {?>
                                                    <option value="<?php echo $row_sess['sesid'] ?>"<?php
                                                    if (!(strcmp($row_sess['sesid'], $colname1_deptcrs))) {
                                                        echo "selected=\"selected\"";
                                                    }?>>
                                                        <?php echo $row_sess['sesname'] ?>
                                                    </option>
                                                    <?php }while ($row_sess = mysql_fetch_assoc($sess));?>
                                                </select>
                                            </td>
                                            <?php } ?>
                                        </div>
                                        
                                        <div class="span4">
                                            <select name="cur" id="cur" onchange="curfilt(this)">
                                                <?php do {?>
                                                <option value="<?php echo $row_cur['curid'] ?>" <?= ($colname1_curid == $row_cur['curid']) ? 'selected' : ''?>>
                                                    <?php echo $row_cur['curname'] ?>
                                                </option>
                                                <?php }while ($row_cur = mysql_fetch_assoc($cur));?>
                                            </select>
                                        </div>
                                    </div>                               
                                    
                                    <form action="" method="post">
                                        <input class="btn btn-primary pull-right" type="submit" name="submit" value="Release Result" ng-show="checks.length > 0"/>
                                        <table class="table table-striped" ng-controller="CheckController">
                                            <thead>
                                                <th>Course Code</th>
                                                <th>Course Name</th>
                                                <th>Uploaded</th>
                                                <th>Accepted</th>
                                                <th>Approved</th>
                                                <th>Released</th>
                                                <th>
                                                    <input type="checkbox" ng-model="checkAll" ng-click="notify()"/>
                                                </th>
                                            </thead>
                                            <?php   if ($totalRows_deptcrs > 0) { // Show if recordset not empty  
                                                        for($idx = 0;$row_deptcrs;$row_deptcrs = mysql_fetch_assoc($deptcrs), $idx++) { ?>
                                            <tr>
                                                <td width="60"><?php echo $row_deptcrs['csid'] ?></td>
                                                <td width="385">
                                                    <a href="result.php?csid=<?php echo $row_deptcrs['csid'] ?>&did=<?php echo $colname_prog ?>&sid=<?php echo $colname1_deptcrs ?>">
                                                        <?php echo ucwords(strtolower($row_deptcrs['csname']))?>
                                                    </a>
                                                </td>
                                                <td width="106"><?php echo getUploadState($row_deptcrs['upload']) ?></td>
                                                <td width="116">
                                                    <?php                                                         
                                                        echo $row_deptcrs['accepted'] != 'yes'? 'Not Accepted': 'Accepted';
                                                    ?>
                                                </td>
                                                <td width="106"><?php echo getApproveState($row_deptcrs['approve']) ?></td>
                                                <td width="116">
                                                    <?php                                                         
                                                        echo $row_deptcrs['released'] != 'yes'? 'Not Released': 'Released';
                                                    ?>
                                                </td>
                                                <td> 
                                                    <?php 
                                                        if($row_deptcrs['upload'] == 'yes' && $row_deptcrs['accepted'] == 'yes' 
                                                                && $row_deptcrs['approve'] == 'yes' && $row_deptcrs['released'] != 'yes'): 
                                                            array_push($checks, 'false') 
                                                    ?>
                                                    <input type="checkbox" ng-model="checks[<?php echo $idx?>]" 
                                                           name="results[]" value="<?php echo $row_deptcrs['csid']?>"/> 
                                                    <?php endif;?>
                                                </td>
                                            </tr>
                                            <?php }                                       
                                                }else { // Show if recordset not empty 
                                            ?> 
                                            <tr>
                                                <td colspan="7">There are no results to consider!</td>
                                            </tr>
                                            <?php }?>
                                        </table>
                                        <input type="submit" class="btn btn-primary pull-right" name="submit" value="Release Result" ng-show="checks.length > 0"/>
                                        <input type="hidden" name="sesid" value="<?php echo $colname1_deptcrs?>"/>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
            
        </div>
                
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