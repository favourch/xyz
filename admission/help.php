<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');
$sesSQL = "SELECT * FROM session WHERE admission = 'TRUE'";
$ses = mysql_query($sesSQL, $tams) or die(mysql_error());
$row_ses = mysql_fetch_assoc($ses);

if(isset($_POST)){
    
    if(isset($_POST['new_email']) && $_POST['new_email'] != NULL){
        
        $updateSQL = sprintf("UPDATE prospective "
                            . "SET email = %s "
                            . "WHERE jambregid = %s ",
                            GetSQLValueString($_POST['new_email'], 'text'),
                            GetSQLValueString($_POST['jambregid'], 'text'));
        $updateRs = mysql_query($updateSQL, $tams) or die(mysql_error());
        
        $sql = sprintf("SELECT fname, lname, mname, jambregid, email, act_token FROM prospective WHERE jambregid = %s ", GetSQLValueString($_POST['jambregid'], 'text'));
        $rs = mysql_query($sql, $tams) or die(mysql_error());
        $row_rs = mysql_fetch_assoc($rs);

        $validate_url = $portal_url . "/admission/activate_account.php?pstid=" . strtoupper($_POST['jambregid']) . "&token=" . $row_rs['act_token'];
        $mail_to = $row_rs['email'];
        $subject = $row_ses['sesname'] . "  Application for Admission on TAMS Portal.";
        $sender = $school_short_name;
        $message = "Congratulations...<br/><br/>
                    Dear {$row_rs['lname']} {$row_rs['fname']}  {$row_rs['mname']},<br/>   
                    Your Application Account has been created successfully. Your login detail is shown below:<br/>
                    <br/> Username : " . strtoupper($row_rs['jambregid']) . "<br/> Password : {$row_rs['lname']}<br/> <br/>"
                . "Click on the link below to activate your account <br/><br/> <a href='{$validate_url}'>Activate My Account</a>";
        $body = $message;


        $bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'> %s Application for Admission (Account Creation )</h3><p>%s</p>", $sesname, $message);

        $mailstatus = sendHtmlEmail($mail_to, $subject, $bd, 'no-reply@tasued.edu.ng', $university);
        
        $not_msg = "Congratulations, your E-mail has been updated! <br/>
                        An activation mail has been sent to the email address that you provided.<br/> 
                        Please check your e-mail to activate your account and proceed with your application process";
        $msg_type = 'success';
    }
    
    if(isset($_POST['new_jambreg']) && $_POST['new_jambreg'] != NULL){
        $testSQL = sprintf("SELECT * FROM prospective WHERE jambregid = %s ",
                GetSQLValueString($_POST['new_jambreg'], 'text') );
        $testRs = mysql_query($testSQL, $tams) or die(mysql_error());
        $num = mysql_num_rows($testRs);
        
        if($num > 0){
            $not_msg = "Sorry! the UTME number you are trying to use already Exist <br/>
                        please contact the helpdesk on <a href='$school_helpdesk'>{$school_helpdesk}</a> for resolution <br/> 
                        ";
            $msg_type = 'warning';
        }
        else{
            $updateSQL = sprintf("UPDATE prospective "
                    . "SET jambregid = %s "
                    . "WHERE jambregid = %s ", GetSQLValueString($_POST['new_jambreg'], 'text'), GetSQLValueString($_POST['jambregid'], 'text'));
            $updateRs = mysql_query($updateSQL, $tams) or die(mysql_error());

            $not_msg = "Congratulations, your UTME Number has been updated! <br/>
                        You can now proceed with your application.<br/> 
                        ";
            $msg_type = 'success';
        }
        
        
    }
    
    if(isset($_POST['login']) && $_POST['jambreg'] != NULL){
        $loginState = doLogin(1, $_POST['jambreg'], $_POST['lname'], "progress.php"); 
    }
}


