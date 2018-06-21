<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');



/* -----------------------------------------------*
 * 
 * Logic of the College/index.php Page 
 *
 * *------------------------------------------------
 */

$MM_authorizedUsers = "1,20,28";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) {
    // For security, start by assuming the visitor is NOT authorized. 
    $isValid = False;

    // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
    // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
    if (!empty($UserName)) {
        // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
        // Parse the strings into arrays. 
        $arrUsers = Explode(",", $strUsers);
        $arrGroups = Explode(",", $strGroups);
        if (in_array($UserName, $arrUsers)) {
            $isValid = true;
        }
        // Or, you may restrict access to only certain users based on their username. 
        if (in_array($UserGroup, $arrGroups)) {
            $isValid = true;
        }
        if (($strUsers == "") && false) {
            $isValid = true;
        }
    }
    return $isValid;
}

$MM_restrictGoTo = "../index.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("", $MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {
    $MM_qsChar = "?";
    $MM_referrer = $_SERVER['PHP_SELF'];
    if (strpos($MM_restrictGoTo, "?"))
        $MM_qsChar = "&";
    if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0)
        $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
    $MM_restrictGoTo = $MM_restrictGoTo . $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
    header("Location: " . $MM_restrictGoTo);
    exit;
}

//Upload File
$rsinsert;
$insert_row = 0;
$insert_error;
if ((isset($_POST["submit"])) && ($_POST["submit"] == "Upload Staff")) {
    if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
        //Import uploaded file to Database	
        $handle = fopen($_FILES['filename']['tmp_name'], "r");
        while (($data = fgetcsv($handle, 1500, ",")) !== FALSE) {

            $insert_query = sprintf("INSERT INTO "
                    . "lecturer (lectid, title, fname, lname, mname, deptid, phone, email, addr, sex, password) "
                    . "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", 
                    GetSQLValueString($data[0], "text"), 
                    GetSQLValueString($data[1], "text"), 
                    GetSQLValueString($data[2], "text"), 
                    GetSQLValueString($data[3], "text"), 
                    GetSQLValueString($data[4], "text"), 
                    GetSQLValueString($data[5], "int"), 
                    GetSQLValueString($data[6], "text"), 
                    GetSQLValueString($data[7], "text"), 
                    GetSQLValueString($data[8], "text"),
                    GetSQLValueString($data[9], "text"), 
                    GetSQLValueString(md5($data[10]), "text"));
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
    $insertSQL = sprintf("INSERT INTO "
            . "lecturer (lectid, title, fname, lname, mname, deptid, phone, email, addr, access, sex, password) "
            . "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", 
            GetSQLValueString($_POST['lectid'], "text"),
            GetSQLValueString($_POST['title'], "text"), 
            GetSQLValueString($_POST['fname'], "text"), 
            GetSQLValueString($_POST['lname'], "text"), 
            GetSQLValueString($_POST['mname'], "text"), 
            GetSQLValueString($_POST['deptid'], "int"), 
            GetSQLValueString($_POST['phone'], "text"), 
            GetSQLValueString($_POST['email'], "text"), 
            GetSQLValueString($_POST['addr'], "text"), 
            GetSQLValueString(5, "int"), 
            GetSQLValueString($_POST['sex'], "text"), 
            GetSQLValueString(md5($_POST['password']), "text"));

    mysql_select_db($database_tams, $tams);
    $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());

    $insertGoTo = "../../index.php";
    if (isset($_SERVER['QUERY_STRING'])) {
        $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
        $insertGoTo .= $_SERVER['QUERY_STRING'];
    }
    header(sprintf("Location: %s", $insertGoTo));
}

mysql_select_db($database_tams, $tams);
$query_dept = "SELECT deptid, deptname FROM department";
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

mysql_select_db($database_tams, $tams);
$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);

mysql_select_db($database_tams, $tams);
$query_staff = ( isset($_GET['filter']) ) ? createFilter("lect") : "SELECT lectid, title, fname, lname, mname FROM lecturer";
$staff = mysql_query($query_staff, $tams) or die(mysql_error());
$row_staff = mysql_fetch_assoc($staff);
$totalRows_staff = mysql_num_rows($staff);


