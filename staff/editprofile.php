<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php'); /* -----------------------------------------------* *  * Logic of the College/index.php Page  * * *------------------------------------------------ */$auth_users = "1, 2,3,4,5,6,20,21,22,23,24";

check_auth($auth_users, $site_root);
define('MAX_FILE_SIZE', 1024 * 2048);
define('UPLOAD_DIR', IMGPATH . '/user/staff/');

$colname_editprof = "-1";
if (isset($_SESSION['MM_Username'])) {
    $colname_editprof = getSessionValue('uid');
} else {
    header('location: ../index.php');
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
    $query_editprof = sprintf("SELECT * FROM lecturer WHERE lectid = %s", GetSQLValueString($colname_editprof, "text"));
    $editprof = mysql_query($query_editprof, $tams) or die(mysql_error());
    $row_editprof = mysql_fetch_assoc($editprof);
    $totalRows_editprof = mysql_num_rows($editprof);
    $updateSQL = sprintf("UPDATE lecturer " . "SET title = %s, mname = %s, " . "phone = %s, email = %s, addr = %s, sex = %s, " . "profile = %s " . "WHERE lectid = %s", GetSQLValueString($_POST['title'], "text"), GetSQLValueString($_POST['mname'], "text"), GetSQLValueString($_POST['phone'], "text"), GetSQLValueString($_POST['email'], "text"), GetSQLValueString($_POST['addr'], "text"), GetSQLValueString($_POST['sex'], "text"), GetSQLValueString($_POST['profile'], "text"), GetSQLValueString($colname_editprof, "text"));
    $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
    $upload = "";
    if ($Result1 && isset($_FILES['filename']) && $_FILES['filename']['name'] != '') {
        $upload = uploadFile(UPLOAD_DIR, "staff", MAX_FILE_SIZE);
    } if ($Result1) {
        $insertGoTo = ( isset($_GET['success']) ) ? $insertGoTo : $insertGoTo . "?success";
    }
    $msg = 'Operation Successfull';
    $notification->set_notification($msg, 'success');
} else {
    $insertGoTo = ( isset($_GET['error']) ) ? $insertGoTo : $insertGoTo . "?error";
    $msg = 'Operation Not Successfull';
    $notification->set_notification($msg, 'error');
}
$query_editprof = sprintf("SELECT * FROM lecturer WHERE lectid = %s", GetSQLValueString($colname_editprof, "text"));
$editprof = mysql_query($query_editprof, $tams) or die(mysql_error());
$row_editprof = mysql_fetch_assoc($editprof);
$totalRows_editprof = mysql_num_rows($editprof);

$query_dept = "SELECT deptid, deptname FROM department";
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);
$deptname = "";
?>
<!doctype html>
}


