<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once("../../path.php");

$auth_users = "11";
check_auth($auth_users, $site_root);

$_SESSION['payment']['sesid'] = $sesid = $_SESSION['admid'];
$sesname = $_SESSION['admname'];

$query_info = sprintf("SELECT * "
                        . "FROM payschedule "
                        . "WHERE level = '0' "
                        . "AND sesid = %s "
                        . "AND admid = %s "
                        . "AND regtype = %s "
                        . "AND payhead = %s",
                        GetSQLValueString($sesid, 'int'),
                        GetSQLValueString($_SESSION['admtype'], 'int'),
                        GetSQLValueString($_SESSION['regmode'] ,'text'),
                        GetSQLValueString('app', 'text'));
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$_SESSION['payment']['scheduleid'] = $row_info['scheduleid'];
$_SESSION['payment']['due'] = $amount = $row_info['amount'];
$_SESSION['payment']['revhead'] = $row_info['revhead'];

$_SESSION['payment']['jambregid'] = $jambregid = getSessionValue('uid');

$owing = ['status' => true, 'amt' => 0.00];

$_SESSION['payment']['name'] =  $_SESSION['lname'].' '.$_SESSION['fname'].' '.$_SESSION['mname'];

$paytype='app';
$pay_status = checkPaymentPros($sesid, $jambregid, $amount, $paytype);
if(!$pay_status['status']){
    $owing['status'] = !$pay_status['status'];
    $_SESSION['payment']['amt'] = $owing['amt'] = $pay_status['owing'];
    $owing['desc'] = $pay_status['desc'];
    
    $_SESSION['payment']['percent'] = $owing['desc'] == 'Incomplete'? 0: 100;    
}else {
    $owing['status'] = !$pay_status['status'];
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
                                        <?php echo $sesname?> Application Payment
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <?php if ($owing['status']) { ?>

                                            <table class="table table-striped">
                                                <caption><strong><?php echo $owing['desc'] ?> Payment(s)</strong></caption>
                                                <thead>
                                                <th>Session</th>
                                                <th>Amount</th>
                                                <th>Description</th>
                                                <th></th>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <?php echo $sesname ?>
                                                        </td>
                                                        <td>
                                                            N<?php echo number_format($owing['amt']) ?>
                                                        </td>
                                                        <td><?php echo $owing['desc'] ?> Application Fee</td>
                                                        <td>
                                                            <button class="btn btn-primary" onclick="location.href = 'paymentinfo.php'">Pay Now</button>
                                                        </td>                            
                                                    </tr>
                                                    <tr>
                                                        <td colspan="4" style="text-align: center"> 

                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                        <?php } else { ?>

                                            <p>
                                                You do not owe this payment (<?php echo $sesname?> Application fee)!
                                            </p>
                                            <br/>


                                        <?php } ?>
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