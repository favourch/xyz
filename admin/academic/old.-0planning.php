<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once('../../path.php'); 

$auth_users = "1,20, 21, 23, 27";
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

$query_RsCsReg = sprintf("SELECT d.deptid, d.deptname, count(r.stdid) AS `count` "
                        . "FROM registration r "
                        . "RIGHT JOIN student s ON r.stdid = s.stdid "
                        . "JOIN programme p ON p.progid = s.progid "
                        . "JOIN department d ON d.deptid = p.deptid "
                        . "WHERE r.status = 'Registered' "
                        //. "AND s.status = 'Undergrad' "
                        . "AND r.sesid = %s "
                        . "GROUP BY p.deptid "
                        . "ORDER BY d.deptid ASC", 
                        GetSQLValueString($ses, 'int'));
$RsCsReg = mysql_query($query_RsCsReg) or die(mysql_error());
$row_RsCsReg = mysql_fetch_assoc($RsCsReg);
$totalRows_RsCsReg = mysql_num_rows($RsCsReg);

$reg = [];
for($idx = 0; $idx < $totalRows_RsCsReg; $idx++, $row_RsCsReg = mysql_fetch_assoc($RsCsReg)) {
	$reg[$row_RsCsReg['deptid']] = $row_RsCsReg['count'];

}
	
$query_RsCsRegAprv = sprintf("SELECT d.deptid, d.deptname, count(r.stdid) AS `count` "
                            . "FROM registration r "
                            . "RIGHT JOIN student s ON r.stdid = s.stdid "
                            . "JOIN programme p ON p.progid = s.progid "
                            . "JOIN department d ON d.deptid = p.deptid "
                            . "WHERE r.approved = 'TRUE' "
                            //. "AND s.status = 'Undergrad' "
                            . "AND r.sesid = %s "
                            . "GROUP BY p.deptid "
                            . "ORDER BY d.deptid ASC", 
                            GetSQLValueString($ses, 'int'));
$RsCsRegAprv = mysql_query($query_RsCsRegAprv) or die(mysql_error());
$row_RsCsRegAprv = mysql_fetch_assoc($RsCsRegAprv);
$totalRows_RsCsRegAprv = mysql_num_rows($RsCsRegAprv);

$cleared = [];
$totalCleard = 0;
for($idx = 0; $idx < $totalRows_RsCsRegAprv; $idx++, $row_RsCsRegAprv = mysql_fetch_assoc($RsCsRegAprv)) {
    $cleared[$row_RsCsRegAprv['deptid']] = $row_RsCsRegAprv['count'];
    $totalCleard = ($totalCleard + $cleared[$row_RsCsRegAprv['deptid']]);
	
}

$query_RsdeptStaff = sprintf("SELECT d.deptname, count(lectid) as `count` "
                            . "FROM lecturer l, department d "
                            . "WHERE d.deptid = l.deptid "
                            . "AND l.status = 'Active' "
                            . "GROUP BY l.deptid "
                            . "ORDER BY d.deptid ASC");
$RsdeptStaff = mysql_query($query_RsdeptStaff) or die(mysql_error());
$row_RsdeptStaff = mysql_fetch_assoc($RsdeptStaff);
$totalRows_RsdeptStaff = mysql_num_rows($RsdeptStaff);
	
$query_RsdeptStd = sprintf("SELECT distinct(count(s.stdid)) as `count`, d.deptname, d.colid "
                        . "FROM student s, department d, programme p, registration r "
                        . "WHERE s.progid = p.progid "
                        . "AND d.deptid = p.deptid "
                        . "AND r.stdid = s.stdid "
                        . "AND r.sesid = %s "
                        //. "AND s.status = 'Undergrad' "
                        . "GROUP BY p.deptid "
                        . "ORDER BY d.deptid ASC",
                        GetSQLValueString($ses, "int"));
$RsdeptStd = mysql_query($query_RsdeptStd) or die(mysql_error());
$row_RsdeptStd = mysql_fetch_assoc($RsdeptStd);
$totalRows_RsdeptStd = mysql_num_rows($RsdeptStd);

