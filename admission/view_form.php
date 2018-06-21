<?php
if (!isset($_SESSION)) {

    session_start();
}

require_once('../path.php');

$auth_users = "11";
check_auth($auth_users, $site_root);

define('MAX_FILE_SIZE', 2048 * 1536);
define('UPLOAD_DIR', IMGPATH.'/user/prospective/');

$jambregid = getSessionValue('uid');
if (isset($_GET['stid']) && in_array(getAccess(), [1, 20, 21, 22, 23, 24])) {
    $jambregid = $_GET['stid'];
}

//Rereive all previous school attended 
$prevSchool_query = sprintf("SELECT * FROM applicant_prev_school "
                            . "WHERE pstdid = %s", 
                            GetSQLValueString($jambregid, 'text'));
$prevSchool = mysql_query($prevSchool_query, $tams);
$totalRows_prevSchool = mysql_num_rows($prevSchool);

$prevSchool_array = array();
if ($totalRows_prevSchool > 0) {
    for (; $prevSchoolRS = mysql_fetch_assoc($prevSchool);) {
        array_push($prevSchool_array, $prevSchoolRS);
    }
}

//Get Prospective infomation
$prospectiveSQL = sprintf("SELECT * FROM prospective p JOIN admissions a ON p.admid = a.admid JOIN admission_type at on at.typeid = a.typeid WHERE jambregid = %s ", GetSQLValueString($jambregid, 'text'));
$prospectiveRS = mysql_query($prospectiveSQL, $tams);
$prospective_row = mysql_fetch_assoc($prospectiveRS);


$query_rspros = sprintf("SELECT p.*, s.sesid, st.stname, formsubmit, a.admid, regtypeid, pr.progname AS prog1, "
        . "pr2.progname AS prog2, at.typename, s1.subjname as jamb1, s2.subjname as jamb2, s3.subjname as jamb3, s4.subjname as jamb4, lga.lganame, at.typeid "
        . "FROM prospective p "
        . "LEFT JOIN admissions a ON p.admid = a.admid "
        . "LEFT JOIN admission_type at ON a.typeid = at.typeid "
        . "LEFT JOIN session s ON a.sesid = s.sesid "
        . "LEFT JOIN programme pr ON p.progid1 = pr.progid "
        . "LEFT JOIN programme pr2 ON p.progid2 = pr2.progid "
        . "LEFT JOIN subject s1 ON p.jambsubj1 = s1.subjid "
        . "LEFT JOIN subject s2 ON p.jambsubj2 = s2.subjid "
        . "LEFT JOIN subject s3 ON p.jambsubj3 = s3.subjid "
        . "LEFT JOIN subject s4 ON p.jambsubj4 = s4.subjid "
        . "LEFT JOIN state st ON st.stid = p.stid "
        . "LEFT JOIN state_lga lga ON lga.lgaid = p.lga "
        . "WHERE p.jambregid = %s", GetSQLValueString($jambregid, "text"));

$rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
$row_rspros = mysql_fetch_assoc($rspros);
$totalRows_rspros = mysql_num_rows($rspros);

$sesid = $row_rspros['sesid'];

$image_url = get_pics($jambregid, UPLOAD_DIR . explode('/', $_SESSION['admname'])[0]);

$jambtotal = ($row_rspros['jambscore1'] + $row_rspros['jambscore2'] + $row_rspros['jambscore3'] + $row_rspros['jambscore4']);


