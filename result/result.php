<?php
if (!isset($_SESSION)) {
    session_start();
}


require_once('../path.php');

$auth_users = "1,2,3";
check_auth($auth_users, $site_root);


$insert_row = 0;
$insert_error = array();
$uploadstat = "";

if( isset($_POST) ){ //database query for approve result
	
	$sesid = $_GET['sid'];
	$csid = $_GET['csid'];
	$deptid = $_GET['did'];
	
	if(isset($_POST['approve']) ){ //database query for approve result
		
		mysql_query("BEGIN");	
                
                $update_query = sprintf("UPDATE result SET approve = %s WHERE csid = %s AND sesid = %s",
                                           GetSQLValueString('TRUE', "text"),
                                           GetSQLValueString($csid, "text"),
                                           GetSQLValueString($sesid, "int"));

                $rsupdate = mysql_query($update_query);
                $update = ( mysql_affected_rows($tams) != -1 )? true: false;	  
                
                $updateSQL = sprintf("UPDATE teaching SET approve=%s WHERE deptid = %s AND csid = %s AND sesid = %s",
                                           GetSQLValueString("Yes", "text"),
                                           GetSQLValueString($deptid, "int"),
                                           GetSQLValueString($csid, "text"),
                                           GetSQLValueString($sesid, "int"));

                
                $result = mysql_query($updateSQL, $tams) or die(mysql_error());
                $uploadstat = "Approval Successful!";
                
		if($result){
                    mysql_query("COMMIT", $tams);
		}else{	
                    mysql_query("ROLLBACK", $tams);
                }               
                
	}elseif(isset($_POST['disapprove'])){ 

                //database query for disapprove result		
		$deleteSQL = sprintf("DELETE FROM result, result_error "
		                        ." USING result LEFT JOIN result_error ON result.csid = result_error.csid AND result.sesid = result_error.sesid  "
                                . "WHERE result.csid = %s "
                                . "AND result.sesid = %s", 
                                GetSQLValueString($csid, 'text'), 
                                GetSQLValueString($sesid, 'int'));
        $Result1 = mysql_query($deleteSQL, $tams) or die(mysql_error());
		
		$filter = ", accepted = ".GetSQLValueString("No", "text");
		if(getAccess() == 2) {
		     $filter .= ", approve = ".GetSQLValueString("No", "text");
		     $filter .= ", released = ".GetSQLValueString("No", "text");
		}
		
		$updateSQL = sprintf("UPDATE teaching SET upload=%s, approve=%s, accepted=%s, released=%s %s WHERE deptid = %s AND csid = %s AND sesid = %s",
						   GetSQLValueString("No", "text"),
						   GetSQLValueString("No", "text"),
						   GetSQLValueString("No", "text"),
						   GetSQLValueString("No", "text"),
						   GetSQLValueString("filter", "defined", $filter),
						   GetSQLValueString($deptid, "int"),
						   GetSQLValueString($csid, "text"),
						   GetSQLValueString($sesid, "int"));		
		$Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
		
	}elseif( isset($_POST['save']) ){ //database query for result modification
		
		for($i = 0; $i<count($_POST['matric']); $i++ ){
			
			$stdid = $_POST['matric'][$i];
			$tscore = $_POST['tedit'][$i];
			$escore = $_POST['eedit'][$i];
			
                        $query_edit = sprintf("SELECT * FROM result WHERE stdid=%s AND csid=%s AND sesid=%s",
						   GetSQLValueString($stdid, "text"),
						   GetSQLValueString($csid, "text"),
						   GetSQLValueString($sesid, "int"));

			$edit = mysql_query($query_edit, $tams) or die(mysql_error());
                        $row_edit = mysql_fetch_assoc($edit);
                        $totalRows_edit = mysql_num_rows($edit);                        
                        
                        $insertSQL = sprintf("INSERT INTO result_log (new_test, new_exam, old_test, old_exam, date, stdid, csid, sesid, lectid) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
						   GetSQLValueString($tscore, "int"),
						   GetSQLValueString($escore, "int"),
                                                   GetSQLValueString($row_edit['tscore'], "int"),
						   GetSQLValueString($row_edit['escore'], "int"),
						   GetSQLValueString(date('Y-m-d H:i:s'), "date"),
						   GetSQLValueString($stdid, "text"),
						   GetSQLValueString($csid, "text"),
						   GetSQLValueString($sesid, "int"),
						   GetSQLValueString(getSessionValue('lid'), "text"));

			
			$Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());
                        
			$updateSQL = sprintf("UPDATE result SET tscore=%s, escore=%s, edited='TRUE' WHERE stdid = %s AND csid = %s AND sesid = %s",
						   GetSQLValueString($tscore, "int"),
						   GetSQLValueString($escore, "int"),
						   GetSQLValueString($stdid, "text"),
						   GetSQLValueString($csid, "text"),
						   GetSQLValueString($sesid, "int"));
			$Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
                        
		}
	}elseif( isset($_POST['accept']) ){ //database query for result modification
            //database query to accept result		
            $acceptSQL = sprintf("UPDATE teaching SET accepted = 'yes' WHERE csid=%s AND sesid=%s", 
                    GetSQLValueString($csid, "text"), 
                    GetSQLValueString($sesid, "int"));


            $Result1 = mysql_query($acceptSQL, $tams) or die(mysql_error());
        }
	
}

