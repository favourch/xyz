<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$not_msg = null;
$msg_type = 'error';

//set the new admission session name, session id and batch
$sesid = isset($_SESSION['admid'])? $_SESSION['admid']: '';
$sesname = isset($_SESSION['admname'])? $_SESSION['admname']: '';
$batch = null;
$admid = null;

if(isset($_GET['type']) && intval($_GET['type']) > 0) {
    $batch = $_GET['type'];
}else {
    $not_msg = 'Please go back to the admissions page and select an admission type to proceed! '
            . '<a href="index.php">Go Back</a>';
}

$adm_query = sprintf("SELECT a.admid, displayname, typename, a.status "
        . "FROM admissions a "
        . "JOIN admission_type at ON a.typeid = at.typeid "
        . "JOIN application_batch ab ON a.admid = ab.admid "
        . "WHERE ab.appbatchid = %s", GetSQLValueString($batch, "int"));
$admission = mysql_query($adm_query, $tams);
$row_admission = mysql_fetch_assoc($admission);
$totalRows_admission = mysql_num_rows($admission);

if($totalRows_admission > 0) {
    $admid = $row_admission['admid'];
}

$admname = "{$sesname} {$row_admission['displayname']} Admission -";

$query_regtype = "SELECT * "
        . "FROM registration_type";
$regtype = mysql_query($query_regtype, $tams) or die(mysql_error());
$totalRows_regtype = mysql_num_rows($regtype);

