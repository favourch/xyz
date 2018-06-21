<?php

if (!isset($_SESSION)) {
    session_start();
}
require_once('../../path.php');


$auth_users = "10";
check_auth($auth_users, $site_root);

//Get current session 
mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * FROM session WHERE status = 'TRUE' ORDER BY sesid DESC LIMIT 1 ");
$session = mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);
$totalRows_session = mysql_num_rows($session);

$row_rspros = '';
$totalRows_rspros = '';

if (getAccess() == '10') {

    mysql_select_db($database_tams, $tams);
    $query_rspros = sprintf("SELECT *  
                                    FROM student  
                                    WHERE stdid=%s", GetSQLValueString(getSessionValue('uid'), "text"));
    $rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
    $row_rspros = mysql_fetch_assoc($rspros);
    $totalRows_rspros = mysql_num_rows($rspros);
}


$amount = 25000;


$percent = 100;
$revhead = 'HKC011';

if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root . '/student/profile.php');
}



$query_paid = sprintf("SELECT * FROM clearance_transactions  "
                    . "WHERE status='APPROVED' "
                    . "AND matric_no= %s", 
                    GetSQLValueString(getSessionValue('uid'), 'text'));
$paid = mysql_query($query_paid, $tams) or die(mysql_error());
$row_paid = mysql_fetch_assoc($paid);
$total_paid = mysql_num_rows($paid);

if ($total_paid > 0) {

//means this candidate has paid
    header('Location: ../../index.php');
    exit();
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
                                    <h3><i class="icon-credit-card"></i>
                                        Payment Confirmation
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <div class="alert alert-info">
                                            Below are the details of the payment transaction you are
                                            about to execute, click the <strong>Pay Now</strong> 
                                            button to proceed your payment or <strong>Cancel</strong> button to terminate.
                                        </div>
                                        <table width="690" class="table table-striped table-bordered table-condensed">
                                            <tr>
                                                <th width="150">Matric No : </th>
                                                <td><?php echo  $row_rspros['stdid'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Full Name : </th>
                                                <td><?php echo strtoupper($row_rspros['lname'] . ' ' . $row_rspros['fname']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Level : </th>
                                                <td><?php echo (getAccess() == '10') ? $row_rspros['level'] . '00' : '0'?> </td>
                                            </tr>
                                            <tr>
                                                <th>Payment Type : </th>
                                                <td>FINAL YEAR CLEARANCE FEE</td>
                                            </tr>
                                            <tr>
                                                <th>Amount to be Paid : </th>
                                                <th style="color: #CC0000"><?php echo '=N= ' . number_format($amount); ?></th>
                                            </tr>
                                            <tr>
                                                <td colspan="2">&nbsp; </td>  
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <table width="200" align="center" >
                                                        <form name="form1" method="post" action="<?php echo "processpayment.php" ?>">
                                                            <tr>
                                                                <td width="50%">
                                                                    <input type="submit" name="paynow" class="btn btn-small btn-primary" value="Pay Now"/>
                                                                </td>
                                                                <td width="50%">
                                                                    <input type="button" class="btn btn-small " onclick="javacript:location='../../index.php'" value="Cancel"/>
                                                                </td>
                                                            </tr>
                                                            <input type="hidden" name="stdid" value="<?= $row_rspros['stdid']  ?>"/>
                                                            <input type="hidden" name="sesid" value="<?php echo $row_session['sesid'] ?>"/>
                                                            <input type="hidden" name="prg" value="NULL"/>
                                                            <input name="amount" type="hidden" value="<?php echo $amount ?>"/>
                                                            <input name="canName" type="hidden" value="<?php echo $row_rspros['lname'] . ' ' . $row_rspros['fname'] . ' ' . $row_rspros['mname'] ?>"/>
                                                            <input name="revhead" type="hidden" value="<?php echo $revhead ?>"/>
                                                            <input name="percent" type="hidden" value="<?php echo $percent ?>"/>
                                                            <input name="form_trig" type="hidden" value="form1"/>
                                                        </form>    
                                                    </table>
                                                </td>
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
    </body>
</html>