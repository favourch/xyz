<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "1,2,3,4,5,6,7,8,9, 20, 21, 22, 23";
check_auth($auth_users, $site_root);

$query_rssess = "SELECT * FROM `session` WHERE listing='TRUE' ORDER BY sesname DESC LIMIT 0,6";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

$sesname = $row_rssess['sesname'];
$deptid = getSessionValue('did');

$colname_col = "-1";
if (getSessionValue('cid') != NULL) {
    $colname_col = getSessionValue('cid');
}
    
$colname_dept = "-1";
if (getSessionValue('did') != NULL) {
    $colname_dept = getSessionValue('did');
}

$query_prog = "";

if(getAccess() == 1){
    
    $query_prog = sprintf("SELECT  p.progname,  p.progid "
                        . "FROM department d, programme p  "
                        . "WHERE p.deptid = d.deptid  ");
                      
    
}
if(getAccess() == 2){
    
    $query_prog = sprintf("SELECT  p.progname,  p.progid "
                        . "FROM department d, programme p  "
                        . "WHERE p.deptid = d.deptid  "
                        . "AND  d.colid = %s", 
                        GetSQLValueString($colname_col, "int"));
    
}
if(getAccess() == 3){
    $query_prog = sprintf("SELECT  p.progname,  p.progid "
                        . "FROM department d, programme p  "
                        . "WHERE p.deptid = d.deptid  "
                        . "AND  d.deptid = %s", 
                        GetSQLValueString($colname_dept, "int"));
}

$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);


$level = 'all';
$filter = '';
$pid = 'all';

$ses = $row_rssess['sesid'];

if(isset($_GET['sid'])) {
    $ses = $_GET['sid'];
}

if(isset($_GET['lvl'])) {
    $level = $_GET['lvl'];
    
    if($level != 'all') {
        $filter .= ' AND l.levelid = '.GetSQLValueString($level, 'int');
    }
}

if(isset($_GET['pid'])) {
    $pid = $_GET['pid'];
    
    if($pid != 'all') {
        $filter .= ' AND s.progid = '.GetSQLValueString($pid, 'int');
    }
}

 $query_stud = sprintf("SELECT distinct(s.stdid), s.fname, s.lname, at.typename, l.levelname, s.sex, st.date_time "
                    . "FROM student s "
                    . "JOIN programme p ON p.progid = s.progid "
                    . "JOIN department d ON d.deptid = p.deptid "
                    . "JOIN admission_type at ON at.typeid = s.admid "                     
                    . "JOIN schfee_transactions st ON st.matric_no = s.stdid "            
                    . "JOIN payschedule p ON p.scheduleid = st.scheduleid "           
                    . "JOIN level_name l ON l.levelid = p.levelid AND l.active = 'TRUE' "
                    . "WHERE p.sesid = %s "
                    . "AND st.status = 'APPROVED' "
                 //   . "AND d.deptid = %s "
                    . " %s ORDER BY s.stdid ASC ",
                    GetSQLValueString($ses, "int"),
                  //   GetSQLValueString($deptid, "int"),
                    GetSQLValueString($filter, "defined", $filter));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

$query_levels = sprintf("SELECT max(duration) as `max` 
                FROM programme p 
                JOIN department d ON d.deptid = p.deptid 
                WHERE  d.deptid = %s",
                GetSQLValueString($deptid, "int"));

$levels = mysql_query($query_levels, $tams) or die(mysql_error());
$row_levels = mysql_fetch_assoc($levels);
$totalRows_levels = mysql_num_rows($levels);

$name = 'Paid students';
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
                                    <h3><i class="icon-money"></i>
                                        Payment List - <?php echo $row_prog['progname']; ?>
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <div class="span3">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Search by <?= $programme_name?></label>
                                                <div class="controls controls-row">
                                                    <select name='prog' onchange="progfilt(this)">
                                                        <option value="all" <?= ($pid == "all") ? 'selected' : '';?>>All</option>
                                                        <?php for(; $row_prog != false ; $row_prog = mysql_fetch_assoc($prog)){?>
                                                        <option value="<?php echo $row_prog['progid']?>" <?= ($pid == $row_prog['progid']) ? 'selected' : '' ?>><?php echo $row_prog['progname'];?></option>
                                                        <?php }?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="span3">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Session</label>
                                                <div class="controls controls-row">
                                                    <select name='ses' onchange="sesfilt(this)">
                                                        <?php for(; $row_rssess != false ; $row_rssess = mysql_fetch_assoc($rssess)){?>
                                                        <option value="<?php echo $row_rssess['sesid']?>" <?= ($ses == $row_rssess['sesid'])?  'selected' : ''?>><?php echo $row_rssess['sesname'];?>
                                                        </option>
                                                        <?php }?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="span3">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Level </label>
                                                <div class="controls controls-row">
                                                    <select onChange="lvlfilt(this)">
                                                        <option value="all" <?php if($level == "all") echo 'selected';?>>All</option>
                                                        <?php 
                                                              $count = isset($row_levels['max'])? $row_levels['max']: 0;                                        
                                                              for($idx = 1; $idx <= $count;$idx++) {
                                                        ?>  
                                                                  <option value="<?php echo $idx?>" 
                                                                      <?php if($level == $idx) echo 'selected';?>>
                                                                      <?php echo $idx.'00';?>
                                                                  </option>

                                                        <?php }?>
                                                        <!--
                                                        <option value="" <?php if($level == '') echo 'selected';?>>Extra Year 1</option>
                                                        <option value="" <?php if($level == '') echo 'selected';?>>Extra Year 2</option>
                                                        -->
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="span3">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">&nbsp;</label>
                                                <div class="controls controls-row">
                                                   
                                                    <button class="btn"><i class="icon-user"></i> Total Paid <span class="label label-lightred"><?= $totalRows_stud ?></span></button>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>  
                                    <table width="670" class="table table-striped table-condensed">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Matric</th>
                                                <th>Name</th>
                                                <th>Sex</th>
                                                <th>Admission Mode</th>
                                                <th>Level</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($totalRows_stud > 0) {
                                                $i = 1;
                                                do {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $i++; ?></td>
                                                        <td>
                                                            <a href="../student/profile.php?stid=<?= $row_stud['stdid'] ?>">
                                                            <?= $row_stud['stdid']; ?>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <?= "{$row_stud['fname']} {$row_stud['lname']}"; ?>
                                                        </td>
                                                        <td><?= getSex($row_stud['sex'])?></td>
                                                        <td><?= (isset($row_stud['typename'])) ? $row_stud['typename'] : '-'; ?></td>
                                                        <td><?= $row_stud['level']?></td>
                                                        <td><?= $row_stud['date_time']?></td>
                                                    </tr>
                                                    <?php
                                                }
                                                while ($row_stud = mysql_fetch_assoc($stud));
                                            }
                                            else {
                                                ?>
                                                <tr>
                                                    <td colspan="6"><div class="alert alert-error">No record available!</div></td>
                                                </tr>
    <?php
}
?>
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
            <?php include INCPATH."/footer.php" ?>
    </body>
</html>