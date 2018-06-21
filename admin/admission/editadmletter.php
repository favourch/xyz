<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');



$auth_users = "1,20";
check_auth($auth_users, $site_root.'/admin');

$type = isset($_GET['type']) ? $_GET['type'] : '';

if(isset($_POST['admletter'])) {
    unset($_POST['admletter']);    
    
    $valid = true;
    foreach ($_POST as $key => $value) {
        if ($value == '' || $value == NULL) {
            $valid = false;
            break;
        }
    }

    if ($valid) {        
        $insertSQL = sprintf("UPDATE admissions SET admletter = %s WHERE admid = %s", 
                GetSQLValueString($_POST['ck'], "text"),
                GetSQLValueString($type, "int"));
        $Result1 = mysql_query($insertSQL, $tams);
        
        if (mysql_errno() != 0) {
            $not_msg = "There was a problem submitting the Admission Letter!";
        }
        
    } else {
        $not_msg = "Cannot submit an empty Admisson Letter!";
    }
    
}

$query_admission = sprintf("SELECT a.*, s.sesname, at.typename "
                    . "FROM admissions a "
                    . "LEFT JOIN admission_type at ON a.typeid = at.typeid "
                    . "LEFT JOIN session s ON a.sesid = s.sesid "
                    . "WHERE a.admid = %s "
                    . "ORDER BY sesid DESC", GetSQLValueString($type, "int"));
$admission = mysql_query($query_admission, $tams) or die(mysql_error());
$row_admission = mysql_fetch_assoc($admission);
$totalRows_admission = mysql_num_rows($admission);

$desc = "({$row_admission['sesname']} {$row_admission['typename']})";
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
                        <div class="box box-bordered box-color">
                            <div class="box-title">
                                <h3>
                                    <i class="icon-reorder"></i>
                                    Edit Admission Letter <?php echo $desc?>
                                </h3>
                            </div>
                            <div class="box-content">  
                                <form class="form-wysiwyg" method="POST" action="#">
                                    <textarea rows="5" class="ckeditor span12" name="ck" 
                                              style="visibility: hidden; display: none;" required>
                                        <?php echo $row_admission['admletter']?>
                                    </textarea>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
        </div>
    </body>
</html>