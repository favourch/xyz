<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



/*-----------------------------------------------*
 * 
 * Logic of the College/coledit.php Page 
 *
 **------------------------------------------------
 */

$MM_authorizedUsers = "3";
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


mysql_select_db($database_tams, $tams);
$query_rssess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,6";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

$msg = '';
if (isset($_POST['assign'])) {

    foreach ($_POST as $key => $value) {
        if ($key == 'assign' || $value == '-1')
            continue;

        $query_rsChk = sprintf("SELECT * "
                . "FROM staff_adviser "
                . "WHERE level=%s "
                . "AND sesid=%s "
                . "AND deptid=%s", GetSQLValueString($key, "int"), GetSQLValueString($row_rssess['sesid'], "int"), GetSQLValueString(getSessionValue('did'), "int"));
        $rsChk = mysql_query($query_rsChk, $tams) or die(mysql_error());
        $row_rsChk = mysql_fetch_assoc($rsChk);
        $totalRows_rsChk = mysql_num_rows($rsChk);

        $Result1 = '';
        if ($totalRows_rsChk > 0) {
            $updSQL = sprintf("UPDATE lecturer SET access=5 "
                    . "WHERE lectid=%s", GetSQLValueString($row_rsChk['lectid'], "text"));
            mysql_query($updSQL, $tams) or die(mysql_error());

            $updateSQL = sprintf("UPDATE staff_adviser "
                    . "SET lectid=%s "
                    . "WHERE level=%s "
                    . "AND sesid=%s "
                    . "AND deptid=%s", GetSQLValueString($value, "text"), GetSQLValueString($key, "int"), GetSQLValueString($row_rssess['sesid'], "int"), GetSQLValueString(getSessionValue('did'), "int"));

            $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
            $update_info = mysql_info($tams);
        }

        if (!$Result1) {
            $insertSQL = sprintf("INSERT INTO staff_adviser (lectid, sesid, level, deptid) VALUES (%s, %s, %s, %s)", GetSQLValueString($value, "text"), GetSQLValueString($row_rssess['sesid'], "int"), GetSQLValueString($key, "text"), GetSQLValueString(getSessionValue('did'), "text"));

            mysql_select_db($database_tams, $tams);
            $Result2 = mysql_query($insertSQL, $tams) or die(mysql_error());
        }

        $updateLct = sprintf("UPDATE lecturer SET access=6 "
                . "WHERE lectid=%s", GetSQLValueString($value, "text"));

        $Result1 = mysql_query($updateLct, $tams) or die(mysql_error());
        $msg = 'Staff advisers updated successfully';
    }
}

$query_lect = sprintf("SELECT lectid, fname, lname FROM lecturer WHERE (access = 5 OR access = 6) AND deptid = %s", GetSQLValueString(getSessionValue('did'), "int"));
$lect = mysql_query($query_lect, $tams) or die(mysql_error());
$row_lect = mysql_fetch_assoc($lect);
$totalRows_lect = mysql_num_rows($lect);


$sesid = $row_rssess['sesid'];
if (isset($_GET['sid'])) {
    $sesid = $_GET['sid'];
}
$query_adv = sprintf("SELECT * FROM staff_adviser WHERE deptid = %s AND sesid = %s", GetSQLValueString(getSessionValue('did'), "int"), GetSQLValueString($sesid, "int"));
$adv = mysql_query($query_adv, $tams) or die(mysql_error());
$row_adv = mysql_fetch_assoc($adv);
$totalRows_adv = mysql_num_rows($adv);

$advisers = array();
for ($i = 0; $i < 6; $i++) {
    $advisers[$i] = NULL;
}

for ($i = 0; $i < $totalRows_adv; $i++, $row_adv = mysql_fetch_assoc($adv)) {
    $advisers[$row_adv['level']] = $row_adv['lectid'];
}


