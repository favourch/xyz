<?php
if (!isset($_SESSION)) {
session_start();
}

require_once('../path.php');

$auth_users = "10";
check_auth($auth_users, $site_root);

fillAccomDetails($site_root, $tams);


$sesid = getSessionValue('sesid');
$sesname = getSessionValue('sesname'); 

//Query for previous session
$QueryPrevSession = sprintf("SELECT * FROM session WHERE sesid < %s ORDER BY sesid DESC LIMIT 1 ", 
                        GetSQLValueString($sesid, "int"));
$prevSession = mysql_query($QueryPrevSession, $tams) or die(mysql_error());
$prevSes = mysql_fetch_assoc($prevSession);

//Get available installment Options for the current session
$queryInstallment = sprintf("SELECT * FROM installment WHERE sesid = %s ", 
                       GetSQLValueString($sesid, "int"));
$installment = mysql_query($queryInstallment, $tams) or die(mysql_error());
$instl = mysql_fetch_assoc($installment);
   //echo 1; die();
$_SESSION['payment']['instalpercent'] = [$instl['instal1'], $instl['instal2']];

$_SESSION['payment']['sesid'] = $sesid;
$_SESSION['payment']['additions'] = false;

$query_info = sprintf("SELECT *  
                        FROM student 
                        WHERE stdid = %s",
 GetSQLValueString(getSessionValue('uid'), "text"));
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$_SESSION['payment']['name'] = $row_info['lname'].' '.$row_info['fname'].' '.$row_info['mname'];

$query_paySes = sprintf("SELECT * FROM session "
                      . "WHERE sesid BETWEEN %s AND %s "
                      . "ORDER BY sesid DESC",
                      GetSQLValueString($row_info['sesid'], "int"),
                      GetSQLValueString($prevSes['sesid'], "int"));
$paySes = mysql_query($query_paySes, $tams) or die(mysql_error());
$totalRows_paySes = mysql_num_rows($paySes);


$owing = [];
$prevCleared = true;
$level = $row_info['level'];
$status = $row_info['stid'] == $indigene_state_id  ? 'Indigene': 'Nonindigene';
$extra_msg = NULL;

 $query_curSchedule = sprintf("SELECT * "
                            . "FROM payschedule "
                            . "WHERE sesid = %s "
                            . "AND level = %s "
                            . "AND status = %s "
                            . "AND admid = %s",
                            GetSQLValueString($sesid, "int"),
                            GetSQLValueString($level, "text"),
                            GetSQLValueString($status, "text"),
                            GetSQLValueString($row_info['admid'], "text")); 
$curSchedule = mysql_query($query_curSchedule, $tams) or die(mysql_error());
$row_curSchedule = mysql_fetch_assoc($curSchedule);
$totalRows_curSchedule = mysql_num_rows($curSchedule);

$_SESSION['payment']['penalty'] = $row_curSchedule['penalty'];
$_SESSION['payment']['level'] = $row_curSchedule['level'];

