<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "1,20";
check_auth($auth_users, $site_root);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
    $updateSQL = sprintf("UPDATE course "
            . "SET csid = %s, csname = %s, semester = %s,"
            . "type = %s, catid = %s, deptid = %s, "
            . "cscont = %s, level = %s, unit = %s, "
            . "status = %s "
            . "WHERE csid = %s",
            GetSQLValueString($_POST['ncsid'], "text"),
            GetSQLValueString($_POST['csname'], "text"),
            GetSQLValueString($_POST['semester'], "text"),
            GetSQLValueString($_POST['type'], "text"),
            GetSQLValueString($_POST['catid'], "int"),
            GetSQLValueString($_POST['deptid'], "int"), 
            GetSQLValueString($_POST['cscont'], "text"),
            GetSQLValueString($_POST['level'], "int"), 
            GetSQLValueString($_POST['unit'], "int"),
            GetSQLValueString($_POST['status'], "text"),
            GetSQLValueString($_POST['csid'], "text"));

    $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());

    $updateGoTo = "index.php";
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

$colname_editcs = "-1";
if (isset($_GET['csid'])) {
    $colname_editcs = $_GET['csid'];
}

$query_editcs = sprintf("SELECT * FROM course WHERE csid = %s", GetSQLValueString($colname_editcs, "text"));
$editcs = mysql_query($query_editcs, $tams) or die(mysql_error());
$row_editcs = mysql_fetch_assoc($editcs);
$totalRows_editcs = mysql_num_rows($editcs);


$query_cat = "SELECT * FROM category";
$cat = mysql_query($query_cat, $tams) or die(mysql_error());
$row_cat = mysql_fetch_assoc($cat);
$totalRows_cat = mysql_num_rows($cat);


$query_dept1 = "SELECT * FROM department";
$dpt = mysql_query($query_dept1, $tams) or die(mysql_error());
$row_dpt = mysql_fetch_assoc($dpt);
$totalRows_dpt = mysql_num_rows($dpt);

