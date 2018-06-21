<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "1,20,21,22,23,24";
check_auth($auth_users, $site_root.'/admin');
        
$query_session = "SELECT sesid, sesname FROM session ORDER BY sesid DESC";
$session = mysql_query($query_session, $tams) or die(mysql_error());
$totalRows_session = mysql_num_rows($session);

echo $sid = $_SESSION['admid']; exit();
if (isset($_GET['sid'])) {
    $sid = $_GET['sid'];
}

$query_rsprog = "SELECT progid, progname FROM programme WHERE continued = 'Yes' ORDER BY progname ASC";
$rsprog = mysql_query($query_rsprog, $tams) or die(mysql_error());
$row_rsprog = mysql_fetch_assoc($rsprog);
$totalRows_rsprog = mysql_num_rows($rsprog);

$programmes = array();
for ($idx = 0; $idx < $totalRows_rsprog; $idx++, $row_rsprog = mysql_fetch_assoc($rsprog)) {
    $programmes[$row_rsprog['progid']] = 0;
}
mysql_data_seek($rsprog, 0);
$row_rsprog = mysql_fetch_assoc($rsprog);

$first_choice = $app_fee = $admitted = $acceptance = $school_fees = $programmes;

$query_admit = sprintf("SELECT progoffered, count(pstdid) as count "
        . "FROM prospective "
        . "WHERE sesid = %s "
        . "AND adminstatus = 'Yes' "
        . "GROUP BY progoffered", GetSQLValueString($sid, "int"));
$admit = mysql_query($query_admit, $tams) or die(mysql_error());
$row_admit = mysql_fetch_assoc($admit);
$totalRows_admit = mysql_num_rows($admit);

for ($idx = 0; $idx < $totalRows_admit; $idx++, $row_admit = mysql_fetch_assoc($admit)) {
    if (isset($admitted[$row_admit['progoffered']])) {
        $admitted[$row_admit['progoffered']] = $row_admit['count'];
    }
}

$query_first = sprintf("SELECT progid1, count(pstdid) as count "
        . "FROM prospective "
        . "WHERE sesid = %s "
        . "AND formpayment = 'Yes' "
        . "GROUP BY progid1 "
        . "HAVING progid1 IS NOT NULL", GetSQLValueString($sid, "int"));
$first = mysql_query($query_first, $tams) or die(mysql_error());
$row_first = mysql_fetch_assoc($first);
$totalRows_first = mysql_num_rows($first);

for ($idx = 0; $idx < $totalRows_first; $idx++, $row_first = mysql_fetch_assoc($first)) {
    if (isset($first_choice[$row_first['progid1']])) {
        $first_choice[$row_first['progid1']] = $row_first['count'];
    }
}

$query_application = sprintf("SELECT progid1, status, count(distinct(pstdid)) as count "
        . "FROM prospective p "
        . "JOIN appfee_transactions s ON p.jambregid = s.can_no "
        . "WHERE status = 'APPROVED' "
        . "AND p.sesid = s.sesid "
        . "AND p.sesid = %s "
        . "GROUP BY progid1", GetSQLValueString($sid, "int"));
$application = mysql_query($query_application, $tams) or die(mysql_error());
$row_application = mysql_fetch_assoc($application);
$totalRows_application = mysql_num_rows($application);

for ($idx = 0; $idx < $totalRows_application; $idx++, $row_application = mysql_fetch_assoc($application)) {
    if (isset($app_fee[$row_application['progid1']])) {
        $app_fee[$row_application['progid1']] = $row_application['count'];
    }
}

$query_accept = sprintf("SELECT progoffered, status, count(distinct(pstdid)) as count "
        . "FROM prospective p "
        . "JOIN accfee_transactions s ON p.jambregid = s.can_no "
        . "WHERE status = 'APPROVED' "
        . "AND p.sesid = s.sesid "
        . "AND p.sesid = %s "
        . "GROUP BY progoffered", GetSQLValueString($sid, "int"));
$accept = mysql_query($query_accept, $tams) or die(mysql_error());
$row_accept = mysql_fetch_assoc($accept);
$totalRows_accept = mysql_num_rows($accept);

for ($idx = 0; $idx < $totalRows_accept; $idx++, $row_accept = mysql_fetch_assoc($accept)) {
    if (isset($acceptance[$row_accept['progoffered']])) {
        $acceptance[$row_accept['progoffered']] = $row_accept['count'];
    }
}

$query_schfee = sprintf("SELECT progoffered, status, count(distinct(pstdid)) as count "
        . "FROM prospective p "
        . "JOIN schfee_transactions s ON p.jambregid = s.can_no "
        . "WHERE status = 'APPROVED' "
        . "AND p.sesid = s.sesid "
        . "AND p.sesid = %s "
        . "GROUP BY progoffered", GetSQLValueString($sid, "int"));
$schfee = mysql_query($query_schfee, $tams) or die(mysql_error());
$row_schfee = mysql_fetch_assoc($schfee);
$totalRows_schfee = mysql_num_rows($schfee);

for ($idx = 0; $idx < $totalRows_schfee; $idx++, $row_schfee = mysql_fetch_assoc($schfee)) {
    if (isset($school_fees[$row_schfee['progoffered']])) {
        $school_fees[$row_schfee['progoffered']] = $row_schfee['count'];
    }
}

$query_regcount = sprintf("SELECT typename, regtype, count(distinct(pstdid)) as count "
        . "FROM prospective p "
        . "JOIN admissions a ON p.admid = a.admid "
        . "JOIN admission_type at ON a.typeid = at.typeid "
        . "WHERE p.sesid = %s "
        . "AND p.formpayment = 'Yes' "
        . "GROUP BY regtype, at.typeid", 
        GetSQLValueString($sid, "int"));
