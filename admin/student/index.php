<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "1,20,28,29,30";
check_auth($auth_users, $site_root.'/admin');

$query_session = "SELECT sesid, sesname FROM session WHERE status = 'TRUE'";
$session = mysql_query($query_session, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);
$totalRows_session = mysql_num_rows($session);


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


if(isset($_POST['delete_reg'])){
    $sql1 = sprintf("DELETE FROM course_reg WHERE resultid = %s ", GetSQLValueString($_POST['delete_reg'], 'int'));
    mysql_query($sql1, $tams) or die(mysql_error());
    
    header("Location : index.php");
    exit();
}

//Upload File
$rsinsert;
$uploadstat = "";
$insert_row = 0;
$insert_error = array();
$msg = '';
$pass = '1allahu2';

function add_registration($stdid, $sesid, $admid, $tams) {
    $status = true;
    $cur_ses = getSessionValue('sesid');
    $query_reg_ses = "SELECT sesid FROM session WHERE sesid BETWEEN $sesid AND $cur_ses";
    $reg_ses = mysql_query($query_reg_ses, $tams);
    $totalRows_reg_ses = mysql_num_rows($reg_ses);
    
    $query_level = "SELECT entrylevel FROM admission_type WHERE typeid = $admid";
    $level = mysql_query($query_level, $tams);
    $row_level = mysql_fetch_assoc($level);
    $totalRows_level = mysql_num_rows($level);    
    $level = $row_level['entrylevel'];
    
    for(;$row_reg_ses = mysql_fetch_assoc($reg_ses);) {
        $registered = 'Registered';
        if($row_reg_ses['sesid'] == $cur_ses)
            $registered = 'Unregistered';
        
        $regSql = sprintf('INSERT IGNORE INTO registration (stdid, sesid, status, course, approved, level) '
                . 'VALUES (%s, %s, %s, %s, %s, %s)',
                GetSQLValueString($stdid, 'text'),
                GetSQLValueString($row_reg_ses['sesid'], 'int'),
                GetSQLValueString($registered, 'text'),
                GetSQLValueString($registered, 'text'),
                GetSQLValueString('TRUE', 'text'),
                GetSQLValueString($level++, 'int'));
        $reg = mysql_query($regSql, $tams);
        
        if(!$reg) {
            $status = false;
            break;
        }
    }
    
    return $status;
}


// Get cURL resource
$curl = curl_init();
// Set some options - we are passing in a useragent too here
curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => 'http://panel.xwireless.net/API/WebSMS/Http/v1.0a/index.php?method=credit_check&username=aadenubi&password=1allahu2&format=json',
    CURLOPT_USERAGENT => 'TASUED TAMS'
));
// Send the request & save response to $resp
$resp = curl_exec($curl);
// Close request to clear up some resources
curl_close($curl);
$sms_info = json_decode($resp, true);
$sms_credit = $sms_info['3']['credit'];


if(isset($_POST['action']) && $_POST['action'] == 'send_message'){
    
    $sender_name = $_POST['sender_name'];
    $receiver = $_POST['phone'];
    $body = $_POST['msg_body'];
    $url = "http://panel.xwireless.net/API/WebSMS/Http/v1.0a/index.php?username=aadenubi&password=1allahu2&sender=".urlencode($sender_name)."&to=".$receiver."&message=".urlencode($body)."&sendondate=".date('Y,m,d');
    // Get cURL resource
    $send_curl = curl_init();
    // Set some options - we are passing in a useragent too here
    curl_setopt_array($send_curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
        CURLOPT_USERAGENT => 'TASUED TAMS'
    ));
    
    // Send the request & save response to $resp
    $resp = curl_exec($send_curl);
    if(!$resp){ 
        $notification->set_notification('Error: "' . curl_error($send_curl) . '" - Code: ' . curl_errno($send_curl), 'error');
    }
    curl_close($send_curl);
    //die();
    $notification->set_notification($resp, 'success');
    
    
}


