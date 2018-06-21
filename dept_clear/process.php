<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../path.php');



$auth_users = "1,2,3,20";
check_auth($auth_users, '/'.$site_root);


if(isset($_POST['colid']) && $_POST['colid'] != ''){
   $_SESSION['olv_veri_col'] = $_POST['colid'];  
}

if(isset($_POST['select']) && $_POST['select'] == "Proceed DE"){
    $_SESSION['olv_veri_type'] = "DE";
}


if(!isset($_SESSION['olv_veri_col'])){
  header('Location: index.php');
  exit();
}


$cur_session = -1;
if(isset($_GET['sid'])){
    $cur_session = $_GET['sid'];
}else{
    $cur_session = $_SESSION['sesid'];
}

$query_lect = sprintf("SELECT * FROM lecturer l JOIN department d  ON l.deptid = d.deptid WHERE lectid = %s", 
                        GetSQLValueString($_SESSION['uid'], 'text')); 
$lect = mysql_query($query_lect, $tams) or die(mysql_error());
$row_lect = mysql_fetch_assoc($lect);


$query_ses = sprintf("SELECT * FROM session ORDER BY sesid DESC");
$ses = mysql_query($query_ses, $tams) or die(mysql_error());
$row_ses = mysql_fetch_assoc($ses);


$query_prog = sprintf("SELECT progid, progname "
                    . "FROM programme "
                    . "WHERE continued = 'Yes' "
                    . "ORDER BY progid DESC");
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);

$programs = array();
while($row_prog = mysql_fetch_assoc($prog)){
    $programs[] = array(
        'progid' => $row_prog['progid'],
        'progname' => $row_prog['progname']
    );
}

$program = json_encode($programs);

$currentPage = $_SERVER["PHP_SELF"];

$maxRows_Rsall = 25;
$pageNum_Rsall = 0;
if (isset($_GET['pageNum_Rsall'])) {
    $pageNum_Rsall = $_GET['pageNum_Rsall'];
}
$startRow_Rsall = $pageNum_Rsall * $maxRows_Rsall;
//***********************************************


$query_part = '';
$query_part1 ="";
if(isset($_POST['search']) && isset($_POST['seed']) && $_POST['seed'] != ''){
    $seed = trim($_POST['seed']);
     $query_part1 = "AND  (p.fname LIKE '%".$seed."%' "
                    . "OR p.lname LIKE  '%".$seed."%' "
                    . "OR p.mname LIKE '%".$seed."%' "
                    . "OR v.stdid LIKE '%".$seed."%' "
                    . "OR v.jambregid LIKE '%".$seed."%' ) ";
   
}

if(isset($_POST['search']) 
        && isset($_POST['from']) 
        && isset($_POST['to']) 
        && $_POST['from'] != '' 
        && $_POST['to'] != ''){
    $from = GetSQLValueString(trim($_POST['from']), 'date');
    $to = GetSQLValueString(trim($_POST['to']), 'date');
    
     $query_part =   sprintf(" AND v.created_date BETWEEN CAST(%s AS DATE) AND CAST(%s AS DATE)", 
                    $from,
                    $to);
}



    $student_type = "Prospective Students";
    
    if(isset($_SESSION['olv_veri_type']) && $_SESSION['olv_veri_type'] == "DE"){
        $student_type .= " (DE)";
        $query = sprintf("SELECT v.*, p.*, c.colid, c.colname, d.deptid, d.deptname, at.typename, prg.progname, p.jambregid AS stdid, rt.regtypename, rt.regtypeid FROM verification v "
                . "JOIN prospective p ON v.jambregid = p.jambregid "
                . "JOIN admissions a ON a.admid = p.admid "
                . "JOIN admission_type at ON at.typeid = a.typeid "
                . "JOIN registration_type rt ON p.regtypeid = rt.regtypeid "
                . "JOIN programme prg ON p.progoffered = prg.progid "
                . "JOIN department d ON d.deptid = prg.deptid "
                . "JOIN college c ON c.colid = d.colid "
                . "AND d.deptid = %s AND clinic_clear = 'yes' "
                . "AND p.adminstatus = 'Yes' "
                . "AND v.olevel_submit = 'TRUE'  %s "
                . "WHERE v.sesid = %s AND at.typename = 'DE' "
                . "AND v.status = '0' %s ",
            GetSQLValueString($row_lect['deptid'], 'int'),
            $query_part,
            GetSQLValueString($cur_session, 'int'), $query_part1);
    }
    else{
        $query = sprintf("SELECT v.*, p.*, c.colid, c.colname, d.deptid, d.deptname, at.typename, prg.progname, p.jambregid AS stdid, rt.regtypename, rt.regtypeid FROM verification v "
                . "JOIN prospective p ON v.jambregid = p.jambregid "
                . "JOIN admissions a ON a.admid = p.admid "
                . "JOIN admission_type at ON at.typeid = a.typeid "
                . "JOIN registration_type rt ON p.regtypeid = rt.regtypeid "
                . "JOIN programme prg ON p.progoffered = prg.progid "
                . "JOIN department d ON d.deptid = prg.deptid "
                . "JOIN college c ON c.colid = d.colid "
                . "AND d.deptid = %s AND clinic_clear = 'yes' "
                . "AND p.adminstatus = 'Yes' "
                . "AND v.olevel_submit = 'TRUE'  %s "
                . "WHERE v.sesid = %s "
                . "AND v.status = '0' %s "
                . "AND (round(((p.jambscore1+jambscore2+jambscore3+jambscore4)*0.15),0)+round((p.score*0.8),0))>29 ",
            GetSQLValueString($row_lect['deptid'], 'int'),
            $query_part,
            GetSQLValueString($cur_session, 'int'), $query_part1);
    }
     
    $query_limit_verify = sprintf("%s ORDER BY  v.jambregid ASC LIMIT %d, %d  ", $query, $startRow_Rsall, $maxRows_Rsall);
    $verify = mysql_query($query_limit_verify, $tams) or die(mysql_error());
    //$verify_row = mysql_fetch_assoc($verify);
    //$verify_row_num = mysql_num_rows($verify);    