if (is_null($sesid) || is_null($batch)) {
    $not_msg = 'Admission is not currently on for any admission type. You cannot create an account!';
}
//echo $batch; die();
if (isset($_POST["submit"]) && !is_null($sesid) && $row_admission['status'] == 'TRUE' && !is_null($batch)) {

    $year = explode('/', $sesname);
    $valid = true;
    $emails = [];
    $jambnos= [];
    
    $query_chk = sprintf("SELECT * FROM prospective WHERE jambregid = %s OR (email = %s AND admid = %s)", 
                GetSQLValueString($_POST['jambregid'], "text"), 
                GetSQLValueString($_POST['email'], "text"), 
                GetSQLValueString($row_admission['admid'], "text"));
    $result = mysql_query($query_chk, $tams) or die(mysql_error());
    $row_result = mysql_fetch_assoc($result);
    $num_row_chk = mysql_num_rows($result);
        
    for(; $row_result; $row_result = mysql_fetch_assoc($result)) {
        $emails[] = strtolower($row_result['email']);
        $jambnos[] = strtolower($row_result['jambregid']);
    }
    
    foreach($_POST as $key => $value) {
        $value = strtolower($value);
        
        if($key == 'mname')
            continue;
                    
        if($value == '' || $value == NULL) {        
            $valid = false;
            $not_msg = "Some required fields are missing! Please ensure all fields are supplied.";
            break;
        }
        
        if($key == 'email') {
            if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $not_msg = "The Email address supplied is invalid! Please check and correct!";
                $valid = false;
                break;
            }  
            
            if(in_array($value, $emails)) {
                $not_msg = "The email address specified already exist in our system! "
                        . "Please provide a unique one for your account.";
                $valid = false;
                break;
            }
        }
        
        if($key == 'jambregid') {
            if(strlen($value) != 10 || !is_numeric(substr($value, 0, 8)) || !is_string(substr($value, 8))) {
                $not_msg = "The JAMB registration number supplied is invalid! Please check and correct!";
                $valid = false;
                break;
            }    
            
            if(in_array($value, $jambnos)) {
                $not_msg = "The JAMB No. specified already exist in our system! Log in to continue your application.";
                $valid = false;
                break;
            }
        }  
    }
        
    if($valid) {
        
        mysql_query('START TRANSACTION;', $tams);
        $insertSQL = sprintf("INSERT INTO prospective (jambregid, jambyear, fname, mname, sex, lname, email, "
                . "phone, regtypeid, sesid, access, admid, create_date, appbatch, act_token) "
                . "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, UUID())", 
                GetSQLValueString(strtoupper($_POST['jambregid']), "text"), 
                GetSQLValueString($_POST['jambyear'], "text"), 
                GetSQLValueString($_POST['fname'], "text"), 
                GetSQLValueString($_POST['mname'], "text"), 
                GetSQLValueString($_POST['sex'], "text"), 
                GetSQLValueString($_POST['lname'], "text"), 
                GetSQLValueString($_POST['email'], "text"), 
                GetSQLValueString($_POST['phone'], "text"), 
                GetSQLValueString($_POST['regtype'], "text"), 
                GetSQLValueString($sesid, "int"), 
                GetSQLValueString(11, "int"), 
                GetSQLValueString($admid, "int"), 
                GetSQLValueString(date('Y-m-d H:i:s'), "date"), 
                GetSQLValueString($batch, "text"));
        $Result1 = mysql_query($insertSQL, $tams);

        $Result2 = true;

        if (mysql_errno() == 0) {
            mysql_query('COMMIT;', $tams);
            
            
            
            $sql = sprintf("SELECT act_token FROM prospective WHERE jambregid = %s ", GetSQLValueString($_POST['jambregid'], 'text'));
            $rs = mysql_query($sql, $tams) or die(mysql_error());
            $row_rs = mysql_fetch_assoc($rs);
            
            $validate_url = $portal_url."/admission/activate_account.php?pstid=".strtoupper($_POST['jambregid'])."&token=".$row_rs['act_token'];
            $mail_to = $_POST['email'];
            $subject = $sesname."  Application for Admission on TAMS Portal.";
            $sender = $school_short_name;
            $message = "Congratulations...<br/><br/>
                    Dear {$_POST['lname']} {$_POST['fname']}  {$_POST['mname']},<br/>   
                    Your Application Account has been created successfully. Your login detail is shown below:<br/>
                    <br/> Username : " . strtoupper($_POST['jambregid']) . "<br/> Password : {$_POST['lname']}<br/> <br/>"
                    . "Click on the link below to activate your account <br/><br/> <a href='{$validate_url}'>Activate My Account</a>";
            $body = $message;
            
            
            $bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'> %s Application for Admission (Account Creation )</h3><p>%s</p>", $sesname , $message);
    
    
            $mailstatus = sendHtmlEmail($mail_to, $subject, $bd, 'no-reply@tasued.edu.ng', $university);

            $not_msg = "Congratulations, your account has been created! <br/>
                        An activation mail has been sent to the email address that you provided.<br/> 
                        Please check your e-mail to activate your account and proceed with your application process";
            $msg_type = 'danger';

        }else {
            $not_msg = "There was a problem creating your account. Please try again! "
                    . "If the problem persists,  <a href='".$school_helpdesk."'>click here</a> to contact the HelpDesk!";
        }
    }
}else {
    
}
?>
<!doctype html>
<html>
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
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
                                        <?php echo $admname;?> Create Account 
                                    </h3>
                                </div>
                                <div class="box-content ">
                                    <div class="alert alert-success"><b>Those Who May Apply</b>: UTME/DE Candidates who chose TASUED as First Choice/Second Choice and those seeking Change of Institution through JAMB </div>
                                    <form name="form1" method="post" class="form-horizontal form-validate" id="accountform">
                                        <?php if(isset($not_msg)) :?>
                                        <div class="alert alert-<?php echo $msg_type?>">
                                            <?php echo $not_msg?>
                                        </div>
                                        <?php endif;?>
                                        <div class="span6">  

                                            <div class="control-group">
                                                <label for="jambregid" class="control-label">JAMB Reg. No</label>
                                                <div class="controls">
                                                    <input type="text" name="jambregid" id="jambregid" class="input-large" min-length=""/>
                                                </div>
                                            </div>

                                            <div class="control-group">
                                                <label for="lname" class="control-label">Surname</label>
                                                <div class="controls">
                                                    <input type="text" name="lname" id="lname" class="input-large" 
                                                           data-rule-required="true">
                                                </div>
                                            </div>

                                            <div class="control-group">
                                                <label for="fname" class="control-label">First Name</label>
                                                <div class="controls">
                                                    <input type="text" name="fname" id="fname" class="input-large" 
                                                           data-rule-required="true">
                                                </div>
                                            </div>

                                            <div class="control-group">
                                                <label for="mname" class="control-label">Middle Name</label>
                                                <div class="controls">
                                                    <input type="text" name="mname" id="mname" class="input-large">
                                                </div>
                                            </div>

                                            <div class="control-group">
                                                <label for="phone" class="control-label">Phone No</label>
                                                <div class="controls">
                                                    <input type="text" name="phone" id="phone" class="input-large" 
                                                           data-rule-required="true" data-rule-number="true">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="span6">

                                            <div class="control-group">
                                                <label for="email" class="control-label">E-mail</label>
                                                <div class="controls">
                                                    <input type="text" name="email" id="email" class="input-large" 
                                                           data-rule-required="true" data-rule-email="true">
                                                </div>
                                            </div>        

                                            <div class="control-group">
                                                <label for="sex" class="control-label">Sex</label>
                                                <div class="controls">
                                                   <select name="sex" id="sex" data-rule-required="true">
                                                        <option value="male">Male</option>
                                                        <option value="female">Female</option>
                                                    </select>
                                                </div>
                                            </div> 
                                            
                                            <div class="control-group">
                                                <label for="jambyear" class="control-label">Exam Year</label>
                                                <div class="controls">
                                                    <select name="jambyear" id="jambyear" data-rule-required="true">
                                                        <?php 
                                                            $year = intval(date('Y'));
                                                            for($idx = 0; $idx < 5; $idx++, $year-- ) :?>
                                                        <option value="<?php echo $year?>">
                                                            <?php echo $year?>
                                                        </option>
                                                        <?php endfor;?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="control-group">
                                                <label for="regtype" class="control-label">Application Type</label>
                                                <div class="controls">
                                                    <select name="regtype" data-rule-required="true">
                                                        <?php
                                                            if ($totalRows_regtype > 0) :
                                                                for (;$row_regtype = mysql_fetch_assoc($regtype);) :
                                                        ?>
                                                            <option value="<?php echo $row_regtype['regtypeid']?>">
                                                                <?php echo $row_regtype['displayname']?>
                                                            </option>
                                                        <?php endfor;
                                                             endif;?>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="control-group">
                                                <label for="admid" class="control-label">Admission Type</label>
                                                <div class="controls">
                                                    <?php echo $row_admission['typename']?>
                                                    <input type="hidden" name="admid" value="<?php echo $admid?>"/>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clearfix"> 
                                        </div>
                                        <?php if(!is_null($sesid) && !is_null($admid)) :?>
                                        <div class="row-fluid">
                                            <div style="float:right; width: 300px; margin: 20px; text-align: centre;">
                                                <input class="btn btn-primary" type="submit" name="submit" value="Create Account"/>  <br/> <br/>
                                            </div>
                                            <div style="float:left; width: 400px; margin: 10px;">
                                                <img src="../img/account_error.png" height="60" width="60">
                                                Already Created Account? Click <a href="help.php">here</a> to proceed
                                            </div>
                                        </div>
                                        <?php endif;?>
                                        
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
        </div> 
    </body>
</html>