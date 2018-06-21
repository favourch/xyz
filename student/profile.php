<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');


$auth_users = "1,2,3,4,5,6,10,20,21,22,23,24,27,28,29";
check_auth($auth_users, $site_root);

$colname_student = "-1";
if (isset($_GET['stid'])) {
    $colname_student = $_GET['stid'];
}
else {
    $colname_student = getSessionValue('stid');
}

fillAccomDetails($site_root, $tams);

$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$query_student = sprintf("SELECT s.*, progname, p.deptid, d.deptname, d.colid, c.colname, at.displayname  "
        . "FROM student s, programme p, department d, college c, admission_type at "
        . "WHERE s.progid = p.progid "
        . "AND p.deptid = d.deptid "
        . "AND d.colid = c.colid "
        . "AND s.admid = at.typeid "
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

 $query_reg_fee = sprintf("SELECT st.* "
                        . "FROM registration_transactions st "
                    //    . "JOIN student s on s.jambregid = st.can_no "
                        . "WHERE st.can_no = %s "
                        . "AND st.status = 'APPROVED' ",
                       // . "AND st.reg_fee = 'TRUE'", 
                        GetSQLValueString($row_student['jambregid'], "text")); 
$reg_fee = mysql_query($query_reg_fee, $tams) or die(mysql_error());
$row_reg_fee = mysql_fetch_assoc($reg_fee);
$totalRows_reg_fee = mysql_num_rows($reg_fee);




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
                                        <?php echo $row_student['lname']." ".$row_student['fname']."'s"?> Profile - ( JAMB REG: <?php echo $row_student['jambregid'] ?> )
                                    </h3>
                                    <ul class="tabs">
                                        <li class="active">
                                            <a href="/<?= $site_root?>/registration/course_reg_form.php?stid=<?= $colname_student?>">Courses Form</a>
                                        </li>
                                        
                                        <?php if($totalRows_reg_fee > 0){?>
                                        <li class="active">
                                            <a href="matric_oath.php" target="_tab">Matriculation Oath</a>
                                        </li>
                                        <?php } ?>
                                        
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
                                                     <?php if (strlen($row_student['degree']) >0) { ?>
                                                     <a style="color:red"> <?php echo 'Congratulations ... You made the PASS LIST and Graduated with '.strtoupper($row_student['degree']) ; }?></a>
                                                     <?php if($row_student['passlist'] == 'Yes'){?>
                                                     <a href="nysc_form.php" target="tabs" class="btn btn-pink btn-small">Print NYSC Mobilization Form</a>
                                                     <a href="ex_n_rec_clr_form_deg_result.php" target="tabs" class="btn btn-blue btn-small">Print Final Clearance Form</a>
                                                     <a href="ex_n_rec_not_of_rslt_form.php" target="tabs" class="btn btn-purple btn-small">Print Notification of Result Request Form</a>
                                                     <?php } ?>
                                                     
                                                        <div class="span3">
                                                            <div class="user">
                                                                <strong><?php echo $row_student['lname']; ?> <?php echo $row_student['fname']; ?>  <?php echo $row_student['mname']; ?></strong> (<?= getSex($row_student['sex'])  ?>)
                                                                <p><?= $row_student['stdid']?></p>
                                                            </div>
                                                            <p>
                                                                <img class="timeline-images" style="width: 250px; height: 280px;" src="<?= $image_url?>" />   
                                                            </p>
                                                        </div>
                                                        <table class="table  table-nomargin span8"> 
                                                            <div class="user"><br><br></div>
                                                            <tbody>
                                                                
                                                                
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
                                                                    <td><?php echo $row_student['displayname']; 
                                                                         if (strlen($row_student['degree']) >0){
                                                                            echo ' (Graduated with '.strtoupper($row_student['degree']).')';
                                                                            }
                                                                            else {
                                                                            echo ' (Presently in Year '.$row_student['level'].')';
                                                                            }
                                                                        ?>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Campus Address</th>
                                                                    <td><?=  $row_student['addr']?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Sponsor Details</th>
                                                                    <td><?=  $row_student['sponsorname']?> <br />
                                                                    <?=  $row_student['sponsoradrs']?> <br />
                                                                    <?=  $row_student['sponsorphn']?> 
                                                                    </td>
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
