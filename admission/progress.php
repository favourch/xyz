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
$default_stage = 'instruction';

if(isset($_GET['stage'])){
    $default_stage = $_GET['stage'];
}


$query_screen = sprintf("SELECT *
                        FROM screening
                        WHERE jambregid=%s",
                        GetSQLValueString($jambregid, "text"));
$screen = mysql_query($query_screen, $tams) or die(mysql_error());
$row_screen = mysql_fetch_assoc($screen);
$totalRows_screen = mysql_num_rows($screen);


//Get Prospective infomation
$prospectiveSQL = sprintf("SELECT p.*, s.sesid, s.sesname, st.stname, formsubmit, a.admid, regtype, pr.progname AS prog1, pr3.progname AS program_name,a.advert, "
                        . "pr2.progname AS prog2, at.typename, s1.subjname as jamb1, s2.subjname as jamb2, s3.subjname as jamb3, s4.subjname as jamb4, lga.lganame "
                        . "FROM prospective p "
                        . "LEFT JOIN admissions a ON p.admid = a.admid "
                        . "LEFT JOIN admission_type at ON a.typeid = at.typeid "
                        . "LEFT JOIN session s ON a.sesid = s.sesid "
                        . "LEFT JOIN programme pr ON p.progid1 = pr.progid "
                        . "LEFT JOIN programme pr2 ON p.progid2 = pr2.progid "
                        . "LEFT JOIN programme pr3 ON p.progoffered = pr3.progid "
                        . "LEFT JOIN subject s1 ON p.jambsubj1 = s1.subjid "
                        . "LEFT JOIN subject s2 ON p.jambsubj2 = s2.subjid "
                        . "LEFT JOIN subject s3 ON p.jambsubj3 = s3.subjid "
                        . "LEFT JOIN subject s4 ON p.jambsubj4 = s4.subjid "
                        . "LEFT JOIN state st ON st.stid = p.stid "
                        . "LEFT JOIN state_lga lga ON lga.lgaid = p.lga "
                        . "WHERE p.jambregid = %s", GetSQLValueString($jambregid, "text"));
$prospectiveRS = mysql_query($prospectiveSQL, $tams);
$prospective_row = mysql_fetch_assoc($prospectiveRS);

$form_stageSQL = sprintf("SELECT * FROM applicant_appform_stage WHERE pstdid = %s ",GetSQLValueString($jambregid, "text"));
$form_stageRS = mysql_query($form_stageSQL, $tams);
$form_stage_num = mysql_num_rows($form_stageRS);

$admitted = ($prospective_row['adminstatus'] == 'Yes')? true: false;

$accepted = ($prospective_row['acceptance'] == 'Yes')? true: false;

$image_url = get_pics($jambregid, UPLOAD_DIR.explode('/', $_SESSION['admname'])[0]);

if($prospective_row['formpayment'] == 'Yes' && $prospective_row['formsubmit'] == 'Yes'){
    $default_stage = 'chk_statu';
}


if($prospective_row['acceptance'] == 'Yes'){
    $default_stage = 'reg_process';
}

$_SESSION['payment']['sesid'] = $sesid = $_SESSION['admid'];
$sesname = $_SESSION['admname'];

 $query_info = sprintf("SELECT * "
        . "FROM payschedule "
        . "WHERE level = '0' "
        . "AND sesid = %s "
        . "AND admid = %s "
        . "AND regtype = %s "
        . "AND payhead = %s", 
        GetSQLValueString($sesid, 'int'), 
        GetSQLValueString($_SESSION['admtype'], 'int'), 
        GetSQLValueString($_SESSION['regmode'], 'text'), 
        GetSQLValueString('app', 'text')); 
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$_SESSION['payment']['scheduleid'] = $row_info['scheduleid'];
$_SESSION['payment']['due'] = $amount = $row_info['amount'];
$_SESSION['payment']['revhead'] = $row_info['revhead'];

$_SESSION['payment']['jambregid'] = $jambregid = getSessionValue('uid');