$query_DeptPay = sprintf("SELECT d.deptid, d.deptname, count(distinct(st.matric_no)) AS `count` "
                        . "FROM schfee_transactions st "
                        . "JOIN payschedule ps ON st.scheduleid = ps.scheduleid "
                        . "JOIN student s ON st.matric_no = s.stdid "
                        . "JOIN registration r ON r.stdid = s.stdid AND r.stdid = st.matric_no "
                        . "JOIN programme p ON p.progid = s.progid "
                        . "JOIN department d ON d.deptid = p.deptid "
                        . "JOIN college c ON c.colid = d.colid "
                        . "WHERE st.status = 'APPROVED' "
                        . "AND ps.sesid = %s "
                        //. "AND s.status = 'Undergrad' "
                        . "GROUP BY d.deptid "
                        . "ORDER BY d.deptid ASC", 
                        GetSQLValueString($ses, 'int'));
$DeptPay = mysql_query($query_DeptPay) or die(mysql_error());
$row_DeptPay = mysql_fetch_assoc($DeptPay);
$totalRows_DeptPay = mysql_num_rows($DeptPay);

$deptpaid = [];
for($idx = 0; $idx < $totalRows_DeptPay; $idx++, $row_DeptPay = mysql_fetch_assoc($DeptPay)) {
    $deptpaid[$row_DeptPay['deptid']] = $row_DeptPay['count'];		
}

$query_Rsdept = "SELECT deptid, deptname, colid FROM department ORDER BY deptid ASC";
$Rsdept = mysql_query($query_Rsdept, $tams) or die(mysql_error());
$row_Rsdept = mysql_fetch_assoc($Rsdept);
$totalRows_Rsdept = mysql_num_rows($Rsdept);


$query_RscolStaff = sprintf("SELECT c.colname, count(lectid) as `count` "
                            . "FROM lecturer l, department d, college c "
                            . "WHERE d.deptid = l.deptid "
                            . "AND d.colid = c.colid "
                            . "AND l.status = 'Active' "
                            . "GROUP BY c.colid "
                            . "ORDER BY c.colid ASC");
$RscolStaff = mysql_query($query_RscolStaff) or die(mysql_error());
$row_RscolStaff = mysql_fetch_assoc($RscolStaff);
$totalRows_RscolStaff = mysql_num_rows($RscolStaff);

$query_RscolStd = sprintf("SELECT distinct(count(s.stdid)) as `count`, c.colname "
                        . "FROM student s, department d, programme p, college c, registration r "
                        . "WHERE s.progid = p.progid "
                        . "AND d.deptid = p.deptid "
                        . "AND r.stdid = s.stdid "
                        . "AND r.sesid = %s "
                        . "AND d.colid = c.colid "
                        //. "AND s.status = 'Undergrad' "
                        . "GROUP BY c.colid "
                        . "ORDER BY c.colid ASC",
                        GetSQLValueString($ses, "int"));
$RscolStd = mysql_query($query_RscolStd) or die(mysql_error());
$row_RscolStd = mysql_fetch_assoc($RscolStd);
$totalRows_RscolStd = mysql_num_rows($RscolStd);

$query_Rscol = "SELECT colid, colname, coltitle FROM college ORDER BY colid ASC";
$Rscol = mysql_query($query_Rscol, $tams) or die(mysql_error());
$row_Rscol = mysql_fetch_assoc($Rscol);
$totalRows_Rscol = mysql_num_rows($Rscol);

$query_RsColCsReg = sprintf("SELECT c.colid, c.colname,  count(r.stdid) AS `count` "
                            . "FROM registration r "
                            . "RIGHT JOIN student s ON r.stdid = s.stdid "
                            . "JOIN programme p ON p.progid = s.progid "
                            . "JOIN department d ON d.deptid = p.deptid "
                            . "JOIN college c ON c.colid = d.colid "
                            . "WHERE r.status = 'Registered' "
                            . "AND r.sesid = %s "
                            //. "AND s.status = 'Undergrad' "
                            . "GROUP BY c.colid "
                            . "ORDER BY c.colid ASC", 
                            GetSQLValueString($ses, 'int'));
$RsColCsReg = mysql_query($query_RsColCsReg) or die(mysql_error());
$row_RsColCsReg = mysql_fetch_assoc($RsColCsReg);
$totalRows_RsColCsReg = mysql_num_rows($RsColCsReg);

$colreg = [];
for($idx = 0; $idx < $totalRows_RsColCsReg; $idx++, $row_RsColCsReg = mysql_fetch_assoc($RsColCsReg)) {
    $colreg[$row_RsColCsReg['colid']] = $row_RsColCsReg['count'];
}

