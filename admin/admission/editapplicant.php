<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

define('MAX_FILE_SIZE', 2048 * 1536);
define('UPLOAD_DIR', IMGPATH.'/user/prospective/');

$auth_users = "20,24,28";
check_auth($auth_users, $site_root.'/admin');

$applicantId = '-1';
if (isset($_GET['jambregid'])) {
    $applicantId = $_GET['jambregid'];
}

$query_rschk = sprintf("SELECT p.*, s.* , su1.subjname AS subj1, su2.subjname AS subj2, su3.subjname AS subj3, su4.subjname AS subj4 "
        . "FROM prospective p "
        . " LEFT JOIN session s ON p.sesid = s.sesid "
        . " LEFT JOIN subject su1 ON p.jambsubj1 = su1.subjid "
        . " LEFT JOIN subject su2 ON p.jambsubj2 = su2.subjid "
        . " LEFT JOIN subject su3 ON p.jambsubj3 = su3.subjid "
        . " LEFT JOIN subject su4 ON p.jambsubj4 = su4.subjid "
        . "WHERE p.jambregid=%s", 
        GetSQLValueString($applicantId, "text"));
$rschk = mysql_query($query_rschk, $tams) or die(mysql_error());
$row_rschk = mysql_fetch_assoc($rschk);

$totalRows_rschk = mysql_num_rows($rschk);

//if ($row_rschk['admtype'] == 'DE') {
//    header(sprintf('Location: editapplicant2.php?jambreg=%s', $applicantId));
//}


$code = array(
            'jambregid' =>   $row_rschk['jambregid'],
            'fname'     =>   $row_rschk['fname'],
            'lname'     =>   $row_rschk['lname'],
            'mname'     =>   $row_rschk['mname'],
            'pstdid'    =>   $row_rschk['pstdid'],
            'ver_code'  =>   $row_rschk['ver_code'],
            'progid1'   =>   (int)$row_rschk['progid1'],
            'typename'  =>   $row_rschk['typename'],
            'progname'  =>   $row_rschk['progname'],
            'phone'     =>   $row_rschk['phone'],
            'email'     =>   $row_rschk['email'],
            'sesid'     =>   (int)$row_rschk['sesid'],
            'regtypename'=>  $row_rschk['regtypename'],
            'regtypeid'=>  $row_rschk['regtypeid'],
            'jambsubj1' => (isset($row_rschk['subj1'])) ? $row_rschk['subj1'] : '',
            'jambsubj2' => (isset($row_rschk['subj2'])) ? $row_rschk['subj2'] : '',
            'jambsubj3' => (isset($row_rschk['subj3'])) ? $row_rschk['subj3'] : '',
            'jambsubj4' => (isset($row_rschk['subj4'])) ? $row_rschk['subj4'] : '',
            'jambscore1' => (int) $row_rschk['jambscore1'],
            'jambscore2' =>  (int)$row_rschk['jambscore2'],
            'jambscore3' =>  (int)$row_rschk['jambscore3'],
            'jambscore4' =>  (int)$row_rschk['jambscore4']
            
        );  