$owing = ['status' => true, 'amt' => 0.00];

$_SESSION['payment']['name'] = $_SESSION['lname'] . ' ' . $_SESSION['fname'] . ' ' . $_SESSION['mname'];

$paytype = 'app';
$pay_status = checkPaymentPros($sesid, $jambregid, $amount, $paytype);
if (!$pay_status['status']) {
    $owing['status'] = !$pay_status['status'];
    $_SESSION['payment']['amt'] = $owing['amt'] = $pay_status['owing'];
    $owing['desc'] = $pay_status['desc'];

    $_SESSION['payment']['percent'] = $owing['desc'] == 'Incomplete' ? 0 : 100;
} else {
    $owing['status'] = !$pay_status['status'];
}

if(isset($_POST['send_link'])){
    
    $sql = sprintf("SELECT * FROM prospective WHERE jambregid = %s ", GetSQLValueString(getSessionValue('uid'), 'text'));
            $rs = mysql_query($sql, $tams) or die(mysql_error());
            $row_rs = mysql_fetch_assoc($rs);
            
            $validate_url = $portal_url."/admission/activate_account.php?pstid=".strtoupper($row_rs['jambregid'])."&token=".$row_rs['act_token'];
            $mail_to = $row_rs['email'];
            $subject = $sesname."  Application for Admission on TAMS Portal.";
            $sender = $school_short_name;
            $message = "Congratulations...<br/><br/>
                    Dear {$row_rs['lname']} {$row_rs['fname']}  {$row_rs['mname']},<br/>   
                    Your Application Account has been created successfully. Your login detail is shown below:<br/>
                    <br/> Username : " . strtoupper($row_rs['jambregid']) . "<br/> Password : {$row_rs['lname']}<br/> <br/>"
                    . "Click on the link below to activate your account <br/><br/> <a href='{$validate_url}'>Activate My Account</a>";
            $body = $message;
            
            
            $bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'> %s Application for Admission (Account Creation )</h3><p>%s</p>", $sesname , $message);
    
    
            $mailstatus = sendHtmlEmail($mail_to, $subject, $bd, 'no-reply@tasued.edu.ng', $university);

            $not_msg = "A new Activation Link has been sent to your E-mail Address - ".$row_rs['email'];
            $msg_type = 'success';
            
            $notification->set_notification($not_msg, $msg_type);
}

if(isset($_POST['change_email'])){
            $sql2 = sprintf("UPDATE prospective SET email = %s  WHERE jambregid = %s ", GetSQLValueString($_POST['email'], 'text'), GetSQLValueString(getSessionValue('uid'), 'text'));
            mysql_query($sql2, $tams) or die(mysql_error());
            
    $sql = sprintf("SELECT * FROM prospective WHERE jambregid = %s ", GetSQLValueString(getSessionValue('uid'), 'text'));
            $rs = mysql_query($sql, $tams) or die(mysql_error());
            $row_rs = mysql_fetch_assoc($rs);
            
            $validate_url = $portal_url."/admission/activate_account.php?pstid=".strtoupper($row_rs['jambregid'])."&token=".$row_rs['act_token'];
            $mail_to = $row_rs['email'];
            $subject = $sesname."  Application for Admission on TAMS Portal.";
            $sender = $school_short_name;
            $message = "Congratulations...<br/><br/>
                    Dear {$row_rs['lname']} {$row_rs['fname']}  {$row_rs['mname']},<br/>   
                    Your Application Account has been created successfully. Your login detail is shown below:<br/>
                    <br/> Username : " . strtoupper($row_rs['jambregid']) . "<br/> Password : {$row_rs['lname']}<br/> <br/>"
                    . "Click on the link below to activate your account <br/><br/> <a href='{$validate_url}'>Activate My Account</a>";
            $body = $message;
            
            
            $bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'> %s Application for Admission (Account Creation )</h3><p>%s</p>", $sesname , $message);
    
    
            $mailstatus = sendHtmlEmail($mail_to, $subject, $bd, 'no-reply@tasued.edu.ng', $university);

            $not_msg = "A new Activation Link has been sent to your E-mail Address - ".$row_rs['email'];
            $msg_type = 'success';
            
            $notification->set_notification($not_msg, $msg_type);
}

