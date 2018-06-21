<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "1,2,3,4,5,6,7,8,9, 20, 21, 22, 23";
check_auth($auth_users, $site_root);

if (isset($_GET['lid'])) {
    $colname_staff = $_GET['lid'];
}
else {
    $colname_staff = getSessionValue('MM_Username');
}

$query_staff = sprintf("SELECT l.*, d.deptname, c.colname , c.colid "
                    . "FROM lecturer l, department d, college c "
                    . "WHERE d.deptid = l.deptid "
                    . "AND d.colid = c.colid "
                    . "AND lectid = %s", 
                    GetSQLValueString($colname_staff, "text"));
$staff = mysql_query($query_staff, $tams) or die(mysql_error());
$row_staff = mysql_fetch_assoc($staff);
$totalRows_staff = mysql_num_rows($staff);

$img_dir = IMGPATH."/user/staff";
$img_url = get_pics($colname_staff, $img_dir);
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