if (isset($_GET["action"]) && $_GET["action"] == "reset" && isset($_GET['stdid'])) {
    $query_insert = sprintf("UPDATE student SET password = md5(lower(lname)) WHERE stdid = %s", GetSQLValueString($_GET['stdid'], 'text')); 
    $rs_insert = mysql_query($query_insert, $tams);
    header("Location: index.php?stdid=".GetSQLValueString($_GET['stdid'], 'text'));
    exit;
}

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
    mysql_query('START TRANSACTION;', $tams);
    $insertSQL = sprintf("INSERT INTO student (stdid, fname, lname, mname, progid, phone, email, addr, sex, dob, sesid, `level`, `stid`, admid, password, status, `access`, credit, profile) "
            . "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", 
            GetSQLValueString($_POST['stdid'], "text"), 
            GetSQLValueString($_POST['fname'], "text"), 
            GetSQLValueString($_POST['lname'], "text"), 
            GetSQLValueString($_POST['mname'], "text"), 
            GetSQLValueString($_POST['progid'], "int"), 
            GetSQLValueString($_POST['phone'], "text"), 
            GetSQLValueString($_POST['email'], "text"), 
            GetSQLValueString($_POST['addr'], "text"), 
            GetSQLValueString($_POST['sex'], "text"), 
            GetSQLValueString($_POST['dob'], "date"), 
            GetSQLValueString($_POST['sesid'], "int"), 
            GetSQLValueString($_POST['level'], "int"), 
            GetSQLValueString($_POST['stid'], "int"), 
            GetSQLValueString($_POST['admode'], "text"), 
            GetSQLValueString(md5($_POST['password']), "text"), 
            GetSQLValueString($_POST['status'], "text"), 
            GetSQLValueString($_POST['access'], "int"), 
            GetSQLValueString($_POST['credit'], "int"), 
            GetSQLValueString($_POST['profile'], "text"));

    $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());
    $Result2 = add_registration($_POST['stdid'], $_POST['sesid'], $_POST['admode'], $tams);
    unset($_POST['MM_insert']);
    

    if($Result1 && $Result2) {
        $notification->set_notification('Student Added successfully!', 'success');
        mysql_query('COMMIT;', $tams);
        
        $params['entid'] = $_POST['stdid'];
        $params['enttype'] = 'student';
        $params['action'] = 'create';
        $params['cont'] = json_encode($_POST);
        audit_log($params);
    }else {
        $notification->set_notification('Student could not be added!', 'error');
        mysql_query('ROLLBACK;', $tams);
    }  
}

