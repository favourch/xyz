<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once ('../phpexcel/PHPExcel/IOFactory.php');
require_once('../path.php');

$auth_users = "1,2,3,4,5,6";
check_auth($auth_users, $site_root);

$tot_pass           = 0;
$tot_fail           = 0;
$pcent1             = 0;
$pcent2             = 0;
$highest_scr        = '-';
$lowest_scr         = '-';
$scores             = [];
$results            = [];
$data               = "";
$insert_row         = 0;
$insert_error       = [];
$uploadstat         = NULL;
$type               = "error";

$redirect_url = $_SERVER['REQUEST_URI'];

if (isset($_POST['submit']) && $_POST['submit'] == "Upload Result") { //database query to upload result	
    $sesid = $_POST['sesid'];
    $csid = $_POST['csid'];
    $dpt = (isset($_POST['deptid'])) ? $_POST['deptid'] : getSessionValue('did');

    $allowed_type = ['text/comma-separated-values',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'application/octet-stream' //TODO remove, only used for testing
    ];

    if (is_uploaded_file($_FILES['filename']['tmp_name']) && in_array($_FILES['filename']['type'], $allowed_type)) {

        //Query for select boxes in the result view	
        $query_exist = sprintf("SELECT cr.resultid, cr.stdid "
                                . "FROM course_reg cr "
                                . "WHERE cr.sesid = %s "
                                . "AND cr.csid=%s ",
                                GetSQLValueString($sesid, "int"), 
                                GetSQLValueString($csid, "text"));
        $exist = mysql_query($query_exist, $tams) or die(mysql_error());
        $row_exist = mysql_fetch_assoc($exist);
        $totalRows_exist = mysql_num_rows($exist);
        $existing_courses = [];
        for ($idx = 0; $idx < $totalRows_exist; $idx++, $row_exist = mysql_fetch_assoc($exist)) {
            $existing_courses[$row_exist['stdid']] = $row_exist['resultid'];
        }

        $ids = [];
        $missing_entries = [];
        $insert_values = [];
        $upload_date = date('Y-m-d H:i:s');
        
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
                       
	    if($stdid == '')
	    	continue;
	    	
            if (array_key_exists($stdid, $existing_courses)) {
                $insert_values[] = sprintf("(%s, %s, %s, %s, %s, %s, %s)", 
                                            GetSQLValueString($stdid, "text"),  
                                            GetSQLValueString($sesid, "int"), 
                                            GetSQLValueString($csid, "text"),  
                                            GetSQLValueString($tscore, "int"), 
                                            GetSQLValueString($escore, "int"), 
                                            GetSQLValueString("upload", "text"), 
                                            GetSQLValueString($upload_date, "date"));
                
                $ids[] = $existing_courses[$stdid];
                $insert_row++;
            }else {
                $missing_entries[$stdid] = [$stdid, $tscore, $escore];
            }
        }
        
        $reg_entries = [];
                
        if(!empty($missing_entries)) {
            $stdids = array_keys($missing_entries);
            
            //Query to check school fees payment for unregistered students
            $query_exist = sprintf("SELECT s.matric_no "
                                    . "FROM schfee_transactions s "
                                    . "JOIN payschedule p ON s.scheduleid = p.scheduleid "
                                    . "WHERE s.status = 'APPROVED' "
                                    . "AND p.sesid = %s "
                                    . "AND s.matric_no IN ('%s') ",
                                    GetSQLValueString($sesid, "int"), 
                                    GetSQLValueString("stdids", "defined", implode("','", $stdids)));
            $exist = mysql_query($query_exist, $tams) or die(mysql_error());            
            $totalRows_exist = mysql_num_rows($exist);

            for(;$row_exist = mysql_fetch_assoc($exist);) {
                $stdid = $row_exist['matric_no'];
                $insert_values[] = sprintf("(%s, %s, %s, %s, %s, %s, %s)", 
                        GetSQLValueString($stdid, "text"), 
                        GetSQLValueString($sesid, "int"), 
                        GetSQLValueString($csid, "text"), 
                        GetSQLValueString($missing_entries[$stdid][1], "int"), 
                        GetSQLValueString($missing_entries[$stdid][2], "int"), 
                        GetSQLValueString("upload", "text"), 
                        GetSQLValueString($upload_date, "date"));

                $reg_entries[] = sprintf("(%s, %s, %s, 'TRUE')", 
                                        GetSQLValueString($stdid, "text"), 
                                        GetSQLValueString($sesid, "int"), 
                                        GetSQLValueString($csid, "text"));
                $ids[] = $stdid;
                $insert_row++;
                
                unset($missing_entries[$stdid]);
            }
            
            if(!empty($reg_entries)) {
                //Query to register paid students
                $query_reg = sprintf("REPLACE INTO `course_reg` (stdid, sesid, csid, approved) VALUES %s;", 
                                        GetSQLValueString('reg_entries', 'defined', implode(",", $reg_entries)));
                $reg = mysql_query($query_reg, $tams) or die(mysql_error());
                $totalRows_reg = mysql_num_rows($reg);
            }
        }
        
        unset($objIterator);

        if ($insert_row > 0) {
            $insert_query = sprintf("INSERT INTO `result` (stdid, sesid, csid, tscore, escore, upload_type, upload_date) "
                                    . "VALUES %s;", 
                    GetSQLValueString("insert_values", "defined", implode(',', $insert_values)));
            $rsupdate = mysql_query($insert_query, $tams);
        }

        if (!empty($missing_entries)) {
            $entry = [];
            foreach ($missing_entries as $stdid => $data) {
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
                    . " Their results could not be uploaded <a target='_blank' href='result_error.php?sid={$sesid}&csid={$csid}'>"
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

// Purge result	
if (isset($_POST['purge'])) {     
    $sesid = $_POST['sesid'];
    $csid = $_POST['csid'];
        
    $query_check = sprintf("SELECT * FROM teaching WHERE csid = %s AND sesid = %s AND lectid1 = %s", 
                    GetSQLValueString($csid, 'text'), 
                    GetSQLValueString($sesid, 'int'), 
                    GetSQLValueString(getSessionValue('uid'), 'text'));
    $check = mysql_query($query_check, $tams);
    $row_check = mysql_fetch_assoc($check);
    $totalRows_check = mysql_num_rows($check);
    
    if($totalRows_check > 0) {
        if($row_check['approve'] == 'no' || $row_check['accepted'] == 'no' || $row_check['accepted'] == 'no') {
            $sql = sprintf("DELETE FROM result "
                            . "WHERE csid = %s "
                            . "AND sesid = %s", 
                            GetSQLValueString($csid, 'text'), 
                            GetSQLValueString($sesid, 'int'));
            $result = mysql_query($sql, $tams);
	    $error = mysql_errno();
	    
	    $sql = sprintf("DELETE FROM result_error "
                            . "WHERE csid = %s "
                            . "AND sesid = %s", 
                            GetSQLValueString($csid, 'text'), 
                            GetSQLValueString($sesid, 'int'));
            $result = mysql_query($sql, $tams);
            
            $msg = "The result for %s could not be removed from the database! Please try again or contact the system administrator.";
            $type = 'error';

            if($error == 0 && mysql_errno() == 0) {
                $sql = sprintf("UPDATE teaching SET upload = 'no' WHERE csid = %s AND sesid = %s", 
                            GetSQLValueString($csid, 'text'), 
                            GetSQLValueString($sesid, 'int'));
                $result = mysql_query($sql, $tams);

                if(mysql_errno() == 0) {
                    $msg = "The previously uploaded result for %s has been completely removed from the database!";
                    $type = 'success';
                }
            }
        }else {
            $msg = "The result(%s) you are trying to purge, has already been approved at the department/college!";
            $type = 'error';
        }
    }else {
        $msg = "The result(%s) you are trying to purge, has not been assigned to you!";
        $type = 'error';
    }
    
    $notification->set_Notification(sprintf($msg, GetSQLValueString($csid, "text")), $type);
    $_POST = [];
}

// Edit result	
if (isset($_POST['update'])) { 
    $sesid = $_POST['sesid'];
    $csid = $_POST['csid'];
    
    $query_check = sprintf("SELECT * FROM teaching WHERE csid = %s AND sesid = %s AND lectid1 = %s", 
                    GetSQLValueString($csid, 'text'), 
                    GetSQLValueString($sesid, 'int'), 
                    GetSQLValueString(getSessionValue('uid'), 'text'));
    $check = mysql_query($query_check, $tams);
    $row_check = mysql_fetch_assoc($check);
    $totalRows_check = mysql_num_rows($check);
    
    if($totalRows_check > 0) {
        if($row_check['approve'] == 'no' || $row_check['accepted'] == 'no' || $row_check['released'] == 'no') {
            
            $msg = "The result for %s could not be updated in the database! Please try again or contact the system administrator.";
            $type = 'error';
            
            // Process deleted entries
            if(!empty($_POST['deletes'])) {
                mysql_query('START TRANSACTION;');
                $query_deletes = sprintf("SELECT * "
                                        . "FROM result "
                                        . "WHERE resultid IN(%s)", 
                                        GetSQLValueString("delete_ids", "defined", implode(',', $_POST['deletes'])));
                $deletes = mysql_query($query_deletes, $tams);
                $row_deletes = mysql_fetch_assoc($deletes);
                $totalRows_deletes = mysql_num_rows($deletes);
                
                $insert_entries = [];
                for(; $row_deletes; $row_deletes = mysql_fetch_assoc($deletes)) {
                    $insert_entries[] = sprintf("(%s,%s,%s,%s,%s,%s,%s,%s)",
                                            GetSQLValueString($row_deletes['stdid'], "text"), 
                                            GetSQLValueString($row_deletes['csid'], "text"), 
                                            GetSQLValueString($row_deletes['sesid'], "int"), 
                                            GetSQLValueString(getSessionValue("uid"), "text"), 
                                            GetSQLValueString($row_deletes['tscore'], "int"), 
                                            GetSQLValueString($row_deletes['escore'], "int"), 
                                            GetSQLValueString(date('Y-m-d H:i:s'), "date"), 
                                            GetSQLValueString("delete", "text"));
                }
                
                $delete_query = sprintf("DELETE FROM `result` WHERE resultid IN (%s);", 
                        GetSQLValueString("delete_ids", "defined", implode(',', $_POST['deletes'])));                
                $rsdelete = mysql_query($delete_query, $tams);
                
                $insert_query = sprintf("INSERT INTO `result_log` (stdid, csid, sesid, lectid, old_test, old_exam, "
                        . "date, type) VALUES %s;", 
                        GetSQLValueString("insert_entries", "defined", implode(',', $insert_entries))); 
                $rsinsert = mysql_query($insert_query, $tams);
                
                if($rsdelete && $rsinsert) {
                    mysql_query('COMMIT;');
                    $msg = "The result for %s was successfully updated!";
                    $type = 'success';
                }else {
                    mysql_query('ROLLBACK;');
                }
            }
            
            $ids = [];
            $update_columns = ['tscore' => '`tscore` = CASE ', 'escore' => '`escore` = CASE ', 'edited' => "edited = 'TRUE'"];
            $test_present = false;
            $exam_present = false;
            
            foreach($_POST['edits'] as $id => $result) {
                // Update entry for tscore
                if(isset($result['tscore'])) {
                    $test_present = true;
                    $update_columns['tscore'] .= sprintf("WHEN `resultid` = %s THEN %s ", 
                                                        GetSQLValueString($id, "int"), 
                                                        GetSQLValueString($result['tscore'], "int"));
                }
                
                // Update entry for escore
                if(isset($result['escore'])) {
                    $exam_present = true;
                    $update_columns['escore'] .= sprintf("WHEN `resultid` = %s THEN %s ", 
                                                    GetSQLValueString($id, "int"), 
                                                    GetSQLValueString($result['escore'], "int"));
                }
                
                $ids[] = $id;
            }
            
            $query_update = sprintf("SELECT * "
                                    . "FROM result "
                                    . "WHERE resultid IN(%s)", 
                    GetSQLValueString("update_ids", "defined", implode(',', $ids)));
            $update = mysql_query($query_update, $tams);
            $row_update = mysql_fetch_assoc($update);
            $totalRows_update = mysql_num_rows($update);
                
            if(!empty($ids)) {
                $insert_entries = [];
                $edits = $_POST['edits'];
                
                for (; $row_update; $row_update = mysql_fetch_assoc($update)) {
                    $resultid = $row_update['resultid'];
                    $tscore = isset($edits[$resultid]['tscore'])? $edits[$resultid]['tscore']: $row_update['tscore'];
                    $escore = isset($edits[$resultid]['escore'])? $edits[$resultid]['escore']: $row_update['escore'];

                    $insert_entries[] = sprintf("(%s,%s,%s,%s,%s,%s,%s,%s,%s)", 
                                                GetSQLValueString($row_update['stdid'], "text"), 
                                                GetSQLValueString($row_update['csid'], "text"), 
                                                GetSQLValueString($row_update['sesid'], "int"), 
                                                GetSQLValueString(getSessionValue("uid"), "text"), 
                                                GetSQLValueString($row_update['tscore'], "int"), 
                                                GetSQLValueString($row_update['escore'], "int"), 
                                                GetSQLValueString($tscore, "int"), 
                                                GetSQLValueString($escore, "int"), 
                                                GetSQLValueString(date('Y-m-d H:i:s'), "date"));
                }
                
                $update_columns['tscore'] .= 'END';
                $update_columns['escore'] .= 'END';
                
                if(!$test_present)
                    unset($update_columns['tscore']);
                
                if(!$exam_present)
                    unset($update_columns['escore']);
                
                $where = sprintf(" WHERE `resultid` IN (%s)", 
                        GetSQLValueString("ids", "defined", implode(',', $ids)));

                $update_query = sprintf("UPDATE `result` SET %s %s", 
                        GetSQLValueString("update_columns", "defined", implode(',', $update_columns)), 
                        GetSQLValueString($where, "defined", $where));
                $rsupdate = mysql_query($update_query, $tams);                
                
                $insert_query = sprintf("INSERT INTO `result_log` (stdid, csid, sesid, lectid, old_test, old_exam, "
                        . "new_test, new_exam, date) VALUES %s;", 
                        GetSQLValueString("insert_entries", "defined", implode(',', $insert_entries)));
                $rsinsert = mysql_query($insert_query, $tams);
                
            }            
            
            if($rsupdate && $rsinsert) {
                $msg = "The previously uploaded result for %s was successfully updated!";
                $type = 'success';
            }
            
        }else {
            $msg = "The result(%s) you are trying to edit, has already been approved at the department/college!";
            $type = 'error';
        }
    }else {
        $msg = "The result(%s) you are trying to edit, has not been assigned to you!";
        $type = 'error';
    }
    
    $notification->set_Notification(sprintf($msg, GetSQLValueString($csid, "text")), $type);
    
    $_POST = [];
}

// Add result entries	
if (isset($_POST['newentries'])) { 
    $sesid = $_POST['sesid'];
    $csid = $_POST['csid'];
    
    $query_check = sprintf("SELECT * FROM teaching WHERE csid = %s AND sesid = %s AND lectid1 = %s", 
                    GetSQLValueString($csid, 'text'), 
                    GetSQLValueString($sesid, 'int'), 
                    GetSQLValueString(getSessionValue('uid'), 'text'));
    $check = mysql_query($query_check, $tams);
    $row_check = mysql_fetch_assoc($check);
    $totalRows_check = mysql_num_rows($check);
    
    if($totalRows_check > 0) {
        if($row_check['approve'] == 'no' || $row_check['accepted'] == 'no' || $row_check['released'] == 'no') {
            
            $msg = "The result for %s could not be updated in the database! "
                    . "Please try again or contact the system administrator.";
            $type = 'error';
            
            // Process new entries
            if(!empty($_POST['entries'])) {
                
                $insert_entries = [];
                foreach($_POST['entries'] as $stdid => $scores) {
                    $insert_entries[] = sprintf("(%s,%s,%s,%s,%s,%s,%s)",
                                            GetSQLValueString($stdid, "text"), 
                                            GetSQLValueString($sesid, "text"), 
                                            GetSQLValueString($csid, "text"), 
                                            GetSQLValueString($scores['tscore'], "int"), 
                                            GetSQLValueString($scores['escore'], "int"), 
                                            GetSQLValueString(date('Y-m-d H:i:s'), "date"), 
                                            GetSQLValueString("add", "text"));
                }
                
                $insert_query = sprintf("INSERT INTO result (stdid, sesid, csid, tscore, escore, upload_date, upload_type) "
                        . "VALUES %s;", 
                        GetSQLValueString("insert_entries", "defined", implode(',', $insert_entries)));   
                $rsinsert = mysql_query($insert_query, $tams);
                
//                $insert_query = sprintf("INSERT INTO `result_log` (stdid, csid, sesid, lectid, _test, old_exam, "
//                        . "date, type) VALUES %s;", 
//                        GetSQLValueString("insert_entries", "defined", implode(',', $insert_entries))); 
//                $rsinsert = mysql_query($insert_query, $tams);
                if ($rsinsert) {
                    $msg = "The previously uploaded result for %s has been successfully added to!";
                    $type = 'success';
                }
            }  
        }else {
            $msg = "The result(%s) you are trying to add to, has already been approved at the department/college!";
            $type = 'error';
        }
    }else {
        $msg = "The result(%s) you are trying to add to, has not been assigned to you!";
        $type = 'error';
    }
    
    $notification->set_Notification(sprintf($msg, GetSQLValueString($csid, "text")), $type);
    
    $_POST = [];
}

$query_sess = "SELECT * FROM `session` WHERE listing = 'TRUE' ORDER BY sesname DESC LIMIT 0,2";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$colname_rslt = "-1";
if (isset($_GET['csid'])) {
  $colname_rslt = $_GET['csid'];
}

$colname1_rslt = "-1";
if (isset($_GET['sid'])) {
    $colname1_rslt = $_GET['sid'];
}else {
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
$and = '';
if (isset($_GET['crs'])) {
    $and = sprintf("AND c.csid = %s ",GetSQLValueString($_GET['crs'], 'text'));
}

$queryCsType = sprintf("SELECT * FROM course c, department d, college cl "
                    .  "WHERE c.deptid = d.deptid "
                    .  "AND d.colid = cl.colid %s ", $and );
$crsType = mysql_query($queryCsType, $tams) or die(mysql_error());
$row_crsType = mysql_fetch_assoc($crsType);
$totalRows_crsType = mysql_num_rows($crsType);
    
$filt = '';
switch (strtolower($row_crsType['type'])) {
    case 'college':       
        $filt = sprintf(" AND c.colid = %s ", GetSQLValueString(getSessionValue('cid'), "text"));
        break;
    case 'departmental':
         $filt = sprintf(" AND d.deptid = %s ", GetSQLValueString(getSessionValue('did'), "text"));        
        break;
    case 'general':        
        $filt = "";
        break;
    default:
        break;
}

$query_prog = sprintf("SELECT progid, progname "
                    . "FROM programme p ");
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

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

$query_crs = sprintf("SELECT distinct (csid) "
                    . "FROM teaching "
                    . "WHERE upload != 'yes' "
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

$query_crsinfo = sprintf("SELECT * "
                        . "FROM teaching t "
                        . "WHERE t.sesid=%s "
                        . "AND t.csid=%s ", 
                        GetSQLValueString($colname_sescrs, "int"), 
                        GetSQLValueString($colname1_sescrs, "text"));
$crsinfo = mysql_query($query_crsinfo, $tams) or die(mysql_error());
$row_crsinfo = mysql_fetch_assoc($crsinfo);
$totalRows_crsinfo = mysql_num_rows($crsinfo);

$accepted = (strtolower($row_crsinfo['accepted']) == 'yes') ? true : false;

$query_sescrs = sprintf("SELECT distinct(t.csid) "
                        . "FROM teaching t "
                        . "WHERE t.sesid=%s "
                        . "AND t.lectid1=%s "
                        . "AND upload = 'yes' "
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

$query_rslt = sprintf("SELECT distinct(r.stdid), r.resultid, r.csid, r.edited, r.sesid, tscore, escore, s.fname, s.lname "
                    . "FROM result r, student s, programme p, teaching t "
                    . "WHERE r.stdid    = s.stdid "
                    . "AND r.csid       = t.csid "
                    . "AND t.upload     = 'yes' "
                    . "AND t.lectid1    = %s "
                    . "AND r.csid       = %s "
                    . "AND r.sesid      = t.sesid "
                    . "AND r.sesid      = %s "
                    . "AND s.progid     = p.progid ", 
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
        "<a class='btn btn-primary pull-left' target='_blank' href='result_error.php?sid={$colname_sescrs}&csid={$colname1_sescrs}'>View result errors</a>" : '';

//Query for select boxes in the result view//
$sname = "";
do {
    if ($colname2_crs == $row_sess['sesid']) {
        $sname = $row_sess['sesname'];
    }
} while ($row_sess = mysql_fetch_assoc($sess));

$query_grad = sprintf("SELECT * FROM grading g, session s WHERE g.sesid = %s AND g.colid = %s",
                GetSQLValueString($colname_sescrs, "int"),
                GetSQLValueString($row_crsType['colid'], "int"));
$grad = mysql_query($query_grad, $tams) or die(mysql_error());
$row_grad = mysql_fetch_assoc($grad);
$totalRows_grad = mysql_num_rows($grad);

if($totalRows_rslt > 0){
    for (; $row_rslt = mysql_fetch_assoc($rslt);) {
        
        $tot_scr = $row_rslt['tscore'] + $row_rslt['escore'];
        if ($tot_scr >= $row_grad['passmark']) {
            $tot_pass = $tot_pass + 1;
            $pcent1 = $tot_pass * 100 / $totalRows_rslt;
        }else {
            $tot_fail = $tot_fail + 1;
            $pcent2 = $tot_fail * 100 / $totalRows_rslt;
        }
        
        array_push($scores, $tot_scr);
        array_push($results, $row_rslt);
    }
    
    $data = json_encode($results);
    
    $highest_scr = max($scores);
    $lowest_scr = min($scores);
    $scores = [];
}

$query_crslevel = sprintf("SELECT c.level, c.type, dc.level as level1 "
                            . "FROM course c "
                            . "LEFT JOIN department_course dc ON c.csid = dc.csid "
                            . "AND dc.deptid = %s "
                            . "WHERE c.csid = %s", 
                            GetSQLValueString(getSessionValue('did'), "int"), 
                            GetSQLValueString($colname1_sescrs, "text"));
$crslevel = mysql_query($query_crslevel, $tams) or die(mysql_error());
$row_crslevel = mysql_fetch_assoc($crslevel);
$totalRows_crslevel = mysql_num_rows($crslevel);

$level = isset($row_crslevel['level1'])? $row_crslevel['level1']: $row_crslevel['level'];
$levels = [0];
if($level) {
    $levels = [];
    for (; $level <= 4; $level++) {
        $levels[] = $level;
    }
}

$query_suggestion = sprintf("SELECT stdid, fname, lname, mname "
        . "FROM student s "
        . "JOIN programme p ON s.progid = p.progid "
        . "JOIN department d ON d.deptid = p.deptid "
        . "WHERE d.deptid = %s AND s.level IN (%s)", 
        GetSQLValueString(getSessionValue('did'), "int"), 
        GetSQLValueString("levels", "defined", implode(',', $levels)));
$suggestion = mysql_query($query_suggestion, $tams) or die(mysql_error());
$row_suggestion = mysql_fetch_assoc($suggestion);
$totalRows_suggestion = mysql_num_rows($suggestion);

$initialSug = [];
for ($idx = 0; $totalRows_suggestion > $idx; $idx++, $row_suggestion = mysql_fetch_assoc($suggestion)) {
    $initialSug[] = $row_suggestion;
}
?>
<!doctype html>
<html ng-app="TamsApp">
    <?php include INCPATH."/header.php" ?>  
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="PageCtrl">
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
                                    
                                        <form name="form1" method="post" enctype="multipart/form-data" action="">
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
                                                    <?php if ($totalRows_crs > 0) { 
                                                    // Show if recordset not empty
                                                    ?>
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
                         
                         
                            <div class="box box-bordered box-color" class="ng-cloak">
                                 <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Uploaded Result 
                                    </h3>
                                </div>

                                <div class="box-content">
                                    <div class="row-fluid">
                                        <form class="form-vertical" method="POST" action="printresult2.php" target="_blank">   
                                            
                                            <div class="span3">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">Session</label>
                                                    <div class="controls controls-row">
                                                        <select name="sesid" id="ssid" onchange="ssesfilt(this)">
                                                            <?php  for(;$row_ses; $row_ses = mysql_fetch_assoc($ses)) { ?>
                                                                <option value="<?php echo $row_ses['sesid'] ?>"
                                                                <?php
                                                                if (!(strcmp($row_ses['sesid'], $colname_sescrs))) {
                                                                    echo "selected=\"selected\"";
                                                                }
                                                                ?>>
                                                                    <?php echo $row_ses['sesname'] ?></option>
                                                            <?php }?>

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
                                                            <?php for(;$row_sescrs; $row_sescrs = mysql_fetch_assoc($sescrs)) { ?>
                                                                <option value="<?php echo $row_sescrs['csid'] ?>"
                                                                <?php
                                                                if (!(strcmp($row_sescrs['csid'], $colname1_sescrs))) {
                                                                    echo "selected=\"selected\"";
                                                                }
                                                                ?>>
                                                                    <?php echo $row_sescrs['csid'] ?></option>

                                                            <?php }?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="span3">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield"><?= $programme_name ?></label>
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
                                                                } while ($row_prog = mysql_fetch_assoc($prog));
                                                                ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="span2">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">&nbsp;</label>
                                                    <div class="controls controls-row">
                                                        <button type="submit" name="submit" class="btn btn-small btn-blue">Print Result</button> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        <a href="statistics/index.php?crsid=<?= $colname1_sescrs?>&sesid=<?= $colname_sescrs?>" target="Tabs" class="btn btn-primary btn-small">View Result Statistics</a>                                                       
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="MM_Insert" value="form1">
                                        </form>                                    
                                    </div>
                                                                     
                                    <div class="row-fluid">
                                        <div>Total no. of Students:
                                            <span id="total"><?php echo $totalRows_rslt ?></span> (100%)
                                        </div>

                                        <div class="pull-left">
                                            <p>No. Passed: <span id="pass"><?= $tot_pass ." (".number_format($pcent1)."%)" ?></span></p>                                        
                                            <p>No. Failed: <span id="fail"><?= $tot_fail ." (".number_format($pcent2)."%)" ?></span></p>
                                        </div>

                                        <div class="pull-left" style="margin-left: 30px;">
                                            <p>Highest Score: <span id="high"><?= $highest_scr ?></span></p>
                                            <p>Lowest Score: <span id="low"><?= $lowest_scr ?></span></p>
                                        </div>
                                    </div>
                                    
                                    <?php if(!$accepted && 1 > 2) {?>
                                    <div class="row-fluid">
                                        <div>
                                            <?php echo $error_link ?>
                                            <form ng-submit="purgeResult($event)" action="" method="post" class="pull-left" style="margin-left: 20px; margin-right: 20px">
                                                <button type="submit" name="purge" class="btn btn-primary">Purge Result</button>
                                                <input type="hidden" name="sesid" value="<?php echo $colname_sescrs?>"/>
                                                <input type="hidden" name="csid" value="<?php echo $colname1_sescrs?>"/>
                                            </form>
                                            
                                            <button type="submit" data-toggle="modal" href="#new-entry-modal" name="purge" class="btn btn-primary pull-left">Add New Entry</button>
                                            
                                            <form action="" method="post" class='pull-right'>
                                                <input type="submit" class="btn btn-primary" name="update" value="Update Result" ng-show="editDone || deletes.length > 0"/>
                                                <input type="hidden" name="sesid" value="<?php echo $colname_sescrs ?>"/>
                                                <input type="hidden" name="csid" value="<?php echo $colname1_sescrs ?>"/>
                                                <input type="hidden" ng-repeat="(k,e) in edits" name="edits[{{k}}][tscore]" value="{{e.tscore}}" ng-if="e.tscore != null && e.tscore != ''"/>
                                                <input type="hidden" ng-repeat="(k,e) in edits" name="edits[{{k}}][escore]" value="{{e.escore}}" ng-if="e.escore != null && e.escore != ''"/>
                                                <input type="hidden" ng-repeat="d in deletes" name="deletes[]" value="{{d}}"/>
                                            </form>
                                        </div>
                                    </div>
                                    <?php }?>
                                    
                                    <table class="table table-striped table-condensed table-hover">
                                        <thead>
                                            <tr>
                                                <th><strong>S/N</strong></th>
                                                <th><strong>Matric</strong></th>
                                                <th><strong>Name</strong></th>
                                                <th align="center"><strong>CA</strong></th>
                                                <th align="center"><strong>Exam</strong></th>
                                                <th align="center"><strong>Total</strong></th>
                                                <th align="center"><strong>Remark</strong></th>
                                                <th align="center"></th>
                                                <th align="center">
                                                    <?php if(1>2){?>
                                                    <button ng-click="editResult()" ng-bind="editEnabled? 'Done': 'Edit'" class="btn btn-small btn-primary"></button>
                                                    <?php } ?>
                                                </th>
                                                <th align="center">Reset</th>
                                                <th align="center">Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr ng-repeat="rs in results" ng-if="!rs.deleted">
                                                <td>{{$index + 1}}</td>
                                                <td class="matric" >
                                                    <a href="../student/profile.php?stid={{rs.stdid}}">{{rs.stdid}}</a>
                                                </td>
                                                <td>{{rs.lname}} &nbsp; {{rs.fname}} &nbsp; {{rs.mname}}</td>
                                                <td align="center" class="tscore" width="40">
                                                    <input class="input-mini" type="text" maxlength="2" ng-model="rs.tscore" ng-show="rs.edit"/>
                                                    <span ng-bind="scoreValue(rs.tscore)" ng-show="!rs.edit"></span>
                                                </td>
                                                <td align="center" class="escore" width="40">
                                                    <input class="input-mini" type="text" maxlength="2" ng-model="rs.escore" ng-show="rs.edit"/>
                                                    <span ng-bind="scoreValue(rs.escore)" ng-show="!rs.edit"></span>
                                                </td>
                                                <td align="center">
                                                    <span class="totscore" ng-bind="getScore(rs.tscore, rs.escore)"></span>
                                                </td>
                                                <td align="center" class="rem" ng-bind="getRemark(getScore(rs.tscore, rs.escore), grade_pass)"></td>
                                                <td align="center">
                                                    <a ng-if="rs.edited == 'TRUE'" target='_blank' href='edithistory.php?stdid={{rs.stdid}}&csid={{rs.csid}}&sid={{rs.sesid}}'>Edited</a>
                                                </td>
                                                <td align="center">
                                                    <button ng-click="editEntry($index)" ng-show="editEnabled" ng-bind="editText($index)"></button>
                                                </td>
                                                <td align="center">
                                                    <a title="Reset the score to it's initial value." ng-show="editEnabled" ng-click="reset($index)" href="" style="margin-right: 15px; margin-left: 15px">
                                                        <i class="fa fa-refresh"></i>
                                                    </a>    
                                                </td>
                                                <td align="center">
                                                    <a title="Delete the result entry." ng-show="editEnabled" ng-click="delete($index)" href="">
                                                        <i class="fa fa-trash-o"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr ng-show="results.length < 1">
                                                <td colspan="11"><div class="alert alert-error">No record found !</div></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <?php if(!$accepted) {?>
                                    <form action="" method="post">
                                        <input type="submit" class="btn btn-primary" name="update" value="Update Result" ng-show="editDone || deletes.length > 0"/>
                                        <input type="hidden" name="sesid" value="<?php echo $colname_sescrs ?>"/>
                                        <input type="hidden" name="csid" value="<?php echo $colname1_sescrs ?>"/>
                                        <input type="hidden" ng-repeat="(k,e) in edits" name="edits[{{k}}][tscore]" value="{{e.tscore}}" ng-if="e.tscore != null && e.tscore != ''"/>
                                        <input type="hidden" ng-repeat="(k,e) in edits" name="edits[{{k}}][escore]" value="{{e.escore}}" ng-if="e.escore != null && e.escore != ''"/>
                                        <input type="hidden" ng-repeat="d in deletes" name="deletes[]" value="{{d}}"/>
                                    </form>
                                    <?php }?>
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
            
            <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="modal hide fade" 
                 id="new-entry-modal">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">X</button>
                    <h3 id="myModalLabel">Add New Result Entry</h3>
                </div>
                <div class="modal-body" style="min-height: 400px">
                    <div>
	                 <input type="text" ng-model="student" size="70" placeholder="Enter student matric number!" class="pull-left"/>
	                 <button ng-click="searchStudent()" class="btn btn-primary pull-left" style="margin-left: 20px;">Search</button>
	            </div>
                    <form action="" method="post">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <td>Matric No.</td>
                                    <td>Name</td>
                                    <td>Test Score</td>
                                    <td>Exam Score</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="(k,v) in newEntries">
                                    <td>{{k}}</td>
                                    <td>{{v.lname}}, {{v.fname}}</td>
                                    <td><input type="text" class="input-mini" name="entries[{{k}}][tscore]" ng-model="v.tscore"/></td>
                                    <td><input type="text" class="input-mini" name="entries[{{k}}][escore]" ng-model="v.escore"/></td>   
                                    <td>
                                        <a title="Delete the result entry." ng-click="removeEntry(k)" href="">
                                            <i class="fa fa-trash-o"></i>
                                        </a>                                        
                                    </td>
                                </tr>
                            </tbody>
                                  
                        </table>
                        <input type="submit" name="newentries" value="Add Result Entries" ng-show="showAddButton()" class="btn btn-primary"/>
                        <input type="hidden" name="sesid" value="<?php echo $colname_sescrs ?>"/>
                        <input type="hidden" name="csid" value="<?php echo $colname1_sescrs ?>"/>
                    </form>  
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </body>
    <script>
        var app = angular.module('TamsApp', []);
        
        app.controller('PageCtrl', function($scope, $interpolate, $http){
	    var sesid = '<?php echo $colname1_rslt ?>';
            var csid = '<?php echo $colname_rslt ?>';

            $scope.results      = <?php echo json_encode($results) ?>;
            $scope.grade_pass   = <?php echo isset($row_grad['passmark']) ? $row_grad['passmark'] : 45 ?>;
            $scope.editEnabled = false;
            $scope.edits = {};
            $scope.deletes = [];
            $scope.newEntries = {};
            $scope.editDone = false;
            $scope.student = null;
                
            $scope.removeEntry = function(idx) {
                var entry = $scope.newEntries[idx];
                entry.selected = false;
                delete $scope.newEntries[idx];
            };
                                                
            $scope.getRemark = function(score, grade){
                if(score == "-") {
                    return "-";
                }

                var gradePs = (score >= grade) ? "P" : "F";
                return gradePs;
            };

            $scope.scoreValue = function(scoreValue) {
                var value;
                value = (scoreValue == "" || scoreValue == null) ? "-" : scoreValue;
                return value;
            };
                                    
            $scope.getScore = function(test, exam ) {                                     
                if(test == "" && exam == "") {
                    return "-";
                }

                if(test == "") {
                    return Number(exam);
                }

                if(exam == "") {
                    return Number(test);
                }

                return  Number(test) + Number(exam);
            };

            $scope.editResult = function() {
                $scope.editEnabled = !$scope.editEnabled;
            };
            
            $scope.editEntry = function(idx) {
                var entry = $scope.results[idx];
                entry.edit = !entry.edit;
                                    
                if(!entry.edit) {                    
                    if(!$scope.edits[entry.resultid]) {
                        $scope.edits[entry.resultid] = {};
                    }
                    
                    if(entry.tscore != entry.init_score.tscore) {                        
                        $scope.edits[entry.resultid].tscore = entry.tscore;
                    }else {
                        delete $scope.edits[''+entry.resultid].tscore;
                    }
                    
                    if(entry.escore != entry.init_score.escore) {
                        $scope.edits[entry.resultid].escore = entry.escore;
                    }else {
                        delete $scope.edits[entry.resultid].escore;
                    }
                    
                    if(entry.tscore == entry.init_score.tscore && entry.escore == entry.init_score.escore)
                        delete $scope.edits[entry.resultid];
                                        
                    if(Object.keys($scope.edits).length > 0) {
                        $scope.editDone = true;
                    }else {
                        $scope.editDone = false;
                    }
                }
                
            };
            
            $scope.reset = function(idx) {
                var entry = $scope.results[idx];
                if(entry.init_score) {
                    entry.tscore = entry.init_score.tscore;
                    entry.escore = entry.init_score.escore;
                }
                
                if($scope.edits[entry.resultid]) {
                    delete $scope.edits[entry.resultid];                    
                }
                
                if(Object.keys($scope.edits).length > 0) {
                    $scope.editDone = true;
                }else {
                    $scope.editDone = false;
                }
            };
            
            $scope.delete = function(idx) {
                var entry = $scope.results[idx];                
                $scope.deletes.push(entry.resultid);
                $scope.results.splice(idx, 1);
            };
            
            $scope.purgeResult = function(e) {
                if(!confirm("Are you sure you want to completely remove the uploaded results?")) {
                    e.preventDefault();
                }
            };
            
            $scope.editText = function(idx) {
                var entry = $scope.results[idx]; 
                
                if(!entry.init_score)
                    entry.init_score = {
                        tscore: entry.tscore,
                        escore: entry.escore
                    };
                          
                if((entry.init_score.tscore != entry.tscore || entry.init_score.escore != entry.escore) && !entry.edit) {
                    return 'Pending';
                }
                
                return entry.edit? 'Done': 'Edit';
            };
            
            $scope.showAddButton = function() {
                return Object.keys($scope.newEntries).length > 0? true: false;
            }
            
            $scope.searchStudent = function() {
                if(!$scope.student || $scope.student.length < 11)
                    alert("Please enter a valid matric number!");
                
                $http.get('suggestions.php?q='+$scope.student+'&c='+csid+'&s='+sesid).then(function(response) {
                    var data = response.data;
                    if(data.stdid) {
                        $scope.newEntries[data.stdid] = data;
                    }else {
                        alert("No student found for the specified matric number or student already in the uploaded result!")
                    }
                }, function(response) {
                    alert("No student found for the specified matric number or student already in the uploaded result!")
                });
            }
        });
    </script>
</html>