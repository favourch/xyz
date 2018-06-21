<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "11";
check_auth($auth_users, $site_root);

if(isset($_GET['check_status'])) {
    $ord_id = $_GET['check_status'];
    $table = $_GET['check_type'];
    $response = checkPaymentStatus($ord_id, $merchant_id, $table, $tams); 
    
    $notification->set_notification($response['msg'], $response['status']);
}


$query_history = sprintf('SELECT can_no, ordid, status, reference, amt, date_time, stamped '
        . 'FROM appfee_transactions '
        . 'WHERE can_no = %s '
        . 'ORDER BY date_time DESC', GetSQLValueString($_SESSION['MM_Username'], "text"));
$history = mysql_query($query_history, $tams) or die(mysql_error());
$totalRows_history = mysql_num_rows($history);

$query_acchistory = sprintf('SELECT can_no, ordid, status, reference, amt, date_time, stamped '
        . 'FROM accfee_transactions '
        . 'WHERE can_no = %s '
        . 'ORDER BY date_time DESC', GetSQLValueString($_SESSION['MM_Username'], "text"));
$acchistory = mysql_query($query_acchistory, $tams) or die(mysql_error());
$totalRows_acchistory = mysql_num_rows($acchistory);

$query_olvlhistory = sprintf('SELECT can_no, ordid, status, reference, amt, date_time, stamped '
        . 'FROM olevelverifee_transactions '
        . 'WHERE can_no = %s OR matric_no = %s  '
        . 'ORDER BY date_time DESC', GetSQLValueString($_SESSION['MM_Username'], "text"), GetSQLValueString($_SESSION['MM_Username'], "text"));
$olvlhistory = mysql_query($query_olvlhistory, $tams) or die(mysql_error());
$totalRows_olvlhistory = mysql_num_rows($olvlhistory);

$query_feehistory = sprintf('SELECT can_no, ordid, status, reference, amt, date_time, stamped '
        . 'FROM schfee_transactions '
        . 'WHERE can_no = %s '
        . 'ORDER BY date_time DESC', GetSQLValueString($_SESSION['MM_Username'], "text"));
$feehistory = mysql_query($query_feehistory, $tams) or die(mysql_error());
//$row_feehistory = mysql_fetch_assoc($feehistory);
$totalRows_feehistory = mysql_num_rows($feehistory); 

$query_reghistory = sprintf('SELECT can_no, ordid, status, reference, amt, date_time, stamped '
        . 'FROM registration_transactions '
        . 'WHERE can_no = %s '
        . 'ORDER BY date_time DESC', GetSQLValueString($_SESSION['MM_Username'], "text"));