$query_RsColCsRegAprv = sprintf("SELECT c.colid, d.deptid, d.deptname, count(distinct(r.stdid)) AS `count` "
                                . "FROM registration r "
                                . "RIGHT JOIN student s ON r.stdid = s.stdid "
                                . "JOIN programme p ON p.progid = s.progid "
                                . "JOIN department d ON d.deptid = p.deptid "
                                . "JOIN college c ON c.colid = d.colid "
                                . "WHERE r.approved = 'TRUE' "
                                . "AND r.sesid = %s "
                                //. "AND s.status = 'Undergrad' "
                                . "GROUP BY c.colid "
                                . "ORDER BY c.colid ASC", 
                                GetSQLValueString($ses, 'int'));
$RsColCsRegAprv = mysql_query($query_RsColCsRegAprv) or die(mysql_error());
$row_RsColCsRegAprv = mysql_fetch_assoc($RsColCsRegAprv);
$totalRows_RsColCsRegAprv = mysql_num_rows($RsColCsRegAprv);

$colcleared = [];
for($idx = 0; $idx < $totalRows_RsColCsRegAprv; $idx++, $row_RsColCsRegAprv = mysql_fetch_assoc($RsColCsRegAprv)) {
    $colcleared[$row_RsColCsRegAprv['colid']] = $row_RsColCsRegAprv['count'];		
}

$query_ColPay = sprintf("SELECT c.colid, c.colname, count(distinct(st.matric_no)) AS `count` "
                        . "FROM schfee_transactions st "
                        . "JOIN payschedule ps ON st.scheduleid = ps.scheduleid "
                        . "JOIN student s ON st.matric_no = s.stdid "
                        . "JOIN registration r ON r.stdid = s.stdid AND r.stdid = st.matric_no "
                        . "JOIN programme p ON p.progid = s.progid "
                        . "JOIN department d ON d.deptid = p.deptid "
                        . "JOIN college c ON c.colid = d.colid "
                        . "WHERE st.status = 'APPROVED' "
                        //. "AND s.status = 'Undergrad' "
                        . "AND ps.sesid = %s "
                        . "GROUP BY c.colid "
                        . "ORDER BY c.colid ASC", 
                        GetSQLValueString($ses, 'int'));
$ColPay = mysql_query($query_ColPay) or die(mysql_error());
$row_ColPay = mysql_fetch_assoc($ColPay);
$totalRows_ColPay = mysql_num_rows($ColPay);

$colpaid = [];
for($idx = 0; $idx < $totalRows_ColPay; $idx++, $row_ColPay = mysql_fetch_assoc($ColPay)) {
    $colpaid[$row_ColPay['colid']] = $row_ColPay['count'];		
}

// Stats query
$query_statsColPaid = sprintf("SELECT c.colid, c.colname, count(distinct(st.matric_no)) AS `count`, ps.level "
                            . "FROM schfee_transactions st "
                            . "JOIN payschedule ps ON st.scheduleid = ps.scheduleid "
                            . "JOIN student s ON st.matric_no = s.stdid "
                            . "JOIN registration r ON r.stdid = s.stdid AND r.stdid = st.matric_no "
                            . "JOIN programme p ON p.progid = s.progid "
                            . "JOIN department d ON d.deptid = p.deptid "
                            . "JOIN college c ON c.colid = d.colid "
                            . "WHERE st.status = 'APPROVED' "
                            . "AND ps.sesid = %s "
                            //. "AND s.status = 'Undergrad' "
                            . "GROUP BY c.colid, ps.level "
                            . "ORDER BY c.colid, ps.level ASC", 
                            GetSQLValueString($ses, 'int'));
$statsColPaid = mysql_query($query_statsColPaid) or die(mysql_error());
$row_statsColPaid = mysql_fetch_assoc($statsColPaid);
$totalRows_statsColPaid = mysql_num_rows($statsColPaid);

