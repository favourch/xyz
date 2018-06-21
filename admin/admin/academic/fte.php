<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once('../../path.php'); 

$auth_users = "1,20";
check_auth($auth_users, $site_root.'/admin');

$total = 0;

$cur = $ses = $_SESSION['sesid'];

$query_sess = "SELECT * "
                . "FROM `session` "
                . "WHERE sesid <= $cur "
                . "ORDER BY sesname DESC";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$totalRows_sess = mysql_num_rows($sess);

if(isset($_GET['sid'])) {
    $ses = $_GET['sid'];
}

$query_level = sprintf("SELECT * "
                        . "FROM level_name "
                        . "ORDER BY levelid ASC");
$level = mysql_query($query_level) or die(mysql_error());
$row_level = mysql_fetch_assoc($level);
$totalRows_level = mysql_num_rows($level);
$lvl = $row_level['levelid'];
if(isset($_GET['lvl'])) {
    $lvl = $_GET['lvl'];
}

$query_prog = sprintf("SELECT * "
                        . "FROM programme "
                        . "ORDER BY deptid ASC");
$prog = mysql_query($query_prog) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);
$pid = $row_prog['progid'];

if(isset($_GET['pid'])) {
    $pid = $_GET['pid'];
}

$query_staff = sprintf("SELECT count(lectid) as staffno "
                        . "FROM lecturer l "
                        . "JOIN programme p ON l.deptid = p.deptid "
                        . "WHERE p.progid = %s ", 
                        GetSQLValueString($pid, 'int'));
$staff = mysql_query($query_staff) or die(mysql_error());
$row_staff = mysql_fetch_assoc($staff);
$totalRows_staff = mysql_num_rows($staff);

$query_min_unit = sprintf("SELECT * "
                        . "FROM reg_unit "
                        . "WHERE progid = %s "
                        . "AND level = %s", 
                        GetSQLValueString($pid, 'int'), 
                        GetSQLValueString($lvl, 'int'));
$min_unit = mysql_query($query_min_unit) or die(mysql_error());
$row_min_unit = mysql_fetch_assoc($min_unit);
$totalRows_min_unit = mysql_num_rows($min_unit);

$min = $row_min_unit['min'];
                                                
$query_courselist = sprintf("SELECT c.csid, c.csname, dc.unit, count(s.stdid) AS reg "
                        . "FROM course c "
                        . "JOIN department_course dc ON c.csid = dc.csid AND dc.progid = %s "
                        //. "LEFT JOIN lecturer l ON dc.deptid = l.deptid "
                        . "LEFT JOIN course_reg cr ON dc.csid = cr.csid "
                        . "LEFT JOIN student s ON cr.stdid = s.stdid AND dc.progid = s.progid " 
                        . "WHERE cr.sesid = %s "
                        //. "AND r.status = 'Registered' "
                        . "GROUP BY c.csid ", 
                        GetSQLValueString($pid, 'int'), 
                        GetSQLValueString($ses, 'int'));
$courselist = mysql_query($query_courselist) or die(mysql_error());
$row_courselist = mysql_fetch_assoc($courselist);
$totalRows_courselist = mysql_num_rows($courselist);
?>
<!doctype html>
<html>
    <?php include INCPATH."/header.php" ?>
    <body  data-layout-sidebar="fixed" data-layout-topbar="fixed">
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
                                        FTE
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">   
                                        
                                        <div class="span4">
                                            <select name='ses' onchange="sesfilt(this)">
                                                <?php for (; $row_sess = mysql_fetch_assoc($sess); ) { ?>
                                                    <option value="<?php echo $row_sess['sesid'] ?>" <?php if ($ses == $row_sess['sesid']) echo 'selected' ?>>
                                                        <?php echo $row_sess['sesname']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        
                                        <div class="span4">
                                            <select name='prog' onchange="progfilt(this)">
                                                <?php for (; $row_prog; $row_prog = mysql_fetch_assoc($prog)) { ?>
                                                    <option value="<?php echo $row_prog['progid'] ?>" <?php if ($pid == $row_prog['progid']) echo 'selected' ?>>
                                                        <?php echo $row_prog['progname']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        
                                        <div class="span4">
                                            <select name='level' onchange="lvlfilt(this)">
                                                <?php for (; $row_level; $row_level = mysql_fetch_assoc($level)) { ?>
                                                    <option value="<?php echo $row_level['levelid'] ?>" <?php if ($lvl == $row_level['levelid']) echo 'selected' ?>>
                                                        <?php echo $row_level['levelname']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row-fluid">
                                        <div class="span3">Student/Staff Ratio = 1:30</div>
                                        <div class="span3">Staff No. = <?php echo $row_staff['staffno']?></div>
                                        <div class="span3">Minimum Reg. Unit = <?php echo $min?></div>
                                    </div>
                                    
                                    <div class="row-fluid">
                                        <table class="table table-condensed table-striped" >
                                            <thead>
                                                <tr>
                                                    <th align="center" >S/N</th>
                                                    <th align="center">Code</th>
                                                    <th align="center">Title</th>
                                                    <th align="center">Unit (U)</th>
                                                    <th align="center">Registered Student (R)</th>
                                                    <th align="center">U*R</th>
                                                    <th align="center">(U*R) / Min Reg.</th>
                                                </tr>
                                            </thead>
                                            <?php
                                                $i = 1;
                                                $total_unit = $total_reg = $total_ur = 0;
                                                for($idx = 1; $row_courselist; $row_courselist = mysql_fetch_assoc($courselist), $idx++) {
                                            ?>
                                            <tr align="center" >
                                                <td><?php echo $idx; ?></td>
                                                <td><?php echo $row_courselist['csid']; ?></td>
                                                <td><?php echo $row_courselist['csname'] ?></td>
                                                <td><?php echo $row_courselist['unit']; $total_unit += $row_courselist['unit']?></td>
                                                <td><?php echo $row_courselist['reg']; $total_reg += $row_courselist['reg']?></td>
                                                <td><?php echo $ur = $row_courselist['unit'] * $row_courselist['reg']; $total_ur += $ur?></td>
                                                <td><?php echo $fte = ($row_courselist['unit'] * $row_courselist['reg']) / $min; $total_fte += $fte?></td>
                                            </tr>

                                            <?php }?>
                                            <tr>
                                                <th align="right" colspan="3"><strong>Total </strong></th>
                                                <th align="center"><?php echo $total_unit ?></th>
                                                <th align="center"><?php echo $total_reg ?></th>
                                                <th align="center"><?php echo $total_ur ?></th>
                                                <th align="center"><?php echo $total_fte?></th>
                                            </tr>
                                        </table>
                                    </div><br/>  
                                    
                                    <div class="row-fluid">   
                                        F.T.E (Staff) = <?php echo $total_fte/30;?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
        </div>
    </body>
</html>