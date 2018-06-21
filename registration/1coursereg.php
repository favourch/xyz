<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "2,3,4,5,6";
check_auth($auth_users, $site_root);


$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$colname_crs = "-1";
if (isset($row_sess['sesid'])) {
  $colname_crs = $row_sess['sesid'];
}

if (isset($_GET['sid'])) {
  $colname_crs = $_GET['sid'];
}

$colname1_crs = "-1";
if (isset($_GET['csid'])) {
  $colname1_crs = $_GET['csid'];
}

$query_crs = sprintf("SELECT r.stdid, s.lname, s.fname "
                    . "FROM course_reg r, student s "
                    . "WHERE r.stdid = s.stdid "
                    . "AND r.sesid = %s "
                    . "AND r.csid=%s", 
                    GetSQLValueString($colname_crs, "int"), 
                    GetSQLValueString($colname1_crs, "text"));
$crs = mysql_query($query_crs, $tams) or die(mysql_error());
$row_crs = mysql_fetch_assoc($crs);
$totalRows_crs = mysql_num_rows($crs);
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
                                        Registered Students for <?php echo $_GET['csid']?>
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">

                                        <table width="690">
                                            <tr>
                                                <td colspan="5"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="5">
                                                    Session &nbsp;&nbsp;            
                                                    <select name="sesid" onchange="sesfilt(this)">
                                                        <?php
                                                        do {
                                                            ?>
                                                            <option value="<?php echo $row_sess['sesid'] ?>"<?php if (!(strcmp($row_sess['sesid'], $colname_crs))) {
                                                            echo "selected=\"selected\"";
                                                        } ?>><?php echo $row_sess['sesname'] ?></option>
                                                            <?php
                                                        } while ($row_sess = mysql_fetch_assoc($sess));
                                                        $rows = mysql_num_rows($sess);
                                                        if ($rows > 0) {
                                                            mysql_data_seek($sess, 0);
                                                            $row_sess = mysql_fetch_assoc($sess);
                                                        }
                                                        ?>
                                                    </select>
                                                    &nbsp;&nbsp;
                                            <?php echo $totalRows_crs ?> registered students
                                                </td>
                                            </tr>
<?php if ($totalRows_crs > 0) {  // ?> 
    <?php do { ?>
                                                    <tr>
                                                        <td><a href="../student/profile.php?stid=<?php echo $row_crs['stdid']; ?>"><?php echo $row_crs['stdid']; ?></a></td>
                                                        <td><?php echo $row_crs['lname'] . ", " . $row_crs['fname']; ?></td>
                                                        <td><a href="viewform.php?stid=<?php echo $row_crs['stdid']; ?> " target="_blank">View Form</a></td>
                                                        <td></td>
                                                        <td>&nbsp;</td>
                                                    </tr>
                                            <?php } while ($row_crs = mysql_fetch_assoc($crs)); ?>
                                        <?php } ?>
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