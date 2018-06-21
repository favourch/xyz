<?php

require_once('../../path.php');

$auth_users = "1,2,3,10,20";
//check_auth($auth_users, $site_root);


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

$query_prog1 = "SELECT progid, progname FROM programme ORDER BY progname ASC";
$prog1 = mysql_query($query_prog1, $tams) or die(mysql_error());
//$row_prog1 = mysql_fetch_assoc($prog1);
$totalRows_prog1 = mysql_num_rows($prog1);

$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);

$totalRows_student = "";
$student = "";
if (isset($_GET['filter']) && $_GET['filter'] != "col") {

    $query_student = createFilter("stud");
    $student = mysql_query($query_student, $tams) or die(mysql_error());
    $row_student = mysql_fetch_assoc($student);
    $totalRows_student = mysql_num_rows($student);
}

$progid = '1';
if (isset($_GET['pid'])) {
    $progid = $_GET['pid'];
}

$level = '1';
if (isset($_GET['lvl'])) {
    $level = $_GET['lvl'];
}

$query_student = sprintf("SELECT * "
                        . "FROM student "
                        . "WHERE progid = %s "
                        . "AND level = %s "
                        . "ORDER BY stdid ASC", 
                        GetSQLValueString($progid, "int"), 
                        GetSQLValueString($level, "int"));
$student = mysql_query($query_student, $tams) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);


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
                    

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Print Transcript
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <div class="span4">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield"><?= $programme_name ?></label>
                                                <div class="controls">
                                                    <select name="prog" id="prog" onchange="progfilt(this)" style="width: 200px">
                                                        <?php for(;$row_prog1 = mysql_fetch_assoc($prog1);) :?>
                                                        <option value="<?php echo $row_prog1['progid'] ?>" 
                                                            <?php
                                                                if (!(strcmp($row_prog1['progid'], $progid))) {
                                                                    echo "selected=\"selected\"";
                                                                }
                                                            ?>>
                                                            <?php echo $row_prog1['progname'] ?>
                                                        </option>
                                                        <?php endfor;?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="span4">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Level</label>
                                                <div class="controls">
                                                    <select name="level" id="level" onchange="lvlfilt(this)">
                                                        <option value="1" <?php if (!(strcmp(1, $level))) {
    echo "selected=\"selected\"";
} ?>>100</option>
                                                        <option value="2" <?php if (!(strcmp(2, $level))) {
    echo "selected=\"selected\"";
} ?>>200</option>
                                                        <option value="3" <?php if (!(strcmp(3, $level))) {
                                                echo "selected=\"selected\"";
                                            } ?>>300</option>
                                                        <option value="4" <?php if (!(strcmp(4, $level))) {
                                                echo "selected=\"selected\"";
                                            } ?>>400</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
<?php if ($totalRows_student > 0) { // Show if recordset not empty  ?>
    <?php $i = 1;
    do { ?>
                                                    <tr>
                                                        <td><?= $i++; ?></td>
                                                        <td><a href="profile.php?stid=<?php echo $row_student['stdid']; ?>"><?php echo $row_student['stdid']; ?></a></td>
                                                        <td><?php echo $row_student['fname']; ?>, <?php echo ucwords(strtolower($row_student['lname'])); ?></td>
                                                        <td>
                                                            <a target="_blank" href="transprint.php?stdid=<?php echo $row_student['stdid'];?>">
                                                                Print Transcript
                                                            </a>
                                                        </td>
                                                    </tr>
    <?php }
    while ($row_student = mysql_fetch_assoc($student)); ?>
<?php } ?>
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