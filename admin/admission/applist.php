<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');



$auth_users = "1,20,21,22,23,24,28";
check_auth($auth_users, $site_root.'/admin');
        
$query_session = "SELECT sesid, sesname FROM session ORDER BY sesid DESC";
$session = mysql_query($query_session, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);
$totalRows_session = mysql_num_rows($session);


$query_pstd = '';

$pid = '';
$view = '';
$sid = '';

if (isset($_GET['pid'])) {
    $pid = $_GET['pid'];
}

if (isset($_GET['view'])) {
    $view = $_GET['view'];
}

if (isset($_GET['sid'])) {
    $sid = $_GET['sid'];
}

$query_proginfo = sprintf("SELECT * FROM programme WHERE progid = %s", GetSQLValueString($pid, "int"));
$proginfo = mysql_query($query_proginfo, $tams) or die(mysql_error());
$row_proginfo = mysql_fetch_assoc($proginfo);
$totalRows_proginfo = mysql_num_rows($proginfo);

$name = $row_proginfo['progname'] . ' ';

$query = sprintf("WHERE progid1 = %s "
                . "AND p.sesid = %s "
                . "AND p.formpayment = 'Yes' ", 
                GetSQLValueString($pid, "int"),
                GetSQLValueString($sid, "int"));


switch ($view) {
    case 'utme':
        $query_pstd = "SELECT pstdid, formnum, jambregid, fname, lname, mname, typename, "
                . "jambscore1, jambscore2, jambscore3, jambscore4, score "
                . "FROM prospective p "
                . "LEFT JOIN admissions a ON p.admid = a.admid "
                . "LEFT JOIN admission_type at ON a.typeid = at.typeid ";
        
        $query = sprintf("WHERE at.typeid=2 AND progid1 = %s "
                . "AND formpayment = 'Yes' AND formsubmit= 'Yes' "
                . "AND p.sesid = %s ", GetSQLValueString($pid, "int"), GetSQLValueString($sid, "int"));
        $name .= '(UTME Applicants)';
        $query_pstd .= $query;
        break;

    case 'de':
         $query_pstd = "SELECT pstdid, formnum, jambregid, fname, lname, mname, typename, "
                . "jambscore1, jambscore2, jambscore3, jambscore4, score "
                . "FROM prospective p "
                . "LEFT JOIN admissions a ON p.admid = a.admid "
                . "LEFT JOIN admission_type at ON a.typeid = at.typeid ";
        
        $query = sprintf("WHERE at.typeid=1 AND progid1 = %s "
                . "AND formpayment = 'Yes' "
                . "AND p.sesid = %s ", GetSQLValueString($pid, "int"), GetSQLValueString($sid, "int"));
        $name .= '(DE Applicants)';
        $query_pstd .= $query;
        break;

    case 'admitted':
        $query_pstd = "SELECT pstdid, formnum, jambregid, fname, lname, mname, typename, "
                . "jambscore1, jambscore2, jambscore3, jambscore4, score "
                . "FROM prospective p "
                . "LEFT JOIN admissions a ON p.admid = a.admid "
                . "LEFT JOIN admission_type at ON a.typeid = at.typeid ";
        
        $query = sprintf("WHERE progoffered = %s "
                . "AND adminstatus = 'Yes' "
                . "AND p.sesid = %s ", GetSQLValueString($pid, "int"), GetSQLValueString($sid, "int"));
        $name .= '(Admitted Applicants)';
        $query_pstd .= $query;
        break;

    case 'accept_fee':
        $query_pstd = "SELECT DISTINCT(pstdid), formnum, jambregid, fname, lname, mname, typename, "
                . "jambscore1, jambscore2, jambscore3, jambscore4, score "
                . "FROM prospective p "
                . "LEFT JOIN admissions a ON p.admid = a.admid "
                . "LEFT JOIN admission_type at ON a.typeid = at.typeid ";
        
        $query = sprintf("JOIN accfee_transactions s ON p.jambregid = s.can_no "
                . "WHERE s.status = 'APPROVED' "
                . "AND p.sesid = s.sesid "
                . "AND p.progoffered = %s "
                . "AND p.sesid = %s ", GetSQLValueString($pid, "int"), GetSQLValueString($sid, "int"));
        $name .= '(Acceptance Fees)';
        $query_pstd .= $query;
        break;

    case 'school_fee':
        $query_pstd = "SELECT DISTINCT(pstdid), formnum, jambregid, fname, lname, mname, typename, jambscore1, "
                    . "jambscore2, jambscore3, jambscore4, score "
                    . "FROM prospective p "
                    . "LEFT JOIN admissions a ON p.admid = a.admid "
                    . "LEFT JOIN admission_type at ON a.typeid = at.typeid ";
        
        $query = sprintf("JOIN schfee_transactions s ON p.jambregid = s.can_no "
                . "WHERE s.status = 'APPROVED' "
                . "AND p.sesid = s.sesid "
                . "AND p.progoffered = %s "
                . "AND p.sesid = %s ", GetSQLValueString($pid, "int"), GetSQLValueString($sid, "int"));
        $name .= '(School Fees)';
        $query_pstd .= $query;
        break;
    
      case 'reg_fee':
        $query_pstd = "SELECT DISTINCT(pstdid), formnum, p.jambregid, fname, lname, mname, typename, "
                . "jambscore1, jambscore2, jambscore3, jambscore4, score "
                . "FROM prospective p "
                . "LEFT JOIN admissions a ON p.admid = a.admid "
                . "LEFT JOIN admission_type at ON a.typeid = at.typeid ";
        
        $query = sprintf("JOIN schfee_transactions s ON p.jambregid = s.can_no "
                . "WHERE s.status = 'APPROVED' AND s.reg_fee = 'TRUE' "
                . "AND p.sesid = s.sesid "
                . "AND p.progoffered = %s "
                . "AND p.sesid = %s ", GetSQLValueString($pid, "int"), GetSQLValueString($sid, "int"));
        $name .= '(Registration Fees)';
        $query_pstd .= $query;
        break;
        
    case 'grn_file':
        $query_pstd = "SELECT DISTINCT(pstdid), p.formnum, p.jambregid, p.fname, p.lname, p.mname, typename, "
                . "jambscore1, jambscore2, jambscore3, jambscore4, score "
                . "FROM prospective p "
               // . "LEFT JOIN student s ON s.jambregid = p.jambregid "
                . "LEFT JOIN admissions a ON p.admid = a.admid "
                . "LEFT JOIN admission_type at ON a.typeid = at.typeid ";
        
        $query = sprintf("JOIN schfee_transactions st ON p.jambregid = st.can_no "
                . "JOIN student s ON s.jambregid = p.jambregid "
                . "WHERE st.status = 'APPROVED' "
                . "AND st.reg_fee = 'TRUE' "
                . "AND p.sesid = st.sesid "
                . "AND s.green_file = 'TRUE' "
                . "AND p.progoffered = %s "
                . "AND p.sesid = %s ", GetSQLValueString($pid, "int"), GetSQLValueString($sid, "int"));
        $name .= '(Green-File Submission)';
        $query_pstd .= $query;
        break;

    default :
        $query_pstd = sprintf("SELECT pstdid, formnum, jambregid, fname, lname, mname, typename, "
                . "jambscore1, jambscore2, jambscore3, jambscore4, score "
                . "FROM prospective "
                . "LEFT JOIN admissions a ON p.admid = a.admid "
                . "LEFT JOIN admission_type at ON a.typeid = at.typeid "
                . "WHERE progid1 = %s "
                . "AND p.sesid = %s", GetSQLValueString($pid, "int"), GetSQLValueString($sid, "int"));
}

