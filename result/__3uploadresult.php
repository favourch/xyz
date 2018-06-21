<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once ('../phpexcel/PHPExcel/IOFactory.php');
require_once('../path.php');



$auth_users = "2,3,4,5,6";
check_auth($auth_users, $site_root);

$tot_pass = 0;
$tot_fail = 0;
$pcent1 = 0;
$pcent2 = 0;
$heighest_scr = '-';
$lowest_scr = '-';
$scores = array();

$insert_row = 0;
$insert_error = [];
$uploadstat = NULL;
$type = "error";

if (isset($_POST['submit']) && $_POST['submit'] == "Upload Result") { //database query to upload result	
    $sesid = $_POST['sesid'];
    $csid = $_POST['csid'];
    $dpt = (isset($_POST['deptid'])) ? $_POST['deptid'] : getSessionValue('did');

    $allowed_type = ['text/comma-separated-values',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel'
    ];

    if (is_uploaded_file($_FILES['filename']['tmp_name']) && in_array($_FILES['filename']['type'], $allowed_type)) {

        //Query for select boxes in the result view	
        $query_exist = sprintf("SELECT r.resultid, r.stdid "
                . "FROM result r "
                . "WHERE r.sesid=%s "
                . "AND r.csid=%s ", GetSQLValueString($sesid, "int"), GetSQLValueString($csid, "text"));
        $exist = mysql_query($query_exist, $tams) or die(mysql_error());
        $row_exist = mysql_fetch_assoc($exist);
        $totalRows_exist = mysql_num_rows($exist);

        $existing_courses = [];
        for ($idx = 0; $idx < $totalRows_exist; $idx++, $row_exist = mysql_fetch_assoc($exist)) {
            $existing_courses[$row_exist['stdid']] = $row_exist['resultid'];
        }

        $ids = [];
        $missing_entries = [];
        $update_columns = ['tscore' => '`tscore` = CASE ', 'escore' => '`escore` = CASE '];

        mysql_query("BEGIN", $tams);

        //Import uploaded file to Database	
        $objPHPExcel = PHPExcel_IOFactory::load($_FILES['filename']['tmp_name']);
        $objWorkSheet = $objPHPExcel->getActiveSheet();
        $objIterator = $objWorkSheet->getRowIterator();

        unset($objPHPExcel);
        unset($objWorkSheet);

        foreach ($objIterator as $row) {

            $stdid = (string) $row->getColumnValue(0)->getValue();
            $tscore = $row->getColumnValue(1)->getValue();
            $escore = $row->getColumnValue(2)->getValue();

            if (array_key_exists($stdid, $existing_courses)) {
                // Update entry for tscore
                $update_columns['tscore'] .= sprintf("WHEN `resultid` = %s THEN %s ", GetSQLValueString($existing_courses[$stdid], "int"), GetSQLValueString($tscore, "int"));

                // Update entry for escore
                $update_columns['escore'] .= sprintf("WHEN `resultid` = %s THEN %s ", GetSQLValueString($existing_courses[$stdid], "int"), GetSQLValueString($escore, "int"));
                $ids[] = $existing_courses[$stdid];
                $insert_row++;
            } else {
                $missing_entries[] = [$stdid, $tscore, $escore];
            }
        }

        unset($objIterator);

        if ($insert_row > 0) {
            $update_columns['tscore'] .= 'END';
            $update_columns['escore'] .= 'END';
            $where = sprintf(" WHERE `resultid` IN (%s)", GetSQLValueString("ids", "defined", implode(',', $ids)));

            $update_query = sprintf("UPDATE `result` SET %s %s", GetSQLValueString("update_columns", "defined", implode(',', $update_columns)), GetSQLValueString($where, "defined", $where));

            $rsupdate = mysql_query($update_query, $tams);
        }

        if (!empty($missing_entries)) {
            $entry = [];
            foreach ($missing_entries as $data) {
                $data[3] = $csid;
                $data[4] = $sesid;
                $data[5] = getSessionValue('uid');
                $entry[] = "('" . implode("','", $data) . "')";
            }

            $entry = implode(',', $entry);
            $repl_query = sprintf("REPLACE INTO result_error (stdid, tscore, escore, csid, sesid, lectid) "
                                . "VALUES %s;", GetSQLValueString($entry, "defined", $entry));
            $rsrepl = mysql_query($repl_query, $tams);

            $uploadstat = "Unfortunately, the result file contained ". count($missing_entries)." unregistered students! "
                    . " Their results could not be uploaded <a href='result_error.php?sid={$sesid}&csid={$csid}'>"
                    . "Click here to view</a>.";
            $notification->set_Notification($uploadstat, 'error');
        } else {
            $uploadstat = "Upload Successful! " . $insert_row . " results uploaded.";
            $type = 'success';
            $notification->set_Notification($uploadstat, 'success');
        }

        $insert_query = sprintf("UPDATE teaching SET upload=%s WHERE csid=%s AND sesid=%s AND deptid=%s", GetSQLValueString("Yes", "text"), GetSQLValueString($csid, "text"), GetSQLValueString($sesid, "int"), GetSQLValueString($dpt, "int"));
        mysql_query($insert_query, $tams);

        mysql_query("COMMIT", $tams);
    } else {
        $uploadstat = "The file you specified is not of the accepted type! Please upload an Excel or CSV file.";
        $notification->set_Notification($uploadstat, 'error');
    }
}


