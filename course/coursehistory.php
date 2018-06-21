<?php
require_once('../Connections/tams.php');

if (!isset($_SESSION)) {
    session_start();
}

require_once('../param/param.php');
require_once('../functions/function.php');
require('../param/site.php');

/* -----------------------------------------------*
 * 
 * Logic of the College/coledit.php Page 
 *
 * *------------------------------------------------
 */





$colname_courselist = "-1";
if (isset($_GET['csid'])) {
    $colname_courselist = $_GET['csid'];
}
mysql_select_db($database_tams, $tams);
$query_courselist = sprintf("SELECT lectid1, lectid2, csname, c.deptid, sesname FROM teaching t, course c, session s WHERE s.sesid = t.sesid AND c.csid = t.csid AND t.csid = %s ORDER BY sesname DESC", GetSQLValueString($colname_courselist, "text"));
$courselist = mysql_query($query_courselist, $tams) or die(mysql_error());
$row_courselist = mysql_fetch_assoc($courselist);
$totalRows_courselist = mysql_num_rows($courselist);

$colname_lect = "-1";
if (isset($row_courselist['deptid'])) {
    $colname_lect = $row_courselist['deptid'];
}
mysql_select_db($database_tams, $tams);
$query_lect = sprintf("SELECT lectid, title, fname, lname FROM lecturer WHERE deptid = %s", GetSQLValueString($colname_lect, "int"));
$lect = mysql_query($query_lect, $tams) or die(mysql_error());
$row_lect = mysql_fetch_assoc($lect);
$totalRows_lect = mysql_num_rows($lect);

$lectlist = array();
$count = 0;
do {
    $lectlist[$row_lect['lectid']] = $row_lect['title'] . " " . $row_lect['lname'] . ", " . $row_lect['fname'];

    $count++;
}
while ($row_lect = mysql_fetch_assoc($lect));
if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}



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
                                        Teaching History for <?php echo $colname_courselist?>
                                    </h3>
                                </div>
                                <div class="box-content nopadding">
                                    <?php if ($totalRows_courselist > 0) { ?>
                                    <table class='table table-condensed table-striped table-hover'>
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Session</th>
                                                <th>Convener</th>
                                                <th>Assistant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                                <?php $i=1; do { ?>
                                                    <tr>
                                                        <td><?= $i++ ?></td>
                                                        <td><?php echo $row_courselist['sesname'] ?></td>
                                                        <td>        	
                                                            <?php
                                                            if (isset($lectlist[$row_courselist['lectid1']]))
                                                                echo "<a href='../staff/profile.php?lid={$row_courselist['lectid1']}'>" . $lectlist[$row_courselist['lectid1']] . '</a>';
                                                            else
                                                                echo "-"
                                                                ?>

                                                        </td>
                                                        <td>
                                                            <?php
                                                            if (isset($lectlist[$row_courselist['lectid2']]))
                                                                echo "<a href='../staff/profile.php?lid={$row_courselist['lectid2']}'>" . $lectlist[$row_courselist['lectid2']] . '</a>';
                                                            else
                                                                echo "-"
                                                                ?>
                                                        </td>
                                                    </tr>
                                                <?php }while ($row_courselist = mysql_fetch_assoc($courselist)); ?>
                                        </tbody>    
                                    </table>
                                    <?php }else { ?>
                                    <div class="alert alert-danger">No Record Found</div>
                                    <?php } ?>
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