// Get information for previous session
for ($idx = 1; $row_paySes = mysql_fetch_assoc($paySes); $idx++) {
    $amount = 0;
    $query_prevSchedule = sprintf("SELECT * 
                            FROM payschedule p 
                            JOIN registration r ON p.sesid = r.sesid 
                            AND p.level = r.level  
                            WHERE p.sesid = %s 
                            AND r.stdid = %s 
                            AND p.status = %s 
                            AND p.admid = %s", 
                            GetSQLValueString($row_paySes['sesid'], "int"), 
                            GetSQLValueString(getSessionValue('uid'), "text"),
                            GetSQLValueString($status, "text"), 
                            GetSQLValueString($row_info['admid'], "text"));
    $prevSchedule = mysql_query($query_prevSchedule, $tams) or die(mysql_error());
    $row_prevSchedule = mysql_fetch_assoc($prevSchedule);
    $totalRows_prevSchedule = mysql_num_rows($prevSchedule);
    
    $query_prevPay = sprintf("SELECT *  
                            FROM schfee_transactions 
                            WHERE scheduleid = %s 
                            AND matric_no = %s 
                            AND status = 'APPROVED'", 
                        GetSQLValueString($row_prevSchedule['scheduleid'], "int"), 
                        GetSQLValueString(getSessionValue('MM_Username'), "text"));
    $prevPay = mysql_query($query_prevPay, $tams) or die(mysql_error());
    $totalRows_prevPay = mysql_num_rows($prevPay);

    for (; $row_prevPay = mysql_fetch_assoc($prevPay);) {
        $amount += doubleval(str_replace(',', '', substr($row_prevPay['amt'], 3)));
    }

    if ($row_prevSchedule['amount'] > $amount) {
        $prevCleared = false;
        $last = $row_paySes['sesid'];
        $_SESSION['payment']['prev_ses'] = true;
        $_SESSION['payment']['sesname'] = $owing[$row_paySes['sesid']]['sesname'] = $row_paySes['sesname'];
        $_SESSION['payment']['amount'] = $owing[$row_paySes['sesid']]['amount'] = $row_prevSchedule['amount'] - $amount;
        $_SESSION['payment']['penalty'] = $owing[$row_paySes['sesid']]['penalty'] = $row_prevSchedule['penalty'];
        $_SESSION['payment']['scheduleid'] = $owing[$row_paySes['sesid']]['$scheduleid'] = $row_prevSchedule['scheduleid'];
        $_SESSION['payment']['revhead'] = $row_prevSchedule['revhead'];
        $_SESSION['payment']['level'] = $row_prevSchedule['level'];
        $_SESSION['payment']['sesid'] = $row_paySes['sesid'];
        $owing[$row_paySes['sesid']]['last'] = false;
    }
}

if(isset($last)) {
    $owing[$last]['last'] = true;
}

$payment_enabled = $row_curSchedule['paystatus'] == 'active'? true: false;
$_SESSION['payment']['payment_enabled'] = $payment_enabled;