$reghistory = mysql_query($query_reghistory, $tams) or die(mysql_error());
$totalRows_reghistory = mysql_num_rows($reghistory);

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
<!--                    <div class="breadcrumbs">
                        <ul>
                            <li>
                                <a href="more-login.html">Home</a><i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="student.php">Profile</a>
                            </li>
                        </ul>
                        <div class="close-bread">
                            <a href="#"><i class="icon-remove"></i></a>
                        </div>
                    </div>-->

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Payment History
                                    </h3>
                                </div>
                                <div class="box-content">                                    
                                    <strong>Application</strong>
                                    <div class="well">
                                        <table class="table table-hover table-striped table-bordered">
                                            <thead>
                                            <th>S/N</th>
                                            <th>Reference</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th></th>
                                            </thead>
                                            <tbody>  
                                                <?php 
                                                    if($totalRows_history > 0):
                                                        $idx = 0;
                                                        for(;$row_history = mysql_fetch_assoc($history);) :?>
                                                <tr>
                                                    <td><?php echo ++$idx ?></td>
                                                    <td align="center"><?php echo $row_history['reference'] ?></td>
                                                    <td align="center"><?php echo $row_history['amt'] ?></td>
                                                    <td><?php echo $row_history['status'] ?></td>
                                                    <td align="center"><?php echo $row_history['date_time'] ?></td>                            
                                                    <td>
                                                        <?php if ($row_history['status'] == 'APPROVED') { ?>
                                                            <a class="btn btn-small btn-blue" target="_blank" href="admission_payment/receipt.php?no=<?php echo $row_history['ordid'] ?>">Print Receipt</a>
                                                            <?php }else { ?>
                                                            <a class="btn btn-small btn-blue" href="?check_status=<?php echo $row_history['ordid'] ?>&check_type=appfee">Check Status</a>
                                                        <?php }?>
                                                    </td>
                                                </tr>
                                                <?php 
                                                    endfor;
                                                    else :
                                                ?>
                                                <tr>
                                                    <td colspan="6">You have not made any payment yet!</td>
                                                </tr>
                                                <?php endif;?>
                                            </tbody>
                                        </table>
                                    </div><br>    

                                    <?php if($totalRows_acchistory > 0):?>
                                    <strong>Acceptance</strong>
                                    <div class="well">
                                        <table class="table table-hover table-striped table-bordered">
                                            <thead>
                                            <th>S/N</th>
                                            <th>Reference</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th></th>
                                            </thead>
                                            <tbody>                                            
                                                <?php
                                                    $idx = 0;
                                                    for(;$row_acchistory = mysql_fetch_assoc($acchistory);) :?>
                                                <tr>
                                                    <td><?php echo ++$idx ?></td>
                                                    <td align="center"><?php echo $row_acchistory['reference'] ?></td>
                                                    <td align="center"><?php echo $row_acchistory['amt'] ?></td>
                                                    <td><?php echo $row_acchistory['status'] ?></td>
                                                    <td align="center"><?php echo $row_acchistory['date_time'] ?></td>                            
                                                    <td>
                                                        <?php if ($row_acchistory['status'] == 'APPROVED') { ?>
                                                           <a class="btn btn-small btn-blue" target="_blank" href="acceptance_payment/receipt.php?no=<?php echo $row_acchistory['ordid'] ?>">Print Receipt</a>
                                                            <?php }else { ?>
                                                            <a class="btn btn-small btn-blue" href="?check_status=<?php echo $row_acchistory['ordid'] ?>&check_type=accfee">Check Status</a>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                                <?php endfor;?>
                                            </tbody>
                                        </table>
                                    </div><br> 
                                    <?php endif; ?>

                                    <?php if($totalRows_feehistory > 0):?>
                                    <strong>School Fees</strong>
                                    <div class="well">
                                        <table class="table table-hover table-striped table-bordered">
                                            <thead>
                                                <th>S/N</th>
                                                <th>Reference</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th></th>
                                            </thead>
                                            <tbody>                                            
                                                <?php 
                                                   for(;$row_feehistory = mysql_fetch_assoc($feehistory);) :?>
                                                <tr>
                                                    <td><?php echo ++$idx ?></td>
                                                    <td align="center"><?php echo $row_feehistory['reference'] ?></td>
                                                    <td align="center"><?php echo $row_feehistory['amt'] ?></td>
                                                    <td><?php echo $row_feehistory['status'] ?></td>
                                                    <td align="center"><?php echo $row_feehistory['date_time'] ?></td>                            
                                                    <td>
                                                        <?php if ($row_feehistory['status'] == 'APPROVED') { ?>
                                                            <a class="btn btn-small btn-blue" target="_blank" href="fee_payment/receipt.php?no=<?php echo $row_feehistory['ordid'] ?>">Print Receipt</a>
                                                            <?php }else { ?>
                                                            <a class="btn btn-small btn-blue" href="?check_status=<?php echo $row_feehistory['ordid'] ?>&check_type=schfee">Check Status</a>
                                                            
                                                        <?php  } ?>
                                                    </td>
                                                </tr>
                                                <?php endfor;?>                                                
                                            </tbody>
                                        </table>
                                    </div><br> 
                                    <?php endif; ?>
                                    
                                    <?php if($totalRows_olvlhistory > 0):?>
                                    <strong>O&apos;Level Verification Fee</strong>
                                    <div class="well">
                                        <table class="table table-hover table-striped table-bordered">
                                            <thead>
                                                <th>S/N</th>
                                                <th>Reference</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th></th>
                                            </thead>
                                            <tbody>                                            
                                                <?php 
                                                   for(;$row_olvlhistory = mysql_fetch_assoc($olvlhistory);) :?>
                                                <tr>
                                                    <td><?php echo ++$idx ?></td>
                                                    <td align="center"><?php echo $row_olvlhistory['reference'] ?></td>
                                                    <td align="center"><?php echo $row_olvlhistory['amt'] ?></td>
                                                    <td><?php echo $row_olvlhistory['status'] ?></td>
                                                    <td align="center"><?php echo $row_olvlhistory['date_time'] ?></td>                            
                                                    <td>
                                                        <?php if ($row_olvlhistory['status'] == 'APPROVED') { ?>
                                                            <a class="btn btn-small btn-blue" target="_blank" href="../olevel_service/client/olevel_veri_payment/receipt.php?no=<?php echo $row_olvlhistory['ordid'] ?>">Print Receipt</a>
                                                            <?php }else { ?>
                                                            <a class="btn btn-small btn-blue" href="?check_status=<?php echo $row_olvlhistory['ordid'] ?>&check_type=olevelfee">Check Status</a>
                                                            
                                                        <?php  } ?>
                                                    </td>
                                                </tr>
                                                <?php endfor;?>                                                
                                            </tbody>
                                            
                                        </table>
                                    </div><br> 
                                    <?php endif; ?>
                                    
                                    <?php if($totalRows_reghistory > 0):?>
                                    <strong>Registration Fee</strong>
                                    <div class="well">
                                        <table class="table table-hover table-striped table-bordered">
                                            <thead>
                                                <th>S/N</th>
                                                <th>Reference</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th></th>
                                            </thead>
                                            <tbody>                                            
                                                <?php 
                                                   for(;$row_reghistory = mysql_fetch_assoc($reghistory);) :?>
                                                <tr>
                                                    <td><?php echo ++$idx ?></td>
                                                    <td align="center"><?php echo $row_reghistory['reference'] ?></td>
                                                    <td align="center"><?php echo $row_reghistory['amt'] ?></td>
                                                    <td><?php echo $row_reghistory['status'] ?></td>
                                                    <td align="center"><?php echo $row_reghistory['date_time'] ?></td>                            
                                                    <td>
                                                        <?php if ($row_reghistory['status'] == 'APPROVED') { ?>
                                                            <a class="btn btn-small btn-blue" target="_blank" href="../registration_fee/receipt.php?no=<?php echo $row_reghistory['ordid'] ?>">Print Receipt</a>
                                                            <?php }else { ?>
                                                            <a class="btn btn-small btn-blue" href="?check_status=<?php echo $row_reghistory['ordid'] ?>&check_type=regfee">Check Status</a>
                                                            
                                                        <?php  } ?>
                                                    </td>
                                                </tr>
                                                <?php endfor;?>                                                
                                            </tbody>
                                            
                                        </table>
                                    </div><br> 
                                    <?php endif; ?>
                                    
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

