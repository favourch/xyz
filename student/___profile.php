<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



/*-----------------------------------------------*
 * 
 * Logic of the College/index.php Page 
 *
 **------------------------------------------------
 */

$MM_authorizedUsers = "1,2,3,4,5,6,10,20,21,22,23,24";
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
$colname_student = "-1";
if (isset($_GET['stid'])) {
    $colname_student = $_GET['stid'];
}
else {
    $colname_student = getSessionValue('stid');
}

mysql_select_db($database_tams, $tams);

$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$query_student = sprintf("SELECT s.*, progname, p.deptid, deptname, d.colid, colname "
        . "FROM student s, programme p, department d, college c "
        . "WHERE s.progid = p.progid "
        . "AND p.deptid = d.deptid "
        . "AND d.colid = c.colid "
        . "AND stdid = %s", GetSQLValueString($colname_student, "text"));
$student = mysql_query($query_student, $tams) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);

$query_reg = sprintf("SELECT * FROM registration r WHERE stdid = %s", GetSQLValueString($colname_student, "text"));
$reg = mysql_query($query_reg, $tams) or die(mysql_error());
$row_reg = mysql_fetch_assoc($reg);
$totalRows_reg = mysql_num_rows($reg);

$query_rsdisp = sprintf("SELECT * FROM disciplinary WHERE stdid = %s", GetSQLValueString($colname_student, "text"));
$rsdisp = mysql_query($query_rsdisp, $tams) or die(mysql_error());
$row_rsdisp = mysql_fetch_assoc($rsdisp);
$totalRows_rsdisp = mysql_num_rows($rsdisp);

$pictureId = $colname_student;

if ($row_student['jambregid'] != NULL) {
    $pictureId = strtoupper($row_student['jambregid']);
}


$img_dir = "../img/user/student";
$image_url = get_pics($colname_student, $img_dir);

$props = array('email', 'phone', 'sex', 'dob', 'addr', 'image');

foreach ($props as $key => $prop) {
    $value = isset($row_student[$prop]) ? strtolower($row_student[$prop]) : '';
    switch ($prop) {
        case 'email':
            if (!isset($value) || $value == '' || in_array($value, array('change@youremail.com'))) {
                $props[$key] = 'Email Address';
            }
            else {
                unset($props[$key]);
            }
            break;

        case 'phone':
            if (!isset($value) || $value == '') {
                $props[$key] = 'Phone Number';
            }
            else {
                unset($props[$key]);
            }
            break;

        case 'sex':
            if (!isset($value) || !in_array($value, array('m', 'f'))) {
                $props[$key] = 'Sex';
            }
            else {
                unset($props[$key]);
            }
            break;

        case 'dob':
            if (!isset($value) || $value == '0000-00-00') {
                $props[$key] = 'Date of Birth';
            }
            else {
                unset($props[$key]);
            }
            break;

        case 'image':
            if ($image_url == '../images/student/profile.png') {
                $props[$key] = 'Profile Image';
            }
            else {
                unset($props[$key]);
            }
            break;

        default:
            if (!isset($value) || $value == 'Your Campus or Home Addres') {
                $props[$key] = 'Address';
            }
            else {
                unset($props[$key]);
            }
    }
}



if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
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
                                    <h3><i class="icon-user"></i>
                                        <?php echo $row_student['lname']." ".$row_student['fname']."'s"?> Profile
                                    </h3>
                                    <ul class="tabs">
                                        <li class="active">
                                            <a href="/<?= $site_root?>/registration/course_reg_form.php?stid=<?= $colname_student?>">Courses Form</a>
                                        </li>
<!--                                        <li>
                                            <a data-toggle="tab" href="#t8">Sample tab #2</a>
                                        </li>
                                        <li>
                                            <a data-toggle="tab" href="#t9">Third tab</a>
                                        </li>-->
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
                                                        <div class="date">Year <?= $row_student['level']; ?></div>
                                                    </div>
                                                    <div class="activity">
                                                        <div class="span3">
                                                            <div class="user">
                                                                <strong><?php echo $row_student['lname']; ?> <?php echo $row_student['fname']; ?></strong>
                                                                <p><?= $row_student['stdid']?></p>
                                                            </div>
                                                            <p>
                                                                <img class="timeline-images" style="width: 250px; height: 280px;" src="<?= $image_url?>" />
                                                            </p>
                                                        </div>
                                                        <table class="table  table-nomargin span6"> 
                                                            <div class="user"><br><br></div>
                                                            <tbody>
                                                                <tr>
                                                                    <th>Name</th>
                                                                    <td><?php echo $row_student['lname']; ?> <?php echo $row_student['fname']; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Sex</th>
                                                                    <td><?= getSex($row_student['sex'])  ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>College</th>
                                                                    <td><a href="../college/college.php?cid=<?php echo $row_student['colid']; ?>"><?php echo $row_student['colname']; ?></a></a></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Department</th>
                                                                    <td><a href="../department/department.php?did=<?php echo $row_student['deptid']; ?>"><?php echo $row_student['deptname']; ?></a></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Phone</th>
                                                                    <td><a href="callto: <?=  $row_student['phone']?>"><?=  $row_student['phone']?></a></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Email</th>
                                                                    <td><a   href=" mailto:<?=  $row_student['email']?> "><?=  $row_student['email']?></a></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Entry Mode </th>
                                                                    <td><?php echo $row_student['admode']; ?> <em>(presently in Year <?php echo $row_student['level']; ?>)</em></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Address</th>
                                                                    <td><?=  $row_student['addr']?></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="user activity">
                                                    <strong>Profile :</strong>
                                                    <p><?= $row_student['profile'] ?></p>
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
        <?php include INCPATH."/footer.php" ?>
    </body>
</html>
<?php
//mysql_free_result($dept);
//
//mysql_free_result($col);
//
//mysql_free_result($staff);
?>