$query_reghistory = sprintf('SELECT can_no, can_name, ordid, status, reference, amt, date_time, pcount '
                        . "FROM registration_transactions "
                        . "WHERE can_no = %s "
                        . "AND status = 'APPROVED' ", 
                        GetSQLValueString(getSessionValue('uid'), "text"));
$reghistory = mysql_query($query_reghistory, $tams) or die(mysql_error());
$row_reghistory = mysql_fetch_assoc($reghistory);
$totalRows_reghistory = mysql_num_rows($reghistory);

$regpay = false;

if($totalRows_reghistory > 0){
    $regpay = true;
}

?>
<!doctype html>
<html ng-app="tams">
<?php include INCPATH . "/header.php" ?>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="pageController">
    <?php include INCPATH . "/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
        <?php include INCPATH . "/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                <?php include INCPATH . "/page_header.php" ?>                                                                                                               
                    <div class="row-fluid">
                        <?php if($prospective_row['activate'] == 'true') {?>
                        <div class="span2">
                            <div class="box box-color ">
                                <div class="box-title">
                                    <h3><i class="icon-list"></i> Guide </h3>
                                </div>                                
                                <div class="box-content nopadding">
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <button ng-click="setFlag('instruction')" class="btn btn-block btn-success" style="text-align: left"><i class="icon icon-hand-right"></i> &nbsp; Instruction</button>
                                            <button ng-click="setFlag('app_fee_pay')" class="btn btn-block <?= ($prospective_row['formpayment'] == 'Yes') ? 'btn-success' : 'btn-warning' ;?> " style="text-align: left"  ><i class="icon icon-hand-right"></i> &nbsp; Application Fee</button>
                                            <button ng-click="setFlag('app_form')" class="btn btn-block <?= ($prospective_row['formsubmit'] == 'Yes') ? 'btn-success' : 'btn-warning' ;?>" style="text-align: left" ><i class="icon icon-hand-right"></i> &nbsp; Application Form</button>
                                            <button ng-click="setFlag('veri_olv')" class="btn btn-block btn-success" style="text-align: left"><i class="icon icon-hand-right"></i> &nbsp;O&apos;level Result Verification</button>
                                            <button ng-click="setFlag('chk_statu')" class="btn btn-block" style="text-align: left"><i class="icon icon-hand-right"></i> &nbsp; Admission Status</button>
                                            <?php if($admitted){?>
                                            <button ng-click="setFlag('reg_process')" class="btn btn-block" style="text-align: left"><i class="icon icon-hand-right"></i> &nbsp; Registration Process</button>
                                            <?php if(1 > 2){?>
                                            <a href="fee_payment/"  class="btn btn-block" style="text-align: left"><i class="icon icon-hand-right"></i> &nbsp; Pay School Fees</a>
                                            <button ng-click="setFlag('reg_process')" class="btn btn-block" style="text-align: left"><i class="icon icon-hand-right"></i> &nbsp; Final Clearance </button>
                                            <button ng-click="setFlag('reg_process')" class="btn btn-block" style="text-align: left"><i class="icon icon-hand-right"></i> &nbsp; Course Registration</button>
                                            <?php } ?>
                                            <?php }?>
                                        </div>
                                    </div>
                                    <p>&nbsp;</p>
                                    <p>&nbsp;</p>
                                </div>
                            </div> 
                        </div>
                        <div class="span10">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-list"></i><?= $prospective_row['sesname']?>  <?= $prospective_row['typename']?>  Application </h3>
                                </div>                                
                                <div class="box-content" ng-cloak="">
                                    <div class="row-fluid">
                                        <!--<div class="span12"> -->
                                            <div ng-if="flag == 'instruction'" >
                                                <h5 style="text-align:center"><?= $prospective_row['sesname']?> POST-UTME SCREENING EXERCISE FOR 100 LEVEL AND DIRECT ENTRY (200 LEVEL) CANDIDATES</h5>
                                                <div class="">
                                                    <?= $prospective_row['advert']?>
                                                    <button ng-click="setFlag('app_fee_pay')" class="btn btn-small btn-primary" > Continue</button>
                                                </div>
                                            </div>
                                       <!-- </div> -->
                                    </div>
                                    
                                    <div ng-if="flag == 'app_fee_pay'">
                                        <h4>Application Payment </h4>
                                        <div class="row-fluid">
                                            <?php if ($totalRows_info > 0) {?>
                                                <?php if ($owing['status']) { ?>

                                                    <table class="table table-striped">
                                                        <caption><strong><?php echo $owing['desc'] ?> Payment(s)</strong></caption>
                                                        <thead>
                                                        <th>Session</th>
                                                        <th>Amount</th>
                                                        <th>Description</th>
                                                        <th></th>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>
                                                                    <?php echo $sesname ?>
                                                                </td>
                                                                <td>
                                                                    N<?php echo number_format($owing['amt']) ?>
                                                                </td>
                                                                <td><?php echo $owing['desc'] ?> Application Fee</td>
                                                                <td>
                                                                    <button class="btn btn-primary" onclick="location.href = 'admission_payment/paymentinfo.php'">Pay Now</button>
                                                                </td>                            
                                                            </tr>
                                                            <tr>
                                                                <td colspan="4" style="text-align: center"> 

                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>

                                                <?php } else { ?>
                                                
                                                    <div class="row-fluid">
                                                        <div class="span4">
                                                            <img src="img/payments.png"  height="400px" width="400px">
                                                        </div>
                                                        <div class="span8">
                                                            <p>
                                                                <br/><br/>
                                                                You do not owe <b> <?php echo $sesname ?> Application Fee payment</b>!<br/><br/>
                                                                check the <a href="payhistory.php" target="tabs">Payment History</a> for your record of payments.<br/><br/>
                                                                
                                                                <?php if($prospective_row['formsubmit'] == 'Yes'){ ?>
                                                                   <button type="button" ng-click="setFlag('chk_statu')" class="btn btn-primary">Check Admission Status</button>
                                                                <?php } else {?>
                                                                    <a href="form.php" class="btn btn-primary">Fill Application form</a>
                                                                <?php }?>
                                                                
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <br/>
                                                <?php } ?>
                                            </div>
                                            <?php }else {?>
                                                <div class="row-fluid">
                                                    <div class="span4">
                                                        <img src="img/payments.png"  height="400px" width="400px">
                                                    </div>
                                                    <div class="span8">
                                                        <p>
                                                            <br/><br/>
                                                            <b> <?php echo $sesname ?> Application Fee</b> has not been determined!<br/><br/>
                                                            <a href="<?= $school_helpdesk; ?>">Click here</a> to contact the HelpDesk for further assistance.
                                                        </p>
                                                    </div>
                                                </div>
                                                <br/>
                                            <?php }?>
                                    </div>
                                    <div ng-if="flag == 'app_form'">
                                        <?php if($form_stage_num == 0 && $prospective_row['formsubmit'] != 'Yes') {?>
                                            <div class="row-fluid">
                                                <div class="span4">
                                                    <img src="img/fill_form.jpg">
                                                </div>
                                                <div class="span8">
                                                    <h3>Start Application</h3>
                                                    You are about to fill the <?= $sesname?> <?= $prospective_row['typename']?> Application Form.  <br/>
                                                    You can Save and Continue with the Application at any stage of the Application Process <br/>
                                                    You are required to print a copy of the Application Form, for record purpose, after submission <br/><br/><br/>
                                                    <a href='form.php' class="btn btn-primary"> Start Application</a>
                                                </div>
                                            </div>
                                        <?php }else if($form_stage_num > 0 && $prospective_row['formsubmit'] != 'Yes'){?>
                                            
                                            <div class="row-fluid">
                                                <div class="span4">
                                                    <img src="img/fill_form.jpg">
                                                </div>
                                                <div class="span8">
                                                    <h3>Incomplete Application Form !</h3>
                                                    You are required to complete the <?= $sesname?> <?= $prospective_row['typename']?> Application Form to be considered for Admission.<br/>
                                                    Print a copy of the Application Form, for record purpose, after submission<br/><br/><br/>
                                                    <a href='form.php' class="btn btn-primary"> Continue Application </a>
                                                </div>
                                            </div>
                                        <?php }else if($form_stage_num > 0 && $prospective_row['formsubmit'] == 'Yes'){?>
                                            <div class="row-fluid">
                                                <div class="span4">
                                                    <img src="img/fill_form.jpg">
                                                </div>
                                                <div class="span8">
                                                    <h3>Completed Application Form !</h3>
                                                    You have submitted the <?= $sesname?> <?= $prospective_row['typename']?> Application Form for Admission consideration.<br/>
                                                    View and Print a copy of the Application Form, for record purpose.<br/><br/><br/>
                                                    <a href='form.php' class="btn btn-primary"> View Application</a>
                                                </div>
                                            </div>
                                        <?php }?>
                                    </div>
                                
                                    <div ng-if="flag == 'veri_olv'">
                                        <div class="row-fluid">
                                            <div class="span4">
                                                <img src="img/verification.png"  style="height:200px; width:200px;">
                                            </div>
                                            <div class="span8">
                                                <h4>O'Level Result Verification System</h4>
                                                <div>
                                                    <p>
                                                        It is MANDATORY for all Applicants to submit their O'Level Results for Verification through our Online Result Verification System (ORVS) <br />
                                                        You are required to obtain the O'Level Result Checker scratch card(s) from the appropriate Examination body to use the ORVS.<br />
                                                        Meanwhile, you will be charged the rate of N2,500 for each result to be verified. You will also be notified once the Admissions Office is satified
                                                        that your O'Level results meet the minimum requirement for Admission. 
                                                    </p><br/>
                                                    <center><a href='../olevel_service/index.php' class="btn btn-primary">O'Level Result Verification  </a></center>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div ng-if="flag == 'chk_statu'">
                                        <h4>Admission Status </h4>
                                        <div class="row-fluid">
                                            <div class="span12">
                                                <table class="table" class="table table-condensed ">
                                                    <tr>
                                                        <td rowspan="6" width="20%">
                                                            <div class="fileupload-new thumbnail" style="width: 200px; height: 150px;"><img style="width: 200px; height: 150px;" src="<?= $image_url ?>"></div>
                                                        </td>
                                                        <th width="30%">Full Name</th>
                                                        <td width="50%"><?= $prospective_row['fname']. " ". $prospective_row['lname']. " ".$prospective_row['mname']?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>U.T.M.E ID</th>
                                                        <td><?= $prospective_row['jambregid']?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Screening Score</th>
                                                        <td><?= $prospective_row['score']?></td>
                                                    </tr>
                                                    <?php if($prospective_row['score'] != NULL){ ?>
                                                    <tr>
                                                        <th>Aggregate Score</th>
                                                        <td>
                                                            <?= ((($prospective_row['jambscore1']+$prospective_row['jambscore2']+$prospective_row['jambscore3']+$prospective_row['jambscore4'])*0.15) + ($prospective_row['score']*0.8)) ?>
                                                        </td>
                                                    </tr>
                                                    <?php } ?>
                                                    <tr>
                                                        <th>Status</th>
                                                        <td>
                                                            <?php if($prospective_row['adminstatus'] == 'Yes') {?>
                                                                <div style="color: green; font-size: 10pt; font-weight: bold">ADMITTED
                                                                    <?php if($prospective_row['adminstatus'] == 'Yes' && $prospective_row['jamb_total'] == NULL) {?> <br /> 
                                                                        (Provisional upon Change of Institution on the JAMB portal) 
                                                                    <?php } ?>
                                                                </div>
                                                            <?php } ?>
                                                            
                                                            <?php if($prospective_row['score'] != 'NULL' && $prospective_row['adminstatus'] == 'Wtd') {?>
                                                                <div style="color: red; font-size: 10pt; font-weight: bold">ADMISSION WITHDRAWN
                                                                </div>
                                                            <?php } ?>
                                                            
                                                            <?php if ($prospective_row['score'] != 'NULL' && $prospective_row['adminstatus'] == 'No') { ?>
                                                                <div style="color: red; font-size: 10pt; font-weight: bold">ADMISSION UNDER CONSIDERATION
                                                                </div>
                                                            
                                                            <?php } ?>
                                                            <?php if ($prospective_row['score'] == 'NULL' && $prospective_row['adminstatus'] == 'No') { ?>
                                                            <div style="color: red; font-size: 10pt; font-weight: bold">NOT YET CONSIDERED</div>
                                                            <?php }?>
                                                        </td>
                                                    </tr>
                                                    
                                                    <?php if($prospective_row['adminstatus'] == 'Yes') {?>
                                                    <tr>
                                                        <th>Programme Offered</th>
                                                        <td><?php echo ($prospective_row['adminstatus'] == 'Yes' && $prospective_row['progoffered'] != NULL )? $prospective_row['program_name'] : "-" ?></td>
                                                    </tr>
                                                    <?php }else{ ?>
                                                    <tr>
                                                        <th>Screening Information</th>
                                                        <td>
                                                          <?php  if ($totalRows_screen > 0) { ?>
                                                            <div class="span9">
                                                                <a href="screenslip.php" class="btn btn-small btn-primary" target="tabs">Print Screenng Slip</a>
                                                            </div>
                                                            <?php } else echo "Check Back Soon"; ?>
                                                        </td>
                                                    </tr>
                                                    <?php }?>
                                                </table>
                                            </div>
                                        </div>
                                        <p>&nbsp;</p>
                                        <div class="row-fluid">
                                            <?php if($admitted && $prospective_row['progoffered'] != NULL ) {?>
                                                <div class="span3">
                                                    <a href="#" class="btn btn-small btn-primary">Print Status Page</a>
                                                </div>
                                                
                                                <?php if(!$accepted){?>
                                                    <div class="span3">
                                                        <a href="acceptance_payment/" target="tabs" class="btn btn-small btn-primary">Pay Acceptance Fee</a>
                                                    </div>
                                                 <?php } ?>
                                                 
                                                <?php if($accepted){?>
                                                    <div class="span3">
                                                        <a href="printaccletter.php" target="tabs" class="btn btn-small btn-primary">Print Acceptance Letter</a>
                                                    </div>
                                                   
                                                    <div class="span3">
                                                        <a href="printadmletter.php" target="tab" class="btn btn-small btn-primary">Print Admission Letter</a>
                                                    </div>
                                                    
                                                    <div class="span3">
                                                        <a href="clearcert.php" target="tabs" class="btn btn-small btn-primary">Clearance Certificate</a>
                                                    </div>
                                                    
                                                <?php }?>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div ng-if="flag == 'reg_process'">
                                        <div class="alert alert-info">
                                            <p>
                                                The Registration process is COMPULSORY for all new students to observe and adhere strictly to it. You will receive SMS/eMail alert 
                                                on fulfilment of the requirements for each stage; this will trigger action for the next stage.
                                            </p>
                                            
                                        </div>
                                        
                                        <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Stages In the Registration Process</th>
                                                        <th>Action / Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>1</td>
                                                        <td>Pay Registration Fee</td>
                                                        <td><a href="../registration_fee/index.php" target="tab" class="btn btn-small btn-primary"><?= ($regpay)? 'Print Receipt': 'Proceed' ?></a></td>
                                                    </tr>
                                                    
                                                    <tr>
                                                        <td>2</td>
                                                        <td>Health Information & Laboratory Tests</td>
                                                        
                                                        <td>
                                                            <?php if ($regpay) {?>
                                                                <?php if ($prospective_row['clinic_form'] == 'no'){?>
                                                                    <a href="../clinic/client/index.php" target="tab" class="btn btn-small btn-primary">Proceed</a>
                                                                <?php } ?> 
                                                                <?php if ($prospective_row['clinic_form'] == 'yes'){?>
                                                                    <a href="../clinic/client/printform.php" target="tab" class="btn btn-small btn-primary">Print Medical Form</a>
                                                                <?php } ?>  
                                                                <?php if ($prospective_row['clinic_clear'] == 'yes'){?>
                                                                    <a href="../clinic/client/med_cert.php" target="tab" class="btn btn-small btn-blue">Print Medical Certificate</a>
                                                                <?php }?>
                                                            <?php } else{ ?>
                                                                <span class="badge badge-warning" title="You have to pay your Registration Fee">Pending</span>
                                                            <?php }?>
                                                        </td>
                                                        
                                                        
                                                    </tr>
                                                    <tr>
                                                        <td>3</td>
                                                        <td>School Fee Payment</td>
                                                        <td>
                                                            <?php if( $regpay){?>
                                                            <div class="alert alert-info">You have been cleared at the departmental level. Proceed to pay school and get your Matriculation Number</div>
                                                            <a href="fee_payment/" class="btn btn-small btn-primary">Pay School Fee</a>
                                                            <?php } else{?>
                                                            <span class="badge badge-warning" title="You have to complete your medical Screening">Pending</span>
                                                            <?php }?>
                                                        </td>
                                                    </tr>
                                                    
                                                    <tr>
                                                        <td>4</td>
                                                        <td>Course Registration</td>
                                                        <td>
                                                            <?php if($pay_status['status'] && $regpay){?>
                                                            <a href="autologin_student.php" class="btn btn-small btn-primary">Proceed</a>
                                                            <?php } else{?>
                                                            <span class="badge badge-warning" title="You have to complete your medical Screening">Pending</span>
                                                            <?php }?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>5</td>
                                                        <td>Green File Submission</td>
                                                        <td>
                                                            <div class="alert alert-info">You are required to submit your green file with copies of all your credentials at the Admission office... Please contact the Admissions Office for details</div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                           
                                    </div>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                        </div>
                        <?php } else {?>
                        <div class="span12">
                            <p>&nbsp;</p>
                            <div class="span4">
                                <img src="img/activation.png">
                            </div>
                            <div class="span6">
                                <p><br/>Your Account has NOT been Activated. <br/><br/>
                                Please check your e-Mail for your account activation link<br/>
                                to continue wih your Application process.</p>
                                <br/>
                                <form action="progress.php" method="post">
                                <button class="btn btn-small btn-primary" type="submit" name="send_link">Re-Send Activation Link</button>
                                &nbsp;  &nbsp;
                                
                                <a href="#change_email" role="button" class="btn btn-small btn-warning" data-toggle="modal">Update Email Address</a>
                                </form>
                            </div>
                        </div>
                        <?php }?>
                    </div>
                </div>
            </div>          
        </div>
        <div id="change_email" class="modal hide fade "  tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">Change E-mail Address</h3>
            </div>
            <form action="progress.php" method="POST" class="form-horizontal form-bordered" >
                <div class="modal-body">
                    <div class="alert alert-info">
                       You can update your E-mail Address to a new one.<br/> A new Activation Link will be sent to the new e-mail Address
                    </div>
                    <div class="control-group">
                        <label for="spn_phone" class="control-label">E-mail Address</label>
                        <div class="controls">
                            <input type="text" name="email" value="<?= $prospective_row['email']?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button type="submit" name="change_email" class="btn btn-primary">Update E-mail </button> 
                </div>
            </form>
        </div>
<?php include INCPATH . "/footer.php" ?>
        <script>
            var app = angular.module('tams', []);
            
            app.controller('pageController', function($scope){
                $scope.flag ='<?= $default_stage?>';
                $scope.setFlag = function(v){
                    $scope.flag = v;
                }
            });
        </script>
    </body>
</html>