$statCPaid = [];
$statCPaid['total'][1] = 0;
$statCPaid['total'][2] = 0;
$statCPaid['total'][3] = 0;
$statCPaid['total'][4] = 0;
for($idx = 0; $idx < $totalRows_statsColPaid; $idx++, $row_statsColPaid = mysql_fetch_assoc($statsColPaid)) {
    if($row_statsColPaid['level'] > 4){
        $statCPaid[$row_statsColPaid['colid']]['4'] += $row_statsColPaid['count'];
        $statCPaid['total']['4'] = isset($statCPaid['total']['4'])? 
                $statCPaid['total']['4'] + $row_statsColPaid['count']: $row_statsColPaid['count'];
    }else {
        $statCPaid[$row_statsColPaid['colid']][$row_statsColPaid['level']] = $row_statsColPaid['count'];
        $statCPaid['total'][$row_statsColPaid['level']] += $row_statsColPaid['count'];
    }
}

$query_statsColPop = sprintf("SELECT count(distinct(r.stdid)) AS `count`, c.colid, c.coltitle, r.level "
                            . "FROM registration r "
                            . "RIGHT JOIN student s ON r.stdid = s.stdid "
                            . "JOIN programme p ON p.progid = s.progid "
                            . "JOIN department d ON d.deptid = p.deptid "
                            . "JOIN college c ON c.colid = d.colid "
                            . "WHERE r.sesid = %s "
                            //. "AND s.status = 'Undergrad' "
                            . "GROUP BY c.colid, r.level "
                            . "ORDER BY c.colid, r.level ASC", 
                            GetSQLValueString($ses, 'int'));
$statsColPop = mysql_query($query_statsColPop) or die(mysql_error());
$row_statsColPop = mysql_fetch_assoc($statsColPop);
$totalRows_statsColPop = mysql_num_rows($statsColPop);

$statCPop = [];
$statCPop['total'][1] = 0;
$statCPop['total'][2] = 0;
$statCPop['total'][3] = 0;
$statCPop['total'][4] = 0;
for($idx = 0; $idx < $totalRows_statsColPop; $idx++, $row_statsColPop = mysql_fetch_assoc($statsColPop)) {
    
    if($row_statsColPop['level'] > 4){
        $statCPop[$row_statsColPop['colid']]['4'] = isset($statCPop[$row_statsColPop['colid']]['4'])? 
                $statCPop[$row_statsColPop['colid']]['4'] + $row_statsColPop['count']: 0;        
        $statCPop['total']['4'] +=  $row_statsColPop['count'];
    }else {
        
        $statCPop[$row_statsColPop['colid']][$row_statsColPop['level']] = $row_statsColPop['count'];        
        $statCPop['total'][$row_statsColPop['level']] += $row_statsColPop['count'];
    }
}

$additions = [];