if (isset($_GET['totalRows_Rsall'])) {
    
    $totalRows_Rsall = $_GET['totalRows_Rsall'];
}
else {
    
    $all_Rsall = mysql_query($query);
    $totalRows_Rsall = mysql_num_rows($all_Rsall);
}
$totalPages_Rsall = ceil($totalRows_Rsall / $maxRows_Rsall) - 1;

$queryString_Rsall = "";
if (!empty($_SERVER['QUERY_STRING'])) {
    $params = explode("&", $_SERVER['QUERY_STRING']);
    $newParams = array();
    foreach ($params as $param) {
        if (stristr($param, "pageNum_Rsall") == false &&
                stristr($param, "totalRows_Rsall") == false) {
            array_push($newParams, $param);
        }
    }
    if (count($newParams) != 0) {
        $queryString_Rsall = "&" . htmlentities(implode("&", $newParams));
    }
}
$queryString_Rsall = sprintf("&totalRows_Rsall=%d%s", $totalRows_Rsall, $queryString_Rsall);

$code = array();


while ($verify_row = mysql_fetch_assoc($verify)) {
   
    $code[] = array(
            'id'        =>   $verify_row['id'],
            'jambregid' =>   $verify_row['stdid'],
            'fname'     =>   $verify_row['fname'],
            'lname'     =>   $verify_row['lname'],
            'mname'     =>   $verify_row['mname'],
            'pstdid'    =>   $verify_row['pstdid'],
            'ver_code'  =>   $verify_row['ver_code'],
            'progid1'   =>   $verify_row['progid1'],
            'typename'  =>   $verify_row['typename'],
            'progname'  =>   $verify_row['progname'],
            'dept_clear'  =>   $verify_row['dept_clear'],
            'phone'     =>   $verify_row['phone'],
            'email'     =>   $verify_row['email'],
            'sesid'     =>   $verify_row['sesid'],
            'regtypename'=>  $verify_row['regtypename'],
            'regtypeid'=>  $verify_row['regtypeid'],
            'jambscore1' => (int) $verify_row['jambscore1'],
            'jambscore2' =>  (int)$verify_row['jambscore2'],
            'jambscore3' =>  (int)$verify_row['jambscore3'],
            'jambscore4' =>  (int)$verify_row['jambscore4'],
            'score' =>  $verify_row['score'],
            'jamb_total' =>  $verify_row['jamb_total']
        );  
}

$codes = json_encode($code);

$query = sprintf("SELECT colid, colname, coltitle "
                . "FROM college WHERE colid = %s", 
        GetSQLValueString($_SESSION['olv_veri_col'], 'int'));
$college = mysql_query($query, $tams) or die(mysql_error());
$row_college = mysql_fetch_assoc($college);


