<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');

$_SESSION['payment'] = array('amount' => '2500', 'additions' => '');
$addition_text = $_SESSION['payment']['additions'] ? 'Additional ' : '';
$prevCleared = !$_SESSION['payment']['prev_ses'];

$row_session['sesid'] = getSessionValue('sesid');

if (!checkFees($row_session['sesid'], getSessionValue('stid'))) {
    header('Location: ../../../payments/index.php');
}

$percent = 100;
$revhead = 'HKC026';

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
                                            <caption><strong><?php echo $addition_text; ?>Payment for Teaching Practice Reposting <?php //echo $_SESSION['payment']['sesname'] ?></strong></caption>
                                            <tr>
                                                <th width="150">Matric No. : </th>
                                                <td><?php echo getSessionValue('stid') ?></td>
                                            </tr>
                                            <tr>
                                                <th>Full Name : </th>
                                                <td><?php echo getSessionValue('lname') . ' ' . getSessionValue('fname') ?></td>
                                            </tr>
                                            <tr>
                                                <th>Amount to be paid: </th>
                                                <th style="color: #CC0000">
                                                    <?php echo '=N= ' . number_format($_SESSION['payment']['amount']); ?>
                                                </th>
                                            </tr>
                                            <tr>
                                                <td colspan="2">&nbsp; </td>  
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <table width="200" align="center" >

                                                        <tr>
                                                            <td width="50%">
                                                                <button onclick="location.href = 'processpayment.php'">Pay Now</button>
                                                            </td>
                                                            <td width="50%">
                                                                <button onclick="location.href = '../'">Cancel</button >
                                                            </td>
                                                        </tr>   
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