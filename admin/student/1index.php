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

$MM_authorizedUsers = "1,20";
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

mysql_select_db($database_tams, $tams);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

//Upload File
$rsinsert;
$uploadstat = "";
$insert_row = 0;
$insert_error = array();
if ((isset($_POST["submit"])) && ($_POST["submit"] == "Upload Students")) {
    if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
        //Import uploaded file to Database	
        $handle = fopen($_FILES['filename']['tmp_name'], "r");
        while (($data = fgetcsv($handle, 1500, ",")) !== FALSE) {

            $insert_query = sprintf("INSERT INTO student (stdid, fname, lname, mname, progid, phone, email, addr, sex, dob, "
                    . "sesid, `level`, admode, password, status, `access`, credit, profile) "
                    . "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", GetSQLValueString($data[0], "text"), GetSQLValueString($data[1], "text"), GetSQLValueString($data[2], "text"), GetSQLValueString($data[3], "text"), GetSQLValueString($data[4], "int"), GetSQLValueString($data[5], "text"), GetSQLValueString($data[6], "text"), GetSQLValueString($data[7], "text"), GetSQLValueString($data[8], "text"), GetSQLValueString($data[9], "date"), GetSQLValueString($data[10], "int"), GetSQLValueString($data[11], "int"), GetSQLValueString($data[12], "text"), GetSQLValueString(md5($data[2]), "text"), GetSQLValueString($data[13], "text"), GetSQLValueString($data[14], "int"), GetSQLValueString($data[15], "int"), GetSQLValueString($data[16], "text"));

            /* $rsinsert = mysql_query($insert_query, $tams);
              echo mysql_info($tams);
              list($f,$s,$t) = explode(":", mysql_info($tams));
              $insert = strpos($s,"1"); */

            $rsinsert1 = mysql_query($insert_query, $tams);
            list($f, $s, $t) = explode(":", mysql_info($tams));
            $update1 = strpos($s, "1");
            if ($update1) {
                $insert_row++;
            }
            else {
                $insert_error[] = $data[0];
            }
        }
        if (count($insert_error) > 0) {
            $uploadstat = "Upload Unsuccessful! The following results could not be uploaded:<br/>";
            foreach ($insert_error as $error) {
                $uploadstat .= $error . "<br/>";
            }
        }
        else {
            $uploadstat = "Upload Successful! " . $insert_row . " results uploaded.";
        }
        fclose($handle);
    }
}


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
    $insertSQL = sprintf("INSERT INTO student (stdid, fname, lname, mname, progid, phone, email, addr, sex, dob, sesid, `level`, `stid`, admode, password, status, `access`, credit, profile) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", GetSQLValueString($_POST['stdid'], "text"), GetSQLValueString($_POST['fname'], "text"), GetSQLValueString($_POST['lname'], "text"), GetSQLValueString($_POST['mname'], "text"), GetSQLValueString($_POST['progid'], "int"), GetSQLValueString($_POST['phone'], "text"), GetSQLValueString($_POST['email'], "text"), GetSQLValueString($_POST['addr'], "text"), GetSQLValueString($_POST['sex'], "text"), GetSQLValueString($_POST['dob'], "date"), GetSQLValueString($_POST['sesid'], "int"), GetSQLValueString($_POST['level'], "int"), GetSQLValueString($_POST['stid'], "int"), GetSQLValueString($_POST['admode'], "text"), GetSQLValueString(md5($_POST['password']), "text"), GetSQLValueString($_POST['status'], "text"), GetSQLValueString($_POST['access'], "int"), GetSQLValueString($_POST['credit'], "int"), GetSQLValueString($_POST['profile'], "text"));

    $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());

    unset($_POST['MM_insert']);

    $params['entid'] = $_POST['stdid'];
    $params['enttype'] = 'student';
    $params['action'] = 'create';
    $params['cont'] = json_encode($_POST);
    audit_log($params);

    $insertGoTo = "addstdnt.php";
    if (isset($_SERVER['QUERY_STRING'])) {
        $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
        $insertGoTo .= $_SERVER['QUERY_STRING'];
    }
    header(sprintf("Location: %s", $insertGoTo));
}

$query_prog = "SELECT progid, progname FROM programme";
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$query_state = "SELECT * FROM `state` ";
$state = mysql_query($query_state, $tams) or die(mysql_error());
$row_state = mysql_fetch_assoc($state);
$totalRows_state = mysql_num_rows($state);

