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
$query_cat = sprintf("SELECT catid, catname FROM category WHERE type=1");
$cat = mysql_query($query_cat, $tams) or die(mysql_error());
$row_cat = mysql_fetch_assoc($cat);
$totalRows_cat = mysql_num_rows($cat);

$colname_courses = "-1";
if (isset($row_cat['catid'])) {
    $colname_courses = $row_cat['catid'];
}

if (isset($_GET['catid'])) {
    $colname_courses = $_GET['catid'];
}

mysql_select_db($database_tams, $tams);
$query_courses = sprintf("SELECT csid, csname FROM course c, category ct WHERE c.catid = ct.catid AND ct.catid = %s ORDER BY csid ASC", GetSQLValueString($colname_courses, "int"));
$courses = mysql_query($query_courses, $tams) or die(mysql_error());
$row_courses = mysql_fetch_assoc($courses);
$totalRows_courses = mysql_num_rows($courses);


/* mysql_select_db($database_tams, $tams);
  $query_col = "SELECT colid, coltitle FROM college";
  $col = mysql_query($query_col, $tams) or die(mysql_error());
  $row_col = mysql_fetch_assoc($col);
  $totalRows_col = mysql_num_rows($col);

  $filtername = "The University";
  if( isset($_GET['filter'])){
  if( $_GET['filter'] == "dept" || ( $_GET['filter'] == "cat" && isset($_GET['did']) ) )
  do {
  if( $_GET['did'] == $row_dept['deptid'] )
  $filtername = $row_dept['deptname'];
  } while ($row_dept = mysql_fetch_assoc($dept));
  elseif( $_GET['filter'] == "col" || ( $_GET['filter'] == "cat" && isset($_GET['cid']) ) )
  do {
  if( $_GET['cid'] == $row_col['colid'] )
  $filtername = $row_col['coltitle'];
  } while ($row_col = mysql_fetch_assoc($col));

  $filtername = ( isset( $filtername ) ) ? $filtername : "The University";
  }
 */

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
if (getAccess() == 20 || getAccess() == 21 || getAccess() == 28 || (getAccess() == 2 && getSessionValue('cid') == $cid) || (getAccess() == 3 && getSessionValue('did') == $did) || (getAccess() == 4) || (( getAccess() == 5) && getSessionValue('did') == $did)) {
    $acl = getSessionValue('lid');
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
                                        General University Courses
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <form action="#" method="POST" class='form-horizontal'>
                                        <div class="control-group">
                                            <label for="select" class="control-label">Filter by Category</label>
                                            <div class="controls">
                                                <select class="input-xlarge" name="catid" id="catid" onchange="catFilter(this)">
                                                    <option value="-1" <?php if (isset($_GET['catid'])) if (!(strcmp(-1, $_GET['catid']))) {echo "selected=\"selected\"";} ?>>---Select A Category---</option>
                                                    <?php
                                                    do {
                                                        ?>
                                                        <option value="<?php echo $row_cat['catid'] ?>" <?php if (isset($_GET['catid'])) if (!(strcmp($row_cat['catid'], $_GET['catid']))) {
                                                            echo "selected=\"selected\"";
                                                        } ?>><?php echo $row_cat['catname'] ?></option>
                                                    <?php
                                                    }
                                                    while ($row_cat = mysql_fetch_assoc($cat));
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </form>
                                    <?php if ($totalRows_courses > 0) { ?>
                                    <table class="table table-condensed tabe-striped table-hover">                                                                                                                            
                                        <thead>
                                            <tr>
                                                <th colspan="3">List of Departments</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $count = 0;
                                            do {
                                            ?>
                                            <tr>
                                                <td><?php echo $row_courses['csid']; ?></td>
                                                <td><a href="course.php?csid=<?php echo $row_courses['csid']; ?>"><?php echo $row_courses['csname']; ?></a></td>
                                                <td>
                                                    <?php if(!strcmp(getSessionValue('lid'), $acl) ){ ?> 
                                                    <a href="../registration/coursereg.php?csid=<?php echo $row_courses['csid'] ?>">Registered Students </a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php } while ($row_courses = mysql_fetch_assoc($courses)); ?>
                                            <?php mysql_free_result($courses); ?>
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
<?php
mysql_free_result($cat);
?>
