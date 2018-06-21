<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



$MM_authorizedUsers = "10";

$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page

function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) {

    // For security, start by assuming the visitor is NOT authorized. 

    $isValid = False;



    // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
    // Therefore, we know that a user is NOT logged in if that Session variable is blank. 

    if (!empty($UserName)) {

        // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
        // Parse the strings into arrays. 

        $arrUsers = Explode(",", $strUsers);

        $arrGroups = Explode(",", $strGroups);

        if (in_array($UserName, $arrUsers)) {

            $isValid = true;
        }

        // Or, you may restrict access to only certain users based on their username. 

        if (in_array($UserGroup, $arrGroups)) {

            $isValid = true;
        }

        if (($strUsers == "") && false) {

            $isValid = true;
        }
    }

    return $isValid;
}

$MM_restrictGoTo = "../index.php";

if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("", $MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {

    $MM_qsChar = "?";

    $MM_referrer = $_SERVER['PHP_SELF'];
    if (strpos($MM_restrictGoTo, "?"))
        $MM_qsChar = "&";
    if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0)
        $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
    $MM_restrictGoTo = $MM_restrictGoTo . $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
    header("Location: " . $MM_restrictGoTo);

    exit;
}

$page_title = "Tasued";

$query_feehistory = sprintf('SELECT matric_no, ordid, status, reference, amt, date_time '
                            . 'FROM schfee_transactions '
                            . 'WHERE matric_no = %s '
                            . 'ORDER BY date_time DESC', GetSQLValueString($_SESSION['MM_Username'], "text"));
$feehistory = mysql_query($query_feehistory, $tams) or die(mysql_error());
$row_feehistory = mysql_fetch_assoc($feehistory);
$totalRows_feehistory = mysql_num_rows($feehistory);

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
                                        Fee Payment Instruction
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <table class="table table-condensed table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>S/N</th>
                                                    <th>Reference</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                    <th>&nbsp;</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if ($totalRows_feehistory > 0) {
                                                    for ($idx = 0; $idx < $totalRows_feehistory; $idx++, $row_feehistory = mysql_fetch_assoc($feehistory)) {
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $idx + 1 ?></td>
                                                            <td align="center"><?php echo $row_feehistory['reference'] ?></td>
                                                            <td align="center"><?php echo $row_feehistory['amt'] ?></td>
                                                            <td><?php echo $row_feehistory['status'] ?></td>
                                                            <td align="center"><?php echo $row_feehistory['date_time'] ?></td>                            
                                                            <td>
                                                                <?php if ($row_feehistory['status'] == 'APPROVED') { ?>
                                                                    <a target="_blank" href="receipt.php?no=<?php echo $row_feehistory['ordid'] ?>">Print Receipt</a>
                                                                <?php } ?>
                                                            </td>
                                                        </tr>
                                                    <?php }
                                                }
                                                else { ?>
                                                    <tr>
                                                        <td colspan="8">
                                                            <div class="alert alert-danger">
                                                                You have not made any payment yet! 
                                                            </div>
                                                        </td>
                                                    </tr>
                                        <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
    </body>
</html>