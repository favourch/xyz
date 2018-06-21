<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../path.php');

$auth_users = "11";
check_auth($auth_users, $site_root);

define('MAX_FILE_SIZE', 2048 * 1536);
define('UPLOAD_DIR', IMGPATH.'/user/prospective/');

$jambregid = getSessionValue('uid');
if (isset($_GET['stid']) && in_array(getAccess(), [1, 20, 21, 22, 23, 24])) {
    $jambregid = $_GET['stid'];
}


$doc_uploadSQL = sprintf("SELECT * FROM applicant_docs_upload "
                    . "WHERE pstdid = %s ",
                    GetSQLValueString($jambregid, 'text'));
$docRS = mysql_query($doc_uploadSQL, $tams) or die(mysql_error());
$totalRows_doc = mysql_num_rows($docRS);

$doc_upload_array = array();
if ($totalRows_doc > 0) {
    for (;$doc_row = mysql_fetch_assoc($docRS); ) {
        array_push($doc_upload_array, $doc_row);   
    }
}

$formStagrSQL = sprintf("SELECT * "
                        . "FROM applicant_appform_stage "
                        . "WHERE pstdid = %s ", 
                        GetSQLValueString($jambregid, 'text'));
$formStageRS = mysql_query($formStagrSQL, $tams);
$totalRows_fromStage = mysql_num_rows($formStageRS);

$formStage_array = array();
if ($totalRows_fromStage > 0) {
    for (;$formStage_row = mysql_fetch_assoc($formStageRS); ) {
        array_push($formStage_array, $formStage_row['stage']);   
    }
}

$stage = 'file_upload';
if(isset($_GET['stage'])){
    $stage = $_GET['stage'];
}

//Get Prospective infomation
$prospectiveSQL = sprintf("SELECT * FROM prospective p JOIN admissions a ON p.admid = a.admid JOIN admission_type at on at.typeid = a.typeid WHERE jambregid = %s ", 
        GetSQLValueString($jambregid, 'text'));
$prospectiveRS = mysql_query($prospectiveSQL, $tams);
$prospective_row = mysql_fetch_assoc($prospectiveRS);

if($prospective_row['activate'] != 'true'){
    header('location:progress_page.php');
    die();
}

$image_url = get_pics($jambregid, UPLOAD_DIR.explode('/', $_SESSION['admname'])[0]);



// Retreive all programmes
$prog_query = sprintf("SELECT * FROM programme");
$prog = mysql_query($prog_query, $tams);
$totalRows_prog = mysql_num_rows($prog);

$prog_array = array();
if($totalRows_prog > 0){
    for(; $progRS = mysql_fetch_assoc($prog); ){
        array_push($prog_array, $progRS); 
    }
}


// Retreive all subjects
$sub_query = sprintf("SELECT * FROM subject");
$sub = mysql_query($sub_query, $tams);
$totalRows_sub = mysql_num_rows($sub);

$subj_array = array();
if($totalRows_sub > 0){
    for(; $subjRS = mysql_fetch_assoc($sub); ){
        array_push($subj_array, $subjRS); 
    }
}


// Retreive all states
$state_query = sprintf("SELECT * FROM states");
$state = mysql_query($state_query, $tams);
$totalRows_state = mysql_num_rows($state);

$state_array = array();
if($totalRows_state > 0){
    for(; $stateRS = mysql_fetch_assoc($state); ){
        array_push($state_array, $stateRS); 
    }
}


//Rereive all previous school attended 
$prevSchool_query = sprintf("SELECT * FROM applicant_prev_school "
                            . "WHERE pstdid = %s", 
                            GetSQLValueString($jambregid, 'text'));
$prevSchool = mysql_query($prevSchool_query, $tams);
$totalRows_prevSchool = mysql_num_rows($prevSchool);

$prevSchool_array = array();
if ($totalRows_prevSchool > 0) {
    for (; $prevSchoolRS = mysql_fetch_assoc($prevSchool);) {
        array_push($prevSchool_array, $prevSchoolRS);
    }
}

function savePreviouseSchoolRecord($prev_sch){
    global $tams;
   global $jambregid;
    if(!empty($prev_sch)){
        foreach($prev_sch as $prv){
            $sql = sprintf("INSERT INTO applicant_prev_school "
                    . "(pstdid, school_name, "
                    . "school_address, start_date,"
                    . "end_date, cert_obtain ) "
                    . "VALUE (%s, %s, %s, %s, %s, %s)", 
                    GetSQLValueString($jambregid, 'text'),
                    GetSQLValueString($prv['name'], 'text'),
                    GetSQLValueString($prv['addr'], 'text'),
                    GetSQLValueString($prv['from'], 'text'),
                    GetSQLValueString($prv['to'], 'text'),
                    GetSQLValueString($prv['cert'], 'text'));
            mysql_query($sql, $tams) or die(mysql_error());
        }
        
        return TRUE;
    }else{
        return FALSE;
    }
    
}



function updateAppFromStage($stage){
    global $tams;
    global $jambregid;
    $sql1 = sprintf("DELETE FROM "
                . "applicant_appform_stage WHERE pstdid = %s AND stage = %s ", 
                GetSQLValueString($jambregid, 'text'), 
                GetSQLValueString($stage, 'text'));
    mysql_query($sql1, $tams);
        
    $sql2 = sprintf("INSERT INTO "
                . "applicant_appform_stage (pstdid, stage) "
                . "VALUE(%s, %s)", 
                GetSQLValueString($jambregid, 'text'), 
                GetSQLValueString($stage, 'text'));
        mysql_query($sql2, $tams)or die(mysql_error());
}





$query_rspros = sprintf("SELECT p.*, s.sesid, st.stname, formsubmit, a.admid, regtype, pr.progname AS prog1, s.sesname, "
        . "pr2.progname AS prog2, at.typename, s1.subjname as jamb1, s2.subjname as jamb2, s3.subjname as jamb3, s4.subjname as jamb4, lga.lganame "
        . "FROM prospective p "
        . "LEFT JOIN admissions a ON p.admid = a.admid "
        . "LEFT JOIN admission_type at ON a.typeid = at.typeid "
        . "LEFT JOIN session s ON a.sesid = s.sesid "
        . "LEFT JOIN programme pr ON p.progid1 = pr.progid "
        . "LEFT JOIN programme pr2 ON p.progid2 = pr2.progid "
        . "LEFT JOIN subject s1 ON p.jambsubj1 = s1.subjid "
        . "LEFT JOIN subject s2 ON p.jambsubj2 = s2.subjid "
        . "LEFT JOIN subject s3 ON p.jambsubj3 = s3.subjid "
        . "LEFT JOIN subject s4 ON p.jambsubj4 = s4.subjid "
        . "LEFT JOIN states st ON st.stid = p.stid "
        . "LEFT JOIN state_lga lga ON lga.lgaid = p.lga "
        . "WHERE p.jambregid = %s", GetSQLValueString($jambregid, "text"));

$rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
$row_rspros = mysql_fetch_assoc($rspros);
$totalRows_rspros = mysql_num_rows($rspros);

$sesid = $row_rspros['sesid'];



$query_info = sprintf("SELECT * "
                    . "FROM payschedule "
                    . "WHERE level = '0' "
                    . "AND sesid = %s "
                    . "AND admid = %s "
                    . "AND status = %s "
                    . "AND payhead = %s", 
                    GetSQLValueString($sesid, 'int'), 
                    GetSQLValueString($row_rspros['admid'], 'text'), 
                    GetSQLValueString($row_rspros['regtype'], 'text'), 
                    GetSQLValueString('app', 'text'));
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$amt = $row_info['amount'];

$pay_status = checkPaymentPros($sesid, $jambregid, $amt);


if (!$pay_status['status']) {
    header('Location: admission_payment/index.php');
    exit;
}

if ($row_rspros['formsubmit'] == 'Yes' && in_array(getAccess(), [11])) {
    header('Location: view_form.php');
    exit;
}



$jambtotal = ($row_rspros['jambscore1'] + $row_rspros['jambscore2'] + $row_rspros['jambscore3'] + $row_rspros['jambscore4']);


$query_rssit1 = sprintf("SELECT * 
                        FROM olevel o 
                        JOIN olevelresult l ON o.olevelid = l.olevelid 
                        JOIN subject s ON l.subject = s.subjid 
                        JOIN grade g ON l.grade = g.grdid 
                        WHERE o.jambregid = %s
                        AND sitting='first'", 
                        GetSQLValueString($jambregid, "text"));
$rssit1 = mysql_query($query_rssit1, $tams) or die(mysql_error());
$row_rssit1 = mysql_fetch_assoc($rssit1);
$totalRows_rssit1 = mysql_num_rows($rssit1);


