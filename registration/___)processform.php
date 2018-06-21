<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



/* -----------------------------------------------*
 * 
 * Logic of the College/index.php Page 
 *
 * *------------------------------------------------
 */

$MM_authorizedUsers = "6";
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

$MM_restrictGoTo = "../login.php";
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



$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


mysql_select_db($database_tams, $tams);
$query_rssess = "SELECT * FROM `session` WHERE status = 'TRUE' ORDER BY sesname DESC ";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

if (isset($_POST['clear'])) {
    $updateSQL = sprintf("UPDATE result SET cleared=%s "
                        . "WHERE stdid=%s "
                        . "AND sesid=%s", 
                        GetSQLValueString('FALSE', "text"), 
                        GetSQLValueString($_POST['stid'], "text"), 
                        GetSQLValueString($row_rssess['sesid'], "int"));

    mysql_select_db($database_tams, $tams);
    $Result = mysql_query($updateSQL, $tams) or die(mysql_error());

    foreach ($_POST['course'] as $value) {

        $updateSQL = sprintf("UPDATE result SET cleared=%s "
                . "WHERE csid=%s "
                . "AND stdid=%s "
                . "AND sesid=%s", GetSQLValueString('TRUE', "text"), GetSQLValueString($value, "text"), GetSQLValueString($_POST['stid'], "text"), GetSQLValueString($row_rssess['sesid'], "int"));

        mysql_select_db($database_tams, $tams);
        $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
        $update_info = mysql_info($tams);

        $updateSQL = sprintf("UPDATE registration SET approved=%s "
                . "WHERE stdid=%s "
                . "AND sesid=%s", GetSQLValueString('TRUE', "text"), GetSQLValueString($_POST['stid'], "text"), GetSQLValueString($row_rssess['sesid'], "int"));

        mysql_select_db($database_tams, $tams);
        $Result2 = mysql_query($updateSQL, $tams) or die(mysql_error());
    }
}

 $query_info = sprintf("SELECT * FROM `staff_adviser` WHERE lectid=%s AND sesid=%s", 
        GetSQLValueString(getSessionValue('lectid'), "text"),
        GetSQLValueString($row_rssess['sesid'], "int"));
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);
//die(var_dump($query_info));

$query_studs = sprintf("SELECT s.stdid, fname, lname, s.progid "
        . "FROM student s "
        . "JOIN registration r ON s.stdid = r.stdid "
        . "JOIN programme p ON p.progid = s.progid "
        . "WHERE r.course = 'Registered' "
        . "AND r.approved = 'FALSE' "
        . "AND r.sesid = %s "
        . "AND p.deptid = %s "
        . "AND s.level = %s", 
        GetSQLValueString($row_rssess['sesid'], "int"), 
        GetSQLValueString(getSessionValue('did'), "int"), 
        GetSQLValueString($row_info['level'], "int"));
$studs = mysql_query($query_studs, $tams) or die(mysql_error());
$row_studs = mysql_fetch_assoc($studs);
$totalRows_studs = mysql_num_rows($studs);

$query_pstuds = sprintf("SELECT s.stdid, fname, lname, s.progid "
        . "FROM student s "
        . "JOIN registration r ON s.stdid = r.stdid "
        . "JOIN programme p ON p.progid = s.progid "
        . "WHERE r.course = 'Registered' "
        . "AND r.approved = 'TRUE' "
        . "AND r.sesid = %s "
        . "AND p.deptid = %s "
        . "AND s.level = %s", 
        GetSQLValueString($row_rssess['sesid'], "int"), 
        GetSQLValueString(getSessionValue('did'), "int"), 
        GetSQLValueString($row_info['level'], "int"));
$pstuds = mysql_query($query_pstuds, $tams) or die(mysql_error());
$row_pstuds = mysql_fetch_assoc($pstuds);
$totalRows_pstuds = mysql_num_rows($pstuds);

if (isset($_GET['stid'])) {
    $query_chk = sprintf("SELECT * "
            . "FROM student s "
            . "JOIN registration r ON r.stdid = s.stdid "
            . "WHERE s.stdid = %s "
            . "AND r.sesid = %s "
            . "AND r.approved = 'TRUE'", 
            GetSQLValueString($_GET['stid'], "text"), 
            GetSQLValueString($row_rssess['sesid'], "int"));
    $chk = mysql_query($query_chk, $tams) or die(mysql_error());
    $row_chk = mysql_fetch_assoc($chk);
    $totalRows_chk = mysql_num_rows($chk);
}

