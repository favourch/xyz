<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');



$auth_users = "1,2,20,21,22,23";
check_auth($auth_users, $site_root . '/admin');

$query_rssess = "SELECT * FROM `session`  ORDER BY sesid DESC";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

$sesname = $row_rssess['sesname'];

$query_level = sprintf("SELECT * FROM level_name");
$lvl = mysql_query($query_level, $tams) or die(mysql_error());
$row_level = mysql_fetch_assoc($lvl);


$query_prog = sprintf("SELECT  p.progname,  p.progid "
        . "FROM programme p  ");
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$level = 'all';
$filter = '';
$pid = 'all';

$ses = $row_rssess['sesid'];

if (isset($_GET['sid'])) {
    $ses = $_GET['sid'];
}

//if (isset($_GET['lvl'])) {
//    $level = $_GET['lvl'];
//
//    if ($level != 'all') {
//        $filter = 'AND st.level = ' . GetSQLValueString($level, 'int');
//    }
//}

if (isset($_GET['pid'])) {
    $pid = $_GET['pid'];

    if ($pid != 'all') {
        $filter .= ' AND s.progid1 = ' . GetSQLValueString($pid, 'int');
    }
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$query_part = "";
if(isset($_POST['MM_Search']) && $_POST['MM_Search'] == 'search'){
    $query_part = sprintf(" AND date_time BETWEEN DATE(%s) AND DATE(%s) ",
                        GetSQLValueString($_POST['from'], 'text'),
                        GetSQLValueString($_POST['to'], 'text'));
}

$query_ft_app = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM appfee_transactions WHERE charges IS NOT NULL AND sesid = %s %s ", 
        GetSQLValueString($ses, "int"), $query_part);
$ft_app_rs = mysql_query($query_ft_app, $tams) or die(mysql_error());
$row_app = mysql_fetch_assoc($ft_app_rs);

$query_ft_olv = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM olevelverifee_transactions WHERE charges IS NOT NULL AND sesid = %s %s ", 
        GetSQLValueString($ses, "int"),$query_part);
$ft_olv_rs = mysql_query($query_ft_olv, $tams) or die(mysql_error());
$row_olv = mysql_fetch_assoc($ft_olv_rs);

$query_ft_acc = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM accfee_transactions WHERE charges IS NOT NULL AND sesid = %s %s ", 
        GetSQLValueString($ses, "int"), $query_part);
$ft_acc_rs = mysql_query($query_ft_acc, $tams) or die(mysql_error());
$row_acc = mysql_fetch_assoc($ft_acc_rs);

$query_ft_reg = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM registration_transactions WHERE charges IS NOT NULL AND sesid = %s %s", 
        GetSQLValueString($ses, "int"), $query_part);
$ft_reg_rs = mysql_query($query_ft_reg, $tams) or die(mysql_error());
$row_reg = mysql_fetch_assoc($ft_reg_rs);

$query_ft_sch = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM schfee_transactions WHERE  charges IS NOT NULL AND sesid = %s %s", 
        GetSQLValueString($ses, "int"),$query_part);
$ft_sch_rs = mysql_query($query_ft_sch, $tams) or die(mysql_error());
$row_sch = mysql_fetch_assoc($ft_sch_rs);

$query_ft_clr = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM clearance_transactions WHERE charges IS NOT NULL AND sesid = %s %s", 
        GetSQLValueString($ses, "int"), $query_part);
$ft_clr_rs = mysql_query($query_ft_clr, $tams) or die(mysql_error());
$row_clr = mysql_fetch_assoc($ft_clr_rs);

// FT total for all sessions
$query_ft_app_all = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM appfee_transactions WHERE charges IS NOT NULL ");
$ft_app_all_rs = mysql_query($query_ft_app_all, $tams) or die(mysql_error());
$row_app_all = mysql_fetch_assoc($ft_app_all_rs);

$query_ft_olv_all = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM olevelverifee_transactions WHERE charges IS NOT NULL ");
$ft_olv_all_rs = mysql_query($query_ft_olv_all, $tams) or die(mysql_error());
$row_olv_all = mysql_fetch_assoc($ft_olv_all_rs);

$query_ft_acc_all = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM accfee_transactions WHERE charges IS NOT NULL ");
$ft_acc_all_rs = mysql_query($query_ft_acc_all, $tams) or die(mysql_error());
$row_acc_all = mysql_fetch_assoc($ft_acc_all_rs);