if($ses == $cur) {
    // Get all accepted students who haven't paid their fees.
  $query_accstud = sprintf("SELECT count(distinct(at.can_no)) AS `count`, d.deptid, c.colid, entrylevel "
                            . "FROM accfee_transactions at "
                            . "LEFT JOIN schfee_transactions st ON at.can_no = st.can_no AND at.status = st.status "
                            . "JOIN prospective ps ON at.can_no = ps.jambregid "
                            . "JOIN admissions a ON ps.admid = a.admid "
                            . "JOIN admission_type adt ON a.typeid = adt.typeid "
                            . "JOIN programme p ON p.progid = ps.progoffered "
                            . "JOIN department d ON d.deptid = p.deptid "
                            . "JOIN college c ON c.colid = d.colid "
                            . "WHERE st.can_no IS NULL AND at.status = 'APPROVED' AND at.sesid = %s "
                            . "GROUP BY d.deptid, c.colid, a.typeid "
                            . "ORDER BY c.colid, d.deptid, a.typeid ASC",
                    GetSQLValueString($cur, "int"));    
    $accstud = mysql_query($query_accstud) or die(mysql_error());
    $row_accstud = mysql_fetch_assoc($accstud);
    $totalRows_accstud = mysql_num_rows($accstud);
    
    // Break down into various college, department and Admission type
    for(;$row_accstud = mysql_fetch_assoc($accstud);) {
        
        // Department segment
        if(isset($additions[$row_accstud['colid']][$row_accstud['deptid']])){
            $additions[$row_accstud['colid']][$row_accstud['deptid']] += $row_accstud['count'];
        }else {
            $additions[$row_accstud['colid']][$row_accstud['deptid']] = $row_accstud['count'];
        }
                        
        // College segment
        if(isset($additions[$row_accstud['colid']]['total'])){
            $additions[$row_accstud['colid']]['total'] += $row_accstud['count'];
        }else {
            $additions[$row_accstud['colid']]['total'] = $row_accstud['count'];
        }
        
        // Admission Type segment
        if(isset($statCPop[$row_accstud['colid']][$row_accstud['entrylevel']])) {
            $statCPop[$row_accstud['colid']][$row_accstud['entrylevel']] += $row_accstud['count'];
            $statCPop['total'][$row_accstud['entrylevel']] += $row_accstud['count'];
        }else {
            $statCPop[$row_accstud['colid']][$row_accstud['entrylevel']] = $row_accstud['count'];
            $statCPop['total'][$row_accstud['entrylevel']] += $row_accstud['count'];
        }
    }
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
                                        General University Statistics
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <select name='ses' onchange="sesfilt(this)">
                                            <?php for (; $row_sess = mysql_fetch_assoc($sess); ) { ?>
                                                <option value="<?php echo $row_sess['sesid'] ?>" <?php if ($ses == $row_sess['sesid']) echo 'selected' ?>>
                                                    <?php echo $row_sess['sesname']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    
                                    <div class="row-fluid">
                                        <table class="table table-condensed table-striped" >
                                            <thead>
                                                <tr>
                                                    <th align="center" >S/n</th>
                                                    <th align="center">Departments</th>
                                                    <th align="center">Staff</th>
                                                    <th align="center">Student </th>
                                                    <th align="center">Registered</th>
                                                    <th align="center">Cleared</th>
                                                    <th align="center">Paid</th>
                                                    <th align="center">%</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            $i = 1;
                                            $totalStaff = 0;
                                            $totalStudent = 0;
                                            $totalReg = 0;
                                            $totalCleard = 0;
                                            do {

                                                $totalStaff = ($totalStaff + $row_RsdeptStaff['count']);
                                                $totalStudent = $totalStudent +
                                                        ($row_RsdeptStd['count'] += isset($additions[$row_Rsdept['colid']][$row_Rsdept['deptid']]) ?
                                                        $additions[$row_Rsdept['colid']][$row_Rsdept['deptid']] : 0);
                                                $totalReg = ($totalReg + $row_RsCsReg['count']);
                                                ?>

                                                <tr align="center" >
                                                    <td><?php echo $i; ?></td>
                                                    <td><?php echo $row_Rsdept['deptname']; ?></td>
                                                    <td>
                                                        <a target="_blank" href="stafflist.php?did=<?php echo $row_Rsdept['deptid'] ?>">
                                                            <?php echo $row_RsdeptStaff['count'] ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a target="_blank" href="studentlist.php?did=<?php echo $row_Rsdept['deptid'] ?>&sid=<?php echo $ses ?>">
                                                            <?php echo $row_RsdeptStd['count'] ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a target="_blank" 
                                                           href="studentlist.php?action=reg&did=<?php echo $row_Rsdept['deptid'] ?>&sid=<?php echo $ses ?>">
                                                               <?php echo isset($reg[$row_Rsdept['deptid']]) ? $reg[$row_Rsdept['deptid']] : 0; ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a target="_blank" 
                                                           href="studentlist.php?action=clear&did=<?php echo $row_Rsdept['deptid'] ?>&sid=<?php echo $ses ?>">
                                                               <?php echo isset($cleared[$row_Rsdept['deptid']]) ? $cleared[$row_Rsdept['deptid']] : 0 ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a target="_blank" 
                                                           href="studentlist.php?action=paid&did=<?php echo $row_Rsdept['deptid'] ?>&sid=<?php echo $ses ?>">
                                                           <?php
                                                                if (!isset($deptpaid[$row_Rsdept['deptid']]))
                                                                    $deptpaid[$row_Rsdept['deptid']] = 0;

                                                                echo $deptpaid[$row_Rsdept['deptid']];
                                                           ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <?php echo round($deptpaid[$row_Rsdept['deptid']] / $row_RsdeptStd['count'] * 100, 2) ?>
                                                    </td>
                                                </tr>

                                                <?php
                                                $i++;

                                                $row_RsdeptStd = mysql_fetch_assoc($RsdeptStd);
                                                $row_RsCsRegAprv = mysql_fetch_assoc($RsCsRegAprv);
                                                $row_RsdeptStaff = mysql_fetch_assoc($RsdeptStaff);
                                            } while ($row_Rsdept = mysql_fetch_assoc($Rsdept));
                                            ?>
                                            <tr>
                                                <th align="center"><strong>Total </strong></th>
                                                <th align="center"><?php echo $totalRows_Rsdept ?></th>
                                                <th align="center"><?php echo $totalStaff ?></th>
                                                <th align="center"><?php echo $totalStudent ?></th>
                                                <th align="center"><?php echo array_sum($reg); ?></th>
                                                <th align="center"><?php echo array_sum($cleared) ?></th>
                                                <th align="center"><?php echo array_sum($deptpaid) ?></th>
                                                <th align="center"><?php echo round(array_sum($deptpaid)/$totalStudent*100, 2)?></th>
                                            </tr>
                                        </table>
                                    </div><br/>
                                    
                                    
                                    <div class="row-fluid">
                                        <div class="span8">
                                            <table class="table table-condensed table-striped" >
                                                <thead>
                                                    <tr>
                                                        <th align="center">College</th>
                                                        <th align="center">100L</th>
                                                        <th align="center">200L</th>
                                                        <th align="center">300L</th>
                                                        <th align="center">400L</th>
                                                    </tr>
                                                </thead>
                                                <?php
                                                do {
                                                    ?>

                                                    <tr align="center" >
                                                        <td><?php echo $row_Rscol['coltitle'] ?></td>
                                                        <td>
                                                            <a href="studentlist.php?action=paid&cid=<?php echo $row_Rscol['colid'] ?>&sid=<?php echo $ses ?>&lvl=1">
                                                                <?php echo isset($statCPaid[$row_Rscol['colid']][1]) ? $statCPaid[$row_Rscol['colid']][1] : 0; ?>
                                                            </a> | 
                                                            <a href="studentlist.php?cid=<?php echo $row_Rscol['colid'] ?>&sid=<?php echo $ses ?>&lvl=1">
                                                                <?php echo $statCPop[$row_Rscol['colid']][1] ?>
                                                            </a>
                                                        </td>

                                                        <td>
                                                            <a href="studentlist.php?action=paid&cid=<?php echo $row_Rscol['colid'] ?>&sid=<?php echo $ses ?>&lvl=2">
                                                                <?php echo $statCPaid[$row_Rscol['colid']][2] ?>
                                                            </a> | 
                                                            <a href="studentlist.php?cid=<?php echo $row_Rscol['colid'] ?>&sid=<?php echo $ses ?>&lvl=2">
                                                                <?php echo isset($statCPop[$row_Rscol['colid']][2]) ? $statCPop[$row_Rscol['colid']][2] : 0; ?>
                                                            </a>
                                                        </td>

                                                        <td>
                                                            <a href="studentlist.php?action=paid&cid=<?php echo $row_Rscol['colid'] ?>&sid=<?php echo $ses ?>&lvl=3">
                                                                <?php echo $statCPaid[$row_Rscol['colid']][3] ?>
                                                            </a> | 
                                                            <a href="studentlist.php?cid=<?php echo $row_Rscol['colid'] ?>&sid=<?php echo $ses ?>&lvl=3">
                                                                <?php echo isset($statCPop[$row_Rscol['colid']][3]) ? $statCPop[$row_Rscol['colid']][3] : 0 ?>
                                                            </a>
                                                        </td>

                                                        <td>
                                                            <a href="studentlist.php?action=paid&cid=<?php echo $row_Rscol['colid'] ?>&sid=<?php echo $ses ?>&lvl=4">
                                                                <?php echo $statCPaid[$row_Rscol['colid']][4] ?>
                                                            </a> | 
                                                            <a href="studentlist.php?cid=<?php echo $row_Rscol['colid'] ?>&sid=<?php echo $ses ?>&lvl=4">
                                                                <?php echo isset($statCPop[$row_Rscol['colid']][4]) ? $statCPop[$row_Rscol['colid']][4] : 0 ?>
                                                            </a>
                                                        </td>
                                                    </tr>

                                                    <?php
                                                } while ($row_Rscol = mysql_fetch_assoc($Rscol));
                                                $rows = mysql_num_rows($Rscol);
                                                if ($rows > 0) {
                                                    mysql_data_seek($Rscol, 0);
                                                    $row_Rscol = mysql_fetch_assoc($Rscol);
                                                }
                                                ?>
                                                <tr>
                                                    <th align="center"><strong>Total </strong></th>
                                                    <th align="center"><?php echo "{$statCPaid['total'][1]} | {$statCPop['total'][1]}" ?></th>
                                                    <th align="center"><?php echo "{$statCPaid['total'][2]} | {$statCPop['total'][2]}" ?></th>
                                                    <th align="center"><?php echo "{$statCPaid['total'][3]} | {$statCPop['total'][3]}" ?></th>
                                                    <th align="center"><?php echo "{$statCPaid['total'][4]} | {$statCPop['total'][4]}" ?></th>
                                                </tr>
                                            </table>
                                        </div>
                                    </div><br/>
                                    
                                    <div class="row-fluid">

                                        <table class="table table-condensed table-striped" >
                                            <thead>
                                                <tr>
                                                    <th align="center" >S/n</th>
                                                    <th align="center">College</th>
                                                    <th align="center">Staff</th>
                                                    <th align="center">Student </th>
                                                    <th align="center">Registered</th>
                                                    <th align="center">Cleared</th>
                                                    <th align="center">Paid</th>
                                                    <th align="center">%</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            $i = 1;
                                            $totalColStaff = 0;
                                            $totalColStudent = 0;

                                            do {

                                                $totalColStaff = ($totalColStaff + $row_RscolStaff['count']);
                                                $totalColStudent = $totalColStudent +
                                                        ($row_RscolStd['count'] += isset($additions[$row_Rscol['colid']]['total']) ?
                                                        $additions[$row_Rscol['colid']]['total'] : 0);
                                                ?>

                                                <tr align="center" >
                                                    <td><?php echo $i; ?></td>
                                                    <td><?php echo $row_Rscol['colname'] ?></td>
                                                    <td>
                                                        <a target="_blank" href="stafflist.php?cid=<?php echo $row_Rscol['colid'] ?>">
                                                            <?php echo $row_RscolStaff['count'] ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a target="_blank" href="studentlist.php?cid=<?php echo $row_Rscol['colid'] ?>&sid=<?php echo $ses ?>">
                                                            <?php echo $row_RscolStd['count'] ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a target="_blank" 
                                                           href="studentlist.php?action=reg&cid=<?php echo $row_Rscol['colid'] ?>&sid=<?php echo $ses ?>">
                                                               <?php echo isset($colreg[$row_Rscol['colid']]) ? $colreg[$row_Rscol['colid']] : 0; ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a target="_blank" 
                                                           href="studentlist.php?action=clear&cid=<?php echo $row_Rscol['colid'] ?>&sid=<?php echo $ses ?>">
                                                               <?php echo isset($colcleared[$row_Rscol['colid']]) ? $colcleared[$row_Rscol['colid']] : 0 ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a target="_blank" 
                                                           href="studentlist.php?action=paid&cid=<?php echo $row_Rscol['colid'] ?>&sid=<?php echo $ses ?>">
                                                            <?php
                                                                if (!isset($colpaid[$row_Rscol['colid']]))
                                                                    $colpaid[$row_Rscol['colid']] = 0;

                                                                echo $colpaid[$row_Rscol['colid']];
                                                            ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <?php echo round($colpaid[$row_Rscol['colid']] / $row_RscolStd['count'] * 100, 2) ?>
                                                    </td>
                                                </tr>

                                                <?php
                                                $i++;
                                                $row_RsColCsRegAprv = mysql_fetch_assoc($RsColCsRegAprv);
                                                $row_RscolStd = mysql_fetch_assoc($RscolStd);
                                                $row_RscolStaff = mysql_fetch_assoc($RscolStaff);
                                            } while ($row_Rscol = mysql_fetch_assoc($Rscol));
                                            ?>
                                            <tr>
                                                <th align="center"><strong>Total </strong></th>
                                                <th align="center"><?php echo $totalRows_Rscol ?></th>
                                                <th width="50" align="center"><?php echo $totalColStaff ?></th>
                                                <th align="center"><?php echo $totalColStudent ?></th>
                                                <th align="center"><?php echo array_sum($colreg); ?></th>
                                                <th align="center"><?php echo array_sum($colcleared); ?></th>
                                                <th align="center"><?php echo array_sum($colpaid); ?></th>
                                                <th align="center">
                                                    <?php echo round(array_sum($colpaid)/$totalColStudent*100, 2); ?>
                                                </th>
                                            </tr>
                                        </table>
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
    

