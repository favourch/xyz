<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "1,20,31";
check_auth($auth_users, $site_root.'/admin');

$not_found_text = '';
$colname_rsstdnt = "";

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if(isset($_POST['clear'])){
    
    $updateSQL = sprintf("UPDATE prospective SET clinic_clear = 'yes' WHERE jambregid = %s ",GetSQLValueString($_POST['jambregid'], 'text'));
    $upRS = mysql_query($updateSQL, $tams) or die(mysql_error());
    
    
    $msg = "You have been cleared by the health Center";
    
    $bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'>Health Center Clearance </h3><p>%s</p>",$msg);
    
    @sendHtmlEmail($_POST['email'], 
                "Olevel Verification", 
                $bd, 'clini@tasued.edu.ng',
                "Health Center (Health Status Verification)");
    
    $sms = "You have been cleared by the health Center";
    @sendSMS('TASUED', $_POST['phone'], $sms);
    
    
    
        header("location: $editFormAction");
        exit();
   
}

if (isset($_GET['search']) && $_GET['search'] != NULL) {
    $seed = $colname_rsstdnt = $_GET['search'];

    $query_rsstdnt = "SELECT * "
            . "FROM prospective "
            . "WHERE adminstatus  = 'yes' AND clinic_pay = 'yes' AND clinic_form = 'yes' AND (lname LIKE '%" . $seed . "%' "
            . "OR fname LIKE '%" . $seed . "%' "
            . "OR jambregid LIKE '%" . $seed . "%')"; 
    $rsstdnt = mysql_query($query_rsstdnt, $tams) or die(mysql_error());
    $not_found_text = " for the search word \"{$seed}\"";
    $row_rsstdnt = mysql_fetch_assoc($rsstdnt);
    $totalRows_rsstdnt = mysql_num_rows($rsstdnt);
}

$data = array();
do {
   $data[] =  $row_rsstdnt;                                
} while ($row_rsstdnt = mysql_fetch_assoc($rsstdnt));





?>
<!doctype html>
<html ng-app = "clinic">
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="PageCTRL">
        <?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH."/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH."/page_header.php" ?>

                    <div class="row-fluid">
                        <div class="box box-bordered box-color">
                            <div class="box-title">
                                <h3><i class="icon-reorder"></i>
                                    Clinic Module (Prospective Student Medical Clearance)
                                </h3>
                            </div>
                            <div class="box-content ">  
                                <div class="row-fluid">
                                    <div class="span12 well ">
                                        <span>Search student with jambreg No or Surnname</span>
                                        <form id="form1" name="form1" method="get" action="">
                                        <input name="search" type="text" id="search" 
                                               class="input-xxlarge" value="<?php echo $colname_rsstdnt ?>" 
                                               placeholder="Search By Name UTME No. or Form No." 
                                               data-rule-requuired="true"/>
                                        <input  style="margin-bottom: 10px" type="submit" id="submit" value="Search" 
                                                class="btn btn-primary"/>                                        
                                    </form>
                                    </div>
                                </div>
                                <div class="row-fluid">
                                    <div class="span12">
                                        <table width="626" align="center" class="table table-bordered table-condensed table-hover table-striped">
                                            <thead>
                                                <tr>
                                                    <th width="71">S/n</th>
                                                    <th width="150">UTME No.</th>
                                                    <th width="275">Full Name</th>
                                                    <th width="">Sex</th>
                                                    <th width="">Date Of Birth</th>
                                                    <th width="110">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr ng-repeat="dt in data" >
                                                    <td>{{$index + 1}}</td>	
                                                    <td>{{dt.jambregid}}</td>
                                                    <td>{{dt.lname}} {{dt.fname}} {{dt.mname}} </td>
                                                    <td>{{dt.Sex}}</td>
                                                    <td>{{dt.DoB}}</td>
                                                    <td ng-if="dt.clinic_clear == 'no'">
                                                        <a href="#clear_student" data-toggle="modal"  class="btn btn-small btn-purple" ng-click="SetSelected(dt)">Clear Student</a>
                                                    </td>
                                                    <td ng-if="dt.clinic_clear == 'yes'">
                                                        <span class="badge badge-success">Cleared</span>
                                                    </td>
                                                </tr>
                                            </tbody>   
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div aria-hidden="false" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="modal hide fade" id="clear_student">
                            <div class="modal-header">
                                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                                <h3 id="myModalLabel">Confirm Details </h3>
                            </div>
                            <form class="form-vertical" method="post" action="<?= $editFormAction; ?>" >

                                <div class="modal-body" style="min-height: 300px">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th>UTME No.</th>
                                                <td>{{selectedItem.jambregid}}</td>
                                            </tr>
                                            <tr>
                                                <th>FULL NAME</th>
                                                <td>{{selectedItem.lname}} {{selectedItem.fname}} {{selectedItem.mname}}</td>
                                            </tr>
                                            <tr>
                                                <th>SEX.</th>
                                                <td>{{selectedItem.Sex}}</td>
                                            </tr>
                                            <tr>
                                                <th>Date Of Birth.</th>
                                                <td>{{selectedItem.DoB}}</td>
                                            </tr>
                                        </table>
                                        <input type="hidden" name="jambregid" value="{{selectedItem.jambregid}}">
                                        <input type="hidden" name="phone" value="{{selectedItem.phone}}">
                                        <input type="hidden" name="email" value="{{selectedItem.email}}">
                                        <div class="alert alert-warning">You have choose to clear the Medical Verification of the above mentioned prospective student. <br /> Are you sure you want to proceed with this action?</div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-primary" type="submit" name="clear" >Yes</button>
                                    <button aria-hidden="true" data-dismiss="modal" class="btn">No</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>          
            </div>
            
            
            <?php include INCPATH."/footer.php" ?>
            <script>
                var data1 = <?php echo json_encode($data) ?>;
                
                var clinic = angular.module('clinic', []);
                
                clinic.controller('PageCTRL', function($scope){
                    $scope.data = data1;
                    
                    $scope.selectedItem = "";
                    $scope.SetSelected = function(v){
                        $scope.selectedItem = v;
                    }
                });
            </script>
        </div>
    </body>
</html>