$query_ft_reg_all = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM registration_transactions WHERE charges IS NOT NULL ");
$ft_reg_all_rs = mysql_query($query_ft_reg_all, $tams) or die(mysql_error());
$row_reg_all = mysql_fetch_assoc($ft_reg_all_rs);

$query_ft_sch_all = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM schfee_transactions WHERE  charges IS NOT NULL ");
$ft_sch_all_rs = mysql_query($query_ft_sch_all, $tams) or die(mysql_error());
$row_sch_all = mysql_fetch_assoc($ft_sch_all_rs);

$query_ft_clr_all = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM clearance_transactions WHERE charges IS NOT NULL ");
$ft_clr_all_rs = mysql_query($query_ft_clr_all, $tams) or die(mysql_error());
$row_clr_all = mysql_fetch_assoc($ft_clr_all_rs);


//Cepep
$query_cepep_olv = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM tasueded_tams_cepep.olevelverifee_transactions WHERE charges IS NOT NULL AND sesid = %s %s", 
        GetSQLValueString($ses, "int"), $query_part);
$cepep_olv_rs = mysql_query($query_cepep_olv, $tams) or die(mysql_error());
$row_cepep_olv = mysql_fetch_assoc($cepep_olv_rs); 

$query_cepep_app = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM tasueded_tams_cepep.appfee_transactions WHERE charges IS NOT NULL AND sesid = %s %s", 
        GetSQLValueString($ses, "int"), $query_part);
$cepep_app_rs = mysql_query($query_cepep_app, $tams) or die(mysql_error());
$row_cepep_app = mysql_fetch_assoc($cepep_app_rs); 

$query_cepep_acc = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM tasueded_tams_cepep.accfee_transactions WHERE charges IS NOT NULL AND sesid = %s %s ", 
        GetSQLValueString($ses, "int"), $query_part);
$cepep_acc_rs = mysql_query($query_cepep_acc, $tams) or die(mysql_error());
$row_cepep_acc = mysql_fetch_assoc($cepep_acc_rs); 

$query_cepep_sch = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM tasueded_tams_cepep.schfee_transactions WHERE charges IS NOT NULL AND sesid = %s %s ", 
        GetSQLValueString($ses, "int"), $query_part);
$cepep_sch_rs = mysql_query($query_cepep_sch, $tams) or die(mysql_error());
$row_cepep_sch = mysql_fetch_assoc($cepep_sch_rs); 

$query_cepep_clr = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM tasueded_tams_cepep.clearance_transactions WHERE charges IS NOT NULL AND sesid = %s %s", 
        GetSQLValueString($ses, "int"), $query_part);
$cepep_clr_rs = mysql_query($query_cepep_clr, $tams) or die(mysql_error());
$row_cepep_clr = mysql_fetch_assoc($cepep_clr_rs);


//PT total for all classes

$query_cepep_olv_all = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM tasueded_tams_cepep.olevelverifee_transactions WHERE charges IS NOT NULL " );
$cepep_olv_all_rs = mysql_query($query_cepep_olv_all, $tams) or die(mysql_error());
$row_cepep_olv_all = mysql_fetch_assoc($cepep_olv_all_rs); 

$query_cepep_app_all = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM tasueded_tams_cepep.appfee_transactions WHERE charges IS NOT NULL ");
$cepep_app_all_rs = mysql_query($query_cepep_app_all, $tams) or die(mysql_error());
$row_cepep_app_all = mysql_fetch_assoc($cepep_app_all_rs); 

$query_cepep_acc_all = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM tasueded_tams_cepep.accfee_transactions WHERE charges IS NOT NULL ");
$cepep_acc_all_rs = mysql_query($query_cepep_acc_all, $tams) or die(mysql_error());
$row_cepep_acc_all = mysql_fetch_assoc($cepep_acc_all_rs); 

$query_cepep_sch_all = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM tasueded_tams_cepep.schfee_transactions WHERE charges IS NOT NULL ");
$cepep_sch_all_rs = mysql_query($query_cepep_sch_all, $tams) or die(mysql_error());
$row_cepep_sch_all = mysql_fetch_assoc($cepep_sch_all_rs); 

$query_cepep_clr_all = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM tasueded_tams_cepep.clearance_transactions WHERE charges IS NOT NULL ");
$cepep_clr_all_rs = mysql_query($query_cepep_clr_all, $tams) or die(mysql_error());
$row_cepep_clr_all = mysql_fetch_assoc($cepep_clr_all_rs);

