<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');


$auth_users = "1,20";
check_auth($auth_users, $site_root);

//Upload File
$rsinsert;
$insert_row = 0;
$insert_error;
if ((isset($_POST["submit"])) && ($_POST["submit"] == "Upload Courses")) {
    if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
        //Import uploaded file to Database	
        $handle = fopen($_FILES['filename']['tmp_name'], "r");
        while (($data = fgetcsv($handle, 1500, ",")) !== FALSE) {

            $insert_query = sprintf("INSERT INTO course (csid, csname, semester, catid, deptid, cscont) VALUES (%s, %s, %s, %s, %s, %s)", GetSQLValueString($data[0], "text"), GetSQLValueString($data[1], "text"), GetSQLValueString($data[2], "text"), GetSQLValueString($data[3], "int"), GetSQLValueString($data[4], "text"), GetSQLValueString($data[5], "text"));
            mysql_select_db($database_Tsdb, $Tsdb);
            $rsinsert = mysql_query($insert_query);
            $insert_error = mysql_error();
            $insert_row++;
        }

        fclose($handle);
    }
}



$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) { 
    $insertSQL = sprintf("INSERT INTO course (csid, csname, semester, type, catid, deptid, cscont, curid ) "
            . "VALUES (%s, %s, %s, %s, %s, %s, %s,%s)", 
            GetSQLValueString($_POST['csid'], "text"), 
            GetSQLValueString($_POST['csname'], "text"),
            GetSQLValueString($_POST['semester'], "text"),
            GetSQLValueString($_POST['type'], "text"), 
            GetSQLValueString($_POST['catid'], "int"), 
            GetSQLValueString($_POST['deptid'], "int"),
            GetSQLValueString($_POST['cscont'], "text"),GetSQLValueString($_POST['curid'], "int"));

    
    $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());

    $insertGoTo = "index.php";
    if ($Result1)
        $insertGoTo = ( isset($_GET['success']) ) ? $insertGoTo : $insertGoTo . "?success";
    else
        $insertGoTo = ( isset($_GET['error']) ) ? $insertGoTo : $insertGoTo . "?error";
    
    if (isset($_SERVER['QUERY_STRING'])) {
        $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
        $insertGoTo .= $_SERVER['QUERY_STRING'];
    }
    header(sprintf("Location: %s", $insertGoTo));
}

$query_dept1 = "SELECT * FROM department";
$dpt = mysql_query($query_dept1, $tams) or die(mysql_error());
$row_dpt = mysql_fetch_assoc($dpt);
$totalRows_dpt = mysql_num_rows($dpt);



$query_cat = "SELECT * FROM category";
$cat = mysql_query($query_cat, $tams) or die(mysql_error());
$row_cat = mysql_fetch_assoc($cat);
$totalRows_cat = mysql_num_rows($cat);

$query_cur = "SELECT * FROM curriculum";
$cur = mysql_query($query_cur, $tams) or die(mysql_error());
$row_cur = mysql_fetch_assoc($cur);
$totalRows_cur = mysql_num_rows($cur);



$query_dept = ( isset($_GET['cid']) ) ? "SELECT deptid, deptname FROM department WHERE colid = " . $_GET['cid'] . " ORDER BY deptname ASC" : "SELECT deptid, deptname FROM department ORDER BY deptname ASC";
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
    if ($_GET['filter'] == "cat")
        do {
            if ($_GET['catid'] == $row_cat['catid'])
                $filtername .= "(" . $row_cat['catname'] . ")";
        } while ($row_cat = mysql_fetch_assoc($cat));
}




if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}
$deptname = "";