$pstd = mysql_query($query_pstd, $tams) or die(mysql_error());
$row_pstd = mysql_fetch_assoc($pstd);
$totalRows_pstd = mysql_num_rows($pstd);

$query_regcount = sprintf("SELECT typename, regtype, count(distinct(pstdid)) as count "
        . "FROM prospective p "
        . "JOIN admissions a ON p.admid = a.admid "
        . "JOIN admission_type at ON a.typeid = at.typeid "
        . "{$query} "
        . "GROUP BY regtype, at.typeid");
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
                                    <?php echo $name?>
                                </h3>
                            </div>
                            <div class="box-content ">  
                                <table class="table table-bordered table-condensed table-striped">
                                    <thead>
                                        <tr>
                                            <th>S/N</th>
                                            <th>Jamb Reg. No.</th>
                                            <th>Form Number</th>
                                            <th>Name</th>
                                            <th>Admission Type</th>
                                            <th>JAMB Score</th>
                                            <th>Exam Score</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody style="font-weight: normal">
                                        <?php
                                            if ($totalRows_pstd > 0) :
                                                for ($idx = 0; $idx < $totalRows_pstd; $idx++, $row_pstd = mysql_fetch_assoc($pstd)) :
                                        ?>
                                        <tr>
                                            <td><?php echo $idx + 1 ?></td>
                                            <td align="center"><?php echo strtoupper($row_pstd['jambregid']) ?></td>
                                            <td align="center"><?php echo $row_pstd['formnum'] ?></td>
                                            <td><?php echo strtoupper("{$row_pstd['lname']} {$row_pstd['fname']}") ?></td>
                                            <td align="center"><?php echo $row_pstd['typename'] ?></td>
                                            <td align="center">
                                                <?php
                                                $score = 0;
                                                $score += (isset($row_pstd['jambscore1']) && $row_pstd['jambscore1'] != '') ?
                                                        $row_pstd['jambscore1'] : 0;
                                                $score += (isset($row_pstd['jambscore2']) && $row_pstd['jambscore2'] != '') ?
                                                        $row_pstd['jambscore2'] : 0;
                                                $score += (isset($row_pstd['jambscore3']) && $row_pstd['jambscore3'] != '') ?
                                                        $row_pstd['jambscore3'] : 0;
                                                $score += (isset($row_pstd['jambscore4']) && $row_pstd['jambscore4'] != '') ?
                                                        $row_pstd['jambscore4'] : 0;

                                                echo $score;
                                                ?>
                                            </td>
                                            <td align="center"><?php echo (isset($row_pstd['score'])) ? $row_pstd['score'] : '-' ?></td>
                                            <td>
                                                <a target="_blank" href="/<?= $site_root ?>/admission/viewform.php?stid=<?php echo $row_pstd['jambregid'] ?>">View Profile</a>
                                            </td>
                                        </tr>
                                        <?php 
                                                endfor;
                                            else :
                                        ?>
                                        <tr>
                                            <td colspan="8">There are no applicants to display!</td>
                                        </tr>
                                        <?php endif; ?>
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