//AIMS
$query_aims_app = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM tasueded_aims.application_transactions ap "
        . "JOIN tasueded_aims.session s ON ap.session_id = s.session_id "
        . "WHERE ap.charges IS NOT NULL AND s.sesid = %s %s " , 
        GetSQLValueString($ses, "int"), $query_part); 
$aims_app_rs = mysql_query($query_aims_app, $tams) or die(mysql_error()); 
$row_aims_app = mysql_fetch_assoc($aims_app_rs);

$query_aims_acc = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM tasueded_aims.acceptance_transactions ac "
         . "JOIN tasueded_aims.session s ON ac.session_id = s.session_id "
        . "WHERE ac.charges IS NOT NULL AND s.sesid = %s %s ", 
        GetSQLValueString($ses, "int"), $query_part);
$aims_acc_rs = mysql_query($query_aims_acc, $tams) or die(mysql_error()); 
$row_aims_acc = mysql_fetch_assoc($aims_acc_rs);

$query_aims_sch = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM tasueded_aims.schfees_transactions sc "
        . "JOIN tasueded_aims.session s ON sc.session_id = s.session_id "
        . "WHERE sc.charges IS NOT NULL AND s.sesid = %s %s ", 
        GetSQLValueString($ses, "int"), $query_part); 
$aims_sch_rs = mysql_query($query_aims_sch, $tams) or die(mysql_error()); 
$row_aims_sch = mysql_fetch_assoc($aims_sch_rs);


// AIMF for all sessions

$query_aims_app_all = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM tasueded_aims.application_transactions ap "
       
        . "WHERE ap.charges IS NOT NULL "); 
$aims_app_all_rs = mysql_query($query_aims_app_all, $tams) or die(mysql_error()); 
$row_aims_app_all = mysql_fetch_assoc($aims_app_all_rs);

$query_aims_acc_all = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM tasueded_aims.acceptance_transactions ac "
        
        . "WHERE ac.charges IS NOT NULL ");
$aims_acc_all_rs = mysql_query($query_aims_acc_all, $tams) or die(mysql_error()); 
$row_aims_acc_all = mysql_fetch_assoc($aims_acc_all_rs);

$query_aims_sch_all = sprintf("SELECT SUM(CONVERT(REPLACE(SUBSTRING(amt,4),',',''), DECIMAL)*charges) as total "
        . "FROM tasueded_aims.schfees_transactions sc "
       
        . "WHERE sc.charges IS NOT NULL "); 
$aims_sch_all_rs = mysql_query($query_aims_sch_all, $tams) or die(mysql_error()); 
$row_aims_sch_all = mysql_fetch_assoc($aims_sch_all_rs);


$name = 'Paid students';

if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}

// total
$ft_total = $row_olv['total'] + $row_app['total'] + $row_acc['total'] + $row_reg['total'] + $row_sch['total'] + $row_clr['total'];
$cepep_total = $row_cepep_olv['total'] + $row_cepep_app['total'] + $row_cepep_acc['total'] + $row_cepep_sch['total'] + $row_cepep_clr['total'];
$aims_total = $row_aims_app['total'] + $row_aims_acc['total'] + $row_aims_sch['total'];

// total for all sessions
$ft_total_all = $row_olv_all['total'] + $row_app_all['total'] + $row_acc_all['total'] + $row_reg_all['total'] + $row_sch_all['total'] + $row_clr_all['total'];
$cepep_total_all = $row_cepep_olv_all['total'] + $row_cepep_app_all['total'] + $row_cepep_acc_all['total'] + $row_cepep_sch_all['total'] + $row_cepep_clr_all['total'];
$aims_total_all = $row_aims_app_all['total'] + $row_aims_acc_all['total'] + $row_aims_sch_all['total'];
$total_charge_all = $ft_total_all+$cepep_total_all+$aims_total_all;

//total pay
$query_total_pay = sprintf("SELECT SUM(amount) as total "
        . "FROM imbil ");
$total_pay_rs = mysql_query($query_total_pay, $tams) or die(mysql_error());
$row_total_pay = mysql_fetch_assoc($total_pay_rs);


$balance = $total_charge_all - $row_total_pay['total']; 


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$query_part = "";
if(isset($_POST['MM_Search']) && $_POST['MM_Search'] == 'search'){
    $query_part = sprintf(" AND st.date_time BETWEEN DATE(%s) AND DATE(%s) ",
                        GetSQLValueString($_POST['from'], 'text'),
                        GetSQLValueString($_POST['to'], 'text'));
}

