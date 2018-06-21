<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../../../path.php');

$auth_users = "1, 20,21,23";
check_auth($auth_users, $site_root);

$stdid = -1;
if (isset($_GET['stdid']) ) {
    $stdid = $_GET['stdid'];
}

$usertype = -1;
if (isset($_GET['utype']) ) {
    //    Get Student details 
    $query = sprintf("SELECT '-' AS stdid, s.jambregid, s.fname, s.lname, '-' AS level, s.mname, p.progname "
            . "FROM prospective s LEFT JOIN programme p ON p.progid = s.progid1 "
            . "WHERE s.jambregid LIKE %s ", GetSQLValueString(trim($stdid), 'text'));
    $Rs_search = mysql_query($query) or die(mysql_error());
    $row_search = mysql_fetch_assoc($Rs_search);
    $num_row_search = mysql_num_rows($Rs_search);
}else{
    
    //    Get Student details 
    $query = sprintf("SELECT s.stdid, s.jambregid, s.fname, s.lname, s.level, s.mname, p.progname "
            . "FROM student s LEFT JOIN programme p ON p.progid = s.progid "
            . "WHERE stdid LIKE %s ", GetSQLValueString(trim($stdid), 'text'));
    $Rs_search = mysql_query($query) or die(mysql_error());
    $row_search = mysql_fetch_assoc($Rs_search);
    $num_row_search = mysql_num_rows($Rs_search);
}


    


//Get application fee transaction history
 $query_app_fee = sprintf("SELECT a.*, s.sesname "
                        . "FROM appfee_transactions a, session s WHERE s.sesid = a.sesid "
                        . "AND (can_no = %s AND a.status = 'APPROVED') OR (matric_no = %s "
                        . "AND a.status = 'APPROVED') ",  
                        GetSQLValueString($row_search['jambregid'], 'text'), 
                        GetSQLValueString($row_search['stdid'], 'text'));
$Rs_app_fee = mysql_query($query_app_fee) or die(mysql_error());
$row_app_fee = mysql_fetch_assoc($Rs_app_fee);
$num_row_app_fee = mysql_num_rows($Rs_app_fee);


//Get acceptance fee transaction history
 $query_acc_fee = sprintf("SELECT a.*, s.sesname "
                        . "FROM accfee_transactions a "
                        . "LEFT JOIN session s ON s.sesid = a.sesid "
                        . "WHERE (can_no = %s "
                        . "OR can_no = %s) "
                        . "AND a.status = 'APPROVED' ",  
                        GetSQLValueString($row_search['jambregid'], 'text'), 
                        GetSQLValueString($row_search['stdid'], 'text')); 
$Rs_acc_fee = mysql_query($query_acc_fee) or die(mysql_error());
$row_acc_fee = mysql_fetch_assoc($Rs_acc_fee); 
$num_row_acc_fee = mysql_num_rows($Rs_acc_fee);


//Get Olevel Verification fee transaction history
$query_veri_fee = sprintf("SELECT a.* "
                        . "FROM olevelverifee_transactions a "
                     //   . "LEFT JOIN session s ON s.sesid = a.sesid "
                        . "WHERE can_no = %s "
                        . "OR can_no = %s "
                        . "AND a.status = 'APPROVED' ",  
                        GetSQLValueString($row_search['jambregid'], 'text'), 
                        GetSQLValueString($row_search['stdid'], 'text'));
$Rs_veri_fee = mysql_query($query_veri_fee) or die(mysql_error());
$row_veri_fee = mysql_fetch_assoc($Rs_veri_fee);
$num_row_veri_fee = mysql_num_rows($Rs_veri_fee);

//Get school fee transaction history
$query_sch_fee = sprintf("SELECT a.*, s.sesname "
                        . "FROM schfee_transactions a "
                        . "JOIN session s ON s.sesid = a.sesid "
                        . "WHERE (can_no = %s "
                        . "OR can_no = %s "
                        . "OR matric_no = %s "
                        . "OR matric_no = %s) "
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
                        . "WHERE (can_no = %s "
                        . "OR can_no = %s "
                        . "OR matric_no = %s "
                        . "OR matric_no = %s) "
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
                        . "WHERE (can_no = %s "
                        . "OR can_no = %s "
                        . "OR matric_no = %s "
                        . "OR matric_no = %s)"
                        . "AND a.status = 'APPROVED' ", 
                        GetSQLValueString($row_search['jambregid'], 'text'),
                        GetSQLValueString($row_search['stdid'], 'text'), 
                        GetSQLValueString($row_search['jambregid'], 'text'), 
                        GetSQLValueString($row_search['stdid'], 'text'));