$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,4";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$colname_rslt = "-1";
if (isset($_GET['csid'])) {
  $colname_rslt = $_GET['csid'];
}

$colname1_rslt = "-1";
//if (isset($row_sess['sesid'])) {
//  $colname1_rslt = $row_sess['sesid'];
//}

if (isset($_GET['sid'])) {
    $colname1_rslt = $_GET['sid'];
}
else{
    $colname1_rslt = $row_sess['sesid'];   
}

$colname2_rslt = "-1";
$filter = '';
if (isset($_GET['did'])) {
	$colname2_rslt = $_GET['did'];
	$filter = "AND p.deptid =".$colname2_rslt;
}

$colname_dept = "-1";
if (getSessionValue('cid') != NULL) {
    $colname_dept = getSessionValue('cid');
}

mysql_select_db($database_tams, $tams);
$query_status = sprintf("SELECT colid, approve, upload, progname, type "
                        . "FROM course c, teaching t, programme p, department d "
                        . "WHERE d.deptid = p.deptid "
                        . "AND c.csid = t.csid "
                        . "AND t.deptid = p.deptid "
                        . "AND t.deptid = %s "
                        . "AND sesid = %s "
                        . "AND t.csid = %s", 
                        GetSQLValueString($colname2_rslt, "int"), 
                        GetSQLValueString($colname1_rslt, "int"), 
                        GetSQLValueString($colname_rslt, "text"));

$status = mysql_query($query_status, $tams) or die(mysql_error());
$row_status = mysql_fetch_assoc($status);
$totalRows_status = mysql_num_rows($status);

$query_dept = sprintf("SELECT deptid, deptname "
                    . "FROM department "
                    . "WHERE colid = %s", 
                    GetSQLValueString($colname_dept, "int"));
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$totalRows_crsType = 0;
if (isset($_GET['crs'])) {
    
    $queryCsType = sprintf("SELECT * FROM course c, category ct, department d, college cl "
                        .  "WHERE c.catid = ct.catid "
                        .  "AND c.deptid = d.deptid "
                        .  "AND d.colid = cl.colid "
                        .  "AND csid = %s ",
                         GetSQLValueString($_GET['crs'], 'text'));
    $crsType = mysql_query($queryCsType, $tams) or die(mysql_error());
    $row_crsType = mysql_fetch_assoc($crsType);
    $totalRows_crsType = mysql_num_rows($crsType);
    
}

$filt = '';
if(in_array($row_crsType['catname'], ['Enterpreneural','General' ,'Educational'])){
    $filt .= sprintf(" WHERE deptid = %s ", GetSQLValueString(getSessionValue('did'), "text"));
    
}

$query_prog = sprintf("SELECT progid, progname FROM programme %s ", $filt);
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

//die(var_dump($_SESSION));

$colname_crs = "-1";
if (isset($row_dept['deptid'])) {
    $colname_crs = $row_dept['deptid'];
}

if (isset($_GET['did'])) {
    $colname_crs = $_GET['did'];
}

