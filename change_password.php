<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('path.php');



/* -----------------------------------------------*
 * 
 * Logic of the College/index.php Page 
 *
 * *------------------------------------------------
 */

$auth_users = "1, 2, 3, 4, 5, 6, 7, 10, 20, 21, 22, 23, 24, 26, 25, 27, 28, 29, 30,31";
check_auth($auth_users, $site_root);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}



$affected_row = 0;
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
    
    if(getAccess() >= 20){
       
      
        $QueryPSW1 = sprintf("SELECT password "
                            . "FROM ictstaff "
                            . "WHERE stfid = %s "
                            . "LIMIT 1",  
                            GetSQLValueString(getSessionValue('uid'), "text") );
        $psw = mysql_query($QueryPSW1, $tams) or die(mysql_error());
        $row_psw = mysql_fetch_assoc($psw);
            
        if($row_psw['password'] === md5(strtolower($_POST['prev_password']))){

            if($_POST['new_password'] === $_POST['re_password']){

                $QueryPSW2 = sprintf("UPDATE ictstaff "
                                    . "SET password = %s "
                                    . "WHERE stfid = '%s'", 
                                    GetSQLValueString(md5(strtolower($_POST['new_password'])), 'text'),
                                    getSessionValue('uid'));
                $psw = mysql_query($QueryPSW2, $tams) or die(mysql_error());
                $affected_row = mysql_affected_rows();

                ($affected_row > 0) ? $notification->set_notification('Operation Successful', 'success') :  $notification->set_notification('Operation Not Successful', 'error');
            }
            else{

                $notification->set_notification('Password missmatch', 'error');
            }
        }
        else{

            $notification->set_notification('Incorrect previous password ', 'error');
        }
    }
    else{
        
        switch (getAccess()) {
            case 10:
                $QueryPSW1 = sprintf("SELECT password "
                                    . "FROM student "
                                    . "WHERE stdid = %s "
                                    . "LIMIT 1", getSessionValue('uid'));
                $psw = mysql_query($QueryPSW1, $tams) or die(mysql_error());
                $row_psw = mysql_fetch_assoc($psw);

                if($row_psw['password'] == md5(strtolower($_POST['prev_password']))){

                    if($_POST['new_password'] === $_POST['re_password']){

                        $QueryPSW2 = sprintf("UPDATE student "
                                . "SET password = %s "
                                . "WHERE stdid = '%s'", GetSQLValueString(md5(strtolower($_POST['new_password'])), 'text'), getSessionValue('uid'));
                        $psw = mysql_query($QueryPSW2, $tams) or die(mysql_error());
                        $affected_row = mysql_affected_rows();

                        ($affected_row > 0) ? $notification->set_notification('Operation Successful', 'success') : $notification->set_notification('Operation Not Successful', 'error');
                    }
                    else{

                        $notification->set_notification('Password missmatch', 'error');
                    }   
                }
                else{

                    $notification->set_notification('Incorrect previous password ', 'error');
                }

                break;
            case 11:

                'prospective';
                break;

            default:

                $QueryPSW1 = sprintf("SELECT password "
                                    . "FROM lecturer "
                                    . "WHERE lectid = %s "
                                    . "LIMIT 1",  
                                    GetSQLValueString(getSessionValue('uid'), "text") );
                $psw = mysql_query($QueryPSW1, $tams) or die(mysql_error());
                $row_psw = mysql_fetch_assoc($psw);

                if($row_psw['password'] === md5(strtolower($_POST['prev_password']))){

                    if($_POST['new_password'] === $_POST['re_password']){

                        $QueryPSW2 = sprintf("UPDATE lecturer "
                                        . "SET password = %s "
                                        . "WHERE lectid = '%s'", 
                                        GetSQLValueString(md5($_POST['new_password']), 'text'),
                                        getSessionValue('uid'));
                        $psw = mysql_query($QueryPSW2, $tams) or die(mysql_error());
                        $affected_row = mysql_affected_rows();

                        ($affected_row > 0) ? $notification->set_notification('Operation Successful', 'success') :  $notification->set_notification('Operation Not Successful', 'error');
                    }
                    else{

                        $notification->set_notification('Password missmatch', 'error');
                    }  


                }else{

                    $notification->set_notification('Incorrect previous password ', 'error');
                }


                break;
        }  
    }
    
}


$page_title = "Tasued";
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
                                    <h3><i class="icon-key"></i>
                                        Change Password
                                    </h3>
                                    <ul class="tabs">
                                    </ul>
                                </div>
                                <div class="box-content">
                                    <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data">
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Previous Password</label>
                                            <div class="controls">
                                                <div class="input-xlarge">
                                                    <input type="password" name="prev_password" class="input-block-level" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">New Password</label>
                                            <div class="controls">
                                                <div class="input-xlarge">
                                                    <input type="password"  name="new_password" class="complexify-me input-block-level" required="">
                                                    <span class="help-block">
                                                        <div class="progress progress-info">
                                                            <div style="width: 0%;" class="bar bar-red">0%</div>
                                                        </div>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Retype New Password</label>
                                            <div class="controls">
                                                <div class="input-xlarge">
                                                    <input type="password" name="re_password" class="input-block-level" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="MM_update" value="form1" />
                                        <div class="form-actions">
                                            <input type="submit" value="Change Password" class="btn btn-primary" >
                                            <button class="btn" type="button">Cancel</button>
                                        </div>
                                    </form>                          
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