$default = 0;
$colname_stud = "-1";
if (getAccess() < 7 && isset($_GET['stid'])) {
    if ($totalRows_chk > 0) {
        $colname_stud = $row_studs['stdid'];
        $colname_pstud = $_GET['stid'];
        $default = 1;
    }
    else {
        $colname_stud = $_GET['stid'];
        $colname_pstud = $row_pstuds['stdid'];
    }
}
else {
    $colname_stud = $row_studs['stdid'];
    $colname_pstud = $row_pstuds['stdid'];
}

$query_stud = sprintf("SELECT s.progid, colid, p.deptid, fname, lname, level "
        . "FROM student s, programme p, department d "
        . "WHERE s.progid = p.progid AND d.deptid = p.deptid AND stdid = %s", GetSQLValueString($colname_stud, "text"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

$query_cour = sprintf("SELECT r.cleared, c.csid, c.csname, c.semester, c.unit, c.status "
        . "FROM result r "
        . "JOIN course c ON r.csid = c.csid "
        . "WHERE r.sesid = %s "
        . "AND stdid = %s", GetSQLValueString($row_rssess['sesid'], "int"), GetSQLValueString($colname_stud, "text"));
$cour = mysql_query($query_cour, $tams) or die(mysql_error());
$row_cour = mysql_fetch_assoc($cour);
$totalRows_cour = mysql_num_rows($cour);

$query_pcour = sprintf("SELECT r.cleared, c.csid, c.csname, c.semester, c.unit, c.status "
        . "FROM result r "
        . "JOIN course c ON r.csid = c.csid "
        . "WHERE r.sesid = %s "
        . "AND stdid = %s", GetSQLValueString($row_rssess['sesid'], "int"), GetSQLValueString($colname_pstud, "text"));
$pcour = mysql_query($query_pcour, $tams) or die(mysql_error());
$row_pcour = mysql_fetch_assoc($pcour);
$totalRows_pcour = mysql_num_rows($pcour);

$utUnits = 0;
$puUnits = 0;

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
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-calendar"></i>
                                        Course Form Clearance<?php echo ' ('.$row_info['level'].'00 Level)';?>
                                    </h3>
                                </div>
                                <div class="box-content nopadding">
                                    <form method="post" action="">
                                        <ul class="tabs tabs-inline tabs-top">
                                        <li class="active">
                                            <a data-toggle="tab" href="#first11"><i class="icon-remove"></i> Unprocessed</a>
                                        </li>
                                        <li class="">
                                            <a data-toggle="tab" href="#second22"><i class="icon-check"></i> Processed</a>
                                        </li>
                                        
                                    </ul>
                                    <div class="tab-content padding tab-content-inline tab-content-bottom">
                                        <div id="first11" class="tab-pane active">
                                             <?php if ($totalRows_studs) { ?>
                                            <div class="row-fluid">
                                                    <div class="span3">
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">&nbsp;</label>
                                                            <div class="controls controls-row">
                                                                <a class="btn btn-small btn-lime" href="editform.php?stid=<?php echo $colname_stud ?>">Add/Delete Courses</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="span3">
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Filter By Student</label>
                                                            <div class="controls controls-row">
                                                                <select onChange="studfilt(this)" name="stdid">
                                                                    <?php
                                                                    do {
                                                                        ?>
                                                                        <option value="<?php echo $row_studs['stdid'] ?>" 
                                                                                <?php if ($colname_stud == $row_studs['stdid']) echo 'selected' ?>>
                                                                                    <?php echo ucwords(strtolower($row_studs['lname'] . " "
                                                                                                    . $row_studs['fname'])) . " (" . $row_studs['stdid'] . ")"
                                                                                    ?>
                                                                        </option>
                                                                        <?php
                                                                    } while ($row_studs = mysql_fetch_assoc($studs));
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <table class="table table-condensed table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Code</th>
                                                        <th>Name</th>
                                                        <th>Unit</th>
                                                        <th>Status</th>
                                                        <th> &nbsp;</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php for ($i = 0; $i < $totalRows_cour; $i++) { ?>
                                                        <tr>
                                                            <td><a href="#"><?php echo $row_cour['csid'] ?></a></td>
                                                            <td><?php echo $row_cour['csname'] ?></td>
                                                            <td><?php echo $row_cour['unit'];
                                                             $utUnits += $row_cour['unit']; ?></td>
                                                            <td><?php echo $row_cour['status'] ?></td>
                                                            <td><span class="hide"><?php echo $row_cour['unit']; ?></span>
                                                                <input class="processed" type="checkbox" name="course[]" 
                                                                       value="<?php echo $row_cour['csid'] ?>" 
                                                        <?php if ($row_cour['cleared'] == 'TRUE') echo 'checked' ?>/></td>
                                                        </tr>
                                                    <?php $row_cour = mysql_fetch_assoc($cour); } ?>
                                                        <tr>
                                                            <th colspan="2"> Total</th>
                                                            <th><span id="total"><?php echo $utUnits ?></span></th>
                                                            <th></th>
                                                            <th></th>
                                                        </tr>
                                                </tbody>
                                            </table>
                                            <div class="form-actions">
                                                <input type="hidden" name="stid" value="<?php echo $colname_stud ?>">
                                                <input type="submit" name="clear" value="Clear" class="btn btn-primary">
                                                <button class="btn" type="button">Cancel</button>
                                            </div>
                                            <?php }else { ?>
                                            <div class="alert alert-error"> No unprocessed course form!</div>
                                            <?php } ?>
                                        </div>
                                        <div id="second22" class="tab-pane">
                                            <?php if ($totalRows_pstuds) { ?>
                                            <div class="row-fluid">
                                                    <div class="span3">
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">&nbsp;</label>
                                                            <div class="controls controls-row">
                                                                <a class="btn btn-small btn-lime" href="editform.php?stid=<?php echo $colname_pstud?>">Add/Delete Courses</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="span3">
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Filter By Student</label>
                                                            <div class="controls controls-row">
                                                                <select onChange="studfilt(this)" name="stdid">
                                                                    <?php
                                                                    do {
                                                                        ?>
                                                                        <option value="<?php echo $row_pstuds['stdid'] ?>" 
                                                                                <?php if ($colname_pstud == $row_pstuds['stdid']) echo 'selected' ?>>
                                                                                    <?php echo ucwords(strtolower($row_pstuds['fname'] . " "
                                                                                                    . $row_pstuds['lname'])) . "(" . $row_pstuds['stdid'] . ")"
                                                                                    ?>
                                                                        </option>
                                                                        <?php
                                                                    } while ($row_pstuds = mysql_fetch_assoc($pstuds));
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <table class="table table-condensed table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Code</th>
                                                            <th>Name</th>
                                                            <th>Unit</th>
                                                            <th>Status</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php for ($i = 0; $i < $totalRows_pcour; $i++) { ?>
                                                        <tr>
                                                            <td><?php echo $row_pcour['csid'] ?></td>
                                                            <td><?php echo $row_pcour['csname'] ?></td>
                                                            <td><?php echo $row_pcour['unit']; $puUnits += $row_pcour['unit']; ?></td>
                                                            <td><?php echo $row_pcour['status'] ?></td>
                                                            <td><span class="hide"><?php echo $row_pcour['unit']; ?></span>
                                                                <input type="checkbox" class="unprocessed" value="<?php echo $row_pcour['csid'] ?>" 
                                                            <?php if ($row_pcour['cleared'] == 'TRUE') echo 'checked' ?>/>
                                                            </td>
                                                        </tr>
                                                        <?php $row_pcour = mysql_fetch_assoc($pcour);} ?>
                                                        <tr>
                                                           
                                                            <th colspan="2">Total</th>
                                                            <th><span id="totalUnpro"><?php echo $puUnits ?></span></th>
                                                            <th></th>
                                                            <th></th>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            <?php }else { ?>
                                               <div class="alert alert-error"> No Processed course form!</div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    </form>
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
    <script>

          $(function() {
              $('.processed').change(function() {
                  var cur = $(this); 
                  var unit = parseInt(cur.prev().text());
                  var total = $('#total');
                  var totalUnit = parseInt(total.text());
                  if(cur.is(':checked')) {
                      total.text(totalUnit + unit);                      
                  }else {
                      total.text(totalUnit - unit);  
                  }
              });
              
              $('.unprocessed').change(function() {
                  var cur = $(this); 
                  var unit = parseInt(cur.prev().text());
                  var total = $('#totalUnpro');
                  var totalUnit = parseInt(total.text());
                  if(cur.is(':checked')) {
                      total.text(totalUnit + unit);                      
                  }else {
                      total.text(totalUnit - unit);  
                  }
              });
          });
      </script>
</html>

