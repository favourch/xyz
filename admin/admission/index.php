<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "1,20,21,22,23,24,27,28";
check_auth($auth_users, $site_root.'/admin');
        
        mysql_query("SET SQL_BIG_SELECTS=1", $tams);
        
$query_session = "SELECT sesid, sesname FROM session ORDER BY sesid DESC";
$session = mysql_query($query_session, $tams) or die(mysql_error());
$totalRows_session = mysql_num_rows($session);

$sid = $_SESSION['admid'] == NULL? $_SESSION['sesid']: $_SESSION['admid'];
$batchid = 'all';
$typeid = 'all';

$filter = '';

if (isset($_GET['sid'])) {
    $sid = $_GET['sid'];
}

if (isset($_GET['bid'])) {
    $batchid = $_GET['bid'];
    if($batchid != 'all' && is_numeric($batchid))
        $filter .= 'AND p.appbatch ='.  GetSQLValueString($batchid, "text");
}

if (isset($_GET['aid'])) {
    $typeid = $_GET['aid'];
    if($typeid != 'all' && is_numeric($typeid))
        $filter .= ' AND a.admid ='.  GetSQLValueString($typeid, "text");
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

$first_choice = $second_choice = $admitted = $acceptance = $school_fees = $reg_fees = $grn_files = $programmes;

$query_admit = sprintf("SELECT progoffered, count(distinct(jambregid)) as count "
        . "FROM prospective p "
        . "JOIN admissions a ON p.admid = a.admid "
        . "WHERE a.sesid = p.sesid "
        . "AND a.sesid = %s " 
        . "AND adminstatus = 'Yes' %s "
        . "GROUP BY progoffered", 
        GetSQLValueString($sid, "int"), 
        GetSQLValueString($filter, "defined", $filter));
$admit = mysql_query($query_admit, $tams) or die(mysql_error());
$row_admit = mysql_fetch_assoc($admit);
$totalRows_admit = mysql_num_rows($admit);

for ($idx = 0; $idx < $totalRows_admit; $idx++, $row_admit = mysql_fetch_assoc($admit)) {
    if (isset($admitted[$row_admit['progoffered']])) {
        $admitted[$row_admit['progoffered']] = $row_admit['count'];
    }
}

// Applicants::utme choice
$query_first = sprintf("SELECT progid1, count(distinct(jambregid)) as count "
        . "FROM prospective p "
        . "JOIN admissions a ON p.admid = a.admid "
        . "JOIN admission_type adt ON adt.typeid = a.typeid "
        . "JOIN appfee_transactions at ON p.jambregid = at.can_no "
        . "WHERE at.percentPaid = 100 "
        . "AND at.status = 'APPROVED' "
        . "AND a.sesid = %s "
        . "AND adt.typeid = 2 "
        . "AND progid1 IS NOT NULL "
        . "AND p.formsubmit = 'Yes' "
        . "AND (p.jambscore1+p.jambscore2+p.jambscore3+p.jambscore4)>179 "
        . "%s "
        . "GROUP BY progid1 "
        . "HAVING progid1 IS NOT NULL", 
         GetSQLValueString($sid, "int"), 
         GetSQLValueString($filter, "defined", $filter));
$first = mysql_query($query_first, $tams) or die(mysql_error());
$row_first = mysql_fetch_assoc($first);
$totalRows_first = mysql_num_rows($first);

for ($idx = 0; $idx < $totalRows_first; $idx++, $row_first = mysql_fetch_assoc($first)) {
    if (isset($first_choice[$row_first['progid1']])) {
        $first_choice[$row_first['progid1']] = $row_first['count'];
    }
}

// Applicants::de choice
$query_application = sprintf("SELECT progid1, at.status, count(distinct(jambregid)) as count "
        . "FROM prospective p "
        . "JOIN admissions a ON p.admid = a.admid "
        . "JOIN admission_type adt ON adt.typeid = a.typeid "
        . "JOIN appfee_transactions at ON p.jambregid = at.can_no "
        . "WHERE at.percentPaid = 100 "
        . "AND at.status = 'APPROVED' "
        . "AND a.sesid = %s "
        . "AND adt.typeid = 1 "
        . "AND progid1 IS NOT NULL "
        . "AND p.formsubmit = 'Yes' "
        . "%s "
        . "GROUP BY progid1 "
        . "HAVING progid1 IS NOT NULL", 
        GetSQLValueString($sid, "int"), 
        GetSQLValueString($filter, "defined", $filter));
$application = mysql_query($query_application, $tams) or die(mysql_error());
$row_application = mysql_fetch_assoc($application);
$totalRows_application = mysql_num_rows($application);

for ($idx = 0; $idx < $totalRows_application; $idx++, $row_application = mysql_fetch_assoc($application)) {
    if (isset($second_choice[$row_application['progid1']])) {
        $second_choice[$row_application['progid1']] = $row_application['count'];
    }
}

$query_accept = sprintf("SELECT progoffered, at.status, count(distinct(jambregid)) as count "
        . "FROM prospective p "
        . "JOIN admissions a ON p.admid = a.admid "
        . "JOIN accfee_transactions at ON p.jambregid = at.can_no AND a.sesid = at.sesid "
        . "WHERE at.status = 'APPROVED' "
        . "AND adminstatus = 'Yes' "
        . "AND a.sesid = %s "
        . "%s "
        . "GROUP BY progoffered", 
        GetSQLValueString($sid, "int"), 
        GetSQLValueString($filter, "defined", $filter));
$accept = mysql_query($query_accept, $tams) or die(mysql_error());
$row_accept = mysql_fetch_assoc($accept);
$totalRows_accept = mysql_num_rows($accept);

for ($idx = 0; $idx < $totalRows_accept; $idx++, $row_accept = mysql_fetch_assoc($accept)) {
    if (isset($acceptance[$row_accept['progoffered']])) {
        $acceptance[$row_accept['progoffered']] = $row_accept['count'];
    }
}

$query_schfee = sprintf("SELECT progoffered, st.status, count(distinct(jambregid)) as count "
        . "FROM prospective p "
        . "JOIN admissions a ON p.admid = a.admid "
        . "JOIN schfee_transactions st ON p.jambregid = st.can_no "
        . "JOIN payschedule ps ON st.scheduleid = ps.scheduleid AND a.sesid = ps.sesid "
        . "WHERE st.status = 'APPROVED' "
        . "AND a.sesid = %s "
        . "%s "
        . "GROUP BY progoffered", 
        GetSQLValueString($sid, "int"), 
        GetSQLValueString($filter, "defined", $filter));
$schfee = mysql_query($query_schfee, $tams) or die(mysql_error());
$row_schfee = mysql_fetch_assoc($schfee);
$totalRows_schfee = mysql_num_rows($schfee);

for ($idx = 0; $idx < $totalRows_schfee; $idx++, $row_schfee = mysql_fetch_assoc($schfee)) {
    if (isset($school_fees[$row_schfee['progoffered']])) {
        $school_fees[$row_schfee['progoffered']] = $row_schfee['count'];
    }
}

$query_regfee = sprintf("SELECT progoffered, st.status, count(distinct(jambregid)) as count "
        . "FROM prospective p "
        . "JOIN admissions a ON p.admid = a.admid "
        . "JOIN registration_transactions st ON p.jambregid = st.can_no "
     //   . "JOIN payschedule ps ON st.scheduleid = ps.scheduleid AND a.sesid = ps.sesid "
        . "WHERE st.status = 'APPROVED' AND a.sesid = st.sesid "
        . "AND a.sesid = %s "
        . "%s "
        . "GROUP BY progoffered", 
        GetSQLValueString($sid, "int"), 
        GetSQLValueString($filter, "defined", $filter));
$regfee = mysql_query($query_regfee, $tams) or die(mysql_error());
$row_regfee = mysql_fetch_assoc($regfee);
$totalRows_regfee = mysql_num_rows($regfee);

for ($idx = 0; $idx < $totalRows_regfee; $idx++, $row_regfee = mysql_fetch_assoc($regfee)) {
    if (isset($reg_fees[$row_regfee['progoffered']])) {
        $reg_fees[$row_regfee['progoffered']] = $row_regfee['count'];
    }
}


 $query_grnfile = sprintf("SELECT p.progoffered, count(distinct(p.jambregid)) as count "
        . "FROM prospective p "
        . "JOIN admissions a ON p.admid = a.admid "
       // . "JOIN schfee_transactions st ON p.jambregid = st.can_no "
      //  . "JOIN payschedule ps ON st.scheduleid = ps.scheduleid AND a.sesid = ps.sesid "
        . "JOIN student s ON s.jambregid = p.jambregid "
      //  . "WHERE st.status = 'APPROVED' AND reg_fee = 'TRUE' "
        . "WHERE s.green_file = 'TRUE' AND a.sesid = %s "
        . "%s "
        . "GROUP BY progoffered", 
        GetSQLValueString($sid, "int"), 
        GetSQLValueString($filter, "defined", $filter));
$grnfile = mysql_query($query_grnfile, $tams) or die(mysql_error());
$row_grnfile = mysql_fetch_assoc($grnfile);
$totalRows_grnfile = mysql_num_rows($grnfile); 

for ($idx = 0; $idx < $totalRows_grnfile; $idx++, $row_grnfile = mysql_fetch_assoc($grnfile)) {
    if (isset($grn_files[$row_grnfile['progoffered']])) {
        $grn_files[$row_grnfile['progoffered']] = $row_grnfile['count'];
    }
}


$query_regcount = sprintf("SELECT p.regtypeid, typeid, count(distinct(jambregid)) as count "
        . "FROM prospective p "
        . "JOIN admissions a ON p.admid = a.admid AND progid1 IS NOT NULL "
        . "JOIN appfee_transactions af ON p.jambregid = af.can_no "
        . "JOIN registration_type rt ON p.regtypeid = rt.regtypeid "
        . "WHERE af.status = 'APPROVED' "
        . "AND af.percentPaid = 100 "
        . "AND a.sesid = %s "
        . "%s "
        . "GROUP BY p.regtypeid, a.typeid", 
        GetSQLValueString($sid, "int"), 
        GetSQLValueString($filter, "defined", $filter)); 
$regcount = mysql_query($query_regcount, $tams) or die(mysql_error());
$totalRows_regcount = mysql_num_rows($regcount);

$stud_count = [];
$admtypes = [];

for(;$row_regcount = mysql_fetch_assoc($regcount);) {    
    $stud_count[$row_regcount['regtypeid']][$row_regcount['typeid']] = $row_regcount['count'];
}

$query_regtypes = "SELECT regtypeid, displayname "
        . "FROM registration_type"; 
$regtypes = mysql_query($query_regtypes, $tams) or die(mysql_error());
$totalRows_regtypes = mysql_num_rows($regtypes);

$total_value = [];

$query_appbatch = sprintf("SELECT appbatchid, batchname "
        . "FROM application_batch b "
        . "JOIN admissions a ON b.admid = a.admid "
        . "WHERE a.sesid = %s "
        . "AND a.admid = %s "
        . "ORDER BY appbatchid DESC",
        GetSQLValueString($sid, text),
        GetSQLValueString($typeid, text));
$appbatch = mysql_query($query_appbatch, $tams) or die(mysql_error());
$totalRows_appbatch = mysql_num_rows($appbatch);

$query_admtype = sprintf("SELECT admid, a.typeid, typename "
        . "FROM admissions a "
        . "JOIN admission_type at ON a.typeid = at.typeid "
        . "WHERE a.sesid = %s", 
        GetSQLValueString($sid, text));
$admtype = mysql_query($query_admtype, $tams) or die(mysql_error());
$totalRows_admtype = mysql_num_rows($admtype);
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
                    <form method="post" action="printapplist.php">
                        <div class="span4">
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
                        <div class="span4">
                            Select Type 
                            <select onchange="admfilt(this)">
                                <option value="all">All</option>
                                <?php for (; $row_admtype = mysql_fetch_assoc($admtype);): 
                                        $admtypes[$row_admtype['typeid']] = $row_admtype['typename'];
                                ?>
                                <option value="<?php echo $row_admtype['admid'] ?>" 
                                    <?php echo $typeid == $row_admtype['admid']? 'selected': ''?>>
                                    <?php echo $row_admtype['typename'] ?>
                                </option>
                                <?php endfor;?>
                            </select>   
                        </div>
                        <div class="span4">
                            Select Batch 
                            <select onchange="batchfilt(this)">
                                <option value="all">All</option>
                                <?php for (; $row_appbatch = mysql_fetch_assoc($appbatch);): ?>
                                <option value="<?php echo $row_appbatch['appbatchid'] ?>" 
                                    <?php echo $batchid == $row_appbatch['appbatchid']? 'selected': ''?>>
                                    <?php echo $row_appbatch['batchname'] ?>
                                </option>
                                <?php endfor;?>
                            </select>   
                        </div>
                         <div class="span3">
	                        	<button type="submit">Download Admission List</button> 
	                        </div>
                        </form>
                    </div>
                    
                    <div class="row-fluid">
                        <table style="width:650px; font-weight: normal;" class="table table-striped table-condensed">
                            <tr>
                                <td></td>
                                <?php 
                                    foreach($admtypes as $typeid => $typename) : 
                                        $total_values[$typeid] = 0;
                                ?>
                                <td><?php echo $typename;?></td>
                                <?php endforeach; ?>
                                <td>TOTAL</td>            
                            </tr>
                            
                            <?php 
                                if($totalRows_regtypes > 0) :
                                    $total = 0;
                                
                                    for(; $row_regtypes = mysql_fetch_assoc($regtypes);) :                                            
                            ?>
                            <tr>
                                <td><?php echo $row_regtypes['displayname'];?></td>
                                <?php foreach($admtypes as $typeid => $typename) : ?>
                                <td>
                                    <?php 
                                        $value = $stud_count[$row_regtypes['regtypeid']][$typeid];
                                        $total += $value;                                        
                                        $total_value[$typeid] += $value;
                                        
                                        if (!is_numeric($value))
                                            $value = '-';
                                                                                
                                        echo $value;
                                    ?>
                                </td>
                                <?php endforeach; ?>
                                <td>
                                    <?php 
                                        if ($total == 0)
                                            $total = '-';
                                        
                                        echo $total;
                                        $total = 0;
                                    ?>
                                </td>            
                            </tr>
                            <?php endfor;?>

                            <tr>
                                <td>Total</td>
                                <?php
                                    foreach ($admtypes as $typeid => $typename) :
                                ?>
                                <td>
                                    <?php
                                        $value = $total_value[$typeid];
                                        $total += $value;  
                                        if (!is_numeric($value) || $value == 0)
                                            $value = '-';
                                        echo $value;
                                    ?>
                                </td>
                                <?php endforeach; ?>
                                <td>
                                    <?php  
                                        if ($total == 0)
                                            $total = '-'; 
                                        
                                        echo $total;
                                    ?>
                                </td>            
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
                                            <th>UTME</th>
                                            <th>DE</th>
                                            <th>Admitted</th>
                                            <th>Acceptance Fee</th>
                                            <th>School Fees</th>                                            
                                            <th>Reg. Fees</th>
                                            <th>Green Files</th>
                                        </tr>
                                    </thead>        
                                    <tfoot>       
                                        <tr>
                                            <th colspan="8">Total</th>
                                        </tr>
                                        <tr>
                                            <th><?php echo $totalRows_rsprog ?></th>
                                            <th><?php echo array_sum($first_choice) ?></th>
                                            <th><?php echo array_sum($second_choice) ?></th>
                                            <th><?php echo array_sum($admitted) ?></th>
                                            <th><?php echo array_sum($acceptance) ?></th>
                                            <th><?php echo array_sum($school_fees) ?></th>                                            
                                            <th><?php echo array_sum($reg_fees) ?></th>
                                            <th><?php echo array_sum($grn_files) ?></th>
                                        </tr>
                                    </tfoot>
                                    <tbody style="font-weight: normal">
                                        <?php for ($idx = 0; $idx < $totalRows_rsprog; $idx++, $row_rsprog = mysql_fetch_assoc($rsprog)) { ?>
                                        <tr>
                                            <td><?php echo $row_rsprog['progname'] ?></td>
                                            <td align="center">
                                                <a target="_blank" href="applist.php?pid=<?php echo $row_rsprog['progid'] ?>&sid=<?php echo $sid ?>&view=utme">
                                                    <?php echo $first_choice[$row_rsprog['progid']] ?>
                                                </a>
                                            </td>
                                            <td align="center">
                                                <a target="_blank" href="applist.php?pid=<?php echo $row_rsprog['progid'] ?>&sid=<?php echo $sid ?>&view=de">
                                                    <?php echo $second_choice[$row_rsprog['progid']] ?>
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
                                            <td align="center">
                                                <a target="_blank" href="applist.php?pid=<?php echo $row_rsprog['progid'] ?>&sid=<?php echo $sid ?>&view=reg_fee">
                                                    <?php echo $reg_fees[$row_rsprog['progid']] ?>
                                                </a>
                                            </td>
                                            <td align="center">
                                                <a target="_blank" href="applist.php?pid=<?php echo $row_rsprog['progid'] ?>&sid=<?php echo $sid ?>&view=grn_file">
                                                    <?php echo $grn_files[$row_rsprog['progid']] ?>
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