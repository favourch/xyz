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


$colname_dept = "-1";
if (isset($_GET['did'])) {
    $colname_dept = $_GET['did'];
}

mysql_select_db($database_tams, $tams);
$query_dept = sprintf("SELECT * "
                    . "FROM department "
                    . "WHERE deptid = %s",
                    GetSQLValueString($colname_dept, "int"));
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$maxRows_deptprog = 10;
$pageNum_deptprog = 0;
if (isset($_GET['pageNum_deptprog'])) {
    $pageNum_deptprog = $_GET['pageNum_deptprog'];
}
$startRow_deptprog = $pageNum_deptprog * $maxRows_deptprog;

$colname_deptprog = "-1";
if (isset($_GET['did'])) {
    $colname_deptprog = $_GET['did'];
}

mysql_select_db($database_tams, $tams);
$query_deptprog = sprintf("SELECT progid, progname, programme.deptid, colid "
                        . "FROM programme, department "
                        . "WHERE programme.deptid = department.deptid "
                        . "AND programme.deptid = %s", 
                        GetSQLValueString($colname_deptprog, "int"));
$query_limit_deptprog = sprintf("%s LIMIT %d, %d", $query_deptprog, $startRow_deptprog, $maxRows_deptprog);
$deptprog = mysql_query($query_limit_deptprog, $tams) or die(mysql_error());
$row_deptprog = mysql_fetch_assoc($deptprog);

if (isset($_GET['totalRows_deptprog'])) {
    
    $totalRows_deptprog = $_GET['totalRows_deptprog'];
}
else {
    
    $all_deptprog = mysql_query($query_deptprog);
    $totalRows_deptprog = mysql_num_rows($all_deptprog);
}
$totalPages_deptprog = ceil($totalRows_deptprog / $maxRows_deptprog) - 1;

mysql_select_db($database_tams, $tams);
$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);
$collegename = "";


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
                                <a href="department.php">Department</a> <i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="departmentDetails.php">Agricultural Science</a>
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
                                        <?= $row_dept['deptname'];?> <?= $department_name?>
                                    </h3>
                                     <?php $access = array(1,2,3);if( in_array(getAccess(),$access) && ( getAccess() == 1 ||  getAccess() == 2 && getSessionValue('cid') == $row_prog['colid'] ) || ( getAccess() == 3 && getSessionValue('did') == $row_prog['deptid'] ) ){?>
                                        <ul class="tabs pull-right form">
                                            <li>                             
                                                <a class="btn btn-green btn-small" href="../department/deptedit.php?did=<?php echo $colname_dept?>"><i class="icon-cogs"> </i> Edit</a>                       
                                            </li>
                                        </ul>
                                    <?php }?>
                                </div>
                                <div class="box-content">
                                    <p>
                                        <?php echo $row_dept['page_up'];?>
                                    </p>
                                    
                                    <table class="table table-striped table-condensed">                                                                                                                            
                                        <thead>
                                            <tr>
                                                <th colspan="2">List of <?= $programme_name?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                            <?php 
                                            if ($totalRows_deptprog > 0) {
                                                do { ?>
                                            <tr>
                                                <td>
                                                    <a href="../programme/programme.php?pid=<?php echo $row_deptprog['progid']?>"><?php echo $row_deptprog['progname']; ?> </a>
                                                </td>
                                                <td>
                                                    <?php $access = array(1,2,3);if( in_array(getAccess(),$access) && ( getAccess() == 1 ||  getAccess() == 2 && getSessionValue('cid') == $row_dept['colid'] ) || ( getAccess() == 3 && getSessionValue('did') == $row_dept['deptid'] ) ){?>
                                                    <a class="btn btn-small" href="../programme/progedit.php?pid=<?php echo $row_deptprog['progid']?>"><i class="icon-cogs"></i> Edit</a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php } while ($row_deptprog = mysql_fetch_assoc($deptprog)); 
                                            }
                                            ?>
                                            
                                        </tbody>
                                    </table>
                                    <p>
                                        <?php echo $row_dept['page_down'];?>
                                    </p>
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

