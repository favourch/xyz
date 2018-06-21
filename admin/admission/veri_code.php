<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');


$auth_users = "1, 2, 20, 24";
check_auth($auth_users, $site_root);

//Get all session 
$query_session = "SELECT "
               . "sesid, sesname "
               . "FROM session "
               . "ORDER BY sesid DESC";
$session = mysql_query($query_session, $tams) or die(mysql_error());
$totalRows_session = mysql_num_rows($session);

$sid = $_SESSION['sesid'];
if (isset($_GET['sid'])) {
    $sid = $_GET['sid'];
}

$Query_verif = sprintf("SELECT v.*, p.fname, p.lname, p.mname, p.sex "
                     . "FROM verification v, prospective p  "
                     . "WHERE p.jambregid = v.stdid "
                     . "AND v.sesid = %s ", 
                     GetSQLValueString($sid, 'text'));
$verif = mysql_query($Query_verif, $tams) or die(mysql_error());
$totalRows_verif = mysql_num_rows($verif);

$code = array();
for (; $row_verif = mysql_fetch_assoc($verif);){
    
    $data = array(
        'jambregid' => $row_verif['stdid'],
        'fname'     => $row_verif['fname'],
        'lname'     => $row_verif['lname'],
        'mname'     => $row_verif['mname'],
        'sex'       => $row_verif['sex'],
        'ver_code'  => $row_verif['ver_code']  
    );
    
    array_push($code, $data);
}


//die(var_dump($code));

$page_title = "Tasued";
?>
<!doctype html>
<html ng-app="veriApp">
    <?php include INCPATH . "/header.php" ?>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="PageController">
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
                                    <h3><i class="icon-key"></i>
                                        Verification Code
                                    </h3>
                                    <ul class="tabs">
                                    </ul>
                                </div>
                                <div class="box-content">
                                    <form>
                                        <div class="row-fluid">
                                            <div class="span3">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">Session</label>
                                                    <div class="controls controls-row">
                                                        <select onchange="sesfilt(this)">
                                                            <?php for (; $row_session = mysql_fetch_assoc($session);): ?>
                                                            <option value="<?php echo $row_session['sesid'] ?>" 
                                                                <?php echo $sid == $row_session['sesid']? 'selected': ''?>>
                                                                <?php echo $row_session['sesname'] ?>
                                                            </option>
                                                            <?php endfor;?>
                                                        </select> 
                                                    </div>
                                                </div>
                                            </div> 
                                            <div class="span3">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">&nbsp;</label>
                                                    <div class="controls controls-row">
                                                        <div class="input-prepend">
                                                            <span class="add-on">Filter with JambRegID or Names </span>
                                                            <input id="textfield" class="input-block-level" type="text" ng-model="search" >
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> 
                                        </div>
                                    </form> 
                                    <div class="row-fluid" >
                                        <table class="table table-striped table-condensed">
                                            <thead>
                                                <tr>
                                                    <th width="5%">S/N</th>
                                                    <th width="10%">JambregID</th>
                                                    <th width="55%">Full Name</th>
                                                    <th width="30%">Verification Code</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr ng-repeat="dt in record | filter :search " ng-show="record.length > 0">
                                                    <td ng-bind="$index +1"></td>
                                                    <td ng-bind="dt.jambregid"></td>
                                                    <td>{{dt.lname}} {{dt.fname}} {{dt.mname}}</td>
                                                    <td style="color: brown" ng-bind="dt.ver_code"></td>
                                                </tr>
                                                <tr ng-show="record.length < 1">
                                                    <td colspan="4"> <div class="alert">No Record available </div></td>
                                                </tr>
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
<?php include INCPATH . "/footer.php" ?>
    </body>
    <script >
        var data = <?= (!empty($code)) ? json_encode($code) : '[]'?>;
        var veriApp = angular.module('veriApp', [])
                .controller('PageController', function($scope){
                    $scope.record = data;
            console.log($scope.record);
                });
         
    </script>
</html>