$query_rssit1 = sprintf("SELECT * 
                        FROM olevel o 
                        JOIN olevelresult l ON o.olevelid = l.olevelid 
                        JOIN subject s ON l.subject = s.subjid 
                        JOIN grade g ON l.grade = g.grdid 
                        WHERE o.jambregid=%s
                        AND sitting='first'", GetSQLValueString($row_rschk['jambregid'], "text"));
$rssit1 = mysql_query($query_rssit1, $tams) or die(mysql_error());
$row_rssit1 = mysql_fetch_assoc($rssit1);
$totalRows_rssit1 = mysql_num_rows($rssit1);

$query_rssit2 = sprintf("SELECT * 
                        FROM olevel o 
                        JOIN olevelresult l ON o.olevelid = l.olevelid 
                        JOIN subject s ON l.subject = s.subjid 
                        JOIN grade g ON l.grade = g.grdid 
                        WHERE o.jambregid=%s
                        AND sitting='second'", GetSQLValueString($row_rschk['jambregid'], "text"));

$rssit2 = mysql_query($query_rssit2, $tams) or die(mysql_error());
$row_rssit2 = mysql_fetch_assoc($rssit2);
$totalRows_rssit2 = mysql_num_rows($rssit2);

$msg = [];

$sesid = $row_rschk['sesid'];
$sesname = $row_rschk['sesname'];
$parts = explode('/', $sesname);

if (isset($_POST['save']) && $_POST['save'] != '') {

    $updatefields = [];
    if (isset($_POST['profile'])) {
        foreach ($_POST['profile'] as $field => $value) {
            $updatefields[] = "{$field} = " . GetSQLValueString($value, "text");
            $edit[$field] = array('old'=>$row_rschk[$field], 'new'=> $value);
        }
    }

    if (isset($_POST['acad'])) {
        foreach ($_POST['acad'] as $field => $value) {
            $prefix = ($field == 'score') ? 'jambscore' : 'jambsubj';
            $edit[$field] = array('old'=>$row_rschk[$field], 'new'=> $value);
            foreach ($value as $num => $val) {
                $updatefields[] = "{$prefix}{$num} = " . GetSQLValueString($val, "int");
                
            }
        }
    }

    if (isset($_POST['profile']) || isset($_POST['acad'])) {
        $updatefields = implode(', ', $updatefields);

        $updateSQL = sprintf("UPDATE prospective SET %s WHERE jambregid = %s", GetSQLValueString($updatefields, 'defined', $updatefields), GetSQLValueString($applicantId, "text"));
        $updatefields = [];
        $profile_update = mysql_query($updateSQL, $tams) or die(mysql_error());
        
        
        
    }

    if (isset($_FILES['filename'])) {
        $upload = uploadFile(UPLOAD_DIR.$parts[0].'/', "prospective", MAX_FILE_SIZE);
    }

    $olevel = [];
    // Process O'level exam
    if (isset($_POST['result'])) {
        $resultfields = [
            'jambregid' => 'jambregid',
            'exmtyp' => 'examtype',
            'exmyr' => 'examyear',
            'examnumber' => 'examnumber',
            'sitting' => 'sitting'
        ];

        foreach ($_POST['result'] as $olevelid => $param) {
            if (!in_array($olevelid, ['first', 'second'])) {
                foreach ($param as $field => $value) {
                    if (in_array($value, ['-1', ''])) {
                        // Add to error.
                        continue;
                    }

                    $updatefields[] = "{$resultfields[$field]} = " . GetSQLValueString($value, "text");
                }
                $updatefields = implode(', ', $updatefields);
                $updatequery = sprintf("UPDATE olevel SET %s WHERE olevelid = %s", GetSQLValueString($updatefields, "defined", $updatefields), GetSQLValueString($olevelid, "text"));
                $result_update = mysql_query($updatequery, $tams) or die(mysql_error());
                $updatefields = [];
            } else {
                $error = false;

                $namemap = [
                    'exmtyp' => 'Exam Type',
                    'exmyr' => 'Exam Year',
                    'examnumber' => 'Exam Number',
                ];

                $param['sitting'] = $olevelid;
                $param['jambregid'] = $applicantId;

                foreach ($resultfields as $field => $value) {
                    if (in_array($param[$field], ['-1', ''])) {
                        // Add to error.
                        $msg[] = "Cannot insert {$olevelid} O'level sitting, "
                                . "contains an empty/invalid value: {$namemap[$field]}";
                        $error = true;
                        break;
                    }
                    $insertvalues[] = $param[$field];
                }

                if (!$error) {
                    $insertfields = implode(", ", array_values($resultfields));
                    $insertvalues = implode("', '", $insertvalues);
                    $insertquery = sprintf("INSERT INTO olevel (%s) VALUES ('%s')", GetSQLValueString($insertfields, "defined", $insertfields), GetSQLValueString($insertvalues, "defined", $insertvalues));
                    $result_entry = mysql_query($insertquery, $tams) or die(mysql_error());
                    $olevel[$olevelid] = mysql_insert_id();
                    $updatefields = [];
                }
            }
        }
        
        
    }

    $studRecSQL = sprintf("SELECT * FROM student WHERE jambregid = %s ", GetSQLValueString($applicantId, "text"));
    $studRecRs = mysql_query($studRecSQL, $tams) or die(mysql_error());
    $studRecFound = mysql_num_rows($studRecRs);
    
    //$edit['programme'] = array('old' =>  $row_rschk['progoffered'] , 'new'=> $_POST['profile']['progoffered']);
    
    if($studRecRs > 0){
        $studUpdateSQL = sprintf("UPDATE student SET progid = %s  WHERE jambregid = %s", GetSQLValueString($_POST['profile']['progoffered'], "int"), GetSQLValueString($applicantId, "text"));
        $studUpdateRs = mysql_query($studUpdateSQL, $tams) or die(mysql_error());
    }
        
    // Process O'level result
    if (isset($_POST['grade'])) {

        foreach ($_POST['grade'] as $gradeid => $param) {
            $updatequery = "UPDATE olevelresult SET %s WHERE resultid = "
                    . GetSQLValueString($gradeid, "int");

            foreach ($param as $field => $value) {
                $updatefields[] = GetSQLValueString($field, "defined", $field) . " = " . GetSQLValueString($value, "int");
            }

            $updatefields = implode(', ', $updatefields);
            $updatequery = sprintf($updatequery, GetSQLValueString($updatefields, "defined", $updatefields));

            $updatefields = [];
            $result_update = mysql_query($updatequery, $tams) or die(mysql_error());
        }
    }

    // Process O'level new result
    if (isset($_POST['newgrade'])) {
        $insertvalues = [];
        $insertstring = "INSERT INTO olevelresult (olevelid, subject, grade) VALUES (%s) ";

        foreach ($_POST['newgrade'] as $olevelid => $types) {
            // Check if the olevelid is either first or second.
            // If it is, further check whether it has been inserted by a query from above.
            if (in_array($olevelid, ['first', 'second']) && !isset($olevel[$olevel])) {
                continue;
            }

            // Assign the new insertion id to olevelid
            $oldid = $olevelid;
            $olevelid = isset($olevel[$olevelid]) ? $olevel[$olevelid] : $olevelid;

            foreach ($types as $type => $items) {

                if ($type == 'subject') {

                    foreach ($items as $count => $item) {

                        if ($item == '-1' || !isset($_POST['newgrade'][$oldid]['grade'][$count]) ||
                                $_POST['newgrade'][$oldid]['grade'][$count] == '-1') {
                            continue;
                        }

                        $insertvalues[] = GetSQLValueString($olevelid, 'int');
                        $insertvalues[] = GetSQLValueString($item, 'int');
                        $insertvalues[] = GetSQLValueString($_POST['newgrade'][$oldid]['grade'][$count], 'int');
                        $insertvalues = implode(', ', $insertvalues);

                        $insertquery = sprintf($insertstring, GetSQLValueString($insertvalues, "defined", $insertvalues));
                        $result_insert = mysql_query($insertquery, $tams) or die(mysql_error());
                        $insertvalues = [];
                    }
                }
            }
        }
    }
    
    $params['entid'] = $applicantId;
    $params['enttype'] = 'prospective';
    $params['action'] = 'edit';
    $params['cont'] = json_encode($edit);
    audit_log($params);
}

        
        //$params['entid'] = $applicantId;
        //$params['enttype'] = 'prospective';
        //$params['action'] = 'edit';
        //$params['cont'] = json_encode($edit);
        //audit_log($params);
        
$query = sprintf("SELECT * FROM session ORDER BY sesid DESC");
$session = mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);
$totalRows_session = mysql_num_rows($session);

$query_rschk = sprintf("SELECT * 
                        FROM prospective  
                        WHERE jambregid=%s", GetSQLValueString($applicantId, "text"));
$rschk = mysql_query($query_rschk, $tams) or die(mysql_error());
$row_rschk = mysql_fetch_assoc($rschk);
$totalRows_rschk = mysql_num_rows($rschk);

$query_rssit1 = sprintf("SELECT *, o.olevelid as olevelid 
                        FROM olevel o 
                        LEFT JOIN olevelresult l ON o.olevelid = l.olevelid 
                        WHERE o.jambregid = %s
                        AND sitting = 'first'", GetSQLValueString($row_rschk['jambregid'], "text"));
$rssit1 = mysql_query($query_rssit1, $tams) or die(mysql_error());
$row_rssit1 = mysql_fetch_assoc($rssit1);
$totalRows_rssit1 = mysql_num_rows($rssit1);


$query_rssit2 = sprintf("SELECT *, o.olevelid as olevelid 
                        FROM olevel o 
                        LEFT JOIN olevelresult l ON o.olevelid = l.olevelid 
                        WHERE o.jambregid = %s
                        AND sitting = 'second'", GetSQLValueString($row_rschk['jambregid'], "text"));
$rssit2 = mysql_query($query_rssit2, $tams) or die(mysql_error());
$row_rssit2 = mysql_fetch_assoc($rssit2);
$totalRows_rssit2 = mysql_num_rows($rssit2);

$query_rsprg = sprintf("SELECT progid, progname FROM programme");
$rsprg = mysql_query($query_rsprg, $tams) or die(mysql_error());
$row_rsprg = mysql_fetch_assoc($rsprg);
$totalRows_rsprg = mysql_num_rows($rsprg);

$query_state = "SELECT* FROM state";
$state = mysql_query($query_state, $tams) or die(mysql_error());
$row_state = mysql_fetch_assoc($state);
$totalRows_state = mysql_num_rows($state);

$query_rssubj = "SELECT * FROM subject";
$rssubj = mysql_query($query_rssubj, $tams) or die(mysql_error());
$row_rssubj = mysql_fetch_assoc($rssubj);
$totalRows_rssubj = mysql_num_rows($rssubj);


$query_rsgrd = "SELECT * FROM grade";
$rsgrd = mysql_query($query_rsgrd, $tams) or die(mysql_error());
$row_rsgrd = mysql_fetch_assoc($rsgrd);
$totalRows_rsgrd = mysql_num_rows($rsgrd);

$query_admtype = sprintf("SELECT * "
                        . "FROM admissions a "
                        . "JOIN admission_type at ON a.typeid = at.typeid "
                        . "JOIN session s ON a.sesid = s.sesid");
$admtype = mysql_query($query_admtype, $tams) or die(mysql_error());
$totalRows_admtype = mysql_num_rows($admtype);

$ses_folder = explode('/', $_SESSION['admname']);
$image_url = get_pics($applicantId, IMGPATH."/user/prospective/{$ses_folder[0]}");
?>
<!doctype html>
<html ng-app="tams">
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="PageController"> 
        <?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH."/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH."/page_header.php" ?>

                    <div class="row-fluid">
                        <div class="box box-bordered box-color">
                            <div class="box-title">
                                <h3><i class="icon-reorder"></i>
                                    Edit Applicant (<?php echo $applicantId?>)
                                </h3>
                            </div>
                            <div class="box-content ">  
                                <form action="" method="post" enctype="multipart/form-data" name="form1" id="form1" class="form-signin" >
                                    <p>&nbsp; </p>
                                    <table class="table table-striped">
                                        <tr>
                                            <td>
                                                <fieldset >
                                                    <legend>Bio Data</legend>
                                                    <table border="0" class="table">
                                                        <tr>
                                                            <td>
                                                                <img  style="alignment-adjust: central" src="<?php echo $image_url; ?>" alt="Image"  id="placeholder" name="placeholder" width="160" height="160" align="top"/>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <table  width="320" border="0"  class="table table-hover table-striped table-bordered">
                                                        <tr>
                                                            <td>UTME No</td>
                                                            <td><strong><?php echo $row_rschk['jambregid'] ?></strong></td>
                                                        </tr> 

                                                        <tr>
                                                            <td width="169" align="left" valign="top">First Name :</td>
                                                            <td width="442" align="left" valign="top">
                                                                <input type="text" name="profile[fname]" id="fname" value="<?php echo $row_rschk['fname'] ?>"  disabled toggle-field-edit/>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td valign="top" align="left">Middle Name : </td>
                                                            <td align="left" valign="top" >
                                                                <input type="text" name="profile[mname]" id="mname" value="<?php echo $row_rschk['mname'] ?>" disabled toggle-field-edit/>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td valign="top" align="left">Last Name : </td>
                                                            <td align="left" valign="top">
                                                                <input type="text" name="profile[lname]" id="lname" value="<?php echo $row_rschk['lname'] ?>" disabled toggle-field-edit/>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td valign="top" align="left">Sex :</td>
                                                            <td align="left" valign="top">
                                                                <select name="profile[sex]" id="sex" required disabled toggle-field-edit>
                                                                    <option value="-1">--Choose--</option>
                                                                    <option value="male" <?php if ('male' == $row_rschk['sex']) echo 'selected' ?>>Male</option>
                                                                    <option value="female" <?php if ('female' == $row_rschk['sex']) echo 'selected' ?>>Female</option>
                                                                </select>

                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td valign="top" align="left">Date of Birth :</td>
                                                            <td align="left" valign="top">
                                                                <input type="text" name="profile[dob]" id="dob" value="<?php echo $row_rschk['dob']; ?>"  disabled toggle-field-edit/>
                                                                <span style="color: #999999"> YYYY-MM-DD</span>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td align="left" valign="top">Health Status : </td>
                                                            <td align="left" valign="top">
                                                                <?php $health = $row_rschk['healthstatus']; ?>
                                                                <select name="profile[healthstatus]" id="health" disabled toggle-field-edit>
                                                                    <option value="-1">--Choose--</option>
                                                                    <option value="Fit" <?php if ('fit' == $health) echo 'selected' ?>>Fit</option>
                                                                    <option value="Disable" <?php if ('disabled' == $health) echo 'selected' ?>>Disabled</option>
                                                                </select>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td align="left" valign="top">Passport  : </td>
                                                            <td align="left" valign="top">
                                                                <div class="fileupload fileupload-new" data-provides="fileupload">
                                                                    <div class="fileupload-new thumbnail" style="width: 200px; height: 150px;">
                                                                        <img src="<?php echo $image_url?>" />
                                                                    </div>
                                                                    <div class="fileupload-preview fileupload-exists thumbnail" 
                                                                         style="max-width: 200px; max-height: 150px; line-height: 20px;"></div>
                                                                    <div>
                                                                        <span class="btn btn-file">
                                                                            <span class="fileupload-new">Browse</span>
                                                                            <span class="fileupload-exists">Change</span>
                                                                            <input type="file" name='filename' id="image" disabled toggle-field-edit/>
                                                                        </span>
                                                                        <a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Remove</a>
                                                                    </div>
                                                                </div>
                                                                
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td align="left" valign="top">&nbsp;</td>
                                                            <td align="left" valign="top">&nbsp;</td>
                                                        </tr>
                                                    </table>
                                                    <p>&nbsp;</p>
                                                </fieldset>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <fieldset>
                                                    <legend>Personal Data</legend>
                                                    <table class="table table-hover table-striped table-bordered">
                                                        <tr>
                                                            <td valign="top" align="left">E-mail Address : </td>
                                                            <td align="left" valign="top">
                                                                <input type="text" name="profile[email]" id="email" value="<?php echo $row_rschk['email'] ?>" size="50" disabled toggle-field-edit/>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td valign="top" align="left">Phone No : </td>
                                                            <td align="left" valign="top">
                                                                <input type="text" name="profile[phone]" id="phone" value="<?php echo $row_rschk['phone'] ?>" disabled toggle-field-edit/>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td align="left" valign="top">Address : </td>
                                                            <td align="left" valign="top">
                                                                <textarea name="profile[address]" id="address" cols="35" rows="5" disabled toggle-field-edit>
                                                                    <?php echo $row_rschk['address'] ?>
                                                                </textarea>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td align="left" valign="top">Religion : </td>
                                                            <td align="left" valign="top">
                                                                <?php $religion = strtolower($row_rschk['religion']); ?>
                                                                <select name="profile[religion]" id="religion" disabled toggle-field-edit>
                                                                    <option value="-1">--Choose--</option>
                                                                    <option value="christianity" <?php if ('christianity' == $religion) echo 'selected' ?>>Christianity</option>
                                                                    <option value="islam" <?php if ('islam' == $religion) echo 'selected' ?>>Islam</option>
                                                                    <option value="traditional" <?php if ('traditional' == $religion) echo 'selected' ?>>Islam</option>
                                                                    <option value="others" <?php if ('others' == $religion) echo 'selected' ?>>Islam</option>
                                                                </select>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td align="left" valign="top">State Of Origin :</td>
                                                            <td align="left" valign="top">
                                                                <select name="profile[stid]" id="soforig" disabled toggle-field-edit>
                                                                    <option value="-1">Choose</option>
                                                                    <?php do { ?>
                                                                        <option value="<?php echo $row_state['stid'] ?>" 
                                                                                <?php if ($row_state['stid'] == $row_rschk['stid']) echo 'selected' ?>>
                                                                                    <?php echo $row_state['stname'] ?>
                                                                        </option>
                                                                        <?php
                                                                    }while ($row_state = mysql_fetch_assoc($state));

                                                                    $rows = mysql_num_rows($state);
                                                                    if ($rows > 0) {
                                                                        mysql_data_seek($state, 0);
                                                                        $row_state = mysql_fetch_assoc($state);
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td align="left" valign="top">L.G.A :</td>
                                                            <td align="left" valign="top">
                                                                <?php
                                                                $lga = '';
                                                                if ($row_rschk['stid'] == '27') {
                                                                    $lga = $row_rschk['lga'];
                                                                } else {
                                                                    $lga = 'Others';
                                                                }
                                                                ?>
                                                                <select name="profile[lga]" id="lga" disabled toggle-field-edit>
                                                                    <option  value="-1">Choose</option>
                                                                    <option class="og" value="Abeokuta North" <?php if ('Abeokuta North' == $lga) echo 'selected' ?>>Abeokuta North</option>
                                                                    <option class="og" value="Abeokuta South" <?php if ('Abeokuta South' == $lga) echo 'selected' ?>>Abeokuta South</option>
                                                                    <option class="og" value="Ado-Odo/Ota" <?php if ('Ado-Odo/Ota' == $lga) echo 'selected' ?>>Ado-Odo/Ota</option>
                                                                    <option class="og" value="Egbado North" <?php if ('Egbado North' == $lga) echo 'selected' ?>>Egbado North</option>
                                                                    <option class="og" value="Egbado South" <?php if ('Egbado South' == $lga) echo 'selected' ?>>Egbado South</option>
                                                                    <option class="og" value="Ewekoro" <?php if ('Ewekoro' == $lga) echo 'selected' ?>>Ewekoro</option>
                                                                    <option class="og" value="Ifo" <?php if ('Ifo' == $lga) echo 'selected' ?>>Ifo</option>
                                                                    <option class="og" value="Ijebu East" <?php if ('Ijebu East' == $lga) echo 'selected' ?>>Ijebu East</option>
                                                                    <option class="og" value="Ijebu North" <?php if ('Ijebu North' == $lga) echo 'selected' ?>>Ijebu North</option>
                                                                    <option class="og" value="Ijebu North East" <?php if ('Ijebu North East' == $lga) echo 'selected' ?>>Ijebu North East</option>
                                                                    <option class="og" value="Ijebu Ode" <?php if ('Ijebu-Ode' == $lga) echo 'selected' ?>>Ijebu Ode</option>
                                                                    <option class="og" value="Ikenne" <?php if ('Ikenne' == $lga) echo 'selected' ?>>Ikenne</option>
                                                                    <option class="og" value="Imeko-Afon" <?php if ('Imeko-Afon' == $lga) echo 'selected' ?>>Imeko-Afon</option>
                                                                    <option class="og" value="Ipokia" <?php if ('Ipokia' == $lga) echo 'selected' ?>>Ipokia</option>
                                                                    <option class="og" value="Obafemi-Owode" <?php if ('Obafemi-Owode' == $lga) echo 'selected' ?>>Obafemi-Owode</option>
                                                                    <option class="og" value="Ogun Waterside" <?php if ('Ogun Waterside' == $lga) echo 'selected' ?>>Ogun Waterside</option>
                                                                    <option class="og" value="Odeda" <?php if ('Odeda' == $lga) echo 'selected' ?>>Odeda</option>
                                                                    <option class="og" value="Odogbolu" <?php if ('Odogbolu' == $lga) echo 'selected' ?>>Odogbolu</option>
                                                                    <option class="og" value="Remo North" <?php if ('Remo North' == $lga) echo 'selected' ?>>Remo North</option>
                                                                    <option class="og" value="Shagamu" <?php if ('Shagamu' == $lga) echo 'selected' ?>>Shagamu</option>
                                                                    <option class="others" value="Others" <?php if ('Others' == $lga) echo 'selected' ?>>Others</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td valign="top" align="left">Sponsor&apos;s Name : </td>
                                                            <td align="left" valign="top">
                                                                <input type="text" name="profile[sponsorname]" id="sponsorname" value="<?php echo $row_rschk['sponsorname'] ?>" disabled toggle-field-edit/>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td valign="top" align="left">Sponsor&apos;s Phone : </td>
                                                            <td align="left" valign="top">
                                                                <input type="text" name="profile[sponsorphn]" id="sponsorphn" value="<?php echo $row_rschk['sponsorphn'] ?>" disabled toggle-field-edit/>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td valign="top" align="left">Sponsor&apos;s Address : </td>
                                                            <td align="left" valign="top">
                                                                <textarea name='profile[sponsoradrs]' cols="35" rows="5"disabled toggle-field-edit>
                                                                    <?php echo $row_rschk['sponsoradrs'] ?>
                                                                </textarea>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </fieldset> 
                                            </td>
                                        </tr>
                                    </table>

                                    <table>
                                        <tr>
                                            <td width="651">
                                                <fieldset>
                                                    <legend>Academic Data</legend>
                                                    <table width="622" border="0" align="left">
                                                        <tr>
                                                            <td colspan="4">
                                                                <p>&nbsp;</p>
                                                                <table width="320" class="table table-hover table-striped table-bordered">
                                                                    <tr>
                                                                        <th colspan="2"> UTME ENTRY</th>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>UTME Reg No.</td>
                                                                        <td align="left">
                                                                            <input type="text" name="profile[jambregid]" value="<?php echo $row_rschk['jambregid'] ?>" disabled toggle-field-edit/>
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td>UTME Year.</td>
                                                                        <td align="left">
                                                                            <input type="text" name="profile[jambyear]" value="<?php echo $row_rschk['jambyear'] ?>" disabled toggle-field-edit/>
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <th>Subject</th>
                                                                        <th>Score</th>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>English Language </td>
                                                                        <td>
                                                                            <input type="text" name="acad[score][1]" value="<?php echo $row_rschk['jambscore1'] ?>" disabled toggle-field-edit/>
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td>
                                                                            <select name="acad[subject][2]" disabled toggle-field-edit>
                                                                                <option value="-1">--Choose--</option>
                                                                                <?php
                                                                                do {
                                                                                    ?>
                                                                                    <option value="<?php echo $row_rssubj['subjid'] ?>" 
                                                                                            <?php if ($row_rssubj['subjid'] == $row_rschk['jambsubj2']) echo 'selected' ?>>
                                                                                                <?php echo $row_rssubj['subjname'] ?>
                                                                                    </option>
                                                                                    <?php
                                                                                } while ($row_rssubj = mysql_fetch_assoc($rssubj));

                                                                                $rows = mysql_num_rows($rssubj);
                                                                                if ($rows > 0) {
                                                                                    mysql_data_seek($rssubj, 0);
                                                                                    $row_rssubj = mysql_fetch_assoc($rssubj);
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" name="acad[score][2]" value="<?php echo $row_rschk['jambscore2'] ?>" disabled toggle-field-edit/>
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td>
                                                                            <select name="acad[subject][3]" disabled toggle-field-edit>
                                                                                <option value="-1">--Choose--</option>
                                                                                <?php
                                                                                do {
                                                                                    ?>
                                                                                    <option value="<?php echo $row_rssubj['subjid'] ?>" 
                                                                                            <?php if ($row_rssubj['subjid'] == $row_rschk['jambsubj3']) echo 'selected' ?>>
                                                                                                <?php echo $row_rssubj['subjname'] ?>
                                                                                    </option>
                                                                                    <?php
                                                                                } while ($row_rssubj = mysql_fetch_assoc($rssubj));

                                                                                $rows = mysql_num_rows($rssubj);
                                                                                if ($rows > 0) {
                                                                                    mysql_data_seek($rssubj, 0);
                                                                                    $row_rssubj = mysql_fetch_assoc($rssubj);
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" name="acad[score][3]" value="<?php echo $row_rschk['jambscore3'] ?>" disabled toggle-field-edit/>
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td>
                                                                            <select name="acad[subject][4]" disabled toggle-field-edit>
                                                                                <option value="-1">--Choose--</option>
                                                                                <?php do { ?>
                                                                                    <option value="<?php echo $row_rssubj['subjid'] ?>" 
                                                                                            <?php if ($row_rssubj['subjid'] == $row_rschk['jambsubj4']) echo 'selected' ?>>
                                                                                                <?php echo $row_rssubj['subjname'] ?>
                                                                                    </option>
                                                                                    <?php
                                                                                } while ($row_rssubj = mysql_fetch_assoc($rssubj));

                                                                                $rows = mysql_num_rows($rssubj);
                                                                                if ($rows > 0) {
                                                                                    mysql_data_seek($rssubj, 0);
                                                                                    $row_rssubj = mysql_fetch_assoc($rssubj);
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" name="acad[score][4]" value="<?php echo $row_rschk['jambscore4'] ?>" disabled toggle-field-edit/>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                                <p>&nbsp;</p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2">
                                                                <p>&nbsp;</p>
                                                                <table width="320" class="table table-hover table-striped table-bordered">
                                                                    <tr>
                                                                        <th colspan="2"> Programme Choices</th>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>1st Choice of Programme : </td>
                                                                        <td align="left">
                                                                            <select name="profile[progid1]" id="progid1" style="width: 400px" disabled toggle-field-edit>
                                                                                <option value="-1">--Choose programme--</option>

                                                                                <?php do { ?>

                                                                                    <option value="<?php echo $row_rsprg['progid'] ?>" 
                                                                                            <?php if ($row_rsprg['progid'] == $row_rschk['progid1']) echo 'selected' ?>>
                                                                                                <?php echo $row_rsprg['progname'] ?>
                                                                                    </option>

                                                                                    <?php
                                                                                } while ($row_rsprg = mysql_fetch_assoc($rsprg));

                                                                                $rows = mysql_num_rows($rsprg);
                                                                                if ($rows > 0) {
                                                                                    mysql_data_seek($rsprg, 0);
                                                                                    $row_rsprg = mysql_fetch_assoc($rsprg);
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td> 2nd Choice of programme :</td>
                                                                        <td align="left">
                                                                            <select name="profile[progid2]" id="progid2" style="width: 400px" disabled toggle-field-edit>
                                                                                <option value="-1">--Choose programme--</option>

                                                                                <?php do { ?>

                                                                                    <option value="<?php echo $row_rsprg['progid'] ?>" 
                                                                                            <?php if ($row_rsprg['progid'] == $row_rschk['progid2']) echo 'selected' ?>>
                                                                                                <?php echo $row_rsprg['progname'] ?>
                                                                                    </option>

                                                                                    <?php
                                                                                } while ($row_rsprg = mysql_fetch_assoc($rsprg));

                                                                                $rows = mysql_num_rows($rsprg);
                                                                                if ($rows > 0) {
                                                                                    mysql_data_seek($rsprg, 0);
                                                                                    $row_rsprg = mysql_fetch_assoc($rsprg);
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                                <p>&nbsp;</p>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td><strong>O'Level 1st Sitting </strong></td>
                                                            <td><strong> O'Level 2nd Sitting </strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <table width="309" border="0" class="table table-hover table-striped table-bordered">
                                                                    <tr>
                                                                        <td>Exam No : </td>
                                                                        <td>
                                                                            <?php $id = (isset($row_rssit1['olevelid'])) ? $row_rssit1['olevelid'] : 'first'; ?>
                                                                            <input name="result[<?php echo $id ?>][examnumber]" 
                                                                                   type="text" id="examnumber" size="10" value="<?php echo $row_rssit1['examnumber'] ?>" disabled toggle-field-edit/>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td width="155">Exam Type : </td>
                                                                        <td width="133">
                                                                            <?php $exmtyp = $row_rssit1['examtype']; ?>   
                                                                            <select name="result[<?php echo $id ?>][exmtyp]" id="exmtyp" style="width: 85px" disabled toggle-field-edit>
                                                                                <option value="-1">--Choose--</option>
                                                                                <option value="WASCE(MAY/JUNE)" <?php if ("WASCE(MAY/JUNE)" == $exmtyp) echo 'selected' ?>>WASCE(MAY/JUNE)</option>
                                                                                <option value="WASCE(Private)" <?php if ("WASCE(Private)" == $exmtyp) echo 'selected' ?>>WASCE(Private)</option>
                                                                                <option value="NECO" <?php if ("NECO" == $exmtyp) echo 'selected' ?>>NECO</option>
                                                                                <option value="NECO(Private)" <?php if ("NECO(Private)" == $exmtyp) echo 'selected' ?>>NECO(Private)</option>
                                                                                <option value="NABTEB(MAY/JUNE)" <?php if ("NABTEB(MAY/JUNE)" == $exmtyp) echo 'selected' ?>>NABTEB(MAY/JUNE)</option>
                                                                                <option value="NABTEB(Private)" <?php if ("NABTEB(Private)" == $exmtyp) echo 'selected' ?>>NABTEB(Private)</option>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Exam Year : </td>
                                                                        <td>
                                                                            <input name="result[<?php echo $id ?>][exmyr]" type="text" id="exmyr" size="8" value="<?php echo $row_rssit1['examyear'] ?>" maxlength="4" disabled toggle-field-edit/>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Subject </strong></td>
                                                                        <td><strong>Grade</strong></td>
                                                                    </tr>
                                                                    <?php
                                                                    $totalRows_rssit1 = $row_rssit1['resultid'] == NULL ? 0 : $totalRows_rssit1;
                                                                    for ($i = 1; $i <= $totalRows_rssit1; $i++) {
                                                                        ?>
                                                                        <tr>
                                                                            <td>
                                                                                <?php
                                                                                $sub = $row_rssit1['subject'];
                                                                                ?> 
                                                                                <select name="grade[<?php echo $row_rssit1['resultid'] ?>][subject]" id="subj['name'][]" disabled toggle-field-edit>
                                                                                    <option value="-1">--Choose--</option>
                                                                                    <?php do { ?>
                                                                                        <option value="<?php echo $row_rssubj['subjid'] ?>" <?php if ($row_rssubj['subjid'] == $sub) echo 'selected' ?>><?php echo $row_rssubj['subjname'] ?></option>
                                                                                        <?php
                                                                                    } while ($row_rssubj = mysql_fetch_assoc($rssubj));

                                                                                    $rows = mysql_num_rows($rssubj);
                                                                                    if ($rows > 0) {
                                                                                        mysql_data_seek($rssubj, 0);
                                                                                        $row_rssubj = mysql_fetch_assoc($rssubj);
                                                                                    }
                                                                                    ?>
                                                                                </select>                                                                            
                                                                            </td>
                                                                            <td>
                                                                                <?php
                                                                                $grade = $row_rssit1['grade'];
                                                                                ?> 
                                                                                <select name="grade[<?php echo $row_rssit1['resultid'] ?>][grade]" id="subj['grade'][]" disabled toggle-field-edit>
                                                                                    <option value="-1">grade</option>
                                                                                    <?php do { ?>
                                                                                        <option value="<?php echo $row_rsgrd['grdid'] ?>" <?php if ($row_rsgrd['grdid'] == $grade) echo 'selected' ?>><?php echo $row_rsgrd['grdname'] ?></option>
                                                                                        <?php
                                                                                    } while ($row_rsgrd = mysql_fetch_assoc($rsgrd));

                                                                                    $rows = mysql_num_rows($rsgrd);
                                                                                    if ($rows > 0) {
                                                                                        mysql_data_seek($rsgrd, 0);
                                                                                        $row_rsgrd = mysql_fetch_assoc($rsgrd);
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </td>
                                                                        </tr>
                                                                        <?php $row_rssit1 = mysql_fetch_assoc($rssit1);
                                                                    }
                                                                    ?>

                                                                    <?php for ($i = 0; $i < 9 - $totalRows_rssit1; $i++) { ?>
                                                                        <tr>
                                                                            <td>
                                                                                <select name="newgrade[<?php echo $id ?>][subject][<?php echo $i ?>]" id="subj['name'][]" disabled toggle-field-edit>
                                                                                    <option value="-1">--Choose--</option>
                                                                                    <?php
                                                                                    do {
                                                                                        ?>
                                                                                        <option value="<?php echo $row_rssubj['subjid'] ?>"><?php echo $row_rssubj['subjname'] ?></option>
                                                                                        <?php
                                                                                    } while ($row_rssubj = mysql_fetch_assoc($rssubj));

                                                                                    $rows = mysql_num_rows($rssubj);
                                                                                    if ($rows > 0) {
                                                                                        mysql_data_seek($rssubj, 0);
                                                                                        $row_rssubj = mysql_fetch_assoc($rssubj);
                                                                                    }
                                                                                    ?>
                                                                                </select></td>
                                                                            <td>
                                                                                <select name="newgrade[<?php echo $id ?>][grade][<?php echo $i ?>]" id="subj['grade'][]" disabled toggle-field-edit>
                                                                                    <option value="-1">grade</option>
                                                                                    <?php
                                                                                    do {
                                                                                        ?>
                                                                                        <option value="<?php echo $row_rsgrd['grdid'] ?>"><?php echo $row_rsgrd['grdname'] ?></option>
                                                                                        <?php
                                                                                    } while ($row_rsgrd = mysql_fetch_assoc($rsgrd));
                                                                                    $rows = mysql_num_rows($rsgrd);
                                                                                    if ($rows > 0) {
                                                                                        mysql_data_seek($rsgrd, 0);
                                                                                        $row_rsgrd = mysql_fetch_assoc($rsgrd);
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </td>
                                                                        </tr>
<?php } ?>
                                                                    <tr>
                                                                        <td>&nbsp;</td>
                                                                        <td>&nbsp;</td>
                                                                    </tr>
                                                                </table>                                                                
                                                            </td>
                                                            <td>
                                                                <table width="309" border="0" class="table table-hover table-striped table-bordered">
                                                                    <tr>
<?php $id = (isset($row_rssit2['olevelid'])) ? $row_rssit2['olevelid'] : 'second'; ?>
                                                                        <td>Exam No : </td>
                                                                        <td><input name="result[<?php echo $id ?>][examnumber]" type="text" id="examnumber" size="10" value="<?php echo $row_rssit2['examnumber'] ?>" disabled toggle-field-edit/></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td width="155">Exam Type : </td>
                                                                        <td width="133">
                                                                            <select name="result[<?php echo $id ?>][exmtyp]" id="exmtyp2" style="width: 85px" disabled toggle-field-edit>
                                                                                <option value="-1">--Choose--</option>
                                                                                <option value="WASCE(MAY/JUNE)" <?php if ("WASCE(MAY/JUNE)" == $row_rssit2['examtype']) echo 'selected' ?>>WASCE(MAY/JUNE)</option>
                                                                                <option value="WASCE(Private)" <?php if ("WASCE(Private)" == $row_rssit2['examtype']) echo 'selected' ?>>WASCE(Private)</option>
                                                                                <option value="NECO" <?php if ("NECO" == $row_rssit2['examtype']) echo 'selected' ?>>NECO</option>
                                                                                <option value="NECO(Private)" <?php if ("NECO(Private)" == $row_rssit2['examtype']) echo 'selected' ?>>NECO(Private)</option>
                                                                                <option value="NABTEB(MAY/JUNE)" <?php if ("NABTEB(MAY/JUNE)" == $row_rssit2['examtype']) echo 'selected' ?>>NABTEB(MAY/JUNE)</option>
                                                                                <option value="NABTEB(Private)" <?php if ("NABTEB(Private)" == $row_rssit2['examtype']) echo 'selected' ?>>NABTEB(Private)</option>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Exam Year : </td>
                                                                        <td><input name="result[<?php echo $id ?>][exmyr]" type="text" id="exmyr2" size="8" value="<?php echo $row_rssit2['examyear'] ?>" maxlength="4" disabled toggle-field-edit/></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Subject </strong></td>
                                                                        <td><strong>Grade</strong></td>
                                                                    </tr>
                                                                    <?php
                                                                    $totalRows_rssit2 = $row_rssit2['resultid'] == NULL ? 0 : $totalRows_rssit2;
                                                                    for ($i = 0; $i < $totalRows_rssit2; $i++) {
                                                                        ?>
                                                                        <tr>
                                                                            <td>
                                                                                <select name="grade[<?php echo $row_rssit2['resultid'] ?>][subject]" id="subj2['name'][]" disabled toggle-field-edit>
                                                                                    <option value="-1">--Choose-</option>
                                                                                    <?php do { ?>
                                                                                        <option value="<?php echo $row_rssubj['subjid'] ?>" <?php if ($row_rssubj['subjid'] == $row_rssit2['subject']) echo 'selected' ?>><?php echo $row_rssubj['subjname'] ?></option>
                                                                                        <?php
                                                                                    } while ($row_rssubj = mysql_fetch_assoc($rssubj));
                                                                                    $rows = mysql_num_rows($rssubj);
                                                                                    if ($rows > 0) {
                                                                                        mysql_data_seek($rssubj, 0);
                                                                                        $row_rssubj = mysql_fetch_assoc($rssubj);
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </td>

                                                                            <td>
                                                                                <select name="grade[<?php echo $row_rssit2['resultid'] ?>][grade]" id="subj2['grade'][]" disabled toggle-field-edit>
                                                                                    <option value="-1">grade</option>
                                                                                    <?php do { ?>
                                                                                        <option value="<?php echo $row_rsgrd['grdid'] ?>" <?php if ($row_rsgrd['grdid'] == $row_rssit2['grade']) echo 'selected' ?>><?php echo $row_rsgrd['grdname'] ?></option>
                                                                                        <?php
                                                                                    } while ($row_rsgrd = mysql_fetch_assoc($rsgrd));
                                                                                    $rows = mysql_num_rows($rsgrd);
                                                                                    if ($rows > 0) {
                                                                                        mysql_data_seek($rsgrd, 0);
                                                                                        $row_rsgrd = mysql_fetch_assoc($rsgrd);
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </td>
                                                                        </tr>
    <?php $row_rssit2 = mysql_fetch_assoc($rssit2);
} ?>


<?php for ($i = 0; $i < 9 - $totalRows_rssit2; $i++) { ?>
                                                                        <tr>
                                                                            <td>
                                                                                <select name="newgrade[<?php echo $id ?>][subject][<?php echo $i ?>]" id="subj2['name'][]" disabled toggle-field-edit>
                                                                                    <option value="-1">--Choose-</option>
                                                                                    <?php do { ?>
                                                                                        <option value="<?php echo $row_rssubj['subjid'] ?>"><?php echo $row_rssubj['subjname'] ?></option>
                                                                                        <?php
                                                                                    } while ($row_rssubj = mysql_fetch_assoc($rssubj));

                                                                                    $rows = mysql_num_rows($rssubj);
                                                                                    if ($rows > 0) {
                                                                                        mysql_data_seek($rssubj, 0);
                                                                                        $row_rssubj = mysql_fetch_assoc($rssubj);
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <select name="newgrade[<?php echo $id ?>][grade][<?php echo $i ?>]" id="subj2['grade'][]" disabled toggle-field-edit>
                                                                                    <option value="-1">grade</option>
                                                                                    <?php do { ?>
                                                                                        <option value="<?php echo $row_rsgrd['grdid'] ?>"><?php echo $row_rsgrd['grdname'] ?></option>
                                                                                        <?php
                                                                                    } while ($row_rsgrd = mysql_fetch_assoc($rsgrd));

                                                                                    $rows = mysql_num_rows($rsgrd);
                                                                                    if ($rows > 0) {
                                                                                        mysql_data_seek($rsgrd, 0);
                                                                                        $row_rsgrd = mysql_fetch_assoc($rsgrd);
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </td>
                                                                        </tr>
<?php } ?>
                                                                    <tr>
                                                                        <td><a class="btn btn-small btn-blue" 
                                                                   href="#view_result" role="button" 
                                                                   data-toggle="modal" ng-click="fetchResult(pros); setSelected(pros)"><i class="icon icon-eye-open"></i>View fetched O'Level Result</a></td>
                                                                        <td>&nbsp;</td>
                                                                    </tr>
                                                                </table>                                                                
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <p>&nbsp;</p>
                                                </fieldset>           
                                            </td>
                                        </tr>
                                        <?php if (getAccess()==20 || getAccess()==24) { ?>
                                        <tr>
                                            <td>
                                                <fieldset>
                                                    <p>&nbsp;</p>
                                                    <legend>Administrative </legend>
                                                    <table class="table table-bordered table-hover table-striped table-condensed">
                                                        <tr>
                                                            <td>Form Number:</td>
                                                            <td>
                                                                <input type="text" name="profile[formnum]" 
                                                                       value="<?php echo $row_rschk['formnum']; ?>" 
                                                                       disabled toggle-field-edit/>
                                                            </td>
                                                        </tr><tr>
                                                            <td>Form Submit</td>
                                                            <td>
                                                                <select name="profile[formsubmit]" disabled toggle-field-edit>
                                                                    <option  value="Yes" <?= ($row_rschk['formsubmit'] == 'Yes') ? 'selected' : '' ?>>Yes</option>
                                                                    <option  value="No" <?= ($row_rschk['formsubmit'] == 'No') ? 'selected' : '' ?>>No</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Application Fee</td>
                                                            <td>
                                                                <select name="profile[formpayment]" disabled toggle-field-edit>
                                                                    <option  value="Yes" <?= ($row_rschk['formpayment'] == 'Yes') ? 'selected' : '' ?>>Yes</option>
                                                                    <option  value="No" <?= ($row_rschk['formpayment'] == 'No') ? 'selected' : '' ?>>No</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Acceptance Fee</td>
                                                            <td>
                                                                <select name="profile[acceptance]" disabled toggle-field-edit>
                                                                    <option  value="Yes" <?= ($row_rschk['acceptance'] == 'Yes') ? 'selected' : '' ?>>Yes</option>
                                                                    <option  value="No" <?= ($row_rschk['acceptance'] == 'No') ? 'selected' : '' ?>>No</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Post UTME Score </td>
                                                            <td>
                                                                <input type="text" name="profile[score]" id="score" value="<?php echo $row_rschk['score']; ?>" size="7" disabled toggle-field-edit/>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Programme Offered </td>
                                                            <td>
                                                                <select name="profile[progoffered]" id="progoffered" style="width: 400px" disabled toggle-field-edit>
                                                                    <option value="-1">--Choose programme--</option>
                                                                    <?php do { ?>
                                                                    <option value="<?php echo $row_rsprg['progid'] ?>" 
                                                                    <?php if ($row_rsprg['progid'] == $row_rschk['progoffered']) echo 'selected' ?>>
                                                                    <?php echo $row_rsprg['progname'] ?>
                                                                    </option>
                                                                    <?php } while ($row_rsprg = mysql_fetch_assoc($rsprg)); ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Admission Status </td>
                                                            <td>
                                                                <select name="profile[adminstatus]" disabled toggle-field-edit>
                                                                    <option value="-1">Choose</option>
                                                                    <option value="Yes" <?php if ("Yes" == $row_rschk['adminstatus']) echo 'selected' ?>>Admitted</option>
                                                                    <option value="No" <?php if ("No" == $row_rschk['adminstatus']) echo 'selected' ?>>Not Admited</option>
                                                                    <option value="Wtd" <?php if ("Wtd" == $row_rschk['adminstatus']) echo 'selected' ?>>Withdrawn</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Admission Session</td>
                                                            <td>
                                                                <select name="profile[sesid]" disabled toggle-field-edit>
                                                                    <option value="<?php echo $sesid?>" 
                                                                    <?php if ($sesid  == $row_rschk['sesid']) echo 'selected' ?>>
                                                                    <?php echo $sesname?>
                                                                    </option>
                                                                            <?php do { ?>
                                                                        <option value="<?php echo $row_session['sesid'] ?>" 
                                                                        <?php if ($row_session['sesid'] == $row_rschk['sesid']) echo 'selected' ?>>
                                                                        <?php echo $row_session['sesname'] ?>
                                                                        </option>
                                                    <?php } while ($row_session = mysql_fetch_assoc($session)); ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Admission Type </td>
                                                            <td>
                                                                <select name="profile[admid]" disabled toggle-field-edit>
                                                                    <?php for(;$row_admtype = mysql_fetch_assoc($admtype);) :?>
                                                                    <option value="<?php echo $row_admtype['admid']?>" <?php if ($row_admtype['admid'] == $row_rschk['admid']) echo 'selected' ?>>
                                                                        <?php echo $row_admtype['typename']?> (<?php echo $row_admtype['sesname']?>)
                                                                    </option>
                                                                    <?php endfor;?>                                                            
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Registration Type</td>
                                                            <td>
                                                                <select name="profile[regtype]" disabled toggle-field-edit>
                                                                    <option value="-1">Choose</option>
                                                                    <option value="regular" <?php if ("regular" == $row_rschk['regtype']) echo 'selected' ?>>First Choice</option>
                                                                    <option value="coi" <?php if ("coi" == $row_rschk['regtype']) echo 'selected' ?>>Change of Institution</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </fieldset>
                                                <p>&nbsp;</p>  
                                            </td>
                                        </tr> 
                                        <?php } ?>
                                    </table>
                                    <table width="250" border="0" align="center">
                                        <p>&nbsp;</p>
                                        <tr align="center">
                                            <td><input type="submit" name="save" id="save" value="Update Application" class="btn btn-primary"/></td>
                                        </tr>
                                    </table>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
            
            <div id="view_result" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 id="myModalLabel">O&apos;Level Result for ( {{seletedItem.jambregid}} ) {{seletedItem.fname}} {{seletedItem.lname}} {{seletedItem.mname}} - {{seletedItem.progname}} ({{fetched_result.progcount.admitted}} of {{fetched_result.progcount.quota}})</h4>
            </div>
            <form method="post" action="process.php">
                <div class="modal-body">
                    <div class="row-fluid" ng-if="loading">
                        <div>
                            <img src="../../olevel_service/giphy.gif">
                        </div>
                    </div>
                    <div class="row-fluid" ng-if="fetched_result.olevel" >
                        <h4>UTME Subject Combination</h4>
                        <div class="alert alert-info">
                            
                            {{seletedItem.jambsubj1}} = {{seletedItem.jambscore1}},  {{seletedItem.jambsubj2}}  = {{seletedItem.jambscore2}}, 
                            {{seletedItem.jambsubj3}} = {{seletedItem.jambscore3}},  {{seletedItem.jambsubj4}} = {{seletedItem.jambscore4}} 
                            <b>Total = {{seletedItem.jambscore1 + seletedItem.jambscore2 + seletedItem.jambscore3 + seletedItem.jambscore4}}</b>
                        </div>
                        <div class="span6 well well-large" ng-repeat="result in fetched_result.olevel">
                            <span>Exam Year : {{result.exam_year}}</span>
                            <div ng-bind-html="renderHTML(result.result_table)"></div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <input type="hidden" name="stdid" value="{{seletedItem.jambregid}}">
                    <input type="hidden" name="email" value="{{seletedItem.email}}">
                    <input type="hidden" name="phone" value="{{seletedItem.phone}}">
                    <input type="hidden" name="progname" value="{{seletedItem.progname}}">
                    <?php if(1 > 2){?>
                    <button type="submit" name="release" class="btn btn-primary">Release Code</button>
                    <button type="submit" name="refer" class="btn btn-warning">Refer to Admin.</button>
                    <button type="submit" name="terminate" class="btn btn-danger">Deny Admission</button>
                    <?php }?>
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                </div>
            </form>
        </div>
        </div>
        <script type="text/javascript">
        
        
        
        $(function () {
            function toggleLga(action) {
                var lga = $('#lga');

                if (action === 'hide') {
                    lga.children('.og').hide();
                    lga.children('.others').show();
                } else {
                    lga.children('.og').show();
                    lga.children('.others').hide();
                }
            }

            if ($('#stid').val() == 27) {
                toggleLga('show');
            } else {
                toggleLga('hide');
            }


            $('#stid').change(function () {
                var stid = $(this).val();
                if (stid == 27) {
                    toggleLga('show');
                } else {
                    toggleLga('hide');

                }
            });

        });

        var appModule = angular.module('tams', ['ngSanitize']);
        
        appModule.controller('PageController', function($scope, $http, $sce) {
            $scope.pros = <?= json_encode($code)?>;
            
            $scope.seletedItem = '';
                
                $scope.setSelected = function(val){
                   $scope.seletedItem  = val;
                };
            
            
            $scope.loading = false;
                $scope.fetched_result = false;
                $scope.fetchResult = function(obj){
                    $scope.loading = true;
                    $http({
                        method : "POST",
                        url : "../../olevel_service/api/index.php?action=fetch_result",
                        data: {
                            user:    obj.jambregid,
                            progid1: obj.progid1,
                            sesid: obj.sesid
                        }
                    }).then(function mySucces(response) {
                        $scope.fetched_result = response.data;
                        $scope.loading = false;

                    }, function myError(response) {
                        $scope.loading = false;
                        alert('Unable to perform operation'+ response);
                    });    
                };
                
                $scope.renderHTML = function(html_code){
                    var decoded = angular.element('<textarea />').html(html_code).text();
                    return $sce.trustAsHtml(decoded);
                };
        });
        
        appModule.directive('toggleFieldEdit', function ($compile) {

            return {
                restrict: 'A',
                scope :{},
                link: function (scope, elem, attrs) {
                    scope.state = true;
                    
                    var nextEl = "<span ng-click='toggleField()' title='Edit this field' \n\
                                        style='cursor: pointer; position: relative; margin-right:2px; z-index:100'>\n\
                                    <i class='fa fa-edit'></i>\n\
                                </span>";
                    elem.before(angular.element($compile(nextEl)(scope)));
                    
                    scope.toggleField = function() {
                        scope.state = !scope.state;
                        elem.attr('disabled', scope.state);
                    };
                }
            };

        });

        </script> 
    </body>
</html>