if (getAccess() == 3) {
    $colname_crs = getSessionValue('did');
}

if (getAccess() == 4 || getAccess() == 5 || getAccess() == 6) {
    $colname_crs = getSessionValue('did');
}

$filter = "";
$colname1_crs = "-1";
if (getSessionValue('uid') != NULL) {
    $colname1_crs = getSessionValue('uid');
    $filter = "AND lectid1=" . GetSQLValueString($colname1_crs, 'text');
}

if (getAccess() == 2 || getAccess() == 3) {
    $filter = "";
}

$colname2_crs = "-1";
if (isset($row_sess['sesid'])) {
    $colname2_crs = $row_sess['sesid'];
}

if (isset($_GET['sid'])) {
    $colname2_crs = $_GET['sid'];
}

$query_crs = sprintf("SELECT csid "
                    . "FROM teaching "
                    . "WHERE upload='no' "
                    . "AND sesid=%s "
                    . "AND deptid=%s %s "
                    . "ORDER BY csid ASC", 
                    GetSQLValueString($colname2_crs, "int"), 
                    GetSQLValueString($colname_crs, "int"), 
                    $filter);
$crs = mysql_query($query_crs, $tams) or die(mysql_error());
$row_crs = mysql_fetch_assoc($crs);
$totalRows_crs = mysql_num_rows($crs);


//Query for select boxes in the result view	
$query_ses = sprintf("SELECT DISTINCT s.sesname, t.sesid "
                    . "FROM session s, teaching t "
                    . "WHERE s.sesid = t.sesid "
                    . "AND t.lectid1=%s "
                    . "ORDER BY s.sesname DESC", 
                    GetSQLValueString(getSessionValue('uid'), "text"));
$ses = mysql_query($query_ses, $tams) or die(mysql_error());
$row_ses = mysql_fetch_assoc($ses);
$totalRows_ses = mysql_num_rows($ses);

$colname_sescrs = "-1";
if (isset($row_ses['sesid'])) {
    $colname_sescrs = $row_ses['sesid'];
}

if (isset($_GET['ssid'])) {
    $colname_sescrs = $_GET['ssid'];
}
$colname1_progcrs = '-1';
if (isset($_GET['pid'])) {
    $colname1_progcrs = $_GET['pid'];
}

$colname1_sescrs = "-1";
if (isset($_GET['crs'])) {
    $colname1_sescrs = $_GET['crs'];
}

$query_sescrs = sprintf("SELECT t.csid "
                        . "FROM teaching t "
                        . "WHERE t.sesid=%s "
                        . "AND t.lectid1=%s "
                        . "ORDER BY t.csid DESC", 
                        GetSQLValueString($colname_sescrs, "int"), 
                        GetSQLValueString(getSessionValue('uid'), "text"));
$sescrs = mysql_query($query_sescrs, $tams) or die(mysql_error());
$row_sescrs = mysql_fetch_assoc($sescrs);
$totalRows_sescrs = mysql_num_rows($sescrs);

$colname2_rslt = "-1";
if (getSessionValue('uid') != NULL) {
    $colname2_rslt = getSessionValue('uid');
}

$query_rslt = sprintf("SELECT r.csid, r.edited, r.stdid, r.sesid, tscore, escore, s.fname, s.lname "
                    . "FROM result r, student s, programme p, teaching t "
                    . "WHERE r.stdid = s.stdid "
                    . "AND r.csid = t.csid "
                    . "AND t.upload = 'yes' "
                    . "AND t.lectid1=%s "
                    . "AND r.csid = %s "
                    . "AND r.sesid = t.sesid "
                    . "AND r.sesid = %s "
                    . "AND s.progid = p.progid", 
                    GetSQLValueString($colname2_rslt, "text"), 
                    GetSQLValueString($colname1_sescrs, "text"), 
                    GetSQLValueString($colname_sescrs, "int"));
$rslt = mysql_query($query_rslt, $tams) or die(mysql_error());
//$row_rslt = mysql_fetch_assoc($rslt);
$totalRows_rslt = mysql_num_rows($rslt);


