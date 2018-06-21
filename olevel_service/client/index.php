<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');
$auth_users = "10,11";
check_auth($auth_users, $site_root);

$where1  = "";
$clause = "";




//Get current user details 
if (getAccess() == '10') {

    mysql_select_db($database_tams, $tams);
    $query_rspros = sprintf("SELECT * "
                            . "FROM student "
                            . "WHERE stdid = %s", 
                            GetSQLValueString(getSessionValue('uid'), "text"));
    $rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
    $row_rspros = mysql_fetch_assoc($rspros);
    $totalRows_rspros = mysql_num_rows($rspros);
    
    $where1 .= sprintf("WHERE stdid = %s AND status='use'",GetSQLValueString(getSessionValue('uid'), "text"));
    
    $where2 .= sprintf("WHERE stdid = %s",GetSQLValueString(getSessionValue('uid'), "text"));
     
    $clause .= sprintf("JOIN verification v ON olvr.stdid = v.stdid "
                    . "AND  olvr.stdid = %s AND olvr.status = 'use'", 
                    GetSQLValueString(getSessionValue('uid'), 'text'));
} else {

    mysql_select_db($database_tams, $tams);
    $query_rspros = sprintf("SELECT p.* "
                            . "FROM prospective p "
                            . "WHERE p.jambregid = %s", 
                            GetSQLValueString(getSessionValue('uid'), "text"));
    $rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
    $row_rspros = mysql_fetch_assoc($rspros);
    $totalRows_rspros = mysql_num_rows($rspros);
    
    $where1 .= sprintf("WHERE jambregid = %s AND status='use'",GetSQLValueString(getSessionValue('uid'), "text"));
    
    $where2 .= sprintf("WHERE jambregid = %s",GetSQLValueString(getSessionValue('uid'), "text"));
    
    $clause .= sprintf("JOIN verification v ON olvr.jambregid = v.jambregid "
                    . "AND  olvr.jambregid = %s AND olvr.status = 'use'", 
                    GetSQLValueString(getSessionValue('uid'), 'text'));
}

$prog = getSessionValue('progid');
$level = getSessionValue('level');
if(getSessionValue('accttype') == 'pros'){
    $prog = $row_rspros['progid1'];
    $level = 0;
}


$query = sprintf("SELECT * "
                . "FROM verification "
                . "%s", 
                $where2);
$rsResult = mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);
$num_row_result = mysql_num_rows($rsResult); 

$status = ($row_result['verified'] == "TRUE") ? "<p style=' color: green; font-size: 20px; font-weight: bold'>VERIFIED</p>" : "<p style=' color: red; font-size: 20px; font-weight: bold'>NOT YET VERIFIED</p>";

$Query_prog_opt = sprintf("SELECT * "
                        . "FROM prog_options "
                        . "WHERE jambregid = %s ", 
                        GetSQLValueString(getSessionValue('uid'), 'text'));
$prog_opt = mysql_query($Query_prog_opt, $tams) or die(mysql_error());
$prog_opt_row = mysql_fetch_assoc($prog_opt);
$prog_opt_row_num = mysql_num_rows($prog_opt);


if ($prog_opt_row_num > 0) {

    $progQuery = sprintf("SELECT progid, progname "
                        . "FROM programme "
                        . "WHERE progid IN ( " . str_replace(["'", '"'], "", $prog_opt_row['choice']) . " )");
                $prgRS = mysql_query($progQuery) or die(mysql_error());
    $prgRS_row = mysql_fetch_assoc($prgRS);
}


$query1 = sprintf("SELECT *, olvr.id as resultid , "
                . "olvr.status as olvstatus, "
                . "olvr.approve as olvapprove "
                . "FROM olevel_veri_data olvr %s ", $where1); 
$veri_data = mysql_query($query1, $tams) or die(mysql_error());
$veri_data_row = mysql_fetch_assoc($veri_data);
$veri_data_row_num = mysql_num_rows($veri_data);

$data = array();
if($veri_data_row_num > 0){
    do{
        $data[] = $veri_data_row;
    }while($veri_data_row = mysql_fetch_assoc($veri_data));
}

$olevel_sub = FALSE;

$query2 = sprintf("SELECT * FROM verification %s LIMIT 1 ",$where2);
$q2_data = mysql_query($query2, $tams) or die(mysql_error());
$q2_data_row = mysql_fetch_assoc($q2_data);


if($q2_data_row['olevel_submit'] == 'TRUE'){
    $olevel_sub = TRUE;
}

