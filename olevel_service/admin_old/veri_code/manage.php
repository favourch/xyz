<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../../../path.php');

$auth_users = "1,20,26,24";
check_auth($auth_users, $site_root . '/admin');





$cur_session = -1;
if (isset($_GET['sid'])) {
    $cur_session = $_GET['sid'];
} else {
    $cur_session = $_SESSION['sesid'];
}



$query_ses = sprintf("SELECT * FROM session ORDER BY sesid DESC");
$ses = mysql_query($query_ses, $tams) or die(mysql_error());
$row_ses = mysql_fetch_assoc($ses);


$query = sprintf("SELECT colid, colname, coltitle "
                . "FROM college ");
$college = mysql_query($query, $tams) or die(mysql_error());
$row_college = mysql_fetch_assoc($college);


if(isset($_POST['search'])){
    
    $query_part = '';
    
    if($_POST['utype'] == 'pros'){
        
        $query_part = sprintf(" JOIN prospective p ON v.stdid = p.jambregid 
                                JOIN programme prg ON p.progoffered = prg.progid
                                JOIN department d ON d.deptid = prg.deptid 
                                JOIN college c ON c.colid = d.colid AND c.colid = %s ", 
                                GetSQLValueString($_POST['colid'], 'int'));
    }else{
        
        $query_part = sprintf(" JOIN student p ON v.stdid = p.stdid 
                                JOIN programme prg ON p.progid = prg.progid
                                JOIN department d ON d.deptid = prg.deptid 
                                JOIN college c ON c.colid = d.colid AND c.colid = %s ", 
                                GetSQLValueString($_POST['colid'], 'int')) ;
    }
    
    echo $query = sprintf("SELECT v.*, p.fname, 
                     p.lname, p.mname, prg.progname 
                     FROM verification v 
                     %s  
                     WHERE v.stdid = %s AND v.sesid = %s ", 
                     $query_part, 
                    GetSQLValueString($_POST['seed'], 'text'),
                    GetSQLValueString($_POST['sesid'], 'int') ); die();
    $queryRS = mysql_query($query, $tams) or die(mysql_error());

    $code = array();
    while ($queryRow = mysql_fetch_assoc($queryRS)) {

        $code[] = array(
            'id' => $queryRow['id'],
            'jambregid' => $queryRow['stdid'],
            'fname' => $queryRow['fname'],
            'lname' => $queryRow['lname'],
            'mname' => $queryRow['mname'],
            'ver_code' => $queryRow['ver_code'],
            'progname' => $queryRow['progname'],
            'sittings' => $queryRow['sittings'],
            'extra' => $queryRow['extra'],
        );
    }

    $codes = json_encode($code);
}


