<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once("../../path.php");

$auth_users = "11";
check_auth($auth_users, $site_root);

$jambregid = getSessionValue('uid');

//check for O'Level Verification before School Payment is ALLOWED
/*$query_check_verify = sprintf("SELECT * "
                        . "FROM verification "
                        . "WHERE stdid=%s",
                         GetSQLValueString($jambregid, 'text'));
$check_verify = mysql_query($query_check_verify, $tams) or die(mysql_error());
$row_check_verify = mysql_fetch_assoc($check_verify);
$veri_data_row_num = mysql_num_rows($check_verify); 


if ($row_check_verify['status'] != 'releas') {
    header('Location: ../../olevel_service/client/index.php');
    exit;
}
*/
$query_student = sprintf("SELECT * "
                        . "FROM prospective p "
                        . "LEFT JOIN admissions a ON p.admid = a.admid "
                        . "LEFT JOIN admission_type at ON a.typeid = at.typeid "
                        . "WHERE p.jambregid=%s",
                         GetSQLValueString($jambregid, 'text'));
$student = mysql_query($query_student, $tams) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$veri_data_row_num = mysql_num_rows($student);

$_SESSION['payment']['sesid'] = $sesid = $_SESSION['admid'];
$sesname = $_SESSION['admname'];

$_SESSION['payment']['status'] = $status = $row_student['stid'] == $indigene_state_id? 'Indigene': 'Nonindigene';

$query_info = sprintf("SELECT * "
                        . "FROM payschedule "
                        . "WHERE level = %s "
                        . "AND sesid = %s "
                        . "AND admid = %s "
                        . "AND status = %s",
                        GetSQLValueString($row_student['entrylevel'], 'text'),
                        GetSQLValueString($sesid, 'int'),
                        GetSQLValueString($_SESSION['admtype'], 'int'),
                        GetSQLValueString($status, 'text')); 
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$_SESSION['payment']['scheduleid'] = $row_info['scheduleid'];
$_SESSION['payment']['prg'] = $row_info['progofferd'];
$_SESSION['payment']['due'] = $amount = $row_info['amount'];
$_SESSION['payment']['penalty'] = $penalty = 0;

$has_penalty = false; 

if($_SESSION['sesid'] != $_SESSION['admid']) {
    $has_penalty = true;
    $_SESSION['payment']['penalty'] = $penalty = $row_info['penalty'];
} 


// Add penalty to total amount
//$_SESSION['payment']['due'] += $_SESSION['payment']['penalty'];

$_SESSION['payment']['revhead'] = $row_info['revhead'];

$_SESSION['payment']['jambregid'] = $jambregid = getSessionValue('uid');

$owing = ['status' => true, 'amt' => 0.00];

$_SESSION['payment']['name'] =  $_SESSION['lname'].' '.$_SESSION['fname'].' '.$_SESSION['mname'];

$pay_status = checkPaymentPros($sesid, $jambregid, $amount, 'sch');
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
                                        <?php echo $sesname?> School Fee Payment
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
                                                
                                                <?php if($has_penalty) :?>
                                                <th>Penalty</th>
                                                <?php endif;?>
                                                
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

                                                        <?php if ($has_penalty) : ?>
                                                        <td><?php echo $penalty?></td>
                                                        <?php endif; ?>

                                                        <td><?php echo $owing['desc'] ?> School Fee</td>
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
                                                No Payment Schedule has been set for the (<?php echo $sesname?>) School Fee for New Students. Please, Check Back Later!
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
        </div>
    </body>
</html>