$query_error = sprintf("SELECT count(*) as count "
                    . "FROM result_error r "
                    . "WHERE r.csid = %s "
                    . "AND r.sesid = %s ", 
                    GetSQLValueString($colname1_sescrs, "text"), 
                    GetSQLValueString($colname_sescrs, "int"));
$error = mysql_query($query_error, $tams) or die(mysql_error());
$row_error = mysql_fetch_assoc($error);
$totalRows_error = mysql_num_rows($error);

$error_link = $totalRows_error > 0 ?
        "<a target='_blank' href='result_error.php?sid={$colname_sescrs}&csid={$colname1_sescrs}'>View result errors</a>" : '';

//Query for select boxes in the result view//
$sname = "";
do {
    if ($colname2_crs == $row_sess['sesid']) {
        $sname = $row_sess['sesname'];
    }
} while ($row_sess = mysql_fetch_assoc($sess));




$query_grad = sprintf("SELECT * FROM grading g, session s WHERE g.sesid = %s AND g.colid = %s",
                GetSQLValueString($colname1_rslt, "int"),
                GetSQLValueString($row_crsType['colid'], "int"));
$grad = mysql_query($query_grad, $tams) or die(mysql_error());
$row_grad = mysql_fetch_assoc($grad);
$totalRows_grad = mysql_num_rows($grad);

