<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once('../../path.php'); 

$auth_users = "1, 20, 21, 23, 27";
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
                        . "WHERE active = 'TRUE' "
                        . "ORDER BY levelid ASC");
$level = mysql_query($query_level) or die(mysql_error());
$row_level = mysql_fetch_assoc($level);
$totalRows_level = mysql_num_rows($level);
$lvl = $row_level['levelid'];

/*if(isset($_GET['lvl'])) {
    $lvl = $_GET['lvl'];
*/

if (isset($_GET['lvl'])) {
    $lvl = $_GET['lvl'];
    if ($lvl != 'all' && is_numeric($lvl)) {
    
        $filter .= 'AND dc.level ='.  GetSQLValueString($lvl, "int");
        $filter2 .= 'AND level ='.  GetSQLValueString($lvl, "int");
    }
}

$query_prog = sprintf("SELECT * "
                        . "FROM programme WHERE continued='Yes' "
                        . "ORDER BY progname ASC");
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
                        . "WHERE p.progid = %s AND l.status = 'active' ", 
                        GetSQLValueString($pid, 'int'));
$staff = mysql_query($query_staff) or die(mysql_error());
$row_staff = mysql_fetch_assoc($staff); 
$totalRows_staff = mysql_num_rows($staff); 

$query_stdlist = sprintf("SELECT count(stdid) AS classlist FROM registration "
                    . "WHERE progid = %s "
                    . "%s "
                    . "AND sesid = %s ",
                    GetSQLValueString($pid, 'int'), 
                    GetSQLValueString($filter2, "defined", $filter2), 
                    GetSQLValueString($ses, 'int'));
$stdlist = mysql_query($query_stdlist) or die(mysql_error());
$row_stdlist = mysql_fetch_assoc($stdlist); 
$totalRows_stdlist = mysql_num_rows($stdlist);

$query_min_unit = sprintf("SELECT *, AVG(min) as total "
                        . "FROM reg_unit "
                        . "WHERE progid = %s "
                        . "%s", 
                        GetSQLValueString($pid, 'int'), 
                        GetSQLValueString($filter2, "defined", $filter2));
$min_unit = mysql_query($query_min_unit) or die(mysql_error()); 
$row_min_unit = mysql_fetch_assoc($min_unit);
$totalRows_min_unit = mysql_num_rows($min_unit);

$min = $row_min_unit['total']; 
                                                
$query_courselist = sprintf("SELECT c.csid, c.csname, dc.unit, count(distinct(cr.stdid)) AS reg, d.deptid "
                        . "FROM course c "
                        . "JOIN department_course dc ON c.csid = dc.csid  "
                        . "JOIN department d ON d.deptid = c.deptid "
                        . "JOIN programme p ON p.deptid = d.deptid AND p.progid = %s "
                        //. "LEFT JOIN lecturer l ON dc.deptid = l.deptid "
                        . "JOIN course_reg cr ON dc.csid = cr.csid "
                       // . "JOIN student s ON cr.stdid = s.stdid " 
                       // . "WHERE dc.level = %s "
                        . "WHERE cr.sesid = %s "
                        . "AND c.deptid = dc.deptid "
                        . "%s "
                        //. "AND r.status = 'Registered' "
                        . "GROUP BY c.csid ", 
                        GetSQLValueString($pid, 'int'), 
                        GetSQLValueString($ses, 'int'), 
                        GetSQLValueString($filter, "defined", $filter));
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
                                        FTE Calcultations
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
                                                <option value="all">All Levels</option>
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
                                        <div class="span3">Staff No. = <a target="_blank" href="stafflist.php?did=<?php echo $row_courselist['deptid'] ?> "> <?php echo $row_staff['staffno']?> </a></div>
                                        <div class="span3">Student No. = <a target="_blank" href="studentlist.php?did=<?php echo $row_courselist['deptid'] ?>&sid=<?php echo $ses ?>"> <?php echo $row_stdlist['classlist']?> </a></div>
                                        <div class="span3">Minimum Reg. Unit = <?php echo round($min,0)?></div>
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
                                                <td>
                                                    <a target="_blank" href="../../course/course.php?csid=<?php echo $row_courselist['csid'] ?>">
                                                    <?php echo $row_courselist['csid']; ?></td>
                                                    </a>
                                                <td><?php echo ucwords(strtolower($row_courselist['csname'])) ?></td>
                                                <td><?php echo $row_courselist['unit']; $total_unit += $row_courselist['unit']?></td>
                                                <td>
                                                    <a target="_blank" href="../../registration/coursereg.php?sid=<?php echo $ses ?>&csid=<?php echo $row_courselist['csid'] ?>">
                                                    <?php echo $row_courselist['reg']; $total_reg += $row_courselist['reg']?></td>
                                                <td><?php echo $ur = $row_courselist['unit'] * $row_courselist['reg']; $total_ur += $ur?></td>
                                                <td><?php echo $fte = round(($row_courselist['unit'] * $row_courselist['reg']) / $min,1); $total_fte += $fte?></td>
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
                                        <b>F.T.E (Student)</b> = (U*R) / Min Reg. = <?php echo $total_ur;?>/<?php echo $min;?> = <b><?php echo round($total_ur/$min,1);?></b> 
                                    </div>
                                    <br/>
                                    <div class="row-fluid">  
                                            <b>Required Staff</b> = FTE (Student) / Staff-Student Ratio = <?php echo round($total_ur/$min,1);?>/<?php echo '30';?> = <b><?php echo round(($total_ur/$min)/30,0);?></b>
                                    </div>
                                        
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