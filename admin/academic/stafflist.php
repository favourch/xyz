<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "20,21,23";
check_auth($auth_users, $site_root.'/admin');

$query_staff = "";

if (isset($_GET['did'])) {
    $deptid = $_GET['did'];
    $query_staff = sprintf("SELECT l.lectid, l.fname, l.lname, l.email, l.phone 
                        FROM lecturer l 
                        JOIN department d ON d.deptid = l.deptid 
                        WHERE l.status = 'Active' AND d.deptid = %s", GetSQLValueString($deptid, "int"));


    $query_info = sprintf("SELECT deptname as name
                        FROM department
                        WHERE deptid = %s", GetSQLValueString($deptid, "int"));
}

if (isset($_GET['cid'])) {
    $colid = $_GET['cid'];
    $query_staff = sprintf("SELECT l.lectid, l.fname, l.lname, l.email, l.phone 
                        FROM lecturer l 
                        JOIN department d ON d.deptid = l.deptid 
                        JOIN college c ON c.colid = d.colid 
                        WHERE c.colid = %s", GetSQLValueString($colid, "int"));


    $query_info = sprintf("SELECT colname as name 
                        FROM college
                        WHERE colid = %s", GetSQLValueString($colid, "int"));
}

$staff = mysql_query($query_staff, $tams) or die(mysql_error());
$row_staff = mysql_fetch_assoc($staff);
$totalRows_staff = mysql_num_rows($staff);


$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$name = $row_info['name'];
?>
<!doctype html>
<html>
    <?php include INCPATH."/header.php" ?>
    <body  data-layout-sidebar="fixed" data-layout-topbar="fixed">
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
                                        <?php echo $name ?>
                                    </h3>
                                </div>
                                <div class="box-content">    
                                    <div class="row-fluid">
                                        
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>S/N</th>
                                                    <th>Staff Id</th>
                                                    <th>Name</th>
                                                    <th>Phone</th>
                                                    <th>Email</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if ($totalRows_staff > 0) {
                                                    $i = 1;
                                                    do {
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $i++; ?></td>
                                                            <td>
                                                                <a href="../../staff/profile.php?lid=<?php echo $row_staff['lectid'] ?>">
                                                                <?php echo $row_staff['lectid'] ?>
                                                                </a>
                                                            </td>
                                                            <td>
                                                            <?php echo "{$row_staff['fname']} {$row_staff['lname']}"; ?>
                                                            </td>
                                                            <td><?php echo (isset($row_staff['phone'])) ? $row_staff['phone'] : '-'; ?></td>
                                                            <td><?php echo (isset($row_staff['email'])) ? $row_staff['email'] : '-'; ?></td>
                                                        </tr>
                                                        <?php
                                                    } while ($row_staff = mysql_fetch_assoc($staff));
                                                } else {
                                                    ?>
                                                    <tr>
                                                        <td colspan="5">No record available!</td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
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