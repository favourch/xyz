<?php
require_once('../Connections/tams.php');

if (!isset($_SESSION)) {
    session_start();
}

require_once('../param/param.php');
require_once('../functions/function.php');
require('../param/site.php');

/*-----------------------------------------------*
 * 
 * Logic of the College/index.php Page 
 *
 **------------------------------------------------
 */

$MM_authorizedUsers = "1, 2, 3, 4, 5, 6, 20, 21, 22, 23, 24";
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

$MM_restrictGoTo = "../login.php";
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
if (isset($_GET['lid'])) {
    $colname_staff = $_GET['lid'];
}
else {
    $colname_staff = getSessionValue('MM_Username');
}


mysql_select_db($database_tams, $tams);
$query_staff = sprintf("SELECT l.*, d.deptname, c.colname , c.colid "
                    . "FROM lecturer l, department d, college c "
                    . "WHERE d.deptid = l.deptid "
                    . "AND d.colid = c.colid "
                    . "AND lectid = %s", 
                    GetSQLValueString($colname_staff, "text"));
$staff = mysql_query($query_staff, $tams) or die(mysql_error());
$row_staff = mysql_fetch_assoc($staff);
$totalRows_staff = mysql_num_rows($staff);




if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}




$img_dir = "../img/user/staff";
$img_url = get_pics($colname_staff, $img_dir);

$page_title = "Tasued";
?>
<!doctype html>
<html>
    <?php include "../include/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
        <?php include "../include/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include "../include/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include "../include/page_header.php" ?>
                    

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-user"></i>
                                        <?php echo $row_staff['lname']." ".$row_staff['fname']."'s"?> Profile
                                    </h3>
                                    <ul class="tabs">
                                        <li ><a href="teaching_history.php?lid=<?= $row_staff['lectid']?>" class="btn btn-small btn-blue">Teaching History</a></li>
                                    </ul>
                                </div>
                                <div class="box-content nopadding">
                                    <ul class="timeline">
                                        <li>
                                            <div class="timeline-content">
                                                <div class="row-fluid">
                                                    <div class="left">
                                                        <div class="icon lightred">
                                                            <i class="icon-user"></i>
                                                        </div>
                                                        
                                                    </div>
                                                    <div class="activity">
                                                        <div class="span3">
                                                            <div class="user">
                                                                <strong><?= $row_staff['lname'] . " " . $row_staff['fname'] . "" ?></strong>
                                                                <p><?= $row_staff['lectid']?></p>
                                                            </div>
                                                            <p>
                                                                <img class="timeline-images" style="width: 250px; height: 280px;" src="<?= $img_url?>" />
                                                            </p>
                                                        </div>
                                                        <table class="table  table-nomargin span6"> 
                                                            <div class="user"><br><br></div>
                                                            <tbody>
                                                                <tr>
                                                                    <th>Name</th>
                                                                    <td><?= $row_staff['lname'] . " " . $row_staff['fname'] ." ".$row_staff['mname'] ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Sex</th>
                                                                    <td><?= getSex($row_staff['sex'])  ?></td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                    <th>College</th>
                                                                    <td><a href="../college/college.php?cid=<?= $row_staff['colid']?>"><?= $row_staff['colname']?></a></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Department</th>
                                                                    <td><a href="../department/department.php?did=<?= $row_staff['deptid']?>"><?= $row_staff['deptname']?></a></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Phone</th>
                                                                    <td><a href="callto: <?= $row_staff['phone']?>"><?= $row_staff['phone']?></a></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Email</th>
                                                                    <td><a   href=" mailto:<?= $row_staff['email']?> "><?= $row_staff['email']?></a></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Address</th>
                                                                    <td><?= $row_staff['addr']?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Profile</th>
                                                                    <td><?= $row_staff['profile']  ?></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                               
                                            </div>
                                            <div class="line"></div>
                                        </li>
                                    </ul>                                   
                                </div>
                            </div>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include "../include/footer.php" ?>
    </body>
</html>
<?php
//mysql_free_result($dept);
//
//mysql_free_result($col);
//
//mysql_free_result($staff);
?>