if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root . '/ict');
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
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
                                    <h3><i class="icon-barcode"></i>
                                        O'Level Management 
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="well well-small">
                                        <form method="post" action="<?= $editFormAction?>">
                                            <div class="row-fluid">
                                                <div class="span3">
                                                    <div class="control-group">
                                                        <label class="control-label" for="textfield">Session</label>
                                                        <div class="controls">
                                                            <select name="sesid" class="input-block-level" required="">
                                                                <option value="">--Choose--</option>
                                                                <?php do { ?>
                                                                    <option value="<?= $row_ses['sesid'] ?>" <?= ($cur_session == $row_ses['sesid']) ? 'selected' : '' ?>><?= $row_ses['sesname'] ?></option>
                                                                <?php } while ($row_ses = mysql_fetch_assoc($ses)); ?>
                                                            </select>
                                                        </div>
                                                    </div> 
                                                </div>
                                                <div class="span3">
                                                    <div class="control-group">
                                                        <label class="control-label" for="textfield">Student Type</label>
                                                        <div class="controls"> 
                                                            <select name="utype" class="input-block-level" required="">
                                                                <option value="">--Choose--</option>
                                                                <option value="pros">Prospective</option>
                                                                <option value="stud">Returning</option>
                                                            </select> 
                                                        </div>
                                                    </div> 
                                                </div>
                                                <div class="span3">
                                                    <div class="control-group">
                                                        <label class="control-label" for="textfield">College</label>
                                                        <div class="controls"> 
                                                            <select name="colid" class="input-block-level" required="">
                                                                <option value="">--Choose--</option>
                                                                <?php do { ?>
                                                                    <option value="<?= $row_college['colid'] ?>"><?= $row_college['coltitle'] ?></option>
                                                                <?php } while ($row_college = mysql_fetch_assoc($college)); ?>
                                                            </select>  
                                                        </div>
                                                    </div> 
                                                </div>
                                                <div class="span3">
                                                    <div class="control-group">
                                                        <label class="control-label" for="textfield">Search by Matric or Jamregid</label>
                                                        <div class="controls"> 
                                                            <div class="input-append">
                                                                <input type="text" class="input-medium" name="seed"  required="">
                                                            </div>  
                                                        </div>
                                                    </div> 
                                                </div>
                                            </div>
                                            <div class="row-fluid">
                                                <div class="span3">
                                                    <div class="control-group">
                                                        <div class="controls"> 
                                                            <button type="submit" class="btn btn-green" name="search">Search</button>  
                                                        </div>
                                                    </div> 
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class='row-fluid'>
                                        <div class="span12"> 
                                            <table class="table  table-condensed table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Jamb Reg No</th>
                                                        <th>Full Name</th>
                                                        <th>No. of Sittings</th>
                                                        <th>Programme Choice</th>
                                                        <th>Verification Code</th>
                                                        <th width="30%">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr ng-repeat="cd in ver_code | filter : seed" class="fade in">
                                                                <td>
                                                                    <a  target="_blank" href="'../../../admission/viewform.php?stid={{cd.jambregid}}">{{cd.jambregid}} </a>    
                                                                </td>
                                                                <td>{{cd.lname}} {{cd.fname}} {{cd.mname}}</td>
                                                                <td>{{cd.sittings}}</td>
                                                                <td>{{cd.progname}}</td>
                                                                <td>{{cd.ver_code}}</td>
                                                                <td>
                                                                    <div ng-if="!loading">
                                                                        <?php if(in_array(getAccess(), ['20','24', '26'])){?>
                                                                        <input type="button" name='submit' ng-click="releaseCode($index, cd)" class="btn btn-small btn-green" value="Release"/> &nbsp;&nbsp;&nbsp;
                                                                        <input type="button" name='refer' ng-click="referToAdmin($index, cd)" class="btn btn-small btn-brown" value="Refer"/>&nbsp;&nbsp;&nbsp;
                                                                        <input type="button" name='refer' ng-click="refere($index, cd)" class="btn btn-small btn-blue" value="Contact"/>&nbsp;&nbsp;&nbsp;
                                                                        <?php } ?>
                                                                        
                                                                        <?php if(in_array(getAccess(), ['20'])){?>
                                                                        <input ng-if="cd.extra == '0'" type="button" name='refer' ng-click="extraSubmission($index, cd)" class="btn btn-small btn-red" value="Extra"/>
                                                                        <span ng-if="cd.extra != '0' " class="badge badge-warning">Extra submission enabled</span>
                                                                        <?php }?>
                                                                    </div>
                                                                </td>  
                                                            </tr>
                                                </tbody>
                                            </table> 
                                        </div>
                                    </div>
                                    <p>&nbsp;</p>
                                    
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

           app.controller('pageCtrl', function($scope, $http){

               //Release Student code 
               $scope.releaseCode = function(index, obj){
                         $scope.loading = true;
                         $http({
                             method : "POST",
                             url : "../../api/index.php?action=release_code",
                             data: {
                                 id :obj.id,
                                 user: obj.jambregid,
                                 prog_choice: obj.progname
                             }
                         }).then(function mySucces(response) {
                             deletItem(index);
                             $scope.loading = false;
                             alert(response.data);  
                         }, function myError(response) {
                             $scope.loading = false;
                             console.log(response);
                             alert('Unable to perform operation'+ response);
                         });    
                     };

                //Refere student for change of programme 
               $scope.referToAdmin = function(index, obj){
                         $scope.loading = true;
                         $http({
                             method : "POST",
                             url : "../../api/index.php?action=refer",
                             data: {
                                 id :obj.id,
                                 user: obj.jambregid,
                                 prog_choice: obj.progname
                             }
                         }).then(function mySucces(response) {
                             deletItem(index);
                             $scope.loading = false;
                             alert(response.data);  
                         }, function myError(response) {
                             $scope.loading = false;
                             alert('Unable to perform operation'+ response.data);
                             console.log(response);
                         });    
                     };

                //Set student to contact admission officer
               $scope.refere = function(index, obj){
                       $scope.loading = true;
                       $http({
                           method : "POST",
                           url : "../../api/index.php?action=refer2",
                           data: {
                               id :obj.id,
                               stdid: obj.stdid,
                               colid: obj.colid,
                               ordid: obj.ordid
                                
                           }
                       }).then(function mySucces(response) {
                           deletItem(index);
                           $scope.loading = false;
                           alert(response.data); 
                       }, function myError(response) {
                           $scope.loading = false;
                            alert(response.data);
                            
                       });
                   };
                    
               //Active Extra Olevel submission
               $scope.extraSubmission = function(index, obj){
                       $scope.loading = true;
                       $http({
                           method : "POST",
                           url : "../../api/index.php?action=extra",
                           data: {
                               id :obj.id,
                               stdid: obj.stdid,
                               colid: obj.colid,
                               ordid: obj.ordid
                                
                           }
                       }).then(function mySucces(response) {
                           deletItem(index);
                           $scope.loading = false;
                           alert(response.data); 
                       }, function myError(response) {
                           $scope.loading = false;
                            alert(response.data);
                            
                       });
                   };
                    
               function deletItem(index){
                   $scope.ver_code.splice(index, 1);
               };
                
                
                
               $scope.ver_code = veri_codes;
               $scope.programme = progs;
               $scope.seletedItem = '';
                
               $scope.setSelected = function(val){
                  $scope.seletedItem  = val;
               };
           });
        </script>
    </body>
</html>