<html>
<?php include INCPATH . "/header.php" ?>    

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
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
                                        Edit Profile of <?php echo $row_editprof['lname'] . ", " . substr($row_editprof['fname'], 0, 1) ?> 
                                    </h3>     
                                </div>           
                                <div class="box-content">   
                                    <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data">  
                                        <div class="controls-group">      
                                            <label class="control-label" for="textfield">Image</label>   
                                            <div class="controls">                                        
                                                <div data-provides="fileupload" class="fileupload fileupload-new">
                                                    <div style="width: 200px; height: 150px;" class="fileupload-new thumbnail">    
                                                        <img style="width: 200px; height: 150px;" src="<?= get_pics($colname_editprof, '../img/user/staff') ?>">    
                                                    </div>                   
                                                    <div style="max-width: 200px; max-height: 150px; line-height: 20px;" class="fileupload-preview fileupload-exists thumbnail"></div>                                                <div>                                                 
                                                        <span class="btn btn-file">                       

                                                            <span class="fileupload-new">Select image</span> 
                                                            <span class="fileupload-exists">Change</span>     
                                                            <input type="file" name="filename"></span>     
                                                        <a data-dismiss="fileupload" class="btn fileupload-exists" href="#">Remove</a>       
                                                    </div>
                                                </div>     

                                            </div>            
                                        </div>                                  
                                        <div class="control-group">            
                                            <label class="control-label" for="textfield">Title</label> 
                                            <div class="controls">                                     
                                                <select name="title">                                
                                                    <option value="Prof" <?php
                                                            if (!(strcmp("Prof", htmlentities($row_editprof['title'], ENT_COMPAT, 'utf-8')))) {
                                                                echo "selected=\"selected\"";
                                                            }
                                                            ?>>Prof.</option>                                                    <option value="Dr" <?php
                                                            if (!(strcmp("Dr", htmlentities($row_editprof['title'], ENT_COMPAT, 'utf-8')))) {
                                                                echo "selected=\"selected\"";
                                                            }
                                                            ?>>Dr.</option>                                                    <option value="Mr" <?php
                                                            if (!(strcmp("Mr", htmlentities($row_editprof['title'], ENT_COMPAT, 'utf-8')))) {
                                                                echo "selected=\"selected\"";
                                                            }
                                                            ?>>Mr.</option>                                                    <option value="Mrs" <?php
                                                            if (!(strcmp("Mrs", htmlentities($row_editprof['title'], ENT_COMPAT, 'utf-8')))) {
                                                                echo "selected=\"selected\"";
                                                            }
                                                            ?>>Mrs.</option>                                                    <option value="Miss" <?php
                                                            if (!(strcmp("Miss", htmlentities($row_editprof['title'], ENT_COMPAT, 'utf-8')))) {
                                                                echo "selected=\"selected\"";
                                                            }
                                                            ?>>Miss</option>                                                </select>  
                                            </div>                    
                                        </div> 
                                        <div class="control-group">  

                                            <label class="control-label" for="textfield">First Name</label>   
                                            <div class="controls">                                              
                                                <input type="text" name="fname" class="input-large" readonly="" disabled="" value="<?php echo htmlentities($row_editprof['fname'], ENT_COMPAT, 'utf-8'); ?>" size="32" />       
                                            </div>                                        </div>  
                                        <div class="control-group">         
                                            <label class="control-label" for="textfield">Last Name</label>                        
                                            <div class="controls">   
                                                <input type="text" name="lname" readonly="" disabled=""  class="input-large" value="<?php echo htmlentities($row_editprof['lname'], ENT_COMPAT, 'utf-8'); ?>" size="32" />                                         
                                            </div>                        
                                        </div>                                     
                                        <div class="control-group">                                   
                                            <label class="control-label" for="textfield">Middle Name</label>                                            <div class="controls">                                              
                                                <input type="text" name="mname" class="input-large" value="<?php echo htmlentities($row_editprof['mname'], ENT_COMPAT, 'utf-8'); ?>" size="32" />                                          
                                            </div>                    
                                        </div>      
                                        <div class="control-group">   
                                            <label class="control-label" for="textfield">Department</label>    
                                            <div class="controls">      
                                                <select name="deptid" disabled=""  id="deptid"><?php do { ?>     
                                                        <option value="<?php echo $row_dept['deptid'] ?>"<?php
                                                                if (!(strcmp($row_dept['deptid'], htmlentities($row_editprof['deptid'], ENT_COMPAT, 'utf-8')))) {
                                                                    echo "selected=\"selected\"";
                                                                }
                                                                ?><?php echo $row_dept['deptname'] ?></option>   
<?php
} while ($row_dept = mysql_fetch_assoc($dept));
$rows = mysql_num_rows($dept);
if ($rows > 0) {
    mysql_data_seek($dept, 0);
    $row_dept = mysql_fetch_assoc($dept);
}
?>                                              
                                                </select>                                     
                                            </div>             
                                        </div>      
                                        <div class="control-group"> 
                                            <label class="control-label" for="textfield">Phone No.</label> 
                                            <div class="controls">                                            
                                                <input type="text" class="input-large" name="phone" value="<?php echo htmlentities($row_editprof['phone'], ENT_COMPAT, 'utf-8'); ?>" />                                            </div>                      
                                        </div>                                      
                                        <div class="control-group">  
                                            <label class="control-label" for="textfield">E-mail.</label> 
                                            <div class="controls">                                        
                                                <input type="text" class="input-large" name="email" value="<?php echo htmlentities($row_editprof['email'], ENT_COMPAT, 'utf-8'); ?>" />                                        
                                            </div>      

                                        </div>  
                                        <div class="control-group">    
                                            <label class="control-label" for="textfield">Address</label>  
                                            <div class="controls">                                          
                                                <textarea name="addr" class="input-xlarge"><?php echo htmlentities($row_editprof['addr'], ENT_COMPAT, 'utf-8'); ?></textarea>                                            </div>                               
                                        </div>                                        <div class="control-group">                                         
                                            <label class="control-label"  for="textfield">Sex </label>            
                                            <div class="controls">  

                                                <select name="sex">  
                                                    <option value="M" <?php
                                                            if (!(strcmp("M", htmlentities($row_editprof['sex'], ENT_COMPAT, 'utf-8')))) {
                                                                echo "SELECTED";
                                                            }
?>>Male</option>                                                    <option value="F" <?php
                                                            if (!(strcmp("F", htmlentities($row_editprof['sex'], ENT_COMPAT, 'utf-8')))) {
                                                                echo "SELECTED";
                                                            }
?>>Female</option>                                                </select>                                        
                                            </div>            
                                        </div>                                   
                                        <div class="control-group">                                          
                                            <label class="control-label" for="textfield">Research Area </label>        
                                            <div class="controls">                                              
                                                <textarea name="profile" class="input-xlarge"><?= htmlentities($row_editprof['profile'], ENT_COMPAT, 'utf-8'); ?></textarea>                                      
                                            </div>                          
                                        </div>          

                                        <input type="hidden" name="MM_update" value="form1" />           
                                        <input type="hidden" name="lectid" value="<?php echo $row_editprof['lectid']; ?>" />                                    
                                        <div class="form-actions">                                          
                                            <input type="submit" value="Update Staff" class="btn btn-primary" >      
                                            <button class="btn" type="button">Cancel</button>                
                                        </div>                                
                                    </form>         
                                </div>    
                            </div>   
                            <p>&nbsp;</p>  
                        </div>               

                    </div>             
                </div>            </div>    
        </div><?php include INCPATH . "/footer.php" ?>  
    </body>
</html>