$query_rssit2 = sprintf("SELECT * 
                        FROM olevel o 
                        JOIN olevelresult l ON o.olevelid = l.olevelid 
                        JOIN subject s ON l.subject = s.subjid 
                        JOIN grade g ON l.grade = g.grdid 
                        WHERE o.jambregid=%s
                        AND sitting='second'", GetSQLValueString($jambregid, "text"));
$rssit2 = mysql_query($query_rssit2, $tams) or die(mysql_error());
$row_rssit2 = mysql_fetch_assoc($rssit2);
$totalRows_rssit2 = mysql_num_rows($rssit2);


if(isset($_POST['post_prev_school'])){
    
    if(savePreviouseSchoolRecord(json_decode($_POST['post_prev_school'], TRUE))) {
        updateAppFromStage('Educational Background');
        
        header("Location: form.php?stage=educational_background");
        die();
    }
}

if(isset($_POST['doc_upload'])){
    $location = $parts = explode('/', $_SESSION['admname'])[0].'/';
    $ext = strtolower(substr($_FILES['file']['name'], strrpos($_FILES['file']['name'],'.')));
    $file = $jambregid.'_'.$_POST['file_type'].$ext;
    $filename = $location . $file;
    $permited = array('application/pdf');
    if($_FILES['file']){
        if(in_array($_FILES[file]['type'], $permited)){
            $status = move_uploaded_file($_FILES['file']['tmp_name'], $filename);
            if($status){
                $deleteSQL = sprintf("DELETE "
                                    . "FROM applicant_docs_upload "
                                    . "WHERE pstdid = %s "
                                    . "AND doc_name = %s ",
                                    GetSQLValueString($jambregid, 'text'),
                                    GetSQLValueString($filename, 'text'));
                mysql_query($deleteSQL, $tams) or die(mysql_error());
                
                $SQL = sprintf("INSERT INTO "
                        . "applicant_docs_upload (pstdid, doc_name, name ) "
                        . "VALUES(%s, %s, %s )", 
                        GetSQLValueString($jambregid, 'text'),
                        GetSQLValueString($filename, 'text'),
                        GetSQLValueString($_POST['file_type'], 'text'));
                mysql_query($SQL, $tams) or die(mysql_error());   
                
                updateAppFromStage('Document Upload');
                
                header('Location: form.php?stage=file_upload');
                exit;
            }
        }else{
            $notification->set_notification("Incorrect File Type. You are expected to upload only a PDF file", 'error');
        }
        
    }
    
}


if(isset($_POST['image_upload'])){
    $parts = explode('/', $_SESSION['admname']);
    $upload = uploadFile(UPLOAD_DIR.$parts[0].'/', "prospective", MAX_FILE_SIZE);
    if($upload){
        updateAppFromStage('Passport Upload');
    }
    header('Location: form.php?stage=file_upload');
    exit;
}


//Post Biodata info
if(isset($_POST['personal_info'])){
    $bioSQL = sprintf("UPDATE prospective "
                    . "SET fname = %s, lname = %s, "
                    . "mname = %s, DoB = %s, "
                    . "Sex = %s, healthStatus = %s,  "
                    . "phone = %s, email= %s, "
                    . "stid = %s, lga = %s, "
                    . "Religion = %s, address = %s "
                    . "WHERE jambregid = %s ", 
                    GetSQLValueString($_POST['fname'], 'text'),
                    GetSQLValueString($_POST['lname'], 'text'),
                    GetSQLValueString($_POST['mname'], 'text'),
                    GetSQLValueString($_POST['dob'], 'text'),
                    GetSQLValueString($_POST['sex'], 'text'),
                    GetSQLValueString($_POST['healthstatus'], 'text'),
                    GetSQLValueString($_POST['phone'], 'text'),
                    GetSQLValueString($_POST['email'], 'text'),
                    GetSQLValueString($_POST['stid'], 'int'),
                    GetSQLValueString($_POST['lga'], 'int'),
                    GetSQLValueString($_POST['religion'], 'text'),
                    GetSQLValueString($_POST['address'], 'text'),
                    GetSQLValueString($jambregid, 'text'));
    $bio_data = mysql_query($bioSQL, $tams) or die(mysql_error());
    
    updateAppFromStage('Personal Information');
    header('Location: form.php?stage=personal_information');
    exit;
}


if(isset($_POST['psns_and_nxkn'])){
    $spns_nxkSQL = sprintf("UPDATE prospective "
                        . "SET sponsorname = %s, sponsorphn = %s, "
                        . "sponsoremail = %s,sponsoradrs = %s, sponsorrelation = %s, "
                        . "nxt_kin_fullname = %s, nxt_kin_phone = %s, "
                        . "nxt_kin_email = %s, nxt_kin_address = %s,"
                        . "nxt_kin_relation = %s "
                        . "WHERE jambregid = %s ", 
                        GetSQLValueString($_POST['spn_full_name'], 'text'),
                        GetSQLValueString($_POST['spn_phone'], 'text'),
                        GetSQLValueString($_POST['spn_email'], 'text'),
                        GetSQLValueString($_POST['spn_address'], 'text'),
                        GetSQLValueString($_POST['spn_relationship'], 'text'),
                        GetSQLValueString($_POST['nxk_full_name'], 'text'),
                        GetSQLValueString($_POST['nxk_phone'], 'text'),
                        GetSQLValueString($_POST['nxk_email'], 'text'),
                        GetSQLValueString($_POST['nxk_address'], 'text'),
                        GetSQLValueString($_POST['nxk_relationship'], 'text'), 
                        GetSQLValueString($jambregid, 'text') );
    $spn_nxkRS = mysql_query($spns_nxkSQL, $tams) or die(mysql_error());

    updateAppFromStage('Sponsor and Next of kin information');
    header('Location: form.php?stage=sponsor_and_next_of_kin');
    exit;
}




if(isset($_POST['jamb_result'])){
   $utmeSQL = sprintf("UPDATE prospective "
                    . "SET jambyear = %s, jambsubj1 = %s, "
                    . "jambsubj2 = %s, jambsubj3 = %s, "
                    . "jambsubj4 = %s, jambscore1 = %s, "
                    . "jambscore2 = %s, jambscore3 = %s, "
                    . "jambscore4 = %s WHERE jambregid = %s", 
            GetSQLValueString($_POST['jambyear'], 'text'),
            GetSQLValueString($_POST['jambsubj1'], 'int'),
            GetSQLValueString($_POST['jambsubj2'], 'int'),
            GetSQLValueString($_POST['jambsubj3'], 'int'),
            GetSQLValueString($_POST['jambsubj4'], 'int'),
            GetSQLValueString($_POST['jambscore1'], 'int'),
            GetSQLValueString($_POST['jambscore2'], 'int'),
            GetSQLValueString($_POST['jambscore3'], 'int'),
            GetSQLValueString($_POST['jambscore4'], 'int'),
            GetSQLValueString($jambregid, 'text'));
    $utmeRS = mysql_query($utmeSQL, $tams) or die(mysql_error());
    updateAppFromStage('UTME Result');
    header('Location: form.php?stage=utme_result');
    exit;
}


if(isset($_POST['prog_choice'])){
    $SQL = sprintf("UPDATE prospective "
                . "SET progid1 = %s, progid2 = %s "
                . "WHERE jambregid = %s", 
                GetSQLValueString($_POST['progid1'], 'int'),
                GetSQLValueString($_POST['progid2'], 'int'),
                GetSQLValueString($jambregid, 'text'));
    $progRS = mysql_query($SQL, $tams);
    updateAppFromStage('Programme Choice');
    header('Location: form.php?stage=programme_choice');
    exit;
}


if(isset($_POST['o_level_result'])){
    
    if(!empty($_POST['olevel'])){
        $selectSQL = sprintf("SELECT olevelid "
                            . "FROM olevel "
                            . "WHERE jambregid = %s", 
                            GetSQLValueString($jambregid, "text"));
        $rsid = mysql_query($selectSQL, $tams) or die(mysql_error());
        $row_rsid = mysql_fetch_assoc($rsid);
        $totalRows_rsid = mysql_num_rows($rsid);

        if ($totalRows_rsid > 0) {
            for ($i = 0; $i < $totalRows_rsid; $i++) {
                $olevelid = $row_rsid['olevelid'];
                mysql_query("DELETE FROM olevelresult WHERE olevelid = {$olevelid}", $tams);
                mysql_query("DELETE FROM olevel WHERE olevelid = {$olevelid}", $tams);
                $row_rsid = mysql_fetch_assoc($rsid);
            }
        }
        
        foreach ($_POST['olevel'] as $olevel_sitting){
            if(!empty($olevel_sitting)  && !empty($olevel_sitting['subject'])){
                mysql_query("BEGIN", $tams);
                $SQL1 = sprintf("INSERT INTO olevel "
                        . "(jambregid, examtype, examyear, examnumber, sitting ) "
                        . "VALUES (%s, %s, %s, %s, %s)", 
                        GetSQLValueString($jambregid, 'text'),
                        GetSQLValueString($olevel_sitting['exam_type'], 'text'),
                        GetSQLValueString($olevel_sitting['exam_year'], 'text'),
                        GetSQLValueString($olevel_sitting['exam_num'], 'text'),
                        GetSQLValueString($olevel_sitting['sitting'], 'text'));
                $olevel = mysql_query($SQL1, $tams) or die(mysql_error());
                $olevel_id = mysql_insert_id();
                
                foreach($olevel_sitting['subject'] as $key => $value){
                    if($value == -1 || $olevel_sitting['grade'][$key] == -1){
                        continue;
                    }
                    $insertSQL = sprintf("INSERT INTO olevelresult (olevelid, subject, grade) VALUES (%s, %s, %s)",
                        GetSQLValueString($olevel_id, "int"),
                        GetSQLValueString($olevel_sitting['subject'][$key], "int"),
                        GetSQLValueString($olevel_sitting['grade'][$key], "text"));
                    $Result2 = mysql_query($insertSQL, $tams) or die(mysql_error());
                }
            }
        }
        mysql_query("COMMIT", $tams);
        updateAppFromStage('OLevel Result');
        header('Location: form.php?stage=olevel_result');
        exit;
    }
}



//var_dump($prospective_row['typeid']);
//die();



        
$yet_to_fill = array();
array_push($yet_to_fill, 'Passport Upload');
array_push($yet_to_fill, 'Educational Background');
array_push($yet_to_fill, 'Personal Information');
array_push($yet_to_fill, 'Sponsor and Next of kin information');
array_push($yet_to_fill, 'UTME Result');
array_push($yet_to_fill, 'Programme Choice');
array_push($yet_to_fill, 'OLevel Result');

$miss = array_diff($yet_to_fill, $formStage_array);


if (isset($_POST['frmsubmit'])) {
   
    if(empty($miss)){
        $admission_type = $prospective_row['admtype'];
        $parts = explode('/', $_SESSION['admname']);
        $part2 = str_pad($row_rspros['pstdid'], 4, '0', STR_PAD_LEFT);
        $formnum = substr($parts[0], 2) . $admission_type . $part2;
        
        $query_update = sprintf("UPDATE prospective "
                . "SET formsubmit = %s, formnum = %s "
                . "WHERE jambregid=%s", GetSQLValueString("Yes", "text"), GetSQLValueString($formnum, "text"), GetSQLValueString($jambregid, "text"));
        $update = mysql_query($query_update, $tams) or die(mysql_error());


        $updateGoTo = "view_form.php";
        if (isset($_SERVER['QUERY_STRING'])) {
            $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
            $updateGoTo .= $_SERVER['QUERY_STRING'];
        }


        header(sprintf("Location: %s", $updateGoTo));
        exit;
    }else{
        $msg = 'Incomplete Application! Check and Fill the following stage(s) ';
        $msg .= "<ul>";
                
        foreach ($miss AS $m){
            $msg .= "<li>{$m}</li>";
        }
         $msg .= "</ul>"; 
         $msg .="<br/>Ensure that you complete the Application Form before the final Submission";
        $notification->set_notification($msg, 'error');
    }
    
    
}







?>
<!doctype html>
<html ng-app="tams_admission_appform">
<?php include INCPATH . "/header.php" ?>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="MainCtrl">
<?php include INCPATH . "/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
<?php include INCPATH . "/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
        <?php include INCPATH . "/page_header.php" ?>                                                                                                               
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-list"></i> <?= $_SESSION['admname']?> <?= $prospective_row['typename']?> Application Form </h3>
                                </div>                                
                                <div class="box-content nopadding">
                                    <div class="row-fluid">
                                        <?php if($prospective_row['activate'] == 'true') {?>
                                        <div class="span12">
                                            <div class="tabs-container">
                                                <ul class="tabs tabs-inline tabs-left">
                                                    <li ng-class="{'active': stage == 'file_upload'}">
                                                        <a href="#t1" data-toggle="tab"><i class="icon-upload"></i> File Upload </a>
                                                    </li>
                                                    <li ng-class="{'active': stage == 'personal_information'}">
                                                        <a href="#t2" data-toggle="tab"><i class="icon-user"></i> Personal Information</a>
                                                    </li>
                                                    <li ng-class="{'active': stage == 'educational_background'}">
                                                        <a href="#t8" data-toggle="tab"><i class="icon-list"></i> Educational Background </a>
                                                    </li>
                                                    <li ng-class="{'active': stage == 'olevel_result'}">
                                                        <a href="#t6" data-toggle="tab"><i class="icon-list"></i> O&apos;Level Result</a>
                                                    </li>
                                                    <li ng-class="{'active': stage == 'utme_result'}">
                                                        <a href="#t5" data-toggle="tab"><i class="icon-list"></i>U.T.M.E Result</a>
                                                    </li>
                                                    <li ng-class="{'active': stage == 'programme_choice'}">
                                                        <a href="#t7" data-toggle="tab"><i class="icon-list-alt"></i> Course of Study </a>
                                                    </li>
                                                    <li ng-class="{'active': stage == 'sponsor_and_next_of_kin'}">
                                                        <a href="#t4" data-toggle="tab"><i class="glyphicon-old_man"></i> Sponsorship</a>
                                                    </li>
                                                    <li >
                                                        <a href="#t9" data-toggle="tab"><i class="icon-eye-open"></i> Submit Form</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-content padding tab-content-inline">
                                                <div class="tab-pane" id="t1" ng-class="{'active': stage == 'file_upload'}">
                                                    <h4>Passport / File Upload</h4> 
                                                    <div class="row-fluid">
                                                        <div class="span6">
                                                            <div class="box box-bordered">
                                                                <div class="box-title">
                                                                    <h3>
                                                                        <i class="icon-camera"></i>
                                                                        Profile Picture
                                                                    </h3>
                                                                    <ul class="tabs">
                                                                        <li class="active"><a href="#passport_upload" role="button" class="btn" data-toggle="modal"> <i class="icon-camera"></i> Upload Passport</a></li>
                                                                    </ul>
                                                                </div>
                                                                <div class="box-content" style="align-items: center">
                                                                    <div class="span6">
                                                                        <div class="fileupload-new thumbnail" style="width: 200px; height: 150px;">
                                                                            <img style="width: 200px; height: 150px;" src="<?= $image_url ?>">
                                                                        </div>
                                                                    </div>
                                                                    <div class="span6">
                                                                        <div class="" style="font-size:10px">
                                                                            <br/>
                                                                           Upload a RECENT scanned passport photograph with your FACE centred.<br/><br/>
                                                                           The Uploaded Passport will be used for ID Card and other purposes, if admitted.
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="span6">
                                                            <div class="box box-bordered">
                                                                <div class="box-title">
                                                                    <h3>
                                                                        <i class="icon-reorder"></i>
                                                                        Document Upload
                                                                    </h3>
                                                                    <ul class="tabs">
                                                                        <li class="active"><a href="#doc_upload" role="button" class="btn" data-toggle="modal"> <i class="icon-file"></i> Upload Document</a></li>
                                                                    </ul>
                                                                </div>
                                                                <div class="box-content">
                                                                    <div class="row-fluid">
                                                                        <div class="span12" >
                                                                            <table class="table table-striped table-condensed">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th>#</th>
                                                                                        <th>Document Name</th>
                                                                                        <th>Action</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody ng-cloak="" >
                                                                                    <tr ng-if="uploaded_doc.length > 0" ng-repeat="doc in uploaded_doc" >
                                                                                        <td>{{$index + 1}}</td></td>
                                                                                        <td>{{doc.name}}</td>
                                                                                        <td>
                                                                                            <a href="{{doc.doc_name}}" target="tab" ><i class="icon icon-eye-open"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                                                            <a href="delete_file.php?fid={{doc.docid}}"><i class="icon icon-trash"></i></a>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr ng-if="uploaded_doc.length < 1">
                                                                                        <td colspan="3">
                                                                                            <div style="font-size:10px">
                                                                                                <div class="alert alert-warning" >No Uploaded Document available.</div>
                                                                                                <p>You are required to upload a scanned copy of your relevant documents in support of your Application for <?= $row_rspros['sesname']?> <?= $row_rspros['typename']?> Admission.</p>
                                                                                            </div>
                                                                                        </td>
                                                                                    </tr>
                                                                                </tbody>
                                                                            </table> 
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>     
                                                </div>
                                                <div class="tab-pane" id="t2" ng-class="{'active': stage == 'personal_information'}">
                                                    <div class="row-fluid">
                                                        <div class="span12">
                                                            <div class="box box-bordered">
                                                                <div class="box-title">
                                                                    <h3>
                                                                        <i class="icon-user"></i>
                                                                        Personal Information 
                                                                    </h3>
                                                                    <ul class="tabs">
                                                                       <li class="active"> <a href="#personal_info" role="button" class="btn" data-toggle="modal"> <i class="icon-user"></i> Add / Edit</a></li>
                                                                    </ul>
                                                                </div>
                                                                <div class="box-content">
                                                                    <div class="alert alert-info" style="font-size: 10px">
                                                                         Complete or Edit your Personal Information using the Add/Edit 
                                                                    </div>
                                                                    <div class="well">
                                                                        <table class="table table-striped table-bordered table-hover">                                          
                                                                            <tbody>
                                                                                <tr>
                                                                                    <th width="15%">Surname :</th>
                                                                                    <td width="25%"><?php echo $row_rspros['lname'] ?></td>
                                                                                    <th width="15%">First name :</th>
                                                                                    <td width="25%"><?php echo $row_rspros['fname'] ?></td>
                                                                                    <td width="20%" rowspan="4">
                                                                                        <img width="160" height="160" align="top" name="placeholder" id="placeholder" alt="Image" src="<?php echo $image_url; ?>" style="alignment-adjust: central">
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Middle Name :</th>
                                                                                    <td><?php echo $row_rspros['mname'] ?></td>
                                                                                    <th>E-Mail :</th>
                                                                                    <td><?php echo $row_rspros['email'] ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Phone :</th>
                                                                                    <td><?php echo $row_rspros['phone'] ?></td>
                                                                                    <th>State of Origin :</th>
                                                                                    <td><?php echo $row_rspros['stname'] ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Address :</th>
                                                                                    <td><?php echo $row_rspros['address'] ?></td>
                                                                                    <th>Local Govt :</th>
                                                                                    <td><?php echo $row_rspros['lganame'] ?></td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="tab-pane" id="t4" ng-class="{'active': stage == 'sponsor_and_next_of_kin'}">
                                                    <h4>Sponsor's / Next of Kin Information.</h4>
                                                    <div class="row-fluid">
                                                        <div class="span12">
                                                            <div class="box box-bordered">
                                                                <div class="box-title">
                                                                    <h3>
                                                                        <i class="icon-user"></i>
                                                                       Sponsor / Next of Kin Information
                                                                    </h3>
                                                                    <ul class="tabs">
                                                                       <li class="active"> <a href="#spn_nxk_info" role="button" class="btn" data-toggle="modal"> <i class="icon-user"></i> Add / Edit</a></li>
                                                                    </ul>
                                                                </div>
                                                                <div class="box-content">
                                                                    <div class="alert alert-info" style="font-size: 10px">
                                                                         Complete or Edit your Sponsor/Next of Kin Information using the Add/Edit 
                                                                    </div>
                                                                    <div class="well">
                                                                        <table class="table table-striped table-bordered table-hover">                                          
                                                                            <thead>
                                                                                <tr>
                                                                                    <th width="50%" colspan="2">Sponsor Information.</th>
                                                                                    <th width="50%" colspan="2">Next of Kin information</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <th width="20%">Sponsor's Name.</th>
                                                                                    <td width="30%"><?= $prospective_row['sponsorname']?></td>
                                                                                    <th width="20%">Next OF Kin  Name.</th>
                                                                                    <td width="30%"><?= $prospective_row['nxt_kin_fullname']?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th width="20%">Sponsor's Phone.</th>
                                                                                    <td width="30%"><?= $prospective_row['sponsorphn']?></td>
                                                                                    <th width="20%">Next OF Kin Phone.</th>
                                                                                    <td width="30%"><?= $prospective_row['nxt_kin_phone']?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th width="20%">Sponsor's email.</th>
                                                                                    <td width="30%"><?= $prospective_row['sponsoremail']?></td>
                                                                                    <th width="20%">Next OF Kin Email.</th>
                                                                                    <td width="30%"><?= $prospective_row['nxt_kin_email']?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th width="20%">Sponsor's Address.</th>
                                                                                    <td width="30%"><?= $prospective_row['sponsoradrs']?></td>
                                                                                    <th width="20%">Next OF Kin Address.</th>
                                                                                    <td width="30%"><?= $prospective_row['nxt_kin_address']?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th width="20%">Sponsor's Relationship.</th>
                                                                                    <td width="30%"><?= $prospective_row['sponsorrelation']?></td>
                                                                                    <th width="20%">Next OF Kin Relationship.</th>
                                                                                    <td width="30%"><?= $prospective_row['nxt_kin_relation']?></td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="t5" ng-class="{'active': stage == 'utme_result'}">
                                                    <h4>U.T.M.E Result</h4>
                                                    <div class="row-fluid">
                                                        <div class="span12">
                                                            <div class="box box-bordered">
                                                                <div class="box-title">
                                                                    <h3>
                                                                        <i class="icon-user"></i>
                                                                        U.T.M.E Result
                                                                    </h3>
                                                                    <ul class="tabs">
                                                                       <li class="active"> <a href="#utme_result" role="button" class="btn" data-toggle="modal"> <i class="icon-user"></i> Add / Edit</a></li>
                                                                    </ul>
                                                                </div>
                                                                <div class="box-content">
                                                                    <div class="alert alert-info" style="font-size: 10px">
                                                                         Complete or Edit your U.T.M.E Result using the Add/Edit 
                                                                    </div>
                                                                    <div class="well">
                                                                        <table class="table table-hover table-striped table-bordered">
                                                                            <tbody>                                            
                                                                                <tr>
                                                                                    <td>UTME Reg No. :</td>
                                                                                    <td><?php echo $row_rspros['jambregid'] ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>UTME Year. : </td>
                                                                                    <td><?php echo $row_rspros['jambyear'] ?></td>
                                                                                </tr>
                                                                                <?php if(!in_array($prospective_row['typeid'], [1])){?>
                                                                                <tr>
                                                                                    <th align="center" colspan="2">Subjects / Scores </th>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><?php echo $row_rspros['jamb1'] ?></td>
                                                                                    <td><?php echo $row_rspros['jambscore1'] ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><?php echo $row_rspros['jamb2'] ?></td>
                                                                                    <td><?php echo $row_rspros['jambscore2'] ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><?php echo $row_rspros['jamb3'] ?></td>
                                                                                    <td><?php echo $row_rspros['jambscore3'] ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><?php echo $row_rspros['jamb4'] ?></td>
                                                                                    <td><?php echo $row_rspros['jambscore4'] ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Total </th>
                                                                                    <td style="color:green; font-weight: bold"><?php echo $jambtotal ?></td>
                                                                                </tr>
                                                                                <?php } ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="t6" ng-class="{'active': stage == 'olevel_result'}">
                                                    <h4>O'Level Result </h4>
                                                    <div class="row-fluid">
                                                        <div class="span12">
                                                            <div class="box box-bordered">
                                                                <div class="box-title">
                                                                    <h3>
                                                                        <i class="icon-list"></i>
                                                                        O&apos;Level Result
                                                                    </h3>
                                                                    <ul class="tabs">
                                                                        <li class="active"><a href="#oleve_result" role="button" class="btn" data-toggle="modal"> <i class="icon-user"></i> Add / Edit</a></li>
                                                                    </ul>
                                                                </div>
                                                                <div class="box-content">
                                                                    <div class="alert alert-info" style="font-size: 10px">
                                                                         Complete or Edit your O&apos;Level Result using the Add/Edit <br/>
                                                                         It is COMPULSORY to submit at least one (1) O&apos;Level Result.
                                                                    </div>
                                                                    <div class="row-fluid">
                                                                        <div class="span6">
                                                                            <h4>First Sitting</h4>
                                                                            <div class="well">
                                                                                <table width="320" class="table table-hover table-striped table-bordered">
                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <th>Exam number</th>
                                                                                            <td><?= $row_rssit1['examnumber'] ?></td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th>Exam Type</th>
                                                                                            <td><?= $row_rssit1['examtype'] ?></td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th>Exam Year</th>
                                                                                            <td><?= $row_rssit1['examyear'] ?></td>
                                                                                        </tr>
                                                                                    
                                                                                        <tr>
                                                                                            <th colspan="2" style="text-align: center">Subject / Grade</th>
                                                                                        </tr>
                                                                                    
                                                                                    <?php if ($totalRows_rssit1 > 0) { ?>
                                                                                    
                                                                                        <?php for ($i = 0; $i < $totalRows_rssit1; $i++) { ?>
                                                                                            <tr>
                                                                                                <td><?php echo $row_rssit1['subjname'] ?></td>
                                                                                                <td><?php echo $row_rssit1['grdname'] ?></td>
                                                                                            </tr>
                                                                                        <?php $row_rssit1 = mysql_fetch_assoc($rssit1); } ?>
                                                                                         
                                                                                    <?php  } else { ?>
                                                                                        <tr>
                                                                                            <td colspan='2' style="color: red">No result</td>
                                                                                        </tr>
                                                                                    <?php } ?>

                                                                                    </tbody>
                                                                                </table> 
                                                                            </div>
                                                                        </div>
                                                                        <div class="span6">
                                                                            <h4>Second Sitting</h4>
                                                                            <div class="well">
                                                                                <table width="320" class="table table-hover table-striped table-bordered">
                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <th>Exam number</th>
                                                                                            <td><?= $row_rssit2['examnumber'] ?></td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th>Exam Type</th>
                                                                                            <td><?= $row_rssit2['examtype'] ?></td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th>Exam Year</th>
                                                                                            <td><?= $row_rssit2['examyear'] ?></td>
                                                                                        </tr>
                                                                                    
                                                                                        <tr>
                                                                                            <th colspan="2" style="text-align: center">Subject / Grade</th>
                                                                                        </tr>

                                                                                    <?php if ($totalRows_rssit2 > 0) { ?>  
                                                                                       <?php for ($i = 0; $i < $totalRows_rssit2; $i++) { ?>
                                                                                            <tr>
                                                                                                <td><?php echo $row_rssit2['subjname'] ?></td>
                                                                                                <td><?php echo $row_rssit2['grdname'] ?></td>
                                                                                            </tr>
                                                                                            <?php $row_rssit2 = mysql_fetch_assoc($rssit2); }?>
                                                                                        
                                                                                        <?php } else { ?>
                                                                                        <tr>
                                                                                            <td colspan='2' style="color: red">No result</td>
                                                                                        </tr>
                                                                                        <?php } ?>
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                                <div class="tab-pane" id="t7" ng-class="{'active': stage == 'programme_choice'}">
                                                    <div class="row-fluid">
                                                        <div class="span12">
                                                            <div class="box box-bordered">
                                                                <div class="box-title">
                                                                    <h3>
                                                                        <i class="icon-user"></i>
                                                                       Course of Study
                                                                    </h3>
                                                                    <ul class="tabs">
                                                                        <li class="active"><a href="#prog_choice" role="button" class="btn" data-toggle="modal"> <i class="icon-user"></i> Add / Edit </a></li>
                                                                    </ul>
                                                                </div>
                                                                <div class="box-content">
                                                                    <div class="alert alert-info" style="font-size: 10px">
                                                                         Select or Edit any two (2) choice of programmes to study at the <?= $institution?> using the Add/Edit <br/>
                                                                         Be guided by the requirements for each programme as stated in the <a href="../docs/2017-2018-BROCHURE.pdf" target="tabs">Admission Brochure</a> 
                                                                    </div>
                                                                    <table class="table table-bordered">
                                                                        <tbody>
                                                                            <tr>
                                                                                <th width='30%'>
                                                                                    First Choice 
                                                                                </th>
                                                                                <td><?= $row_rspros['prog1'] ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>
                                                                                    Second Choice 
                                                                                </th>
                                                                                <td><?= $row_rspros['prog2'] ?></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="t8" ng-class="{'active': stage == 'educational_background'}">
                                                    <h4>Educational Background</h4>
                                                    <div class="row-fluid">
                                                        
                                                        <div class="span12">
                                                            <div class="box box-bordered">
                                                                <div class="box-title">
                                                                    <h3>
                                                                        <i class="icon-user"></i>
                                                                        Educational Background 
                                                                    </h3>
                                                                    <ul class="tabs">
                                                                        <li class="active"><a href="#prev_school" role="button" class="btn" data-toggle="modal"> <i class="icon-user"></i> Add / Edit  </a></li>
                                                                    </ul>
                                                                </div>
                                                                <div class="box-content">
                                                                    <div class="alert alert-info" style="font-size: 10px">
                                                                         Complete or Edit your Educational Background using the Add/Edit
                                                                    </div>
                                                                    <table class="table table-condensed table-striped">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>#</th>
                                                                                <th>School Name</th>
                                                                                <th>School Address</th>
                                                                                <th>From</th>
                                                                                <th>To</th>
                                                                                <th>Certificate Obtains</th>
                                                                                <th>Action</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr ng-repeat="prv_sch in prev_school_submitted track by $index">
                                                                                <td>{{$index +1}}</td>
                                                                                <td>{{prv_sch.school_name}}</td>
                                                                                <td>{{prv_sch.school_address}}</td>
                                                                                <td>{{prv_sch.start_date}}</td>
                                                                                <td>{{prv_sch.end_date}}</td>
                                                                                <td>{{prv_sch.cert_obtain}}</td>
                                                                                <td><button class="btn btn-small btn-red">Remove</button></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="t9">
                                                    <div class="box-content">
                                                        <strong>Bio Data</strong>
                                                        <div class="well">
                                                            <table class="table table-striped table-bordered table-hover">                                          
                                                                <tbody>
                                                                    <tr>
                                                                        <th width="15%">Surname :</th>
                                                                        <td width="25%"><?php echo $row_rspros['lname'] ?></td>
                                                                        <th width="15%">First Name :</th>
                                                                        <td width="25%"><?php echo $row_rspros['fname'] ?></td>
                                                                        <td width="20%" rowspan="4">
                                                                            <img width="160" height="160" align="top" name="placeholder" id="placeholder" alt="Image" src="<?php echo $image_url; ?>" style="alignment-adjust: central">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Middle Name :</th>
                                                                        <td><?php echo $row_rspros['mname'] ?></td>
                                                                        <th>E-Mail :</th>
                                                                        <td><?php echo $row_rspros['email'] ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Phone :</th>
                                                                        <td><?php echo $row_rspros['phone'] ?></td>
                                                                        <th>State of Origin :</th>
                                                                        <td><?php echo $row_rspros['stname'] ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Address :</th>
                                                                        <td><?php echo $row_rspros['address'] ?></td>
                                                                        <th>Local Govt :</th>
                                                                        <td><?php echo $row_rspros['lganame'] ?></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <br/>
                                                        <?php if ($row_rspros['admid'] == 2) : ?>
                                                            <strong>UTME RESULT</strong>
                                                            <div class="well">
                                                                <table class="table table-hover table-striped table-bordered">

                                                                    <tbody>                                            
                                                                        <tr>
                                                                            <td>UTME Reg No. :</td>
                                                                            <td><?php echo $row_rspros['jambregid'] ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>UTME Year. : </td>
                                                                            <td><?php echo $row_rspros['jambyear'] ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th align="center" colspan="2">Subjects / Scores </th>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><?php echo $row_rspros['jamb1'] ?></td>
                                                                            <td><?php echo $row_rspros['jambscore1'] ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><?php echo $row_rspros['jamb2'] ?></td>
                                                                            <td><?php echo $row_rspros['jambscore2'] ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><?php echo $row_rspros['jamb3'] ?></td>
                                                                            <td><?php echo $row_rspros['jambscore3'] ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><?php echo $row_rspros['jamb4'] ?></td>
                                                                            <td><?php echo $row_rspros['jambscore4'] ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Total </th>
                                                                            <td style="color:green; font-weight: bold"><?php echo $jambtotal ?></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <br>
                                                        <?php elseif ($row_rspros['admid'] == 1) : ?>
                                                            <strong>DIRENT ENTRY</strong>
                                                            <div class="well">
                                                                <table width="320" class="table table-hover table-striped table-bordered">
                                                                    <tr>
                                                                        <th colspan="2"> DIRECT ENTRY </th>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>UTME Reg No.</td>
                                                                        <td align="left"><?php echo $row_rspros['jambregid'] ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>UTME Year.</td>
                                                                        <td align="left"><?php echo $row_rspros['jambyear'] ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"style="font-weight: bold" align="center"> Previous Qualification </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>School Name :</td>
                                                                        <td align="left"><?php echo $row_rspros['deschname'] ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Graduation year :</td>
                                                                        <td align="left"><?php echo $row_rspros['degradyear'] ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Grade : </td>
                                                                        <td align="left">
                                                                            <?php echo getDeGrade($row_rspros['degrade']); ?>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div><br/>
                                                        <?php endif; ?>
                                                        <strong>Programme Choices</strong>
                                                        <div class='well'>
                                                            <table class='table table-hover table-striped table-bordered'>                                            
                                                                <tbody>
                                                                    <tr>
                                                                        <th>
                                                                            1st Choice of Programme
                                                                        </th> 
                                                                        <td>
                                                                            <?php echo $row_rspros['prog1'] ?>
                                                                        </td>                                                                                                                                       
                                                                    </tr>
                                                                    <tr>
                                                                        <th>
                                                                            2nd Choice of Programme
                                                                        </th> 
                                                                        <td>
                                                                            <?php echo $row_rspros['prog2'] ?>
                                                                        </td>                                                                                                                                       
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div> 
                                                        <br>
                                                        <strong>O'LEVEL</strong>
                                                        <?php 
                                                        mysql_data_seek($rssit1, 0);
                                                        $row_rssit1 = mysql_fetch_assoc($rssit1);
                                                        
                                                        mysql_data_seek($rssit2, 0);
                                                        $row_rssit2 = mysql_fetch_assoc($rssit2);
                                                        ?>
                                                        <div class="well">
                                                            <table class="table table-hover table-striped table-bordered">
                                                                <tbody>
                                                                    <tr>
                                                                        <td width="50%">
                                                                            <table width="320" class="table table-hover table-striped table-bordered">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th colspan="2">First Sitting</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <tr>
                                                                                        <th>Exam number</th>
                                                                                        <td><?= $row_rssit1['examnumber'] ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Exam Type</th>
                                                                                        <td><?= $row_rssit1['examtype'] ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Exam Year</th>
                                                                                        <td><?= $row_rssit1['examyear'] ?></td>
                                                                                    </tr>
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th colspan="2">Subject / Grade</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <?php
                                                                                if ($totalRows_rssit1 > 0) {
                                                                                    
                                                                                    for ($i = 0; $i < $totalRows_rssit1; $i++) {
                                                                                        ?>
                                                                                        <tr>
                                                                                            <td><?php echo $row_rssit1['subjname'] ?></td>
                                                                                            <td><?php echo $row_rssit1['grdname'] ?></td>
                                                                                        </tr>

                                                                                        <?php
                                                                                        $row_rssit1 = mysql_fetch_assoc($rssit1);
                                                                                    }
                                                                                } else {
                                                                                    ?>

                                                                                    <tr>
                                                                                        <td colspan='2'>No result</td>
                                                                                    </tr>
                                                                                <?php } ?>

                                                                </tbody>
                                                            </table>                    
                                                            </td>
                                                            <td width="50%">
                                                                <table width="320" class="table table-hover table-striped table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th colspan="2">Second Sitting</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <th>Exam number</th>
                                                                            <td><?= $row_rssit2['examnumber'] ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Exam Type</th>
                                                                            <td><?= $row_rssit2['examtype'] ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Exam Year</th>
                                                                            <td><?= $row_rssit2['examyear'] ?></td>
                                                                        </tr>
                                                                    <thead>
                                                                        <tr>
                                                                            <th colspan="2">Subject / Grade</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <?php
                                                                    if ($totalRows_rssit2 > 0) {
                                                                        for ($i = 0; $i < $totalRows_rssit2; $i++) { ?>
                                                                            <tr>
                                                                                <td><?php echo $row_rssit2['subjname'] ?></td>
                                                                                <td><?php echo $row_rssit2['grdname'] ?></td>
                                                                            </tr>
                                                                            <?php
                                                                            $row_rssit2 = mysql_fetch_assoc($rssit2);
                                                                        }
                                                                    } else {
                                                                        ?>
                                                                        <tr><td colspan='2'>No result</td></tr>
                                                                    <?php } ?>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                            </tr>
                                                            </tbody>
                                                            </table>
                                                        </div>
                                                        <br>
                                                        <strong>Previouse School / Qualification </strong>
                                                        <div class="well">
                                                            <div class="row-fluid">
                                                                <div class="span12">
                                                                    <table class="table table-condensed table-striped">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>#</th>
                                                                                <th>School Name</th>
                                                                                <th>School Address</th>
                                                                                <th>From</th>
                                                                                <th>To</th>
                                                                                <th>Certificate Obtains</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr ng-repeat="prv_sch in prev_school_submitted track by $index">
                                                                                <td>{{$index +1}}</td>
                                                                                <td>{{prv_sch.school_name}}</td>
                                                                                <td>{{prv_sch.school_address}}</td>
                                                                                <td>{{prv_sch.start_date}}</td>
                                                                                <td>{{prv_sch.end_date}}</td>
                                                                                <td>{{prv_sch.cert_obtain}}</td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <strong>Sponsor's / Next Of Kin Info. </strong>
                                                        <div class="well">
                                                            <table class="table table-striped table-bordered table-hover">                                          
                                                                <thead>
                                                                    <tr>
                                                                        <th width="50%" colspan="2">Sponsor Infomation.</th>
                                                                        <th width="50%" colspan="2">Next of Kin</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <th width="20%">Sponsor's Name.</th>
                                                                        <td width="30%"><?= $prospective_row['sponsorname'] ?></td>
                                                                        <th width="20%">Next OF Kin  Name.</th>
                                                                        <td width="30%"><?= $prospective_row['nxt_kin_fullname'] ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th width="20%">Sponsor's Phone.</th>
                                                                        <td width="30%"><?= $prospective_row['sponsorphn'] ?></td>
                                                                        <th width="20%">Next OF Kin Phone.</th>
                                                                        <td width="30%"><?= $prospective_row['nxt_kin_phone'] ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th width="20%">Sponsor's email.</th>
                                                                        <td width="30%"><?= $prospective_row['sponsoremail'] ?></td>
                                                                        <th width="20%">Next OF Kin Email.</th>
                                                                        <td width="30%"><?= $prospective_row['nxt_kin_email'] ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th width="20%">Sponsor's Address.</th>
                                                                        <td width="30%"><?= $prospective_row['sponsoradrs'] ?></td>
                                                                        <th width="20%">Next OF Kin Address.</th>
                                                                        <td width="30%"><?= $prospective_row['nxt_kin_address'] ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th width="20%">Sponsor's Relationship.</th>
                                                                        <td width="30%"><?= $prospective_row['sponsorrelation'] ?></td>
                                                                        <th width="20%">Next OF Kin Relationship.</th>
                                                                        <td width="30%"><?= $prospective_row['nxt_kin_relation'] ?></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <div class="form-actions">
                                                            <form method="post" action="form.php">
                                                                <div>
                                                                    <p> <input type="checkbox"  required="">  &nbsp;&nbsp; I affirm that the information provided in this <?= $row_rspros['sesname']?> <?= $row_rspros['typename']?>  Application Form is true and correct to the best of my Knowledge. 
                                                                    I take full responsibility for any error of omission or commision, understanding that such error will affect my consideration for Admission in to <?= $university?>.</p><br/>
                                                                    <button type="submit" name="frmsubmit" class="btn btn-primary">Submit Application Form</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p>&nbsp;</p>
                                        <p>&nbsp;</p>
                                        <p>&nbsp;</p>
                                        <?php } else {?>
                                        <div class="span12">
                                            <p>&nbsp;</p>
                                            <div class="span4">
                                                <img src="img/activation.jpg">
                                            </div>
                                            <div class="span6 alert alert-danger">
                                                <h3> Your Account has not been activated. Please check your e-Mail for your account activation link</h3>
                                            </div>
                                        </div>
                                        <?php }?>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
<?php include INCPATH . "/footer.php" ?>
        
        <!--Passport Upload--> 
        <div id="passport_upload" class="modal hide fade " style="width: 300px" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">Upload Passport</h3>
            </div>
            <form action="form.php" method="POST" class="form-horizontal form-bordered" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                        <div class="fileupload-new thumbnail" style="width: 200px; height: 150px;"><img src="<?= $image_url ?>"></div>
                        <div class="fileupload-preview fileupload-exists thumbnail" style="max-width: 200px; max-height: 150px; line-height: 20px;"></div>
                        <div>
                            <span class="btn btn-file">
                                <span class="fileupload-new">Select image</span>
                                <span class="fileupload-exists">Change</span>
                                <input type="file" name="filename">
                            </span>
                            <a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Remove</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button type="submit" name="image_upload" class="btn btn-primary">Upload Passport </button>  
                </div>
            </form>
        </div>
        <!--End of Upload Passport-->
        
        <!--Upload Docs--> 
        <div id="doc_upload" class="modal hide fade " tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">Upload Documents</h3>
            </div>
            <form action="form.php" method="POST" class="form-horizontal form-bordered" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <ul>
                            <li>Upload Document(s) as a single file for each Document Type.</li>
                            <li>Only PDF file format is Allowed.</li>
                        </ul>
                    </div>
                    <div class="control-group">
                        <label for="file_type" class="control-label">Document Type</label>
                        <div class="controls">
                            <select class="input" name="file_type" required="">
                                <option value="">Choose Document Type</option>
                                <option value="olehvel_result">O&apos;level Result</option>
                                <option value="ND_Certificate">ND Certificate</option>
                                <option value="NCE_Certificate">NCE Certficate</option>
                                <option value="Birth_Certificate">Birth Certificate</option>
                                <option value="Certificate _of_Origin">Certificate of Origin</option>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="spn_phone" class="control-label"></label>
                        <div class="controls">
                            <div class="fileupload fileupload-new" data-provides="fileupload">
                                <span class="btn btn-file">
                                    <span class="fileupload-new">Select file</span>
                                    <span class="fileupload-exists">Change</span>
                                    <input type="file" name="file" required=""></span>
                                <span class="fileupload-preview"></span>
                                <a href="#" class="close fileupload-exists" data-dismiss="fileupload" style="float: none">×</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button type="submit" name="doc_upload" class="btn btn-primary">Upload Docs </button> 
                </div>
            </form>
        </div>
        <!--End of Upload Doc-->
        
        <!--Update persona Info-->
        <div id="personal_info" class="modal hide fade " style="width: 805px; margin-left: -450px;  !important;"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">Personal Info</h3>
            </div>
            <form action="form.php" method="POST" class="form-horizontal form-bordered">
                <div class="modal-body">
                    <div class="row-fluid">
                        <div class="span6">
                            <div class="control-group">
                                <label for="textfield" class="control-label">Surname</label>
                                <div class="controls">
                                    <input type="text" name="lname" id="textfield" placeholder="Text input" class="input-large" value="<?= $prospective_row['lname'] ?>">
                                </div>
                            </div>
                            
                            <div class="control-group">
                                <label for="textfield" class="control-label">Middle Name</label>
                                <div class="controls">
                                    <input type="text" name="mname" id="textfield" placeholder="Middle Name" class="input-large" value="<?= $prospective_row['mname'] ?>">
                                </div>
                            </div>

                            <div class="control-group">
                                <label for="dob" class="control-label">Date of Birth</label>
                                <div class="controls">
                                    <input name="dob" id="dob" class="input-medium datepick" data-date-format="yyyy-mm-dd" required="true" type="text" value="<?= $prospective_row['DoB'] ?>">                                                                   
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="phone" class="control-label">Phone</label>
                                <div class="controls">
                                    <input type="text" name="phone" id="textfield" placeholder="Phone Number" class="input-large" value="<?= $prospective_row['phone'] ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="textfield" class="control-label" >State of Origin</label>
                                <div class="controls">
                                    <select name="stid" id="religion" class="input-medium" ng-model="stid" ng-change="getLga(stid)">
                                        <option ng-repeat="state in states"   value="{{state.stid}}" ng-selected="state.stid == <?= $prospective_row['stid'] ?>">{{state.stname}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="textfield" class="control-label" >Home Address</label>
                                <div class="controls">
                                    <textarea name="address" class="input-large"><?= $prospective_row['address'] ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="span6">
                            <div class="control-group">
                                <label for="textfield" class="control-label">First Name</label>
                                <div class="controls">
                                    <input type="text" name="fname" id="textfield" placeholder="First Name" class="input-large" value="<?= $prospective_row['fname'] ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="textarea" class="control-label">Sex</label>
                                <div class="controls">
                                    <select name="sex" class="input-large">
                                        <option value="">--Choose --</option>
                                        <option value="male" <?= ($prospective_row['Sex'] == 'male') ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= ($prospective_row['Sex'] == 'female') ? 'selected' : '' ?>>Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="dob" class="control-label">Physical Stature</label>
                                <div class="controls">
                                    <select name="healthstatus" id="healthstatus" required="true" class="input-medium">
                                        <option value="fit" <?= ($prospective_row['healthStatus'] == 'fit') ? 'selected' : '' ?>>Fit</option>
                                        <option value="disabled" <?= ($prospective_row['healthStatus'] == 'disabled') ? 'selected' : '' ?>>Disable</option>
                                    </select>
                                    <span for="healthstatus" class="help-block error valid"></span>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="textfield" class="control-label">E-Mail</label>
                                <div class="controls">
                                    <input type="email" name="email" placeholder="Text input" class="input-large" value="<?= $prospective_row['email'] ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="textfield" class="control-label" >L.G.A of Origin</label>
                                <div class="controls">
                                    <select name="lga" id="lga" class="input-medium">
                                        <option ng-repeat="l in lga.rs" value="{{l.lgaid}}">{{l.lganame}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="textfield" class="control-label">Religion</label>
                                <div class="controls">
                                    <select name="religion" id="religion"class="input-medium">
                                        <option value="christianity" <?= ($prospective_row['Religion'] == 'christianity') ? 'selected' : '' ?>>Christianity</option>
                                        <option value="islam" <?= ($prospective_row['Religion'] == 'islam') ? 'selected' : '' ?>>Islam</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button type="submit" name="personal_info" class="btn btn-primary">Save and contnue</button>
                </div>
            </form>
        </div>
        <!--End--> 
        
        <!--Sponsor Next of Kin-->
        <div id="spn_nxk_info" class="modal hide fade "  style="width: 55%; margin-left: -655px !important;" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">Sponsors / next of Kin Information.</h3>
            </div>
            <form action="form.php" method="POST" class="form-horizontal form-bordered">
                <div class="modal-body">
                    <div class="row-fluid">
                        <div class="span6">
                            <h5>Sponsor's Info</h5>
                            <div class="control-group">
                                <label for="spn_full name" class="control-label">Full Name</label>
                                <div class="controls">
                                    <input type="text" name="spn_full_name" id="textfield" placeholder="Sponsor Full name" class="input-xlarge" value="<?= $prospective_row['sponsorname'] ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="spn_phone" class="control-label">Phone</label>
                                <div class="controls">
                                    <input type="text" name="spn_phone" id="textfield" placeholder="Phone Number" class="input-xlarge" value="<?= $prospective_row['sponsorphn'] ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="spn_phone" class="control-label">E-mail</label>
                                <div class="controls">
                                    <input type="email" name="spn_email" id="textfield" placeholder="E-mail" class="input-xlarge" value="<?= $prospective_row['sponsoremail'] ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="textfield" class="control-label" >Home Address</label>
                                <div class="controls">
                                    <textarea name="spn_address" class="input-xlarge"><?= $prospective_row['sponsoradrs'] ?></textarea>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="spn_phone" class="control-label">Relationship</label>
                                <div class="controls">
                                    <input type="text" name="spn_relationship" id="textfield" placeholder="Relationship " class="input-xlarge" value="<?= $prospective_row['sponsorrelation'] ?>" >
                                </div>
                            </div>
                        </div>
                        <div class="span6">
                            <h5>Next of Kin Information.</h5>
                            <div class="control-group">
                                <label for="n_full name" class="control-label">Full Name</label>
                                <div class="controls">
                                    <input type="text" name="nxk_full_name" id="textfield" placeholder="Full Name" class="input-xlarge" value="<?= $prospective_row['nxt_kin_fullname'] ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="nxk_phone" class="control-label">Phone</label>
                                <div class="controls">
                                    <input type="text" name="nxk_phone" id="textfield" placeholder="Phone Number" class="input-xlarge" value="<?= $prospective_row['nxt_kin_phone'] ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="nxk_phone" class="control-label">E-mail</label>
                                <div class="controls">
                                    <input type="email" name="nxk_email" id="textfield" placeholder="E-mail" class="input-xlarge" value="<?= $prospective_row['nxt_kin_email'] ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="textfield" class="control-label" >Home Address</label>
                                <div class="controls">
                                    <textarea name="nxk_address" class="input-xlarge"><?= $prospective_row['nxt_kin_address'] ?></textarea>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="nxk_relationship" class="control-label">Relationship</label>
                                <div class="controls">
                                    <input type="text" name="nxk_relationship" id="textfield" placeholder="Relationship " class="input-xlarge"value="<?= $prospective_row['nxt_kin_relation'] ?>" >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button type="submit" name="psns_and_nxkn" class="btn btn-primary">Save and Continue</button>
                </div>
            </form>
        </div>
        <!--End--> 
        
        <!--UTME RESULT-->
        <div id="utme_result" class="modal hide fade "  style="width: 50%; margin-left: -400px !important;" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">UTME Result.</h3>
            </div>
            <form action="form.php" method="POST" class="form-horizontal form-bordered">
                <div class="modal-body">
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="well">
                                <div class="control-group">
                                    <label for="textfield" class="control-label">U.T.M.E ID</label>
                                    <div class="controls">
                                        <input type="text" name="jambregid" id="textfield" placeholder="UTME ID" class="input-xlarge" value="<?= $prospective_row['jambregid'] ?>">
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label for="textfield" class="control-label">U.T.M.E Year</label>
                                    <div class="controls">
                                        <input type="text" name="jambyear" id="textfield" placeholder="UTME Year" class="input-xlarge" value="<?= $prospective_row['jambyear'] ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if(!in_array($prospective_row['typeid'], [1])){?>
                        <div class="span12">
                            <div class="well">
                                <table class="table table-bordered ">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Subject</th>
                                            <th>Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>
                                                English Language
                                                <input type="hidden" name="jambsubj1" value="3">
                                            </td>
                                            <td>
                                                <input type="unmber" class="input" name="jambscore1" value="<?= (int) $prospective_row['jambscore1'] ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>
                                                <select name="jambsubj2">
                                                    <option ng-repeat="subject in subjects" value="{{subject.subjid}}" ng-selected="subject.subjid == '<?= $prospective_row['jambsubj2'] ?>'">{{subject.subjname}}</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="unmber" class="input" name="jambscore2" value="<?= (int) $prospective_row['jambscore2'] ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>
                                                <select name="jambsubj3">
                                                    <option ng-repeat="subject in subjects" value="{{subject.subjid}}" ng-selected="subject.subjid == '<?= $prospective_row['jambsubj3'] ?>'">{{subject.subjname}}</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="unmber" class="input"   name="jambscore3" value="<?= (int) $prospective_row['jambscore3'] ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>
                                                <select name="jambsubj4">
                                                    <option ng-repeat="subject in subjects" value="{{subject.subjid}}" ng-selected="subject.subjid == '<?= $prospective_row['jambsubj4'] ?>'">{{subject.subjname}}</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="unmber" class="input"  name="jambscore4" value="<?= (int) $prospective_row['jambscore4'] ?>">
                                            </td>
                                        </tr>
                                    </tbody>
                                    <thead>
                                        <tr>
                                            <th colspan="2">Total Score</th>
                                            <th><?= $prospective_row['jambscore1'] + $prospective_row['jambscore2'] + $prospective_row['jambscore3'] + $prospective_row['jambscore4'] ?> </th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button type="submit" name="jamb_result" class="btn btn-primary">Save and continue</button>
                </div>
            </form>
        </div>
        <!--End--> 
        
        <!--Olevel Result-->
        <div id="oleve_result" class="modal hide fade "  style="width: 80%; margin-left: -655px !important;" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">UTME Result.</h3>
            </div>
            <form action="form.php" method="POST" class="form-horizontal form-bordered">
                <div class="modal-body">
                    <input type="checkbox" value='1' ng-click="trigerSit2()"> Enable second Sitting 
                    <div class="row-fluid">
                        <div class="row-fluid">
                            <div class="span6">
                                <h4>First Sitting</h4>
                                <div class="well">
                                    <div class="control-group">
                                        <label for="nxk_phone" class="control-label">Exam Number</label>
                                        <div class="controls">
                                            <input type="text" name="olevel[0][exam_num]" id="textfield" placeholder="Exam number " class="input-xlarge" required="">
                                        </div>
                                    </div>
                                    <div class="control-group">
                                        <label for="nxk_phone" class="control-label">Exam Type</label>
                                        <div class="controls">
                                            <select id="exmtyp2" name="olevel[0][exam_type]" class="input-medium"  required="">
                                                <option value="">--Choose--</option>
                                                <option value="WASCE(MAY/JUNE)">WASCE(MAY/JUNE)</option>
                                                <option value="WASCE(Private)">WASCE(Private)</option>
                                                <option value="NECO">NECO</option>
                                                <option value="NECO(Private)">NECO(Private)</option>
                                                <option value="NABTEB(MAY/JUNE)">NABTEB(MAY/JUNE)</option>
                                                <option value="NABTEB(Private)">NABTEB(Private)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="control-group">
                                        <label for="nxk_phone" class="control-label">Exam Year</label>
                                        <div class="controls">
                                            <input type="text" name="olevel[0][exam_year]" id="textfield" placeholder="Exam Year " required="" class="input-xlarge">
                                        </div>
                                        <input type="hidden" name="olevel[0][sitting]" value="first">
                                    </div>
                                    <table class="table table-bordered ">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Subject</th>
                                                <th>Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php for ($idx = 0; $idx < 9; $idx++) { ?>
                                                <tr>
                                                    <td><?= $idx + 1 ?></td>
                                                    <td>
                                                        <select name="olevel[0][subject][<?= $idx ?>]" required="">
                                                            <option value=""> -- Choose Subject -- </option>
                                                            <option ng-repeat="subject in subjects" value="{{subject.subjid}}">{{subject.subjname}}</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="subj2['grade'][]" name="olevel[0][grade][<?= $idx ?>]" class="input-medium" required="">
                                                            <option value="">grade</option>
                                                            <option value="1">A1</option>
                                                            <option value="2">B2</option>
                                                            <option value="3">B3</option>
                                                            <option value="4">C4</option>
                                                            <option value="5">C5</option>
                                                            <option value="6">C6</option>
                                                            <option value="7">D7</option>
                                                            <option value="8">E8</option>
                                                            <option value="9">F9</option>
                                                            <option value="10">AR</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="span6" ng-if="sit2">
                                <h4>Second Sitting</h4>
                                <div class="well">
                                    <div class="control-group">
                                        <label for="nxk_phone" class="control-label">Exam Number</label>
                                        <div class="controls">
                                            <input type="text" name="olevel[1][exam_num]" id="textfield" placeholder="Exam number " class="input-xlarge">
                                        </div>
                                    </div>
                                    <div class="control-group">
                                        <label for="nxk_phone" class="control-label">Exam Type</label>
                                        <div class="controls">
                                            <select id="exmtyp2" name="olevel[1][exam_type]" class="input-medium" data-rule-required="true">
                                                <option value="-1">--Choose--</option>
                                                <option value="WASCE(MAY/JUNE)">WASCE(MAY/JUNE)</option>
                                                <option value="WASCE(Private)">WASCE(Private)</option>
                                                <option value="NECO">NECO</option>
                                                <option value="NECO(Private)">NECO(Private)</option>
                                                <option value="NABTEB(MAY/JUNE)">NABTEB(MAY/JUNE)</option>
                                                <option value="NABTEB(Private)">NABTEB(Private)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="control-group">
                                        <label for="nxk_phone" class="control-label">Exam Year</label>
                                        <div class="controls">
                                            <input type="text" name="olevel[1][exam_year]" id="textfield" placeholder="Exam Year " class="input-xlarge">
                                        </div>
                                        <input type="hidden" name="olevel[1][sitting]" value="second">
                                    </div>
                                    <table class="table table-bordered ">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Subject</th>
                                                <th>Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php for ($idx1 = 0; $idx1 < 9; $idx1++) { ?>
                                                <tr>
                                                    <td><?= $idx1 + 1 ?></td>
                                                    <td>
                                                        <select name="olevel[1][subject][<?= $idx1 ?>]">
                                                            <option value="-1">--Choose--</option>
                                                            <option ng-repeat="subject in subjects" value="{{subject.subjid}}">{{subject.subjname}}</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="subj2['grade'][]" name="olevel[1][grade][<?= $idx1 ?>]" class="input-medium"  

                                                                data-rule-required="true">
                                                            <option value="-1">grade</option>
                                                            <option value="1">A1</option>
                                                            <option value="2">B2</option>
                                                            <option value="3">B3</option>
                                                            <option value="4">C4</option>
                                                            <option value="5">C5</option>
                                                            <option value="6">C6</option>
                                                            <option value="7">D7</option>
                                                            <option value="8">E8</option>
                                                            <option value="9">F9</option>
                                                            <option value="10">AR</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button type="submit" name="o_level_result" class="btn btn-primary">Save and continue</button>
                </div>
            </form>
        </div>
        <!--END-->
        
        <!--Programme Choice--> 
        <div id="prog_choice" class="modal hide fade "   tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">Program Choice </h3>
            </div>
            <form action="form.php" method="POST" class="form-horizontal form-bordered">
                <div class="modal-body">
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="control-group">
                                <label for="textfield" class="control-label">Program Choice 1</label>
                                <div class="controls">
                                    <select name="progid1" id="progid1" class="input-xlarge">
                                        <option ng-repeat="prog in programme" value="{{prog.progid}}" ng-selected="prog.progid == '<?= $prospective_row['progid1']?>'">{{prog.progname}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="textfield" class="control-label">Program Choice 2</label>
                                <div class="controls">
                                    <select name="progid2" id="progid1" class="input-xlarge">
                                        <option ng-repeat="prog in programme" value="{{prog.progid}}" ng-selected="prog.progid == '<?= $prospective_row['progid2'] ?>'">{{prog.progname}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button type="submit" name="prog_choice" class="btn btn-primary">Save and continue</button>
                </div>
            </form>
        </div>
        <!--End-->
        
        <!--Previouse School-->
        <div id="prev_school" class="modal hide fade"  style="width: 80%; margin-left: -655px !important;"   tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">Program Choice </h3>
            </div>
            <form action="form.php" method="POST" class="form-horizontal form-bordered" novalidate="" name="previous_school">
                <div class="modal-body">
                    <div class='well well-large'>
                        <div class="row-fluid">
                            <div class="span3">
                                <input type="text" name="scname" class="input" placeholder="SchooL Name" ng-model="prev_sch.name" ng-required="true">
                            </div>
                            <div class="span3">
                                <textarea name="schadd" placeholder="School Address" cols="10" rows="3" class="input" ng-model="prev_sch.addr" ng-required="true"></textarea>
                            </div>
                            <div class="span1">
                                <input type="text" name="from" id="from" class="prev_date input input-mini"  placeholder="From" ng-model="prev_sch.from"ng-required="true" >
                            </div>
                            <div class="span1">
                                <input type="text" name="to" id="to" class="prev_date input input-mini" placeholder="To" ng-model="prev_sch.to" ng-required="true">
                            </div>
                            <div class="span3">
                                <select class="input input-medium" name="cert" ng-model="prev_sch.cert" ng-required="true">
                                    <option value="Primary School Leaving Certificate">Primary School Certificate</option>
                                    <option value="WASCE/SSCE Certificate">WASCE/SSCE Certificate</option>
                                    <option value="NCE Certificate">NCE Certificate</option>
                                    <?php if(in_array($prospective_row['typeid'], ['1'])){?>
                                    <option value="ND Certificate">ND Certificate</option>
                                    <option value="HND Certificate">HND Certificate</option>
                                    <option value="BSc Certificate">BSc Certificate</option>
                                    <?php }?>
                                </select>
                            </div>
                            <div class="span1">
                                <button type="button" ng-click="addPrevSchool(prev_sch, previous_school.$valid )">Add</button>
                            </div>
                        </div>
                    </div>
                    <div class="well">
                        <table class="table table-condensed table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>School Name</th>
                                    <th>School Address</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Certificate Obtains</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="prv_sch in prev_school track by $index">
                                    <td>{{$index +1}}</td>
                                    <td>{{prv_sch.name}}</td>
                                    <td>{{prv_sch.addr}}</td>
                                    <td>{{prv_sch.from}}</td>
                                    <td>{{prv_sch.to}}</td>
                                    <td>{{prv_sch.cert}}</td>
                                    <td><button type="button" class="btn btn-small btn-warning" ng-click="removePrevSchool($index)">Remove</button></td>
                                </tr>
                            <input type="hidden" name="post_prev_school" value="{{prev_school}}">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button type="submit" name="prog_choice" class="btn btn-primary">Save and continue</button>
                </div>
            </form>
        </div>
        <!--End-->
    </body>
    <script>
        $('.prev_date').datepicker({
            'format':'yyyy',
            'viewMode': 'years'
        });
        
        var app = angular.module('tams_admission_appform', []);
        app.controller('MainCtrl', function($scope, $http){
        
            $scope.prev_school = [];
            $scope.stage = '<?= $stage?>';
            $scope.states = <?= json_encode($state_array)?>;
            $scope.programme = <?= json_encode($prog_array)?>;
            $scope.subjects = <?= json_encode($subj_array)?>;
            $scope.form_stage = <?= json_encode($formStage_array)?>;
            $scope.prev_school_submitted = <?= json_encode($prevSchool_array)?>;
            $scope.uploaded_doc = <?= ($totalRows_doc > 0) ? json_encode($doc_upload_array) : '[]'?>;
            
            
         $scope.score4 = 0;
            
            console.log($scope.stage);
            
            $scope.addPrevSchool = function(sch, isValid){
                if(isValid){
                    $scope.prev_school.push(sch);
                    console.log($scope.prev_school);
                    $scope.prev_sch = {};
                }else{
                    alert("The From Field is not valid");
                }
                  
            };
            
            $scope.removePrevSchool = function(sch){
                if(confirm("Are you sure you want to remove this ?")){
                    $scope.prev_school.splice(sch, 1);
                }  
            };
            
            $scope.foundInAppStage = function(v){
                for(var i = 0; i < $scope.form_stage.length; i++){
                    if($scope.form_stage[i].stage === v){
                        return true;
                    }
                }
                return false;
            };
            
            $scope.sit2 = false;
            $scope.trigerSit2 = function(){
                $scope.sit2 = !$scope.sit2;
            }
            
            $scope.getLga = function(stid){
                if(stid != ''){
                    $http({
                        method : "POST",
                        url : "api/index.php?action=lga",
                        data: stid, 
                    }).then(function mySucces(response) {
                        $scope.lga = response.data;
                        $scope.loading = false; 
                    }, function myError(response) {
                        $scope.lga = response.statusText;

                    });
                }
            }
        });
    </script>
</html>


