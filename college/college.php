<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



/*-----------------------------------------------*
 * 
 * Logic of the College/college.php Page 
 *
 **------------------------------------------------
 */


$maxRows_dept = 10;
$pageNum_dept = 0;
if (isset($_GET['pageNum_dept'])) {
    
    $pageNum_dept = $_GET['pageNum_dept'];
}
$startRow_dept = $pageNum_dept * $maxRows_dept;



$colname_dept = "-1";
if (isset($_GET['cid'])) {
    
    $colname_dept = $_GET['cid'];
}


mysql_select_db($database_tams, $tams);
$query_dept = sprintf("SELECT deptid, deptname, colid "
                    . "FROM department "
                    . "WHERE `continue`='Yes' AND colid = %s "
                    . "ORDER BY deptname ASC",
                    GetSQLValueString($colname_dept, "int"));
$query_limit_dept = sprintf("%s LIMIT %d, %d", $query_dept, $startRow_dept, $maxRows_dept);
$dept = mysql_query($query_limit_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);


if (isset($_GET['totalRows_dept'])) {
    
    $totalRows_dept = $_GET['totalRows_dept'];
}
else {
    
    $all_dept = mysql_query($query_dept);
    $totalRows_dept = mysql_num_rows($all_dept);
}
$totalPages_dept = ceil($totalRows_dept / $maxRows_dept) - 1;

$colname_col = "-1";
if (isset($_GET['cid'])) {
    $colname_col = $_GET['cid'];
}

mysql_select_db($database_tams, $tams);
$query_col = sprintf("SELECT colid, colname, colcode, coltitle, page_up, page_down "
                    . "FROM college "
                    . "WHERE colid = %s", 
                    GetSQLValueString($colname_col, "int"),
                    GetSQLValueString($colname_col, "int"));
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);


if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true"))
    doLogout($site_root);





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
                                <a href="../index.php">Home</a> <i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="index.php">College</a> <i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="#"><?= $row_col['coltitle'];?></a>
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
                                        <?= $row_col['colname'];?>
                                    </h3>
                                    <?php $access = array(1,2);if( in_array(getAccess(),$access) && ( getAccess() == 1 || getSessionValue('cid') == $colname_col) ){?>
                                        <ul class="tabs pull-right form">
                                            <li>                             
                                                <a class="btn btn-green btn-small" href="coledit.php?cid=<?php echo $colname_col;?>"><i class="icon-cogs"> </i> Edit</a>                       
                                            </li>
                                        </ul>
                                    <?php }?>
                                </div>
                                <div class="box-content">  
                                    <p>
                                        <?= $row_col['page_up'];?>
                                    </p>
                                    
                                     <?php if ($totalRows_dept > 0) { ?>
                                    <table class="table table-hover tale-nomargin table-striped">
                                        <thead>
                                            <tr>
                                                <th colspan="2">List of Department</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php do { ?>
                                                <tr>
                                                    <td><a href="../department/department.php?did=<?php echo $row_dept['deptid']?>"><?= $row_dept['deptname']; ?></a></td>
                                                    <td>
                                                       <?php $access = array(1,2,3);if(in_array(getAccess(),$access) && ( getAccess() == 1 || getAccess() == 2 && getSessionValue('cid') == $row_dept['colid'] ) || ( getAccess() == 3 && getSessionValue('cid') == $row_dept['deptid'] ) ){?>
                                                        <a href="../department/deptedit.php?did=<?php echo $row_dept['deptid']?>" class="btn btn-small"> <i class="icon-cogs"></i> Edit</a>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php } while ($row_dept = mysql_fetch_assoc($dept)); ?>
                                        </tbody>  
                                    </table>
                                    
                                    <p>
                                        <?php echo $row_col['page_down'];?>
                                    </p>
                                    <?php }else{?>
                                    
                                    <div class="alert alert-danger"> No record found </div>
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

