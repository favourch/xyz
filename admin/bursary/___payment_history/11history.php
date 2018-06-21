<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../../../path.php');
$auth_users = "1, 20, 21, 23";
check_auth($auth_users, $site_root.'/admin');

$stdid = -1;
if (isset($_GET['stdid']) && $_GET['stdid'] != "") {
    $stdid = $_GET['stdid'];
}
    
//    Get Student details 
$query = sprintf("SELECT s.stdid, s.jambregid, s.fname, s.lname, s.level, s.mname, p.progname "
                . "FROM student s LEFT JOIN programme p ON p.progname = s.progid "
                . "WHERE stdid LIKE %s ", 
                GetSQLValueString(trim($stdid), 'text'));
$Rs_search = mysql_query($query) or die(mysql_error());
$row_search = mysql_fetch_assoc($Rs_search);
$num_row_search = mysql_num_rows($Rs_search);

//Get application fee transaction history
$query_app_fee = sprintf("SELECT a.*, s.sesname "
                        . "FROM appfee_transactions a LEFT JOIN session s ON s.sesid = a.sesid "
                        . "WHERE can_no = %s OR can_no = %s "
                        . "AND a.status = 'APPROVED' ",  
                        GetSQLValueString($row_search['jambregid'], 'text'), 
                        GetSQLValueString($row_search['stdid'], 'text'));
$Rs_app_fee = mysql_query($query_app_fee) or die(mysql_error());
$row_app_fee = mysql_fetch_assoc($Rs_app_fee);
$num_row_app_fee = mysql_num_rows($Rs_app_fee);


//Get acceptance fee transaction history
$query_acc_fee = sprintf("SELECT a.*, s.sesname "
                        . "FROM accfee_transactions a "
                        . "LEFT JOIN session s ON s.sesid = a.sesid "
                        . "WHERE can_no = %s "
                        . "OR can_no = %s "
                        . "AND a.status = 'APPROVED' ",  
                        GetSQLValueString($row_search['jambregid'], 'text'), 
                        GetSQLValueString($row_search['stdid'], 'text'));
$Rs_acc_fee = mysql_query($query_acc_fee) or die(mysql_error());
$row_acc_fee = mysql_fetch_assoc($Rs_acc_fee);
$num_row_acc_fee = mysql_num_rows($Rs_acc_fee);

//Get school fee transaction history
$query_sch_fee = sprintf("SELECT a.*, s.sesname "
                        . "FROM schfee_transactions a "
                        . "JOIN session s ON s.sesid = a.sesid "
                        . "WHERE can_no = %s "
                        . "OR can_no = %s "
                        . "OR matric_no = %s "
                        . "OR matric_no = %s"
                        . "AND a.status = 'APPROVED' ", 
                        GetSQLValueString($row_search['jambregid'], 'text'), 
                        GetSQLValueString($row_search['stdid'], 'text'),
                        GetSQLValueString($row_search['jambregid'], 'text'), 
                        GetSQLValueString($row_search['stdid'], 'text'));
$Rs_sch_fee = mysql_query($query_sch_fee) or die(mysql_error());
$row_sch_fee = mysql_fetch_assoc($Rs_sch_fee);
$num_row_sch_fee = mysql_num_rows($Rs_sch_fee);


//Get reparation fee transaction history
$query_rep_fee = sprintf("SELECT a.*, s.sesname "
                        . "FROM reparation_transactions a "
                        . "LEFT JOIN session s ON s.sesid = a.sesid "
                        . "WHERE can_no = %s "
                        . "OR can_no = %s "
                        . "OR matric_no = %s "
                        . "OR matric_no = %s"
                        . "AND a.status = 'APPROVED' ", 
                        GetSQLValueString($row_search['jambregid'], 'text'), 
                        GetSQLValueString($row_search['stdid'], 'text'),
                        GetSQLValueString($row_search['jambregid'], 'text'), 
                        GetSQLValueString($row_search['stdid'], 'text'));
$Rs_rep_fee = mysql_query($query_rep_fee) or die(mysql_error());
$row_rep_fee = mysql_fetch_assoc($Rs_rep_fee);
$num_row_rep_fee = mysql_num_rows($Rs_rep_fee);