if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root . '/admin');
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


if(isset($_POST['release'])){
    
    $msg = "Congratulations! <br /> Your O`level result has been"
            . " verified and it meets the minimum admission requirement of the"
            . " programme you applied for. <br /><br />"
            . "You have, therefore, been offered provisional admission "
            . "to pursue a Full-Time Bachelor's Degree in (<strong>{$_POST['progname']}</strong>). <br /><br />"
            . "Login to the portal - https://my.tasued.edu.ng to check your Admission Status & pay Acceptance Fee online ";
           

    $SQL = sprintf("UPDATE verification "
                . "SET released_code = 'yes', "
                . "status = 'release', "
                . "released_by = %s, "
                . "released_date = NOW(), "
                . "treated = 'yes', "
                . "msg = %s  "
                . " WHERE jambregid = %s", 
                GetSQLValueString(getSessionValue('uid'), 'text'),
                GetSQLValueString($msg, 'text'),
                 GetSQLValueString($_POST['stdid'], 'text')
            );
    mysql_query($SQL, $tams) or die(mysql_error());
    
    $SQL2 = sprintf("UPDATE prospective "
                . "SET adminstatus = 'yes', progoffered = progid1 "
                . "WHERE jambregid = %s ",
                GetSQLValueString($_POST['stdid'], 'text'));
    mysql_query($SQL2, $tams) or die(mysql_error());
    
    $bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'>O'Level Result Verification </h3><p>%s</p>",$msg);
    
    
    if(isset($_POST['email']) && $_POST['email'] != ""){
        sendHtmlEmail($_POST['email'], 
                "Olevel Verification", 
                $bd, 'admissions@tasued.edu.ng',
                "Admissions Office (Olevel Result Verification unit)");
    }
    
    if(isset($_POST['phone']) && $_POST['phone'] != ""){
        $sms = "You have been offered provisional admission at TASUED. Login to the portal- https://my.tasued.edu.ng to check your Admission Status & pay Acceptance Fee online";
        sendSMS("TASUED", $_POST['phone'], $sms);
    }   
    
    header("Location: process.php");
    die();
}



if(isset($_POST['refer'])){
    $msg = " Your O`level result has been verified but "
                . "does not meet the minimum admission requirement of the programme that you applied for i.e "
                . " (<strong>{$_POST['progname']}</strong>).<br/><br />"
                . "However, you have been referred for a Change of Programme. "
                . "Kindly wait for the Admissions Office's decision on your application. <br /><br />Check back in 2 working days";
    
    $SQL = sprintf("UPDATE verification "
                    . "SET status = 'refer', "
                    . "refered_date = NOW(), "
                    . "refered_by = %s, "
                     . "msg = %s, "
                    . "refere = 'yes', "
                    . "treated = 'yes' "
                    . "WHERE jambregid = %s ",
                     GetSQLValueString(getSessionValue('uid'), 'text'), 
                     GetSQLValueString($msg, 'text'),
                     GetSQLValueString($_POST['stdid'], 'text'));
    
    mysql_query($SQL, $tams) or die(mysql_error());
    
    $SQL2 = sprintf("UPDATE prospective "
                . "SET adminstatus = 'No' "
                . "WHERE jambregid = %s ",
                GetSQLValueString($_POST['stdid'], 'text'));
    mysql_query($SQL2, $tams) or die(mysql_error());
    
    $bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'>O'Level Result Verification </h3><p>%s</p>",$msg);
    
    if(isset($_POST['email']) && $_POST['email'] != ""){
        sendHtmlEmail($_POST['email'], 
                "Olevel Verification", 
                $bd, 'admissions@tasued.edu.ng',
                "Admissions Office (Olevel Result Verification unit)");
    }
    
    
    
    header("Location: process.php");
    die();
    
}


