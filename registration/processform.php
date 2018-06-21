<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "6,3";
check_auth($auth_users, $site_root);

$sesid = $_SESSION['sesid']; 
if(isset($_GET['sid']) && getAccess() == 3) {
    $sesid = $_GET['sid'];
}
 // echo $sesid; exit();
$query_rssess = sprintf("SELECT * FROM `session` s "
                        . "WHERE s.sesid <= %s "
                        . "ORDER BY sesname DESC ", 
                        GetSQLValueString($_SESSION['sesid'], "int")); 
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$totalRows_rssess = mysql_num_rows($rssess);

if (isset($_POST['clear'])) {
    $updateSQL = sprintf("UPDATE course_reg SET cleared=%s "
                        . "WHERE stdid=%s "
                        . "AND sesid=%s", 
                        GetSQLValueString('FALSE', "text"), 
                        GetSQLValueString($_POST['stid'], "text"), 
                        GetSQLValueString($sesid, "int"));
    $Registration = mysql_query($updateSQL, $tams) or die(mysql_error());

    foreach ($_POST['course'] as $value) {

        $updateSQL = sprintf("UPDATE course_reg SET cleared=%s "
                . "WHERE csid=%s "
                . "AND stdid=%s "
                . "AND sesid=%s", 
                GetSQLValueString('TRUE', "text"), 
                GetSQLValueString($value, "text"), 
                GetSQLValueString($_POST['stid'], "text"), 
                GetSQLValueString($sesid, "int"));

        $Registration1 = mysql_query($updateSQL, $tams) or die(mysql_error());
        $update_info = mysql_info($tams);

        $updateSQL = sprintf("UPDATE registration SET approved=%s "
                . "WHERE stdid=%s "
                . "AND sesid=%s", 
                GetSQLValueString('TRUE', "text"), 
                GetSQLValueString($_POST['stid'], "text"), 
                GetSQLValueString($sesid, "int"));

        $Registration2 = mysql_query($updateSQL, $tams) or die(mysql_error());
    }
}

$lvl = '';

if(getAccess() == 6) {
    $query_info = sprintf("SELECT * FROM `staff_adviser` WHERE lectid=%s AND sesid=%s", 
            GetSQLValueString(getSessionValue('lectid'), "text"), 
            GetSQLValueString($sesid, "int"));
    $info = mysql_query($query_info, $tams) or die(mysql_error());
    $row_info = mysql_fetch_assoc($info);
    $totalRows_info = mysql_num_rows($info);
    
    $lvl = $row_info['level'];
}else {
    $query_info = sprintf("SELECT * FROM `staff_adviser` WHERE lectid=%s AND sesid=%s", 
            GetSQLValueString(getSessionValue('lectid'), "text"), 
            GetSQLValueString($sesid, "int"));
    $info = mysql_query($query_info, $tams) or die(mysql_error());
    $row_info = mysql_fetch_assoc($info);
    $totalRows_info = mysql_num_rows($info);
    
    $lvl = 1;
    if (isset($_GET['lvl'])) {
        $lvl = $_GET['lvl'];
    }
}

$query_studs = sprintf("SELECT s.stdid, fname, lname, s.progid "
        . "FROM student s "
        . "JOIN registration r ON s.stdid = r.stdid "
        . "JOIN programme p ON p.progid = s.progid "
        . "WHERE r.course = 'Registered' "
        . "AND r.approved = 'FALSE' "
        . "AND r.sesid = %s "
        . "AND p.deptid = %s "
        . "AND s.level = %s", 
        GetSQLValueString($sesid, "int"), 
        GetSQLValueString(getSessionValue('did'), "int"), 
        GetSQLValueString($lvl, "int"));
$studs = mysql_query($query_studs, $tams) or die(mysql_error());
$row_studs = mysql_fetch_assoc($studs);
$totalRows_studs = mysql_num_rows($studs);

$query_pstuds = sprintf("SELECT s.stdid, fname, lname, s.progid, s.curid "
        . "FROM student s "
        . "JOIN registration r ON s.stdid = r.stdid "
        . "JOIN programme p ON p.progid = s.progid "
        . "WHERE r.course = 'Registered' "
        . "AND r.approved = 'TRUE' "
        . "AND r.sesid = %s "        
        . "AND p.deptid = %s "
        . "AND r.level = %s", 
        GetSQLValueString($sesid, "int"), 
        GetSQLValueString(getSessionValue('did'), "int"), 
        GetSQLValueString($lvl, "int"));
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
            GetSQLValueString($sesid, "int"));
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