//Get clearance fee transaction history
$query_clr_fee = sprintf("SELECT a.*, s.sesname "
                        . "FROM clearance_transactions a "
                        . "LEFT JOIN session s ON s.sesid = a.sesid "
                        . "WHERE can_no = %s "
                        . "OR can_no = %s "
                        . "OR matric_no = %s "
                        . "OR matric_no = %s"
                        . "AND a.status = 'APPROVED' ", 
                        GetSQLValueString($row_search['jambregid'], 'text'),
                        GetSQLValueString($row_search['stdid'], 'text'), 
                        GetSQLValueString($row_search['jambregid'], 'text'), 
                        GetSQLValueString($row_search['stdid'], 'text'));
$Rs_clr_fee = mysql_query($query_clr_fee) or die(mysql_error());
$row_clr_fee = mysql_fetch_assoc($Rs_clr_fee);
$num_row_clr_fee = mysql_num_rows($Rs_clr_fee);

?>
<!doctype html>

<html>
<?php include INCPATH . "/header.php" ?>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
<?php include INCPATH . "/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
<?php include INCPATH . "/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
    <?php include INCPATH . "/page_header.php" ?>
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-color box-bordered ">
                                <div class="box-title">
                                    <h3>
                                        <i class="icon-th"></i> <?= $stdid?> Payment History
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="well well-small">
                                        <b> Matric No. : </b> <?= $row_search['stdid']?><br/>
                                        <b> Jamb Reg ID : </b> <?= $row_search['jambregid']?> <br/>
                                        <b> Full Name </b> <?= strtoupper($row_search['lname']) . ", ".strtolower( $row_search['fname'] . " " . $row_search['mname']) ?> <br/>
                                    </div>
                                    <?php if($num_row_app_fee > 0){?>
                                    <h4>Application Fee</h4>
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <table class="table table-bordered table-condensed tabe-striped">
                                                <thead>
                                                    <tr>
                                                        <th>S/n</th>
                                                        <th>Student ID</th>
                                                        <th>Reference </th>
                                                        <th>Session </th>
                                                        <th>Amount </th>
                                                        <th>Date Time</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if($num_row_app_fee > 0){?>
                                                    <?php $i=1; do {?>
                                                    <tr>
                                                        <td><?= $i++; ?></td>
                                                        <td><?= $row_app_fee['can_no']?></td>
                                                        <td><?= $row_app_fee['reference']?></td>
                                                        <td><?= $row_app_fee['sesname']?></td>
                                                        <td><?= $row_app_fee['amt']?></td>
                                                        <td><?= $row_app_fee['date_time']?></td>
                                                        
                                                    </tr>
                                                    <?php } while($row_app_fee = mysql_fetch_assoc($Rs_app_fee))?>
                                                    <?php } else {?>
                                                    <tr>
                                                        <td colspan="6"><div class="alert alert-warning"> No Payment Record</div></td>
                                                    </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    
                                    <?php if($num_row_acc_fee > 0){?>
                                    <h4>Acceptance Fee</h4>
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <table class="table table-bordered table-condensed tabe-striped">
                                                <thead>
                                                    <tr>
                                                        <th>S/n</th>
                                                        <th>Student ID</th>
                                                        <th>Reference </th>
                                                        <th>Session </th>
                                                        <th>Amount </th>
                                                        <th>Date Time</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if($num_row_acc_fee > 0){?>
                                                    <?php $i=1; do {?>
                                                    <tr>
                                                        <td><?= $i++; ?></td>
                                                        <td><?= $row_acc_fee['can_no']?></td>
                                                        <td><?= $row_acc_fee['reference']?></td>
                                                        <td><?= $row_acc_fee['sesname']?></td>
                                                        <td><?= $row_acc_fee['amt']?></td>
                                                        <td><?= $row_acc_fee['date_time']?></td>
                                                    </tr>
                                                    <?php } while($row_acc_fee = mysql_fetch_assoc($Rs_acc_fee))?>
                                                    <?php } else {?>
                                                    <tr>
                                                        <td colspan="6"><div class="alert alert-warning"> No Payment Record</div></td>
                                                    </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    
                                    <?php if($num_row_sch_fee > 0){?>
                                    <h4>School Fee</h4>
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <table class="table table-bordered table-condensed tabe-striped">
                                                <thead>
                                                    <tr>
                                                        <th>S/n</th>
                                                        <th>Jambreg ID</th>
                                                        <th>Student ID</th>
                                                        <th>Reference </th>
                                                        <th>Session </th>
                                                        <th>Amount </th>
                                                        <th>Date Time</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if($num_row_sch_fee > 0){?>
                                                    <?php $i=1; do {?>
                                                    <tr>
                                                        <td><?= $i++; ?></td>
                                                        <td><?= $row_sch_fee['can_no']?></td>
                                                        <td><?= $row_sch_fee['matric_no']?></td>
                                                        <td><?= $row_sch_fee['reference']?></td>
                                                        <td><?= $row_sch_fee['sesname']?></td>
                                                        <td><?= $row_sch_fee['amt']?></td>
                                                        <td><?= $row_sch_fee['date_time']?></td>
                                                    </tr>
                                                    <?php } while($row_sch_fee = mysql_fetch_assoc($Rs_sch_fee))?>
                                                    <?php } else {?>
                                                    <tr>
                                                        <td colspan="7"><div class="alert alert-warning"> No Payment Record</div></td>
                                                    </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    
                                    <?php if($num_row_rep_fee > 0){?>
                                    <h4>Reparation Fee</h4>
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <table class="table table-bordered table-condensed tabe-striped">
                                                <thead>
                                                    <tr>
                                                        <th>S/n</th>
                                                        <th>Jambreg ID</th>
                                                        <th>Student ID</th>
                                                        <th>Reference </th>
                                                        <th>Session </th>
                                                        <th>Amount </th>
                                                        <th>Date Time</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if($num_row_rep_fee > 0){?>
                                                    <?php $i=1; do {?>
                                                    <tr>
                                                        <td><?= $i++; ?></td>
                                                        <td><?= $row_rep_fee['can_no']?></td>
                                                        <td><?= $row_rep_fee['matric_no']?></td>
                                                        <td><?= $row_rep_fee['reference']?></td>
                                                        <td><?= $row_rep_fee['sesname']?></td>
                                                        <td><?= $row_rep_fee['amt']?></td>
                                                        <td><?= $row_rep_fee['date_time']?></td>
                                                    </tr>
                                                    <?php } while($row_rep_fee = mysql_fetch_assoc($Rs_rep_fee))?>
                                                    <?php } else {?>
                                                    <tr>
                                                        <td colspan="7"><div class="alert alert-warning"> No Payment Record</div></td>
                                                    </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    
                                    <?php if($num_row_clr_fee > 0){?>
                                    <h4>Clearance Fee</h4>
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <table class="table table-bordered table-condensed tabe-striped">
                                                <thead>
                                                    <tr>
                                                        <th>S/n</th>
                                                        <th>Jambreg ID</th>
                                                        <th>Student ID</th>
                                                        <th>Reference </th>
                                                        <th>Session </th>
                                                        <th>Amount </th>
                                                        <th>Date Time</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if($num_row_clr_fee > 0){?>
                                                    <?php $i=1; do {?>
                                                    <tr>
                                                        <td><?= $i++; ?></td>
                                                        <td><?= $row_clr_fee['can_no']?></td>
                                                        <td><?= $row_clr_fee['matric_no']?></td>
                                                        <td><?= $row_clr_fee['reference']?></td>
                                                        <td><?= $row_clr_fee['sesname']?></td>
                                                        <td><?= $row_clr_fee['amt']?></td>
                                                        <td><?= $row_clr_fee['date_time']?></td>
                                                    </tr>
                                                    <?php } while($row_clr_fee = mysql_fetch_assoc($Rs_clr_fee))?>
                                                    <?php } else {?>
                                                    <tr>
                                                        <td colspan="7"><div class="alert alert-warning"> No Payment Record</div></td>
                                                    </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
<?php include INCPATH . "/footer.php" ?>
    </body>
</html>

