<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "1,20";
check_auth($auth_users, $site_root."/admin");

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
    $updateSQL = sprintf("UPDATE college SET colname=%s, colcode=%s, coltitle=%s, remark=%s WHERE colid=%s", GetSQLValueString($_POST['colname'], "text"), GetSQLValueString($_POST['colcode'], "text"), GetSQLValueString($_POST['coltitle'], "text"), GetSQLValueString($_POST['remark'], "text"), GetSQLValueString($_POST['colid'], "int"));

    $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());

    $updateGoTo = "college.php";
    if ($Result1)
        $updateGoTo = ( isset($_GET['success']) ) ? $updateGoTo : $updateGoTo . "?success";
    else
        $updateGoTo = ( isset($_GET['error']) ) ? $updateGoTo : $updateGoTo . "?error";

    if (isset($_SERVER['QUERY_STRING'])) {
        $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
        $updateGoTo .= $_SERVER['QUERY_STRING'];
    }
    header(sprintf("Location: %s", $updateGoTo));
}

$colname_editcol = "-1";
if (isset($_GET['cid'])) {
    $colname_editcol = $_GET['cid'];
}

$query_editcol = sprintf("SELECT * FROM college WHERE colid = %s", GetSQLValueString($colname_editcol, "int"));
$editcol = mysql_query($query_editcol, $tams) or die(mysql_error());
$row_editcol = mysql_fetch_assoc($editcol);
$totalRows_editcol = mysql_num_rows($editcol);

$maxRows_college = 10;
$pageNum_college = 0;
if (isset($_GET['pageNum_college'])) {
    $pageNum_college = $_GET['pageNum_college'];
}
$startRow_college = $pageNum_college * $maxRows_college;

$query_college = "SELECT colid, colname, colcode FROM college";
$query_limit_college = sprintf("%s LIMIT %d, %d", $query_college, $startRow_college, $maxRows_college);
$college = mysql_query($query_limit_college, $tams) or die(mysql_error());
$row_college = mysql_fetch_assoc($college);

if (isset($_GET['totalRows_college'])) {
    $totalRows_college = $_GET['totalRows_college'];
}
else {
    $all_college = mysql_query($query_college);
    $totalRows_college = mysql_num_rows($all_college);
}
$totalPages_college = ceil($totalRows_college / $maxRows_college) - 1;


if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}
$deptname = "";

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
                    </div>
                    <br/>-->
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Edit <?php echo $row_editcol['colname']; ?>
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post">
                                        <div class="control-group">
                                            <label class="control-label" for="textfield"><?php echo $college_name; ?> Name </label>
                                            <div class="controls">
                                                <input name="colname" type="text"  class="input-xxlarge" required="" value="<?php echo htmlentities($row_editcol['colname'], ENT_COMPAT, 'utf-8'); ?>"/>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield"><?php echo $college_name; ?> Code</label>
                                            <div class="controls">
                                                <input name="colcode" type="text" class="input-medium"  readonly="" value="<?php echo htmlentities($row_editcol['colcode'], ENT_COMPAT, 'utf-8'); ?>" />
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield"><?php echo $college_name; ?> Title</label>
                                            <div class="controls">
                                                <input type="text" name="coltitle" class="input-large" required="" value="<?php echo htmlentities($row_editcol['coltitle'], ENT_COMPAT, 'utf-8'); ?>"/>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Remark </label>
                                            <div class="controls">
                                                <textarea name="remark" class="input-xlarge"><?= htmlentities($row_editcol['remark'], ENT_COMPAT, 'utf-8'); ?></textarea>
                                            </div>
                                        </div>
                                        <input type="hidden" name="colid" value="<?= $row_editcol['colid']; ?>" />
                                        <input type="hidden" name="MM_update" value="form1" />
                                        <div class="form-actions">
                                            <input type="submit" value="Update <?= $college_name; ?>" class="btn btn-primary" >
                                            <button class="btn" type="button">Cancel</button>
                                        </div>
                                    </form>
                                    
                                    <p>&nbsp;</p>
                                    <table class="table table-condensed table-condensed table-striped">
                                        <thead>
                                            <tr>
                                                <th width="5%">Code</th>
                                                <th width="80" class="colspace">Name</th>
                                                <th width="15%">&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($totalRows_college > 0) { // Show if recordset not empty ?>
                                                <?php do { ?>
                                                    <tr>
                                                        <td><?php echo $row_college['colcode']; ?></td>
                                                        <td><a href="../../college/college.php?cid=<?= $row_college['colid'] ?>"><?php echo $row_college['colname']; ?></a></td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-cogs"></i><span class="caret"></span></a>
                                                                <ul class="dropdown-menu">
                                                                    <li>
                                                                        <a href="../department/?cid=<?php echo $row_college['colid']; ?>">Add Department</a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="college.php?cid=<?php echo $row_college['colid']; ?>">Edit</a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="#">Delete</a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php }
                                                while ($row_college = mysql_fetch_assoc($college)); ?>
<?php } // Show if recordset not empty   ?>
                                        </tbody>

                                    </table>
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