$page_title = "Tasued";
?>

<!doctype html>
<html ng-app="list">
    <?php include INCPATH . "/header.php" ?>
    <script>
        var app = angular.module('list', []);

        

        app.controller('pageCtrl', function ($scope) {

            
        });
    </script>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="pageCtrl">
        <?php include INCPATH . "/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH . "/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH . "/page_header.php" ?>

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-money"></i>
                                        0.5% Charges
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        
                                        <div class="span4">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Session</label>
                                                <div class="controls controls-row">
                                                    <select name='ses' onchange="sesfilt(this)">
                                                        <?php for (; $row_rssess != false; $row_rssess = mysql_fetch_assoc($rssess)) { ?>
                                                            <option value="<?php echo $row_rssess['sesid'] ?>" <?= ($ses == $row_rssess['sesid']) ? 'selected' : '' ?>><?php echo $row_rssess['sesname']; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <!--
                                        <div class="span4">
                                            <form action="<?= $editFormAction; ?>" method="post">
                                                <input type="hidden" name="MM_Search" value="search">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">Search by Date</label>
                                                    <div class="controls">
                                                        <div class="input-append input-prepend">
                                                            <input type="text" name="from" class="input-small datepick" placeholder="From" data-date-format="yyyy-mm-dd">
                                                            <input type="text" name="to" class="input-small datepick" placeholder="To" data-date-format="yyyy-mm-dd">
                                                            <button type="submit" class="btn ">Search</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                     -->
                                    
                                     <div class="span4">
                                         <div class="control-group">
                                           <table width="400" class="table table-striped table-condensed">
                                               <tr>
                                                   <td>
                                                      Total Charges = <?php echo number_format($total_charge_all,2); ?>
                                                   </td>
                                                   <td>
                                                       Total Paid = <?php echo number_format($row_total_pay['total'],2); ?>
                                                   </td>
                                                   <td>
                                                       Balance = <?php echo number_format($balance,2); ?>
                                                   </td>
                                               </tr>
                                               
                                           </table>
                                           </div>
                                        </div>
                                    </div> 
                                    <table width="670" class="table table-striped table-condensed">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Payment Heads</th>
                                                <th>Full Time</th>
                                                <th>CEPEP</th>
                                                <th>PG & Others</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td>OLevel Verification Fee</td>
                                                <td><?= number_format($row_olv['total'],2) ?></td>
                                                <td><?= number_format($row_cepep_olv['total'],2) ?></td>
                                                <td>-</td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td>Application Fee </td>
                                                <td><?= number_format($row_app['total'],2) ?></td>
                                                <td><?= number_format($row_cepep_app['total'],2) ?></td>
                                                <td><?= number_format($row_aims_app['total'],2) ?></td>
                                            </tr>
                                            <tr>
                                                <td>3</td>
                                                <td>Acceptance Fee </td>
                                                <td><?= number_format($row_acc['total'],2) ?></td>
                                                <td><?= number_format($row_cepep_acc['total'],2) ?></td>
                                                <td><?= number_format($row_aims_acc['total'],2) ?></td>
                                            </tr>
                                            <tr>
                                                <td>4</td>
                                                <td>Registration Fee </td>
                                                <td><?= number_format($row_reg['total'],2) ?></td>
                                                <td>-</td>
                                                <td>-</td>
                                            </tr>
                                            <tr>
                                                <td>5</td>
                                                <td>School Fee </td>
                                                <td><?= number_format($row_sch['total'],2) ?></td>
                                                <td><?= number_format($row_cepep_sch['total'],2) ?></td>
                                                <td><?= number_format($row_aims_sch['total'],2) ?></td>
                                            </tr>
                                            
                                            <tr>
                                                <td>6</td>
                                                <td>Clearance Fee </td>
                                                <td><?= number_format($row_clr['total'],2) ?></td>
                                                <td><?= number_format($row_cepep_clr['total'],2) ?></td>
                                                <td>-</td>
                                            </tr>
                                            
                                            <thead>
                                            <tr>
                                                <th>TOTAL</th>
                                                <th></th>
                                                <th><?= number_format($ft_total,2) ?></th>
                                                <th><?= number_format($cepep_total,2) ?></th>
                                                <th><?= number_format($aims_total,2) ?></th>
                                            </tr>
                                        </thead>
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                        <!--</div>-->
                    </div>
                </div>          
            </div>
            <?php include INCPATH . "/footer.php" ?>
    </body>
</html>