$query_stud = sprintf("SELECT s.progid, colid, p.deptid, fname, lname, level, s.curid "
        . "FROM student s, programme p, department d "
        . "WHERE s.progid = p.progid AND d.deptid = p.deptid AND stdid = %s", 
        GetSQLValueString($colname_stud, "text"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

$query_cour = sprintf("SELECT distinct(r.csid), r.cleared, c.semester, c.csname, dc.status, dc.unit "
                        . "FROM course_reg r, course c, department_course dc "
                        . "WHERE c.csid = r.csid AND r.csid = dc.csid "
                        . "AND r.stdid = %s AND c.curid = dc.curid AND c.curid = %s "
                        . "AND dc.progid = %s "
                        . "AND r.sesid = %s "
                        
                        . "UNION "
                        
                        . "SELECT distinct(r.csid), r.cleared, c.semester, c.csname, c.status, c.unit "
                        . "FROM course_reg r, course c "
                        . "WHERE c.csid = r.csid "
                        . "AND r.stdid = %s AND c.curid = %s "
                        . "AND r.sesid = %s "
                        . "AND r.csid NOT IN "
                        
                        . "(SELECT r.csid "
                        . "FROM course_reg r, course c, department_course dc "
                        . "WHERE c.csid = r.csid AND r.csid = dc.csid "
                        . "AND r.stdid = %s AND c.curid = dc.curid AND c.curid = %s "
                        . "AND dc.progid = %s "
                        . "AND r.sesid = %s ) ",
                        
                        GetSQLValueString($colname_stud, "text"), 
                        GetSQLValueString($row_stud['curid'], "int"),
                        GetSQLValueString(getSessionValue('pid'), "int"),
                        GetSQLValueString($sesid, "int"),
                        
                        GetSQLValueString($colname_stud, "text"), 
                        GetSQLValueString($row_stud['curid'], "int"),
                        GetSQLValueString($sesid, "int"),
                        
                        GetSQLValueString($colname_stud, "text"), 
                        GetSQLValueString($row_stud['curid'], "int"),
                        GetSQLValueString(getSessionValue('pid'), "int"),
                        GetSQLValueString($sesid, "int")); 

$cour = mysql_query($query_cour, $tams) or die(mysql_error());
$row_cour = mysql_fetch_assoc($cour);
$totalRows_cour = mysql_num_rows($cour);

$query_pcour = sprintf("SELECT distinct(r.csid), r.cleared, c.semester, c.csname, dc.status, dc.unit "
                        . "FROM course_reg r, course c, department_course dc "
                        . "WHERE c.csid = r.csid AND r.csid = dc.csid "
                        . "AND r.stdid = %s AND c.curid = dc.curid AND c.curid = %s "
                        . "AND dc.progid = %s "
                        . "AND r.sesid = %s "
                        
                        . "UNION "
                        
                        . "SELECT distinct(r.csid), r.cleared, c.semester, c.csname, c.status, c.unit "
                        . "FROM course_reg r, course c "
                        . "WHERE c.csid = r.csid "
                        . "AND r.stdid = %s AND c.curid = %s "
                        . "AND r.sesid = %s "
                        . "AND r.csid NOT IN "
                        
                        . "(SELECT r.csid "
                        . "FROM course_reg r, course c, department_course dc "
                        . "WHERE c.csid = r.csid AND r.csid = dc.csid "
                        . "AND r.stdid = %s AND c.curid = dc.curid AND c.curid = %s "
                        . "AND dc.progid = %s "
                        . "AND r.sesid = %s ) ",
                        
                        GetSQLValueString($colname_stud, "text"), 
                        GetSQLValueString($row_studs['curid'], "int"),
                        GetSQLValueString(getSessionValue('pid'), "int"),
                        GetSQLValueString($sesid, "int"),
                        
                        GetSQLValueString($colname_stud, "text"), 
                        GetSQLValueString($row_studs['curid'], "int"),
                        GetSQLValueString($sesid, "int"),
                        
                        GetSQLValueString($colname_stud, "text"), 
                        GetSQLValueString($row_studs['curid'], "int"),
                        GetSQLValueString(getSessionValue('pid'), "int"),
                        GetSQLValueString($sesid, "int")); 
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
                    </div>-->
                    <br/>
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-calendar"></i>
                                        Course Form Clearance<?php echo ' ('.$lvl.'00 Level)';?>
                                    </h3>
                                </div>
                                
                                <div class="box-content">
                                    <?php if (getAccess() == 3) : ?>
                                        <div class="row-fluid">
                                            <div class="span2">Session:</div>
                                            <div class="span3">
                                                <div class="control-group">
                                                    <div class="controls controls-row">
                                                        <select name="sesid" onchange="sesfilt(this)">
                                                            <?php for (; $row_rssess = mysql_fetch_assoc($rssess);) : ?>
                                                                <option value="<?php echo $row_rssess['sesid'] ?>" 
                                                                        <?php if ($sesid == $row_rssess['sesid']) echo 'selected' ?>>
                                                                            <?php echo $row_rssess['sesname'] ?>
                                                                </option>
                                                            <?php endfor; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="span2">Level:</div>
                                            <div class="span3">
                                                <div class="control-group">
                                                    <select name="level" onchange="lvlfilt(this)">
                                                        <option value="1" <?php if ($lvl == 1) echo 'selected' ?>>100</option>
                                                        <option value="2" <?php if ($lvl == 2) echo 'selected' ?>>200</option>
                                                        <option value="3" <?php if ($lvl == 3) echo 'selected' ?>>300</option>
                                                        <option value="4" <?php if ($lvl == 4) echo 'selected' ?>>400</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
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

