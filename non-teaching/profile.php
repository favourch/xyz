<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "1, 20, 60";
check_auth($auth_users, $site_root);

if (isset($_GET['lid'])) {
    $colname_staff = $_GET['lid'];
}
else {
    $colname_staff = getSessionValue('uid');
}








mysql_select_db($database_tams, $tams);
$query_staff = sprintf("SELECT * "
                    . "FROM non_teaching  "
                    . "WHERE staff_id = %s", 
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
    <?php include INCPATH."/header.php" ?>
   
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" >
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
                                        <?php echo $row_staff['lname']." ".$row_staff['fname']."'s"?> Profile
                                    </h3>
                                    <ul class="tabs">
                                        <li><a href="editprofile.php?lid=<?= $row_staff['staff_id']?>" class="btn btn-small btn-blue ">Update my profile</a></li>
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
                                                                <strong><?= $row_staff['lname'] . " " . $row_staff['fname'] . "'s" ?></strong>
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
                                                                    <th>DoB</th>
                                                                    <td><?= $row_staff['date_of_birth'] ?></td>
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
                                                                    <th>Designation</th>
                                                                    <td><?= $row_staff['designation']?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Department/Unit</th>
                                                                    <td><?= $row_staff['section_unit']?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Grade step</th>
                                                                    <td><?= $row_staff['salary_grade_step']  ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Qualification</th>
                                                                    <td><?= $row_staff['qualification']  ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Date of App. in University</th>
                                                                    <td><?= $row_staff['date_of_app_in_university']  ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Nature of Appointment</th>
                                                                    <td><?= $row_staff['nature_of_app']  ?></td>
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
        
        <?php include INCPATH."/footer.php" ?>
    </body>
</html>