//query to fetch all non teaching staff (ntsf)
$query_ntsf = "SELECT * FROM non_teaching";
$ntsf = mysql_query($query_ntsf, $tams) or die(mysql_error());
$row_ntsf = mysql_fetch_assoc($ntsf);
$totalRows_ntsf = mysql_num_rows($ntsf);

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
                    <br/>
                    <div class="span6">
<?php //statusMsg(); ?>
                    </div>-->

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Staff Management 
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <div id="accordion2" class="accordion">
                                            <div class="accordion-group">
                                                <div class="accordion-heading">
                                                    <a href="#collapseOne" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="icon-plus"></i> Add New Staff
                                                    </a>
                                                </div>
                                                <div class="accordion-body collapse" id="collapseOne" style="height: 0px;">
                                                    <div class="accordion-inner">
                                                        <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post">
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Staff Id </label>
                                                                <div class="controls">
                                                                    <input name="lectid" type="text"  class="input-large" required=""/>
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Title</label>
                                                                <div class="controls">
                                                                    <select name="title" class="input-large" required="">
                                                                        <option value="">-- Select --</option>
                                                                        <option value="Prof">Prof.</option>
                                                                        <option value="Dr">Dr.</option>
                                                                        <option value="Mr" >Mr.</option>
                                                                        <option value="Mrs">Mrs.</option>
                                                                        <option value="Miss">Miss</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">First Name</label>
                                                                <div class="controls">
                                                                    <input name="fname"  type="text" class="input-xlarge"  required="" />
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Last Name</label>
                                                                <div class="controls">
                                                                    <input name="lname"  type="text" class="input-xlarge"  required="" />
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Middle Name</label>
                                                                <div class="controls">
                                                                    <input name="mname"  type="text" class="input-xlarge"  required="" />
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Department</label>
                                                                <div class="controls">
                                                                    <select name="deptid">
                                                                        <?php
                                                                        $rows = mysql_num_rows($dept);
                                                                        if ($rows > 0) {
                                                                            mysql_data_seek($dept, 0);
                                                                            $row_dept = mysql_fetch_assoc($dept);
                                                                        }
                                                                        do {
                                                                            ?>
                                                                            <option value="<?php echo $row_dept['deptid'] ?>"><?php echo $row_dept['deptname'] ?></option>
                                                                            <?php
                                                                        }
                                                                        while ($row_dept = mysql_fetch_assoc($dept));
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
                                                                <label class="control-label" for="textfield">Phone No</label>
                                                                <div class="controls">
                                                                    <input name="phone"  type="text" class="input-xlarge"  required="" />
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Email </label>
                                                                <div class="controls">
                                                                    <input name="email"  type="email" class="input-xlarge"  required="" />
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Password </label>
                                                                <div class="controls">
                                                                    <input name="password"  type="email" class="input-xlarge"  required="" />
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Address  </label>
                                                                <div class="controls">
                                                                    <textarea name="addr" class="input-xlarge"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Sex</label>
                                                                <div class="controls" class="input-xlarge">
                                                                    <select name="sex">
                                                                        <option value="M">Male</option>
                                                                        <option value="F">Female</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="MM_insert" value="form1" />
                                                            <div class="form-actions">
                                                                <input type="submit" value="Add Staff" class="btn btn-primary" >
                                                                <button class="btn" type="button">Cancel</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion-group">
                                                <div class="accordion-heading">
                                                    <a href="#collapseTwo" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="icon-plus"></i> Add New Staff from File
                                                    </a>
                                                </div>
                                                <div class="accordion-body collapse" id="collapseTwo" style="height: 0px;">
                                                    <div class="accordion-inner">
                                                        <div class="alert" >
                                                            NOTE: Upload CSV file with no column heading and in the order of: lectid, title, fname, lname, mname, deptid, phone, email, addr, sex, access, password, profile.
                                                        </div>
                                                        <form class="form-horizontal form-validate" action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data">
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Select File </label>
                                                                <div class="controls">
                                                                    <input name="filename" type="file"/>
                                                                </div>
                                                            </div>
                                                            <div class="form-actions">
                                                                <input type="submit" value="Upload Staffs" class="btn btn-primary" >
                                                                <button class="btn" type="button">Cancel</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p>&nbsp;</p>
                                    <div class="row-fluid">
                                        <div class="span6">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Filter By <?= $department_name ?> </label>
                                                <div class="controls">
                                                    <select name="dept2" id="dept2" onchange="deptFilter(this)">
                                                        <option value="-1" <?php if (isset($_GET['did'])) if (!(strcmp(-1, $_GET['did']))) {
                                                                echo "selected=\"selected\"";
                                                            } ?>>---Select A Department---</option>
                                                        <?php
                                                        do {
                                                            ?>
                                                            <option value="<?php echo $row_dept['deptid'] ?>"<?php if (isset($_GET['did'])) if (!(strcmp($row_dept['deptid'], $_GET['did']))) {
                                                                echo "selected=\"selected\"";
                                                            } ?>><?php echo $row_dept['deptname'] ?></option>
                                                            <?php
                                                        }
                                                        while ($row_dept = mysql_fetch_assoc($dept));
                                                        $rows = mysql_num_rows($dept);
                                                        if ($rows > 0) {
                                                            mysql_data_seek($dept, 0);
                                                            $row_dept = mysql_fetch_assoc($dept);
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>  
                                        <div class="span6">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">View By <?= $college_name?></label>
                                                <div class="controls">
                                                    <select name="col2" id="col" onchange="colFilter(this)">
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
                                                        $rows = mysql_num_rows($col);
                                                        if ($rows > 0) {
                                                            mysql_data_seek($col, 0);
                                                            $row_col = mysql_fetch_assoc($col);
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>  
                                    </div>
                                    <table class="table table-hover table-striped table-condensed">
                                        <thead>
                                            <tr>
                                                <th width="5%">S/N</th>
                                                <th width="10%">Staff ID</th>
                                                <th width="75%">Full Name</th>
                                                <th width="10%">&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i= 0; do { ?>
                                            <tr>
                                                <td><?= $i++; ?></td>
                                                <td><a href="../../staff/profile.php?lid=<?= $row_staff['lectid']?>"><?= $row_staff['lectid']?></a></td>
                                                <td><?= $row_staff['title']." ".$row_staff['lname'].", ".$row_staff['fname']." ".$row_staff['mname']; ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-cogs"></i><span class="caret"></span></a>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a href="editstaff.php?lid=<?php echo $row_staff['lectid']; ?>">Edit</a>
                                                            </li>
                                                            <li>
                                                                <a href="#">Delete</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php } while ($row_staff = mysql_fetch_assoc($staff)); ?>
                                        </tbody>

                                    </table>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Non teaching Staff Management 
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <?php if(1 == 2){?>
                                    <div class="row-fluid">
                                        <div id="accordion2" class="accordion">
                                            <div class="accordion-group">
                                                <div class="accordion-heading">
                                                    <a href="#collapseOne" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="icon-plus"></i> Add New Staff
                                                    </a>
                                                </div>
                                                <div class="accordion-body collapse" id="collapseOne" style="height: 0px;">
                                                    <div class="accordion-inner">
                                                        <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post">
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Staff Id </label>
                                                                <div class="controls">
                                                                    <input name="lectid" type="text"  class="input-large" required=""/>
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Title</label>
                                                                <div class="controls">
                                                                    <select name="title" class="input-large" required="">
                                                                        <option value="">-- Select --</option>
                                                                        <option value="Prof">Prof.</option>
                                                                        <option value="Dr">Dr.</option>
                                                                        <option value="Mr" >Mr.</option>
                                                                        <option value="Mrs">Mrs.</option>
                                                                        <option value="Miss">Miss</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">First Name</label>
                                                                <div class="controls">
                                                                    <input name="fname"  type="text" class="input-xlarge"  required="" />
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Last Name</label>
                                                                <div class="controls">
                                                                    <input name="lname"  type="text" class="input-xlarge"  required="" />
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Middle Name</label>
                                                                <div class="controls">
                                                                    <input name="mname"  type="text" class="input-xlarge"  required="" />
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Department</label>
                                                                <div class="controls">
                                                                    <select name="deptid">
                                                                        <?php
                                                                        $rows = mysql_num_rows($dept);
                                                                        if ($rows > 0) {
                                                                            mysql_data_seek($dept, 0);
                                                                            $row_dept = mysql_fetch_assoc($dept);
                                                                        }
                                                                        do {
                                                                            ?>
                                                                            <option value="<?php echo $row_dept['deptid'] ?>"><?php echo $row_dept['deptname'] ?></option>
                                                                            <?php
                                                                        }
                                                                        while ($row_dept = mysql_fetch_assoc($dept));
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
                                                                <label class="control-label" for="textfield">Phone No</label>
                                                                <div class="controls">
                                                                    <input name="phone"  type="text" class="input-xlarge"  required="" />
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Email </label>
                                                                <div class="controls">
                                                                    <input name="email"  type="email" class="input-xlarge"  required="" />
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Password </label>
                                                                <div class="controls">
                                                                    <input name="password"  type="email" class="input-xlarge"  required="" />
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Address  </label>
                                                                <div class="controls">
                                                                    <textarea name="addr" class="input-xlarge"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Sex</label>
                                                                <div class="controls" class="input-xlarge">
                                                                    <select name="sex">
                                                                        <option value="M">Male</option>
                                                                        <option value="F">Female</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="MM_insert" value="form1" />
                                                            <div class="form-actions">
                                                                <input type="submit" value="Add Staff" class="btn btn-primary" >
                                                                <button class="btn" type="button">Cancel</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion-group">
                                                <div class="accordion-heading">
                                                    <a href="#collapseTwo" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="icon-plus"></i> Add New Staff from File
                                                    </a>
                                                </div>
                                                <div class="accordion-body collapse" id="collapseTwo" style="height: 0px;">
                                                    <div class="accordion-inner">
                                                        <div class="alert" >
                                                            NOTE: Upload CSV file with no column heading and in the order of: lectid, title, fname, lname, mname, deptid, phone, email, addr, sex, access, password, profile.
                                                        </div>
                                                        <form class="form-horizontal form-validate" action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data">
                                                            <div class="control-group">
                                                                <label class="control-label" for="textfield">Select File </label>
                                                                <div class="controls">
                                                                    <input name="filename" type="file"/>
                                                                </div>
                                                            </div>
                                                            <div class="form-actions">
                                                                <input type="submit" value="Upload Staffs" class="btn btn-primary" >
                                                                <button class="btn" type="button">Cancel</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <p>&nbsp;</p>
                                    
                                    <table class="table table-hover table-striped table-condensed">
                                        <thead>
                                            <tr>
                                                <th width="5%">S/N</th>
                                                <th width="10%">Staff ID</th>
                                                <th width="30%">Full Name</th>
                                                <th width="30%">Section/Unit</th>
                                                <th width="20%">Designation</th>
                                                <th width="5%">&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i= 0; do { ?>
                                            <tr>
                                                <td><?= $i++; ?></td>
                                                <td><a target="tabs" href="../../non-teaching/profile.php?lid=<?= $row_ntsf['staff_id']?>"><?= $row_ntsf['staff_id']?></a></td>
                                                <td><?= $row_staff['title']." ".$row_ntsf['lname'].", ".$row_ntsf['fname']." ".$row_ntsf['mname']; ?></td>
                                                <td><?= $row_ntsf['section_unit'] ?></td>
                                                <td><?= $row_ntsf['designation']?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-cogs"></i><span class="caret"></span></a>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a href="../../non-teaching/editprofile.php?lid=<?php echo $row_ntsf['staff_id']; ?>">Edit</a>
                                                            </li>
                                                            <li>
                                                                <a href="#">Delete</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php } while ($row_ntsf = mysql_fetch_assoc($ntsf)); ?>
                                        </tbody>

                                    </table>
                                </div>
                            </div>
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