if ($prevCleared) {
    $_SESSION['payment']['prev_ses'] = false;
    $_SESSION['payment']['sesname'] = $owing[$sesid]['sesname'] = $sesname;
    $_SESSION['payment']['amount'] = $owing[$sesid]['amount'] = $row_curSchedule['amount'];
    $_SESSION['payment']['scheduleid'] = $owing[$sesid]['$scheduleid'] = $row_curSchedule['scheduleid'];    
    $_SESSION['payment']['with_penalty'] = $row_curSchedule['penalty_enabled'] == 'TRUE'? true: false;
    $_SESSION['payment']['revhead'] = $row_curSchedule['revhead'];
    $_SESSION['payment']['level'] = $row_curSchedule['level'];
    $_SESSION['payment']['percent'] = 100;
    $_SESSION['payment']['installment'] = 'none';
    $_SESSION['payment']['additions'] = false;
	
    $query_curPay = sprintf("SELECT *  
                            FROM schfee_transactions 
                            WHERE scheduleid = %s 
                            AND matric_no = %s 
                            AND status = 'APPROVED'",
                            GetSQLValueString($row_curSchedule['scheduleid'], "int"), 
                            GetSQLValueString(getSessionValue('MM_Username'), "text"));
    $curPay = mysql_query($query_curPay, $tams) or die(mysql_error());
    $firstTran = $row_curPay = mysql_fetch_assoc($curPay);
    $totalRows_curPay = mysql_num_rows($curPay);
    $curAmount = 0;
    $totalPercent = 0;

    for ($idx = 0; $idx < $totalRows_curPay; $row_curPay = mysql_fetch_assoc($curPay), $idx++) {
        $curAmount += doubleval(str_replace(['NGN', 'N', ','], '', $row_curPay['amt']));
        $totalPercent += intval($row_curPay['percentPaid']);
    }

    if($totalPercent >= 100) {
        $_SESSION['payment']['installment'] = 'complete';
        $owing[$sesid]['amount'] = $_SESSION['payment']['amount'] -= $curAmount;
        
        if($row_curSchedule['amount'] > $curAmount) {
            $_SESSION['payment']['additions'] = true;
            $_SESSION['payment']['percent'] = 100;
        }
    }elseif($totalPercent === $instl['instal1']) {
        $_SESSION['payment']['percent'] = $instl['instal2'];
        $_SESSION['payment']['installment'] = 'incomplete';        
        $_SESSION['payment']['with_penalty'] = false;
        
        $_SESSION['payment']['amount'] = ($_SESSION['payment']['amount'] * $instl['instal2']) / 100;  
                
        // Increase the display amount for
        //$owing[$sesid]['amount'] = $owing[$sesid]['amount'] * 100/$instl['instal2'];
    }elseif($totalPercent == 0) {
    	if($_SESSION['payment']['with_penalty']) {
            $owing[$sesid]['amount'] = $_SESSION['payment']['amount'] += $_SESSION['payment']['penalty'];  
        }
    }

//    
//    if ($totalRows_curPay == 0) {
//        $_SESSION['payment']['additions'] = false;
//    } elseif ($totalRows_curPay == 1) {
//
//        if ($firstTran['percentPaid'] == 100) {
//
//            $_SESSION['payment']['installment'] = 'complete';
//            $owing[$sesid]['amount'] = $_SESSION['payment']['amount'] -= $curAmount;
//
//        } elseif ($firstTran['percentPaid'] == $instl['instal1']) {
//
//            $_SESSION['payment']['percent'] = $instl['instal2'];
//            $_SESSION['payment']['installment'] = 'incomplete';
//            $_SESSION['payment']['additions'] = false;
//
//            // Get outstanding payment.
//            $owe = $_SESSION['payment']['amount'] - $curAmount;
//
//            // Increase amount to make 40% equal to outstanding + addition
//            //$_SESSION['payment']['amount'] = $owing[$sesid]['amount'] = $owe * 2.5;
//        }
//    } elseif ($totalRows_curPay > 1) {
//
//        if ($totalPercent >= 100) {
//            $_SESSION['payment']['installment'] = 'complete';
//            $owing[$sesid]['amount'] = $_SESSION['payment']['amount'] -= $curAmount;
//        }
//    }

}
?>
<!doctype html>
<html ng-app="tams-mod">
    <?php include INCPATH."/header.php" ?>

    <body ng-controller="PayController" data-layout-sidebar="fixed" data-layout-topbar="fixed">
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
                                    <h3><i class="icon-money"></i>
                                         Payment
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <?php if (!$prevCleared) { ?>
                                            
                                            <div class="row-fluid">
                                                <div class="alert alert-error">
                                                    You have outstanding payments from previous session(s).
                                                </div>
                                            </div>

                                            <table class="table table-striped">
                                                <caption><strong>Outstanding Payment(s)</strong></caption>
                                                <thead>
                                                <th>Session</th>
                                                <th>Amount</th>
                                                <th>Penalty</th>
                                                <th>&nbsp;</th>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($owing as $session => $values) { ?>
                                                        <tr>
                                                            <td>
                                                                <?php echo $values['sesname'] ?>
                                                            </td>
                                                            <td>
                                                                <?php echo number_format($values['amount']) ?>
                                                            </td>
                                                            <td>
                                                                <?php echo number_format($values['penalty']) ?>
                                                            </td>
                                                            <td>
                                                                <?php if($values['last']) :?>
                                                                <button class="btn btn-lime" onclick="location.href = 'paymentinfo.php'">Pay Now</button>
								<?php endif;?>
                                                                
                                                                <!--TODO: Add pay button from testing 'last' value-->
                                                            </td>                            
                                                        </tr>
                                                    <?php } ?>
                                                    <tr>
                                                        <td colspan="4" style="text-align: center"> 

                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                        <?php }else if($totalPercent >= 100 && !$_SESSION['payment']['additions']) { ?>
                                            <div class="row-fluid">
                                                <div class="alert alert-error">
                                                    You are cleared for the CURRENT SESSION. Click on Pay History link to REPRINT your RECEIPTS!
                                                </div>
                                            </div>
                                        <?php }else { ?>
                                        
                                            <?php if($payment_enabled) :?>
                                            <div class="row-fluid">
                                                <div class="alert alert-success">
                                                   You are cleared for the previous session(s)!
                                                </div>
                                            </div>
                                            <?php else :?>
                                            <div class="row-fluid">
                                                <div class="alert alert-error">
                                                    Payment for this session is currently not enabled!
                                                </div>
                                            </div>
                                            <?php endif;?>
                                        
                                            <br/>
                                            <table class="table table-striped">
                                                <caption><strong>Payment Invoice</strong></caption>
                                                <thead>
                                                <th>Session</th>
                                                <th>Amount</th>
                                                <th>Per cent</th>
                                                <th>Penalty</th>
                                                </thead>
                                                <tbody>
                                                        <?php if ($_SESSION['payment']['installment'] != 'complete') { // Incomplete payments ?>
                                                        <tr>
                                                            <td>
                                                            <?php echo $owing[$sesid]['sesname'] ?>
                                                            </td>
                                                            <td ng-bind="dispAmt | currency: 'N': 2">
                                                            <?php echo $_SESSION['payment']['amount'] ?>
                                                            </td>
                                                            <td>
                                                                <select ng-model="percent" 
                                                                        ng-options="values.value as values.name for values in validValues"
                                                                        ng-click="calcAmt()">
                                                                </select>
                                                            </td>
                                                        <td>
                                                            <?php 
                                                                if($_SESSION['payment']['with_penalty']) {
                                                                    echo $_SESSION['payment']['penalty'];
                                                                }else {
                                                                    echo '-';
                                                                }
                                                            ?>
                                                        </td>
                                                        </tr>                        
                                                        <tr>
                                                        <td colspan="4" style="text-align: center"> 
                                                                <button class="btn btn-blue" ng-click="processUrl('percent')">Pay Now</button>
                                                            </td>
                                                        </tr>
                                                        <?php }elseif ($_SESSION['payment']['additions']) { // Additional payments ?>
                                                        <tr>
                                                            <td>
                                                            <?php echo $owing[$sesid]['sesname'] ?>
                                                            </td>
                                                            <td>
                                                            <?php echo $owing[$sesid]['amount'] ?>
                                                            </td>
                                                            <td align="center">-</td>
                                                            <td align="center">-</td>
                                                        </tr>                        
                                                        <tr>
                                                        <td colspan="4" style="text-align: center"> 
                                                                <button class="btn btn-blue" ng-click="processUrl('addition')">Pay Now</button>
                                                            </td>
                                                        </tr>
                                                        <?php }else { ?>
                                                        <tr>
                                                            <td colspan="4">
                                                                <div class="alert alert-success">
                                                                    You are not owing for the current session (<?php echo $owing[$sesid]['sesname'] ?>).
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>                              
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
        </div>
    </body>
    <script>

    angular.module('tams-mod', [])
    
    .controller('PayController', function($scope, $window) {

        $scope.validValues = [
    
            <?php if(isset($instl['instal1'])){?>
            <?php if(is_null($firstTran['percentPaid']) || $firstTran['percentPaid'] == 0){?>           
            {'value': 100, 'name': '100%'},
            {'value': <?= $instl['instal1']?>, 'name': '<?= $instl['instal1'].'%'?>'},
            <?php }?>
            <?php if($firstTran['percentPaid'] == $instl['instal1']){?>
            {'value': <?= $instl['instal2']?>, 'name': '<?= $instl['instal2'].'%'?>'}
            <?php }
            }else{?>
              {'value': 100, 'name': '100%'}          
            <?php }?>
        ];
        

        $scope.percent = <?php echo $_SESSION['payment']['percent'] ?>;       
        $scope.amount = <?php echo $owing[$sesid]['amount'] ?>;
        $scope.dispAmt = <?php echo $_SESSION['payment']['amount'] ?>;
        
        $scope.calcAmt = function () {
            $scope.dispAmt = $scope.amount * $scope.percent/100;
        };
                
        $scope.processUrl = function(type) {
            switch(type) {
                case 'percent':
                    $window.location.href = 'paymentinfo.php?pc='+$scope.percent;
                    break;

                case 'addition':
                    $window.location.href = 'paymentinfo.php?ad&pc=100';
                    break;

                default:
            }

        };
    });

    </script>
</html>