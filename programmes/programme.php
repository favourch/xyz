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


$colname_prog = "-1";
if (isset($_GET['pid'])) {
    $colname_prog = $_GET['pid'];
}
mysql_select_db($database_tams, $tams);
$query_prog = sprintf("SELECT p.*, d.colid FROM programme p, department d "
                    . "WHERE d.deptid = p.deptid "
                    . "AND progid=%s",
                    GetSQLValueString($colname_prog, "int"));
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$colname_progcrs = "-1";
if (isset($row_prog['deptid'])) {
    $colname_progcrs = $row_prog['deptid'];
}

mysql_select_db($database_tams, $tams);
$query_progcrs = sprintf("SELECT c.csid, c.csname "
                        . "FROM course c, department_course dc "
                        . "WHERE c.csid = dc.csid "
                        . "AND c.deptid = %s "
                        . "AND dc.progid=%s "
                        . "AND c.type <> 'General'",
                        GetSQLValueString($colname_progcrs, "int"), 
                        GetSQLValueString($colname_prog, "int"));
$progcrs = mysql_query($query_progcrs, $tams) or die(mysql_error());
$row_progcrs = mysql_fetch_assoc($progcrs);
$totalRows_progcrs = mysql_num_rows($progcrs);

mysql_select_db($database_tams, $tams);
$query_dept = "SELECT deptid, deptname "
            . "FROM department "
            . "ORDER BY deptname ASC";
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);




if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}
$deptname = "";




?>
<!doctype html>
<html>
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
        <?php include INCPATH."/top_nav_bar_index.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH."/side_nav_bar_login.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH."/page-header.php" ?>
                    <div class="breadcrumbs">
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
                    </div>

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        <?= $row_prog['progname'];?>
                                    </h3>
                                    <?php $access = [1,2,3];if( in_array(getAccess(),$access) && ( getAccess() == 1 || getAccess() == 2 && getSessionValue('cid') == $row_dept['colid'] ) || ( getAccess() == 3 && getSessionValue('did') == $row_dept['deptid'] ) ){?>
                                        <ul class="tabs pull-right form">
                                            <li>                             
                                                <a class="btn btn-green btn-small" href="../programme/progedit.php?pid=<?php echo $colname_prog;?>"><i class="icon-cogs"> </i> Edit</a>                       
                                            </li>
                                        </ul>
                                    <?php }?>
                                </div>
                                <div class="box-content">
                                    
                                    <p>
                                        <?php echo $row_prog['page_up']; ?>
                                        For the award of a <?php echo $degree?> degree in  <?php echo $row_prog['progname']; ?>, prospective Students are required to attend lectures and be examined in the courses listed below.
                                        Apart from the departmental courses, students are expected to offer some courses in other departments within their college and some general <a href="../course/generalcourse.php">university course</a>.
                                    </p>
                                    
                                    <?php if ($totalRows_progcrs > 0){?>
                                    
                                    <table class="table table-hover tale-nomargin table-striped">
                                        <thead>
                                            <tr>
                                                <th colspan="2">List of Courses</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php do { ?>
                                                <tr>
                                                    <td><a href="../course/course.php?csid=<?php echo $row_progcrs['csid']?>&pid=<?php echo $colname_prog;?>"><?php echo ucwords(strtolower($row_progcrs['csname'])); ?></a></td>
                                                </tr>
                                            <?php }
                                            while ($row_progcrs = mysql_fetch_assoc($progcrs)); ?>
                                        </tbody>  
                                    </table>
                                    
                                    <?php }else{?>
                                    
                                    <div class="alert alert-danger"> No <?= $college_name; ?> created yet </div>
                                    <?php }?>
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

