<?php
if (!isset($_SESSION)) {
    session_start();
}

//echo var_dump($_SESSION); die();
require_once('../path.php');

//Fetch all curruculum 
$curr_SQL = "SELECT * FROM curriculum";
$curr = mysql_query($curr_SQL, $tams) or die(mysql_error());
$row_curr = mysql_fetch_assoc($curr);



$query_dept = ( isset($_GET['cid']) ) ? "SELECT deptid, deptname FROM department WHERE `continue`='Yes' AND colid = " . $_GET['cid'] . " ORDER BY deptname ASC" : "SELECT deptid, deptname FROM department WHERE deptid=0 ORDER BY deptname ASC";
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$courses = "";
$totalRows_courses = "";
if (isset($_GET['filter']) && $_GET['filter'] != "col") {
    $query_courses = createFilter("course");
    $courses = mysql_query($query_courses, $tams) or die(mysql_error());
    $row_courses = mysql_fetch_assoc($courses);
    $totalRows_courses = mysql_num_rows($courses);
}

$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);

$filtername = "The University";
if (isset($_GET['filter'])) {
    if ($_GET['filter'] == "dept" || ( $_GET['filter'] == "cat" && isset($_GET['did']) ))
        do {
            if ($_GET['did'] == $row_dept['deptid'])
                $filtername = $row_dept['deptname'];
        } while ($row_dept = mysql_fetch_assoc($dept));
    elseif ($_GET['filter'] == "col" || ( $_GET['filter'] == "cat" && isset($_GET['cid']) ))
        do {
            if ($_GET['cid'] == $row_col['colid'])
                $filtername = $row_col['coltitle'];
        } while ($row_col = mysql_fetch_assoc($col));

    $filtername = ( isset($filtername) ) ? $filtername : "The University";
}

//Fill an array with valid lecturer ids to view registered students.if(){}
$did = "-1";
if (isset($_GET['did'])) {
    $did = $_GET['did'];
}

$cid = "-1";
if (isset($_GET['cid'])) {
    $cid = $_GET['cid'];
}
$acl = "-1";
if (getAccess() == 1 || getAccess() == 20 || getAccess() == 21 || (getAccess() == 2 && getSessionValue('cid') == $cid) || (getAccess() == 3 && getSessionValue('did') == $did) || ((getAccess() == 4 || getAccess() == 5 || getAccess() == 6) && getSessionValue('did') == $did)) {
    $acl = getSessionValue('lid');
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
                                        Courses in the <?= $institution?>
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <form >
                                        <div class="row-fluid">
                                            <div class="span4">
                                                <div class="control-group">
                                                    <label for="select" class="control-label">Filter by <?= $college_name ?></label>
                                                    <div class="controls">
                                                        <select name="cid" class='input-large' onchange="colFilter(this)">
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
                                                                <option value="<?php echo $row_col['colid'] ?>"<?php
                                                                if (!(strcmp($row_col['colid'], $value))) {
                                                                    echo "selected=\"selected\"";
                                                                }
                                                                ?>><?php echo $row_col['coltitle'] ?></option>
                                                                        <?php
                                                                    }
                                                                    while ($row_col = mysql_fetch_assoc($col));
                                                                    ?>
                                                        </select>
                                                    </div>
                                                </div> 
                                            </div>
                                            <div class="span4">
                                                <div class="control-group">
                                                    <label for="select" class="control-label">Filter by Department</label>
                                                    <div class="controls">
                                                        <select name="dept" id="dept"  class="input-large" onchange="deptFilter(this)">
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
                                                                <option value="<?php echo $row_dept['deptid'] ?>"<?php
                                                                        if (isset($_GET['did']))
                                                                            if (!(strcmp($row_dept['deptid'], $_GET['did']))) {
                                                                                echo "selected=\"selected\"";
                                                                            }
                                                                        ?>><?php echo $row_dept['deptname'] ?></option>
                                                                            <?php
                                                                        }
                                                                        while ($row_dept = mysql_fetch_assoc($dept));
                                                                        ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="span4">
                                                <div class="control-group">
                                                    <label for="select" class="control-label">Filter by Curriculum</label>
                                                    <div class="controls">
                                                        <select name="cur" id="cur"  class="input-large" onchange="curFilter(this)">
                                                            <option value="-1">--Choose Curriculum--</option> 
                                                            <?php do{ ?>
                                                            <option value="<?= $row_curr['curid']?>" <?= (isset($_GET['curid']) && $_GET['curid'] == $row_curr['curid'])? "selected": ""?> ><?= $row_curr['curname']?></option>
                                                            <?php }while($row_curr = mysql_fetch_assoc($curr)); ?>            
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <p>
                                        Apart from the departmental courses, 
                                        students are expected to offer some courses 
                                        in other departments within their college 
                                        and some general <a href="../course/generalcourse.php"><?= $institution?> course</a>.
                                    </p>
                                    <?php if ($totalRows_courses > 0) { ?>
                                    <table class="table table-condensed table-hover table-striped">                                                                                                                            
                                        <thead>
                                            <tr>
                                                <th colspan="3">List of <?= $department_name?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $count = 0;
                                            do {
                                            ?>
                                            <tr>
                                                <td><?php echo $row_courses['csid']; ?></td>
                                                <td>
                                                    <a href="course.php?csid=<?php echo $row_courses['csid'];?>"><?php echo $row_courses['csname']; ?></a>
                                                </td>
                                                <td>
                                                    <?php if (!strcmp(getSessionValue('lid'), $acl)) { ?> 
                                                        <a href="../registration/coursereg.php?csid=<?php echo $row_courses['csid'] ?>&curid=<?php echo $_GET['curid'] ?>">Registered Students  (<?php echo $row_courses['tot_reg'] ?>) </a>
                                                    <?php } ?>
                                                </td>
                                            </tr> 
                                            <?php } while ($row_courses = mysql_fetch_assoc($courses)); ?>
                                            <?php mysql_free_result($courses); ?>
                                        </tbody>
                                    </table>
                                    <?php } else{?>
                                    <div class="alert alert-danger"> No record available use the filter  </div>
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