// Edit result	
if (isset($_POST['update'])) { 
    $sesid = $_POST['sesid'];
    $csid = $_POST['csid'];
    
    $strata = getAccess() == 2? 'college': 'department';
    
    $filter = '';
    if(getAccess() == '2') {
        $filter =sprintf("AND t.lectid = %s ", GetSQLValueString(getSessionValue('uid'), 'text') ) ;
    }
    
    $query_check = sprintf("SELECT * FROM teaching t "
                        . "JOIN department d ON t.deptid = d.deptid "
                        . "JOIN college c ON d.colid = c.colid "
                        . "WHERE csid = %s AND sesid = %s %s", 
                    GetSQLValueString($csid, 'text'), 
                    GetSQLValueString($sesid, 'int'), 
                    GetSQLValueString('filter', 'defined', $filter)); 
    $check = mysql_query($query_check, $tams) or die(mysql_error());
    $row_check = mysql_fetch_assoc($check);
    $totalRows_check = mysql_num_rows($check);
    
    if($totalRows_check > 0  || in_array(getAccess(), [2,3])) {
        if(getAccess() == 2 || (getAccess() == 3 && $row_check['accepted'] == 'no' && $row_check['approve']== 'no')) {
            
            //$msg = "The result for %s could not be updated from the database! Please try again or contact the system administrator.";
            //$type = 'error';
            
            $msg = "The result for %s Updated successfuly";
            $type = 'success';
            
            // Process deleted entries
            if(!empty($_POST['deletes'])) {
                mysql_query('START TRANSACTION;');
                $query_deletes = sprintf("SELECT * "
                                        . "FROM result "
                                        . "WHERE resultid IN(%s)", 
                                        GetSQLValueString("delete_ids", "defined", implode(',', $_POST['deletes'])));
                $deletes = mysql_query($query_deletes, $tams) or die(mysql_error());
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

                    $insert_entries[] = sprintf("(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)", 
                                                GetSQLValueString($row_update['stdid'], "text"), 
                                                GetSQLValueString($row_update['csid'], "text"), 
                                                GetSQLValueString($row_update['sesid'], "int"), 
                                                GetSQLValueString(getSessionValue("uid"), "text"), 
                                                GetSQLValueString($row_update['tscore'], "int"), 
                                                GetSQLValueString($row_update['escore'], "int"), 
                                                GetSQLValueString($tscore, "int"), 
                                                GetSQLValueString($escore, "int"), 
                                                GetSQLValueString(date('Y-m-d H:i:s'), "date"), 
                                                GetSQLValueString('edit', "text"));
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
                $rsupdate = mysql_query($update_query, $tams) or die(mysql_error());                

                $insert_query = sprintf("INSERT INTO `result_log` (stdid, csid, sesid, lectid, old_test, old_exam, "
                        . "new_test, new_exam, , type) VALUES %s;", 
                        GetSQLValueString("insert_entries", "defined", implode(',', $insert_entries)));
                $rsinsert = mysql_query($insert_query, $tams);
                
            }
            
            if($rsupdate && $rsinsert) {
                $msg = "The previously uploaded result for %s was successfully updated!";
                $type = 'success';
            }
            
        }else {
            $msg = "The result(%s) you are trying to edit, has already been approved at the $strata!";
            $type = 'error';
        }
    }else {
        $msg = "The result(%s) you are trying to edit, has not been assigned to any lecturer!";
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
    
    if($totalRows_check > 0 || in_array(getAccess(), [2,3])) {
        if(getAccess() == 2 || 
                (getAccess() == 3 && $row_check['accepted'] == 'no' && $row_check['approve']== 'no')) {
            
            $msg = "The result for %s could not be updated in the database! Please try again or contact the system administrator.";
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

$filter = "";
$colname_rslt = "-1";
if (isset($_GET['csid'])) {
  $colname_rslt = $_GET['csid'];
}

$colname1_rslt = getSessionValue('sesid');
if (isset($sesid)) {
  $colname1_rslt = $sesid;
}

if (isset($_GET['sid'])) {
  $colname1_rslt = $_GET['sid'];
}

$colname2_rslt = "-1";
if (isset($_GET['did'])) {
	$colname2_rslt = $_GET['did'];
	$filter = "AND p.deptid =".$colname2_rslt;
}

$query_status = sprintf("SELECT colid, approve, upload, accepted, released, progname, type, l.fname, l.lname "
                        . "FROM course c, teaching t, programme p, department d, lecturer l "
                        . "WHERE d.deptid = p.deptid AND c.csid = t.csid AND t.deptid = p.deptid "
                        . "AND t.lectid1 = l.lectid "
                        . "AND t.deptid = %s AND sesid = %s AND t.csid = %s", 
                        GetSQLValueString($colname2_rslt, "int"), 
                        GetSQLValueString($colname1_rslt, "int"), 
                        GetSQLValueString($colname_rslt, "text"));
$status = mysql_query($query_status, $tams) or die(mysql_error());
$row_status = mysql_fetch_assoc($status);
$totalRows_status = mysql_num_rows($status);

$approved = ( strtolower($row_status['approve']) == "yes" ) ? true: false;
$uploaded = ( strtolower($row_status['upload']) == "yes" ) ? true: false;
$accepted = ( strtolower($row_status['accepted']) == "yes" ) ? true: false;
$released = ( strtolower($row_status['released']) == "yes" ) ? true: false;
$name = $row_status['progname'];
$name .= ( isset($_GET['csid']) ) ? " (".$_GET['csid'].")": "";

$query_rslt = sprintf("SELECT distinct(r.stdid), r.resultid, r.edited, r.csid, tscore, escore, fname, lname, mname, sex "
                        . "FROM result r, student s, programme p, teaching t "
                        . "WHERE r.stdid = s.stdid "
                        . "AND r.csid = t.csid "
                        . "AND r.sesid = t.sesid "
                        . "AND t.upload = 'yes' "
                        . "AND r.csid = %s "
                        . "AND r.sesid = %s "
                        . "AND s.progid = p.progid %s "
                        . "ORDER BY r.stdid ASC", 
                        GetSQLValueString($colname_rslt, "text"), 
                        GetSQLValueString($colname1_rslt, "int"), 
                        GetSQLValueString($filter, "undefined", $filter));
$rslt = mysql_query($query_rslt, $tams) or die(mysql_error());
$row_rslt = mysql_fetch_assoc($rslt);
$totalRows_rslt = mysql_num_rows($rslt);

//$query_dept = sprintf("SELECT d1.deptid, d1.deptname "
//                        . "FROM department d1 "
//                        . "INNER JOIN department d2 ON d1.colid = d2.colid "
//                        . "WHERE d2.deptid = %s", 
//                        GetSQLValueString($colname2_rslt, "int"));

$query_dept = sprintf("SELECT deptid, deptname "
                        . "FROM department", 
                        GetSQLValueString($colname2_rslt, "int"));                        
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$query_grad = sprintf("SELECT * FROM grading g, session s WHERE g.sesid = %s AND g.colid = %s",
                        GetSQLValueString($colname1_rslt, "int"),
                        GetSQLValueString($row_status['colid'], "int"));
$grad = mysql_query($query_grad, $tams) or die(mysql_error());
$row_grad = mysql_fetch_assoc($grad);
$totalRows_grad = mysql_num_rows($grad);

$tot_pass           = 0;
$tot_fail           = 0;
$pcent1             = 0;
$pcent2             = 0;
$heighest_scr       = '-';
$lowest_scr         = '-';
$scores             = [];
$results            = [];
for (; $row_rslt; $row_rslt = mysql_fetch_assoc($rslt)) {

    $row_rslt['edit'] = false;
    $tot_scr = $row_rslt['tscore'] + $row_rslt['escore'];
    if ($tot_scr >= $row_grad['passmark']) {
        $tot_pass++;        
    }else {
        $tot_fail++;
    }

    array_push($scores, $tot_scr);
    array_push($results, $row_rslt);
}

$totalRows_rslt = $totalRows_rslt < 1? 1: $totalRows_rslt;
$pcent1 = $tot_pass * 100 / $totalRows_rslt;
$pcent2 = $tot_fail * 100 / $totalRows_rslt;

$scores = empty($scores)? [0]: $scores;
$heighest_scr = max($scores);
$lowest_scr = min($scores);
?>
<!doctype html>
<html ng-app="TamsApp">
    <?php include INCPATH."/header.php" ?>    
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="PageCtrl" ng-cloak>
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
                                        Consider Result for <?php echo $name?>
                                    </h3>
                                </div>

                                <div class="box-content"> 
                                    <form method="post" action="printresult2.php" target="_blank">
                                        <div class="row-fluid">
                                            <div class="span4">
                                                <?php if ($row_status['type'] != "Departmental") {
                                                    // Show if recordset not empty 
                                                    ?>
                                                <select name="deptid" onchange="deptfilt(this)">
                                                    <?php do { ?>
                                                        <option value="<?php echo $row_dept['deptid'] ?>" <?php
                                                        if (!(strcmp($row_dept['deptid'], $colname2_rslt))) {
                                                            echo "selected=\"selected\"";
                                                        }
                                                        ?>><?php echo $row_dept['deptname'] ?></option>
                                                <?php } while ($row_dept = mysql_fetch_assoc($dept)); ?>
                                                </select>
                                                <?php } // Show if recordset not empty   
                                                ?>
                                            </div>
                                            <div class="span4">
                                            	<input type="hidden" name="sesid" value="<?php echo $colname1_rslt?>"/>
                                            	<input type="hidden" name="csid" value="<?php echo $colname_rslt?>"/>                                            	
                                            	<input type="hidden" name="deptid" value="<?php echo $colname2_rslt?>"/>
                                                <input type="hidden" name="MM_Insert" value="form1"/>
                                                <input type="submit" class="btn btn-small btn-blue" value="Print Result"/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                 <a href="statistics/index.php?crsid=<?= $colname_rslt?>&sesid=<?= $colname1_rslt?>" target="Tabs" class="btn btn-primary btn-small">View Result Statistics</a>
                                            </div>

                                        </div>
                                        <div class="row-fluid">
                                            <div class="text-center">
                                                <span style="font-weight: bold">Course Lecturer</span>:  <?php echo $row_status['lname'].', '.$row_status['fname']; ?>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="row-fluid">
                                        <div>Total no. of Students:
                                            <span id="total"><?php echo $totalRows_rslt ?></span> (100%)
                                        </div>

                                        <div class="pull-left">
                                            <p>No. Passed: <span id="pass"><?= $tot_pass ." (".number_format($pcent1)."%)" ?></span></p>
                                            <p>No. Failed: <span id="fail"><?= $tot_fail ." (".number_format($pcent2)."%)" ?></span></p>
                                        </div>

                                        <div class="pull-left" style="margin-left: 30px;">
                                            <p>Highest Score: <span id="high"><?= $heighest_scr ?></span></p>
                                            <p>Lowest Score: <span id="low"><?= $lowest_scr ?></span></p>
                                        </div>

                                        <div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <?php if (in_array(getAccess(), [1,2,3])) { ?>
                                        <a class='btn btn-primary pull-left' target='_blank' href='result_error.php?sid=<?php echo $colname1_rslt?>&csid=<?php echo $colname_rslt?>' style="margin-right: 20px">View result errors</a>
                                        <?php }?>
                                        <?php if ((getAccess() == 3 && !$accepted && !$approved) || getAccess() == 2 || getAccess() == 1) { ?>
                                        <button type="submit" data-toggle="modal" href="#new-entry-modal" name="purge" class="btn btn-primary">Add New Entry</button>
                                        <?php }?>
                                        
                                        <?php if ($uploaded) { ?>
                                        <form action="" method="post" style="display: inline; margin-right: 20px;" class="pull-right">                                   
                                            <?php if ((getAccess() == 1 || getAccess() == 3) && !$accepted) { ?>
                                                <input type="submit" class="btn btn-primary" name="accept" value="Accept Result"/>
                                            <?php } ?>
                                            <?php if ((getAccess() == 1 || getAccess() == 2) && $accepted && !$approved) { ?>
                                                <input type="submit" class="btn btn-primary" name="approve" value="Approve Result"/>
                                            <?php } ?>
                                            <?php if ((getAccess() == 1 && $accepted) || (getAccess() == 2 && $accepted) || (getAccess() == 3 && !$accepted)) { ?>
                                                <input type="submit" class="btn btn-primary" name="disapprove" value="Step-Down Result" style="margin-left: 20px"/>
                                            <?php } ?>

                                            <input type="hidden" name="sesid" value="<?php echo $colname1_rslt ?>"/>
                                            <input type="hidden" name="csid" value="<?php echo $colname_rslt ?>"/>                                            
                                        </form>
                                        <?php } ?>
                                        
                                        <?php if ((getAccess() == 3 && !$approved && !$released) || (getAccess() == 2 && $released) || (getAccess() == 1 && $released)) { ?>
                                        <form action="" method="post" class="pull-right" style="margin-right: 20px">
                                            <input type="submit" class="btn btn-primary" name="update" value="Update Result" ng-show="editDone || deletes.length > 0"/>
                                            <input type="hidden" name="sesid" value="<?php echo $colname1_rslt ?>"/>
                                            <input type="hidden" name="csid" value="<?php echo $colname_rslt ?>"/>
                                            <input type="hidden" ng-repeat="(k,e) in edits" name="edits[{{k}}][tscore]" value="{{e.tscore}}" ng-if="e.tscore != null && e.tscore != ''"/>
                                            <input type="hidden" ng-repeat="(k,e) in edits" name="edits[{{k}}][escore]" value="{{e.escore}}" ng-if="e.escore != null && e.escore != ''"/>
                                            <input type="hidden" ng-repeat="d in deletes" name="deletes[]" value="{{d}}"/>
                                        </form>   
                                        <?php }?>
                                    </div>
                                    
                                    <table class="table table-striped table-condensed table-hover">
                                        <thead>
                                            <tr>
                                                <th><strong>S/N</strong></th>
                                                <th><strong>Matric</strong></th>
                                                <th><strong>Name</strong></th>
                                                <th align="center"><strong>Sex</strong></th>
                                                <th align="center"><strong>CA</strong></th>
                                                <th align="center"><strong>Exam</strong></th>
                                                <th align="center"><strong>Total</strong></th>
                                                <th align="center"><strong>Remark</strong></th>
                                                <th align="center"></th>
                                                
                                                <th align="center">
                                                    <button ng-click="editResult()" ng-bind="editEnabled? 'Done': 'Edit'" class="btn btn-small btn-primary"></button>
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
                                                <td>{{rs.lname}} &nbsp; {{rs.fname}} {{rs.mname}}</td>
                                                <td>{{rs.sex}}</td>
                                                <td align="center" class="tscore" width="40">
                                                    <input type="text" size="1" maxlength="2" ng-model="rs.tscore" ng-show="rs.edit" name="edits[{{rs.resultid}}][tscore]" value=""/>
                                                    <span ng-bind="scoreValue(rs.tscore)" ng-show="!rs.edit"></span>
                                                </td>
                                                <td align="center" class="escore" width="40">
                                                    <input type="text" size="1" maxlength="2" ng-model="rs.escore" ng-show="rs.edit" name="edits[{{rs.resultid}}][escore]" value=""/>
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
                                                                        
                                    <div class="text-center" style="margin-top: 50px">
                                        <?php if ((getAccess() == 3 && !approved && !released) || (getAccess() == 2 && released)) { ?>
                                        <form action="" method="post" style="display: inline">
                                            <input type="submit" class="btn btn-primary" name="update" value="Update Result" ng-show="editDone || deletes.length > 0"/>
                                            <input type="hidden" name="sesid" value="<?php echo $colname1_rslt ?>"/>
                                            <input type="hidden" name="csid" value="<?php echo $colname_rslt ?>"/>
                                            <input type="hidden" ng-repeat="(k,e) in edits" name="edits[{{k}}][tscore]" value="{{e.tscore}}" ng-if="e.tscore != null && e.tscore != ''"/>
                                            <input type="hidden" ng-repeat="(k,e) in edits" name="edits[{{k}}][escore]" value="{{e.escore}}" ng-if="e.escore != null && e.escore != ''"/>
                                            <input type="hidden" ng-repeat="d in deletes" name="deletes[]" value="{{d}}"/>
                                        </form>
                                        <?php }?>
                                        
                                        <?php if($uploaded) {?>
                                        <form action="" method="post" style="display: inline; margin-right: 20px;">                                   
                                            <?php if(getAccess() == 3 && !$accepted){?>
                                            <input type="submit" class="btn btn-primary" name="accept" value="Accept Result"/>
                                            <?php }?>
                                            <?php if(getAccess() == 2 && $accepted && !$approved) { ?>
                                            <input type="submit" class="btn btn-primary" name="approve" value="Approve Result"/>
                                            <?php }?>
                                            <?php if((getAccess() == 2 && $accepted) || (getAccess() == 3 && !$accepted)) { ?>
                                            <input type="submit" class="btn btn-primary" name="disapprove" value="Step-Down Result" style="margin-left: 20px"/>
                                            <?php }?>
                                            
                                            <input type="hidden" name="sesid" value="<?php echo $colname1_rslt ?>"/>
                                            <input type="hidden" name="csid" value="<?php echo $colname_rslt ?>"/>                                            
                                        </form>
                                        <?php }?>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
            
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
                        <input type="hidden" name="sesid" value="<?php echo $colname1_rslt ?>"/>
                        <input type="hidden" name="csid" value="<?php echo $colname_rslt ?>"/>
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