if ((isset($_POST["MM_submit"])) && ($_POST["MM_submit"] == "mod_reg")) {
    $rs_delete = $rs_insert = true;
    $stid = $_POST['stdid'];
    
    mysql_query('START TRANSACTION;');
    
    if(isset($_POST['del'])) {
        $delete = [];
        foreach($_POST['del'] as $entry) {
            $delete[] = sprintf("(stdid = %s AND sesid = %s)",
                                    GetSQLValueString($entry['stdid'], 'text'),
                                    GetSQLValueString($entry['sesid'], 'text'));
        }
        
        $delete = implode(' OR ', $delete);
        
        $query_delete = sprintf("DELETE FROM registration WHERE %s", GetSQLValueString($delete, 'defined', $delete));  
        $rs_delete = mysql_query($query_delete, $tams);
    }
    
    if(isset($_POST['entry'])) {
        $entries = [];
        
        foreach($_POST['entry'] as $entry) {
            $entries[] = sprintf("(%s, %s, %s, %s, %s, %s, %s)",
                                GetSQLValueString($stid, 'text'),
                                GetSQLValueString($entry['sesid'], 'text'),
                                GetSQLValueString($entry['status'], 'text'),
                                GetSQLValueString($entry['course'], 'text'),
                                GetSQLValueString($entry['approved'], 'text'),
                                GetSQLValueString($entry['level'], 'text'),
                                GetSQLValueString($entry['progid'], 'text'));
           
        }
        
        $entries = implode(',', $entries);
        
        $query_insert = sprintf("REPLACE INTO registration VALUES %s;", GetSQLValueString($entries, 'defined', $entries)); 
        $rs_insert = mysql_query($query_insert, $tams);
    }
    
    if ($rs_delete && $rs_insert) {
        $notification->set_notification('Registration updated successfully!', 'success');
        mysql_query('COMMIT;', $tams);

//        $params['entid'] = $_POST['stdid'];
//        $params['enttype'] = 'reg';
//        $params['action'] = 'create';
//        $params['cont'] = json_encode($_POST);
//        audit_log($params);
    } else {
        $notification->set_notification('Registration could not be updated!', 'error');
        mysql_query('ROLLBACK;', $tams);
    }
    
    $_POST['search'] = $stid;
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

$query_admType = "SELECT typeid, displayname FROM admission_type";
$admType = mysql_query($query_admType, $tams) or die(mysql_error());
$totalRows_admType = mysql_num_rows($admType);

$query_level = "SELECT * FROM level_name";
$level = mysql_query($query_level, $tams) or die(mysql_error());
$totalRows_level = mysql_num_rows($level);


if(isset($_POST['c_reg_stdt'])){
    
    $cs_reg_SQL = sprintf("INSERT INTO course_reg (stdid, csid, sesid, cleared, approved, edited) VALUES(%s, %s, %s, 'TRUE', 'FALSE', 'FALSE')",
                           GetSQLValueString($_POST['stdid'], 'text'),
                            GetSQLValueString($_POST['csid'], 'text'),
                            GetSQLValueString($_POST['sesid'], 'int'));
    $cs_reg_RS = mysql_query($cs_reg_SQL, $tams) or die(mysql_error());     
    
    header("Location: index.php");
    exit();
}



$colname_rsstdnt = "-1";
$totalRows_rsstdnt = 0;
if (isset($_POST['search']) && $_POST['search'] != NULL) {
    $colname_rsstdnt = $_POST['search'];
    $seed = $colname_rsstdnt;
    
    $query_rsstdnt = "SELECT stdid, lname, fname, mname, progname, s.progid, email, phone  
                        FROM student s 
                        JOIN programme p ON s.progid = p.progid 
                        WHERE lname LIKE '%" . $seed . "%'
                        OR fname LIKE '%" . $seed . "%'
                        OR stdid LIKE '%" . $seed . "%'";

    $rsstdnt = mysql_query($query_rsstdnt, $tams) or die(mysql_error());

    $row_rsstdnt = mysql_fetch_assoc($rsstdnt);
    $totalRows_rsstdnt = mysql_num_rows($rsstdnt);
}
?>
<!doctype html>
<html ng-app="TamsApp">
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="PageController">
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
                                            <?php if(getAccess() != 29) {?>
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
                                                                            <?php for (; $row_level = mysql_fetch_assoc($level);) : ?>
                                                                                <option value="<?php echo $row_level['levelid'] ?>">
                                                                                    <?php echo $row_level['levelname'] ?>
                                                                                </option>
                                                                            <?php endfor; mysql_data_seek($level, 0);?>
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
                                                                            <?php for(;$row_admType = mysql_fetch_assoc($admType);) :?>
                                                                            <option value="<?php echo $row_admType['typeid']?>" ><?php echo $row_admType['displayname']?></option>
                                                                            <?php endfor;?>
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
                                            </div>
                                            <?php } ?>
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
                                                <th>Image</th>
                                                <th width="10%">Student ID</th>
                                                <th width="40%">Full Name</th>
                                                <th width="35%">Programme</th>
                                                <th width="10%">&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i=1; do { ?>
                                                <tr>
                                                    <td><?= $i++; ?></td>
                                                    <td>
                                                    <img style="width: 60px; height: 50px;" src="<?= get_pics($row_rsstdnt['stdid'], '../../img/user/student') ?>">
                                                    </td>
                                                    <td><a href="../../student/profile.php?stid=<?php echo $row_rsstdnt['stdid']; ?>"><?php echo $row_rsstdnt['stdid']; ?></a></td>
                                                    <td><?php echo $row_rsstdnt['lname']; ?>, <?php echo ucwords(strtolower($row_rsstdnt['fname'])); ?> <?php echo ucwords(strtolower($row_rsstdnt['mname'])); ?></td>
                                                    <td><?php echo $row_rsstdnt['progname']; ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-cogs"></i><span class="caret"></span></a>
                                                            <ul class="dropdown-menu">
                                                            	<?php if (getAccess() == 20 || getAccess() == 28){?>
                                                                <li>
                                                                    <a href="editstudent.php?stid=<?php echo $row_rsstdnt['stdid']; ?>">Edit</a>
                                                                </li>
                                                                <li>
                                                                    <a href="index.php?stid=<?php echo $row_rsstdnt['stdid']; ?>&action=reset">Reset Password</a>
                                                                </li>
                                                                <li>
                                                                    <a target="_blank" href="../../registration/editform.php?stid=<?php echo $row_rsstdnt['stdid'];?>">
                                                                        Add/Edit Courses
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a target="_blank" href="../payment/search_payment.php?search=<?php echo $row_rsstdnt['stdid'];?>&type=reg&ptype=schfee">
                                                                        Payments
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="#registration" data-toggle="modal" ng-click="setCurrent('<?php echo $row_rsstdnt['stdid']; ?>', '<?php echo $row_rsstdnt['progid']?>')">Registration</a>
                                                                </li>
                                                                <?php }?>
                                                                
                                                                <?php if (getAccess() == 20) { ?>
                                                                <li>
                                                                    <a target="_blank" href="../result/resultstatement.php?stid=<?php echo $row_rsstdnt['stdid'];?>">
                                                                        Edit Result
                                                                    </a>
                                                                </li>
                                                                <?php }?>
                                                                <?php if (getAccess() == 20 || getAccess() == 29)  { ?>
                                                                <li>
                                                                    <a href="#message" data-toggle="modal" ng-click="getContactInfo('<?php echo $row_rsstdnt['stdid']; ?>', '<?php echo $row_rsstdnt['phone']; ?>', '<?php echo $row_rsstdnt['fname']. ' '.$row_rsstdnt['lname'] . ' '. $row_rsstdnt['mname'] ?>', '<?= $row_rsstdnt['progname']?>')">Send SMS</a>
                                                                </li>
                                                                <?php }?>
                                                                <?php if (getAccess() == 20  || $_SESSION['MM_Username'] == 'ict0030')  { ?>
                                                                <li>
                                                                    <a href="#cs_reg" data-toggle="modal" ng-click="getSelectedItem('<?= $row_rsstdnt['stdid']; ?>')">Delete Course Reg</a>
                                                                </li>
                                                                <?php }?>
                                                                <?php if (getAccess() == 20 || getAccess() == 28 )  { ?>
                                                                <li>
                                                                    <a href="#add_cs_reg" data-toggle="modal" ng-click="getReg('<?= $row_rsstdnt['stdid']; ?>')">Set Course Reg</a>
                                                                </li>
                                                                <?php }?>
                                                                
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php }while ($row_rsstdnt = mysql_fetch_assoc($rsstdnt)); ?>
                                        </tbody>
                                    </table>
                                    <?php }else{?>
                                    <div class="alert alert-danger">
                                        SORRY!!! NO Record Available Search by Name or Matric No
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
        
        <div aria-hidden="false" 
             aria-labelledby="myModalLabel" 
             role="dialog" tabindex="-1" 
             class="modal hide fade" 
             id="registration">

            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h3 id="myModalLabel">Student Registration ({{current}})</h3>
            </div>
            <form class="form-vertical" method="post" action="index.php" >
                <div class="modal-body" style="min-height: 300px">
                    <div ng-show="loading" class="center">
                        <img src="../disciplinary/giphy.gif" width="60px" height="60px">
                    </div>
                    
                    <div class="row">
                        <div class="span4">
                            <button class="btn btn-primary" ng-click="addEntry()" type="button">Add New Entry</button>
                        </div>
                    </div>
                    <div>
                        <table class="table table-striped" >
                            <thead>
                                <tr>
                                    <th>Matric. No.</th>
                                    <th>Session</th>
                                    <th>Level</th>
                                    <th>Session Status</th>
                                    <th>Course Status</th>
                                    <th>Programme</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="entry in regEntries">
                                    <input type="hidden" name="del[{{$index}}][stdid]" value="{{entry.stdid}}" ng-disabled="!entry.deleted"/>
                                    <input type="hidden" name="del[{{$index}}][sesid]" value="{{entry.sesid}}" ng-disabled="!entry.deleted"/>
                                    <input type="hidden" name="entry[{{$index}}][newEntry]" value="{{entry.isNew}}" ng-disabled="!entry.isNew && !entry.updated"/>
                                    <input type="hidden" name="entry[{{$index}}][progid]" value="{{entry.progid}}" ng-disabled="!entry.isNew && !entry.updated">
                                    <input type="hidden" name="entry[{{$index}}][approved]" value="{{entry.approved}}" ng-disabled="!entry.isNew && !entry.updated">
                                    <td>{{current}}</td>
                                    <td>
                                        <span ng-hide="entry.isNew || entry.updated">{{entry.sesname}}</span>
                                        <div ng-show="entry.isNew || entry.updated">
                                            <select ng-disabled="!entry.isNew && !entry.updated" name="entry[{{$index}}][sesid]" class="input-mini">
                                                <?php
                                                    do {
                                                        ?>
                                                        <option value="<?php echo $row_sess['sesid'] ?>" ng-selected="entry.sesid=='<?php echo $row_sess['sesid'] ?>'">
                                                            <?php echo $row_sess['sesname'] ?>
                                                        </option>
                                                        <?php
                                                    } while ($row_sess = mysql_fetch_assoc($sess));
                                                ?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <span ng-hide="entry.isNew || entry.updated">{{entry.level}}</span>
                                        <div ng-show="entry.isNew || entry.updated">
                                            <select ng-disabled="!entry.isNew && !entry.updated" name="entry[{{$index}}][level]" class="input-mini">
                                                <?php for (; $row_level = mysql_fetch_assoc($level);) : ?>
                                                    <option value="<?php echo $row_level['levelid'] ?>" ng-selected="entry.level=='<?php echo $row_level['levelid'] ?>'">
                                                        <?php echo $row_level['levelname'] ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <span ng-hide="entry.isNew || entry.updated">{{entry.status}}</span>
                                        <div ng-show="entry.isNew || entry.updated">
                                            <select ng-model="entry.status" ng-disabled="!entry.isNew && !entry.updated" name="entry[{{$index}}][status]" class="input-mini">
                                                <option value="Registered" ng-selected="entry.status=='Registered'">Registered</option>
                                                <option value="Unregistered" ng-selected="entry.status=='Unregistered'">Unregistered</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <span ng-hide="entry.isNew || entry.updated">{{entry.course}}</span>
                                        <div ng-show="entry.isNew || entry.updated">
                                            <select ng-model="entry.course" ng-disabled="!entry.isNew && !entry.updated" name="entry[{{$index}}][course]" class="input-mini">
                                                <option value="Registered" ng-selected="entry.course=='Registered'">Registered</option>
                                                <option value="Unregistered" ng-selected="entry.course=='Unregistered'">Unregistered</option>
                                            </select>
                                        </div>
                                    </td>  
                                    <td>
                                        <span ng-hide="entry.isNew || entry.updated">{{entry.progname}}</span>
                                        <div ng-show="entry.isNew || entry.updated">
                                            <select ng-model="entry.progid" ng-disabled="!entry.isNew && !entry.updated" 
                                                    name="entry[{{$index}}][progid]" class="input-mini">
                                                
                                                <?php  mysql_data_seek($prog, 0);
                                                for (; $row_prog = mysql_fetch_assoc($prog);) : ?>
                                                    <option value="<?php echo $row_prog['progid'] ?>" ng-selected="entry.progid=='<?php echo $row_prog['progid'] ?>'">
                                                        <?php echo $row_prog['progname'] ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </td> 
                                    <td>
                                        <a style="color: red; font-style: normal; font-weight: bolder;" 
                                           ng-hide="entry.deleted" ng-click="delEntry($index)" href="">
                                            <span><i class="fa fa-trash-o"></i></span>
                                        </a>
                                        <a style="color: red; font-style: normal; font-weight: bolder;" 
                                           ng-hide="entry.isNew || entry.deleted" ng-click="setEdit(entry)" href="">
                                            <span><i class="fa fa-edit"></i></span>
                                        </a>
                                        <a style="color: red; font-style: normal; font-weight: bolder;" 
                                           ng-show="entry.deleted" ng-click="reAddEntry($index)" href="">
                                            <span><i class="fa fa-plus"></i></span>
                                        </a>
                                    </td>
                                </tr>

                                <tr ng-show="regEntries.length < 1">
                                    <td colspan="7">There are no registration entries for this student!</td>
                                </tr>
                            </tbody>
                        </table>
                        <input type="hidden" name="stdid" value="{{current}}">
                        <input type="hidden" name="MM_submit" value="mod_reg">
                    </div>

                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit" ng-disabled="count < 1">Submit</button>
                    <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
                </div>
            </form>
        </div>
        
        <div aria-hidden="false" 
             aria-labelledby="myModalLabel" 
             role="dialog" tabindex="-1" 
             class="modal hide fade" 
             id="message">

            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h3 id="myModalLabel">Send message to  ({{contact.stdid}})</h3>
                <h4>{{contact.fullname}}</h4>
                <h4>{{contact.programe}} </h4>
            </div>
            <form class="form" method="post" action="index.php" >
                <div class="modal-body" style="min-height: 300px">
                    <div>
                        <span class="badge badge-success">SMS Credit Balance : {{contact.sms_credit_balance}} </span>
                        <span ng-class="{'badge-warning': sms_body.length > 160}" class="badge ">SMS counter : {{160 - sms_body.length}} </span>
                    </div>
                    <p>&nbsp;</p>
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="controls">
                                <input  type="text" name="sender_name" class="input-block-level"  value="TASUED">
                            </div>
                            <div class="controls">
                                <input  id="textfield"  name="phone" class="input-block-level" type="text" readonly="" value="{{contact.phone}}">
                            </div>
                            
                            <div class="controls">
                                <textarea class="input-block-level" ng-model="sms_body" ng-trim="false" rows="9" name="msg_body" placeholder="message body here "></textarea>
                            </div>
                            <input type="hidden" name="action" value="send_message">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit" >Send Message</button>
                    <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
                </div>
            </form>
        </div>

        <!--Delete Course Reg-->
       <div aria-hidden="false" 
            aria-labelledby="myModalLabel" 
            role="dialog" tabindex="-1" 
            class="modal hide fade" 
            id="cs_reg">

           <div class="modal-header">
               <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
               <h3 id="myModalLabel">Delete Course Registration</h3>
           </div>
           <form class="form" method="post" action="index.php" >
               <div class="modal-body" style="min-height: 300px">
                   <div class="row-fluid">
                       <div class="span12">
                           <div class="controls">
                               <div class="input-append input-prepend">
                                   <span class="add-on"><i class="icon-search"></i> Course Code</span>
                                   <input placeholder="Search here..." class="input-medium" type="text" ng-model="scid" >
                                   <button class="btn" type="button" ng-click="fetchReg(scid)">Search!</button>
                               </div>
                           </div>
                       </div>
                   </div>
                   <div>
                       <table class="table table-condensed table-striped">
                           <thead>
                               <tr>
                                   <th>#</th>
                                   <th>Session</th>
                                   <th>Course</th>
                                   <th>Action </th>
                               </tr>
                           </thead>
                           <tbody>
                               <tr ng-repeat=" reg in result.rs">
                                   <td>{{$index + 1}}</td>
                                   <td>{{reg.sesname}}</td>
                                   <td>{{reg.csid}}</td>
                                   <td>
                                       <a href="#cs_reg1" data-toggle="modal" ng-click="getSelectedItem(reg)"><i class="red icon icon-trash"></i></a>
                                   </td>
                               </tr>

                           </tbody>
                       </table>
                   </div>
               </div>
               <div class="modal-footer">
                   <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
               </div>
           </form>
       </div>
       
       
       <!--Delete Course Reg-->
       <div aria-hidden="false" 
            aria-labelledby="myModalLabel" 
            role="dialog" tabindex="-1" 
            class="modal hide fade" 
            id="add_cs_reg">

           <div class="modal-header">
               <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
               <h3 id="myModalLabel">Add Course Registration</h3>
           </div>
           <form class="form" method="post" action="index.php" >
               <div class="modal-body" style="min-height: 300px">
                   <div ng-if="status.rs">
                       <div class="row-fluid">
                            <div class="span12">
                               <div class="controls">
                                   <div class="input-append input-prepend">
                                       <span class="add-on"><i class="icon-search"></i> Course Code</span>
                                       <input placeholder="Search here..." class="input-medium" type="text" ng-model="scid12" >
                                       <button class="btn" type="button" ng-click="fetchCourse(scid12)">Search!</button>
                                   </div>
                               </div>
                            </div>
                       </div>
                       <div class="well" ng-if="result.rs">
                           <div class="control-group">
                                <label class="control-label" for="de">Student ID</label>
                                <div class="controls" class="input-block-level">
                                    <input type="text" name="stdid" class="input input-medium" readonly="" value="{{selectedItem}}">
                                </div>
                            </div>
                           <div class="control-group">
                                <label class="control-label" for="de">Course Code</label>
                                <div class="controls" class="input-block-level">
                                    <input type="text" name="csid" class="input input-medium" readonly="" value="{{result.rs.csid}}">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="de">Session</label>
                                <div class="controls" class="input-block-level">
                                    <input type="text"  class="input input-medium" required readonly="" value="<?= $row_session['sesname']?>">
                                    <input type="hidden" name="sesid"  value="<?= $row_session['sesid']?>">
                                </div>
                            </div>
                            <div class="control-group">
                                
                                <div class="controls" class="input-block-level">
                                    <button type="submit" name="c_reg_stdt">Submit</button>
                                </div>
                            </div>
                       </div>
                   </div>
               </div>
               <div class="modal-footer">
                   <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
               </div>
           </form>
       </div>
       
       <!--Delete Course Reg2-->
       <div aria-hidden="false" 
            aria-labelledby="myModalLabel" 
            role="dialog" tabindex="-1" 
            class="modal hide fade" 
            id="cs_reg1">

           <div class="modal-header">
               <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
               <h3 id="myModalLabel">Delete Course Registration</h3>
           </div>
           <form class="form" method="post" action="<?= $editFormAction ?>" >
               <div class="modal-body" style="min-height: 300px">
                   <div class="row-fluid">
                       <div class="span12">
                           <div class="alert alert-danger">
                               <p>
                                   You have choose to delete the bellow Course Registration information<br />
                                   <br />
                                   <b>Session :</b> {{selectedItem.sesname}}<br />
                                   <b>Course Code :</b> {{selectedItem.csid}}<br /><br />
                                   Are you sure you want to proceed with the action ?
                               </p>
                           </div>
                       </div>
                   </div>
                   
               </div>
               <div class="modal-footer">
                   <button type="submit" class="btn btn-danger" name="delete_reg" value="{{selectedItem.resultid}}">Yes</button>
                   <button aria-hidden="true" data-dismiss="modal" class="btn">No</button>
               </div>
           </form>
       </div>
        <?php include INCPATH."/footer.php" ?>
    </body>
    <script type="text/javascript">
        angular.module('TamsApp', []).
                
        controller('PageController', ['$scope', '$http', function($scope, $http) {
                
            $scope.current = null;
            $scope.loading = false;
            $scope.regEntries = [];
            $scope.count = 0;
            $scope.contact = {};
            $scope.newEntries = 0;
            $scope.progid = null;

            $scope.setCurrent = function(matric, progid) {
                $scope.regEntries = [];
                $scope.loading = true;
                $scope.current = matric;
		$scope.progid = progid;
                $scope.count = 0;
                $scope.newEntries = 0;
                
                $http.get('getstudent.php?stid='+$scope.current).then(function(res) {
                    $scope.loading = false;
                    
                    if(res.data.status)
                        $scope.regEntries = res.data.entries;
                    else
                        alert(res.data.status_msg);
                }, function() {
                    $scope.loading = false;
                    alert("Error fetching registration entries!");
                });      
            };
            
            $scope.getContactInfo = function (stdid, phone, fullname, programe) {
                            $scope.loading = true;
                            $scope.contact.stdid = stdid;
                            $scope.contact.phone = phone;
                            $scope.contact.fullname = fullname;
                            $scope.contact.programe = programe;
                            $scope.contact.sms_credit_balance = <?= $sms_credit; ?>;
                            

                        };
                        
                        
            $scope.addEntry = function() {
                $scope.regEntries.unshift({
                    "status": "Unregistered",
                    "course": "Unregistered",
                    "progid": $scope.progid,
                    "approved": 'FALSE',
                    "isNew": true, 
                    "deleted": false
                    
                });
                $scope.newEntries++;
                $scope.count++;
            };
            
            $scope.delEntry = function(idx) {
                var entry = $scope.regEntries.splice(idx, 1)[0];               
                
                if(entry.isNew) {
                    $scope.newEntries--;
                    $scope.count--;
                }else {
                    entry.deleted = true;
                    $scope.regEntries.push(entry);   
                    $scope.count++;
                }
            };
            
            $scope.reAddEntry = function(idx) {
                var entry = $scope.regEntries.splice(idx, 1)[0];
                entry.deleted = false;
                $scope.regEntries.splice($scope.newEntries, 0,entry);      
                $scope.count--;
            };
            
            $scope.setEdit = function(entry) {
                entry.updated = !entry.updated;                
                entry.updated? $scope.count++: $scope.count--;
            };
            
            $scope.selectedItem ;
            $scope.getSelectedItem = function(v){
               $scope.selectedItem = v;
            };
            
            $scope.fetchReg = function(csid){
               $http({
                       method : "POST",
                       url : "get_cs_reg.php?action=cs_reg",
                       data: {
                           'csid' : csid,
                           'stdid': $scope.selectedItem
                       }
                   }).then(function mySucces(response) {
                       $scope.result = response.data;
                       $scope.current.loading = false; 
                   }, function myError(response) {
                       $scope.result = response.statusText;
                       $scope.current.loading = false; 
                   });
                   
           };
           
            $scope.getReg = function(stdid){
                $scope.getSelectedItem(stdid);
                
                $http({
                       method : "POST",
                       url : "../disciplinary/api/index.php?action=registration",
                       data: {
                           'stdid': stdid
                       }
                   }).then(function mySucces(response) {
                       
                        if(response.data.status == 'error'){
                           alert(response.data.msg);
                        }else{
                           $scope.status = response.data;
                        }
                        
                   }, function myError(response) { 
                       alert(response.statusText);
                       $scope.current.loading = false; 
                   });
            };
            
            $scope.fetchCourse = function(csid){
                    $http({
                            method : "POST",
                            url : "../result/api.php?action=course",
                            data:  {
                                'course' : csid
                            }, 
                        }).then(function mySucces(response) {
                            $scope.result = response.data;
                            
                        }, function myError(response) {
                            $scope.result = response.statusText;
                        });
                }
        }]);   
    </script>
</html>