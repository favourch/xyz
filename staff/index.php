<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "1,2,3,4,5,6,20,21,22,23,24,27";
check_auth($auth_users, $site_root);

$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);

$query_dept = "SELECT deptid, deptname FROM department ORDER BY deptname ASC";
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$query_staff = ( isset( $_GET['filter'] ) )? createFilter("lect"): "SELECT title, lectid,fname, lname, email FROM lecturer WHERE status='Active' AND lectid=0";
$staff = mysql_query($query_staff, $tams) or die(mysql_error());
$row_staff = mysql_fetch_assoc($staff);
$totalRows_staff = mysql_num_rows($staff);
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
                                        Staff in the <?= $institution?>
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <form class="form form-vertical">
                                        <div class="row-fluid">
                                            <div class="span3">
                                                <div class="control-group">
                                                    <label for="select" class="control-label">Filter by <?= $college_name ?></label>
                                                    <div class="controls">
                                                        <select name="col2" id="col" onchange="colFilter(this)">
                                                            <option value="-1" <?php if (isset($_GET['cid'])) if (!(strcmp(-1, $_GET['cid']))) {
                                                                    echo "selected=\"selected\"";
                                                                } ?>>---Select A <?= $college_name ?>---</option>
                                                            <?php
                                                            $rows = mysql_num_rows($col);
                                                            if ($rows > 0) {
                                                                mysql_data_seek($col, 0);
                                                                $row_col = mysql_fetch_assoc($col);
                                                            }
                                                            do {
                                                                ?>
                                                                <option value="<?php echo $row_col['colid'] ?>" <?php if (isset($_GET['cid'])) if (!(strcmp($row_col['colid'], $_GET['cid']))) {
                                                                    echo "selected=\"selected\"";
                                                                } ?>><?php echo $row_col['coltitle'] ?></option>
                                                            <?php
                                                        }
                                                        while ($row_col = mysql_fetch_assoc($col));
                                                        ?>
                                                        </select>
                                                    </div>
                                                </div> 
                                            </div>
                                            <div class="span3">
                                                <div class="control-group">
                                                    <label for="select" class="control-label">Filter by Department</label>
                                                    <div class="controls">
                                                        <select name="dept2" id="dept2" onchange="deptFilter(this)">
                                                            <option value="-1" <?php if (isset($_GET['did'])) if (!(strcmp(-1, $_GET['did']))) {
                                                                echo "selected=\"selected\"";
                                                            } ?>>---Select A Department---</option>
                                                            <?php
                                                            $rows = mysql_num_rows($dept);
                                                            if ($rows > 0) {
                                                                mysql_data_seek($dept, 0);
                                                                $row_dept = mysql_fetch_assoc($dept);
                                                            }
                                                            do {
                                                                ?>
                                                                <option value="<?php echo $row_dept['deptid'] ?>"<?php if (isset($_GET['did'])) if (!(strcmp($row_dept['deptid'], $_GET['did']))) {
                                                                    echo "selected=\"selected\"";
                                                                } ?>><?php echo $row_dept['deptname'] ?></option>
                                                            <?php
                                                        }
                                                        while ($row_dept = mysql_fetch_assoc($dept));
                                                        ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </form>
                                    
                                    <?php if ($totalRows_staff > 0){?>
                                    <div class="row-fluid">
                                        <table class="table table-condensed table-striped">
                                                <thead>
                                                    <tr>
                                                        <th colspan="2">List of Staff </th>
                                                        <th>
                                                            <i class="glyphicon-user"></i>
                                                            <span class="label label-lightred"><?= $totalRows_staff . " staff" ?></span>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php do { ?>
                                                        <tr>
                                                            <td width="224"><a href="profile.php?lid=<?php echo $row_staff['lectid']; ?>"><?php echo $row_staff['title'] . " " . $row_staff['lname'] . ", " . $row_staff['fname']; ?></a></td>
                                                            <td width="287"><a href="mailto:<?php echo $row_staff['email']; ?>"><?php echo $row_staff['email']; ?></a></td>
                                                            <td width="157"><a href="../staff/teaching_history.php?lid=<?php echo $row_staff['lectid'] ?>">Teaching History</a>
                                                        </tr>
                                                    <?php }
                                                    while ($row_staff = mysql_fetch_assoc($staff));
                                                    ?>
                                                </tbody>  
                                            </table>
                                    </div>
                                    <?php }else{?>
                                    
                                    <div class="alert alert-danger"> No <?= $college_name; ?> created yet </div>
                                    <?php }?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH."/footer.php" ?>
    </body>
</html>
<?php
mysql_free_result($dept);

mysql_free_result($col);

mysql_free_result($staff);
?>