$query_prog1 = (isset($_GET['cid'])) ? "SELECT progid, progname FROM programme p, department d WHERE d.deptid = p.deptid AND colid = " . $_GET['cid'] . " ORDER BY progname ASC" : "SELECT progid, progname FROM programme WHERE  deptid= 0 ORDER BY progname ASC";
$prog1 = mysql_query($query_prog1, $tams) or die(mysql_error());
$row_prog1 = mysql_fetch_assoc($prog1);
$totalRows_prog1 = mysql_num_rows($prog1);

$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);

//$totalRows_student = "";
//$student = "";
//if (isset($_GET['filter']) && $_GET['filter'] != "col") {
//
//    $query_student = createFilter("stud");
//    $student = mysql_query($query_student, $tams) or die(mysql_error());
//    $row_student = mysql_fetch_assoc($student);
//    $totalRows_student = mysql_num_rows($student);
//}
//
//$level = '-1';
//if (isset($_GET['lvl'])) {
//    $level = $_GET['lvl'];
//}
//$deptname = "";

$colname_rsstdnt = "-1";
$totalRows_rsstdnt = 0;
if (isset($_POST['search']) && $_POST['search'] != NULL) {
    $colname_rsstdnt = $_POST['search'];
    $seed = $colname_rsstdnt;

    mysql_select_db($database_tams, $tams);
    $query_rsstdnt = "SELECT stdid, lname, fname 
					FROM student
					 WHERE lname LIKE '%" . $seed . "%'
					 OR fname LIKE '%" . $seed . "%'
					 OR stdid LIKE '%" . $seed . "%'";

    $rsstdnt = mysql_query($query_rsstdnt, $tams) or die(mysql_error());

    $row_rsstdnt = mysql_fetch_assoc($rsstdnt);
    $totalRows_rsstdnt = mysql_num_rows($rsstdnt);
}


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
                    <?php statusMsg(); ?>
                    </div>

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Student in the University
                                    </h3>
                                    <ul class="tabs">
                                        <li class="active">
                                            <a href="#" class="btn  red"><?= $totalRows_rsstdnt . " students" ?></a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <div id="accordion2" class="accordion">
                                                <div class="accordion-group">
                                                    <div class="accordion-heading">
                                                        <a href="#collapseOne" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                            <i class="icon-plus"></i> Add New Student
                                                        </a>
                                                    </div>
                                                    <div class="accordion-body collapse" id="collapseOne" style="height: 0px;">
                                                        <div class="accordion-inner">
                                                            <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>" method="post">
                                                                <div class="control-group">
                                                                    <label class="control-label" for="textfield">Matric No </label>
                                                                    <div class="controls">
                                                                        <input name="stdid" type="text"  class="input-large" required=""/>
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
                                                                    <label class="control-label" for="textfield">Programme</label>
                                                                    <div class="controls">
                                                                        <select name="progid" required="">
                                                                            <option value="">-- Choose -- </option>
                                                                            <?php
                                                                            do {
                                                                                ?>
                                                                                <option value="<?php echo $row_prog['progid'] ?>"><?php echo $row_prog['progname'] ?></option>
                                                                                <?php
                                                                            }
                                                                            while ($row_prog = mysql_fetch_assoc($prog));
                                                                            $rows = mysql_num_rows($prog);
                                                                            if ($rows > 0) {
                                                                                mysql_data_seek($prog, 0);
                                                                                $row_prog = mysql_fetch_assoc($prog);
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
                                                                    <label class="control-label" for="textfield">Address  </label>
                                                                    <div class="controls">
                                                                        <textarea name="addr" class="input-xlarge" required=""></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="control-group">
                                                                    <label class="control-label" for="textfield">Password </label>
                                                                    <div class="controls">
                                                                        <input name="password"  type="text" class="input-xlarge"  required="" />
                                                                    </div>
                                                                </div>
                                                                <div class="control-group">
                                                                    <label class="control-label" for="textfield">Sex</label>
                                                                    <div class="controls" class="input-xlarge">
                                                                        <select name="sex" required="">
                                                                            <option value="M">Male</option>
                                                                            <option value="F">Female</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="control-group">
                                                                    <label class="control-label" for="textfield">Date of Birth </label>
                                                                    <div class="controls">
                                                                        <input name="dob"  type="date" class="input-xlarge"  required="" />
                                                                    </div>
                                                                </div>
                                                                <div class="control-group">
                                                                    <label class="control-label" for="textfield">Session</label>
                                                                    <div class="controls" class="input-xlarge" required="">
                                                                        <select name="sesid">
                                                                            <?php
                                                                            do {
                                                                                ?>
                                                                                <option value="<?php echo $row_sess['sesid'] ?>"><?php echo $row_sess['sesname'] ?></option>
                                                                                <?php
                                                                            }
                                                                            while ($row_sess = mysql_fetch_assoc($sess));
                                                                            $rows = mysql_num_rows($sess);
                                                                            if ($rows > 0) {
                                                                                mysql_data_seek($sess, 0);
                                                                                $row_sess = mysql_fetch_assoc($sess);
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="control-group">
                                                                    <label class="control-label" for="textfield">Level</label>
                                                                    <div class="controls" class="input-xlarge">
                                                                        <select name="level" required="">
                                                                            <option value="1">100</option>
                                                                            <option value="2">200</option>
                                                                            <option value="3">300</option>
                                                                            <option value="4">400</option>
                                                                            <option value="5">500</option>
                                                                            <option value="6">600</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="control-group">
                                                                    <label class="control-label" for="textfield">State of Origin</label>
                                                                    <div class="controls" class="input-xlarge">
                                                                        <select name="stid" required="">
                                                                            <?php do { ?>     
                                                                                <option value="<?php echo $row_state['stid'] ?>"><?php echo $row_state['stname'] ?></option>
                                                                            <?php }
                                                                            while ($row_state = mysql_fetch_assoc($state))
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="control-group">
                                                                    <label class="control-label" for="textfield">Admission Mode</label>
                                                                    <div class="controls" class="input-xlarge">
                                                                        <select name="admode" required="">
                                                                            <option value="UTME" >UTME</option>
                                                                            <option value="DE" >DE</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="control-group">
                                                                    <label class="control-label" for="textfield">Profile</label>
                                                                    <div class="controls" class="input-xlarge">
                                                                        <textarea name="profile" class="input-xlarge"></textarea>
                                                                    </div>
                                                                </div>
                                                                <input type="hidden" name="status" value="Undergrad" />
                                                                <input type="hidden" name="access" value="10" />
                                                                <input type="hidden" name="credit" value="0" />
                                                                <input type="hidden" name="MM_insert" value="form1" />
                                                                <div class="form-actions">
                                                                    <input type="submit" value="Add Student" class="btn btn-primary" >
                                                                    <button class="btn" type="button">Cancel</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--                                            <div class="accordion-group">
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
                                                                                            </div>-->
                                            </div>
                                        </div>
                                        <div class="span12">
                                            <form class="form form-vertical  form-validate" action="<?php echo $editFormAction; ?>" method="post">
                                                <div class="control-group span10">
                                                    <label class="control-label" for="textfield">Search By Name or Matric No </label>
                                                    <div class="controls span10">
                                                        <input name="search" type="text" class="input-xxlarge" />
                                                    </div>
                                                    <div class="controls ">
                                                        <input type="submit" class="btn " name="submit" value="Search" />
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <?php if (!empty($row_rsstdnt)) {?>
                                    <table class="table table-hover table-striped table-condensed">
                                        <thead>
                                            <tr>
                                                <th width="5%">S/N</th>
                                                <th width="10%">Student ID</th>
                                                <th width="75%">Full Name</th>
                                                <th width="10%">&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i=1; do { ?>
                                                <tr>
                                                    <td><?= $i++; ?></td>
                                                    <td><a href="../../student/profile.php?stid=<?php echo $row_rsstdnt['stdid']; ?>"><?php echo $row_rsstdnt['stdid']; ?></a></td>
                                                    <td><?php echo $row_rsstdnt['fname']; ?>, <?php echo ucwords(strtolower($row_rsstdnt['lname'])); ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-cogs"></i><span class="caret"></span></a>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <a href="editstudent.php?stid=<?php echo $row_rsstdnt['stdid']; ?>">Edit</a>
                                                                </li>
                                                                <li>
                                                                    <a href="#">Delete</a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php }while ($row_rsstdnt = mysql_fetch_assoc($rsstdnt)); ?>
                                        </tbody>
                                    </table>
                                    <?php }else{?>
                                    <div class="alert alert-danger">
                                        SORRY !! NO Record Available Search by Name or Matric No
                                    </div>
                                    <?php }?>
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