$Query_prog_opt = sprintf("SELECT * "
                        . "FROM prog_options "
                        . "WHERE jambregid = %s ",
                        GetSQLValueString(getSessionValue('uid'), 'text') );
$prog_opt = mysql_query($Query_prog_opt, $tams) or die(mysql_error());
$prog_opt_row = mysql_fetch_assoc($prog_opt);
$prog_opt_row_num = mysql_num_rows($prog_opt);


if($prog_opt_row_num > 0){
    
    $progQuery = sprintf("SELECT progid, progname "
                        . "FROM programme "
                        . "WHERE progid IN ( " . str_replace(["'", '"'], "", $prog_opt_row['choice']) . " )");
    $prgRS = mysql_query($progQuery) or die(mysql_error());
    $prgRS_row = mysql_fetch_assoc($prgRS);
}
    

    
$status = ($row_result['verified'] == "TRUE") ? "<p style=' color: green; font-size: 20px; font-weight: bold'>VERIFIED</p>" : "<p style=' color: red; font-size: 20px; font-weight: bold'>NOT YET VERIFIED</p>";


if (isset($_POST['ver_code']) && $_POST['ver_code'] != NULL) {

    if ($_POST['ver_code'] == $q2_data_row['ver_code']) {

        $query2 = sprintf("UPDATE verification "
                . "SET verified = 'TRUE' "
                . "WHERE  jambregid = %s", 
                GetSQLValueString(getSessionValue('uid'), 'text'));
        $updateverify = mysql_query($query2, $tams) or die(mysql_error());
        
        if ($updateverify) {
            $notification->set_notification('Verification successfull.', 'success');

            
        } else {
            $notification->set_notification('Unable to verify please try again.', 'error');
        }
    } else {
        $notification->set_notification('Incorrect verification Code Entered', 'error');
    }
    
    header("Location: index.php");
    exit;
}


if (isset($_POST['prog_choice']) && $_POST['prog_choice'] != NULL) {

    $progQuery1 = sprintf("SELECT progid, progname "
            . "FROM programme "
            . "WHERE progid = '{$_POST['prog_choice']}'");
    $prg = mysql_query($progQuery1) or die(mysql_error());
    $prg_row = mysql_fetch_assoc($prg);

    $msg = "Congratulations! Your new choice of programme "
            . "is accepted and your o'level result met the "
            . "requirement of the programme "
            . " you selected  i.e <br/>(<strong>{$prg_row['progname']}</strong>)<br/>. Copy and paste "
            . "the above  verification code in the "
            . "text box below and click verified so that you can"
            . " proceed with your payment ";

    mysql_query("BEGIN");

    $Query_prosp = sprintf("UPDATE prospective "
            . "SET progoffered = %s "
            . "WHERE jambregid = %s", GetSQLValueString($_POST['prog_choice'], 'text'), GetSQLValueString(getSessionValue('uid'), 'text'));
    $prosRS = mysql_query($Query_prosp) or die(mysql_error());


    $Query_veri = sprintf("UPDATE verification "
            . "SET msg = %s, released_code = 'yes'"
            . "WHERE jambregid = %s ", 
            GetSQLValueString($msg, 'text'), 
            GetSQLValueString(getSessionValue('uid'), 'text'));
    $veriRS = mysql_query($Query_veri) or die(mysql_error());
    $affected = mysql_affected_rows();

    
    if ($affected > 0) {
        mysql_query("COMMIT");
    } else {
        mysql_query("ROLLBACK");
    }

    header("Location: index.php");
    exit;
}


if(isset($_POST['submit_veri'])){
   $veriSQL = sprintf("UPDATE verification "
                    . "SET olevel_submit = 'TRUE' "
                    . " %s ", 
                    $where2); 
    $veriRS = mysql_query($veriSQL, $tams) or die(mysql_error());
    
    header('Location:index.php');
    die();
}