$query_dept = "SELECT deptid, deptname FROM department";
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$query_level = "SELECT * FROM level_name";
$level = mysql_query($query_level, $tams) or die(mysql_error());
$row_level = mysql_fetch_assoc($dept);
$totalRows_level = mysql_num_rows($level);

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
<!--                <div class="container-fluid nav-fixed">
                    <?php include INCPATH."/page_header.php" ?>
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
                    <br/>
                    <div class="span6">
                        <?php //statusMsg();?>
                    </div>-->
                    
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Edit <?php echo $_GET['csid'] ?>
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Course Code: </label>
                                                <div class="controls">
                                                    <input name="ncsid" value="<?php echo htmlentities($row_editcs['csid'], ENT_COMPAT, 'utf-8'); ?>" type="text"  class="input-medium" required=""/>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Course Name:</label>
                                                <div class="controls">
                                                    <input name="csname" value="<?php echo htmlentities($row_editcs['csname'], ENT_COMPAT, 'utf-8'); ?>" type="text" class="input-xxlarge"  required="" />
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Semester</label>
                                                <div class="controls">
                                                    <select name="semester"  class="input-medium"  required="">
                                                        <option value="F" <?php if (!(strcmp("F", htmlentities($row_editcs['semester'], ENT_COMPAT, 'utf-8')))) {echo "selected=\"selected\"";} ?>>First</option>
                                                        <option value="S" <?php if (!(strcmp("S", htmlentities($row_editcs['semester'], ENT_COMPAT, 'utf-8')))) {echo "selected=\"selected\"";} ?>>Second</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Course Type:</label>
                                                <div class="controls">
                                                    <select name="type" id="type" required="" class="input-medium">
                                                        <option value="General" <?php if (!(strcmp("General", htmlentities($row_editcs['type'], ENT_COMPAT, 'utf-8')))) {echo "selected=\"selected\"";} ?>>General</option>
                                                        <option value="College" <?php if (!(strcmp("College", htmlentities($row_editcs['type'], ENT_COMPAT, 'utf-8')))) {echo "selected=\"selected\"";} ?>>College</option>
                                                        <option value="Departmental" <?php if (!(strcmp("Departmental", htmlentities($row_editcs['type'], ENT_COMPAT, 'utf-8')))) {echo "selected=\"selected\"";} ?>>Departmental</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Course Category </label>
                                                <div class="controls">
                                                    <select name="catid" id="catid" required="" class="input-medium">
                                                        <?php
                                                        $rows = mysql_num_rows($cat);
                                                        if ($rows > 0) {
                                                            mysql_data_seek($cat, 0);
                                                            $row_cat = mysql_fetch_assoc($cat);
                                                        }
                                                        do {
                                                            ?>
                                                            <option value="<?php echo $row_cat['catid'] ?>" <?php if (!(strcmp($row_cat['catid'], htmlentities($row_editcs['catid'], ENT_COMPAT, 'utf-8')))) {echo "selected=\"selected\"";} ?>><?php echo $row_cat['catname'] ?></option>
                                                            <?php
                                                        }
                                                        while ($row_cat = mysql_fetch_assoc($cat));
                                                        $rows = mysql_num_rows($cat);
                                                        if ($rows > 0) {
                                                            mysql_data_seek($cat, 0);
                                                            $row_cat = mysql_fetch_assoc($cat);
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Department </label>
                                                <div class="controls">
                                                    <select name="deptid" id="deptid" >
                                                        <?php
                                                        $rows = mysql_num_rows($dpt);
                                                        if ($rows > 0) {
                                                            mysql_data_seek($dpt, 0);
                                                            $row_dpt = mysql_fetch_assoc($dpt);
                                                        }
                                                        do {
                                                            ?>
                                                            <option value="<?php echo $row_dpt['deptid'] ?>" <?php if (!(strcmp($row_dpt['deptid'], htmlentities($row_editcs['deptid'], ENT_COMPAT, 'utf-8')))) {echo "selected=\"selected\"";} ?>><?php echo $row_dpt['deptname'] ?></option>
                                                            <?php
                                                        }
                                                        while ($row_dpt = mysql_fetch_assoc($dpt));
                                                        $rows = mysql_num_rows($dept);
                                                        if ($rows > 0) {
                                                            mysql_data_seek($dept, 0);
                                                            $row_dept = mysql_fetch_assoc($dept);
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Level: </label>
                                                <div class="controls">
                                                    <select name="level" id="level" >
                                                        <?php
                                                        while($row_level = mysql_fetch_assoc($level)) {
                                                            ?>
                                                            <option value="<?php echo $row_level['levelid'] ?>" <?php if (!(strcmp($row_level['levelid'], htmlentities($row_editcs['level'], ENT_COMPAT, 'utf-8')))) {echo "selected=\"selected\"";} ?>><?php echo $row_level['levelname'] ?></option>
                                                            <?php
                                                        }
                                                        
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Unit: </label>
                                                <div class="controls">
                                                    <select name="unit" id="unit" >
                                                        <option value="1" <?php if (1 == $row_editcs['unit']) {echo "selected=\"selected\"";} ?>>1</option>   
                                                        <option value="2" <?php if (2 == $row_editcs['unit']) {echo "selected=\"selected\"";} ?>>2</option> 
                                                        <option value="3" <?php if (3 == $row_editcs['unit']) {echo "selected=\"selected\"";} ?>>3</option> 
                                                        <option value="4" <?php if (4 == $row_editcs['unit']) {echo "selected=\"selected\"";} ?>>4</option> 
                                                        <option value="5" <?php if (5 == $row_editcs['unit']) {echo "selected=\"selected\"";} ?>>5</option> 
                                                        <option value="6" <?php if (6 == $row_editcs['unit']) {echo "selected=\"selected\"";} ?>>6</option>                                                          
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Unit: </label>
                                                <div class="controls">
                                                    <select name="status" id="status" >
                                                        <option value="Compulsory" <?php if ("Compulsory" == $row_editcs['status']) {echo "selected=\"selected\"";} ?>>Compulsory</option>   
                                                        <option value="Required" <?php if ("Required" == $row_editcs['status']) {echo "selected=\"selected\"";} ?>>Required</option> 
                                                        <option value="Elective" <?php if ("Elective" == $row_editcs['status']) {echo "selected=\"selected\"";} ?>>Elective</option>                                                         
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Course Content: </label>
                                                <div class="controls">
                                                    <textarea name="cscont" class="input-xlarge"><?php echo htmlentities($row_editcs['cscont'], ENT_COMPAT, 'utf-8'); ?></textarea>
                                                </div>
                                            </div>

                                            <input type="hidden" name="MM_update" value="form1" />
                                            <input type="hidden" name="csid" value="<?php echo $row_editcs['csid']; ?>" />
                                            <div class="form-actions">
                                                <input type="submit" value="Update Course" class="btn btn-primary" >
                                                <button class="btn" type="button">Cancel</button>
                                                
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH."/footer.php" ?>
    </body>
</html>

