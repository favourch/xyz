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


mysql_select_db($database_tams, $tams);
$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);

$colname_dept = "-1";
if (isset($_GET['cid'])) {
    $colname_dept = $_GET['cid'];
}
mysql_select_db($database_tams, $tams);
$query_dept = ( isset($_GET['filter']) ) ? sprintf("SELECT deptid, deptname, deptcode FROM department WHERE `continue`='Yes'  AND colid=%s ORDER BY deptname ASC", GetSQLValueString($colname_dept, "int")) : "SELECT deptid, deptname, deptcode FROM department WHERE `continue`='Yes' ORDER BY deptname ASC";
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$staff = array();
$student = array();
do {
    $query_studstat = sprintf("SELECT count(stdid) as cstud FROM student s, programme p WHERE p.progid = s.progid AND p.deptid=%s", GetSQLValueString($row_dept['deptid'], "int"));
    $studstat = mysql_query($query_studstat, $tams) or die(mysql_error());
    $row_studstat = mysql_fetch_assoc($studstat);

    $student[] = ( $row_studstat['cstud'] != 0) ? $row_studstat['cstud'] : "-";

    $query_stafstat = sprintf("SELECT count(lectid) as cstaf FROM lecturer l WHERE l.deptid=%s",GetSQLValueString($row_dept['deptid'], "int"));
    $stafstat = mysql_query($query_stafstat, $tams) or die(mysql_error());
    $row_stafstat = mysql_fetch_assoc($stafstat);

    $staff[] = ( $row_stafstat['cstaf'] != 0 ) ? $row_stafstat['cstaf'] : "-";
    
}while ($row_dept = mysql_fetch_assoc($dept));

$rows = mysql_num_rows($dept);
if ($rows > 0) {
    mysql_data_seek($dept, 0);
    $row_dept = mysql_fetch_assoc($dept);
}


$name = "The University";

if (isset($_GET['filter'])) {
    do {
        if ($_GET['cid'] == $row_col['colid'])
            $name = $row_col['coltitle'];
    } while ($row_col = mysql_fetch_assoc($col));
    $rows = mysql_num_rows($col);
    if ($rows > 0) {
        mysql_data_seek($col, 0);
        $row_col = mysql_fetch_assoc($col);
    }
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
                                <a href="department.php">Department</a>
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
                                        <?= $department_name?> in the <?= $institution?>
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <form action="#" method="POST" class='form-horizontal'>
                                        <div class="control-group">
                                            <label for="select" class="control-label">Filter by <?= $college_name?></label>
                                            <div class="controls">
                                                <select name="cid" class=' input-large' onchange="colFilter(this)">
                                                    <option value="-1">---Select a <?= $college_name?>---</option>
                                                    <?php
                                                    $rows = mysql_num_rows($col);
                                                    if ($rows > 0) {
                                                        mysql_data_seek($col, 0);
                                                        $row_col = mysql_fetch_assoc($col);
                                                    }
                                                    $value = ( isset($_GET['cid']) ) ? $_GET['cid'] : "";
                                                    do {
                                                        ?>
                                                        <option value="<?php echo $row_col['colid'] ?>"<?php if (!(strcmp($row_col['colid'], $value))) {
                                                        echo "selected=\"selected\"";
                                                    } ?>><?php echo $row_col['coltitle'] ?></option>
                                                        <?php
                                                    }
                                                    while ($row_col = mysql_fetch_assoc($col));
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </form>
                                    <?php if ($totalRows_dept > 0) { ?>
                                    <table class="table dataTable dataTable-scroll-x">                                                                                                                            
                                        <thead>
                                            <tr>
                                                <th colspan="">List of Departments</th>
                                                <th colspan="">
                                                    <?php $access = array(3);if( in_array(getAccess(),$access) && ( getAccess() == 2 && $_SESSION['cid'] == $row_dept['colid'] ) || ( getAccess() == 3 && $_SESSION['did'] == $row_dept['deptid'] )){?>
                                                    Action
                                                    <?php } ?>
                                                    <?php if( getAccess() < 4 && getAccess() > 0){?>
                                                    <span style="float:right; width:99px; text-align:center">
                                                        No. of Students
                                                    </span>
                                                    <span style="float:right; width:99px; text-align:center">
                                                        No. of Staff
                                                    </span>
                                                    <?php }?>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $count = 0;
                                            do {
                                            ?>
                                            <tr>
                                                <td><a href="department.php?did=<?= $row_dept['deptid'];?>"><?= $row_dept['deptname'] ?> </a></td>
                                                <td>
                                                    <?php $access = array(3);if( in_array(getAccess(),$access) && ( getAccess() == 2 && $_SESSION['cid'] == $row_dept['colid'] ) || ( getAccess() == 3 && $_SESSION['did'] == $row_dept['deptid'] )){?>
                                                    <a href="deptedit.php?did=<?php echo $row_dept['deptid'];?><?php  if( isset($_GET['cid']) )echo "&cid=".$_GET['cid'];?>"><i class="icon-cogs"> Edit</a>
                                                    <?php } ?>
                                                    <?php if( getAccess() < 4 && getAccess() > 0){?>
                                                    <span style="float:right; width:99px; text-align:center">
                                                        <?php echo $student[$count] ?>
                                                    </span>
                                                    <span style="float:right; width:99px; text-align:center">
                                                        <?php echo $staff[$count] ?>
                                                    </span>
                                                    <?php }?>
                                                </td>
                                            </tr> 
                                            <?php $count++; } while ($row_dept = mysql_fetch_assoc($dept)); ?>
                                        </tbody>
                                    </table>
                                    <?php } else{?>
                                    <div class="alert alert-danger"> No record Available</div>
                                    <?php }?>
                                </div>
                            </div>
                        </div>
                        <p>&nbsp;</p>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH."/footer.php" ?>
    </body>
</html>