if($totalRows_rslt > 0){
    for (; $row_rslt = mysql_fetch_assoc($rslt);) {
        
        $tot_scr = $row_rslt['tscore'] + $row_rslt['escore'];
        if ($tot_scr > $row_grad['passmark']) {

            $tot_pass = $tot_pass + 1;
            $pcent1 = $tot_pass * 100 / $totalRows_rslt;
        }
        else {

            $tot_fail = $tot_fail + 1;
            $pcent2 = $tot_fail * 100 / $totalRows_rslt;
        }
        
        array_push($scores, $tot_scr);
    }

    $heighest_scr = max($scores);
    $lowest_scr = min($scores);

    mysql_data_seek($rslt, 0);  
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
                    
                    <div class="row-fluid">                        
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Result Upload
                                    </h3>
                                    <a data-toggle="modal" href="#help-modal" class="pull-right btn btn-primary btn-medium">
                                        Upload Procedure - Help
                                    </a>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <form name="form1" method="post" enctype="multipart/form-data">
                                            <fieldset>
                                                <legend>Upload Result for <?php echo $sname; ?></legend>  
                                                <?php if (getAccess() == 2) { ?>          
                                                <select name="deptid" onchange="deptfilt(this)">
                                                <?php do { ?>
                                                    <option value="<?php echo $row_dept['deptid'] ?>"
                                                            <?php if (!(strcmp($row_dept['deptid'], $colname_crs))) {
                                                                echo "selected=\"selected\"";
                                                            } ?>>
                                                <?php echo $row_dept['deptname'] ?></option>
                                                <?php } while ($row_dept = mysql_fetch_assoc($dept)); ?>

                                                </select>
                                                <?php } ?>

                                                <select name="sesid" onchange="sesfilt(this)">
                                                <?php
                                                $rows = mysql_num_rows($sess);
                                                if ($rows > 0) {
                                                    mysql_data_seek($sess, 0);
                                                    $row_sess = mysql_fetch_assoc($sess);
                                                }
                                                do {
                                                    ?>
                                                    <option value="<?php echo $row_sess['sesid'] ?>"
                                                    <?php if (!(strcmp($row_sess['sesid'], $colname2_crs))) {
                                                        echo "selected=\"selected\"";
                                                    } ?>>
                                                    <?php echo $row_sess['sesname'] ?></option>
                                                    <?php
                                                } while ($row_sess = mysql_fetch_assoc($sess));

                                                $rows = mysql_num_rows($sess);
                                                if ($rows > 0) {
                                                    mysql_data_seek($sess, 0);
                                                    $row_sess = mysql_fetch_assoc($sess);
                                                }
                                                ?>

                                                </select>

                                                <br /><br />
                                                <p class="text-error">
                                                    Please ensure your Excel/CSV file has just three columns, in this format 'Matric no| CA | Exam'. 
                                                    <br/>NOTE: No headers are required!
                                                </p>
                                                
                                                <input name="filename" type="file" />
                                                    <?php if ($totalRows_crs > 0) { // Show if recordset not empty ?>
                                                    <select name="csid">
                                                    <?php do { ?>
                                                        
                                                        <option value="<?php echo $row_crs['csid'] ?>" 
                                                        <?php if (!(strcmp($row_crs['csid'], $colname_dept))) {
                                                            echo "selected";
                                                        } ?>>
                                                        <?php echo $row_crs['csid'] ?>
                                                        </option>
                                                    <?php } while ($row_crs = mysql_fetch_assoc($crs)); ?> 
                                                    
                                                    </select>
                                                    <?php } else { ?>
                                                        No course available. 
                                                    <?php } ?>
                                                        <input type="submit" name="submit" value="Upload Result" class="btn btn-primary"/>
                                            </fieldset>
                                        </form>
                                    </div>                                    
                                </div>
                            </div>
                            
                            <div class="box box-bordered box-color">
                                 <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Uploaded Result 
                                    </h3>
                                </div>

                                <div class="box-content">
                                    <form class="form-vertical" method="POST" action="printresult2.php" target="_blank">
                                        <div class="row-fluid">
                                                <div class="span3">
                                                        <div class="control-group">
                                                                <label class="control-label" for="textfield">Session</label>
                                                                <div class="controls controls-row">
                                                                    <select name="sesid" id="ssid" onchange="ssesfilt(this)">
                                                                        <?php
                                                                        do {
                                                                            ?>
                                                                            <option value="<?php echo $row_ses['sesid'] ?>"
                                                                            <?php
                                                                            if (!(strcmp($row_ses['sesid'], $colname_sescrs))) {
                                                                                echo "selected=\"selected\"";
                                                                            }
                                                                            ?>>
                                                                                <?php echo $row_ses['sesname'] ?></option>
                                                                            <?php
                                                                        }
                                                                        while ($row_ses = mysql_fetch_assoc($ses));
                                                                        ?>

                                                                    </select>
                                                                </div>
                                                        </div>
                                                </div>
                                                <div class="span3">
                                                        <div class="control-group">
                                                                <label class="control-label" for="textfield">Course</label>
                                                                <div class="controls controls-row">
                                                                    <select name="csid" onchange="crsfilt(this)">
                                                                        <option value="-1">----</option>
                                                                        <?php do { ?>
                                                                            <option value="<?php echo $row_sescrs['csid'] ?>"
                                                                            <?php
                                                                            if (!(strcmp($row_sescrs['csid'], $colname1_sescrs))) {
                                                                                echo "selected=\"selected\"";
                                                                            }
                                                                            ?>>
                                                                                <?php echo $row_sescrs['csid'] ?></option>

                                                                            <?php
                                                                        }
                                                                        while ($row_sescrs = mysql_fetch_assoc($sescrs));
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                        </div>
                                                </div>
                                           
                                                <div class="span3">
                                                    <div class="control-group">
                                                        <label class="control-label" for="textfield"><?= $programme_name?></label>
                                                        <div class="controls controls-row">
                                                            <select name="progid">
                                                                <option value="-1">----</option>
                                                                <?php do { ?>
                                                                    <option value="<?php echo $row_prog['progid'] ?>"
                                                                    <?php
                                                                    if (!(strcmp($row_prog['progid'], $colname1_progcrs))) {
                                                                        echo "selected=\"selected\"";
                                                                    }
                                                                    ?>>
                                                                        <?php echo $row_prog['progname'] ?></option>

                                                                    <?php
                                                                }while ($row_prog = mysql_fetch_assoc($prog));
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            <input type="hidden" name="MM_Insert" value="form1">
                                                <div class="span2">
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">&nbsp;</label>
                                                                <div class="controls controls-row">
                                                                    <button type="submit" name="submit">Print Result</button>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>
                                    </form>
                                  
                                    <div class="row-fluid">

                                        <div>Total no. of Students:
                                            <span id="total"><?php echo $totalRows_rslt ?></span> (100%)
                                        </div>

                                        <div>
                                            No. Passed: <span id="pass"><?= $tot_pass ." (".number_format($pcent1)."%)" ?></span>
                                        </div>

                                        <div>
                                            No. Failed: <span id="fail"><?= $tot_fail ." (".number_format($pcent2)."%)" ?></span>
                                        </div>

                                        <div>
                                            Highest Score: <span id="high"><?= $heighest_scr ?></span>
                                        </div>

                                        <div>
                                            Lowest Score: <span id="low"><?= $lowest_scr ?></span>
                                        </div>

                                        <div>
                                            <?php echo $error_link ?>
                                        </div>
                                    </div>

                                    <table class="table table-striped">
                                        <tr>
                                            <td><strong>S/N</strong></td>
                                            <td><strong>Matric</strong></td>
                                            <td><strong>Name</strong></td>
                                            <td align="center"><strong>CA</strong></td>
                                            <td align="center"><strong>Exam</strong></td>
                                            <td align="center"><strong>Total</strong></td>
                                            <td align="center"><strong>Remark</strong></td>
                                            <td align="center"></td>
                                        </tr>

                                        <?php
                                        if ($totalRows_rslt > 0) :
                                            $i = 1;
                                            while ($row_rslt = mysql_fetch_assoc($rslt)){
                                                ?>                
                                                <tr>
                                                    <td><?php echo $i++ ?></td>
                                                    <td class="matric" >
                                                        <a href="../student/profile.php?stid=<?php echo $row_rslt['stdid'] ?>">
                                                            <?php echo $row_rslt['stdid'] ?></a>
                                                    </td>
                                                    <td><?php echo $row_rslt['lname'] . ", " . $row_rslt['fname'] ?></td>
                                                    <td align="center" class="tscore">
                                                        <span><?php echo scoreValue($row_rslt['tscore']); ?></span>                        
                                                    </td>
                                                    <td align="center" class="escore">
                                                        <span><?php echo scoreValue($row_rslt['escore']); ?></span>                        
                                                    </td>
                                                    <td align="center">
                                                        <span class="totscore"><?php echo getScore($row_rslt['tscore'], $row_rslt['escore']); ?></span>
                                                    </td>
                                                    <td align="center" class="rem">
                                                        <?php echo getRemark(getScore($row_rslt['tscore'], $row_rslt['escore']), $row_grad['passmark']); ?>
                                                    </td>
                                                    <td align="center">
                                                        <?php
                                                        echo ($row_rslt['edited'] == 'TRUE') ?
                                                                "<a target='_blank' href='edithistory.php?stdid={$row_rslt['stdid']}&csid={$row_rslt['csid']}&sid={$row_rslt['sesid']}'>Edited</a>" : '';
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        else :
                                            ?>
                                            <tr>
                                                <td colspan="7" align="center">Result not yet uploaded!</td>
                                            </tr>
<?php endif; ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
            
            <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="modal hide fade" 
                 id="help-modal">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">X</button>
                    <h3 id="myModalLabel">Upload Procedure - Help</h3>
                </div>
                <div class="modal-body">
                    <h4>Step 1: Type Scores in Excel  </h4>
                    <img src="../images/upload-help/1score.jpg"/>
                    <br/>
                    <h4>Step 2: Choose Save As</h4>
                    <img src="../images/upload-help/2save-as.jpg"/>
                    <br/>
                    <h4>Step 3: Specify File Name</h4>
                    <img src="../images/upload-help/3file-name.jpg"/>
                    <br/>
                    <h4>Step 4: Save as CSV Comma Delimiter</h4>
                    <img src="../images/upload-help/4csv-comma-delimited.jpg"/>
                    <br/>
                    <h4>Step 5: Save on Your Computer</h4>
                    <img src="../images/upload-help/5final-save.jpg"/>
                    <br/>
                    <h4>Step 6: Open the TAMS Result Upload Page</h4>
                    <img src="../images/upload-help/6upload-page.jpg"/>
                    <br/>
                    <h4>Step 7: Choose Result File to Upload Result</h4>
                    <img src="../images/upload-help/7choose-file.jpg"/>
                    <br/>
                    <h4>Step 8: Specify Session, Course & click Upload</h4>
                    <img src="../images/upload-help/8choose-course-to-upload.jpg"/>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal">Close</button>
                </div>
            </div>
            
        </div>
    </body>
</html>