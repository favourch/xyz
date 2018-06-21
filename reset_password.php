<?php
require_once('path.php');

if (!isset($_SESSION)) {
    session_start();
}

//Message to be displayed if no username is entered.
$msg = 'Enter a valid username!';
$updatePassword = false;

$genPswd = mysql_query("SELECT LEFT(UUID(), 8) AS newpswd", $tams) or die(mysql_error()); 
$newPswd  = mysql_fetch_assoc($genPswd)['newpswd'];


//@mail('suleodu.adedayo@gmail.com', "Password Recovery", "I am just testing my mail", "noreply@tasued.edu.ng");


$userSQL = "";
$row_rsUser = "";

if (isset($_POST["MM_insert"]) && $_POST["MM_insert"] == "form1") {

    switch ($_POST['who']) {
        
        case 'student':
            
            //Retrieve user's email and password
            $userSQL = sprintf("SELECT stdid, lname, email, password "
                            . " FROM student "
                            . " WHERE stdid = %s ",
                            GetSQLValueString($_POST['username'], "text"));
            $rsUser = mysql_query($userSQL, $tams) or die(mysql_error());
            $row_rsUser = mysql_fetch_assoc($rsUser);
            $foundUser = mysql_num_rows($rsUser);
            
            
            
            if($foundUser > 0 && !in_array($row_rsUser['email'], ['change@your.mail', 'changeyour@mail.com'])){
                
                $PswdUpdtSQL = sprintf("UPDATE student "
                                    . " SET password = %s "
                                    . " WHERE stdid = %s ",
                                    GetSQLValueString(md5($newPswd), 'text'),
                                    GetSQLValueString($row_rsUser['stdid'], 'text'));
                $updatePassword = mysql_query($PswdUpdtSQL, $tams) or die(mysql_error());
                
                $mail_to = $row_rsUser['email'];
                $subject = "  Password Recovery on TAMS Portal.";
                $sender = $school_short_name;
                $message = "Password Reset Successful.<br/><br/>
                        Dear {$row_rsUser['lname']} {$row_rsUser['fname']}  {$row_rsUser['mname']},<br/>   
                        Your password is reset  Your new login detail is shown below:<br/>
                        <br/> Username : " . $row_rsUser['stdid'] . "<br/> Password : {$newPswd}<br/> <br/>";
                $body = $message;
                $bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'> Password Recorvery System (TAMS)</h3><p>%s</p>",  $message);
                $mailstatus = sendHtmlEmail($mail_to, $subject, $bd, 'no-reply@tasued.edu.ng', $university);
                
                
                //Display appropriate message on success or failure of mail delivery.
                if ($mailstatus) {
                    $msg = "An email with your correct username and password has been sent to your registered email %s";
                    $msg = sprintf($msg, $row_rsUser['email']);
                    $notification->set_notification($msg, 'success');
                } else {
                    $msg = "Could not send email to the following address: %s. Please try again!";
                    $msg = sprintf($msg, $row_rsUser['email']);
                    $notification->set_notification($msg, 'error');
                }
                
                
            }else{
                
                $msg = "The username can not be found your your email address is incorrect please contact the ICT through the online helpdesk";
                $notification->set_notification($msg, 'error');
            }
            break;
            
        case 'staff':
            //Retrieve user's email and password
            $userSQL = sprintf("SELECT lectid, lname, email, password "
                            . "FROM lecturer "
                            . "WHERE lectid = %s ", 
                            GetSQLValueString($_POST['username'], "text"));
            $rsUser = mysql_query($userSQL, $tams) or die(mysql_error());
            $row_rsUser = mysql_fetch_assoc($rsUser);
            $foundUser = mysql_num_rows($rsUser);

            if ($foundUser > 0 && !in_array($row_rsUser['email'], ['change@your.mail', 'changeyour@mail.com'])) {
                
                $PswdUpdtSQL = sprintf("UPDATE lecturer "
                                    . " SET password = %s "
                                    . " WHERE lectid = %s ",
                                    GetSQLValueString(md5($newPswd), 'text'),
                                    GetSQLValueString($row_rsUser['lectid'], 'text'));
                $updatePassword = mysql_query($PswdUpdtSQL, $tams) or die(mysql_error());
                
                
                $mail_to = $row_rsUser['email'];
                $subject = "  Password Recovery on TAMS Portal.";
                $sender = $school_short_name;
                $message = "Password Reset Successful.<br/><br/>
                        Dear {$row_rsUser['lname']} {$row_rsUser['fname']}  {$row_rsUser['mname']},<br/>   
                        Your password is reset  Your new login detail is shown below:<br/>
                        <br/> Username : " . $row_rsUser['lectid'] . "<br/> Password : {$newPswd}<br/> <br/>";
                $body = $message;
                $bd = sprintf("<h3 style='font-weight:normal; margin: 20px 0;'> Password Recorvery System (TAMS)</h3><p>%s</p>",  $message);
                $mailstatus = sendHtmlEmail($mail_to, $subject, $bd, 'no-reply@tasued.edu.ng', $university);


                //Display appropriate message on success or failure of mail delivery.
                if ($mailstatus) {
                    $msg = "An email with your correct username and password has been sent to your registered email %s";
                    $msg = sprintf($msg, $row_rsUser['email']);
                    $notification->set_notification($msg, 'success');
                } else {
                    $msg = "Could not send email to the following address: %s. Please try again!";
                    $msg = sprintf($msg, $row_rsUser['email']);
                    $notification->set_notification($msg, 'error');
                }
                
            }else{
                
                $msg = "The username can not be found your your email address is incorrect please contact the ICT through the online helpdesk";
                $notification->set_notification($msg, 'error');
            }
            
            break;

        default:
            break;
    }
}//End of $_POST

?>
<!doctype html>
<html>
    <?php include INCPATH."/header.php" ?>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
        <?php include INCPATH."/top_nav_bar.php";?>
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
                                        Reset Password
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <div class=" span12 alert alert-info" >
                                            Submit your Login Username and your Password will be forwarded to the 
                                            E-mail Address you Registered during account creation. Please Note that for security reason and confidentiality of your information on this portal you are expected to change you password
                                        </div> 
                                        <form method="post" class="form form-horizontal" action="reset_password.php"> 
                                        <div class="control-group">
                                            <div class="controls">
                                                    <div class="input-append">
                                                            <select name="who" class="input-large" required="">
                                                                <option value="">--User type--</option>
                                                                <option value="student">Returning Student</option>
                                                                <option value="staff">Academic Staff</option>
                                                            </select>
                                                        <input type="text"  name="username" class="input-large" required="" placeholder="Enter your Username">
                                                        <button type="submit" class="btn">Recover Password</button>
                                                    </div>
                                            </div>
                                            <input type="hidden" name="MM_insert" value="form1" />
                                        </div>
                                    </form> 
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH."/footer.php" ?>
    </body>
</html>