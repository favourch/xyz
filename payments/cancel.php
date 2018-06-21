<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



$MM_authorizedUsers = "10";

$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) {

    // For security, start by assuming the visitor is NOT authorized. 

    $isValid = False;



    // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
    // Therefore, we know that a user is NOT logged in if that Session variable is blank. 

    if (!empty($UserName)) {

        // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
        // Parse the strings into arrays. 

        $arrUsers = Explode(",", $strUsers);

        $arrGroups = Explode(",", $strGroups);

        if (in_array($UserName, $arrUsers)) {

            $isValid = true;
        }

        // Or, you may restrict access to only certain users based on their username. 

        if (in_array($UserGroup, $arrGroups)) {

            $isValid = true;
        }

        if (($strUsers == "") && false) {

            $isValid = true;
        }
    }

    return $isValid;
}
$MM_restrictGoTo = "../index.php";

if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("", $MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {

    $MM_qsChar = "?";

    $MM_referrer = $_SERVER['PHP_SELF'];
    if (strpos($MM_restrictGoTo, "?"))
        $MM_qsChar = "&";
    if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0)
        $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
    $MM_restrictGoTo = $MM_restrictGoTo . $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
    header("Location: " . $MM_restrictGoTo);

    exit;
}

$page_title = "Tasued";




mysql_select_db($database_tams,$tams);
$query = sprintf("SELECT * FROM student WHERE stdid=%s", GetSQLValueString(getSessionValue('MM_Username'), "text"));
$rsResult =  mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);

$paydesc = "SCHOOL FEES";
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
                                        Fee Payment Notification
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="form-horizontal form-bordered">
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Full Name</label>
                                            <div class="controls">
                                                <?php echo $row_result['lname'].' '.$row_result['fname'].' '.$row_result['mname']?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Matric No.</label>
                                            <div class="controls">
                                                <?php echo $row_result['stdid']?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Payment Description</label>
                                            <div class="controls">
                                                <div class="alert alert-error">Your payment transaction has been canceled!</div>
                                            </div>
                                        </div>
                                        
                                    </div> 
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