if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
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
<!--                    <div class="breadcrumbs">
                        <ul>
                            <li>
                                <a href="index.php">Home</a> <i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="college.php">College</a>
                            </li>
                        </ul>
                        <div class="close-bread">
                            <a href="#"><i class="icon-remove"></i></a>
                        </div>
                    </div>-->

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Assign Staff Adviser
                                    </h3>
                                </div>
                                <div class="box-content nopadding">
                                    <table class="table">
                                        <form method="post" action="" >
                                            <tr>
                                                <td colspan="3">
                                                    <select onchange="sesfilt(this)">
                                                        <?php do { ?>
                                                            <option value="<?php echo $row_rssess['sesid'] ?>" <?php if (!(strcmp($row_rssess['sesid'], $sesid))) {
                                                            echo "selected=\"selected\"";
                                                        } ?>><?php echo $row_rssess['sesname'] ?></option>
                                                        <?php
                                                        }
                                                        while ($row_rssess = mysql_fetch_assoc($rssess));
                                                        $rows = mysql_num_rows($rssess);
                                                        if ($rows > 0) {
                                                            mysql_data_seek($rssess, 0);
                                                            $row_rssess = mysql_fetch_assoc($rssess);
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>100 Level</td>
                                                <td>
                                                    <select name="1" disabled>
                                                        <option value="-1" >Select a lecturer</option>
                                                        <?php for ($i = 0; $i < $totalRows_lect; $i++, $row_lect = mysql_fetch_assoc($lect)) { ?>
                                                            <option value="<?php echo $row_lect['lectid'] ?>" <?php if ($row_lect['lectid'] == $advisers[1]) echo 'selected'; ?>>
                                                            <?php echo $row_lect['fname'] . ' ' . $row_lect['lname'] ?>
                                                            </option>
                                                            <?php
                                                        }
                                                        $rows = mysql_num_rows($lect);
                                                        if ($rows > 0) {
                                                            mysql_data_seek($lect, 0);
                                                            $row_lect = mysql_fetch_assoc($lect);
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td><input type="checkbox" class="enable"/></td>
                                            </tr>

                                            <tr>
                                                <td>200 Level</td>
                                                <td>
                                                    <select name="2" disabled>
                                                        <option value="-1" >Select a lecturer</option>
                                                        <?php for ($i = 0; $i < $totalRows_lect; $i++, $row_lect = mysql_fetch_assoc($lect)) { ?>
                                                            <option value="<?php echo $row_lect['lectid'] ?>" <?php if ($row_lect['lectid'] == $advisers[2]) echo 'selected'; ?>>
                                                            <?php echo $row_lect['fname'] . ' ' . $row_lect['lname'] ?>
                                                            </option>
                                                            <?php
                                                        }
                                                        $rows = mysql_num_rows($lect);
                                                        if ($rows > 0) {
                                                            mysql_data_seek($lect, 0);
                                                            $row_lect = mysql_fetch_assoc($lect);
                                                        }
                                                        ?>
                                                    </select>
                                                </td>               
                                                <td><input type="checkbox" class="enable"/></td>
                                            </tr> 

                                            <tr>
                                                <td>300 Level</td>
                                                <td>
                                                    <select name="3" disabled>
                                                        <option value="-1" >Select a lecturer</option>
                                                        <?php for ($i = 0; $i < $totalRows_lect; $i++, $row_lect = mysql_fetch_assoc($lect)) { ?>
                                                            <option value="<?php echo $row_lect['lectid'] ?>" <?php if ($row_lect['lectid'] == $advisers[3]) echo 'selected'; ?>>
                                                            <?php echo $row_lect['fname'] . ' ' . $row_lect['lname'] ?>
                                                            </option>
                                                            <?php
                                                        }
                                                        $rows = mysql_num_rows($lect);
                                                        if ($rows > 0) {
                                                            mysql_data_seek($lect, 0);
                                                            $row_lect = mysql_fetch_assoc($lect);
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td><input type="checkbox" class="enable"/></td>            
                                            </tr> 

                                            <tr>
                                                <td>400 Level</td>
                                                <td>
                                                    <select name="4" disabled>
                                                        <option value="-1" >Select a lecturer</option>
                                                        <?php for ($i = 0; $i < $totalRows_lect; $i++, $row_lect = mysql_fetch_assoc($lect)) { ?>
                                                            <option value="<?php echo $row_lect['lectid'] ?>" <?php if ($row_lect['lectid'] == $advisers[4]) echo 'selected'; ?>>
                                                            <?php echo $row_lect['fname'] . ' ' . $row_lect['lname'] ?>
                                                            </option>
                                                            <?php
                                                        }
                                                        $rows = mysql_num_rows($lect);
                                                        if ($rows > 0) {
                                                            mysql_data_seek($lect, 0);
                                                            $row_lect = mysql_fetch_assoc($lect);
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td><input type="checkbox" class="enable"/></td>            
                                            </tr> 
                                            <?php if ($sesid == $row_rssess['sesid']) { ?>
                                                <tr>
                                                    <td colspan="2" align="center">
                                                        <input class="btn btn-primary"  type="submit" name="assign" value="Assign"/>
                                                    </td>            
                                                </tr>
                                            <?php } ?>
                                        </form>
                                    </table>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH."/footer.php" ?>
    </body>
     <script type="text/javascript">
        $(function() {
            $('.enable').change(function() {
                if($(this).is(':checked')) {
                    $(this).parent().prev().children('select').attr('disabled', false);
                    return;
                }
                $(this).parent().prev().children('select').attr('disabled', true);
            });
        });
    </script>
</html>