$Rs_clr_fee = mysql_query($query_clr_fee) or die(mysql_error());
$row_clr_fee = mysql_fetch_assoc($Rs_clr_fee);
$num_row_clr_fee = mysql_num_rows($Rs_clr_fee);




if (isset($_POST['table'])) {

    switch ($_POST['table']) {

        case 'appfee':
            $querySQL = sprintf("UPDATE appfee_transactions SET stamped = 'Yes' WHERE ordid = %s", 
                    GetSQLValueString($_POST['ordid'], 'text'));
            $updateRS = mysql_query($querySQL, $tams) or die(mysql_error());
            $affetted_row = mysql_affected_rows();

            break;

        case 'accfee':
            $querySQL = sprintf("UPDATE accfee_transactions SET stamped = 'Yes' WHERE ordid = %s", GetSQLValueString($_POST['ordid'], 'text'));
            $updateRS = mysql_query($querySQL, $tams) or die(mysql_error());
            $affetted_row = mysql_affected_rows();

            break;
            
         case 'verifee':
            $querySQL = sprintf("UPDATE olevelverifee_transactions SET stamped = 'Yes' WHERE ordid = %s", GetSQLValueString($_POST['ordid'], 'text'));
            $updateRS = mysql_query($querySQL, $tams) or die(mysql_error());
            $affetted_row = mysql_affected_rows();

            break;


        case 'schfee':
            $query_schedule = sprintf("SELECT scheduleid "
                                    . "FROM schfee_transactions "
                                    . "WHERE ordid = %s",
                                    GetSQLValueString($_POST['ordid'], 'text') );
            $GetRS = mysql_query($query_schedule, $tams) or die(mysql_error());
            $row_get = mysql_fetch_assoc($GetRS);
            
            $querySQL = sprintf("UPDATE schfee_transactions "
                    . "SET stamped = 'Yes' "
                    . "WHERE scheduleid = %s "
                    . "AND status = 'APPROVED' "
                    . "AND matric_no = %s ", 
                    GetSQLValueString($row_get['scheduleid'], 'int'), 
                    GetSQLValueString($_POST['stdid'], 'text'));
            $updateRS = mysql_query($querySQL, $tams) or die(mysql_error());
            $affetted_row = mysql_affected_rows();

            break;

        case 'repfee':
            $querySQL = sprintf("UPDATE reparation_transactions SET stamped = 'Yes' WHERE ordid = %s", GetSQLValueString($_POST['ordid'], 'text'));
            $updateRS = mysql_query($querySQL, $tams) or die(mysql_error());
            $affetted_row = mysql_affected_rows();

            break;

        case 'clrfee':

            $querySQL = sprintf("UPDATE clearance_transactions SET stamped = 'Yes' WHERE ordid = %s", GetSQLValueString($_POST['ordid'], 'text'));
            $updateRS = mysql_query($querySQL, $tams) or die(mysql_error());
            $affetted_row = mysql_affected_rows();

            break;

        case 'grand':
            $querySQL = sprintf("UPDATE gradfees_transactions SET stamped = 'Yes' WHERE ordid = %s", GetSQLValueString($_POST['ordid'], 'text'));
            $updateRS = mysql_query($querySQL, $grand) or die(mysql_error());
            $affetted_row = mysql_affected_rows();


            break;

        default:

            break;
    }

    header("Location:{$editFormAction}");
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
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
                                                        <th>&nbsp;</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    
                                                    <?php $i=1; do {?>
                                                    <tr>
                                                        <td><?= $i++; ?></td>
                                                        <td><?= $row_app_fee['can_no']?></td>
                                                        <td><?= $row_app_fee['reference']?></td>
                                                        <td><?= $row_app_fee['sesname']?></td>
                                                        <td><?= $row_app_fee['amt']?></td>
                                                        <td><?= $row_app_fee['date_time']?></td>
                                                        <td>
                                                            <?php if ($row_app_fee['stamped'] == 'Yes') { ?>
                                                            <a target="tab" href="receipts/application.php?stdid=<?= $row_app_fee['can_no']?>&no=<?= $row_app_fee['ordid']?>" class="btn btn-small btn-gray">Receipt</a>
                                                            <?php } else { ?>
                                                                <form method="post" action="<?= $editFormAction; ?>">
                                                                    <button type="submit" name="submit" class="btn btn-small btn-blue">Stamped</button>
                                                                    <input type="hidden" name="ordid" value="<?= $row_app_fee['ordid'] ?>">
                                                                    <input type="hidden" name="table" value="appfee">
                                                                </form>
                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                    <?php } while($row_app_fee = mysql_fetch_assoc($Rs_app_fee))?>
                                                    
                                                    
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
                                                        <th>&nbsp;</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    
                                                    <?php $i=1; do {?>
                                                    <tr>
                                                        <td><?= $i++; ?></td>
                                                        <td><?= $row_acc_fee['can_no']?></td>
                                                        <td><?= $row_acc_fee['reference']?></td>
                                                        <td><?= $row_acc_fee['sesname']?></td>
                                                        <td><?= $row_acc_fee['amt']?></td>
                                                        <td><?= $row_acc_fee['date_time']?></td>
                                                        <td>
                                                            <?php if ($row_acc_fee['stamped'] == 'Yes') { ?>
                                                            <a target="tab" href="receipts/acceptance.php?stdid=<?= $row_acc_fee['can_no']?>&no=<?= $row_acc_fee['ordid'] ?>" class="btn btn-small btn-gray">Receipt</a>
                                                            <?php } else { ?>
                                                                <form method="post" action="<?= $editFormAction; ?>">
                                                                    <button type="submit" name="submit" class="btn btn-small btn-blue">Stamped</button>
                                                                    <input type="hidden" name="ordid" value="<?= $row_acc_fee['ordid'] ?>">
                                                                    <input type="hidden" name="table" value="accfee">
                                                                </form>
                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                    <?php } while($row_acc_fee = mysql_fetch_assoc($Rs_acc_fee))?>
                                                    
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    
                                    <?php if($num_row_veri_fee > 0){?>
                                    <h4>OLevel Verification Fee</h4>
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <table class="table table-bordered table-condensed tabe-striped">
                                                <thead>
                                                    <tr>
                                                        <th>S/n</th>
                                                        <th>Student ID</th>
                                                        <th>Reference </th>
                                                        
                                                        <th>Amount </th>
                                                        <th>Date Time</th>
                                                        <th>&nbsp;</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    
                                                    <?php $i=1; do {?>
                                                    <tr>
                                                        <td><?= $i++; ?></td>
                                                        <td><?= $row_veri_fee['can_no']?></td>
                                                        <td><?= $row_veri_fee['reference']?></td>
                                                        
                                                        <td><?= $row_veri_fee['amt']?></td>
                                                        <td><?= $row_veri_fee['date_time']?></td>
                                                        <td>
                                                            <?php if ($row_veri_fee['stamped'] == 'Yes') { ?>
                                                            <a target="tab" href="receipts/olevel.php?stdid=<?= $row_veri_fee['can_no']?>&no=<?= $row_veri_fee['ordid'] ?>" class="btn btn-small btn-gray">Receipt</a>
                                                            <?php } else { ?>
                                                                <form method="post" action="<?= $editFormAction; ?>">
                                                                    <button type="submit" name="submit" class="btn btn-small btn-blue">Stamped</button>
                                                                    <input type="hidden" name="ordid" value="<?= $row_veri_fee['ordid'] ?>">
                                                                    <input type="hidden" name="table" value="verifee">
                                                                </form>
                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                    <?php } while($row_veri_fee = mysql_fetch_assoc($Rs_veri_fee))?>
                                                    
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
                                                        <th>&nbsp;</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    
                                                    <?php $i=1; do {?>
                                                    <tr>
                                                        <td><?= $i++; ?></td>
                                                        <td><?= $row_sch_fee['can_no']?></td>
                                                        <td><?= $row_sch_fee['matric_no']?></td>
                                                        <td><?= $row_sch_fee['reference']?></td>
                                                        <td><?= $row_sch_fee['sesname']?></td>
                                                        <td><?= $row_sch_fee['amt']?></td>
                                                        <td><?= $row_sch_fee['date_time']?></td>
                                                        <td>
                                                            <?php if ($row_sch_fee['stamped'] == 'Yes') { ?>
                                                            <a target="tab" href="receipts/sch_fee.php?stdid=<?= $row_sch_fee['matric_no']?>&no=<?= $row_sch_fee['ordid'] ?>" class="btn btn-small btn-gray">Receipt</a>
                                                            <?php } else { ?>
                                                                <form method="post" action="<?= $editFormAction; ?>">
                                                                    <button type="submit" name="submit" class="btn btn-small btn-blue">Stamped</button>
                                                                    <input type="hidden" name="ordid" value="<?= $row_sch_fee['ordid'] ?>">
                                                                    <input type="hidden" name="stdid" value="<?= $row_sch_fee['matric_no'] ?>">
                                                                    <input type="hidden" name="table" value="schfee">
                                                                </form>
                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                    <?php } while($row_sch_fee = mysql_fetch_assoc($Rs_sch_fee))?>
                                                    
                                                    
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
                                                        <th>&nbsp;</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    
                                                    <?php $i=1; do {?>
                                                    <tr>
                                                        <td><?= $i++; ?></td>
                                                        <td><?= $row_rep_fee['can_no']?></td>
                                                        <td><?= $row_rep_fee['matric_no']?></td>
                                                        <td><?= $row_rep_fee['reference']?></td>
                                                        <td><?= $row_rep_fee['sesname']?></td>
                                                        <td><?= $row_rep_fee['amt']?></td>
                                                        <td><?= $row_rep_fee['date_time']?></td>
                                                        <td>
                                                            <?php if ($row_rep_fee['stamped'] == 'Yes') { ?>
                                                            <a target="tab" href="receipts/reparation.php?stdid=<?= $row_rep_fee['matric_no']?>&no=<?= $row_rep_fee['ordid']?>" class="btn btn-small btn-gray">Receipt</a>
                                                            <?php } else { ?>
                                                                <form method="post" action="<?= $editFormAction; ?>">
                                                                    <button type="submit" name="submit" class="btn btn-small btn-blue">Stamped</button>
                                                                    <input type="hidden" name="ordid" value="<?= $row_rep_fee['ordid'] ?>">
                                                                    <input type="hidden" name="table" value="repfee">
                                                                </form>
                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                    <?php } while($row_rep_fee = mysql_fetch_assoc($Rs_rep_fee))?>
                                                    
                                                    
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
                                                        <th>&nbsp;</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    
                                                    <?php $i=1; do {?>
                                                    <tr>
                                                        <td><?= $i++; ?></td>
                                                        <td><?= $row_clr_fee['can_no']?></td>
                                                        <td><?= $row_clr_fee['matric_no']?></td>
                                                        <td><?= $row_clr_fee['reference']?></td>
                                                        <td><?= $row_clr_fee['sesname']?></td>
                                                        <td><?= $row_clr_fee['amt']?></td>
                                                        <td><?= $row_clr_fee['date_time']?></td>
                                                        <td>
                                                            <?php if ($row_clr_fee['stamped'] == 'Yes') { ?>
                                                            <a target="tab" href="receipts/clearance.php?stdid=<?= $row_clr_fee['matric_no']?>&no=<?= $row_clr_fee['ordid'] ?>" class="btn btn-small btn-gray">Receipt</a>
                                                            <?php } else { ?>
                                                                <form method="post" action="<?= $editFormAction; ?>">
                                                                    <button type="submit" name="submit" class="btn btn-small btn-blue">Stamped</button>
                                                                    <input type="hidden" name="ordid" value="<?= $row_clr_fee['ordid'] ?>">
                                                                    <input type="hidden" name="table" value="clrfee">
                                                                </form>
                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                    <?php } while($row_clr_fee = mysql_fetch_assoc($Rs_clr_fee))?>
                                                    
                                                   
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