if(isset($_POST['second_sitting'])){
    if(getAccess() == '10'){
        $sit = "Sitting 2";
        $query = sprintf("INSERT INTO olevel_veri_data "
                        . "(stdid, usertype, sesid, progid, level, label) "
                        . "VALUES(%s, %s, %s, %s, %s, %s)", 
                        GetSQLValueString(getSessionValue('uid'), 'text'), 
                        GetSQLValueString(getSessionValue('accttype'), 'text'),
                        GetSQLValueString(getSessionValue('sesid'), 'text'),
                        GetSQLValueString($prog, 'int'),
                        GetSQLValueString($level, 'text'),
                        GetSQLValueString($sit, 'text')); 
        $olevel = mysql_query($query, $tams) or die(mysql_error());
    }else{
        $sit = "Sitting 2";
        $query = sprintf("INSERT INTO olevel_veri_data "
                    . "(jambregid, usertype, sesid, progid, level, label) "
                    . "VALUES(%s, %s, %s, %s, %s, %s)", 
                    GetSQLValueString(getSessionValue('uid'), 'text'), 
                    GetSQLValueString(getSessionValue('accttype'), 'text'),
                    GetSQLValueString(getSessionValue('sesid'), 'text'),
                    GetSQLValueString($prog, 'int'),
                    GetSQLValueString($level, 'text'),
                    GetSQLValueString($sit, 'text')); 
    $olevel = mysql_query($query, $tams) or die(mysql_error());
    }
   
    
    header('Location:index.php');
    die();
}

