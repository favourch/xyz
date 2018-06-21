<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$colname_course = "-1";
if (isset($_GET['csid'])) {
    $colname_course = $_GET['csid'];
}

$colname_course1 = "-1";
if (isset($_GET['pid'])) {
    $colname_course1 = $_GET['pid'];
}

$query = "SELECT c.*, d.deptname FROM course c, department d WHERE c.deptid = d.deptid AND csid = %s";

//echo $_SERVER['HTTP_REFERER'];
if (isset($_GET['pid']))
    $query = "SELECT c.*, dc.status, dc.unit, d.deptname FROM course c,"
            . "department d, department_course dc "
            . "WHERE c.csid = dc.csid "
            . "AND c.deptid = d.deptid "
            //. "AND dc.progid = ".$colname_course1." AND c.csid = %s";
            . "AND c.csid = %s AND dc.progid = %s";
//$query_course = sprintf($query, GetSQLValueString($colname_course, "text"));

$query_course = sprintf($query, GetSQLValueString($colname_course, "text"), GetSQLValueString($colname_course1, "text"));
$course = mysql_query($query_course, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($course);
$totalRows_course = mysql_num_rows($course);

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
                                        Course Details
                                    </h3>
                                    <ul class="tabs pull-right form">
                                        <li>                             
                                            <a class="btn btn-green btn-small" href="../course/coursehistory.php?csid=<?php echo $row_course['csid']?>"><i class="icon-cogs"> </i> Teaching History</a>                       
                                        </li>
                                    </ul>
                                </div>
                                <div class="box-content nopadding">
                                    <div class="form-horizontal form-bordered">
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Course Code</label>
                                            <div class="controls">
                                                <?= $row_course['csid']; ?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Course Name</label>
                                            <div class="controls">
                                                <?= $row_course['csname']; ?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Semester</label>
                                            <div class="controls">
                                                <?= getSemester($row_course['semester']); ?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Course Type</label>
                                            <div class="controls">
                                                <?= $row_course['type']; ?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Host Department</label>
                                            <div class="controls">
                                                <a href="../department/department.php?did=<?php echo $row_course['deptid'] ?>"><?php echo $row_course['deptname']; ?></a>
                                            </div>
                                        </div>
                                        <?php if( isset( $_GET['pid'] ) ){?>  
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Status </label>
                                            <div class="controls">
                                                <?= $row_course['status']; ?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Unit</label>
                                            <div class="controls">
                                                <?= $row_course['unit']; ?>
                                            </div>
                                        </div>
                                        <?php }?>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Course Content</label>
                                            <div class="controls">
                                                <?= $row_course['cscont']; ?>
                                            </div>
                                        </div>
                                    </div>
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