?>
<!doctype html>
<html ng-app="app">
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="pageCtrl">
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
                                    <h3><i class="icon-reorder"></i>
                                        Create Account Support Module
                                    </h3>
                                </div>
                                <div class="box-content ">
                                    
                                    <div class="row-fluid">
                                        <div class="span6">
                                            <div class="well">
                                                <h4>Having Problem ?</h4>
                                                <form method="post" action="#" class="form form-horizontal">
                                                    <?php if (isset($not_msg)) : ?>
                                                        <div class="alert alert-<?php echo $msg_type ?>">
                                                            <?php echo $not_msg ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="control-group">
                                                        <label for="email" class="control-label">Problem Type </label>
                                                        <div class="controls">
                                                            <input type="radio" name="prob_type" value="email"  ng-click="getPrb('email')"> Wrong email Address?
                                                            <input type="radio" name="prob_type" value="jamb"  ng-click="getPrb('jamb')"> Wrong Jamb number?
                                                        </div>
                                                    </div> 
                                                    <div class="row-fluid" ng-if="loading">
                                                        <div>
                                                            <img src="../olevel_service/giphy.gif">
                                                        </div>
                                                    </div>
                                                    <div class="alert alert-danger" ng-if="fetched_result.status == '000' || fetched_result.status == '001'">
                                                        {{fetched_result.msg}}
                                                        <div class="form-actions">
                                                            <button type="button" ng-click="reset()" class="btn btn-small btn-gray">Try Again</button>
                                                        </div>
                                                    </div>
                                                    <div class="alert alert-success" ng-if="fetched_result.status == '002'">
                                                        <p>
                                                            {{fetched_result.rs.lname + ' '+ fetched_result.rs.fname +' '+fetched_result.rs.mname}} <br/>
                                                            {{fetched_result.rs.jambregid}}<br/>
                                                            {{fetched_result.rs.email}}<br/>
                                                        </p>
                                                        <div class="control-group" >
                                                            <label for="new_email" class="control-label"> New Email </label>
                                                            <div class="controls">
                                                                <input type="text" name="new_email" value="{{fetched_result.rs.email}}"> 
                                                            </div>
                                                        </div> 
                                                        <input type="hidden" name="jambregid" value="{{fetched_result.rs.jambregid}}">
                                                        <div class="form-actions">
                                                            <button type="submit"  class="btn btn-primary btn-success">Save and Get Auth Code</button>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="alert alert-danger" ng-if="fetched_result2.status == '000' || fetched_result2.status == '001'">
                                                        {{fetched_result2.msg}}
                                                        <div class="form-actions">
                                                            <button type="button" ng-click="reset()" class="btn btn-small btn-gray">Try Again</button>
                                                        </div>
                                                    </div>
                                                    <div class="alert alert-success" ng-if="fetched_result2.status == '002'">
                                                        <p>
                                                            {{fetched_result2.rs.lname + ' '+ fetched_result2.rs.fname +' '+fetched_result2.rs.mname}} <br/>
                                                            {{fetched_result2.rs.jambregid}}<br/>
                                                            {{fetched_result2.rs.email}}<br/>
                                                        </p>
                                                        <div class="control-group" >
                                                            <label for="new_email" class="control-label"> New UTME No. </label>
                                                            <div class="controls">
                                                                <input type="text" name="new_jambreg" value="{{fetched_result2.rs.jambregid}}"> 
                                                            </div>
                                                        </div> 
                                                        <input type="hidden" name="jambregid" value="{{fetched_result2.rs.jambregid}}">
                                                        <div class="form-actions">
                                                            <button type="submit"  class="btn btn-primary btn-success">Save and Continue</button>
                                                        </div>
                                                    </div>
                                                    
                                                    <div ng-if="prb == 'jamb' && fetched_result2 == false">
                                                        <div class="control-group" >
                                                            <label for="email" class="control-label">Email </label>
                                                            <div class="controls">
                                                                <input type="text" name="email" ng-model="jamberr.email"> 
                                                            </div>
                                                        </div> 
                                                        <div class="control-group" >
                                                            <label for="jambreg" class="control-label">Surname </label>
                                                            <div class="controls">
                                                                <input type="text" name="lname" ng-model="jamberr.lname"> 
                                                            </div>
                                                        </div> 
                                                        <div class="form-actions">
                                                            <button type="button" ng-click="fetchData2(jamberr)" class="btn btn-primary btn-success">Submit</button>
                                                        </div>
                                                    </div>
                                                    <div ng-if="prb == 'email' && fetched_result == false">
                                                        <div class="control-group">
                                                            <label for="email" class="control-label">Jamb Reg. id</label>
                                                            <div class="controls">
                                                                <input type="text" name="jambreg" ng-model="mailerr.jambregid"> 
                                                            </div>
                                                        </div> 
                                                        <div class="control-group">
                                                            <label for="email" class="control-label">Surname</label>
                                                            <div class="controls">
                                                                <input type="text" name="lname" ng-model="mailerr.lname"> 
                                                            </div>
                                                        </div> 
                                                        <div class="form-actions">
                                                            <button type="button" ng-click="fetchData(mailerr)" class="btn btn-primary btn-success">Submit</button>
                                                        </div>
                                                    </div>
                                                    
                                                </form>
                                            </div>
                                        </div>
                                        <div class="span6">
                                            <div class="well">
                                                <?php if (isset($_GET['accesscheck'])) : ?>
                                                    <div class="alert alert-error">
                                                        You are not authorized to view the resource you just attempted to view. 
                                                        Please log in with an account with the right privilege! 

                                                        <?php if (isset($_SERVER['HTTP_REFERER'])) : ?>
                                                            <a href="<?php echo $_SERVER['HTTP_REFERER'] ?>">Click here</a> to go back.
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                                <form method="post" action="#" class="form form-horizontal">
                                                    <div class="control-group">
                                                        <label for="email" class="control-label">Jamb Reg. ID </label>
                                                        <div class="controls">
                                                            <input type="text" class="input" name="jambreg"> 
                                                        </div>
                                                    </div>  
                                                    <div class="control-group">
                                                        <label for="email" class="control-label">Surnname </label>
                                                        <div class="controls">
                                                            <input type="text" class="input" name="lname"> 
                                                        </div>
                                                    </div> 
                                                    <div class="form-actions">
                                                        <button type="submit" name="login" class="btn btn-primary btn-success">Login</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="clearfix"> </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
        </div> 
        <script>
            var app = angular.module('app', []);
            
            app.controller('pageCtrl', function($scope, $http){
                $scope.getPrb = function(v){
                    $scope.prb = v;
                };
                
                $scope.reset = function(){
                    $scope.fetched_result = false;
                    $scope.fetched_result2 = false;
                }
                
                $scope.fetched_result = false;
                $scope.fetchData = function(obj){
                    $scope.loading = true;
                    $http({
                        method : "POST",
                        url : "api/index.php?action=applicant1",
                        data:obj
                    }).then(function mySucces(response) {
                        $scope.fetched_result = response.data;
                        $scope.loading = false;

                    }, function myError(response) {
                        $scope.loading = false;
                        alert('Unable to perform operation'+ response);
                    });    
                };
                
                $scope.fetched_result2 = false;
                $scope.fetchData2 = function(obj){
                    $scope.loading = true;
                    $http({
                        method : "POST",
                        url : "api/index.php?action=applicant",
                        data:obj
                    }).then(function mySucces(response) {
                        $scope.fetched_result2 = response.data;
                        $scope.loading = false;

                    }, function myError(response) {
                        $scope.loading = false;
                        alert('Unable to perform operation'+ response);
                    });    
                };
            });
        </script>
    </body>
</html>