$query_rssit1 = sprintf("SELECT * 
                        FROM olevel o 
                        JOIN olevelresult l ON o.olevelid = l.olevelid 
                        JOIN subject s ON l.subject = s.subjid 
                        JOIN grade g ON l.grade = g.grdid 
                        WHERE o.jambregid = %s
                        AND sitting='first'", GetSQLValueString($jambregid, "text"));
$rssit1 = mysql_query($query_rssit1, $tams) or die(mysql_error());
$row_rssit1 = mysql_fetch_assoc($rssit1);
$totalRows_rssit1 = mysql_num_rows($rssit1);


$query_rssit2 = sprintf("SELECT * 
                        FROM olevel o 
                        JOIN olevelresult l ON o.olevelid = l.olevelid 
                        JOIN subject s ON l.subject = s.subjid 
                        JOIN grade g ON l.grade = g.grdid 
                        WHERE o.jambregid=%s
                        AND sitting='second'", GetSQLValueString($jambregid, "text"));
$rssit2 = mysql_query($query_rssit2, $tams) or die(mysql_error());
$row_rssit2 = mysql_fetch_assoc($rssit2);
$totalRows_rssit2 = mysql_num_rows($rssit2);

$query_info = sprintf("SELECT * "
                    . "FROM payschedule "
                    . "WHERE level = '0' "
                    . "AND sesid = %s "
                    . "AND admid = %s "
                    . "AND regtype = %s "
                    . "AND payhead = %s", 
                    GetSQLValueString($sesid, 'int'), 
                    GetSQLValueString($row_rspros['typeid'], 'text'), 
                    GetSQLValueString($row_rspros['regtypeid'], 'text'), 
                    GetSQLValueString('app', 'text')); 
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$amt = $row_info['amount']; 

$olvel_veri_data = sprintf("SELECT * FROM olevel_veri_data olv JOIN verification v ON olv.jambregid  = v.jambregid AND olv.approve = 'yes' AND olv.status = 'use' AND v.olevel_submit = 'TRUE' WHERE olv.jambregid = %s ",
                        GetSQLValueString($jambregid, "text"));
$olevel_verRS = mysql_query($olvel_veri_data, $tams) or die(mysql_error());
$row_olevel_rs = mysql_fetch_assoc($olevel_verRS);
$num_rows_olevel = mysql_num_rows($olevel_verRS);

$pay_status = checkPaymentPros($sesid, $jambregid, $amt); 


if (!$pay_status['status']) {
    header('Location: admission_payment/index.php');
    exit;
}

if ($row_rspros['formsubmit'] == 'No' && in_array(getAccess(), [11])) {
    header('Location: form.php');
    exit;
}
?>

<!doctype html>

<html ng-app="tams_admission_appform">

<?php include INCPATH . "/header.php" ?>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="MainCtrl">
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
                                    <h3><i class="icon-reorder"></i><?php echo $row_rspros['typename']?> Application Form</h3>
                                </div>
                                <div class="box-content">
                                    <?php if($row_rspros['activate'] == 'true') {?>
                                    <strong>Bio Data</strong>
                                    <div class="well">
                                        <table class="table table-striped table-bordered table-hover">                                          
                                            <tbody>
                                                <tr>
                                                    <th width="15%">Surname :</th>
                                                    <td width="25%"><?php echo $row_rspros['lname'] ?></td>
                                                    <th width="15%">First name :</th>
                                                    <td width="25%"><?php echo $row_rspros['fname'] ?></td>
                                                    <td width="20%" rowspan="4">
                                                        <img width="160" height="160" align="top" name="placeholder" id="placeholder" alt="Image" src="<?php echo $image_url; ?>" style="alignment-adjust: central">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Middle Name :</th>
                                                    <td><?php echo $row_rspros['mname'] ?></td>
                                                    <th>E-Mail :</th>
                                                    <td><?php echo $row_rspros['email'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Phone :</th>
                                                    <td><?php echo $row_rspros['phone'] ?></td>
                                                    <th>State of Origin :</th>
                                                    <td><?php echo $row_rspros['stname'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Address :</th>
                                                    <td><?php echo $row_rspros['address'] ?></td>
                                                    <th>Local Govt :</th>
                                                    <td><?php echo $row_rspros['lganame'] ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <br/>
                                    <?php if ($row_rspros['admid'] == 2) : ?>
                                        <strong>UTME RESULT</strong>
                                        <div class="well">
                                            <table class="table table-hover table-striped table-bordered">

                                                <tbody>                                            
                                                    <tr>
                                                        <td>UTME Reg No. :</td>
                                                        <td><?php echo $row_rspros['jambregid'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>UTME Year. : </td>
                                                        <td><?php echo $row_rspros['jambyear'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th align="center" colspan="2">Subjects / Scores </th>
                                                    </tr>
                                                    <tr>
                                                        <td><?php echo $row_rspros['jamb1'] ?></td>
                                                        <td><?php echo $row_rspros['jambscore1'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?php echo $row_rspros['jamb2'] ?></td>
                                                        <td><?php echo $row_rspros['jambscore2'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?php echo $row_rspros['jamb3'] ?></td>
                                                        <td><?php echo $row_rspros['jambscore3'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?php echo $row_rspros['jamb4'] ?></td>
                                                        <td><?php echo $row_rspros['jambscore4'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Total </th>
                                                        <td style="color:green; font-weight: bold"><?php echo $jambtotal ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <br>
                                    <?php elseif ($row_rspros['admid'] == 1) : ?>
                                        <strong>DIRENT ENTRY</strong>
                                        <div class="well">
                                            <table width="320" class="table table-hover table-striped table-bordered">
                                                <tr>
                                                    <th colspan="2"> DIRECT ENTRY </th>
                                                </tr>
                                                <tr>
                                                    <td>UTME Reg No.</td>
                                                    <td align="left"><?php echo $row_rspros['jambregid'] ?></td>
                                                </tr>
                                                <tr>
                                                    <td>UTME Year.</td>
                                                    <td align="left"><?php echo $row_rspros['jambyear'] ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"style="font-weight: bold" align="center"> Previous Qualification </td>
                                                </tr>
                                                <tr>
                                                    <td>School Name :</td>
                                                    <td align="left"><?php echo $row_rspros['deschname'] ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Graduation year :</td>
                                                    <td align="left"><?php echo $row_rspros['degradyear'] ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Grade : </td>
                                                    <td align="left">
                                                        <?php echo getDeGrade($row_rspros['degrade']); ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div><br/>
                                    <?php endif; ?>
                                    <strong>Programme Choices</strong>
                                    <div class='well'>
                                        <table class='table table-hover table-striped table-bordered'>                                            
                                            <tbody>
                                                <tr>
                                                    <th>
                                                        1st Choice of Programme
                                                    </th> 
                                                    <th>
                                                        <?php echo $row_rspros['prog1'] ?>
                                                    </th>                                                                                                                                       
                                                </tr>
                                                <tr>
                                                    <th>
                                                        2nd Choice of Programme
                                                    </th> 
                                                    <td>
                                                        <?php echo $row_rspros['prog2'] ?>
                                                    </td>                                                                                                                                       
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div> 
                                    <br>
                                    <strong>O'LEVEL</strong>
                                    <div class="well">
                                        <?php if($num_rows_olevel > 0) {?>
                                        <table width="100%" class="table table-hover table-striped table-bordered">
                                            <tbody>
                                                <tr>
                                                    <?php do{ $dt = json_decode($row_olevel_rs['result_plain'], TRUE) ;?> 
                                                    <td width="50%">
                                                        <table width="320" class="table table-hover table-striped table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th colspan="2"><?= $row_olevel_rs['label']?></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <th>Exam</th>
                                                                    <td><?= $dt['result']['exam_name'] ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Candidate Name</th>
                                                                    <td><?= $dt['result']['candidate_name'] ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Exam number</th>
                                                                    <td><?= $dt['result']['exam_number'] ?></td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                    <th>Exam Type</th>
                                                                    <td><?= $dt['result']['exam_type'] ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Exam Year</th>
                                                                    <td><?= $dt['result']['exam_year'] ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Exam Center</th>
                                                                    <td><?= $dt['result']['exam_center'] ?></td>
                                                                </tr>
                                                            <thead>
                                                                <tr>
                                                                    <th colspan="2">Subject / Grade</th>
                                                                </tr>
                                                            </thead>
                                                            <?php foreach ($dt['result']['result'] as $res) {?>
                                                            <tr>
                                                                <td><?= $res['subject'] ?></td>
                                                                <td><?= $res['score'] ?></td>
                                                            </tr>
                                                            <?php } ?>
                                                        </tbody>
                                                        </table>                    
                                                    </td>
                                                    <?php } while($row_olevel_rs = mysql_fetch_assoc($olevel_verRS))?>
                                                </tr>
                                            </tbody>
                                        
                                        </table> 
                                        <?php }else{?>
                                        <div class="alert alert-danger"><p style="font-size:20px; text-align:center">You are required to verify your O&apos;Level result Click the button below to verify your O&apos;Level result <br/><br/><a href="../olevel_service/index.php" class="btn btn-primary">Verify my O&apos;Level Result</a></p></div>
                                        <?php }?>
                                    </div>
                                    <br>
                                    <strong>Previouse School / Qualification </strong>
                                    <div class="well">
                                        <div class="row-fluid">
                                            <div class="span12">
                                                <table class="table table-condensed table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>School Name</th>
                                                            <th>School Address</th>
                                                            <th>From</th>
                                                            <th>To</th>
                                                            <th>Certificate Obtains</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr ng-repeat="prv_sch in prev_school_submitted track by $index">
                                                            <td>{{$index +1}}</td>
                                                            <td>{{prv_sch.school_name}}</td>
                                                            <td>{{prv_sch.school_address}}</td>
                                                            <td>{{prv_sch.start_date}}</td>
                                                            <td>{{prv_sch.end_date}}</td>
                                                            <td>{{prv_sch.cert_obtain}}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <strong>Sponsor's / Next Of Kin Info. </strong>
                                    <div class="well">
                                        <table class="table table-striped table-bordered table-hover">                                          
                                            <thead>
                                                <tr>
                                                    <th width="50%" colspan="2">Sponsor Info.</th>
                                                    <th width="50%" colspan="2">Next of Kin</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th width="20%">Sponsor's Name.</th>
                                                    <td width="30%"><?= $prospective_row['sponsorname'] ?></td>
                                                    <th width="20%">Next OF Kin  Name.</th>
                                                    <td width="30%"><?= $prospective_row['nxt_kin_fullname'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th width="20%">Sponsor's Phone.</th>
                                                    <td width="30%"><?= $prospective_row['sponsorphn'] ?></td>
                                                    <th width="20%">Next OF Kin Phone.</th>
                                                    <td width="30%"><?= $prospective_row['nxt_kin_phone'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th width="20%">Sponsor's email.</th>
                                                    <td width="30%"><?= $prospective_row['sponsoremail'] ?></td>
                                                    <th width="20%">Next OF Kin Email.</th>
                                                    <td width="30%"><?= $prospective_row['nxt_kin_email'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th width="20%">Sponsor's Address.</th>
                                                    <td width="30%"><?= $prospective_row['sponsoradrs'] ?></td>
                                                    <th width="20%">Next OF Kin Address.</th>
                                                    <td width="30%"><?= $prospective_row['nxt_kin_address'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th width="20%">Sponsor's Relationship.</th>
                                                    <td width="30%"><?= $prospective_row['sponsorrelation'] ?></td>
                                                    <th width="20%">Next OF Kin Relationship.</th>
                                                    <td width="30%"><?= $prospective_row['nxt_kin_relation'] ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="form-actions">
                                        <div>
                                            
                                            <a href="printform.php" target="tabs" class="btn btn-primary">Print Application From</a>
                                        </div>
                                    </div>
                                    <?php } else{ ?>
                                            <p><br/>Your Account has NOT been Activated. <br/><br/>Please check your e-Mail for your account activation link<br/> to continue wih your Application process.</p>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>

<?php include INCPATH . "/footer.php" ?>
            <script>    
                var app = angular.module('tams_admission_appform', []);
                app.controller('MainCtrl', function($scope, $http){

                    $scope.prev_school = [];

                    $scope.prev_school_submitted = <?= json_encode($prevSchool_array) ?>;
                  

                    $scope.score4 = 0;
                    console.log($scope.form_stage);
                });
            </script>
    </body>

</html>