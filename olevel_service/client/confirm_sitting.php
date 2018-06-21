<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');
//$auth_users = "1,20,24,26, 10,11";
//check_auth($auth_users, $site_root . '/admin');

$referer = $_SERVER['HTTP_REFERER'];


if(isset($_POST['sitting'])){
    $_SESSION['olevel_sitting'] = $_POST['sitting'];
}

if(getSessionValue('accttype') == 'pros'){
    $query = sprintf("SELECT * "
                    . "FROM prospective "
                    . "WHERE jambregid = %s", 
                    GetSQLValueString(getSessionValue('uid'), "text"));
    
}else{
    $query = sprintf("SELECT * "
                    . "FROM student "
                    . "WHERE stdid = %s", 
                    GetSQLValueString(getSessionValue('uid'), "text"));
}
$rsResult = mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);

$prog = $row_result = $row_result['progid'];
$level = getSessionValue('level');
if(getSessionValue('accttype') == 'pros'){
    $prog = $row_result['progoffered'];
    $level = 0;
}

if(isset($_POST['mm_submit']) &&  $_POST['mm_submit'] == 'proceed'){
    
    //Get current user details 
    if(getAccess() == '10') 
    {
        $confVeriSQL = sprintf("SELECT * "
                            . "FROM verification "
                            . "WHERE stdid = %s ",
                            GetSQLValueString(getSessionValue('uid'), 'text'));
        $confVeriRS = mysql_query($confVeriSQL, $tams) or die(mysql_error());
        $confVeriCount = mysql_num_rows($confVeriRS);

        if($confVeriCount > 0){

            for($i = 0; $i < $_SESSION['olevel_sitting']; $i++){
                $sit = "Sitting ".($i+1);
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
            }

        }else{

            $verificationSQL = sprintf("INSERT INTO verification "
                                . "(stdid, sesid, type, ver_code, olevel_sitting ) "
                                . "VALUES(%s, %s, %s, UUID(), %s )", 
                                GetSQLValueString(getSessionValue('uid'), 'text'),
                                GetSQLValueString(getSessionValue('admid'), 'int'),
                                GetSQLValueString(getSessionValue('accttype'), 'text'),
                                GetSQLValueString($_SESSION['olevel_sitting'], "int"));
            $verificationRS = mysql_query($verificationSQL, $tams) or die(mysql_error());

            for($i = 0; $i < $_SESSION['olevel_sitting']; $i++){
                $sit = "Sitting ".($i+1);
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
            }
        }
    }
    else
    {
        $confVeriSQL = sprintf("SELECT * "
                        . "FROM verification "
                        . "WHERE jambregid = %s ",
                        GetSQLValueString(getSessionValue('uid'), 'text'));
        $confVeriRS = mysql_query($confVeriSQL, $tams) or die(mysql_error());
        $confVeriCount = mysql_num_rows($confVeriRS);

        if($confVeriCount > 0){


            for($i = 0; $i < $_SESSION['olevel_sitting']; $i++){
                $sit = "Sitting ".($i+1);
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

        }else{

            $verificationSQL = sprintf("INSERT INTO verification "
                                . "(jambregid, sesid, type, ver_code, olevel_sitting ) "
                                . "VALUES(%s, %s, %s, UUID(), %s )", 
                                GetSQLValueString(getSessionValue('uid'), 'text'),
                                GetSQLValueString(getSessionValue('admid'), 'int'),
                                GetSQLValueString(getSessionValue('accttype'), 'text'),
                                GetSQLValueString($_SESSION['olevel_sitting'], "int"));
            $verificationRS = mysql_query($verificationSQL, $tams) or die(mysql_error());


            for($i = 0; $i < $_SESSION['olevel_sitting']; $i++){
                $sit = "Sitting ".($i+1);
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
        }
    }
    
    header(sprintf("Location: index.php"));
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
                                        O'Level Verification Module (Data Submission)
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="well well-small">
                                        <div class="alert alert-info">
                                            <i class="glyphicon-circle_info"></i> 
                                            You have choose to submit <?= $_SESSION['olevel_sitting'] ?> 
                                            Sitting of O'Level result
                                            click the Proceed button or Cancel to go back 
                                        </div> 
                                        <div class="well well-small">
                                            <h2>O'Level Sitting = <?= $_SESSION['olevel_sitting'] ?></h2>
                                            
                                        </div>
                                        <div class="row-fluid">
                                            <div class="span6">
                                                <div class="pull-left left"><a href="" class="btn btn-warning">Cancel</a></div>        
                                            </div>
                                            <form method='post' action='confirm_sitting.php'>
                                                <div class="span6">
                                                    
                                                    <div class="pull-right right">
                                                        <button type='submit' name='mm_submit' value='proceed' class="btn btn-success">Proceed</button>  
                                                    </div>                
                                                </div>
                                            </form>
                                        </div>
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
    </body>
</html>