$page_title = "Tasued";
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
                    <div class="span6">
                        <?php statusMsg();?>
                    </div>
                    
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Courses in the University
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div id="accordion2" class="accordion">
                                        <div class="accordion-group">
                                            <div class="accordion-heading">
                                                <a href="#collapseOne" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                    <i class="icon-plus"></i> Create New Course
                                                </a>
                                            </div>
                                            <div class="accordion-body collapse" id="collapseOne" style="height: 0px;">
                                                <div class="accordion-inner">
                                                    
                                                    <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post">
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Course Code: </label>
                                                            <div class="controls">
                                                                <input name="csid" type="text"  class="input-xlarge" required=""/>
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Course Name:</label>
                                                            <div class="controls">
                                                                <input name="csname" type="text" class="input-medium"  required="" />
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Semester</label>
                                                            <div class="controls">
                                                                <select name="semester"  class="input-medium"  required="">
                                                                    <option value="F">First</option>
                                                                    <option value="S">Second</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Course Type:</label>
                                                            <div class="controls">
                                                                <select name="type" id="type" required="" class="input-medium">
                                                                    <option value="General">General</option>
                                                                    <option value="College">College</option>
                                                                    <option value="Departmental">Departmental</option>
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
                                                                        <option value="<?php echo $row_cat['catid'] ?>"><?php echo $row_cat['catname'] ?></option>
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
                                                                        <option value="<?php echo $row_dpt['deptid'] ?>"><?php echo $row_dpt['deptname'] ?></option>
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
                                                            <label class="control-label" for="textfield">Curriculum Type:</label>
                                                            <div class="controls">
                                                                <select name="curid" id="type" required="" class="input-medium">
                                                                    <?php do{?>
                                                                    <option value="<?= $row_cur['curid']?>"><?= $row_cur['curname']?></option>
                                                                    <?php }while($row_cur = mysql_fetch_assoc($cur) )?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Course Content: </label>
                                                            <div class="controls">
                                                                <textarea name="cscont" class="input-xlarge"></textarea>
                                                            </div>
                                                        </div>
                                                        
                                                        <input type="hidden" name="MM_insert" value="form1" />
                                                        <div class="form-actions">
                                                            <input type="submit" value="Add Course" class="btn btn-primary" >
                                                            <button class="btn" type="button">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-group">
                                            <div class="accordion-heading">
                                                <a href="#collapseTwo" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                    <i class="icon-plus"></i> Upload Course from File
                                                </a>
                                            </div>
                                            <div class="accordion-body collapse" id="collapseTwo" style="height: 0px;">
                                                <div class="accordion-inner">
                                                    <p>Upload CSV file with no column heading and in the order of: csid, csname, semester, type, catid, deptid, cscont.</p>
                                                    <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data">
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">CSV File </label>
                                                            <div class="controls">
                                                                <input name="filename" type="file" />
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="colid" value="" />
                                                        <input type="hidden" name="MM_insert" value="form2" />
                                                        <div class="form-actions">
                                                            <input type="submit" value="Add Course" class="btn btn-primary" >
                                                            <button class="btn" type="button">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row-fluid">
                                        <div class="span4">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Filter By <?= $college_name?> </label>
                                                <div class="controls">
                                                    <select name="col" id="col"  class="input-medium"onchange="colFilter(this)">
                                                        <option value="-1" <?php if (isset($_GET['cid'])) if (!(strcmp(-1, $_GET['cid']))) {
                                                                                                                echo "selected=\"selected\"";
                                                                                                            } ?>>---Select A College---</option>
                                                        <?php
                                                        $rows = mysql_num_rows($col);
                                                        if ($rows > 0) {
                                                            mysql_data_seek($col, 0);
                                                            $row_col = mysql_fetch_assoc($col);
                                                        }
                                                        do {
                                                            ?>
                                                            <option value="<?php echo $row_col['colid'] ?>" <?php if (isset($_GET['cid'])) if (!(strcmp($row_col['colid'], $_GET['cid']))) {
                                                                echo "selected=\"selected\"";
                                                            } ?>><?php echo $row_col['coltitle'] ?></option>
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
                                                <label class="control-label" for="textfield">View By Department</label>
                                                <div class="controls">
                                                    <select name="dept" id="dept" onchange="deptFilter(this)" style="width:250px;">
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
                                                            <option value="<?php echo $row_dept['deptid'] ?>"<?php if (isset($_GET['did'])) if (!(strcmp($row_dept['deptid'], $_GET['did']))) {
                                                                echo "selected=\"selected\"";
                                                            } ?>><?php echo $row_dept['deptname'] ?></option>
                                                            <?php
                                                        }
                                                        while ($row_dept = mysql_fetch_assoc($dept));
                                                        $rows = mysql_num_rows($dept);
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>  
                                        <div class="span4">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">View By Category </label>
                                                <div class="controls">
                                                    <select name="cat" onchange="catFilter(this)" style="width:150px;">
                                                        <option value="-1" <?php if (isset($_GET['catid'])) if (!(strcmp(-1, $_GET['catid']))) {
                                                                        echo "selected=\"selected\"";
                                                                    } ?>>---Select A Category---</option>
                                                        <?php
                                                        $rows = mysql_num_rows($cat);
                                                        if ($rows > 0) {
                                                            mysql_data_seek($cat, 0);
                                                            $row_cat = mysql_fetch_assoc($cat);
                                                        }
                                                        do {
                                                            ?>
                                                            <option value="<?php echo $row_cat['catid'] ?>"<?php if (isset($_GET['catid'])) if (!(strcmp($row_cat['catid'], $_GET['catid']))) {
                                                                echo "selected=\"selected\"";
                                                            } ?>><?php echo $row_cat['catname'] ?></option>
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
                                        </div>  
                                    </div>
                                    <div class="row-fluid">
                                        <table class="table table-condensed">
                                            <thead>
                                                <tr>
                                                    <th width="10%">Course Code</th>
                                                    <th width="70%">Course Title</th>
                                                    <th width="10%">Category</th>
                                                    <th width="10%">&nbsp;</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($totalRows_courses > 0) { // Show if recordset not empty ?>
                                                    <?php do { ?>
                                                        <tr>
                                                            <td width="50"><?php echo $row_courses['csid']; ?></td>
                                                            <td width="364"><?php echo $row_courses['csname']; ?></td>
                                                            <td width="105"><?php echo $row_courses['catname']; ?></td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-cogs"></i><span class="caret"></span></a>
                                                                    <ul class="dropdown-menu">
                                                                        <li>
                                                                            <a href="courseedit.php?csid=<?php echo $row_courses['csid']; ?>">Edit</a>
                                                                        </li>
                                                                        <li>
                                                                            <a href="#">Delete</a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php }
                                                    while ($row_courses = mysql_fetch_assoc($courses)); ?>
                                                    <?php mysql_free_result($courses);
                                                } // Show if recordset not empty 
                                                ?>
                                            </tbody>
                                        </table>
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