if(isset($_POST['terminate'])){
    $msg = "Sorry, your O'Level Result does not meet the minimum admission requirement for the "
            . " programme you applied for i.e <br/>(<strong>{$_POST['progname']}</strong>). <br/><br/>"
            . "Your application for an Offer Provisional Admission is denied. However, you can contact the Admissions Office for further advice";
            
    $SQL = sprintf("UPDATE verification "
                . "SET  "
                . "status = 'terminate', "
                . "refered_by = %s, "
                . "refered_date = NOW(), "
                . "treated = 'yes', "
                . "msg = %s "
                . " WHERE jambregid = %s ", 
                GetSQLValueString(getSessionValue('uid'), 'text'),
                GetSQLValueString($msg, 'text'),
                GetSQLValueString($_POST['stdid'], 'text')
                
            );
    mysql_query($SQL, $tams) or die(mysql_error());
    
    $SQL2 = sprintf("UPDATE prospective "
                . "SET adminstatus = 'no' "
                . "WHERE jambregid = %s ",
                GetSQLValueString($_POST['stdid'], 'text'));
    mysql_query($SQL2, $tams) or die(mysql_error());
    
    $bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'>O'Level Result Verification </h3><p>%s</p>",$msg);
    
    
    if(isset($_POST['email']) && $_POST['email'] != ""){
        sendHtmlEmail($_POST['email'], 
                "Olevel Verification", 
                $bd, 'admissions@tasued.edu.ng',
                "Admissions Office (Olevel Result Verification unit)");
    }    
    
  
    header("Location: process.php");
    die();
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
                                        <?= $student_type ?> Department Clearance  Page (<?= $row_college['coltitle']?>) <?= $row_lect['deptname']?>
                                    </h3>
                                    
                                </div>
                                <div class="box-content">
                                    <div class="well well-small">
                                        <div class="row-fluid">
                                            <div class="span2">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">Session</label>
                                                    <div class="controls controls-row">
                                                        <div class="input-append ">
                                                            <select name="name" class="input-block-level"  onchange="sesfilt(this)">
                                                                <option value="">--Choose--</option>
                                                                <?php do { ?>
                                                                    <option value="<?= $row_ses['sesid'] ?>" <?= ($cur_session == $row_ses['sesid']) ? 'selected' : '' ?>><?= $row_ses['sesname'] ?></option>
                                                                <?php } while ($row_ses = mysql_fetch_assoc($ses)); ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div> 
                                            </div>
                                            <div class="span3">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">Search by Name or Jamregid</label>
                                                    <div class="controls"> 
                                                        <form method="post" name="search" action="<?= $editFormAction?>">
                                                            <div class="input-append">
                                                                <input type="text" class="input-medium" name="seed" ng-model="seed" >
                                                                <button type="submit" name="search" class="btn btn-blue">Search</button>
                                                            </div>  
                                                        </form>
                                                    </div>
                                                </div> 
                                            </div>
                                            <div class="span7">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">Search by Date</label>
                                                    <div class="controls"> 
                                                        <form method="post" class="form-inline"name="search" action="<?= $editFormAction?>">
                                                            <div class="input-append">
                                                                <input type="text" class="input-medium datepick" data-date-format="yyyy-mm-dd" value="<?= (isset($_POST['from'])) ? $_POST['from'] : '' ?>" id="textfield" name="from" placeholder="From">
                                                                <input type="text" class="input-medium datepick" data-date-format="yyyy-mm-dd" value="<?= (isset($_POST['to'])) ? $_POST['to'] : '' ?>" id="textfield" name="to"  placeholder="To">
                                                                <button type="submit" name="search" class="btn btn-blue">Search</button>
                                                            </div>  
                                                        </form>
                                                    </div>
                                                </div> 
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class='row-fluid'>
                                      
                                            <div class="span12">
                                                <div ng-if="loading">
                                                    <span>Processing</span> please wait <img src="../../../img/loading.gif">
                                                </div>
                                                <table class="table  table-condensed table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th width="5%">#</th>
                                                            <th width="10%">Jamb Reg No</th>
                                                            <th width="25%">Full Name</th>
                                                            <th width="25%">Programme Choice</th>
                                                            <th width="10%">Adm.Type</th>
                                                            <th width="10%">Reg.Type</th>
                                                            <th width="10%">Agregate Score</th>
                                                            <th width="15%">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        
                                                        <tr ng-repeat="cd in ver_code | filter : seed" class="fade in">
                                                            <td>{{$index + 1}}</td>
                                                            <td>
                                                                <a  target="_blank" href="'../../../admission/viewform.php?stid={{cd.jambregid}}">{{cd.jambregid}} </a>    
                                                            </td>
                                                            <td>{{cd.lname}} {{cd.fname}} {{cd.mname}}</td>
                                                            <td>{{cd.progname}}</td>
                                                            <td>{{cd.typename}}</td>
                                                            <td>{{cd.regtypename}}</td>
                                                            <td ng-if="cd.regtypeid == 1">{{(cd.jamb_total * 0.15) + (cd.score * 0.8) | roundup}}</td>
                                                            <td ng-if="cd.regtypeid != 1">{{((cd.jambscore1 + cd.jambscore2 + cd.jambscore3 + cd.jambscore4)* 0.15) + (cd.score * 0.8) | roundup }}</td>
                                                            
                                                            <td>
                                                                <a class="btn btn-small btn-blue" ng-if="cd.dept_clear == 'no'"
                                                                   href="#view_result" role="button" 
                                                                   data-toggle="modal" ng-click="fetchResult(cd); setSelected(cd)"><i class="icon icon-eye-open"></i>View</a>
                                                                   <div class="badge badge-success" ng-if="cd.dept_clear == 'yes'"> Cleared </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table> 
                                            </div>
                                    </div>
                                    <p>&nbsp;</p>
                                    <div class="row-fluid">
                                        <table  class="table table-condensed table-striped">
                                            <tr width="50" align="center">
                                                <td style="text-align: center"><a class="btn btn-small btn-blue" href="<?php printf("%s?pageNum_Rsall=%d%s", $currentPage, max(0, $pageNum_Rsall - 1), $queryString_Rsall); ?>"><i class='icon-fast-backward'></i> Prev</a></td>
                                                <td style="text-align: center"><?php echo 'Page ' . ($pageNum_Rsall + 1) . " of " . ($totalPages_Rsall + 1); ?></td>
                                                <td style="text-align: center"><a class="btn btn-small btn-blue" href="<?php printf("%s?pageNum_Rsall=%d%s", $currentPage, min($totalPages_Rsall, $pageNum_Rsall + 1), $queryString_Rsall); ?>">Next <i class='icon-fast-forward'></i></a></td>
                                            </tr>
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

        <div id="view_result" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 id="myModalLabel">O&apos;Level Result for ( {{seletedItem.jambregid}} ) {{seletedItem.fname}} {{seletedItem.lname}} {{seletedItem.mname}} - {{seletedItem.progname}} ({{fetched_result.progcount.admitted}} of {{fetched_result.progcount.quota}})</h4>
            </div>
            <form method="post" action="process.php">
                <div class="modal-body">
                    <div class="row-fluid" ng-if="loading">
                        <div>
                            <img src="../../giphy.gif">
                        </div>
                    </div>
                    <div class="row-fluid" ng-if="fetched_result.olevel" >
                        <div class="span6 well well-large" ng-repeat="result in fetched_result.olevel">
                            <span>Exam Year : {{result.exam_year}}</span>
                            <div ng-bind-html="renderHTML(result.result_table)"></div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <input type="hidden" name="stdid" value="{{seletedItem.jambregid}}">
                    <input type="hidden" name="email" value="{{seletedItem.email}}">
                    <input type="hidden" name="phone" value="{{seletedItem.phone}}">
                    <input type="hidden" name="progname" value="{{seletedItem.progname}}">
                    <?php if(in_array(getAccess(), ['20','1', '2', '3'])){?>
                    <button type="submit" name="release" class="btn btn-primary">Clear</button>
                    <?php }?>
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                </div>
            </form>
        </div>
        <script>
            var veri_codes = <?= $codes ?>;
            var progs = <?= $program ?>;

            var app = angular.module('just', ['ngSanitize']);
            
            app.filter('roundup', function () {
                    return function (value) {
                        return Math.ceil(value);
                    };
                })

            app.controller('pageCtrl', function($scope, $http,  $sce){

                
                
                $scope.ver_code = veri_codes;
                $scope.programme = progs;
                $scope.seletedItem = '';
                
                $scope.setSelected = function(val){
                   $scope.seletedItem  = val;
                };
                
                $scope.loading = false;
                $scope.fetched_result = false;
                $scope.fetchResult = function(obj){
                    $scope.loading = true;
                    $http({
                        method : "POST",
                        url : "../olevel_service/api/index.php?action=fetch_result",
                        data: {
                            user:    obj.jambregid,
                            progid1: obj.progid1,
                            sesid: obj.sesid
                        }
                    }).then(function mySucces(response) {
                        $scope.fetched_result = response.data;
                        $scope.loading = false;

                    }, function myError(response) {
                        $scope.loading = false;
                        alert('Unable to perform operation'+ response);
                    });    
                };
                
                $scope.renderHTML = function(html_code){
                    var decoded = angular.element('<textarea />').html(html_code).text();
                    return $sce.trustAsHtml(decoded);
                };
            });
        </script>
    </body>
</html>