if(isset($_POST['purge'])){
   
    $query = sprintf("DELETE FROM olevel_veri_data WHERE id  = %s ", 
                    GetSQLValueString($_POST['purge'], 'int'));
    $olevel = mysql_query($query, $tams) or die(mysql_error());
    
    header('Location:index.php');
    die();
}
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
                                    <h3><i class="icon-reorder"></i>
                                        O'Level Verification Module (Status Page)
                                    </h3>
                                </div>
                                
                                <?php if(1 == 1){?>
                                <div class="box-content">
                                    <?php if ($num_row_result > 0) { ?>
                                    <!--Enabling Second Sitting-->
                                        <?php if ($veri_data_row_num < 2) { ?>
                                            <div class="well well-small">
                                                <form method="post" action="index.php">
                                                <div class="alert alert-info">
                                                    Provided that your submission has not exceeded (2) two sittings you can still add (1) one more result.
                                                    Click the proceed button to add another result.
                                                </div>
                                                <button type="submit" name="second_sitting" class="btn btn-small btn-purple">proceed</button>
                                                </form>
                                            </div>
                                        <?php } ?>
                                    
                                        <div class="well well-small">
                                            <form method="post" class="form-horizontal" action="index.php">
                                                <div class="row-fluid">
                                                    <div class="span12">
                                                        <?php if ($olevel_sub) { ?>
                                                            <div class="alert alert-success">You have successfully submitted your O&apos;Level Result for further processing </div>
                                                        <?php } else { ?>
                                                            <div class="alert alert-danger" ng-if="!submitable">You still have pending O&apos;Level Result to be fetched </div>
                                                            <div class="alert alert-warning" ng-if="submitable">Certify your bellow fetched result(s) and click the submit O&apos;Level Result button to proceed </div>
                                                        <?php } ?>
                                                        <table class="table table-bordered table-condensed table-striped table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>S/n</th>
                                                                    <th>Label </th>
                                                                    <th>Exam Name</th>
                                                                    <th>Exam Type</th> 
                                                                    <th >Exam Year</th>
                                                                    <th >Exam No.</th>
                                                                    <th>Status </th>
                                                                    <th>Actions </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>

                                                                <tr ng-repeat="dt in submission" ng-cloak="" ng-if="submission.length > 0">
                                                                    <td>{{$index +1 }}</td>
                                                                    <td>{{dt.label}}</td>
                                                                    <td>{{dt.exam_name}}</td>
                                                                    <td>{{dt.exam_type}}</td>
                                                                    <td>{{dt.exam_year}}</td>
                                                                    <td>{{dt.exam_no}}</td>
                                                                    <td>
                                                                        <div class="badge badge-success" ng-if="dt.approve == 'Yes'">Fetched</div>
                                                                        <div class="badge badge-warning" ng-if="dt.approve != 'Yes'">Not Fetched </div>
                                                                    </td>
                                                                    <td> 
                                                                    <!--target="pop" onclick="pop=window.open(this.href,'OLEVELE SERVICE','width=700,height=700');"-->
                                                                         
                                                                        <form method="post" action="index.php">
                                                                            <a ng-if="dt.approve != 'Yes'" class="btn btn-primary btn-sm" href="http://olevel-io.ttihub.ng/client_area/index.php?school={{school_id}}&result_id={{dt.resultid}}&key={{school_key}}&user_id={{user_id}}">Fetch Result</a>
                                                                            <a class="btn btn-small btn-blue" 
                                                                               ng-if="dt.approve == 'Yes'" 
                                                                               href="#view_result" role="button" 
                                                                               data-toggle="modal" ng-click="getResult(dt); setSelectedItem(dt)"> <i class="icon icon-eye-open"></i> View Result</a>
                                                                            <a class="btn btn-small btn-brown" 
                                                                               ng-if="dt.approve == 'Yes'" 
                                                                               href="olevel_pdf.php?resultid={{dt.resultid}}&typ={{dt.usertype}}" target="tab"> <i class="icon icon-print"></i> Print Result</a> 
                                                                               <button ng-if="dt.approve != 'Yes'"  type="submit" class="btn btn-warning btn-sm" name="purge" value="{{dt.resultid}}">Clear</button>
                                                                        </form>
                                                                          
                                                                    </td>
                                                                </tr>
                                                                <tr ng-if="submission.length < 1">
                                                                    <td colspan="8"> 
                                                                        <div class="alert alert-warning">No record</div>
                                                                    </td>
                                                                </tr>
                                                            </tbody>    
                                                        </table>      
                                                    </div>
                                                </div>

                                                <p>&nbsp;</p>
                                                <?php if (!$olevel_sub) { ?>
                                                    <div class="row-fluid">
                                                        <div class="span12 alert alert-danger" ng-if="!submitable">
                                                            <p>Please note that without fetching all your result, your result will not be submitted for further processing </p>
                                                        </div>
                                                    </div>
                                                    <div class="row-fluid">
                                                        <div class="span12 alert alert-success" ng-if="submitable">
                                                            <p><input type="checkbox"  required="">
                                                                I certified that the above listed O&apos;Level result(s) is/are correct to the best of my knowledge 
                                                                and it should be used for the further processing of my Admission.  </p>
                                                            <button class="btn btn-success" name="submit_veri" >Submit O&apos;level Result</button>   
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            </form>
                                        </div>
                                    
                                    
                                    <!--revert the access level  to 11, when ready to activate VERIFY CODE-->
                                    <?php if(getAccess() == '110'){?>
                                    <p>&nbsp;</p>
                                            <h4>Admission Verification Status</h4>
                                            <div class="well">
                                                <p>&nbsp;</p>
                                                <div class="row-fluid">
                                                    <div class="span12">
                                                        <div class='alert' style="text-align: center" >
                                                            <?php echo $status; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p>&nbsp;</p>
                                                <?php if ($row_result['verified'] == "FALSE") { ?>
                                                    <div class="row-fluid">
                                                        <div class="span12">
                                                            <div class='alert' style="text-align: center" >

                                                                <?php if ($row_result['released_code'] == "yes" && $row_result['verified'] == "FALSE") { ?>
                                                                    <p style=' color: green; font-size: 20px; font-weight: bold'> <?= $row_result['ver_code'] ?> </p>
                                                                <?php } ?>

                                                                <?php if ($row_result['msg'] != "") { ?>
                                                                    <div class="alert alert-info">
                                                                        <p><?= $row_result['msg'] ?></p>
                                                                    </div>
                                                                <?php } ?>

                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                                
                                            
                                            
                                                <div class="row-fluid">
                                                    <?php if ($prog_opt_row_num > 0 && $row_result['refere'] == 'yes' && $row_result['released_code'] == 'no' && $row_result['treated'] == 'yes') { ?>
                                                        <div class="span6">
                                                            <form  class="form form-horizontal" name="form" method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>"> 
                                                                <div class="control-group">
                                                                    <label class="control-label" for="textfield">Choose Programme</label>
                                                                    <div class="controls">
                                                                        <div class="input-append">
                                                                            <select name="prog_choice" class="input-medium" required="">
                                                                                <option value="">--Choose--</option>

                                                                                <?php do { ?>
                                                                                    <option value="<?= $prgRS_row['progid'] ?>"><?= $prgRS_row['progname'] ?></option>
                                                                                <?php } while ($prgRS_row = mysql_fetch_assoc($prgRS)) ?>

                                                                            </select>
                                                                            <button type='submit' class="btn btn-blue">Accept Programme</button>
                                                                        </div>   
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            
                                                <div class="row-fluid">
                                                    <?php if ($row_result['verified'] == "FALSE" || empty($row_result)) { ?>
                                                        <div class="span6">
                                                            <form  class="form form-horizontal" name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>"> 
                                                                <div class="control-group">
                                                                    <label class="control-label" for="textfield">Verification Code</label>
                                                                    <div class="controls">
                                                                        <div class="input-append">
                                                                            <input type="text" class="input-block-level"  id="textfield" name="ver_code">
                                                                            <button type='submit' name='submit' class="btn btn-purple">Verify</button>
                                                                        </div>   
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                        
                                                      
                                                    <?php } else { ?>
                                                        <?php if (getAccess() == '11') { ?>
                                                            <a href="../admission/fee_payment/index.php" class="btn btn-primary">Pay School Fee</a>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                    <?php }?>
                                    
                                    
                                     <?php }else{?>
                                        <div class="row-fluid">
                                            <div class="span12">
                                                <div class="well well-small">
                                                    <div class="alert alert-info" style="text-align: center">
                                                        <h4>Welcome to the O'Level Result Verification System</h4>
                                                    </div>
                                                    <div class="alert alert-info" style="text-align: center">
                                                        <i class="glyphicon-circle_info"></i> 
                                                        You are to pay for your O'Level Result Verification Click the proceed button bellow to continue
                                                    </div>
                                                    <div class="row-fluid">
                                                        <div class="span3"></div>
                                                        <div class="span6">
                                                            <img src="../logo/waec_logo.png" height="100px" width="100px"> &nbsp;
                                                            <img src="../logo/neco_logo.png" height="100px" width="100px"> &nbsp;
                                                            <img src="../logo/nabteb_logo.png" height="100px" width="100px">
                                                        </div>
                                                        <div class="span3"></div>
                                                    </div>
                                                    <a href="status.php" class='btn btn-primary'>Proceed </a>
                                                </div>
                                            </div>
                                        </div>
                                     <?php }?>
                                </div>
                                <?php }?>
                            </div>
                            <p>&nbsp;</p>   
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH . "/footer.php" ?>
        <div id="view_result" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">{{selectedItem.exam_name}} Result for {{selectedItem.exam_no}}</h3>
            </div>
            <div class="modal-body">
                <div  ng-bind-html="renderHTML(selectedItem.result_table)"></div>
                
                <div class="row" ng-if="result.result.result" >
                    <div class="span4">
                        <b>Exam Name :</b> {{result.result.exam_name}} <br/>
                        <b>Exam Type :</b> {{result.result.exam_type}} <br/>
                        <b>Exam Year :</b> {{result.result.exam_year}} <br/>
                        <b>Exam Number :</b> {{result.result.exam_number}} <br/>
                        <b>Candidate Name :</b> {{result.result.candidate_name}} <br/>
                        <b>Exam Center :</b> {{result.result.exam_center}} <br/>
                        <br/>
                        <b>Subject/Score</b> 
                        <table class="table table-sm ">
                            <tbody>
                                <tr ng-repeat="rs in result.result.result">
                                    <td>{{rs.subject}}</td>
                                    <td>{{rs.score}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
        <script>
            var data = <?= json_encode($data) ?>;
            
            var app = angular.module('app', ['ngSanitize']);
            app.controller('pageCtrl', function($scope, $sce, $window){
                $scope.submission = data;
                
                $scope.school_id = '<?= $olevelio['school_id']?>';
                $scope.school_key = '<?= $olevelio['school_key']?>';
                $scope.user_id = '<?= getSessionValue('uid') ?>' ;
                
                $scope.result;
                $scope.getResult = function (v){
                    
                    $scope.result = JSON.parse(v.result_plain);
                    console.log($scope.result);
                }
                
                $scope.submitable = true;
                for(var i=0; i < data.length; i++){
                    if(data[i].olvstatus === 'use' && data[i].olvapprove != 'Yes'){
                        $scope.submitable = false;
                        break;
                    }
                   
                }
                
                $scope.selectedItem = '';
                $scope.setSelectedItem = function(item){
                    console.log(item);
                    $scope.selectedItem = item;
                };
                
                $scope.renderHTML = function(html_code){
                var decoded = angular.element('<textarea />').html(html_code).text();
                return $sce.trustAsHtml(decoded);
                };
                
                $scope.popitup = function(school_id, resultid, school_key,user_id){
                    var url = 'http://olevel-io.ttihub.ng/client_area/index.php?school='+school_id+'&result_id='+resultid+'&key='+school_key+'&user_id='+user_id;
                    newwindow=$window.open(url,'olevelresultio','height=800,width=800,left=400');
                	if ($window.focus) {newwindow.focus()}
                	return false;
                }
            });
            
            
            
        </script>
        
        
        
        
    </body>
</html>