$regcount = mysql_query($query_regcount, $tams) or die(mysql_error());
$totalRows_regcount = mysql_num_rows($regcount);

$stud_count = [];

for(;$row_regcount = mysql_fetch_assoc($regcount);) {
    $stud_count[$row_regcount['typename']][$row_regcount['regtype']] = $row_regcount['count'];
}

$total_value = [];
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
                       Select Session 
                       <select onchange="sesfilt(this)">
                            <?php for (; $row_session = mysql_fetch_assoc($session);): ?>
                            <option value="<?php echo $row_session['sesid'] ?>" 
                                <?php echo $sid == $row_session['sesid']? 'selected': ''?>>
                                <?php echo $row_session['sesname'] ?>
                            </option>
                            <?php endfor;?>
                       </select>    
                    </div>
                    
                    <div class="row-fluid">
                        <table style="width:400px; font-weight: normal;" class="table table-striped table-condensed">
                            <?php if(count($stud_count) > 0) :?>
                            <tr>
                                <td></td>
                                <?php foreach(array_keys($stud_count) as $typename) :?>
                                <td>
                                    <?php 
                                        echo $typename;
                                        $total_value[$typename]['total'] = 0;
                                    ?>
                                </td>
                                <?php endforeach;?>
                                <td>TOTAL</td>            
                            </tr>
                            <tr>
                                <td>First Choice</td>
                                <?php 
                                    $total = 0;
                                    $vertical = 0;
                                    $horizontal = 0;
                                    foreach($stud_count as $t => $v) :?>
                                <td>
                                    <?php 
                                        echo $v['regular'];
                                        $total += $v['regular'];
                                        $total_value[$t]['total'] += $v['regular']?>
                                </td>
                                <?php endforeach;?>
                                <td>
                                    <?php echo $total;?>
                                </td>            
                            </tr>
                            <tr>
                                <td>Change Of Institution</td>
                                <?php 
                                    $total = 0;
                                    foreach($stud_count as $t => $v) :?>
                                <td>
                                    <?php 
                                        echo $v['coi'];
                                        $total += $v['coi'];
                                        $total_value[$t]['total'] += $v['coi']?>
                                </td>
                                <?php endforeach;?>
                                <td>
                                    <?php echo $total?>
                                </td>              
                            </tr>
                            <tr>
                                <td>Total</td>
                                <?php 
                                    $total = 0;
                                    foreach($stud_count as $t => $v) :?>
                                <td>
                                    <?php 
                                        echo $total_value[$t]['total'];
                                        $total += $total_value[$t]['total']?>
                                </td>
                                <?php endforeach;?>
                                <td><?php echo $total?></td>            
                            </tr>
                            <?php else:?>
                            <tr>
                                <td>There are no statistics to display!</td>            
                            </tr>
                            <?php endif;?>
                        </table>
                    </div>
                    
                    <div class="row-fluid">
                        <div class="box box-bordered box-color">
                            <div class="box-title">
                                <h3><i class="icon-reorder"></i>
                                    Admission Overview
                                </h3>
                            </div>
                            <div class="box-content ">  
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Programme</th>
                                            <th>1st Choice</th>
                                            <th>Application Fee</th>
                                            <th>Admitted</th>
                                            <th>Acceptance Fee</th>
                                            <th>School Fees</th>
                                        </tr>
                                    </thead>        
                                    <tfoot>       
                                        <tr>
                                            <th colspan="6">Total</th>
                                        </tr>
                                        <tr>
                                            <th><?php echo $totalRows_rsprog ?></th>
                                            <th><?php echo array_sum($first_choice) ?></th>
                                            <th><?php echo array_sum($app_fee) ?></th>
                                            <th><?php echo array_sum($admitted) ?></th>
                                            <th><?php echo array_sum($acceptance) ?></th>
                                            <th><?php echo array_sum($school_fees) ?></th>
                                        </tr>
                                    </tfoot>
                                    <tbody style="font-weight: normal">
                                        <?php for ($idx = 0; $idx < $totalRows_rsprog; $idx++, $row_rsprog = mysql_fetch_assoc($rsprog)) { ?>
                                        <tr>
                                            <td><?php echo $row_rsprog['progname'] ?></td>
                                            <td align="center">
                                                <a target="_blank" href="applist.php?pid=<?php echo $row_rsprog['progid'] ?>&sid=<?php echo $sid ?>&view=first">
                                                    <?php echo $first_choice[$row_rsprog['progid']] ?>
                                                </a>
                                            </td>
                                            <td align="center">
                                                <a target="_blank" href="applist.php?pid=<?php echo $row_rsprog['progid'] ?>&sid=<?php echo $sid ?>&view=app_fee">
                                                    <?php echo $app_fee[$row_rsprog['progid']] ?>
                                                </a>
                                            </td>
                                            <td align="center">
                                                <a target="_blank" href="applist.php?pid=<?php echo $row_rsprog['progid'] ?>&sid=<?php echo $sid ?>&view=admitted">
                                                    <?php echo $admitted[$row_rsprog['progid']] ?>
                                                </a>
                                            </td>
                                            <td align="center">
                                                <a target="_blank" href="applist.php?pid=<?php echo $row_rsprog['progid'] ?>&sid=<?php echo $sid ?>&view=accept_fee">
                                                    <?php echo $acceptance[$row_rsprog['progid']] ?>
                                                </a>
                                            </td>
                                            <td align="center">
                                                <a target="_blank" href="applist.php?pid=<?php echo $row_rsprog['progid'] ?>&sid=<?php echo $sid ?>&view=school_fee">
                                                    <?php echo $school_fees[$row_rsprog['progid']] ?>
                                                </a>
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
            <?php include INCPATH."/footer.php" ?>
        </div>
    </body>
</html>